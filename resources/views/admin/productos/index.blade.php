@extends('layouts.app')

@section('title', 'Gestión de Productos | BIO-GEST Tenebrios Gold')
@section('header_title', 'Gestión de Productos (CRUD)')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-size:1.45rem; color:#151515;">Catálogo Administrativo de Productos</h1>
            <p style="margin:4px 0 0 0; color:#64748b; font-size:0.9rem;">Registra, edita, ajusta precios o desactiva productos de la tienda</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.inventario.index') }}" class="tg-btn-gold">
                <i class="fa-solid fa-warehouse"></i> Ver Inventario y Kardex
            </a>
            <a href="{{ route('admin.productos.create') }}" class="tg-btn-dark">
                <i class="fa-solid fa-plus"></i> Registrar Producto
            </a>
        </div>
    </div>

    <!-- Barra de Filtro Rápido -->
    <div class="tg-card" style="padding:16px 20px;">
        <form action="{{ route('admin.productos.index') }}" method="GET" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div style="flex:1; min-width:200px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#151515; margin-bottom:4px;">Buscar por nombre</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ej. Paquete 1.000 larvas..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px;">
            </div>
            <div style="min-width:180px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#151515; margin-bottom:4px;">Categoría</label>
                <select name="id_categoria" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria }}" {{ (string) request('id_categoria') === (string) $cat->id_categoria ? 'selected' : '' }}>
                            {{ $cat->nombre_categoria }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:150px;">
                <label style="display:block; font-size:0.8rem; font-weight:700; color:#151515; margin-bottom:4px;">Estado</label>
                <select name="estado" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    <option value="">Todos</option>
                    <option value="Activo" {{ request('estado') === 'Activo' ? 'selected' : '' }}>Activo</option>
                    <option value="Inactivo" {{ request('estado') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="tg-btn-dark" style="padding:9px 16px;">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
                @if(request()->hasAny(['q', 'id_categoria', 'estado']))
                    <a href="{{ route('admin.productos.index') }}" style="padding:9px 14px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; font-weight:600; font-size:0.88rem;">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Productos -->
    <div class="tg-card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio (COP)</th>
                        <th>Existencias</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $prod)
                        @php
                            $stockMin = $prod->stock_minimo ?? 10;
                        @endphp
                        <tr>
                            <td style="font-weight:700; color:#151515;">PROD-{{ str_pad((string) $prod->id_producto, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="font-weight:700; color:#151515;">{{ $prod->nombre_producto }}</div>
                                @if($prod->especies_recomendadas)
                                    <div style="font-size:0.78rem; color:#64748b;">
                                        <i class="fa-solid fa-paw" style="color:#e4b353;"></i> {{ \Illuminate\Support\Str::limit($prod->especies_recomendadas, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ optional($prod->categoria)->nombre_categoria ?? 'Sin categoría' }}</td>
                            <td style="font-weight:700; color:#151515;">${{ number_format($prod->precio, 0, ',', '.') }}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <span style="font-weight:800; font-size:0.95rem; color:{{ $prod->stock <= $stockMin ? '#b91c1c' : '#151515' }};">
                                        {{ $prod->stock }} uds.
                                    </span>
                                    @if($prod->stock <= 0)
                                        <span class="badge badge-danger">Agotado</span>
                                    @elseif($prod->stock <= $stockMin)
                                        <span class="badge badge-warning">Bajo (mín. {{ $stockMin }})</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $prod->estado === 'Activo' ? 'badge-active' : 'badge-danger' }}">
                                    {{ $prod->estado }}
                                </span>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('admin.productos.edit', $prod->id_producto) }}"
                                   style="background:#151515; color:#e4b353; padding:6px 12px; border-radius:6px; text-decoration:none; font-size:0.82rem; font-weight:700; margin-right:6px; display:inline-block;">
                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                </a>

                                <form action="{{ route('admin.productos.destroy', $prod->id_producto) }}" method="POST" style="display:inline;"
                                      onsubmit="return confirm('¿Confirmas cambiar el estado de este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    @if($prod->estado === 'Activo')
                                        <button type="submit" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:0.82rem; font-weight:700;">
                                            <i class="fa-solid fa-ban"></i> Desactivar
                                        </button>
                                    @else
                                        <button type="submit" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:0.82rem; font-weight:700;">
                                            <i class="fa-solid fa-rotate-left"></i> Activar
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:25px; color:#64748b;">
                                No se encontraron productos registrados con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
