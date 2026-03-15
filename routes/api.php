<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CookingSessionController;
use App\Http\Controllers\CookingSessionTimerController;
use App\Http\Controllers\GroceryListController;
use App\Http\Controllers\GroceryListItemController;
use App\Http\Controllers\PantryItemController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\RecipeIngredientController;
use App\Http\Controllers\ShoppingItemController;
use App\Http\Controllers\SpaceStorageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    Route::post('/recipe/ai/generate-recipe', [RecipeController::class, 'generateAiRecipeFromIngredients']);
    // Route::post('/recipe/ai/turn-vegetarian', [RecipeController::class, 'turnRecipeVegetarian']);
    // Route::post('/recipe/ai/turn-vegan', [RecipeController::class, 'turnRecipeVegan']);

    Route::apiResource('grocery-lists', GroceryListController::class);
    Route::post('save-grocery-list-data', [GroceryListController::class, 'saveGroceryListWithData']);
    Route::post('grocery-lists/{groceryList}/items', [GroceryListController::class, 'updateItems']);
    Route::post('get-grocery-lists', [GroceryListController::class, 'paginateGroceryLists']);
    Route::apiResource('grocery-list-items', GroceryListItemController::class);

    Route::post('save-shopping-session', [GroceryListController::class, 'saveShoppingSession']);
    Route::get('get-shopping-session', [GroceryListController::class, 'getShoppingSession']);
    Route::post('finish-shopping-session', [GroceryListController::class, 'finishShoppingSession']);
    Route::delete('delete-shopping-session', [GroceryListController::class, 'deleteShoppingSession']);

    Route::patch('shopping-items/{shoppingItem}', [ShoppingItemController::class, 'update']);
    Route::post('shopping-items/update-order', [ShoppingItemController::class, 'updateOrder']);

    Route::post('create-cooking-session', [CookingSessionController::class, 'createCookingSession']);
    Route::get('get-cooking-session', [CookingSessionController::class, 'getCookingSession']);
    Route::post('cooking-session/timer-fab-position', [CookingSessionController::class, 'updateTimerFabPosition']);

    Route::post('cooking-session/timers', [CookingSessionTimerController::class, 'createTimer']);
    Route::post('cooking-session/timers/start', [CookingSessionTimerController::class, 'startTimer']);
    Route::post('cooking-session/timers/pause-or-continue', [CookingSessionTimerController::class, 'pauseOrContinueTimer']);
    Route::post('cooking-session/timers/complete', [CookingSessionTimerController::class, 'completeTimer']);
    Route::post('cooking-session/timers/reset', [CookingSessionTimerController::class, 'resetTimer']);
    Route::delete('cooking-session/timers', [CookingSessionTimerController::class, 'deleteTimer']);

    Route::post('reset-all-recipe-steps', [CookingSessionController::class, 'resetRecipeData']);
});
