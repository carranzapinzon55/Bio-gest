@extends('layouts.app')

@section('title', 'Mis Direcciones | Tenebrios Gold')

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="color:#151515; margin:0;"><i class="fas fa-map-marker-alt" style="color:#e4b353;"></i> Mis Direcciones</h1>
        <a href="{{ route('cliente.cuenta') }}" style="color:#151515; text-decoration:none; font-weight:600;">Volver a mi cuenta</a>
    </div>

    <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:30px;">
        <!-- Direcciones Guardadas -->
        <div>
            @forelse($direcciones as $dir)
                <div style="background:#fff; border-radius:8px; padding:20px; box-shadow:0 3px 10px rgba(0,0,0,0.06); margin-bottom:15px; border-left:4px solid {{ $dir->es_principal ? '#e4b353' : '#ccc' }};">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <h4 style="margin:0; color:#151515;">{{ $dir->nombre_ubicacion }}</h4>
                        @if($dir->es_principal)
                            <span style="font-size:0.75rem; background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-weight:700;">Principal</span>
                        @endif
                    </div>
                    <p style="color:#555; margin:8px 0;">{{ $dir->direccion }}</p>
                    <div style="color:#888; font-size:0.85rem;">{{ $dir->ciudad }}, {{ $dir->departamento }}</div>
                    
                    <form action="{{ route('cliente.direcciones.destroy', $dir->id_direccion) }}" method="POST" style="margin-top:12px; text-align:right;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:0.85rem; font-weight:600;">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </form>
                </div>
            @empty
                <p style="color:#777;">No tienes direcciones registradas.</p>
            @endforelse
        </div>

        <!-- Formulario Agregar Dirección -->
        <div style="background:#fff; border-radius:8px; padding:25px; box-shadow:0 3px 10px rgba(0,0,0,0.06);">
            <h3 style="color:#151515; margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">Agregar Dirección</h3>
            
            <form action="{{ route('cliente.direcciones.store') }}" method="POST">
                @csrf
                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; font-size:0.9rem; color:#333;">Nombre Ubicación (Ej: Casa, Finca, Trabajo) *</label>
                    <input type="text" name="nombre_ubicacion" data-only-letters="true" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo letras (Ej: Casa, Finca, Oficina)" value="{{ old('nombre_ubicacion', 'Casa') }}" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:700; margin-bottom:4px; font-size:0.9rem; color:#333;">Dirección Completa *</label>
                    <input type="text" name="direccion" minlength="5" maxlength="255" placeholder="Ej: Calle 26 # 68-90" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                </div>

                <div style="display:flex; gap:10px; margin-bottom:12px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:4px; font-size:0.9rem; color:#333;">Ciudad *</label>
                        <input type="text" name="ciudad" data-only-letters="true" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s.'-]+$" title="Solo letras" value="Bogotá" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:4px; font-size:0.9rem; color:#333;">Departamento *</label>
                        <input type="text" name="departamento" data-only-letters="true" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s.'-]+$" title="Solo letras" value="Cundinamarca" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;">
                    </div>
                </div>

                <button type="submit" style="width:100%; background:#151515; color:#e4b353; border:none; padding:10px; border-radius:6px; font-weight:700; cursor:pointer;">
                    Guardar Dirección
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
