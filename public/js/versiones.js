document.addEventListener('DOMContentLoaded', function () {
    var dataEl = document.getElementById('versiones-data');
    if (!dataEl) return;

    var datos = JSON.parse(dataEl.textContent);   // { versiones: { 'v1.0': {...} }, parametros: [{clave,label,icono}] }

    var selBase  = document.getElementById('cmp-base');
    var selComp  = document.getElementById('cmp-comp');
    var btn      = document.getElementById('btn-comparar');
    var thBase   = document.getElementById('th-base');
    var thComp   = document.getElementById('th-comp');
    var cuerpo   = document.getElementById('diff-cuerpo');
    var contador = document.getElementById('diff-conteo');

    // Sin versiones no se dibuja el panel de comparación
    if (!selBase || !selComp) return;

    function celda(tag, texto, clase) {
        var el = document.createElement(tag);
        if (clase) el.className = clase;
        el.textContent = texto;
        return el;
    }

    /* ---------- Dibuja la tabla de diferencias ---------- */
    function render() {
        var base = datos.versiones[selBase.value];
        var comp = datos.versiones[selComp.value];
        if (!base || !comp) return;

        thBase.textContent = selBase.value;
        thComp.textContent = selComp.value;
        cuerpo.innerHTML = '';

        datos.parametros.forEach(function (p) {
            var a = base[p.clave];
            var b = comp[p.clave];
            var delta = b - a;

            var fila = document.createElement('tr');

            // Parámetro + ícono
            var tdParam = document.createElement('td');
            var wrap = document.createElement('div');
            wrap.className = 'diff__param';
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('viewBox', '0 0 24 24');
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', p.icono);
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');
            svg.appendChild(path);
            wrap.appendChild(svg);
            var nombre = document.createElement('span');
            nombre.textContent = p.label;
            wrap.appendChild(nombre);
            tdParam.appendChild(wrap);
            fila.appendChild(tdParam);

            fila.appendChild(celda('td', a, 'col-center col-mono'));
            fila.appendChild(celda('td', b, 'col-center col-mono col-new'));

            var tdDelta = document.createElement('td');
            tdDelta.className = 'col-center';
            var chip = celda('span', (delta > 0 ? '+' : '') + delta, 'delta' + (delta > 0 ? ' delta--up' : (delta < 0 ? ' delta--down' : '')));
            tdDelta.appendChild(chip);
            fila.appendChild(tdDelta);

            cuerpo.appendChild(fila);
        });

        if (contador) contador.textContent = datos.parametros.length + ' factores auditados';
    }

    if (btn) btn.addEventListener('click', render);
    if (selBase) selBase.addEventListener('change', render);
    if (selComp) selComp.addEventListener('change', render);

    /* ---------- "Comparar" en la tabla: fija esa versión como base ---------- */
    document.querySelectorAll('[data-comparar]').forEach(function (link) {
        link.addEventListener('click', function () {
            selBase.value = link.getAttribute('data-comparar');
            render();
            var panel = document.getElementById('panel-comparar');
            if (panel && panel.scrollIntoView) panel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        });
    });

    render();
});
 