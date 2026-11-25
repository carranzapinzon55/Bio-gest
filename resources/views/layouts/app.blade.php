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
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <style>
            :root {
                --tg-dark: #111111;
                --tg-gold: #e6a100;
                --tg-gold-hover: #cf9000;
                --tg-bg: #f8f9fa;
                --tg-card: #ffffff;
                --tg-border: #e5e7eb;
                --tg-cream: #fdf8f0;
            }
            body.admin-layout {
                background-color: var(--tg-bg);
                color: #1f2937;
                display: flex;
                min-height: 100vh;
                margin: 0;
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
            /* Sidebar Blanco con Bloque Superior Compacto Oscuro #111111 */
            .admin-sidebar {
                width: 260px;
                background-color: #ffffff;
                color: #1f2937;
                border-right: 1px solid var(--tg-border);
                display: flex;
                flex-direction: column;
                flex-shrink: 0;
                transition: transform 0.3s ease;
                z-index: 1000;
            }
            .admin-sidebar .brand {
                background-color: var(--tg-dark);
                padding: 14px 18px;
                border-bottom: 2px solid var(--tg-gold);
                display: flex;
                align-items: center;
                gap: 12px;
                height: 65px;
                box-sizing: border-box;
            }
            .admin-sidebar .brand img {
                width: 36px;
                height: 36px;
                object-fit: contain;
                border-radius: 6px;
                background: #ffffff;
                padding: 2px;
            }
            .admin-sidebar .brand h2 {
                color: #ffffff;
                font-size: 0.92rem;
                font-weight: 800;
                margin: 0;
                letter-spacing: 0.6px;
                line-height: 1.2;
            }
            .admin-sidebar .brand p {
                color: var(--tg-gold);
                font-size: 0.72rem;
                font-weight: 700;
                margin: 2px 0 0 0;
                text-transform: uppercase;
                letter-spacing: 0.8px;
            }
            .admin-sidebar .menu {
                list-style: none;
                padding: 14px 0;
                margin: 0;
                flex: 1;
                overflow-y: auto;
            }
            .admin-sidebar .menu li a {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 22px;
                color: #4b5563;
                text-decoration: none;
                font-size: 0.88rem;
                font-weight: 500;
                border-left: 3px solid transparent;
                transition: all 0.2s ease;
            }
            .admin-sidebar .menu li a i {
                width: 20px;
                text-align: center;
                color: #9ca3af;
                transition: color 0.2s ease;
            }
            .admin-sidebar .menu li a:hover {
                background-color: #f9fafb;
                color: var(--tg-dark);
            }
            .admin-sidebar .menu li a:hover i {
                color: var(--tg-gold);
            }
            .admin-sidebar .menu li.active a,
            .admin-sidebar .menu li.active a:hover {
                background-color: var(--tg-cream) !important;
                color: var(--tg-gold) !important;
                border-left: 3px solid var(--tg-gold) !important;
                font-weight: 700 !important;
            }
            .admin-sidebar .menu li.active a i,
            .admin-sidebar .menu li.active a:hover i {
                color: var(--tg-gold) !important;
            }
            .admin-sidebar .sidebar-footer {
                padding: 14px 18px;
                border-top: 1px solid var(--tg-border);
                background-color: #ffffff;
            }
            .btn-logout-admin {
                width: 100%;
                background-color: #ffffff;
                color: #dc2626;
                border: 1px solid #fee2e2;
                padding: 9px 14px;
                border-radius: 8px;
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
        <!-- Estilos Globales Tienda Pública Tenebrios Gold (#111111 y #e4b353) -->
        <link rel="stylesheet" href="{{ asset('assets/css/index_style.css') }}?v={{ file_exists(public_path('assets/css/index_style.css')) ? filemtime(public_path('assets/css/index_style.css')) : time() }}">
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
                <li class="{{ request()->is('admin/inicio*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/inicio') }}">
                        <i class="fa-solid fa-gauge"></i> Inicio
                    </a>
                </li>
                <li class="{{ (request()->is('admin/estadisticas*') || request()->is('admin/reportes*')) ? 'active' : '' }}">
                    <a href="{{ url('/admin/estadisticas') }}">
                        <i class="fa-solid fa-chart-line"></i> Estadísticas
                    </a>
                </li>
                <li class="{{ request()->is('admin/pedidos*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/pedidos') }}">
                        <i class="fa-solid fa-receipt"></i> Pedidos
                    </a>
                </li>
                <li class="{{ request()->is('admin/productos*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/productos') }}">
                        <i class="fa-solid fa-cubes"></i> Productos
                    </a>
                </li>
                <li class="{{ (request()->is('admin/inventario*') && !request()->is('admin/alertas-stock*')) ? 'active' : '' }}">
                    <a href="{{ url('/admin/inventario') }}">
                        <i class="fa-solid fa-boxes-stacked"></i> Inventario
                    </a>
                </li>
                <li class="{{ request()->is('admin/alertas-stock*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/alertas-stock') }}">
                        <i class="fa-solid fa-triangle-exclamation"></i> Alertas Stock
                        @if(isset($conteoAlertasStock) && $conteoAlertasStock > 0)
                            <span class="badge-stock-danger">{{ $conteoAlertasStock }}</span>
                        @else
                            <span class="badge-stock-zero">0</span>
                        @endif
                    </a>
                </li>
                <li class="{{ request()->is('admin/promociones*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/promociones') }}">
                        <i class="fa-solid fa-tags"></i> Promociones
                    </a>
                </li>
                <li class="{{ request()->is('admin/chats*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/chats') }}">
                        <i class="fa-solid fa-comments"></i> Chats
                    </a>
                </li>
                <li class="{{ request()->is('admin/usuarios*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/usuarios') }}">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="{{ request()->is('admin/seguridad*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/seguridad') }}">
                        <i class="fa-solid fa-shield-halved"></i> Seguridad
                    </a>
                </li>
                <li class="{{ request()->is('admin/ajustes*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/ajustes') }}">
                        <i class="fa-solid fa-gear"></i> Ajustes
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
            <div class="footer-top">
                <div class="footer-col brand-col">
                    <div class="footer-logo-placeholder">
                        <img src="{{ asset('assets/img/Logo.jpeg') }}" alt="Logo Tenebrios Gold" class="footer-logo">
                    </div>
                    <h4 class="footer-brand">TENEBRIOS <span>GOLD</span></h4>
                    <p>Alimento vivo y pie de cr&iacute;a con garant&iacute;a de llegada viva.</p>
                </div>
                <div class="footer-col info-col">
                    <h4>Informaci&oacute;n</h4>
                    <ul>
                        <li><a href="{{ route('nosotros') }}"><i class="fas fa-chevron-right"></i> &iquest;Qui&eacute;nes somos?</a></li>
                        <li><a href="{{ route('envios') }}"><i class="fas fa-chevron-right"></i> Env&iacute;os y reembolso por mortalidad</a></li>
                        <li><a href="{{ route('faq') }}"><i class="fas fa-chevron-right"></i> Preguntas frecuentes</a></li>
                        <li><a href="{{ route('contacto.index') }}"><i class="fas fa-chevron-right"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col social-col">
                    <h4>S&iacute;guenos</h4>
                    <p>Novedades, cuidados y promociones en nuestras redes.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Tenebrios Gold. Todos los derechos reservados.</p>
            </div>
        </footer>
    @endif

    @stack('scripts')

    <script>
        window.addEventListener('storage', event => {
            if (event.key === 'biogest_product_catalog_sync') window.location.reload();
        });
    </script>

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
