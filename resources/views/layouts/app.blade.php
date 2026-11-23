<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenebrios Gold | BIO-GEST')</title>

    <!-- Librería de íconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if(request()->is('admin*'))
        <!-- Estilos Panel Administrativo Unificado Tenebrios Gold (#151515 y #e4b353) -->
        <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
        <style>
            :root {
                --tg-dark: #151515;
                --tg-gold: #e4b353;
                --tg-gold-hover: #c99b45;
                --tg-bg: #f4f6f9;
                --tg-card: #ffffff;
                --tg-border: #e2e8f0;
            }
            body.admin-layout {
                background-color: var(--tg-bg);
                color: #1e293b;
                display: flex;
                min-height: 100vh;
                margin: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            /* Sidebar Oscuro #151515 con Acentos Dorados #e4b353 */
            .admin-sidebar {
                width: 260px;
                background-color: var(--tg-dark);
                color: #ffffff;
                border-right: 3px solid var(--tg-gold);
                display: flex;
                flex-direction: column;
                flex-shrink: 0;
                transition: transform 0.3s ease;
                z-index: 1000;
            }
            .admin-sidebar .brand {
                padding: 22px 20px;
                border-bottom: 1px solid #2a2a2a;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .admin-sidebar .brand img {
                width: 42px;
                height: 42px;
                object-fit: contain;
                border-radius: 6px;
                border: 1px solid var(--tg-gold);
            }
            .admin-sidebar .brand h2 {
                color: var(--tg-gold);
                font-size: 1.15rem;
                margin: 0;
                letter-spacing: 0.5px;
            }
            .admin-sidebar .brand p {
                color: #a1a1aa;
                font-size: 0.75rem;
                margin: 2px 0 0 0;
                text-transform: uppercase;
            }
            .admin-sidebar .menu {
                list-style: none;
                padding: 15px 0;
                margin: 0;
                flex: 1;
            }
            .admin-sidebar .menu li a {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 13px 22px;
                color: #e2e8f0;
                text-decoration: none;
                font-size: 0.92rem;
                font-weight: 500;
                border-left: 4px solid transparent;
                transition: all 0.2s ease;
            }
            .admin-sidebar .menu li a i {
                width: 20px;
                text-align: center;
                color: var(--tg-gold);
                transition: color 0.2s ease;
            }
            .admin-sidebar .menu li.active a,
            .admin-sidebar .menu li a:hover {
                background-color: var(--tg-gold);
                color: var(--tg-dark);
                font-weight: 700;
                border-left-color: #ffffff;
            }
            .admin-sidebar .menu li.active a i,
            .admin-sidebar .menu li a:hover i {
                color: var(--tg-dark);
            }
            .admin-sidebar .sidebar-footer {
                padding: 15px 20px;
                border-top: 1px solid #2a2a2a;
            }
            .btn-logout-admin {
                width: 100%;
                background-color: #262626;
                color: #f87171;
                border: 1px solid #3f3f46;
                padding: 10px 14px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: all 0.2s;
            }
            .btn-logout-admin:hover {
                background-color: #dc2626;
                color: #ffffff;
                border-color: #dc2626;
            }

            /* Contenedor Principal Admin */
            .admin-main {
                flex: 1;
                display: flex;
                flex-direction: column;
                min-width: 0;
                overflow-x: hidden;
            }
            .admin-topbar {
                background-color: var(--tg-dark);
                color: #ffffff;
                padding: 14px 28px;
                border-bottom: 2px solid var(--tg-gold);
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 15px;
            }
            .admin-topbar-left {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .menu-toggle-btn {
                display: none;
                background: var(--tg-gold);
                color: var(--tg-dark);
                border: none;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 1.1rem;
                cursor: pointer;
                font-weight: 700;
            }
            .admin-topbar-title {
                font-size: 1.15rem;
                font-weight: 700;
                color: var(--tg-gold);
            }
            .admin-user-badge {
                display: flex;
                align-items: center;
                gap: 10px;
                background: #242424;
                padding: 6px 14px;
                border-radius: 20px;
                border: 1px solid var(--tg-gold);
                font-size: 0.88rem;
                color: #ffffff;
            }
            .admin-user-badge i {
                color: var(--tg-gold);
            }
            .admin-content-body {
                padding: 25px 30px;
                flex: 1;
            }

            /* Utilidades y Componentes Tenebrios Gold */
            .tg-card {
                background: var(--tg-card);
                border-radius: 10px;
                padding: 22px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.06);
                border-top: 3px solid var(--tg-gold);
                margin-bottom: 22px;
            }
            .tg-btn-gold {
                background-color: var(--tg-gold);
                color: var(--tg-dark);
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9rem;
                transition: background 0.2s;
            }
            .tg-btn-gold:hover {
                background-color: var(--tg-gold-hover);
                color: var(--tg-dark);
            }
            .tg-btn-dark {
                background-color: var(--tg-dark);
                color: var(--tg-gold);
                border: 1px solid var(--tg-gold);
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 700;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9rem;
                transition: all 0.2s;
            }
            .tg-btn-dark:hover {
                background-color: var(--tg-gold);
                color: var(--tg-dark);
            }
            .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .custom-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.88rem;
            }
            .custom-table thead tr {
                background-color: var(--tg-dark);
                color: var(--tg-gold);
            }
            .custom-table th {
                padding: 12px 14px;
                text-align: left;
                font-weight: 700;
                color: var(--tg-gold);
                border-bottom: 2px solid var(--tg-gold);
                white-space: nowrap;
            }
            .custom-table td {
                padding: 12px 14px;
                border-bottom: 1px solid #e2e8f0;
                color: #1e293b;
                vertical-align: middle;
            }
            .custom-table tbody tr:hover {
                background-color: #fef9ec;
            }
            .alert-flash {
                padding: 14px 18px;
                border-radius: 8px;
                margin-bottom: 20px;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }
            .alert-success {
                background-color: #dcfce7;
                color: #15803d;
                border-left: 5px solid #16a34a;
            }
            .alert-error {
                background-color: #fee2e2;
                color: #b91c1c;
                border-left: 5px solid #dc2626;
            }
            .alert-warning {
                background-color: #fef3c7;
                color: #92400e;
                border-left: 5px solid #d97706;
            }
            .badge {
                padding: 4px 10px;
                border-radius: 12px;
                font-size: 0.76rem;
                font-weight: 700;
                display: inline-block;
            }
            .badge-active { background-color: #dcfce7; color: #15803d; }
            .badge-warning { background-color: #fef3c7; color: #92400e; }
            .badge-danger { background-color: #fee2e2; color: #b91c1c; }
            .badge-info { background-color: #e0f2fe; color: #0369a1; }

            /* Responsividad Total para Móviles y Tablets */
            @media (max-width: 992px) {
                body.admin-layout {
                    flex-direction: column;
                }
                .admin-sidebar {
                    width: 100%;
                    border-right: none;
                    border-bottom: 3px solid var(--tg-gold);
                    display: none;
                }
                .admin-sidebar.open {
                    display: flex;
                }
                .menu-toggle-btn {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
                .admin-content-body {
                    padding: 18px 15px;
                }
                .admin-topbar {
                    padding: 12px 15px;
                }
            }
        </style>
    @else
        <!-- Estilos Globales Tienda Pública Tenebrios Gold (#151515 y #e4b353) -->
        <link rel="stylesheet" href="{{ asset('assets/css/index_style.css') }}">
    @endif
    @stack('styles')
</head>
<body class="{{ request()->is('admin*') ? 'admin-layout' : '' }}">

    @if(request()->is('admin*'))
        <!-- ========================================================== -->
        <!-- MODO PANEL ADMINISTRATIVO UNIFICADO (BIO-GEST)             -->
        <!-- ========================================================== -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="brand">
                <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="Logo Tenebrios Gold" onerror="this.style.display='none'">
                <div>
                    <h2>TENEBRIOS GOLD</h2>
                    <p>BIO-GEST Admin</p>
                </div>
            </div>

            <ul class="menu">
                <li class="{{ request()->routeIs('admin.inicio') ? 'active' : '' }}">
                    <a href="{{ route('admin.inicio') }}">
                        <i class="fa-solid fa-gauge-high"></i> Panel Principal
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.usuarios.index') }}">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.productos.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.productos.index') }}">
                        <i class="fa-solid fa-box-open"></i> Productos
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.inventario.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.inventario.index') }}">
                        <i class="fa-solid fa-warehouse"></i> Inventario y Kardex
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.pedidos.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.pedidos.index') }}">
                        <i class="fa-solid fa-cart-flatbed"></i> Pedidos
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.reportes.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.reportes.ventas') }}">
                        <i class="fa-solid fa-chart-line"></i> Reportes y CSV
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}">
                        <i class="fa-solid fa-store"></i> Ver Tienda Cliente
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout-admin">
                        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="menu-toggle-btn" onclick="document.getElementById('adminSidebar').classList.toggle('open')" aria-label="Menú">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="admin-topbar-title">
                        @yield('header_title', 'Panel de Administración BIO-GEST')
                    </div>
                </div>
                <div class="admin-user-badge">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>{{ auth()->user()->nombre_completo ?? 'Administrador' }}</span>
                </div>
            </header>

            <main class="admin-content-body">
                @if(session('success'))
                    <div class="alert-flash alert-success">
                        <span><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-flash alert-error">
                        <span><i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-flash alert-error" style="flex-direction:column; align-items:flex-start;">
                        <div><i class="fa-solid fa-circle-exclamation"></i> <strong>Por favor corrige los siguientes errores:</strong></div>
                        <ul style="margin:6px 0 0 20px; padding:0;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    @else
        <!-- ========================================================== -->
        <!-- MODO TIENDA PÚBLICA Y CLIENTE (TENEBRIOS GOLD)             -->
        <!-- ========================================================== -->
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

        @if(session('success'))
            <div style="background:#dcfce7; color:#15803d; padding:12px 5%; text-align:center; font-weight:600; border-bottom:2px solid #86efac;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fee2e2; color:#b91c1c; padding:12px 5%; text-align:center; font-weight:600; border-bottom:2px solid #fca5a5;">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        <main>
            @yield('content')
        </main>

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
    @endif

    @stack('scripts')

    @auth
    <script>
        (function () {
            const checkUrl = "{{ route('auth.session.status') }}";
            const loginUrl = "{{ route('login') }}";
            setInterval(async function () {
                try {
                    const res = await fetch(checkUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.status === 401) {
                        window.location.href = loginUrl + '?deactivated=1';
                        return;
                    }
                    const data = await res.json();
                    if (data && data.active === false) {
                        window.location.href = (data.redirect || loginUrl) + '?deactivated=1';
                    }
                } catch (e) {
                    // Ignorar errores de red temporales
                }
            }, 4000);
        })();
    </script>
    @endauth
</body>
</html>
