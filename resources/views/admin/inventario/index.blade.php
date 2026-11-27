@extends('layouts.admin')

@section('title', 'Gestión de Inventario y Movimientos | BIO-GEST Tenebrios Gold')
@section('header_title', 'Inventario y Registro de Movimientos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#111111;">Inventario y Movimientos</h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.9rem;">
                Control de existencias actuales y registro continuo de entradas, salidas y ajustes de productos
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
    <div class="admin-split-layout split-wide" style="margin-bottom:24px;">
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
                <table class="custom-table" id="tablaExistenciasProductos">
                    <thead>
                        <tr>
                            <th style="width:50px;">Foto</th>
                            <th>Producto</th>
                            <th style="text-align:center;">Stock Actual</th>
                            <th style="text-align:center;">Stock Mín.</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $preselectedId = request('id_producto') ?? request('producto_id');
                        @endphp
                        @foreach($productos as $prod)
                            @php
                                $minimo = (int) ($prod->stock_minimo ?? 10);
                                $esAgotado = $prod->stock <= 0;
                                $esBajo = !$esAgotado && $prod->stock <= $minimo;
                                $imgSrc = $prod->imagen ? asset('assets/img/' . $prod->imagen) : asset('assets/img/Tenebrios_img.png');
                                $esSeleccionado = ((string)$preselectedId === (string)$prod->id_producto);
                            @endphp
                            <tr id="fila-producto-{{ $prod->id_producto }}" class="fila-producto {{ $esSeleccionado ? 'fila-resaltada' : '' }}"
                                style="{{ $esSeleccionado ? 'border-left:4px solid #e6a100; background-color:#fdf8f0;' : ($esAgotado ? 'background-color:#fff1f2;' : ($esBajo ? 'background-color:#fffbeb;' : '')) }}; transition:all 0.22s ease;">
                                <td>
                                    <img src="{{ $imgSrc }}" alt="{{ $prod->nombre_producto }}"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/img/Tenebrios_img.png') }}';"
                                         style="width:38px; height:38px; object-fit:contain; border-radius:6px; background:#fff; border:1px solid #e5e7eb; padding:2px;">
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
                                <td style="text-align:right;">
                                    <button type="button" class="tg-btn-gold" style="padding:4px 10px; font-size:0.78rem;"
                                            onclick="seleccionarProductoParaMovimiento({{ $prod->id_producto }})">
                                        <i class="fa-solid fa-pen-to-square"></i> Mover
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulario Manual: Registrar Movimiento en Inventario -->
        <div class="tg-card" id="formMovimientoWrap" style="border-top:3px solid #e6a100;">
            <h2 style="margin:0 0 14px 0; font-size:1.1rem; color:#111111; border-bottom:1px solid #e5e7eb; padding-bottom:10px;">
                <i class="fa-solid fa-arrows-rotate" style="color:#e6a100;"></i> Registrar Movimiento Manual
            </h2>

            <!-- Tarjeta Visual del Producto Seleccionado en Tiempo Real -->
            <div id="previewProductoSeleccionado" style="display:none; background:#fdf8f0; border:1px solid #fde68a; border-radius:8px; padding:12px; margin-bottom:15px; align-items:center; gap:14px; transition:all 0.22s ease;">
                <img id="previewImg" src="{{ asset('assets/img/Tenebrios_img.png') }}" alt="Preview"
                     style="width:55px; height:55px; object-fit:contain; border-radius:6px; background:#ffffff; border:1px solid #fcd34d; padding:2px;">
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:800; font-size:0.95rem; color:#111111;" id="previewNombre">-</div>
                    <div style="font-size:0.8rem; color:#6b7280;" id="previewCategoria">-</div>
                    <div style="font-size:0.82rem; margin-top:2px;">
                        Stock actual: <strong id="previewStock" style="color:#e6a100; font-size:0.95rem;">0</strong> uds.
                        <span id="previewStockMinimoBadge" style="font-size:0.75rem; color:#6b7280; margin-left:6px;"></span>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.inventario.movimiento') }}" method="POST">
                @csrf
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Seleccionar Producto *</label>
                    <select name="id_producto" id="selectProductoMovimiento" required onchange="actualizarPreviewProducto()"
                            style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                        <option value="">-- Seleccionar producto del catálogo --</option>
                        @foreach($productos as $prod)
                            @php
                                $img = $prod->imagen ? asset('assets/img/' . $prod->imagen) : asset('assets/img/Tenebrios_img.png');
                            @endphp
                            <option value="{{ $prod->id_producto }}"
                                    data-nombre="{{ $prod->nombre_producto }}"
                                    data-stock="{{ $prod->stock }}"
                                    data-minimo="{{ $prod->stock_minimo ?? 10 }}"
                                    data-categoria="{{ optional($prod->categoria)->nombre_categoria ?? 'General' }}"
                                    data-imagen="{{ $img }}"
                                    {{ (string) old('id_producto', $preselectedId) === (string) $prod->id_producto ? 'selected' : '' }}>
                                {{ $prod->nombre_producto }} (Stock actual: {{ $prod->stock }} uds.)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-form-grid-2-compact" style="margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Tipo de Movimiento *</label>
                        <select name="tipo_movimiento" required style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="ENTRADA" {{ old('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>ENTRADA (+ ingreso)</option>
                            <option value="SALIDA" {{ old('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>SALIDA (- despacho)</option>
                            <option value="AJUSTE" {{ old('tipo_movimiento') === 'AJUSTE' ? 'selected' : '' }}>AJUSTE (= conteo físico)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Cantidad *</label>
                        <input type="number" name="cantidad" min="1" value="{{ old('cantidad', 10) }}" required
                               style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">Motivo / Observación del Movimiento *</label>
                    <input type="text" name="observacion" value="{{ old('observacion') }}" required maxlength="255"
                           placeholder="Ej. Cosecha lote semanal / Ajuste conteo físico"
                           style="width:100%; padding:9px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                </div>

                <button type="submit" class="tg-btn-dark" style="width:100%; justify-content:center; padding:10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar Movimiento en MySQL
                </button>
            </form>
        </div>
    </div>

    <!-- SECCIÓN INFERIOR: HISTORIAL DE MOVIMIENTOS (ENTRADAS Y SALIDAS) CON BUSCADOR EN TIEMPO REAL -->
    <div class="tg-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:16px;">
            <div>
                <h2 style="margin:0; font-size:1.15rem; color:#111111;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#e6a100;"></i> Historial de Movimientos de Inventario
                </h2>
                <p style="margin:3px 0 0 0; font-size:0.84rem; color:#6b7280;">
                    Registro continuo de entradas, salidas y ajustes de stock
                </p>
            </div>

            <!-- Filtros y Buscador en Tiempo Real -->
            <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                <!-- Buscador en tiempo real de la tabla -->
                <div style="position:relative;">
                    <input type="text" id="buscadorTiempoRealMovimientos" placeholder="Buscar producto o motivo..."
                           style="padding:7px 12px 7px 30px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; width:220px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:0.8rem;"></i>
                </div>

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
                    @if(request()->hasAny(['id_producto', 'tipo_movimiento', 'producto_id']))
                        <a href="{{ route('admin.inventario.index') }}" style="font-size:0.82rem; color:#6b7280; text-decoration:underline;">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table" id="tablaMovimientosInventario">
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
                        <tr class="fila-movimiento">
                            <td style="white-space:nowrap; font-size:0.84rem; color:#4b5563;">
                                {{ \Illuminate\Support\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i') }}
                            </td>
                            <td style="font-weight:700; color:#111111;" class="col-producto">
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
                            <td style="color:#374151; font-size:0.85rem;" class="col-observacion">
                                {{ $mov->observacion }}
                            </td>
                            <td style="white-space:nowrap; font-size:0.82rem; color:#6b7280;">
                                <i class="fa-solid fa-user-shield" style="color:#e6a100; margin-right:4px;"></i> Administrador
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:28px; color:#6b7280;">
                                No se encontraron movimientos de inventario registrados.
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

@push('scripts')
<script>
    function actualizarPreviewProducto() {
        const select = document.getElementById('selectProductoMovimiento');
        const preview = document.getElementById('previewProductoSeleccionado');
        const selected = select.options[select.selectedIndex];

        // Quitar resaltado de todas las filas
        document.querySelectorAll('.fila-producto').forEach(f => {
            f.style.borderLeft = '';
            f.style.backgroundColor = '';
        });

        if (selected && selected.value) {
            const id = selected.value;
            const nombre = selected.getAttribute('data-nombre');
            const stock = selected.getAttribute('data-stock');
            const min = selected.getAttribute('data-minimo');
            const cat = selected.getAttribute('data-categoria');
            const img = selected.getAttribute('data-imagen');

            document.getElementById('previewNombre').textContent = nombre;
            document.getElementById('previewStock').textContent = stock;
            document.getElementById('previewCategoria').textContent = 'Categoría: ' + cat;
            document.getElementById('previewStockMinimoBadge').textContent = '(Stock mínimo: ' + min + ' uds.)';
            document.getElementById('previewImg').src = img;

            preview.style.display = 'flex';

            // Resaltar la fila en la tabla de existencias
            const fila = document.getElementById('fila-producto-' + id);
            if (fila) {
                fila.style.borderLeft = '4px solid #e6a100';
                fila.style.backgroundColor = '#fdf8f0';
                fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        } else {
            preview.style.display = 'none';
        }
    }

    function seleccionarProductoParaMovimiento(id) {
        const select = document.getElementById('selectProductoMovimiento');
        select.value = id;
        actualizarPreviewProducto();
        document.getElementById('formMovimientoWrap').scrollIntoView({ behavior: 'smooth' });
    }

    // Buscador en tiempo real de movimientos
    document.addEventListener('DOMContentLoaded', function () {
        actualizarPreviewProducto();

        const inputBuscar = document.getElementById('buscadorTiempoRealMovimientos');
        if (inputBuscar) {
            inputBuscar.addEventListener('input', function () {
                const term = this.value.toLowerCase().trim();
                const filas = document.querySelectorAll('#tablaMovimientosInventario .fila-movimiento');

                filas.forEach(fila => {
                    const prod = fila.querySelector('.col-producto')?.textContent.toLowerCase() || '';
                    const obs = fila.querySelector('.col-observacion')?.textContent.toLowerCase() || '';

                    if (prod.includes(term) || obs.includes(term)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endpush
