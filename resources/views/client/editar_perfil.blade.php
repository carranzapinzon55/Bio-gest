@extends('layouts.app')

@section('title', 'Editar Perfil | Tenebrios Gold')

@section('content')
<div style="max-width:650px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:10px; padding:35px; box-shadow:0 3px 12px rgba(0,0,0,0.06); border-top:4px solid #e4b353;">
        <h2 style="color:#151515; margin-top:0;"><i class="fas fa-user-edit" style="color:#e4b353;"></i> Editar Perfil</h2>
        
        <form action="{{ route('cliente.perfil.actualizar') }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Apellido *</label>
                    <input type="text" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Teléfono</label>
                <input type="tel" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom:25px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Perfil de Uso *</label>
                <select name="perfil_uso" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    <option value="Alimentación continua" {{ $usuario->perfil_uso === 'Alimentación continua' ? 'selected' : '' }}>Alimentación continua</option>
                    <option value="Inicio de criadero" {{ $usuario->perfil_uso === 'Inicio de criadero' ? 'selected' : '' }}>Inicio de criadero</option>
                    <option value="Proyecto comercial / Gran escala" {{ $usuario->perfil_uso === 'Proyecto comercial / Gran escala' ? 'selected' : '' }}>Proyecto comercial / Gran escala</option>
                    <option value="Mascota única" {{ $usuario->perfil_uso === 'Mascota única' ? 'selected' : '' }}>Mascota única</option>
                </select>
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
