document.addEventListener('DOMContentLoaded', () => {
    const formRegistro = document.getElementById('form-registro');

    if (formRegistro) {
        formRegistro.addEventListener('submit', (e) => {
            const pass = document.getElementById('password').value;
            const confirmPass = document.getElementById('confirm-password').value;
            const terminos = document.getElementById('terminos');

            // Validar coincidencia de contraseñas
            if (pass !== confirmPass) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor verifícalas.');
                return;
            }

            // Validar longitud mínima
            if (pass.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }

            // Validar términos y condiciones
            if (terminos && !terminos.checked) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones para registrarte.');
            }
        });
    }
});