@extends('layouts.app')
 
@php
    // $evaluacionId, $evaluacionCodigo, $evento, $factores, $agravantes, $controles, $criterio y $cerrada
    // llegan desde EventosController@calificar. Si no llegan, la vista se muestra igual, vacía.
    $evaluacionId     = $evaluacionId ?? null;
    $evaluacionCodigo = $evaluacionCodigo ?? '—';
    $evento           = $evento ?? ['id' => '—', 'pk' => null, 'banda' => null];
    $cerrada          = $cerrada ?? false;
    $exposiciones     = ['bajo' => 'baja', 'medio' => 'media', 'alto' => 'alta', 'critico' => 'crítica'];
 
    $factores   = $factores ?? [];
    $agravantes = $agravantes ?? [];
    $controles  = $controles ?? [];
    $criterio   = $criterio ?? ['nivel' => 0, 'factor' => '—', 'texto' => 'Elija un nivel para ver su criterio.'];
 
    $pesoTotal     = collect($factores)->sum('peso');
    $totalFactores = count($factores);
 
    // Resumen inicial: factores con nivel elegido o marcados "Falta dato"
    $calificados = collect($factores)->filter(function ($f) {
        return $f['nivel'] !== null || $f['falta'];
    })->count();
    $avance = $totalFactores ? round($calificados / $totalFactores * 100) : 0;
 
    // Después de un error de validación se usa lo que la persona había marcado; si no, lo guardado
    $hayOld = session()->hasOldInput();
@endphp
 
@section('title', $evaluacionCodigo . ' · Calificar evento ' . $evento['id'] . ' · Matriz LA/FT')
 
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/calificar.css') }}">
@endpush
 
@section('content')
 
    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li><a href="{{ Route::has('evaluaciones.index') ? route('evaluaciones.index') : '#' }}">Evaluaciones</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li><a href="{{ Route::has('evaluaciones.show') && $evaluacionId ? route('evaluaciones.show', $evaluacionId) : '#' }}" class="breadcrumb__link">{{ $evaluacionCodigo }}</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">{{ $evento['id'] }}</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Calificar evento {{ $evento['id'] }}</h1>
                @if ($evento['banda'])
                    <span class="exposure band--{{ $evento['banda'] }}">Exposición {{ $exposiciones[$evento['banda']] ?? $evento['banda'] }}</span>
                @else
                    <span class="exposure">Sin calcular</span>
                @endif
            </div>
            <p class="page-desc">Ponderación matemática de factores basales, agravantes concurrentes y controles compensatorios para mitigación LA/FT.</p>
        </div>
 
        <div class="page-actions">
            <div class="engine">
                <span class="engine__label">Motor algorítmico</span>
                <span class="engine__value">SVR-QUANT v4.2</span>
            </div>
            <button type="submit" form="form-calificar" name="accion" value="calcular" id="btn-calcular" class="btn-secondary" {{ $cerrada ? 'disabled' : '' }}>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <span data-label>Calcular resultado</span>
            </button>
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
        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
 
        @if ($cerrada)
            <div class="alert alert--error" role="status">Evaluación cerrada · solo lectura. No se pueden guardar calificaciones.</div>
        @endif
 
        {{-- Guarda en EventosController@guardarCalificaciones (ruta calificaciones.store) --}}
        <form method="POST" action="{{ Route::has('calificaciones.store') && $evento['pk'] ? route('calificaciones.store', $evento['pk']) : '#' }}" id="form-calificar" class="layout">
            @csrf
 
            {{-- Columna izquierda: factores --}}
            <div class="col col--main">
 
                <section class="panel panel--factors" aria-label="Factores de riesgo">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <span class="step">1</span>
                            <h2 class="panel__title">Factores de riesgo <small>(los {{ $totalFactores }} son obligatorios)</small></h2>
                        </div>
                        <span class="pill pill--blue">Ponderación total {{ $pesoTotal }} %</span>
                    </div>
 
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Factor (peso)</th>
                                    <th scope="col" class="col-center">Nivel de riesgo <small>(0 = bajo · 5 = alto)</small></th>
                                    <th scope="col">Justificación</th>
                                    <th scope="col" class="col-center">Falta dato</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factores as $f)
                                    @php $nivelActual = old('factor_' . $f['clave'], $f['nivel']); @endphp
                                    <tr data-factor data-peso="{{ $f['peso'] }}">
                                        <td>
                                            <div class="factor__name">{{ $f['nombre'] }}</div>
                                            <div class="factor__weight">peso {{ $f['peso'] }} %</div>
                                        </td>
                                        <td>
                                            <div class="level-group">
                                                @for ($n = 0; $n <= 5; $n++)
                                                    <label class="level">
                                                        <input type="radio" name="factor_{{ $f['clave'] }}" value="{{ $n }}" {{ $nivelActual !== null && $nivelActual !== '' && (int) $nivelActual === $n ? 'checked' : '' }}>
                                                        <span>{{ $n }}</span>
                                                    </label>
                                                @endfor
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="justificacion_{{ $f['clave'] }}" class="control control--sm" value="{{ old('justificacion_' . $f['clave'], $f['justificacion']) }}">
                                        </td>
                                        <td class="col-center">
                                            <input type="checkbox" name="falta_{{ $f['clave'] }}" value="1" data-falta {{ ($hayOld ? old('falta_' . $f['clave']) : $f['falta']) ? 'checked' : '' }} aria-label="Falta dato: {{ $f['nombre'] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
 
                {{-- Criterio del nivel seleccionado --}}
                <div class="info-banner">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <strong>Nivel {{ $criterio['nivel'] }} · {{ $criterio['factor'] }}:</strong>
                        <em>«{{ $criterio['texto'] }}».</em>
                    </div>
                </div>
 
                {{-- Resumen y guardado --}}
                <div class="summary">
                    <div class="summary__main">
                        <div class="summary__row">
                            <span class="summary__label">Resumen:</span>
                            <span class="summary__text" id="resumen-texto">{{ $calificados }} / {{ $totalFactores }} factores calificados</span>
                        </div>
                        <div class="progress"><div class="progress__bar" id="resumen-barra" style="width: {{ $avance }}%"></div></div>
                    </div>
                    <button type="submit" name="accion" value="guardar" class="btn-secondary" {{ $cerrada ? 'disabled' : '' }}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Guardar calificaciones
                    </button>
                </div>
            </div>
 
            {{-- Columna derecha: agravantes, controles y criterios --}}
            <div class="col col--side">
 
                {{-- Agravantes --}}
                <section class="panel" aria-label="Agravantes">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <span class="step">2</span>
                            <h2 class="panel__title">Agravantes</h2>
                        </div>
                        <span class="pill pill--amber">suman, máx. +10</span>
                    </div>
                    <div class="list">
                        @foreach ($agravantes as $a)
                            @php $marcado = $hayOld ? in_array($a['clave'], (array) old('agravantes', [])) : $a['marcado']; @endphp
                            <div class="check-item">
                                <label class="check-item__row">
                                    <span class="check-item__left">
                                        <input type="checkbox" name="agravantes[]" value="{{ $a['clave'] }}" data-evidence-toggle {{ $marcado ? 'checked' : '' }}>
                                        <span class="check-item__text">{{ $a['texto'] }}</span>
                                    </span>
                                    <span class="chip chip--plus">+{{ $a['valor'] }}</span>
                                </label>
                                <div class="check-item__evidence" {{ $marcado ? '' : 'hidden' }}>
                                    <input type="text" name="evidencia_{{ $a['clave'] }}" class="control control--sm" placeholder="Ingresar evidencia requerida..." value="{{ old('evidencia_' . $a['clave'], $a['evidencia']) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
 
                {{-- Controles --}}
                <section class="panel" aria-label="Controles">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <span class="step">3</span>
                            <h2 class="panel__title">Controles</h2>
                        </div>
                        <span class="pill pill--blue">restan, máx. -3</span>
                    </div>
                    <div class="list">
                        @foreach ($controles as $c)
                            @php $marcado = $hayOld ? in_array($c['clave'], (array) old('controles', [])) : $c['marcado']; @endphp
                            <div class="check-item">
                                <label class="check-item__row">
                                    <span class="check-item__left">
                                        <input type="checkbox" name="controles[]" value="{{ $c['clave'] }}" data-evidence-toggle {{ $marcado ? 'checked' : '' }}>
                                        <span class="check-item__text">{{ $c['texto'] }}</span>
                                    </span>
                                    <span class="chip chip--minus">{{ $c['valor'] }}</span>
                                </label>
                                <div class="check-item__evidence" {{ $marcado ? '' : 'hidden' }}>
                                    <input type="text" name="evidencia_{{ $c['clave'] }}" class="control control--sm" placeholder="Ingresar evidencia requerida..." value="{{ old('evidencia_' . $c['clave'], $c['evidencia']) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
 
                {{-- Criterios de cumplimiento (nota amarilla) --}}
                <aside class="note">
                    <div class="note__head">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Criterios de cumplimiento
                    </div>
                    <ul class="note__list">
                        <li><p>Cada agravante o control marcado <strong>exige una evidencia</strong> (se abre automáticamente un campo de texto para auditoría).</p></li>
                        <li><p><strong>Dato faltante:</strong> el sistema asigna nivel 3 de manera prudencial y deja la marca visible en la bitácora regulatoria.</p></li>
                    </ul>
                </aside>
            </div>
        </form>
    </div>
@endsection
 
@push('scripts')
    <script src="{{ asset('js/calificar.js') }}"></script>
@endpush
 