<?php

namespace Tests\Feature;

use App\Enums\AiCreditTransactionType;
use App\Enums\AiGenerationStatus;
use App\Enums\AiOperation;
use App\Models\AiUserData;
use App\Models\PantryItem;
use App\Models\SpaceStorage;
use App\Models\User;
use App\Models\UserAiRecipeLog;
use App\Services\AiCreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The scan flow, whose whole point is the gap between the model answering and
 * anything becoming real.
 */
class PantryScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // QUEUE_CONNECTION is sync in phpunit.xml, so the upload runs the job
        // inline and one request covers upload through to the stored result.
        config(['services.ai.driver' => 'fake', 'services.ai.fake_delay' => 0]);

        Storage::fake('local');
    }

    public function test_a_photo_becomes_a_list_and_touches_nothing(): void
    {
        [$user] = $this->readyUser();

        $response = $this->scan($user);

        $response->assertStatus(202)
            ->assertJsonPath('action', AiOperation::PantryFromPhoto->value);

        $log = UserAiRecipeLog::findOrFail($response->json('generation_id'));

        $this->assertSame(AiGenerationStatus::Completed, $log->status);
        $this->assertNotEmpty($log->result_json, 'the scan produced a list');
        $this->assertNull($log->confirmed_at);

        // The whole reason this is a review flow.
        $this->assertSame(0, PantryItem::count(), 'nothing reaches the pantry unreviewed');
    }

    public function test_the_photo_is_kept_for_the_review_and_served_behind_auth(): void
    {
        [$user] = $this->readyUser();
        $id = $this->scan($user)->json('generation_id');

        $this->actingAs($user, 'api')
            ->getJson("/api/pantry/ai/generations/{$id}")
            ->assertOk()
            ->assertJsonPath('photo_url', "/api/pantry/ai/generations/{$id}/photo")
            ->assertJsonStructure(['items', 'spaces', 'status', 'confirmed_at']);

        $this->actingAs($user, 'api')->get("/api/pantry/ai/generations/{$id}/photo")->assertOk();

        // Someone else's fridge is nobody's business.
        $this->actingAs(User::factory()->create(), 'api')
            ->get("/api/pantry/ai/generations/{$id}/photo")
            ->assertNotFound();
    }

    public function test_confirming_writes_the_edited_list_not_the_suggestion(): void
    {
        [$user, $space] = $this->readyUser();
        $id = $this->scan($user)->json('generation_id');

        $this->actingAs($user, 'api')
            ->postJson("/api/pantry/ai/generations/{$id}/confirm", [
                'items' => [
                    ['name' => 'Renamed by hand', 'space_id' => $space->id, 'notes' => null],
                    ['name' => 'Left unassigned', 'space_id' => null, 'notes' => 'half empty'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('added', 2);

        $items = PantryItem::where('user_id', $user->id)->orderBy('id')->get();

        $this->assertCount(2, $items, 'exactly what was submitted, not what the model said');
        $this->assertSame('Renamed by hand', $items[0]->name);
        $this->assertSame($space->id, $items[0]->space_id);
        $this->assertNull($items[1]->space_id, 'unassigned survives as unassigned');

        $log = UserAiRecipeLog::findOrFail($id);
        $this->assertNotNull($log->confirmed_at);
        $this->assertFalse(Storage::disk('local')->exists($log->request_meta['photo_path']), 'the photo is released');
    }

    public function test_confirming_twice_adds_one_pantry(): void
    {
        [$user] = $this->readyUser();
        $id = $this->scan($user)->json('generation_id');
        $body = ['items' => [['name' => 'Milk', 'space_id' => null, 'notes' => null]]];

        $this->actingAs($user, 'api')->postJson("/api/pantry/ai/generations/{$id}/confirm", $body)->assertOk();
        $this->actingAs($user, 'api')
            ->postJson("/api/pantry/ai/generations/{$id}/confirm", $body)
            ->assertOk()
            ->assertJsonPath('added', 0);

        $this->assertSame(1, PantryItem::count());
    }

    public function test_another_users_space_is_refused(): void
    {
        [$user] = $this->readyUser();
        $stranger = SpaceStorage::create(['user_id' => User::factory()->create()->id, 'name' => 'Their fridge']);
        $id = $this->scan($user)->json('generation_id');

        $this->actingAs($user, 'api')
            ->postJson("/api/pantry/ai/generations/{$id}/confirm", [
                'items' => [['name' => 'Milk', 'space_id' => $stranger->id, 'notes' => null]],
            ])
            ->assertForbidden();

        $this->assertSame(0, PantryItem::count());
    }

    public function test_discarding_drops_the_suggestion_and_the_photo(): void
    {
        [$user] = $this->readyUser();
        $id = $this->scan($user)->json('generation_id');
        $path = UserAiRecipeLog::findOrFail($id)->request_meta['photo_path'];

        $this->actingAs($user, 'api')->deleteJson("/api/pantry/ai/generations/{$id}")->assertOk();

        $log = UserAiRecipeLog::findOrFail($id);
        $this->assertNull($log->result_json);
        $this->assertNotNull($log->acknowledged_at, 'a discarded scan stops being news');
        $this->assertFalse(Storage::disk('local')->exists($path));
        $this->assertSame(0, PantryItem::count());
    }

    /**
     * The two endpoint families share one table. An id from one must not be
     * readable through the other, or a recipe run could be walked sideways
     * into a shape it was never meant to answer for.
     */
    public function test_a_recipe_generation_is_not_a_scan(): void
    {
        [$user] = $this->readyUser();

        $recipeRun = UserAiRecipeLog::create([
            'user_id' => $user->id,
            'action' => AiOperation::GenerateRecipeFromIngredients->value,
            'status' => AiGenerationStatus::Completed,
        ]);

        $this->actingAs($user, 'api')
            ->getJson("/api/pantry/ai/generations/{$recipeRun->id}")
            ->assertNotFound();
    }

    public function test_another_users_scan_is_invisible(): void
    {
        [$user] = $this->readyUser();
        $id = $this->scan($user)->json('generation_id');

        $this->actingAs(User::factory()->create(), 'api')
            ->getJson("/api/pantry/ai/generations/{$id}")
            ->assertNotFound();
    }

    public function test_the_same_key_twice_charges_once(): void
    {
        [$user] = $this->readyUser();
        $credits = app(AiCreditService::class);

        $first = $this->scan($user, key: 'same-key');
        $second = $this->scan($user, key: 'same-key');

        $this->assertSame($first->json('generation_id'), $second->json('generation_id'));
        $this->assertSame(4, $credits->balance($user->id), 'granted 5, spent 1, once');
    }

    /** @return array{0: User, 1: SpaceStorage} */
    private function readyUser(): array
    {
        $user = User::factory()->create();

        // The grant creates the ai_user_data row; can_use_ai is guarded and
        // defaults to false, so the flag is flipped straight on the table.
        app(AiCreditService::class)->grant($user->id, 5, AiCreditTransactionType::PromoGrant, null, "seed-{$user->id}");
        AiUserData::where('user_id', $user->id)->update(['can_use_ai' => true]);

        $space = SpaceStorage::create([
            'user_id' => $user->id,
            'name' => 'Fridge',
            'description' => 'Dairy, leftovers, opened jars',
        ]);

        return [$user, $space];
    }

    private function scan(User $user, string $key = 'scan-key')
    {
        return $this->actingAs($user, 'api')->post('/api/pantry/ai/from-photo', [
            'photo' => UploadedFile::fake()->image('shelf.jpg', 800, 600),
            'idempotency_key' => $key,
        ]);
    }
}
