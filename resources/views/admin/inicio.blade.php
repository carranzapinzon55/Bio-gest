@extends('layouts.admin')

@section('title', 'Dashboard General | BIO-GEST Tenebrios Gold')
@section('header_title', 'Dashboard General BIO-GEST')

@section('content')
    <!-- 1. ENCABEZADO DE BIENVENIDA Y ACCIONES RÁPIDAS -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Bienvenido, {{ auth()->user()->nombre ?? 'Administrador' }}
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Control integral y monitoreo en tiempo real de <strong>TENEBRIOS GOLD</strong> (Año {{ now()->year }}).
            </p>
        </div>
        <div class="admin-header-actions" style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.estadisticas') }}" class="tg-btn-gold">
                <i class="fa-solid fa-chart-line"></i> Estadísticas y Reportes
            </a>
            <a href="{{ route('admin.pedidos.index') }}" class="tg-btn-dark">
                <i class="fa-solid fa-receipt"></i> Gestión de Pedidos
            </a>
            <a href="{{ route('admin.productos.create') }}" class="tg-btn-outline">
                <i class="fa-solid fa-plus"></i> Nuevo Producto
            </a>
        </div>
    </div>

    <!-- 2. CUATRO TARJETAS KPI INTERACTIVAS (CONSULTADAS DESDE MYSQL) -->
    <section class="kpi-grid">
        <!-- KPI 1: Ingresos del Mes (Navega a Estadísticas y Reportes) -->
        <a href="{{ route('admin.estadisticas') }}" class="kpi-card" style="border-left-color: #e6a100; text-decoration:none; cursor:pointer;" title="Ver Reporte Completo de Ingresos">
            <div class="kpi-icon" style="background:#fdf8f0; color:#e6a100;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Ingresos del Mes</span>
                <span class="kpi-value">${{ number_format($kpis['ingresos_mes'], 0, ',', '.') }}</span>
                <span class="kpi-subtext" style="color:#047857;">
                    <i class="fa-solid fa-calendar-check"></i> Mes actual &rarr; Ver reporte
                </span>
            </div>
        </a>

        <!-- KPI 2: Pedidos Pendientes (Navega a Pedidos con filtro Pendiente) -->
        <a href="{{ route('admin.pedidos.index', ['estado' => 'Pendiente']) }}" class="kpi-card" style="border-left-color: #f59e0b; text-decoration:none; cursor:pointer;" title="Filtrar Pedidos Pendientes">
            <div class="kpi-icon" style="background:#fef3c7; color:#b45309;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Pedidos Pendientes</span>
                <span class="kpi-value">{{ $kpis['pedidos_pendientes'] }}</span>
                <span class="kpi-subtext" style="color:#b45309;">
                    <i class="fa-solid fa-hourglass-half"></i> Requieren gestión &rarr; Atender
                </span>
            </div>
        </a>

        <!-- KPI 3: Clientes Activos (Navega a Usuarios con filtro Cliente y Activo) -->
        <a href="{{ route('admin.usuarios.index', ['rol' => 'Cliente', 'estado' => 'Activo']) }}" class="kpi-card" style="border-left-color: #3b82f6; text-decoration:none; cursor:pointer;" title="Ver Clientes Activos">
            <div class="kpi-icon" style="background:#dbeafe; color:#1d4ed8;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Clientes Activos</span>
                <span class="kpi-value">{{ $kpis['clientes_activos'] }}</span>
                <span class="kpi-subtext" style="color:#1d4ed8;">
                    <i class="fa-solid fa-user-check"></i> Clientes activos &rarr; Ver directorio
                </span>
            </div>
        </a>

        <!-- KPI 4: Alertas de Stock (Navega a Alertas de Stock) -->
        <a href="{{ route('admin.alertas-stock') }}" class="kpi-card {{ $kpis['alertas_stock'] > 0 ? 'kpi-danger' : '' }}" style="border-left-color: {{ $kpis['alertas_stock'] > 0 ? '#ef4444' : '#10b981' }}; text-decoration:none; cursor:pointer;" title="Ver Alertas de Reabastecimiento">
            <div class="kpi-icon" style="background:{{ $kpis['alertas_stock'] > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ $kpis['alertas_stock'] > 0 ? '#ef4444' : '#10b981' }};">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Alertas de Stock</span>
                <span class="kpi-value" style="color:{{ $kpis['alertas_stock'] > 0 ? '#dc2626' : '#111111' }};">{{ $kpis['alertas_stock'] }}</span>
                <span class="kpi-subtext" style="color:{{ $kpis['alertas_stock'] > 0 ? '#dc2626' : '#047857' }}; font-weight:600;">
                    <i class="fa-solid {{ $kpis['alertas_stock'] > 0 ? 'fa-circle-exclamation' : 'fa-circle-check' }}"></i>
                    {{ $kpis['alertas_stock'] > 0 ? 'Stock <= Mínimo &rarr; Reabastecer' : 'Inventario en niveles óptimos' }}
                </span>
            </div>
        </a>
    </section>

    <!-- 3. DOS GRÁFICAS CON CHART.JS -->
    <div class="charts-grid-2">
        <!-- Gráfica 1: Rendimiento de Ventas (COP) por mes -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-chart-column" style="color:#e6a100;"></i>
                    Rendimiento de Ventas (COP)
                </h2>
                <span style="font-size:0.78rem; font-weight:600; color:#6b7280; background:#f3f4f6; padding:3px 8px; border-radius:6px;">
                    Año {{ now()->year }}
                </span>
            </div>
            <p style="color:#6b7280; font-size:0.8rem; margin-bottom:14px;">
                Evolución mensual de facturación en barras doradas (#e6a100) y línea de tendencia (#111111).
            </p>
            <div style="position:relative; height:280px; width:100%;">
                <canvas id="chartVentasMes"></canvas>
            </div>
        </div>

        <!-- Gráfica 2: Estado de Pedidos (Dona) -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-chart-pie" style="color:#e6a100;"></i>
                    Estado de Pedidos
                </h2>
                <a href="{{ route('admin.pedidos.index') }}" class="card-header-link">Ver pedidos &rarr;</a>
            </div>
            <p style="color:#6b7280; font-size:0.8rem; margin-bottom:14px;">
                Distribución de pedidos según su estado operativo actual.
            </p>
            <div style="position:relative; height:280px; width:100%; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartEstadoPedidos"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. DOS TABLAS INFERIORES -->
    <div class="tables-grid-2">
        <!-- Tabla Inferior 1: Pedidos Recientes -->
        <div class="tg-card" style="margin-bottom:0;">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-clock-rotate-left" style="color:#e6a100;"></i>
                    Pedidos Recientes
                </h2>
                <a href="{{ route('admin.pedidos.index') }}" class="card-header-link">Ver todos &rarr;</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>N° Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th style="text-align:right;">Total (COP)</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidosRecientes as $pedido)
                            @php
                                $estadoClass = match($pedido->estado_pedido) {
                                    'Pendiente' => 'badge-pendiente',
                                    'Confirmado', 'Procesando', 'Enviado', 'Despachado' => 'badge-confirmado',
                                    'Entregado', 'Completado' => 'badge-entregado',
                                    'Cancelado' => 'badge-cancelado',
                                    default => 'badge-pendiente'
                                };
                            @endphp
                            <tr>
                                <td style="font-weight:700; white-space:nowrap; color:#111111;">
                                    {{ $pedido->numero_pedido ?: 'PED-' . (1000 + $pedido->id_pedido) }}
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#111111;">
                                        {{ optional($pedido->usuario)->nombre_completo ?? 'Cliente registrado' }}
                                    </div>
                                    <small style="color:#6b7280;">{{ optional($pedido->usuario)->correo ?? '' }}</small>
                                </td>
                                <td style="white-space:nowrap; font-size:0.8rem; color:#6b7280;">
                                    {{ optional($pedido->fecha_pedido)->format('d/m/Y H:i') }}
                                </td>
                                <td style="text-align:right; font-weight:800; color:#111111; white-space:nowrap;">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge {{ $estadoClass }}">
                                        {{ $pedido->estado_pedido }}
                                    </span>
                                </td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <a href="{{ route('admin.pedidos.show', $pedido->id_pedido) }}" class="tg-btn-outline" style="padding:4px 10px; font-size:0.78rem;" title="Ver detalle del pedido">
                                        <i class="fa-solid fa-eye"></i> Detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:24px; color:#6b7280;">
                                    <i class="fa-solid fa-inbox" style="font-size:1.5rem; display:block; margin-bottom:6px; color:#cbd5e1;"></i>
                                    No se registran pedidos recientes en la base de datos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabla Inferior 2: Alertas de Stock Bajo -->
        <div class="tg-card" style="margin-bottom:0;">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-triangle-exclamation" style="color:{{ $kpis['alertas_stock'] > 0 ? '#ef4444' : '#e6a100' }};"></i>
                    Alertas de Stock Bajo
                </h2>
                <a href="{{ url('/admin/alertas-stock') }}" class="card-header-link">Gestionar stock &rarr;</a>
            </div>

            @if($productosBajoStock->isEmpty())
                <div style="background:#d1fae5; border:1px solid #a7f3d0; border-radius:8px; padding:10px 14px; margin-bottom:12px; font-size:0.82rem; color:#065f46; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>¡Excelente! Todos los productos están actualmente sobre su stock mínimo. Mostrando productos más próximos al umbral:</span>
                </div>
            @endif

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align:center;">Stock / Mínimo</th>
                            <th>Nivel de Stock</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosNivelStock as $producto)
                            @php
                                $stockMin = $producto->stock_minimo ?? 10;
                                $porcentaje = $stockMin > 0 ? min(100, round(($producto->stock / max($stockMin * 2, 1)) * 100)) : 100;
                                if ($producto->stock <= 0) {
                                    $fillClass = 'danger';
                                    $badge = 'badge-critico';
                                    $estadoTexto = 'Agotado';
                                } elseif ($producto->stock <= $stockMin) {
                                    $fillClass = 'danger';
                                    $badge = 'badge-critico';
                                    $estadoTexto = 'Stock Bajo';
                                } elseif ($producto->stock <= ($stockMin * 1.5)) {
                                    $fillClass = 'warning';
                                    $badge = 'badge-alerta';
                                    $estadoTexto = 'Precaución';
                                } else {
                                    $fillClass = 'success';
                                    $badge = 'badge-activo';
                                    $estadoTexto = 'Óptimo';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#111111;">{{ $producto->nombre_producto }}</div>
                                    <small style="color:#6b7280;">{{ $producto->categoria?->nombre_categoria ?? 'Sin categoría' }}</small>
                                </td>
                                <td style="text-align:center; font-weight:700; white-space:nowrap;">
                                    <span style="color:{{ $producto->stock <= $stockMin ? '#dc2626' : '#111111' }}; font-size:0.95rem;">
                                        {{ $producto->stock }}
                                    </span>
                                    <span style="color:#9ca3af; font-size:0.75rem;">/ {{ $stockMin }}</span>
                                </td>
                                <td>
                                    <div class="stock-level-container">
                                        <div class="stock-level-bar">
                                            <div class="stock-level-fill {{ $fillClass }}" style="width: {{ $porcentaje }}%;"></div>
                                        </div>
                                        <span style="font-size:0.72rem; color:#6b7280; font-weight:600;">{{ $porcentaje }}%</span>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge {{ $badge }}">
                                        {{ $estadoTexto }}
                                    </span>
                                </td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <a href="{{ route('admin.inventario.index', ['id_producto' => $producto->id_producto]) }}" class="tg-btn-outline" style="padding:4px 10px; font-size:0.78rem;" title="Ajustar o registrar entrada de stock">
                                        <i class="fa-solid fa-boxes-packing"></i> Ajustar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:24px; color:#6b7280;">
                                    No se encontraron productos registrados en el inventario.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // GRÁFICA 1: RENDIMIENTO DE VENTAS (COP) (Barras Doradas y Línea #111111)
    // -------------------------------------------------------------
    const ctxVentas = document.getElementById('chartVentasMes');
    if (ctxVentas) {
        const labelsVentas = @json($chartVentasLabels);
        const dataVentas = @json($chartVentasData);

        new Chart(ctxVentas, {
            data: {
                labels: labelsVentas,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Ventas Mensuales ($ COP)',
                        data: dataVentas,
                        backgroundColor: '#e6a100',
                        hoverBackgroundColor: '#cf9000',
                        borderRadius: 6,
                        maxBarThickness: 34,
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Línea de Rendimiento',
                        data: dataVentas,
                        borderColor: '#111111',
                        backgroundColor: '#111111',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#111111',
                        fill: false,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            color: '#374151',
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += '$ ' + new Intl.NumberFormat('es-CO').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#6b7280',
                            font: { family: "'Inter', sans-serif", size: 11 },
                            callback: function (val) {
                                return '$ ' + new Intl.NumberFormat('es-CO', { notation: 'compact' }).format(val);
                            }
                        },
                        grid: { color: '#f3f4f6' }
                    },
                    x: {
                        ticks: {
                            color: '#6b7280',
                            font: { family: "'Inter', sans-serif", size: 11 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // GRÁFICA 2: ESTADO DE PEDIDOS (Dona)
    // Pendiente, Confirmado/Enviado, Entregado, Cancelado
    // -------------------------------------------------------------
    const ctxPedidos = document.getElementById('chartEstadoPedidos');
    if (ctxPedidos) {
        const dataEstados = [
            {{ $chartPedidosEstados['Pendiente'] ?? 0 }},
            {{ $chartPedidosEstados['Confirmado / Enviado'] ?? 0 }},
            {{ $chartPedidosEstados['Entregado'] ?? 0 }},
            {{ $chartPedidosEstados['Cancelado'] ?? 0 }}
        ];

        new Chart(ctxPedidos, {
            type: 'doughnut',
            data: {
                labels: ['Pendiente', 'Confirmado / Enviado', 'Entregado', 'Cancelado'],
                datasets: [{
                    data: dataEstados,
                    backgroundColor: ['#e6a100', '#3b82f6', '#10b981', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#374151',
                            usePointStyle: true,
                            padding: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${val} pedidos (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
