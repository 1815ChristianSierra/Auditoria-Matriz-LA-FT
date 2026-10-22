document.addEventListener('DOMContentLoaded', function () {
    var formularios = document.querySelectorAll('form.inline-form');
    if (!formularios.length) return;

    formularios.forEach(function (form) {
        var boton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function (e) {
            // "Recalcular" pide confirmación: el resultado actual pasa a "Reemplazado"
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

            // Evita calcular dos veces seguidas
            if (boton) boton.disabled = true;
        });
    });

    // Al volver con el botón "Atrás" el botón debe quedar activo otra vez
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        formularios.forEach(function (form) {
            var boton = form.querySelector('button[type="submit"]');
            if (boton && !boton.hasAttribute('title')) boton.disabled = false;
        });
    });
});
 