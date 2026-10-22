document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-version');
    if (!form || !form.classList.contains('is-readonly')) return;

    // Modo lectura (pantalla "Ver versión"): nada se edita ni se envía

    form.addEventListener('submit', function (e) { e.preventDefault(); });

    // Se bloquean solo los campos de datos (los que tienen name). El selector de factor y el
    // ejemplo de puntaje no tienen name: siguen sirviendo para consultar.
    form.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (campo) {
        if (campo.type === 'hidden') return;

        if (campo.type === 'checkbox' || campo.type === 'radio' || campo.tagName === 'SELECT') {
            campo.disabled = true;
        } else {
            // readOnly (y no disabled): los scripts de las pestañas activan y desactivan los puntos con "disabled"
            campo.readOnly = true;
        }
    });
});
 