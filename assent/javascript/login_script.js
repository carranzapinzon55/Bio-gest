/* document.addEventListener('DOMContentLoaded', () => {
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
 */

/**
 * ============================================================
 * SISTEMA DE GESTIÓN BIO-GEST · MÓDULO DE INICIO DE SESIÓN
 * ============================================================
 *
 * Módulo: Seguridad y Autenticación (HU-01, HU-02)
 * Persistencia: localStorage (Modo sin Backend)
 * Proyecto Formativo: BIO-GEST (Tenebrios Gold)
 */

/* ============================================================
   1. CONFIGURACIÓN
   ============================================================ */
const CLAVE_USUARIOS = "sga_aprendices"; // Misma base de datos de usuarios
const CLAVE_SESION_ACTIVA = "biogest_usuario_sesion";

/* ============================================================
   2. REPOSITORIO DE AUTENTICACIÓN
   ============================================================ */
const repositorioAuth = {

    /** Lee la lista completa de usuarios desde localStorage */
    _leerUsuarios() {
        try {
            const texto = localStorage.getItem(CLAVE_USUARIOS);
            const datos = texto ? JSON.parse(texto) : [];
            return Array.isArray(datos) ? datos : [];
        } catch (error) {
            throw new Error("No fue posible leer los registros de usuarios.");
        }
    },

    /** Valida credenciales e inicia sesión */
    async autenticar(correo, password) {
        const usuarios = this._leerUsuarios();
        const correoLimpio = correo.trim().toLowerCase();

        // Buscar el usuario por correo electrónico
        const usuarioEncontrado = usuarios.find(function (u) {
            return u.correo && u.correo.toLowerCase() === correoLimpio;
        });

        // Validaciones (HU-01: Mensaje de error si credenciales son incorrectas)
        if (!usuarioEncontrado) {
            throw new Error("El correo electrónico no está registrado.");
        }

        if (usuarioEncontrado.password !== password) {
            throw new Error("Contraseña incorrecta. Por favor intenta de nuevo.");
        }

        if (usuarioEncontrado.estado === "Inactivo") {
            throw new Error("Tu cuenta está inactiva. Contacta al administrador.");
        }

        // Guardar sesión activa en localStorage
        const sesionData = {
            id: usuarioEncontrado.id,
            nombre: usuarioEncontrado.nombre,
            apellido: usuarioEncontrado.apellido,
            correo: usuarioEncontrado.correo,
            rol: usuarioEncontrado.rol || "Cliente", // Por defecto Cliente
            fechaInicio: new Date().toISOString()
        };

        localStorage.setItem(CLAVE_SESION_ACTIVA, JSON.stringify(sesionData));

        return sesionData;
    }
};

/* ============================================================
   3. REFERENCIAS AL DOM
   ============================================================ */
const formLogin      = document.getElementById("form-login");
const inputCorreo    = document.getElementById("correo");
const inputPassword  = document.getElementById("contrasena");
const togglePassword = document.getElementById("togglePassword");
const divMensaje     = document.getElementById("mensaje");

/* ============================================================
   4. EVENTOS Y LÓGICA DE INTERFAZ
   ============================================================ */

if (formLogin) {
    formLogin.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        ocultarMensaje();

        const correo = inputCorreo.value.trim();
        const password = inputPassword.value;

        // Validaciones básicas de campos
        if (!correo || !password) {
            mostrarMensaje("Por favor completa todos los campos.", "error");
            return;
        }

        try {
            // Autenticación asíncrona
            const usuarioSesion = await repositorioAuth.autenticar(correo, password);

            mostrarMensaje(`¡Bienvenido(a) ${usuarioSesion.nombre}! Redirigiendo...`, "exito");

            // HU-02: Redirección según el rol asignado
            setTimeout(function () {
                if (usuarioSesion.rol === "Administrador") {
                    window.location.href = "../vistas/admin_dashboard.html"; // Vista de Administrador
                } else {
                    window.location.href = "../vistas/catalogo.html"; // Vista de Cliente
                }
            }, 1500);

        } catch (error) {
            mostrarMensaje(error.message, "error");
        }
    });
}

/* Mostrar / Ocultar Contraseña */
if (togglePassword && inputPassword) {
    togglePassword.addEventListener("click", function () {
        if (inputPassword.type === "password") {
            inputPassword.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            inputPassword.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    });
}

/* ============================================================
   5. MENSAJES VISUALES
   ============================================================ */
function mostrarMensaje(texto, tipo) {
    if (!divMensaje) return;
    divMensaje.textContent = texto;
    divMensaje.style.display = "block";

    if (tipo === "exito") {
        divMensaje.style.backgroundColor = "#d4edda";
        divMensaje.style.color = "#155724";
        divMensaje.style.border = "1px solid #c3e6cb";
    } else {
        divMensaje.style.backgroundColor = "#f8d7da";
        divMensaje.style.color = "#721c24";
        divMensaje.style.border = "1px solid #f5c6cb";
    }
}

function ocultarMensaje() {
    if (!divMensaje) return;
    divMensaje.style.display = "none";
    divMensaje.textContent = "";
}