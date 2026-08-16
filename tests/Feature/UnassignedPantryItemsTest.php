<?php

namespace Tests\Feature;

use App\Models\PantryItem;
use App\Models\SpaceStorage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Items with no storage space.
 *
 * The scan leaves an item unassigned rather than forcing it somewhere wrong,
 * which only works if unassigned is somewhere you can actually reach — and
 * until this filter existed it was not.
 */
class UnassignedPantryItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_unassigned_flag_returns_only_items_with_no_space(): void
    {
        [$user, $space] = $this->userWithItems();

        $names = collect(
            $this->actingAs($user, 'api')
                ->getJson('/api/pantry-items?unassigned=1')
                ->assertOk()
                ->json('data')
        )->pluck('name');

        $this->assertEqualsCanonicalizing(['Olives', 'Rice'], $names->all());
        $this->assertNotContains('Milk', $names->all(), "an assigned item is not unassigned");

        // The existing filter still works, and still wins when both are absent.
        $this->assertSame(
            ['Milk'],
            collect($this->actingAs($user, 'api')
                ->getJson("/api/pantry-items?space_id={$space->id}")
                ->assertOk()
                ->json('data'))->pluck('name')->all(),
        );
    }

    public function test_without_the_flag_everything_comes_back(): void
    {
        [$user] = $this->userWithItems();

        $this->actingAs($user, 'api')
            ->getJson('/api/pantry-items')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_the_spaces_list_carries_the_unassigned_count(): void
    {
        [$user] = $this->userWithItems();

        $this->actingAs($user, 'api')
            ->postJson('/api/get-storage-spaces', [])
            ->assertOk()
            ->assertJsonPath('unassigned_count', 2);
    }

    /** Another user's loose items must not inflate the count or the list. */
    public function test_a_strangers_unassigned_items_are_not_yours(): void
    {
        [$user] = $this->userWithItems();

        PantryItem::create(['user_id' => User::factory()->create()->id, 'name' => 'Their rice']);

        $this->actingAs($user, 'api')
            ->getJson('/api/pantry-items?unassigned=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($user, 'api')
            ->postJson('/api/get-storage-spaces', [])
            ->assertOk()
            ->assertJsonPath('unassigned_count', 2);
    }

    /** @return array{0: User, 1: SpaceStorage} */
    private function userWithItems(): array
    {
        $user = User::factory()->create();
        $space = SpaceStorage::create(['user_id' => $user->id, 'name' => 'Fridge']);

        PantryItem::create(['user_id' => $user->id, 'space_id' => $space->id, 'name' => 'Milk']);
        PantryItem::create(['user_id' => $user->id, 'space_id' => null, 'name' => 'Olives']);
        PantryItem::create(['user_id' => $user->id, 'space_id' => null, 'name' => 'Rice']);

        return [$user, $space];
    }
}
