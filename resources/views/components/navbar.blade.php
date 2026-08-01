@props(['active' => ''])

@php
    $user = auth()->user();
    $userWithRole = \App\Models\Personal::join('rol', 'personal.id_rol', '=', 'rol.id_rol')
        ->where('personal.id_personal', $user->id_personal)
        ->select('personal.*', 'rol.nombre_rol')
        ->first();
    $userRole    = $userWithRole ? $userWithRole->nombre_rol : null;
    $userName    = $user->user ? $user->user->nombre : ($user->usuario ?? 'Usuario');
    $isAdmin     = $userRole === 'Administrador';
    $userInitial = strtoupper(substr($userName, 0, 1));
@endphp

<nav class="topbar">
 
    {{-- Logo --}}
    <a class="nav-logo" href="{{ route('dashboard') }}">
        <div class="nav-logo-mark">P</div>
        <div class="nav-logo-text">PARKING<em>SURE</em></div>
    </a>

    {{-- Botón hamburguesa (móvil) --}}
    <button class="nav-hamburger" id="hamburger-btn" aria-label="Menú" aria-expanded="false">
        <span class="nav-ham-line"></span>
        <span class="nav-ham-line"></span>
        <span class="nav-ham-line"></span>
    </button>

    {{-- Enlacces de navegación --}}
    <div class="nav-links" id="nav-links" role="navigation">

        <a class="nav-link {{ $active === 'dashboard'   ? 'nav-link--active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-line nav-link-icon"></i>
            <span class="nav-link-label">Dashboard</span>
        </a>

        <a class="nav-link {{ $active === 'parqueadero' ? 'nav-link--active' : '' }}" href="{{ route('parqueadero') }}">
            <i class="fas fa-parking nav-link-icon"></i>
            <span class="nav-link-label">Parqueadero</span>
        </a>

        <a class="nav-link {{ $active === 'vehiculos'   ? 'nav-link--active' : '' }}" href="{{ route('vehiculos') }}">
            <i class="fas fa-car nav-link-icon"></i>
            <span class="nav-link-label">Vehículos</span>
        </a>

        <a class="nav-link {{ $active === 'pagos'       ? 'nav-link--active' : '' }}" href="{{ route('pagos') }}">
            <i class="fas fa-credit-card nav-link-icon"></i>
            <span class="nav-link-label">Pagos</span>
        </a>

        <a class="nav-link {{ $active === 'reportes'    ? 'nav-link--active' : '' }}" href="{{ route('reportes') }}">
            <i class="fas fa-chart-bar nav-link-icon"></i>
            <span class="nav-link-label">Reportes</span>
        </a>

        @if($isAdmin)
            <div class="nav-divider" aria-hidden="true"></div>

            <a class="nav-link nav-link--admin {{ $active === 'usuarios'  ? 'nav-link--active' : '' }}" href="{{ route('usuarios') }}">
                <i class="fas fa-users nav-link-icon"></i>
                <span class="nav-link-label">Usuarios</span>
            </a>

            <a class="nav-link nav-link--admin {{ $active === 'servicios' ? 'nav-link--active' : '' }}" href="{{ route('servicios') }}">
                <i class="fas fa-cogs nav-link-icon"></i>
                <span class="nav-link-label">Servicios</span>
            </a>
        @endif

    </div>

    {{-- Perfil y salida --}}
    <div class="nav-right">
        <div class="nav-user-avatar">{{ $userInitial }}</div>
        <div class="nav-user-info">
            <span class="nav-user-name">{{ $userName }}</span>
            <span class="nav-user-role">{{ $userRole ?? 'Sin rol' }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Salir</span>
            </button>
        </form>
    </div>

</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hamburger = document.getElementById('hamburger-btn');
    const navLinks  = document.getElementById('nav-links');

    if (!hamburger || !navLinks) return;

    hamburger.addEventListener('click', function () {
        const isOpen = navLinks.classList.toggle('nav-links--open');
        hamburger.classList.toggle('nav-hamburger--open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen);
    });

    navLinks.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            navLinks.classList.remove('nav-links--open');
            hamburger.classList.remove('nav-hamburger--open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>
