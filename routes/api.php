<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require valid token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Chirps API routes (verified users only)
    Route::middleware('verified')->group(function () {
        Route::get('/chirps', [App\Http\Controllers\ChirpController::class, 'index']);
        Route::post('/chirps', [App\Http\Controllers\ChirpController::class, 'store']);
        Route::put('/chirps/{chirp}', [App\Http\Controllers\ChirpController::class, 'update']);
        Route::delete('/chirps/{chirp}', [App\Http\Controllers\ChirpController::class, 'destroy']);
    });
});
