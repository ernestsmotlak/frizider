<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::group(['prefix' => '/api'], function () {
//    Route::group(['prefix' => 'user'], function () {
//        Route::get('show-all', [UserController::class, 'index']);
//        Route::post('store', [UserController::class, 'store']);
//        Route::get('show/{id}', [UserController::class, 'show']);
//        Route::patch('update/{id}', [UserController::class, 'update']);
//        Route::delete('destroy/{id}', [UserController::class, 'destroy']);
//    });
//});
