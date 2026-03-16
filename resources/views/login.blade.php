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