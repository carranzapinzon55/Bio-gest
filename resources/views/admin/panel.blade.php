<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de administrador | BIO-GEST Tenebrios Gold</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('admin-panel/css/admin.css') }}">
</head>
<body>
    <header class="topbar">
        <button class="icon-btn menu-toggle" data-action="toggleSidebar" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="brand">
            <h1>Tenebrios Gold</h1>
            <small>panel de administrador</small>
        </div>
        <div class="global-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="globalSearch" placeholder="Buscar usuarios, productos, pedidos..." autocomplete="off">
        </div>
        <div class="topbar-actions">
            <button class="icon-btn" data-action="notifications" aria-label="Notificaciones">
                <i class="fa-regular fa-bell"></i>
                <span class="notif-count" id="notifCount" hidden>0</span>
            </button>
            <button class="profile" data-action="profileMenu">
                <span class="avatar-ic"><i class="fa-regular fa-user"></i></span>
                <span>Administrador</span>
                <i class="fa-solid fa-chevron-down small muted"></i>
            </button>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <p class="nav-title">Menú principal</p>
        <nav id="sideNav"></nav>
        <div class="sidebar-foot">
            <strong>BIO-GEST</strong> v1.0<br>
            &copy; Tenebrios Gold
        </div>
    </aside>
    <div class="sidebar-backdrop" data-action="toggleSidebar"></div>
    <main class="content" id="app"></main>
    <div class="toasts" id="toasts"></div>

    <script>
        window.BIOGEST_ADMIN_ASSET_PATH = @json(asset('assets/img') . '/');
        window.BIOGEST_ADMIN_DATA_URL = @json(route('admin.panel.data'));
        window.BIOGEST_ADMIN_ORDER_STATUS_URL = @json(route('admin.pedidos.estado', ['id_pedido' => '__ORDER_ID__']));
        window.BIOGEST_ADMIN_PRODUCT_STORE_URL = @json(route('admin.productos.store'));
        window.BIOGEST_ADMIN_PRODUCT_UPDATE_URL = @json(route('admin.productos.update', ['producto' => '__PRODUCT_ID__']));
        window.BIOGEST_ADMIN_LOGOUT_URL = @json(route('logout'));
        window.BIOGEST_HOME_URL = @json(route('home'));
    </script>
    <script src="{{ asset('admin-panel/js/admin.js') }}"></script>
</body>
</html>
