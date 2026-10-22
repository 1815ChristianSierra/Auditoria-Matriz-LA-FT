@extends('layouts.app')

@php
    // $evaluacionId, $evaluacionCodigo, $evento, $banda, $resultado, $historial y $responsables llegan desde
    // DecisionesController@create. Si no llegan, la vista se muestra igual, vacía.
    $evaluacionId     = $evaluacionId ?? null;
    $evaluacionCodigo = $evaluacionCodigo ?? '—';
    $evento           = $evento ?? ['id' => '—', 'pk' => null];
    $banda            = $banda ?? 'bajo';
    $resultado        = $resultado ?? ['id' => null, 'residual' => 0, 'regla' => null, 'version' => '—', 'fecha' => '—'];
    $historial        = $historial ?? [];
    $responsables     = $responsables ?? [];

    // Valores que acepta la BD (tabla decision_seguimiento)
    $decisiones = [
        'en_revision'            => ['En revisión',           'Analista y supervisor cotejan datos auxiliares'],
        'escalar_a_cumplimiento' => ['Escalar a Cumplimiento', 'Remitir a Oficialía para reporte regulatorio ROS'],
        'descartar'              => ['Descartar',              'Falso positivo debidamente justificado'],
        'plan_de_accion'         => ['Plan de acción',         'Mitigación con fecha límite y seguimiento formal'],
    ];
    $estadosSeg = ['pendiente' => 'Pendiente', 'en_curso' => 'En curso', 'cerrado' => 'Cerrado'];
    $bandas     = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];

    $justificacion = old('justificacion', '');
    $maxJust       = 500;
    $prioritario   = in_array($banda, ['alto', 'critico']);
@endphp

@section('title', $evaluacionCodigo . ' · Decisión ' . $evento['id'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/decisiones.css') }}">
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
                    <li><a href="{{ Route::has('resultados.show') && $evento['pk'] ? route('resultados.show', $evento['id']) : '#' }}" class="breadcrumb__mono">{{ $evento['id'] }}</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Decisión</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Decisión sobre {{ $evento['id'] }}</h1>
                @if ($prioritario)
                    <span class="priority band--{{ $banda }}">Evento prioritario</span>
                @endif
            </div>
        </div>

        <span class="system-id">ID Resultado: <strong>#{{ $resultado['id'] }}</strong></span>
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
            <section class="panel" aria-label="Decisión humana y plan de acción">
                <div class="panel__head">
                    <div class="panel__head-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <h2 class="panel__title">Decisión humana y plan de acción</h2>
                    </div>
                    <span class="pill-amber">Campos obligatorios (*)</span>
                </div>

                {{-- Guarda en DecisionesController@store (ruta decisiones.store); después vuelve al resultado --}}
                <form method="POST" action="{{ Route::has('decisiones.store') ? route('decisiones.store') : '#' }}" id="form-decision" class="form">
                    @csrf
                    <input type="hidden" name="resultado" value="{{ $resultado['id'] }}">

                    {{-- Decisión --}}
                    <div>
                        <span class="field-label">Decisión <span class="required">*</span></span>
                        <div class="decision-grid">
                            @foreach ($decisiones as $valor => [$titulo, $descripcion])
                                <label class="decision-card">
                                    <input type="radio" name="decision" value="{{ $valor }}" {{ old('decision', 'en_revision') === $valor ? 'checked' : '' }} required>
                                    <span>
                                        <span class="decision-card__title">{{ $titulo }}</span>
                                        <span class="decision-card__sub">{{ $descripcion }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Justificación --}}
                    <div class="field--grow">
                        <label class="field-label field-label--row" for="justificacion">
                            <span>Justificación (obligatoria) <span class="required">*</span></span>
                            <span class="field-count" id="justificacion-count">{{ mb_strlen($justificacion) }} / {{ $maxJust }} caract.</span>
                        </label>
                        <textarea id="justificacion" name="justificacion" rows="3" maxlength="{{ $maxJust }}" class="control control--textarea" placeholder="Ingrese el sustento técnico y regulatorio de la decisión..." required>{{ $justificacion }}</textarea>
                    </div>

                    {{-- Responsable + Fecha compromiso --}}
                    <div class="row row--two">
                        <div>
                            <label class="field-label" for="responsable">Responsable del plan <span class="required" data-plan-req hidden>*</span></label>
                            <select id="responsable" name="responsable" class="control">
                                <option value="">Sin asignar</option>
                                @foreach ($responsables as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('responsable') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="fecha_compromiso">Fecha compromiso <span class="required" data-plan-req hidden>*</span></label>
                            <input type="date" id="fecha_compromiso" name="fecha_compromiso" class="control control--mono" min="{{ date('Y-m-d') }}" value="{{ old('fecha_compromiso') }}">
                        </div>
                    </div>

                    {{-- Plan de acción --}}
                    <div class="field--grow">
                        <label class="field-label" for="plan_accion">Plan de acción <span class="required" data-plan-req hidden>*</span></label>
                        <textarea id="plan_accion" name="plan_accion" rows="3" class="control control--textarea" placeholder="Pasos, responsables y plazos...">{{ old('plan_accion') }}</textarea>
                    </div>

                    {{-- Seguimiento + Evidencia --}}
                    <div class="row row--follow">
                        <div>
                            <label class="field-label" for="estado_seguimiento">Estado del seguimiento</label>
                            <select id="estado_seguimiento" name="estado_seguimiento" class="control">
                                @foreach ($estadosSeg as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('estado_seguimiento', 'pendiente') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="evidencia">Evidencia</label>
                            <div class="inline">
                                <input type="text" id="evidencia" name="evidencia" class="control" placeholder="Enlace o referencia..." value="{{ old('evidencia') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="form__actions">
                        <button type="submit" class="btn-secondary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Guardar decisión
                        </button>
                        <a href="{{ Route::has('resultados.show') && $evento['pk'] ? route('resultados.show', $evento['id']) : '#' }}" class="btn-ghost">Cancelar</a>
                    </div>
                </form>
            </section>

            {{-- Columna derecha --}}
            <div class="side">

                {{-- Resultado calculado --}}
                <section class="panel band--{{ $banda }}" aria-label="Resultado calculado">
                    <div class="panel__head">
                        <span class="panel__label">Resultado calculado</span>
                        <svg class="panel__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <div class="card__body">
                        <div class="score">
                            <div>
                                <span class="score__number">{{ $resultado['residual'] }}</span><span class="score__max">/100</span>
                            </div>
                            <span class="sev">{{ $bandas[$banda] }}</span>
                        </div>
                        <div class="score__meta">
                            @if ($resultado['regla'])
                                <div class="score__rule">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    Regla {{ $resultado['regla'] }} activada
                                </div>
                            @endif
                            <div class="score__version">
                                <span>Versión de matriz: <strong>{{ $resultado['version'] }}</strong></span>
                                <span>{{ $resultado['fecha'] }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Historial de decisiones --}}
                <section class="panel" aria-label="Historial de decisiones">
                    <div class="panel__head">
                        <span class="panel__label">Historial de decisiones</span>
                        <svg class="panel__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </div>
                    <div class="card__body">
                        <div class="timeline">
                            @foreach ($historial as $h)
                                <div class="timeline__item {{ $h['estado'] === 'pendiente' ? 'is-pending' : '' }}">
                                    <span class="timeline__dot"></span>
                                    <p class="timeline__title">{{ $h['titulo'] }}</p>
                                    <p class="timeline__meta">{{ $h['detalle'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Nota amarilla --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <p class="note__text"><strong>La decisión es humana:</strong> el sistema no reporta, bloquea ni cierra casos automáticamente. Requiere firma y responsabilidad de oficial autorizado.</p>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/decisiones.js') }}"></script>
@endpush
 