@extends('layouts.app')

@section('title', 'Carrito de Compras | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/carrito_style.css') }}">
@endpush

@section('content')
<div style="max-width:1000px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-shopping-cart" style="color:#e4b353;"></i> Tu Carrito de Compras
    </h1>

    @if(session('success'))
        <div style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if(empty($carrito))
        <div style="background:#fff; border-radius:10px; padding:60px 20px; text-align:center; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <i class="fas fa-shopping-basket" style="font-size:3.5rem; color:#cbd5e1; margin-bottom:15px;"></i>
            <h2 style="color:#333; margin-bottom:10px;">Tu carrito está vacío</h2>
            <p style="color:#777; margin-bottom:25px;">Aún no has agregado paquetes de larvas o pie de cría.</p>
            <a href="{{ route('catalogo.index') }}" style="background:#151515; color:#e4b353; padding:12px 25px; border-radius:6px; text-decoration:none; font-weight:700;">
                <i class="fas fa-store"></i> Explorar Catálogo
            </a>
        </div>
    @else
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:30px; align-items:start;">
            <!-- Lista de Productos en el Carrito -->
            <div style="background:#fff; border-radius:10px; padding:20px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #eee; text-align:left; color:#666; font-size:0.9rem;">
                            <th style="padding-bottom:12px;">Producto</th>
                            <th style="padding-bottom:12px; text-align:center;">Precio</th>
                            <th style="padding-bottom:12px; text-align:center;">Cantidad</th>
                            <th style="padding-bottom:12px; text-align:right;">Subtotal</th>
                            <th style="padding-bottom:12px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carrito as $id => $item)
                            @php 
                                $itemSubtotal = $item['precio'] * $item['cantidad'];
                            @endphp
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:15px 0;">
                                    <div style="font-weight:700; color:#151515;">{{ $item['nombre_producto'] }}</div>
                                </td>
                                <td style="padding:15px 0; text-align:center; color:#555;">
                                    ${{ number_format($item['precio'], 0, ',', '.') }}
                                </td>
                                <td style="padding:15px 0; text-align:center;">
                                    <form action="{{ route('carrito.actualizar', $id) }}" method="POST" style="display:inline-flex; align-items:center; gap:5px;">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="cantidad" value="{{ $item['cantidad'] }}" min="1" style="width:55px; padding:5px; text-align:center; border:1px solid #ccc; border-radius:4px;">
                                        <button type="submit" style="background:#f3f4f6; border:1px solid #ccc; padding:5px 8px; border-radius:4px; cursor:pointer;" title="Actualizar">
                                            <i class="fas fa-sync-alt" style="font-size:0.8rem;"></i>
                                        </button>
                                    </form>
                                </td>
                                <td style="padding:15px 0; text-align:right; font-weight:700; color:#151515;">
                                    ${{ number_format($itemSubtotal, 0, ',', '.') }}
                                </td>
                                <td style="padding:15px 0; text-align:right;">
                                    <form action="{{ route('carrito.eliminar', $id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1rem;" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Resumen de Compra -->
            <div style="background:#fff; border-radius:10px; padding:25px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:4px solid #e4b353;">
                <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:12px;">Resumen</h3>
                
                <div style="display:flex; justify-content:space-between; margin:12px 0; color:#555;">
                    <span>Subtotal:</span>
                    <span style="font-weight:700;">${{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>

                <!-- Bloque de Cupón Promocional -->
                <div style="background:#fdf8f0; border:1px solid #fde68a; border-radius:8px; padding:12px; margin:15px 0;">
                    <form action="{{ route('carrito.cupon.aplicar') }}" method="POST" style="display:flex; gap:8px;">
                        @csrf
                        <input type="text" name="codigo_cupon" value="{{ $cuponAplicado['codigo'] ?? '' }}" placeholder="Código de cupón" style="flex:1; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; text-transform:uppercase;">
                        <button type="submit" style="background:#111111; color:#e4b353; border:none; padding:8px 14px; border-radius:6px; font-weight:700; font-size:0.82rem; cursor:pointer;">
                            Aplicar
                        </button>
                    </form>

                    @if(!empty($cuponAplicado))
                        <div style="margin-top:10px; padding-top:8px; border-top:1px dashed #fcd34d; font-size:0.82rem; color:#15803d; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <i class="fas fa-tag"></i> <strong>{{ $cuponAplicado['nombre'] ?? $cuponAplicado['codigo'] }}</strong>
                                <span style="color:#4b5563;">(-{{ number_format($cuponAplicado['porcentaje'], 0) }}%)</span>
                            </div>
                            <form action="{{ route('carrito.cupon.quitar') }}" method="POST" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none; border:none; color:#b91c1c; font-weight:700; cursor:pointer; font-size:0.8rem; text-decoration:underline;">
                                    Quitar
                                </button>
                            </form>
                        </div>
                    @elseif(isset($promocionesDisponibles) && $promocionesDisponibles->isNotEmpty())
                        <div style="margin-top:10px; font-size:0.78rem; color:#92400e;">
                            <strong>Promociones disponibles:</strong>
                            <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top:5px;">
                                @foreach($promocionesDisponibles as $promoDisp)
                                    <form action="{{ route('carrito.cupon.aplicar') }}" method="POST" style="margin:0; display:inline;">
                                        @csrf
                                        <input type="hidden" name="codigo_cupon" value="{{ $promoDisp->codigo_cupon }}">
                                        <button type="submit" style="background:#111111; color:#e4b353; border:none; padding:3px 8px; border-radius:4px; font-size:0.75rem; font-weight:700; cursor:pointer;" title="Click para aplicar: {{ $promoDisp->nombre_comercial }}">
                                            {{ $promoDisp->nombre_comercial }} ({{ number_format($promoDisp->descuento_porcentaje, 0) }}% OFF)
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if(!empty($descuentoMonto) && $descuentoMonto > 0)
                    <div style="display:flex; justify-content:space-between; margin:12px 0; color:#15803d; font-weight:700;">
                        <span>Descuento ({{ $cuponAplicado['nombre'] ?? $cuponAplicado['codigo'] }}):</span>
                        <span>-${{ number_format($descuentoMonto, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div style="display:flex; justify-content:space-between; margin:12px 0; color:#555;">
                    <span>Envío:</span>
                    <span>
                        @if($costoEnvio === 0.0 || $costoEnvio === 0)
                            <strong style="color:#15803d;">¡GRATIS!</strong>
                        @else
                            ${{ number_format($costoEnvio, 0, ',', '.') }}
                        @endif
                    </span>
                </div>

                <div style="display:flex; justify-content:space-between; margin:20px 0 25px 0; padding-top:12px; border-top:2px solid #eee; font-size:1.3rem; font-weight:800; color:#151515;">
                    <span>Total:</span>
                    <span>${{ number_format($total, 0, ',', '.') }} COP</span>
                </div>

                <a href="{{ route('checkout.index') }}" style="display:block; text-align:center; background:#151515; color:#e4b353; padding:14px; border-radius:8px; text-decoration:none; font-weight:700; font-size:1.05rem;">
                    <i class="fas fa-credit-card"></i> Finalizar Compra
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
