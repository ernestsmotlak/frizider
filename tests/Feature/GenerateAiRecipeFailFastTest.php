<?php

namespace Tests\Feature;

use App\Ai\GeminiAiClient;
use App\Ai\Operations\RecipeFromIngredients;
use App\Contracts\AiClient;
use App\Enums\AiCreditTransactionType;
use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Jobs\GenerateAiRecipe;
use App\Models\AiCreditTransaction;
use App\Models\User;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GenerateAiRecipeFailFastTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_400_fails_on_the_first_attempt_and_refunds(): void
    {
        [$log, $charge, $credits, $user] = $this->pending();

        Http::fake(['*' => Http::response('{"error":{"code":400,"status":"INVALID_ARGUMENT"}}', 400)]);

        GenerateAiRecipe::dispatchSync($log, $charge);

        // The whole point: one call, not one per $tries.
        Http::assertSentCount(1);

        $log->refresh();
        $this->assertSame(AiGenerationStatus::Failed, $log->status);
        $this->assertNotNull($log->completed_at);
        $this->assertStringContainsString('400', $log->error_message);
        // Granted 2, spent 1, refunded 1.
        $this->assertSame(2, $credits->balance($user->id), 'the credit must come back');
    }

    public function test_a_500_still_throws_so_laravel_can_retry(): void
    {
        [$log, $charge] = $this->pending();

        Http::fake(['*' => Http::response('{"error":{"code":500}}', 500)]);

        $this->expectException(RuntimeException::class);

        (new GenerateAiRecipe($log, $charge))->handle(app(RecipeFromIngredients::class), app(AiClient::class));
    }

    public function test_the_client_never_sees_the_providers_error_body(): void
    {
        [$log, $charge, , $user] = $this->pending();

        Http::fake(['*' => Http::response('{"error":{"code":400,"status":"INVALID_ARGUMENT"}}', 400)]);
        GenerateAiRecipe::dispatchSync($log, $charge);

        $this->actingAs($user, 'api');

        foreach ([
                     $this->getJson("/api/recipe/ai/generations/{$log->id}"),
                     $this->getJson('/api/recipe/ai/active-generations'),
                 ] as $response) {
            $response->assertOk();
            $body = $response->getContent();

            $this->assertStringNotContainsString('INVALID_ARGUMENT', $body);
            $this->assertStringNotContainsString('Gemini', $body);
            $this->assertStringContainsString(UserAiRecipeLog::CLIENT_FAILURE_MESSAGE, $body);
        }

        // ...while the real reason is still on the row for us.
        $this->assertStringContainsString('INVALID_ARGUMENT', $log->refresh()->error_message);
    }

    /**
     * A charged, pending generation ready to run against a faked Gemini.
     *
     * @return array{0: UserAiRecipeLog, 1: AiCreditTransaction, 2: AiCreditService, 3: User}
     */
    private function pending(): array
    {
        config([
            'services.ai.driver' => 'gemini',
            'services.ai.model' => 'gemini-3.5-flash',
            'services.ai.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'services.ai.gemini.key' => 'test-key',
        ]);

        $this->assertInstanceOf(GeminiAiClient::class, app(AiClient::class));

        $user = User::factory()->create();
        $credits = app(AiCreditService::class);
        $credits->grant($user->id, 2, AiCreditTransactionType::PromoGrant);

        $key = 'test-'.uniqid();

        $log = UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => AiGenerationStatus::Pending,
            'request_meta' => ['ingredients' => [['id' => null, 'name' => 'Tomatoes', 'quantity' => 2, 'unit' => 'pcs']]],
            'idempotency_key' => $key,
        ]);

        $charge = $credits->spend($user->id, 1, $log, $key);

        return [$log, $charge, $credits, $user];
    }
}
