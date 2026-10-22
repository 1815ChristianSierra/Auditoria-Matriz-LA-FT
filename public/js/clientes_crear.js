document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-cliente');
    if (!form) return;

    var nombre      = document.getElementById('nombre');
    var nombreError = document.getElementById('nombre-error');

    var ingresos      = document.getElementById('ingresos');
    var ingresosLabel = document.getElementById('ingresos-estado');
    var pep           = document.getElementById('es_pep');
    var pepLabel      = document.getElementById('pep-estado');
    var vinculo       = document.getElementById('vinculo_pep');
    var vinculoCampo  = document.getElementById('vinculo-campo');

    /* ---------- Etiquetas Sí / No de los interruptores ---------- */
    function syncLabel(check, label) {
        if (!check || !label) return;
        label.textContent = check.checked ? 'Sí' : 'No';
        label.classList.toggle('is-on', check.checked);
    }

    /* ---------- Vínculo con el PEP: solo se habilita si es PEP ---------- */
    function syncPep() {
        syncLabel(pep, pepLabel);
        if (!pep || !vinculo) return;
        vinculo.disabled = !pep.checked;
        if (vinculoCampo) vinculoCampo.classList.toggle('is-disabled', !pep.checked);
        if (!pep.checked) vinculo.value = '';
    }

    if (ingresos) ingresos.addEventListener('change', function () { syncLabel(ingresos, ingresosLabel); });
    if (pep) {
        pep.addEventListener('change', function () {
            syncPep();
            if (pep.checked && vinculo) vinculo.focus();
        });
    }
    syncLabel(ingresos, ingresosLabel);
    syncPep();

    /* ---------- Validación del nombre ---------- */
    function mostrarError(mostrar) {
        if (!nombre || !nombreError) return;
        nombre.classList.toggle('control--error', mostrar);
        nombre.setAttribute('aria-invalid', mostrar ? 'true' : 'false');
        nombreError.hidden = !mostrar;
    }

    if (nombre) {
        nombre.addEventListener('input', function () {
            if (nombre.value.trim() !== '') mostrarError(false);
        });
    }

    /* ---------- Envío ---------- */
    form.addEventListener('submit', function (e) {
        if (nombre && nombre.value.trim() === '') {
            e.preventDefault();
            mostrarError(true);
            nombre.focus();
            return;
        }
        // Mientras no exista la ruta clientes.store el formulario apunta a "#" y no envía nada (prototipo).
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 