<?php

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
    Route::post('get-recipes', [RecipeController::class, 'paginateRecipes']);
    Route::post('recipes/{recipe}/ingredients', [RecipeController::class, 'updateIngredients']);

    Route::apiResource('recipe-ingredients', RecipeIngredientController::class);
});

