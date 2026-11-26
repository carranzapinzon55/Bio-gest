@extends('layouts.app')

@section('title', 'Catálogo de Productos | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/catologo_style.css') }}">
<style>
    .product-card {
        cursor: pointer;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        display: flex;
        flex-direction: column;
        border: 1px solid #eef0f3;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
        border-color: #e4b353;
    }
    .filter-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .filter-bar .form-control {
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.88rem;
        color: #1e293b;
        background: #ffffff;
        outline: none;
        transition: border-color 0.15s;
    }
    .filter-bar .form-control:focus {
        border-color: #e4b353;
    }
    .btn-action-comprar {
        background: #151515;
        color: #e4b353;
        border: 1px solid #151515;
        padding: 9px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.84rem;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action-comprar:hover:not(:disabled) {
        background: #000000;
        color: #f7cb6b;
    }
    .btn-action-agregar {
        background: #ffffff;
        color: #151515;
        border: 1.5px solid #151515;
        padding: 8px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.84rem;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .btn-action-agregar:hover:not(:disabled) {
        background: #fdf8f0;
        border-color: #e4b353;
        color: #b48316;
    }
</style>
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

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom:20px; border-bottom:2px solid #e4b353; padding-bottom:12px;">
        <div>
            <h2 style="color:#151515; margin:0;"><i class="fas fa-boxes"></i> Catálogo de Alimento Vivo y Pie de Cría</h2>
            <p style="margin:4px 0 0; color:#64748b; font-size:0.9rem;">Explora nuestra selección zootécnica de alta calidad nutricional</p>
        </div>
        <div style="font-size:0.9rem; color:#64748b; margin-top:8px;">
            Mostrando <strong>{{ $productos->count() }}</strong> {{ $productos->count() === 1 ? 'producto' : 'productos' }}
        </div>
    </div>

    <!-- Barra de Filtros y Ordenamiento Funcional -->
    <div class="filter-bar">
        <form action="{{ route('catalogo.index') }}" method="GET" id="filtroForm" style="display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end;">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif

            <!-- Filtro por Categoría -->
            <div style="flex:1; min-width:160px;">
                <label for="filtroCategoria" style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:5px;">
                    <i class="fas fa-layer-group" style="color:#e4b353;"></i> Categoría
                </label>
                <select name="categoria" id="filtroCategoria" class="form-control" style="width:100%;">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria }}" {{ request('categoria') == $cat->id_categoria ? 'selected' : '' }}>
                            {{ $cat->nombre_categoria }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Rango de Precio -->
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:5px;">
                    <i class="fas fa-dollar-sign" style="color:#e4b353;"></i> Rango de Precio (COP)
                </label>
                <div style="display:flex; gap:6px; align-items:center;">
                    <input type="number" name="precio_min" value="{{ request('precio_min') }}" placeholder="Mínimo" class="form-control" style="width:50%;" min="0" step="500">
                    <span style="color:#94a3b8;">-</span>
                    <input type="number" name="precio_max" value="{{ request('precio_max') }}" placeholder="Máximo" class="form-control" style="width:50%;" min="0" step="500">
                </div>
            </div>

            <!-- Filtro por Disponibilidad -->
            <div style="flex:1; min-width:140px;">
                <label for="filtroDisponibilidad" style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:5px;">
                    <i class="fas fa-warehouse" style="color:#e4b353;"></i> Disponibilidad
                </label>
                <select name="disponibilidad" id="filtroDisponibilidad" class="form-control" style="width:100%;">
                    <option value="">Cualquier estado</option>
                    <option value="en_stock" {{ request('disponibilidad') === 'en_stock' ? 'selected' : '' }}>En Stock</option>
                    <option value="agotado" {{ request('disponibilidad') === 'agotado' ? 'selected' : '' }}>Agotados</option>
                </select>
            </div>

            <!-- Ordenamiento -->
            <div style="flex:1; min-width:160px;">
                <label for="filtroOrden" style="display:block; font-size:0.8rem; font-weight:700; color:#334155; margin-bottom:5px;">
                    <i class="fas fa-arrow-down-wide-short" style="color:#e4b353;"></i> Ordenar por
                </label>
                <select name="orden" id="filtroOrden" class="form-control" style="width:100%;">
                    <option value="recientes" {{ request('orden') === 'recientes' || !request('orden') ? 'selected' : '' }}>Más recientes</option>
                    <option value="precio_asc" {{ request('orden') === 'precio_asc' ? 'selected' : '' }}>Menor precio</option>
                    <option value="precio_desc" {{ request('orden') === 'precio_desc' ? 'selected' : '' }}>Mayor precio</option>
                    <option value="nombre_asc" {{ request('orden') === 'nombre_asc' ? 'selected' : '' }}>Nombre (A - Z)</option>
                </select>
            </div>

            <!-- Botones de Acción de Filtros -->
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn-action-comprar" style="padding:9px 16px;">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                @if(request()->hasAny(['categoria', 'precio_min', 'precio_max', 'disponibilidad', 'orden', 'q']))
                    <a href="{{ route('catalogo.index') }}" class="btn-action-agregar" style="padding:8px 14px; text-decoration:none;" title="Limpiar todos los filtros">
                        <i class="fas fa-rotate-left"></i> Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Rejilla de Productos con Tarjetas Interactivas y Doble Acción -->
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:25px;">
        @forelse($productos as $producto)
            <div class="product-card" onclick="window.location.href='{{ url('/productos/' . $producto->id_producto) }}'">
                <div style="height:190px; background:#fafafa; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                    <img src="{{ asset('assets/img/' . ($producto->imagen ?? 'Tenebrios_img.png')) }}" alt="{{ $producto->nombre_producto }}" style="max-height:100%; max-width:100%; object-fit:contain;" onerror="this.src='{{ asset('assets/img/Tenebrios_img.png') }}';">
                    @if($producto->stock <= 0)
                        <span style="position:absolute; top:10px; right:10px; background:#ef4444; color:#fff; font-size:0.75rem; font-weight:700; padding:3px 8px; border-radius:6px;">Agotado</span>
                    @endif
                </div>
                <div style="padding:16px; display:flex; flex-direction:column; flex:1;">
                    <span style="font-size:0.72rem; text-transform:uppercase; color:#888; font-weight:700; letter-spacing:0.5px;">
                        {{ optional($producto->categoria)->nombre_categoria ?? 'General' }}
                    </span>
                    <h3 style="font-size:1.05rem; margin:6px 0; color:#151515; font-weight:800; line-height:1.3;">
                        <a href="{{ url('/productos/' . $producto->id_producto) }}" onclick="event.stopPropagation();" style="text-decoration:none; color:inherit;">
                            {{ $producto->nombre_producto }}
                        </a>
                    </h3>
                    <p style="font-size:0.83rem; color:#64748b; flex:1; margin-bottom:12px; line-height:1.4;">
                        {{ \Illuminate\Support\Str::limit($producto->descripcion, 85) }}
                    </p>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                        <span style="font-size:1.25rem; font-weight:800; color:#151515;">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                        @if($producto->stock > 0)
                            <span style="font-size:0.75rem; background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:12px; font-weight:700;">Stock: {{ $producto->stock }}</span>
                        @endif
                    </div>

                    <!-- Doble Acción de Compra (deteniendo propagación para no abrir detalle) -->
                    <div class="product-actions" style="display:flex; gap:8px;" onclick="event.stopPropagation();">
                        <!-- Botón 1: Comprar (agrega y redirige inmediatamente a /carrito) -->
                        <form action="{{ route('carrito.agregar') }}" method="POST" style="flex:1; margin:0;" onclick="event.stopPropagation();">
                            @csrf
                            <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn-action-comprar" style="width:100%;" {{ $producto->stock <= 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }} title="Comprar y ver carrito inmediatamente">
                                <i class="fas fa-bolt"></i> Comprar
                            </button>
                        </form>

                        <!-- Botón 2: Agregar al carrito (fetch POST en segundo plano sin recargar ni redirigir) -->
                        <button type="button" class="btn-action-agregar" style="flex:1;" onclick="agregarAlCarritoAjax({{ $producto->id_producto }}, 1, event)" {{ $producto->stock <= 0 ? 'disabled style=opacity:0.5;cursor:not-allowed;' : '' }} title="Agregar al carrito en segundo plano">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:50px; background:#ffffff; border-radius:12px; border:1px dashed #cbd5e1;">
                <i class="fas fa-box-open" style="font-size:3rem; color:#cbd5e1;"></i>
                <p style="margin-top:15px; color:#64748b; font-size:1.05rem;">No se encontraron productos disponibles con los filtros seleccionados.</p>
                <a href="{{ route('catalogo.index') }}" class="btn-action-comprar" style="display:inline-flex; margin-top:8px; padding:10px 20px;">
                    Ver todos los productos
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
    function agregarAlCarritoAjax(idProducto, cantidad, event) {
        if (event) {
            event.stopPropagation();
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('{{ route('carrito.agregar') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                id_producto: idProducto,
                cantidad: cantidad || 1
            })
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                // Actualizar contador del carrito en el header
                const countBadge = document.getElementById('cart-count');
                if (countBadge && typeof data.cart_count !== 'undefined') {
                    countBadge.textContent = data.cart_count;
                }
                // Mostrar notificación Toast
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
                window.showToast('Error de comunicación con el servidor.', 'error');
            }
        });
    }
</script>
@endpush
