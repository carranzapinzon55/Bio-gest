/*
document.addEventListener('DOMContentLoaded', () => {
    // 1. Cierre de sesión centralizado
    const btnCerrarSesion = document.getElementById('btn-cerrar-sesion');
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Deseas cerrar tu sesión?')) {
                fetch('../api/logout.php')
                    .then(() => window.location.href = '../vistas/login.html');
            }
        });
    }

    // 2. Lógica para mi_cuenta.html (Dashboard de bienvenida)
    if (document.querySelector('.grid-resumen-cuenta')) {
        cargarResumenCuenta();
    }

    // 3. Lógica para editar_perfil.html (Mis Datos)
    const formDatos = document.getElementById('form-mis-datos');
    if (formDatos) {
        cargarDatosUsuario();
        formDatos.addEventListener('submit', guardarDatosUsuario);
    }
// ---4 DIRECCIONES ---
function toggleFormDireccion() {
    const cardForm = document.getElementById('card-nueva-direccion');
    if (cardForm) cardForm.classList.toggle('oculto');
}

function cargarDirecciones() {
    const grid = document.querySelector('.grid-direcciones');
    if (!grid) return;

    fetch('../api/direcciones.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                grid.innerHTML = `<p style="grid-column:1/-1; color: #d9534f; font-weight: bold; text-align: center;">${data.message}</p>`;
                return;
            }
            
            grid.innerHTML = '';

            if (data.direcciones.length === 0) {
                grid.innerHTML = '<p style="grid-column:1/-1; text-align: center;">No tienes direcciones guardadas.</p>';
                return;
            }

            data.direcciones.forEach(dir => {
                const idDir = dir.id_direccion || dir.id;
                const esPrincipal = dir.es_principal == 1;

                const card = document.createElement('div');
                card.className = `card-direccion ${esPrincipal ? 'principal' : ''}`;
                card.innerHTML = `
                    ${esPrincipal ? '<span class="badge-principal">Principal</span>' : ''}
                    <h3>${dir.nombre_ubicacion || 'Dirección'}</h3>
                    <p>${dir.direccion}</p>
                    <p>${dir.ciudad}, ${dir.departamento}</p>
                    <div class="acciones-card">
                        ${!esPrincipal ? `<button type="button" class="btn-link" onclick="marcarPrincipal(${idDir})">Establecer como principal</button>` : ''}
                        <button type="button" class="btn-link peligro" onclick="eliminarDireccion(${idDir})">Eliminar</button>
                    </div>
                `;
                grid.appendChild(card);
            });
        })
        .catch(err => {
            grid.innerHTML = '<p style="grid-column:1/-1; color: red; text-align: center;">Error al conectar con la base de datos.</p>';
            console.error("Error en fetch:", err);
        });
}

function guardarDireccion(e) {
    e.preventDefault();
    const payload = {
        nombre_ubicacion: document.getElementById('dir-nombre').value,
        direccion: document.getElementById('dir-calle').value,
        ciudad: document.getElementById('dir-ciudad').value,
        departamento: document.getElementById('dir-depto').value
    };

    fetch('../api/direcciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            toggleFormDireccion();
            document.getElementById('form-direccion').reset();
            cargarDirecciones();
        }
    });
}

function marcarPrincipal(id_direccion) {
    if (!id_direccion) return;
    fetch('../api/direcciones.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_direccion: id_direccion })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) cargarDirecciones();
    });
}

function eliminarDireccion(id_direccion) {
    if (!id_direccion) return;
    if (!confirm('¿Deseas eliminar esta dirección?')) return;
    
    fetch('../api/direcciones.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_direccion: id_direccion })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) cargarDirecciones();
    });
}

    // 5. Lógica para mis_pedidos.html
    if (document.querySelector('.tabla-pedidos')) {
        cargarMisPedidos();
    }
});

// --- DERECHOS / PERFIL ---
function cargarResumenCuenta() {
    fetch('../api/obtener_perfil.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const titulo = document.querySelector('.titulo-seccion');
                if (titulo) titulo.textContent = `Hola, ${data.usuario.nombre} ${data.usuario.apellido}`;

                // Actualizar tarjeta de dirección principal si existe
                if (data.direccion_principal) {
                    const cardDir = document.querySelectorAll('.card-resumen-dashboard')[1];
                    if (cardDir) {
                        cardDir.querySelector('.dato-destacado').textContent = data.direccion_principal.nombre_ubicacion || 'Casa';
                        cardDir.querySelector('.dato-secundario').textContent = `${data.direccion_principal.direccion} (${data.direccion_principal.ciudad})`;
                    }
                }

                // Actualizar tarjeta de último pedido si existe
                if (data.ultimo_pedido) {
                    const cardPed = document.querySelectorAll('.card-resumen-dashboard')[0];
                    if (cardPed) {
                        const p = data.ultimo_pedido;
                        cardPed.querySelector('.dato-destacado').textContent = `#${p.numero_pedido || 'TG' + p.id_pedido} - ${p.estado_pedido}`;
                        cardPed.querySelector('.dato-secundario').textContent = `Total: $ ${parseFloat(p.total).toLocaleString('es-CO')} (${p.fecha_pedido})`;
                    }
                }
            } else {
                window.location.href = '../vistas/login.html';
            }
        });
}

function cargarDatosUsuario() {
    fetch('../api/obtener_perfil.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('nombre').value = data.usuario.nombre || '';
                document.getElementById('apellido').value = data.usuario.apellido || '';
                document.getElementById('email').value = data.usuario.correo || '';
                document.getElementById('telefono').value = data.usuario.telefono || '';
            }
        });
}

function guardarDatosUsuario(e) {
    e.preventDefault();
    const passNueva = document.getElementById('pass-nueva').value;
    const passConfirm = document.getElementById('pass-confirm').value;

    if (passNueva !== '' && passNueva !== passConfirm) {
        alert('Las contraseñas nuevas no coinciden.');
        return;
    }

    const payload = {
        nombre: document.getElementById('nombre').value,
        apellido: document.getElementById('apellido').value,
        correo: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        pass_actual: document.getElementById('pass-actual').value,
        pass_nueva: passNueva
    };

    fetch('../api/actualizar_perfil.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById('pass-actual').value = '';
            document.getElementById('pass-nueva').value = '';
            document.getElementById('pass-confirm').value = '';
        }
    });
}

// --- DIRECCIONES ---
function toggleFormDireccion() {
    const cardForm = document.getElementById('card-nueva-direccion');
    if (cardForm) cardForm.classList.toggle('oculto');
}

function cargarDirecciones() {
    fetch('../api/direcciones.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            const grid = document.querySelector('.grid-direcciones');
            grid.innerHTML = '';

            if (data.direcciones.length === 0) {
                grid.innerHTML = '<p>No tienes direcciones guardadas.</p>';
                return;
            }

            data.direcciones.forEach(dir => {
                const esPrincipal = dir.es_principal == 1;
                const card = document.createElement('div');
                card.className = `card-direccion ${esPrincipal ? 'principal' : ''}`;
                card.innerHTML = `
                    ${esPrincipal ? '<span class="badge-principal">Principal</span>' : ''}
                    <h3>${dir.nombre_ubicacion || 'Dirección'}</h3>
                    <p>${dir.direccion}</p>
                    <p>${dir.ciudad}, ${dir.departamento}</p>
                    <div class="acciones-card">
                        ${!esPrincipal ? `<button class="btn-link" onclick="marcarPrincipal(${dir.id_direccion})">Establecer como principal</button>` : ''}
                        <button class="btn-link peligro" onclick="eliminarDireccion(${dir.id_direccion})">Eliminar</button>
                    </div>
                `;
                grid.appendChild(card);
            });
        });
}

function guardarDireccion(e) {
    e.preventDefault();
    const payload = {
        nombre_ubicacion: document.getElementById('dir-nombre').value,
        direccion: document.getElementById('dir-calle').value,
        ciudad: document.getElementById('dir-ciudad').value,
        departamento: document.getElementById('dir-depto').value
    };

    fetch('../api/direcciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            toggleFormDireccion();
            document.getElementById('form-direccion').reset();
            cargarDirecciones();
        }
    });
}

function marcarPrincipal(id_direccion) {
    fetch('../api/direcciones.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_direccion })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) cargarDirecciones();
        else alert(data.message);
    });
}

function eliminarDireccion(id_direccion) {
    if (!confirm('¿Deseas eliminar esta dirección?')) return;
    fetch('../api/direcciones.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_direccion })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) cargarDirecciones();
        else alert(data.message);
    });
}

// --- PEDIDOS ---
function cargarMisPedidos() {
    fetch('../api/mis_pedidos.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            const tbody = document.querySelector('.tabla-pedidos tbody');
            tbody.innerHTML = '';

            if (data.pedidos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">No has realizado pedidos aún.</td></tr>';
                return;
            }

            data.pedidos.forEach(p => {
                const tr = document.createElement('tr');
                const num = p.numero_pedido || ('#TG' + String(p.id_pedido).padStart(4, '0'));
                const claseEstado = (p.estado_pedido || 'Pendiente').toLowerCase().replace(' ', '');
                
                tr.innerHTML = `
                    <td class="id-pedido">${num}</td>
                    <td>${p.fecha_pedido}</td>
                    <td class="precio-total">$ ${parseFloat(p.total).toLocaleString('es-CO')}</td>
                    <td><span class="badge-estado ${claseEstado}">${p.estado_pedido}</span></td>
                    <td><button class="btn-detalle" onclick="verDetallePedido(${p.id_pedido})">Ver detalle</button></td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function verDetallePedido(id_pedido) {
    fetch(`../api/mis_pedidos.php?id_pedido=${id_pedido}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                return;
            }
            let msj = `Detalle del Pedido #${data.pedido.numero_pedido || data.pedido.id_pedido}\n`;
            msj += `Fecha: ${data.pedido.fecha_pedido}\n`;
            msj += `Estado: ${data.pedido.estado_pedido}\n\nProductos:\n`;

            data.detalles.forEach(d => {
                msj += `- ${d.nombre_producto || 'Producto ' + d.id_producto}: ${d.cantidad}x ($${parseFloat(d.precio_unitario).toLocaleString('es-CO')}) = $${parseFloat(d.subtotal).toLocaleString('es-CO')}\n`;
            });

            msj += `\nTotal: $${parseFloat(data.pedido.total).toLocaleString('es-CO')}`;
            alert(msj);
        });
}
*/
/**
 * ============================================================
 * SISTEMA DE GESTIÓN BIO-GEST · MÓDULO MI CUENTA Y PERFIL
 * ============================================================
 * Persistencia: localStorage (Modo sin Backend / Local)
 */

const CLAVE_SESION = "biogest_usuario_sesion";
const CLAVE_USUARIOS = "sga_aprendices";
const CLAVE_DIRECCIONES = "biogest_direcciones_usuario";
const CLAVE_PEDIDOS = "biogest_pedidos_usuario";

function obtenerSesionActiva() {
    const sesion = localStorage.getItem(CLAVE_SESION);
    return sesion ? JSON.parse(sesion) : null;
}

document.addEventListener('DOMContentLoaded', () => {
    // 1. Cierre de sesión centralizado
    const btnCerrarSesion = document.getElementById('btn-cerrar-sesion');
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('¿Deseas cerrar tu sesión?')) {
                localStorage.removeItem(CLAVE_SESION);
                window.location.href = '../vistas/login.html';
            }
        });
    }

    // 2. Lógica para mi_cuenta.html (Dashboard de bienvenida)
    if (document.querySelector('.grid-resumen-cuenta')) {
        cargarResumenCuenta();
    }

    // 3. Lógica para editar_perfil.html (Mis Datos)
    const formDatos = document.getElementById('form-mis-datos');
    if (formDatos) {
        cargarDatosUsuario();
        formDatos.addEventListener('submit', guardarDatosUsuario);
    }

    // 4. Lógica para direcciones.html
    const formDir = document.getElementById('form-direccion');
    if (formDir) {
        cargarDirecciones();
        formDir.addEventListener('submit', guardarDireccion);
    } else if (document.querySelector('.grid-direcciones')) {
        cargarDirecciones();
    }

    // 5. Lógica para mis_pedidos.html
    if (document.querySelector('.tabla-pedidos')) {
        cargarMisPedidos();
    }
});

/* ============================================================
   RESUMEN DE CUENTA (mi_cuenta.html)
   ============================================================ */
function cargarResumenCuenta() {
    const sesion = obtenerSesionActiva();
    if (!sesion) {
        window.location.href = '../vistas/login.html';
        return;
    }

    const titulo = document.querySelector('.titulo-seccion');
    if (titulo) titulo.textContent = `Hola, ${sesion.nombre} ${sesion.apellido}`;

    // Actualizar tarjeta de dirección principal
    const todasDirs = JSON.parse(localStorage.getItem(CLAVE_DIRECCIONES)) || {};
    const dirsUsuario = todasDirs[sesion.id] || [];
    const dirPrincipal = dirsUsuario.find(d => d.es_principal) || dirsUsuario[0];

    if (dirPrincipal) {
        const cardDir = document.querySelectorAll('.card-resumen-dashboard')[1];
        if (cardDir) {
            const dest = cardDir.querySelector('.dato-destacado');
            const sec = cardDir.querySelector('.dato-secundario');
            if (dest) dest.textContent = dirPrincipal.nombre_ubicacion || 'Casa';
            if (sec) sec.textContent = `${dirPrincipal.direccion} (${dirPrincipal.ciudad})`;
        }
    }

    // Actualizar tarjeta de último pedido
    const todosPedidos = JSON.parse(localStorage.getItem(CLAVE_PEDIDOS)) || [];
    const pedidosUsuario = todosPedidos.filter(p => p.usuarioId === sesion.id);
    const ultimoPedido = pedidosUsuario[pedidosUsuario.length - 1];

    if (ultimoPedido) {
        const cardPed = document.querySelectorAll('.card-resumen-dashboard')[0];
        if (cardPed) {
            const dest = cardPed.querySelector('.dato-destacado');
            const sec = cardPed.querySelector('.dato-secundario');
            if (dest) dest.textContent = `${ultimoPedido.idPedido} - ${ultimoPedido.estado || 'Completado'}`;
            if (sec) sec.textContent = `Total: $ ${Number(ultimoPedido.total).toLocaleString('es-CO')} (${ultimoPedido.fecha})`;
        }
    }
}

/* ============================================================
   EDITAR PERFIL (editar_perfil.html)
   ============================================================ */
function cargarDatosUsuario() {
    const sesion = obtenerSesionActiva();
    if (!sesion) return;

    const usuarios = JSON.parse(localStorage.getItem(CLAVE_USUARIOS)) || [];
    const usuarioActual = usuarios.find(u => u.id === sesion.id) || sesion;

    if (document.getElementById('nombre')) document.getElementById('nombre').value = usuarioActual.nombre || '';
    if (document.getElementById('apellido')) document.getElementById('apellido').value = usuarioActual.apellido || '';
    if (document.getElementById('email')) document.getElementById('email').value = usuarioActual.correo || '';
    if (document.getElementById('telefono')) document.getElementById('telefono').value = usuarioActual.telefono || '';
}

function guardarDatosUsuario(e) {
    e.preventDefault();
    const sesion = obtenerSesionActiva();
    if (!sesion) return;

    const passActual = document.getElementById('pass-actual')?.value || '';
    const passNueva = document.getElementById('pass-nueva')?.value || '';
    const passConfirm = document.getElementById('pass-confirm')?.value || '';

    let usuarios = JSON.parse(localStorage.getItem(CLAVE_USUARIOS)) || [];
    const index = usuarios.findIndex(u => u.id === sesion.id);

    if (index === -1) {
        alert('Usuario no encontrado.');
        return;
    }

    if (passNueva !== '') {
        if (usuarios[index].password && usuarios[index].password !== passActual) {
            alert('La contraseña actual es incorrecta.');
            return;
        }
        if (passNueva !== passConfirm) {
            alert('Las contraseñas nuevas no coinciden.');
            return;
        }
        usuarios[index].password = passNueva;
    }

    const nuevoNombre = document.getElementById('nombre').value.trim();
    const nuevoApellido = document.getElementById('apellido').value.trim();
    const nuevoCorreo = document.getElementById('email').value.trim();
    const nuevoTelefono = document.getElementById('telefono').value.trim();

    usuarios[index].nombre = nuevoNombre;
    usuarios[index].apellido = nuevoApellido;
    usuarios[index].correo = nuevoCorreo;
    usuarios[index].telefono = nuevoTelefono;

    localStorage.setItem(CLAVE_USUARIOS, JSON.stringify(usuarios));

    // Actualizar sesión activa
    sesion.nombre = nuevoNombre;
    sesion.apellido = nuevoApellido;
    sesion.correo = nuevoCorreo;
    sesion.telefono = nuevoTelefono;
    localStorage.setItem(CLAVE_SESION, JSON.stringify(sesion));

    alert('¡Perfil actualizado correctamente!');
    if (document.getElementById('pass-actual')) document.getElementById('pass-actual').value = '';
    if (document.getElementById('pass-nueva')) document.getElementById('pass-nueva').value = '';
    if (document.getElementById('pass-confirm')) document.getElementById('pass-confirm').value = '';
}

/* ============================================================
   DIRECCIONES (direcciones.html)
   ============================================================ */
function toggleFormDireccion() {
    const cardForm = document.getElementById('card-nueva-direccion');
    if (cardForm) cardForm.classList.toggle('oculto');
}

function obtenerDireccionesUsuario() {
    const sesion = obtenerSesionActiva();
    if (!sesion) return [];
    const todas = JSON.parse(localStorage.getItem(CLAVE_DIRECCIONES)) || {};
    return todas[sesion.id] || [];
}

function guardarDireccionesUsuario(dirs) {
    const sesion = obtenerSesionActiva();
    if (!sesion) return;
    let todas = JSON.parse(localStorage.getItem(CLAVE_DIRECCIONES)) || {};
    todas[sesion.id] = dirs;
    localStorage.setItem(CLAVE_DIRECCIONES, JSON.stringify(todas));
}

function cargarDirecciones() {
    const grid = document.querySelector('.grid-direcciones');
    if (!grid) return;

    const direcciones = obtenerDireccionesUsuario();
    grid.innerHTML = '';

    if (direcciones.length === 0) {
        grid.innerHTML = '<p style="grid-column:1/-1; text-align: center;">No tienes direcciones guardadas.</p>';
        return;
    }

    direcciones.forEach((dir, index) => {
        const esPrincipal = dir.es_principal;
        const card = document.createElement('div');
        card.className = `card-direccion ${esPrincipal ? 'principal' : ''}`;
        card.innerHTML = `
            ${esPrincipal ? '<span class="badge-principal">Principal</span>' : ''}
            <h3>${dir.nombre_ubicacion || 'Dirección'}</h3>
            <p>${dir.direccion}</p>
            <p>${dir.ciudad}, ${dir.departamento}</p>
            <div class="acciones-card">
                ${!esPrincipal ? `<button type="button" class="btn-link" onclick="marcarPrincipal(${index})">Establecer como principal</button>` : ''}
                <button type="button" class="btn-link peligro" onclick="eliminarDireccion(${index})">Eliminar</button>
            </div>
        `;
        grid.appendChild(card);
    });
}

function guardarDireccion(e) {
    e.preventDefault();
    const nombre_ubicacion = document.getElementById('dir-nombre')?.value.trim() || 'Principal';
    const direccion = document.getElementById('dir-calle')?.value.trim();
    const ciudad = document.getElementById('dir-ciudad')?.value.trim();
    const departamento = document.getElementById('dir-depto')?.value.trim();

    if (!direccion || !ciudad) {
        alert('La dirección y la ciudad son obligatorias.');
        return;
    }

    let direcciones = obtenerDireccionesUsuario();
    const esPrincipal = direcciones.length === 0;

    direcciones.push({
        nombre_ubicacion,
        direccion,
        ciudad,
        departamento,
        es_principal: esPrincipal
    });

    guardarDireccionesUsuario(direcciones);
    alert('Dirección guardada exitosamente.');
    toggleFormDireccion();
    const formDir = document.getElementById('form-direccion');
    if (formDir) formDir.reset();
    cargarDirecciones();
}

function marcarPrincipal(index) {
    let direcciones = obtenerDireccionesUsuario();
    direcciones = direcciones.map((dir, i) => {
        dir.es_principal = (i === index);
        return dir;
    });
    guardarDireccionesUsuario(direcciones);
    cargarDirecciones();
}

function eliminarDireccion(index) {
    if (!confirm('¿Deseas eliminar esta dirección?')) return;
    let direcciones = obtenerDireccionesUsuario();
    direcciones.splice(index, 1);
    guardarDireccionesUsuario(direcciones);
    cargarDirecciones();
}

/* ============================================================
   MIS PEDIDOS (mis_pedidos.html)
   ============================================================ */
function cargarMisPedidos() {
    const tbody = document.querySelector('.tabla-pedidos tbody');
    if (!tbody) return;

    const sesion = obtenerSesionActiva();
    if (!sesion) return;

    const todosPedidos = JSON.parse(localStorage.getItem(CLAVE_PEDIDOS)) || [];
    const pedidosUsuario = todosPedidos.filter(p => p.usuarioId === sesion.id);

    tbody.innerHTML = '';

    if (pedidosUsuario.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No has realizado pedidos aún.</td></tr>';
        return;
    }

    pedidosUsuario.forEach((p, index) => {
        const tr = document.createElement('tr');
        const num = p.idPedido || (`#TG00${index + 1}`);
        const claseEstado = (p.estado || 'Completado').toLowerCase().replace(' ', '');

        tr.innerHTML = `
            <td class="id-pedido">${num}</td>
            <td>${p.fecha}</td>
            <td class="precio-total">$ ${parseFloat(p.total).toLocaleString('es-CO')}</td>
            <td><span class="badge-estado ${claseEstado}">${p.estado || 'Completado'}</span></td>
            <td><button type="button" class="btn-detalle" onclick="verDetallePedido('${p.idPedido}')">Ver detalle</button></td>
        `;
        tbody.appendChild(tr);
    });
}

function verDetallePedido(idPedido) {
    const todosPedidos = JSON.parse(localStorage.getItem(CLAVE_PEDIDOS)) || [];
    const pedido = todosPedidos.find(p => p.idPedido === idPedido);

    if (!pedido) {
        alert('No se encontró la información del pedido.');
        return;
    }

    let msj = `Detalle del Pedido #${pedido.idPedido}\n`;
    msj += `Fecha: ${pedido.fecha}\n`;
    msj += `Estado: ${pedido.estado || 'Completado'}\n\nProductos:\n`;

    pedido.items.forEach(d => {
        msj += `- ${d.nombre}: ${d.cantidad}x ($${parseFloat(d.precio).toLocaleString('es-CO')}) = $${parseFloat(d.precio * d.cantidad).toLocaleString('es-CO')}\n`;
    });

    msj += `\nTotal: $${parseFloat(pedido.total).toLocaleString('es-CO')}`;
    alert(msj);
}

// Exponer funciones globalmente para los eventos onclick en HTML
window.toggleFormDireccion = toggleFormDireccion;
window.marcarPrincipal = marcarPrincipal;
window.eliminarDireccion = eliminarDireccion;
window.verDetallePedido = verDetallePedido;
window.guardarDireccion = guardarDireccion;
window.guardarDatosUsuario = guardarDatosUsuario;
