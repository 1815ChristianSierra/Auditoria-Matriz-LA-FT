document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-agravante');
    if (!form) return;

    var filas     = document.getElementById('agravantes-filas');
    var total     = document.getElementById('agravantes-total');
    var chip      = document.getElementById('agravantes-chip');
    var medidor   = document.getElementById('agravantes-medidor');
    var barra     = document.getElementById('agravantes-barra');
    var porcentaje = document.getElementById('agravantes-pct');
    var estado    = document.getElementById('agravantes-estado');
    var codigo    = document.getElementById('a-codigo');
    var nombre    = document.getElementById('a-nombre');
    var desc      = document.getElementById('a-desc');
    var enviar    = form.querySelector('[type="submit"]');

    var UMBRAL_ESTABLE = 75;   // % de agravantes activos a partir del cual se considera "Estable"

    function crear(tag, clase, texto) {
        var el = document.createElement(tag);
        if (clase) el.className = clase;
        if (texto != null) el.textContent = texto;
        return el;
    }

    /* ---------- Contadores, barra y estado ---------- */
    function actualizar() {
        var checks = filas.querySelectorAll('input[type="checkbox"]');
        var n = checks.length;
        var activos = 0;

        barra.innerHTML = '';
        checks.forEach(function (c) {
            var fila = c.closest('tr');
            if (c.checked) activos++;
            if (fila) fila.classList.toggle('is-off', !c.checked);

            var cod = fila ? fila.querySelector('[data-codigo]').getAttribute('data-codigo') : '';
            var seg = crear('span', c.checked ? '' : 'is-off');
            seg.title = cod + ': ' + (c.checked ? 'Activo' : 'Inactivo');
            barra.appendChild(seg);
        });

        var pct = n ? Math.round(activos / n * 100) : 0;
        var texto = activos + ' / ' + n;

        chip.textContent = texto;
        medidor.textContent = texto;
        total.textContent = n + (n === 1 ? ' elemento' : ' elementos');
        porcentaje.textContent = pct + '% del catálogo operativo';

        var estable = pct >= UMBRAL_ESTABLE;
        estado.textContent = estable ? 'Estable' : 'Revisar';
        estado.classList.toggle('is-warn', !estable);
    }
    filas.addEventListener('change', actualizar);

    /* ---------- Código automático (AGR-06, AGR-07, ...) ---------- */
    function siguienteCodigo() {
        var mayor = 0;
        filas.querySelectorAll('[data-codigo]').forEach(function (td) {
            var numero = parseInt(td.getAttribute('data-codigo').replace(/\D/g, ''), 10) || 0;
            if (numero > mayor) mayor = numero;
        });
        return 'AGR-' + (mayor + 1 < 10 ? '0' : '') + (mayor + 1);
    }

    // Marca en rojo los campos inválidos después del primer intento de guardar
    if (enviar) enviar.addEventListener('click', function () { form.classList.add('was-validated'); });

    form.addEventListener('reset', function () {
        form.classList.remove('was-validated');
        setTimeout(function () { codigo.value = siguienteCodigo(); }, 0);
    });

    /* ---------- Agregar la fila (solo en pantalla: prototipo) ---------- */
    function agregarFila(cod, nom, descripcion) {
        var tr = crear('tr', 'agr-new');

        var tdCod = crear('td', 'col-code');
        tdCod.setAttribute('data-codigo', cod);
        tdCod.appendChild(crear('span', 'agr-chip', cod));
        tr.appendChild(tdCod);

        var tdNom = crear('td', 'col-name', nom);
        tdNom.title = nom;
        tr.appendChild(tdNom);

        var tdDesc = crear('td', 'col-desc', descripcion || '—');
        tdDesc.title = descripcion || '';
        tr.appendChild(tdDesc);

        var tdAct = crear('td', 'col-right');
        var label = crear('label', 'toggle');
        var input = document.createElement('input');
        input.type = 'checkbox';
        input.checked = true;
        input.setAttribute('aria-label', 'Estado de ' + cod);
        label.appendChild(input);
        label.appendChild(crear('span', 'toggle__track'));
        tdAct.appendChild(label);
        tr.appendChild(tdAct);

        filas.appendChild(tr);

        // Deja visible la fila nueva
        var contenedor = filas.closest('.table-scroll');
        if (contenedor) contenedor.scrollTop = contenedor.scrollHeight;
        setTimeout(function () { tr.classList.remove('agr-new'); }, 1500);
    }

    form.addEventListener('submit', function (e) {
        // Con una ruta real (catalogos.agravantes.store) el formulario se envía normalmente.
        if (form.getAttribute('action') !== '#') return;

        e.preventDefault();
        agregarFila(codigo.value.trim().toUpperCase(), nombre.value.trim(), desc.value.trim());
        form.reset();
        actualizar();
    });

    actualizar();
});
 