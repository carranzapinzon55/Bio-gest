@extends('layouts.app')

@section('title', 'Mis Pedidos | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/mis_pedidos_style.css') }}">
@endpush

@section('content')
<div style="max-width:1000px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-boxes-packing" style="color:#e4b353;"></i> Historial de Mis Pedidos
    </h1>

    <div style="background:#fff; border-radius:10px; padding:25px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
        @if($pedidos->isEmpty())
            <div style="text-align:center; padding:40px;">
                <i class="fas fa-box-open" style="font-size:3rem; color:#cbd5e1; margin-bottom:15px;"></i>
                <p style="color:#666; font-size:1.1rem;">Aún no has realizado pedidos en BIO-GEST.</p>
                <a href="{{ route('catalogo.index') }}" style="background:#151515; color:#e4b353; padding:10px 20px; border-radius:6px; text-decoration:none; font-weight:700;">Ir a la tienda</a>
            </div>
        @else
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:2px solid #eee; text-align:left; color:#666; font-size:0.9rem;">
                        <th style="padding:12px 8px;">Número</th>
                        <th style="padding:12px 8px;">Fecha</th>
                        <th style="padding:12px 8px;">Total</th>
                        <th style="padding:12px 8px;">Estado</th>
                        <th style="padding:12px 8px; text-align:right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedidos as $pedido)
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:14px 8px; font-weight:700; color:#151515;">
                                {{ $pedido->numero_pedido ?? 'PED-' . $pedido->id_pedido }}
                            </td>
                            <td style="padding:14px 8px; color:#555; font-size:0.9rem;">
                                {{ optional($pedido->fecha_pedido)->format('d/m/Y H:i') }}
                            </td>
                            <td style="padding:14px 8px; font-weight:700; color:#151515;">
                                ${{ number_format($pedido->total, 0, ',', '.') }}
                            </td>
                            <td style="padding:14px 8px;">
                                @php
                                    $colores = [
                                        'Pendiente'  => 'background:#fef3c7; color:#92400e;',
                                        'Procesando' => 'background:#dbeafe; color:#1e40af;',
                                        'Completado' => 'background:#dcfce7; color:#15803d;',
                                        'Entregado'  => 'background:#dcfce7; color:#15803d;',
                                        'Cancelado'  => 'background:#fee2e2; color:#b91c1c;',
                                    ];
                                    $estilo = $colores[$pedido->estado_pedido] ?? 'background:#f3f4f6; color:#374151;';
                                @endphp
                                <span style="padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:700; {{ $estilo }}">
                                    {{ $pedido->estado_pedido }}
                                </span>
                            </td>
                            <td style="padding:14px 8px; text-align:right;">
                                <a href="{{ route('cliente.pedidos.show', $pedido->id_pedido) }}" style="background:#f3f4f6; color:#151515; padding:6px 12px; border-radius:4px; text-decoration:none; font-size:0.85rem; font-weight:600;">
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
