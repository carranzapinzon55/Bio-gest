@extends('layouts.app')

@section('title', 'Contacto | Tenebrios Gold')

@section('content')
<div style="max-width:800px; margin:40px auto; padding:0 20px;">
    <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:35px; border-top:4px solid #e4b353;">
        <h1 style="color:#151515; margin-top:0;"><i class="fas fa-envelope" style="color:#e4b353;"></i> Contáctanos</h1>
        <p style="color:#666; margin-bottom:25px;">¿Tienes dudas sobre la cría, compras por volumen o envíos especiales? Escríbenos.</p>

        @auth
            @if(isset($errors) && $errors->any())
                <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:18px;">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('contacto.store') }}" method="POST">
                @csrf
                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Tu Nombre (Cuenta verificada)</label>
                        <input type="text" value="{{ auth()->user()->nombre_completo }}" readonly style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; color:#475569; font-weight:600;">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Tu Correo Electrónico</label>
                        <input type="email" value="{{ auth()->user()->correo }}" readonly style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; color:#475569; font-weight:600;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Asunto *</label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" required minlength="3" maxlength="150" placeholder="Ej: Cotización para granja o asesoría" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333;">Mensaje *</label>
                    <textarea name="mensaje" rows="5" required minlength="5" maxlength="2000" placeholder="Escribe tu consulta para el administrador..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">{{ old('mensaje') }}</textarea>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:12px 25px; border-radius:6px; font-weight:700; cursor:pointer;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje al Administrador
                    </button>
                    <a href="{{ route('cliente.chat.index') }}" style="background:#e4b353; color:#151515; padding:12px 22px; border-radius:6px; font-weight:700; text-decoration:none;">
                        <i class="fas fa-comments"></i> Ir al Chat en Vivo
                    </a>
                </div>
            </form>
        @else
            <div style="background:#fdf8f0; border:1px solid #fde68a; border-left:4px solid #e4b353; padding:24px; border-radius:10px; text-align:center;">
                <i class="fas fa-user-lock" style="font-size:2.4rem; color:#e4b353; margin-bottom:12px;"></i>
                <h3 style="color:#151515; margin:0 0 8px 0;">Inicia sesión para enviar mensajes al administrador</h3>
                <p style="color:#555; margin:0 0 20px 0; font-size:0.95rem;">
                    Para garantizar una atención personalizada y poder responderte por el <strong>Chat en Vivo</strong>, debes estar registrado e iniciar sesión en tu cuenta de BIO-GEST.
                </p>
                <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
                    <a href="{{ route('login') }}" style="background:#151515; color:#e4b353; padding:11px 24px; border-radius:6px; text-decoration:none; font-weight:700;">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="{{ route('registro') }}" style="background:#e4b353; color:#151515; padding:11px 24px; border-radius:6px; text-decoration:none; font-weight:700;">
                        <i class="fas fa-user-plus"></i> Crear Cuenta 
                    </a>
                </div>
            </div>
        @endauth
    </div>
</div>
@endsection
