<?php

namespace Tests\Feature;

use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Models\User;
use App\Models\UserAiRecipeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerationAcknowledgementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_finished_run_is_announced_until_it_is_acknowledged(): void
    {
        $user = User::factory()->create();
        $log = $this->finished($user, AiGenerationStatus::Failed);

        $this->actingAs($user, 'api');

        // Reloading the app on its own is not being told.
        $this->assertSame([$log->id], $this->announced());
        $this->assertSame([$log->id], $this->announced());

        $this->postJson("/api/recipe/ai/generations/{$log->id}/acknowledge")
            ->assertOk()
            ->assertJson(['acknowledged' => true]);

        $this->assertSame([], $this->announced());
        $this->assertNotNull($log->refresh()->acknowledged_at);
    }

    public function test_acknowledging_settles_it_for_every_device(): void
    {
        $user = User::factory()->create();
        $log = $this->finished($user, AiGenerationStatus::Completed);

        // "Phone" acknowledges.
        $this->actingAs($user, 'api')
            ->postJson("/api/recipe/ai/generations/{$log->id}/acknowledge")
            ->assertOk();

        // A second client with its own empty storage must also stay quiet —
        // the thing localStorage could never do.
        $this->assertSame([], $this->announced());
    }

    public function test_acknowledging_twice_is_harmless(): void
    {
        $user = User::factory()->create();
        $log = $this->finished($user, AiGenerationStatus::Failed);

        $this->actingAs($user, 'api');
        $this->postJson("/api/recipe/ai/generations/{$log->id}/acknowledge")->assertOk();

        $first = $log->refresh()->acknowledged_at;

        $this->postJson("/api/recipe/ai/generations/{$log->id}/acknowledge")->assertOk();

        $this->assertEquals($first, $log->refresh()->acknowledged_at, 'the first answer stands');
    }

    public function test_a_running_generation_is_always_returned_so_a_refresh_can_reattach(): void
    {
        $user = User::factory()->create();

        $running = UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => AiGenerationStatus::Processing,
            'request_meta' => ['ingredients' => []],
            'idempotency_key' => 'running-'.uniqid(),
        ]);

        $this->actingAs($user, 'api');

        $this->assertSame([$running->id], $this->announced());

        // Acknowledging something still in flight must not hide it.
        $this->postJson("/api/recipe/ai/generations/{$running->id}/acknowledge")
            ->assertOk()
            ->assertJson(['acknowledged' => false]);

        $this->assertSame([$running->id], $this->announced());
    }

    public function test_another_users_generation_cannot_be_acknowledged(): void
    {
        $log = $this->finished(User::factory()->create(), AiGenerationStatus::Completed);

        $this->actingAs(User::factory()->create(), 'api')
            ->postJson("/api/recipe/ai/generations/{$log->id}/acknowledge")
            ->assertNotFound();

        $this->assertNull($log->refresh()->acknowledged_at);
    }

    /** Ids currently being announced to the signed-in user. */
    private function announced(): array
    {
        return collect($this->getJson('/api/recipe/ai/active-generations')->assertOk()->json('generations'))
            ->pluck('generation_id')
            ->all();
    }

    private function finished(User $user, AiGenerationStatus $status): UserAiRecipeLog
    {
        return UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => $status,
            'request_meta' => ['ingredients' => []],
            'error_message' => $status === AiGenerationStatus::Failed ? 'Gemini request failed (400): INVALID_ARGUMENT' : null,
            'idempotency_key' => 'done-'.uniqid(),
            'completed_at' => now()->subMinute(),
        ]);
    }
}
