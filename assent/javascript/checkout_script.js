document.addEventListener('DOMContentLoaded', () => {
    const btnContinuar = document.getElementById('btn-continuar-pago');
    const formCheckout = document.getElementById('form-checkout');

    if (btnContinuar && formCheckout) {
        btnContinuar.addEventListener('click', () => {
            const inputs = formCheckout.querySelectorAll('input[required]');
            let formularioValido = true;

            // Validar que todos los campos requeridos estén diligenciados
            inputs.forEach(input => {
                if (input.value.trim() === '') {
                    input.classList.add('error');
                    formularioValido = false;
                } else {
                    input.classList.remove('error');
                }
            });

            // Validar correo con expresión regular
            const emailInput = document.getElementById('email');
            if (emailInput && emailInput.value.trim() !== '') {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(emailInput.value.trim())) {
                    emailInput.classList.add('error');
                    formularioValido = false;
                    alert('Por favor, ingresa un correo electrónico válido.');
                    return;
                }
            }

            if (!formularioValido) {
                alert('Por favor, completa todos los campos obligatorios del formulario de envío.');
                return;
            }

            // Simulación de navegación al paso 2: Pago
            alert('Información de envío guardada correctamente. Redirigiendo al paso de pago...');
            // window.location.href = 'pago.html'; // Redirección real al crear la siguiente vista
        });

        // Limpiar estilos de error mientras el usuario escribe
        formCheckout.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => {
                if (input.value.trim() !== '') {
                    input.classList.remove('error');
                }
            });
        });
    }
});