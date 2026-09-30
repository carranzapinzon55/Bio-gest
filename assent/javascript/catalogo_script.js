// Variable global para almacenar los productos cargados de la base de datos
let todosLosProductos = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarProductosDesdeBD();
    configurarEventosFiltros();
});

// 1. Cargar productos desde la API de PHP
async function cargarProductosDesdeBD() {
    const contenedor = document.getElementById('grid-productos');
    if (!contenedor) return;

    try {
        const respuesta = await fetch('../api/get_productos.php');
        const resultado = await respuesta.json();

        if (resultado.success && resultado.data.length > 0) {
            todosLosProductos = resultado.data;
            renderizarProductos(todosLosProductos);
        } else {
            contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 40px;">No hay productos registrados en la base de datos.</p>';
        }
    } catch (error) {
        console.error('Error al conectar con la API de productos:', error);
        contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: red; padding: 40px;">Error al consultar la base de datos.</p>';
    }
}

// 2. Pintar las tarjetas en la grilla de productos
function renderizarProductos(productos) {
    const contenedor = document.getElementById('grid-productos');
    if (!contenedor) return;

    if (productos.length === 0) {
        contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #666; padding: 40px;">No se encontraron productos que coincidan con los filtros seleccionados.</p>';
        return;
    }

    contenedor.innerHTML = ''; // Limpiar contenedor

    productos.forEach(producto => {
        const precioFormateado = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            maximumFractionDigits: 0
        }).format(producto.precio);

        const tarjetaHTML = `
            <article class="product-card">
                <div class="product-img">
                    <img src="../assent/img/${producto.imagen || 'Logo.jpeg'}" 
                         alt="${producto.nombre_producto}" 
                         onerror="this.src='../assent/img/Logo.jpeg';">
                </div>
                <div class="product-details">
                    <h3>${producto.nombre_producto}</h3>
                    <p class="description">${producto.descripcion}</p>
                    <span class="price">${precioFormateado} COP</span>
                    <button class="btn-add-cart" onclick="agregarAlCarrito(${producto.id_producto})">
                        <i class="fas fa-cart-plus"></i> Añadir
                    </button>
                </div>
            </article>
        `;
        contenedor.innerHTML += tarjetaHTML;
    });
}

// 3. Escuchadores de eventos para los filtros
function configurarEventosFiltros() {
    // Buscador en tiempo real
    const inputBusqueda = document.getElementById('input-busqueda');
    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', filtrarYOrdenarProductos);
    }

    // Slider de precio máximo
    const rangePrecio = document.getElementById('price-range');
    if (rangePrecio) {
        rangePrecio.addEventListener('input', filtrarYOrdenarProductos);
    }

    // Selector de ordenamiento (Menor/Mayor precio)
    const selectOrden = document.getElementById('sort-select');
    if (selectOrden) {
        selectOrden.addEventListener('change', filtrarYOrdenarProductos);
    }

    // Checkboxes de categorías y botón de aplicar
    const btnAplicar = document.querySelector('.btn-apply-filters');
    if (btnAplicar) {
        btnAplicar.addEventListener('click', filtrarYOrdenarProductos);
    }

    const checkboxesCat = document.querySelectorAll('input[name="cat"]');
    checkboxesCat.forEach(chk => {
        chk.addEventListener('change', filtrarYOrdenarProductos);
    });
}

// 4. Lógica centralizada de filtrado y ordenamiento
function filtrarYOrdenarProductos() {
    if (!todosLosProductos.length) return;

    let resultados = [...todosLosProductos];

    // A. Filtrar por término del buscador
    const inputBusqueda = document.getElementById('input-busqueda');
    if (inputBusqueda && inputBusqueda.value.trim() !== '') {
        const busqueda = inputBusqueda.value.toLowerCase().trim();
        resultados = resultados.filter(p => 
            p.nombre_producto.toLowerCase().includes(busqueda) || 
            (p.descripcion && p.descripcion.toLowerCase().includes(busqueda))
        );
    }

    // B. Filtrar por categorías seleccionadas
    const checkboxesCat = document.querySelectorAll('input[name="cat"]:checked');
    const categoriasSeleccionadas = Array.from(checkboxesCat).map(chk => parseInt(chk.value));
    
    if (categoriasSeleccionadas.length > 0) {
        resultados = resultados.filter(p => 
            categoriasSeleccionadas.includes(parseInt(p.id_categoria))
        );
    } else {
        // Si desmarcan todas las categorías, no se muestran productos
        resultados = [];
    }

    // C. Filtrar por Precio Máximo (Range)
    const rangePrecio = document.getElementById('price-range');
    if (rangePrecio) {
        const precioMax = parseFloat(rangePrecio.value);
        resultados = resultados.filter(p => parseFloat(p.precio) <= precioMax);
    }

    // D. Ordenar por precio
    const selectOrden = document.getElementById('sort-select');
    if (selectOrden) {
        const opcion = selectOrden.value;
        if (opcion === 'low-high') {
            resultados.sort((a, b) => parseFloat(a.precio) - parseFloat(b.precio));
        } else if (opcion === 'high-low') {
            resultados.sort((a, b) => parseFloat(b.precio) - parseFloat(a.precio));
        }
    }

    // Renderizar los resultados procesados
    renderizarProductos(resultados);
}

// 5. Agregar al carrito
function agregarAlCarrito(idProducto) {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        let cantidadActual = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = cantidadActual + 1;
    }
    alert('Producto #' + idProducto + ' agregado al carrito');
}