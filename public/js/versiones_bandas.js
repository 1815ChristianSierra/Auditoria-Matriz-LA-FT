document.addEventListener('DOMContentLoaded', function () {
    var filas = document.querySelectorAll('[data-banda-fila]');
    if (!filas.length) return;

    /* ---------- Topes: números enteros dentro de su rango ---------- */
    document.querySelectorAll('[data-tope]').forEach(function (input) {
        input.addEventListener('change', function () {
            var min = input.min !== '' ? parseInt(input.min, 10) : 0;
            var max = input.max !== '' ? parseInt(input.max, 10) : null;
            var valor = parseInt(input.value, 10);

            if (isNaN(valor)) valor = min;
            if (valor < min) valor = min;
            if (max !== null && valor > max) valor = max;
            input.value = valor;
        });
    });

    /* ---------- Ejemplo: la banda resultante según el puntaje ---------- */
    var puntaje = document.getElementById('bnd-puntaje');
    var etiqueta = document.getElementById('bnd-resultado');
    var segmentos = document.querySelectorAll('[data-banda-seg]');
    if (!puntaje || !etiqueta) return;

    var bandas = Array.prototype.map.call(filas, function (fila) {
        return {
            clave: fila.getAttribute('data-banda-fila'),
            nombre: fila.getAttribute('data-nombre'),
            desde: parseInt(fila.getAttribute('data-desde'), 10)
        };
    });

    // Se elige la banda de mayor "Desde" menor o igual al puntaje
    function bandaPara(valor) {
        var elegida = bandas[0];
        bandas.forEach(function (b) {
            if (b.desde <= valor && b.desde >= elegida.desde) elegida = b;
        });
        return elegida;
    }

    function actualizar() {
        var valor = parseInt(puntaje.value, 10);
        if (isNaN(valor)) valor = 0;
        valor = Math.max(0, Math.min(100, valor));

        var banda = bandaPara(valor);

        etiqueta.className = 'bnd-pill band--' + banda.clave;
        etiqueta.textContent = banda.nombre;
        segmentos.forEach(function (seg) {
            seg.classList.toggle('is-current', seg.getAttribute('data-banda-seg') === banda.clave);
        });
    }

    puntaje.addEventListener('input', actualizar);
    puntaje.addEventListener('change', function () {
        var valor = parseInt(puntaje.value, 10);
        puntaje.value = isNaN(valor) ? 0 : Math.max(0, Math.min(100, valor));
        actualizar();
    });
    actualizar();
});
 