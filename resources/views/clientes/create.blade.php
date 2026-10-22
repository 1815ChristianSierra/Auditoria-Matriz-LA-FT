@extends('layouts.app')

@php
    // $siguienteCodigo, $referencia, $auditoria, $jurisdicciones, $motor y $listas llegan desde ClientesController@create.
    // Si no llegan, la vista se muestra igual con valores neutros.
    $siguienteCodigo = $siguienteCodigo ?? 'CLI-001';
    $referencia      = $referencia      ?? '—';
    $auditoria       = $auditoria       ?? '—';

    $tipos = ['individual' => 'Individual', 'juridica' => 'Jurídica'];
    $jurisdicciones = $jurisdicciones ?? [
        'local'         => 'Local',
        'extranjero'    => 'Extranjero',
        'frontera'      => 'Zona fronteriza / alto riesgo',
        'offshore'      => 'Zona offshore / régimen especial',
        'alto_riesgo'   => 'Jurisdicción no cooperante (GAFI)',
        'internacional' => 'Internacional estándar',
    ];

    $motor  = $motor ?? ['nombre' => 'Motor OFAC / ONU', 'version' => 'Listas sintéticas', 'revisadas' => 0, 'total' => 0];
    $listas = $listas ?? [];
    $avance = $motor['total'] ? ($motor['revisadas'] / $motor['total']) * 100 : 0;

    $esPep        = (bool) old('es_pep');
    $ingresos     = (bool) old('ingresos_verificados');
    $errorNombre  = $errors->first('nombre');
@endphp

@section('title', 'Nuevo cliente · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes_crear.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li><a href="{{ Route::has('clientes.index') ? route('clientes.index') : '#' }}">Clientes</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Nuevo</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Nuevo cliente</h1>
                <span class="tag-blue">Ficha Matriz LA/FT</span>
            </div>
        </div>

        <span class="secure">Entorno seguro · Nivel 3</span>
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

            {{-- Formulario --}}
            <section class="panel" aria-label="Datos generales de la entidad">
                <div class="panel__head">
                    <div class="panel__head-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <h2 class="panel__title">Datos generales de la entidad</h2>
                    </div>
                    <span class="panel__ref">REF: {{ $referencia }}</span>
                </div>

                {{-- Guarda en ClientesController@store (ruta clientes.store) --}}
                <form method="POST" action="{{ Route::has('clientes.store') ? route('clientes.store') : '#' }}" id="form-cliente" class="form" novalidate>
                    @csrf

                    {{-- Código --}}
                    <div>
                        <label class="field-label" for="codigo">
                            <span>Código</span>
                            <span class="field-hint">Autogenerado por sistema</span>
                        </label>
                        <div class="input-icon">
                            <input type="text" id="codigo" class="control control--readonly" value="{{ $siguienteCodigo }}" readonly>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </div>
                    </div>

                    {{-- Nombre ficticio --}}
                    <div>
                        <label class="field-label" for="nombre">
                            <span>Nombre ficticio <span class="required">*</span></span>
                        </label>
                        <input type="text" id="nombre" name="nombre" class="control {{ $errorNombre ? 'control--error' : '' }}" placeholder="Nombre del cliente (ficticio)" value="{{ old('nombre') }}" aria-invalid="{{ $errorNombre ? 'true' : 'false' }}">
                        <p class="field-error" id="nombre-error" {{ $errorNombre ? '' : 'hidden' }}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <span>{{ $errorNombre ?: 'Obligatorio: ingrese el nombre ficticio del cliente.' }}</span>
                        </p>
                    </div>

                    {{-- Tipo de persona --}}
                    <div>
                        <span class="field-label"><span>Tipo de persona <span class="required">*</span></span></span>
                        <div class="type-grid">
                            @foreach ($tipos as $valor => $texto)
                                <label class="type-card">
                                    <input type="radio" name="tipo_persona" value="{{ $valor }}" {{ old('tipo_persona', 'individual') === $valor ? 'checked' : '' }}>
                                    @if ($valor === 'individual')
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    @endif
                                    <span>{{ $texto }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Inicio de relación + Jurisdicción --}}
                    <div class="row row--two">
                        <div>
                            <label class="field-label" for="inicio_relacion"><span>Inicio de la relación</span></label>
                            <input type="date" id="inicio_relacion" name="inicio_relacion" class="control control--mono" value="{{ old('inicio_relacion', date('Y-m-d')) }}">
                        </div>
                        <div>
                            <label class="field-label" for="jurisdiccion"><span>Jurisdicción de residencia</span></label>
                            <select id="jurisdiccion" name="jurisdiccion" class="control">
                                @foreach ($jurisdicciones as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('jurisdiccion', 'local') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Ingresos verificados --}}
                    <div class="option">
                        <div>
                            <span class="option__title">Ingresos verificados</span>
                            <span class="option__sub">Evidencia documental de soporte financiero</span>
                        </div>
                        <div class="option__control">
                            <span class="option__state" id="ingresos-estado">No</span>
                            <label class="toggle">
                                <input type="checkbox" id="ingresos" name="ingresos_verificados" value="1" aria-label="Ingresos verificados" {{ $ingresos ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                    </div>

                    {{-- Actividad económica --}}
                    <div class="field--grow">
                        <label class="field-label" for="actividad"><span>Actividad económica</span></label>
                        <textarea id="actividad" name="actividad" rows="3" class="control control--textarea" placeholder="Describa la actividad económica del cliente...">{{ old('actividad') }}</textarea>
                    </div>

                    {{-- Es PEP --}}
                    <div class="option">
                        <div>
                            <span class="option__title">Es PEP</span>
                            <span class="option__sub">Persona Políticamente Expuesta (nacional o extranjera)</span>
                        </div>
                        <div class="option__control">
                            <span class="option__state" id="pep-estado">No</span>
                            <label class="toggle">
                                <input type="checkbox" id="es_pep" name="es_pep" value="1" aria-label="Es PEP" {{ $esPep ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                    </div>

                    {{-- Vínculo con el PEP --}}
                    <div class="field is-disabled" id="vinculo-campo">
                        <label class="field-label" for="vinculo_pep">
                            <span>Vínculo con el PEP</span>
                            <span class="field-hint">Se habilita al marcar «Es PEP»</span>
                        </label>
                        <input type="text" id="vinculo_pep" name="vinculo_pep" class="control" placeholder="Especifique relación familiar o comercial con el PEP..." value="{{ old('vinculo_pep') }}" disabled>
                    </div>

                    {{-- Restricción de integridad --}}
                    <div class="integrity">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <div>
                            <span class="integrity__title">Restricción de integridad</span>
                            <p>Cliente sintético: siempre activo. La base de datos rechaza datos de personas reales.</p>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="form__actions">
                        <a href="{{ Route::has('clientes.index') ? route('clientes.index') : '#' }}" class="btn-ghost">Cancelar</a>
                        <button type="submit" class="btn-secondary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Guardar cliente
                        </button>
                    </div>
                </form>
            </section>

            {{-- Columna derecha --}}
            <div class="side">

                {{-- Verificación en listas --}}
                <section class="panel" aria-label="Verificación en listas">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Verificación en listas</h2>
                        </div>
                        <span class="pill-neutral">Sin verificar</span>
                    </div>
                    <div class="card__body">
                        <p class="card__text">Al guardar, el sistema cruza el nombre contra las listas sintéticas de PEP y sanciones.</p>

                        <div class="engine">
                            <div class="engine__left">
                                <svg class="engine__ring" fill="none" viewBox="0 0 36 36">
                                    <path class="track" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" stroke="currentColor" stroke-dasharray="100, 100" stroke-width="3"/>
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" stroke="currentColor" stroke-dasharray="{{ round($avance) }}, 100" stroke-width="3"/>
                                </svg>
                                <div>
                                    <span class="engine__name">{{ $motor['nombre'] }}</span>
                                    <span class="engine__sub">{{ $motor['version'] }}</span>
                                </div>
                            </div>
                            <span class="engine__count">{{ $motor['revisadas'] }} / {{ $motor['total'] }}</span>
                        </div>

                        <div class="checks">
                            @foreach ($listas as $l)
                                <div class="checks__row"><span>{{ $l['nombre'] }}</span><span>{{ $l['estado'] }}</span></div>
                            @endforeach
                        </div>
                    </div>
                </section>

                {{-- Criterio de resolución (nota amarilla) --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2zm6 6l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <span class="note__title">Criterio de resolución</span>
                        <p>Si hay coincidencia, queda como «Parcial» hasta que una persona la confirme o la descarte.</p>
                    </div>
                </aside>

                {{-- Advertencia regulatoria (nota amarilla) --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <span class="note__title">Advertencia regulatoria</span>
                        <p>Use solo datos ficticios. No ingrese información de personas reales.</p>
                        <div class="note__foot">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <span>Cumplimiento Ley de Protección de Datos</span>
                        </div>
                    </div>
                </aside>

                {{-- Auditoría de alta --}}
                <div class="audit">
                    <span class="audit__left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Auditoría de alta
                    </span>
                    <span class="audit__code">{{ $auditoria }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/clientes_crear.js') }}"></script>
@endpush
 