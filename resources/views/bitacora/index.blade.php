@extends('layouts.app')

@php
    // $registros, $integridad, $seleccionado, $desde y $hasta llegan desde BitacoraController@index.
    // Si no llegan, la vista se muestra igual, sin registros.
    $registros    = $registros ?? [];
    $integridad   = $integridad ?? ['estado' => 'vacia', 'registros' => 0, 'ultimo_hash' => '', 'ids' => [], 'mensaje' => 'La bitácora aún no tiene registros.', 'hora' => ''];
    $seleccionado = $seleccionado ?? null;

    $iconoIntegridad = ['ok' => '✓', 'vacia' => '–', 'alterada' => '!', 'error' => '!'];
    $accionClase = ['CALCULAR_RESULTADO' => 'info', 'APROBAR_VERSION' => 'info', 'CREAR_VERSION' => 'ok', 'RECHAZAR_VERSION' => 'no'];

    $coleccion = collect($registros);
    $usuarios = $coleccion->pluck('usuario')->unique()->sort()->values();
    $acciones = $coleccion->pluck('accion')->unique()->values();
    $entidades = $coleccion->pluck('entidad_tipo')->unique()->values();

    $desde = $desde ?? date('Y-m-d');
    $hasta = $hasta ?? date('Y-m-d');

    // Muestra el JSON con sangría de 2 espacios
    $json = fn($valor) => $valor === null
        ? 'null'
        : str_replace(
            '    ',
            '  ',
            json_encode($valor, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
@endphp

@section('title', 'Bitácora de auditoría · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/bitacora.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Control</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Bitácora</li>
                </ol>
            </nav>
            <h1 class="page-title">Bitácora de auditoría</h1>
        </div>

        <button type="button" class="btn-secondary" id="btn-exportar">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="2" />
            </svg>
            Exportar CSV
        </button>
    </header>

    {{-- Aviso --}}
    <div class="info-banner">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                stroke-linejoin="round" stroke-width="2" />
        </svg>
        <span>Registro inmutable de transacciones y cambios de estado con verificación criptográfica.</span>
    </div>

    <div class="page-body">

        {{-- Filtros --}}
        <section class="filters" aria-label="Filtros de la bitácora">
            <form id="form-filtros" class="filters__row" novalidate>
                <div>
                    <label class="field-label" for="f-desde">Desde</label>
                    <input type="date" id="f-desde" name="desde" class="control control--mono"
                        value="{{ $desde }}">
                </div>
                <div>
                    <label class="field-label" for="f-hasta">Hasta</label>
                    <input type="date" id="f-hasta" name="hasta" class="control control--mono"
                        value="{{ $hasta }}">
                </div>
                <div>
                    <label class="field-label" for="f-usuario">Usuario</label>
                    <select id="f-usuario" name="usuario" class="control">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="f-accion">Acción</label>
                    <select id="f-accion" name="accion" class="control">
                        <option value="">Todas</option>
                        @foreach ($acciones as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label" for="f-entidad">Entidad</label>
                    <select id="f-entidad" name="entidad" class="control">
                        <option value="">Todas</option>
                        @foreach ($entidades as $e)
                            <option value="{{ $e }}">{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-secondary btn--block">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        Filtrar
                    </button>
                </div>
            </form>
        </section>

        {{-- Integridad de la cadena de hashes (el resultado lo calcula BitacoraController) --}}
        <div class="integrity integrity--{{ $integridad['estado'] }}" id="integridad" role="status">
            <div class="integrity__main">
                <span class="integrity__ok" id="integridad-icono">{{ $iconoIntegridad[$integridad['estado']] ?? '!' }}</span>
                <span id="integridad-texto">{{ $integridad['mensaje'] }}</span>
                <span class="integrity__hash" id="integridad-hash" {{ $integridad['estado'] === 'ok' && $integridad['ultimo_hash'] ? '' : 'hidden' }}>{{ $integridad['ultimo_hash'] }}...</span>
            </div>
            <button type="button" class="integrity__btn" id="btn-verificar" data-url="{{ Route::has('bitacora.verificar') ? route('bitacora.verificar') : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                </svg>
                Verificar integridad
            </button>
        </div>

        {{-- Tabla de auditoría --}}
        <section class="panel" aria-label="Registros de auditoría">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="w-id col-center">ID</th>
                        <th scope="col" class="w-fecha">Fecha y hora</th>
                        <th scope="col" class="w-user">Usuario</th>
                        <th scope="col" class="w-accion">Acción</th>
                        <th scope="col" class="w-entidad">Entidad</th>
                        <th scope="col" class="w-motivo">Motivo</th>
                        <th scope="col" class="w-aprob">Aprobador</th>
                        <th scope="col" class="w-hash">Hash</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registros as $r)
                        <tr class="{{ $r['id'] === $seleccionado ? 'is-selected' : '' }} {{ in_array($r['id'], $integridad['ids'], true) ? 'is-altered' : '' }}"
                            data-registro="{{ $r['id'] }}" data-id="{{ $r['id'] }}"
                            data-fecha="{{ $r['fecha'] }}" data-usuario="{{ $r['usuario'] }}"
                            data-accion="{{ $r['accion'] }}" data-entidad="{{ $r['entidad'] }}"
                            data-entidad-tipo="{{ $r['entidad_tipo'] }}" data-motivo="{{ $r['motivo'] }}"
                            data-aprobador="{{ $r['aprobador'] }}" data-hash="{{ $r['hash'] }}" tabindex="0">
                            <td class="col-center col-mono col-muted col-id">#{{ $r['id'] }}</td>
                            <td class="col-mono">{{ $r['fecha'] }}</td>
                            <td class="col-user">{{ $r['usuario'] }}</td>
                            <td><span
                                    class="act act--{{ $accionClase[$r['accion']] ?? 'info' }}">{{ $r['accion'] }}</span>
                            </td>
                            <td class="col-entidad" title="{{ $r['entidad'] }}">{{ $r['entidad'] }}</td>
                            <td class="col-muted" title="{{ $r['motivo'] }}">{{ $r['motivo'] }}</td>
                            <td class="col-muted col-aprob">{{ $r['aprobador'] ?? '—' }}</td>
                            <td class="col-mono col-muted col-hash">{{ $r['hash'] }}</td>
                        </tr>
                    @endforeach
                    @if (!count($registros))
                        <tr>
                            <td colspan="8" class="col-empty">Aún no hay registros en la bitácora.</td>
                        </tr>
                    @endif
                    <tr id="fila-vacia" hidden>
                        <td colspan="8" class="col-empty">No hay registros para este filtro.</td>
                    </tr>
                </tbody>
            </table>
        </section>

        {{-- Detalle del registro seleccionado --}}
        @foreach ($registros as $r)
            <section class="panel detail" data-detalle="{{ $r['id'] }}"
                aria-label="Detalle del registro #{{ $r['id'] }}" {{ $r['id'] === $seleccionado ? '' : 'hidden' }}>
                <div class="detail__head">
                    <h2 class="detail__title">Detalle del registro #{{ $r['id'] }} · {{ $r['accion'] }}</h2>
                    <span class="tag-module">Módulo: {{ $r['modulo'] }}</span>
                </div>

                <div class="diff">
                    <div>
                        <div class="diff__label"><span>Valor anterior</span><small>estado_previo</small></div>
                        <div class="code code--old">
                            <pre><code>{{ $json($r['anterior']) }}</code></pre>
                        </div>
                    </div>
                    <div>
                        <div class="diff__label diff__label--new"><span>Valor nuevo</span><small>estado_actualizado</small>
                        </div>
                        <div class="code code--new">
                            <pre><code>{{ $json($r['nuevo']) }}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="detail__foot">
                    <div class="detail__hashes">
                        <span>Hash anterior:</span>
                        <span class="hash">{{ $r['hash_anterior'] ? $r['hash_anterior'] . '...' : 'inicio de la cadena' }}</span>
                        <span class="sep">·</span>
                        <span>Hash de este registro:</span>
                        <span class="hash hash--this">{{ $r['hash'] }}...</span>
                    </div>
                    <div class="note">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                        </svg>
                        Solo lectura: no se edita ni se borra. (WORM Compliance)
                    </div>
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/bitacora.js') }}"></script>
@endpush
 