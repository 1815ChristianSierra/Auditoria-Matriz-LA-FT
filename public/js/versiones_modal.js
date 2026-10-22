document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modal-nueva-version');
    if (!modal) return;

    var form   = modal.querySelector('form');
    var numero = document.getElementById('nv-numero');
    var enviar = form ? form.querySelector('[type="submit"]') : null;
    var lastFocus = null;

    var existentes = [];
    try { existentes = JSON.parse(numero.getAttribute('data-existentes') || '[]'); } catch (e) { existentes = []; }

    /* ---------- Abrir / cerrar ---------- */
    function abrir() {
        lastFocus = document.activeElement;
        modal.classList.add('is-open');
        numero.focus();
        numero.select();
    }

    function cerrar() {
        modal.classList.remove('is-open');
        if (form) form.classList.remove('was-validated');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.querySelectorAll('[data-modal-open="modal-nueva-version"]').forEach(function (btn) {
        btn.addEventListener('click', abrir);
    });

    modal.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', cerrar);
    });

    // Clic en el fondo oscuro
    modal.addEventListener('mousedown', function (e) {
        if (e.target === modal) cerrar();
    });

    // Tecla Esc
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) cerrar();
    });

    /* ---------- Validación: el número de versión no se puede repetir ---------- */
    function validarNumero() {
        var valor = numero.value.trim().toLowerCase();
        numero.setCustomValidity(existentes.indexOf(valor) !== -1 ? 'Esa versión ya existe. Use otro número.' : '');
    }
    numero.addEventListener('input', validarNumero);
    validarNumero();

    // Marca en rojo los campos inválidos después del primer intento de envío
    if (enviar) {
        enviar.addEventListener('click', function () {
            form.classList.add('was-validated');
        });
    }

    /* ---------- Envío ---------- */
    // Mientras no exista la ruta versiones.store el formulario apunta a "#":
    // en ese caso solo se cierra el modal (prototipo, sin guardar).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') {
            e.preventDefault();
            cerrar();
        }
    });
});
 