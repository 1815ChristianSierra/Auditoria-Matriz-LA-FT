{{--
    Pestaña "Agravantes" del catálogo.
    La incluye catalogos/index.blade.php dentro del panel data-panel="agravantes".
    Usa las clases base de catalogos.css y carga su propio CSS y JS.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalogos_agravantes.css') }}">
@endpush

@php
    // $agravantes llega desde CatalogosController@index; si no llega, la vista se muestra igual con la lista vacía.
    $agravantes = $agravantes ?? collect();

    $totalAgr   = count($agravantes);
    $activosAgr = collect($agravantes)->where('activo', true)->count();
    $pctAgr     = $totalAgr ? (int) round($activosAgr / $totalAgr * 100) : 0;
    $estableAgr = $pctAgr >= 75;

    // Siguiente código: el número mayor + 1 (AGR-05 → AGR-06)
    $mayorAgr = collect($agravantes)->map(fn ($a) => (int) preg_replace('/\D/', '', $a['codigo']))->max() ?? 0;
    $siguienteAgr = sprintf('AGR-%02d', $mayorAgr + 1);
@endphp

{{-- Tabla de agravantes --}}
<section class="panel panel--table" aria-label="Agravantes registrados">
    <div class="agr-toolbar">
        <div class="agr-toolbar__left">
            <span class="agr-toolbar__title">Parámetros registrados</span>
            <span class="agr-toolbar__dot"></span>
            <span class="agr-toolbar__sub">Catálogo de agravantes base</span>
        </div>
        <div class="agr-toolbar__right">
            <span class="agr-toolbar__count" id="agravantes-total">{{ $totalAgr }} {{ $totalAgr === 1 ? 'elemento' : 'elementos' }}</span>
            <div class="agr-active-chip">
                <span>Activos:</span>
                <span id="agravantes-chip">{{ $activosAgr }} / {{ $totalAgr }}</span>
            </div>
        </div>
    </div>

    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col" class="agr-w-cod">Código</th>
                    <th scope="col" class="agr-w-nom">Nombre</th>
                    <th scope="col" class="agr-w-desc">Descripción</th>
                    <th scope="col" class="agr-w-act col-right">Activo</th>
                </tr>
            </thead>
            <tbody id="agravantes-filas">
                @forelse ($agravantes as $a)
                    <tr class="{{ $a['activo'] ? '' : 'is-off' }}">
                        <td class="col-code" data-codigo="{{ $a['codigo'] }}"><span class="agr-chip">{{ $a['codigo'] }}</span></td>
                        <td class="col-name" title="{{ $a['nombre'] }}">{{ $a['nombre'] }}</td>
                        <td class="col-desc" title="{{ $a['descripcion'] }}">{{ $a['descripcion'] }}</td>
                        <td class="col-right">
                            <label class="toggle">
                                <input type="checkbox" name="activo[{{ $a['codigo'] }}]" value="1" data-url="{{ Route::has('catalogos.activo') ? route('catalogos.activo', ['tipo' => 'agravantes', 'codigo' => $a['codigo']]) : '' }}" aria-label="Estado de {{ $a['codigo'] }}" {{ $a['activo'] ? 'checked' : '' }}>
                                <span class="toggle__track"></span>
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="col-empty">Aún no hay agravantes registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Formulario "Nuevo agravante" --}}
<section class="panel" aria-label="Nuevo agravante">
    <div class="agr-head">
        <h2 class="agr-head__title">Nuevo agravante</h2>
        <span class="agr-head__tag">Formulario de registro</span>
    </div>

    <div class="agr-body">

        {{-- Guarda en CatalogosController@storeAgravante (ruta catalogos.agravantes.store) --}}
        <form method="POST" action="{{ Route::has('catalogos.agravantes.store') ? route('catalogos.agravantes.store') : '#' }}" id="form-agravante" class="agr-form">
            @csrf

            <div class="agr-fields">
                <div>
                    <label class="agr-label" for="a-codigo">Código <span class="required">*</span></label>
                    <input type="text" id="a-codigo" name="codigo" class="control control--mono" value="{{ $siguienteAgr }}" readonly>
                </div>
                <div>
                    <label class="agr-label" for="a-nombre">Nombre <span class="required">*</span></label>
                    <input type="text" id="a-nombre" name="nombre" class="control" placeholder="Nombre del agravante..." value="{{ old('nombre') }}" required>
                </div>
            </div>

            <div>
                <label class="agr-label" for="a-desc">Descripción</label>
                <textarea id="a-desc" name="descripcion" rows="3" class="control control--textarea" placeholder="Describa qué mide o qué representa...">{{ old('descripcion') }}</textarea>
            </div>

            <div class="agr-actions">
                <button type="submit" class="btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Guardar
                </button>
                <button type="reset" class="btn-ghost">Cancelar</button>
            </div>
        </form>

        {{-- Nota técnica + medidor de agravantes activos --}}
        <div class="agr-side">
            <aside class="agr-note">
                <span class="agr-note__title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Nota técnica de calibración
                </span>
                <p>Los puntos de cada agravante se definen por versión, en <strong>«Editar versión»</strong>.</p>
                <div class="agr-note__foot">Regla de negocio: RN-220 · Matriz multivariada V3.4 (vigente)</div>
            </aside>

            <div class="agr-meter">
                <div class="agr-meter__row">
                    <span class="agr-meter__label">Agravantes activos</span>
                    <span class="agr-meter__value" id="agravantes-medidor">{{ $activosAgr }} / {{ $totalAgr }}</span>
                </div>
                <div class="agr-bar" id="agravantes-barra" aria-hidden="true">
                    @foreach ($agravantes as $a)
                        <span class="{{ $a['activo'] ? '' : 'is-off' }}" title="{{ $a['codigo'] }}: {{ $a['activo'] ? 'Activo' : 'Inactivo' }}"></span>
                    @endforeach
                </div>
                <div class="agr-meter__foot">
                    <span id="agravantes-pct">{{ $pctAgr }}% del catálogo operativo</span>
                    <span class="agr-meter__state {{ $estableAgr ? '' : 'is-warn' }}" id="agravantes-estado">{{ $estableAgr ? 'Estable' : 'Revisar' }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script src="{{ asset('js/catalogos_agravantes.js') }}"></script>
@endpush
 