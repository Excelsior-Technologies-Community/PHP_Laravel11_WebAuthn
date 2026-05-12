<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Secure Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, #1e293b 0%, #020617 45%),
                #020617;
            color: #fff;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        /* TOP NAV */

        .topbar {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 24px;
            padding: 28px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        .welcome h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #94a3b8;
            font-size: 15px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* BUTTONS */

        .btn {
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 24px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .btn-passkey {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            box-shadow: 0 8px 20px rgba(34,197,94,0.25);
        }

        .btn-passkey:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(34,197,94,0.4);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(248,113,113,0.2);
            padding: 10px 18px;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .logout-btn {
            color: #f87171;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .logout-btn:hover {
            color: #fff;
        }

        /* GRID */

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media(max-width: 950px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .top-actions {
                width: 100%;
                flex-wrap: wrap;
            }
        }

        /* CARDS */

        .card {
            background: rgba(15, 23, 42, 0.82);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 24px;
            padding: 28px;
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        }

        .card h2 {
            font-size: 22px;
            margin-bottom: 24px;
            color: #fff;
        }

        /* TABLE */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead tr {
            background: rgba(255,255,255,0.04);
        }

        table th {
            text-align: left;
            padding: 16px;
            font-size: 14px;
            color: #cbd5e1;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        table td {
            padding: 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #e2e8f0;
            vertical-align: middle;
        }

        table tr:hover {
            background: rgba(255,255,255,0.03);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(34,197,94,0.15);
            color: #4ade80;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }

        .activity-badge {
            background: rgba(59,130,246,0.15);
            color: #60a5fa;
        }

        .empty-state {
            text-align: center;
            padding: 40px 10px;
            color: #94a3b8;
            font-size: 15px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="welcome">
            <h1>
                Welcome, {{ Auth::user()->name }}
            </h1>

            <p>
                Manage your passkeys and monitor login activity securely.
            </p>
        </div>

        <div class="top-actions">

            <button id="register-passkey" class="btn btn-passkey">
                + Register Passkey
            </button>

            <a href="/logout" class="logout-btn">
                Logout
            </a>

        </div>

    </div>

    <!-- GRID -->

    <div class="grid">

        <!-- PASSKEY CARD -->

        <div class="card">

            <h2>Registered Passkeys</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($credentials as $cred)

                        <tr>

                            <td>
                                <span class="badge">
                                    🔐 Passkey
                                </span>
                            </td>

                            <td>
                                {{ $cred->created_at->format('d M Y • h:i A') }}
                            </td>

                            <td>

                                <form method="POST" action="/passkey/delete/{{ $cred->id }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-delete">
                                        Delete
                                    </button>
                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="3">
                                <div class="empty-state">
                                    No passkeys registered yet.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <!-- LOGIN ACTIVITY -->

        <div class="card">

            <h2>Login Activity</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Browser</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($activities as $log)

                        <tr>

                            <td>
                                <span class="badge activity-badge">
                                    {{ $log->login_method }}
                                </span>
                            </td>

                            <td>{{ $log->browser }}</td>

                            <td>{{ $log->ip_address }}</td>

                            <td>
                                {{ $log->created_at->format('d M Y • h:i A') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    No login activity found.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- WEBAUTHN SCRIPT -->

<script>
function base64UrlToBuffer(base64url) {
    let base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');

    while (base64.length % 4) {
        base64 += '=';
    }

    const binary = atob(base64);

    return Uint8Array.from(binary, c => c.charCodeAt(0));
}

function bufferToBase64Url(buffer) {
    return btoa(String.fromCharCode(...new Uint8Array(buffer)))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

const registerBtn = document.getElementById('register-passkey');

registerBtn.addEventListener('click', async () => {

    registerBtn.disabled = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute('content');

    try {

        const optionsResponse = await fetch('/webauthn/register/options', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const optionsData = await optionsResponse.json();

        optionsData.challenge = base64UrlToBuffer(optionsData.challenge);

        optionsData.user.id = base64UrlToBuffer(optionsData.user.id);

        const credential = await navigator.credentials.create({
            publicKey: optionsData
        });

        const credentialData = {
            id: credential.id,
            rawId: bufferToBase64Url(credential.rawId),
            type: credential.type,
            response: {
                attestationObject: bufferToBase64Url(
                    credential.response.attestationObject
                ),
                clientDataJSON: bufferToBase64Url(
                    credential.response.clientDataJSON
                )
            }
        };

        const finishResponse = await fetch('/webauthn/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(credentialData)
        });

        if (finishResponse.ok) {

            alert('Passkey registered successfully!');

            location.reload();

        } else {

            alert('Registration failed.');

            registerBtn.disabled = false;
        }

    } catch (err) {

        console.error('WebAuthn Error:', err);

        alert('Error during WebAuthn registration.');

        registerBtn.disabled = false;
    }
});
</script>

</body>
</html>