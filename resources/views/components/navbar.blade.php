@props(['active' => ''])

<nav class="topbar">
    <a class="logo" href="{{ route('dashboard') }}">
        <div class="logo-mark">P</div>
        <div class="logo-text">PARKING<em>SURE</em></div>
    </a>
    
    <button class="hamburger" id="hamburger-btn" aria-label="Menú">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="nav-links" id="nav-links">
        <a class="nb {{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-line nav-icon"></i> Dashboard
        </a>

        <a class="nb {{ $active === 'parqueadero' ? 'active' : '' }}" href="{{ route('parqueadero') }}">
            <i class="fas fa-parking nav-icon"></i> Parqueadero
        </a>

        <a class="nb {{ $active === 'vehiculos' ? 'active' : '' }}" href="{{ route('vehiculos') }}">
            <i class="fas fa-car nav-icon"></i> Vehículos
        </a>

        <a class="nb {{ $active === 'pagos' ? 'active' : '' }}" href="{{ route('pagos') }}">
            <i class="fas fa-credit-card nav-icon"></i> Pagos
        </a>

        {{-- Solo visible para Administrador --}}
        @php
            $user = auth()->user();
            $userWithRole = \App\Models\Personal::join('rol', 'personal.id_rol', '=', 'rol.id_rol')
                ->where('personal.id_personal', $user->id_personal)
                ->select('personal.*', 'rol.nombre_rol')
                ->first();
            $userRole = $userWithRole ? $userWithRole->nombre_rol : null;
        @endphp
        @if($userRole === 'Administrador')
            <a class="nb {{ $active === 'usuarios' ? 'active' : '' }}" href="{{ route('usuarios') }}">
                <i class="fas fa-users nav-icon"></i> Usuarios
            </a>

            <a class="nb {{ $active === 'servicios' ? 'active' : '' }}" href="{{ route('servicios') }}">
                <i class="fas fa-cogs nav-icon"></i> Servicios
            </a>
        @endif

        <a class="nb {{ $active === 'reportes' ? 'active' : '' }}" href="{{ route('reportes') }}">
            <i class="fas fa-chart-bar nav-icon"></i> Reportes
        </a>
    </div>

    <div class="nav-right">
        @php
            $userName = auth()->user()->user ? auth()->user()->user->name : (auth()->user()->usuario ?? 'Usuario');
        @endphp
        <div class="user-avatar">{{ strtoupper(substr($userName, 0, 1)) }}</div>
        <div class="user-info">
            <div class="u-name">{{ $userName }}</div>
            <div class="u-role">{{ $userRole ?? 'Sin rol' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn-logout">Salir</button>
        </form>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger-btn');
        const navLinks = document.getElementById('nav-links');

        if (hamburger && navLinks) {
            hamburger.addEventListener('click', function(e) {
                e.preventDefault();
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Cerrar menú al hacer click en un link
            const links = navLinks.querySelectorAll('.nb');
            links.forEach(link => {
                link.addEventListener('click', function() {
                    hamburger.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        }
    });
</script>
