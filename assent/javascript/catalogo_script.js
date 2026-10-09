const CLAVE_LOCAL_STORAGE = 'carrito_biogest';

// Datos de prueba locales (Fallback cuando PHP/MySQL no están conectados)
const productosMock = [
    {
        id_producto: 1,
        nombre_producto: "Tenebrios 100 unidades",
        categoria: "Tenebrios",
        precio: 35000,
        descripcion: "Paquete de 100 larvas vivas de Tenebrio molitor de alta calidad.",
        imagen: "Logo.jpeg",
        popularidad: 5
    },
    {
        id_producto: 2,
        nombre_producto: "Tenebrios 250 unidades",
        categoria: "Tenebrios",
        precio: 65000,
        descripcion: "Paquete de 250 larvas vivas de Tenebrio molitor.",
        imagen: "Logo.jpeg",
        popularidad: 4
    },
    {
        id_producto: 3,
        nombre_producto: "Abono Orgánico (Frass) 1kg",
        categoria: "Abono Orgánico",
        precio: 40000,
        descripcion: "Abono 100% natural rico en nutrientes derivado de la cría de insectos.",
        imagen: "Logo.jpeg",
        popularidad: 2
    },
    {
        id_producto: 4,
        nombre_producto: "Kit de Cría Básico",
        categoria: "Kits de Cría",
        precio: 120000,
        descripcion: "Kit completo que incluye contenedor, alimento inicial y colonia base.",
        imagen: "Logo.jpeg",
        popularidad: 3
    },
    {
        id_producto: 5,
        nombre_producto: "Kit de Cría Pro",
        categoria: "Kits de Cría",
        precio: 250000,
        descripcion: "Sistema de producción escala media con control de humedad e infraestructura.",
        imagen: "Logo.jpeg",
        popularidad: 1
    }
];

let listaProductosGlobal = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
    actualizarContadorHeader();
    inicializarFiltrosYEventos();
});

// Carga productos desde la API o usa datos simulados si falla la conexión
async function cargarProductos() {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    try {
        const respuesta = await fetch('../api/obtener_productos.php');
        if (!respuesta.ok) throw new Error("Sin respuesta del servidor PHP");
        
        const datos = await respuesta.json();

        if (Array.isArray(datos)) {
            listaProductosGlobal = datos;
        } else if (datos && Array.isArray(datos.productos)) {
            listaProductosGlobal = datos.productos;
        } else if (datos && Array.isArray(datos.data)) {
            listaProductosGlobal = datos.data;
        } else {
            listaProductosGlobal = productosMock;
        }
    } catch (error) {
        console.warn('Backend PHP no disponible. Cargando catálogo local simulado:', error);
        listaProductosGlobal = productosMock;
    }

    aplicarFiltrosYOrden();
}

// Vincula los IDs y Clases exactos de tu archivo HTML
function inicializarFiltrosYEventos() {
    // 1. Rango de Precio y Etiqueta de Texto
    const rangeInput = document.getElementById('price-range');
    const priceLabel = document.getElementById('price-label');

    if (rangeInput && priceLabel) {
        rangeInput.addEventListener('input', (e) => {
            const valor = Number(e.target.value);
            priceLabel.textContent = new Intl.NumberFormat('es-CO', { 
                style: 'currency', 
                currency: 'COP', 
                maximumFractionDigits: 0 
            }).format(valor);
        });
    }

    // 2. Botón "Aplicar Filtros"
    const btnApply = document.querySelector('.btn-apply-filters');
    if (btnApply) {
        btnApply.addEventListener('click', aplicarFiltrosYOrden);
    }

    // 3. Menú Desplegable "Ordenar Por"
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', aplicarFiltrosYOrden);
    }

    // 4. Barra de Búsqueda
    const inputBusqueda = document.getElementById('input-busqueda');
    const btnBusqueda = document.querySelector('.search-bar button');

    if (inputBusqueda) {
        inputBusqueda.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') aplicarFiltrosYOrden();
        });
    }
    if (btnBusqueda) {
        btnBusqueda.addEventListener('click', aplicarFiltrosYOrden);
    }
}

// Filtra y ordena los datos del arreglo
function aplicarFiltrosYOrden() {
    let resultado = [...listaProductosGlobal];

    // Búsqueda por texto en barra superior
    const inputBusqueda = document.getElementById('input-busqueda');
    if (inputBusqueda && inputBusqueda.value.trim() !== '') {
        const texto = inputBusqueda.value.toLowerCase().trim();
        resultado = resultado.filter(p => {
            const nombre = (p.nombre_producto || p.nombre || '').toLowerCase();
            const desc = (p.descripcion || '').toLowerCase();
            return nombre.includes(texto) || desc.includes(texto);
        });
    }

    // Filtro por Categorías seleccionadas
    const catCheckboxes = document.querySelectorAll('input[name="cat"]:checked');
    if (catCheckboxes.length > 0) {
        const categoriasSeleccionadas = Array.from(catCheckboxes).map(cb => {
            return cb.parentElement.textContent.trim().toLowerCase();
        });

        resultado = resultado.filter(p => {
            const catProd = (p.categoria || p.categoria_producto || '').toLowerCase();
            return categoriasSeleccionadas.some(cat => catProd.includes(cat) || cat.includes(catProd));
        });
    }

    // Filtro por Precio Máximo
    const rangeInput = document.getElementById('price-range');
    if (rangeInput) {
        const maxPrecio = Number(rangeInput.value);
        resultado = resultado.filter(p => Number(p.precio || 0) <= maxPrecio);
    }

    // Ordenamiento por criterio seleccionado
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        const opcion = sortSelect.value;
        if (opcion === 'low-high') {
            resultado.sort((a, b) => Number(a.precio) - Number(b.precio));
        } else if (opcion === 'high-low') {
            resultado.sort((a, b) => Number(b.precio) - Number(a.precio));
        } else if (opcion === 'popular') {
            resultado.sort((a, b) => (b.popularidad || 0) - (a.popularidad || 0));
        }
    }

    renderizarProductos(resultado);
}

// Pinta los productos procesados dentro de #contenedor-productos
function renderizarProductos(productos) {
    const contenedor = document.getElementById('contenedor-productos');
    if (!contenedor) return;

    if (productos.length === 0) {
        contenedor.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">No se encontraron productos que coincidan con los criterios de búsqueda.</p>';
        return;
    }

    contenedor.innerHTML = ''; // Limpiar contenedor

    productos.forEach(producto => {
        const id = producto.id_producto || producto.id;
        const nombre = producto.nombre_producto || producto.nombre || 'Producto';
        const precioNum = Number(producto.precio || 0);
        const precioFormateado = precioNum.toLocaleString('es-CO');
        const descripcion = producto.descripcion || 'Sin descripción disponible.';

        // Ajuste automático de rutas de imágenes
        let imagenRuta = producto.imagen || '../assent/img/Logo.jpeg';
        if (!imagenRuta.startsWith('../') && !imagenRuta.startsWith('http')) {
            imagenRuta = `../assent/img/${imagenRuta}`;
        }

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

        // Asignar evento al botón "Añadir"
        const btnAgregar = tarjeta.querySelector('.btn-add-cart');
        btnAgregar.addEventListener('click', (e) => {
            e.stopPropagation();
            agregarAlCarritoDesdeCatalogo({
                id: id,
                nombre: nombre,
                precio: precioNum,
                imagen: imagenRuta
            });
        });

        contenedor.appendChild(tarjeta);
    });
}

function verDetalle(idProducto) {
    window.location.href = `detalle_producto.html?id=${idProducto}`;
}

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

function actualizarContadorHeader() {
    const carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const cartCount = document.getElementById('cart-count');
    if (cartCount) cartCount.textContent = totalItems;
}