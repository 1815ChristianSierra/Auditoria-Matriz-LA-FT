document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-filtros');
    if (!form) return;

    /* ---------- Filtros: se aplican solos al cambiarlos (el buscador, con Enter) ---------- */
    form.querySelectorAll('select, input[type="checkbox"]').forEach(function (campo) {
        campo.addEventListener('change', function () { form.submit(); });
    });
});
 