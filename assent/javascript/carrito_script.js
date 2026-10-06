const COSTO_ENVIO = 6000;
const CLAVE_LOCAL_STORAGE = 'carrito_biogest';

document.addEventListener('DOMContentLoaded', () => {
    renderizarCarrito();
});

// Obtener carrito guardado en localStorage
function obtenerCarrito() {
    return JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
}

// Guardar carrito actualizado en localStorage
function guardarCarrito(carrito) {
    localStorage.setItem(CLAVE_LOCAL_STORAGE, JSON.stringify(carrito));
}

// Renderizar la lista de productos dinámicamente
function renderizarCarrito() {
    const contenedor = document.getElementById('lista-productos-carrito');
    const carrito = obtenerCarrito();

    if (!contenedor) return;

    if (carrito.length === 0) {
        contenedor.innerHTML = `
            <div style="text-align: center; padding: 40px 20px; color: #666;">
                <p style="font-size: 18px; margin-bottom: 15px;">Tu carrito está vacío.</p>
                <a href="catalogo.html" style="color: #2e7d32; text-decoration: underline; font-weight: bold;">Explorar el catálogo</a>
            </div>
        `;
        recalcularTotales([]);
        return;
    }

    contenedor.innerHTML = ''; // Limpiar contenido previo

    carrito.forEach((prod, index) => {
        const subtotal = prod.precio * prod.cantidad;
        const imagenRuta = prod.imagen ? prod.imagen : '../assent/img/Logo.jpeg';

        const fila = document.createElement('div');
        fila.classList.add('fila-producto');

        fila.innerHTML = `
            <div class="col-prod info-prod">
                <img src="${imagenRuta}" alt="${prod.nombre}" class="prod-thumb" onerror="this.src='../assent/img/Logo.jpeg';">
                <div>
                    <span class="prod-nombre">${prod.nombre}</span>
                    ${prod.presentacion ? `<br><small style="color:#777;">${prod.presentacion}</small>` : ''}
                </div>
            </div>
            <div class="col-precio">$ ${Number(prod.precio).toLocaleString('es-CO')}</div>
            <div class="col-cant">
                <div class="selector-cantidad">
                    <button type="button" class="btn-cant" onclick="actualizarCantidad(${index}, -1)">-</button>
                    <input type="number" class="input-cant" value="${prod.cantidad}" readonly>
                    <button type="button" class="btn-cant" onclick="actualizarCantidad(${index}, 1)">+</button>
                </div>
            </div>
            <div class="col-sub subtotal-item">$ ${Number(subtotal).toLocaleString('es-CO')}</div>
            <div class="col-accion">
                <button class="btn-eliminar" onclick="eliminarProducto(${index})" title="Eliminar producto">🗑️</button>
            </div>
        `;

        contenedor.appendChild(fila);
    });

    recalcularTotales(carrito);
}

// Actualizar cantidad (+ / -) de un producto por su índice
function actualizarCantidad(index, cambio) {
    let carrito = obtenerCarrito();

    if (carrito[index]) {
        carrito[index].cantidad += cambio;

        if (carrito[index].cantidad < 1) {
            carrito[index].cantidad = 1;
        }

        guardarCarrito(carrito);
        renderizarCarrito();
    }
}

// Eliminar un producto del carrito
function eliminarProducto(index) {
    let carrito = obtenerCarrito();
    
    // Eliminar el producto seleccionado
    carrito.splice(index, 1);

    guardarCarrito(carrito);
    renderizarCarrito();
}

// Recalcular subtotal, envío, total y contador del header
function recalcularTotales(carrito) {
    let subtotalGeneral = 0;
    let totalItems = 0;

    carrito.forEach(item => {
        subtotalGeneral += item.precio * item.cantidad;
        totalItems += item.cantidad;
    });

    const envio = subtotalGeneral > 0 ? COSTO_ENVIO : 0;
    const totalGeneral = subtotalGeneral > 0 ? subtotalGeneral + envio : 0;

    // Actualizar elementos en la interfaz
    const elemSubtotal = document.getElementById('resumen-subtotal');
    const elemEnvio = document.getElementById('resumen-envio');
    const elemTotal = document.getElementById('resumen-total');
    const elemCount = document.getElementById('cart-count');

    if (elemSubtotal) elemSubtotal.textContent = `$ ${subtotalGeneral.toLocaleString('es-CO')}`;
    if (elemEnvio) elemEnvio.textContent = `$ ${envio.toLocaleString('es-CO')}`;
    if (elemTotal) elemTotal.textContent = `$ ${totalGeneral.toLocaleString('es-CO')}`;
    if (elemCount) elemCount.textContent = totalItems;
}

// Función para el botón "Finalizar compra"
function procesarCompra() {
    const carrito = obtenerCarrito();
    if (carrito.length === 0) {
        alert('Tu carrito está vacío. Agrega productos antes de continuar.');
        return;
    }
    // Redirige a la interfaz de pago/checkout
    window.location.href = 'finalizar_compra.html';
}