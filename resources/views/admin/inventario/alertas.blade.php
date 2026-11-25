@extends('layouts.admin')

@section('title', 'Alertas de Stock Bajo | BIO-GEST Tenebrios Gold')
@section('header_title', 'Alertas de Stock y Reabastecimiento')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Alertas de Stock Bajo
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Control preventivo de existencias para evitar roturas de inventario en <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.inventario.index') }}" class="tg-btn-gold">
                <i class="fa-solid fa-boxes-stacked"></i> Ver Todo el Inventario
            </a>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN DE ALERTAS -->
    <section class="kpi-grid">
        <div class="kpi-card {{ $conteoCriticos > 0 ? 'kpi-danger' : 'kpi-success' }}">
            <div class="kpi-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Productos en Nivel Crítico</span>
                <span class="kpi-value" style="color:{{ $conteoCriticos > 0 ? '#dc2626' : '#047857' }};">
                    {{ $conteoCriticos }}
                </span>
                <span class="kpi-subtext">
                    {{ $conteoCriticos > 0 ? 'Stock igual o menor al mínimo' : 'Sin alertas críticas activas' }}
                </span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Total de Productos en Catálogo</span>
                <span class="kpi-value">{{ $conteoTotal }}</span>
                <span class="kpi-subtext">Existencias monitoreadas</span>
            </div>
        </div>
    </section>

    <!-- LISTADO DE PRODUCTOS CON ALERTA O MONITOREO -->
    <div class="tg-card">
        <div class="card-header-flex">
            <h2>
                <i class="fa-solid fa-clipboard-list" style="color:#e6a100;"></i>
                Monitoreo de Existencias y Puntos de Reorden
            </h2>
        </div>

        @if($conteoCriticos === 0)
            <div style="background:#d1fae5; border:1px solid #a7f3d0; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#065f46; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-circle-check" style="font-size:1.2rem;"></i>
                <div>
                    <strong>¡Excelente estado de inventario!</strong> Ningún producto se encuentra actualmente por debajo de su umbral mínimo de stock. A continuación se presentan las existencias ordenadas por menor disponibilidad:
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th style="text-align:center;">Stock Actual</th>
                        <th style="text-align:center;">Stock Mínimo</th>
                        <th>Nivel de Existencias</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Acción Inmediata</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $listaMostrar = $conteoCriticos > 0 ? $productosCriticos : $todosProductos;
                    @endphp
                    @forelse($listaMostrar as $prod)
                        @php
                            $min = $prod->stock_minimo ?? 10;
                            $pct = $min > 0 ? min(100, round(($prod->stock / max($min * 2, 1)) * 100)) : 100;
                            if ($prod->stock <= 0) {
                                $barClass = 'danger';
                                $badgeClass = 'badge-critico';
                                $estado = 'Agotado';
                            } elseif ($prod->stock <= $min) {
                                $barClass = 'danger';
                                $badgeClass = 'badge-critico';
                                $estado = 'Stock Crítico';
                            } elseif ($prod->stock <= ($min * 1.5)) {
                                $barClass = 'warning';
                                $badgeClass = 'badge-alerta';
                                $estado = 'Precaución';
                            } else {
                                $barClass = 'success';
                                $badgeClass = 'badge-activo';
                                $estado = 'Óptimo';
                            }
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:#6b7280; white-space:nowrap;">
                                PROD-{{ str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                <strong style="color:#111111;">{{ $prod->nombre_producto }}</strong>
                            </td>
                            <td style="color:#6b7280;">{{ $prod->categoria?->nombre_categoria ?? 'General' }}</td>
                            <td style="text-align:center; font-weight:800; font-size:1rem; color:{{ $prod->stock <= $min ? '#dc2626' : '#111111' }};">
                                {{ $prod->stock }} uds.
                            </td>
                            <td style="text-align:center; font-weight:600; color:#6b7280;">
                                {{ $min }} uds.
                            </td>
                            <td>
                                <div class="stock-level-container">
                                    <div class="stock-level-bar" style="width:110px;">
                                        <div class="stock-level-fill {{ $barClass }}" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    <span style="font-size:0.75rem; font-weight:700; color:#4b5563;">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge {{ $badgeClass }}">
                                    {{ $estado }}
                                </span>
                            </td>
                            <td style="text-align:center; white-space:nowrap;">
                                <a href="{{ route('admin.inventario.index', ['id_producto' => $prod->id_producto]) }}" class="tg-btn-gold" style="padding:5px 12px; font-size:0.8rem;">
                                    <i class="fa-solid fa-boxes-packing"></i> Reabastecer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:24px; color:#6b7280;">
                                No hay productos para mostrar en este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
