<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

// Redirect root to login
Route::get('/', fn() => redirect('/login'));

// Regular login/register
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'store']);
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/dashboard', [AuthController::class, 'dashboard'])->middleware('auth');
Route::get('/logout', [AuthController::class, 'logout']);

// WebAuthn routes
WebAuthnRoutes::register(); // Handles /webauthn/register/options, /finish, etc.