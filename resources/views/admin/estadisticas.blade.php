@extends('layouts.admin')

@section('title', 'Estadísticas y Reportes Comerciales | BIO-GEST Tenebrios Gold')
@section('header_title', 'Estadísticas y Reportes Unificados')

@section('content')
    <!-- 1. ENCABEZADO Y FILTROS DEL PERÍODO -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Estadísticas y Reportes de Ventas
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Métricas comerciales unificadas, rotación de inventario y exportación oficial de <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <!-- Exportar Excel (.xlsx) -->
            <a href="{{ route('admin.estadisticas.excel', request()->all()) }}" class="tg-btn-dark" style="font-size:0.88rem; background:#047857; border-color:#059669; color:#ffffff;" title="Descargar reporte formateado en Excel (.xlsx)">
                <i class="fa-solid fa-file-excel" style="color:#ffffff; font-size:1.05rem;"></i> Exportar Excel (.xlsx)
            </a>

            <!-- Exportar PDF -->
            <a href="{{ route('admin.estadisticas.pdf', request()->all()) }}" class="tg-btn-dark" style="font-size:0.88rem; background:#b91c1c; border-color:#dc2626; color:#ffffff;" title="Descargar reporte ejecutivo en PDF">
                <i class="fa-solid fa-file-pdf" style="color:#ffffff; font-size:1.05rem;"></i> Exportar PDF
            </a>

            <!-- Botón Funcional Exportar CSV -->
            <a href="{{ route('admin.estadisticas.csv', request()->all()) }}" class="tg-btn-dark" style="font-size:0.88rem;" title="Descargar reporte filtrado compatible con Excel">
                <i class="fa-solid fa-file-csv" style="color:#e6a100; font-size:1.05rem;"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- TARJETA DE FILTROS -->
    <div class="tg-card" style="padding:18px 22px; margin-bottom:24px;">
        <form method="GET" action="{{ route('admin.estadisticas') }}" class="filter-bar">
            <!-- Fecha Inicio -->
            <div class="form-group-filter">
                <label for="fecha_inicio"><i class="fa-regular fa-calendar"></i> Fecha Desde</label>
                <input
                    type="date"
                    id="fecha_inicio"
                    name="fecha_inicio"
                    class="form-input-filter"
                    value="{{ $fechaInicio }}"
                    required
                >
            </div>

            <!-- Fecha Fin -->
            <div class="form-group-filter">
                <label for="fecha_fin"><i class="fa-regular fa-calendar-check"></i> Fecha Hasta</label>
                <input
                    type="date"
                    id="fecha_fin"
                    name="fecha_fin"
                    class="form-input-filter"
                    value="{{ $fechaFin }}"
                    required
                >
            </div>

            <!-- Estado de Pedido -->
            <div class="form-group-filter">
                <label for="estado_pedido"><i class="fa-solid fa-filter"></i> Estado del Pedido</label>
                <select id="estado_pedido" name="estado_pedido" class="form-input-filter" style="min-width:170px;">
                    <option value="">Todos los Estados</option>
                    <option value="Pendiente" {{ $estadoFiltro === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Confirmado/Enviado" {{ $estadoFiltro === 'Confirmado/Enviado' ? 'selected' : '' }}>Confirmado / Enviado</option>
                    <option value="Entregado" {{ $estadoFiltro === 'Entregado' ? 'selected' : '' }}>Entregado / Completado</option>
                    <option value="Cancelado" {{ $estadoFiltro === 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <!-- Botones de Acción de Filtro -->
            <div style="display:flex; gap:8px;">
                <button type="submit" class="tg-btn-gold">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar Período
                </button>
                <a href="{{ route('admin.estadisticas') }}" class="tg-btn-outline" title="Restablecer filtros al mes actual">
                    <i class="fa-solid fa-rotate-left"></i> Restablecer
                </a>
            </div>
        </form>
    </div>

    <!-- 2. CUATRO TARJETAS KPI (CÁLCULO DINÁMICO DESDE MYSQL) -->
    <section class="kpi-grid">
        <!-- KPI 1: Ventas Totales ($ COP) -->
        <div class="kpi-card" style="border-left-color: #e6a100;">
            <div class="kpi-icon" style="background:#fdf8f0; color:#e6a100;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Ventas Totales (COP)</span>
                <span class="kpi-value">${{ number_format($ventasTotales, 0, ',', '.') }}</span>
                <span class="kpi-subtext" style="color:#047857;">
                    <i class="fa-solid fa-receipt"></i> {{ $totalPedidos }} pedido(s) en el período
                </span>
            </div>
        </div>

        <!-- KPI 2: Ticket Promedio -->
        <div class="kpi-card" style="border-left-color: #3b82f6;">
            <div class="kpi-icon" style="background:#dbeafe; color:#1d4ed8;">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Ticket Promedio</span>
                <span class="kpi-value">${{ number_format($ticketPromedio, 0, ',', '.') }}</span>
                <span class="kpi-subtext" style="color:#1d4ed8;">
                    <i class="fa-solid fa-arrow-trend-up"></i> Promedio por pedido efectivo
                </span>
            </div>
        </div>

        <!-- KPI 3: Unidades Vendidas -->
        <div class="kpi-card" style="border-left-color: #10b981;">
            <div class="kpi-icon" style="background:#d1fae5; color:#047857;">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Unidades Vendidas</span>
                <span class="kpi-value">{{ number_format($unidadesVendidas, 0, ',', '.') }}</span>
                <span class="kpi-subtext" style="color:#047857;">
                    <i class="fa-solid fa-cart-shopping"></i> Volumen despachado
                </span>
            </div>
        </div>

        <!-- KPI 4: Productos con Baja Rotación -->
        <div class="kpi-card {{ $productosBajaRotacion > 0 ? 'kpi-danger' : '' }}" style="border-left-color: {{ $productosBajaRotacion > 0 ? '#ef4444' : '#10b981' }};">
            <div class="kpi-icon" style="background:{{ $productosBajaRotacion > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ $productosBajaRotacion > 0 ? '#ef4444' : '#10b981' }};">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Baja Rotación</span>
                <span class="kpi-value" style="color:{{ $productosBajaRotacion > 0 ? '#dc2626' : '#111111' }};">{{ $productosBajaRotacion }}</span>
                <span class="kpi-subtext" style="color:{{ $productosBajaRotacion > 0 ? '#dc2626' : '#047857' }}; font-weight:600;">
                    <i class="fa-solid {{ $productosBajaRotacion > 0 ? 'fa-triangle-exclamation' : 'fa-check-double' }}"></i>
                    {{ $productosBajaRotacion > 0 ? 'Productos sin ventas en el período' : 'Todos con rotación activa' }}
                </span>
            </div>
        </div>
    </section>

    <!-- 3. DOS GRÁFICAS CON CHART.JS -->
    <div class="charts-grid-2">
        <!-- Gráfica 1: Ingresos vs. Pedidos por Mes -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-chart-line" style="color:#e6a100;"></i>
                    Ingresos vs. Pedidos por Mes
                </h2>
                <span style="font-size:0.78rem; font-weight:600; color:#6b7280; background:#f3f4f6; padding:3px 8px; border-radius:6px;">
                    Histórico Año {{ now()->year }}
                </span>
            </div>
            <p style="color:#6b7280; font-size:0.8rem; margin-bottom:14px;">
                Comparativa de facturación ($ COP, barras doradas) frente a la cantidad de pedidos gestionados (línea oscura).
            </p>
            <div style="position:relative; height:290px; width:100%;">
                <canvas id="chartIngresosPedidosMes"></canvas>
            </div>
        </div>

        <!-- Gráfica 2: Top Productos Más Vendidos -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2>
                    <i class="fa-solid fa-trophy" style="color:#e6a100;"></i>
                    Top Productos Más Vendidos
                </h2>
                <span style="font-size:0.78rem; font-weight:600; color:#6b7280; background:#f3f4f6; padding:3px 8px; border-radius:6px;">
                    Período Seleccionado
                </span>
            </div>
            <p style="color:#6b7280; font-size:0.8rem; margin-bottom:14px;">
                Ranking por unidades vendidas en el rango de fechas actual.
            </p>
            <div style="position:relative; height:290px; width:100%;">
                <canvas id="chartTopProductos"></canvas>
            </div>
        </div>
    </div>

    <!-- 4. TABLA: SALUD Y ROTACIÓN DE INVENTARIO -->
    <div class="tg-card">
        <div class="card-header-flex">
            <h2>
                <i class="fa-solid fa-heart-pulse" style="color:#e6a100;"></i>
                Salud y Rotación de Inventario
            </h2>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="badge badge-rotacion-alta">Alta (>= 5 uds)</span>
                <span class="badge badge-rotacion-media">Media (1 - 4 uds)</span>
                <span class="badge badge-rotacion-baja">Baja (0 uds)</span>
            </div>
        </div>
        <p style="color:#6b7280; font-size:0.82rem; margin-bottom:16px;">
            Diagnóstico de movimiento comercial por producto vs. existencias reales en bodega.
        </p>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th style="text-align:right;">Precio Unitario</th>
                        <th style="text-align:center;">Unidades Vendidas</th>
                        <th style="text-align:right;">Ingresos Período</th>
                        <th style="text-align:center;">Stock Actual / Mínimo</th>
                        <th style="text-align:center;">Rotación</th>
                        <th style="text-align:center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tablaSaludInventario as $item)
                        <tr>
                            <td style="font-weight:700; white-space:nowrap; color:#6b7280;">
                                PROD-{{ str_pad((string) $item['id_producto'], 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                <strong style="color:#111111;">{{ $item['nombre_producto'] }}</strong>
                            </td>
                            <td style="color:#6b7280;">{{ $item['categoria'] }}</td>
                            <td style="text-align:right; font-weight:600; white-space:nowrap;">
                                ${{ number_format($item['precio'], 0, ',', '.') }}
                            </td>
                            <td style="text-align:center; font-weight:800; font-size:0.95rem; color:#111111;">
                                {{ $item['unidades_vendidas'] }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:#111111; white-space:nowrap;">
                                ${{ number_format($item['total_ingresos'], 0, ',', '.') }}
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <span style="font-weight:700; color:{{ $item['stock'] <= $item['stock_minimo'] ? '#dc2626' : '#111111' }};">
                                    {{ $item['stock'] }}
                                </span>
                                <span style="color:#9ca3af; font-size:0.75rem;">/ {{ $item['stock_minimo'] }}</span>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $item['badge_class'] }}">
                                    {{ $item['rotacion'] }} Rotación
                                </span>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="{{ route('admin.inventario.index', ['id_producto' => $item['id_producto']]) }}" class="tg-btn-outline" style="padding:4px 10px; font-size:0.78rem;" title="Ver Kardex del producto">
                                    <i class="fa-solid fa-boxes-stacked"></i> Kardex
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:24px; color:#6b7280;">
                                No se encontraron productos registrados para el análisis.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. LISTADO DETALLADO DE PEDIDOS DEL PERÍODO -->
    <div class="tg-card" style="margin-bottom:0;">
        <div class="card-header-flex">
            <h2>
                <i class="fa-solid fa-receipt" style="color:#e6a100;"></i>
                Ventas y Pedidos Registrados en el Período ({{ $pedidos->count() }})
            </h2>
            <a href="{{ route('admin.pedidos.index') }}" class="card-header-link">Ir a Gestión de Pedidos &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
                        <th>Fecha / Hora</th>
                        <th>Cliente</th>
                        <th>Ciudad</th>
                        <th>Método Pago</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:right;">Total (COP)</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        @php
                            $badgeClass = match($pedido->estado_pedido) {
                                'Pendiente' => 'badge-pendiente',
                                'Confirmado', 'Procesando', 'Enviado', 'Despachado' => 'badge-confirmado',
                                'Entregado', 'Completado' => 'badge-entregado',
                                'Cancelado' => 'badge-cancelado',
                                default => 'badge-pendiente'
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:#111111; white-space:nowrap;">
                                {{ $pedido->numero_pedido ?: 'PED-' . (1000 + $pedido->id_pedido) }}
                            </td>
                            <td style="white-space:nowrap; font-size:0.8rem; color:#6b7280;">
                                {{ optional($pedido->fecha_pedido)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div style="font-weight:600; color:#111111;">{{ optional($pedido->usuario)->nombre_completo ?? 'Cliente' }}</div>
                                <small style="color:#6b7280;">{{ optional($pedido->usuario)->correo ?? '' }}</small>
                            </td>
                            <td>{{ $pedido->ciudad_entrega ?? 'Bogotá' }}</td>
                            <td style="font-size:0.82rem; color:#4b5563;">{{ $pedido->metodo_pago }}</td>
                            <td style="text-align:right; font-weight:600; color:#4b5563;">
                                ${{ number_format($pedido->subtotal, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right; font-weight:800; color:#111111; white-space:nowrap;">
                                ${{ number_format($pedido->total, 0, ',', '.') }}
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $badgeClass }}">
                                    {{ $pedido->estado_pedido }}
                                </span>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="{{ route('admin.pedidos.show', $pedido->id_pedido) }}" class="tg-btn-outline" style="padding:4px 10px; font-size:0.78rem;">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:24px; color:#6b7280;">
                                No se encontraron pedidos registrados en el rango de fechas seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // GRÁFICA 1: INGRESOS VS. PEDIDOS POR MES (Barras #e6a100 y Línea #111111)
    // -------------------------------------------------------------
    const ctxIngresosPedidos = document.getElementById('chartIngresosPedidosMes');
    if (ctxIngresosPedidos) {
        const labelsMeses = @json($chartMesesLabels);
        const dataIngresos = @json($chartIngresosData);
        const dataPedidos = @json($chartPedidosData);

        new Chart(ctxIngresosPedidos, {
            data: {
                labels: labelsMeses,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Ingresos Mensuales ($ COP)',
                        data: dataIngresos,
                        backgroundColor: '#e6a100',
                        hoverBackgroundColor: '#cf9000',
                        borderRadius: 6,
                        yAxisID: 'yIngresos',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Cantidad de Pedidos',
                        data: dataPedidos,
                        borderColor: '#111111',
                        backgroundColor: '#111111',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        yAxisID: 'yPedidos',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
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
                                if (context.dataset.yAxisID === 'yIngresos') {
                                    return ' ' + context.dataset.label + ': $' + new Intl.NumberFormat('es-CO').format(context.parsed.y);
                                }
                                return ' ' + context.dataset.label + ': ' + context.parsed.y + ' pedido(s)';
                            }
                        }
                    }
                },
                scales: {
                    yIngresos: {
                        type: 'linear',
                        display: true,
                        position: 'left',
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
                    yPedidos: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            stepSize: 1,
                            color: '#111111',
                            font: { family: "'Inter', sans-serif", size: 11, weight: 'bold' }
                        }
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
    // GRÁFICA 2: TOP PRODUCTOS MÁS VENDIDOS
    // -------------------------------------------------------------
    const ctxTop = document.getElementById('chartTopProductos');
    if (ctxTop) {
        const topLabels = @json($chartTopLabels);
        const topData = @json($chartTopUnidades);

        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topLabels.length ? topLabels : ['Sin ventas en el período'],
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: topData.length ? topData : [0],
                    backgroundColor: '#e6a100',
                    hoverBackgroundColor: '#cf9000',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return ` ${context.parsed.x} unidades vendidas`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#6b7280',
                            font: { family: "'Inter', sans-serif", size: 11 }
                        },
                        grid: { color: '#f3f4f6' }
                    },
                    y: {
                        ticks: {
                            color: '#374151',
                            font: { family: "'Inter', sans-serif", size: 11 }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush
