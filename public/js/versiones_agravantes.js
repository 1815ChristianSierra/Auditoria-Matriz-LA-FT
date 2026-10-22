document.addEventListener('DOMContentLoaded', function () {
    var cuerpo = document.getElementById('vag-filas');
    if (!cuerpo) return;

    var elActivos   = document.getElementById('vag-activos');
    var elImpacto   = document.getElementById('vag-impacto');
    var elTope      = document.getElementById('vag-tope');
    var elCoherencia = document.getElementById('vag-coherencia');

    // El tope se lee en vivo de la pestaña "Bandas y topes" (campo tope-agravantes); si no existe, se usa el de la vista.
    var campoTope = document.getElementById('tope-agravantes');
    var topeInicial = parseInt(cuerpo.getAttribute('data-tope'), 10) || 0;

    function tope() {
        var valor = campoTope ? parseInt(campoTope.value, 10) : NaN;
        return isNaN(valor) ? topeInicial : valor;
    }

    function texto(el, valor) { if (el) el.textContent = valor; }

    /* ---------- Escenario del ejemplo: n agravantes × p puntos, con tope ---------- */
    function escenario(prefijo, n, p, t) {
        var fila  = document.getElementById(prefijo + '-fila');
        var total = n * p;
        var superaTope = total > t;

        texto(document.getElementById(prefijo + '-texto'), n + ' agravantes × ' + p + ' puntos = ' + total);
        texto(document.getElementById(prefijo + '-res'), superaTope ? 'se suman ' + t + ' (tope)' : 'se suman ' + total);
        if (fila) fila.classList.toggle('is-capped', superaTope);
    }

    /* ---------- Recalcula todo ---------- */
    function actualizar() {
        var t = tope();
        var filas = cuerpo.querySelectorAll('tr');
        var activos = 0, suma = 0, coherente = true;

        filas.forEach(function (fila) {
            var incluir = fila.querySelector('[data-incluir]');
            var puntos  = fila.querySelector('[data-puntos]');
            var on = incluir.checked;

            fila.classList.toggle('is-off', !on);
            puntos.disabled = !on;
            puntos.max = t;

            if (on) {
                var v = parseInt(puntos.value, 10) || 0;
                activos++;
                suma += v;
                if (v > t) coherente = false;
            }
        });

        texto(elActivos, activos + '/' + filas.length);
        texto(elImpacto, '+' + Math.min(suma, t) + ' pts máx');
        texto(elTope, t);

        // Puntos "típicos" para el ejemplo: el promedio de los agravantes incluidos
        var p = activos ? Math.round(suma / activos) : 0;
        escenario('vag-ej1', 3, p, t);
        escenario('vag-ej2', 6, p, t);

        if (elCoherencia) {
            elCoherencia.textContent = coherente ? 'Coherencia verificada' : 'Un agravante supera el tope';
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
    document.querySelectorAll('[data-ir-tab]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var destino = document.querySelector('[data-tab="' + btn.getAttribute('data-ir-tab') + '"]');
            if (destino) destino.click();
        });
    });

    actualizar();
});
 