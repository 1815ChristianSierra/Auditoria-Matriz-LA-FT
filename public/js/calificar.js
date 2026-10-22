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

    /* ---------- Calcular resultado: promedio ponderado (dato faltante = nivel 3) ---------- */
    function calcular() {
        var total = 0;
        rows.forEach(function (row) {
            var peso   = parseFloat(row.getAttribute('data-peso')) || 0;
            var marca  = row.querySelector('input[type="radio"]:checked');
            var falta  = row.querySelector('[data-falta]');
            var nivel  = (!marca || (falta && falta.checked)) ? 3 : parseFloat(marca.value);
            total += (peso * nivel) / 100;
        });
        return total;
    }

    function resetCalculo() {
        if (!btnCalcular) return;
        btnCalcular.classList.remove('is-done');
        if (btnLabel) btnLabel.textContent = labelInicial;
    }

    if (btnCalcular) {
        btnCalcular.addEventListener('click', function () {
            btnCalcular.disabled = true;
            if (btnLabel) btnLabel.textContent = 'Procesando...';

            setTimeout(function () {
                btnCalcular.disabled = false;
                btnCalcular.classList.add('is-done');
                if (btnLabel) btnLabel.textContent = 'Calculado: ' + calcular().toFixed(2) + ' / 5.00';
            }, 500);
        });
    }

    form.addEventListener('change', function () {
        updateResumen();
        resetCalculo();
    });
    updateResumen();

    /* ---------- Envío ---------- */
    // Mientras no exista la ruta calificaciones.store el formulario apunta a "#" y no envía nada (prototipo).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 