<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Acceso</title>
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body style="margin:0;min-height:100vh;font-family:'Inter',sans-serif;background:#f0f2f5;color:#0a0a0a;display:flex;flex-wrap:wrap;">
  <div style="flex:0.95;min-width:300px;background:#ffffff;border-right:1px solid #e0e0e0;display:flex;flex-direction:column;gap:28px;padding:32px 40px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-90px;left:-90px;width:320px;height:320px;background:radial-gradient(circle,rgba(255,215,0,0.14) 0%,transparent 70%);pointer-events:none;"></div>
    <div style="position:relative;z-index:1;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:32px;">
        <div style="width:36px;height:36px;background:#ffd700;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:16px;color:#0a0a0a;">P</div>
        <div style="font-size:22px;font-weight:900;letter-spacing:-0.5px;color:#0a0a0a;">PARKING<em style="font-style:normal;font-weight:500;color:#737373;">SURE</em></div>
      </div>
      <div style="font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#737373;margin-bottom:12px;">Sistema de Gestión Profesional</div>
      <h1 style="font-size:30px;font-weight:900;line-height:1.15;color:#0a0a0a;letter-spacing:-0.8px;margin:0 0 16px;">Administración centralizada de tu parqueadero</h1>
      <p style="font-size:14px;line-height:1.5;color:#404040;max-width:320px;margin:0 0 2px;">Plataforma integral diseñada para brindar confianza y eficiencia en la gestión de ingresos, módulos y facturación.</p>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;position:relative;z-index:1;mt-2%">
      <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:#404040;font-weight:500;padding:14px;background:#f0f2f5;border:1px solid #e0e0e0;border-radius:10px;">
        <div style="width:34px;height:34px;background:#ffffff;border:1px solid #e0e0e0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;">🅿️</div>
        <div>
          <strong style="display:block;font-size:13px;font-weight:700;color:#0a0a0a;margin-bottom:2px;">Control de Módulos</strong>
          <span style="font-size:12px;color:#404040;font-weight:400;">Estado y ocupación en tiempo real</span>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:#404040;font-weight:500;padding:14px;background:#f0f2f5;border:1px solid #e0e0e0;border-radius:10px;">
        <div style="width:34px;height:34px;background:#ffffff;border:1px solid #e0e0e0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;">🧾</div>
        <div>
          <strong style="display:block;font-size:13px;font-weight:700;color:#0a0a0a;margin-bottom:2px;">Facturación Segura</strong>
          <span style="font-size:12px;color:#404040;font-weight:400;">Generación automática de comprobantes</span>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:12px;font-size:14px;color:#404040;font-weight:500;padding:14px;background:#f0f2f5;border:1px solid #e0e0e0;border-radius:10px;">
        <div style="width:34px;height:34px;background:#ffffff;border:1px solid #e0e0e0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;">📊</div>
        <div>
          <strong style="display:block;font-size:13px;font-weight:700;color:#0a0a0a;margin-bottom:2px;">Reportes Precisos</strong>
          <span style="font-size:12px;color:#404040;font-weight:400;">Métricas y datos exportables al instante</span>
        </div>
      </div>
    </div>

    <div style="position:relative;z-index:1;">
      <p style="font-size:12px;color:#737373;font-weight:500;margin:0;">© {{ date('Y') }} ParkingSure · Todos los derechos reservados</p>
    </div>
  </div>

  <div style="flex:1;min-width:320px;display:flex;align-items:center;justify-content:center;padding:52px 80px;background:#f8fafc;">
    <div style="width:100%;max-width:420px;background:#ffffff;border:1px solid #e6e8eb;border-radius:28px;box-shadow:0 30px 80px rgba(15,23,42,0.08);padding:40px;">
      <div style="margin-bottom:40px;">
        <div style="font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#737373;margin-bottom:8px;">Acceso Seguro</div>
        <h2 style="font-size:32px;font-weight:900;letter-spacing:-1px;color:#0a0a0a;margin:0 0 12px;">Iniciar Sesión</h2>
        <div style="width:40px;height:4px;background:#ffd700;margin-bottom:24px;"></div>
        <p style="font-size:14px;color:#404040;margin:0 0 0;">Ingresa tus credenciales para continuar.</p>
      </div>

      @if (session('status'))
        <div style="display:flex;align-items:center;gap:8px;background:#ebf8ff;border:1px solid rgba(59,130,246,0.25);border-radius:12px;padding:12px 16px;color:#1d4ed8;font-size:13px;margin-bottom:20px;">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div style="display:flex;align-items:center;gap:8px;background:#ffebee;border:1px solid rgba(198,40,40,0.2);border-radius:12px;padding:12px 16px;color:#c62828;font-size:13px;font-weight:500;margin-bottom:20px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:20px;">
        @csrf
        <div style="display:flex;flex-direction:column;gap:8px;">
          <label for="usuario" style="font-size:12px;font-weight:600;color:#404040;">Usuario</label>
          <div style="position:relative;">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#737373;display:flex;align-items:center;"> 
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input id="usuario" name="usuario" type="text" value="{{ old('usuario') }}" placeholder="Ingresa tu usuario" autocomplete="username" required style="width:85%;padding:12px 14px 12px 42px;border:1px solid #cccccc;border-radius:8px;background:#ffffff;color:#0a0a0a;font-size:14px;outline:none;transition: border-color 0.2s ease;" onfocus="this.style.borderColor='#0a0a0a'" onblur="this.style.borderColor='#cccccc'">
          </div>
          @error('usuario')
            <p style="margin:0;color:#c62828;font-size:13px;font-weight:500;">{{ $message }}</p>
          @enderror
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;">
          <label for="password" style="font-size:12px;font-weight:600;color:#404040;">Contraseña</label>
          <div style="position:relative;">
            <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#737373;display:flex;align-items:center;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required style="width:85%;padding:12px 14px 12px 42px;border:1px solid #cccccc;border-radius:8px;background:#ffffff;color:#0a0a0a;font-size:14px;outline:none;transition: border-color 0.2s ease;" onfocus="this.style.borderColor='#0a0a0a'" onblur="this.style.borderColor='#cccccc'">
          </div>
          @error('password')
            <p style="margin:0;color:#c62828;font-size:13px;font-weight:500;">{{ $message }}</p>
          @enderror
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" style="font-size:13px;font-weight:500;color:#404040;">¿Olvidaste tu contraseña?</a>
          @endif
        </div>

        <button type="submit" style="width:100%;padding:14px 16px;border:none;border-radius:8px;background:#ffd700;color:#0a0a0a;font-size:15px;font-weight:700;cursor:pointer;transition:background 0.2s ease;">INGRESAR AL SISTEMA</button>
      </form>

      <div style="text-align:center;margin-top:24px;">
        <a href="{{ url('/') }}" style="font-size:14px;font-weight:600;color:#404040;">← Volver al sitio web</a>
      </div>

      <p style="font-size:12px;color:#737373;text-align:center;margin-top:32px;font-weight:500;">Sistema exclusivo para personal autorizado</p>
    </div>
  </div>
</body>
</html>
