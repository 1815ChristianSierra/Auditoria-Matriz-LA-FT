{{--
    Pestaña "Controles" de Editar versión.
    La incluye versiones/edit.blade.php dentro del panel data-panel="controles".
    Usa las clases base de versiones-edit.css y carga su propio CSS y JS.
    Sus campos (incluir / puntos) van dentro del formulario #form-version de la pantalla.
    El tope se lee en vivo del campo "tope-controles" de la pestaña Bandas y topes.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_controles.css') }}">
@endpush

@php
    // $controlesVersion y el tope llegan desde VersionesController@edit; si no llegan, la lista queda vacía.
    $controlesVersion = $controlesVersion ?? [];

    // Tope de controles de esta versión (se edita en la pestaña Bandas y topes)
    $topeControles = $topeControles ?? 3;

    $totalCt   = count($controlesVersion);
    $incluidos = collect($controlesVersion)->where('incluir', true);
    $activosCt = $incluidos->count();
    $partesCt  = $incluidos->pluck('puntos')->map(fn ($p) => (int) $p)->values()->all();
    $sumaCt    = array_sum($partesCt);
    $restaCt   = min($sumaCt, $topeControles);

    // El segundo escenario agrega un control más, con los puntos promedio de los incluidos
    $extraCt   = $activosCt ? (int) round($sumaCt / $activosCt) : 0;
    $ejemplos  = [
        ['id' => 'vct-ej1', 'partes' => $partesCt],
        ['id' => 'vct-ej2', 'partes' => array_merge($partesCt, [$extraCt])],
    ];
    $coherenteCt = $incluidos->every(fn ($c) => $c['puntos'] <= $topeControles);
@endphp

<div class="vct-wrap">

    {{-- Controles compensatorios de esta versión --}}
    <section class="panel" aria-label="Controles compensatorios de esta versión">
        <div class="panel__head">
            <div class="panel__head-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <h2 class="panel__title">Controles compensatorios de esta versión <small>(puntos que restan al riesgo)</small></h2>
            </div>
            <div class="vct-meta">
                <span><strong id="vct-activos">{{ $activosCt }}/{{ $totalCt }}</strong> activos</span>
                <span class="sep">•</span>
                <span>Impacto mitigante catálogo: <span class="impact" id="vct-impacto">{{ $restaCt ? '-' . $restaCt : '0' }} pts máx</span></span>
            </div>
        </div>

        <table class="vct-table">
            <thead>
                <tr>
                    <th scope="col" class="w-inc col-center">Incluir</th>
                    <th scope="col" class="w-cod">Código</th>
                    <th scope="col" class="w-nom">Control</th>
                    <th scope="col" class="w-tipo">Tipo</th>
                    <th scope="col" class="w-cal col-center">Diseño</th>
                    <th scope="col" class="w-cal col-center">Operación</th>
                    <th scope="col" class="w-pts col-center">Puntos que resta</th>
                </tr>
            </thead>
            <tbody id="vct-filas" data-tope="{{ $topeControles }}">
                @foreach ($controlesVersion as $c)
                    <tr class="{{ $c['incluir'] ? '' : 'is-off' }}">
                        <td class="col-center">
                            <input type="checkbox" class="vct-check" name="controles[{{ $c['codigo'] }}][incluir]" value="1" aria-label="Incluir {{ $c['codigo'] }}" {{ $c['incluir'] ? 'checked' : '' }} data-incluir>
                        </td>
                        <td><span class="vct-chip">{{ $c['codigo'] }}</span></td>
                        <td class="col-name" title="{{ $c['nombre'] }}">{{ $c['nombre'] }}</td>
                        <td>{{ $c['tipo'] }}</td>
                        <td class="col-center"><span class="vct-badge vct-badge--{{ strtolower($c['diseno']) }}">{{ $c['diseno'] }}</span></td>
                        <td class="col-center"><span class="vct-badge vct-badge--{{ strtolower($c['operacion']) }}">{{ $c['operacion'] }}</span></td>
                        <td class="col-center">
                            <input type="number" class="control vct-points" name="controles[{{ $c['codigo'] }}][puntos]" min="0" max="{{ $topeControles }}" value="{{ old('controles.' . $c['codigo'] . '.puntos', $c['puntos']) }}" aria-label="Puntos que resta {{ $c['codigo'] }}" {{ $c['incluir'] ? '' : 'hidden disabled' }} data-puntos>
                            <span class="vct-none" {{ $c['incluir'] ? 'hidden' : '' }} data-ninguno>No incluido</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Tope + ejemplo --}}
    <div class="vct-cards">

        {{-- Tope de controles --}}
        <section class="panel vct-card" aria-label="Tope de controles">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Tope de controles</h2>
                </div>
                <span class="tag tag--mono">Configurado globalmente</span>
            </div>
            <div class="vct-card__body">
                <div class="vct-cap">
                    <div class="vct-cap__num">
                        <strong id="vct-tope">{{ $topeControles }}</strong>
                        <span>PTS LÍMITE</span>
                    </div>
                    <div class="vct-cap__text">
                        <p><strong>puntos:</strong> un evento nunca resta más que esto, aunque tenga más controles acumulados.</p>
                        <p>Asegura que las mitigaciones no anulen indebidamente el riesgo inherente del perfil ni maquillen exposiciones críticas.</p>
                    </div>
                </div>
            </div>
            <div class="vct-card__foot">
                <button type="button" class="vct-link" data-ir-tab="bandas">
                    Editar en «Bandas y topes»
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </button>
                <span class="vct-rule">Regla: R-MIT-02</span>
            </div>
        </section>

        {{-- Ejemplo de aplicación del tope --}}
        <section class="panel vct-card" aria-label="Ejemplo de aplicación del tope">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Ejemplo de aplicación del tope</h2>
                </div>
                <span class="vct-live">Simulación en vivo</span>
            </div>
            <div class="vct-card__body">
                <div class="vct-sim">
                    @foreach ($ejemplos as $e)
                        @php
                            $sumaEj = array_sum($e['partes']);
                            $topado = $sumaEj > $topeControles;
                        @endphp
                        <div class="vct-sim__row {{ $topado ? 'is-capped' : '' }}" id="{{ $e['id'] }}-fila">
                            <span class="vct-sim__left" id="{{ $e['id'] }}-texto">{{ count($e['partes']) ? 'Controles ' . implode(' + ', $e['partes']) . ' = ' . $sumaEj : 'Sin controles incluidos = 0' }}</span>
                            <span class="vct-sim__right">
                                Resultado:
                                <span class="vct-sim__result" id="{{ $e['id'] }}-res">se restan {{ min($sumaEj, $topeControles) }}{{ $topado ? ' (tope)' : '' }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="vct-card__foot">
                <span class="vct-formula">Fórmula: <code>MIN(Σ(CTL_i * Pts_i), MAX_CAP_CTL)</code></span>
                <span class="vct-ok {{ $coherenteCt ? '' : 'is-warn' }}" id="vct-coherencia">{{ $coherenteCt ? 'Coherencia verificada' : 'Un control supera el tope' }}</span>
            </div>
        </section>
    </div>

    {{-- Aviso normativo (nota amarilla) --}}
    <aside class="vct-note">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        <div>
            <strong><span>Aviso normativo:</span> Un control solo resta puntos si el evento tiene su evidencia registrada.</strong>
            <p>Diseño y operación se editan en <a href="{{ Route::has('catalogos.index') ? route('catalogos.index') : '#' }}">Catálogos</a>. Cualquier variación en los parámetros de catálogo impactará retrospectivamente las evaluaciones en estado borrador.</p>
        </div>
    </aside>
</div>

@push('scripts')
    <script src="{{ asset('js/versiones_controles.js') }}"></script>
@endpush
 