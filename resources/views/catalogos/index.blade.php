@extends('layouts.app')

@php
    // $controles, $responsables y $tabActiva llegan desde CatalogosController@index.
    // Si no llegan, la vista se muestra igual con listas vacías.
    $controles    = $controles ?? collect();
    $responsables = $responsables ?? collect();

    // Botón "Crear versión": de cuál se parte y cuántos elementos activos pasarán
    $resumenVersion = $resumenVersion ?? ['siguiente' => 'v1.0', 'base' => null, 'factores' => 0, 'agravantes' => 0, 'controles' => 0];

    $tipos          = ['Preventivo', 'Detectivo', 'Correctivo'];
    $frecuencias    = ['Mensual', 'Por evento', 'Trimestral', 'Anual'];
    $calificaciones = ['Adecuado', 'Mejorable', 'Deficiente'];

    $tabs = ['factores' => 'Factores', 'agravantes' => 'Agravantes', 'controles' => 'Controles'];
    $tabActiva = $tabActiva ?? 'factores';

    // Siguiente código: el número mayor + 1 (CTL-03 → CTL-04)
    $mayor = collect($controles)->map(fn ($c) => (int) preg_replace('/\D/', '', $c['codigo']))->max() ?? 0;
    $siguienteCodigo = sprintf('CTL-%02d', $mayor + 1);
@endphp

@section('title', 'Catálogos · Matriz LA/FT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogos.css') }}">
@endpush

@section('content')

    {{-- Encabezado de página --}}
    <header class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li>Parámetros</li>
                    <li class="breadcrumb__sep">/</li>
                    <li class="breadcrumb__current">Catálogos</li>
                </ol>
            </nav>
            <h1 class="page-title">Catálogos</h1>
        </div>

        <button type="button" class="btn-secondary" data-dialogo-abrir>
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            Crear versión
        </button>
    </header>

    {{-- Diálogo "Crear versión": una versión es una foto del catálogo; pasa solo lo que está activo (en verde) --}}
    <dialog id="dialogo-version" class="dialogo" {{ $errors->has('motivo') ? 'data-abrir' : '' }}>
        <form method="POST" action="{{ Route::has('catalogos.version') ? route('catalogos.version') : '#' }}" class="dialogo__form">
            @csrf
            <h2 class="dialogo__titulo">Crear versión desde el catálogo</h2>

            @if ($resumenVersion['base'])
                <p class="dialogo__texto">
                    Se crea <strong>{{ $resumenVersion['siguiente'] }}</strong> en borrador, a partir de
                    <strong>{{ $resumenVersion['base'] }}</strong>. Pasan a la versión solo los elementos
                    <strong>activos</strong>:
                </p>
                <ul class="dialogo__lista">
                    <li><strong>{{ $resumenVersion['factores'] }}</strong> factores</li>
                    <li><strong>{{ $resumenVersion['agravantes'] }}</strong> agravantes</li>
                    <li><strong>{{ $resumenVersion['controles'] }}</strong> controles</li>
                </ul>
                <p class="dialogo__nota">
                    Lo que esté apagado no se incluye. Lo activo que la versión base no tenía entra con 0 (peso o puntos):
                    se asigna después en «Editar versión».
                </p>

                <label class="field-label" for="version-motivo">Motivo del cambio <span class="required">*</span></label>
                <textarea id="version-motivo" name="motivo" rows="3" class="control" minlength="10" required
                    placeholder="Explique qué cambia respecto a la versión anterior...">{{ old('motivo') }}</textarea>
            @else
                <p class="dialogo__texto">Todavía no hay ninguna versión de la cual partir. Cree la primera desde el listado de versiones.</p>
            @endif

            <div class="dialogo__acciones">
                <button type="button" class="dialogo__cancelar" data-dialogo-cerrar>Cancelar</button>
                <button type="submit" class="btn-secondary" {{ $resumenVersion['base'] ? '' : 'disabled' }}>Crear versión</button>
            </div>
        </form>
    </dialog>

    <div class="page-body">

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

        {{-- Pestañas de subcatálogos --}}
        <nav class="tabs" role="tablist" aria-label="Pestañas de catálogos">
            @foreach ($tabs as $clave => $texto)
                <button type="button" role="tab" class="tab {{ $clave === $tabActiva ? 'is-active' : '' }}" data-tab="{{ $clave }}" aria-selected="{{ $clave === $tabActiva ? 'true' : 'false' }}">{{ $texto }}</button>
            @endforeach
        </nav>

        {{-- Pestaña: Controles (tabla + formulario) --}}
        <div class="tab-panel" data-panel="controles" {{ $tabActiva === 'controles' ? '' : 'hidden' }}>

            <section class="panel panel--table" aria-label="Controles existentes">
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" class="w-codigo">Código</th>
                                <th scope="col" class="w-nombre">Nombre</th>
                                <th scope="col" class="w-tipo">Tipo</th>
                                <th scope="col" class="w-frec">Frecuencia</th>
                                <th scope="col" class="w-resp">Responsable</th>
                                <th scope="col" class="w-diseno col-center">Diseño</th>
                                <th scope="col" class="w-oper col-center">Operación</th>
                                <th scope="col" class="w-activo col-center">Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($controles as $c)
                                <tr>
                                    <td class="col-code">{{ $c['codigo'] }}</td>
                                    <td class="col-name" title="{{ $c['nombre'] }}">{{ $c['nombre'] }}</td>
                                    <td>{{ $c['tipo'] ?: '—' }}</td>
                                    <td>{{ $c['frecuencia'] ?: '—' }}</td>
                                    <td class="col-mono">{{ $c['responsable'] }}</td>
                                    <td class="col-center"><span class="badge badge--{{ strtolower($c['diseno']) }}">{{ $c['diseno'] ?: '—' }}</span></td>
                                    <td class="col-center"><span class="badge badge--{{ strtolower($c['operacion']) }}">{{ $c['operacion'] ?: '—' }}</span></td>
                                    <td class="col-center">
                                        <label class="toggle">
                                            <input type="checkbox" name="activo[{{ $c['codigo'] }}]" value="1" data-url="{{ Route::has('catalogos.activo') ? route('catalogos.activo', ['tipo' => 'controles', 'codigo' => $c['codigo']]) : '' }}" aria-label="Estado de {{ $c['codigo'] }}" {{ $c['activo'] ? 'checked' : '' }}>
                                            <span class="toggle__track"></span>
                                        </label>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="col-empty">Aún no hay controles registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel" aria-label="Nuevo control">
                <h2 class="panel__title">Nuevo control</h2>

                {{-- Guarda en CatalogosController@store (ruta catalogos.store) --}}
                <form method="POST" action="{{ Route::has('catalogos.store') ? route('catalogos.store') : '#' }}" id="form-control" class="form">
                    @csrf

                    <div class="grid">
                        <div class="span-2">
                            <label class="field-label" for="c-codigo">Código</label>
                            <input type="text" id="c-codigo" class="control control--mono" value="{{ $siguienteCodigo }}" readonly disabled>
                        </div>
                        <div class="span-5">
                            <label class="field-label" for="c-nombre">Nombre <span class="required">*</span></label>
                            <input type="text" id="c-nombre" name="nombre" class="control" placeholder="Nombre del control" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="span-2">
                            <label class="field-label" for="c-tipo">Tipo</label>
                            <select id="c-tipo" name="tipo" class="control">
                                @foreach ($tipos as $t)
                                    <option value="{{ $t }}" {{ old('tipo', 'Preventivo') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="span-3">
                            <label class="field-label" for="c-frecuencia">Frecuencia</label>
                            <select id="c-frecuencia" name="frecuencia" class="control">
                                @foreach ($frecuencias as $f)
                                    <option value="{{ $f }}" {{ old('frecuencia', 'Mensual') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid">
                        <div class="span-4">
                            <label class="field-label" for="c-responsable">Responsable</label>
                            <select id="c-responsable" name="responsable" class="control control--mono">
                                @foreach ($responsables as $r)
                                    <option value="{{ $r }}" {{ old('responsable', 'supervisor1') === $r ? 'selected' : '' }}>{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="span-4">
                            <label class="field-label" for="c-diseno">Calificación de diseño</label>
                            <select id="c-diseno" name="calificacion_diseno" class="control">
                                @foreach ($calificaciones as $q)
                                    <option value="{{ $q }}" {{ old('calificacion_diseno', 'Adecuado') === $q ? 'selected' : '' }}>{{ $q }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="span-4">
                            <label class="field-label" for="c-operacion">Calificación de operación</label>
                            <select id="c-operacion" name="calificacion_operacion" class="control">
                                @foreach ($calificaciones as $q)
                                    <option value="{{ $q }}" {{ old('calificacion_operacion', 'Mejorable') === $q ? 'selected' : '' }}>{{ $q }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid">
                        <div class="span-12">
                            <label class="field-label" for="c-descripcion">Descripción</label>
                            <textarea id="c-descripcion" name="descripcion" rows="2" class="control control--textarea" placeholder="Qué hace el control y cómo reduce el riesgo...">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>

                    <div class="form__actions">
                        <div class="form__buttons">
                            <button type="submit" class="btn-secondary">Guardar control</button>
                            <button type="reset" class="btn-ghost">Cancelar</button>
                        </div>

                        <aside class="note">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <p>Los controles se puntúan por versión (<a href="{{ Route::has('versiones.edit') ? route('versiones.edit') : '#' }}">pantalla Editar versión</a>).</p>
                        </aside>
                    </div>
                </form>
            </section>
        </div>

        {{-- Otras pestañas: si existe catalogos/tabs/{pestaña}.blade.php se incluye; si no, se muestra el aviso --}}
        @foreach ($tabs as $clave => $texto)
            @continue($clave === 'controles')
            <div class="tab-panel" data-panel="{{ $clave }}" {{ $tabActiva === $clave ? '' : 'hidden' }}>
                @if (view()->exists('catalogos.tabs.' . $clave))
                    @include('catalogos.tabs.' . $clave)
                @else
                    <div class="tab-empty">La sección «{{ $texto }}» todavía no tiene contenido en el prototipo.</div>
                @endif
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/catalogos.js') }}"></script>
@endpush
 