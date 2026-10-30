<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Matriz LA/FT')</title>

    {{-- Fuentes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    {{-- Estilos específicos de cada vista --}}
    @stack('styles')
</head>
<body class="guest-body">

    {{-- Fondo ambiental --}}
    <div class="bg-ambient" aria-hidden="true">
        <div class="bg-glow bg-glow--top"></div>
        <div class="bg-glow bg-glow--bottom"></div>
        <div class="bg-grid"></div>
    </div>

    {{-- Encabezado institucional --}}
    <header class="topbar">
        <div class="topbar__brand">
            <span class="topbar__dot"></span>
            <span class="topbar__title">Proyecto del curso de Seguridad &amp; Auditoria de Sistemas</span>
        </div>
        <div class="topbar__meta">
            <span class="topbar__badge">ENTORNO SEGURO</span>
            <span>Seccion A</span>
        </div>
    </header>

    {{-- Contenido de la vista --}}
    <main class="guest-main">
        @yield('content')
    </main>

    {{-- Pie de página --}}
    <footer class="guest-footer">
        <p>Prototipo académico · sin conexión a sistemas productivos · datos 100 % sintéticos</p>
    </footer>

    {{-- Scripts específicos de cada vista --}}
    @stack('scripts')
</body>
</html>
