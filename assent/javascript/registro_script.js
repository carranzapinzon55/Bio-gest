/* document.addEventListener('DOMContentLoaded', () => {
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
}); */

/**
 * ============================================================
 * SISTEMA DE GESTIÓN BIO-GEST · MÓDULO DE REGISTRO
 * ============================================================
 *
 * Módulo: Seguridad y Control de Acceso (HU-01)
 * Persistencia: localStorage (Modo Local / Sin Backend)
 * Proyecto Formativo: BIO-GEST (Tenebrios Gold)
 */

/* ============================================================
   1. CONFIGURACIÓN
   ============================================================ */
const CLAVE_USUARIOS = "sga_aprendices"; // Usamos la misma clave global de usuarios
const CLAVE_SIGUIENTE_ID = "sga_aprendices_siguiente_id";

/* ============================================================
   2. REPOSITORIO (Capa de datos con localStorage)
   ============================================================ */
const repositorio = {

    _leer() {
        try {
            const texto = localStorage.getItem(CLAVE_USUARIOS);
            const datos = texto ? JSON.parse(texto) : [];
            return Array.isArray(datos) ? datos : [];
        } catch (error) {
            throw new Error("No fue posible leer los usuarios registrados.");
        }
    },

    _guardar(lista) {
        try {
            localStorage.setItem(CLAVE_USUARIOS, JSON.stringify(lista));
        } catch (error) {
            throw new Error("El almacenamiento local está lleno o no disponible.");
        }
    },

    _siguienteId(lista) {
        const guardado = Number(localStorage.getItem(CLAVE_SIGUIENTE_ID)) || 0;
        const mayorActual = lista.reduce(
            function (mayor, u) { return Math.max(mayor, u.id || 0); }, 0
        );
        return Math.max(guardado, mayorActual) + 1;
    },

    _verificarDuplicados(lista, correo) {
        const correoLimpio = correo.trim().toLowerCase();
        const existe = lista.some(function (u) {
            return u.correo && u.correo.toLowerCase() === correoLimpio;
        });

        if (existe) {
            throw new Error("Ya existe una cuenta registrada con este correo electrónico.");
        }
    },

    async registrar(datos) {
        const lista = this._leer();

        // Validar que el correo no esté registrado previamente
        this._verificarDuplicados(lista, datos.email);

        const nuevoUsuario = {
            id: this._siguienteId(lista),
            nombre: datos.nombre.trim(),
            apellido: datos.apellido.trim(),
            correo: datos.email.trim().toLowerCase(),
            telefono: datos.telefono.trim(),
            password: datos.password, // En producción se aplicará hashing
            rol: "Cliente",          // Por defecto todo autoregistro es rol Cliente (HU-01, HU-02)
            estado: "Activo"
        };

        lista.push(nuevoUsuario);
        this._guardar(lista);

        try {
            localStorage.setItem(CLAVE_SIGUIENTE_ID, String(nuevoUsuario.id));
        } catch (e) {
            // Ignorar fallo secundario de ID
        }

        return nuevoUsuario;
    }
};

/* ============================================================
   3. REFERENCIAS AL DOM
   ============================================================ */
const registroForm           = document.getElementById("registroForm");
const inputNombre            = document.getElementById("nombre");
const inputApellido          = document.getElementById("apellido");
const inputEmail             = document.getElementById("email");
const inputTelefono          = document.getElementById("telefono");
const inputPassword          = document.getElementById("password");
const inputConfirmPassword   = document.getElementById("confirm-password");
const divMensaje             = document.getElementById("mensaje");

const togglePassword         = document.getElementById("togglePassword");
const toggleConfirmPassword  = document.getElementById("toggleConfirmPassword");

/* ============================================================
   4. REGLAS DE VALIDACIÓN
   ============================================================ */
const PATRON_LETRAS = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$/;

function validarFormulario() {
    const nombre = inputNombre.value.trim();
    const apellido = inputApellido.value.trim();
    const email = inputEmail.value.trim();
    const telefono = inputTelefono.value.trim();
    const password = inputPassword.value;
    const confirmPassword = inputConfirmPassword.value;

    if (!nombre || !PATRON_LETRAS.test(nombre)) {
        return { valido: false, mensaje: "Ingresa un nombre válido (solo letras)." };
    }

    if (!apellido || !PATRON_LETRAS.test(apellido)) {
        return { valido: false, mensaje: "Ingresa un apellido válido (solo letras)." };
    }

    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return { valido: false, mensaje: "Ingresa un correo electrónico válido." };
    }

    if (telefono && !/^\d{7,10}$/.test(telefono)) {
        return { valido: false, mensaje: "El teléfono debe contener entre 7 y 10 dígitos numéricos." };
    }

    if (!password || password.length < 6) {
        return { valido: false, mensaje: "La contraseña debe tener al menos 6 caracteres." };
    }

    if (password !== confirmPassword) {
        return { valido: false, mensaje: "Las contraseñas no coinciden. Por favor verifica." };
    }

    return { valido: true };
}

/* ============================================================
   5. MANEJO DE EVENTOS Y REGISTRO
   ============================================================ */
if (registroForm) {
    registroForm.addEventListener("submit", async function (evento) {
        evento.preventDefault();
        ocultarMensaje();

        const validacion = validarFormulario();
        if (!validacion.valido) {
            mostrarMensaje(validacion.mensaje, "error");
            return;
        }

        const datosRegistro = {
            nombre: inputNombre.value,
            apellido: inputApellido.value,
            email: inputEmail.value,
            telefono: inputTelefono.value,
            password: inputPassword.value
        };

        try {
            await repositorio.registrar(datosRegistro);

            mostrarMensaje("¡Cuenta creada exitosamente! Redirigiendo al inicio de sesión...", "exito");
            registroForm.reset();

            // Redirección al login después de 2 segundos (HU-01)
            setTimeout(function () {
                window.location.href = "login.html";
            }, 2000);

        } catch (error) {
            mostrarMensaje(error.message || "Ocurrió un error al crear la cuenta.", "error");
        }
    });
}

/* Mostrar/Ocultar contraseña */
function alternarVisibilidadPassword(input, icono) {
    if (input.type === "password") {
        input.type = "text";
        icono.classList.remove("fa-eye");
        icono.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icono.classList.remove("fa-eye-slash");
        icono.classList.add("fa-eye");
    }
}

if (togglePassword && inputPassword) {
    togglePassword.addEventListener("click", function () {
        alternarVisibilidadPassword(inputPassword, togglePassword);
    });
}

if (toggleConfirmPassword && inputConfirmPassword) {
    toggleConfirmPassword.addEventListener("click", function () {
        alternarVisibilidadPassword(inputConfirmPassword, toggleConfirmPassword);
    });
}

/* ============================================================
   6. MENSAJES VISUALES
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