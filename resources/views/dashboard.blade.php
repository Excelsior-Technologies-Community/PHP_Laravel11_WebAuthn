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