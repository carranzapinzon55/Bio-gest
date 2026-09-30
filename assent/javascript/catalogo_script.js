document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
});

async function cargarProductos() {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    try {
        const respuesta = await fetch('../api/obtener_productos.php');
        const datos = await respuesta.json();

        if (datos.success && datos.productos.length > 0) {
            contenedor.innerHTML = ''; // Limpiar mensaje de carga

            datos.productos.forEach(producto => {
                const id = producto.id_producto || producto.id;
                const nombre = producto.nombre_producto || producto.nombre;
                const precio = Number(producto.precio).toLocaleString('es-CO');
                const descripcion = producto.descripcion || 'Sin descripción disponible.';
                
                // Si no hay imagen en BD o falla la carga, usa Logo.jpeg por defecto
                const imagenRuta = producto.imagen ? `../assent/img/${producto.imagen}` : '../assent/img/Logo.jpeg';

                // Crear la tarjeta con la estructura que espera catologo_style.css
                const tarjeta = document.createElement('div');
                tarjeta.classList.add('product-card');

                tarjeta.innerHTML = `
                    <div class="product-image" onclick="verDetalle(${id})" style="cursor: pointer;">
                        <img src="${imagenRuta}" alt="${nombre}" onerror="this.src='../assent/img/Logo.jpeg';">
                    </div>
                    <div class="product-info">
                        <h3 class="product-title" onclick="verDetalle(${id})" style="cursor: pointer;">${nombre}</h3>
                        <p class="product-description">${descripcion}</p>
                        <div class="product-price">$ ${precio} COP</div>
                        <button class="btn-add-cart" type="button" onclick="verDetalle(${id})">
                            <i class="fas fa-shopping-cart"></i> Añadir
                        </button>
                    </div>
                `;

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