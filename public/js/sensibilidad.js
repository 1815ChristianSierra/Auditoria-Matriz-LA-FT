document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Evaluación y versión base: se aplican al cambiarlas ---------- */
    var filtros = document.getElementById('form-base');
    if (filtros) {
        var evaluacion  = document.getElementById('evaluacion');
        var versionBase = document.getElementById('version_base');

        if (evaluacion) {
            evaluacion.addEventListener('change', function () {
                // Otra evaluación: la versión base vuelve a ser la suya
                if (versionBase) versionBase.disabled = true;
                filtros.submit();
            });
        }
        if (versionBase) {
            versionBase.addEventListener('change', function () { filtros.submit(); });
        }
    }

    var form    = document.getElementById('form-simular');
    var sliders = document.querySelectorAll('.slider');
    if (!form || !sliders.length) return;

    var sumBadge       = document.getElementById('suma-badge');
    var sumText        = document.getElementById('suma-texto');
    var btnSimular     = document.getElementById('btn-simular');
    var btnRestablecer = document.getElementById('btn-restablecer');
    var mensaje        = document.getElementById('sim-mensaje');
    var etiquetaInicial = btnSimular ? btnSimular.textContent : 'Simular';
    var enviando       = false;

    function redondear(n) { return Math.round(n * 100) / 100; }
    function formato(n)   { return String(redondear(n)); }

    function avisar(texto) {
        if (!mensaje) return;
        mensaje.textContent = texto || '';
        mensaje.hidden = !texto;
    }

    /* ---------- Actualiza los % simulados y la suma ---------- */
    function update() {
        var suma = 0;

        sliders.forEach(function (slider) {
            var valor = parseFloat(slider.value) || 0;
            suma += valor;

            var fila = slider.closest('[data-factor]');
            var salida = fila ? fila.querySelector('[data-sim]') : null;
            if (salida) salida.textContent = formato(valor);
        });

        suma = redondear(suma);
        var ok = Math.abs(suma - 100) < 0.01;
        sumBadge.classList.toggle('sum--ok', ok);
        sumBadge.classList.toggle('sum--bad', !ok);
        sumText.textContent = ok ? 'Suma: 100 % ✓' : 'Suma: ' + formato(suma) + ' % (≠ 100 %)';

        // La simulación solo se puede lanzar cuando los pesos suman 100 %
        if (btnSimular) {
            btnSimular.disabled = !ok || enviando;
            btnSimular.title = ok ? '' : 'Los pesos deben sumar 100 %';
        }
    }

    /* ---------- Pinta el resultado de la simulación (tabla y tarjetas) ---------- */
    function celda(clase, texto) {
        var td = document.createElement('td');
        td.className = clase;
        td.textContent = texto;
        return td;
    }

    function pintar(datos) {
        var cuerpo = document.getElementById('ranking-cuerpo');
        if (cuerpo) {
            cuerpo.textContent = '';

            if (!datos.ranking.length) {
                var vacia = document.createElement('tr');
                var td = celda('col-center empty-note', 'Esta evaluación todavía no tiene eventos calculados.');
                td.colSpan = 5;
                vacia.appendChild(td);
                cuerpo.appendChild(vacia);
            }

            datos.ranking.forEach(function (r) {
                var delta = r.sim - r.base;
                var claseDelta = Math.abs(delta) < 0.05 ? '' : (delta > 0 ? ' delta--up' : ' delta--down');
                var tr = document.createElement('tr');

                tr.appendChild(celda('col-event', r.evento));
                tr.appendChild(celda('col-mono col-center', r.pos_base + ' · ' + r.base.toFixed(1)));
                tr.appendChild(celda('col-mono col-center col-sim', r.pos_sim + ' · ' + r.sim.toFixed(1)));
                tr.appendChild(celda('col-mono col-center delta' + claseDelta, (delta > 0.05 ? '+' : '') + delta.toFixed(1)));

                var banda = celda('col-right', '');
                var etiqueta = document.createElement('span');
                etiqueta.className = 'badge band--' + r.banda_sim;
                etiqueta.textContent = r.banda_base_nombre + ' → ' + r.banda_sim_nombre;
                banda.appendChild(etiqueta);
                tr.appendChild(banda);

                cuerpo.appendChild(tr);
            });
        }

        var kBanda = document.getElementById('kpi-banda');
        var kOrden = document.getElementById('kpi-orden');
        var kDelta = document.getElementById('kpi-delta');
        if (kBanda) kBanda.textContent = datos.kpis.cambian_banda;
        if (kOrden) kOrden.textContent = datos.kpis.cambia_orden;
        if (kDelta) {
            kDelta.textContent = datos.kpis.mayor_delta.toFixed(1);
            kDelta.classList.toggle('kpi__value--warn', datos.kpis.mayor_delta >= 0.05);
        }
    }

    function textoDeError(datos) {
        if (datos && datos.message) return datos.message;
        if (datos && datos.errors) {
            var primero = Object.keys(datos.errors)[0];
            if (primero) return datos.errors[primero][0];
        }
        return 'No se pudo simular. Inténtalo de nuevo.';
    }

    /* ---------- Simular: pide el cálculo al servidor (no se guarda nada) ---------- */
    function simular() {
        if (enviando || form.getAttribute('action') === '#') return Promise.resolve();

        enviando = true;
        avisar('');
        if (btnSimular) btnSimular.textContent = 'Simulando…';
        update();

        return fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        }).then(function (respuesta) {
            return respuesta.json().catch(function () { return {}; }).then(function (datos) {
                return { ok: respuesta.ok, datos: datos };
            });
        }).then(function (res) {
            if (!res.ok) throw new Error(textoDeError(res.datos));
            pintar(res.datos);
        }).catch(function (error) {
            avisar(error.message || 'No se pudo simular. Inténtalo de nuevo.');
        }).then(function () {
            enviando = false;
            if (btnSimular) btnSimular.textContent = etiquetaInicial;
            update();
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        simular();
    });

    sliders.forEach(function (slider) {
        slider.addEventListener('input', update);
    });

    /* ---------- Restablecer: vuelve a los pesos base y muestra el resultado base ---------- */
    if (btnRestablecer) {
        btnRestablecer.addEventListener('click', function () {
            sliders.forEach(function (slider) {
                slider.value = slider.getAttribute('data-base');
            });
            update();
            simular();
        });
    }

    update();
});
 