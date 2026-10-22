@extends('layouts.app')

@php
    // $evaluacion y $factoresTotal llegan desde EvaluacionesController@show.
    // Si no llegan, la vista se muestra igual con una evaluación vacía.
    $evaluacion = $evaluacion ?? [
        'id' => null, 'codigo' => '—', 'inicio' => '—', 'fin' => '—', 'alcance' => '', 'version' => '—',
        'version_estado' => '—', 'estado' => 'abierta', 'responsable' => '—', 'eventos' => [],
    ];

    $estados = ['abierta' => 'Abierta', 'cerrada' => 'Cerrada'];
    $bandas = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $decisiones = [
        'pendiente' => 'Pendiente', 'revision' => 'En revisión', 'escalar_a_cumplimiento' => 'Escalada',
        'descartar' => 'Descartada', 'plan_de_accion' => 'Plan de acción',
    ];
    $factoresTotal = $factoresTotal ?? 0;

    $cerrada = $evaluacion['estado'] === 'cerrada'; // cerrada = solo lectura
    $eventos = collect($evaluacion['eventos']);
    $total = $eventos->count();
    $evaluados = $eventos->whereNotNull('residual')->count();
    $pendientes = $total - $evaluados;
    $lockTitle = 'Evaluación cerrada: solo lectura';
@endphp

@section('title', $evaluacion['codigo'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/valuaciones.css') }}">
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
                    <li class="breadcrumb__current">{{ $evaluacion['codigo'] }}</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">{{ $evaluacion['codigo'] }}</h1>
                <span class="status status--{{ $evaluacion['estado'] }}">
                    @if ($cerrada)
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                    @endif
                    {{ $estados[$evaluacion['estado']] }}
                </span>
            </div>
        </div>

        {{-- Acciones: se bloquean cuando la evaluación está cerrada --}}
        <div class="page-actions">
            @if ($cerrada)
                <button type="button" class="btn-secondary" disabled title="{{ $lockTitle }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Registrar evento
                </button>
            @else
                <a href="{{ Route::has('eventos.create') ? route('eventos.create', ['evaluacion' => $evaluacion['id']]) : '#' }}" class="btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Registrar evento
                </a>
            @endif
            <form method="POST" action="{{ Route::has('evaluaciones.calcular') && $evaluacion['id'] ? route('evaluaciones.calcular', $evaluacion['id']) : '#' }}" class="inline-form">
                @csrf
                <button type="submit" class="btn-secondary"
                    @if ($cerrada) disabled title="{{ $lockTitle }}" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" />
                </svg>
                Calcular pendientes
            </button>
            </form>
            <form method="POST" action="{{ Route::has('evaluaciones.cerrar') && $evaluacion['id'] ? route('evaluaciones.cerrar', $evaluacion['id']) : '#' }}" class="inline-form" data-confirm="¿Cerrar la evaluación {{ $evaluacion['codigo'] }}? Quedará en solo lectura.">
                @csrf
                <button type="submit" class="btn-secondary"
                    @if ($cerrada) disabled title="{{ $lockTitle }}" @endif>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                Cerrar evaluación
            </button>
            </form>
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

        @if ($cerrada)
            <div class="readonly-banner" role="status">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                <span><strong>Evaluación cerrada · solo lectura.</strong> No se pueden registrar eventos, calcular ni
                    calificar.</span>
            </div>
        @endif

        {{-- Ficha de resumen --}}
        <section class="summary" aria-label="Resumen de la evaluación">
            <div class="summary__cell">
                <span class="summary__label">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Período
                </span>
                <span class="summary__value summary__value--mono">{{ $evaluacion['inicio'] }} →
                    {{ $evaluacion['fin'] }}</span>
            </div>

            <div class="summary__cell">
                <span class="summary__label">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Versión de parámetros
                </span>
                <span class="summary__value">
                    <span class="summary__value--mono">{{ $evaluacion['version'] }}</span>
                    <span class="summary__sep">·</span>
                    <span class="summary__ok">{{ $evaluacion['version_estado'] }}</span>
                </span>
            </div>

            <div class="summary__cell">
                <span class="summary__label">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Responsable
                </span>
                <span class="summary__value">{{ $evaluacion['responsable'] }}</span>
            </div>

            <div class="summary__cell">
                <span class="summary__label">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    Alcance
                </span>
                <span class="summary__value summary__value--truncate"
                    title="{{ $evaluacion['alcance'] }}">{{ $evaluacion['alcance'] }}</span>
            </div>
        </section>

        {{-- Eventos de la evaluación --}}
        <section class="panel" aria-label="Eventos de la evaluación">
            <div class="panel__bar">
                <div class="panel__bar-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <h2 class="panel__title">Eventos de la evaluación</h2>
                    <span class="panel__count">{{ $total }} registros</span>
                </div>
                <span class="panel__meta">Matriz SARLAFT / AML Core</span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Evento</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Tipo</th>
                            <th scope="col" class="col-right">Monto (USD)</th>
                            <th scope="col" class="col-center">Factores</th>
                            <th scope="col" class="col-right">Inherente</th>
                            <th scope="col" class="col-right">Residual</th>
                            <th scope="col" class="col-center">Banda</th>
                            <th scope="col" class="col-center">Decisión</th>
                            <th scope="col" class="col-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($eventos as $e)
                            @php
                                $calculado = !is_null($e['residual']);
                                $incompleto = $e['factores'] < $factoresTotal;
                            @endphp
                            <tr class="{{ $calculado ? '' : 'row--pending' }}">
                                <td class="col-event">{{ $e['id'] }}</td>
                                <td class="col-mono">{{ $e['cliente'] }}</td>
                                <td><span
                                        class="tag {{ $e['tipo'] === 'LA/FT' ? 'tag--lft' : '' }}">{{ $e['tipo'] }}</span>
                                </td>
                                <td class="col-num">{{ number_format($e['monto'], 2) }}</td>
                                <td class="col-center">
                                    <span class="chip-factors {{ $incompleto ? 'chip-factors--warn' : '' }}">
                                        {{ $e['factores'] }} / {{ $factoresTotal }}
                                        @if ($incompleto)
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                            </svg>
                                        @endif
                                    </span>
                                </td>

                                @if ($calculado)
                                    <td class="col-num">{{ number_format($e['inherente'], 2) }}</td>
                                    <td class="col-num col-residual {{ $e['banda'] === 'critico' ? 'is-critical' : '' }}">
                                        {{ number_format($e['residual'], 2) }}</td>
                                    <td class="col-center"><span
                                            class="badge band--{{ $e['banda'] }}">{{ $bandas[$e['banda']] }}</span>
                                    </td>
                                    <td class="col-center">
                                        @if ($e['decision'])
                                            <span
                                                class="decision decision--{{ $e['decision'] }}">{{ $decisiones[$e['decision']] ?? $e['decision'] }}</span>
                                        @else
                                            <span class="col-empty">—</span>
                                        @endif
                                    </td>
                                    <td class="col-right"><a href="{{ Route::has('resultados.show') ? route('resultados.show', $e['id']) : '#' }}" class="row-link">Ver resultado</a></td>
                                @else
                                    <td class="col-num col-empty">—</td>
                                    <td class="col-num col-empty">—</td>
                                    <td class="col-center row-muted">Sin calcular</td>
                                    <td class="col-center col-empty">—</td>
                                    <td class="col-right">
                                        @if ($cerrada)
                                            <button type="button" class="btn-secondary btn--sm" disabled title="{{ $lockTitle }}">Calificar</button>
                                        @else
                                            <a href="{{ Route::has('eventos.calificar') ? route('eventos.calificar', $e['pk']) : '#' }}" class="btn-secondary btn--sm">Calificar</a>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="col-center row-muted">Esta evaluación aún no tiene eventos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel__foot">
                <div class="panel__foot-left">
                    <span>Total evaluados: <strong>{{ $evaluados }}</strong></span>
                    <span>Pendientes de calificar: <strong
                            class="{{ $pendientes ? 'is-warn' : '' }}">{{ $pendientes }}</strong></span>
                </div>
                <div class="panel__foot-right">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <span>Motor de inferencia listo · Calibración {{ $evaluacion['version'] }}</span>
                </div>
            </div>
        </section>

        {{-- Notas amarillas --}}
        <div class="notes">
            <aside class="note">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                <div>
                    <span class="note__title">Regla de factores incompletos</span>
                    <p class="note__text">
                        Si faltan factores, al calcular se asigna automáticamente <strong>nivel 3</strong> y se registrará
                        formalmente con marca de <strong>«dato faltante»</strong> en la auditoría.
                    </p>
                </div>
            </aside>

            <aside class="note">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2zm6 6l2 2 4-4"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                <div>
                    <span class="note__title">Condición de cierre</span>
                    <p class="note__text">
                        <strong>«Cerrar evaluación»</strong> exige que todos los eventos tengan resultado vigente y factores
                        calificados.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/valuaciones.js') }}"></script>
@endpush
 