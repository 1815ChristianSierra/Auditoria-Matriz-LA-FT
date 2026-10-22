@extends('layouts.app')

@php
    // Estas variables llegan desde SensibilidadController@index; si no llegan, la vista se muestra igual, vacía.
    $listaEvaluaciones = $listaEvaluaciones ?? [];
    $evaluacionSel     = $evaluacionSel ?? null;
    $versiones         = $versiones ?? [];
    $versionSel        = $versionSel ?? null;
    $factores          = $factores ?? [];
    $ranking           = $ranking ?? [];

    $limitaciones = [
        'Pocos escenarios sintéticos: no hay validación estadística ni calibración con datos reales.',
        'Los pesos y escalas son juicio experto; el score es una ayuda para ordenar, no una probabilidad.',
        'No se midieron falsos positivos/negativos ni la concentración de eventos por banda en volumen real.',
    ];

    $suma         = round(collect($factores)->sum('simulado'), 2);
    $sumaOk       = abs($suma - 100) < 0.01;
    $cambianBanda = collect($ranking)->filter(function ($r) { return $r['banda_base'] !== $r['banda_sim']; })->count();
    $cambiaOrden  = collect($ranking)->filter(function ($r) { return $r['pos_base'] !== $r['pos_sim']; })->count();
    $mayorDelta   = collect($ranking)->map(function ($r) { return abs($r['sim'] - $r['base']); })->max() ?? 0;
@endphp

@section('title', 'Análisis de sensibilidad · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/sensibilidad.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Control</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Sensibilidad</li>
                </ol>
            </nav>
            <h1 class="page-title">Análisis de sensibilidad</h1>
        </div>

        <div class="page-actions">
            <button type="submit" form="form-simular" id="btn-simular" class="btn-secondary" {{ $sumaOk ? '' : 'disabled' }}>Simular</button>
            <button type="button" id="btn-restablecer" class="btn-ghost">Restablecer</button>
        </div>
    </header>

    <div class="page-body">

        {{-- Aviso --}}
        <div class="info-banner">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            <span>La simulación NO se guarda. Para cambiar los pesos oficiales, cree una nueva versión de parámetros.</span>
        </div>

        {{-- Mensaje de la simulación (errores) --}}
        <div id="sim-mensaje" class="alert alert--error" role="alert" hidden></div>

        {{-- Filtros: se aplican al cambiarlos; al cambiar la evaluación la versión base vuelve a ser la suya --}}
        <form method="GET" action="{{ url()->current() }}" id="form-base" class="filters">
            <div class="filters__item">
                <label class="field-label" for="evaluacion">Evaluación</label>
                <select id="evaluacion" name="evaluacion" class="control">
                    @forelse ($listaEvaluaciones as $codigo)
                        <option value="{{ $codigo }}" {{ $codigo === $evaluacionSel ? 'selected' : '' }}>{{ $codigo }}</option>
                    @empty
                        <option value="">Sin evaluaciones</option>
                    @endforelse
                </select>
            </div>
            <div class="filters__item">
                <label class="field-label" for="version_base">Versión base</label>
                <select id="version_base" name="version_base" class="control">
                    @forelse ($versiones as $valor => $texto)
                        <option value="{{ $valor }}" {{ $valor === $versionSel ? 'selected' : '' }}>{{ $texto }}</option>
                    @empty
                        <option value="">Sin versiones</option>
                    @endforelse
                </select>
            </div>
        </form>

        <div class="layout">

            {{-- Pesos: base vs. simulados (el formulario se envía con el botón "Simular"; no guarda nada) --}}
            <form method="POST" action="{{ Route::has('sensibilidad.simular') ? route('sensibilidad.simular') : '#' }}" id="form-simular" class="weights-form">
                @csrf
                <input type="hidden" name="evaluacion" value="{{ $evaluacionSel }}">
                <input type="hidden" name="version_base" value="{{ $versionSel }}">

                <section class="panel" aria-label="Pesos base y simulados">
                    <div class="panel__head">
                        <h2 class="panel__title">Pesos: base vs. simulados</h2>
                    </div>

                    <div class="weights">
                        <div class="weights__head">
                            <span>Factor</span>
                            <span class="col-center">Base</span>
                            <span class="col-center">Control interactivo</span>
                            <span class="col-right">Simulado</span>
                        </div>

                        <div class="weights__rows">
                            @forelse ($factores as $f)
                                <div class="weights__row" data-factor>
                                    <span class="weights__name" title="{{ $f['nombre'] }}">{{ $f['nombre'] }}</span>
                                    <span class="weights__base col-center">{{ $f['base'] }} %</span>
                                    <div class="weights__slider">
                                        <input type="range" class="slider" name="pesos[{{ $f['clave'] }}]" min="0" max="{{ $f['max'] }}" step="{{ $f['paso'] ?? 0.5 }}" value="{{ $f['simulado'] }}" data-base="{{ $f['base'] }}" aria-label="Peso simulado de {{ $f['nombre'] }}">
                                    </div>
                                    <div class="col-right">
                                        <span class="sim"><span data-sim>{{ $f['simulado'] }}</span> %</span>
                                    </div>
                                </div>
                            @empty
                                <p class="empty-note">La versión base no tiene factores con peso.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="panel__foot">
                        <span id="suma-badge" class="sum {{ $sumaOk ? 'sum--ok' : 'sum--bad' }}">
                            <span id="suma-texto">{{ $sumaOk ? 'Suma: 100 % ✓' : 'Suma: ' . $suma . ' % (≠ 100 %)' }}</span>
                        </span>
                    </div>
                </section>
            </form>

            {{-- Columna derecha: ranking + KPIs --}}
            <div class="col">

                <section class="panel panel--ranking" aria-label="Comparación de ranking">
                    <div class="panel__head">
                        <h2 class="panel__title">Comparación de ranking</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Evento</th>
                                    <th scope="col" class="col-center">Base (# · score)</th>
                                    <th scope="col" class="col-center">Simulado (# · score)</th>
                                    <th scope="col" class="col-center">Δ</th>
                                    <th scope="col" class="col-right">Banda</th>
                                </tr>
                            </thead>
                            <tbody id="ranking-cuerpo">
                                @forelse ($ranking as $r)
                                    @php $delta = $r['sim'] - $r['base']; @endphp
                                    <tr>
                                        <td class="col-event">{{ $r['evento'] }}</td>
                                        <td class="col-mono col-center">{{ $r['pos_base'] }} · {{ number_format($r['base'], 1) }}</td>
                                        <td class="col-mono col-center col-sim">{{ $r['pos_sim'] }} · {{ number_format($r['sim'], 1) }}</td>
                                        <td class="col-mono col-center delta {{ abs($delta) < 0.05 ? '' : ($delta > 0 ? 'delta--up' : 'delta--down') }}">{{ $delta > 0.05 ? '+' : '' }}{{ number_format($delta, 1) }}</td>
                                        <td class="col-right"><span class="badge band--{{ $r['banda_sim'] }}">{{ $r['banda_base_nombre'] }} → {{ $r['banda_sim_nombre'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="col-center empty-note">Esta evaluación todavía no tiene eventos calculados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <div class="kpis">
                    <div class="kpi">
                        <span class="kpi__label">Cambian de banda</span>
                        <span class="kpi__value" id="kpi-banda">{{ $cambianBanda }}</span>
                    </div>
                    <div class="kpi">
                        <span class="kpi__label">Cambia el orden</span>
                        <span class="kpi__value" id="kpi-orden">{{ $cambiaOrden }}</span>
                    </div>
                    <div class="kpi">
                        <span class="kpi__label">Mayor |Δ| score</span>
                        <span class="kpi__value {{ $mayorDelta >= 0.05 ? 'kpi__value--warn' : '' }}" id="kpi-delta">{{ number_format($mayorDelta, 1) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Limitaciones declaradas --}}
        <section class="panel limits" aria-label="Limitaciones declaradas">
            <h3 class="limits__title">Limitaciones declaradas</h3>
            <ul class="limits__list">
                @foreach ($limitaciones as $texto)
                    <li class="limits__item">
                        <span class="limits__icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"/></svg>
                        </span>
                        <span>{{ $texto }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/sensibilidad.js') }}"></script>
@endpush
 