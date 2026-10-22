document.addEventListener('DOMContentLoaded', function () {
    var filas        = document.querySelectorAll('[data-registro]');
    var comparaciones = document.querySelectorAll('[data-comparacion]');
    if (!filas.length) return;

    var botones = document.querySelectorAll('[data-filtro]');
    var vacia   = document.getElementById('fila-vacia');

    var observacion = document.getElementById('observacion');
    var errorObs    = document.getElementById('observacion-error');
    var form        = document.getElementById('form-dictamen');
    var MIN_OBSERVACION = 10;

    /* ---------- Seleccionar un registro: cambia la comparación ---------- */
    function seleccionar(id) {
        filas.forEach(function (fila) {
            var elegida = fila.getAttribute('data-registro') === id;
            fila.classList.toggle('is-selected', elegida);
            // El dictamen se envía al registro seleccionado
            if (elegida && form) form.setAttribute('action', fila.getAttribute('data-url') || '#');
        });
        comparaciones.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-comparacion') !== id;
        });
    }

    filas.forEach(function (fila) {
        fila.addEventListener('click', function () {
            seleccionar(fila.getAttribute('data-registro'));
        });
        fila.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                seleccionar(fila.getAttribute('data-registro'));
            }
        });

        // "Verificar" lleva directo a la observación
        var boton = fila.querySelector('button');
        if (boton) {
            boton.addEventListener('click', function () {
                if (fila.getAttribute('data-estado') === 'parcial' && observacion) observacion.focus();
            });
        }
    });

    /* ---------- Filtros: Por verificar / Todas / Listas sintéticas ---------- */
    function filtrar(tipo) {
        var visibles = 0;

        filas.forEach(function (fila) {
            var mostrar = true;
            if (tipo === 'pendientes') mostrar = fila.getAttribute('data-estado') === 'parcial';
            if (tipo === 'sinteticas') mostrar = fila.getAttribute('data-sintetica') === '1';

            fila.hidden = !mostrar;
            if (mostrar) visibles++;
        });

        if (vacia) vacia.hidden = visibles !== 0;

        botones.forEach(function (btn) {
            var activo = btn.getAttribute('data-filtro') === tipo;
            btn.classList.toggle('is-active', activo);
            btn.setAttribute('aria-pressed', activo ? 'true' : 'false');
        });
    }

    botones.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filtrar(btn.getAttribute('data-filtro'));
        });
    });

    // Al entrar: el registro abierto (el que se acaba de decidir o el primero) y su filtro
    var inicial = document.querySelector('[data-registro].is-selected') || filas[0];
    seleccionar(inicial.getAttribute('data-registro'));

    var hayPendientes = Array.prototype.some.call(filas, function (fila) { return fila.getAttribute('data-estado') === 'parcial'; });
    filtrar(hayPendientes && inicial.getAttribute('data-estado') === 'parcial' ? 'pendientes' : 'todas');

    /* ---------- Observación obligatoria para confirmar o descartar ---------- */
    function mostrarError(mostrar) {
        if (!observacion || !errorObs) return;
        observacion.classList.toggle('control--error', mostrar);
        errorObs.hidden = !mostrar;
    }

    if (observacion) {
        observacion.addEventListener('input', function () {
            if (observacion.value.trim() !== '') mostrarError(false);
        });
    }

    document.querySelectorAll('[data-accion]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var accion = btn.getAttribute('data-accion');

            // Confirmar o descartar exige una observación de al menos 10 caracteres
            if (accion !== 'parcial' && observacion && observacion.value.trim().length < MIN_OBSERVACION) {
                e.preventDefault();
                mostrarError(true);
                observacion.focus();
                return;
            }
            mostrarError(false);

            // Sin ruta para el registro no se envía nada
            if (!form || form.getAttribute('action') === '#') e.preventDefault();
        });
    });
});
 