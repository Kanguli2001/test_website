<?php

use App\Http\Controllers\ChirpController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Register;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;
use App\Http\Controllers\Auth\VerifyEmail;
use App\Http\Controllers\Auth\ResendVerificationEmail;
use App\Http\Middleware\RedirectIfEmailVerified;

//Route::get("/", [ChirpController::class,"index"]);
Route::get("/", [ChirpController::class, 'index']);


Route::middleware(['auth', 'verified'])->group(function () {
    Route::post("/chirps", [ChirpController::class, 'store']);
    Route::get("/chirps/{chirp}/edit", [ChirpController::class, 'edit']);
    Route::put("/chirps/{chirp}", [ChirpController::class,"update"]);
    Route::delete("/chirps/{chirp}", [ChirpController::class, 'destroy']);
});




//Registraion Routes

Route::view("/register", "auth.register")
    ->middleware("guest")
    ->name("register");

Route::post("/register", Register::class)->middleware("guest");

//Login routes

Route::view("/login", "auth.login")
    ->middleware("guest")
    ->name("login");

Route::post("/login", Login::class)->middleware("guest");

//Logout Route
Route::post("/logout", Logout::class)->middleware("auth")->name("logout");

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware(['auth', RedirectIfEmailVerified::class])->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', VerifyEmail::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend', ResendVerificationEmail::class)
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');


