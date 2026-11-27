<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenebrios Gold | Panel Admin')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Estilos Oficiales Admin Tenebrios Gold -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ file_exists(public_path('assets/css/admin.css')) ? filemtime(public_path('assets/css/admin.css')) : time() }}">

    <style>
        /* Alta especificidad para Menú Lateral Blanco Tenebrios Gold */
        html body .admin-layout-wrapper .admin-sidebar,
        html body #adminSidebar.admin-sidebar {
            background-color: #ffffff !important;
            color: #111111 !important;
            border-right: 1px solid #e5e7eb !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-nav-container,
        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu {
            background-color: #ffffff !important;
            color: #111111 !important;
        }

        /* ÚNICAMENTE el bloque del logo permanece en negro #111111 */
        html body .admin-layout-wrapper .admin-sidebar .sidebar-brand-block {
            background-color: #111111 !important;
            border-bottom: 2px solid #e6a100 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-brand-block .brand-title {
            color: #ffffff !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-brand-block .brand-subtitle {
            color: #e6a100 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li a {
            color: #111111 !important;
            background-color: transparent !important;
            border-left: 3px solid transparent !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li a i {
            color: #4b5563 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li a:hover {
            background-color: #f3f4f6 !important;
            color: #111111 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li a:hover i {
            color: #e6a100 !important;
        }

        /* Ítem Activo: fondo crema #fdf8f0, texto dorado #e6a100, borde izquierdo dorado 3px */
        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li.active a,
        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li.active a:hover {
            background-color: #fdf8f0 !important;
            color: #e6a100 !important;
            border-left: 3px solid #e6a100 !important;
            font-weight: 700 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-menu li.active a i {
            color: #e6a100 !important;
        }

        html body .admin-layout-wrapper .admin-sidebar .sidebar-footer {
            background-color: #ffffff !important;
            color: #6b7280 !important;
            border-top: 1px solid #e5e7eb !important;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="admin-layout-wrapper">
        <!-- 1. BARRA LATERAL IZQUIERDA BLANCA CON BLOQUE COMPACTO OSCURO #111111 -->
        <aside class="admin-sidebar" id="adminSidebar">
            <!-- Bloque superior compacto oscuro #111111 para el logo -->
            <div class="sidebar-brand-block">
                <img src="{{ asset('assets/img/LOGO PNG.png') }}" alt="Logo Tenebrios Gold" class="sidebar-logo" onerror="this.onerror=null; this.src='{{ asset('assets/img/Logo.jpeg') }}';">
                <div class="brand-text">
                    <span class="brand-title">TENEBRIOS GOLD</span>
                    <span class="brand-subtitle">Panel Admin</span>
                </div>
                <button type="button" class="sidebar-close-btn" id="adminSidebarClose" aria-label="Cerrar menú lateral">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <!-- Menú de Navegación con rutas reales -->
            <div class="sidebar-nav-container">
                <div class="sidebar-menu-title">Menú Administrativo</div>
                <ul class="sidebar-menu">
                    <!-- 1. Inicio -->
                    <li class="{{ request()->is('admin/inicio*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/inicio') }}">
                            <i class="fa-solid fa-gauge"></i>
                            <span>Inicio</span>
                        </a>
                    </li>

                    <!-- 2. Estadísticas (Unifica Estadísticas y Reportes) -->
                    <li class="{{ (request()->is('admin/estadisticas*') || request()->is('admin/reportes*')) ? 'active' : '' }}">
                        <a href="{{ url('/admin/estadisticas') }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Estadísticas</span>
                        </a>
                    </li>

                    <!-- 3. Pedidos -->
                    <li class="{{ request()->is('admin/pedidos*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/pedidos') }}">
                            <i class="fa-solid fa-receipt"></i>
                            <span>Pedidos</span>
                        </a>
                    </li>

                    <!-- 4. Productos -->
                    <li class="{{ request()->is('admin/productos*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/productos') }}">
                            <i class="fa-solid fa-cubes"></i>
                            <span>Productos</span>
                        </a>
                    </li>

                    <!-- 5. Inventario -->
                    <li class="{{ (request()->is('admin/inventario*') && !request()->is('admin/alertas-stock*')) ? 'active' : '' }}">
                        <a href="{{ url('/admin/inventario') }}">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span>Inventario</span>
                        </a>
                    </li>

                    <!-- 6. Alertas Stock con badge rojo dinámico -->
                    <li class="{{ request()->is('admin/alertas-stock*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/alertas-stock') }}">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Alertas Stock</span>
                            @if(isset($conteoAlertasStock) && $conteoAlertasStock > 0)
                                <span class="badge-stock-danger" title="{{ $conteoAlertasStock }} producto(s) en o por debajo de su stock mínimo">{{ $conteoAlertasStock }}</span>
                            @else
                                <span class="badge-stock-zero" title="Sin alertas de stock bajo">0</span>
                            @endif
                        </a>
                    </li>

                    <!-- 7. Promociones -->
                    <li class="{{ request()->is('admin/promociones*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/promociones') }}">
                            <i class="fa-solid fa-tags"></i>
                            <span>Promociones</span>
                        </a>
                    </li>

                    <!-- 8. Chats -->
                    <li class="{{ request()->is('admin/chats*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/chats') }}">
                            <i class="fa-solid fa-comments"></i>
                            <span>Chats</span>
                        </a>
                    </li>

                    <!-- 9. Usuarios -->
                    <li class="{{ request()->is('admin/usuarios*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/usuarios') }}">
                            <i class="fa-solid fa-users"></i>
                            <span>Usuarios</span>
                        </a>
                    </li>

                    <!-- 10. Seguridad -->
                    <li class="{{ request()->is('admin/seguridad*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/seguridad') }}">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Seguridad</span>
                        </a>
                    </li>

                    <!-- 11. Ajustes -->
                    <li class="{{ request()->is('admin/ajustes*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/ajustes') }}">
                            <i class="fa-solid fa-gear"></i>
                            <span>Ajustes</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Pie de barra lateral -->
            <div class="sidebar-footer">
                <div>
                    <strong style="color:#111111;">BIO-GEST</strong> · 2026<br>
                    <small>Tenebrios Gold S.A.S.</small>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-logout-nav" title="Cerrar sesión">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

        <!-- 2. CONTENIDO PRINCIPAL Y TOPBAR -->
        <div class="admin-main">
            <!-- Topbar Blanca Superior -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button type="button"
                            class="menu-toggle-btn"
                            id="adminMenuToggle"
                            aria-label="Abrir Menú"
                            aria-expanded="false"
                            aria-controls="adminSidebar">
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>
                    <div class="topbar-title-block">
                        <h1>@yield('header_title', 'TENEBRIOS GOLD · Panel Administrativo')</h1>
                    </div>
                </div>

                <div class="topbar-right">
                    <a href="{{ route('home') }}" target="_blank" class="btn-store-link" title="Abrir tienda de cara al cliente" aria-label="Ver Tienda">
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        <span class="topbar-action-label">Ver Tienda</span>
                    </a>

                    <a href="{{ route('admin.perfil.index') }}" class="admin-user-pill" title="Mi Perfil ({{ auth()->user()->nombre ?? 'Administrador' }})" aria-label="Mi Perfil de {{ auth()->user()->nombre ?? 'Administrador' }}">
                        <span class="avatar">
                            {{ strtoupper(substr(auth()->user()->nombre ?? 'A', 0, 1)) }}
                        </span>
                        <span class="topbar-user-name">{{ auth()->user()->nombre ?? 'Administrador' }}</span>
                    </a>

                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-logout-nav" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <i class="fa-solid fa-power-off" aria-hidden="true"></i>
                            <span class="topbar-action-label">Salir</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Contenido Dinámico de la Página -->
            <main class="admin-content">
                @if(session('success'))
                    <div class="alert-box alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-box alert-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Control accesible del sidebar móvil -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('adminMenuToggle');
            const closeBtn = document.getElementById('adminSidebarClose');
            const backdrop = document.getElementById('adminSidebarBackdrop');

            if (!sidebar || !toggleBtn || !backdrop) return;

            const setSidebarOpen = (isOpen) => {
                sidebar.classList.toggle('open', isOpen);
                backdrop.classList.toggle('open', isOpen);
                document.body.classList.toggle('admin-sidebar-open', isOpen);
                toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            };

            toggleBtn.addEventListener('click', () => {
                setSidebarOpen(!sidebar.classList.contains('open'));
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    setSidebarOpen(false);
                    toggleBtn.focus();
                });
            }

            backdrop.addEventListener('click', () => setSidebarOpen(false));

            sidebar.querySelectorAll('.sidebar-menu a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        setSidebarOpen(false);
                    }
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                    setSidebarOpen(false);
                    toggleBtn.focus();
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 768 && sidebar.classList.contains('open')) {
                    setSidebarOpen(false);
                }
            });
        });
    </script>

    <!-- Scripts adicionales por vista -->
    @stack('scripts')
</body>
</html>
