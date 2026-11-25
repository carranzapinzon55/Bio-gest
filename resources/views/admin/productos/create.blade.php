@extends('layouts.admin')

@section('title', 'Registrar Producto | BIO-GEST Tenebrios Gold')
@section('header_title', 'Registrar Nuevo Producto')

@section('content')
    <div style="max-width:900px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <h1 style="margin:0; font-size:1.4rem; color:#151515;">
                <i class="fa-solid fa-box-open" style="color:#e4b353;"></i> Nuevo Producto en Catálogo
            </h1>
            <a href="{{ route('admin.productos.index') }}" class="tg-btn-dark" style="padding:8px 16px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <div class="tg-card">
            <form action="{{ route('admin.productos.store') }}" method="POST">
                @csrf

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Categoría *</label>
                        <select name="id_categoria" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="">-- Seleccionar categoría --</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ old('id_categoria') == $cat->id_categoria ? 'selected' : '' }}>
                                    {{ $cat->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Nombre del Producto *</label>
                        <input type="text" name="nombre_producto" value="{{ old('nombre_producto') }}" required maxlength="150"
                               placeholder="Ej. Paquete Recomendado – 2.000 larvas"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Precio (COP) * <small style="color:#64748b;">(&gt; 0)</small></label>
                        <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio') }}" required
                               placeholder="34900"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Stock Inicial *</label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', 0) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Stock Mínimo (Alerta)</label>
                        <input type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', 10) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Estado *</label>
                        <select name="estado" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="Activo" {{ old('estado', 'Activo') === 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Inactivo" {{ old('estado') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Especies Recomendadas</label>
                        <input type="text" name="especies_recomendadas" value="{{ old('especies_recomendadas') }}" maxlength="255"
                               placeholder="Ej. Aves, reptiles, erizos, peces..."
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Archivo de Imagen (public/assets/img/)</label>
                        <input type="text" name="imagen" value="{{ old('imagen', 'Tenebrios_img.png') }}" maxlength="255"
                               placeholder="Ej. paquete_500.jpg"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Descripción Comercial</label>
                    <textarea name="descripcion" rows="3" placeholder="Describe el contenido y ventajas del paquete..."
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('descripcion') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Beneficios Nutricionales / Productivos</label>
                        <textarea name="beneficios" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('beneficios') }}</textarea>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Instrucciones de Conservación</label>
                        <textarea name="instrucciones_conservacion" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('instrucciones_conservacion') }}</textarea>
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#151515;">Política de Garantía y Reembolso</label>
                    <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso', 'Garantía 100% de llegada viva documentando desempaque.') }}"
                           style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <a href="{{ route('admin.productos.index') }}" style="padding:11px 20px; border:1px solid #cbd5e1; border-radius:6px; text-decoration:none; color:#475569; font-weight:600;">
                        Cancelar
                    </a>
                    <button type="submit" class="tg-btn-dark" style="padding:11px 26px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
