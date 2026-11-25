@extends('layouts.admin')

@section('title', 'Alertas de Stock | BIO-GEST Tenebrios Gold')
@section('header_title', 'Alertas de Stock y Reabastecimiento')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Alertas de Stock
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Control preventivo de existencias para productos en o por debajo de su stock mínimo en <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.inventario.index') }}" class="tg-btn-gold">
                <i class="fa-solid fa-boxes-stacked"></i> Ver Kardex e Inventario
            </a>
        </div>
    </div>

    <!-- TARJETAS DE RESUMEN DE ALERTAS -->
    <section class="kpi-grid" style="margin-bottom:20px;">
        <div class="kpi-card {{ $conteoCriticos > 0 ? 'kpi-danger' : 'kpi-success' }}" style="border-left:4px solid {{ $conteoCriticos > 0 ? '#dc2626' : '#10b981' }};">
            <div class="kpi-icon" style="background:{{ $conteoCriticos > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ $conteoCriticos > 0 ? '#b91c1c' : '#047857' }};">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Productos en Alerta (Stock &le; Mín.)</span>
                <span class="kpi-value" style="color:{{ $conteoCriticos > 0 ? '#dc2626' : '#047857' }};">
                    {{ $conteoCriticos }}
                </span>
                <span class="kpi-subtext">
                    {{ $conteoCriticos > 0 ? 'Requieren reabastecimiento urgente' : 'Todos sobre el stock mínimo' }}
                </span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #111111;">
            <div class="kpi-icon" style="background:#f3f4f6; color:#111111;">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Total Referencias Monitoreadas</span>
                <span class="kpi-value" style="color:#111111;">{{ $conteoTotal }}</span>
                <span class="kpi-subtext">Productos en catálogo activo</span>
            </div>
        </div>
    </section>

    <!-- LISTADO DE PRODUCTOS CON ALERTA DE STOCK <= STOCK_MINIMO -->
    <div class="tg-card">
        <div class="card-header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:1.15rem; color:#111111; margin:0;">
                <i class="fa-solid fa-bell" style="color:#e6a100;"></i>
                Productos con Stock Bajo o Crítico
            </h2>
        </div>

        @if($conteoCriticos === 0)
            <div style="background:#d1fae5; border:1px solid #a7f3d0; border-radius:8px; padding:16px 18px; margin-bottom:20px; color:#065f46; display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-circle-check" style="font-size:1.4rem;"></i>
                <div>
                    <strong>¡Inventario en estado óptimo!</strong> Ningún producto se encuentra actualmente por debajo de su umbral mínimo de stock. A continuación se presentan las existencias ordenadas por menor disponibilidad para monitoreo preventivo:
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:50px;">Foto</th>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th style="text-align:center;">Stock Actual</th>
                        <th style="text-align:center;">Stock Mínimo</th>
                        <th style="text-align:center;">Nivel y Alerta</th>
                        <th style="text-align:right;">Acción Inmediata</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $listaMostrar = $conteoCriticos > 0 ? $productosCriticos : $todosProductos;
                    @endphp
                    @forelse($listaMostrar as $prod)
                        @php
                            $min = (int) ($prod->stock_minimo ?? 10);
                            $esAgotado = $prod->stock <= 0;
                            $esCritico = $esAgotado || $prod->stock < ($min * 0.5);
                            $pct = $min > 0 ? min(100, round(($prod->stock / max($min * 2, 1)) * 100)) : 100;
                            $imgSrc = $prod->imagen ? asset('assets/img/' . $prod->imagen) : asset('assets/img/Tenebrios_img.png');
                        @endphp
                        <tr style="{{ $esAgotado ? 'background-color:#fff1f2;' : ($prod->stock <= $min ? 'background-color:#fffbeb;' : '') }}">
                            <td>
                                <img src="{{ $imgSrc }}" alt="{{ $prod->nombre_producto }}"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/img/Tenebrios_img.png') }}';"
                                     style="width:38px; height:38px; object-fit:contain; border-radius:4px; background:#fff; border:1px solid #e5e7eb; padding:2px;">
                            </td>
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
                            <td style="text-align:center;">
                                @if($esAgotado)
                                    <span class="badge badge-danger" style="font-weight:800;">
                                        <i class="fa-solid fa-ban"></i> Agotado / Crítico
                                    </span>
                                @elseif($prod->stock <= $min)
                                    <span class="badge badge-danger" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $esCritico ? 'Agotado / Crítico' : 'Stock Bajo' }}
                                    </span>
                                @else
                                    <span class="badge badge-active">
                                        <i class="fa-solid fa-circle-check"></i> Stock Óptimo
                                    </span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <button type="button" class="tg-btn-gold" style="padding:6px 14px; font-size:0.82rem;"
                                        onclick="abrirModalReabastecer({{ $prod->id_producto }}, '{{ addslashes($prod->nombre_producto) }}', {{ $prod->stock }}, {{ $min }})">
                                    <i class="fa-solid fa-plus"></i> Reabastecer
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:28px; color:#6b7280;">
                                No hay productos para mostrar en este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL RÁPIDO PARA REGISTRAR ENTRADA EN KARDEX -->
    <div id="modalReabastecer" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#ffffff; border-radius:10px; width:100%; max-width:480px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); overflow:hidden;">
            <div style="padding:16px 20px; background:#111111; color:#ffffff; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e6a100;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-boxes-packing" style="color:#e6a100; font-size:1.1rem;"></i>
                    <h3 style="margin:0; font-size:1rem; font-weight:800;">Reabastecer Producto (Kardex)</h3>
                </div>
                <button type="button" onclick="cerrarModalReabastecer()" style="background:none; border:none; color:#ffffff; font-size:1.1rem; cursor:pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.inventario.movimiento') }}" method="POST" style="padding:20px;">
                @csrf
                <input type="hidden" name="id_producto" id="modalIdProducto">
                <input type="hidden" name="tipo_movimiento" value="ENTRADA">

                <div style="margin-bottom:14px; background:#f8f9fa; padding:12px; border-radius:6px; border:1px solid #e5e7eb;">
                    <div style="font-weight:800; font-size:0.95rem; color:#111111;" id="modalNombreProducto">-</div>
                    <div style="font-size:0.8rem; color:#6b7280; margin-top:3px;">
                        Stock actual: <strong id="modalStockActual" style="color:#b91c1c;">0</strong> uds. | Stock Mínimo: <span id="modalStockMinimo">0</span> uds.
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                        Cantidad de Unidades a Ingresar (ENTRADA) *
                    </label>
                    <input type="number" name="cantidad" id="modalCantidad" min="1" value="20" required
                           style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.92rem; font-weight:700;">
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                        Motivo / Observación del Kardex *
                    </label>
                    <input type="text" name="observacion" id="modalObservacion" value="Reabastecimiento rápido por alerta de stock bajo" required maxlength="255"
                           style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e5e7eb; padding-top:14px;">
                    <button type="button" onclick="cerrarModalReabastecer()" style="padding:9px 16px; border:1px solid #cbd5e1; border-radius:6px; background:#ffffff; color:#4b5563; font-weight:600; cursor:pointer;">
                        Cancelar
                    </button>
                    <button type="submit" class="tg-btn-dark" style="padding:9px 20px;">
                        <i class="fa-solid fa-check"></i> Registrar ENTRADA
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function abrirModalReabastecer(id, nombre, stock, min) {
        document.getElementById('modalIdProducto').value = id;
        document.getElementById('modalNombreProducto').textContent = nombre;
        document.getElementById('modalStockActual').textContent = stock;
        document.getElementById('modalStockMinimo').textContent = min;
        document.getElementById('modalCantidad').value = Math.max(10, min * 2);
        document.getElementById('modalObservacion').value = 'Reabastecimiento rápido por alerta de stock bajo (Lote de cría)';

        const modal = document.getElementById('modalReabastecer');
        modal.style.display = 'flex';
    }

    function cerrarModalReabastecer() {
        document.getElementById('modalReabastecer').style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modalReabastecer');
        if (e.target === modal) {
            cerrarModalReabastecer();
        }
    });
</script>
@endpush
