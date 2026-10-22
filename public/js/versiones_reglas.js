document.addEventListener('DOMContentLoaded', function () {
    var cuerpo = document.getElementById('vrg-filas');
    if (!cuerpo) return;

    var contador = document.getElementById('vrg-contador');
    var vacio    = document.getElementById('vrg-vacio');
    var codigo   = document.getElementById('vrg-codigo');
    var factor   = document.getElementById('vrg-factor');
    var nivel    = document.getElementById('vrg-nivel');
    var banda    = document.getElementById('vrg-banda');
    var desc     = document.getElementById('vrg-desc');
    var agregar  = document.getElementById('vrg-agregar');
    var cancelar = document.getElementById('vrg-cancelar');

    function crear(tag, clase, texto) {
        var el = document.createElement(tag);
        if (clase) el.className = clase;
        if (texto != null) el.textContent = texto;
        return el;
    }

    function oculto(nombre, valor) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = nombre;
        input.value = valor;
        return input;
    }

    function filas() { return cuerpo.querySelectorAll('tr[data-regla]'); }

    /* ---------- Código automático (ESC-01, ESC-02, ...) ---------- */
    function siguienteCodigo() {
        var mayor = 0;
        filas().forEach(function (tr) {
            var n = parseInt(tr.getAttribute('data-regla').replace(/\D/g, ''), 10) || 0;
            if (n > mayor) mayor = n;
        });
        return 'ESC-' + (mayor + 1 < 10 ? '0' : '') + (mayor + 1);
    }

    /* ---------- Contador y estado vacío ---------- */
    function actualizar() {
        var n = filas().length;
        contador.textContent = n + (n === 1 ? ' regla activa' : ' reglas activas') + ' • Evaluación técnica directa';
        if (vacio) vacio.hidden = n > 0;
        codigo.value = siguienteCodigo();
    }

    /* ---------- Limpiar el formulario ---------- */
    function limpiar() {
        factor.value = '';
        nivel.value = '5';
        banda.value = 'critico';
        desc.value = '';
        [factor, desc].forEach(function (c) { c.classList.remove('is-invalid'); });
    }

    /* ---------- Agregar regla (queda en la tabla y se envía con el formulario de la versión) ---------- */
    function agregarRegla() {
        var okFactor = factor.value !== '';
        var okDesc   = desc.value.trim() !== '';
        factor.classList.toggle('is-invalid', !okFactor);
        desc.classList.toggle('is-invalid', !okDesc);

        if (!okFactor) { factor.focus(); return; }
        if (!okDesc)   { desc.focus(); return; }

        var cod = codigo.value;
        var tr = crear('tr', 'vrg-new');
        tr.setAttribute('data-regla', cod);

        var tdCod = crear('td');
        tdCod.appendChild(crear('span', 'vrg-chip', cod));
        tdCod.appendChild(oculto('reglas[' + cod + '][factor]', factor.value));
        tdCod.appendChild(oculto('reglas[' + cod + '][nivel]', nivel.value));
        tdCod.appendChild(oculto('reglas[' + cod + '][banda]', banda.value));
        tdCod.appendChild(oculto('reglas[' + cod + '][descripcion]', desc.value.trim()));
        tr.appendChild(tdCod);

        var etiquetaFactor = factor.options[factor.selectedIndex].text;
        var tdFac = crear('td', 'col-factor', etiquetaFactor);
        tdFac.title = etiquetaFactor;
        tr.appendChild(tdFac);

        var tdNiv = crear('td');
        tdNiv.appendChild(crear('span', 'vrg-level', nivel.value));
        tr.appendChild(tdNiv);

        var tdBan = crear('td');
        tdBan.appendChild(crear('span', 'vrg-pill band--' + banda.value, banda.options[banda.selectedIndex].text));
        tr.appendChild(tdBan);

        var tdDesc = crear('td', null, desc.value.trim());
        tdDesc.title = desc.value.trim();
        tr.appendChild(tdDesc);

        var tdAcc = crear('td', 'col-right');
        var quitar = crear('button', 'vrg-remove', 'Quitar');
        quitar.type = 'button';
        quitar.setAttribute('data-quitar', '');
        tdAcc.appendChild(quitar);
        tr.appendChild(tdAcc);

        cuerpo.insertBefore(tr, vacio || null);
        setTimeout(function () { tr.classList.remove('vrg-new'); }, 1500);

        limpiar();
        actualizar();
    }

    agregar.addEventListener('click', agregarRegla);
    cancelar.addEventListener('click', limpiar);

    [factor, desc].forEach(function (campo) {
        campo.addEventListener('input', function () { campo.classList.remove('is-invalid'); });
        campo.addEventListener('change', function () { campo.classList.remove('is-invalid'); });
    });

    /* ---------- Quitar regla ---------- */
    cuerpo.addEventListener('click', function (e) {
        var boton = e.target.closest('[data-quitar]');
        if (!boton) return;
        var tr = boton.closest('tr');
        if (tr) tr.remove();
        actualizar();
    });

    actualizar();
});
 