<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkingSure — Nueva Contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f3f7fb 0%, #edf1f7 100%);
            font-family: 'Inter', sans-serif;
            color: #0a0a0a;
        }
        .auth-shell {
            width: min(540px, 92vw);
            background: #ffffff;
            border: 1px solid #e6e8eb;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
            color: #fff;
            padding: 30px 30px 22px;
            border-bottom: 3px solid #d7a93a;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 900;
            letter-spacing: -0.04em;
            font-size: 1.05rem;
        }
        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #d7a93a;
            color: #111827;
            font-size: 1rem;
            font-weight: 900;
        }
        .auth-body {
            padding: 30px;
        }
        .title {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: -0.06em;
            margin: 0 0 10px;
            color: #0a0a0a;
        }
        .subtitle {
            color: #5f6f82;
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0 0 22px;
        }
        .alert {
            margin-bottom: 18px;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        .alert-error {
            background: #fff1f1;
            border: 1px solid rgba(154, 45, 45, 0.2);
            color: #9a2d2d;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #404040;
        }
        input {
            width: 100%;
            border: 1px solid #dfe7f1;
            background: #fff;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.98rem;
            color: #102033;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        input:focus {
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(215, 169, 58, 0.18);
        }
        .btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            background: linear-gradient(180deg, #d7a93a 0%, #c99520 100%);
            color: #111827;
            font-weight: 800;
            font-size: 0.95rem;
            padding: 13px 18px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 18px rgba(215, 169, 58, 0.2);
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(215, 169, 58, 0.25);
        }
        .actions {
            margin-top: 24px;
        }
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #404040;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover {
            color: #111827;
        }
        .spacer { height: 12px; }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-header">
            <div class="brand">
                <span class="brand-mark">P</span>
                <span>PARKING<span style="font-weight:500;color:#94a3b8;">SURE</span></span>
            </div>
        </div>

        <div class="auth-body">
            <h1 class="title">Crear nueva contraseña</h1>
            <p class="subtitle">Define una nueva contraseña segura para continuar con tu acceso al sistema.</p>

            @if ($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div style="margin-bottom:18px;">
                    <label for="email">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus>
                </div>

                <div style="margin-bottom:18px;">
                    <label for="password">Nueva contraseña</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                </div>

                <div style="margin-bottom:18px;">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Actualizar contraseña</button>
                </div>
            </form>

            <a href="{{ route('login') }}" class="back-link">← Volver al inicio de sesión</a>
        </div>
    </div>
</body>
</html>
