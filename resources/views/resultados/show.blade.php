@extends('layouts.app')

@php
    // Estas variables llegan desde ResultadosController@show; si no llegan, la vista se muestra igual, vacía.
    $evaluacionId     = $evaluacionId ?? null;
    $evaluacionCodigo = $evaluacionCodigo ?? '—';
    $evento           = $evento ?? ['id' => '—', 'pk' => null, 'cliente' => '—'];
    $version          = $version ?? '—';
    $banda            = $banda ?? 'bajo';
    $prioridad        = $prioridad ?? ['posicion' => 1, 'total' => 1];
    $regla            = $regla ?? null;
    $factores         = $factores ?? [];
    $inherente        = $inherente ?? 0;
    $agravantes       = $agravantes ?? 0;
    $controles        = $controles ?? 0;
    $residual         = $residual ?? 0;
    $topeAgravantes   = $topeAgravantes ?? 10;
    $topeControles    = $topeControles ?? 3;
    $historial        = $historial ?? [];
    $resultadoId      = $resultadoId ?? null;
    $cerrada          = $cerrada ?? false;

    $bandas = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $nivelTexto = [
        'bajo' => 'Nivel bajo',
        'medio' => 'Nivel medio',
        'alto' => 'Nivel alto',
        'critico' => 'Nivel máximo'
    ];
    $bandaNivel = [0 => 'bajo', 1 => 'bajo', 2 => 'bajo', 3 => 'medio', 4 => 'alto', 5 => 'critico'];
    $estadosHist = ['vigente' => 'Vigente', 'reemplazado' => 'Reemplazado'];

    // Los números ya los calculó la BD (sp_calcular_resultado): aquí solo se muestran
    $pesoTotal  = collect($factores)->sum('peso');
    $residualTx = $residual == floor($residual) ? number_format($residual, 0) : number_format($residual, 1);
    $punto      = min(max($residual, 4), 96); // posición del puntero en la escala (4 % a 96 %)
@endphp

@section('title', $evaluacionCodigo . ' · Resultado ' . $evento['id'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/resultados.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
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
                    <li><a href="{{ Route::has('eventos.calificar') && $evento['pk'] ? route('eventos.calificar', $evento['pk']) : '#' }}"
                            class="breadcrumb__mono">{{ $evento['id'] }}</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Resultado</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Resultado <span>·</span> {{ $evento['id'] }}</h1>
                <span class="tag-neutral">Evaluación integral</span>
            </div>
        </div>

        <div class="page-actions">
            <form method="POST" action="{{ Route::has('resultados.store') && $evento['pk'] ? route('resultados.store', $evento['id']) : '#' }}" class="inline-form"
                data-confirm="Se calculará un resultado nuevo para {{ $evento['id'] }}. El actual quedará como «Reemplazado» en el historial.">
                @csrf
                <button type="submit" class="btn-secondary"
                    @if ($cerrada) disabled title="Evaluación cerrada · solo lectura" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                Recalcular
            </button>
            </form>
            <a href="{{ Route::has('decisiones.create') ? route('decisiones.create', ['resultado' => $resultadoId]) : '#' }}" class="btn-secondary">
                Registrar decisión
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
            </a>

        </div>
    </header>

    <div class="page-body">

        {{-- Mensajes del controlador --}}
        @if (session('success'))
            <div class="alert alert--ok" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error" role="alert">{{ session('error') }}</div>
        @endif

        <div class="layout">

            {{-- Columna izquierda: puntuación, regla y desglose --}}
            <div class="col">

                {{-- Riesgo residual --}}
                <article class="panel score band--{{ $banda }}">
                    <div class="score__top">
                        <span class="score__label">Riesgo residual</span>
                        <span class="score__tag">{{ $nivelTexto[$banda] }}</span>
                    </div>
                    <div class="score__value">
                        <span class="score__number">{{ $residualTx }}</span>
                        <span class="score__max">/ 100</span>
                    </div>
                    <div class="score__bottom">
                        <span class="band-pill">{{ $bandas[$banda] }}</span>
                        <span class="score__priority">Prioridad {{ $prioridad['posicion'] }} de
                            {{ $prioridad['total'] }}</span>
                    </div>
                </article>

                {{-- Regla de escalamiento --}}
                @if ($regla)
                    <article class="alert-card">
                        <div class="alert-card__icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="alert-card__title">Regla de escalamiento activada</h2>
                            <p class="alert-card__text"><strong>{{ $regla['codigo'] }}:</strong> {{ $regla['texto'] }}</p>
                        </div>
                    </article>
                @endif

                {{-- Cómo se obtuvo el residual --}}
                <article class="panel band--{{ $banda }}">
                    <div class="panel__head">
                        <h2 class="panel__title">Cómo se obtuvo el residual</h2>
                        <svg class="panel__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                    </div>
                    <div class="calc">
                        <dl class="calc__list">
                            <div class="calc__row calc__row--strong">
                                <dt>Riesgo inherente</dt>
                                <dd>{{ number_format($inherente, 2) }}</dd>
                            </div>
                            <div class="calc__row">
                                <dt>+ Agravantes (tope {{ $topeAgravantes }})</dt>
                                <dd>+{{ number_format($agravantes, 2) }}</dd>
                            </div>
                            <div class="calc__row">
                                <dt>− Controles (tope {{ $topeControles }})</dt>
                                <dd>−{{ number_format($controles, 2) }}</dd>
                            </div>
                            <div class="calc__row calc__row--total">
                                <dt>= Riesgo residual</dt>
                                <dd>{{ number_format($residual, 2) }}</dd>
                            </div>
                        </dl>

                        <div class="scale">
                            <span class="scale__title">Escala de bandas</span>
                            <div class="scale__bar">
                                @foreach ($bandas as $clave => $texto)
                                    <div
                                        class="scale__seg band--{{ $clave }} {{ $clave === $banda ? 'is-active' : '' }}">
                                        {{ $texto }}</div>
                                @endforeach
                            </div>
                            <div class="scale__track">
                                <div class="scale__pointer" style="left: {{ $punto }}%">
                                    <span class="scale__arrow"></span>
                                    <span class="scale__value">{{ $residualTx }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            {{-- Columna derecha: detalle, historial y notas --}}
            <div class="col">

                {{-- Detalle por factor --}}
                <article class="panel">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <h2 class="panel__title">Detalle por factor <small>(versión {{ $version }})</small></h2>
                        </div>
                        <span class="pill">Ponderación {{ $pesoTotal }}%</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Factor</th>
                                    <th scope="col" class="col-center">Nivel</th>
                                    <th scope="col" class="col-right">Peso</th>
                                    <th scope="col" class="col-right">Aporte</th>
                                    <th scope="col">Justificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($factores as $f)
                                    <tr class="{{ $f['destacado'] ? 'row--flag' : '' }}">
                                        <td class="col-name">
                                            @if ($f['destacado'])
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" />
                                                </svg>
                                            @endif
                                            <span>{{ $f['nombre'] }}</span>
                                            @if ($f['dato_faltante'])
                                                <span class="pill">Dato faltante</span>
                                            @endif
                                        </td>
                                        <td class="col-center"><span
                                                class="lvl band--{{ $bandaNivel[$f['nivel']] }}">{{ $f['nivel'] }}</span>
                                        </td>
                                        <td class="col-right col-mono col-muted">{{ $f['peso'] }} %</td>
                                        <td class="col-right col-mono col-strong">
                                            {{ number_format($f['aporte'], 2) }}</td>
                                        <td class="col-just">{{ $f['justificacion'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="col-center col-muted">Este evento no tiene calificaciones.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="band--{{ $banda }}">
                                <tr>
                                    <td colspan="2">Total inherente</td>
                                    <td class="col-right col-mono col-muted">{{ $pesoTotal }} %</td>
                                    <td class="col-right col-mono col-total">{{ number_format($inherente, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="panel__foot">
                        <span>Fórmula de cálculo: <span>Aporte = peso × nivel ÷ 5</span></span>
                        <span class="panel__foot-end">Normalizado a escala 0-100</span>
                    </div>
                </article>

                {{-- Historial --}}
                <article class="panel">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg class="is-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" />
                            </svg>
                            <h2 class="panel__title">Historial de resultados de este evento</h2>
                        </div>
                        <span class="panel__meta">{{ count($historial) }}
                            {{ count($historial) === 1 ? 'registro procesado' : 'registros procesados' }}</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Calculado</th>
                                    <th scope="col">Versión</th>
                                    <th scope="col" class="col-right">Residual</th>
                                    <th scope="col" class="col-center">Banda</th>
                                    <th scope="col" class="col-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historial as $h)
                                    <tr>
                                        <td class="col-mono">{{ $h['fecha'] }}</td>
                                        <td class="col-mono col-muted">{{ $h['version'] }}</td>
                                        <td class="col-right col-mono col-strong">{{ number_format($h['residual'], 2) }}
                                        </td>
                                        <td class="col-center"><span
                                                class="badge band--{{ $h['banda'] }}">{{ $bandas[$h['banda']] ?? $h['banda'] }}</span>
                                        </td>
                                        <td class="col-center"><span
                                                class="status status--{{ $h['estado'] }}">{{ $estadosHist[$h['estado']] ?? $h['estado'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>

                {{-- Notas amarillas --}}
                <div class="notes">
                    <aside class="note">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        <div>
                            <span class="note__title">Trazabilidad matemática</span>
                            <p class="note__text">Reproducible: mismos factores + misma versión de parámetros = mismo
                                score.</p>
                        </div>
                    </aside>

                    <aside class="note">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        <div>
                            <span class="note__title">Criterio de Oficial de Cumplimiento</span>
                            <p class="note__text">El score ordena la atención. La decisión sobre el evento es humana.</p>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/resultados.js') }}"></script>
@endpush
 