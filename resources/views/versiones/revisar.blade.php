@extends('layouts.app')

@php
    // $version, $parametros, $escenarios y $puedeDecidir llegan desde VersionesController@revisar.
    // Si no llegan, la vista se muestra igual, sin datos.
    $version      = $version ?? ['id' => null, 'codigo' => '—', 'base' => '—', 'estado' => '', 'folio' => '—', 'proponente' => '—', 'rol_proponente' => '', 'fecha' => '—', 'motivo' => ''];
    $parametros   = $parametros ?? [];     // [parámetro, valor en la versión base, valor en la versión propuesta]
    $escenarios   = $escenarios ?? [];     // [evento, score base, score propuesto, banda]
    $puedeDecidir = $puedeDecidir ?? false;

    $bandas = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Crítico'];
    $minimoMotivo = 10;
    $maxDelta = collect($escenarios)->map(fn ($e) => abs($e[2] - $e[1]))->max() ?? 0;
@endphp

@section('title', 'Revisar versión ' . $version['codigo'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_revisar.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Parámetros</li>
                    <li class="breadcrumb__sep">/</li>
                    <li><a href="{{ Route::has('versiones.index') ? route('versiones.index') : '#' }}">Versiones</a></li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__mono">{{ $version['codigo'] }}</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Revisión</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Revisar versión {{ $version['codigo'] }}</h1>
                <span class="state">{{ $version['estado'] }}</span>
            </div>
        </div>

        <span class="tag-gov">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            Supervisión regulatoria AML · Doble control
        </span>
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

            {{-- Columna izquierda: cambios e impacto --}}
            <div class="col">

                {{-- Cambios frente a la versión base --}}
                <section class="panel" aria-label="Cambios frente a {{ $version['base'] }}">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Cambios frente a {{ $version['base'] }}</h2>
                        </div>
                        <span class="panel__meta">{{ count($parametros) }} parámetros comparados</span>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Parámetro</th>
                                    <th scope="col" class="col-right col-mono w-sm">{{ $version['base'] }}</th>
                                    <th scope="col" class="col-right col-mono w-sm">{{ $version['codigo'] }}</th>
                                    <th scope="col" class="col-center w-md">Cambio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($parametros as [$nombre, $antes, $despues])
                                    @php
                                        $d = $despues - $antes;
                                        $clase = $d > 0 ? 'up' : ($d < 0 ? 'down' : '');
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="param {{ $d != 0 ? 'param--changed param--' . $clase : '' }}">{{ $nombre }}</span>
                                        </td>
                                        <td class="col-right col-mono col-muted">{{ $antes }}</td>
                                        <td class="col-right col-mono {{ $d != 0 ? 'col-new' : 'col-muted' }}">{{ $despues }}</td>
                                        <td class="col-center"><span class="delta {{ $d != 0 ? 'delta--' . $clase : '' }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="col-empty">No hay parámetros para comparar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Impacto simulado --}}
                <section class="panel" aria-label="Impacto simulado">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Impacto simulado en los escenarios sintéticos</h2>
                        </div>
                        @if (count($escenarios))
                            <span class="tag-ok">Δ Var: ±{{ number_format($maxDelta, 1) }} pts máx</span>
                        @endif
                    </div>

                    <div class="table-wrap">
                        <table class="table table--roomy">
                            <thead>
                                <tr>
                                    <th scope="col">Evento</th>
                                    <th scope="col" class="col-right">Score {{ $version['base'] }}</th>
                                    <th scope="col" class="col-right">Score {{ $version['codigo'] }}</th>
                                    <th scope="col" class="col-center w-md">Banda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($escenarios as [$evento, $antes, $despues, $banda])
                                    <tr>
                                        <td class="col-event">{{ $evento }}</td>
                                        <td class="col-right col-mono col-muted">{{ number_format($antes, 1) }}</td>
                                        <td class="col-right col-mono col-new">{{ number_format($despues, 1) }}</td>
                                        <td class="col-center"><span class="badge band--{{ $banda }}">{{ $bandas[$banda] ?? $banda }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="col-empty">La simulación sobre escenarios sintéticos todavía no está conectada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Columna derecha: propuesta, decisión y separación de funciones --}}
            <div class="col">

                {{-- Propuesta --}}
                <section class="panel" aria-label="Propuesta">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg class="is-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Propuesta</h2>
                        </div>
                        <span class="panel__meta panel__meta--mono">FOLIO #{{ $version['folio'] }}</span>
                    </div>
                    <div class="card__body">
                        <div>
                            <span class="kv__label">Proponente</span>
                            <div class="kv__who"><strong>{{ $version['proponente'] }}</strong>@if ($version['rol_proponente'])<span>·</span><span>{{ $version['rol_proponente'] }}</span>@endif</div>
                        </div>
                        <div class="kv-grid">
                            <div>
                                <span class="kv__label">Fecha</span>
                                <span class="kv__value kv__value--mono">{{ $version['fecha'] }}</span>
                            </div>
                            <div>
                                <span class="kv__label">Comparada con</span>
                                <span class="kv__value kv__value--accent">{{ $version['base'] }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="kv__label">Motivo</span>
                            <p class="quote">"{{ $version['motivo'] }}"</p>
                        </div>
                    </div>
                </section>

                {{-- Tu decisión --}}
                <section class="panel" aria-label="Tu decisión">
                    <div class="panel__head">
                        <div class="panel__head-left">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <h2 class="panel__title">Tu decisión</h2>
                        </div>
                        <span class="tag-pending">Pendiente</span>
                    </div>

                    {{-- Guarda en VersionesController@decidir (ruta versiones.decidir) --}}
                    <form method="POST" action="{{ Route::has('versiones.decidir') && $version['id'] ? route('versiones.decidir', $version['id']) : '#' }}" id="form-decision" class="form" novalidate>
                        @csrf

                        @unless ($puedeDecidir)
                            <div class="alert alert--warn" role="note">Usted propuso esta versión, por eso no puede decidir sobre ella. Debe hacerlo otro usuario, supervisor o auditor.</div>
                        @endunless

                        <fieldset class="choices">
                            <legend class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Seleccionar resolución</legend>
                            <label class="choice choice--ok">
                                <input type="radio" name="resolucion" value="aprobar" {{ old('resolucion', 'aprobar') === 'aprobar' ? 'checked' : '' }} {{ $puedeDecidir ? '' : 'disabled' }}>
                                <span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    Aprobar
                                </span>
                            </label>
                            <label class="choice choice--no">
                                <input type="radio" name="resolucion" value="rechazar" {{ old('resolucion') === 'rechazar' ? 'checked' : '' }} {{ $puedeDecidir ? '' : 'disabled' }}>
                                <span>
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    Rechazar
                                </span>
                            </label>
                        </fieldset>

                        <div>
                            <label class="field-label" for="motivo-decision">
                                <span>Motivo de la decisión (obligatorio) <span class="required">*</span></span>
                                <span class="field-hint">Mín. {{ $minimoMotivo }} caracteres</span>
                            </label>
                            <textarea id="motivo-decision" name="motivo_decision" rows="3" class="control" data-min="{{ $minimoMotivo }}" placeholder="Quede constancia de por qué aprueba o rechaza..." {{ $puedeDecidir ? '' : 'disabled' }}>{{ old('motivo_decision') }}</textarea>
                            <p class="field-error" id="motivo-error" hidden>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <span>Escriba al menos {{ $minimoMotivo }} caracteres para dejar constancia.</span>
                            </p>
                        </div>

                        <button type="submit" class="btn-secondary btn--block" {{ $puedeDecidir ? '' : 'disabled' }}>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Confirmar decisión
                        </button>
                        <p class="form__note">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Queda registrado: quién decidió, cuándo y por qué.
                        </p>
                    </form>
                </section>

                {{-- Separación de funciones (nota amarilla) --}}
                <aside class="note">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <span class="note__title">Separación de funciones</span>
                        <p>No puede decidir quien propuso la versión (<code>{{ $version['proponente'] }}</code>). Como supervisor/auditor independiente, su dictamen es vinculante e inmutable.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/versiones_revisa.js') }}"></script>
@endpush
 