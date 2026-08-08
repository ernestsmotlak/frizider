<?php

namespace Tests\Feature;

use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserAiRecipeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiGenerationHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The bug this endpoint exists to fix: every finished run is acknowledged
     * within minutes, so the pill's endpoint returns nothing and the user's
     * whole history appears to be empty.
     */
    public function test_acknowledged_runs_are_still_in_the_history(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipeFor($user);
        $log = $this->log($user, AiGenerationStatus::Completed, $recipe, acknowledged: true);

        $this->actingAs($user, 'api');

        // The pill has nothing left to say about it...
        $this->getJson('/api/recipe/ai/active-generations')
            ->assertOk()
            ->assertJsonCount(0, 'generations');

        // ...but the history still has it.
        $this->getJson('/api/recipe/ai/generations')
            ->assertOk()
            ->assertJsonPath('generations.0.generation_id', $log->id)
            ->assertJsonPath('generations.0.recipe_name', $recipe->name)
            ->assertJsonPath('totals.all', 1)
            ->assertJsonPath('totals.completed', 1);
    }

    public function test_reading_the_history_does_not_acknowledge_anything(): void
    {
        $user = User::factory()->create();
        $log = $this->log($user, AiGenerationStatus::Failed, acknowledged: false);

        $this->actingAs($user, 'api');
        $this->getJson('/api/recipe/ai/generations')->assertOk();

        // Opening your history must not count as being told, or the pill would
        // empty itself behind your back.
        $this->assertNull($log->refresh()->acknowledged_at);
        $this->getJson('/api/recipe/ai/active-generations')
            ->assertOk()
            ->assertJsonCount(1, 'generations');
    }

    public function test_every_status_is_listed_newest_first(): void
    {
        $user = User::factory()->create();

        $oldest = $this->log($user, AiGenerationStatus::Completed, $this->recipeFor($user));
        $middle = $this->log($user, AiGenerationStatus::Failed);
        $newest = $this->log($user, AiGenerationStatus::Processing);

        $response = $this->actingAs($user, 'api')
            ->getJson('/api/recipe/ai/generations')
            ->assertOk();

        $this->assertSame(
            [$newest->id, $middle->id, $oldest->id],
            array_column($response->json('generations'), 'generation_id'),
        );

        // A run that is still going has no verdict yet, and a failed one never
        // shows the provider's own words.
        $this->assertNull($response->json('generations.0.error'));
        $this->assertSame(UserAiRecipeLog::CLIENT_FAILURE_MESSAGE, $response->json('generations.1.error'));
        $this->assertSame(3, $response->json('totals.all'));
        $this->assertSame(1, $response->json('totals.completed'));
    }

    public function test_another_users_history_is_not_visible(): void
    {
        $mine = User::factory()->create();
        $theirs = User::factory()->create();

        $this->log($mine, AiGenerationStatus::Completed, $this->recipeFor($mine));
        $this->log($theirs, AiGenerationStatus::Completed, $this->recipeFor($theirs));

        $this->actingAs($mine, 'api')
            ->getJson('/api/recipe/ai/generations')
            ->assertOk()
            ->assertJsonCount(1, 'generations')
            ->assertJsonPath('totals.all', 1);
    }

    /**
     * Deleting the recipe does not delete the fact that you generated it.
     */
    public function test_a_deleted_recipe_leaves_a_row_with_nothing_to_link_to(): void
    {
        $user = User::factory()->create();
        $recipe = $this->recipeFor($user);
        $this->log($user, AiGenerationStatus::Completed, $recipe);

        $recipe->delete();

        $this->actingAs($user, 'api')
            ->getJson('/api/recipe/ai/generations')
            ->assertOk()
            ->assertJsonCount(1, 'generations')
            ->assertJsonPath('generations.0.status', AiGenerationStatus::Completed->value)
            ->assertJsonPath('generations.0.recipe_id', null)
            ->assertJsonPath('generations.0.recipe_name', null);
    }

    public function test_the_history_pages(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $ignored) {
            $this->log($user, AiGenerationStatus::Completed, $this->recipeFor($user));
        }

        $this->actingAs($user, 'api');

        $first = $this->getJson('/api/recipe/ai/generations?per_page=2')->assertOk();
        $this->assertCount(2, $first->json('generations'));
        $this->assertTrue($first->json('has_more'));
        // The count is of everything, not of the page.
        $this->assertSame(5, $first->json('totals.all'));

        $last = $this->getJson('/api/recipe/ai/generations?per_page=2&page=3')->assertOk();
        $this->assertCount(1, $last->json('generations'));
        $this->assertFalse($last->json('has_more'));
    }

    public function test_a_guest_gets_nothing(): void
    {
        $this->getJson('/api/recipe/ai/generations')->assertUnauthorized();
    }

    private function recipeFor(User $user): Recipe
    {
        return Recipe::create([
            'user_id' => $user->id,
            'name' => 'Tomato Soup',
            'is_ai_generated' => true,
        ]);
    }

    private function log(
        User             $user,
        AiGenerationStatus $status,
        ?Recipe          $recipe = null,
        bool             $acknowledged = true,
    ): UserAiRecipeLog
    {
        $finished = $status->isTerminal();

        return UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => $status,
            'recipe_id' => $recipe?->id,
            'error_message' => $status === AiGenerationStatus::Failed ? 'Gemini request failed (400): raw' : null,
            'completed_at' => $finished ? now() : null,
            'acknowledged_at' => $finished && $acknowledged ? now() : null,
        ]);
    }
}
