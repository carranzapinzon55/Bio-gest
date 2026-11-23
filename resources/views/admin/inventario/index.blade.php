@extends('layouts.app')

@section('title', 'Gestión de Inventario y Kardex | BIO-GEST Tenebrios Gold')
@section('header_title', 'Control de Inventario y Movimientos (Kardex)')

@section('content')
    <!-- ALERTAS VISUALES DE ADVERTENCIA CUANDO EXISTAN PRODUCTOS CON STOCK BAJO -->
    @if($productosStockBajo->isNotEmpty())
        <div class="alert-flash alert-warning" style="flex-direction:column; align-items:flex-start;">
            <div style="display:flex; align-items:center; gap:10px; font-size:1rem;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:1.25rem; color:#d97706;"></i>
                <strong>¡Alerta de Stock Bajo / Agotado! ({{ $productosStockBajo->count() }} producto(s) requieren reposición)</strong>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:8px;">
                @foreach($productosStockBajo as $alertaProd)
                    <span style="background:#fff; border:1px solid #f59e0b; color:#92400e; padding:5px 12px; border-radius:20px; font-size:0.84rem; font-weight:700;">
                        {{ $alertaProd->nombre_producto }}:
                        <strong style="color:#b91c1c;">{{ $alertaProd->stock }} uds.</strong>
                        (Mínimo requerido: {{ $alertaProd->stock_minimo ?? 10 }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- KPIs de Inventario -->
    <section class="kpi-grid" style="margin-bottom:22px;">
        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-boxes-stacked kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Referencias Totales</h3>
                <div class="kpi-value" style="color:#151515;">{{ $resumenInventario['total_productos'] }}</div>
                <span class="kpi-subtext">Productos registrados</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-layer-group kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Existencias Totales</h3>
                <div class="kpi-value" style="color:#151515;">{{ number_format($resumenInventario['unidades_totales'], 0, ',', '.') }} uds.</div>
                <span class="kpi-subtext">Unidades disponibles</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #e4b353;">
            <i class="fa-solid fa-coins kpi-icon" style="background:rgba(228,179,83,0.2); color:#151515;"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Valorización Stock</h3>
                <div class="kpi-value" style="color:#151515;">${{ number_format($resumenInventario['valor_inventario'], 0, ',', '.') }}</div>
                <span class="kpi-subtext">Valor comercial actual</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid {{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#dc2626' : '#16a34a' }};">
            <i class="fa-solid fa-bell kpi-icon" style="background:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#fee2e2' : '#dcfce7' }}; color:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#b91c1c' : '#15803d' }};"></i>
            <div>
                <h3 style="font-size:0.8rem; text-transform:uppercase; color:#64748b;">Alertas de Stock Bajo</h3>
                <div class="kpi-value" style="color:{{ $resumenInventario['alertas_stock_bajo'] > 0 ? '#b91c1c' : '#15803d' }};">
                    {{ $resumenInventario['alertas_stock_bajo'] }}
                </div>
                <span class="kpi-subtext">{{ $resumenInventario['alertas_stock_bajo'] > 0 ? 'Atención prioritaria' : 'Todo en nivel óptimo' }}</span>
            </div>
        </div>
    </section>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:22px; align-items:start;">
        <!-- SECCIÓN 1: EXISTENCIAS ACTUALES POR PRODUCTO -->
        <div class="tg-card" style="grid-column: span 2;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
                <h2 style="margin:0; font-size:1.15rem; color:#151515;">
                    <i class="fa-solid fa-clipboard-check" style="color:#e4b353;"></i> Existencias Actuales y Disponibilidad por Producto
                </h2>
                <a href="{{ route('admin.productos.index') }}" style="font-size:0.85rem; color:#151515; font-weight:700; text-decoration:none;">
                    Ir al CRUD de Productos &rarr;
                </a>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th style="text-align:center;">Stock Mínimo</th>
                            <th style="text-align:center;">Stock Disponible</th>
                            <th>Nivel de Stock</th>
                            <th>Ajuste Rápido</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $prod)
                            @php
                                $minimo = $prod->stock_minimo ?? 10;
                                $esAgotado = $prod->stock <= 0;
                                $esBajo = !$esAgotado && $prod->stock <= $minimo;
                            @endphp
                            <tr style="{{ $esAgotado ? 'background-color:#fff1f2;' : ($esBajo ? 'background-color:#fffbeb;' : '') }}">
                                <td style="font-weight:700;">INV-{{ str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div style="font-weight:700; color:#151515;">{{ $prod->nombre_producto }}</div>
                                    <div style="font-size:0.76rem; color:#64748b;">Precio: ${{ number_format($prod->precio, 0, ',', '.') }}</div>
                                </td>
                                <td>{{ optional($prod->categoria)->nombre_categoria }}</td>
                                <td style="text-align:center; color:#64748b; font-weight:600;">{{ $minimo }} uds.</td>
                                <td style="text-align:center;">
                                    <span style="font-size:1.05rem; font-weight:800; color:{{ $esAgotado || $esBajo ? '#b91c1c' : '#151515' }};">
                                        {{ $prod->stock }} uds.
                                    </span>
                                </td>
                                <td>
                                    @if($esAgotado)
                                        <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> AGOTADO</span>
                                    @elseif($esBajo)
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> STOCK BAJO</span>
                                    @else
                                        <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> DISPONIBLE</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.productos.stock', $prod->id_producto) }}" method="POST" style="display:inline-flex; align-items:center; gap:5px;">
                                        @csrf
                                        <input type="number" name="cantidad" value="5" min="1" required
                                               style="width:58px; padding:5px 6px; border:1px solid #cbd5e1; border-radius:4px; font-size:0.82rem;">
                                        <input type="hidden" name="observacion" value="Ajuste rápido desde tabla de existencias">
                                        <button type="submit" name="tipo_movimiento" value="ENTRADA"
                                                style="background:#151515; color:#e4b353; border:none; border-radius:4px; padding:5px 9px; cursor:pointer; font-weight:700; font-size:0.8rem;"
                                                title="Registrar Entrada (+)">
                                            + Entrada
                                        </button>
                                        <button type="submit" name="tipo_movimiento" value="SALIDA"
                                                style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; border-radius:4px; padding:5px 9px; cursor:pointer; font-weight:700; font-size:0.8rem;"
                                                title="Registrar Salida (-)">
                                            - Salida
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECCIÓN 2: FORMULARIO PARA REGISTRAR MOVIMIENTO EN KARDEX -->
        <div class="tg-card">
            <h2 style="margin:0 0 14px 0; font-size:1.1rem; color:#151515; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
                <i class="fa-solid fa-arrows-rotate" style="color:#e4b353;"></i> Registrar Movimiento de Stock
            </h2>

            <form action="{{ route('admin.inventario.movimiento') }}" method="POST">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#151515; margin-bottom:5px;">Producto *</label>
                    <select name="id_producto" required style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px;">
                        <option value="">-- Seleccionar producto --</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id_producto }}" {{ old('id_producto') == $prod->id_producto ? 'selected' : '' }}>
                                {{ $prod->nombre_producto }} (Actual: {{ $prod->stock }} uds.)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:0.85rem; font-weight:700; color:#151515; margin-bottom:5px;">Tipo Movimiento *</label>
                        <select name="tipo_movimiento" required style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="ENTRADA" {{ old('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA (+)</option>
                            <option value="SALIDA" {{ old('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>SALIDA (-)</option>
                            <option value="AJUSTE" {{ old('tipo_movimiento') === 'AJUSTE' ? 'selected' : '' }}>AJUSTE (=)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.85rem; font-weight:700; color:#151515; margin-bottom:5px;">Cantidad *</label>
                        <input type="number" name="cantidad" min="1" value="{{ old('cantidad', 10) }}" required
                               style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:0.85rem; font-weight:700; color:#151515; margin-bottom:5px;">Observación / Motivo *</label>
                    <input type="text" name="observacion" value="{{ old('observacion') }}" required maxlength="255"
                           placeholder="Ej. Cosecha lote #14 / Salida por merma"
                           style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>

                <button type="submit" class="tg-btn-dark" style="width:100%; justify-content:center;">
                    <i class="fa-solid fa-check-circle"></i> Aplicar Movimiento al Stock
                </button>
            </form>
        </div>
    </div>

    <!-- SECCIÓN 3: HISTORIAL DE ENTRADAS Y SALIDAS DE INVENTARIO (KARDEX) -->
    <div class="tg-card" style="margin-top:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:16px;">
            <div>
                <h2 style="margin:0; font-size:1.15rem; color:#151515;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#e4b353;"></i> Historial de Entradas y Salidas de Inventario
                </h2>
                <p style="margin:3px 0 0 0; font-size:0.84rem; color:#64748b;">
                    Auditoría completa de movimientos por compras en tienda, cancelaciones y ajustes manuales
                </p>
            </div>

            <!-- Filtro de Historial -->
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
                    <a href="{{ route('admin.inventario.index') }}" style="font-size:0.82rem; color:#64748b; text-decoration:underline;">Limpiar</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID Mov.</th>
                        <th>Fecha y Hora</th>
                        <th>Producto</th>
                        <th>Tipo de Movimiento</th>
                        <th style="text-align:center;">Cantidad</th>
                        <th style="text-align:center;">Stock Actual</th>
                        <th>Observación / Detalle de Auditoría</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $mov)
                        <tr>
                            <td style="font-weight:700;">#MOV-{{ str_pad((string) $mov->id_movimiento, 4, '0', STR_PAD_LEFT) }}</td>
                            <td style="white-space:nowrap;">{{ \Illuminate\Support\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}</td>
                            <td style="font-weight:700; color:#151515;">{{ optional($mov->producto)->nombre_producto ?? 'Producto #' . $mov->id_producto }}</td>
                            <td>
                                @if($mov->tipo_movimiento === 'ENTRADA')
                                    <span class="badge badge-active"><i class="fa-solid fa-arrow-up"></i> ENTRADA</span>
                                @elseif($mov->tipo_movimiento === 'SALIDA')
                                    <span class="badge badge-danger"><i class="fa-solid fa-arrow-down"></i> SALIDA</span>
                                @else
                                    <span class="badge badge-info"><i class="fa-solid fa-sliders"></i> AJUSTE</span>
                                @endif
                            </td>
                            <td style="text-align:center; font-weight:800; color:{{ $mov->tipo_movimiento === 'ENTRADA' ? '#15803d' : ($mov->tipo_movimiento === 'SALIDA' ? '#b91c1c' : '#0369a1') }};">
                                {{ $mov->tipo_movimiento === 'ENTRADA' ? '+' : ($mov->tipo_movimiento === 'SALIDA' ? '-' : '=') }}{{ $mov->cantidad }} uds.
                            </td>
                            <td style="text-align:center; font-weight:700;">
                                {{ optional($mov->producto)->stock ?? 0 }} uds.
                            </td>
                            <td style="color:#475569;">{{ $mov->observacion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:25px; color:#64748b;">
                                No hay movimientos de inventario registrados para el filtro seleccionado.
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
