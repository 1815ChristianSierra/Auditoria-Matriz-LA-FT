{{--
    Pestaña "Factores" del catálogo.
    La incluye catalogos/index.blade.php dentro del panel data-panel="factores".
    Usa las clases base de catalogos.css y carga su propio CSS y JS.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogos_factores.css') }}">
@endpush

@php
    // $factores llega desde CatalogosController@index; si no llega, la vista se muestra igual con la lista vacía.
    $factores = $factores ?? collect();

    $totalFactores   = count($factores);
    $activosFactores = collect($factores)->where('activo', true)->count();
@endphp

{{-- Tabla de factores --}}
<section class="panel panel--table" aria-label="Factores registrados">
    <div class="fct-toolbar">
        <div class="fct-toolbar__left">
            <span class="fct-toolbar__title">Parámetros registrados</span>
            <span class="fct-toolbar__dot"></span>
            <span class="fct-toolbar__sub">Catálogo de factores base</span>
        </div>
        <span class="fct-toolbar__count" id="factores-total">{{ $totalFactores }} {{ $totalFactores === 1 ? 'elemento' : 'elementos' }}</span>
    </div>

    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col" class="fct-w-cod">Código</th>
                    <th scope="col" class="fct-w-nom">Nombre</th>
                    <th scope="col" class="fct-w-desc">Descripción</th>
                    <th scope="col" class="fct-w-act col-right">Activo</th>
                </tr>
            </thead>
            <tbody id="factores-filas">
                @forelse ($factores as $f)
                    <tr>
                        <td class="col-code" data-codigo="{{ $f['codigo'] }}"><span class="fct-chip">{{ $f['codigo'] }}</span></td>
                        <td class="col-name" title="{{ $f['nombre'] }}">{{ $f['nombre'] }}</td>
                        <td class="col-desc" title="{{ $f['descripcion'] }}">{{ $f['descripcion'] }}</td>
                        <td class="col-right">
                            <label class="toggle">
                                <input type="checkbox" name="activo[{{ $f['codigo'] }}]" value="1" data-url="{{ Route::has('catalogos.activo') ? route('catalogos.activo', ['tipo' => 'factores', 'codigo' => $f['codigo']]) : '' }}" aria-label="Estado de {{ $f['codigo'] }}" {{ $f['activo'] ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="col-empty">Aún no hay factores registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Formulario "Nuevo factor" --}}
<section class="panel" aria-label="Nuevo factor">
    <div class="fct-head">
        <h2 class="fct-head__title">Nuevo factor</h2>
        <span class="fct-head__tag">Formulario de registro</span>
    </div>

    <div class="fct-body">

        {{-- Guarda en CatalogosController@storeFactor (ruta catalogos.factores.store) --}}
        <form method="POST" action="{{ Route::has('catalogos.factores.store') ? route('catalogos.factores.store') : '#' }}" id="form-factor" class="fct-form">
            @csrf

            <div class="fct-fields">
                <div>
                    <label class="fct-label" for="f-codigo"><span>Código <span class="required">*</span></span><small>identificador único</small></label>
                    <input type="text" id="f-codigo" name="codigo" class="control control--mono fct-upper" placeholder="Ej.: MONTO" value="{{ old('codigo') }}" autocomplete="off" required>
                </div>
                <div>
                    <label class="fct-label" for="f-nombre"><span>Nombre <span class="required">*</span></span><small>nombre visible</small></label>
                    <input type="text" id="f-nombre" name="nombre" class="control" placeholder="Nombre" value="{{ old('nombre') }}" required>
                </div>
            </div>

            <div>
                <label class="fct-label" for="f-desc"><span>Descripción</span><small>alcance metodológico</small></label>
                <textarea id="f-desc" name="descripcion" rows="3" class="control control--textarea" placeholder="Describa qué mide o qué representa...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="fct-actions">
                <button type="submit" class="btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Guardar
                </button>
                <button type="reset" class="btn-ghost">Cancelar</button>
            </div>
        </form>

        {{-- Nota técnica + factores activos --}}
        <div class="fct-side">
            <aside class="fct-note">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <div>
                    <span class="fct-note__title">Nota técnica de calibración</span>
                    <p>El peso y la escala (niveles 0 a 5) de cada factor se definen por versión, en <strong>«Editar versión»</strong>.</p>
                    <div class="fct-note__foot">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Matriz multivariada V3.4 (vigente)
                    </div>
                </div>
            </aside>

            <div class="fct-counter">
                <div>
                    <span class="fct-counter__label">Factores activos</span>
                    <span class="fct-counter__value" id="factores-activos">{{ $activosFactores }} / {{ $totalFactores }}</span>
                </div>
                <div class="fct-bars" aria-hidden="true">
                    <span style="height:1.5rem; opacity:.8"></span>
                    <span style="height:2rem"></span>
                    <span style="height:1.25rem; opacity:.7"></span>
                    <span style="height:2.25rem"></span>
                    <span style="height:1.75rem; opacity:.9"></span>
                    <span style="height:1.5rem; opacity:.6"></span>
                    <span style="height:2rem"></span>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script src="{{ asset('js/catalogos_factores.js') }}"></script>
@endpush
 