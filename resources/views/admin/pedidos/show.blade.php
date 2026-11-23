@extends('layouts.app')

@section('title', 'Detalle del Pedido ' . ($pedido->numero_pedido ?? 'PED-' . $pedido->id_pedido) . ' | BIO-GEST')
@section('header_title', 'Detalle de Pedido ' . ($pedido->numero_pedido ?? 'PED-' . $pedido->id_pedido))

@section('content')
    <div style="max-width:960px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
            <div>
                <h1 style="margin:0; font-size:1.5rem; color:#151515;">
                    <i class="fa-solid fa-file-invoice" style="color:#e4b353;"></i>
                    Pedido {{ $pedido->numero_pedido ?? 'PED-' . $pedido->id_pedido }}
                </h1>
                <p style="margin:4px 0 0 0; color:#64748b; font-size:0.88rem;">
                    Registrado el {{ optional($pedido->fecha_pedido)->format('d/m/Y \a \l\a\s H:i') }}
                </p>
            </div>
            <a href="{{ route('admin.pedidos.index') }}" class="tg-btn-dark" style="padding:8px 16px;">
                <i class="fa-solid fa-arrow-left"></i> Volver a Pedidos
            </a>
        </div>

        <!-- Tarjeta de Información del Cliente, Envío y Actualización de Estado -->
        <div class="tg-card">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:22px; padding-bottom:20px; border-bottom:1px solid #e2e8f0;">
                <div>
                    <h3 style="margin:0 0 10px 0; font-size:0.9rem; text-transform:uppercase; color:#64748b;">
                        <i class="fa-solid fa-user" style="color:#e4b353;"></i> Datos del Cliente
                    </h3>
                    <div style="font-weight:700; font-size:1.05rem; color:#151515;">
                        {{ optional($pedido->usuario)->nombre_completo ?? 'Cliente' }}
                    </div>
                    <div style="color:#475569; font-size:0.9rem; margin-top:4px;">
                        <i class="fa-solid fa-envelope"></i> {{ optional($pedido->usuario)->correo ?? 'N/A' }}
                    </div>
                    <div style="color:#475569; font-size:0.9rem; margin-top:4px;">
                        <i class="fa-solid fa-phone"></i> {{ $pedido->telefono_contacto ?? optional($pedido->usuario)->telefono ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <h3 style="margin:0 0 10px 0; font-size:0.9rem; text-transform:uppercase; color:#64748b;">
                        <i class="fa-solid fa-location-dot" style="color:#e4b353;"></i> Información de Entrega
                    </h3>
                    <div style="font-weight:700; color:#151515;">{{ $pedido->direccion_entrega }}</div>
                    <div style="color:#475569; font-size:0.9rem; margin-top:4px;">Ciudad: <strong>{{ $pedido->ciudad_entrega }}</strong></div>
                    <div style="color:#475569; font-size:0.9rem; margin-top:4px;">
                        Método de Pago: <strong>{{ $pedido->metodo_pago }}</strong> ({{ $pedido->estado_pago }})
                    </div>
                </div>

                <div style="background:#151515; color:#fff; padding:18px; border-radius:8px; border:1px solid #e4b353;">
                    <h3 style="margin:0 0 12px 0; font-size:0.9rem; text-transform:uppercase; color:#e4b353;">
                        <i class="fa-solid fa-truck-fast"></i> Gestionar Estado del Pedido
                    </h3>
                    <form action="{{ route('admin.pedidos.estado', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div style="margin-bottom:10px;">
                            <label style="display:block; font-size:0.78rem; color:#cbd5e1; margin-bottom:4px;">Estado Logístico:</label>
                            <select name="estado_pedido" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #e4b353; background:#262626; color:#fff; font-weight:600;">
                                <option value="Pendiente" {{ $pedido->estado_pedido === 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Procesando" {{ $pedido->estado_pedido === 'Procesando' ? 'selected' : '' }}>Procesando</option>
                                <option value="Enviado" {{ $pedido->estado_pedido === 'Despachado' ? 'selected' : '' }}>Enviado / Despachado</option>
                                <option value="Listo para retiro" {{ $pedido->estado_pedido === 'Listo para retiro' ? 'selected' : '' }}>Listo para retiro</option>
                                <option value="Entregado" {{ $pedido->estado_pedido === 'Entregado' ? 'selected' : '' }}>Entregado</option>
                                <option value="Completado" {{ $pedido->estado_pedido === 'Completado' ? 'selected' : '' }}>Completado</option>
                                <option value="Cancelado" {{ $pedido->estado_pedido === 'Cancelado' ? 'selected' : '' }}>Cancelado (Reintegra Stock)</option>
                            </select>
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="display:block; font-size:0.78rem; color:#cbd5e1; margin-bottom:4px;">Estado del Pago:</label>
                            <select name="estado_pago" style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid #3f3f46; background:#262626; color:#fff;">
                                @foreach(['Pendiente', 'Aprobado', 'Rechazado', 'Reembolsado'] as $ep)
                                    <option value="{{ $ep }}" {{ $pedido->estado_pago === $ep ? 'selected' : '' }}>{{ $ep }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="tg-btn-gold" style="width:100%; justify-content:center;">
                            <i class="fa-solid fa-floppy-disk"></i> Actualizar Estado
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tabla de Productos del Pedido -->
            <h3 style="color:#151515; margin:20px 0 12px 0; font-size:1.1rem;">
                <i class="fa-solid fa-boxes-stacked" style="color:#e4b353;"></i> Productos Incluidos en el Pedido
            </h3>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="text-align:center;">Cantidad</th>
                            <th style="text-align:right;">Precio Unitario</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->detalles as $det)
                            <tr>
                                <td style="font-weight:700;">PROD-{{ str_pad((string) $det->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                                <td style="font-weight:700; color:#151515;">{{ optional($det->producto)->nombre_producto ?? 'Producto #' . $det->id_producto }}</td>
                                <td style="text-align:center; font-weight:700;">{{ $det->cantidad }}</td>
                                <td style="text-align:right;">${{ number_format($det->precio_unitario, 0, ',', '.') }}</td>
                                <td style="text-align:right; font-weight:800; color:#151515;">${{ number_format($det->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totales del Pedido -->
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px; margin-top:20px; padding-top:16px; border-top:2px solid #e2e8f0;">
                <div style="font-size:0.95rem; color:#475569;">
                    Subtotal: <strong>${{ number_format($pedido->subtotal, 0, ',', '.') }} COP</strong>
                </div>
                <div style="font-size:0.95rem; color:#475569;">
                    Costo de Envío:
                    <strong>
                        @if($pedido->costo_envio == 0)
                            GRATIS ($0 COP)
                        @else
                            ${{ number_format($pedido->costo_envio, 0, ',', '.') }} COP
                        @endif
                    </strong>
                </div>
                <div style="font-size:1.35rem; font-weight:800; color:#151515; background:#fef9ec; padding:8px 16px; border-radius:8px; border:1px solid #e4b353; margin-top:4px;">
                    Valor Total: ${{ number_format($pedido->total, 0, ',', '.') }} COP
                </div>
            </div>
        </div>
    </div>
@endsection
