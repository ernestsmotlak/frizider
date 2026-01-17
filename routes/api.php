<?php

use App\Http\Controllers\GroceryListController;
use App\Http\Controllers\GroceryListItemController;
use App\Http\Controllers\PantryItemController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeIngredientController;
use App\Http\Controllers\SpaceStorageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.cookie', 'auth:api'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'updateMe']);
    Route::delete('/me', [UserController::class, 'destroyMe']);

    Route::apiResource('space-storages', SpaceStorageController::class);

    Route::apiResource('pantry-items', PantryItemController::class);

    Route::apiResource('recipes', RecipeController::class);
    Route::post('save-recipe-data', [RecipeController::class, 'saveRecipeWithData']);
    Route::post('get-recipes', [RecipeController::class, 'paginateRecipes']);
    Route::post('recipes/{recipe}/ingredients', [RecipeController::class, 'updateIngredients']);
    Route::post('recipes/{recipe}/instructions', [RecipeController::class, 'updateInstructions']);

    Route::apiResource('recipe-ingredients', RecipeIngredientController::class);
    Route::delete('recipe/{recipe}/ingredient/{ingredient}', [RecipeController::class, 'deleteIngredientFromRecipe']);
    Route::post('recipe/{recipe}/ingredient/{ingredient}/toggle-completed', [RecipeController::class, 'toggleIngredientCompleted']);
    Route::delete('recipe/{recipe}/instruction/{instruction}', [RecipeController::class, 'deleteInstructionFromRecipe']);
    Route::post('recipe/{recipe}/instruction/{instruction}/toggle-completed', [RecipeController::class, 'toggleInstructionCompleted']);

    Route::apiResource('grocery-lists', GroceryListController::class);
    Route::post('save-grocery-list-data', [GroceryListController::class, 'saveGroceryListWithData']);
    Route::post('grocery-lists/{groceryList}/items', [GroceryListController::class, 'updateItems']);
    Route::post('get-grocery-lists', [GroceryListController::class, 'paginateGroceryLists']);
    Route::apiResource('grocery-list-items', GroceryListItemController::class);
});

