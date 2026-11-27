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

            /* Transiciones y Microinteracciones Suaves */
            .tg-card,
            .kpi-card,
            .tg-btn-gold,
            .tg-btn-dark,
            .tg-btn-outline,
            .action-item,
            .main-nav ul li a,
            .alert-flash {
                transition: all 0.22s ease !important;
            }

            .tg-card:hover,
            .kpi-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            }

            @keyframes tgFadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .alert-flash {
                animation: tgFadeInUp 0.22s ease forwards;
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
                <li class="{{ request()->is('admin/perfil*') ? 'active' : '' }}">
                    <a href="{{ route('admin.perfil.index') }}">
                        <i class="fa-solid fa-user-gear"></i> Mi Perfil
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
                <a href="{{ route('admin.perfil.index') }}" class="admin-user-badge" style="text-decoration:none;" title="Ir a Mi Perfil de Administrador">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>{{ auth()->user()->nombre_completo ?? 'Administrador' }}</span>
                    <i class="fa-solid fa-pen-to-square" style="font-size:0.78rem; margin-left:4px;"></i>
                </a>
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

            <!-- Buscador General con Desplegable de Sugerencias en Tiempo Real -->
            <div class="search-container" style="position:relative; width:600px;">
                <form action="{{ route('catalogo.index') }}" method="GET" class="search-bar" id="headerSearchForm" autocomplete="off" style="width:100%;">
                    <input type="text" name="q" id="headerSearchInput" value="{{ request('q') }}" placeholder="Buscar larvas, pie de cría, kits..." autocomplete="off">
                    <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
                </form>

                <div id="searchSuggestionsDropdown" class="search-suggestions-dropdown" style="display:none; position:absolute; top:calc(100% + 8px); left:0; width:100%; min-width:520px; max-width:650px; background:#ffffff; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.22); border:1px solid #e5e7eb; z-index:99999; max-height:520px; overflow-y:auto;">
                    <!-- Rellenado dinámicamente vía JS -->
                </div>
            </div>

            <div class="user-actions">
                @auth
                    @php
                        $userHeaderRoute = auth()->user()->esAdmin() ? route('admin.inicio') : route('cliente.cuenta');
                    @endphp
                    <a href="{{ $userHeaderRoute }}" class="action-item" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:8px;" title="Mi Cuenta">
                        <i class="far fa-user"></i>
                        <span>Hola, {{ strtok(auth()->user()->nombre, ' ') }}</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0;">
                        @csrf
                        <button type="submit" class="action-item" style="background:none; border:none; font-size:0.9rem; cursor:pointer; color:inherit; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="action-item" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:8px;" title="Iniciar sesión">
                        <i class="far fa-user"></i>
                        <span>Iniciar sesión</span>
                    </a>
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
                <li><a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">Preguntas Frecuentes</a></li>
                <li><a href="{{ route('contacto.index') }}" class="{{ request()->routeIs('contacto.*') ? 'active' : '' }}">Contacto</a></li>
                @auth
                    @if(!auth()->user()->esAdmin())
                        <li><a href="{{ route('cliente.chat.index') }}" class="{{ request()->routeIs('cliente.chat.*') ? 'active' : '' }}"><i class="fas fa-comments"></i> Chat en Vivo</a></li>
                    @endif
                @endauth
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
                        <a href="https://www.facebook.com/p/Tenebrios-Gold-61582875604473/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook Tenebrios Gold"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/tenebriosgold/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" title="Instagram Tenebrios Gold"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.tiktok.com/@tenebriosgold?is_from_webapp=1&sender_device=pc" target="_blank" rel="noopener noreferrer" aria-label="TikTok" title="TikTok Tenebrios Gold"><i class="fab fa-tiktok"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=573243007206&token=eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IjEyNSJ9.eyJleHAiOjE3OTU3MjEzNzEsInBob25lIjoiNTczMjQzMDA3MjA2IiwidGV4dCI6IiIsImNvbnRleHQiOiJBZmlBUlVBNV9HR21aZk8yRGVvczA0dEphUVB6MUxhMGtsdGdxdnV2TGQzQ2d2X19mYTBUTDMycGIwWDNRUTlXQWJJelEwT3M3SWMwb0lmWm81UlFudFFLenFmSkp4cVdWQWhBYk1TUXZrYUZGeGZCMGUxNWx3NEZyMEJqZ1lUeDBNVzRlUHB2UVlVSlBsa3o0bFVkSmFDTk9RIiwic291cmNlIjoiIiwiYXBwIjoiZmFjZWJvb2sifQ.Yz93kGA0GPrWyPWuHJfcjBA4RxazwiHBpXRtspQhxOd6UyuV5_jFsnLFHy-UWWCvIt3f2HderMNYV9Hpu7pXSQ&fbclid=IwVERDUAV0FnhleHRuA2FlbQIxMABwZG9mBXNydGMGYXBwX2lkDDM1MDY4NTUzMTcyOAABHwYd_5LL7FnP5LFtxCvKcKyIEiVPiHBnpJYJwAdBDjAvuE8oqCg0yWFx_aSc_aem_NnQ69G1mRvEqJxuHiJrRrA" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp Tenebrios Gold"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Tenebrios Gold. Todos los derechos reservados.</p>
            </div>
        </footer>
    @endif

    <!-- Contenedor Global de Notificaciones Toast -->
    <div id="bioToastContainer" style="position:fixed; bottom:24px; right:24px; z-index:999999; display:flex; flex-direction:column; gap:10px; pointer-events:none;"></div>

    @stack('scripts')

    <script>
        // Función global para alternar visibilidad de contraseñas y clase de ícono (fa-eye / fa-eye-slash)
        window.togglePasswordVisibility = function (inputId, iconEl) {
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
        };

        // Función global de notificaciones Toast (BIO-GEST / Tenebrios Gold)
        window.showToast = function (message, type = 'success') {
            const container = document.getElementById('bioToastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.style.pointerEvents = 'auto';
            toast.style.background = '#151515';
            toast.style.color = '#ffffff';
            toast.style.border = type === 'error' ? '1px solid #ef4444' : '1px solid #e4b353';
            toast.style.borderRadius = '8px';
            toast.style.padding = '12px 18px';
            toast.style.boxShadow = '0 8px 24px rgba(0,0,0,0.3)';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '10px';
            toast.style.fontSize = '0.92rem';
            toast.style.fontWeight = '600';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(15px)';
            toast.style.transition = 'all 0.25s ease';

            const iconClass = type === 'error' ? 'fa-circle-xmark' : (type === 'info' ? 'fa-circle-info' : 'fa-circle-check');
            const iconColor = type === 'error' ? '#ef4444' : '#e4b353';

            toast.innerHTML = `<i class="fa-solid ${iconClass}" style="color:${iconColor}; font-size:1.15rem;"></i><span>${message}</span>`;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(15px)';
                setTimeout(() => toast.remove(), 250);
            }, 3500);
        };

        // Buscador General en Tiempo Real con Desplegable (Debounce 250ms)
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('headerSearchInput');
            const dropdown = document.getElementById('searchSuggestionsDropdown');
            if (searchInput && dropdown) {
                let debounceTimer = null;

                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    const query = this.value.trim();

                    if (query.length < 2) {
                        dropdown.style.display = 'none';
                        dropdown.innerHTML = '';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`/buscar-sugerencias?q=${encodeURIComponent(query)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            renderSuggestions(data, query);
                        })
                        .catch(err => {
                            console.error('Error fetching suggestions:', err);
                        });
                    }, 250);
                });

                // Cerrar al hacer clic fuera
                document.addEventListener('click', function (e) {
                    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.style.display = 'none';
                    }
                });

                // Reabrir al enfocar si tiene contenido
                searchInput.addEventListener('focus', function () {
                    if (this.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
                        dropdown.style.display = 'block';
                    }
                });

                // Cerrar con Escape
                searchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        dropdown.style.display = 'none';
                    }
                });

                function renderSuggestions(data, query) {
                    if (!data || data.total === 0) {
                        dropdown.innerHTML = `
                            <div style="padding:18px; text-align:center; color:#64748b; font-size:0.9rem;">
                                <i class="fas fa-search" style="font-size:1.4rem; color:#cbd5e1; margin-bottom:8px; display:block;"></i>
                                No se encontraron coincidencias para "<strong>${escapeHtml(query)}</strong>"
                            </div>
                        `;
                        dropdown.style.display = 'block';
                        return;
                    }

                    let html = '';

                    // 1. Productos Activos
                    if (data.productos && data.productos.length > 0) {
                        html += `<div style="padding:8px 14px 4px; font-size:0.75rem; font-weight:800; color:#e4b353; background:#151515; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-box-open"></i> Productos (${data.productos.length})
                        </div>`;
                        data.productos.forEach(prod => {
                            html += `
                                <a href="${prod.url}" style="display:flex; align-items:center; gap:12px; padding:10px 14px; text-decoration:none; color:#151515; border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#fdf8f0'" onmouseout="this.style.background='transparent'">
                                    <img src="${prod.imagen}" alt="${escapeHtml(prod.titulo)}" style="width:40px; height:40px; object-fit:contain; border-radius:6px; background:#f8fafc; border:1px solid #e2e8f0; flex-shrink:0;" onerror="this.src='/assets/img/Tenebrios_img.png'">
                                    <div style="flex:1; min-width:0;">
                                        <div style="font-weight:700; font-size:0.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(prod.titulo)}</div>
                                        <div style="font-size:0.8rem; color:#059669; font-weight:700;">${prod.subtitulo}</div>
                                    </div>
                                    <i class="fas fa-chevron-right" style="font-size:0.75rem; color:#cbd5e1;"></i>
                                </a>
                            `;
                        });
                    }

                    // 2. Preguntas Frecuentes (FAQ)
                    if (data.faqs && data.faqs.length > 0) {
                        html += `<div style="padding:8px 14px 4px; font-size:0.75rem; font-weight:800; color:#e4b353; background:#151515; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-circle-question"></i> Preguntas Frecuentes (${data.faqs.length})
                        </div>`;
                        data.faqs.forEach(faq => {
                            html += `
                                <a href="${faq.url}" style="display:block; padding:10px 14px; text-decoration:none; color:#151515; border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#fdf8f0'" onmouseout="this.style.background='transparent'">
                                    <div style="font-weight:700; font-size:0.86rem; color:#0f172a;"><i class="fas fa-question" style="color:#e4b353; margin-right:5px; font-size:0.8rem;"></i>${escapeHtml(faq.titulo)}</div>
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:2px; line-height:1.3;">${escapeHtml(faq.subtitulo)}</div>
                                </a>
                            `;
                        });
                    }

                    // 3. Políticas de Envío (/envios)
                    if (data.envios && data.envios.length > 0) {
                        html += `<div style="padding:8px 14px 4px; font-size:0.75rem; font-weight:800; color:#e4b353; background:#151515; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-truck-fast"></i> Políticas de Envío (${data.envios.length})
                        </div>`;
                        data.envios.forEach(env => {
                            html += `
                                <a href="${env.url}" style="display:block; padding:10px 14px; text-decoration:none; color:#151515; border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#fdf8f0'" onmouseout="this.style.background='transparent'">
                                    <div style="font-weight:700; font-size:0.86rem; color:#0f172a;"><i class="fas fa-shield-halved" style="color:#e4b353; margin-right:5px; font-size:0.8rem;"></i>${escapeHtml(env.titulo)}</div>
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:2px; line-height:1.3;">${escapeHtml(env.subtitulo)}</div>
                                </a>
                            `;
                        });
                    }

                    // 4. Información Institucional (/nosotros)
                    if (data.institucional && data.institucional.length > 0) {
                        html += `<div style="padding:8px 14px 4px; font-size:0.75rem; font-weight:800; color:#e4b353; background:#151515; text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:6px;">
                            <i class="fas fa-building"></i> Institucional (${data.institucional.length})
                        </div>`;
                        data.institucional.forEach(inst => {
                            html += `
                                <a href="${inst.url}" style="display:block; padding:10px 14px; text-decoration:none; color:#151515; border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#fdf8f0'" onmouseout="this.style.background='transparent'">
                                    <div style="font-weight:700; font-size:0.86rem; color:#0f172a;"><i class="fas fa-seedling" style="color:#e4b353; margin-right:5px; font-size:0.8rem;"></i>${escapeHtml(inst.titulo)}</div>
                                    <div style="font-size:0.78rem; color:#64748b; margin-top:2px; line-height:1.3;">${escapeHtml(inst.subtitulo)}</div>
                                </a>
                            `;
                        });
                    }

                    // Enlace inferior a catálogo completo
                    html += `
                        <a href="/catalogo?q=${encodeURIComponent(query)}" style="display:block; padding:11px 14px; background:#f8fafc; color:#151515; text-decoration:none; font-size:0.84rem; font-weight:700; text-align:center; border-top:1px solid #e2e8f0; transition:background 0.15s;" onmouseover="this.style.background='#e4b353';" onmouseout="this.style.background='#f8fafc';">
                            Ver todos los resultados en el catálogo <i class="fas fa-arrow-right" style="margin-left:4px;"></i>
                        </a>
                    `;

                    dropdown.innerHTML = html;
                    dropdown.style.display = 'block';
                }

                function escapeHtml(str) {
                    if (!str) return '';
                    return str
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/"/g, "&quot;")
                        .replace(/'/g, "&#039;");
                }
            }
        });
    </script>
    <script>
        // Bloqueo en tiempo real de números en campos de solo letras y de letras en campos numéricos
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[data-only-letters="true"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s.'-]/g, '').replace(/\s{2,}/g, ' ');
                });
            });
            document.querySelectorAll('input[data-only-digits]').forEach(function (input) {
                const maxLen = parseInt(input.getAttribute('data-only-digits'), 10) || 10;
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, maxLen);
                });
            });
        });

        window.addEventListener('storage', event => {
            if (event.key === 'biogest_product_catalog_sync') window.location.reload();
        });
    </script>

    @auth
    <script>
        (function () {
            const checkUrl = "{{ route('auth.session.status') }}";
            const homeUrl = "{{ route('home') }}";
            setInterval(async function () {
                try {
                    const res = await fetch(checkUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = homeUrl;
                        return;
                    }
                    const data = await res.json();
                    if (data && data.active === false) {
                        window.location.href = data.redirect || homeUrl;
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
