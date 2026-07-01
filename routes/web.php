<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebAuthn\WebAuthnRegisterController;
use App\Http\Controllers\WebAuthn\WebAuthnLoginController;
use Laragear\WebAuthn\Http\Routes as WebAuthnRoutes;

Route::get('/', fn() => redirect('/login'));

// Register
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');

// Password Login
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

// Passkey Login
Route::get('/passkey-login', [AuthController::class, 'passkeyLogin'])->name('passkey.login');

// Logout
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Recovery Code (guest accessible, rate-limited)
Route::post('/user/verify-recovery-code', [AuthController::class, 'verifyRecoveryCode'])
    ->middleware('throttle:5,1')
    ->name('recovery.verify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::delete('/passkey/delete/{id}', [AuthController::class, 'deletePasskey'])->name('passkey.delete');
    Route::post('/user/webauthn-required', [AuthController::class, 'updateWebauthnRequired'])->name('user.webauthn-required');
    Route::post('/user/trust-device', [AuthController::class, 'trustDevice'])->name('user.trust-device');
    Route::post('/user/generate-recovery-codes', [AuthController::class, 'generateNewRecoveryCodes'])->name('recovery.generate');
    Route::post('/user/update-profile', [AuthController::class, 'updateProfile'])->name('user.update-profile');
});

WebAuthnRoutes::register();

// WebAuthn Register
Route::post('/webauthn/register/options', [WebAuthnRegisterController::class, 'options'])->name('webauthn.register.options');
Route::post('/webauthn/register', [WebAuthnRegisterController::class, 'register'])->name('webauthn.register');

// WebAuthn Login
Route::post('/webauthn/login/options', [WebAuthnLoginController::class, 'options'])->name('webauthn.login.options');
Route::post('/webauthn/login', [WebAuthnLoginController::class, 'login'])->name('webauthn.login');