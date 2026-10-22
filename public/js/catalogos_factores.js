document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-factor');
    if (!form) return;

    var filas    = document.getElementById('factores-filas');
    var activos  = document.getElementById('factores-activos');
    var total    = document.getElementById('factores-total');
    var codigo   = document.getElementById('f-codigo');
    var nombre   = document.getElementById('f-nombre');
    var desc     = document.getElementById('f-desc');
    var enviar   = form.querySelector('[type="submit"]');

    /* ---------- Contadores ("7 / 7" y "7 elementos") ---------- */
    function actualizarContadores() {
        var checks = filas.querySelectorAll('input[type="checkbox"]');
        var marcados = 0;
        checks.forEach(function (c) { if (c.checked) marcados++; });
        activos.textContent = marcados + ' / ' + checks.length;
        total.textContent = checks.length + (checks.length === 1 ? ' elemento' : ' elementos');
    }
    filas.addEventListener('change', actualizarContadores);

    /* ---------- El código no se puede repetir ---------- */
    function codigosExistentes() {
        return Array.prototype.map.call(filas.querySelectorAll('[data-codigo]'), function (td) {
            return td.getAttribute('data-codigo');
        });
    }
    function validarCodigo() {
        var valor = codigo.value.trim().toUpperCase();
        codigo.setCustomValidity(valor && codigosExistentes().indexOf(valor) !== -1 ? 'Ese código ya existe. Use otro.' : '');
    }
    codigo.addEventListener('input', validarCodigo);
    validarCodigo();

    // Marca en rojo los campos inválidos después del primer intento de guardar
    if (enviar) enviar.addEventListener('click', function () { form.classList.add('was-validated'); });

    form.addEventListener('reset', function () {
        form.classList.remove('was-validated');
        setTimeout(validarCodigo, 0);
    });

    /* ---------- Agregar la fila (solo en pantalla: prototipo) ---------- */
    function crear(tag, clase, texto) {
        var el = document.createElement(tag);
        if (clase) el.className = clase;
        if (texto != null) el.textContent = texto;
        return el;
    }

    function agregarFila(cod, nom, descripcion) {
        var tr = crear('tr', 'fct-new');

        var tdCod = crear('td', 'col-code');
        tdCod.setAttribute('data-codigo', cod);
        tdCod.appendChild(crear('span', 'fct-chip', cod));
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
        setTimeout(function () { tr.classList.remove('fct-new'); }, 1500);
    }

    form.addEventListener('submit', function (e) {
        // Con una ruta real (catalogos.factores.store) el formulario se envía normalmente.
        if (form.getAttribute('action') !== '#') return;

        e.preventDefault();
        agregarFila(codigo.value.trim().toUpperCase(), nombre.value.trim(), desc.value.trim());
        form.reset();
        actualizarContadores();
    });

    actualizarContadores();
});
 