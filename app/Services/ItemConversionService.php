<?php

namespace App\Services;

use App\Models\GroceryListItem;
use App\Models\PantryItem;
use App\Models\RecipeIngredient;
use App\Models\ShoppingItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ItemConversionService
{
    /**
     * Convert source items (shopping_item, grocery_list_item, recipe_ingredient) into new pantry items.
     */
    public function convertToPantryItem(string $sourceType, array $sourceIds, int $userId, int $spaceId, ?string $expiryDate): Collection
    {
        $sources = $this->fetchSources($sourceType, $sourceIds, $userId);

        return DB::transaction(function () use ($sources, $userId, $spaceId, $expiryDate) {
            return $sources->map(function ($source) use ($userId, $spaceId, $expiryDate) {
                return PantryItem::create([
                    'user_id' => $userId,
                    'space_id' => $spaceId,
                    'name' => $source->name,
                    'quantity' => $source->quantity,
                    'unit' => $source->unit,
                    'notes' => $source->notes,
                    'expiry_date' => $expiryDate,
                    'purchase_date' => now()->toDateString(),
                ]);
            })->values();
        });
    }

    /**
     * Convert source items (pantry_item, recipe_ingredient) into new grocery list items.
     */
    public function convertToGroceryListItem(string $sourceType, array $sourceIds, int $userId, int $groceryListId): Collection
    {
        $sources = $this->fetchSources($sourceType, $sourceIds, $userId);

        return DB::transaction(function () use ($sources, $groceryListId) {
            $nextSortOrder = $this->nextSortOrder(GroceryListItem::where('grocery_list_id', $groceryListId));

            return $sources->map(function ($source) use ($groceryListId, &$nextSortOrder) {
                return GroceryListItem::create([
                    'grocery_list_id' => $groceryListId,
                    'name' => $source->name,
                    'quantity' => $source->quantity,
                    'unit' => $source->unit,
                    'notes' => $this->truncateNotes($source->notes, 500),
                    'sort_order' => $nextSortOrder++,
                    'is_purchased' => false,
                    'completed' => false,
                ]);
            })->values();
        });
    }

    /**
     * Convert source items (pantry_item, shopping_item, grocery_list_item) into new recipe ingredients.
     */
    public function convertToRecipeIngredient(string $sourceType, array $sourceIds, int $userId, int $recipeId): Collection
    {
        $sources = $this->fetchSources($sourceType, $sourceIds, $userId);

        return DB::transaction(function () use ($sources, $recipeId) {
            $nextSortOrder = $this->nextSortOrder(RecipeIngredient::where('recipe_id', $recipeId));

            return $sources->map(function ($source) use ($recipeId, &$nextSortOrder) {
                return RecipeIngredient::create([
                    'recipe_id' => $recipeId,
                    'name' => $source->name,
                    'quantity' => $source->quantity,
                    'unit' => $source->unit,
                    'notes' => $this->truncateNotes($source->notes, 500),
                    'sort_order' => $nextSortOrder++,
                    'completed' => false,
                ]);
            })->values();
        });
    }

    /**
     * Fetch and ownership-scope the source rows for the given source type.
     */
    protected function fetchSources(string $sourceType, array $sourceIds, int $userId): Collection
    {
        $sourceIds = array_values(array_unique($sourceIds));

        $sources = match ($sourceType) {
            'pantry_item' => PantryItem::whereIn('id', $sourceIds)
                ->where('user_id', $userId)
                ->get(),
            'recipe_ingredient' => RecipeIngredient::whereIn('id', $sourceIds)
                ->whereHas('recipe', fn ($q) => $q->where('user_id', $userId))
                ->get(),
            'grocery_list_item' => GroceryListItem::whereIn('id', $sourceIds)
                ->whereHas('groceryList', fn ($q) => $q->where('user_id', $userId))
                ->get(),
            'shopping_item' => ShoppingItem::whereIn('id', $sourceIds)
                ->whereHas('shoppingSession', fn ($q) => $q->where('user_id', $userId))
                ->get(),
            default => throw ValidationException::withMessages([
                'source_type' => 'Invalid source type.',
            ]),
        };

        if ($sources->count() !== count($sourceIds)) {
            throw ValidationException::withMessages([
                'source_ids' => 'One or more source items were not found.',
            ]);
        }

        return $sources;
    }

    /**
     * Next sort_order to use (appends after the current max within the given scoped query).
     */
    protected function nextSortOrder($query): int
    {
        $max = $query->max('sort_order');

        return $max === null ? 0 : $max + 1;
    }

    protected function truncateNotes(?string $notes, int $maxLength): ?string
    {
        if ($notes === null) {
            return null;
        }

        return Str::limit($notes, $maxLength, '');
    }
}
