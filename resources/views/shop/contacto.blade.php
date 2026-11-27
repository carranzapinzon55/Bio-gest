@extends('layouts.app')

@section('title', 'Contacto y Soporte | Tenebrios Gold')

@section('content')
<div style="max-width:900px; margin:40px auto; padding:0 20px;">
    <!-- Encabezado con Enlace Directo a WhatsApp -->
    <div style="background:#ffffff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:30px; border-top:4px solid #e4b353; margin-bottom:25px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 style="color:#151515; margin:0 0 8px 0; font-size:1.8rem;">
                    <i class="fas fa-headset" style="color:#e4b353;"></i> Centro de Contacto y Soporte
                </h1>
                <p style="color:#666; margin:0; font-size:0.95rem;">
                    ¿Tienes dudas sobre la cría, compras por volumen o envíos especiales? Escríbenos o habla en tiempo real.
                </p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <!-- Botón Oficial WhatsApp Soporte -->
                <a href="{{ $whatsappUrl ?? 'https://wa.me/573100000000' }}" target="_blank" rel="noopener noreferrer"
                   style="display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#ffffff; padding:11px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.92rem; box-shadow:0 2px 8px rgba(37,211,102,0.3);">
                    <i class="fab fa-whatsapp" style="font-size:1.2rem;"></i> WhatsApp Soporte
                </a>
                <a href="{{ route('cliente.chat.index') }}"
                   style="display:inline-flex; align-items:center; gap:8px; background:#111111; color:#e4b353; padding:11px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.92rem; border:1px solid #e4b353;">
                    <i class="fas fa-comments"></i> Chat en Vivo
                </a>
            </div>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if(session('success'))
        <div style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:20px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario de Contacto -->
    <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:32px; margin-bottom:30px;">
        <h2 style="color:#151515; margin-top:0; font-size:1.3rem; border-bottom:1px solid #eee; padding-bottom:10px;">
            <i class="fas fa-paper-plane" style="color:#e4b353;"></i> Enviar Mensaje al Administrador
        </h2>

        @auth
            <form action="{{ route('contacto.store') }}" method="POST">
                @csrf
                <div style="display:flex; gap:15px; margin-bottom:15px; flex-wrap:wrap;">
                    <div style="flex:1; min-width:240px;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333; font-size:0.88rem;">Tu Nombre (Cuenta verificada)</label>
                        <input type="text" value="{{ auth()->user()->nombre_completo }}" readonly style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; color:#475569; font-weight:600;">
                    </div>
                    <div style="flex:1; min-width:240px;">
                        <label style="display:block; font-weight:700; margin-bottom:6px; color:#333; font-size:0.88rem;">Tu Correo Electrónico</label>
                        <input type="email" value="{{ auth()->user()->correo }}" readonly style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px; background:#f8fafc; color:#475569; font-weight:600;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333; font-size:0.88rem;">Asunto *</label>
                    <input type="text" name="asunto" value="{{ old('asunto') }}" required minlength="3" maxlength="150" placeholder="Ej: Cotización para granja, compras mayoristas o asesoría técnica" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.92rem;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:700; margin-bottom:6px; color:#333; font-size:0.88rem;">Mensaje Detallado *</label>
                    <textarea name="mensaje" rows="4" required minlength="5" maxlength="2000" placeholder="Describe tu consulta para que el administrador pueda darte una respuesta precisa..." style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:0.92rem;">{{ old('mensaje') }}</textarea>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <button type="submit" style="background:#151515; color:#e4b353; border:none; padding:12px 26px; border-radius:6px; font-weight:700; cursor:pointer; font-size:0.95rem;">
                        <i class="fas fa-paper-plane"></i> Enviar Mensaje
                    </button>
                    <span style="font-size:0.85rem; color:#64748b;">
                        <i class="fas fa-info-circle"></i> Las respuestas del administrador aparecerán automáticamente en tu historial abajo.
                    </span>
                </div>
            </form>
        @else
            <div style="background:#fdf8f0; border:1px solid #fde68a; border-left:4px solid #e4b353; padding:24px; border-radius:10px; text-align:center;">
                <i class="fas fa-user-lock" style="font-size:2.4rem; color:#e4b353; margin-bottom:12px;"></i>
                <h3 style="color:#151515; margin:0 0 8px 0;">Inicia sesión para contactar al administrador</h3>
                <p style="color:#555; margin:0 0 20px 0; font-size:0.95rem;">
                    Para garantizar una atención personalizada y poder hacer seguimiento a las <strong>respuestas del administrador</strong>, debes iniciar sesión con tu cuenta de cliente.
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

    <!-- Historial de Consultas y Respuestas del Administrador en Tiempo Real -->
    @auth
        <div style="background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); padding:30px; border-top:4px solid #111111;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="color:#151515; margin:0; font-size:1.25rem;">
                    <i class="fas fa-history" style="color:#e4b353;"></i> Mis Consultas Enviadas y Respuestas del Administrador
                </h2>
                <span style="font-size:0.8rem; background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:999px; font-weight:700;">
                    {{ $misMensajes->count() }} consulta(s)
                </span>
            </div>

            @if($misMensajes->isEmpty())
                <div style="text-align:center; padding:35px 20px; color:#94a3b8; border:1px dashed #cbd5e1; border-radius:8px;">
                    <i class="far fa-envelope-open" style="font-size:2.5rem; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
                    <p style="margin:0; font-weight:600; color:#64748b;">No tienes consultas enviadas aún.</p>
                    <small>Cuando envíes un mensaje al administrador, verás aquí su estado y respuesta en tiempo real.</small>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:16px;">
                    @foreach($misMensajes as $consulta)
                        <div style="border:1px solid #e2e8f0; border-radius:8px; padding:18px; background:#fafafa;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
                                <div style="font-weight:700; color:#111111; font-size:1rem;">
                                    <i class="fas fa-comment-dots" style="color:#e4b353; margin-right:6px;"></i>
                                    {{ $consulta->asunto }}
                                </div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    @if($consulta->estado === 'Atendido' || !empty($consulta->respuesta_admin))
                                        <span style="background:#dcfce7; color:#166534; font-size:0.75rem; font-weight:700; padding:3px 10px; border-radius:999px;">
                                            <i class="fas fa-check-double"></i> Atendido
                                        </span>
                                    @else
                                        <span style="background:#fef3c7; color:#92400e; font-size:0.75rem; font-weight:700; padding:3px 10px; border-radius:999px;">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @endif
                                    <span style="font-size:0.75rem; color:#94a3b8;">
                                        {{ $consulta->fecha_envio ? \Carbon\Carbon::parse($consulta->fecha_envio)->format('d/m/Y h:i a') : '' }}
                                    </span>
                                </div>
                            </div>

                            <p style="color:#475569; font-size:0.9rem; margin:0 0 12px 0; background:#ffffff; padding:10px 14px; border-radius:6px; border:1px solid #f1f5f9;">
                                {{ $consulta->mensaje }}
                            </p>

                            @if(!empty($consulta->respuesta_admin))
                                <div style="background:#fdf8f0; border-left:4px solid #e4b353; padding:12px 16px; border-radius:4px; margin-top:10px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <strong style="color:#111111; font-size:0.85rem;">
                                            <i class="fas fa-shield-alt" style="color:#e4b353;"></i> Respuesta del Administrador BIO-GEST:
                                        </strong>
                                        @if($consulta->fecha_atencion)
                                            <small style="color:#92400e; font-size:0.75rem;">
                                                {{ \Carbon\Carbon::parse($consulta->fecha_atencion)->format('d/m/Y h:i a') }}
                                            </small>
                                        @endif
                                    </div>
                                    <p style="margin:0; color:#333333; font-size:0.9rem; line-height:1.45;">
                                        {{ $consulta->respuesta_admin }}
                                    </p>
                                </div>
                            @else
                                <div style="font-size:0.8rem; color:#64748b; font-style:italic;">
                                    <i class="fas fa-hourglass-half"></i> El administrador está revisando tu solicitud. Te responderá pronto.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endauth
</div>
@endsection
