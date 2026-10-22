document.addEventListener('DOMContentLoaded', function () {
    var form    = document.getElementById('form-decision');
    if (!form) return;

    var motivo  = document.getElementById('motivo-decision');
    var errorEl = document.getElementById('motivo-error');
    var minimo  = parseInt(motivo.getAttribute('data-min'), 10) || 10;

    /* ---------- Motivo de la decisión: obligatorio, mínimo de caracteres ---------- */
    function mostrarError(mostrar) {
        motivo.classList.toggle('control--error', mostrar);
        motivo.setAttribute('aria-invalid', mostrar ? 'true' : 'false');
        errorEl.hidden = !mostrar;
    }

    motivo.addEventListener('input', function () {
        if (motivo.value.trim().length >= minimo) mostrarError(false);
    });

    /* ---------- Envío ---------- */
    form.addEventListener('submit', function (e) {
        if (motivo.value.trim().length < minimo) {
            e.preventDefault();
            mostrarError(true);
            motivo.focus();
            return;
        }

        // Mientras no exista la ruta versiones.decidir el formulario apunta a "#" y no envía nada (prototipo).
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 