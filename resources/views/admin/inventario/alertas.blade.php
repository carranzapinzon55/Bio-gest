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
                <i class="fa-solid fa-boxes-stacked"></i> Ver Inventario y Movimientos
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

        <!-- FILTROS Y BUSCADOR -->
        <div style="background:#f8f9fa; border:1px solid #e5e7eb; border-radius:8px; padding:14px; margin-bottom:18px;">
            <form action="{{ route('admin.alertas-stock') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Buscar por nombre</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ej. Tenebrio molitor, Pie de cría..."
                           style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                </div>

                <div style="min-width:180px;">
                    <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Nivel de Severidad</label>
                    <select name="severidad" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                        <option value="">Todas las alertas</option>
                        <option value="critico" {{ request('severidad') === 'critico' ? 'selected' : '' }}>Agotado / Crítico (&le; 0)</option>
                        <option value="bajo" {{ request('severidad') === 'bajo' ? 'selected' : '' }}>Stock Bajo (&le; Mínimo)</option>
                    </select>
                </div>

                <button type="submit" class="tg-btn-dark" style="padding:8px 16px; font-size:0.88rem;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>

                @if(request()->hasAny(['q', 'severidad']))
                    <a href="{{ route('admin.alertas-stock') }}" class="tg-btn-outline" style="padding:8px 14px; font-size:0.88rem;">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <!-- TABLA DE ALERTAS -->
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:50px;">Foto</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th style="text-align:center;">Stock Actual</th>
                        <th style="text-align:center;">Stock Mínimo</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertas as $prod)
                        @php
                            $min = (int) ($prod->stock_minimo ?? 10);
                            $esAgotado = $prod->stock <= 0;
                            $esCritico = $prod->stock <= 2;
                            $imgSrc = $prod->imagen ? asset('assets/img/' . $prod->imagen) : asset('assets/img/Tenebrios_img.png');
                        @endphp
                        <tr style="{{ $esAgotado ? 'background-color:#fef2f2;' : ($esCritico ? 'background-color:#fffbeb;' : '') }}">
                            <td>
                                <img src="{{ $imgSrc }}" alt="{{ $prod->nombre_producto }}"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/img/Tenebrios_img.png') }}';"
                                     style="width:38px; height:38px; object-fit:contain; border-radius:6px; background:#fff; border:1px solid #e5e7eb; padding:2px;">
                            </td>
                            <td>
                                <div style="font-weight:700; color:#111111;">{{ $prod->nombre_producto }}</div>
                                <div style="font-size:0.75rem; color:#6b7280;">Ref: #{{ $prod->id_producto }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background:#f3f4f6; color:#4b5563;">
                                    {{ optional($prod->categoria)->nombre_categoria ?? 'Sin Categoría' }}
                                </span>
                            </td>
                            <td style="text-align:center; font-weight:800; font-size:1.05rem; color:{{ $esAgotado ? '#dc2626' : '#d97706' }};">
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
                                <!-- Botón Reabastecer que redirige al formulario de inventario con el producto preseleccionado -->
                                <a href="{{ route('admin.inventario.index', ['producto_id' => $prod->id_producto]) }}"
                                   class="tg-btn-gold" style="padding:6px 14px; font-size:0.82rem; text-decoration:none;">
                                    <i class="fa-solid fa-plus"></i> Reabastecer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:28px; color:#6b7280;">
                                No hay productos para mostrar en este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
