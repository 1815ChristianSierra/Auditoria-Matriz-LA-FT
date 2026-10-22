@extends('layouts.app')

@php
    // $registros y $seleccionado llegan desde CoincidenciasController@index (por id de coincidencia).
    // Si no llegan, la vista se muestra igual, sin registros.
    $registros    = $registros ?? [];
    $seleccionado = $seleccionado ?? null;

    $estados = ['parcial' => 'Parcial', 'confirmada' => 'Confirmada', 'descartada' => 'Descartada'];
    $pendientes = collect($registros)->where('estado', 'parcial')->count();
@endphp

@section('title', 'Listas y coincidencias · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/listas.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Operación</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Listas y coincidencias</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Listas y coincidencias</h1>
                @if ($pendientes)
                    <span class="tag-pending">{{ $pendientes }} PENDIENTES</span>
                @endif
            </div>
        </div>

        {{-- Filtros --}}
        <div class="pills" role="group" aria-label="Filtros">
            <button type="button" class="pills__btn is-active" data-filtro="pendientes" aria-pressed="true">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                Por verificar ({{ $pendientes }})
            </button>
            <button type="button" class="pills__btn" data-filtro="todas" aria-pressed="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M3 14h18M3 6h18M3 18h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                Todas
            </button>
            <button type="button" class="pills__btn" data-filtro="sinteticas" aria-pressed="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                Listas sintéticas
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

        {{-- Registro de coincidencias --}}
        <section class="panel" aria-label="Registro de coincidencias">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Registro de coincidencias en listas de control</h2>
                </div>
                <span class="panel__meta">MOTOR PEP/OFAC · SIMULACIÓN ACTIVA</span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Cliente</th>
                            <th scope="col">Registro en lista</th>
                            <th scope="col">Tipo lista</th>
                            <th scope="col">Coincidencia</th>
                            <th scope="col">Verificada por</th>
                            <th scope="col">Fecha</th>
                            <th scope="col" class="col-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $id => $r)
                            <tr class="{{ $id === $seleccionado ? 'is-selected' : '' }}"
                                data-registro="{{ $id }}"
                                data-url="{{ Route::has('coincidencias.update') ? route('coincidencias.update', $id) : '' }}"
                                data-estado="{{ $r['estado'] }}"
                                data-sintetica="{{ $r['sintetica'] ? '1' : '0' }}"
                                tabindex="0">
                                <td><span class="cell-code">{{ $r['codigo'] }}</span></td>
                                <td class="col-name">{{ $r['nombre'] }}</td>
                                <td><span class="type-tag type-tag--{{ strtolower($r['tipo_lista']) }}">{{ $r['tipo_lista'] }}</span></td>
                                <td><span class="state state--{{ $r['estado'] }}">{{ $estados[$r['estado']] }}</span></td>
                                <td class="col-mono col-muted">{{ $r['verificada_por'] ?? '—' }}</td>
                                <td class="col-mono col-muted">{{ $r['fecha'] ?? '—' }}</td>
                                <td class="col-right">
                                    @if ($r['estado'] === 'parcial')
                                        <button type="button" class="btn-secondary btn--sm">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                            Verificar
                                        </button>
                                    @else
                                        <button type="button" class="btn-ghost btn--sm">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                            Ver
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @if (!count($registros))
                            <tr>
                                <td colspan="7" class="col-empty">Aún no hay coincidencias registradas.</td>
                            </tr>
                        @endif
                        <tr id="fila-vacia" hidden>
                            <td colspan="7" class="col-empty">No hay registros para este filtro.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Comparación del registro seleccionado --}}
        @foreach ($registros as $id => $r)
            <section class="panel" data-comparacion="{{ $id }}" aria-label="Comparación {{ $r['codigo'] }}" {{ $id === $seleccionado ? '' : 'hidden' }}>
                <div class="panel__head">
                    <div class="panel__head-left">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <h2 class="panel__title panel__title--lg">Comparar: {{ $r['codigo'] }} vs. registro de la lista</h2>
                    </div>
                    <span class="compare__tag">
                        Índice Jaro-Winkler:
                        <strong class="{{ $r['jaro'] >= 0.85 ? '' : 'is-low' }}">{{ number_format($r['jaro'], 2) }} {{ $r['jaro'] >= 0.85 ? 'MATCH' : 'BAJA' }}</strong>
                    </span>
                </div>

                <div class="compare__grid">
                    {{-- Cliente --}}
                    <div class="compare__col">
                        <div class="compare__col-head">
                            <span class="compare__col-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Cliente ({{ $r['codigo'] }})
                            </span>
                            <span class="compare__col-ref">{{ $r['entidad'] }}</span>
                        </div>
                        @foreach ($r['cliente'] as $c)
                            <div class="field-row {{ $c['coincide'] === false ? 'field-row--diff' : '' }}">
                                <div class="field-row__main">
                                    <span class="field-row__label">{{ $c['label'] }}</span>
                                    <span class="field-row__value">{{ $c['valor'] }}</span>
                                </div>
                                @if ($c['coincide'] === true)
                                    <span class="flag flag--ok">✓ Coincide</span>
                                @elseif ($c['coincide'] === false)
                                    <span class="flag flag--diff">≠ Diferente</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Registro en lista --}}
                    <div class="compare__col compare__col--lista">
                        <div class="compare__col-head">
                            <span class="compare__col-title">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Registro en lista sintética ({{ $r['tipo_lista'] }})
                            </span>
                            <span class="compare__col-ref">{{ $r['ref'] }}</span>
                        </div>
                        @foreach ($r['lista'] as $l)
                            <div class="field-row">
                                <div class="field-row__main">
                                    <span class="field-row__label">{{ $l['label'] }}</span>
                                    <span class="field-row__value">{{ $l['valor'] }}</span>
                                </div>
                                @if ($l['nota'] !== '')
                                    <span class="field-row__note {{ $l['alerta'] ? 'field-row__note--warn' : '' }}">{{ $l['nota'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach

        {{-- Decisión del analista + nota --}}
        <div class="decision-grid">
            {{-- Guarda en CoincidenciasController@update; el JS pone la ruta del registro seleccionado --}}
            <form method="POST" action="#" id="form-dictamen" class="decision-form" novalidate>
            @csrf
            @method('PUT')
            <section class="panel decision" aria-label="Observación del analista">
                <label class="decision__label" for="observacion">
                    <span>Observación del analista <span class="required">*</span></span>
                    <span class="decision__hint">Obligatorio para auditoría interna</span>
                </label>
                <textarea id="observacion" name="observacion" rows="2" class="control" placeholder="Explique por qué confirma o descarta la coincidencia..." {{ count($registros) ? '' : 'disabled' }}>{{ old('observacion') }}</textarea>
                <p class="field-error" id="observacion-error" hidden>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <span>Escriba la observación (mínimo 10 caracteres) antes de confirmar o descartar.</span>
                </p>
                <div class="decision__actions">
                    <button type="submit" class="btn-danger" name="accion" value="confirmar" data-accion="confirmar" {{ count($registros) ? '' : 'disabled' }}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Confirmar coincidencia
                    </button>
                    <button type="submit" class="btn-secondary" name="accion" value="descartar" data-accion="descartar" {{ count($registros) ? '' : 'disabled' }}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Descartar (falso positivo)
                    </button>
                    <button type="submit" class="btn-ghost" name="accion" value="parcial" data-accion="parcial" {{ count($registros) ? '' : 'disabled' }}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Dejar como parcial
                    </button>
                </div>
            </section>
            </form>

            <aside class="note">
                <div class="note__main">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <div>
                        <span class="note__title">Impacto metodológico del dictamen</span>
                        <p>Confirmar sugiere <strong>nivel 5 en PEP/Sanciones</strong> y puede activar automáticamente la regla <code>ESC-01</code>. La decisión final es potestad exclusiva del analista de cumplimiento.</p>
                    </div>
                </div>
                <div class="note__foot">
                    <span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Reglamento SARLAFT / UIF § 14.3
                    </span>
                    <span>ALERTA ROJA TIPO II</span>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/listas.js') }}"></script>
@endpush
 