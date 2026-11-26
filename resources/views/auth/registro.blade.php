<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | BIO-GEST - Tenebrios Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/registro_style.css') }}">
</head>
<body>
    <div class="contenedor-pagina">
        <!-- PANEL IZQUIERDO -->
        <div class="panel-izquierdo">
            <div class="logo-contenedor">
                <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="LOGO PNG TENEBRIOS" class="logo-img">
                <h1>TENEBRIOS GOLD</h1>
                <p class="slogan">Alimentos vivos y pie de cría de calidad</p>
            </div>
            <div class="hoja-decoracion"></div>
        </div>

        <!-- PANEL DERECHO -->
        <div class="panel-derecho">
            <div class="tarjeta-registro">
                <div class="header-tarjeta">
                    <h2>Crear cuenta</h2>
                    <p>Completa tus datos para registrarte en TENEBRIOS GOLD</p>
                </div>

                @if($errors->any())
                    <div style="background:#fee2e2; color:#b91c1c; padding:10px; margin-bottom:15px; border-radius:5px; text-align:left; font-size:0.85rem;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('registro.post') }}" method="POST" id="registroForm">
                    @csrf
                    <!-- Nombre y Apellido -->
                    <div style="display:flex; gap:10px;">
                        <div class="input-form" style="flex:1;">
                            <label for="nombre">Nombre *</label>
                            <div class="input-campo">
                                <i class="fa-regular fa-user"></i>
                                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)" required>
                            </div>
                        </div>

                        <div class="input-form" style="flex:1;">
                            <label for="apellido">Apellido *</label>
                            <div class="input-campo">
                                <i class="fa-regular fa-user"></i>
                                <input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" placeholder="Apellido" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)" required>
                            </div>
                        </div>
                    </div>

                    <!-- Correo electrónico -->
                    <div class="input-form">
                        <label for="email">Correo electrónico *</label>
                        <div class="input-campo">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="email" name="correo" value="{{ old('correo') }}" placeholder="Ingresa tu correo electrónico" required>
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="input-form">
                        <label for="telefono">Teléfono / Celular</label>
                        <div class="input-campo">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 3001234567" inputmode="numeric" maxlength="10" autocomplete="tel" pattern="[0-9]{1,10}">
                        </div>
                    </div>

                    <!-- Perfil de Uso -->
                    <div class="input-form">
                        <label for="perfil_uso">Perfil de uso / Destino *</label>
                        <div class="input-campo">
                            <i class="fa-solid fa-tag"></i>
                            <select id="perfil_uso" name="perfil_uso" required>
                                <option value="Alimentación continua" {{ old('perfil_uso') === 'Alimentación continua' ? 'selected' : '' }}>Alimentación continua</option>
                                <option value="Inicio de criadero" {{ old('perfil_uso') === 'Inicio de criadero' ? 'selected' : '' }}>Inicio de criadero</option>
                                <option value="Proyecto comercial / Gran escala" {{ old('perfil_uso') === 'Proyecto comercial / Gran escala' ? 'selected' : '' }}>Proyecto comercial / Gran escala</option>
                                <option value="Mascota única" {{ old('perfil_uso') === 'Mascota única' ? 'selected' : '' }}>Mascota única</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="input-form">
                        <label for="password">Contraseña * (mínimo 8 caracteres, al menos 1 letra y 1 número)</label>
                        <div class="input-campo">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="contrasena" placeholder="Mínimo 8 caracteres (letras y números)" minlength="8" pattern="^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).{8,}$" title="Mínimo 8 caracteres, incluyendo al menos una letra y un número" required>
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('password', this)" style="cursor:pointer;" title="Mostrar / ocultar contraseña"></i>
                        </div>
                    </div>

                    <!-- Confirmar contraseña -->
                    <div class="input-form">
                        <label for="confirm-password">Confirmar contraseña *</label>
                        <div class="input-campo">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="confirm-password" name="contrasena_confirmation" placeholder="Confirma tu contraseña" minlength="8" required>
                            <i class="fa-regular fa-eye toggle-password" onclick="togglePasswordVisibility('confirm-password', this)" style="cursor:pointer;" title="Mostrar / ocultar contraseña"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-registrar">Registrarse</button>
                </form>

                <div class="login-redirect">
                    <span>¿Ya tienes una cuenta?</span>
                    <a href="{{ route('login') }}">Inicia sesión</a>
                </div>

                <div style="text-align:center; margin-top:10px;">
                    <a href="{{ route('contacto.index') }}" style="color:#64748b; font-size:0.85rem; text-decoration:none;">
                        <i class="fa-regular fa-circle-question"></i> ¿Necesitas ayuda?
                    </a>
                </div>

                <div class="back-home">
                    <a href="{{ route('home') }}"><i class="fa-solid fa-chevron-left"></i> Volver a la tienda</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        function togglePasswordVisibility(inputId, iconEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                iconEl.classList.remove('fa-eye');
                iconEl.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                iconEl.classList.remove('fa-eye-slash');
                iconEl.classList.add('fa-eye');
            }
        }

        // Bloquear números y símbolos en Nombre y Apellido en tiempo real
        ['nombre', 'apellido'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]/g, '').replace(/\s{2,}/g, ' ');
            });
            el.addEventListener('paste', event => {
                event.preventDefault();
                const text = (event.clipboardData || window.clipboardData).getData('text');
                const clean = text.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]/g, '').replace(/\s{2,}/g, ' ');
                document.execCommand ? document.execCommand('insertText', false, clean) : (el.value += clean);
            });
        });

        // Bloquear letras en Teléfono (solo dígitos, máx 10)
        const telefono = document.getElementById('telefono');
        telefono.addEventListener('input', () => {
            telefono.value = telefono.value.replace(/\D/g, '').slice(0, 10);
        });
        telefono.addEventListener('paste', event => {
            event.preventDefault();
            const pastedDigits = event.clipboardData.getData('text').replace(/\D/g, '');
            const start = telefono.selectionStart;
            const end = telefono.selectionEnd;
            const available = 10 - (telefono.value.length - (end - start));
            telefono.setRangeText(pastedDigits.slice(0, Math.max(0, available)), start, end, 'end');
        });
    </script>
</body>
</html>
