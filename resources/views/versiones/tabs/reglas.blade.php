{{--
    Pestaña "Reglas" de Editar versión.
    La incluye versiones/edit.blade.php dentro del panel data-panel="reglas".
    Usa las clases base de versiones-edit.css y carga su propio CSS y JS.
    Esta pestaña va DENTRO del formulario #form-version, por eso "Nueva regla" no es un <form>:
    cada regla agregada se guarda como campos ocultos reglas[ESC-0X][...] y se envía con "Guardar borrador".
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_reglas.css') }}">
@endpush

@php
    // $reglas llega desde VersionesController@edit; los factores y las bandas salen de las otras pestañas.
    $reglas = $reglas ?? [];

    $factoresRegla = collect($factores ?? [])->pluck('nombre', 'clave')->all();
    $bandasRegla   = collect(array_reverse($bandas ?? []))->pluck('nombre', 'clave')->all();   // de la más alta a la más baja

    $totalReglas = count($reglas);

    // Siguiente código: el número mayor + 1 (ESC-01 → ESC-02)
    $mayorRegla = collect($reglas)->map(fn ($r) => (int) preg_replace('/\D/', '', $r['codigo']))->max() ?? 0;
    $siguienteRegla = sprintf('ESC-%02d', $mayorRegla + 1);

    // Ejemplo de "Cómo funciona" (con la primera regla de la versión)
    $primera = $reglas[0] ?? null;
@endphp

<div class="vrg-wrap">

    {{-- Reglas de escalamiento de esta versión --}}
    <section class="panel" aria-label="Reglas de escalamiento de esta versión">
        <div class="panel__head">
            <div class="panel__head-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <h2 class="panel__title">Reglas de escalamiento de esta versión <small>(fuerzan la banda de riesgo)</small></h2>
            </div>
            <span class="vrg-meta" id="vrg-contador">{{ $totalReglas }} {{ $totalReglas === 1 ? 'regla activa' : 'reglas activas' }} • Evaluación técnica directa</span>
        </div>

        <table class="vrg-table">
            <thead>
                <tr>
                    <th scope="col" class="w-cod">Código</th>
                    <th scope="col" class="w-fac">Si el factor...</th>
                    <th scope="col" class="w-niv">Nivel ≥</th>
                    <th scope="col" class="w-ban">Banda forzada</th>
                    <th scope="col">Descripción</th>
                    <th scope="col" class="w-acc col-right">Acción</th>
                </tr>
            </thead>
            <tbody id="vrg-filas">
                @foreach ($reglas as $r)
                    <tr data-regla="{{ $r['codigo'] }}">
                        <td>
                            <span class="vrg-chip">{{ $r['codigo'] }}</span>
                            <input type="hidden" name="reglas[{{ $r['codigo'] }}][factor]" value="{{ $r['factor'] }}">
                            <input type="hidden" name="reglas[{{ $r['codigo'] }}][nivel]" value="{{ $r['nivel'] }}">
                            <input type="hidden" name="reglas[{{ $r['codigo'] }}][banda]" value="{{ $r['banda'] }}">
                            <input type="hidden" name="reglas[{{ $r['codigo'] }}][descripcion]" value="{{ $r['descripcion'] }}">
                        </td>
                        <td class="col-factor" title="{{ $factoresRegla[$r['factor']] ?? $r['factor'] }}">{{ $factoresRegla[$r['factor']] ?? $r['factor'] }}</td>
                        <td><span class="vrg-level">{{ $r['nivel'] }}</span></td>
                        <td><span class="vrg-pill band--{{ $r['banda'] }}">{{ $bandasRegla[$r['banda']] ?? $r['banda'] }}</span></td>
                        <td title="{{ $r['descripcion'] }}">{{ $r['descripcion'] }}</td>
                        <td class="col-right"><button type="button" class="vrg-remove" data-quitar>Quitar</button></td>
                    </tr>
                @endforeach
                <tr id="vrg-vacio" {{ $totalReglas ? 'hidden' : '' }}>
                    <td colspan="6" class="col-empty">Esta versión no tiene reglas de escalamiento.</td>
                </tr>
            </tbody>
        </table>
    </section>

    {{-- Nueva regla + cómo funciona --}}
    <div class="vrg-cards">

        {{-- Nueva regla (sin <form>: esta pestaña ya está dentro del formulario de la versión) --}}
        <section class="panel vrg-card" aria-label="Nueva regla">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Nueva regla</h2>
                </div>
            </div>
            <div class="vrg-card__body">
                <div class="vrg-fields vrg-fields--3">
                    <div>
                        <label class="vrg-label" for="vrg-codigo">Código</label>
                        <input type="text" id="vrg-codigo" class="control control--mono" value="{{ $siguienteRegla }}" disabled>
                    </div>
                    <div>
                        <label class="vrg-label" for="vrg-factor">Si el factor... <span class="required">*</span></label>
                        <select id="vrg-factor" class="control">
                            <option value="" selected>Seleccione un factor</option>
                            @foreach ($factoresRegla as $valor => $texto)
                                <option value="{{ $valor }}">{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="vrg-label" for="vrg-nivel">Nivel mínimo (0-5) <span class="required">*</span></label>
                        <select id="vrg-nivel" class="control control--mono">
                            @foreach ([5, 4, 3, 2, 1, 0] as $n)
                                <option value="{{ $n }}" {{ $n === 5 ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="vrg-fields vrg-fields--2">
                    <div>
                        <label class="vrg-label" for="vrg-banda">Entonces la banda pasa a <span class="required">*</span></label>
                        <select id="vrg-banda" class="control">
                            @foreach ($bandasRegla as $valor => $texto)
                                <option value="{{ $valor }}" {{ $valor === 'critico' ? 'selected' : '' }}>{{ $texto }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="vrg-label" for="vrg-desc">Descripción <span class="required">*</span></label>
                        <textarea id="vrg-desc" rows="2" class="control control--textarea" placeholder="Explique cuándo y por qué se aplica esta regla..."></textarea>
                    </div>
                </div>

                <div class="vrg-actions">
                    <button type="button" class="btn-secondary" id="vrg-agregar">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        Agregar regla
                    </button>
                    <button type="button" class="btn-ghost" id="vrg-cancelar">Cancelar</button>
                </div>
            </div>
        </section>

        {{-- Cómo funciona --}}
        <section class="panel vrg-card" aria-label="Cómo funciona">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Cómo funciona</h2>
                </div>
            </div>
            <div class="vrg-card__body">
                <div class="vrg-how">
                    <p>Si el factor llega al nivel indicado, el evento sube a la banda elegida aunque su puntaje sea menor.</p>
                    <p>Una regla solo puede subir la banda, nunca bajarla.</p>
                </div>

                @if ($primera)
                    <div class="vrg-example">
                        <span class="vrg-example__title">Ejemplo</span>
                        <div class="vrg-example__flow">
                            <span class="vrg-pill band--alto">Alto</span>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <span class="vrg-pill band--{{ $primera['banda'] }}">{{ $bandasRegla[$primera['banda']] ?? $primera['banda'] }}</span>
                        </div>
                        <code>Puntaje 62 + {{ $factoresRegla[$primera['factor']] ?? $primera['factor'] }} = {{ $primera['nivel'] }} (regla {{ $primera['codigo'] }})</code>
                    </div>
                @endif

                <div class="vrg-check">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <span>Validación automática ejecutada en tiempo real según directrices SARLAFT/GAFI.</span>
                </div>
            </div>
        </section>
    </div>

    {{-- Aviso normativo (nota amarilla) --}}
    <aside class="vrg-note">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        <div>
            <strong>Aviso normativo</strong>
            <p>Las reglas de escalamiento tienen precedencia sobre el cálculo ponderado y fuerzan directamente la banda de atención prioritaria. Toda regla creada quedará auditada en la bitácora inmutable con firma del autor.</p>
        </div>
    </aside>
</div>

@push('scripts')
    <script src="{{ asset('js/versiones_reglas.js') }}"></script>
@endpush
 