@extends('layouts.admin')

@section('title', 'Editar Producto | BIO-GEST Tenebrios Gold')
@section('header_title', 'Edición de Producto')

@section('content')
    <div style="max-width:920px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <div>
                <h1 style="margin:0; font-size:1.4rem; color:#111111;">
                    <i class="fa-solid fa-pen-to-square" style="color:#e6a100;"></i> Editar: {{ $producto->nombre_producto }}
                </h1>
                <p style="margin:4px 0 0; color:#6b7280; font-size:0.88rem;">
                    Actualiza la ficha técnica, precios, estado o fotografía de este producto.
                </p>
            </div>
            <a href="{{ route('admin.productos.index') }}" class="tg-btn-dark" style="padding:8px 16px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>

        <div class="tg-card">
            <form action="{{ route('admin.productos.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Categoría *</label>
                        <select name="id_categoria" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id_categoria }}" {{ old('id_categoria', $producto->id_categoria) == $cat->id_categoria ? 'selected' : '' }}>
                                    {{ $cat->nombre_categoria }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Nombre del Producto *</label>
                        <input type="text" name="nombre_producto" value="{{ old('nombre_producto', $producto->nombre_producto) }}" required maxlength="150"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Precio (COP) *</label>
                        <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio', $producto->precio) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Stock Físico *</label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', $producto->stock) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Stock Mínimo (Alerta)</label>
                        <input type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Estado *</label>
                        <select name="estado" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="Activo" {{ old('estado', $producto->estado) === 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Inactivo" {{ old('estado', $producto->estado) === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Especies Recomendadas</label>
                        <input type="text" name="especies_recomendadas" value="{{ old('especies_recomendadas', $producto->especies_recomendadas) }}" maxlength="255"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Política de Garantía y Reembolso</label>
                        <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso', $producto->politica_reembolso) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <!-- ZONA MODERNA DE SUBIDA DE IMAGEN (Drag & Drop + Selector + Preview) -->
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#111111;">
                        <i class="fa-regular fa-image" style="color:#e6a100;"></i> Imagen del Producto (Catálogo y Tienda)
                    </label>

                    <div id="dropzoneContainer" style="border:2px dashed #cbd5e1; border-radius:10px; background:#f8fafc; padding:22px 20px; text-align:center; transition:all 0.25s ease; cursor:pointer;">
                        <input type="file" name="imagen" id="productImageInput" accept="image/png,image/jpeg,image/webp" style="display:none;">

                        <!-- Contenedor Vista Previa Instantánea o Imagen Actual -->
                        <div id="dropzonePreview" style="display:flex; align-items:center; justify-content:center; gap:20px; flex-wrap:wrap;">
                            <div style="width:120px; height:120px; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; background:#ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">
                                <img id="previewImageElement"
                                     src="{{ asset('assets/img/' . ($producto->imagen ?? 'Tenebrios_img.png')) }}"
                                     alt="{{ $producto->nombre_producto }}"
                                     style="max-width:100%; max-height:100%; object-fit:contain;"
                                     onerror="this.src='{{ asset('assets/img/Tenebrios_img.png') }}';">
                            </div>
                            <div style="text-align:left;">
                                <div id="previewFileName" style="font-weight:700; color:#111111; font-size:0.9rem;">
                                    {{ $producto->imagen ? basename($producto->imagen) : 'Imagen actual' }}
                                </div>
                                <div id="previewFileSize" style="color:#64748b; font-size:0.8rem; margin:2px 0 8px;">
                                    Arrastra una nueva imagen o haz clic para sustituirla
                                </div>
                                <button type="button" id="btnChangeImage" style="padding:6px 12px; background:#111111; color:#ffffff; border:none; border-radius:6px; font-size:0.8rem; cursor:pointer;">
                                    <i class="fa-solid fa-arrows-rotate"></i> Seleccionar nueva imagen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Descripción Comercial</label>
                    <textarea name="descripcion" rows="3" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:24px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Beneficios</label>
                        <textarea name="beneficios" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('beneficios', $producto->beneficios) }}</textarea>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Instrucciones de Conservación</label>
                        <textarea name="instrucciones_conservacion" rows="2" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('instrucciones_conservacion', $producto->instrucciones_conservacion) }}</textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <a href="{{ route('admin.productos.index') }}" class="btn-filter-secondary" style="padding:10px 18px;">
                        Cancelar
                    </a>
                    <button type="submit" class="tg-btn-dark" style="padding:10px 22px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzoneContainer');
    const fileInput = document.getElementById('productImageInput');
    const previewImg = document.getElementById('previewImageElement');
    const fileNameEl = document.getElementById('previewFileName');
    const fileSizeEl = document.getElementById('previewFileSize');
    const btnChange = document.getElementById('btnChangeImage');

    if (!dropzone || !fileInput) return;

    dropzone.addEventListener('click', (e) => {
        if (e.target !== btnChange && !btnChange.contains(e.target)) {
            fileInput.click();
        }
    });

    btnChange.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.click();
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#e6a100';
            dropzone.style.backgroundColor = '#fdf8f0';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#f8fafc';
        });
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    function handleFileSelect(file) {
        if (!file.type.match('image.*')) {
            alert('Por favor selecciona un archivo de imagen válido (PNG, JPEG, WEBP).');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            fileNameEl.textContent = file.name;
            fileSizeEl.textContent = 'Nueva imagen seleccionada: ' + (file.size / 1024).toFixed(1) + ' KB';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
