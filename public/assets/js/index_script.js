document.addEventListener('DOMContentLoaded', () => {
    
    // Función reutilizable para inicializar cualquier carrusel por su ID
    function setupCarousel(carouselId) {
        const carousel = document.getElementById(carouselId);
        if (!carousel) return;

        const slides = carousel.querySelectorAll('.carousel-slide');
        const dots = carousel.querySelectorAll('.dot');
        const prevBtn = carousel.querySelector('.prev');
        const nextBtn = carousel.querySelector('.next');
        let currentIndex = 0;

        // Función para cambiar de slide
        function updateCarousel(index) {
            // Remover la clase activa de todos
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            // Asegurar que el índice esté dentro de los límites
            if (index >= slides.length) {
                currentIndex = 0;
            } else if (index < 0) {
                currentIndex = slides.length - 1;
            } else {
                currentIndex = index;
            }

            // Añadir clase activa al slide y punto actual
            slides[currentIndex].classList.add('active');
            dots[currentIndex].classList.add('active');
        }

        // Eventos de botones (Flechas)
        nextBtn.addEventListener('click', () => {
            updateCarousel(currentIndex + 1);
        });

        prevBtn.addEventListener('click', () => {
            updateCarousel(currentIndex - 1);
        });

        // Eventos de puntos (Paginación)
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                updateCarousel(index);
            });
        });

        // Cambio automático cada 5 segundos
        setInterval(() => {
            updateCarousel(currentIndex + 1);
        }, 5000);
    }

    // Inicializamos ambos carruseles de forma independiente
    setupCarousel('carousel1');
    setupCarousel('carousel2');
});