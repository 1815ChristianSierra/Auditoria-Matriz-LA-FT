document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-calificar');
    if (!form) return;

    var rows        = form.querySelectorAll('[data-factor]');
    var resumenText = document.getElementById('resumen-texto');
    var resumenBar  = document.getElementById('resumen-barra');
    var btnCalcular = document.getElementById('btn-calcular');
    var btnLabel    = btnCalcular ? btnCalcular.querySelector('[data-label]') : null;
    var labelInicial = btnLabel ? btnLabel.textContent : '';

    /* ---------- Resumen: factores calificados ---------- */
    function factorCalificado(row) {
        var falta = row.querySelector('[data-falta]');
        return !!row.querySelector('input[type="radio"]:checked') && !(falta && falta.checked);
    }

    function updateResumen() {
        var hechos = 0;
        rows.forEach(function (row) { if (factorCalificado(row)) hechos++; });
        var total = rows.length;

        resumenText.textContent = hechos + ' / ' + total + ' factores calificados';
        resumenText.classList.toggle('is-ok', hechos === total);
        resumenBar.style.width = (total ? (hechos / total) * 100 : 0) + '%';
        resumenBar.classList.toggle('is-ok', hechos === total);
    }

    /* ---------- Dato faltante: atenúa la fila ---------- */
    rows.forEach(function (row) {
        var falta = row.querySelector('[data-falta]');
        if (!falta) return;
        function sync() { row.classList.toggle('is-missing', falta.checked); }
        falta.addEventListener('change', sync);
        sync();
    });

    /* ---------- Evidencia obligatoria en agravantes y controles marcados ---------- */
    form.querySelectorAll('[data-evidence-toggle]').forEach(function (chk) {
        var item  = chk.closest('.check-item');
        var box   = item.querySelector('.check-item__evidence');
        var input = box ? box.querySelector('input') : null;

        function sync() {
            item.classList.toggle('is-checked', chk.checked);
            if (box) box.hidden = !chk.checked;
            if (input) input.required = chk.checked;
        }

        chk.addEventListener('change', function () {
            sync();
            if (chk.checked && input) input.focus();
        });
        sync();
    });

    form.addEventListener('change', updateResumen);
    updateResumen();

    /* ---------- Envío ---------- */
    // "Guardar calificaciones" y "Calcular resultado" envían este mismo formulario (accion=guardar / accion=calcular).
    // El cálculo real lo hace el servidor con el procedimiento sp_calcular_resultado.
    // Sin la ruta calificaciones.store el formulario apunta a "#" y no envía nada.
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') {
            e.preventDefault();
            return;
        }

        var enviador = e.submitter;
        if (!enviador) return;

        if (enviador === btnCalcular && btnLabel) btnLabel.textContent = 'Calculando...';

        // Se desactiva después de armar el envío (un botón desactivado antes no manda su valor) para evitar un doble clic
        setTimeout(function () { enviador.disabled = true; }, 0);
    });

    // Al volver con el botón "Atrás" los botones deben quedar activos otra vez
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = false; });
        if (btnCalcular) btnCalcular.disabled = false;
        if (btnLabel) btnLabel.textContent = labelInicial;
    });
});
 