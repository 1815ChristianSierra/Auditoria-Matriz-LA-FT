{{--
    Modal: Nueva versión (clonar).
    Se abre con cualquier elemento que tenga data-modal-open="modal-nueva-version".
    Carga su propio CSS y JS (public/css/versiones_modal.css y public/js/versiones_modal.js).
    Usa $versiones, $estados y $proponente de versiones/index.blade.php (los manda VersionesController@index).
--}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/versiones_modal.css') }}">
@endpush

@php
    $versionesModal = $versiones ?? [];
    $estadosModal   = $estados ?? ['borrador' => 'Borrador', 'propuesta' => 'Propuesta', 'aprobada' => 'Aprobada', 'rechazada' => 'Rechazada', 'obsoleta' => 'Obsoleta'];
    $proponente     = $proponente ?? (optional(auth()->user())->username ?? 'admin1');

    // Números ya usados (no se pueden repetir)
    $existentes = collect($versionesModal)->pluck('version')->all();

    // Siguiente número sugerido: la versión mayor + 1 en el segundo dígito (v1.2 → v1.3)
    $mayor = [0, 0];
    foreach ($existentes as $numeroExistente) {
        if (preg_match('/^v(\d+)\.(\d+)$/', $numeroExistente, $m) && [(int) $m[1], (int) $m[2]] > $mayor) {
            $mayor = [(int) $m[1], (int) $m[2]];
        }
    }
    $siguienteNumero = $existentes ? 'v' . $mayor[0] . '.' . ($mayor[1] + 1) : 'v1.0';

    // Por defecto se clona la versión aprobada (o la primera de la lista)
    $clonarDesde = collect($versionesModal)->firstWhere('estado', 'aprobada')['version'] ?? ($versionesModal[0]['version'] ?? null);
@endphp

<div id="modal-nueva-version"
     class="vm {{ $errors->any() ? 'is-open' : '' }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-nueva-version-titulo">

    <div class="vm__card">

        <header class="vm__header">
            <h2 class="vm__title" id="modal-nueva-version-titulo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                Nueva versión (clonar)
            </h2>
            <button type="button" class="vm__close" data-modal-close title="Cerrar" aria-label="Cerrar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
            </button>
        </header>

        {{-- Guarda en VersionesController@store (ruta versiones.store) --}}
        <form method="POST" action="{{ Route::has('versiones.store') ? route('versiones.store') : '#' }}" class="vm__form" id="form-nueva-version">
            @csrf

            <div class="vm__body">

                {{-- Número de versión + Proponente --}}
                <div class="vm__row">
                    <div>
                        <label class="vm__label" for="nv-numero">Número de versión <span class="vm__required">*</span></label>
                        <input type="text"
                               id="nv-numero"
                               name="numero"
                               class="vm__control vm__control--mono"
                               value="{{ old('numero', $siguienteNumero) }}"
                               pattern="v[0-9]+\.[0-9]+"
                               title="Use el formato v1.3"
                               placeholder="v1.3"
                               data-existentes='@json($existentes)'
                               autocomplete="off"
                               required>
                    </div>
                    <div>
                        <label class="vm__label" for="nv-proponente">Proponente</label>
                        <input type="text" id="nv-proponente" class="vm__control vm__control--readonly" value="{{ $proponente }}" readonly>
                    </div>
                </div>

                {{-- Clonar desde --}}
                <div>
                    <label class="vm__label" for="nv-origen">Clonar desde <span class="vm__required">*</span></label>
                    <select id="nv-origen" name="clonar_desde" class="vm__control" required>
                        @foreach ($versionesModal as $v)
                            <option value="{{ $v['version'] }}" {{ old('clonar_desde', $clonarDesde) === $v['version'] ? 'selected' : '' }}>{{ $v['version'] }} · {{ strtolower($estadosModal[$v['estado']] ?? $v['estado']) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Motivo del cambio --}}
                <div>
                    <label class="vm__label" for="nv-motivo">Motivo del cambio <span class="vm__required">*</span></label>
                    <textarea id="nv-motivo" name="motivo" rows="4" class="vm__control vm__control--textarea" placeholder="Explique qué se quiere cambiar y por qué..." required>{{ old('motivo') }}</textarea>
                </div>

                {{-- Aviso --}}
                <div class="vm__info">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    <span>Se crea en estado Borrador con una copia de pesos, escalas, bandas, topes, agravantes, controles y reglas.</span>
                </div>
            </div>

            <footer class="vm__footer">
                <button type="button" class="vm__btn vm__btn--ghost" data-modal-close>Cancelar</button>
                <button type="submit" class="vm__btn vm__btn--primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Crear borrador
                </button>
            </footer>
        </form>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/versiones_modal.js') }}"></script>
@endpush
 