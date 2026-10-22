@php
    /*
     * Menú lateral. Cada item: [texto, nombre de ruta, patrón para marcar "activo", path del icono SVG].
     * Si la ruta todavía no existe, el enlace queda en "#" para que no falle.
     * Ajusta los nombres de ruta a los de tu routes/web.php.
     */
    $menu = [
        'Operación' => [
            ['Tablero',              'dashboard',          'dashboard*',    'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
            ['Evaluaciones',         'evaluaciones.index', ['evaluaciones*', 'validaciones*', 'eventos*', 'resultados*', 'decisiones*'], 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['Clientes',             'clientes.index',     'clientes*',     'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['Listas y coincidencias','coincidencias.index',      'coincidencias*',       'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
        ],
        'Parámetros' => [
            ['Versiones',            'versiones.index',    'versiones*',    'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['Catálogos',            'catalogos.index',    'catalogos*',    'M4 6h16M4 10h16M4 14h16M4 18h16'],
        ],
        'Control' => [
            ['Sensibilidad',         'sensibilidad.index', 'sensibilidad*', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
            ['Bitácora',             'bitacora.index',     'bitacora*',     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ],
        'Administración' => [
            ['Usuarios y roles',     'usuarios.index',     'usuarios*',     'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ],
    ];

    $usuario  = auth()->user();
    $nombre   = $usuario->name ?? 'Oficial Cumplimiento';
    $iniciales = strtoupper(mb_substr($nombre, 0, 2));
@endphp

<aside class="sidebar" aria-label="Navegación principal">
    <div class="sidebar__body">

        {{-- Marca institucional --}}
        <div class="brand">
            <div class="brand__logo">
                @if (file_exists(public_path('images/logo.png')))
                    <img src="{{ asset('images/logo.png') }}" alt="Matriz LA/FT">
                @else
                    M
                @endif
            </div>
            <div>
                <h2 class="brand__title">Matriz LA/FT</h2>
                <p class="brand__subtitle">Prototipo Academico</p>
            </div>
        </div>

        {{-- Navegación --}}
        <nav class="nav">
            @foreach ($menu as $seccion => $items)
                <div>
                    <span class="nav__label">{{ $seccion }}</span>
                    <ul class="nav__list">
                        @foreach ($items as [$texto, $ruta, $patron, $icono])
                            <li>
                                <a href="{{ Route::has($ruta) ? route($ruta) : '#' }}"
                                   class="nav__link {{ request()->routeIs($patron) ? 'is-active' : '' }}">
                                    <svg class="nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="{{ $icono }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                    </svg>
                                    {{ $texto }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
    </div>

    {{-- Usuario actual + cerrar sesión --}}
    <div class="sidebar__user">
        <div class="sidebar__user-info">
            <div class="avatar">{{ $iniciales }}</div>
            <span class="sidebar__user-name">{{ $nombre }}</span>
        </div>

        <div class="sidebar__user-actions">
            {{-- POST + CSRF. Ajusta el nombre de la ruta 'logout' si el tuyo es otro. --}}
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <button type="submit" class="btn-logout" title="Cerrar sesión" aria-label="Cerrar sesión">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                </button>
            </form>
            <span class="status-dot" title="Servidor en línea"></span>
        </div>
    </div>
</aside>
