/*const CLAVE_LOCAL_STORAGE = 'carrito_biogest';
let productoActual = null; // Variable global para almacenar el producto cargado

document.addEventListener('DOMContentLoaded', () => {
    cargarDetalleProducto();
    actualizarContadorHeader();
    vincularBotonesAccion();
});

async function cargarDetalleProducto() {
    const urlParams = new URLSearchParams(window.location.search);
    const idProducto = urlParams.get('id');

    if (!idProducto) {
        console.error('No se encontró el parámetro ID en la URL.');
        const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
        if (elemTitulo) elemTitulo.textContent = 'Producto no especificado';
        return;
    }

    try {
        const respuesta = await fetch(`../api/obtener_producto.php?id=${idProducto}`);
        const datos = await respuesta.json();

        // Extraer el producto de cualquier formato de respuesta de la API
        if (datos && datos.producto) {
            productoActual = datos.producto;
        } else if (datos && datos.data) {
            productoActual = datos.data;
        } else if (datos && (datos.id_producto || datos.id)) {
            productoActual = datos;
        }

        if (productoActual) {
            const nombre = productoActual.nombre_producto || productoActual.nombre || 'Producto sin nombre';
            const precioNum = Number(productoActual.precio || 0);
            const precioFormateado = precioNum.toLocaleString('es-CO');
            const descripcion = productoActual.descripcion || 'Sin descripción disponible para este producto.';
            
            let imagen = '../assent/img/Logo.jpeg';
            if (productoActual.imagen) {
                imagen = productoActual.imagen.startsWith('http') || productoActual.imagen.startsWith('../') 
                    ? productoActual.imagen 
                    : `../assent/img/${productoActual.imagen}`;
            }

            // Actualizar elementos de la vista con selectores flexibles
            const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
            const elemBreadcrumb = document.getElementById('breadcrumb-nombre') || document.getElementById('breadcrumb-producto');
            const elemPrecio = document.getElementById('precio-producto') || document.getElementById('precio');
            const elemDescCorta = document.getElementById('descripcion-corta');
            const elemDescLarga = document.getElementById('descripcion-larga') || document.getElementById('descripcion-producto');
            const elemImg = document.getElementById('img-destacada') || document.getElementById('imagen-producto');

            if (elemTitulo) elemTitulo.textContent = nombre;
            if (elemBreadcrumb) elemBreadcrumb.textContent = nombre;
            if (elemPrecio) elemPrecio.textContent = `$ ${precioFormateado} COP`;
            if (elemDescCorta) elemDescCorta.textContent = descripcion;
            if (elemDescLarga) elemDescLarga.innerHTML = `<p>${descripcion}</p>`;
            
            if (elemImg) {
                elemImg.src = imagen;
                elemImg.onerror = () => { elemImg.src = '../assent/img/Logo.jpeg'; };
            }
        } else {
            const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
            if (elemTitulo) elemTitulo.textContent = 'Producto no encontrado';
        }
    } catch (error) {
        console.error('Error al cargar detalle del producto:', error);
        const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
        if (elemTitulo) elemTitulo.textContent = 'Error al cargar producto';
    }
}

// Vincula los clics de los botones
function vincularBotonesAccion() {
    // 1. Intentar vincular por IDs comunes
    const btnAgregarId = document.getElementById('btn-agregar-carrito') || document.getElementById('btn-add-cart') || document.getElementById('btn-agregar');
    const btnComprarId = document.getElementById('btn-comprar-ahora') || document.getElementById('btn-buy-now') || document.getElementById('btn-comprar');

    if (btnAgregarId) {
        btnAgregarId.onclick = (e) => { e.preventDefault(); agregarDesdeDetalle(false); };
    }
    if (btnComprarId) {
        btnComprarId.onclick = (e) => { e.preventDefault(); agregarDesdeDetalle(true); };
    }

    // 2. Recorrer todos los botones por texto de respaldo
    const botones = document.querySelectorAll('button, .btn');

    botones.forEach(boton => {
        const texto = boton.textContent.trim().toLowerCase();

        // Botón "Agregar" o "Añadir"
        if ((texto.includes('agregar') || texto.includes('añadir')) && !texto.includes('comprar')) {
            boton.onclick = (e) => {
                e.preventDefault();
                agregarDesdeDetalle(false);
            };
        }
        // Botón "Comprar ahora"
        else if (texto.includes('comprar')) {
            boton.onclick = (e) => {
                e.preventDefault();
                agregarDesdeDetalle(true);
            };
        }
    });
}

function agregarDesdeDetalle(redirigirACarrito = false) {
    if (!productoActual) {
        alert('El producto aún no ha cargado correctamente.');
        return;
    }

    const inputCantidad = document.getElementById('input-cantidad') || document.getElementById('cantidad') || document.querySelector('input[type="number"]');
    const cantidad = inputCantidad ? (parseInt(inputCantidad.value) || 1) : 1;

    // Obtener presentación seleccionada si aplica
    const btnPresentacion = document.querySelector('.selector-presentacion .btn-opcion.activo') || document.querySelector('.btn-opcion.activo');
    const presentacionTexto = btnPresentacion ? btnPresentacion.textContent.trim() : '';

    const idProd = productoActual.id_producto || productoActual.id;
    const nombreProd = productoActual.nombre_producto || productoActual.nombre || 'Producto';
    const precioProd = Number(productoActual.precio || 0);
    
    let imagenRuta = '../assent/img/Logo.jpeg';
    if (productoActual.imagen) {
        imagenRuta = productoActual.imagen.startsWith('http') || productoActual.imagen.startsWith('../') 
            ? productoActual.imagen 
            : `../assent/img/${productoActual.imagen}`;
    }

    guardarEnLocalStorage({
        id: idProd,
        nombre: nombreProd,
        precio: precioProd,
        cantidad: cantidad,
        presentacion: presentacionTexto,
        imagen: imagenRuta
    });

    if (redirigirACarrito) {
        window.location.href = 'carrito.html';
    } else {
        alert(`¡${nombreProd} agregado al carrito exitosamente!`);
    }
}

function guardarEnLocalStorage(itemNuevo) {
    let carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];

    const indexExistente = carrito.findIndex(item => item.id === itemNuevo.id && item.presentacion === itemNuevo.presentacion);

    if (indexExistente !== -1) {
        carrito[indexExistente].cantidad += itemNuevo.cantidad;
    } else {
        carrito.push(itemNuevo);
    }

    localStorage.setItem(CLAVE_LOCAL_STORAGE, JSON.stringify(carrito));
    actualizarContadorHeader();
}

function actualizarContadorHeader() {
    const carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const cartCount = document.getElementById('cart-count');
    if (cartCount) cartCount.textContent = totalItems;
}

// Exponer funciones auxiliares para eventos onclick desde HTML
window.cambiarCantidad = function(cambio) {
    const input = document.getElementById('input-cantidad') || document.getElementById('cantidad') || document.querySelector('input[type="number"]');
    if (input) {
        let valor = parseInt(input.value) || 1;
        valor += cambio;
        if (valor < 1) valor = 1;
        input.value = valor;
    }
};

window.seleccionarPresentacion = function(boton) {
    const contenedor = boton.parentElement;
    if (contenedor) {
        contenedor.querySelectorAll('.btn-opcion').forEach(btn => btn.classList.remove('activo'));
    }
    boton.classList.add('activo');
};

window.agregarDesdeDetalle = agregarDesdeDetalle;
*/
const CLAVE_LOCAL_STORAGE = 'carrito_biogest';

// Datos locales de respaldo (iguales a los del catálogo para funcionar sin PHP)
const productosMockDetalle = [
    {
        id_producto: 1,
        nombre_producto: "Tenebrios 100 unidades",
        categoria: "Tenebrios",
        precio: 35000,
        descripcion: "Paquete de 100 larvas vivas de Tenebrio molitor de alta calidad, criadas bajo estrictos controles nutricionales.",
        imagen: "Logo.jpeg",
        popularidad: 5
    },
    {
        id_producto: 2,
        nombre_producto: "Tenebrios 250 unidades",
        categoria: "Tenebrios",
        precio: 65000,
        descripcion: "Paquete de 250 larvas vivas de Tenebrio molitor, ideal para alimentación de aves, reptiles y peces.",
        imagen: "Logo.jpeg",
        popularidad: 4
    },
    {
        id_producto: 3,
        nombre_producto: "Abono Orgánico (Frass) 1kg",
        categoria: "Abono Orgánico",
        precio: 40000,
        descripcion: "Abono 100% natural rico en nutrientes y quitina derivado de la cría de insectos.",
        imagen: "Logo.jpeg",
        popularidad: 2
    },
    {
        id_producto: 4,
        nombre_producto: "Kit de Cría Básico",
        categoria: "Kits de Cría",
        precio: 120000,
        descripcion: "Kit completo que incluye contenedor, alimento inicial y colonia base para iniciar tu propia producción.",
        imagen: "Logo.jpeg",
        popularidad: 3
    },
    {
        id_producto: 5,
        nombre_producto: "Kit de Cría Pro",
        categoria: "Kits de Cría",
        precio: 250000,
        descripcion: "Sistema de producción escala media con control de humedad e infraestructura avanzada.",
        imagen: "Logo.jpeg",
        popularidad: 1
    }
];

let productoActual = null; // Variable global para almacenar el producto cargado

document.addEventListener('DOMContentLoaded', () => {
    cargarDetalleProducto();
    actualizarContadorHeader();
    vincularBotonesAccion();
});

async function cargarDetalleProducto() {
    const urlParams = new URLSearchParams(window.location.search);
    const idProducto = Number(urlParams.get('id')) || 1;

    try {
        // Intento de conexión con la API PHP (si estuviera activa)
        const respuesta = await fetch(`../api/obtener_producto.php?id=${idProducto}`);
        if (!respuesta.ok) throw new Error("API no disponible");
        
        const datos = await respuesta.json();

        if (datos && datos.producto) {
            productoActual = datos.producto;
        } else if (datos && datos.data) {
            productoActual = datos.data;
        } else if (datos && (datos.id_producto || datos.id)) {
            productoActual = datos;
        } else {
            throw new Error("Formato de respuesta inválido");
        }
    } catch (error) {
        console.warn('Backend PHP no disponible. Cargando producto desde datos locales (localStorage/Mock):', error);
        
        // Carga inmediata desde el arreglo local de respaldo
        productoActual = productosMockDetalle.find(p => Number(p.id_producto) === idProducto) || productosMockDetalle[0];
    }

    if (productoActual) {
        const nombre = productoActual.nombre_producto || productoActual.nombre || 'Producto sin nombre';
        const precioNum = Number(productoActual.precio || 0);
        const precioFormateado = precioNum.toLocaleString('es-CO');
        const descripcion = productoActual.descripcion || 'Sin descripción disponible para este producto.';
        
        let imagen = '../assent/img/Logo.jpeg';
        if (productoActual.imagen) {
            imagen = productoActual.imagen.startsWith('http') || productoActual.imagen.startsWith('../') 
                ? productoActual.imagen 
                : `../assent/img/${productoActual.imagen}`;
        }

        // Actualizar elementos de la vista con selectores flexibles
        const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
        const elemBreadcrumb = document.getElementById('breadcrumb-nombre') || document.getElementById('breadcrumb-producto');
        const elemPrecio = document.getElementById('precio-producto') || document.getElementById('precio');
        const elemDescCorta = document.getElementById('descripcion-corta');
        const elemDescLarga = document.getElementById('descripcion-larga') || document.getElementById('descripcion-producto');
        const elemImg = document.getElementById('img-destacada') || document.getElementById('imagen-producto');

        if (elemTitulo) elemTitulo.textContent = nombre;
        if (elemBreadcrumb) elemBreadcrumb.textContent = nombre;
        if (elemPrecio) elemPrecio.textContent = `$ ${precioFormateado} COP`;
        if (elemDescCorta) elemDescCorta.textContent = descripcion;
        if (elemDescLarga) elemDescLarga.innerHTML = `<p>${descripcion}</p>`;
        
        if (elemImg) {
            elemImg.src = imagen;
            elemImg.onerror = () => { elemImg.src = '../assent/img/Logo.jpeg'; };
        }
    } else {
        const elemTitulo = document.getElementById('titulo-producto') || document.getElementById('nombre-producto');
        if (elemTitulo) elemTitulo.textContent = 'Producto no encontrado';
    }
}

// Vincula los clics de los botones
function vincularBotonesAccion() {
    const btnAgregarId = document.getElementById('btn-agregar-carrito') || document.getElementById('btn-add-cart') || document.getElementById('btn-agregar');
    const btnComprarId = document.getElementById('btn-comprar-ahora') || document.getElementById('btn-buy-now') || document.getElementById('btn-comprar');

    if (btnAgregarId) {
        btnAgregarId.onclick = (e) => { e.preventDefault(); agregarDesdeDetalle(false); };
    }
    if (btnComprarId) {
        btnComprarId.onclick = (e) => { e.preventDefault(); agregarDesdeDetalle(true); };
    }

    const botones = document.querySelectorAll('button, .btn');
    botones.forEach(boton => {
        const texto = boton.textContent.trim().toLowerCase();
        if ((texto.includes('agregar') || texto.includes('añadir')) && !texto.includes('comprar')) {
            boton.onclick = (e) => {
                e.preventDefault();
                agregarDesdeDetalle(false);
            };
        } else if (texto.includes('comprar')) {
            boton.onclick = (e) => {
                e.preventDefault();
                agregarDesdeDetalle(true);
            };
        }
    });
}

function agregarDesdeDetalle(redirigirACarrito = false) {
    if (!productoActual) {
        alert('El producto aún no ha cargado correctamente.');
        return;
    }

    const inputCantidad = document.getElementById('input-cantidad') || document.getElementById('cantidad') || document.querySelector('input[type="number"]');
    const cantidad = inputCantidad ? (parseInt(inputCantidad.value) || 1) : 1;

    const btnPresentacion = document.querySelector('.selector-presentacion .btn-opcion.activo') || document.querySelector('.btn-opcion.activo');
    const presentacionTexto = btnPresentacion ? btnPresentacion.textContent.trim() : '';

    const idProd = productoActual.id_producto || productoActual.id;
    const nombreProd = productoActual.nombre_producto || productoActual.nombre || 'Producto';
    const precioProd = Number(productoActual.precio || 0);
    
    let imagenRuta = '../assent/img/Logo.jpeg';
    if (productoActual.imagen) {
        imagenRuta = productoActual.imagen.startsWith('http') || productoActual.imagen.startsWith('../') 
            ? productoActual.imagen 
            : `../assent/img/${productoActual.imagen}`;
    }

    guardarEnLocalStorage({
        id: idProd,
        nombre: nombreProd,
        precio: precioProd,
        cantidad: cantidad,
        presentacion: presentacionTexto,
        imagen: imagenRuta
    });

    if (redirigirACarrito) {
        window.location.href = 'carrito.html';
    } else {
        alert(`¡${nombreProd} agregado al carrito exitosamente!`);
    }
}

function guardarEnLocalStorage(itemNuevo) {
    let carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
    const indexExistente = carrito.findIndex(item => item.id === itemNuevo.id && item.presentacion === itemNuevo.presentacion);

    if (indexExistente !== -1) {
        carrito[indexExistente].cantidad += itemNuevo.cantidad;
    } else {
        carrito.push(itemNuevo);
    }

    localStorage.setItem(CLAVE_LOCAL_STORAGE, JSON.stringify(carrito));
    actualizarContadorHeader();
}

function actualizarContadorHeader() {
    const carrito = JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
    const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const cartCount = document.getElementById('cart-count');
    if (cartCount) cartCount.textContent = totalItems;
}

window.cambiarCantidad = function(cambio) {
    const input = document.getElementById('input-cantidad') || document.getElementById('cantidad') || document.querySelector('input[type="number"]');
    if (input) {
        let valor = parseInt(input.value) || 1;
        valor += cambio;
        if (valor < 1) valor = 1;
        input.value = valor;
    }
};

window.seleccionarPresentacion = function(boton) {
    const contenedor = boton.parentElement;
    if (contenedor) {
        contenedor.querySelectorAll('.btn-opcion').forEach(btn => btn.classList.remove('activo'));
    }
    boton.classList.add('activo');
};

window.agregarDesdeDetalle = agregarDesdeDetalle;