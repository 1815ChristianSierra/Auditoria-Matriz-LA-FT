@extends('layouts.app')

@php
    // $clientes y $seleccionado llegan desde ClientesController@index.
    // Si no llegan, la vista se muestra igual, sin clientes.
    $clientes     = $clientes ?? [];
    $seleccionado = $seleccionado ?? null;

    $tipos = ['individual' => 'Individual', 'juridica' => 'Jurídica'];
    $bandas = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $estadosCoinc = ['confirmada' => 'Confirmada', 'por_verificar' => 'Por verificar'];
    $nivelesTexto = ['alerta' => 'Alerta', 'revisar' => 'Revisar', 'ok' => 'Sin alerta'];

    // Nivel de alerta de un cliente según sus coincidencias en listas
    $nivelDe = function (array $c) {
        $estados = collect($c['coincidencias'])->pluck('estado');
        return $estados->contains('confirmada') ? 'alerta' : ($estados->isNotEmpty() ? 'revisar' : 'ok');
    };

    $total = count($clientes);
@endphp

@section('title', 'Clientes · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Clientes</li>
                </ol>
            </nav>
            <h1 class="page-title">Clientes</h1>
        </div>

        <a href="{{ Route::has('clientes.create') ? route('clientes.create') : '#' }}" class="btn-secondary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            </svg>
            Nuevo cliente
        </a>
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

        {{-- Filtros --}}
        <section class="filters" aria-label="Filtros">
            <div class="filters__row">
                <div class="filters__item filters__item--grow">
                    <label class="field-label" for="buscar">Buscar</label>
                    <div class="search">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round"
                                stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        <input type="text" id="buscar" class="control" placeholder="Buscar por código o nombre...">
                    </div>
                </div>
                <div class="filters__item">
                    <label class="field-label" for="filtro-tipo">Tipo</label>
                    <select id="filtro-tipo" class="control">
                        <option value="todos">Todos</option>
                        @foreach ($tipos as $valor => $texto)
                            <option value="{{ $valor }}">{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filters__item">
                    <label class="field-label" for="filtro-pep">PEP</label>
                    <select id="filtro-pep" class="control">
                        <option value="todos">Todos</option>
                        <option value="si">Sí</option>
                        <option value="no">No</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="layout">

            {{-- Columna izquierda: tabla + nota --}}
            <div class="col">
                <section class="panel panel--table" aria-label="Listado de clientes">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Nombre ficticio</th>
                                    <th scope="col">Tipo</th>
                                    <th scope="col" class="col-center">PEP</th>
                                    <th scope="col" class="col-center">Coinc.</th>
                                    <th scope="col" class="col-center">Eventos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientes as $codigo => $c)
                                    @php
                                        $nivel = $nivelDe($c);
                                        $nCoinc = count($c['coincidencias']);
                                    @endphp
                                    <tr class="{{ $codigo === $seleccionado ? 'is-selected' : '' }}"
                                        data-cliente="{{ $codigo }}" data-nombre="{{ $c['nombre'] }}"
                                        data-tipo="{{ $c['tipo'] }}" data-pep="{{ $c['pep'] ? 'si' : 'no' }}"
                                        tabindex="0">
                                        <td class="col-code">{{ $codigo }}</td>
                                        <td class="col-name" title="{{ $c['nombre'] }}">{{ $c['nombre'] }}</td>
                                        <td class="col-muted">{{ $tipos[$c['tipo']] }}</td>
                                        <td class="col-center {{ $c['pep'] ? '' : 'col-muted' }}">
                                            {{ $c['pep'] ? 'Sí' : 'No' }}</td>
                                        <td class="col-center">
                                            @if ($nivel === 'ok')
                                                <span class="col-mono col-muted">{{ $nCoinc }}</span>
                                            @else
                                                <span
                                                    class="count-badge count-badge--{{ $nivel }}">{{ $nCoinc }}</span>
                                            @endif
                                        </td>
                                        <td class="col-center col-mono">{{ count($c['eventos']) }}</td>
                                    </tr>
                                @endforeach
                                @if (!count($clientes))
                                    <tr>
                                        <td colspan="6" class="col-empty">Aún no hay clientes registrados.</td>
                                    </tr>
                                @endif
                                <tr id="fila-vacia" hidden>
                                    <td colspan="6" class="col-empty">No hay clientes que coincidan con los filtros.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="panel__foot">
                        <span id="conteo">Mostrando {{ $total }} de {{ $total }}</span>
                        <span class="sync">Sincronizado</span>
                    </div>
                </section>

                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <p><strong>Datos 100 % ficticios:</strong> la BD rechaza clientes que no estén marcados como sintéticos
                        en conformidad con la normativa de pruebas.</p>
                </aside>
            </div>

            {{-- Columna derecha: ficha del cliente seleccionado --}}
            <section class="panel panel--ficha" aria-label="Ficha del cliente">
                @foreach ($clientes as $codigo => $c)
                    @php
                        $nivel = $nivelDe($c);
                        $nCoinc = count($c['coincidencias']);
                    @endphp
                    <div class="ficha" data-ficha="{{ $codigo }}" {{ $codigo === $seleccionado ? '' : 'hidden' }}>

                        <div class="ficha__head">
                            <h2 class="ficha__title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                </svg>
                                <span>Ficha {{ $codigo }} · {{ $c['nombre'] }}</span>
                            </h2>
                            <span class="tag tag--{{ $nivel }}">{{ $nivelesTexto[$nivel] }}</span>
                        </div>

                        <div class="ficha__grid">
                            <div>
                                <span class="kv__label">Tipo de persona</span>
                                <span class="kv__value">{{ $tipos[$c['tipo']] }}</span>
                            </div>
                            <div>
                                <span class="kv__label">Jurisdicción</span>
                                <span class="kv__value">{{ $c['jurisdiccion'] }}</span>
                            </div>
                            <div>
                                <span class="kv__label">Actividad</span>
                                <span
                                    class="kv__value {{ $c['riesgo'] ? 'kv__value--risk' : '' }}">{{ $c['actividad'] }}</span>
                            </div>
                            <div>
                                <span class="kv__label">Relación desde</span>
                                <span class="kv__value kv__value--mono">{{ $c['desde'] }}</span>
                            </div>
                        </div>

                        <div class="toggle-row">
                            <span class="kv__label" style="margin:0">Es PEP</span>
                            <label class="toggle">
                                <input type="checkbox" name="pep_{{ $codigo }}" aria-label="Es PEP" disabled title="Se cambia en Editar"
                                    {{ $c['pep'] ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <span class="kv__label" style="margin:0">Ingresos verificados</span>
                            <label class="toggle">
                                <input type="checkbox" name="ingresos_{{ $codigo }}" disabled title="Se cambia en Editar"
                                    aria-label="Ingresos verificados" {{ $c['ingresos'] ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </div>

                        {{-- Coincidencias en listas --}}
                        <div class="section">
                            <div class="section__head">
                                <span class="section__title">Coincidencias en listas</span>
                                @if ($nCoinc)
                                    <span class="section__meta section__meta--{{ $nivel }}">{{ $nCoinc }}
                                        {{ $nCoinc === 1 ? 'Detectada' : 'Detectadas' }}</span>
                                @else
                                    <span class="section__meta">Sin coincidencias</span>
                                @endif
                            </div>
                            <div class="mini">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">Lista</th>
                                            <th scope="col">Tipo</th>
                                            <th scope="col" class="col-right">Coincidencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($c['coincidencias'] as $coinc)
                                            <tr>
                                                <td>{{ $coinc['lista'] }}</td>
                                                <td class="col-type">{{ $coinc['tipo'] }}</td>
                                                <td class="col-right"><span
                                                        class="pill pill--{{ $coinc['estado'] }}">{{ $estadosCoinc[$coinc['estado']] }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="col-empty">Sin coincidencias en listas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Eventos del cliente --}}
                        <div class="section">
                            <div class="section__head">
                                <span class="section__title">Eventos del cliente</span>
                                <span class="section__meta">Total: {{ count($c['eventos']) }}</span>
                            </div>
                            <div class="mini">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">Evento</th>
                                            <th scope="col">Evaluación</th>
                                            <th scope="col" class="col-right">Banda</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($c['eventos'] as $e)
                                            <tr>
                                                <td>
                                                    <a href="{{ Route::has('resultados.show') ? route('resultados.show', $e['evento']) : '#' }}"
                                                        class="ev-link">
                                                        {{ $e['evento'] }}
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                                                stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" />
                                                        </svg>
                                                    </a>
                                                </td>
                                                <td class="col-mono">{{ $e['evaluacion'] }}</td>
                                                <td class="col-right"><span
                                                        class="pill pill--band band--{{ $e['banda'] }}">{{ $bandas[$e['banda']] ?? 'Sin calcular' }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="col-empty">Sin eventos registrados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="ficha__actions">
                            <a href="{{ Route::has('clientes.edit') ? route('clientes.edit', $c['id']) : '#' }}"
                                class="btn-secondary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                </svg>
                                Editar
                            </a>
                            <a href="{{ Route::has('coincidencias.index') ? route('coincidencias.index', ['cliente' => $codigo]) : '#' }}"
                                class="btn-secondary">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                                </svg>
                                Ver coincidencias
                            </a>
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/clientes.js') }}"></script>
@endpush
 