// Mostrar/Ocultar formulario de direcciones
function toggleFormDireccion() {
    const cardForm = document.getElementById('card-nueva-direccion');
    if (cardForm) {
        cardForm.classList.toggle('oculto');
    }
}

// Eliminar dirección
function eliminarDireccion(boton) {
    if (confirm('¿Estás seguro de que deseas eliminar esta dirección?')) {
        const card = boton.closest('.card-direccion');
        card.remove();
    }
}

// Validaciones de formularios
document.addEventListener('DOMContentLoaded', () => {
    const formDatos = document.getElementById('form-mis-datos');
    if (formDatos) {
        formDatos.addEventListener('submit', (e) => {
            e.preventDefault();
            const passNueva = document.getElementById('pass-nueva').value;
            const passConfirm = document.getElementById('pass-confirm').value;

            if (passNueva !== '' && passNueva !== passConfirm) {
                alert('Las contraseñas nuevas no coinciden.');
                return;
            }

            alert('¡Datos personales actualizados correctamente!');
        });
    }
});