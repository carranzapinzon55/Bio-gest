@extends('layouts.admin')

@section('title', 'Editar Producto | BIO-GEST Tenebrios Gold')
@section('header_title', 'Edición de Producto')

@section('content')
    <div style="max-width:900px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <h1 style="margin:0; font-size:1.4rem; color:#151515;">
                <i class="fa-solid fa-pen-to-square" style="color:#e4b353;"></i> Editar: {{ $producto->nombre_producto }}
            </h1>
            <a href="{{ route('admin.productos.index') }}" class="tg-btn-dark" style="padding:8px 16px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="tg-card">
            <form action="{{ route('admin.productos.update', $producto->id_producto) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Categoría *</label>
                        <select name="id_categoria" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ old('id_categoria', $producto->id_categoria) == $cat->id_categoria ? 'selected' : '' }}>
                                    {{ $cat->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Nombre del Producto *</label>
                        <input type="text" name="nombre_producto" value="{{ old('nombre_producto', $producto->nombre_producto) }}" required maxlength="150"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Precio (COP) * <small style="color:#64748b;">(&gt; 0)</small></label>
                        <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio', $producto->precio) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Stock Disponible *</label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', $producto->stock) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Stock Mínimo (Alerta)</label>
                        <input type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo ?? 10) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Estado *</label>
                        <select name="estado" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="Activo" {{ old('estado', $producto->estado) === 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Inactivo" {{ old('estado', $producto->estado) === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Especies Recomendadas</label>
                        <input type="text" name="especies_recomendadas" value="{{ old('especies_recomendadas', $producto->especies_recomendadas) }}" maxlength="255"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Archivo de Imagen</label>
                        <input type="text" name="imagen" value="{{ old('imagen', $producto->imagen) }}" maxlength="255"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Descripción Comercial</label>
                    <textarea name="descripcion" rows="3" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Beneficios</label>
                        <textarea name="beneficios" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('beneficios', $producto->beneficios) }}</textarea>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Instrucciones de Conservación</label>
                        <textarea name="instrucciones_conservacion" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('instrucciones_conservacion', $producto->instrucciones_conservacion) }}</textarea>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Política de Garantía y Reembolso</label>
                    <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso', $producto->politica_reembolso) }}"
                           style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <a href="{{ route('admin.productos.index') }}" style="padding:11px 20px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; font-weight:600;">
                        Cancelar
                    </a>
                    <button type="submit" class="tg-btn-dark" style="padding:11px 26px;">
                        <i class="fa-solid fa-check"></i> Actualizar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
