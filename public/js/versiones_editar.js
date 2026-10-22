document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-version');
    if (!form) return;

    /* ---------- Pestañas ---------- */
    var tabs   = document.querySelectorAll('[data-tab]');
    var panels = document.querySelectorAll('[data-panel]');

    function mostrarTab(nombre) {
        tabs.forEach(function (tab) {
            var activa = tab.getAttribute('data-tab') === nombre;
            tab.classList.toggle('is-active', activa);
            tab.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-panel') !== nombre;
        });
    }
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { mostrarTab(tab.getAttribute('data-tab')); });
    });

    /* ---------- Suma de pesos ---------- */
    var pesos     = form.querySelectorAll('[data-peso]');
    var total     = document.getElementById('total-pesos');
    var estado    = document.getElementById('estado-pesos');
    var estadoTxt = document.getElementById('estado-pesos-texto');
    var proponer  = document.getElementById('btn-proponer');

    function actualizarTotal() {
        var suma = 0;
        pesos.forEach(function (input) { suma += parseFloat(input.value) || 0; });
        var ok = suma === 100;

        total.textContent = suma + ' %';
        total.classList.toggle('is-bad', !ok);
        estado.classList.toggle('status--ok', ok);
        estado.classList.toggle('status--bad', !ok);
        estadoTxt.textContent = ok
            ? 'Suma 100 %: se puede proponer'
            : 'La suma actual es ' + suma + ' %. Debe sumar exactamente 100 % para proponer.';

        // Solo se puede proponer cuando los pesos suman 100 %
        if (proponer) {
            proponer.disabled = !ok;
            proponer.title = ok ? '' : 'Los pesos deben sumar 100 %';
        }
    }
    pesos.forEach(function (input) { input.addEventListener('input', actualizarTotal); });
    actualizarTotal();

    /* ---------- Factor seleccionado: cambia la escala de la derecha ---------- */
    var filas   = form.querySelectorAll('[data-factor]');
    var escalas = form.querySelectorAll('[data-escala]');
    var combo   = document.getElementById('factor-select');

    function seleccionarFactor(clave) {
        filas.forEach(function (fila) {
            fila.classList.toggle('is-selected', fila.getAttribute('data-factor') === clave);
        });
        escalas.forEach(function (grupo) {
            grupo.hidden = grupo.getAttribute('data-escala') !== clave;
        });
        if (combo && combo.value !== clave) combo.value = clave;
    }

    filas.forEach(function (fila) {
        fila.addEventListener('click', function () { seleccionarFactor(fila.getAttribute('data-factor')); });
        fila.addEventListener('focusin', function () { seleccionarFactor(fila.getAttribute('data-factor')); });
    });
    if (combo) combo.addEventListener('change', function () { seleccionarFactor(combo.value); });

    /* ---------- Envío ---------- */
    // Marca en rojo el motivo si falta al intentar proponer
    if (proponer) {
        proponer.addEventListener('click', function () { form.classList.add('form--invalid'); });
    }

    // Mientras no exista la ruta versiones.update el formulario apunta a "#" y no envía nada (prototipo).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 