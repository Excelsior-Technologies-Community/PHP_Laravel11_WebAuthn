<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebAuthn\WebAuthnRegisterController;
use App\Http\Controllers\WebAuthn\WebAuthnLoginController;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

Route::get('/', fn() => redirect('/login'));

// Auth Routes
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'store']);

Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate']);

Route::get('/passkey-login', [AuthController::class, 'passkeyLogin'])->name('passkey.login');

Route::get('/logout', [AuthController::class, 'logout']);

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard']);
    Route::delete('/passkey/delete/{id}', [AuthController::class, 'deletePasskey']);
    Route::post('/user/webauthn-required', [AuthController::class, 'updateWebauthnRequired']);
    Route::post('/user/trust-device', [AuthController::class, 'trustDevice']);
    Route::post('/user/generate-recovery-codes', [AuthController::class, 'generateNewRecoveryCodes']);
    Route::post('/user/verify-recovery-code', [AuthController::class, 'verifyRecoveryCode']);
});

// WebAuthn Routes
WebAuthnRoutes::register();

// Custom WebAuthn endpoints (override if needed)
Route::post('/webauthn/register/options', [WebAuthnRegisterController::class, 'options']);
Route::post('/webauthn/register', [WebAuthnRegisterController::class, 'register']);
Route::post('/webauthn/login/options', [WebAuthnLoginController::class, 'options']);
Route::post('/webauthn/login', [WebAuthnLoginController::class, 'login']);