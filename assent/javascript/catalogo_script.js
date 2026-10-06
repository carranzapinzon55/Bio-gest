const CLAVE_LOCAL_STORAGE = 'carrito_biogest';

document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
    actualizarContadorHeader();
});

async function cargarProductos() {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    try {
        const respuesta = await fetch('../api/obtener_productos.php');
        const datos = await respuesta.json();

        // Extraer los productos si vienen dentro de un objeto { success: true, productos: [...] } o array directo
        let productos = [];
        if (Array.isArray(datos)) {
            productos = datos;
        } else if (datos && Array.isArray(datos.productos)) {
            productos = datos.productos;
        } else if (datos && Array.isArray(datos.data)) {
            productos = datos.data;
        }

        if (productos.length > 0) {
            contenedor.innerHTML = ''; // Limpiar mensaje de carga

            productos.forEach(producto => {
                const id = producto.id_producto || producto.id;
                const nombre = producto.nombre_producto || producto.nombre || 'Producto';
                const precioNum = Number(producto.precio || 0);
                const precioFormateado = precioNum.toLocaleString('es-CO');
                const descripcion = producto.descripcion || 'Sin descripción disponible.';
                
                // Si no hay imagen en BD o falla la carga, usa Logo.jpeg por defecto
                const imagenRuta = producto.imagen ? `../assent/img/${producto.imagen}` : '../assent/img/Logo.jpeg';

                // Crear la tarjeta manteniendo exactamente la estructura y clases que requiere catologo_style.css
                const tarjeta = document.createElement('div');
                tarjeta.classList.add('product-card');

                tarjeta.innerHTML = `
                    <div class="product-img product-image" onclick="verDetalle(${id})" style="cursor: pointer;">
                        <img src="${imagenRuta}" alt="${nombre}" onerror="this.src='../assent/img/Logo.jpeg';">
                    </div>
                    <div class="product-details product-info">
                        <h3 class="product-title" onclick="verDetalle(${id})" style="cursor: pointer;">${nombre}</h3>
                        <p class="description product-description">${descripcion}</p>
                        <div class="price product-price">$ ${precioFormateado} COP</div>
                        <button class="btn-add-cart" type="button">
                            <i class="fas fa-shopping-cart"></i> Añadir
                        </button>
                    </div>
                `;

                // Asignar el evento para guardar en el carrito de compras
                const btnAgregar = tarjeta.querySelector('.btn-add-cart');
                btnAgregar.addEventListener('click', (e) => {
                    e.stopPropagation(); // Evita redirigir a detalle al presionar el botón
                    agregarAlCarritoDesdeCatalogo({
                        id: id,
                        nombre: nombre,
                        precio: precioNum,
                        imagen: imagenRuta
                    });
                });

                contenedor.appendChild(tarjeta);
            });
        } else {
            contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">No hay productos disponibles en este momento.</p>';
        }
    } catch (error) {
        console.error('Error al cargar catálogo:', error);
        contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: red; padding: 40px;">Error al conectar con la base de datos.</p>';
    }
}

// Redirige a detalle_producto.html enviando el ID por la URL
function verDetalle(idProducto) {
    window.location.href = `detalle_producto.html?id=${idProducto}`;
}

// Guarda el producto seleccionado en localStorage
function agregarAlCarritoDesdeCatalogo(producto) {
    let carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];

    const index = carrito.findIndex(item => item.id === producto.id);

    if (index !== -1) {
        carrito[index].cantidad += 1;
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad: 1,
            imagen: producto.imagen
        });
    }

    localStorage.setItem(CLAVE_LOCAL_STORAGE, JSON.stringify(carrito));
    actualizarContadorHeader();
    alert(`¡${producto.nombre} agregado al carrito!`);
}

// Actualiza la cifra del icono del carrito en el header
function actualizarContadorHeader() {
    const carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const cartCount = document.getElementById('cart-count');
    if (cartCount) cartCount.textContent = totalItems;
}
// Al renderizar cada producto dentro de catalogo.js:
function renderizarProducto(producto) {
    // Si la imagen en la BD viene como 'assent/img/paquete.jpg', le agregamos '../'
    let rutaImagen = producto.imagen || '../assent/img/Logo.jpeg';
    
    if (!rutaImagen.startsWith('../') && !rutaImagen.startsWith('http')) {
        rutaImagen = '../' + rutaImagen;
    }

    return `
        <div class="card-producto">
            <img src="${rutaImagen}" alt="${producto.nombre}" onerror="this.src='../assent/img/Logo.jpeg'">
            <h3>${producto.nombre}</h3>
            <p class="precio">$ ${Number(producto.precio).toLocaleString('es-CO')} COP</p>
            <a href="detalle_producto.html?id=${producto.id_producto}" class="btn-ver">Ver producto</a>
        </div>
    `;
}