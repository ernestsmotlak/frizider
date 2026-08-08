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

    public function test_the_clear_button_settles_only_the_ids_it_was_given(): void
    {
        $user = User::factory()->create();
        $onScreen = $this->finished($user, AiGenerationStatus::Completed);
        $alsoOnScreen = $this->finished($user, AiGenerationStatus::Failed);

        $this->actingAs($user, 'api');

        // Finished after the pill rendered — tapping clear must not bury it.
        $arrivedLate = $this->finished($user, AiGenerationStatus::Completed);

        $this->postJson('/api/recipe/ai/generations/acknowledge', [
            'ids' => [$onScreen->id, $alsoOnScreen->id],
        ])->assertOk();

        $this->assertSame([$arrivedLate->id], $this->announced());
        $this->assertNull($arrivedLate->refresh()->acknowledged_at);
    }

    public function test_the_clear_button_skips_running_jobs_and_other_peoples(): void
    {
        $user = User::factory()->create();
        $mine = $this->finished($user, AiGenerationStatus::Failed);
        $running = $this->running($user);
        $theirs = $this->finished(User::factory()->create(), AiGenerationStatus::Completed);

        $this->actingAs($user, 'api')
            ->postJson('/api/recipe/ai/generations/acknowledge', [
                'ids' => [$mine->id, $running->id, $theirs->id],
            ])
            ->assertOk()
            ->assertJson(['acknowledged' => [$mine->id]]);

        $this->assertNotNull($mine->refresh()->acknowledged_at);
        $this->assertNull($running->refresh()->acknowledged_at, 'still in flight');
        $this->assertNull($theirs->refresh()->acknowledged_at, 'not the caller\'s to dismiss');

        // The running one is still watched; the failure is gone.
        $this->assertSame([$running->id], $this->announced());
    }

    public function test_every_announced_row_says_which_operation_it_was(): void
    {
        $user = User::factory()->create();
        $this->finished($user, AiGenerationStatus::Completed);

        $rows = $this->actingAs($user, 'api')
            ->getJson('/api/recipe/ai/active-generations')
            ->assertOk()
            ->json('generations');

        $this->assertSame(
            AiOperation::GenerateRecipeFromIngredients->value,
            $rows[0]['action'],
            'the client needs this to pick its wording',
        );
    }

    /** Ids currently being announced to the signed-in user. */
    private function announced(): array
    {
        return collect($this->getJson('/api/recipe/ai/active-generations')->assertOk()->json('generations'))
            ->pluck('generation_id')
            ->all();
    }

    private function running(User $user): UserAiRecipeLog
    {
        return UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => AiGenerationStatus::Processing,
            'request_meta' => ['ingredients' => []],
            'idempotency_key' => 'running-'.uniqid(),
        ]);
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
