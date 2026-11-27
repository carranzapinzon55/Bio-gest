@extends('layouts.app')

@section('title', 'Finalizar Compra | Tenebrios Gold')

@section('content')
<div style="max-width:1050px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-cash-register" style="color:#e4b353;"></i> Finalizar Compra
    </h1>

    @if(session('success'))
        <div style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:35px; align-items:start;">
        <!-- Formulario de Entrega y Pago -->
        <div style="background:#fff; border-radius:10px; padding:25px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Datos de Entrega y Pago</h3>
            
            <form action="{{ route('checkout.procesar') }}" method="POST" id="checkoutForm">
                @csrf

                <!-- Selector de Direcciones Guardadas -->
                @if($direcciones->isNotEmpty())
                    <div style="margin-bottom:18px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                        <label style="display:block; font-weight:700; font-size:0.88rem; color:#333; margin-bottom:6px;">
                            <i class="fas fa-map-marker-alt" style="color:#e4b353;"></i> Mis Direcciones Guardadas:
                        </label>
                        <select id="select_direccion_guardada" style="width:100%; padding:9px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                            <option value="">-- Ingresar nueva dirección o seleccionar guardada --</option>
                            @foreach($direcciones as $dir)
                                <option value="{{ $dir->id_direccion }}" 
                                        data-direccion="{{ $dir->direccion }}" 
                                        data-ciudad="{{ $dir->ciudad }}"
                                        {{ $dir->es_principal ? 'selected' : '' }}>
                                    {{ $dir->nombre_ubicacion ? $dir->nombre_ubicacion . ': ' : '' }}{{ $dir->direccion }} ({{ $dir->ciudad }}) {{ $dir->es_principal ? '★ Principal' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="id_direccion" id="input_id_direccion" value="{{ optional($direcciones->firstWhere('es_principal', true) ?? $direcciones->first())->id_direccion }}">
                    </div>
                @endif

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Dirección Completa de Entrega *</label>
                    <input type="text" name="direccion_entrega" id="direccion_entrega" required
                           value="{{ old('direccion_entrega', optional($direcciones->firstWhere('es_principal', true) ?? $direcciones->first())->direccion) }}" 
                           placeholder="Ej. Calle 123 # 45 - 67, Apto 301"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.92rem;">
                    <small style="color:#64748b; font-size:0.75rem;">Se guardará automáticamente en tus direcciones de cliente.</small>
                </div>

                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Ciudad *</label>
                        <input type="text" name="ciudad_entrega" id="ciudad_entrega" required
                               value="{{ old('ciudad_entrega', optional($direcciones->firstWhere('es_principal', true) ?? $direcciones->first())->ciudad ?? 'Bogotá') }}" 
                               placeholder="Ej. Bogotá, Medellín, Cali..."
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.92rem;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Teléfono de Contacto *</label>
                        <input type="text" name="telefono_contacto" id="telefono_contacto" required
                               value="{{ old('telefono_contacto', auth()->user()->telefono) }}" 
                               placeholder="Ej. 3101234567"
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.92rem;">
                    </div>
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; font-weight:700; margin-bottom:8px; color:#333;">Método de Pago *</label>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:10px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
                            <input type="radio" name="metodo_pago" value="Contraentrega" checked>
                            <span><strong>Pago Contraentrega</strong> (Pagas en efectivo al recibir tu pedido)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:10px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
                            <input type="radio" name="metodo_pago" value="Transferencia Bancaria" {{ old('metodo_pago') === 'Transferencia Bancaria' ? 'checked' : '' }}>
                            <span><strong>Transferencia Bancaria / Nequi / Daviplata</strong> (Confirmación vía WhatsApp)</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:10px; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer;">
                            <input type="radio" name="metodo_pago" value="PSE" {{ old('metodo_pago') === 'PSE' ? 'checked' : '' }}>
                            <span><strong>PSE / Débito en Línea</strong></span>
                        </label>
                    </div>
                </div>

                <input type="hidden" name="codigo_cupon" value="{{ $cuponAplicado['codigo'] ?? '' }}">

                <button type="submit" style="width:100%; background:#151515; color:#e4b353; border:none; padding:14px; border-radius:8px; font-size:1.05rem; font-weight:700; cursor:pointer; display:flex; justify-content:center; align-items:center; gap:8px;">
                    <i class="fas fa-lock"></i> Confirmar y Realizar Pedido
                </button>
            </form>
        </div>

        <!-- Resumen del Pedido y Cupón Promocional -->
        <div style="background:#fff; border-radius:10px; padding:25px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:4px solid #e4b353;">
            <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Resumen del Pedido</h3>

            <div style="margin-bottom:15px;">
                @foreach($carrito as $item)
                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.9rem;">
                        <span>{{ $item['nombre_producto'] }} (x{{ $item['cantidad'] }})</span>
                        <span style="font-weight:700;">${{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Bloque de Cupones de Promoción -->
            <div style="background:#fdf8f0; border:1px solid #fde68a; border-radius:8px; padding:12px; margin-bottom:15px;">
                <form action="{{ route('checkout.index') }}" method="GET" style="display:flex; gap:8px;">
                    <input type="text" name="cupon" value="{{ $cuponAplicado['codigo'] ?? '' }}" placeholder="Código de cupón (Ej: TENEBRIO10)" style="flex:1; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; text-transform:uppercase;">
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
                        <a href="{{ route('checkout.index', ['quitar_cupon' => 1]) }}" style="color:#b91c1c; font-weight:700; text-decoration:underline;">Quitar</a>
                    </div>
                @elseif(isset($promocionesActivas) && $promocionesActivas->isNotEmpty())
                    <div style="margin-top:10px; font-size:0.78rem; color:#92400e;">
                        <strong>Promociones activas disponibles:</strong>
                        <div style="display:flex; flex-wrap:wrap; gap:5px; margin-top:5px;">
                            @foreach($promocionesActivas as $pa)
                                <a href="{{ route('checkout.index', ['cupon' => $pa->codigo_cupon]) }}" style="display:inline-block; background:#111111; color:#e4b353; padding:3px 8px; border-radius:4px; font-weight:700; text-decoration:none; font-size:0.75rem;" title="Aplicar {{ $pa->nombre_comercial }}">
                                    {{ $pa->nombre_comercial }} ({{ number_format($pa->descuento_porcentaje, 0) }}% OFF)
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div style="border-top:1px solid #eee; padding-top:12px; margin-bottom:8px; display:flex; justify-content:space-between; color:#666;">
                <span>Subtotal:</span>
                <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if(!empty($descuentoMonto) && $descuentoMonto > 0)
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#15803d; font-weight:700;">
                    <span>Descuento ({{ $cuponAplicado['nombre'] ?? $cuponAplicado['codigo'] }}):</span>
                    <span>-${{ number_format($descuentoMonto, 0, ',', '.') }}</span>
                </div>
            @endif

            <div style="display:flex; justify-content:space-between; margin-bottom:15px; color:#666;">
                <span>Costo de Envío:</span>
                <span>
                    @if($costoEnvio == 0)
                        <strong style="color:#15803d;">GRATIS</strong>
                    @else
                        ${{ number_format($costoEnvio, 0, ',', '.') }}
                    @endif
                </span>
            </div>

            <div style="border-top:2px solid #eee; padding-top:15px; display:flex; justify-content:space-between; font-size:1.3rem; font-weight:800; color:#151515;">
                <span>Total:</span>
                <span>${{ number_format($total, 0, ',', '.') }} COP</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectDir = document.getElementById('select_direccion_guardada');
    const inputDir = document.getElementById('direccion_entrega');
    const inputCiudad = document.getElementById('ciudad_entrega');
    const inputIdDir = document.getElementById('input_id_direccion');

    if (selectDir) {
        selectDir.addEventListener('change', function () {
            const opt = selectDir.options[selectDir.selectedIndex];
            if (opt && opt.value) {
                if (inputDir) inputDir.value = opt.getAttribute('data-direccion') || '';
                if (inputCiudad) inputCiudad.value = opt.getAttribute('data-ciudad') || '';
                if (inputIdDir) inputIdDir.value = opt.value;
            } else {
                if (inputIdDir) inputIdDir.value = '';
            }
        });
    }
});
</script>
@endpush
