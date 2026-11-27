@extends('layouts.admin')

@section('title', 'Registrar Producto | BIO-GEST Tenebrios Gold')
@section('header_title', 'Registrar Nuevo Producto')

@section('content')
    <div style="max-width:920px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
            <div>
                <h1 style="margin:0; font-size:1.4rem; color:#111111;">
                    <i class="fa-solid fa-box-open" style="color:#e6a100;"></i> Nuevo Producto en Catálogo
                </h1>
                <p style="margin:4px 0 0; color:#6b7280; font-size:0.88rem;">
                    Agrega un producto oficial de TENEBRIOS GOLD con fotografía, precio y control de stock.
                </p>
            </div>
            <a href="{{ route('admin.productos.index') }}" class="tg-btn-dark" style="padding:8px 16px;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>

        <div class="tg-card">
            <form action="{{ route('admin.productos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Categoría *</label>
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
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Nombre del Producto *</label>
                        <input type="text" name="nombre_producto" value="{{ old('nombre_producto') }}" required maxlength="150"
                               placeholder="Ej. Paquete Recomendado – 2.000 larvas vivas"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Precio (COP) * <small style="color:#64748b;">(&gt; 0)</small></label>
                        <input type="number" step="0.01" min="0.01" name="precio" value="{{ old('precio') }}" required
                               placeholder="59900"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Stock Inicial *</label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', 0) }}" required
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Stock Mínimo (Alerta)</label>
                        <input type="number" min="0" name="stock_minimo" value="{{ old('stock_minimo', 10) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Estado *</label>
                        <select name="estado" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                            <option value="Activo" {{ old('estado', 'Activo') === 'Activo' ? 'selected' : '' }}>Activo</option>
                            <option value="Inactivo" {{ old('estado') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:18px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Especies Recomendadas</label>
                        <input type="text" name="especies_recomendadas" value="{{ old('especies_recomendadas') }}" maxlength="255"
                               placeholder="Ej. Aves ornamentales, reptiles, erizos, peces carnívoros"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Política de Garantía y Reembolso</label>
                        <input type="text" name="politica_reembolso" value="{{ old('politica_reembolso', 'Garantía 100% de llegada viva documentando desempaque en video.') }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">
                    </div>
                </div>

                <!-- ZONA MODERNA DE SUBIDA DE IMAGEN (Drag & Drop + Selector + Preview) -->
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#111111;">
                        <i class="fa-regular fa-image" style="color:#e6a100;"></i> Imagen del Producto (Catálogo y Tienda)
                    </label>

                    <div id="dropzoneContainer" style="border:2px dashed #cbd5e1; border-radius:10px; background:#f8fafc; padding:24px 20px; text-align:center; transition:all 0.25s ease; cursor:pointer;">
                        <input type="file" name="imagen" id="productImageInput" accept="image/png,image/jpeg,image/webp" style="display:none;">

                        <div id="dropzonePrompt">
                            <div style="width:48px; height:48px; margin:0 auto 10px; background:#fdf8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#e6a100; font-size:1.4rem;">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div style="font-weight:700; color:#111111; font-size:0.95rem; margin-bottom:4px;">
                                Arrastra y suelta la imagen aquí o <span style="color:#e6a100; text-decoration:underline;">explora tu equipo</span>
                            </div>
                            <div style="color:#64748b; font-size:0.8rem;">
                                Formatos admitidos: PNG, JPEG, JPG o WEBP (máx. 5 MB). Resolución recomendada: 800x800 px.
                            </div>
                        </div>

                        <!-- Contenedor Vista Previa Instantánea -->
                        <div id="dropzonePreview" style="display:none; align-items:center; justify-content:center; gap:20px; flex-wrap:wrap;">
                            <div style="width:120px; height:120px; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; background:#ffffff; box-shadow:0 2px 6px rgba(0,0,0,0.06); display:flex; align-items:center; justify-content:center;">
                                <img id="previewImageElement" src="" alt="Vista previa del producto" style="max-width:100%; max-height:100%; object-fit:contain;">
                            </div>
                            <div style="text-align:left;">
                                <div id="previewFileName" style="font-weight:700; color:#111111; font-size:0.9rem;">archivo.png</div>
                                <div id="previewFileSize" style="color:#64748b; font-size:0.8rem; margin:2px 0 8px;">0 KB</div>
                                <button type="button" id="btnChangeImage" style="padding:6px 12px; background:#111111; color:#ffffff; border:none; border-radius:6px; font-size:0.8rem; cursor:pointer;">
                                    <i class="fa-solid fa-arrows-rotate"></i> Cambiar imagen
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Descripción Comercial</label>
                    <textarea name="descripcion" rows="3" placeholder="Describe las características y ventajas del producto..."
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('descripcion') }}</textarea>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:18px; margin-bottom:24px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Beneficios Nutricionales / Productivos</label>
                        <textarea name="beneficios" rows="2" placeholder="Ej. 52% proteína bruta, alto contenido de ácido linoleico..."
                                  style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('beneficios') }}</textarea>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#111111;">Instrucciones de Conservación</label>
                        <textarea name="instrucciones_conservacion" rows="2" placeholder="Ej. Mantener en refrigeración suave (8-12°C) o a temperatura ambiente..."
                                  style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px;">{{ old('instrucciones_conservacion') }}</textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #e2e8f0; padding-top:18px;">
                    <a href="{{ route('admin.productos.index') }}" class="btn-filter-secondary" style="padding:10px 18px;">
                        Cancelar
                    </a>
                    <button type="submit" class="tg-btn-dark" style="padding:10px 22px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar y Publicar Producto
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
    const promptBox = document.getElementById('dropzonePrompt');
    const previewBox = document.getElementById('dropzonePreview');
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
            fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            promptBox.style.display = 'none';
            previewBox.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
