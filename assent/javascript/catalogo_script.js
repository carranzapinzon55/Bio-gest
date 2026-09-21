// Filtrar productos por categoría
function filtrarCategoria(categoria, boton) {
    // Cambiar estado activo en botones de filtro
    document.querySelectorAll('.btn-filtro').forEach(btn => btn.classList.remove('activo'));
    boton.classList.add('activo');

    // Mostrar u ocultar tarjetas de producto
    const productos = document.querySelectorAll('.card-producto');
    productos.forEach(prod => {
        if (categoria === 'todos' || prod.dataset.categoria === categoria) {
            prod.style.display = 'block';
        } else {
            prod.style.display = 'none';
        }
    });
}

// Función rápida para agregar al carrito desde el catálogo
function agregarAlCarritoRapido(nombreProducto) {
    let cartCount = document.getElementById('cart-count');
    if (cartCount) {
        let actual = parseInt(cartCount.textContent) || 0;
        cartCount.textContent = actual + 1;
    }
    alert(`¡${nombreProducto} agregado al carrito!`);
}