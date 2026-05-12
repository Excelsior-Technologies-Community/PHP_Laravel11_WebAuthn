<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

// Redirect root to login
Route::get('/', fn() => redirect('/login'));

// ===================== AUTH ROUTES =====================
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'store']);

Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate']);

Route::get('/logout', [AuthController::class, 'logout']);

// ===================== PROTECTED ROUTES =====================
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard']);

    Route::delete('/passkey/delete/{id}', [AuthController::class, 'deletePasskey']);
});

// ===================== WEBAUTHN ROUTES =====================
WebAuthnRoutes::register();