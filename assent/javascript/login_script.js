document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('form-login');
    const inputPassword = document.getElementById('password');
    const btnTogglePass = document.getElementById('toggle-password');

    // Mostrar / Ocultar contraseña
    if (btnTogglePass && inputPassword) {
        btnTogglePass.addEventListener('click', () => {
            const esPassword = inputPassword.type === 'password';
            inputPassword.type = esPassword ? 'text' : 'password';
            btnTogglePass.textContent = esPassword ? '👁️‍🗨️' : '👁️';
        });
    }

    // Validación básica en JS antes de enviar
    if (formLogin) {
        formLogin.addEventListener('submit', (e) => {
            const email = document.getElementById('email').value.trim();
            const password = inputPassword.value.trim();

            if (email === '' || password === '') {
                e.preventDefault(); // Detiene el envío
                alert('Por favor, completa todos los campos.');
                return;
            }

            if (!validarEmail(email)) {
                e.preventDefault();
                alert('Por favor, ingresa un correo electrónico válido.');
            }
        });
    }
});

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}