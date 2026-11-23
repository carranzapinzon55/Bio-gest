@extends('layouts.app')

@section('title', 'Finalizar Compra | Tenebrios Gold')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/checkout_style.css') }}">
@endpush

@section('content')
<div style="max-width:1000px; margin:40px auto; padding:0 20px;">
    <h1 style="color:#151515; border-bottom:3px solid #e4b353; padding-bottom:12px; margin-bottom:25px;">
        <i class="fas fa-truck-ramp-box" style="color:#e4b353;"></i> Finalizar Compra
    </h1>

    <div style="display:grid; grid-template-columns:1.5fr 1fr; gap:30px; align-items:start;">
        <!-- Formulario de Entrega y Pago -->
        <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 3px 12px rgba(0,0,0,0.06);">
            <form action="{{ route('checkout.procesar') }}" method="POST">
                @csrf
                <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">1. Datos de Entrega</h3>

                <!-- Selección de Dirección Guardada -->
                @if($direcciones->isNotEmpty())
                    <div style="margin-bottom:18px;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Usar una dirección guardada:</label>
                        <select name="id_direccion" id="id_direccion" onchange="seleccionarDireccion(this)" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                            <option value="">-- Ingresar nueva dirección abajo --</option>
                            @foreach($direcciones as $dir)
                                <option value="{{ $dir->id_direccion }}" 
                                    data-dir="{{ $dir->direccion }}" 
                                    data-ciudad="{{ $dir->ciudad }}" 
                                    data-depto="{{ $dir->departamento }}"
                                    {{ $dir->es_principal ? 'selected' : '' }}>
                                    {{ $dir->nombre_ubicacion }}: {{ $dir->direccion }}, {{ $dir->ciudad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Dirección de Entrega *</label>
                    <input type="text" name="direccion_entrega" id="direccion_entrega" value="{{ old('direccion_entrega', optional($direcciones->first())->direccion) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Ciudad *</label>
                        <input type="text" name="ciudad_entrega" id="ciudad_entrega" value="{{ old('ciudad_entrega', optional($direcciones->first())->ciudad ?? 'Bogotá') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Teléfono de Contacto *</label>
                        <input type="tel" name="telefono_contacto" value="{{ old('telefono_contacto', auth()->user()->telefono) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                </div>

                <h3 style="color:#151515; margin-top:25px; border-bottom:1px solid #eee; padding-bottom:10px;">2. Método de Pago</h3>
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:25px;">
                    <label style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid #e5e7eb; border-radius:6px; cursor:pointer;">
                        <input type="radio" name="metodo_pago" value="Contraentrega" checked>
                        <span><strong>Pago Contraentrega</strong> (Pagas al recibir en efectivo o transferencia)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid #e5e7eb; border-radius:6px; cursor:pointer;">
                        <input type="radio" name="metodo_pago" value="Transferencia">
                        <span><strong>Transferencia Bancaria</strong> (Bancolombia, Nequi, Daviplata)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid #e5e7eb; border-radius:6px; cursor:pointer;">
                        <input type="radio" name="metodo_pago" value="PSE">
                        <span><strong>PSE / Pasarela de Pagos</strong> en línea</span>
                    </label>
                </div>

                <button type="submit" style="width:100%; background:#151515; color:#e4b353; border:none; padding:15px; border-radius:8px; font-weight:700; font-size:1.1rem; cursor:pointer;">
                    <i class="fas fa-check-circle"></i> Confirmar Pedido
                </button>
            </form>
        </div>

        <!-- Resumen del Pedido -->
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

            <div style="border-top:1px solid #eee; padding-top:12px; margin-bottom:8px; display:flex; justify-content:space-between; color:#666;">
                <span>Subtotal:</span>
                <span>${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

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

<script>
function seleccionarDireccion(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.value) {
        document.getElementById('direccion_entrega').value = opt.getAttribute('data-dir');
        document.getElementById('ciudad_entrega').value = opt.getAttribute('data-ciudad');
    }
}
</script>
@endsection
