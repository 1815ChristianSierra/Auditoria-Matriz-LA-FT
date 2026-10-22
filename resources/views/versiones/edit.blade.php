@extends('layouts.app')

@php
    // $version, $factores, $escalas y los datos de cada pestaña llegan desde VersionesController@edit.
    $version  = $version ?? ['id' => null, 'codigo' => '—', 'estado' => '', 'creada' => '—', 'motivo' => ''];
    $factores = $factores ?? [];
    $escalas  = $escalas ?? [];

    // Modo lectura (pantalla "Ver"): sin botones de guardar y con los campos bloqueados
    $soloLectura = $soloLectura ?? false;
    $decision    = $decision ?? null;

    // Ícono de cada factor (la clave es su código en minúsculas); los demás usan el ícono por defecto
    $iconos = [
        'monto' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'frecuencia' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'producto' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'canal' => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
        'jurisdiccion' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'perfil' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'pep' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
        'pep_sanc' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
    ];
    $iconoDefecto = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';

    $tabs = [
        'pesos'      => ['Pesos y escalas', 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
        'bandas'     => ['Bandas y topes', 'M8 7h12M8 12h12M8 17h12M3 7h.01M3 12h.01M3 17h.01'],
        'agravantes' => ['Agravantes',     'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        'controles'  => ['Controles',      'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        'reglas'     => ['Reglas',         'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    ];

    $primero = $factores[0]['clave'] ?? '';
    $suma    = collect($factores)->sum('peso');
    $sumaOk  = abs($suma - 100) < 0.001;
@endphp

@section('title', ($soloLectura ? 'Ver ' : 'Editar ') . $version['codigo'] . ' · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_editar.css') }}">
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
                    <li class="breadcrumb__current">{{ $version['codigo'] }}</li>
                </ol>
            </nav>
            <div class="page-title-row">
                <h1 class="page-title">{{ $soloLectura ? 'Ver' : 'Editar' }} {{ $version['codigo'] }}</h1>
                <span class="state state--{{ strtolower($version['estado']) }}">{{ $version['estado'] }}</span>
            </div>
        </div>

        <div class="page-actions">
            @if ($soloLectura)
                <a href="{{ Route::has('versiones.index') ? route('versiones.index') : '#' }}" class="btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Volver al listado
                </a>
            @else
                <button type="submit" form="form-version" name="accion" value="borrador" class="btn-secondary" formnovalidate>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Guardar borrador
                </button>
                <button type="submit" form="form-version" name="accion" value="proponer" id="btn-proponer" class="btn-secondary" {{ $sumaOk ? '' : 'disabled' }}>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Proponer para aprobación
                </button>
            @endif
        </div>
    </header>

    {{-- Guarda en VersionesController@update (ruta versiones.update) --}}
    <form method="POST" action="{{ ! $soloLectura && Route::has('versiones.update') && $version['id'] ? route('versiones.update', $version['id']) : '#' }}" id="form-version" class="page-body {{ $soloLectura ? 'is-readonly' : '' }}">
        @unless ($soloLectura)
            @csrf
            @method('PUT')
        @endunless

        {{-- Mensajes del controlador --}}
        @if (session('success'))
            <div class="alert alert--ok" role="status">{{ session('success') }}</div>
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

        {{-- Pestañas de configuración --}}
        <nav class="tabs" role="tablist" aria-label="Secciones de la versión">
            @foreach ($tabs as $clave => [$texto, $icono])
                <button type="button" role="tab" class="tab {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $clave }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="{{ $icono }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    {{ $texto }}
                </button>
            @endforeach
        </nav>

        <div class="tab-panels">

            {{-- Pestaña: Pesos y escalas --}}
            <div data-panel="pesos">
                <div class="workbench">

                    {{-- Pesos por factor --}}
                    <section class="panel panel--col" aria-label="Pesos por factor">
                        <div class="panel__head">
                            <div class="panel__head-left">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <h2 class="panel__title">Pesos por factor</h2>
                            </div>
                            <span class="tag">Modelo AML ponderado</span>
                        </div>

                        <div class="list-area">
                            @foreach ($factores as $f)
                                <div class="factor-row {{ $f['clave'] === $primero ? 'is-selected' : '' }}" data-factor="{{ $f['clave'] }}">
                                    <div class="factor-row__name">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="{{ $iconos[$f['clave']] ?? $iconoDefecto }}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                        <span>{{ $f['nombre'] }}</span>
                                    </div>
                                    <div class="factor-row__weight">
                                        <input type="number" name="pesos[{{ $f['clave'] }}]" class="control control--weight" min="0" max="100" value="{{ old('pesos.' . $f['clave'], $f['peso']) }}" data-peso aria-label="Peso de {{ $f['nombre'] }}">
                                        <span>%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="totalizer">
                            <div class="totalizer__row">
                                <span class="totalizer__label">Total</span>
                                <span class="totalizer__value {{ $sumaOk ? '' : 'is-bad' }}" id="total-pesos">{{ $suma }} %</span>
                            </div>
                            <div class="status {{ $sumaOk ? 'status--ok' : 'status--bad' }}" id="estado-pesos">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <span id="estado-pesos-texto">{{ $sumaOk ? 'Suma 100 %: se puede proponer' : 'La suma actual es ' . $suma . ' %. Debe sumar exactamente 100 % para proponer.' }}</span>
                            </div>
                        </div>
                    </section>

                    {{-- Escala del factor --}}
                    <section class="panel panel--col" aria-label="Escala del factor">
                        <div class="panel__head">
                            <div class="panel__head-left">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <h2 class="panel__title">Escala del factor</h2>
                            </div>
                            <span class="tag tag--mono">6 niveles (0-5)</span>
                        </div>

                        <div class="scale-top">
                            <label class="field-label" for="factor-select">Factor seleccionado</label>
                            <select id="factor-select" class="control">
                                @foreach ($factores as $f)
                                    <option value="{{ $f['clave'] }}" {{ $f['clave'] === $primero ? 'selected' : '' }}>{{ $f['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="list-area">
                            @foreach ($factores as $f)
                                <div class="scale-group" data-escala="{{ $f['clave'] }}" {{ $f['clave'] === $primero ? '' : 'hidden' }}>
                                    @for ($n = 0; $n <= 5; $n++)
                                        @php $texto = $escalas[$f['clave']][$n] ?? ''; @endphp
                                        <div class="level">
                                            <span class="level__name {{ $texto !== '' ? 'is-key' : '' }}">Nivel {{ $n }}</span>
                                            <input type="text" name="escalas[{{ $f['clave'] }}][{{ $n }}]" class="control" value="{{ old('escalas.' . $f['clave'] . '.' . $n, $texto) }}" placeholder="Por definir y justificar por el equipo" aria-label="Nivel {{ $n }} de {{ $f['nombre'] }}">
                                        </div>
                                    @endfor
                                </div>
                            @endforeach
                        </div>

                        <div class="panel__foot">
                            <span>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                Valores ordinales evaluados 0-100 pts
                            </span>
                            <span>Mapeo {{ $version['codigo'] }}</span>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Otras pestañas: cada una es un archivo en versiones/tabs --}}
            @foreach ($tabs as $clave => [$texto, $icono])
                @continue($clave === 'pesos')
                <div data-panel="{{ $clave }}" hidden>
                    @include('versiones.tabs.' . $clave)
                </div>
            @endforeach
        </div>

        {{-- Motivo del cambio --}}
        <section class="panel motivo" aria-label="Motivo del cambio">
            <div class="motivo__head">
                <label class="motivo__label" for="motivo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Motivo del cambio @unless ($soloLectura)(obligatorio) <span class="required">*</span>@endunless
                </label>
                <span class="motivo__ref">Creada: {{ $version['creada'] }}</span>
            </div>
            <textarea id="motivo" name="motivo" rows="2" class="control control--textarea" required>{{ old('motivo', $version['motivo']) }}</textarea>
        </section>

        {{-- Decisión (solo en modo lectura, si la versión ya fue aprobada o rechazada) --}}
        @if ($soloLectura && $decision)
            <section class="panel motivo" aria-label="Decisión">
                <div class="motivo__head">
                    <span class="motivo__label">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Decisión de la versión
                    </span>
                    <span class="motivo__ref">{{ $decision['quien'] }} · {{ $decision['fecha'] }}@if ($decision['vigencia']) · Vigente desde {{ $decision['vigencia'] }}@endif</span>
                </div>
                <textarea rows="2" class="control control--textarea" readonly aria-label="Motivo de la decisión">{{ $decision['motivo'] }}</textarea>
            </section>
        @endif

        {{-- Gobernanza --}}
        <aside class="gov">
            <div class="gov__icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            </div>
            <div class="gov__main">
                <div class="gov__title">Gobernanza de modelo LA/FT <small>· Principio de 4 ojos</small></div>
                <p>Solo se edita en borrador. Al proponer, la versión se bloquea y la revisa alguien distinto de quien la propuso. Los cambios tendrán vigencia a partir de su aprobación formal por el Comité de Riesgo Operacional.</p>
            </div>
            <span class="gov__lock">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                INMUTABILIDAD ACTIVA
            </span>
        </aside>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/versiones_editar.js') }}"></script>
    @if ($soloLectura)
        <script src="{{ asset('js/versiones_ver.js') }}"></script>
    @endif
@endpush
 