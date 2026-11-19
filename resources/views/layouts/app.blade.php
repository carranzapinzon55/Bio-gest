<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenebrios Gold | Alimentos vivos de calidad')</title>

    <!-- Librería de íconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos Globales Tenebrios Gold (#151515 y #e4b353) -->
    <link rel="stylesheet" href="{{ asset('assets/css/index_style.css') }}">
    @stack('styles')
</head>
<body>

    <!-- ENCABEZADO -->
    <header class="main-header">
        <a href="{{ route('home') }}" class="logo-area">
            <div class="placeholder-box logo-img logo-placeholder">
                <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="Logo Tenebrios Gold" class="logo-img">
            </div>
            <div class="brand-text">
                <h1>TENEBRIOS GOLD</h1>
                <p>Alimentos vivos de calidad</p>
            </div>
        </a>

        <form action="{{ route('catalogo.index') }}" method="GET" class="search-bar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar larvas, pie de cría, kits...">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <div class="user-actions">
            @auth
                <div class="action-item">
                    <i class="far fa-user"></i>
                    <span>
                        <a href="{{ auth()->user()->esAdmin() ? route('admin.inicio') : route('cliente.cuenta') }}">
                            Hola, {{ strtok(auth()->user()->nombre, ' ') }}
                        </a>
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-item" style="background:none;border:none;font-size:0.9rem;">
                        <i class="fas fa-sign-out-alt"></i> Salir
                    </button>
                </form>
            @else
                <div class="action-item">
                    <i class="far fa-user"></i>
                    <span><a href="{{ route('login') }}">Iniciar sesión</a></span>
                </div>
            @endauth

            <a href="{{ route('carrito.index') }}" class="action-item cart-btn">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count" id="cart-count">
                    {{ session('carrito') ? collect(session('carrito'))->sum('cantidad') : 0 }}
                </span>
            </a>
        </div>
    </header>

    <!-- NAVEGACIÓN PRINCIPAL -->
    <nav class="main-nav">
        <ul>
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a></li>
            <li><a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.*') ? 'active' : '' }}">Productos</a></li>
            <li><a href="{{ route('calculadora.index') }}" class="{{ request()->routeIs('calculadora.*') ? 'active' : '' }}">Calculadora de Alimento</a></li>
            <li><a href="{{ route('envios') }}" class="{{ request()->routeIs('envios') ? 'active' : '' }}">Envíos y Garantías</a></li>
            <li><a href="{{ route('nosotros') }}" class="{{ request()->routeIs('nosotros') ? 'active' : '' }}">¿Quiénes somos?</a></li>
            <li><a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a></li>
            <li><a href="{{ route('contacto.index') }}" class="{{ request()->routeIs('contacto.*') ? 'active' : '' }}">Contacto</a></li>
        </ul>
    </nav>

    <!-- ALERTAS DE SESIÓN -->
    @if(session('success'))
        <div style="background:#dcfce7; color:#15803d; padding:12px 5%; text-align:center; font-weight:600;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fee2e2; color:#b91c1c; padding:12px 5%; text-align:center; font-weight:600;">
            {{ session('error') }}
        </div>
    @endif

    <!-- CONTENIDO DINÁMICO -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="footer-col brand-col">
            <div class="placeholder-box small-square footer-logo-placeholder">
                <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="Logo Tenebrios Gold" class="footer-logo">
            </div>
            <div>
                <h4>TENEBRIOS GOLD</h4>
                <p>Alimento vivo y pie de cría con garantía de llegada viva</p>
            </div>
        </div>
        <div class="footer-col info-col">
            <h4>Información</h4>
            <ul>
                <li><a href="{{ route('nosotros') }}">¿Quiénes somos?</a></li>
                <li><a href="{{ route('envios') }}">Políticas de envío y reembolso por mortalidad</a></li>
                <li><a href="{{ route('faq') }}">Preguntas Frecuentes (Cuidados y Nevera)</a></li>
                <li><a href="{{ route('contacto.index') }}">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-col social-col">
            <h4>Síguenos</h4>
            <div class="social-icons">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-whatsapp"></i>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
