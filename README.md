# PHP_Laravel11_WebAuthn


## Project Description

PHP_Laravel11_WebAuthn is a simple yet modern web application built with Laravel 11 that demonstrates user authentication using both traditional email/password login and modern WebAuthn passkey authentication.

The project allows users to register, log in, and manage accounts, while also providing the option to register passwordless authentication devices (passkeys) for enhanced security and convenience.

It is designed to be beginner-friendly while showcasing real-world WebAuthn integration in Laravel.


## Features

- User registration with name, email, and password

- Secure login using email and password

- Personalized dashboard displaying logged-in user information

- Passwordless authentication using WebAuthn passkeys

- Register and manage security keys or device credentials

- Modern dark-themed responsive UI

- CSRF protection handling with exclusion for WebAuthn routes

- Logout functionality for secure session termination

- Client-side WebAuthn JS integration for creating credentials

- Server-side WebAuthn validation and storage

- Compatible with MySQL database

- Laravel 11 backend with Blade templates for frontend

- Lightweight and beginner-friendly authentication system


## Key Advantages
- Modern passwordless authentication for enhanced security
- Beginner-friendly Laravel 11 setup
- Easy to extend with more authentication features
- Works with biometric devices, security keys, and passkeys
- Responsive dark-themed UI for better UX

## Usage
1. Register a new account.
2. Login using email/password.
3. Go to Dashboard and register a WebAuthn passkey.
4. Next time, login using your passkey for passwordless authentication.


## Requirements
- PHP >= 8.1
- Composer
- MySQL
- Modern browser supporting WebAuthn (Chrome, Edge, Firefox)


## Technologies Used

- Backend: PHP 8, Laravel 11

- Frontend: Blade Templates, HTML, CSS, JavaScript

- Authentication: Laragear WebAuthn package for passwordless login

- Database: MySQL

- Server: Built-in Laravel development server (php artisan serve)

- Security: CSRF protection, WebAuthn security keys support



---



## Installation Steps


---


## STEP 1: Create Laravel 11 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel11_WebAuthn "11.*"

```

### Go inside project:

```
cd PHP_Laravel11_WebAuthn

```

#### Explanation:

Installs a fresh Laravel 11 project and moves into its directory.




## STEP 2: Database Setup 

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel11_WebAuthn
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel11_WebAuthn

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

Connects Laravel to MySQL and creates default tables like users.





## STEP 3: Install Laravel WebAuthn Package

### Install package:

```
composer require laragear/webauthn

```

#### Explanation:

Adds WebAuthn support for passwordless authentication.





## STEP 4: Install WebAuthn Setup Files

### Run:

```
php artisan webauthn:install

```

### This will create:

```
config/webauthn.php
app/Http/Controllers/WebAuthn
migration file

```

### Then Run:

```
php artisan migrate

```

#### Explanation:

Generates WebAuthn config, controllers, migration tables, and migrates them.





## STEP 5: Configure Auth Provider

### Open: config/auth.php

#### Find:

```
'providers' => [

```

### Change driver:

```
'providers' => [
    'users' => [
        'driver' => 'eloquent-webauthn',
        'model' => App\Models\User::class,
        'password_fallback' => true,
    ],
],

```

#### Explanation: 

Tells Laravel to use WebAuthn for authentication with fallback to password.




## STEP 6: Update User Model

### Open: app/Models/User.php

```
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable implements WebAuthnAuthenticatable
{
    use WebAuthnAuthentication;

    protected $fillable = [
        'name',
        'email',
        'password'
    ];
}

```

#### Explanation: 

Implements WebAuthn trait and allows mass assignment of fields.





## STEP 7: Register WebAuthn Routes

### Open: routes/web.php

```
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

```

#### Explanation: 

Sets normal auth routes and WebAuthn endpoints for passkey registration/login.





## STEP 8: Create Authentication Controller

### Create controller:

```
php artisan make:controller AuthController

```

### Open: app/Http/Controllers/AuthController.php

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show registration form
    public function register() {
        return view('register');
    }

    // Handle registration
    public function store(Request $request) {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }

    // Show login form
    public function login() {
        return view('login');
    }

    // Handle login
    public function authenticate(Request $request) {
        if (Auth::attempt($request->only('email','password'))) {
            return redirect('/dashboard');
        }
        return back()->with('error','Invalid login');
    }

    // Dashboard view
    public function dashboard() {
        return view('dashboard');
    }

    // Logout
    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
}

```

#### Explanation: 

Handles registration, login, logout, and dashboard view.




## STEP 9: Install WebAuthn JS Helper

### resources/views/dashboard.blade.php

```
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #162447;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            margin-bottom: 16px;
            color: #e43f5a;
        }

        p {
            font-size: 18px;
            margin-bottom: 24px;
        }

        button {
            padding: 12px 24px;
            margin: 10px;
            border: none;
            border-radius: 8px;
            background: #e43f5a;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #ff5c78;
        }

        a {
            display: inline-block;
            margin-top: 16px;
            color: #8ab6d6;
            text-decoration: none;
        }

        a:hover {
            color: #e43f5a;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Dashboard</h1>
        <p>Welcome, {{ Auth::user()->name }}</p>
        <button id="register-passkey">Register Passkey (WebAuthn)</button>
        <a href="/logout">Logout</a>
    </div>

    <script>
        function base64UrlToBuffer(base64url) {
            let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            while (base64.length % 4) base64 += '=';
            return Uint8Array.from(atob(base64), c => c.charCodeAt(0));
        }

        const registerBtn = document.getElementById('register-passkey');

        registerBtn.addEventListener('click', async () => {
            registerBtn.disabled = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const optionsResponse = await fetch('/webauthn/register/options', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                const optionsData = await optionsResponse.json();
                if (!optionsData || !optionsData.challenge) {
                    alert('Failed to get registration options.');
                    registerBtn.disabled = false;
                    return;
                }
                const publicKey = {
                    ...optionsData,
                    challenge: base64UrlToBuffer(optionsData.challenge),
                    user: { ...optionsData.user, id: base64UrlToBuffer(optionsData.user.id) }
                };
                const credential = await navigator.credentials.create({ publicKey });
                const finishResponse = await fetch('/webauthn/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ credential })
                });
                if (finishResponse.ok) {
                    alert('Passkey registered successfully!');
                } else {
                    alert('Registration failed.');
                    registerBtn.disabled = false;
                }
            } catch (err) {
                console.error('WebAuthn error:', err);
                alert('Error during WebAuthn registration.');
                registerBtn.disabled = false;
            }
        });
    </script>
</body>

</html>

```



### resources/views/login.blade.php

```
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #162447;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 24px;
            color: #e43f5a;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #1f305e;
            color: #f0f0f0;
            font-size: 16px;
        }

        input::placeholder {
            color: #b0b0b0;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: #e43f5a;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #ff5c78;
        }

        a {
            display: block;
            margin-top: 16px;
            color: #8ab6d6;
            text-decoration: none;
        }

        a:hover {
            color: #e43f5a;
        }

        .error {
            color: #ff5c78;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Login</h1>
        @if(session('error'))
            <p class="error">{{ session('error') }}</p>
        @endif
        <form action="/login" method="POST">
            @csrf
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="/register">Don't have an account? Register</a>
    </div>
</body>

</html>

```


### resources/views/register.blade.php

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        /* Global Dark Mode */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #162447;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 24px;
            color: #e43f5a;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #1f305e;
            color: #f0f0f0;
            font-size: 16px;
        }

        input::placeholder {
            color: #b0b0b0;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: #e43f5a;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #ff5c78;
        }

        a {
            display: block;
            margin-top: 16px;
            color: #8ab6d6;
            text-decoration: none;
        }

        a:hover {
            color: #e43f5a;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Register</h1>
        <form action="/register" method="POST">
            @csrf
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <a href="/login">Already have an account? Login</a>
    </div>
</body>
</html>

```


## STEP 10: Exclude the route from CSRF (less secure)

### In app/Http/Middleware/VerifyCsrfToken.php:

```
protected $except = [
    'webauthn/*',
];

```

#### Explanation: 

Prevents CSRF errors for WebAuthn JS requests.





## STEP 11: Test the Application

### Start Laravel dev server:

```
php artisan serve

```

### Open in browser:

```
http://127.0.0.1:8000

```

#### Explanation: 

Starts the Laravel server and you can access registration, login, dashboard, and WebAuthn passkey features.




## Expected Output:

### Register Page:


<img src="screenshots/Screenshot 2026-03-16 162020.png" width="900">


### Login Page:


<img src="screenshots/Screenshot 2026-03-16 162117.png" width="900">


### Dashboard Page:


<img src="screenshots/Screenshot 2026-03-16 162222.png" width="900">


### Register WebAuthn Passkey:


<img src="screenshots/Screenshot 2026-03-16 162238.png" width="900">

<img src="screenshots/Screenshot 2026-03-16 162307.png" width="900">

<img src="screenshots/Screenshot 2026-03-16 162323.png" width="900">





---

## Project Folder Structure:

```
PHP_Laravel11_WebAuthn/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── AuthController.php
│   └── Models/
│       └── User.php
├── config/
│   ├── auth.php
│   └── webauthn.php
├── database/
│   └── migrations/
│       ├── create_users_table.php
│       └── create_webauthn_table.php
├── resources/
│   └── views/
│       ├── register.blade.php
│       ├── login.blade.php
│       └── dashboard.blade.php
├── routes/
│   └── web.php
└── .env

```
