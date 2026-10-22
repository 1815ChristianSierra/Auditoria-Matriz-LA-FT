document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-decision');
    if (!form) return;

    var just    = document.getElementById('justificacion');
    var counter = document.getElementById('justificacion-count');

    /* ---------- Contador de caracteres ---------- */
    function updateCount() {
        if (!just || !counter) return;
        var max = just.maxLength > 0 ? just.maxLength : 500;
        counter.textContent = just.value.length + ' / ' + max + ' caract.';
    }
    if (just) {
        just.addEventListener('input', updateCount);
        updateCount();
    }

    /* ---------- Plan de acción: responsable, fecha y plan son obligatorios solo con esa decisión ---------- */
    var camposPlan = ['responsable', 'fecha_compromiso', 'plan_accion']
        .map(function (id) { return document.getElementById(id); })
        .filter(Boolean);
    var marcas = form.querySelectorAll('[data-plan-req]');

    function syncPlan() {
        var elegida = form.querySelector('input[name="decision"]:checked');
        var esPlan = !!elegida && elegida.value === 'plan_de_accion';
        camposPlan.forEach(function (campo) { campo.required = esPlan; });
        marcas.forEach(function (marca) { marca.hidden = !esPlan; });
    }
    form.querySelectorAll('input[name="decision"]').forEach(function (radio) {
        radio.addEventListener('change', syncPlan);
    });
    syncPlan();

    /* ---------- Envío ---------- */
    // Sin la ruta decisiones.store el formulario apunta a "#" y no envía nada.
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') {
            e.preventDefault();
            return;
        }

        // Se desactiva después de armar el envío para evitar guardar la decisión dos veces
        var enviador = e.submitter;
        if (enviador) setTimeout(function () { enviador.disabled = true; }, 0);
    });

    // Al volver con el botón "Atrás" el botón debe quedar activo otra vez
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = false; });
    });
});
 