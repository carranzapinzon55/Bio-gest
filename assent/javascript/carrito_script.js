const COSTO_ENVIO = 6000;

function actualizarCantidad(boton, cambio) {
    const fila = boton.closest('.fila-producto');
    const inputCant = fila.querySelector('.input-cant');
    let cantidad = parseInt(inputCant.value) || 1;

    cantidad += cambio;
    if (cantidad < 1) cantidad = 1;

    inputCant.value = cantidad;
    
    // Recalcular el subtotal de este item
    const precioUnitario = parseInt(fila.getAttribute('data-precio'));
    const subtotalItem = precioUnitario * cantidad;
    
    fila.querySelector('.subtotal-item').textContent = `$ ${subtotalItem.toLocaleString('es-CO')}`;

    recalcularTotales();
}

function eliminarProducto(boton) {
    const fila = boton.closest('.fila-producto');
    fila.remove();
    recalcularTotales();
}

function recalcularTotales() {
    let subtotalGeneral = 0;
    let totalItems = 0;

    const filas = document.querySelectorAll('.fila-producto');

    filas.forEach(fila => {
        const precioUnitario = parseInt(fila.getAttribute('data-precio'));
        const cantidad = parseInt(fila.querySelector('.input-cant').value) || 0;
        
        subtotalGeneral += precioUnitario * cantidad;
        totalItems += cantidad;
    });

    const totalGeneral = subtotalGeneral > 0 ? subtotalGeneral + COSTO_ENVIO : 0;
    const envio = subtotalGeneral > 0 ? COSTO_ENVIO : 0;

    // Actualizar resumen visual
    document.getElementById('resumen-subtotal').textContent = `$ ${subtotalGeneral.toLocaleString('es-CO')}`;
    document.getElementById('resumen-envio').textContent = `$ ${envio.toLocaleString('es-CO')}`;
    document.getElementById('resumen-total').textContent = `$ ${totalGeneral.toLocaleString('es-CO')}`;
    
    // Actualizar contador del carrito en el header
    document.getElementById('cart-count').textContent = totalItems;
}   