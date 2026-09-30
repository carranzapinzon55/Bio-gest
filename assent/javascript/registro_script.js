document.addEventListener('DOMContentLoaded', () => {

    // --- 1. MOSTRAR / OCULTAR CONTRASEÑA (OJITO) ---
    const setupTogglePassword = (iconId, inputId) => {
        const icon = document.getElementById(iconId);
        const input = document.getElementById(inputId);

        if (icon && input) {
            icon.addEventListener('click', () => {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                
                // Alternar icono de FontAwesome si se usa
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    };

    // Activar ver/ocultar contraseña para ambos campos
    setupTogglePassword('togglePassword', 'password');
    setupTogglePassword('toggleConfirmPassword', 'confirm-password');


    // --- 2. VALIDACIÓN DEL FORMULARIO DE REGISTRO ---
    const formRegistro = document.getElementById('form-registro') || document.getElementById('registroForm');

    if (formRegistro) {
        formRegistro.addEventListener('submit', (e) => {
            const passInput = document.getElementById('password');
            const confirmPassInput = document.getElementById('confirm-password');
            const terminosInput = document.getElementById('terminos');

            const pass = passInput ? passInput.value : '';
            const confirmPass = confirmPassInput ? confirmPassInput.value : '';

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
            if (terminosInput && !terminosInput.checked) {
                e.preventDefault();
                alert('Debes aceptar los términos y condiciones para registrarte.');
            }
        });
    }
});