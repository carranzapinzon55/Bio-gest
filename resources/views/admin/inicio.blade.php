<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bio-Gest | Panel de Administrador - Tenebrios Gold</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <style>
        /* Acentos corporativos Tenebrios-Gold (#151515 y #e4b353) */
        .sidebar { background-color: #151515; color: #ffffff; border-right: 2px solid #e4b353; }
        .sidebar .brand { border-bottom: 1px solid #2d2d2d; }
        .sidebar .brand h2 { color: #e4b353; }
        .sidebar .brand p { color: #aaaaaa; }
        .sidebar .menu li a { color: #e2e8f0; }
        .sidebar .menu li.active a, .sidebar .menu li a:hover {
            background-color: #e4b353;
            color: #151515;
            font-weight: 700;
        }
        .kpi-icon { color: #151515; background-color: rgba(228, 179, 83, 0.25); }
    </style>
</head>
<body>

    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="sidebar">
        <div class="brand">
            <h2>Tenebrios Gold</h2>
            <p>panel de administrador</p>
        </div>
        <ul class="menu">
            <li class="active"><a href="{{ route('admin.inicio') }}"><i class="fa-solid fa-house"></i> Inicio</a></li>
            <li><a href="{{ route('admin.productos.index') }}"><i class="fa-solid fa-box"></i> Productos e Inventario</a></li>
            <li><a href="{{ route('admin.pedidos.index') }}"><i class="fa-solid fa-cart-shopping"></i> Pedidos</a></li>
            <li><a href="{{ route('home') }}"><i class="fa-solid fa-store"></i> Ver Tienda Pública</a></li>
            <li>
                <form action="{{ route('logout') }}" method="POST" style="padding: 12px 20px;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:#f87171; cursor:pointer; font-weight:600;">
                        <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">

        <!-- HEADER SUPERIOR -->
        <header class="top-header">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Buscar pedido, cliente o producto...">
            </div>
            <div class="user-profile">
                <i class="fa-solid fa-bell notifications"></i>
                <div class="profile-info">
                    <i class="fa-circle-user fa-solid"></i>
                    <span>{{ auth()->user()->nombre_completo ?? 'Administrador' }}</span>
                </div>
            </div>
        </header>

        <!-- TÍTULO Y BIENVENIDA -->
        <section class="welcome-title" style="margin-bottom: 20px;">
            <h1>Bienvenido, {{ auth()->user()->nombre ?? 'Administrador' }}</h1>
            <p>Gestiona todo el sistema de TENEBRIOS GOLD desde aquí</p>
        </section>

        <!-- TARJETAS DE MÉTRICAS (KPIs) -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <i class="fa-solid fa-user-group kpi-icon"></i>
                <div class="kpi-details">
                    <h3>total de usuarios</h3>
                    <div class="kpi-value">{{ $kpis['total_usuarios'] }}</div>
                    <span class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> Registrados en BIO-GEST</span>
                </div>
            </div>
            <div class="kpi-card">
                <i class="fa-solid fa-boxes-packing kpi-icon"></i>
                <div class="kpi-details">
                    <h3>Pedidos pendientes</h3>
                    <div class="kpi-value">{{ $kpis['pedidos_pendientes'] }}</div>
                    <span class="kpi-trend up"><i class="fa-solid fa-clock"></i> Por despachar</span>
                </div>
            </div>
            <div class="kpi-card">
                <i class="fa-solid fa-truck-fast kpi-icon"></i>
                <div class="kpi-details">
                    <h3>pedidos realizados</h3>
                    <div class="kpi-value">{{ $kpis['total_pedidos'] }}</div>
                    <span class="kpi-subtext">histórico acumulado</span>
                </div>
            </div>
            <div class="kpi-card">
                <i class="fa-solid fa-sack-dollar kpi-icon"></i>
                <div class="kpi-details">
                    <h3>Ingresos acumulados</h3>
                    <div class="kpi-value">${{ number_format($kpis['ingresos_totales'], 0, ',', '.') }}</div>
                    <span class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> Ventas activas</span>
                </div>
            </div>
        </section>

        <!-- REJILLA DE WIDGETS Y TABLAS -->
        <section class="dashboard-grid">

            <!-- WIDGET 1: USUARIOS -->
            <div class="widget-card">
                <div class="widget-header">
                    <h2>Gestión de usuarios</h2>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($usuariosRecientes as $usr)
                            <tr>
                                <td>USR-{{ str_pad($usr->id_usuario, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $usr->nombre_completo }}</td>
                                <td>{{ optional($usr->rol)->nombre_rol }}</td>
                                <td><span class="badge badge-active">{{ $usr->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- WIDGET 2: PRODUCTOS -->
            <div class="widget-card">
                <div class="widget-header">
                    <h2>Productos</h2>
                    <a href="{{ route('admin.productos.index') }}">Ver todos</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr><th>ID</th><th>Producto</th><th>Precio</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            <tr>
                                <td>PROD-{{ str_pad($prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($prod->nombre_producto, 24) }}</td>
                                <td>${{ number_format($prod->precio, 0, ',', '.') }}</td>
                                <td><span class="badge badge-active">{{ $prod->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- WIDGET 3: PROMOCIONES -->
            <div class="widget-card">
                <div class="widget-header">
                    <h2>Promociones activas</h2>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr><th>Cupón</th><th>Descuento</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($promociones as $promo)
                            <tr>
                                <td><strong>{{ $promo->codigo_cupon }}</strong></td>
                                <td>{{ number_format($promo->descuento_porcentaje, 0) }}%</td>
                                <td><span class="badge badge-active">{{ $promo->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- WIDGET 4: PEDIDOS RECIENTES -->
            <div class="widget-card">
                <div class="widget-header">
                    <h2>Pedidos recientes</h2>
                    <a href="{{ route('admin.pedidos.index') }}">Ver todos</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr><th>ID</th><th>Cliente</th><th>Estado</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($pedidosRecientes as $ped)
                            <tr>
                                <td>{{ $ped->numero_pedido ?? 'PED-'.$ped->id_pedido }}</td>
                                <td>{{ optional($ped->usuario)->nombre_completo }}</td>
                                <td><span class="badge badge-warning">{{ $ped->estado_pedido }}</span></td>
                                <td>${{ number_format($ped->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sin pedidos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- WIDGET 5: INVENTARIO -->
            <div class="widget-card">
                <div class="widget-header">
                    <h2>Inventario</h2>
                    <a href="{{ route('admin.productos.index') }}">Gestionar stock</a>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr><th>ID</th><th>Producto</th><th>Stock</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            <tr>
                                <td>INV-{{ str_pad($prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($prod->nombre_producto, 24) }}</td>
                                <td>{{ $prod->stock }}</td>
                                <td>
                                    @if($prod->stock <= 15)
                                        <span class="badge badge-danger">Bajo</span>
                                    @elseif($prod->stock <= 30)
                                        <span class="badge badge-warning">Medio</span>
                                    @else
                                        <span class="badge badge-active">Óptimo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </section>

    </main>

</body>
</html>
