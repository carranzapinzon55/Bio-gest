@extends('layouts.admin')

@section('title', 'Reporte de Ventas y Exportación CSV | BIO-GEST Tenebrios Gold')
@section('header_title', 'Módulo de Reportes de Ventas y Exportación CSV')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#151515;">
                <i class="fa-solid fa-chart-line" style="color:#e4b353;"></i> Reporte de Ventas por Rango de Fechas
            </h1>
            <p style="margin:4px 0 0 0; color:#64748b; font-size:0.9rem;">
                Consulta ventas registradas, estados de pedidos, totales agrupados por producto y exporta directamente a Excel (CSV UTF-8)
            </p>
        </div>

        <!-- Botón de Exportación CSV con los filtros actuales -->
        <a href="{{ route('admin.reportes.ventas.csv', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'estado_pedido' => $estadoFiltro]) }}"
           class="tg-btn-gold" style="padding:11px 22px; font-size:0.95rem; box-shadow:0 3px 8px rgba(0,0,0,0.12);">
            <i class="fa-solid fa-file-csv" style="font-size:1.15rem;"></i> Exportar Reporte a CSV (Excel)
        </a>
    </div>

    <!-- Formulario de Filtro por Rango de Fechas y Estado -->
    <div class="tg-card" style="background:#151515; color:#ffffff;">
        <form action="{{ route('admin.reportes.ventas') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">
            <div style="flex:1; min-width:190px;">
                <label style="display:block; font-size:0.82rem; font-weight:700; color:#e4b353; margin-bottom:6px;">
                    <i class="fa-regular fa-calendar"></i> Fecha Inicial *
                </label>
                <input type="date" id="rep_fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio }}" max="{{ now()->format('Y-m-d') }}" required
                       style="width:100%; padding:10px 12px; border:1px solid #e4b353; border-radius:6px; background:#242424; color:#ffffff; font-weight:600;">
            </div>

            <div style="flex:1; min-width:190px;">
                <label style="display:block; font-size:0.82rem; font-weight:700; color:#e4b353; margin-bottom:6px;">
                    <i class="fa-regular fa-calendar-check"></i> Fecha Final *
                </label>
                <input type="date" id="rep_fecha_fin" name="fecha_fin" value="{{ $fechaFin }}" min="{{ $fechaInicio }}" max="{{ now()->format('Y-m-d') }}" required
                       style="width:100%; padding:10px 12px; border:1px solid #e4b353; border-radius:6px; background:#242424; color:#ffffff; font-weight:600;">
            </div>

            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.82rem; font-weight:700; color:#e4b353; margin-bottom:6px;">
                    <i class="fa-solid fa-filter"></i> Estado del Pedido
                </label>
                <select name="estado_pedido" style="width:100%; padding:10px 12px; border:1px solid #3f3f46; border-radius:6px; background:#242424; color:#ffffff;">
                    <option value="">Todos los estados</option>
                    @foreach(['Pendiente', 'Procesando', 'Despachado', 'Listo para retiro', 'Entregado', 'Completado', 'Cancelado'] as $est)
                        <option value="{{ $est }}" {{ $estadoFiltro === $est ? 'selected' : '' }}>
                            {{ $est === 'Despachado' ? 'Enviado / Despachado' : $est }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="tg-btn-gold" style="padding:10px 22px;">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Filtrar Reporte
                </button>
                <a href="{{ route('admin.reportes.ventas') }}"
                   style="padding:10px 16px; border:1px solid #52525b; border-radius:6px; color:#e2e8f0; text-decoration:none; font-size:0.88rem; font-weight:600;">
                    Restablecer
                </a>
            </div>
        </form>
    </div>

    <!-- KPIs del Periodo Filtrado -->
    <section class="kpi-grid" style="margin-bottom:22px;">
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-receipt kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Pedidos en el Rango</h3>
                <div class="kpi-value" style="color:#151515;">{{ $kpis['total_pedidos'] }}</div>
                <span class="kpi-subtext">{{ $fechaInicio }} al {{ $fechaFin }}</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #16a34a;">
            <i class="fa-solid fa-sack-dollar kpi-icon" style="background:#dcfce7; color:#15803d;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Total Ventas Efectivas</h3>
                <div class="kpi-value" style="color:#15803d;">${{ number_format($kpis['ingresos_netos'], 0, ',', '.') }}</div>
                <span class="kpi-subtext">Excluyendo cancelados</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-box-open kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Unidades Vendidas</h3>
                <div class="kpi-value" style="color:#151515;">{{ number_format($kpis['unidades_vendidas'], 0, ',', '.') }} uds.</div>
                <span class="kpi-subtext">Total paquetes despachados</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #dc2626;">
            <i class="fa-solid fa-ban kpi-icon" style="background:#fee2e2; color:#b91c1c;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Pedidos Cancelados</h3>
                <div class="kpi-value" style="color:#b91c1c;">{{ $kpis['pedidos_cancelados'] }}</div>
                <span class="kpi-subtext">En el periodo consultado</span>
            </div>
        </div>
    </section>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:22px; margin-bottom:22px;">
        <!-- TABLA 1: TOTALES AGRUPADOS / RESUMIDOS POR PRODUCTO -->
        <div class="tg-card" style="grid-column: span 2; margin-bottom:0;">
            <h2 style="margin:0 0 14px 0; font-size:1.15rem; color:#151515;">
                <i class="fa-solid fa-boxes-stacked" style="color:#e4b353;"></i> Totales Agrupados y Resumidos por Producto
            </h2>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="text-align:center;">N.º Pedidos</th>
                            <th style="text-align:center;">Unidades Vendidas</th>
                            <th style="text-align:center;">Stock Actual</th>
                            <th style="text-align:right;">Total Recaudado (COP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasPorProducto as $item)
                            <tr>
                                <td style="font-weight:700;">PROD-{{ str_pad((string) $item->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:700; color:#151515;">{{ $item->nombre_producto }}</td>
                                <td style="text-align:center;">{{ $item->numero_pedidos }}</td>
                                <td style="text-align:center; font-weight:800; color:#151515;">{{ $item->cantidad_vendida }} uds.</td>
                                <td style="text-align:center;">{{ $item->stock_actual }} uds.</td>
                                <td style="text-align:right; font-weight:800; color:#15803d;">
                                    ${{ number_format($item->total_recaudado, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:22px; color:#64748b;">
                                    No se registran productos vendidos en este rango de fechas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($ventasPorProducto->isNotEmpty())
                        <tfoot>
                            <tr style="background:#fef9ec; font-weight:800; border-top:2px solid #e4b353;">
                                <td colspan="3" style="text-align:right; padding:12px;">TOTAL CONSOLIDADO POR PRODUCTOS:</td>
                                <td style="text-align:center; padding:12px;">{{ $ventasPorProducto->sum('cantidad_vendida') }} uds.</td>
                                <td></td>
                                <td style="text-align:right; padding:12px; color:#151515; font-size:1rem;">
                                    ${{ number_format($ventasPorProducto->sum('total_recaudado'), 0, ',', '.') }} COP
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- TABLA 2: RESUMEN POR ESTADO DE LOS PEDIDOS -->
        <div class="tg-card" style="margin-bottom:0;">
            <h2 style="margin:0 0 14px 0; font-size:1.15rem; color:#151515;">
                <i class="fa-solid fa-chart-pie" style="color:#e4b353;"></i> Resumen por Estado de Pedidos
            </h2>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th style="text-align:center;">Cantidad</th>
                            <th style="text-align:right;">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resumenEstados as $resEst)
                            @php
                                $badgeClass = match($resEst->estado_pedido) {
                                    'Entregado', 'Completado' => 'badge-active',
                                    'Cancelado' => 'badge-danger',
                                    'Despachado', 'Procesando' => 'badge-info',
                                    default => 'badge-warning',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $badgeClass }}">
                                        {{ $resEst->estado_pedido === 'Despachado' ? 'Enviado / Despachado' : $resEst->estado_pedido }}
                                    </span>
                                </td>
                                <td style="text-align:center; font-weight:700;">{{ $resEst->total_pedidos }}</td>
                                <td style="text-align:right; font-weight:700;">${{ number_format($resEst->monto_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:20px; color:#64748b;">
                                    Sin pedidos en este rango.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TABLA 3: DETALLE DE VENTAS REGISTRADAS EN EL PERIODO -->
    <div class="tg-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <h2 style="margin:0; font-size:1.15rem; color:#151515;">
                <i class="fa-solid fa-list-check" style="color:#e4b353;"></i> Detalle de Ventas Registradas ({{ $pedidos->count() }} registros)
            </h2>
            <span style="font-size:0.84rem; color:#64748b;">
                Periodo: <strong>{{ $fechaInicio }}</strong> a <strong>{{ $fechaFin }}</strong>
            </span>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>N.º Pedido</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Productos Vendidos</th>
                        <th>Método Pago</th>
                        <th>Estado Pedido</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:right;">Envío</th>
                        <th style="text-align:right;">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $ped)
                        @php
                            $badgeClass = match($ped->estado_pedido) {
                                'Entregado', 'Completado' => 'badge-active',
                                'Cancelado' => 'badge-danger',
                                'Despachado', 'Procesando' => 'badge-info',
                                default => 'badge-warning',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:800; color:#151515;">
                                <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}" style="color:#151515; text-decoration:underline;">
                                    {{ $ped->numero_pedido ?? 'PED-' . $ped->id_pedido }}
                                </a>
                            </td>
                            <td style="white-space:nowrap;">{{ optional($ped->fecha_pedido)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div style="font-weight:700;">{{ optional($ped->usuario)->nombre_completo ?? 'Cliente' }}</div>
                                <div style="font-size:0.75rem; color:#64748b;">{{ $ped->ciudad_entrega }}</div>
                            </td>
                            <td>
                                @foreach($ped->detalles as $det)
                                    <div style="font-size:0.82rem;">
                                        • {{ optional($det->producto)->nombre_producto ?? 'Producto' }} <strong>(x{{ $det->cantidad }})</strong>
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $ped->metodo_pago }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $ped->estado_pedido === 'Despachado' ? 'Enviado / Despachado' : $ped->estado_pedido }}
                                </span>
                            </td>
                            <td style="text-align:right;">${{ number_format($ped->subtotal, 0, ',', '.') }}</td>
                            <td style="text-align:right;">${{ number_format($ped->costo_envio, 0, ',', '.') }}</td>
                            <td style="text-align:right; font-weight:800; color:#151515;">${{ number_format($ped->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:25px; color:#64748b;">
                                No se encontraron ventas registradas en el rango de fechas seleccionado.
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
    const inputDesde = document.getElementById('rep_fecha_inicio');
    const inputHasta = document.getElementById('rep_fecha_fin');
    const hoyIso = "{{ now()->format('Y-m-d') }}";

    function sincronizarRangoReporte() {
        if (!inputDesde || !inputHasta) return;
        inputDesde.max = inputHasta.value && inputHasta.value < hoyIso ? inputHasta.value : hoyIso;
        inputHasta.max = hoyIso;
        if (inputDesde.value) {
            if (inputDesde.value > hoyIso) {
                inputDesde.value = hoyIso;
            }
            inputHasta.min = inputDesde.value;
            if (inputHasta.value && inputHasta.value < inputDesde.value) {
                inputHasta.value = inputDesde.value;
            }
        }
        if (inputHasta.value && inputHasta.value > hoyIso) {
            inputHasta.value = hoyIso;
        }
    }

    if (inputDesde && inputHasta) {
        inputDesde.addEventListener('change', sincronizarRangoReporte);
        inputHasta.addEventListener('change', sincronizarRangoReporte);
        sincronizarRangoReporte();
    }
});
</script>
@endpush
