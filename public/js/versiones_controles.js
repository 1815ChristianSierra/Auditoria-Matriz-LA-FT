document.addEventListener('DOMContentLoaded', function () {
    var cuerpo = document.getElementById('vct-filas');
    if (!cuerpo) return;

    var elActivos    = document.getElementById('vct-activos');
    var elImpacto    = document.getElementById('vct-impacto');
    var elTope       = document.getElementById('vct-tope');
    var elCoherencia = document.getElementById('vct-coherencia');

    // El tope se lee en vivo de la pestaña "Bandas y topes" (campo tope-controles); si no existe, se usa el de la vista.
    var campoTope = document.getElementById('tope-controles');
    var topeInicial = parseInt(cuerpo.getAttribute('data-tope'), 10) || 0;

    function tope() {
        var valor = campoTope ? parseInt(campoTope.value, 10) : NaN;
        return isNaN(valor) ? topeInicial : valor;
    }

    function texto(el, valor) { if (el) el.textContent = valor; }

    /* ---------- Escenario del ejemplo: suma de los puntos que restan, con tope ---------- */
    function escenario(prefijo, partes, t) {
        var fila = document.getElementById(prefijo + '-fila');
        var suma = partes.reduce(function (a, b) { return a + b; }, 0);
        var superaTope = suma > t;

        texto(document.getElementById(prefijo + '-texto'),
            partes.length ? 'Controles ' + partes.join(' + ') + ' = ' + suma : 'Sin controles incluidos = 0');
        texto(document.getElementById(prefijo + '-res'),
            'se restan ' + Math.min(suma, t) + (superaTope ? ' (tope)' : ''));
        if (fila) fila.classList.toggle('is-capped', superaTope);
    }

    /* ---------- Recalcula todo ---------- */
    function actualizar() {
        var t = tope();
        var filas = cuerpo.querySelectorAll('tr');
        var partes = [], suma = 0, coherente = true;

        filas.forEach(function (fila) {
            var incluir = fila.querySelector('[data-incluir]');
            var puntos  = fila.querySelector('[data-puntos]');
            var ningun  = fila.querySelector('[data-ninguno]');
            var on = incluir.checked;

            fila.classList.toggle('is-off', !on);
            puntos.hidden = !on;
            puntos.disabled = !on;
            puntos.max = t;
            if (ningun) ningun.hidden = on;

            if (on) {
                var v = parseInt(puntos.value, 10) || 0;
                partes.push(v);
                suma += v;
                if (v > t) coherente = false;
            }
        });

        texto(elActivos, partes.length + '/' + filas.length);
        var resta = Math.min(suma, t);
        texto(elImpacto, (resta ? '-' + resta : '0') + ' pts máx');
        texto(elTope, t);

        // El segundo escenario agrega un control más, con los puntos promedio de los incluidos
        var p = partes.length ? Math.round(suma / partes.length) : 0;
        escenario('vct-ej1', partes, t);
        escenario('vct-ej2', partes.concat([p]), t);

        if (elCoherencia) {
            elCoherencia.textContent = coherente ? 'Coherencia verificada' : 'Un control supera el tope';
            elCoherencia.classList.toggle('is-warn', !coherente);
        }
    }

    /* ---------- Puntos: enteros entre 0 y el tope ---------- */
    cuerpo.addEventListener('change', function (e) {
        var puntos = e.target.closest('[data-puntos]');
        if (puntos) {
            var t = tope();
            var v = parseInt(puntos.value, 10);
            if (isNaN(v) || v < 0) v = 0;
            if (v > t) v = t;
            puntos.value = v;
        }
        actualizar();
    });
    cuerpo.addEventListener('input', actualizar);

    if (campoTope) {
        campoTope.addEventListener('input', actualizar);
        campoTope.addEventListener('change', actualizar);
    }

    /* ---------- "Editar en «Bandas y topes»": cambia a esa pestaña ---------- */
    document.querySelectorAll('.vct-wrap [data-ir-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var destino = document.querySelector('[data-tab="' + btn.getAttribute('data-ir-tab') + '"]');
            if (destino) destino.click();
        });
    });

    actualizar();
});
 