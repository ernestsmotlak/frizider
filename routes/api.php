<?php

use App\Http\Controllers\PantryItemController;
use App\Http\Controllers\SpaceStorageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'updateMe']);
    Route::delete('/me', [UserController::class, 'destroyMe']);

    Route::apiResource('space-storages', SpaceStorageController::class);
    Route::apiResource('pantry-item', PantryItemController::class);
});

