document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Funcionalidad para mostrar / ocultar contraseña
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
    const confirmPasswordInput = document.getElementById("confirm-password");

    function setupToggle(iconElement, inputElement) {
        if (iconElement && inputElement) {
            iconElement.addEventListener("click", () => {
                // Cambiar el tipo de input entre password y text
                const type = inputElement.getAttribute("type") === "password" ? "text" : "password";
                inputElement.setAttribute("type", type);
                
                // Cambiar el icono del ojo (abierto / cerrado)
                iconElement.classList.toggle("fa-eye");
                iconElement.classList.toggle("fa-eye-slash");
            });
        }
    }

    setupToggle(togglePassword, passwordInput);
    setupToggle(toggleConfirmPassword, confirmPasswordInput);

    // 2. Validación del formulario al enviar
    const registroForm = document.getElementById("registroForm");

    if (registroForm) {
        registroForm.addEventListener("submit", (e) => {
            e.preventDefault(); // Evita que la página se recargue automáticamente

            const passValue = passwordInput.value;
            const confirmPassValue = confirmPasswordInput.value;
            const nombre = document.getElementById("nombre").value;
            const email = document.getElementById("email").value;
            const rol = document.getElementById("role").value;

            // Validar que las contraseñas coincidan
            if (passValue !== confirmPassValue) {
                alert("¡Las contraseñas no coinciden! Por favor, revísalas.");
                confirmPasswordInput.focus();
                return;
            }

            // Simulación de éxito en el registro (aquí conectarás tu lógica con la base de datos o backend más adelante)
            console.log("Datos registrados:", { nombre, email, rol });
            alert(`¡Registro exitoso! Bienvenido a BIO-GEST, ${nombre}.`);
            
            // Opcional: Redirigir al login después de registrarse con éxito
            window.location.href = "login.html";
        });
    }
});