document.addEventListener('DOMContentLoaded', () => {
    cargarDetalleProducto();
});

async function cargarDetalleProducto() {
    // 1. Extraer el parametro 'id' de la URL (ej: detalle_producto.html?id=2)
    const urlParams = new URLSearchParams(window.location.search);
    const idProducto = urlParams.get('id');

    if (!idProducto) {
        console.error('No se encontró el parámetro ID en la URL.');
        document.getElementById('titulo-producto').textContent = 'Producto no especificado';
        return;
    }

    try {
        // 2. Pedir la información del producto a la API
        const respuesta = await fetch(`../api/obtener_producto.php?id=${idProducto}`);
        const datos = await respuesta.json();

        if (datos.success && datos.producto) {
            const producto = datos.producto;

            // Mapeo flexible de campos según la estructura de tu BD
            const nombre = producto.nombre_producto || producto.nombre || 'Producto sin nombre';
            const precio = producto.precio ? Number(producto.precio).toLocaleString('es-CO') : '0';
            const descripcion = producto.descripcion || 'Sin descripción disponible para este producto.';
            const imagen = producto.imagen ? `../assent/img/${producto.imagen}` : '../assent/img/Logo.jpeg';

            // 3. Reemplazar los contenidos en la vista HTML
            const elemTitulo = document.getElementById('titulo-producto');
            const elemBreadcrumb = document.getElementById('breadcrumb-nombre');
            const elemPrecio = document.getElementById('precio-producto');
            const elemDescCorta = document.getElementById('descripcion-corta');
            const elemDescLarga = document.getElementById('descripcion-larga');
            const elemImg = document.getElementById('img-destacada');

            if (elemTitulo) elemTitulo.textContent = nombre;
            if (elemBreadcrumb) elemBreadcrumb.textContent = nombre;
            if (elemPrecio) elemPrecio.textContent = `$ ${precio} COP`;
            if (elemDescCorta) elemDescCorta.textContent = descripcion;
            if (elemDescLarga) elemDescLarga.innerHTML = `<p>${descripcion}</p>`;
            
            if (elemImg) {
                elemImg.src = imagen;
                elemImg.onerror = () => { elemImg.src = '../assent/img/Logo.jpeg'; };
            }
        } else {
            document.getElementById('titulo-producto').textContent = 'Producto no encontrado';
            console.error('Error desde el servidor:', datos.message);
        }
    } catch (error) {
        console.error('Error al cargar detalle del producto:', error);
        document.getElementById('titulo-producto').textContent = 'Error al cargar producto';
    }
}

// Interacciones de la vista (Miniaturas, selector de cantidad y presentación)
function cambiarImagen(elemento) {
    const imgDestacada = document.getElementById('img-destacada');
    if (imgDestacada) {
        imgDestacada.src = elemento.src;
    }
    document.querySelectorAll('.miniatura').forEach(min => min.classList.remove('activa'));
    elemento.classList.add('activa');
}

function cambiarCantidad(cambio) {
    const input = document.getElementById('input-cantidad');
    if (input) {
        let valor = parseInt(input.value) || 1;
        valor += cambio;
        if (valor < 1) valor = 1;
        input.value = valor;
    }
}

function seleccionarPresentacion(boton) {
    document.querySelectorAll('.selector-presentacion .btn-opcion').forEach(btn => btn.classList.remove('activo'));
    boton.classList.add('activo');
}