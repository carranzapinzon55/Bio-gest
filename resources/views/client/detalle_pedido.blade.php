@extends('layouts.app')

@section('title', 'Detalle de Pedido | Tenebrios Gold')

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="color:#151515; margin:0;">Pedido {{ $pedido->numero_pedido }}</h1>
        <a href="{{ route('cliente.pedidos.index') }}" style="color:#151515; text-decoration:none; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Volver a mis pedidos
        </a>
    </div>

    <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
        <!-- Datos de Entrega y Estado -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px; padding-bottom:20px; border-bottom:1px solid #eee;">
            <div>
                <h4 style="margin:0 0 8px 0; color:#888; font-size:0.85rem; text-transform:uppercase;">Estado del Pedido</h4>
                <span style="font-weight:700; font-size:1.1rem; color:#151515;">{{ $pedido->estado_pedido }}</span>
                <div style="color:#666; font-size:0.85rem; margin-top:4px;">Pago: {{ $pedido->estado_pago }} ({{ $pedido->metodo_pago }})</div>
            </div>
            <div>
                <h4 style="margin:0 0 8px 0; color:#888; font-size:0.85rem; text-transform:uppercase;">Dirección de Entrega</h4>
                <div style="color:#151515; font-weight:600;">{{ $pedido->direccion_entrega }}</div>
                <div style="color:#666; font-size:0.85rem;">{{ $pedido->ciudad_entrega }} - Tel: {{ $pedido->telefono_contacto }}</div>
            </div>
        </div>

        <!-- Detalles de Productos Comprados -->
        <h3 style="color:#151515; margin-bottom:15px;">Productos en el pedido</h3>
        <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <thead>
                <tr style="border-bottom:2px solid #eee; text-align:left; color:#666; font-size:0.85rem;">
                    <th style="padding:8px 0;">Producto</th>
                    <th style="padding:8px 0; text-align:center;">Cantidad</th>
                    <th style="padding:8px 0; text-align:right;">Precio Unitario</th>
                    <th style="padding:8px 0; text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->detalles as $det)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:12px 0; font-weight:600; color:#151515;">{{ optional($det->producto)->nombre_producto }}</td>
                        <td style="padding:12px 0; text-align:center;">{{ $det->cantidad }}</td>
                        <td style="padding:12px 0; text-align:right; color:#555;">${{ number_format($det->precio_unitario, 0, ',', '.') }}</td>
                        <td style="padding:12px 0; text-align:right; font-weight:700; color:#151515;">${{ number_format($det->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-top:2px solid #eee; padding-top:15px; max-width:300px; margin-left:auto;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#555;">
                <span>Subtotal:</span>
                <span>${{ number_format($pedido->subtotal, 0, ',', '.') }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#555;">
                <span>Envío:</span>
                <span>${{ number_format($pedido->costo_envio, 0, ',', '.') }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:1.25rem; font-weight:800; color:#151515; padding-top:8px; border-top:1px solid #eee;">
                <span>Total:</span>
                <span>${{ number_format($pedido->total, 0, ',', '.') }} COP</span>
            </div>
        </div>
    </div>
</div>
@endsection
