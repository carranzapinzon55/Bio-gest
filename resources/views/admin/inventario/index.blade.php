@extends('layouts.admin')

@section('title', 'Gestión de Inventario y Kardex | BIO-GEST Tenebrios Gold')
@section('header_title', 'Inventario y Kardex')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#111111;">Inventario y Kardex</h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.9rem;">
                Control de existencias, entradas, salidas y ajustes de productos
            </p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.alertas-stock') }}" class="tg-btn-gold">
                <i class="fa-solid fa-triangle-exclamation"></i> Ver Alertas de Stock ({{ $resumenInventario['alertas_stock_bajo'] }})
            </a>
            <button type="button" class="tg-btn-dark" onclick="document.getElementById('formMovimientoWrap').scrollIntoView({behavior:'smooth'});">
                <i class="fa-solid fa-plus"></i> Registrar Movimiento
            </button>
        </div>
    </div>

    <!-- ALERTAS VISUALES CUANDO EXISTAN PRODUCTOS CON STOCK BAJO -->
    @if($productosStockBajo->isNotEmpty())
        <div class="alert-flash alert-warning" style="flex-direction:column; align-items:flex-start; margin-bottom:20px;">
            <div style="display:flex; align-items:center; gap:10px; font-size:0.95rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem; color:#d97706;"></i>
                <strong>¡Alerta de Reabastecimiento! ({{ $productosStockBajo->count() }} producto(s) en o por debajo de su stock mínimo)</strong>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:8px;">
                @foreach($productosStockBajo as $alertaProd)
                    <span style="background:#ffffff; border:1px solid #f59e0b; color:#92400e; padding:5px 12px; border-radius:20px; font-size:0.84rem; font-weight:700;">
                        {{ $alertaProd->nombre_producto }}:
                        <strong style="color:#b91c1c;">{{ $alertaProd->stock }} uds.</strong>
                        (Mínimo: {{ $alertaProd->stock_minimo ?? 10 }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- KPIs de Inventario -->
    <section class="kpi-grid" style="margin-bottom:22px;">
        <div class="kpi-card" style="border-left:4px solid #111111;">
            <div class="kpi-icon" style="background:#f3f4f6; color:#111111;">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Productos Registrados</span>
                <span class="kpi-value" style="color:#111111;">{{ $resumenInventario['total_productos'] }}</span>
                <span class="kpi-subtext">Catálogo activo</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #e6a100;">
            <div class="kpi-icon" style="background:#fef3c7; color:#b45309;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Existencias Totales</span>
                <span class="kpi-value" style="color:#b45309;">{{ number_format($resumenInventario['unidades_totales'], 0, ',', '.') }} uds.</span>
                <span class="kpi-subtext">Unidades en bodega</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #0284c7;">
            <div class="kpi-icon" style="background:#e0f2fe; color:#0369a1;">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Valorización Stock</span>
                <span class="kpi-value" style="color:#0369a1;">${{ number_format($resumenInventario['valor_inventario'], 0, ',', '.') }}</span>
                <span class="kpi-subtext">Valor comercial total</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid {{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#dc2626' : '#10b981' }};">
            <div class="kpi-icon" style="background:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#fee2e2' : '#d1fae5' }}; color:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#b91c1c' : '#047857' }};">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Alertas de Stock</span>
                <span class="kpi-value" style="color:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#b91c1c' : '#047857' }};">
                    {{ $resumenInventario['alertas_stock_bajo'] }}
                </span>
                <span class="kpi-subtext">{{ $resumenInventario['alertas_stock_bajo'] > 0 ? 'Stock igual o menor a mínimo' : 'Stock en niveles óptimos' }}</span>
            </div>
        </div>
    </section>

    <!-- SECCIÓN SUPERIOR: EXISTENCIAS ACTUALES POR PRODUCTO + FORMULARIO RÁPIDO DE MOVIMIENTOS -->
    <div style="display:grid; grid-template-columns: minmax(0, 1.8fr) minmax(340px, 1fr); gap:20px; align-items:start; margin-bottom:24px;">
        <!-- Existencias por Producto -->
        <div class="tg-card">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
                <h2 style="margin:0; font-size:1.15rem; color:#111111;">
                    <i class="fa-solid fa-cubes" style="color:#e6a100;"></i> Existencias Actuales por Producto
                </h2>
                <a href="{{ route('admin.productos.index') }}" style="font-size:0.85rem; color:#111111; font-weight:700; text-decoration:none;">
                    Gestionar Productos &rarr;
                </a>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width:50px;">Foto</th>
                            <th>Producto</th>
                            <th style="text-align:center;">Stock Actual</th>
                            <th style="text-align:center;">Stock Mín.</th>
                            <th style="text-align:center;">Estado Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            @php
                                $minimo = (int) ($prod->stock_minimo ?? 10);
                                $esAgotado = $prod->stock <= 0;
                                $esBajo = !$esAgotado && $prod->stock <= $minimo;
                                $imgSrc = $prod->imagen ? asset('assets/img/' . $prod->imagen) : asset('assets/img/Tenebrios_img.png');
                            @endphp
                            <tr style="{{ $esAgotado ? 'background-color:#fff1f2;' : ($esBajo ? 'background-color:#fffbeb;' : '') }}">
                                <td>
                                    <img src="{{ $imgSrc }}" alt="{{ $prod->nombre_producto }}"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/img/Tenebrios_img.png') }}';"
                                         style="width:36px; height:36px; object-fit:contain; border-radius:4px; background:#fff; border:1px solid #e5e7eb; padding:2px;">
                                </td>
                                <td>
                                    <div style="font-weight:700; color:#111111;">{{ $prod->nombre_producto }}</div>
                                    <div style="font-size:0.75rem; color:#6b7280;">{{ optional($prod->categoria)->nombre_categoria }} · ${{ number_format($prod->precio, 0, ',', '.') }}</div>
                                </td>
                                <td style="text-align:center;">
                                    <span style="font-size:1.05rem; font-weight:800; color:{{ $esAgotado || $esBajo ? '#dc2626' : '#111111' }};">
                                        {{ $prod->stock }} uds.
                                    </span>
                                </td>
                                <td style="text-align:center; color:#6b7280; font-weight:600;">
                                    {{ $minimo }} uds.
                                </td>
                                <td style="text-align:center;">
                                    @if($esAgotado)
                                        <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> AGOTADO</span>
                                    @elseif($esBajo)
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> STOCK BAJO</span>
                                    @else
                                        <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> DISPONIBLE</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulario Manual: Registrar Movimiento en Kardex -->
        <div class="tg-card" id="formMovimientoWrap">
            <h2 style="margin:0 0 14px 0; font-size:1.1rem; color:#111111; border-bottom:1px solid #e5e7eb; padding-bottom:10px;">
                <i class="fa-solid fa-arrows-rotate" style="color:#e6a100;"></i> Registrar Movimiento Manual
            </h2>

            <form action="{{ route('admin.inventario.movimiento') }}" method="POST">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Producto *</label>
                    <select name="id_producto" required style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                        <option value="">-- Seleccionar producto --</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id_producto }}" {{ old('id_producto') == $prod->id_producto ? 'selected' : '' }}>
                                {{ $prod->nombre_producto }} (Stock: {{ $prod->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Tipo de Movimiento *</label>
                        <select name="tipo_movimiento" required style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="ENTRADA" {{ old('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA (+)</option>
                            <option value="SALIDA" {{ old('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>SALIDA (-)</option>
                            <option value="AJUSTE" {{ old('tipo_movimiento') === 'AJUSTE' ? 'selected' : '' }}>AJUSTE (=)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Cantidad *</label>
                        <input type="number" name="cantidad" min="1" value="{{ old('cantidad', 10) }}" required
                               style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Motivo / Observación *</label>
                    <input type="text" name="observacion" value="{{ old('observacion') }}" required maxlength="255"
                           placeholder="Ej. Cosecha lote semanal / Ajuste conteo físico"
                           style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                </div>

                <button type="submit" class="tg-btn-dark" style="width:100%; justify-content:center; padding:10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Aplicar al Kardex y MySQL
                </button>
            </form>
        </div>
    </div>

    <!-- SECCIÓN INFERIOR: HISTORIAL KARDEX COMPLETO -->
    <div class="tg-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:16px;">
            <div>
                <h2 style="margin:0; font-size:1.15rem; color:#111111;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#e6a100;"></i> Historial Kardex
                </h2>
                <p style="margin:3px 0 0 0; font-size:0.84rem; color:#6b7280;">
                    Registro de movimientos de inventario leyendo la tabla movimiento_inventario
                </p>
            </div>

            <!-- Filtros de Kardex -->
            <form action="{{ route('admin.inventario.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                <select name="id_producto" style="padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem;">
                    <option value="">Todos los productos</option>
                    @foreach($productos as $prod)
                        <option value="{{ $prod->id_producto }}" {{ (string) request('id_producto') === (string) $prod->id_producto ? 'selected' : '' }}>
                            {{ $prod->nombre_producto }}
                        </option>
                    @endforeach
                </select>

                <select name="tipo_movimiento" style="padding:7px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem;">
                    <option value="">Todos los tipos</option>
                    <option value="ENTRADA" {{ request('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA</option>
                    <option value="SALIDA" {{ request('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>SALIDA</option>
                    <option value="AJUSTE" {{ request('tipo_movimiento') === 'AJUSTE' ? 'selected' : '' }}>AJUSTE</option>
                </select>

                <button type="submit" class="tg-btn-gold" style="padding:7px 14px; font-size:0.84rem;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                @if(request()->hasAny(['id_producto', 'tipo_movimiento']))
                    <a href="{{ route('admin.inventario.index') }}" style="font-size:0.82rem; color:#6b7280; text-decoration:underline;">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th style="text-align:center;">Tipo</th>
                        <th style="text-align:center;">Cantidad</th>
                        <th>Motivo / Observación</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        <tr>
                            <td style="white-space:nowrap; font-size:0.84rem; color:#4b5563;">
                                {{ \Illuminate\Support\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}
                            </td>
                            <td style="font-weight:700; color:#111111;">
                                {{ optional($mov->producto)->nombre_producto ?? ('Producto #' . $mov->id_producto) }}
                            </td>
                            <td style="text-align:center;">
                                @if($mov->tipo_movimiento === 'ENTRADA')
                                    <span class="badge badge-active"><i class="fa-solid fa-arrow-up"></i> ENTRADA</span>
                                @elseif($mov->tipo_movimiento === 'SALIDA')
                                    <span class="badge badge-danger"><i class="fa-solid fa-arrow-down"></i> SALIDA</span>
                                @else
                                    <span class="badge badge-info"><i class="fa-solid fa-sliders"></i> AJUSTE</span>
                                @endif
                            </td>
                            <td style="text-align:center; font-weight:800; font-size:0.95rem; color:{{ $mov->tipo_movimiento === 'ENTRADA' ? '#047857' : ($mov->tipo_movimiento === 'SALIDA' ? '#dc2626' : '#0369a1') }};">
                                {{ $mov->tipo_movimiento === 'ENTRADA' ? '+' : ($mov->tipo_movimiento === 'SALIDA' ? '-' : '=') }}{{ number_format($mov->cantidad, 0, ',', '.') }} uds.
                            </td>
                            <td style="color:#374151; font-size:0.85rem;">
                                {{ $mov->observacion }}
                            </td>
                            <td style="white-space:nowrap; font-size:0.82rem; color:#6b7280;">
                                <i class="fa-solid fa-user-shield" style="color:#e6a100; margin-right:4px;"></i> Administrador
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:28px; color:#6b7280;">
                                No se encontraron movimientos de Kardex registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">
            {{ $movimientos->links() }}
        </div>
    </div>
@endsection
