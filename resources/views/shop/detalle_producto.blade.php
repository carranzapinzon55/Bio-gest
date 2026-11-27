@extends('layouts.app')

@section('title', $producto->nombre_producto . ' | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/detalle_producto_style.css') }}">
<style>
    .btn-comprar-detalle {
        flex: 1;
        background: #151515;
        color: #e4b353;
        border: 2px solid #151515;
        padding: 13px 20px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-comprar-detalle:hover:not(:disabled) {
        background: #000000;
        color: #f7cb6b;
    }
    .btn-agregar-detalle {
        flex: 1;
        background: #ffffff;
        color: #151515;
        border: 2px solid #151515;
        padding: 13px 20px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    .btn-agregar-detalle:hover:not(:disabled) {
        background: #fdf8f0;
        border-color: #e4b353;
        color: #b48316;
    }
</style>
@endpush

@section('content')
<div style="max-width:1100px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); overflow:hidden; display:flex; flex-wrap:wrap;">
        <!-- Imagen del Producto -->
        <div style="flex:1; min-width:320px; background:#fafafa; display:flex; align-items:center; justify-content:center; padding:30px;">
            <img src="{{ asset('assets/img/' . ($producto->imagen ?? 'Tenebrios_img.png')) }}" alt="{{ $producto->nombre_producto }}" style="max-width:100%; max-height:400px; object-fit:contain;" onerror="this.src='{{ asset('assets/img/Tenebrios_img.png') }}';">
        </div>

        <!-- Información y Compra -->
        <div style="flex:1.2; min-width:320px; padding:35px; display:flex; flex-direction:column;">
            <span style="color:#e4b353; font-weight:700; text-transform:uppercase; font-size:0.85rem;">{{ optional($producto->categoria)->nombre_categoria }}</span>
            <h1 style="color:#151515; font-size:1.8rem; margin:10px 0;">{{ $producto->nombre_producto }}</h1>
            
            <div style="margin:15px 0; font-size:2rem; font-weight:800; color:#151515;">
                ${{ number_format($producto->precio, 0, ',', '.') }}
                <span style="font-size:0.9rem; color:#666; font-weight:400;">COP (IVA incluido)</span>
            </div>

            <p style="color:#555; line-height:1.6; margin-bottom:20px;">
                {{ $producto->descripcion }}
            </p>

            @if($producto->beneficios)
                <div style="background:#fef9ee; border-left:4px solid #e4b353; padding:12px; margin-bottom:15px; font-size:0.9rem;">
                    <strong><i class="fas fa-star" style="color:#e4b353;"></i> Beneficios:</strong> {{ $producto->beneficios }}
                </div>
            @endif

            @if($producto->instrucciones_conservacion)
                <div style="background:#f0f9ff; border-left:4px solid #0284c7; padding:12px; margin-bottom:15px; font-size:0.9rem;">
                    <strong><i class="fas fa-snowflake" style="color:#0284c7;"></i> Conservación:</strong> {{ $producto->instrucciones_conservacion }}
                </div>
            @endif

            <!-- Formulario con Doble Acción de Compra -->
            <form action="{{ route('carrito.agregar') }}" method="POST" id="formComprarDetalle" style="margin-top:auto; padding-top:20px; border-top:1px solid #eee;">
                @csrf
                <input type="hidden" name="id_producto" id="detalleIdProducto" value="{{ $producto->id_producto }}">
                
                <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px;">
                    <label for="cantidad" style="font-weight:700; color:#333;">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="{{ $producto->stock }}" style="width:80px; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:1rem; text-align:center;" {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                    <span style="color:#666; font-size:0.85rem;">({{ $producto->stock }} disponibles)</span>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <!-- Botón 1: Comprar (agrega al carrito y redirige inmediatamente a /carrito) -->
                    <button type="submit" class="btn-comprar-detalle" {{ $producto->stock <= 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }} title="Comprar y ver el carrito inmediatamente">
                        <i class="fas fa-bolt"></i> Comprar
                    </button>

                    <!-- Botón 2: Agregar al carrito (fetch POST en segundo plano) -->
                    <button type="button" class="btn-agregar-detalle" onclick="agregarDetalleAjax()" {{ $producto->stock <= 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }} title="Agregar al carrito sin salir de la página">
                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                    </button>

                    <a href="{{ route('catalogo.index') }}" style="background:#f3f4f6; color:#374151; padding:13px 18px; border-radius:8px; text-decoration:none; font-weight:600; display:flex; align-items:center; border:2px solid transparent;" title="Volver al catálogo">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function agregarDetalleAjax() {
        const idProducto = document.getElementById('detalleIdProducto')?.value;
        const inputCantidad = document.getElementById('cantidad');
        const cantidad = inputCantidad ? parseInt(inputCantidad.value, 10) || 1 : 1;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!idProducto) return;

        fetch('{{ route('carrito.agregar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                id_producto: parseInt(idProducto, 10),
                cantidad: cantidad
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                const countBadge = document.getElementById('cart-count');
                if (countBadge && typeof data.cart_count !== 'undefined') {
                    countBadge.textContent = data.cart_count;
                }
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Producto agregado al carrito con éxito.', 'success');
                }
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'No se pudo agregar el producto.', 'error');
                }
            }
        })
        .catch(err => {
            console.error('Error al agregar al carrito:', err);
            if (typeof window.showToast === 'function') {
                window.showToast('Error de conexión con el servidor.', 'error');
            }
        });
    }
</script>
@endpush
