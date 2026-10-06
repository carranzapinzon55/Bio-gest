(function () {
    function actualizarSesionGlobal() {
        // Detectar si el archivo está en la carpeta /vistas/
        const enVistas = window.location.pathname.includes('/vistas/');

        // Enrutamiento dinámico según el nivel de directorio
        const rutaApi = enVistas ? '../api/obtener_perfil.php' : 'api/obtener_perfil.php';
        const rutaMiCuenta = enVistas ? 'mi_cuenta.html' : 'vistas/mi_cuenta.html';
        const rutaLogin = enVistas ? 'login.html' : 'vistas/login.html';

        // Selectores de los elementos de usuario en Header y Footer
        const userLinks = document.querySelectorAll('.user-link, #header-user-link');
        const footerContainer = document.getElementById('footer-session-container');

        fetch(rutaApi)
            .then(res => {
                if (!res.ok) throw new Error("Error en la respuesta del servidor");
                return res.json();
            })
            .then(data => {
                if (data.success && data.usuario) {
                    const primerNombre = data.usuario.nombre.trim().split(' ')[0];

                    // Actualizar Header
                    userLinks.forEach(link => {
                        link.href = rutaMiCuenta;
                        link.innerHTML = `👤 Hola, ${primerNombre}`;
                    });

                    // Actualizar Footer (Reemplaza 'Iniciar sesión' por enlace a 'Mi cuenta')
                    if (footerContainer) {
                        footerContainer.innerHTML = `
                            <a href="${rutaMiCuenta}" class="footer-link-cuenta" style="color: #e6a100; text-decoration: none; font-weight: bold;">
                                👤 Mi cuenta (${primerNombre})
                            </a>
                        `;
                    }
                } else {
                    // Estado sin sesión activa
                    userLinks.forEach(link => {
                        link.href = rutaLogin;
                        link.innerHTML = `👤 Iniciar sesión`;
                    });

                    if (footerContainer) {
                        footerContainer.innerHTML = `
                            <a href="${rutaLogin}" class="footer-link-login" style="color: #e6a100; text-decoration: none;">
                                Iniciar sesión
                            </a>
                        `;
                    }
                }
            })
            .catch(err => {
                console.error("Error al consultar la sesión:", err);
                userLinks.forEach(link => {
                    link.href = rutaLogin;
                    link.innerHTML = `👤 Iniciar sesión`;
                });
                if (footerContainer) {
                    footerContainer.innerHTML = `
                        <a href="${rutaLogin}" class="footer-link-login" style="color: #e6a100; text-decoration: none;">
                            Iniciar sesión
                        </a>
                    `;
                }
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', actualizarSesionGlobal);
    } else {
        actualizarSesionGlobal();
    }
})();