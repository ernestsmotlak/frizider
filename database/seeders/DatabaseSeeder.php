<?php

namespace Database\Seeders;

use App\Models\GroceryList;
use App\Models\GroceryListItem;
use App\Models\PantryItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\SpaceStorage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ]);

        $space = SpaceStorage::create([
            'user_id' => $admin->id,
            'name' => 'Main Fridge',
            'description' => 'Main refrigerator',
            'sort_order' => 1,
        ]);

        PantryItem::create([
            'user_id' => $admin->id,
            'space_id' => $space->id,
            'name' => 'Milk',
            'quantity' => 2.0,
            'unit' => 'liters',
            'expiry_date' => now()->addDays(7),
            'purchase_date' => now()->subDays(2),
        ]);

        PantryItem::create([
            'user_id' => $admin->id,
            'space_id' => $space->id,
            'name' => 'Eggs',
            'quantity' => 12.0,
            'unit' => 'pieces',
            'expiry_date' => now()->addDays(14),
            'purchase_date' => now()->subDays(1),
        ]);

        PantryItem::create([
            'user_id' => $admin->id,
            'space_id' => $space->id,
            'name' => 'Bread',
            'quantity' => 1.0,
            'unit' => 'loaf',
            'expiry_date' => now()->addDays(5),
            'purchase_date' => now()->subDays(1),
        ]);

        PantryItem::create([
            'user_id' => $admin->id,
            'space_id' => $space->id,
            'name' => 'Tomatoes',
            'quantity' => 500.0,
            'unit' => 'grams',
            'expiry_date' => now()->addDays(3),
            'purchase_date' => now(),
        ]);

        PantryItem::create([
            'user_id' => $admin->id,
            'space_id' => $space->id,
            'name' => 'Cheese',
            'quantity' => 250.0,
            'unit' => 'grams',
            'expiry_date' => now()->addDays(10),
            'purchase_date' => now()->subDays(2),
        ]);

        $recipe1 = Recipe::create([
            'user_id' => $admin->id,
            'name' => 'Scrambled Eggs',
            'description' => 'Classic scrambled eggs with toast',
            'instructions' => '1. Crack eggs into a bowl and whisk. 2. Heat butter in a pan. 3. Pour eggs and scramble. 4. Serve with toast.',
            'servings' => 2,
            'prep_time' => 5,
            'cook_time' => 10,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe1->id,
            'name' => 'Eggs',
            'quantity' => 4.0,
            'unit' => 'pieces',
            'sort_order' => 1,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe1->id,
            'name' => 'Butter',
            'quantity' => 20.0,
            'unit' => 'grams',
            'sort_order' => 2,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe1->id,
            'name' => 'Bread',
            'quantity' => 2.0,
            'unit' => 'slices',
            'sort_order' => 3,
        ]);

        $recipe2 = Recipe::create([
            'user_id' => $admin->id,
            'name' => 'Tomato Sandwich',
            'description' => 'Simple and fresh tomato sandwich',
            'instructions' => '1. Slice tomatoes. 2. Toast bread. 3. Layer tomatoes and cheese. 4. Season and serve.',
            'servings' => 1,
            'prep_time' => 5,
            'cook_time' => 3,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => 'Tomatoes',
            'quantity' => 200.0,
            'unit' => 'grams',
            'sort_order' => 1,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => 'Bread',
            'quantity' => 2.0,
            'unit' => 'slices',
            'sort_order' => 2,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => 'Cheese',
            'quantity' => 50.0,
            'unit' => 'grams',
            'sort_order' => 3,
        ]);

        $groceryList1 = GroceryList::create([
            'user_id' => $admin->id,
            'name' => 'Weekly Shopping',
            'notes' => 'Items needed for the week',
        ]);

        GroceryListItem::create([
            'grocery_list_id' => $groceryList1->id,
            'name' => 'Chicken',
            'quantity' => 1.0,
            'unit' => 'kg',
            'sort_order' => 1,
            'is_purchased' => false,
        ]);

        GroceryListItem::create([
            'grocery_list_id' => $groceryList1->id,
            'name' => 'Rice',
            'quantity' => 2.0,
            'unit' => 'kg',
            'sort_order' => 2,
            'is_purchased' => false,
        ]);

        GroceryListItem::create([
            'grocery_list_id' => $groceryList1->id,
            'name' => 'Onions',
            'quantity' => 500.0,
            'unit' => 'grams',
            'sort_order' => 3,
            'is_purchased' => true,
        ]);

        $groceryList2 = GroceryList::create([
            'user_id' => $admin->id,
            'name' => 'Breakfast Items',
            'notes' => 'Items for breakfast recipes',
        ]);

        GroceryListItem::create([
            'grocery_list_id' => $groceryList2->id,
            'name' => 'Bacon',
            'quantity' => 300.0,
            'unit' => 'grams',
            'sort_order' => 1,
            'is_purchased' => false,
        ]);

        GroceryListItem::create([
            'grocery_list_id' => $groceryList2->id,
            'name' => 'Orange Juice',
            'quantity' => 1.0,
            'unit' => 'liter',
            'sort_order' => 2,
            'is_purchased' => false,
        ]);
    }
}
