@extends('layouts.app')

@section('title', 'Catálogo de Productos | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/catologo_style.css') }}">
@endpush

@section('content')
<div class="catalogo-container" style="max-width:1200px; margin:30px auto; padding:0 20px;">
    @if(isset($promocionesActivas) && $promocionesActivas->isNotEmpty())
        <div style="background:#111111; border:2px solid #e4b353; border-radius:10px; padding:14px 20px; margin-bottom:22px; color:#ffffff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-tags" style="color:#e4b353; font-size:1.3rem;"></i>
                <div>
                    <strong style="color:#e4b353;">Cupones de Descuento Disponibles:</strong>
                    <span style="font-size:0.85rem; color:#cbd5e1; margin-left:6px;">Aplícalos al finalizar tu compra</span>
                </div>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                @foreach($promocionesActivas as $promo)
                    <span style="background:#222222; border:1px dashed #e4b353; padding:5px 12px; border-radius:6px; font-size:0.85rem; font-weight:700; color:#e4b353;">
                        {{ $promo->codigo_cupon }}: <strong style="color:#4ade80;">-{{ number_format($promo->descuento_porcentaje, 0) }}%</strong>
                        <small style="color:#9ca3af;">(hasta {{ optional($promo->fecha_fin)->format('d/m/Y') }})</small>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:25px; border-bottom:2px solid #e4b353; padding-bottom:15px;">
        <h2 style="color:#151515; margin:0;"><i class="fas fa-boxes"></i> Catálogo de Alimento Vivo y Pie de Cría</h2>
        
        <!-- Filtro por Categorías -->
        <div style="display:flex; gap:10px; align-items:center; margin-top:10px;">
            <a href="{{ route('catalogo.index') }}" class="btn-filtro {{ !request('categoria') ? 'active' : '' }}" style="padding:6px 14px; border-radius:20px; text-decoration:none; font-weight:600; {{ !request('categoria') ? 'background:#e4b353; color:#151515;' : 'background:#eee; color:#333;' }}">Todos</a>
            @foreach($categorias as $cat)
                <a href="{{ route('catalogo.index', ['categoria' => $cat->id_categoria]) }}" class="btn-filtro {{ request('categoria') == $cat->id_categoria ? 'active' : '' }}" style="padding:6px 14px; border-radius:20px; text-decoration:none; font-weight:600; {{ request('categoria') == $cat->id_categoria ? 'background:#e4b353; color:#151515;' : 'background:#eee; color:#333;' }}">
                    {{ $cat->nombre_categoria }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Rejilla de Productos -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:25px;">
        @forelse($productos as $producto)
            <div style="background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08); display:flex; flex-direction:column; border:1px solid #f0f0f0;">
                <div style="height:190px; background:#fafafa; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                    <img src="{{ asset('assets/img/' . ($producto->imagen ?? 'Tenebrios_img.png')) }}" alt="{{ $producto->nombre_producto }}" style="max-height:100%; max-width:100%; object-fit:contain;" onerror="this.src='{{ asset('assets/img/Tenebrios_img.png') }}';">
                </div>
                <div style="padding:15px; display:flex; flex-direction:column; flex:1;">
                    <span style="font-size:0.75rem; text-transform:uppercase; color:#888; font-weight:700;">{{ optional($producto->categoria)->nombre_categoria }}</span>
                    <h3 style="font-size:1.1rem; margin:8px 0; color:#151515;">
                        <a href="{{ route('catalogo.show', $producto->id_producto) }}" style="text-decoration:none; color:inherit;">
                            {{ $producto->nombre_producto }}
                        </a>
                    </h3>
                    <p style="font-size:0.85rem; color:#666; flex:1; margin-bottom:12px; line-height:1.4;">
                        {{ \Illuminate\Support\Str::limit($producto->descripcion, 90) }}
                    </p>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <span style="font-size:1.25rem; font-weight:800; color:#151515;">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                        @if($producto->stock > 0)
                            <span style="font-size:0.8rem; background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:12px; font-weight:600;">Stock: {{ $producto->stock }}</span>
                        @else
                            <span style="font-size:0.8rem; background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:12px; font-weight:600;">Agotado</span>
                        @endif
                    </div>

                    <form action="{{ route('carrito.agregar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" style="width:100%; background:#151515; color:#e4b353; border:none; padding:10px; border-radius:6px; font-weight:700; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:8px;" {{ $producto->stock <= 0 ? 'disabled style=opacity:0.6;cursor:not-allowed;' : '' }}>
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:50px;">
                <i class="fas fa-box-open" style="font-size:3rem; color:#ccc;"></i>
                <p style="margin-top:15px; color:#777; font-size:1.1rem;">No se encontraron productos disponibles con el criterio seleccionado.</p>
                <a href="{{ route('catalogo.index') }}" style="color:#e4b353; font-weight:700; text-decoration:none;">Ver todos los productos</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
