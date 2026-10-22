document.addEventListener('DOMContentLoaded', function () {
    var emailInput    = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var toggleBtn     = document.getElementById('toggle-password');
    var roleBadges    = document.querySelectorAll('.role-badge');

    /* ---------- Mostrar / ocultar contraseña ---------- */
    if (toggleBtn && passwordInput) {
        var eye    = toggleBtn.querySelector('.icon--eye');
        var eyeOff = toggleBtn.querySelector('.icon--eye-off');

        toggleBtn.addEventListener('click', function () {
            var show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            eye.classList.toggle('is-hidden', show);
            eyeOff.classList.toggle('is-hidden', !show);
            toggleBtn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    }

    /* ---------- Acceso rápido por rol ---------- */
    function setActiveRole(email) {
        roleBadges.forEach(function (badge) {
            badge.classList.toggle('is-active', badge.dataset.email === email);
        });
    }

    roleBadges.forEach(function (badge) {
        badge.addEventListener('click', function () {
            emailInput.value = badge.dataset.email;
            setActiveRole(badge.dataset.email);
            passwordInput.focus();
        });
    });

    if (emailInput) {
        emailInput.addEventListener('input', function () {
            setActiveRole(emailInput.value.trim());
        });
        setActiveRole(emailInput.value.trim());
    }
});
