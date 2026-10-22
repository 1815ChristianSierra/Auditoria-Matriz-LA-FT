document.addEventListener('DOMContentLoaded', function () {
    var formularios = document.querySelectorAll('form.inline-form');
    if (!formularios.length) return;

    formularios.forEach(function (form) {
        var boton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (e) {
            // "Cerrar evaluación" pide confirmación antes de enviarse
            var mensaje = form.getAttribute('data-confirm');
            if (mensaje && !window.confirm(mensaje)) {
                e.preventDefault();
                return;
            }

            // Sin ruta el formulario no envía nada
            if (form.getAttribute('action') === '#') {
                e.preventDefault();
                return;
            }

            // Evita enviar dos veces seguidas el cálculo o el cierre
            if (boton) boton.disabled = true;
        });
    });

    // Al volver con el botón "Atrás" los botones deben quedar activos otra vez
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        formularios.forEach(function (form) {
            var boton = form.querySelector('button[type="submit"]');
            if (boton && !boton.hasAttribute('title')) boton.disabled = false;
        });
    });
});
 