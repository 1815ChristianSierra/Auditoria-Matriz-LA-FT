document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Filtros: al cambiar el estado o el responsable se aplican solos (el buscador, con Enter) ---------- */
    var filtros = document.querySelector('.filters form');
    if (filtros) {
        filtros.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { filtros.submit(); });
        });
    }

    var modal = document.getElementById('modal');
    if (!modal) return;

    var form   = modal.querySelector('form');
    var inicio = document.getElementById('ne-inicio');
    var fin    = document.getElementById('ne-fin');
    var lastFocus = null;

    /* ---------- Abrir / cerrar ---------- */
    function openModal() {
        lastFocus = document.activeElement;
        modal.classList.add('is-open');
        var first = modal.querySelector('select, input:not([readonly]), textarea');
        if (first) first.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.querySelectorAll('[data-modal-open="modal"]').forEach(function (btn) {
        btn.addEventListener('click', openModal);
    });

    modal.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    // Clic en el fondo oscuro
    modal.addEventListener('mousedown', function (e) {
        if (e.target === modal) closeModal();
    });

    // Tecla Esc
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    /* ---------- Fechas: el fin no puede ser menor al inicio ---------- */
    function syncDates() {
        if (!inicio || !fin) return;
        fin.min = inicio.value;
    }
    if (inicio) inicio.addEventListener('change', syncDates);
    syncDates();

    /* ---------- Envío ---------- */
    // Sin la ruta evaluaciones.store el formulario apunta a "#": en ese caso solo se cierra el modal.
    if (form) {
        form.addEventListener('submit', function (e) {
            if (form.getAttribute('action') === '#') {
                e.preventDefault();
                closeModal();
            }
        });
    }
});
 