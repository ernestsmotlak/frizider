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
    }
}
