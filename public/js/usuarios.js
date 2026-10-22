document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('form-usuario');
    if (!form) return;

    var titulo   = document.getElementById('form-titulo');
    var etiqueta = document.getElementById('form-etiqueta');
    var btnGuardar = document.getElementById('btn-guardar');

    var campoUsuario = document.getElementById('u-usuario');
    var campoNombre  = document.getElementById('u-nombre');
    var campoCorreo  = document.getElementById('u-correo');
    var campoRol     = document.getElementById('u-rol');
    var campoEstado  = document.getElementById('u-estado');
    var campoPass    = document.getElementById('u-pass');
    var ayudaPass    = document.getElementById('u-pass-ayuda');

    var accionCrear = form.getAttribute('action');          // usuarios.store
    var metodo   = document.getElementById('u-metodo');   // _method=PUT solo en modo edición
    var filas    = document.querySelectorAll('[data-fila-usuario]');

    /* ---------- Modo "Nuevo usuario" ---------- */
    function modoCrear() {
        titulo.textContent = 'Nuevo usuario';
        etiqueta.textContent = 'Alta RBAC';
        btnGuardar.textContent = 'Guardar usuario';

        campoUsuario.readOnly = false;
        campoPass.required = true;
        campoPass.placeholder = '••••••••••••';
        ayudaPass.textContent = 'El usuario deberá actualizarla en su primer inicio de sesión.';
        metodo.disabled = true;
        form.setAttribute('action', accionCrear);

        filas.forEach(function (fila) { fila.classList.remove('is-editing'); });
        form.classList.remove('was-validated');
    }

    /* ---------- Modo "Editar usuario": carga la fila en el formulario ---------- */
    function modoEditar(datos, fila) {
        // No se usa form.reset(): dispararía el evento 'reset' y volvería a modo "Nuevo usuario"
        titulo.textContent = 'Editar usuario';
        etiqueta.textContent = 'Edición RBAC';
        btnGuardar.textContent = 'Guardar cambios';

        campoUsuario.value = datos.usuario;
        campoUsuario.readOnly = true;                     // el nombre de usuario no se cambia
        campoNombre.value = datos.nombre;
        campoCorreo.value = datos.correo;
        campoRol.value = datos.rol;
        campoEstado.value = datos.estado;

        campoPass.value = '';
        campoPass.required = false;
        campoPass.placeholder = 'Dejar vacío para no cambiarla';
        ayudaPass.textContent = 'Solo escriba una contraseña si desea reemplazar la actual.';
        metodo.disabled = false;
        form.setAttribute('action', datos.url);           // usuarios.update/{id}

        filas.forEach(function (f) { f.classList.toggle('is-editing', f === fila); });
        form.classList.remove('was-validated');
        campoNombre.focus();
    }

    document.querySelectorAll('[data-editar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modoEditar({
                url:     btn.getAttribute('data-url'),
                usuario: btn.getAttribute('data-usuario'),
                nombre:  btn.getAttribute('data-nombre'),
                correo:  btn.getAttribute('data-correo'),
                rol:     btn.getAttribute('data-rol'),
                estado:  btn.getAttribute('data-estado')
            }, btn.closest('[data-fila-usuario]'));
        });
    });

    // "Cancelar" limpia el formulario y vuelve al modo "Nuevo usuario"
    form.addEventListener('reset', function () {
        setTimeout(modoCrear, 0);
    });

    /* ---------- Mostrar / ocultar contraseña ---------- */
    var verPass = document.getElementById('u-pass-ver');
    if (verPass) {
        verPass.addEventListener('click', function () {
            var visible = campoPass.type === 'text';
            campoPass.type = visible ? 'password' : 'text';
            verPass.title = visible ? 'Mostrar contraseña' : 'Ocultar contraseña';
        });
    }

    /* ---------- Envío ---------- */
    // Marca en rojo los campos inválidos después del primer intento de envío
    btnGuardar.addEventListener('click', function () { form.classList.add('was-validated'); });

    // Mientras no exista la ruta usuarios.store el formulario apunta a "#" y no envía nada (prototipo).
    form.addEventListener('submit', function (e) {
        if (form.getAttribute('action') === '#') e.preventDefault();
    });

    modoCrear();
});
 