{{--
    Pestaña "Bandas y topes" de Editar versión.
    La incluye versiones/edit.blade.php dentro del panel data-panel="bandas".
    Usa las clases base de versiones-edit.css y carga su propio CSS y JS.
    Sus campos (topes) van dentro del formulario #form-version de la pantalla.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_bandas.css') }}">
@endpush

@php
    // $bandas, $topeAgravantes, $topeControles y $datoFaltante llegan desde VersionesController@edit.
    $bandas         = $bandas ?? [];
    $topeAgravantes = $topeAgravantes ?? 10;
    $topeControles  = $topeControles ?? 3;
    $datoFaltante   = $datoFaltante ?? 3;

    $topes = [
        ['clave' => 'agravantes',    'nombre' => 'Tope de agravantes (puntos)',  'descripcion' => 'Lo máximo que pueden sumar los agravantes de un evento.',                         'valor' => $topeAgravantes, 'min' => 0, 'max' => null],
        ['clave' => 'controles',     'nombre' => 'Tope de controles (puntos)',   'descripcion' => 'Lo máximo que pueden restar los controles de un evento.',                         'valor' => $topeControles,  'min' => 0, 'max' => null],
        ['clave' => 'dato_faltante', 'nombre' => 'Nivel por dato faltante (0 a 5)', 'descripcion' => 'Nivel que se asigna a un factor sin calificar; se marca «dato faltante».',       'valor' => $datoFaltante,   'min' => 0, 'max' => 5],
    ];

    $puntajeEjemplo = $puntajeEjemplo ?? 96;

    // Banda del ejemplo: la de mayor "Desde" menor o igual al puntaje
    $bandaEjemplo = collect($bandas)->filter(fn ($b) => $b['desde'] <= $puntajeEjemplo)->sortByDesc('desde')->first() ?? ($bandas[0] ?? ['clave' => '', 'nombre' => '—', 'desde' => 0]);

    // Marcas de la escala: el "Desde" de cada banda y el 100 final
    $marcas = collect($bandas)->pluck('desde')->push(100)->all();
@endphp

<div class="bnd-layout">

    {{-- Columna izquierda: bandas + topes --}}
    <div class="bnd-col">

        {{-- Bandas de clasificación --}}
        <section class="panel" aria-label="Bandas de clasificación">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Bandas de clasificación</h2>
                </div>
                <span class="tag tag--mono">{{ count($bandas) }} umbrales activos</span>
            </div>

            <table class="bnd-table">
                <thead>
                    <tr>
                        <th scope="col" class="w-banda">Banda</th>
                        <th scope="col" class="w-num">Desde</th>
                        <th scope="col" class="w-num">Hasta</th>
                        <th scope="col">Tratamiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bandas as $b)
                        <tr data-banda-fila="{{ $b['clave'] }}" data-nombre="{{ $b['nombre'] }}" data-desde="{{ $b['desde'] }}">
                            <td><span class="bnd-pill band--{{ $b['clave'] }}">{{ $b['nombre'] }}</span></td>
                            <td class="col-num">{{ $b['desde'] }}</td>
                            <td class="col-num">{{ $b['hasta'] }}</td>
                            <td title="{{ $b['tratamiento'] }}">{{ $b['tratamiento'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Topes y dato faltante --}}
        <section class="panel" aria-label="Topes y dato faltante">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Topes y dato faltante</h2>
                </div>
                <span class="tag tag--mono">Límites paramétricos</span>
            </div>

            <div class="bnd-topes">
                @foreach ($topes as $t)
                    <div class="bnd-tope">
                        <label for="tope-{{ $t['clave'] }}">
                            <span class="bnd-tope__name">{{ $t['nombre'] }}</span>
                            <span class="bnd-tope__desc">{{ $t['descripcion'] }}</span>
                        </label>
                        <input type="number"
                               id="tope-{{ $t['clave'] }}"
                               name="topes[{{ $t['clave'] }}]"
                               class="control bnd-tope__input"
                               value="{{ old('topes.' . $t['clave'], $t['valor']) }}"
                               min="{{ $t['min'] }}"
                               @if ($t['max'] !== null) max="{{ $t['max'] }}" @endif
                               data-tope>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Columna derecha: escala + ejemplo + nota --}}
    <div class="bnd-col">

        {{-- Escala de bandas --}}
        <section class="panel" aria-label="Escala de bandas">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Escala de bandas (0 a 100)</h2>
                </div>
            </div>
            <div class="bnd-body">
                <div class="bnd-scale">
                    @foreach ($bandas as $b)
                        <div class="bnd-scale__seg band--{{ $b['clave'] }} {{ $b['clave'] === $bandaEjemplo['clave'] ? 'is-current' : '' }}" data-banda-seg="{{ $b['clave'] }}">{{ $b['nombre'] }}</div>
                    @endforeach
                </div>
                <div class="bnd-marks">
                    @foreach ($marcas as $m)
                        <span>{{ $m }}</span>
                    @endforeach
                </div>
                <p class="bnd-hint">Se elige la banda de mayor «Desde» menor o igual al puntaje residual.</p>
            </div>
        </section>

        {{-- Ejemplo / simulación --}}
        <section class="panel" aria-label="Ejemplo">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title">Ejemplo</h2>
                </div>
                <span class="tag tag--mono">Simulación</span>
            </div>
            <div class="bnd-body">
                <div class="bnd-example">
                    <div class="bnd-example__row">
                        <label for="bnd-puntaje">Puntaje residual</label>
                        {{-- Sin name: es solo una simulación, no se envía con el formulario --}}
                        <input type="number" id="bnd-puntaje" class="bnd-example__score" min="0" max="100" value="{{ $puntajeEjemplo }}">
                    </div>
                    <div class="bnd-example__row">
                        <span>Banda resultante</span>
                        <span class="bnd-pill band--{{ $bandaEjemplo['clave'] }}" id="bnd-resultado">{{ $bandaEjemplo['nombre'] }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Nota amarilla --}}
        <aside class="bnd-note">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            <p>Solo se edita en borrador. Al proponer la versión, bandas y topes quedan bloqueados.</p>
        </aside>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/versiones_bandas.js') }}"></script>
@endpush
 