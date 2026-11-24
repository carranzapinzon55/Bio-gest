<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión | BIO-GEST - Tenebrios Gold</title>
    <link rel="stylesheet" href="{{ asset('assets/css/login_style.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Chelsea+Market&family=IBM+Plex+Serif:wght@100;400;700&display=swap" rel="stylesheet"/>
</head>
<body>
    <header>
        <div class="logo-container">
            <a href="{{ route('home') }}" style="display:flex;align-items:center;text-decoration:none;">
                <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="Logo Tenebrios Gold" />
            </a>
        </div>
    </header>

    <main>
        <div class="contenedor-login">
            @if(session('success'))
                <div style="background:#dcfce7; color:#15803d; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; font-size:0.9rem;">
                    {{ session('success') }}
                </div>
            @endif

            @if(request()->query('deactivated') == '1' && !$errors->any())
                <div style="background:#fee2e2; color:#b91c1c; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; font-size:0.9rem;">
                    <p style="margin:2px 0;">Tu cuenta ha sido desactivada por un administrador. Tu sesión fue cerrada de inmediato.</p>
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fee2e2; color:#b91c1c; padding:10px; margin-bottom:15px; border-radius:5px; text-align:center; font-size:0.9rem;">
                    @foreach($errors->all() as $error)
                        <p style="margin:2px 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="form-login">
                @csrf
                <div class="contenedor-header">
                    <h2>Inicio de sesión</h2>
                    <h4>Accede a tu cuenta en BIO-GEST</h4>
                </div>

                <div class="input-contenedor">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="correo" value="{{ old('correo') }}" placeholder="Correo electrónico" required autofocus />
                </div>

                <div class="input-contenedor">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña" required />
                    <i class="fas fa-eye" id="togglePassword" style="cursor: pointer;" onclick="const p=document.getElementById('contrasena');p.type=p.type==='password'?'text':'password';"></i>
                </div>

                <div class="contraseña-olvidada" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <label style="font-size:0.85rem; color:#666; cursor:pointer;">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordarme
                    </label>
                    <a href="{{ route('faq') }}">¿Necesitas ayuda?</a>
                </div>

                <button type="submit" class="btn-submit">Iniciar sesión</button>
            </form>

            <hr class="divisor" />

            <div class="registro-link">
                <span>¿No tienes una cuenta?</span>
                <a href="{{ route('registro') }}">Regístrate</a>
            </div>

            <div style="text-align:center; margin-top:15px;">
                <a href="{{ route('home') }}" style="color:#666; font-size:0.85rem; text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Volver a la tienda
                </a>
            </div>
        </div>
    </main>
</body>
</html>
