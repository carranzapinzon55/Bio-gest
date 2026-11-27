<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo | BIO-GEST Tenebrios Gold</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e6a100;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .brand-title {
            font-size: 16px;
            font-weight: bold;
            color: #111111;
            letter-spacing: 0.5px;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #e6a100;
            font-weight: bold;
            text-transform: uppercase;
        }
        .meta-box {
            text-align: right;
            font-size: 9px;
            color: #4b5563;
        }
        .kpi-container {
            width: 100%;
            margin-bottom: 16px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #e6a100;
            padding: 8px 10px;
            text-align: left;
        }
        .kpi-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            color: #111111;
            margin-top: 3px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin: 14px 0 8px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #111111;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #111111;
        }
        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #075985; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="footer">
        BIO-GEST · Tenebrios Gold S.A.S. — Reporte Ejecutivo Oficial Generado el {{ now()->format('d/m/Y h:i a') }}
    </div>

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td style="width: 60px; vertical-align: middle;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 50px; height: auto;">
                @else
                    <div style="width:40px; height:40px; background:#111111; color:#e6a100; text-align:center; line-height:40px; font-weight:bold; font-size:16px;">TG</div>
                @endif
            </td>
            <td style="vertical-align: middle;">
                <div class="brand-title">TENEBRIOS GOLD</div>
                <div class="brand-subtitle">BIO-GEST · Reporte Ejecutivo de Ventas y Rendimiento</div>
            </td>
            <td class="meta-box" style="vertical-align: middle;">
                <strong>Período Analizado:</strong> {{ $fechaInicio }} al {{ $fechaFin }}<br>
                <strong>Filtro de Estado:</strong> {{ $estadoFiltro ?: 'Todos los pedidos' }}<br>
                <strong>Generado por:</strong> {{ auth()->user()->nombre ?? 'Administrador' }}
            </td>
        </tr>
    </table>

    <!-- KPIS -->
    <table class="kpi-container" style="border-spacing: 6px; border-collapse: separate;">
        <tr>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Ventas Totales</div>
                    <div class="kpi-val" style="color:#047857;">$ {{ number_format($ventasTotales, 0, ',', '.') }} COP</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Pedidos Registrados</div>
                    <div class="kpi-val">{{ $totalPedidos }}</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Ticket Promedio</div>
                    <div class="kpi-val">$ {{ number_format($ticketPromedio, 0, ',', '.') }} COP</div>
                </div>
            </td>
            <td style="width: 25%;">
                <div class="kpi-card">
                    <div class="kpi-label">Unidades Comercializadas</div>
                    <div class="kpi-val" style="color:#e6a100;">{{ $unidadesVendidas }} uds.</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- SECCIÓN 1: SALUD Y ROTACIÓN DE INVENTARIO -->
    <div class="section-title">1. Salud y Rotación de Inventario Oficial</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:55px;">ID</th>
                <th>Nombre del Producto</th>
                <th>Categoría</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-center">Stock</th>
                <th class="text-center">Vendidos</th>
                <th class="text-right">Total Ingresos</th>
                <th class="text-center" style="width:65px;">Rotación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($todosProductos as $prod)
                @php
                    $venta = $ventasPorProducto->get($prod->id_producto);
                    $unidades = $venta ? (int) $venta->total_unidades : 0;
                    $ingresos = $venta ? (float) $venta->total_ingresos : 0;
                    $rotacion = $unidades >= 5 ? 'Alta' : ($unidades >= 1 ? 'Media' : 'Baja');
                    $rotClass = $rotacion === 'Alta' ? 'badge-success' : ($rotacion === 'Media' ? 'badge-info' : 'badge-warning');
                @endphp
                <tr>
                    <td>PROD-{{ str_pad($prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $prod->nombre_producto }}</strong></td>
                    <td>{{ $prod->categoria?->nombre_categoria ?? 'General' }}</td>
                    <td class="text-right">$ {{ number_format($prod->precio, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $prod->stock }}</td>
                    <td class="text-center">{{ $unidades }}</td>
                    <td class="text-right">$ {{ number_format($ingresos, 0, ',', '.') }}</td>
                    <td class="text-center"><span class="badge {{ $rotClass }}">{{ $rotacion }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No se encontraron productos en el catálogo.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SECCIÓN 2: DETALLE DE PEDIDOS -->
    <div class="section-title">2. Detalle de Pedidos y Ventas del Período</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:65px;"># Pedido</th>
                <th style="width:85px;">Fecha</th>
                <th>Cliente</th>
                <th>Ciudad</th>
                <th class="text-right">Total (COP)</th>
                <th>Método Pago</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pedidos as $pedido)
                @php
                    $badgeClass = 'badge-info';
                    if (in_array($pedido->estado_pedido, ['Entregado', 'Completado'])) $badgeClass = 'badge-success';
                    elseif (in_array($pedido->estado_pedido, ['Cancelado'])) $badgeClass = 'badge-danger';
                    elseif (in_array($pedido->estado_pedido, ['Pendiente', 'Confirmado'])) $badgeClass = 'badge-warning';
                @endphp
                <tr>
                    <td><strong>{{ $pedido->numero_pedido ?: ('PED-' . (1000 + $pedido->id_pedido)) }}</strong></td>
                    <td>{{ optional($pedido->fecha_pedido)->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ optional($pedido->usuario)->nombre_completo ?? 'Cliente' }}
                        <div style="font-size:7px; color:#64748b;">{{ optional($pedido->usuario)->correo }}</div>
                    </td>
                    <td>{{ $pedido->ciudad_entrega ?? 'Bogotá D.C.' }}</td>
                    <td class="text-right"><strong>$ {{ number_format($pedido->total, 0, ',', '.') }}</strong></td>
                    <td>{{ $pedido->metodo_pago ?? 'Contraentrega' }}</td>
                    <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $pedido->estado_pedido }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No se registraron pedidos dentro del período y filtro seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
