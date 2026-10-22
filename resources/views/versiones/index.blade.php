@extends('layouts.app')

@php
    // $versiones, $parametros, $baseSel, $compSel y $proponente llegan desde VersionesController@index.
    // Si no llegan, la vista se muestra igual, sin versiones.
    $versiones  = $versiones ?? [];
    $parametros = $parametros ?? [];
    $baseSel    = $baseSel ?? null;
    $compSel    = $compSel ?? null;

    // Ícono de cada parámetro (la clave es "peso_" + código del factor en minúsculas); los demás usan el de por defecto
    $iconoDefecto = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    $iconos = [
        'peso_monto'        => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'peso_frecuencia'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'peso_producto'     => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'peso_canal'        => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
        'peso_jurisdiccion' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'peso_perfil'       => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'peso_pep_sanc'     => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
        'dato_faltante'     => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];
    $parametros = collect($parametros)->map(function ($p) use ($iconos, $iconoDefecto) {
        $p['icono'] = $iconos[$p['clave']] ?? $iconoDefecto;
        return $p;
    })->all();

    $estados = ['borrador' => 'Borrador', 'propuesta' => 'Propuesta', 'aprobada' => 'Aprobada', 'rechazada' => 'Rechazada', 'obsoleta' => 'Obsoleta'];

    $porVersion = collect($versiones)->keyBy('version');
    $base = $porVersion[$baseSel] ?? ($versiones[0] ?? null);
    $comp = $porVersion[$compSel] ?? ($versiones[0] ?? null);

    // Datos para el botón "Comparar" (se leen desde versiones.js)
    $datosComparar = [
        'versiones'  => $porVersion->map(fn ($v) => $v['parametros'])->all(),
        'parametros' => $parametros,
    ];
@endphp

@section('title', 'Versiones de parámetros · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Parámetros</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Versiones</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">Versiones de parámetros</h1>
                <span class="tag-count">{{ count($versiones) }} {{ count($versiones) === 1 ? 'iteración registrada' : 'iteraciones registradas' }}</span>
            </div>
        </div>

        <button type="button" class="btn-secondary" data-modal-open="modal-nueva-version">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            Nueva versión (clonar)
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
        @if ($errors->any())
            <div class="alert alert--error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Ciclo de vida y control dual --}}
        <section class="panel lifecycle" aria-label="Ciclo de vida de una versión">
            <div class="lifecycle__flow">
                <span class="step st--borrador">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Borrador
                </span>
                <svg class="lifecycle__arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <span class="step st--propuesta">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Propuesta
                </span>
                <svg class="lifecycle__arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <span class="step st--aprobada">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Aprobada
                </span>
                <span class="lifecycle__sep">/</span>
                <span class="step st--rechazada">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Rechazada
                </span>
                <span class="lifecycle__sep">/</span>
                <span class="step st--obsoleta">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Obsoleta
                </span>
            </div>
            <p class="lifecycle__note">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                Solo se edita en borrador. Aprueba o rechaza alguien distinto de quien propone (principio de cuatro ojos / control dual).
            </p>
        </section>

        {{-- Historial de versiones --}}
        <section class="panel" aria-label="Historial de versiones">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Historial de versiones y parámetros de ponderación</h2>
                </div>
                <span class="panel__meta">MOTOR REGULATORIO AML</span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Versión</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Proponente</th>
                            <th scope="col">Aprobador</th>
                            <th scope="col">Vigencia</th>
                            <th scope="col">Motivo</th>
                            <th scope="col" class="col-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($versiones as $v)
                            <tr class="st--{{ $v['estado'] }}">
                                <td class="col-version">{{ $v['version'] }}</td>
                                <td><span class="state state--{{ $v['estado'] }}">{{ $estados[$v['estado']] ?? $v['estado'] }}</span></td>
                                <td class="col-mono col-muted">{{ $v['proponente'] }}</td>
                                <td class="col-mono {{ $v['aprobador'] ? '' : 'col-dim' }}">{{ $v['aprobador'] ?? '—' }}</td>
                                <td class="col-mono {{ $v['vigencia'] ? 'col-vigente' : 'col-dim' }}">{{ $v['vigencia'] ?? '—' }}</td>
                                <td class="col-motivo" title="{{ $v['motivo'] }}">{{ $v['motivo'] }}</td>
                                <td class="col-right">
                                    @if ($v['estado'] === 'borrador')
                                        <a href="{{ Route::has('versiones.edit') ? route('versiones.edit', $v['id']) : '#' }}" class="btn-secondary btn--sm">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                            Editar
                                        </a>
                                    @elseif ($v['estado'] === 'propuesta')
                                        <a href="{{ Route::has('versiones.revisar') ? route('versiones.revisar', $v['id']) : '#' }}" class="btn-secondary btn--sm">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                            Revisar
                                        </a>
                                    @else
                                        <span class="actions">
                                            <a href="{{ Route::has('versiones.ver') ? route('versiones.ver', $v['id']) : '#' }}" class="link">Ver</a>
                                            <span class="actions__sep">·</span>
                                            <button type="button" class="link" data-comparar="{{ $v['version'] }}">Comparar</button>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="col-empty">Aún no hay versiones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Comparar versiones --}}
        @if ($base && $comp)
            <section class="panel compare" id="panel-comparar" aria-label="Comparar versiones">
                <div class="compare__bar">
                    <div class="compare__intro">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        <div>
                            <h2 class="panel__title">Comparar versiones</h2>
                            <p class="panel__sub">Análisis diferencial de parámetros entre líneas base y propuestas activas</p>
                        </div>
                    </div>

                    <div class="compare__controls">
                        <div>
                            <label class="field-label" for="cmp-base">Base</label>
                            <select id="cmp-base" class="control">
                                @foreach ($versiones as $v)
                                    <option value="{{ $v['version'] }}" {{ $baseSel === $v['version'] ? 'selected' : '' }}>{{ $v['version'] }} · {{ strtolower($estados[$v['estado']] ?? $v['estado']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label" for="cmp-comp">Comparar con</label>
                            <select id="cmp-comp" class="control">
                                @foreach ($versiones as $v)
                                    <option value="{{ $v['version'] }}" {{ $compSel === $v['version'] ? 'selected' : '' }}>{{ $v['version'] }} · {{ strtolower($estados[$v['estado']] ?? $v['estado']) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="btn-comparar" class="btn-secondary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            Comparar
                        </button>
                    </div>
                </div>

                <div class="compare__grid">
                    {{-- Desglose de pesos y umbrales --}}
                    <div class="diff">
                        <div class="diff__head">
                            <span>Desglose de pesos y umbrales</span>
                            <span id="diff-conteo">{{ count($parametros) }} factores auditados</span>
                        </div>
                        <div class="diff__scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th scope="col">Parámetro</th>
                                        <th scope="col" class="col-center col-mono" id="th-base">{{ $base['version'] }}</th>
                                        <th scope="col" class="col-center col-mono" id="th-comp">{{ $comp['version'] }}</th>
                                        <th scope="col" class="col-center col-mono">Δ</th>
                                    </tr>
                                </thead>
                                <tbody id="diff-cuerpo">
                                    @foreach ($parametros as $p)
                                        @php
                                            $a = $base['parametros'][$p['clave']] ?? 0;
                                            $b = $comp['parametros'][$p['clave']] ?? 0;
                                            $d = $b - $a;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="diff__param">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="{{ $p['icono'] }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                                    <span>{{ $p['label'] }}</span>
                                                </div>
                                            </td>
                                            <td class="col-center col-mono">{{ $a }}</td>
                                            <td class="col-center col-mono col-new">{{ $b }}</td>
                                            <td class="col-center"><span class="delta {{ $d > 0 ? 'delta--up' : ($d < 0 ? 'delta--down' : '') }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Alcance de auditoría (nota amarilla) --}}
                    <aside class="note">
                        <div class="note__main">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <div>
                                <span class="note__title">Alcance de auditoría de calibración</span>
                                <p>Muestra diferencias de pesos y topes. Las escalas, bandas, agravantes, controles y reglas se comparan al revisar una versión propuesta.</p>
                            </div>
                        </div>
                        <div class="note__foot">
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Vigente para Auditoría Interna
                            </span>
                            <span>Norma AML-702</span>
                        </div>
                    </aside>
                </div>
            </section>
        @endif
    </div>

    {{-- Datos que usa el botón "Comparar" --}}
    <script type="application/json" id="versiones-data">@json($datosComparar)</script>

    {{-- Modal "Nueva versión (clonar)" --}}
    @include('versiones.modal')
@endsection

@push('scripts')
    <script src="{{ asset('js/versiones.js') }}"></script>
@endpush
