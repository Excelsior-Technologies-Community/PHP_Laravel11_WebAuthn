<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passkey Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: rgba(22, 36, 71, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 450px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        h1 { margin-bottom: 30px; color: #e43f5a; }
        .passkey-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            padding: 16px 32px;
            font-size: 18px;
            margin: 20px 0;
            width: 100%;
            border: none;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: transform 0.3s;
        }
        .passkey-btn:hover { transform: translateY(-2px); }
        .email-input {
            width: 100%;
            padding: 14px;
            margin: 15px 0;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            background: rgba(31, 48, 94, 0.8);
            color: white;
            font-size: 16px;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #8ab6d6;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .divider span { margin: 0 15px; }
        .password-link {
            color: #8ab6d6;
            text-decoration: none;
        }
        .password-link:hover { color: #e43f5a; }
        .back-link {
            display: block;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1> Passkey Login</h1>
        
        <input type="email" id="email" class="email-input" placeholder="Your email address" value="{{ old('email', $email ?? '') }}">
        
        <button id="passkey-login-btn" class="passkey-btn">
             Login with Passkey
        </button>
        
        <div class="divider">
            <span>OR</span>
        </div>
        
        <a href="/login" class="password-link">Login with Password →</a>
        <a href="/register" class="back-link">Don't have an account? Register</a>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        function base64UrlToBuffer(base64url) {
            let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            while (base64.length % 4) base64 += '=';
            return Uint8Array.from(atob(base64), c => c.charCodeAt(0));
        }
        
        function bufferToBase64Url(buffer) {
            return btoa(String.fromCharCode(...new Uint8Array(buffer)))
                .replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }
        
        document.getElementById('passkey-login-btn').addEventListener('click', async () => {
            const email = document.getElementById('email').value;
            const button = document.getElementById('passkey-login-btn');
            button.disabled = true;
            button.textContent = ' Verifying...';
            
            try {
                // Get assertion options
                const optionsResponse = await fetch('/webauthn/login/options', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email })
                });
                
                if (!optionsResponse.ok) {
                    throw new Error('No passkey registered for this email');
                }
                
                const optionsData = await optionsResponse.json();
                optionsData.challenge = base64UrlToBuffer(optionsData.challenge);
                if (optionsData.allowCredentials) {
                    optionsData.allowCredentials = optionsData.allowCredentials.map(cred => ({
                        ...cred,
                        id: base64UrlToBuffer(cred.id)
                    }));
                }
                
                const assertion = await navigator.credentials.get({ publicKey: optionsData });
                
                const assertionData = {
                    id: assertion.id,
                    rawId: bufferToBase64Url(assertion.rawId),
                    type: assertion.type,
                    response: {
                        authenticatorData: bufferToBase64Url(assertion.response.authenticatorData),
                        clientDataJSON: bufferToBase64Url(assertion.response.clientDataJSON),
                        signature: bufferToBase64Url(assertion.response.signature),
                        userHandle: assertion.response.userHandle ? bufferToBase64Url(assertion.response.userHandle) : null
                    }
                };
                
                const loginResponse = await fetch('/webauthn/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(assertionData)
                });
                
                if (loginResponse.ok) {
                    window.location.href = '/dashboard';
                } else {
                    alert('Login failed. Please try again.');
                    button.disabled = false;
                    button.textContent = ' Login with Passkey';
                }
            } catch (error) {
                console.error(error);
                alert(error.message || 'Passkey authentication failed');
                button.disabled = false;
                button.textContent = ' Login with Passkey';
            }
        });
    </script>
</body>
</html>