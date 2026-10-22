@extends('layouts.app')

@php
    // $evaluacionId, $evaluacionCodigo, $siguienteEvento, $clientes y las opciones llegan desde EventosController@create.
    // Si no llegan, la vista se muestra igual, sin clientes.
    $evaluacionId     = $evaluacionId ?? null;
    $evaluacionCodigo = $evaluacionCodigo ?? '—';
    $siguienteEvento  = $siguienteEvento ?? 'EV-001';
    $clientes         = $clientes ?? [];

    $monedas = $monedas ?? ['USD' => 'USD · Dólar', 'EUR' => 'EUR · Euro', 'CRC' => 'CRC · Colones'];
    $productos = $productos ?? [
        'intl-transfers' => 'Transferencias internacionales',
        'cash-deposits' => 'Depósitos en efectivo concentrados',
        'trade-financing' => 'Cartas de crédito / Comercio exterior',
        'crypto-settlement' => 'Liquidación de criptoactivos'
    ];
    $canales = $canales ?? [
        'digital' => 'Vinculación completamente digital',
        'branch' => 'Atención presencial en agencia',
        'broker' => 'Intermediario externo / Broker'
    ];
    $jurisdicciones = $jurisdicciones ?? [
        'high-risk' => 'Jurisdicción de mayor riesgo',
        'standard' => 'Jurisdicción estándar / Cooperante',
        'low-risk' => 'Jurisdicción local baja exposición'
    ];
    $tiposRiesgo = $tiposRiesgo ?? ['LA', 'FT', 'LA/FT'];

    $clienteSel = old('cliente', array_key_first($clientes));
    $cli = $clientes[$clienteSel] ?? (reset($clientes) ?: [
        'nombre' => '—', 'tipo' => '—', 'desde' => '—', 'ingresos' => false, 'pep' => false, 'coincidencia' => null, 'previos' => 0
    ]);
    $descripcion = old('descripcion', '');
    $hayCoincidencia = !empty($cli['coincidencia']);
    $confirmada = str_contains($cli['coincidencia'] ?? '', 'CONFIRMADA');
@endphp

@section('title', $evaluacionCodigo . ' · Registrar evento · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/eventos.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li>Operación</li>
                <li class="breadcrumb__sep">/</li>
                <li><a href="{{ Route::has('evaluaciones.index') ? route('evaluaciones.index') : '#' }}">Evaluaciones</a>
                </li>
                <li class="breadcrumb__sep">/</li>
                <li><a href="{{ Route::has('evaluaciones.show') && $evaluacionId ? route('evaluaciones.show', $evaluacionId) : '#' }}"
                        class="breadcrumb__link">{{ $evaluacionCodigo }}</a></li>
                <li class="breadcrumb__sep">/</li>
                <li class="breadcrumb__current">Nuevo evento</li>
            </ol>
        </nav>
        <h1 class="page-title">Registrar evento</h1>
        <p class="page-subtitle">Modo analista · Formulario de riesgo operacional</p>
    </header>

    <div class="page-body">

        {{-- Mensajes del controlador --}}
        @if (session('success'))
            <div class="alert alert--ok" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="layout">

            {{-- Formulario principal --}}
            <section class="panel" aria-label="Formulario de evento">
                {{-- Guarda en EventosController@store (ruta eventos.store); después pasa a calificar el evento --}}
                <form method="POST" action="{{ Route::has('eventos.store') ? route('eventos.store') : '#' }}"
                    id="form-evento" class="form">
                    @csrf
                    <input type="hidden" name="evaluacion" value="{{ $evaluacionId }}">

                    {{-- Código + Cliente --}}
                    <div class="row row--code">
                        <div>
                            <label class="field-label" for="codigo">Código</label>
                            <div class="input-wrap">
                                <input type="text" id="codigo" class="control control--readonly"
                                    value="{{ $siguienteEvento }}" readonly>
                                <span class="input-tag">Auto</span>
                            </div>
                        </div>

                        <div>
                            <label class="field-label field-label--row" for="cliente">
                                <span>Cliente <span class="required">*</span></span>
                                <a href="{{ Route::has('clientes.create') ? route('clientes.create') : '#' }}" class="link-inline">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                    Nuevo cliente
                                </a>
                            </label>
                            <select id="cliente" name="cliente" class="control" required>
                                @foreach ($clientes as $codigo => $c)
                                    <option value="{{ $codigo }}" data-nombre="{{ $c['nombre'] }}"
                                        data-tipo="{{ $c['tipo'] }}" data-desde="{{ $c['desde'] }}"
                                        data-ingresos="{{ $c['ingresos'] ? 1 : 0 }}" data-pep="{{ $c['pep'] ? 1 : 0 }}"
                                        data-coincidencia="{{ $c['coincidencia'] ?? '' }}"
                                        data-previos="{{ $c['previos'] }}"
                                        {{ $clienteSel === $codigo ? 'selected' : '' }}>{{ $codigo }} ·
                                        {{ $c['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tipo de riesgo + Fecha --}}
                    <div class="row row--type type-box">
                        <div>
                            <span class="field-label">Tipo de riesgo <span class="required">*</span></span>
                            <div class="radio-group">
                                @foreach ($tiposRiesgo as $tipo)
                                    <label class="radio-pill">
                                        <input type="radio" name="tipo_riesgo" value="{{ $tipo }}"
                                            {{ old('tipo_riesgo', 'LA/FT') === $tipo ? 'checked' : '' }} required>
                                        <span>{{ $tipo }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="field-label" for="fecha">Fecha del evento</label>
                            <input type="date" id="fecha" name="fecha" class="control control--mono"
                                value="{{ old('fecha', date('Y-m-d')) }}">
                        </div>
                    </div>

                    {{-- Monto, Moneda, Producto --}}
                    <div class="row row--amount">
                        <div>
                            <label class="field-label" for="monto">Monto <span class="required">*</span></label>
                            <div class="input-prefix">
                                <span>$</span>
                                <input type="text" id="monto" name="monto" class="control control--mono"
                                    inputmode="decimal" placeholder="0.00" value="{{ old('monto') }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="field-label" for="moneda">Moneda</label>
                            <select id="moneda" name="moneda" class="control control--mono">
                                @foreach ($monedas as $valor => $texto)
                                    <option value="{{ $valor }}"
                                        {{ old('moneda', 'USD') === $valor ? 'selected' : '' }}>{{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="producto">Producto</label>
                            <select id="producto" name="producto" class="control">
                                @foreach ($productos as $valor => $texto)
                                    <option value="{{ $valor }}"
                                        {{ old('producto') === $valor ? 'selected' : '' }}>
                                        {{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Canal + Jurisdicción --}}
                    <div class="row row--two">
                        <div>
                            <label class="field-label" for="canal">Canal</label>
                            <select id="canal" name="canal" class="control">
                                @foreach ($canales as $valor => $texto)
                                    <option value="{{ $valor }}"
                                        {{ old('canal') === $valor ? 'selected' : '' }}>{{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="jurisdiccion">Jurisdicción</label>
                            <select id="jurisdiccion" name="jurisdiccion" class="control">
                                @foreach ($jurisdicciones as $valor => $texto)
                                    <option value="{{ $valor }}"
                                        {{ old('jurisdiccion') === $valor ? 'selected' : '' }}>
                                        {{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="field-label field-label--row" for="descripcion">
                            <span>Descripción del evento <span class="required">*</span></span>
                            <span class="field-count" id="descripcion-count">{{ mb_strlen($descripcion) }}
                                caracteres</span>
                        </label>
                        <textarea id="descripcion" name="descripcion" rows="3" class="control control--textarea" required>{{ $descripcion }}</textarea>
                    </div>

                    {{-- Frecuencia --}}
                    <div>
                        <label class="field-label" for="frecuencia">Frecuencia (descripción)</label>
                        <textarea id="frecuencia" name="frecuencia" rows="2" class="control control--textarea">{{ old('frecuencia') }}</textarea>
                    </div>

                    {{-- Acciones --}}
                    <div class="form__actions">
                        <button type="submit" class="btn-secondary">
                            Guardar y calificar
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" />
                            </svg>
                        </button>
                        <a href="{{ Route::has('evaluaciones.show') && $evaluacionId ? route('evaluaciones.show', $evaluacionId) : (Route::has('evaluaciones.index') ? route('evaluaciones.index') : '#') }}"
                            class="btn-ghost">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" />
                            </svg>
                            Cancelar
                        </a>
                    </div>
                </form>
            </section>

            {{-- Columna derecha --}}
            <div class="side">

                {{-- Cliente seleccionado --}}
                <section class="panel" aria-label="Cliente seleccionado">
                    <div class="card__head">
                        <h2 class="card__title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            Cliente seleccionado
                        </h2>
                        <span class="tag tag--blue">ID activo</span>
                    </div>
                    <div class="card__body">
                        <div class="data-box">
                            <div>
                                <span class="data-label">Cliente</span>
                                <span class="data-value data-value--name" data-cli="nombre">{{ $clienteSel }} ·
                                    {{ $cli['nombre'] }}</span>
                            </div>
                            <div class="data-grid">
                                <div>
                                    <span class="data-label">Tipo / Jurisdicción</span>
                                    <span class="data-value" data-cli="tipo">{{ $cli['tipo'] }}</span>
                                </div>
                                <div>
                                    <span class="data-label">Relación desde</span>
                                    <span class="data-value data-value--mono" data-cli="desde">{{ $cli['desde'] }}</span>
                                </div>
                            </div>
                            <div class="data-grid">
                                <div>
                                    <span class="data-label">Ingresos verificados</span>
                                    <span class="dot-status {{ $cli['ingresos'] ? 'is-ok' : 'is-bad' }}"
                                        data-cli="ingresos">{{ $cli['ingresos'] ? 'Sí' : 'No' }}</span>
                                </div>
                                <div>
                                    <span class="data-label">PEP</span>
                                    <span class="dot-status {{ $cli['pep'] ? 'is-warn' : 'is-neutral' }}"
                                        data-cli="pep">{{ $cli['pep'] ? 'Sí' : 'No' }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ Route::has('clientes.index') ? route('clientes.index', ['cliente' => $clienteSel]) : '#' }}"
                            data-cli="ficha" data-base="{{ Route::has('clientes.index') ? route('clientes.index') : '' }}" class="link-inline">
                            Ver ficha completa
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                        </a>
                    </div>
                </section>

                {{-- Verificación en listas --}}
                <section class="panel" aria-label="Verificación en listas">
                    <div class="card__head">
                        <h2 class="card__title card__title--alert">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            Verificación en listas
                        </h2>
                        <span class="tag {{ $hayCoincidencia ? 'tag--rose' : 'tag--ok' }}"
                            data-lista="etiqueta">{{ $hayCoincidencia ? 'Alerta roja' : 'Sin alerta' }}</span>
                    </div>
                    <div class="card__body">

                        <div class="alert-red" data-lista="alerta" {{ $hayCoincidencia ? '' : 'hidden' }}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <div>
                                <span class="alert-red__title" data-lista="titulo">{{ $confirmada ? 'Coincidencia confirmada' : 'Coincidencia parcial' }}</span>
                                <p class="alert-red__text" data-cli="coincidencia">{{ $cli['coincidencia'] }}</p>
                            </div>
                        </div>

                        <div class="alert-ok" data-lista="limpio" {{ $hayCoincidencia ? 'hidden' : '' }}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <span>Sin coincidencias en listas.</span>
                        </div>

                        <div class="sugg">
                            <div class="sugg__row" data-lista="sugerencia" {{ $confirmada ? '' : 'hidden' }}>
                                <span class="sugg__label">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                    </svg>
                                    Sugerencia:
                                </span>
                                <span class="chip-amber">calificar PEP/Sanciones = 5</span>
                            </div>
                            <div class="sugg__row">
                                <span>Eventos previos del cliente:</span>
                                <span class="sugg__count" data-cli="previos">{{ $cli['previos'] }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Criterio operativo --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <div>
                        <span class="note__title">Criterio operativo</span>
                        <p class="note__text">«El sistema muestra la coincidencia; el analista decide el nivel del factor.»
                        </p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/eventos.js') }}"></script>
@endpush
 