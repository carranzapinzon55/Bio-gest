document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Funcionalidad para mostrar / ocultar contraseña
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const confirmPasswordInput = document.getElementById("confirm-password");

    function setupToggle(iconElement, inputElement) {
        if (iconElement && inputElement) {
            iconElement.addEventListener("click", () => {
                const type = inputElement.getAttribute("type") === "password" ? "text" : "password";
                inputElement.setAttribute("type", type);
                
                iconElement.classList.toggle("fa-eye");
                iconElement.classList.toggle("fa-eye-slash");
            });
        }
    }

    setupToggle(togglePassword, passwordInput);
    setupToggle(toggleConfirmPassword, confirmPasswordInput);

    // 2. Validación unificada del formulario al enviar
    const registroForm = document.getElementById("registroForm") || document.getElementById("form-registro");

    if (registroForm && passwordInput && confirmPasswordInput) {
        registroForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Evita que la página se recargue automáticamente

            const passValue = passwordInput.value;
            const confirmPassValue = confirmPasswordInput.value;
            const nombre = document.getElementById("nombre")?.value || "";
            const email = document.getElementById("email")?.value || "";
            const rol = document.getElementById("role")?.value || "";
            const terminos = document.getElementById("terminos");

            // Validar longitud mínima de la contraseña
            if (passValue.length < 6) {
                alert("La contraseña debe tener al menos 6 caracteres.");
                passwordInput.focus();
                return;
            }

            // Validar que las contraseñas coincidan
            if (passValue !== confirmPassValue) {
                alert("¡Las contraseñas no coinciden! Por favor, revísalas.");
                confirmPasswordInput.focus();
                return;
            }

            // Validar términos y condiciones (si el elemento existe en el HTML)
            if (terminos && !terminos.checked) {
                alert("Debes aceptar los términos y condiciones para registrarte.");
                terminos.focus();
                return;
            }

            // Simulación de éxito en el registro
            console.log("Datos registrados:", { nombre, email, rol });
            alert(`¡Registro exitoso! Bienvenido a BIO-GEST, ${nombre}.`);
            
            // Redirigir al login después de registrarse con éxito
            window.location.href = "login.html";
        });
    }
});