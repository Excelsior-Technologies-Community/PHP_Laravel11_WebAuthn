<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #f0f0f0;
            min-height: 100vh;
            padding: 30px 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .top-bar h1 { color: #e43f5a; margin: 0; }
        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: #f0f0f0;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.2); }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 800px) { .grid { grid-template-columns: 1fr; } }
        .card {
            background: rgba(22, 36, 71, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .card h2 {
            margin-top: 0;
            font-size: 18px;
            color: #8ab6d6;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 12px;
        }
        .full-width { grid-column: 1 / -1; }
        input, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            background: rgba(31, 48, 94, 0.8);
            color: white;
            font-size: 14px;
        }
        label { font-size: 13px; color: #8ab6d6; display: block; margin-top: 12px; }
        button {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            background: #e43f5a;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
            margin-top: 12px;
            transition: 0.2s;
        }
        button:hover { background: #ff5c78; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-secondary { background: #3b82f6; }
        .btn-secondary:hover { background: #60a5fa; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #ef4444; }

        .score-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .score-number { font-size: 28px; font-weight: bold; color: #22c55e; }
        .score-bar-track {
            width: 100%;
            height: 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            overflow: hidden;
            margin-top: 6px;
        }
        .score-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            border-radius: 20px;
            transition: width 0.6s ease;
        }
        .score-breakdown { margin-top: 14px; font-size: 12px; }
        .score-item { display: flex; justify-content: space-between; padding: 4px 0; color: #8ab6d6; }
        .score-item.done { color: #22c55e; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        th { color: #8ab6d6; font-weight: 500; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; }
        .badge-trusted { background: rgba(34,197,94,0.2); color: #22c55e; }
        .badge-new { background: rgba(228,63,90,0.2); color: #ff5c78; }
        .row-between { display: flex; justify-content: space-between; align-items: center; }
        .muted { color: #8ab6d6; font-size: 12px; }
        .toggle-wrap { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
        .switch { position: relative; display: inline-block; width: 46px; height: 24px; flex-shrink: 0; }
        .switch input { display: none; }
        .slider {
            position: absolute; cursor: pointer; inset: 0;
            background: rgba(255,255,255,0.2); border-radius: 24px; transition: 0.3s;
        }
        .slider::before {
            content: ""; position: absolute; height: 18px; width: 18px; left: 3px; bottom: 3px;
            background: white; border-radius: 50%; transition: 0.3s;
        }
        input:checked + .slider { background: #22c55e; }
        input:checked + .slider::before { transform: translateX(22px); }
        .code-chip {
            display: inline-block; background: rgba(31,48,94,0.8); padding: 6px 10px;
            border-radius: 6px; margin: 4px; font-family: monospace; font-size: 13px;
            letter-spacing: 1px;
        }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: rgba(34,197,94,0.15); color: #22c55e; }
        .alert-error { background: rgba(220,38,38,0.15); color: #ff5c78; }
        .empty { color: #6c7793; font-size: 13px; padding: 10px 0; }
    </style>
</head>
<body>
<div class="container">

    <div class="top-bar">
        <h1>Dashboard</h1>
        <a href="{{ route('logout') }}" class="logout-btn">Logout</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="grid">

        <div class="card">
            <h2>Security Score</h2>

            <div class="score-row">
                <span class="muted">Overall protection level</span>
                <span class="score-number">{{ $securityScore }}%</span>
            </div>
            <div class="score-bar-track">
                <div class="score-bar-fill" style="width: {{ $securityScore }}%;"></div>
            </div>

            <div class="score-breakdown">
                <div class="score-item {{ $credentials->count() > 0 ? 'done' : '' }}">
                    <span>Passkey registered</span><span>{{ $credentials->count() > 0 ? '✓ +40' : '+40' }}</span>
                </div>
                <div class="score-item {{ auth()->user()->webauthn_required ? 'done' : '' }}">
                    <span>Passkey-only login enabled</span><span>{{ auth()->user()->webauthn_required ? '✓ +30' : '+30' }}</span>
                </div>
                <div class="score-item {{ $trustedDevices->count() > 0 ? 'done' : '' }}">
                    <span>Trusted device added</span><span>{{ $trustedDevices->count() > 0 ? '✓ +20' : '+20' }}</span>
                </div>
                <div class="score-item {{ auth()->user()->email_verified ? 'done' : '' }}">
                    <span>Email verified</span><span>{{ auth()->user()->email_verified ? '✓ +10' : '+10' }}</span>
                </div>
            </div>

            <div class="toggle-wrap">
                <div>
                    <div>Require Passkey Login</div>
                    <div class="muted">Force passkey-only login for this account</div>
                </div>
                <label class="switch">
                    <input type="checkbox" id="webauthn-toggle" {{ auth()->user()->webauthn_required ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="card">
            <h2>Edit Profile</h2>
            <div id="profile-msg" class="muted"></div>
            <form id="profile-form">
                <label>Name</label>
                <input type="text" id="profile-name" value="{{ auth()->user()->name }}" required>

                <label>Email</label>
                <input type="email" id="profile-email" value="{{ auth()->user()->email }}" required>

                <label>New Password (leave blank to keep current)</label>
                <input type="password" id="profile-password" placeholder="••••••••">

                <label>Confirm New Password</label>
                <input type="password" id="profile-password-confirm" placeholder="••••••••">

                <button type="submit">Save Changes</button>
            </form>
        </div>

        <div class="card">
            <h2>Passkeys ({{ $credentials->count() }})</h2>
            @forelse($credentials as $credential)
                <div class="row-between" style="padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.06);">
                    <div>
                        <div>{{ $credential->alias ?? 'Unnamed Passkey' }}</div>
                        <div class="muted">Added {{ $credential->created_at->diffForHumans() }}</div>
                    </div>
                    <button class="btn-danger delete-passkey-btn" data-id="{{ $credential->id }}">Remove</button>
                </div>
            @empty
                <div class="empty">No passkeys registered yet.</div>
            @endforelse
            <a href="{{ route('passkey.login') }}"><button class="btn-secondary" type="button">Manage Passkeys</button></a>
        </div>

        <div class="card">
            <h2>Recovery Codes</h2>
            <div class="muted">Unused codes: {{ $recoveryCodes->count() }}</div>
            <div id="recovery-codes-list" style="margin: 12px 0;">
                @forelse($recoveryCodes as $rc)
                    <span class="code-chip">{{ $rc->code }}</span>
                @empty
                    <div class="empty">No unused recovery codes left.</div>
                @endforelse
            </div>
            <button class="btn-secondary" id="regenerate-codes-btn">Generate New Codes</button>
        </div>

        <div class="card">
            <h2>Trusted Devices ({{ $trustedDevices->count() }})</h2>
            @forelse($trustedDevices as $device)
                <div class="row-between" style="padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.06);">
                    <div>
                        <div>{{ $device->device_name }}</div>
                        <div class="muted">{{ $device->browser }} · {{ $device->ip_address }}</div>
                    </div>
                    <span class="badge badge-trusted">Trusted</span>
                </div>
            @empty
                <div class="empty">No trusted devices yet.</div>
            @endforelse
        </div>

        <div class="card full-width">
            <h2>Recent Login Activity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Device</th>
                        <th>Location</th>
                        <th>IP</th>
                        <th>Status</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->login_method }}</td>
                            <td>{{ $activity->device_name ?? $activity->browser }}</td>
                            <td>{{ $activity->city ? $activity->city . ', ' . $activity->country : '—' }}</td>
                            <td>{{ $activity->ip_address }}</td>
                            <td>
                                @if($activity->is_trusted)
                                    <span class="badge badge-trusted">Trusted</span>
                                @else
                                    <span class="badge badge-new">New Device</span>
                                @endif
                            </td>
                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No login activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function jsonHeaders() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        };
    }

    async function safeJson(response) {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error('Server error (status ' + response.status + '). Please reload the page.');
        }
        return response.json();
    }

    document.getElementById('webauthn-toggle').addEventListener('change', async (e) => {
        try {
            const res = await fetch('{{ route('user.webauthn-required') }}', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify({ enabled: e.target.checked })
            });
            await safeJson(res);
            window.location.reload();
        } catch (error) {
            alert(error.message);
            e.target.checked = !e.target.checked;
        }
    });

    document.getElementById('regenerate-codes-btn').addEventListener('click', async (e) => {
        e.target.disabled = true;
        e.target.textContent = 'Generating...';
        try {
            const res = await fetch('{{ route('recovery.generate') }}', {
                method: 'POST',
                headers: jsonHeaders()
            });
            const data = await safeJson(res);
            const list = document.getElementById('recovery-codes-list');
            list.innerHTML = '';
            data.codes.forEach(code => {
                const chip = document.createElement('span');
                chip.className = 'code-chip';
                chip.textContent = code;
                list.appendChild(chip);
            });
        } catch (error) {
            alert(error.message);
        } finally {
            e.target.disabled = false;
            e.target.textContent = 'Generate New Codes';
        }
    });

    document.querySelectorAll('.delete-passkey-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove this passkey?')) return;
            const id = btn.dataset.id;
            try {
                const res = await fetch('/passkey/delete/' + id, {
                    method: 'DELETE',
                    headers: jsonHeaders()
                });
                if (res.ok || res.redirected) {
                    window.location.reload();
                } else {
                    alert('Could not remove passkey.');
                }
            } catch (error) {
                alert('Something went wrong, please try again.');
            }
        });
    });

    document.getElementById('profile-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = document.getElementById('profile-msg');
        const password = document.getElementById('profile-password').value;
        const passwordConfirm = document.getElementById('profile-password-confirm').value;

        if (password && password !== passwordConfirm) {
            msg.textContent = 'Passwords do not match.';
            msg.style.color = '#ff5c78';
            return;
        }

        const payload = {
            name: document.getElementById('profile-name').value,
            email: document.getElementById('profile-email').value
        };
        if (password) payload.password = password;

        try {
            const res = await fetch('{{ route('user.update-profile') }}', {
                method: 'POST',
                headers: jsonHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await safeJson(res);

            if (data.success) {
                msg.textContent = 'Profile updated successfully.';
                msg.style.color = '#22c55e';
                document.getElementById('profile-password').value = '';
                document.getElementById('profile-password-confirm').value = '';
            } else {
                msg.textContent = data.message || 'Could not update profile.';
                msg.style.color = '#ff5c78';
            }
        } catch (error) {
            msg.textContent = error.message;
            msg.style.color = '#ff5c78';
        }
    });
</script>
</body>
</html>