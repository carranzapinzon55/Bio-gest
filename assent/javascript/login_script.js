document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('contrasena');
    const divMensaje = document.getElementById('mensaje');

    // Funcionalidad para mostrar/ocultar contraseña
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }

    // Envío del formulario vía fetch
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();

        const correo = document.getElementById('correo').value;
        const contrasena = document.getElementById('contrasena').value;

        try {
            // Nota: Se usa '../api/login.php' porque las vistas están dentro de una subcarpeta
            const respuesta = await fetch('../api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ correo, contrasena })
            });

            const datos = await respuesta.json();

            divMensaje.style.display = 'block';
            divMensaje.textContent = datos.message;
            divMensaje.style.backgroundColor = datos.success ? '#d4edda' : '#f8d7da';
            divMensaje.style.color = datos.success ? '#155724' : '#721c24';

            if (datos.success) {
                localStorage.setItem('usuario_nombre', datos.usuario.nombre);
                localStorage.setItem('usuario_rol', datos.usuario.rol);

                setTimeout(() => {
                    window.location.href = 'catalogo.html';
                }, 1500);
            }
        } catch (error) {
            console.error('Error:', error);
            divMensaje.style.display = 'block';
            divMensaje.textContent = 'Ocurrió un error al intentar iniciar sesión.';
            divMensaje.style.backgroundColor = '#f8d7da';
            divMensaje.style.color = '#721c24';
        }
    });
});
