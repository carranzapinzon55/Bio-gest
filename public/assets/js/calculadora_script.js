document.addEventListener('DOMContentLoaded', () => {
            const weightInput = document.getElementById('weightInput');
            const frequencySelect = document.getElementById('frequencySelect');
            const calculateBtn = document.getElementById('calculateBtn');
            const validationAlert = document.getElementById('validationAlert');
            const dailyTotalDisplay = document.getElementById('dailyTotalDisplay');
            const breakdownGrid = document.getElementById('breakdownGrid');
            const savePetBtn = document.getElementById('savePetBtn');

            function calculateFood() {
                const weightVal = weightInput.value.trim();

                // Criterio de Aceptación 02: Validar datos obligatorios faltantes
                if (weightVal === '' || isNaN(weightVal) || Number(weightVal) <= 0) {
                    validationAlert.style.display = 'block';
                    validationAlert.textContent = 'Error: Por favor ingresa un peso válido y obligatorio para tu mascota antes de calcular.';
                    return;
                } else {
                    validationAlert.style.display = 'none';
                }

                // Criterio de Aceptación 01: Cálculo estimado basado en características
                const weight = parseFloat(weightVal);
                const freq = parseInt(frequencySelect.value);

                // Estimación aproximada: 10% a 15% del peso corporal en insectos (ejemplo estándar para reptiles/geckos)
                let minTotal = (weight * 0.10).toFixed(1);
                let maxTotal = (weight * 0.15).toFixed(1);

                dailyTotalDisplay.innerHTML = `${minTotal} - ${maxTotal} <span>gr de alimento vivo (aprox.)</span>`;

                // Generar desglose según frecuencia
                breakdownGrid.innerHTML = '';
                const timesLabels = [
                    ['☀️ Mañana', '🌙 Tarde', '🌙 Noche'],
                    ['☀️ Mañana'],
                    ['☀️ Mañana', '🌤️ Mediodía', '🌙 Tarde']
                ];

                let labels = freq === 1 ? ['☀️ Única toma'] : (freq === 2 ? ['☀️ Mañana', '🌙 Tarde'] : ['☀️ Mañana', '🌤️ Mediodía', '🌙 Tarde']);

                let minPerMeal = (minTotal / freq).toFixed(1);
                let maxPerMeal = (maxTotal / freq).toFixed(1);

                labels.forEach(label => {
                    const box = document.createElement('div');
                    box.className = 'breakdown-box';
                    box.innerHTML = `
                        <div class="time">${label}</div>
                        <div class="amount">${minPerMeal} - ${maxPerMeal} g</div>
                    `;
                    breakdownGrid.appendChild(box);
                });
            }

            calculateBtn.addEventListener('click', calculateFood);

            savePetBtn.addEventListener('click', () => {
                const weightVal = weightInput.value.trim();
                if (weightVal === '' || isNaN(weightVal) || Number(weightVal) <= 0) {
                    validationAlert.style.display = 'block';
                    validationAlert.textContent = 'Error: Debes calcular una cantidad válida antes de guardar en tu mascota.';
                    return;
                }
                alert('¡Datos de alimentación guardados exitosamente en el perfil de tu mascota!');
            });
        });