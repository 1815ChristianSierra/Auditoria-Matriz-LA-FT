{{--
    Pestaña "Agravantes" de Editar versión.
    La incluye versiones/edit.blade.php dentro del panel data-panel="agravantes".
    Usa las clases base de versiones-edit.css y carga su propio CSS y JS.
    Sus campos (incluir / puntos) van dentro del formulario #form-version de la pantalla.
    El tope se lee en vivo del campo "tope-agravantes" de la pestaña Bandas y topes.
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_agravantes.css') }}">
@endpush

@php
    // $agravantes y el tope llegan desde VersionesController@edit; si no llegan, la lista queda vacía.
    $agravantes = $agravantes ?? [];

    // Tope de agravantes de esta versión (se edita en la pestaña Bandas y topes)
    $topeAgravantes = $topeAgravantes ?? 10;

    $totalAg    = count($agravantes);
    $incluidos  = collect($agravantes)->where('incluir', true);
    $activosAg  = $incluidos->count();
    $sumaAg     = $incluidos->sum('puntos');
    $impactoAg  = min($sumaAg, $topeAgravantes);

    // Puntos "típicos" para el ejemplo: promedio de los agravantes incluidos
    $puntosEj   = $activosAg ? (int) round($sumaAg / $activosAg) : 0;
    $ejemplos   = [
        ['id' => 'vag-ej1', 'n' => 3],
        ['id' => 'vag-ej2', 'n' => 6],
    ];
    $coherenteAg = $incluidos->every(fn ($a) => $a['puntos'] <= $topeAgravantes);
@endphp

<div class="vag-wrap">

    {{-- Agravantes de esta versión --}}
    <section class="panel" aria-label="Agravantes de esta versión">
        <div class="panel__head">
            <div class="panel__head-left">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                <h2 class="panel__title">Agravantes de esta versión <small>(puntos que suman al riesgo)</small></h2>
            </div>
            <div class="vag-meta">
                <span><strong id="vag-activos">{{ $activosAg }}/{{ $totalAg }}</strong> activos</span>
                <span class="sep">•</span>
                <span>Impacto total catálogo: <span class="impact" id="vag-impacto">+{{ $impactoAg }} pts máx</span></span>
            </div>
        </div>

        <table class="vag-table">
            <thead>
                <tr>
                    <th scope="col" class="w-inc col-center">Incluir</th>
                    <th scope="col" class="w-cod">Código</th>
                    <th scope="col" class="w-nom">Agravante</th>
                    <th scope="col" class="w-desc">Descripción</th>
                    <th scope="col" class="w-pts col-center">Puntos</th>
                </tr>
            </thead>
            <tbody id="vag-filas" data-tope="{{ $topeAgravantes }}">
                @foreach ($agravantes as $a)
                    <tr class="{{ $a['incluir'] ? '' : 'is-off' }}">
                        <td class="col-center">
                            <input type="checkbox" class="vag-check" name="agravantes[{{ $a['codigo'] }}][incluir]" value="1" aria-label="Incluir {{ $a['codigo'] }}" {{ $a['incluir'] ? 'checked' : '' }} data-incluir>
                        </td>
                        <td><span class="vag-chip">{{ $a['codigo'] }}</span></td>
                        <td class="col-name" title="{{ $a['nombre'] }}">{{ $a['nombre'] }}</td>
                        <td class="col-desc" title="{{ $a['descripcion'] }}">{{ $a['descripcion'] }}</td>
                        <td class="col-center">
                            <input type="number" class="control vag-points" name="agravantes[{{ $a['codigo'] }}][puntos]" min="0" max="{{ $topeAgravantes }}" value="{{ old('agravantes.' . $a['codigo'] . '.puntos', $a['puntos']) }}" aria-label="Puntos de {{ $a['codigo'] }}" {{ $a['incluir'] ? '' : 'disabled' }} data-puntos>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- Tope + ejemplo --}}
    <div class="vag-cards">

        {{-- Tope de agravantes --}}
        <section class="panel vag-card" aria-label="Tope de agravantes">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 10l7-7m0 0l7 7m-7-7v18" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Tope de agravantes</h2>
                </div>
                <span class="tag tag--mono">Configurado globalmente</span>
            </div>
            <div class="vag-card__body">
                <div class="vag-cap">
                    <div class="vag-cap__num">
                        <strong id="vag-tope">{{ $topeAgravantes }}</strong>
                        <span>PTS LÍMITE</span>
                    </div>
                    <div class="vag-cap__text">
                        <p><strong>puntos:</strong> un evento nunca suma más que esto, aunque tenga más agravantes.</p>
                        <p>Asegura que el factor de agravantes no distorsione unilateralmente la calificación intrínseca del cliente.</p>
                    </div>
                </div>
            </div>
            <div class="vag-card__foot">
                <button type="button" class="vag-link" data-ir-tab="bandas">
                    Editar en «Bandas y topes»
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14 5l7 7m0 0l-7 7m7-7H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </button>
                <span class="vag-rule">Regla: R-CAP-04</span>
            </div>
        </section>

        {{-- Ejemplo de aplicación del tope --}}
        <section class="panel vag-card" aria-label="Ejemplo de aplicación del tope">
            <div class="panel__head">
                <div class="panel__head-left">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <h2 class="panel__title panel__title--caps">Ejemplo de aplicación del tope</h2>
                </div>
                <span class="vag-live">Simulación en vivo</span>
            </div>
            <div class="vag-card__body">
                <div class="vag-sim">
                    @foreach ($ejemplos as $e)
                        @php
                            $totalEj = $e['n'] * $puntosEj;
                            $topado  = $totalEj > $topeAgravantes;
                        @endphp
                        <div class="vag-sim__row {{ $topado ? 'is-capped' : '' }}" id="{{ $e['id'] }}-fila">
                            <span class="vag-sim__left" id="{{ $e['id'] }}-texto">{{ $e['n'] }} agravantes × {{ $puntosEj }} puntos = {{ $totalEj }}</span>
                            <span class="vag-sim__right">
                                Resultado:
                                <span class="vag-sim__result" id="{{ $e['id'] }}-res">{{ $topado ? 'se suman ' . $topeAgravantes . ' (tope)' : 'se suman ' . $totalEj }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="vag-card__foot">
                <span class="vag-formula">Fórmula: <code>MIN(Σ(AGR_i * Pts_i), MAX_CAP)</code></span>
                <span class="vag-ok {{ $coherenteAg ? '' : 'is-warn' }}" id="vag-coherencia">{{ $coherenteAg ? 'Coherencia verificada' : 'Un agravante supera el tope' }}</span>
            </div>
        </section>
    </div>

    {{-- Administración de catálogos --}}
    <aside class="vag-info">
        <div class="vag-info__icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
        </div>
        <div>
            <strong>Administración de catálogos</strong>
            <p>Para agregar o desactivar un agravante use el módulo <a href="{{ Route::has('catalogos.index') ? route('catalogos.index') : '#' }}">Catálogos</a>. Aquí solo se definen sus puntos en esta versión.</p>
        </div>
    </aside>
</div>

@push('scripts')
    <script src="{{ asset('js/versiones_agravantes.js') }}"></script>
@endpush
 