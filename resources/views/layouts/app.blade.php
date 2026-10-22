<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Matriz LA/FT - Prototipo Academico')</title>

    {{-- Fuentes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Estilos compartidos (base, sidebar, topbar) --}}
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">

    {{-- Estilos específicos de cada vista --}}
    @stack('styles')
</head>
<body class="app-body">

    {{-- Menú lateral --}}
    @include('partials.sidebar')

    <div class="app-main">

        {{-- Menú superior --}}
        @include('partials.topbar')

        {{-- Contenido de la vista --}}
        <main class="app-content">
            @yield('content')
        </main>
    </div>

    {{-- Scripts específicos de cada vista --}}
    @stack('scripts')
</body>
</html>
