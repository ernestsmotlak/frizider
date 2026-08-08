<?php

namespace Tests\Feature;

use App\Ai\AiRequest;
use App\Ai\AiResponse;
use App\Ai\FakeAiClient;
use App\Contracts\AiClient;
use App\Enums\AiCreditTransactionType;
use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Jobs\GenerateAiRecipe;
use App\Models\AiCreditTransaction;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SweepStalledAiGenerationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_run_nobody_finished_is_failed_and_refunded(): void
    {
        [$log, , $credits, $user] = $this->charged(AiGenerationStatus::Processing, ageMinutes: 30);

        $this->assertSame(1, $credits->balance($user->id), 'charged, not yet refunded');

        $this->artisan('ai:sweep-stalled')->assertSuccessful();

        $log->refresh();
        $this->assertSame(AiGenerationStatus::Failed, $log->status);
        $this->assertNotNull($log->completed_at);
        $this->assertStringContainsString('Abandoned', $log->error_message);
        $this->assertSame(2, $credits->balance($user->id), 'the credit comes back');
    }

    public function test_a_run_still_within_its_time_is_left_alone(): void
    {
        [$log, , $credits, $user] = $this->charged(AiGenerationStatus::Processing, ageMinutes: 2);

        $this->artisan('ai:sweep-stalled')->assertSuccessful();

        $this->assertSame(AiGenerationStatus::Processing, $log->refresh()->status);
        $this->assertSame(1, $credits->balance($user->id), 'nothing refunded');
    }

    public function test_sweeping_twice_does_not_refund_twice(): void
    {
        [, , $credits, $user] = $this->charged(AiGenerationStatus::Pending, ageMinutes: 30);

        $this->artisan('ai:sweep-stalled')->assertSuccessful();
        $this->artisan('ai:sweep-stalled')->assertSuccessful();

        $this->assertSame(2, $credits->balance($user->id));
        $this->assertSame(1, AiCreditTransaction::where('type', AiCreditTransactionType::Refund)->count());
    }

    public function test_a_finished_run_is_never_touched(): void
    {
        [$log, , $credits, $user] = $this->charged(AiGenerationStatus::Completed, ageMinutes: 90);

        $this->artisan('ai:sweep-stalled')->assertSuccessful();

        $this->assertSame(AiGenerationStatus::Completed, $log->refresh()->status);
        $this->assertSame(1, $credits->balance($user->id), 'a successful run keeps its charge');
    }

    /**
     * The race the sweep creates: the run it gave up on comes back to life.
     * The credit is already returned, so the recipe must not be kept.
     */
    public function test_a_job_that_finishes_after_being_swept_does_not_persist(): void
    {
        [$log, $charge, $credits, $user] = $this->charged(AiGenerationStatus::Processing, ageMinutes: 30);

        $this->artisan('ai:sweep-stalled')->assertSuccessful();
        $this->assertSame(2, $credits->balance($user->id));

        // The long-lost worker finally answers.
        config(['services.ai.driver' => 'fake', 'services.ai.fake_delay' => 0]);
        Http::fake();

        GenerateAiRecipe::dispatchSync($log->refresh(), $charge);

        $this->assertSame(0, Recipe::where('user_id', $user->id)->count(), 'no recipe from a refunded run');
        $this->assertSame(AiGenerationStatus::Failed, $log->refresh()->status);
        $this->assertSame(2, $credits->balance($user->id), 'balance unchanged by the late arrival');
    }

    /**
     * The narrow race: the sweep lands while the provider request is still in
     * flight, so the job passes its opening check and only learns it has been
     * abandoned at the moment it goes to write.
     */
    public function test_a_sweep_that_lands_mid_request_still_wins(): void
    {
        [$log, $charge, $credits, $user] = $this->charged(AiGenerationStatus::Processing, ageMinutes: 30);

        $this->app->bind(AiClient::class, fn() => new class implements AiClient {
            public function send(AiRequest $request): AiResponse
            {
                Artisan::call('ai:sweep-stalled');

                return (new FakeAiClient)->send($request);
            }
        });

        config(['services.ai.fake_delay' => 0]);

        // Still running when the job starts — the opening check cannot catch this.
        $this->assertSame(AiGenerationStatus::Processing, $log->refresh()->status);

        GenerateAiRecipe::dispatchSync($log, $charge);

        $this->assertSame(0, Recipe::where('user_id', $user->id)->count(), 'no recipe from a refunded run');
        $this->assertSame(AiGenerationStatus::Failed, $log->refresh()->status);
        $this->assertNull($log->recipe_id);
        $this->assertSame(2, $credits->balance($user->id), 'refunded exactly once');
    }

    /**
     * @return array{0: UserAiRecipeLog, 1: AiCreditTransaction, 2: AiCreditService, 3: User}
     */
    private function charged(AiGenerationStatus $status, int $ageMinutes): array
    {
        $user = User::factory()->create();
        $credits = app(AiCreditService::class);
        $credits->grant($user->id, 2, AiCreditTransactionType::PromoGrant);

        $key = 'sweep-'.uniqid();

        $log = UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => $status,
            'request_meta' => ['ingredients' => [['id' => null, 'name' => 'Tomatoes', 'quantity' => 2, 'unit' => 'pcs']]],
            'idempotency_key' => $key,
            'completed_at' => $status->isTerminal() ? now() : null,
        ]);

        $charge = $credits->spend($user->id, 1, $log, $key);

        $log->forceFill(['created_at' => now()->subMinutes($ageMinutes)])->save();

        return [$log->refresh(), $charge, $credits, $user];
    }
}
