@extends('layouts.app')

@section('title', 'Evaluaciones - Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/evaluaciones.css') }}">
@endpush

@php
    // $evaluaciones, $total, $responsables y los datos del modal llegan desde EvaluacionesController@index.
    // Si no llegan, la vista se muestra igual, sin evaluaciones.
    $evaluaciones = $evaluaciones ?? [];
    $responsables = $responsables ?? [];

    $estados   = ['abierta' => 'Abierta', 'cerrada' => 'Cerrada'];
    $mostrando = count($evaluaciones);
    $total     = $total ?? $mostrando;

    // Modal "Nueva evaluación"
    $siguienteCodigo    = $siguienteCodigo ?? 'EVAL-' . date('Y') . '-01';
    $responsablesModal  = $responsablesModal ?? [];
    $versionesAprobadas = $versionesAprobadas ?? [];
@endphp

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Evaluaciones</li>
                </ol>
            </nav>
            <h1 class="page-title">Evaluaciones</h1>
        </div>

        <button type="button" class="btn-secondary" data-modal-open="modal">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
            Nueva evaluación
        </button>
    </header>

    <div class="page-body">

        {{-- Mensajes del controlador --}}
        @if (session('success'))
            <div class="alert alert--ok" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error" role="alert">{{ session('error') }}</div>
        @endif

        {{-- Filtros --}}
        <section class="filters" aria-label="Filtros">
            <form method="GET" action="{{ url()->current() }}" class="filters__row">

                <div class="filters__item">
                    <label class="field-label" for="estado">Estado</label>
                    <select id="estado" name="estado" class="control">
                        <option value="">Todas</option>
                        @foreach ($estados as $valor => $texto)
                            <option value="{{ $valor }}" {{ request('estado') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filters__item">
                    <label class="field-label" for="responsable">Responsable</label>
                    <select id="responsable" name="responsable" class="control">
                        <option value="">Todos</option>
                        @foreach ($responsables as $resp)
                            <option value="{{ $resp }}" {{ request('responsable') === $resp ? 'selected' : '' }}>{{ $resp }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filters__item filters__item--grow">
                    <label class="field-label" for="q">Buscar</label>
                    <div class="search">
                        <span class="search__icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                        </span>
                        <input type="text" id="q" name="q" class="control" placeholder="Buscar por código..." value="{{ request('q') }}">
                    </div>
                </div>
            </form>
        </section>

        {{-- Listado --}}
        <section class="panel" aria-label="Listado de evaluaciones">
            <div class="panel__header">
                <h2 class="panel__title">Listado de evaluaciones</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Código</th>
                            <th scope="col">Período</th>
                            <th scope="col">Alcance</th>
                            <th scope="col" class="col-center">Versión</th>
                            <th scope="col" class="col-center">Eventos</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Responsable</th>
                            <th scope="col" class="col-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($evaluaciones as $e)
                            <tr class="row--{{ $e['estado'] }}">
                                <td class="col-code">{{ $e['codigo'] }}</td>
                                <td class="col-period">{{ $e['inicio'] }} → {{ $e['fin'] }}</td>
                                <td class="col-scope" title="{{ $e['alcance'] }}">{{ $e['alcance'] }}</td>
                                <td class="col-center col-muted">{{ $e['version'] }}</td>
                                <td class="col-center col-strong">{{ $e['eventos'] }}</td>
                                <td><span class="badge badge--{{ $e['estado'] }}">{{ $estados[$e['estado']] }}</span></td>
                                <td>{{ $e['responsable'] }}</td>
                                <td class="col-center"><a href="{{ Route::has('evaluaciones.show') ? route('evaluaciones.show', $e['id']) : '#' }}" class="row-link">{{ $e['estado'] === 'abierta' ? 'Abrir' : 'Ver' }}</a></td>                    </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="col-center col-muted">No hay evaluaciones para mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Pie: contador + nota --}}
        <footer class="page-footer">
            <p class="page-count">Mostrando {{ $mostrando }} de {{ $total }}</p>

            <aside class="note">
                <div class="note__inner">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                    <p>Al pulsar <strong>«+ Nueva evaluación»</strong> se abre el modal (imagen 03b). Esta pantalla debe verse completa.</p>
                </div>
            </aside>
        </footer>
    </div>

    {{-- Modal: Nueva evaluación --}}
    @include('evaluaciones.modal')
@endsection

@push('scripts')
    <script src="{{ asset('js/evaluaciones.js') }}"></script>
@endpush
 