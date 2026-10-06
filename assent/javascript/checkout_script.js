const COSTO_ENVIO = 6000;
const CLAVE_LOCAL_STORAGE = 'carrito_biogest';

document.addEventListener('DOMContentLoaded', () => {
    cargarResumenPedido();
    configurarFormulario();
});

function obtenerCarrito() {
    return JSON.parse(localStorage.getItem(CLAVE_LOCAL_STORAGE)) || [];
}

function cargarResumenPedido() {
    const carrito = obtenerCarrito();
    const contenedorLista = document.getElementById('lista-items-resumen');
    const elemTotal = document.getElementById('monto-total');
    const elemCartCount = document.getElementById('cart-count');

    if (carrito.length === 0) {
        alert('No tienes productos en el carrito para finalizar la compra.');
        window.location.href = 'catalogo.html';
        return;
    }

    if (contenedorLista) {
        contenedorLista.innerHTML = '';
        let subtotal = 0;
        let totalCantidad = 0;

        carrito.forEach(item => {
            const subtotalItem = item.precio * item.cantidad;
            subtotal += subtotalItem;
            totalCantidad += item.cantidad;

            const divItem = document.createElement('div');
            divItem.classList.add('item-resumen');
            divItem.innerHTML = `
                <span>${item.nombre} (x${item.cantidad})</span>
                <span class="precio-item">$ ${Number(subtotalItem).toLocaleString('es-CO')}</span>
            `;
            contenedorLista.appendChild(divItem);
        });

        // Ítem del envío
        const divEnvio = document.createElement('div');
        divEnvio.classList.add('item-resumen');
        divEnvio.innerHTML = `
            <span>Envío</span>
            <span class="precio-item">$ ${Number(COSTO_ENVIO).toLocaleString('es-CO')}</span>
        `;
        contenedorLista.appendChild(divEnvio);

        const total = subtotal + COSTO_ENVIO;
        if (elemTotal) elemTotal.textContent = `$ ${Number(total).toLocaleString('es-CO')}`;
        if (elemCartCount) elemCartCount.textContent = totalCantidad;
    }
}

function configurarFormulario() {
    const btnContinuar = document.getElementById('btn-continuar-pago');
    const formCheckout = document.getElementById('form-checkout');

    if (!btnContinuar || !formCheckout) return;

    btnContinuar.addEventListener('click', async () => {
        const inputs = formCheckout.querySelectorAll('input[required]');
        let formularioValido = true;

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                input.classList.add('error');
                formularioValido = false;
            } else {
                input.classList.remove('error');
            }
        });

        const emailInput = document.getElementById('email');
        if (emailInput && emailInput.value.trim() !== '') {
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(emailInput.value.trim())) {
                emailInput.classList.add('error');
                formularioValido = false;
                alert('Por favor, ingresa un correo electrónico válido.');
                return;
            }
        }

        if (!formularioValido) {
            alert('Por favor, completa todos los campos obligatorios del formulario de envío.');
            return;
        }

        await enviarPedidoAlServidor();
    });

    formCheckout.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value.trim() !== '') {
                input.classList.remove('error');
            }
        });
    });
}

async function enviarPedidoAlServidor() {
    const carrito = obtenerCarrito();
    const btnContinuar = document.getElementById('btn-continuar-pago');

    const datosEnvio = {
        nombre: document.getElementById('nombre').value.trim(),
        direccion: document.getElementById('direccion').value.trim(),
        ciudad: document.getElementById('ciudad').value.trim(),
        departamento: document.getElementById('departamento').value.trim(),
        telefono: document.getElementById('telefono').value.trim(),
        email: document.getElementById('email').value.trim()
    };

    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const total = subtotal + COSTO_ENVIO;

    btnContinuar.disabled = true;
    btnContinuar.textContent = 'Procesando pedido...';

    try {
        const respuesta = await fetch('../api/guardar_pedido.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                envio: datosEnvio,
                carrito: carrito,
                total: total
            })
        });

        const resultado = await respuesta.json();

        if (resultado.success) {
            alert(`¡Pedido realizado con éxito! Código de orden: #${resultado.id_pedido}`);
            localStorage.removeItem(CLAVE_LOCAL_STORAGE); // Limpiar el carrito
            window.location.href = '../index.html';
        } else {
            alert(resultado.message || 'Error al procesar el pedido.');
            btnContinuar.disabled = false;
            btnContinuar.textContent = 'Confirmar y pagar';
        }
    } catch (error) {
        console.error('Error al conectar con la API:', error);
        alert('Error de conexión con el servidor.');
        btnContinuar.disabled = false;
        btnContinuar.textContent = 'Confirmar y pagar';
    }
}