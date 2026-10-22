{{--
    Modal: Nueva evaluación.
    Se abre con cualquier elemento que tenga data-modal-open="modal-nueva-evaluacion"
    (ver public/js/evaluaciones.js). Las variables $siguienteCodigo, $responsablesModal
    y $versionesAprobadas llegan desde EvaluacionesController@index (se usan en evaluaciones/index.blade.php).
--}}
<div id="modal"
     class="modal {{ $errors->any() ? 'is-open' : '' }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-nueva-evaluacion-titulo">

    <div class="modal__wrap">

        {{-- Tarjeta principal --}}
        <div class="modal__card">

            <header class="modal__header">
                <h2 class="modal__title" id="modal-nueva-evaluacion-titulo">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                    Nueva evaluación
                </h2>
                <button type="button" class="modal__close" data-modal-close title="Cerrar" aria-label="Cerrar">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                </button>
            </header>

            {{-- Guarda en EvaluacionesController@store (ruta evaluaciones.store) --}}
            <form method="POST" action="{{ Route::has('evaluaciones.store') ? route('evaluaciones.store') : '#' }}" id="form-nueva-evaluacion">
                @csrf

                <div class="modal__body">

                    @if ($errors->any())
                        <div class="alert alert--error" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Código (solo lectura) + Responsable --}}
                    <div class="modal__grid">
                        <div>
                            <label class="field-label field-label--row" for="ne-codigo">
                                <span>Código</span>
                                <span class="field-hint">autogenerado</span>
                            </label>
                            <div class="input-icon">
                                <input type="text" id="ne-codigo" class="control control--readonly" value="{{ $siguienteCodigo }}" readonly>
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </div>
                        </div>

                        <div>
                            <label class="field-label" for="ne-responsable">Responsable <span class="required">*</span></label>
                            <select id="ne-responsable" name="responsable" class="control" required>
                                @foreach ($responsablesModal as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('responsable') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Período --}}
                    <div class="modal__grid">
                        <div>
                            <label class="field-label" for="ne-inicio">Período: inicio <span class="required">*</span></label>
                            <input type="date" id="ne-inicio" name="inicio" class="control control--date" value="{{ old('inicio', date('Y-m-01')) }}" required>
                        </div>
                        <div>
                            <label class="field-label" for="ne-fin">Período: fin <span class="required">*</span></label>
                            <input type="date" id="ne-fin" name="fin" class="control control--date" value="{{ old('fin', date('Y-m-t')) }}" required>
                        </div>
                    </div>

                    {{-- Alcance --}}
                    <div>
                        <label class="field-label" for="ne-alcance">Alcance <span class="required">*</span></label>
                        <textarea id="ne-alcance" name="alcance" rows="3" class="control control--textarea" placeholder="Describa qué eventos y criterios cubre la evaluación..." required>{{ old('alcance') }}</textarea>
                    </div>

                    {{-- Versión de parámetros --}}
                    <div>
                        <label class="field-label field-label--row" for="ne-version">
                            <span>Versión de parámetros <span class="required">*</span></span>
                            <span class="field-hint field-hint--ok"><span class="dot"></span>Matriz Activa</span>
                        </label>
                        <select id="ne-version" name="version" class="control" required>
                            @forelse ($versionesAprobadas as $valor => $texto)
                                <option value="{{ $valor }}" {{ old('version') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                            @empty
                                <option value="" disabled selected>No hay versiones aprobadas</option>
                            @endforelse
                        </select>
                        <p class="field-help">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            </svg>
                            Solo se listan versiones aprobadas.
                        </p>
                    </div>
                </div>

                <footer class="modal__footer">
                    <button type="button" class="btn-ghost" data-modal-close>Cancelar</button>
                    <button type="submit" class="btn-secondary" {{ count($versionesAprobadas) && count($responsablesModal) ? '' : 'disabled' }}>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                        Crear evaluación
                    </button>
                </footer>
            </form>
        </div>

        {{-- Etiqueta amarilla de validación --}}
        <aside class="note-tag">
            <div class="note-tag__head">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
                Validación LA/FT
            </div>
            <p class="note-tag__text">La BD rechaza evaluaciones con versiones que no estén aprobadas.</p>
            <div class="note-tag__foot">
                <span>Regla #VAL-09</span>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
            </div>
        </aside>
    </div>
</div>
 