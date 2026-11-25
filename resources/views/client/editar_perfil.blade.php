@extends('layouts.app')

@section('title', 'Editar Perfil | Tenebrios Gold')

@section('content')
<div style="max-width:650px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:10px; padding:35px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:4px solid #e4b353;">
        <h2 style="color:#151515; margin-top:0;"><i class="fas fa-user-edit" style="color:#e4b353;"></i> Editar Perfil</h2>

        @if($errors->any())
            <div style="background:#fee2e2; color:#b91c1c; padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:0.88rem;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('cliente.perfil.actualizar') }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Nombre *</label>
                    <input type="text" name="nombre" data-only-letters="true" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)" value="{{ old('nombre', $usuario->nombre) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Apellido *</label>
                    <input type="text" name="apellido" data-only-letters="true" minlength="2" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)" value="{{ old('apellido', $usuario->apellido) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Correo Electrónico *</label>
                <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="150" placeholder="tucorreo@ejemplo.com" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Teléfono</label>
                <input type="tel" name="telefono" data-only-digits="10" inputmode="numeric" maxlength="10" pattern="^[0-9]{7,10}$" title="Solo números (7 a 10 dígitos)" value="{{ old('telefono', $usuario->telefono) }}" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Perfil de Uso *</label>
                <select name="perfil_uso" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    <option value="Alimentación continua" {{ $usuario->perfil_uso === 'Alimentación continua' ? 'selected' : '' }}>Alimentación continua</option>
                    <option value="Inicio de criadero" {{ $usuario->perfil_uso === 'Inicio de criadero' ? 'selected' : '' }}>Inicio de criadero</option>
                    <option value="Proyecto comercial / Gran escala" {{ $usuario->perfil_uso === 'Proyecto comercial / Gran escala' ? 'selected' : '' }}>Proyecto comercial / Gran escala</option>
                    <option value="Mascota única" {{ $usuario->perfil_uso === 'Mascota única' ? 'selected' : '' }}>Mascota única</option>
                </select>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:22px;">
                <h4 style="margin:0 0 8px 0; color:#151515; font-size:0.95rem;">
                    <i class="fas fa-lock" style="color:#e4b353;"></i> Cambiar Contraseña (Opcional)
                </h4>
                <p style="margin:0 0 12px 0; color:#64748b; font-size:0.8rem;">
                    Déjalo en blanco si no deseas cambiarla. Si decides actualizarla, debe tener mínimo 8 caracteres, al menos una letra y un número.
                </p>
                <div style="display:flex; gap:15px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:200px;">
                        <label style="display:block; font-weight:700; margin-bottom:5px; color:#333; font-size:0.85rem;">Nueva Contraseña</label>
                        <input type="password" name="contrasena" minlength="8" pattern="^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).{8,}$" title="Mínimo 8 caracteres, al menos una letra y un número" placeholder="Mín. 8 caracteres (letras y números)" autocomplete="new-password" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                    <div style="flex:1; min-width:200px;">
                        <label style="display:block; font-weight:700; margin-bottom:5px; color:#333; font-size:0.85rem;">Confirmar Nueva Contraseña</label>
                        <input type="password" name="contrasena_confirmation" minlength="8" placeholder="Repite la nueva contraseña" autocomplete="new-password" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:15px;">
                <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:12px 25px; border-radius:6px; font-weight:700; cursor:pointer;">
                    Guardar Cambios
                </button>
                <a href="{{ route('cliente.cuenta') }}" style="background:#f3f4f6; color:#374151; padding:12px 20px; border-radius:6px; text-decoration:none; font-weight:600;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
