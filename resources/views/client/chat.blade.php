@extends('layouts.app')

@section('title', 'Chat en Vivo con Soporte | Tenebrios Gold')

@section('content')
<div style="max-width:850px; margin:35px auto; padding:0 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
        <div>
            <h1 style="color:#151515; margin:0; font-size:1.6rem;">
                <i class="fas fa-comments" style="color:#e4b353;"></i> Chat en Vivo con Soporte BIO-GEST
            </h1>
            <p style="color:#666; margin:4px 0 0 0; font-size:0.9rem;">
                Conversa directamente con el administrador de Tenebrios Gold sobre tus pedidos, envíos o pie de cría.
            </p>
        </div>
        <a href="{{ route('cliente.cuenta') }}" style="background:#f3f4f6; color:#151515; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:600; font-size:0.88rem;">
            <i class="fas fa-arrow-left"></i> Volver a Mi Cuenta
        </a>
    </div>

    <div style="background:#ffffff; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08); overflow:hidden; border:1px solid #e5e7eb; display:flex; flex-direction:column; height:520px;">
        <!-- Cabecera del Chat -->
        <div style="background:#111111; color:#ffffff; padding:14px 20px; border-bottom:2px solid #e4b353; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; border-radius:50%; background:#e4b353; color:#111111; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.1rem;">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <div style="font-weight:800; font-size:0.98rem; color:#e4b353;">Soporte Oficial Tenebrios Gold</div>
                    <div style="font-size:0.78rem; color:#cbd5e1;">Atención directa al cliente · {{ $usuario->nombre_completo }}</div>
                </div>
            </div>
            @if(!empty($adminEnLinea))
                <span id="adminOnlineBadge" style="background:#065f46; color:#d1fae5; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:999px;">
                    <i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#34d399;"></i> En línea
                </span>
            @else
                <span id="adminOnlineBadge" style="background:#374151; color:#e5e7eb; font-size:0.75rem; font-weight:700; padding:4px 10px; border-radius:999px;">
                    <i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#9ca3af;"></i> Fuera de línea
                </span>
            @endif
        </div>

        <!-- Historial de Mensajes -->
        <div id="clientChatBox" style="flex:1; overflow-y:auto; padding:20px; background:#f8fafc; display:flex; flex-direction:column; gap:12px;">
            @forelse($mensajes as $msg)
                @if($msg->es_admin)
                    <!-- Respuesta del Administrador -->
                    <div style="align-self:flex-start; max-width:78%; background:#111111; color:#ffffff; padding:12px 16px; border-radius:12px; border-bottom-left-radius:2px; border:1px solid #e4b353;">
                        <div style="font-size:0.74rem; font-weight:700; color:#e4b353; margin-bottom:4px;">
                            <i class="fas fa-shield-alt"></i> {{ optional($msg->admin)->nombre ?? 'Administrador BIO-GEST' }}
                        </div>
                        <div style="font-size:0.92rem; line-height:1.45;">{{ $msg->mensaje }}</div>
                        <div style="font-size:0.7rem; color:#9ca3af; margin-top:5px; text-align:right;">
                            {{ $msg->fecha_envio ? $msg->fecha_envio->format('d/m/Y h:i a') : '' }}
                        </div>
                    </div>
                @else
                    <!-- Mensaje del Cliente -->
                    <div style="align-self:flex-end; max-width:78%; background:#e4b353; color:#111111; padding:12px 16px; border-radius:12px; border-bottom-right-radius:2px; font-weight:500;">
                        <div style="font-size:0.74rem; font-weight:800; color:#111111; margin-bottom:4px;">
                            Tú ({{ $usuario->nombre }})
                        </div>
                        <div style="font-size:0.92rem; line-height:1.45;">{{ $msg->mensaje }}</div>
                        <div style="font-size:0.7rem; color:#333333; margin-top:5px; text-align:right;">
                            {{ $msg->fecha_envio ? $msg->fecha_envio->format('d/m/Y h:i a') : '' }}
                        </div>
                    </div>
                @endif
            @empty
                <div id="emptyChatNotice" style="margin:auto; text-align:center; color:#64748b; padding:30px;">
                    <i class="far fa-comments" style="font-size:2.8rem; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
                    <strong style="color:#1e293b; display:block; margin-bottom:4px;">¡Hola, {{ $usuario->nombre }}!</strong>
                    <span>Escribe tu mensaje abajo para iniciar una conversación en vivo con el administrador.</span>
                </div>
            @endforelse
        </div>

        <!-- Formulario para Enviar Mensaje -->
        <form id="clientChatForm" action="{{ route('cliente.chat.enviar') }}" method="POST" style="padding:14px 18px; background:#ffffff; border-top:1px solid #e2e8f0; display:flex; gap:10px; align-items:center;">
            @csrf
            <input type="text" name="mensaje" id="clientChatInput" required maxlength="2000" placeholder="Escribe tu mensaje para el administrador..." autocomplete="off"
                   style="flex:1; padding:12px 15px; border:1px solid #cbd5e1; border-radius:8px; font-size:0.92rem; outline:none;">
            <button type="submit" style="background:#111111; color:#e4b353; border:1px solid #e4b353; padding:12px 22px; border-radius:8px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-paper-plane"></i> Enviar
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('clientChatBox');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Actualizar automáticamente cada 5 segundos por si el administrador responde
    setInterval(async function () {
        try {
            const res = await fetch("{{ route('cliente.chat.index') }}", {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.ok || !Array.isArray(data.mensajes)) return;

            const badge = document.getElementById('adminOnlineBadge');
            if (badge) {
                if (data.admin_online) {
                    badge.style.background = '#065f46';
                    badge.style.color = '#d1fae5';
                    badge.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#34d399;"></i> En línea';
                } else {
                    badge.style.background = '#374151';
                    badge.style.color = '#e5e7eb';
                    badge.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#9ca3af;"></i> Fuera de línea';
                }
            }

            if (data.mensajes.length === 0) return;

            chatBox.innerHTML = data.mensajes.map(m => {
                if (m.es_admin) {
                    return `
                        <div style="align-self:flex-start; max-width:78%; background:#111111; color:#ffffff; padding:12px 16px; border-radius:12px; border-bottom-left-radius:2px; border:1px solid #e4b353;">
                            <div style="font-size:0.74rem; font-weight:700; color:#e4b353; margin-bottom:4px;">
                                <i class="fas fa-shield-alt"></i> ${m.autor}
                            </div>
                            <div style="font-size:0.92rem; line-height:1.45;">${m.mensaje}</div>
                            <div style="font-size:0.7rem; color:#9ca3af; margin-top:5px; text-align:right;">${m.fecha_envio}</div>
                        </div>
                    `;
                }
                return `
                    <div style="align-self:flex-end; max-width:78%; background:#e4b353; color:#111111; padding:12px 16px; border-radius:12px; border-bottom-right-radius:2px; font-weight:500;">
                        <div style="font-size:0.74rem; font-weight:800; color:#111111; margin-bottom:4px;">Tú (${m.autor})</div>
                        <div style="font-size:0.92rem; line-height:1.45;">${m.mensaje}</div>
                        <div style="font-size:0.7rem; color:#333333; margin-top:5px; text-align:right;">${m.fecha_envio}</div>
                    </div>
                `;
            }).join('');
            chatBox.scrollTop = chatBox.scrollHeight;
        } catch (e) {}
    }, 5000);
});
</script>
@endpush
