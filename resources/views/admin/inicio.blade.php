@extends('layouts.app')

@section('title', 'Panel Principal | BIO-GEST Tenebrios Gold')
@section('header_title', 'Dashboard General BIO-GEST')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:25px;">
        <div>
            <h1 style="margin:0; font-size:1.6rem; color:#151515;">Bienvenido, {{ auth()->user()->nombre ?? 'Administrador' }}</h1>
            <p style="margin:4px 0 0 0; color:#64748b;">Control integral de productos, inventario, pedidos y reportes de TENEBRIOS GOLD</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.productos.create') }}" class="tg-btn-dark">
                <i class="fa-solid fa-plus"></i> Nuevo Producto
            </a>
            <a href="{{ route('admin.reportes.ventas') }}" class="tg-btn-gold">
                <i class="fa-solid fa-file-csv"></i> Reporte de Ventas
            </a>
        </div>
    </div>

    <!-- TARJETAS DE MÉTRICAS (KPIs) -->
    <section class="kpi-grid">
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-user-group kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div class="kpi-details">
                <h3 style="font-size:0.82rem; text-transform:uppercase; color:#64748b;">Total de Usuarios</h3>
                <div class="kpi-value" style="color:#151515;">{{ $kpis['total_usuarios'] }}</div>
                <span class="kpi-trend up"><i class="fa-solid fa-check"></i> Registrados en BIO-GEST</span>
            </div>
        </div>
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-boxes-packing kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div class="kpi-details">
                <h3 style="font-size:0.82rem; text-transform:uppercase; color:#64748b;">Pedidos Pendientes</h3>
                <div class="kpi-value" style="color:#151515;">{{ $kpis['pedidos_pendientes'] }}</div>
                <span class="kpi-trend up"><i class="fa-solid fa-clock"></i> Por gestionar</span>
            </div>
        </div>
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-truck-fast kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div class="kpi-details">
                <h3 style="font-size:0.82rem; text-transform:uppercase; color:#64748b;">Pedidos Realizados</h3>
                <div class="kpi-value" style="color:#151515;">{{ $kpis['total_pedidos'] }}</div>
                <span class="kpi-subtext">Histórico acumulado</span>
            </div>
        </div>
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-sack-dollar kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div class="kpi-details">
                <h3 style="font-size:0.82rem; text-transform:uppercase; color:#64748b;">Ingresos Acumulados</h3>
                <div class="kpi-value" style="color:#151515;">${{ number_format($kpis['ingresos_totales'], 0, ',', '.') }}</div>
                <span class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> Ventas efectivas</span>
            </div>
        </div>
    </section>

    <!-- REJILLA DE WIDGETS Y TABLAS -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(460px, 1fr)); gap:22px;">

        <!-- WIDGET 1: PEDIDOS RECIENTES -->
        <div class="tg-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:1.05rem; margin:0; color:#151515;"><i class="fa-solid fa-cart-flatbed" style="color:#e4b353;"></i> Pedidos Recientes</h2>
                <a href="{{ route('admin.pedidos.index') }}" style="font-size:0.85rem; color:#151515; font-weight:700; text-decoration:none;">Ver todos &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidosRecientes as $ped)
                            <tr>
                                <td style="white-space:nowrap;">
                                    <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}" style="font-weight:700; color:#151515; text-decoration:none;">
                                        {{ $ped->numero_pedido ?? 'PED-' . $ped->id_pedido }}
                                    </a>
                                </td>
                                <td>{{ optional($ped->usuario)->nombre_completo ?? 'Cliente' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($ped->estado_pedido) {
                                            'Entregado', 'Completado' => 'badge-active',
                                            'Cancelado' => 'badge-danger',
                                            'Despachado', 'Procesando' => 'badge-info',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $ped->estado_pedido }}</span>
                                </td>
                                <td style="font-weight:700; text-align:right; white-space:nowrap;">${{ number_format($ped->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="text-align:center;">Sin pedidos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WIDGET 2: ESTADO DE INVENTARIO -->
        <div class="tg-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:1.05rem; margin:0; color:#151515;"><i class="fa-solid fa-warehouse" style="color:#e4b353;"></i> Estado del Inventario</h2>
                <a href="{{ route('admin.inventario.index') }}" style="font-size:0.85rem; color:#151515; font-weight:700; text-decoration:none;">Ir a Inventario &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="text-align:center;">Stock</th>
                            <th>Alerta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            @php $min = $prod->stock_minimo ?? 10; @endphp
                            <tr>
                                <td style="white-space:nowrap; font-weight:700;">INV-{{ str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:600; color:#151515;">{{ $prod->nombre_producto }}</td>
                                <td style="font-weight:700; text-align:center; white-space:nowrap;">{{ $prod->stock }} uds.</td>
                                <td>
                                    @if($prod->stock <= 0)
                                        <span class="badge badge-danger">Agotado</span>
                                    @elseif($prod->stock <= $min)
                                        <span class="badge badge-warning">Stock Bajo</span>
                                    @else
                                        <span class="badge badge-active">Óptimo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WIDGET 3: CATÁLOGO DE PRODUCTOS -->
        <div class="tg-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:1.05rem; margin:0; color:#151515;"><i class="fa-solid fa-box-open" style="color:#e4b353;"></i> Catálogo de Productos</h2>
                <a href="{{ route('admin.productos.index') }}" style="font-size:0.85rem; color:#151515; font-weight:700; text-decoration:none;">Gestionar &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th style="text-align:right;">Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            <tr>
                                <td style="white-space:nowrap; font-weight:700;">PROD-{{ str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:600; color:#151515;">{{ $prod->nombre_producto }}</td>
                                <td style="text-align:right; font-weight:700; white-space:nowrap;">${{ number_format($prod->precio, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $prod->estado === 'Activo' ? 'badge-active' : 'badge-danger' }}">
                                        {{ $prod->estado }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WIDGET 4: USUARIOS RECIENTES -->
        <div class="tg-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:1.05rem; margin:0; color:#151515;"><i class="fa-solid fa-users" style="color:#e4b353;"></i> Usuarios Recientes</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuariosRecientes as $usr)
                            <tr>
                                <td style="white-space:nowrap; font-weight:700;">USR-{{ str_pad((string) $usr->id_usuario, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:600; color:#151515;">{{ $usr->nombre_completo }}</td>
                                <td>{{ optional($usr->rol)->nombre_rol }}</td>
                                <td><span class="badge badge-active">{{ $usr->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
