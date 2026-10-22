@extends('layouts.app')

@php
    // $cliente y $jurisdicciones llegan desde ClientesController@edit.
    // Si no llegan, la vista se muestra igual con un cliente vacío.
    $cliente = $cliente ?? [
        'id' => null, 'codigo' => '—', 'nombre' => '', 'tipo' => 'individual', 'desde' => '', 'jurisdiccion' => 'local',
        'ingresos' => false, 'actividad' => '', 'pep' => false, 'vinculo' => '', 'score' => 0, 'coincidencias' => [], 'eventos' => [],
    ];

    $tipos = ['individual' => 'Individual', 'juridica' => 'Jurídica'];
    $jurisdicciones = $jurisdicciones ?? [
        'local'         => 'Local',
        'extranjero'    => 'Extranjero',
        'frontera'      => 'Zona fronteriza / alto riesgo',
        'offshore'      => 'Zona offshore / régimen especial',
        'alto_riesgo'   => 'Jurisdicción no cooperante (GAFI)',
        'internacional' => 'Internacional estándar',
    ];
    $bandas       = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $estadosCoinc = ['confirmada' => 'Confirmada', 'por_verificar' => 'Por verificar'];

    $ingresos    = (bool) old('ingresos_verificados', $cliente['ingresos']);
    $esPep       = (bool) old('es_pep', $cliente['pep']);
    $errorNombre = $errors->first('nombre');
@endphp

@section('title', 'Editar cliente ' . $cliente['codigo'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes_editar.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li>Operación</li>
                <li class="breadcrumb__sep">/</li>
                <li><a href="{{ Route::has('clientes.index') ? route('clientes.index') : '#' }}">Clientes</a></li>
                <li class="breadcrumb__sep">/</li>
                <li class="breadcrumb__mono">{{ $cliente['codigo'] }}</li>
                <li class="breadcrumb__sep">/</li>
                <li class="breadcrumb__current">Editar</li>
            </ol>
        </nav>
        <div class="page-title-row">
            <h1 class="page-title">Editar cliente</h1>
            <span class="chip-code">{{ $cliente['codigo'] }}</span>
            <span class="tag-neutral">Modo edición supervisada</span>
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
        <div class="layout">

            {{-- Formulario --}}
            <section class="panel" aria-label="Parámetros del sujeto obligado">
                <div class="panel__head">
                    <div class="panel__head-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <h2 class="panel__title">Parámetros del sujeto obligado</h2>
                    </div>
                    <span class="panel__hint">Todos los campos marcados con (*) son obligatorios</span>
                </div>

                {{-- Guarda en ClientesController@update (ruta clientes.update) --}}
                <form method="POST" action="{{ Route::has('clientes.update') && $cliente['id'] ? route('clientes.update', $cliente['id']) : '#' }}" id="form-cliente" class="form" novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Código + Nombre --}}
                    <div class="row row--code">
                        <div>
                            <label class="field-label" for="codigo">
                                <span>Código</span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </label>
                            <input type="text" id="codigo" class="control control--readonly" value="{{ $cliente['codigo'] }}" readonly>
                        </div>
                        <div>
                            <label class="field-label" for="nombre"><span>Nombre ficticio <span class="required">*</span></span></label>
                            <input type="text" id="nombre" name="nombre" class="control {{ $errorNombre ? 'control--error' : '' }}" value="{{ old('nombre', $cliente['nombre']) }}" aria-invalid="{{ $errorNombre ? 'true' : 'false' }}">
                            <p class="field-error" id="nombre-error" {{ $errorNombre ? '' : 'hidden' }}>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <span>{{ $errorNombre ?: 'Obligatorio: ingrese el nombre ficticio del cliente.' }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Tipo de persona + Inicio de relación --}}
                    <div class="row row--two">
                        <div>
                            <span class="field-label"><span>Tipo de persona <span class="required">*</span></span></span>
                            <div class="segmented" role="radiogroup" aria-label="Tipo de persona">
                                @foreach ($tipos as $valor => $texto)
                                    <label class="segmented__opt">
                                        <input type="radio" name="tipo_persona" value="{{ $valor }}" {{ old('tipo_persona', $cliente['tipo']) === $valor ? 'checked' : '' }}>
                                        <span class="segmented__dot"></span>
                                        <span>{{ $texto }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <label class="field-label" for="inicio_relacion"><span>Inicio de la relación</span></label>
                            <input type="date" id="inicio_relacion" name="inicio_relacion" class="control control--mono" value="{{ old('inicio_relacion', $cliente['desde']) }}">
                        </div>
                    </div>

                    {{-- Jurisdicción + Ingresos verificados --}}
                    <div class="row row--two">
                        <div>
                            <label class="field-label" for="jurisdiccion"><span>Jurisdicción de residencia</span></label>
                            <select id="jurisdiccion" name="jurisdiccion" class="control">
                                @foreach ($jurisdicciones as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('jurisdiccion', $cliente['jurisdiccion']) === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <span class="field-label"><span>Ingresos verificados</span></span>
                            <div class="switch-box">
                                <span class="switch-box__state" id="ingresos-estado">No</span>
                                <label class="toggle">
                                    <input type="checkbox" id="ingresos" name="ingresos_verificados" value="1" aria-label="Ingresos verificados" {{ $ingresos ? 'checked' : '' }}>
                                    <span class="toggle__track"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Actividad --}}
                    <div class="field--grow">
                        <label class="field-label" for="actividad">
                            <span>Actividad</span>
                            <span class="field-hint">Perfil transaccional declarado</span>
                        </label>
                        <textarea id="actividad" name="actividad" rows="3" class="control control--textarea">{{ old('actividad', $cliente['actividad']) }}</textarea>
                    </div>

                    {{-- Es PEP + Vínculo con el PEP --}}
                    <div class="row row--pep">
                        <div>
                            <span class="field-label"><span>Es PEP</span></span>
                            <div class="switch-box">
                                <span class="switch-box__state" id="pep-estado">No</span>
                                <label class="toggle">
                                    <input type="checkbox" id="es_pep" name="es_pep" value="1" aria-label="Es PEP" {{ $esPep ? 'checked' : '' }}>
                                    <span class="toggle__track"></span>
                                </label>
                            </div>
                        </div>
                        <div class="field is-disabled" id="vinculo-campo">
                            <label class="field-label" for="vinculo_pep">
                                <span>Vínculo con el PEP</span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            </label>
                            <input type="text" id="vinculo_pep" name="vinculo_pep" class="control" placeholder="Se habilita al marcar «Es PEP»" value="{{ old('vinculo_pep', $cliente['vinculo']) }}" disabled>
                        </div>
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
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

            {{-- Columna derecha --}}
            <div class="side">

                {{-- Coincidencias en listas --}}
                <section class="panel" aria-label="Coincidencias en listas">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Coincidencias en listas</h2>
                        </div>
                        @if (count($cliente['coincidencias']))
                            <span class="count-badge">{{ count($cliente['coincidencias']) }}</span>
                        @endif
                    </div>
                    <div class="card__body">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th scope="col">Lista</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col" class="col-right">Coincidencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cliente['coincidencias'] as $c)
                                    <tr>
                                        <td class="col-name">{{ $c['lista'] }}</td>
                                        <td class="col-mono">{{ $c['tipo'] }}</td>
                                        <td class="col-right">
                                            <span class="pill pill--{{ $c['estado'] }}">
                                                @if ($c['estado'] === 'confirmada')
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                                @endif
                                                {{ $estadosCoinc[$c['estado']] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="col-right" style="text-align:center;color:var(--muted)">Sin coincidencias en listas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <a href="{{ Route::has('coincidencias.index') ? route('coincidencias.index', ['cliente' => $cliente['codigo']]) : '#' }}" class="link-more">
                            Ver coincidencias
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </a>
                    </div>
                </section>

                {{-- Eventos del cliente --}}
                <section class="panel" aria-label="Eventos del cliente">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Eventos del cliente</h2>
                        </div>
                        <span class="panel__meta">Histórico LA/FT</span>
                    </div>
                    <div class="card__body">
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th scope="col">Evento</th>
                                    <th scope="col">Evaluación</th>
                                    <th scope="col" class="col-right">Banda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cliente['eventos'] as $e)
                                    <tr>
                                        <td>
                                            <a href="{{ Route::has('resultados.show') ? route('resultados.show', $e['evento']) : '#' }}" class="ev-link">
                                                {{ $e['evento'] }}
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                            </a>
                                        </td>
                                        <td class="col-mono">{{ $e['evaluacion'] }}</td>
                                        <td class="col-right"><span class="pill pill--band band--{{ $e['banda'] }}">{{ $bandas[$e['banda']] ?? 'Sin calcular' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="col-right" style="text-align:center;color:var(--muted)">Sin eventos registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if (count($cliente['eventos']))
                            <div class="score-box">
                                <span>Score ponderado residual:</span>
                                <strong>{{ number_format($cliente['score'], 1) }} / 100</strong>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Aviso normativo de recalculo (nota amarilla) --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <span class="note__title">Aviso normativo de recalculo</span>
                        <p>Los cambios no recalculan resultados ya calculados. Recalcule el evento si corresponde.</p>
                        <span class="note__link">
                            Consultar guía de auditoría
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H8m9 0v9" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </span>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Mismo comportamiento que "Nuevo cliente": interruptores Sí/No, vínculo con el PEP y validación del nombre --}}
    <script src="{{ asset('js/clientes_crear.js') }}"></script>
@endpush
 