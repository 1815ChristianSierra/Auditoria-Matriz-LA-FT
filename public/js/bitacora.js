document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Verificar integridad de la cadena (consulta a la BD) ---------- */
    var banner      = document.getElementById('integridad');
    var btnVerificar = document.getElementById('btn-verificar');
    var iconos = { ok: '✓', vacia: '–', alterada: '!', error: '!' };

    function pintarIntegridad(d) {
        banner.className = 'integrity integrity--' + d.estado;
        document.getElementById('integridad-icono').textContent = iconos[d.estado] || '!';
        document.getElementById('integridad-texto').textContent = d.mensaje;

        var hash = document.getElementById('integridad-hash');
        hash.textContent = d.ultimo_hash + '...';
        hash.hidden = !(d.estado === 'ok' && d.ultimo_hash);

        banner.title = d.hora ? 'Verificado a las ' + d.hora : '';

        // Se marcan en la tabla los registros que no pasan la verificación
        document.querySelectorAll('[data-registro]').forEach(function (fila) {
            var id = parseInt(fila.getAttribute('data-registro'), 10);
            fila.classList.toggle('is-altered', (d.ids || []).indexOf(id) !== -1);
        });
    }

    if (banner && btnVerificar) {
        btnVerificar.addEventListener('click', function () {
            var url = btnVerificar.getAttribute('data-url');
            if (!url) return;

            btnVerificar.disabled = true;
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(pintarIntegridad)
                .catch(function () {
                    pintarIntegridad({ estado: 'error', mensaje: 'No se pudo verificar la cadena. Inténtalo de nuevo.', ultimo_hash: '', ids: [], hora: '' });
                })
                .then(function () { btnVerificar.disabled = false; });
        });
    }

    var filas    = document.querySelectorAll('[data-registro]');
    var detalles = document.querySelectorAll('[data-detalle]');
    if (!filas.length) return;

    var form     = document.getElementById('form-filtros');
    var vacia    = document.getElementById('fila-vacia');

    /* ---------- Seleccionar un registro: cambia el detalle de abajo ---------- */
    function seleccionar(id) {
        filas.forEach(function (fila) {
            fila.classList.toggle('is-selected', fila.getAttribute('data-registro') === id);
        });
        detalles.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-detalle') !== id;
        });
    }

    filas.forEach(function (fila) {
        fila.addEventListener('click', function () { seleccionar(fila.getAttribute('data-registro')); });
        fila.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                seleccionar(fila.getAttribute('data-registro'));
            }
        });
    });

    /* ---------- Filtros ---------- */
    function valor(nombre) {
        var campo = form.elements[nombre];
        return campo ? campo.value : '';
    }

    function filtrar() {
        var desde   = valor('desde');
        var hasta   = valor('hasta');
        var usuario = valor('usuario');
        var accion  = valor('accion');
        var entidad = valor('entidad');
        var visibles = 0;

        filas.forEach(function (fila) {
            var dia = fila.getAttribute('data-fecha').slice(0, 10);   // AAAA-MM-DD
            var ok = (!desde   || dia >= desde)
                  && (!hasta   || dia <= hasta)
                  && (!usuario || fila.getAttribute('data-usuario') === usuario)
                  && (!accion  || fila.getAttribute('data-accion') === accion)
                  && (!entidad || fila.getAttribute('data-entidad-tipo') === entidad);

            fila.hidden = !ok;
            if (ok) visibles++;
        });

        if (vacia) vacia.hidden = visibles !== 0;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        filtrar();
    });
    form.querySelectorAll('select, input').forEach(function (campo) {
        campo.addEventListener('change', filtrar);
    });
    filtrar();

    /* ---------- Exportar CSV (solo las filas visibles) ---------- */
    var btnExportar = document.getElementById('btn-exportar');

    function celdaCsv(texto) {
        return '"' + String(texto == null ? '' : texto).replace(/"/g, '""') + '"';
    }

    if (btnExportar) {
        btnExportar.addEventListener('click', function () {
            var columnas = ['id', 'fecha', 'usuario', 'accion', 'entidad', 'motivo', 'aprobador', 'hash'];
            var lineas = [columnas.join(',')];

            filas.forEach(function (fila) {
                if (fila.hidden) return;
                lineas.push(columnas.map(function (c) { return celdaCsv(fila.getAttribute('data-' + c)); }).join(','));
            });

            // El BOM hace que Excel reconozca los acentos
            var blob = new Blob(['\ufeff' + lineas.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            var enlace = document.createElement('a');
            enlace.href = URL.createObjectURL(blob);
            enlace.download = 'bitacora_' + new Date().toISOString().slice(0, 10) + '.csv';
            document.body.appendChild(enlace);
            enlace.click();
            document.body.removeChild(enlace);
            URL.revokeObjectURL(enlace.href);
        });
    }
});
 