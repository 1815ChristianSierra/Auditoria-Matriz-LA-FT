document.addEventListener('DOMContentLoaded', function () {
    var filas   = document.querySelectorAll('[data-cliente]');
    var fichas  = document.querySelectorAll('[data-ficha]');
    if (!filas.length) return;

    var buscar  = document.getElementById('buscar');
    var tipo    = document.getElementById('filtro-tipo');
    var pep     = document.getElementById('filtro-pep');
    var conteo  = document.getElementById('conteo');
    var vacio   = document.getElementById('fila-vacia');

    function normalizar(texto) {
        return (texto || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    /* ---------- Seleccionar cliente: cambia la ficha de la derecha ---------- */
    function seleccionar(codigo) {
        filas.forEach(function (fila) {
            fila.classList.toggle('is-selected', fila.getAttribute('data-cliente') === codigo);
        });
        fichas.forEach(function (ficha) {
            ficha.hidden = ficha.getAttribute('data-ficha') !== codigo;
        });
    }

    filas.forEach(function (fila) {
        fila.addEventListener('click', function () {
            seleccionar(fila.getAttribute('data-cliente'));
        });
        fila.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                seleccionar(fila.getAttribute('data-cliente'));
            }
        });
    });

    /* ---------- Filtros de la tabla ---------- */
    function filtrar() {
        var q = normalizar(buscar ? buscar.value : '');
        var visibles = 0;

        filas.forEach(function (fila) {
            var texto = normalizar(fila.getAttribute('data-cliente') + ' ' + fila.getAttribute('data-nombre'));
            var okTexto = !q || texto.indexOf(q) !== -1;
            var okTipo  = !tipo || tipo.value === 'todos' || fila.getAttribute('data-tipo') === tipo.value;
            var okPep   = !pep  || pep.value  === 'todos' || fila.getAttribute('data-pep')  === pep.value;
            var mostrar = okTexto && okTipo && okPep;

            fila.hidden = !mostrar;
            if (mostrar) visibles++;
        });

        if (conteo) conteo.textContent = 'Mostrando ' + visibles + ' de ' + filas.length;
        if (vacio) vacio.hidden = visibles !== 0;
    }

    [buscar, tipo, pep].forEach(function (control) {
        if (!control) return;
        control.addEventListener('input', filtrar);
        control.addEventListener('change', filtrar);
    });

    filtrar();
});
 