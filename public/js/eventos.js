document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-evento');
    if (!form) return;

    var select  = document.getElementById('cliente');
    var desc    = document.getElementById('descripcion');
    var counter = document.getElementById('descripcion-count');

    function el(name) { return document.querySelector('[data-cli="' + name + '"]'); }
    function setText(name, value) { var n = el(name); if (n) n.textContent = value; }
    function setDot(name, clase, texto) {
        var n = el(name);
        if (!n) return;
        n.className = 'dot-status ' + clase;
        n.textContent = texto;
    }

    /* ---------- Tarjetas del cliente seleccionado ---------- */
    function updateCliente() {
        if (!select) return;
        var opt = select.options[select.selectedIndex];
        var d = opt.dataset;

        setText('nombre', opt.value + ' · ' + d.nombre);

        // "Ver ficha completa" abre la ficha del cliente elegido
        var ficha = el('ficha');
        if (ficha && ficha.getAttribute('data-base')) ficha.href = ficha.getAttribute('data-base') + '?cliente=' + encodeURIComponent(opt.value);
        setText('tipo', d.tipo);
        setText('desde', d.desde);
        setText('previos', d.previos);
        setDot('ingresos', d.ingresos === '1' ? 'is-ok' : 'is-bad', d.ingresos === '1' ? 'Sí' : 'No');
        setDot('pep', d.pep === '1' ? 'is-warn' : 'is-neutral', d.pep === '1' ? 'Sí' : 'No');

        // Verificación en listas
        var hay = d.coincidencia !== '';
        var alerta = document.querySelector('[data-lista="alerta"]');
        var limpio = document.querySelector('[data-lista="limpio"]');
        var sugerencia = document.querySelector('[data-lista="sugerencia"]');
        var titulo = document.querySelector('[data-lista="titulo"]');
        var confirmada = hay && d.coincidencia.indexOf('CONFIRMADA') !== -1;
        var etiqueta = document.querySelector('[data-lista="etiqueta"]');

        if (alerta) alerta.hidden = !hay;
        if (limpio) limpio.hidden = hay;
        if (sugerencia) sugerencia.hidden = !confirmada;   // la sugerencia "PEP/Sanciones = 5" es solo para coincidencias confirmadas
        if (titulo) titulo.textContent = confirmada ? 'Coincidencia confirmada' : 'Coincidencia parcial';
        if (etiqueta) {
            etiqueta.className = 'tag ' + (hay ? 'tag--rose' : 'tag--ok');
            etiqueta.textContent = hay ? 'Alerta roja' : 'Sin alerta';
        }
        setText('coincidencia', d.coincidencia);
    }

    if (select) {
        select.addEventListener('change', updateCliente);
        updateCliente();
    }

    /* ---------- Contador de caracteres ---------- */
    function updateCount() {
        if (desc && counter) counter.textContent = desc.value.length + ' caracteres';
    }
    if (desc) {
        desc.addEventListener('input', updateCount);
        updateCount();
    }

    /* ---------- Envío ---------- */
    // Mientras no exista la ruta eventos.store el formulario apunta a "#" y no envía nada (prototipo).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 