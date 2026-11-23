@extends('layouts.app')

@section('title', 'Contacto | Tenebrios Gold')

@section('content')
<div style="max-width:800px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:35px; border-top:4px solid #e4b353;">
        <h1 style="color:#151515; margin-top:0;"><i class="fas fa-envelope" style="color:#e4b353;"></i> Contáctanos</h1>
        <p style="color:#666; margin-bottom:25px;">¿Tienes dudas sobre la cría, compras por volumen o envíos especiales? Escríbenos.</p>

        <form action="{{ route('contacto.store') }}" method="POST">
            @csrf
            <div style="display:flex; gap:15px; margin-bottom:15px;">
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Correo Electrónico *</label>
                    <input type="email" name="correo" value="{{ old('correo') }}" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Asunto</label>
                <input type="text" name="asunto" value="{{ old('asunto') }}" placeholder="Ej: Cotización para granja o asesoría" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Mensaje *</label>
                <textarea name="mensaje" rows="5" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">{{ old('mensaje') }}</textarea>
            </div>

            <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:12px 25px; border-radius:6px; font-weight:700; cursor:pointer;">
                <i class="fas fa-paper-plane"></i> Enviar Mensaje
            </button>
        </form>
    </div>
</div>
@endsection
