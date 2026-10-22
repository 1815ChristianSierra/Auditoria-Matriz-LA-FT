document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Pestañas: Factores / Agravantes / Controles ---------- */
    var tabs   = document.querySelectorAll('[data-tab]');
    var panels = document.querySelectorAll('[data-panel]');

    function mostrarTab(nombre) {
        tabs.forEach(function (tab) {
            var activa = tab.getAttribute('data-tab') === nombre;
            tab.classList.toggle('is-active', activa);
            tab.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-panel') !== nombre;
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { mostrarTab(tab.getAttribute('data-tab')); });
    });

    /* ---------- Interruptores "Activo" (factores, agravantes y controles): guardan el cambio en la BD ---------- */
    document.addEventListener('change', function (e) {
        var check = e.target;
        if (!check.matches || !check.matches('input[type="checkbox"][data-url]')) return;

        // Cambio que el propio script revirtió tras un error: no se vuelve a enviar
        if (check.hasAttribute('data-revertido')) return;

        var url = check.getAttribute('data-url');
        var token = document.querySelector('input[name="_token"]');
        if (!url || !token) return;

        var estado = check.checked;
        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token.value
            },
            body: JSON.stringify({ activo: estado ? 1 : 0 })
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
        }).catch(function () {
            // Si no se pudo guardar, el interruptor vuelve a su estado anterior
            check.setAttribute('data-revertido', '');
            check.checked = !estado;
            check.dispatchEvent(new Event('change', { bubbles: true }));   // actualiza contadores y barras
            check.removeAttribute('data-revertido');
            alert('No se pudo guardar el cambio. Inténtalo de nuevo.');
        });
    });

    /* ---------- Diálogo "Crear versión" ---------- */
    var dialogo = document.getElementById('dialogo-version');
    if (dialogo) {
        document.querySelectorAll('[data-dialogo-abrir]').forEach(function (boton) {
            boton.addEventListener('click', function () { dialogo.showModal(); });
        });
        dialogo.querySelectorAll('[data-dialogo-cerrar]').forEach(function (boton) {
            boton.addEventListener('click', function () { dialogo.close(); });
        });
        // Un clic en el fondo oscuro también lo cierra
        dialogo.addEventListener('click', function (e) { if (e.target === dialogo) dialogo.close(); });
        // Si el servidor devolvió un error del motivo, se vuelve a abrir
        if (dialogo.hasAttribute('data-abrir')) dialogo.showModal();

        // Sin ruta no se envía nada; con ruta, se evita enviar dos veces seguidas
        dialogo.querySelector('form').addEventListener('submit', function (e) {
            if (e.currentTarget.getAttribute('action') === '#') {
                e.preventDefault();
                return;
            }
            var enviador = e.submitter;
            if (enviador) setTimeout(function () { enviador.disabled = true; }, 0);
        });
    }

    /* ---------- Formulario "Nuevo control" ---------- */
    var form = document.getElementById('form-control');
    if (!form) return;

    var enviar = form.querySelector('[type="submit"]');

    // Marca en rojo los campos inválidos después del primer intento de guardar
    if (enviar) {
        enviar.addEventListener('click', function () { form.classList.add('was-validated'); });
    }

    // "Cancelar" limpia el formulario
    form.addEventListener('reset', function () { form.classList.remove('was-validated'); });

    // Mientras no exista la ruta catalogos.store el formulario apunta a "#" y no envía nada (prototipo).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') e.preventDefault();
    });
});
 