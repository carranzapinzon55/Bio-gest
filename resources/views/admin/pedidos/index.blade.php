@extends('layouts.admin')

@section('title', 'Gestión de Pedidos | BIO-GEST Tenebrios Gold')
@section('header_title', 'Gestión y Seguimiento de Pedidos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#111111;">Gestión de Pedidos</h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.9rem;">
                Consulta los pedidos recibidos desde MySQL, avanza estados a un clic y gestiona inventario en tiempo real
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
    <div class="admin-split-layout">
        <!-- Columna Izquierda: Filtros y Tabla -->
        <div style="min-width:0;">
            <!-- Filtros por Estado y Búsqueda -->
            <div class="tg-card" style="padding:16px 20px; margin-bottom:18px;">
                <form action="{{ route('admin.pedidos.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
                    <div style="flex:1 1 200px; min-width:0;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Buscar pedido o cliente</label>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Ej. PED-1001, nombre o correo..."
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>

                    <div style="flex:1 1 180px; min-width:0;">
                        <label style="display:block; font-size:0.8rem; font-weight:700; color:#111111; margin-bottom:4px;">Filtrar por Estado</label>
                        <select name="estado" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="">Todos los Estados</option>
                            <option value="Pendiente" {{ request('estado') === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="Confirmado" {{ request('estado') === 'Confirmado' ? 'selected' : '' }}>Confirmado</option>
                            <option value="Procesando" {{ request('estado') === 'Procesando' ? 'selected' : '' }}>Procesando</option>
                            <option value="Despachado" {{ request('estado') === 'Despachado' ? 'selected' : '' }}>Enviado / Despachado</option>
                            <option value="Entregado" {{ request('estado') === 'Entregado' ? 'selected' : '' }}>Entregado</option>
                            <option value="Cancelado" {{ request('estado') === 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    <button type="submit" class="tg-btn-dark" style="padding:9px 18px; font-size:0.88rem;">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>

                    @if(request()->hasAny(['q', 'estado']))
                        <a href="{{ route('admin.pedidos.index') }}" class="tg-btn-outline" style="padding:9px 14px; font-size:0.88rem;">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tabla de Pedidos -->
            <div class="tg-card" style="padding:0; overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#111111;">
                        <i class="fa-solid fa-list-check" style="color:#e6a100;"></i> Listado de Pedidos
                    </h3>
                    <span style="font-size:0.82rem; color:#6b7280;">Mostrando {{ $pedidos->count() }} de {{ $pedidos->total() }} pedidos</span>
                </div>

                <div class="table-responsive">
                    <table class="tg-table" style="margin:0;">
                        <thead>
                            <tr>
                                <th>N° Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th style="text-align:center;">Acción Rápida</th>
                                <th style="text-align:right;">Ficha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $pedidosJsMap = []; @endphp
                            @forelse($pedidos as $ped)
                                @php
                                    $badgeClass = match($ped->estado_pedido) {
                                        'Entregado', 'Completado' => 'badge-active',
                                        'Cancelado' => 'badge-danger',
                                        'Despachado', 'Procesando', 'Enviado', 'Confirmado' => 'badge-info',
                                        default => 'badge-warning'
                                    };

                                    $pedidosJsMap[$ped->id_pedido] = [
                                        'id' => $ped->id_pedido,
                                        'numero' => $ped->numero_pedido ?? ('PED-' . $ped->id_pedido),
                                        'fecha' => optional($ped->fecha_pedido)->format('d/m/Y H:i'),
                                        'cliente' => optional($ped->usuario)->nombre_completo ?? (trim(($ped->usuario->nombre ?? '') . ' ' . ($ped->usuario->apellido ?? '')) ?: 'Cliente'),
                                        'correo' => optional($ped->usuario)->correo ?? 'Sin correo',
                                        'telefono' => $ped->telefono_contacto ?? (optional($ped->usuario)->telefono ?: 'Sin teléfono'),
                                        'direccion' => $ped->direccion_entrega ?? (optional($ped->direccion)->direccion ?: 'Dirección principal'),
                                        'ciudad' => $ped->ciudad_entrega ?? (optional($ped->direccion)->ciudad ?: 'Bogotá'),
                                        'metodo_pago' => $ped->metodo_pago ?? 'Contraentrega',
                                        'estado_pago' => $ped->estado_pago ?? 'Pendiente',
                                        'subtotal' => (float) $ped->subtotal,
                                        'costo_envio' => (float) $ped->costo_envio,
                                        'total' => (float) $ped->total,
                                        'estado' => $ped->estado_pedido,
                                        'action_url' => route('admin.pedidos.estado', $ped->id_pedido),
                                        'show_url' => route('admin.pedidos.show', $ped->id_pedido),
                                        'productos' => $ped->detalles->map(function($d) {
                                            return [
                                                'nombre' => optional($d->producto)->nombre_producto ?? ('Producto #' . $d->id_producto),
                                                'cantidad' => $d->cantidad,
                                                'precio' => (float) $d->precio_unitario,
                                                'subtotal' => (float) $d->subtotal,
                                                'imagen' => optional($d->producto)->imagen ? asset('assets/img/' . $d->producto->imagen) : asset('assets/img/Tenebrios_img.png'),
                                            ];
                                        })->values()
                                    ];
                                @endphp
                                <tr id="row-pedido-{{ $ped->id_pedido }}" class="pedido-table-row" data-pedido-id="{{ $ped->id_pedido }}" style="cursor:pointer;" onclick="mostrarDetallePedidoPorId({{ $ped->id_pedido }})">
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
                                    <td style="text-align:center; white-space:nowrap;" onclick="event.stopPropagation();">
                                        <!-- Botones de Acción Rápida a Un Clic -->
                                        <div style="display:inline-flex; gap:4px; align-items:center;">
                                            @if($ped->estado_pedido === 'Pendiente')
                                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="accion_rapida" value="avanzar">
                                                    <button type="submit" class="tg-btn-gold" style="padding:4px 9px; font-size:0.75rem;" title="Confirmar Pedido (Avanzar)">
                                                        <i class="fa-solid fa-check"></i> Confirmar
                                                    </button>
                                                </form>
                                            @elseif(in_array($ped->estado_pedido, ['Confirmado', 'Procesando']))
                                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="accion_rapida" value="avanzar">
                                                    <button type="submit" class="tg-btn-dark" style="padding:4px 9px; font-size:0.75rem; background:#0284c7; color:#fff;" title="Despachar Pedido">
                                                        <i class="fa-solid fa-truck-fast"></i> Despachar
                                                    </button>
                                                </form>
                                            @elseif(in_array($ped->estado_pedido, ['Enviado', 'Despachado', 'Listo para retiro']))
                                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="accion_rapida" value="avanzar">
                                                    <button type="submit" class="tg-btn-dark" style="padding:4px 9px; font-size:0.75rem; background:#059669; color:#fff;" title="Marcar como Entregado">
                                                        <i class="fa-solid fa-box-open"></i> Entregar
                                                    </button>
                                                </form>
                                            @elseif(in_array($ped->estado_pedido, ['Entregado', 'Completado']))
                                                <span style="font-size:0.75rem; color:#059669; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Finalizado</span>
                                            @elseif($ped->estado_pedido === 'Cancelado')
                                                <span style="font-size:0.75rem; color:#dc2626; font-weight:700;"><i class="fa-solid fa-ban"></i> Cancelado</span>
                                            @endif

                                            @if($ped->estado_pedido !== 'Cancelado' && !in_array($ped->estado_pedido, ['Entregado', 'Completado']))
                                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="margin:0;" onsubmit="return confirm('¿Cancelar pedido {{ $ped->numero_pedido }}? Se restaurará automáticamente el stock al inventario.');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="accion_rapida" value="cancelar">
                                                    <button type="submit" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:4px 8px; border-radius:4px; font-size:0.75rem; cursor:pointer;" title="Cancelar y restaurar stock">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align:right; white-space:nowrap;">
                                        <button type="button" class="tg-btn-gold" style="padding:6px 10px; font-size:0.78rem;" onclick="event.stopPropagation(); mostrarDetallePedidoPorId({{ $ped->id_pedido }});">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </button>
                                        <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}" class="tg-btn-outline" style="padding:6px 8px; font-size:0.78rem; margin-left:3px;" onclick="event.stopPropagation();" title="Abrir ficha completa del pedido">
                                            <i class="fa-solid fa-up-right-from-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:28px; color:#6b7280;">
                                        No se encontraron pedidos registrados en MySQL.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px; padding:0 20px 16px 20px;">
                    {{ $pedidos->links() }}
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Panel Lateral "Detalle del Pedido" Con Scroll Interno Limpio -->
        <div class="tg-card admin-sticky-panel" id="panelDetallePedido" style="position:sticky; top:85px; max-height:calc(100vh - 110px); display:flex; flex-direction:column; padding:0; overflow:hidden;">
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
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                            <span style="font-weight:800; font-size:1.05rem; color:#111111;" id="detNumeroPedido">#PED-</span>
                            <span style="font-size:0.8rem; color:#6b7280;" id="detFechaPedido">-</span>
                        </div>
                        <div style="margin-top:8px;">
                            <a id="detShowLink" href="#" class="tg-btn-outline" style="padding:4px 10px; font-size:0.76rem; width:100%; justify-content:center;">
                                <i class="fa-solid fa-up-right-from-square"></i> Abrir Ficha Completa del Pedido
                            </a>
                        </div>
                    </div>

                    <!-- Botones de Acción Rápida en el Drawer -->
                    <div style="background:#fefce8; border:1px solid #fef08a; border-radius:8px; padding:12px;">
                        <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; color:#854d0e; margin-bottom:8px;">
                            <i class="fa-solid fa-bolt" style="color:#e6a100;"></i> Acciones Rápidas (1 Clic)
                        </div>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <form id="formDrawerAvanzar" method="POST" style="margin:0; flex:1;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="accion_rapida" value="avanzar">
                                <button type="submit" class="tg-btn-gold" id="btnDrawerAvanzar" style="width:100%; justify-content:center; padding:8px; font-size:0.82rem;">
                                    <i class="fa-solid fa-forward-step"></i> <span id="lblDrawerAvanzar">Avanzar Estado</span>
                                </button>
                            </form>
                            <form id="formDrawerConfirmarPago" method="POST" style="margin:0;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="accion_rapida" value="confirmar_pago">
                                <button type="submit" class="tg-btn-dark" style="padding:8px 10px; font-size:0.82rem; background:#059669; color:#fff;" title="Aprobar Pago">
                                    <i class="fa-solid fa-circle-check"></i> Pago OK
                                </button>
                            </form>
                            <form id="formDrawerCancelar" method="POST" style="margin:0;" onsubmit="return confirm('¿Confirmas cancelar este pedido? Se restaurará automáticamente el stock al inventario.');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="accion_rapida" value="cancelar">
                                <button type="submit" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:8px 10px; border-radius:6px; font-size:0.82rem; cursor:pointer;" title="Cancelar y restaurar stock">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            </form>
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

                    <!-- Formulario Manual de Cambio de Estado con Reintegro de Stock -->
                    <div style="background:#111111; color:#ffffff; padding:16px; border-radius:8px; border:1px solid #e6a100;">
                        <div style="font-size:0.78rem; font-weight:800; text-transform:uppercase; color:#e6a100; margin-bottom:8px;">
                            <i class="fa-solid fa-truck-ramp-box"></i> Ajuste Personalizado de Estado
                        </div>
                        <form id="formCambiarEstado" method="POST">
                            @csrf
                            @method('PATCH')
                            <div style="margin-bottom:10px;">
                                <label style="display:block; font-size:0.75rem; color:#d1d5db; margin-bottom:4px;">Estado Logístico:</label>
                                <select name="estado_pedido" id="selectEstadoPedido" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #e6a100; background:#222222; color:#ffffff; font-weight:600; font-size:0.85rem;">
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Confirmado">Confirmado</option>
                                    <option value="Procesando">Procesando</option>
                                    <option value="Enviado">Enviado / Despachado</option>
                                    <option value="Entregado">Entregado</option>
                                    <option value="Cancelado">Cancelado (Restaura Stock al Inventario)</option>
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
    const PEDIDOS_MAP = @json($pedidosJsMap ?? []);

    function fmtCOP(val) {
        return '$ ' + Number(val || 0).toLocaleString('es-CO');
    }

    function mostrarDetallePedidoPorId(id) {
        const p = PEDIDOS_MAP[id];
        if (p) {
            mostrarDetallePedido(p);
        }
    }

    function mostrarDetallePedido(p) {
        if (!p) return;
        document.getElementById('drawerEmpty').style.display = 'none';
        const content = document.getElementById('drawerContent');
        content.style.display = 'flex';

        // Títulos y datos
        document.getElementById('drawerTitle').textContent = 'Pedido ' + p.numero;
        document.getElementById('detNumeroPedido').textContent = '#' + p.numero;
        document.getElementById('detFechaPedido').textContent = p.fecha || 'Reciente';
        const showLink = document.getElementById('detShowLink');
        if (showLink && p.show_url) {
            showLink.href = p.show_url;
        }

        // Badge
        const badge = document.getElementById('drawerBadgeEstado');
        badge.textContent = p.estado;
        badge.className = 'badge ' + (
            (p.estado === 'Entregado' || p.estado === 'Completado') ? 'badge-active' :
            (p.estado === 'Cancelado' ? 'badge-danger' :
            (p.estado === 'Despachado' || p.estado === 'Procesando' || p.estado === 'Enviado' || p.estado === 'Confirmado' ? 'badge-info' : 'badge-warning'))
        );

        // Actualizar botón de avance rápido en el drawer
        const formAvanzar = document.getElementById('formDrawerAvanzar');
        const formConfirmarPago = document.getElementById('formDrawerConfirmarPago');
        const formCancelar = document.getElementById('formDrawerCancelar');
        const lblAvanzar = document.getElementById('lblDrawerAvanzar');
        const btnAvanzar = document.getElementById('btnDrawerAvanzar');

        if (formAvanzar) formAvanzar.action = p.action_url;
        if (formConfirmarPago) formConfirmarPago.action = p.action_url;
        if (formCancelar) formCancelar.action = p.action_url;

        if (p.estado === 'Pendiente') {
            lblAvanzar.textContent = 'Confirmar Pedido';
            btnAvanzar.disabled = false;
            btnAvanzar.style.opacity = '1';
        } else if (p.estado === 'Confirmado' || p.estado === 'Procesando') {
            lblAvanzar.textContent = 'Despachar / Enviar';
            btnAvanzar.disabled = false;
            btnAvanzar.style.opacity = '1';
        } else if (p.estado === 'Enviado' || p.estado === 'Despachado' || p.estado === 'Listo para retiro') {
            lblAvanzar.textContent = 'Marcar Entregado';
            btnAvanzar.disabled = false;
            btnAvanzar.style.opacity = '1';
        } else {
            lblAvanzar.textContent = 'Estado Final (' + p.estado + ')';
            btnAvanzar.disabled = true;
            btnAvanzar.style.opacity = '0.6';
        }

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

        // Formulario manual
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
