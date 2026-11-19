// 1. Base de datos simulada (Lista de productos)
const productosDB = [
    { id: 1, nombre: "Tenebrios", unidades: "100 unidades", precio: 8000 },
    { id: 2, nombre: "Tenebrios", unidades: "250 unidades", precio: 15000 },
    { id: 3, nombre: "Zophobas", unidades: "100 unidades", precio: 10000 },
    { id: 4, nombre: "Zophobas", unidades: "250 unidades", precio: 18000 },
    { id: 5, nombre: "Grillos", unidades: "100 unidades", precio: 9000 },
    { id: 6, nombre: "Grillos", unidades: "250 unidades", precio: 16000 },
    { id: 7, nombre: "Cucarachas Dubia", unidades: "100 unidades", precio: 12000 },
    { id: 8, nombre: "Cucarachas Dubia", unidades: "250 unidades", precio: 20000 }
];

// Formateador de moneda colombiana
const formatoPesos = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0
});

// 2. Renderizar los productos una vez que el HTML haya cargado
document.addEventListener('DOMContentLoaded', () => {
    
    const grid = document.getElementById('product-grid');
    
    // Verificamos que el div de la cuadrícula exista antes de intentar meterle los productos
    if (grid) {
        productosDB.forEach(prod => {
            const card = document.createElement('div');
            card.className = 'product-card';
            
            // Limpiamos el formato para que quede por ejemplo "$ 8.000"
            const precioFormateado = formatoPesos.format(prod.precio).replace('COP', '').trim();

            // Inyectamos el HTML de cada tarjeta de producto
            card.innerHTML = `
                <div class="img-placeholder"></div>
                <div class="product-title">${prod.nombre}</div>
                <div class="product-unit">${prod.unidades}</div>
                <div class="product-price">${precioFormateado}</div>
                <button class="btn-add" onclick="agregarAlCarrito(${prod.id}    )">Agregar</button>
            `;
            grid.appendChild(card);
        });
    } else {
        console.error("No se encontró el contenedor de productos (product-grid) en el HTML.");
    }
});

// 3. Lógica del Carrito de Compras
let cantidadCarrito = 0;

function agregarAlCarrito() {
    cantidadCarrito++;
    
    // Sumar al contador
    const contador = document.getElementById('cart-counter');
    if(contador) contador.innerText = cantidadCarrito;
    
    // Mostrar la alerta inferior (Toast)
    const toast = document.getElementById("toast");
    if(toast) {
        toast.className = "show";
        setTimeout(() => { 
            toast.className = toast.className.replace("show", ""); 
        }, 3000);
    }
}

// 4. Lógica de Navegación entre las secciones (Menú superior)
function cambiarSeccion(seccion) {
    const secProductos = document.getElementById('sec-productos');
    const secQuienesSomos = document.getElementById('sec-quienes-somos');
    const navProductos = document.getElementById('nav-productos');
    const navQuienesSomos = document.getElementById('nav-quienes-somos');

    // Ocultar todas las secciones
    if(secProductos) secProductos.classList.remove('active');
    if(secQuienesSomos) secQuienesSomos.classList.remove('active');
    
    // Limpiar el estilo visual de los botones del menú
    document.querySelectorAll('nav a').forEach(el => el.classList.remove('active'));

    // Activar la sección correspondiente según el clic
    if (seccion === 'productos') {
        if(secProductos) secProductos.classList.add('active');
        if(navProductos) navProductos.classList.add('active');
    } else if (seccion === 'quienes-somos') {
        if(secQuienesSomos) secQuienesSomos.classList.add('active');
        if(navQuienesSomos) navQuienesSomos.classList.add('active');
    }
}