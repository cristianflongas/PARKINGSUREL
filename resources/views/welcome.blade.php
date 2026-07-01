<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ParkingSure') }} | Sistema de Gestión de Parqueaderos</title>
        <meta name="description" content="Sistema profesional de gestión de parqueaderos con seguridad avanzada, control de accesos y facturación automática">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body style="margin:0;min-height:100vh;background:#f8fafc;color:#111827;font-family:'Inter',sans-serif;line-height:1.6;">
        <div style="display:flex;flex-direction:column;min-height:100vh;">
            <header style="width:100%;max-width:1240px;margin:0 auto;padding:1.45rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                <a href="{{ url('/') }}" style="display:inline-flex;align-items:center;gap:0.9rem;font-weight:800;font-size:1.45rem;letter-spacing:-0.04em;color:inherit;text-decoration:none;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;background:#f7c600;color:#111;font-weight:900;font-size:1.2rem;box-shadow:0 14px 30px rgba(247,198,0,0.2);">P</span>
                    PARKING<span style="color:#4b5563;font-weight:600;">SURE</span>
                </a>
                <div style="display:flex;flex-wrap:wrap;gap:0.9rem;align-items:center;">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:0.85rem 1.4rem;font-weight:700;font-size:0.96rem;text-decoration:none;color:#111827;background:rgba(255,255,255,0.92);border:1px solid rgba(15,23,42,0.08);">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:0.85rem 1.4rem;font-weight:700;font-size:0.96rem;text-decoration:none;color:#0f172a;background:#f7c600;border:1px solid transparent;box-shadow:0 14px 30px rgba(247,198,0,0.22);">Iniciar sesión</a>
                        @endauth
                    @endif
                </div>
            </header>

            <section style="position:relative;overflow:hidden;min-height:72vh;max-height:780px;">
                <div class="carousel-slide" style="position:absolute;inset:0;background-image:url('https://static.tildacdn.com/tild3361-3663-4864-b132-633662383234/photo.png');background-size:cover;background-position:center;opacity:1;transition:opacity 0.8s ease;"></div>
                <div class="carousel-slide" style="position:absolute;inset:0;background-image:url('https://s.yimg.com/ny/api/res/1.2/AKpy5FWvJW2lFblEFk3LAg--/YXBwaWQ9aGlnaGxhbmRlcjt3PTk2MDtoPTU0MDtjZj13ZWJw/https://media.zenfs.com/en/cheezburger_332/d1ff8ee63971cb871743b01b1ad5b5a1');background-size:cover;background-position:center;opacity:0;transition:opacity 0.8s ease;"></div>
                <div class="carousel-slide" style="position:absolute;inset:0;background-image:url('https://i.pinimg.com/originals/83/f2/aa/83f2aa5ee8c8f6b69248941bb09f0bad.jpg');background-size:cover;background-position:center;opacity:0;transition:opacity 0.8s ease;"></div>
                <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,23,42,0.42),rgba(15,23,42,0.7));pointer-events:none;"></div>
                <div style="position:relative;z-index:2;display:flex;flex-direction:column;justify-content:center;min-height:72vh;padding:2rem 1.5rem;max-width:1040px;margin:0 auto;color:#ffffff;">
                    <span style="display:inline-flex;margin-bottom:1.2rem;color:#f7c600;letter-spacing:0.18em;font-size:0.77rem;font-weight:800;text-transform:uppercase;">Plataforma de gestión de parqueaderos</span>
                    <h1 style="margin:0;font-size:clamp(2.8rem,5vw,4.9rem);line-height:0.98;font-weight:900;letter-spacing:-0.05em;">Control inteligente para tu <span style="color:#f7c600;">parking</span></h1>
                    <p style="margin:1.75rem 0 0;font-size:1.05rem;color:rgba(255,255,255,0.88);max-width:680px;">Sistema seguro de administración de parqueaderos con control de accesos, facturación automática y seguimiento en tiempo real para una operación más rentable.</p>
                    <div style="margin-top:2.5rem;display:flex;flex-wrap:wrap;gap:1rem;">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:0.85rem 1.4rem;font-weight:700;font-size:0.96rem;text-decoration:none;color:#0f172a;background:#f7c600;border:1px solid transparent;box-shadow:0 14px 30px rgba(247,198,0,0.22);">Ir al panel</a>
                            @else
                                <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:0.85rem 1.4rem;font-weight:700;font-size:0.96rem;text-decoration:none;color:#0f172a;background:#f7c600;border:1px solid transparent;box-shadow:0 14px 30px rgba(247,198,0,0.22);">Acceder al sistema</a>
                            @endauth
                        @endif
                    </div>
                </div>
                <div style="position:absolute;bottom:1.5rem;left:1.5rem;display:flex;gap:0.75rem;z-index:3;">
                    <button type="button" data-carousel-prev style="border:none;cursor:pointer;background:rgba(255,255,255,0.14);color:#ffffff;width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;transition:background 150ms ease;">‹</button>
                    <button type="button" data-carousel-next style="border:none;cursor:pointer;background:rgba(255,255,255,0.14);color:#ffffff;width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;transition:background 150ms ease;">›</button>
                </div>
            </section>

            <section style="max-width:1240px;margin:0 auto;padding:4rem 1.5rem;">
                <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;max-width:760px;margin:0 auto 2.5rem;text-align:center;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;padding:0.55rem 1rem;border-radius:999px;background:rgba(247,198,0,0.18);color:#92400e;font-size:0.8rem;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;">Características clave</span>
                    <h2 style="margin:0;font-size:2.8rem;font-weight:900;line-height:1.05;color:#111827;">Gestiona tu parqueadero con claridad, rapidez y total seguridad</h2>
                    <p style="margin:0;color:#475569;font-size:1.05rem;line-height:1.85;">Controla accesos, facturación y ocupación desde una misma plataforma diseñada para tu operación diaria.</p>
                </div>

                <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(3,minmax(240px,1fr));">
                    <article style="background:#ffffff;border:1px solid rgba(15,23,42,0.08);border-radius:32px;padding:2.2rem;min-height:300px;display:flex;flex-direction:column;gap:1.2rem;box-shadow:0 24px 60px rgba(15,23,42,0.07);">
                        <div style="width:56px;height:56px;border-radius:18px;display:grid;place-items:center;font-size:1.55rem;background:rgba(37,99,235,0.16);color:#1d4ed8;">🚗</div>
                        <h3 style="margin:0;font-size:1.25rem;font-weight:900;color:#0f172a;">Flujo rápido de vehículos</h3>
                        <p style="margin:0;color:#475569;line-height:1.8;">Optimiza ingresos con un flujo ágil y órdenes claras para el equipo de parqueadero.</p>
                    </article>
                    <article style="background:#ffffff;border:1px solid rgba(15,23,42,0.08);border-radius:32px;padding:2.2rem;min-height:300px;display:flex;flex-direction:column;gap:1.2rem;box-shadow:0 24px 60px rgba(15,23,42,0.07);">
                        <div style="width:56px;height:56px;border-radius:18px;display:grid;place-items:center;font-size:1.55rem;background:rgba(16,185,129,0.16);color:#047857;">🔐</div>
                        <h3 style="margin:0;font-size:1.25rem;font-weight:900;color:#0f172a;">Accesos controlados</h3>
                        <p style="margin:0;color:#475569;line-height:1.8;">Define roles y permisos claros, con registros disponibles para cualquier auditoría.</p>
                    </article>
                    <article style="background:#ffffff;border:1px solid rgba(15,23,42,0.08);border-radius:32px;padding:2.2rem;min-height:300px;display:flex;flex-direction:column;gap:1.2rem;box-shadow:0 24px 60px rgba(15,23,42,0.07);">
                        <div style="width:56px;height:56px;border-radius:18px;display:grid;place-items:center;font-size:1.55rem;background:rgba(234,88,12,0.16);color:#9a3412;">📈</div>
                        <h3 style="margin:0;font-size:1.25rem;font-weight:900;color:#0f172a;">Informes instantáneos</h3>
                        <p style="margin:0;color:#475569;line-height:1.8;">Visualiza ocupación, ingresos y desempeño en segundos para tomar decisiones rápidas.</p>
                    </article>
                </div>
            </section>

            <section style="max-width:1240px;margin:0 auto;padding:4rem 1.5rem;">
                <div style="background:linear-gradient(180deg,rgba(15,23,42,0.95),rgba(15,23,42,0.88));border-radius:40px;box-shadow:0 40px 90px rgba(15,23,42,0.18);overflow:hidden;">
                    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:2rem;align-items:center;padding:3rem 2.5rem;">
                        <div>
                            <p style="margin:0 0 1rem;font-size:0.9rem;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#fbbf24;">Resultados que puedes medir</p>
                            <h3 style="margin:0;font-size:2.7rem;line-height:1.05;font-weight:900;color:#ffffff;">Una apariencia más suave y fácil de leer</h3>
                            <p style="margin:1.5rem 0 0;color:rgba(248,250,252,0.82);font-size:1rem;line-height:1.85;">Un panel con bloques redondeados y espaciado equilibrado para presentar tus métricas sin peso visual.</p>
                        </div>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(2,minmax(0,1fr));">
                            <div style="padding:1.7rem 1.5rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.14);border-radius:28px;text-align:center;">
                                <p style="margin:0 0 0.5rem;font-size:2.3rem;font-weight:900;letter-spacing:-0.04em;color:#fbbf24;">99.9%</p>
                                <p style="margin:0;color:rgba(248,250,252,0.8);text-transform:uppercase;letter-spacing:0.12em;font-size:0.82rem;">Disponibilidad</p>
                            </div>
                            <div style="padding:1.7rem 1.5rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.14);border-radius:28px;text-align:center;">
                                <p style="margin:0 0 0.5rem;font-size:2.3rem;font-weight:900;letter-spacing:-0.04em;color:#fbbf24;">24/7</p>
                                <p style="margin:0;color:rgba(248,250,252,0.8);text-transform:uppercase;letter-spacing:0.12em;font-size:0.82rem;">Soporte</p>
                            </div>
                            <div style="padding:1.7rem 1.5rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.14);border-radius:28px;text-align:center;">
                                <p style="margin:0 0 0.5rem;font-size:2.3rem;font-weight:900;letter-spacing:-0.04em;color:#fbbf24;">1000+</p>
                                <p style="margin:0;color:rgba(248,250,252,0.8);text-transform:uppercase;letter-spacing:0.12em;font-size:0.82rem;">Vehículos gestionados</p>
                            </div>
                            <div style="padding:1.7rem 1.5rem;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.14);border-radius:28px;text-align:center;">
                                <p style="margin:0 0 0.5rem;font-size:2.3rem;font-weight:900;letter-spacing:-0.04em;color:#fbbf24;">≤2s</p>
                                <p style="margin:0;color:rgba(248,250,252,0.8);text-transform:uppercase;letter-spacing:0.12em;font-size:0.82rem;">Tiempo de respuesta</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer style="padding:2rem 1.5rem;text-align:center;color:#94a3b8;max-width:1240px;margin:0 auto;">
                <p style="margin:0.35rem 0;font-size:0.96rem;">&copy; {{ date('Y') }} {{ config('app.name', 'ParkingSure') }} - Sistema profesional de gestión de parqueaderos.</p>
                <p style="margin:0.35rem 0;font-size:0.96rem;">Seguridad, control y confianza en cada turno.</p>
            </footer>
        </div>
        <script>
            (function(){
                const slides = document.querySelectorAll('.carousel-slide');
                const prev = document.querySelector('[data-carousel-prev]');
                const next = document.querySelector('[data-carousel-next]');
                let current = 0;
                const change = index => {
                    slides.forEach((slide, i) => slide.style.opacity = i === index ? '1' : '0');
                    current = index;
                };
                prev && prev.addEventListener('click', () => change((current - 1 + slides.length) % slides.length));
                next && next.addEventListener('click', () => change((current + 1) % slides.length));
                setInterval(() => change((current + 1) % slides.length), 7000);
            })();
        </script>
    </body>
</html>
