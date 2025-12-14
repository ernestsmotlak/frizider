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

        $additionalSpaces = [
            ['name' => 'Freezer', 'description' => 'Main freezer compartment', 'sort_order' => 2],
            ['name' => 'Pantry Shelf 1', 'description' => 'Top pantry shelf', 'sort_order' => 3],
            ['name' => 'Pantry Shelf 2', 'description' => 'Middle pantry shelf', 'sort_order' => 4],
            ['name' => 'Pantry Shelf 3', 'description' => 'Bottom pantry shelf', 'sort_order' => 5],
            ['name' => 'Spice Rack', 'description' => 'Kitchen spice rack', 'sort_order' => 6],
            ['name' => 'Wine Fridge', 'description' => 'Wine storage refrigerator', 'sort_order' => 7],
            ['name' => 'Garage Fridge', 'description' => 'Extra refrigerator in garage', 'sort_order' => 8],
            ['name' => 'Basement Storage', 'description' => 'Basement food storage', 'sort_order' => 9],
            ['name' => 'Counter Top', 'description' => 'Kitchen counter storage', 'sort_order' => 10],
            ['name' => 'Cupboard 1', 'description' => 'Kitchen cupboard', 'sort_order' => 11],
            ['name' => 'Cupboard 2', 'description' => 'Kitchen cupboard', 'sort_order' => 12],
            ['name' => 'Cupboard 3', 'description' => 'Kitchen cupboard', 'sort_order' => 13],
            ['name' => 'Drawer 1', 'description' => 'Kitchen drawer', 'sort_order' => 14],
            ['name' => 'Drawer 2', 'description' => 'Kitchen drawer', 'sort_order' => 15],
            ['name' => 'Bread Box', 'description' => 'Bread storage container', 'sort_order' => 16],
            ['name' => 'Fruit Bowl', 'description' => 'Counter fruit bowl', 'sort_order' => 17],
            ['name' => 'Vegetable Drawer', 'description' => 'Fridge vegetable drawer', 'sort_order' => 18],
            ['name' => 'Meat Drawer', 'description' => 'Fridge meat drawer', 'sort_order' => 19],
            ['name' => 'Dairy Compartment', 'description' => 'Fridge dairy section', 'sort_order' => 20],
            ['name' => 'Door Shelf 1', 'description' => 'Fridge door top shelf', 'sort_order' => 21],
            ['name' => 'Door Shelf 2', 'description' => 'Fridge door middle shelf', 'sort_order' => 22],
            ['name' => 'Door Shelf 3', 'description' => 'Fridge door bottom shelf', 'sort_order' => 23],
            ['name' => 'Deep Freezer', 'description' => 'Standalone deep freezer', 'sort_order' => 24],
            ['name' => 'Root Cellar', 'description' => 'Root vegetable storage', 'sort_order' => 25],
            ['name' => 'Canning Shelf', 'description' => 'Canned goods storage', 'sort_order' => 26],
            ['name' => 'Baking Supplies', 'description' => 'Baking ingredients storage', 'sort_order' => 27],
            ['name' => 'Snack Cabinet', 'description' => 'Snack food storage', 'sort_order' => 28],
            ['name' => 'Beverage Fridge', 'description' => 'Beverage refrigerator', 'sort_order' => 29],
            ['name' => 'Outdoor Fridge', 'description' => 'Outdoor kitchen fridge', 'sort_order' => 30],
            ['name' => 'Guest Fridge', 'description' => 'Guest room refrigerator', 'sort_order' => 31],
            ['name' => 'Office Fridge', 'description' => 'Office mini fridge', 'sort_order' => 32],
            ['name' => 'Garage Freezer', 'description' => 'Garage freezer unit', 'sort_order' => 33],
        ];

        $createdSpaces = [];
        foreach ($additionalSpaces as $spaceData) {
            $createdSpaces[] = SpaceStorage::create([
                'user_id' => $admin->id,
                'name' => $spaceData['name'],
                'description' => $spaceData['description'],
                'sort_order' => $spaceData['sort_order'],
            ]);
        }

        $allSpaces = array_merge([$space], $createdSpaces);

        $additionalPantryItems = [
            ['name' => 'Yogurt', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Butter', 'quantity' => 250.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 30, 'purchase_days' => 5],
            ['name' => 'Sour Cream', 'quantity' => 300.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 10, 'purchase_days' => 3],
            ['name' => 'Cream Cheese', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 14, 'purchase_days' => 2],
            ['name' => 'Chicken Breast', 'quantity' => 600.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 3, 'purchase_days' => 1],
            ['name' => 'Ground Beef', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 2, 'purchase_days' => 0],
            ['name' => 'Salmon Fillet', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 2, 'purchase_days' => 0],
            ['name' => 'Pork Chops', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 3, 'purchase_days' => 1],
            ['name' => 'Carrots', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 14, 'purchase_days' => 3],
            ['name' => 'Potatoes', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 0, 'expiry_days' => 30, 'purchase_days' => 5],
            ['name' => 'Onions', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 0, 'expiry_days' => 30, 'purchase_days' => 7],
            ['name' => 'Garlic', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 60, 'purchase_days' => 10],
            ['name' => 'Bell Peppers', 'quantity' => 4.0, 'unit' => 'pieces', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Broccoli', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 5, 'purchase_days' => 1],
            ['name' => 'Spinach', 'quantity' => 300.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 4, 'purchase_days' => 1],
            ['name' => 'Lettuce', 'quantity' => 1.0, 'unit' => 'head', 'space_index' => 0, 'expiry_days' => 5, 'purchase_days' => 2],
            ['name' => 'Cucumber', 'quantity' => 3.0, 'unit' => 'pieces', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Mushrooms', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 0, 'expiry_days' => 5, 'purchase_days' => 1],
            ['name' => 'Zucchini', 'quantity' => 4.0, 'unit' => 'pieces', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Cauliflower', 'quantity' => 1.0, 'unit' => 'piece', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Orange Juice', 'quantity' => 1.0, 'unit' => 'liter', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 2],
            ['name' => 'Apple Juice', 'quantity' => 1.0, 'unit' => 'liter', 'space_index' => 0, 'expiry_days' => 7, 'purchase_days' => 3],
            ['name' => 'Cranberry Juice', 'quantity' => 1.0, 'unit' => 'liter', 'space_index' => 0, 'expiry_days' => 14, 'purchase_days' => 5],
            ['name' => 'Frozen Peas', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 1, 'expiry_days' => 365, 'purchase_days' => 30],
            ['name' => 'Frozen Corn', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 1, 'expiry_days' => 365, 'purchase_days' => 45],
            ['name' => 'Frozen Broccoli', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 1, 'expiry_days' => 365, 'purchase_days' => 60],
            ['name' => 'Ice Cream', 'quantity' => 1.0, 'unit' => 'liter', 'space_index' => 1, 'expiry_days' => 90, 'purchase_days' => 10],
            ['name' => 'Frozen Pizza', 'quantity' => 2.0, 'unit' => 'pieces', 'space_index' => 1, 'expiry_days' => 180, 'purchase_days' => 20],
            ['name' => 'Frozen Chicken', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 1, 'expiry_days' => 180, 'purchase_days' => 15],
            ['name' => 'Frozen Fish', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 1, 'expiry_days' => 180, 'purchase_days' => 25],
            ['name' => 'Pasta', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 30],
            ['name' => 'Rice', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 45],
            ['name' => 'Flour', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 2, 'expiry_days' => 365, 'purchase_days' => 60],
            ['name' => 'Sugar', 'quantity' => 1.0, 'unit' => 'kg', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 90],
            ['name' => 'Olive Oil', 'quantity' => 500.0, 'unit' => 'ml', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 120],
            ['name' => 'Vegetable Oil', 'quantity' => 1.0, 'unit' => 'liter', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 100],
            ['name' => 'Vinegar', 'quantity' => 500.0, 'unit' => 'ml', 'space_index' => 2, 'expiry_days' => 1095, 'purchase_days' => 150],
            ['name' => 'Soy Sauce', 'quantity' => 250.0, 'unit' => 'ml', 'space_index' => 2, 'expiry_days' => 1095, 'purchase_days' => 180],
            ['name' => 'Salt', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 1095, 'purchase_days' => 200],
            ['name' => 'Black Pepper', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 1095, 'purchase_days' => 150],
            ['name' => 'Paprika', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 120],
            ['name' => 'Cumin', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 100],
            ['name' => 'Coriander', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 90],
            ['name' => 'Turmeric', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 80],
            ['name' => 'Cinnamon', 'quantity' => 100.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 110],
            ['name' => 'Oregano', 'quantity' => 50.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 95],
            ['name' => 'Basil', 'quantity' => 50.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 85],
            ['name' => 'Thyme', 'quantity' => 50.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 75],
            ['name' => 'Rosemary', 'quantity' => 50.0, 'unit' => 'grams', 'space_index' => 5, 'expiry_days' => 730, 'purchase_days' => 70],
            ['name' => 'Canned Tomatoes', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 25, 'expiry_days' => 730, 'purchase_days' => 60],
            ['name' => 'Canned Beans', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 25, 'expiry_days' => 730, 'purchase_days' => 50],
            ['name' => 'Canned Corn', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 25, 'expiry_days' => 730, 'purchase_days' => 40],
            ['name' => 'Canned Tuna', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 25, 'expiry_days' => 730, 'purchase_days' => 30],
            ['name' => 'Canned Soup', 'quantity' => 400.0, 'unit' => 'grams', 'space_index' => 25, 'expiry_days' => 730, 'purchase_days' => 35],
            ['name' => 'Honey', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 1825, 'purchase_days' => 200],
            ['name' => 'Maple Syrup', 'quantity' => 250.0, 'unit' => 'ml', 'space_index' => 2, 'expiry_days' => 1095, 'purchase_days' => 150],
            ['name' => 'Jam', 'quantity' => 300.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 365, 'purchase_days' => 60],
            ['name' => 'Peanut Butter', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 365, 'purchase_days' => 90],
            ['name' => 'Almond Butter', 'quantity' => 300.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 365, 'purchase_days' => 80],
            ['name' => 'Coffee', 'quantity' => 500.0, 'unit' => 'grams', 'space_index' => 2, 'expiry_days' => 365, 'purchase_days' => 30],
            ['name' => 'Tea Bags', 'quantity' => 100.0, 'unit' => 'pieces', 'space_index' => 2, 'expiry_days' => 730, 'purchase_days' => 120],
            ['name' => 'Cocoa Powder', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 26, 'expiry_days' => 730, 'purchase_days' => 100],
            ['name' => 'Baking Powder', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 26, 'expiry_days' => 365, 'purchase_days' => 60],
            ['name' => 'Baking Soda', 'quantity' => 200.0, 'unit' => 'grams', 'space_index' => 26, 'expiry_days' => 1095, 'purchase_days' => 180],
            ['name' => 'Vanilla Extract', 'quantity' => 100.0, 'unit' => 'ml', 'space_index' => 26, 'expiry_days' => 1825, 'purchase_days' => 200],
            ['name' => 'Chocolate Chips', 'quantity' => 300.0, 'unit' => 'grams', 'space_index' => 26, 'expiry_days' => 365, 'purchase_days' => 90],
        ];

        foreach ($additionalPantryItems as $item) {
            $selectedSpace = $allSpaces[$item['space_index']];
            PantryItem::create([
                'user_id' => $admin->id,
                'space_id' => $selectedSpace->id,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'expiry_date' => now()->addDays($item['expiry_days']),
                'purchase_date' => now()->subDays($item['purchase_days']),
            ]);
        }

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

        $additionalRecipes = [
            [
                'name' => 'Chicken Stir Fry',
                'description' => 'Quick and healthy chicken stir fry with vegetables',
                'instructions' => '1. Cut chicken into strips. 2. Heat oil in wok. 3. Cook chicken until done. 4. Add vegetables and stir fry. 5. Season and serve.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Bell Peppers', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Broccoli', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Soy Sauce', 'quantity' => 50.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Pasta Carbonara',
                'description' => 'Classic Italian pasta with bacon and eggs',
                'instructions' => '1. Cook pasta. 2. Fry bacon. 3. Mix eggs and cheese. 4. Combine with hot pasta. 5. Serve immediately.',
                'servings' => 4,
                'prep_time' => 10,
                'cook_time' => 20,
                'ingredients' => [
                    ['name' => 'Pasta', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Bacon', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Eggs', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 3],
                    ['name' => 'Parmesan Cheese', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Beef Tacos',
                'description' => 'Delicious Mexican beef tacos',
                'instructions' => '1. Cook ground beef with spices. 2. Warm tortillas. 3. Fill with beef. 4. Add toppings. 5. Serve.',
                'servings' => 6,
                'prep_time' => 15,
                'cook_time' => 20,
                'ingredients' => [
                    ['name' => 'Ground Beef', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Tortillas', 'quantity' => 12.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Lettuce', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Tomatoes', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 4],
                    ['name' => 'Cheese', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Vegetable Curry',
                'description' => 'Spicy vegetable curry with coconut milk',
                'instructions' => '1. Sauté vegetables. 2. Add curry paste. 3. Pour coconut milk. 4. Simmer until tender. 5. Serve with rice.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 30,
                'ingredients' => [
                    ['name' => 'Potatoes', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Carrots', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Coconut Milk', 'quantity' => 400.0, 'unit' => 'ml', 'sort_order' => 3],
                    ['name' => 'Curry Paste', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Grilled Salmon',
                'description' => 'Simple grilled salmon with herbs',
                'instructions' => '1. Season salmon. 2. Heat grill. 3. Grill salmon 4-5 minutes per side. 4. Serve with lemon.',
                'servings' => 2,
                'prep_time' => 5,
                'cook_time' => 10,
                'ingredients' => [
                    ['name' => 'Salmon', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Lemon', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 2],
                    ['name' => 'Olive Oil', 'quantity' => 30.0, 'unit' => 'ml', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Classic Caesar salad with homemade dressing',
                'instructions' => '1. Make dressing. 2. Toss lettuce with dressing. 3. Add croutons and parmesan. 4. Serve.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 0,
                'ingredients' => [
                    ['name' => 'Romaine Lettuce', 'quantity' => 1.0, 'unit' => 'head', 'sort_order' => 1],
                    ['name' => 'Parmesan Cheese', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Croutons', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Caesar Dressing', 'quantity' => 150.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken Noodle Soup',
                'description' => 'Comforting chicken noodle soup',
                'instructions' => '1. Cook chicken. 2. Add vegetables and broth. 3. Simmer. 4. Add noodles. 5. Cook until done.',
                'servings' => 6,
                'prep_time' => 15,
                'cook_time' => 45,
                'ingredients' => [
                    ['name' => 'Chicken', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Noodles', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Carrots', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Celery', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 4],
                    ['name' => 'Chicken Broth', 'quantity' => 1.5, 'unit' => 'liters', 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Beef Stew',
                'description' => 'Hearty beef stew with vegetables',
                'instructions' => '1. Brown beef. 2. Add vegetables and broth. 3. Simmer for 2 hours. 4. Season and serve.',
                'servings' => 6,
                'prep_time' => 20,
                'cook_time' => 120,
                'ingredients' => [
                    ['name' => 'Beef', 'quantity' => 800.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Potatoes', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Carrots', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Onions', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Margherita Pizza',
                'description' => 'Classic Italian pizza with tomato and mozzarella',
                'instructions' => '1. Make dough. 2. Roll out. 3. Add sauce and cheese. 4. Bake at 220°C for 12 minutes.',
                'servings' => 4,
                'prep_time' => 30,
                'cook_time' => 12,
                'ingredients' => [
                    ['name' => 'Pizza Dough', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Tomato Sauce', 'quantity' => 200.0, 'unit' => 'ml', 'sort_order' => 2],
                    ['name' => 'Mozzarella', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Basil', 'quantity' => 20.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chocolate Chip Cookies',
                'description' => 'Classic homemade chocolate chip cookies',
                'instructions' => '1. Mix dry ingredients. 2. Cream butter and sugar. 3. Combine. 4. Add chocolate chips. 5. Bake at 180°C for 10 minutes.',
                'servings' => 24,
                'prep_time' => 15,
                'cook_time' => 10,
                'ingredients' => [
                    ['name' => 'Flour', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Butter', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Sugar', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Chocolate Chips', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Greek Salad',
                'description' => 'Fresh Greek salad with feta cheese',
                'instructions' => '1. Chop vegetables. 2. Mix with olives and feta. 3. Drizzle with olive oil and vinegar. 4. Serve.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 0,
                'ingredients' => [
                    ['name' => 'Cucumber', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 1],
                    ['name' => 'Tomatoes', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Feta Cheese', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Olives', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Beef Burgers',
                'description' => 'Juicy homemade beef burgers',
                'instructions' => '1. Mix ground beef with seasonings. 2. Form patties. 3. Grill or pan-fry. 4. Serve on buns with toppings.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Ground Beef', 'quantity' => 600.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Burger Buns', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Lettuce', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Tomatoes', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Shrimp Scampi',
                'description' => 'Garlic butter shrimp pasta',
                'instructions' => '1. Cook pasta. 2. Sauté shrimp with garlic. 3. Add butter and lemon. 4. Toss with pasta. 5. Serve.',
                'servings' => 4,
                'prep_time' => 10,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Shrimp', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Pasta', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Garlic', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Butter', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Vegetable Lasagna',
                'description' => 'Layered vegetable lasagna',
                'instructions' => '1. Cook vegetables. 2. Layer with pasta and cheese. 3. Bake at 180°C for 45 minutes. 4. Rest before serving.',
                'servings' => 8,
                'prep_time' => 30,
                'cook_time' => 45,
                'ingredients' => [
                    ['name' => 'Lasagna Noodles', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Ricotta Cheese', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Mozzarella', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Spinach', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken Curry',
                'description' => 'Spicy Indian chicken curry',
                'instructions' => '1. Marinate chicken. 2. Cook with onions and spices. 3. Add tomatoes and coconut milk. 4. Simmer until done.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 40,
                'ingredients' => [
                    ['name' => 'Chicken', 'quantity' => 800.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Onions', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Curry Powder', 'quantity' => 30.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Coconut Milk', 'quantity' => 400.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'French Toast',
                'description' => 'Sweet breakfast French toast',
                'instructions' => '1. Mix eggs and milk. 2. Dip bread slices. 3. Cook in buttered pan. 4. Serve with syrup.',
                'servings' => 4,
                'prep_time' => 10,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Bread', 'quantity' => 8.0, 'unit' => 'slices', 'sort_order' => 1],
                    ['name' => 'Eggs', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Milk', 'quantity' => 200.0, 'unit' => 'ml', 'sort_order' => 3],
                    ['name' => 'Maple Syrup', 'quantity' => 100.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Fish Tacos',
                'description' => 'Crispy fish tacos with slaw',
                'instructions' => '1. Season and cook fish. 2. Make slaw. 3. Warm tortillas. 4. Assemble tacos. 5. Serve.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'White Fish', 'quantity' => 600.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Tortillas', 'quantity' => 8.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Cabbage', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Lime', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Mushroom Risotto',
                'description' => 'Creamy Italian mushroom risotto',
                'instructions' => '1. Sauté mushrooms. 2. Toast rice. 3. Add broth gradually. 4. Stir until creamy. 5. Add cheese and serve.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 30,
                'ingredients' => [
                    ['name' => 'Arborio Rice', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Mushrooms', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Parmesan Cheese', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'White Wine', 'quantity' => 150.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken Parmesan',
                'description' => 'Breaded chicken with marinara and cheese',
                'instructions' => '1. Bread chicken. 2. Pan-fry until golden. 3. Top with sauce and cheese. 4. Bake until cheese melts.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 30,
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'quantity' => 600.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Breadcrumbs', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Marinara Sauce', 'quantity' => 400.0, 'unit' => 'ml', 'sort_order' => 3],
                    ['name' => 'Mozzarella', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Quinoa Salad',
                'description' => 'Healthy quinoa salad with vegetables',
                'instructions' => '1. Cook quinoa. 2. Let cool. 3. Mix with vegetables. 4. Add dressing. 5. Serve chilled.',
                'servings' => 6,
                'prep_time' => 20,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Quinoa', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Cucumber', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Tomatoes', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Feta Cheese', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Beef and Broccoli',
                'description' => 'Chinese-style beef and broccoli stir fry',
                'instructions' => '1. Marinate beef. 2. Stir fry beef. 3. Add broccoli. 4. Add sauce. 5. Serve over rice.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Beef Strips', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Broccoli', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Soy Sauce', 'quantity' => 50.0, 'unit' => 'ml', 'sort_order' => 3],
                    ['name' => 'Ginger', 'quantity' => 20.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chocolate Brownies',
                'description' => 'Rich and fudgy chocolate brownies',
                'instructions' => '1. Melt chocolate and butter. 2. Mix with sugar and eggs. 3. Add flour. 4. Bake at 180°C for 25 minutes.',
                'servings' => 16,
                'prep_time' => 15,
                'cook_time' => 25,
                'ingredients' => [
                    ['name' => 'Dark Chocolate', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Butter', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Sugar', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Flour', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Pad Thai',
                'description' => 'Classic Thai stir-fried noodles',
                'instructions' => '1. Soak noodles. 2. Cook protein. 3. Stir fry noodles with sauce. 4. Add vegetables. 5. Serve with lime.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Rice Noodles', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Shrimp', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Bean Sprouts', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Pad Thai Sauce', 'quantity' => 100.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken Fajitas',
                'description' => 'Spicy chicken fajitas with peppers',
                'instructions' => '1. Marinate chicken. 2. Cook chicken and peppers. 3. Warm tortillas. 4. Serve with toppings.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 20,
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'quantity' => 600.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Bell Peppers', 'quantity' => 3.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Tortillas', 'quantity' => 8.0, 'unit' => 'pieces', 'sort_order' => 3],
                    ['name' => 'Sour Cream', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Lentil Soup',
                'description' => 'Hearty and healthy lentil soup',
                'instructions' => '1. Sauté vegetables. 2. Add lentils and broth. 3. Simmer for 30 minutes. 4. Season and serve.',
                'servings' => 6,
                'prep_time' => 15,
                'cook_time' => 35,
                'ingredients' => [
                    ['name' => 'Lentils', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Carrots', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Onions', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Vegetable Broth', 'quantity' => 1.5, 'unit' => 'liters', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'BBQ Ribs',
                'description' => 'Slow-cooked BBQ ribs',
                'instructions' => '1. Season ribs. 2. Slow cook for 3 hours. 3. Brush with BBQ sauce. 4. Grill to finish.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 180,
                'ingredients' => [
                    ['name' => 'Pork Ribs', 'quantity' => 1.5, 'unit' => 'kg', 'sort_order' => 1],
                    ['name' => 'BBQ Sauce', 'quantity' => 300.0, 'unit' => 'ml', 'sort_order' => 2],
                    ['name' => 'Brown Sugar', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Caprese Salad',
                'description' => 'Fresh Italian salad with mozzarella',
                'instructions' => '1. Slice tomatoes and mozzarella. 2. Arrange on plate. 3. Add basil. 4. Drizzle with olive oil.',
                'servings' => 4,
                'prep_time' => 10,
                'cook_time' => 0,
                'ingredients' => [
                    ['name' => 'Tomatoes', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Mozzarella', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Basil', 'quantity' => 30.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Olive Oil', 'quantity' => 50.0, 'unit' => 'ml', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Chicken Quesadillas',
                'description' => 'Cheesy chicken quesadillas',
                'instructions' => '1. Cook chicken. 2. Fill tortillas with chicken and cheese. 3. Cook until golden. 4. Cut and serve.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 15,
                'ingredients' => [
                    ['name' => 'Chicken', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Tortillas', 'quantity' => 8.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Cheese', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Beef Stroganoff',
                'description' => 'Creamy Russian beef stroganoff',
                'instructions' => '1. Cook beef strips. 2. Add mushrooms and onions. 3. Add sour cream. 4. Serve over noodles.',
                'servings' => 4,
                'prep_time' => 15,
                'cook_time' => 30,
                'ingredients' => [
                    ['name' => 'Beef Strips', 'quantity' => 600.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Mushrooms', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Sour Cream', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Egg Noodles', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Apple Pie',
                'description' => 'Classic homemade apple pie',
                'instructions' => '1. Make pie crust. 2. Fill with spiced apples. 3. Top with crust. 4. Bake at 200°C for 45 minutes.',
                'servings' => 8,
                'prep_time' => 30,
                'cook_time' => 45,
                'ingredients' => [
                    ['name' => 'Apples', 'quantity' => 1.0, 'unit' => 'kg', 'sort_order' => 1],
                    ['name' => 'Pie Crust', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 2],
                    ['name' => 'Sugar', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Cinnamon', 'quantity' => 10.0, 'unit' => 'grams', 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Teriyaki Chicken',
                'description' => 'Sweet and savory teriyaki chicken',
                'instructions' => '1. Marinate chicken. 2. Cook until done. 3. Add teriyaki sauce. 4. Serve with rice.',
                'servings' => 4,
                'prep_time' => 20,
                'cook_time' => 20,
                'ingredients' => [
                    ['name' => 'Chicken Thighs', 'quantity' => 800.0, 'unit' => 'grams', 'sort_order' => 1],
                    ['name' => 'Teriyaki Sauce', 'quantity' => 200.0, 'unit' => 'ml', 'sort_order' => 2],
                    ['name' => 'Rice', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Spinach and Feta Quiche',
                'description' => 'Savory quiche with spinach and feta',
                'instructions' => '1. Make pastry. 2. Sauté spinach. 3. Mix with eggs and feta. 4. Bake at 180°C for 35 minutes.',
                'servings' => 6,
                'prep_time' => 25,
                'cook_time' => 35,
                'ingredients' => [
                    ['name' => 'Pie Crust', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 1],
                    ['name' => 'Spinach', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 2],
                    ['name' => 'Feta Cheese', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 3],
                    ['name' => 'Eggs', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 4],
                ],
            ],
        ];

        foreach ($additionalRecipes as $recipeData) {
            $recipe = Recipe::create([
                'user_id' => $admin->id,
                'name' => $recipeData['name'],
                'description' => $recipeData['description'],
                'instructions' => $recipeData['instructions'],
                'servings' => $recipeData['servings'],
                'prep_time' => $recipeData['prep_time'],
                'cook_time' => $recipeData['cook_time'],
            ]);

            foreach ($recipeData['ingredients'] as $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => $ingredient['name'],
                    'quantity' => $ingredient['quantity'],
                    'unit' => $ingredient['unit'],
                    'sort_order' => $ingredient['sort_order'],
                ]);
            }
        }

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

        $groceryList1Items = [
            ['name' => 'Potatoes', 'quantity' => 2.0, 'unit' => 'kg', 'sort_order' => 4, 'is_purchased' => false],
            ['name' => 'Carrots', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 5, 'is_purchased' => false],
            ['name' => 'Bell Peppers', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 6, 'is_purchased' => false],
            ['name' => 'Garlic', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 7, 'is_purchased' => false],
            ['name' => 'Ginger', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 8, 'is_purchased' => false],
            ['name' => 'Spinach', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 9, 'is_purchased' => false],
            ['name' => 'Broccoli', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 10, 'is_purchased' => false],
            ['name' => 'Cauliflower', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 11, 'is_purchased' => false],
            ['name' => 'Zucchini', 'quantity' => 3.0, 'unit' => 'pieces', 'sort_order' => 12, 'is_purchased' => false],
            ['name' => 'Mushrooms', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 13, 'is_purchased' => false],
            ['name' => 'Cucumber', 'quantity' => 2.0, 'unit' => 'pieces', 'sort_order' => 14, 'is_purchased' => false],
            ['name' => 'Lettuce', 'quantity' => 1.0, 'unit' => 'head', 'sort_order' => 15, 'is_purchased' => false],
            ['name' => 'Beef', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 16, 'is_purchased' => false],
            ['name' => 'Pork', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 17, 'is_purchased' => false],
            ['name' => 'Salmon', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 18, 'is_purchased' => false],
            ['name' => 'Pasta', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 19, 'is_purchased' => false],
            ['name' => 'Olive Oil', 'quantity' => 500.0, 'unit' => 'ml', 'sort_order' => 20, 'is_purchased' => false],
            ['name' => 'Vinegar', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 21, 'is_purchased' => false],
            ['name' => 'Soy Sauce', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 22, 'is_purchased' => false],
            ['name' => 'Salt', 'quantity' => 1.0, 'unit' => 'pack', 'sort_order' => 23, 'is_purchased' => false],
            ['name' => 'Black Pepper', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 24, 'is_purchased' => false],
            ['name' => 'Paprika', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 25, 'is_purchased' => false],
            ['name' => 'Cumin', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 26, 'is_purchased' => false],
            ['name' => 'Coriander', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 27, 'is_purchased' => false],
            ['name' => 'Turmeric', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 28, 'is_purchased' => false],
            ['name' => 'Cinnamon', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 29, 'is_purchased' => false],
            ['name' => 'Flour', 'quantity' => 1.0, 'unit' => 'kg', 'sort_order' => 30, 'is_purchased' => false],
            ['name' => 'Sugar', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 31, 'is_purchased' => false],
            ['name' => 'Honey', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 32, 'is_purchased' => false],
            ['name' => 'Butter', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 33, 'is_purchased' => false],
            ['name' => 'Yogurt', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 34, 'is_purchased' => false],
            ['name' => 'Sour Cream', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 35, 'is_purchased' => false],
            ['name' => 'Canned Tomatoes', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 36, 'is_purchased' => false],
            ['name' => 'Chicken Broth', 'quantity' => 1.0, 'unit' => 'liter', 'sort_order' => 37, 'is_purchased' => false],
            ['name' => 'Beef Broth', 'quantity' => 1.0, 'unit' => 'liter', 'sort_order' => 38, 'is_purchased' => false],
            ['name' => 'Vegetable Broth', 'quantity' => 1.0, 'unit' => 'liter', 'sort_order' => 39, 'is_purchased' => false],
            ['name' => 'Coconut Milk', 'quantity' => 400.0, 'unit' => 'ml', 'sort_order' => 40, 'is_purchased' => false],
            ['name' => 'Lentils', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 41, 'is_purchased' => false],
            ['name' => 'Chickpeas', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 42, 'is_purchased' => false],
            ['name' => 'Black Beans', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 43, 'is_purchased' => false],
            ['name' => 'Kidney Beans', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 44, 'is_purchased' => false],
            ['name' => 'Quinoa', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 45, 'is_purchased' => false],
            ['name' => 'Barley', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 46, 'is_purchased' => false],
            ['name' => 'Bulgur', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 47, 'is_purchased' => false],
            ['name' => 'Couscous', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 48, 'is_purchased' => false],
            ['name' => 'Tofu', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 49, 'is_purchased' => false],
            ['name' => 'Tempeh', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 50, 'is_purchased' => false],
            ['name' => 'Avocado', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 51, 'is_purchased' => false],
            ['name' => 'Lemons', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 52, 'is_purchased' => false],
            ['name' => 'Limes', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 53, 'is_purchased' => false],
            ['name' => 'Oranges', 'quantity' => 8.0, 'unit' => 'pieces', 'sort_order' => 54, 'is_purchased' => false],
            ['name' => 'Apples', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 55, 'is_purchased' => false],
            ['name' => 'Pears', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 56, 'is_purchased' => false],
            ['name' => 'Grapes', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 57, 'is_purchased' => false],
            ['name' => 'Mangoes', 'quantity' => 3.0, 'unit' => 'pieces', 'sort_order' => 58, 'is_purchased' => false],
            ['name' => 'Pineapple', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 59, 'is_purchased' => false],
            ['name' => 'Watermelon', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 60, 'is_purchased' => false],
            ['name' => 'Cantaloupe', 'quantity' => 1.0, 'unit' => 'piece', 'sort_order' => 61, 'is_purchased' => false],
            ['name' => 'Cilantro', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 62, 'is_purchased' => false],
            ['name' => 'Parsley', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 63, 'is_purchased' => false],
            ['name' => 'Basil', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 64, 'is_purchased' => false],
            ['name' => 'Thyme', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 65, 'is_purchased' => false],
            ['name' => 'Rosemary', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 66, 'is_purchased' => false],
            ['name' => 'Oregano', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 67, 'is_purchased' => false],
            ['name' => 'Dill', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 68, 'is_purchased' => false],
            ['name' => 'Mint', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 69, 'is_purchased' => false],
            ['name' => 'Green Onions', 'quantity' => 2.0, 'unit' => 'bunches', 'sort_order' => 70, 'is_purchased' => false],
            ['name' => 'Celery', 'quantity' => 1.0, 'unit' => 'bunch', 'sort_order' => 71, 'is_purchased' => false],
            ['name' => 'Radishes', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 72, 'is_purchased' => false],
            ['name' => 'Beets', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 73, 'is_purchased' => false],
            ['name' => 'Sweet Potatoes', 'quantity' => 1.0, 'unit' => 'kg', 'sort_order' => 74, 'is_purchased' => false],
            ['name' => 'Corn', 'quantity' => 4.0, 'unit' => 'ears', 'sort_order' => 75, 'is_purchased' => false],
        ];

        foreach ($groceryList1Items as $item) {
            GroceryListItem::create([
                'grocery_list_id' => $groceryList1->id,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'sort_order' => $item['sort_order'],
                'is_purchased' => $item['is_purchased'],
            ]);
        }

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

        $groceryList2Items = [
            ['name' => 'Pancake Mix', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 3, 'is_purchased' => false],
            ['name' => 'Maple Syrup', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 4, 'is_purchased' => false],
            ['name' => 'Waffles', 'quantity' => 8.0, 'unit' => 'pieces', 'sort_order' => 5, 'is_purchased' => false],
            ['name' => 'Hash Browns', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 6, 'is_purchased' => false],
            ['name' => 'Sausages', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 7, 'is_purchased' => false],
            ['name' => 'Ham', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 8, 'is_purchased' => false],
            ['name' => 'Bagels', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 9, 'is_purchased' => false],
            ['name' => 'Cream Cheese', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 10, 'is_purchased' => false],
            ['name' => 'Smoked Salmon', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 11, 'is_purchased' => false],
            ['name' => 'Capers', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 12, 'is_purchased' => false],
            ['name' => 'English Muffins', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 13, 'is_purchased' => false],
            ['name' => 'Croissants', 'quantity' => 4.0, 'unit' => 'pieces', 'sort_order' => 14, 'is_purchased' => false],
            ['name' => 'Jam', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 15, 'is_purchased' => false],
            ['name' => 'Peanut Butter', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 16, 'is_purchased' => false],
            ['name' => 'Oatmeal', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 17, 'is_purchased' => false],
            ['name' => 'Granola', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 18, 'is_purchased' => false],
            ['name' => 'Bananas', 'quantity' => 6.0, 'unit' => 'pieces', 'sort_order' => 19, 'is_purchased' => false],
            ['name' => 'Strawberries', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 20, 'is_purchased' => false],
            ['name' => 'Blueberries', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 21, 'is_purchased' => false],
            ['name' => 'Raspberries', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 22, 'is_purchased' => false],
            ['name' => 'Greek Yogurt', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 23, 'is_purchased' => false],
            ['name' => 'Cottage Cheese', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 24, 'is_purchased' => false],
            ['name' => 'Cereal', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 25, 'is_purchased' => false],
            ['name' => 'Milk', 'quantity' => 2.0, 'unit' => 'liters', 'sort_order' => 26, 'is_purchased' => false],
            ['name' => 'Almond Milk', 'quantity' => 1.0, 'unit' => 'liter', 'sort_order' => 27, 'is_purchased' => false],
            ['name' => 'Coffee', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 28, 'is_purchased' => false],
            ['name' => 'Tea Bags', 'quantity' => 50.0, 'unit' => 'pieces', 'sort_order' => 29, 'is_purchased' => false],
            ['name' => 'Honey', 'quantity' => 250.0, 'unit' => 'grams', 'sort_order' => 30, 'is_purchased' => false],
            ['name' => 'Chia Seeds', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 31, 'is_purchased' => false],
            ['name' => 'Flax Seeds', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 32, 'is_purchased' => false],
            ['name' => 'Almonds', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 33, 'is_purchased' => false],
            ['name' => 'Walnuts', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 34, 'is_purchased' => false],
            ['name' => 'Pecans', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 35, 'is_purchased' => false],
            ['name' => 'Cashews', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 36, 'is_purchased' => false],
            ['name' => 'Pistachios', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 37, 'is_purchased' => false],
            ['name' => 'Pumpkin Seeds', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 38, 'is_purchased' => false],
            ['name' => 'Sunflower Seeds', 'quantity' => 150.0, 'unit' => 'grams', 'sort_order' => 39, 'is_purchased' => false],
            ['name' => 'Sesame Seeds', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 40, 'is_purchased' => false],
            ['name' => 'Coconut Flakes', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 41, 'is_purchased' => false],
            ['name' => 'Dried Cranberries', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 42, 'is_purchased' => false],
            ['name' => 'Dried Apricots', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 43, 'is_purchased' => false],
            ['name' => 'Dates', 'quantity' => 300.0, 'unit' => 'grams', 'sort_order' => 44, 'is_purchased' => false],
            ['name' => 'Raisins', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 45, 'is_purchased' => false],
            ['name' => 'Protein Powder', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 46, 'is_purchased' => false],
            ['name' => 'Whey Protein', 'quantity' => 1.0, 'unit' => 'kg', 'sort_order' => 47, 'is_purchased' => false],
            ['name' => 'Coconut Water', 'quantity' => 1.0, 'unit' => 'liter', 'sort_order' => 48, 'is_purchased' => false],
            ['name' => 'Green Tea', 'quantity' => 50.0, 'unit' => 'bags', 'sort_order' => 49, 'is_purchased' => false],
            ['name' => 'Herbal Tea', 'quantity' => 40.0, 'unit' => 'bags', 'sort_order' => 50, 'is_purchased' => false],
            ['name' => 'Matcha Powder', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 51, 'is_purchased' => false],
            ['name' => 'Cocoa Powder', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 52, 'is_purchased' => false],
            ['name' => 'Dark Chocolate', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 53, 'is_purchased' => false],
            ['name' => 'Vanilla Extract', 'quantity' => 50.0, 'unit' => 'ml', 'sort_order' => 54, 'is_purchased' => false],
            ['name' => 'Almond Extract', 'quantity' => 50.0, 'unit' => 'ml', 'sort_order' => 55, 'is_purchased' => false],
            ['name' => 'Baking Powder', 'quantity' => 100.0, 'unit' => 'grams', 'sort_order' => 56, 'is_purchased' => false],
            ['name' => 'Baking Soda', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 57, 'is_purchased' => false],
            ['name' => 'Yeast', 'quantity' => 50.0, 'unit' => 'grams', 'sort_order' => 58, 'is_purchased' => false],
            ['name' => 'Cornstarch', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 59, 'is_purchased' => false],
            ['name' => 'Coconut Oil', 'quantity' => 500.0, 'unit' => 'ml', 'sort_order' => 60, 'is_purchased' => false],
            ['name' => 'Avocado Oil', 'quantity' => 500.0, 'unit' => 'ml', 'sort_order' => 61, 'is_purchased' => false],
            ['name' => 'Sesame Oil', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 62, 'is_purchased' => false],
            ['name' => 'Balsamic Vinegar', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 63, 'is_purchased' => false],
            ['name' => 'Red Wine Vinegar', 'quantity' => 250.0, 'unit' => 'ml', 'sort_order' => 64, 'is_purchased' => false],
            ['name' => 'Apple Cider Vinegar', 'quantity' => 500.0, 'unit' => 'ml', 'sort_order' => 65, 'is_purchased' => false],
            ['name' => 'Worcestershire Sauce', 'quantity' => 150.0, 'unit' => 'ml', 'sort_order' => 66, 'is_purchased' => false],
            ['name' => 'Hot Sauce', 'quantity' => 150.0, 'unit' => 'ml', 'sort_order' => 67, 'is_purchased' => false],
            ['name' => 'Mustard', 'quantity' => 200.0, 'unit' => 'grams', 'sort_order' => 68, 'is_purchased' => false],
            ['name' => 'Mayonnaise', 'quantity' => 400.0, 'unit' => 'grams', 'sort_order' => 69, 'is_purchased' => false],
            ['name' => 'Ketchup', 'quantity' => 500.0, 'unit' => 'grams', 'sort_order' => 70, 'is_purchased' => false],
        ];

        foreach ($groceryList2Items as $item) {
            GroceryListItem::create([
                'grocery_list_id' => $groceryList2->id,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'sort_order' => $item['sort_order'],
                'is_purchased' => $item['is_purchased'],
            ]);
        }

        $additionalGroceryLists = [
            ['name' => 'Monthly Essentials', 'notes' => 'Items needed monthly'],
            ['name' => 'Party Shopping', 'notes' => 'Items for upcoming party'],
            ['name' => 'BBQ Weekend', 'notes' => 'Items for weekend BBQ'],
            ['name' => 'Healthy Week', 'notes' => 'Healthy eating items'],
            ['name' => 'Baking Supplies', 'notes' => 'Items for baking'],
            ['name' => 'Snacks & Treats', 'notes' => 'Snacks and treats'],
            ['name' => 'Dinner Party', 'notes' => 'Items for dinner party'],
            ['name' => 'Meal Prep Sunday', 'notes' => 'Items for meal prep'],
            ['name' => 'Holiday Shopping', 'notes' => 'Holiday items'],
            ['name' => 'Kids Lunch Box', 'notes' => 'Items for kids lunches'],
            ['name' => 'Vegetarian Week', 'notes' => 'Vegetarian items'],
            ['name' => 'Protein Focus', 'notes' => 'High protein items'],
            ['name' => 'Mediterranean Diet', 'notes' => 'Mediterranean ingredients'],
            ['name' => 'Asian Cuisine', 'notes' => 'Asian cooking ingredients'],
            ['name' => 'Mexican Night', 'notes' => 'Items for Mexican dinner'],
            ['name' => 'Italian Dinner', 'notes' => 'Italian ingredients'],
            ['name' => 'Soup Season', 'notes' => 'Items for soups'],
            ['name' => 'Salad Week', 'notes' => 'Fresh salad ingredients'],
            ['name' => 'Smoothie Ingredients', 'notes' => 'Smoothie items'],
            ['name' => 'Dessert Making', 'notes' => 'Dessert ingredients'],
            ['name' => 'Spice Restock', 'notes' => 'Spices and seasonings'],
            ['name' => 'Frozen Foods', 'notes' => 'Frozen items'],
            ['name' => 'Canned Goods', 'notes' => 'Canned items'],
            ['name' => 'Organic Produce', 'notes' => 'Organic fruits and vegetables'],
            ['name' => 'Bulk Items', 'notes' => 'Items to buy in bulk'],
            ['name' => 'Quick Meals', 'notes' => 'Quick meal ingredients'],
            ['name' => 'Comfort Food', 'notes' => 'Comfort food items'],
            ['name' => 'Detox Week', 'notes' => 'Healthy detox items'],
            ['name' => 'Energy Boost', 'notes' => 'Energy boosting foods'],
            ['name' => 'Budget Shopping', 'notes' => 'Budget-friendly items'],
            ['name' => 'Premium Items', 'notes' => 'Premium quality items'],
            ['name' => 'Local Market', 'notes' => 'Items from local market'],
            ['name' => 'Farmers Market', 'notes' => 'Fresh farmers market items'],
        ];

        foreach ($additionalGroceryLists as $list) {
            GroceryList::create([
                'user_id' => $admin->id,
                'name' => $list['name'],
                'notes' => $list['notes'],
            ]);
        }
    }
}
