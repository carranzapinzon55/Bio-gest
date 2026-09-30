document.addEventListener('DOMContentLoaded', () => {
    const registroForm = document.getElementById('registroForm');
    const divMensaje = document.getElementById('mensaje');

    // Alternar visibilidad de contraseñas
    const setupTogglePassword = (iconId, inputId) => {
        const icon = document.getElementById(iconId);
        const input = document.getElementById(inputId);
        if (icon && input) {
            icon.addEventListener('click', () => {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('fa-eye-slash');
            });
        }
    };

    setupTogglePassword('togglePassword', 'password');
    setupTogglePassword('toggleConfirmPassword', 'confirm-password');

    // Procesar formulario de registro
    registroForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.getElementById('nombre').value;
        const apellido = document.getElementById('apellido').value;
        const correo = document.getElementById('email').value;
        const telefono = document.getElementById('telefono').value;
        const contrasena = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Validar coincidencia de contraseñas
        if (contrasena !== confirmPassword) {
            divMensaje.style.display = 'block';
            divMensaje.textContent = 'Las contraseñas no coinciden.';
            divMensaje.style.backgroundColor = '#f8d7da';
            divMensaje.style.color = '#721c24';
            return;
        }

        try {
            // Se realiza la petición hacia '../api/registro.php'
            const respuesta = await fetch('../api/registro.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre, apellido, correo, telefono, contrasena })
            });

            const datos = await respuesta.json();

            divMensaje.style.display = 'block';
            divMensaje.textContent = datos.message;
            divMensaje.style.backgroundColor = datos.success ? '#d4edda' : '#f8d7da';
            divMensaje.style.color = datos.success ? '#155724' : '#721c24';

            if (datos.success) {
                registroForm.reset();
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            }
        } catch (error) {
            console.error('Error:', error);
            divMensaje.style.display = 'block';
            divMensaje.textContent = 'Ocurrió un error al procesar el registro.';
            divMensaje.style.backgroundColor = '#f8d7da';
            divMensaje.style.color = '#721c24';
        }
    });
});