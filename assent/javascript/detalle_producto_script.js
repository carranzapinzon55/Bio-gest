// Cambiar la imagen principal al hacer clic en las miniaturas
function cambiarImagen(elemento) {
    const imgDestacada = document.getElementById('img-destacada');
    imgDestacada.src = elemento.src;

    // Actualizar clase activa
    document.querySelectorAll('.miniatura').forEach(thumb => {
        thumb.classList.remove('activa');
    });
    elemento.classList.add('activa');
}

// Selector de Presentaciones
function seleccionarPresentacion(boton) {
    document.querySelectorAll('.btn-opcion').forEach(btn => {
        btn.classList.remove('activo');
    });
    boton.classList.add('activo');
}

// Control numérico de cantidad (+ / -)
function cambiarCantidad(valor) {
    const inputCantidad = document.getElementById('input-cantidad');
    let cantidadActual = parseInt(inputCantidad.value) || 1;
    
    cantidadActual += valor;
    if (cantidadActual < 1) {
        cantidadActual = 1;
    }
    
    inputCantidad.value = cantidadActual;
}