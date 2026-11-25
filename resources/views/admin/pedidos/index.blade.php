@extends('layouts.admin')

@section('title', 'Gestión de Pedidos | BIO-GEST Tenebrios Gold')
@section('header_title', 'Gestión y Seguimiento de Pedidos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#111111;">Gestión de Pedidos</h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.9rem;">
                Consulta los pedidos recibidos desde MySQL, revisa el detalle y actualiza estados con control de inventario
            </p>
        </div>
        <a href="{{ route('admin.estadisticas') }}" class="tg-btn-gold">
            <i class="fa-solid fa-chart-line"></i> Estadísticas y Reportes
        </a>
    </div>

    <!-- 4 KPIs REALES DESDE MYSQL -->
    <section class="kpi-grid" style="margin-bottom:20px;">
        <div class="kpi-card" style="border-left:4px solid #111111;">
            <div class="kpi-icon" style="background:#f3f4f6; color:#111111;">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Total Pedidos</span>
                <span class="kpi-value" style="color:#111111;">{{ number_format($kpis['total']) }}</span>
                <span class="kpi-subtext">Histórico en base de datos</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #e6a100;">
            <div class="kpi-icon" style="background:#fef3c7; color:#b45309;">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Pendientes</span>
                <span class="kpi-value" style="color:#b45309;">{{ number_format($kpis['pendientes']) }}</span>
                <span class="kpi-subtext">Requieren confirmación</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #0284c7;">
            <div class="kpi-icon" style="background:#e0f2fe; color:#0369a1;">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Confirmados / Enviados</span>
                <span class="kpi-value" style="color:#0369a1;">{{ number_format($kpis['confirmados_enviados']) }}</span>
                <span class="kpi-subtext">En proceso o despachados</span>
            </div>
        </div>

        <div class="kpi-card" style="border-left:4px solid #10b981;">
            <div class="kpi-icon" style="background:#d1fae5; color:#047857;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="kpi-details">
                <span class="kpi-title">Entregados</span>
                <span class="kpi-value" style="color:#047857;">{{ number_format($kpis['entregados']) }}</span>
                <span class="kpi-subtext">Completados con éxito</span>
            </div>
        </div>
    </section>

    <!-- Layout Dividido: Tabla de Pedidos y Panel Lateral "Detalle del Pedido" -->
    <div style="display:grid; grid-template-columns: minmax(0, 1.45fr) minmax(360px, 1fr); gap:20px; align-items:start;">
        <!-- Columna Izquierda: Filtros y Tabla -->
        <div>
            <!-- Filtros por Estado y Búsqueda -->
            <div class="tg-card" style="padding:16px 20px; margin-bottom:18px;">
                <form action="{{ route('admin.pedidos.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                    <div style="flex:1; min-width:200px;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Buscar pedido o cliente</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ej. PED-1001, nombre o correo..."
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>

                    <div style="min-width:180px;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Filtrar por Estado</label>
                        <select name="estado" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="">Todos los estados</option>
                            @foreach(['Pendiente', 'Procesando', 'Despachado', 'Listo para retiro', 'Entregado', 'Completado', 'Cancelado'] as $est)
                                <option value="{{ $est }}" {{ request('estado') === $est ? 'selected' : '' }}>
                                    {{ $est === 'Despachado' ? 'Enviado / Despachado' : ($est === 'Procesando' ? 'Confirmado / En proceso' : $est) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex; gap:8px;">
                        <button type="submit" class="tg-btn-dark" style="padding:9px 16px;">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                        @if(request()->hasAny(['q', 'estado']))
                            <a href="{{ route('admin.pedidos.index') }}" style="padding:9px 14px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; font-weight:600; font-size:0.88rem;">
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Tabla de Pedidos -->
            <div class="tg-card">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>N.º Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th style="text-align:right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pedidos as $ped)
                                @php
                                    $badgeClass = match($ped->estado_pedido) {
                                        'Entregado', 'Completado' => 'badge-active',
                                        'Cancelado' => 'badge-danger',
                                        'Despachado', 'Procesando', 'Listo para retiro' => 'badge-info',
                                        default => 'badge-warning',
                                    };
                                    $pedidoJson = htmlspecialchars(json_encode([
                                        'id' => $ped->id_pedido,
                                        'numero' => $ped->numero_pedido ?? 'PED-' . $ped->id_pedido,
                                        'fecha' => optional($ped->fecha_pedido)->format('d/m/Y H:i'),
                                        'cliente' => optional($ped->usuario)->nombre_completo ?? (trim(($ped->usuario->nombre ?? '') . ' ' . ($ped->usuario->apellido ?? '')) ?: 'Cliente'),
                                        'correo' => optional($ped->usuario)->correo ?? 'Sin correo',
                                        'telefono' => $ped->telefono_contacto ?: (optional($ped->usuario)->telefono ?? 'No registrado'),
                                        'direccion' => $ped->direccion_entrega ?? 'Sin dirección especificada',
                                        'ciudad' => $ped->ciudad_entrega ?? 'Sin ciudad especificada',
                                        'metodo_pago' => $ped->metodo_pago ?? 'Contraentrega',
                                        'estado_pago' => $ped->estado_pago ?? 'Pendiente',
                                        'subtotal' => (float) $ped->subtotal,
                                        'costo_envio' => (float) $ped->costo_envio,
                                        'total' => (float) $ped->total,
                                        'estado' => $ped->estado_pedido,
                                        'action_url' => route('admin.pedidos.estado', $ped->id_pedido),
                                        'productos' => $ped->detalles->map(function($d) {
                                            return [
                                                'nombre' => optional($d->producto)->nombre_producto ?? ('Producto #' . $d->id_producto),
                                                'cantidad' => $d->cantidad,
                                                'precio' => (float) $d->precio_unitario,
                                                'subtotal' => (float) $d->subtotal,
                                                'imagen' => optional($d->producto)->imagen ? asset('assets/img/' . $d->producto->imagen) : asset('assets/img/Tenebrios_img.png'),
                                            ];
                                        })->values()
                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8');
                                @endphp
                                <tr id="row-pedido-{{ $ped->id_pedido }}" class="pedido-table-row" style="cursor:pointer;" onclick="mostrarDetallePedido({{ $pedidoJson }})">
                                    <td style="font-weight:800; color:#111111; white-space:nowrap;">
                                        <i class="fa-solid fa-receipt" style="color:#e6a100; margin-right:5px;"></i>
                                        {{ $ped->numero_pedido ?? 'PED-' . $ped->id_pedido }}
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:#111111;">{{ optional($ped->usuario)->nombre_completo ?? (trim(($ped->usuario->nombre ?? '') . ' ' . ($ped->usuario->apellido ?? '')) ?: 'Cliente') }}</div>
                                        <div style="font-size:0.78rem; color:#6b7280;">{{ optional($ped->usuario)->correo }}</div>
                                    </td>
                                    <td style="white-space:nowrap; font-size:0.84rem; color:#4b5563;">
                                        {{ optional($ped->fecha_pedido)->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <div style="font-weight:800; color:#111111;">${{ number_format($ped->total, 0, ',', '.') }}</div>
                                        <div style="font-size:0.75rem; color:#6b7280;">{{ $ped->metodo_pago }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $ped->estado_pedido === 'Despachado' ? 'Enviado / Despachado' : ($ped->estado_pedido === 'Procesando' ? 'Confirmado' : $ped->estado_pedido) }}
                                        </span>
                                    </td>
                                    <td style="text-align:right; white-space:nowrap;">
                                        <button type="button" class="tg-btn-gold" style="padding:6px 12px; font-size:0.8rem;" onclick="event.stopPropagation(); mostrarDetallePedido({{ $pedidoJson }});">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:28px; color:#6b7280;">
                                        No se encontraron pedidos registrados en MySQL.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px;">
                    {{ $pedidos->links() }}
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Panel Lateral "Detalle del Pedido" Con Scroll Interno Limpio -->
        <div class="tg-card" id="panelDetallePedido" style="position:sticky; top:85px; max-height:calc(100vh - 110px); display:flex; flex-direction:column; padding:0; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#ffffff; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-invoice" style="color:#e6a100; font-size:1.1rem;"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#111111;" id="drawerTitle">Detalle del Pedido</h3>
                </div>
                <span id="drawerBadgeEstado" class="badge badge-warning">Seleccione</span>
            </div>

            <!-- Cuerpo con scroll interno limpio -->
            <div id="drawerBody" style="padding:18px 20px; overflow-y:auto; flex:1;">
                <div id="drawerEmpty" style="text-align:center; padding:45px 15px; color:#9ca3af;">
                    <i class="fa-solid fa-hand-pointer" style="font-size:2.4rem; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
                    <p style="margin:0; font-weight:600; font-size:0.92rem;">Haz clic en cualquier pedido de la tabla</p>
                    <small>Se desplegarán cliente, dirección completa, productos, subtotales y actualización de estado.</small>
                </div>

                <div id="drawerContent" style="display:none; flex-direction:column; gap:16px;">
                    <!-- Cabecera de pedido -->
                    <div style="background:#f8f9fa; border:1px solid #e5e7eb; border-radius:8px; padding:12px 14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-weight:800; font-size:1.05rem; color:#111111;" id="detNumeroPedido">#PED-</span>
                            <span style="font-size:0.8rem; color:#6b7280;" id="detFechaPedido">-</span>
                        </div>
                    </div>

                    <!-- Datos del Cliente y Entrega Completa -->
                    <div style="border:1px solid #e5e7eb; border-radius:8px; padding:14px;">
                        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6b7280; margin-bottom:8px;">
                            <i class="fa-solid fa-user" style="color:#e6a100;"></i> Datos del Cliente y Contacto
                        </div>
                        <div style="font-weight:700; font-size:0.95rem; color:#111111;" id="detClienteNombre">-</div>
                        <div style="font-size:0.84rem; color:#4b5563; margin-top:3px;">
                            <i class="fa-solid fa-envelope" style="width:16px; color:#9ca3af;"></i> <span id="detClienteCorreo">-</span>
                        </div>
                        <div style="font-size:0.84rem; color:#4b5563; margin-top:3px;">
                            <i class="fa-solid fa-phone" style="width:16px; color:#9ca3af;"></i> <span id="detClienteTelefono">-</span>
                        </div>

                        <div style="border-top:1px dashed #e5e7eb; margin:10px 0 8px 0; padding-top:8px;">
                            <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6b7280; margin-bottom:4px;">
                                <i class="fa-solid fa-location-dot" style="color:#e6a100;"></i> Dirección de Entrega Completa
                            </div>
                            <div style="font-weight:700; font-size:0.88rem; color:#111111;" id="detDireccionEntrega">-</div>
                            <div style="font-size:0.84rem; color:#4b5563; margin-top:2px;">
                                Ciudad: <strong id="detCiudadEntrega" style="color:#111111;">-</strong>
                            </div>
                        </div>

                        <div style="border-top:1px dashed #e5e7eb; margin:10px 0 0 0; padding-top:8px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.82rem; color:#6b7280;">Método de Pago:</span>
                            <span style="font-weight:700; font-size:0.85rem; color:#111111;" id="detMetodoPago">-</span>
                        </div>
                    </div>

                    <!-- Listado de Productos -->
                    <div>
                        <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:#6b7280; margin-bottom:8px; display:flex; justify-content:space-between;">
                            <span><i class="fa-solid fa-boxes-stacked" style="color:#e6a100;"></i> Productos en el Pedido</span>
                            <span id="detTotalItems" style="color:#111111;">0 items</span>
                        </div>
                        <div id="detProductosList" style="display:flex; flex-direction:column; gap:8px;">
                            <!-- Items dinámicos -->
                        </div>
                    </div>

                    <!-- Totales y Costo de Envío -->
                    <div style="background:#fdf8f0; border:1px solid #fef3c7; border-radius:8px; padding:12px 14px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.86rem; color:#4b5563; margin-bottom:4px;">
                            <span>Subtotal:</span>
                            <strong id="detSubtotal" style="color:#111111;">$ 0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:0.86rem; color:#4b5563; margin-bottom:6px;">
                            <span>Costo de Envío:</span>
                            <strong id="detCostoEnvio" style="color:#111111;">$ 0</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #fde68a; padding-top:6px;">
                            <span style="font-weight:800; font-size:0.95rem; color:#111111;">Total a Pagar:</span>
                            <span style="font-weight:800; font-size:1.15rem; color:#e6a100;" id="detTotal">$ 0</span>
                        </div>
                    </div>

                    <!-- Formulario de Cambio de Estado con Reintegro de Stock -->
                    <div style="background:#111111; color:#ffffff; padding:16px; border-radius:8px; border:1px solid #e6a100;">
                        <div style="font-size:0.78rem; font-weight:800; text-transform:uppercase; color:#e6a100; margin-bottom:8px;">
                            <i class="fa-solid fa-truck-ramp-box"></i> Cambiar Estado del Pedido
                        </div>
                        <form id="formCambiarEstado" method="POST">
                            @csrf
                            @method('PATCH')
                            <div style="margin-bottom:10px;">
                                <label style="display:block; font-size:0.75rem; color:#d1d5db; margin-bottom:4px;">Estado Logístico:</label>
                                <select name="estado_pedido" id="selectEstadoPedido" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #e6a100; background:#222222; color:#ffffff; font-weight:600; font-size:0.85rem;">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Procesando">Confirmado / Procesando</option>
                                    <option value="Enviado">Enviado / Despachado</option>
                                    <option value="Entregado">Entregado</option>
                                    <option value="Cancelado">Cancelado (Restaura Stock en Kardex)</option>
                                </select>
                            </div>
                            <div style="margin-bottom:12px;">
                                <label style="display:block; font-size:0.75rem; color:#d1d5db; margin-bottom:4px;">Estado del Pago:</label>
                                <select name="estado_pago" id="selectEstadoPago" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #374151; background:#222222; color:#ffffff; font-size:0.85rem;">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Aprobado">Aprobado</option>
                                    <option value="Rechazado">Rechazado</option>
                                    <option value="Reembolsado">Reembolsado</option>
                                </select>
                            </div>
                            <button type="submit" class="tg-btn-gold" style="width:100%; justify-content:center; padding:10px;">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios en MySQL
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function fmtCOP(val) {
        return '$ ' + Number(val || 0).toLocaleString('es-CO');
    }

    function mostrarDetallePedido(p) {
        document.getElementById('drawerEmpty').style.display = 'none';
        const content = document.getElementById('drawerContent');
        content.style.display = 'flex';

        // Títulos y datos
        document.getElementById('drawerTitle').textContent = 'Pedido ' + p.numero;
        document.getElementById('detNumeroPedido').textContent = '#' + p.numero;
        document.getElementById('detFechaPedido').textContent = p.fecha || 'Reciente';

        // Badge
        const badge = document.getElementById('drawerBadgeEstado');
        badge.textContent = p.estado;
        badge.className = 'badge ' + (
            (p.estado === 'Entregado' || p.estado === 'Completado') ? 'badge-active' :
            (p.estado === 'Cancelado' ? 'badge-danger' :
            (p.estado === 'Despachado' || p.estado === 'Procesando' || p.estado === 'Enviado' ? 'badge-info' : 'badge-warning'))
        );

        // Cliente y Entrega Completa
        document.getElementById('detClienteNombre').textContent = p.cliente;
        document.getElementById('detClienteCorreo').textContent = p.correo;
        document.getElementById('detClienteTelefono').textContent = p.telefono;
        document.getElementById('detDireccionEntrega').textContent = p.direccion;
        document.getElementById('detCiudadEntrega').textContent = p.ciudad;
        document.getElementById('detMetodoPago').textContent = p.metodo_pago + ' (' + p.estado_pago + ')';

        // Productos
        const container = document.getElementById('detProductosList');
        container.innerHTML = '';
        document.getElementById('detTotalItems').textContent = (p.productos ? p.productos.length : 0) + ' items';

        (p.productos || []).forEach(it => {
            const row = document.createElement('div');
            row.style.cssText = 'display:flex; align-items:center; gap:10px; padding:8px; border:1px solid #f3f4f6; border-radius:6px; background:#fafafa;';
            row.innerHTML = `
                <img src="${it.imagen}" alt="${it.nombre}" onerror="this.onerror=null; this.src='{{ asset('assets/img/Tenebrios_img.png') }}';" style="width:38px; height:38px; object-fit:contain; border-radius:4px; background:#fff; border:1px solid #e5e7eb;">
                <div style="flex:1; min-width:0;">
                    <div style="font-weight:700; font-size:0.86rem; color:#111111; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${it.nombre}</div>
                    <div style="font-size:0.75rem; color:#6b7280;">${it.cantidad} &times; ${fmtCOP(it.precio)}</div>
                </div>
                <div style="font-weight:800; font-size:0.86rem; color:#111111; white-space:nowrap;">
                    ${fmtCOP(it.subtotal)}
                </div>
            `;
            container.appendChild(row);
        });

        // Totales
        document.getElementById('detSubtotal').textContent = fmtCOP(p.subtotal);
        document.getElementById('detCostoEnvio').textContent = p.costo_envio > 0 ? fmtCOP(p.costo_envio) : 'GRATIS ($0)';
        document.getElementById('detTotal').textContent = fmtCOP(p.total);

        // Formulario
        const form = document.getElementById('formCambiarEstado');
        form.action = p.action_url;

        // Seleccionar estado
        const selectEstado = document.getElementById('selectEstadoPedido');
        const st = p.estado === 'Despachado' ? 'Enviado' : p.estado;
        for (let opt of selectEstado.options) {
            opt.selected = (opt.value === st || (opt.value === 'Procesando' && st === 'Confirmado'));
        }

        const selectPago = document.getElementById('selectEstadoPago');
        for (let opt of selectPago.options) {
            opt.selected = (opt.value === p.estado_pago);
        }

        // Resaltar fila seleccionada en tabla
        document.querySelectorAll('.pedido-table-row').forEach(tr => tr.style.backgroundColor = '');
        const activeRow = document.getElementById('row-pedido-' + p.id);
        if (activeRow) activeRow.style.backgroundColor = '#fdf8f0';
    }

    // Auto-seleccionar primer pedido si existe
    document.addEventListener('DOMContentLoaded', function() {
        const firstRow = document.querySelector('.pedido-table-row');
        if (firstRow) {
            firstRow.click();
        }
    });
</script>
@endpush
