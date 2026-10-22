@extends('layouts.app')

@section('title', 'Tablero de Priorización - Matriz de Pruebas')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@php
    // Estas variables llegan desde DashboardController@index; si no llegan, la vista se muestra igual, vacía.
    $listaEvaluaciones = $listaEvaluaciones ?? [];
    $evaluacionSel     = $evaluacionSel ?? null;
    $evaluacionId      = $evaluacionId ?? null;
    $version           = $version ?? null;
    $filtros           = $filtros ?? ['banda' => '', 'cliente' => '', 'q' => '', 'solo_sin_decision' => true];
    $clientes          = $clientes ?? [];
    $eventos           = $eventos ?? [];
    $total             = $total ?? 0;
    $conteo            = $conteo ?? [];
    $sinDecision       = $sinDecision ?? 0;
    $datoFaltante      = $datoFaltante ?? 0;
    $alertas           = $alertas ?? [];

    $bandas     = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $decisiones = [
        'pendiente' => 'Pendiente', 'en_revision' => 'En revisión', 'escalar_a_cumplimiento' => 'Escalada',
        'descartar' => 'Descartada', 'plan_de_accion' => 'Plan de acción',
    ];

    // Porcentaje de cada banda sobre todos los eventos de la evaluación (las tarjetas no dependen de los filtros)
    $pct = function ($banda) use ($conteo, $total) {
        return $total ? round((($conteo[$banda] ?? 0) / $total) * 100) : 0;
    };
@endphp

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Tablero</li>
                </ol>
            </nav>
            <h1 class="page-title">Tablero de priorización</h1>
        </div>

        <button type="button" class="btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
            Exportar CSV
        </button>
    </header>

    <div class="dashboard">

        {{-- Filtros --}}
        <section class="filters" aria-label="Filtros">
            <form method="GET" action="{{ url()->current() }}" id="form-filtros" class="filters__row">

                <div class="filters__item--lg filters__item">
                    <label class="field-label" for="evaluacion">Evaluación</label>
                    <div class="control-group control">
                        <select id="evaluacion" name="evaluacion">
                            @forelse ($listaEvaluaciones as $codigo)
                                <option value="{{ $codigo }}" {{ $codigo === $evaluacionSel ? 'selected' : '' }}>{{ $codigo }}</option>
                            @empty
                                <option value="">Sin evaluaciones</option>
                            @endforelse
                        </select>
                        @if ($version)
                            <span class="status-tag">
                                <span class="status-tag__dot"></span>
                                {{ $version['numero'] }} · {{ $version['estado'] }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="filters__item">
                    <label class="field-label" for="banda">Banda</label>
                    <select id="banda" name="banda" class="control">
                        <option value="">Todas</option>
                        @foreach ($bandas as $valor => $texto)
                            <option value="{{ $valor }}" {{ $filtros['banda'] === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filters__item filters__item--wide">
                    <label class="field-label" for="cliente">Cliente</label>
                    <select id="cliente" name="cliente" class="control">
                        <option value="">Todos</option>
                        @foreach ($clientes as $cli)
                            <option value="{{ $cli }}" {{ $filtros['cliente'] === $cli ? 'selected' : '' }}>{{ $cli }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filters__item filters__item--md">
                    <label class="field-label" for="q">Buscar</label>
                    <div class="search">
                        <span class="search__icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </span>
                        <input type="text" id="q" name="q" class="control" placeholder="Buscar evento..." value="{{ $filtros['q'] }}">
                    </div>
                </div>

                <label class="switch">
                    <input type="hidden" name="solo_sin_decision" value="0">
                    <input type="checkbox" name="solo_sin_decision" value="1" {{ $filtros['solo_sin_decision'] ? 'checked' : '' }}>
                    <span class="switch__track"></span>
                    <span class="switch__text">Solo sin decisión</span>
                </label>
            </form>
        </section>

        {{-- Tarjetas KPI --}}
        <section class="kpis" aria-label="Resumen">
            @foreach ($bandas as $clave => $texto)
                <div class="kpi band--{{ $clave }}">
                    <p class="kpi__label">Eventos {{ $texto }}</p>
                    <div class="kpi__row">
                        <span class="kpi__value">{{ $conteo[$clave] ?? 0 }}</span>
                        <span class="kpi__pct">{{ $pct($clave) }} %</span>
                    </div>
                    <div class="kpi__track"><div class="kpi__bar" style="width: {{ $pct($clave) }}%"></div></div>
                </div>
            @endforeach

            <div class="kpi kpi--neutral">
                <p class="kpi__label">Sin decisión</p>
                <div class="kpi__row">
                    <span class="kpi__value">{{ $sinDecision }}</span>
                    <span class="kpi__pct">{{ $datoFaltante }} con dato faltante</span>
                </div>
                <div class="kpi__track"><div class="kpi__bar" style="width: {{ $total ? round($sinDecision / $total * 100) : 0 }}%"></div></div>
            </div>
        </section>

        <div class="grid">

            {{-- Columna principal --}}
            <div class="grid__main">

                {{-- Ranking de atención --}}
                <section class="panel panel--ranking" aria-label="Ranking de atención">
                    <div class="panel__header">
                        <h2 class="panel__title">Ranking de atención <small>(banda más alta primero)</small></h2>
                        <span class="panel__count">{{ count($eventos) }} de {{ $total }}</span>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col" class="col-center col-rank">#</th>
                                    <th scope="col">Evento</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col" class="col-right">Inher.</th>
                                    <th scope="col" class="col-right">Agrav.</th>
                                    <th scope="col" class="col-right">Cont...</th>
                                    <th scope="col" class="col-right th-residual">Residual</th>
                                    <th scope="col" class="col-center">Banda</th>
                                    <th scope="col" class="col-center">Regla</th>
                                    <th scope="col" class="col-center">Decisión</th>
                                    <th scope="col" class="col-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($eventos as $e)
                                    <tr>
                                        <td class="col-center col-rank">{{ $e['prioridad'] }}</td>
                                        <td class="col-strong">{{ $e['id'] }}</td>
                                        <td class="mono">{{ $e['cliente'] }}</td>
                                        <td class="col-num col-num--main">{{ number_format($e['inherente'], 2) }}</td>
                                        <td class="col-num">{{ number_format($e['agravante'], 2) }}</td>
                                        <td class="col-num">{{ number_format($e['controles'], 2) }}</td>
                                        <td class="col-num col-num--residual">{{ number_format($e['residual'], 2) }}</td>
                                        <td class="col-center">
                                            <span class="badge band--{{ $e['banda'] }}">{{ $bandas[$e['banda']] ?? $e['banda'] }}</span>
                                        </td>
                                        <td class="{{ $e['regla'] ? 'col-center' : 'col-dash' }}">
                                            @if ($e['regla'])
                                                <span class="rule-tag">{{ $e['regla'] }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="col-center">
                                            @php $decision = $e['decision'] ?? 'pendiente'; @endphp
                                            <span class="decision-tag decision-tag--{{ $decision }}">{{ $decisiones[$decision] ?? $decision }}</span>
                                        </td>
                                        <td class="col-center"><a href="{{ Route::has('resultados.show') ? route('resultados.show', $e['id']) : '#' }}" class="row-link">Ver</a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="col-center col-dash empty-row">{{ $total ? 'No hay eventos para mostrar con estos filtros.' : 'Esta evaluación todavía no tiene eventos calculados.' }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Gráfico: inherente vs. residual --}}
                <section class="panel panel--padded chart-panel" aria-label="Riesgo inherente vs. residual">
                    <div class="chart-header">
                        <h3 class="chart-title">Riesgo inherente vs. residual por evento</h3>
                        <div class="legend">
                            <span class="legend__item"><span class="legend__swatch legend__swatch--inherent"></span>Inherente</span>
                            <span class="legend__item"><span class="legend__swatch legend__swatch--residual"></span>Residual</span>
                        </div>
                    </div>

                    <div class="chart">
                      <div class="chart__scroll">
                       <div class="chart__inner" style="--n: {{ max(count($eventos), 1) }}">
                        <div class="chart__plot">
                            <div class="chart__line" style="bottom: 100%"><span>100</span></div>
                            <div class="chart__line" style="bottom: 50%"><span>50</span></div>
                            <div class="chart__line chart__line--base" style="bottom: 0"><span>0</span></div>

                            <div class="chart__bars">
                                @foreach ($eventos as $e)
                                    <div class="chart__pair band--{{ $e['banda'] }}">
                                        <div class="chart__bar" style="height: {{ min(100, max(0, $e['inherente'])) }}%" title="Inherente: {{ number_format($e['inherente'], 2) }}"></div>
                                        <div class="chart__bar chart__bar--residual" style="height: {{ min(100, max(0, $e['residual'])) }}%" title="Residual: {{ number_format($e['residual'], 2) }}"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="chart__labels">
                            @foreach ($eventos as $e)
                                <span class="chart__label">{{ $e['id'] }}</span>
                            @endforeach
                        </div>
                       </div>
                      </div>
                    </div>
                </section>
            </div>

            {{-- Columna lateral --}}
            <div class="grid__side">

                {{-- Distribución por banda --}}
                <section class="panel panel--padded" aria-label="Distribución por banda">
                    <h3 class="panel__heading">Distribución por banda</h3>
                    <div class="dist">
                        @foreach ($bandas as $clave => $texto)
                            <div class="dist__row band--{{ $clave }}">
                                <span class="dist__name">{{ $texto }}</span>
                                <div class="dist__track"><div class="dist__bar" style="width: {{ $pct($clave) }}%"></div></div>
                                <span class="dist__count">{{ $conteo[$clave] ?? 0 }} ({{ $pct($clave) }} %)</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Alertas --}}
                <section class="panel panel--padded panel--alertas" aria-label="Alertas">
                    <h3 class="panel__heading">Alertas</h3>
                    <div class="alerts">
                        @forelse ($alertas as $a)
                            @php
                                $url = $a['tipo'] === 'coincidencias'
                                    ? (Route::has('coincidencias.index') ? route('coincidencias.index') : '#')
                                    : (Route::has('evaluaciones.show') && $evaluacionId ? route('evaluaciones.show', $evaluacionId) : '#');
                            @endphp
                            <a href="{{ $url }}" class="alert alert--link">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                                <span>{{ $a['texto'] }}@if ($a['detalle']) <strong>({{ $a['detalle'] }})</strong>@endif</span>
                            </a>
                        @empty
                            <div class="alert alert--none">
                                <span>Sin alertas por ahora.</span>
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Nota institucional --}}
                <aside class="note">
                    <div class="note__inner">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                        <p>El score ordena la atención; no decide reportes.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
 