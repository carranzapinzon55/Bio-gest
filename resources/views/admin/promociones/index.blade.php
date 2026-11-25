@extends('layouts.admin')

@section('title', 'Promociones y Cupones | BIO-GEST Tenebrios Gold')
@section('header_title', 'Promociones y Cupones')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Promociones y Cupones
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Administración de campañas comerciales y cupones de descuento en MySQL para <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <button type="button" class="tg-btn-dark" onclick="abrirCrearPromocion()">
            <i class="fa-solid fa-plus"></i> Nueva Promoción
        </button>
    </div>

    <!-- Layout Dividido: Tabla de Promociones a la Izquierda y Panel Lateral a la Derecha con scroll interno limpio -->
    <div style="display:grid; grid-template-columns: minmax(0, 1.45fr) minmax(360px, 1fr); gap:20px; align-items:start;">
        <!-- Columna Izquierda: Tabla de Promociones -->
        <div class="tg-card">
            <div class="card-header-flex" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="font-size:1.15rem; color:#111111; margin:0;">
                    <i class="fa-solid fa-tags" style="color:#e6a100;"></i> Listado de Cupones y Campañas
                </h2>
                <span style="font-size:0.84rem; font-weight:700; color:#6b7280;">
                    Total: {{ $promociones->count() }} cupones
                </span>
            </div>

            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Código Cupón</th>
                            <th style="text-align:center;">Descuento</th>
                            <th>Vigencia</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promociones as $promo)
                            @php
                                $promoJson = htmlspecialchars(json_encode([
                                    'id' => $promo->id_promocion,
                                    'codigo' => $promo->codigo_cupon,
                                    'descuento' => (float) $promo->descuento_porcentaje,
                                    'fecha_inicio' => optional($promo->fecha_inicio)->format('Y-m-d\TH:i'),
                                    'fecha_fin' => optional($promo->fecha_fin)->format('Y-m-d\TH:i'),
                                    'estado' => $promo->estado,
                                    'update_url' => route('admin.promociones.update', $promo->id_promocion),
                                ]), ENT_QUOTES, 'UTF-8');
                            @endphp
                            <tr id="row-promo-{{ $promo->id_promocion }}" class="promo-table-row" style="cursor:pointer;" onclick="cargarPromocionEnPanel({{ $promoJson }})">
                                <td>
                                    <strong style="font-size:0.95rem; color:#111111; letter-spacing:0.5px; display:inline-flex; align-items:center;">
                                        <i class="fa-solid fa-ticket" style="color:#e6a100; margin-right:6px;"></i>
                                        {{ $promo->codigo_cupon }}
                                    </strong>
                                </td>
                                <td style="text-align:center; font-weight:800; color:#047857; font-size:1rem;">
                                    {{ number_format($promo->descuento_porcentaje, 0) }}% OFF
                                </td>
                                <td style="color:#4b5563; font-size:0.82rem; white-space:nowrap;">
                                    <div><i class="fa-regular fa-calendar-check" style="color:#e6a100;"></i> {{ optional($promo->fecha_inicio)->format('d/m/Y') }}</div>
                                    <div style="color:#6b7280; font-size:0.76rem;">hasta {{ optional($promo->fecha_fin)->format('d/m/Y') }}</div>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge {{ $promo->estado === 'Activo' ? 'badge-active' : 'badge-danger' }}">
                                        {{ $promo->estado }}
                                    </span>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <button type="button" class="tg-btn-gold" style="padding:5px 10px; font-size:0.8rem; margin-right:4px;"
                                            onclick="event.stopPropagation(); cargarPromocionEnPanel({{ $promoJson }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.promociones.estado', $promo->id_promocion) }}" method="POST" style="display:inline;"
                                          onsubmit="event.stopPropagation(); return confirm('¿Cambiar estado del cupón {{ $promo->codigo_cupon }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="tg-btn-gold" style="padding:5px 10px; font-size:0.8rem; background:{{ $promo->estado === 'Activo' ? '#fee2e2' : '#d1fae5' }}; color:{{ $promo->estado === 'Activo' ? '#b91c1c' : '#047857' }}; border:none;"
                                                title="{{ $promo->estado === 'Activo' ? 'Desactivar cupón' : 'Activar cupón' }}">
                                            <i class="fa-solid {{ $promo->estado === 'Activo' ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.promociones.destroy', $promo->id_promocion) }}" method="POST" style="display:inline;"
                                          onsubmit="event.stopPropagation(); return confirm('¿Deseas eliminar definitivamente este cupón?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tg-btn-gold" style="padding:5px 10px; font-size:0.8rem; background:#fee2e2; color:#b91c1c; border:none; margin-left:4px;" title="Eliminar cupón">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center; padding:28px; color:#6b7280;">
                                    No hay promociones registradas en la base de datos MySQL.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Columna Derecha: Panel Lateral Formulario con Scroll Interno Limpio -->
        <div class="tg-card" id="panelPromocionLateral" style="position:sticky; top:85px; max-height:calc(100vh - 110px); display:flex; flex-direction:column; padding:0; overflow:hidden;">
            <!-- Encabezado fijo del panel -->
            <div style="padding:16px 20px; border-bottom:1px solid #e5e7eb; background:#ffffff; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-tag" style="color:#e6a100; font-size:1.1rem;"></i>
                    <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#111111;" id="panelTitle">Nueva Promoción</h3>
                </div>
                <button type="button" onclick="abrirCrearPromocion()" class="tg-btn-gold" style="padding:4px 10px; font-size:0.78rem;">
                    <i class="fa-solid fa-plus"></i> Nuevo
                </button>
            </div>

            <!-- Contenedor con scroll interno limpio -->
            <div style="padding:20px; overflow-y:auto; flex:1; -webkit-overflow-scrolling:touch;">
                <form id="formPromocion" action="{{ route('admin.promociones.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                            Código del Cupón *
                        </label>
                        <input type="text" name="codigo_cupon" id="promoCodigo" required maxlength="50"
                               placeholder="Ej. TENEBRIO10, BLACKFRIDAY"
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-weight:800; letter-spacing:0.8px; font-size:0.92rem; text-transform:uppercase;">
                        <small style="color:#6b7280; font-size:0.75rem;">Se convertirá automáticamente a mayúsculas.</small>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                            Porcentaje de Descuento (%) *
                        </label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="number" step="0.5" min="1" max="100" name="descuento_porcentaje" id="promoDescuento" required
                                   placeholder="10"
                                   style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.92rem; font-weight:700;">
                            <span style="font-weight:800; color:#e6a100; font-size:1.1rem;">%</span>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                            Fecha y Hora de Inicio *
                        </label>
                        <input type="datetime-local" name="fecha_inicio" id="promoFechaInicio" required
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                            Fecha y Hora de Vencimiento *
                        </label>
                        <input type="datetime-local" name="fecha_fin" id="promoFechaFin" required
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:0.82rem; font-weight:700; color:#111111; margin-bottom:5px;">
                            Estado de la Campaña *
                        </label>
                        <select name="estado" id="promoEstado" required style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="Activo">Activo (Disponible en tienda)</option>
                            <option value="Inactivo">Inactivo (Pausado)</option>
                        </select>
                    </div>

                    <!-- Botones que nunca quedan recortados -->
                    <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #e5e7eb; padding-top:16px; margin-top:10px;">
                        <button type="button" onclick="abrirCrearPromocion()" style="padding:9px 16px; border:1px solid #cbd5e1; border-radius:6px; background:#ffffff; color:#4b5563; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="submit" class="tg-btn-dark" id="btnGuardarPromo" style="padding:9px 20px;">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function formatearFechaInput(d) {
        if (!d) return '';
        const pad = n => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function abrirCrearPromocion() {
        document.getElementById('panelTitle').textContent = 'Nueva Promoción';
        const form = document.getElementById('formPromocion');
        form.action = "{{ route('admin.promociones.store') }}";
        document.getElementById('formMethod').value = 'POST';

        document.getElementById('promoCodigo').value = '';
        document.getElementById('promoDescuento').value = '10';

        const now = new Date();
        const in30Days = new Date();
        in30Days.setDate(in30Days.getDate() + 30);

        document.getElementById('promoFechaInicio').value = formatearFechaInput(now);
        document.getElementById('promoFechaFin').value = formatearFechaInput(in30Days);
        document.getElementById('promoEstado').value = 'Activo';

        document.getElementById('btnGuardarPromo').innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Crear Promoción';

        document.querySelectorAll('.promo-table-row').forEach(tr => tr.style.backgroundColor = '');
    }

    function cargarPromocionEnPanel(p) {
        document.getElementById('panelTitle').textContent = 'Editar Cupón: ' + p.codigo;
        const form = document.getElementById('formPromocion');
        form.action = p.update_url;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('promoCodigo').value = p.codigo;
        document.getElementById('promoDescuento').value = p.descuento;
        document.getElementById('promoFechaInicio').value = p.fecha_inicio;
        document.getElementById('promoFechaFin').value = p.fecha_fin;
        document.getElementById('promoEstado').value = p.estado;

        document.getElementById('btnGuardarPromo').innerHTML = '<i class="fa-solid fa-check"></i> Guardar cambios';

        document.querySelectorAll('.promo-table-row').forEach(tr => tr.style.backgroundColor = '');
        const row = document.getElementById('row-promo-' + p.id);
        if (row) row.style.backgroundColor = '#fdf8f0';
    }

    // Al cargar la vista, inicializar el panel
    document.addEventListener('DOMContentLoaded', function() {
        const firstRow = document.querySelector('.promo-table-row');
        if (firstRow) {
            firstRow.click();
        } else {
            abrirCrearPromocion();
        }
    });
</script>
@endpush
