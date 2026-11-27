@extends('layouts.app')

@section('title', 'Chat en Vivo con Soporte | Tenebrios Gold')

@section('content')
@php
    $whatsappRaw = \App\Models\Configuracion::get('whatsapp_soporte', '+57 310 000 0000');
    $whatsappDigits = preg_replace('/\D+/', '', $whatsappRaw);
    if (!str_starts_with($whatsappDigits, '57') && strlen($whatsappDigits) === 10) {
        $whatsappDigits = '57' . $whatsappDigits;
    }
    $whatsappUrl = 'https://wa.me/' . ($whatsappDigits ?: '573100000000');
@endphp

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
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <!-- Botón WhatsApp Soporte -->
            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
               style="background:#25D366; color:#ffffff; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:700; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px; box-shadow:0 2px 6px rgba(37,211,102,0.3);">
                <i class="fab fa-whatsapp" style="font-size:1rem;"></i> WhatsApp Soporte
            </a>
            <a href="{{ route('cliente.cuenta') }}" style="background:#f3f4f6; color:#151515; padding:8px 14px; border-radius:6px; text-decoration:none; font-weight:600; font-size:0.85rem;">
                <i class="fas fa-arrow-left"></i> Mi Cuenta
            </a>
        </div>
    </div>

    <!-- Indicador visual de mensaje nuevo -->
    <div id="newMsgBanner" style="display:none; background:#dcfce7; color:#166534; border:1px solid #86efac; border-radius:8px; padding:10px 16px; margin-bottom:12px; font-weight:700; font-size:0.88rem; justify-content:space-between; align-items:center;">
        <span><i class="fas fa-bell fa-shake"></i> ¡Nuevo mensaje recibido del Administrador!</span>
        <button type="button" onclick="document.getElementById('newMsgBanner').style.display='none'" style="background:none; border:none; color:#166534; cursor:pointer; font-weight:700;">×</button>
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
                    <div class="chat-msg-item" data-id="{{ $msg->id_mensaje }}" style="align-self:flex-start; max-width:78%; background:#111111; color:#ffffff; padding:12px 16px; border-radius:12px; border-bottom-left-radius:2px; border:1px solid #e4b353; transition:background-color 0.4s ease;">
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
                    <div class="chat-msg-item" data-id="{{ $msg->id_mensaje }}" style="align-self:flex-end; max-width:78%; background:#e4b353; color:#111111; padding:12px 16px; border-radius:12px; border-bottom-right-radius:2px; font-weight:500;">
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
            <button type="submit" id="btnEnviarMensaje" style="background:#111111; color:#e4b353; border:1px solid #e4b353; padding:12px 22px; border-radius:8px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-paper-plane"></i> Enviar
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Sintetizador Web Audio API para notificaciones suaves sin dependencias de archivos de audio externos.
 */
function playNotificationSound() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        // Tono suave melodioso en 2 notas (587.33 Hz - Re5 y 880 Hz - La5)
        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.12);

        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.35);
    } catch (e) {
        console.warn('Web Audio API no inicializada o bloqueada por interacción del usuario', e);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const chatBox = document.getElementById('clientChatBox');
    const form = document.getElementById('clientChatForm');
    const input = document.getElementById('clientChatInput');
    const badgeOnline = document.getElementById('adminOnlineBadge');
    const banner = document.getElementById('newMsgBanner');

    // Registrar IDs conocidos inicialmente
    let knownMessageIds = new Set();
    document.querySelectorAll('.chat-msg-item').forEach(el => {
        const id = parseInt(el.getAttribute('data-id'), 10);
        if (id) knownMessageIds.add(id);
    });

    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Envío asíncrono con AJAX para experiencia fluida
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;

            const submitBtn = document.getElementById('btnEnviarMensaje');
            submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ mensaje: text })
            })
            .then(res => res.json())
            .then(data => {
                input.value = '';
                submitBtn.disabled = false;
                if (data.ok && data.mensaje) {
                    knownMessageIds.add(data.mensaje.id_mensaje);
                    appendMessage(data.mensaje, false);
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                // Si falla AJAX, enviar tradicionalmente
                form.submit();
            });
        });
    }

    function appendMessage(msg, isNewIncoming = false) {
        const emptyNotice = document.getElementById('emptyChatNotice');
        if (emptyNotice) emptyNotice.remove();

        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-msg-item';
        msgDiv.setAttribute('data-id', msg.id_mensaje);

        if (msg.es_admin) {
            msgDiv.style.cssText = 'align-self:flex-start; max-width:78%; background:#111111; color:#ffffff; padding:12px 16px; border-radius:12px; border-bottom-left-radius:2px; border:1px solid #e4b353; transition:background-color 0.4s ease;';
            msgDiv.innerHTML = `
                <div style="font-size:0.74rem; font-weight:700; color:#e4b353; margin-bottom:4px;">
                    <i class="fas fa-shield-alt"></i> ${msg.autor || 'Administrador BIO-GEST'}
                </div>
                <div style="font-size:0.92rem; line-height:1.45;">${escapeHtml(msg.mensaje)}</div>
                <div style="font-size:0.7rem; color:#9ca3af; margin-top:5px; text-align:right;">
                    ${msg.fecha_envio || ''}
                </div>
            `;
            if (isNewIncoming) {
                // Alerta sonora Web Audio API y resaltado visual
                playNotificationSound();
                if (banner) banner.style.display = 'flex';
                msgDiv.style.backgroundColor = '#1e293b';
                msgDiv.style.boxShadow = '0 0 14px rgba(228,179,83,0.6)';
            }
        } else {
            msgDiv.style.cssText = 'align-self:flex-end; max-width:78%; background:#e4b353; color:#111111; padding:12px 16px; border-radius:12px; border-bottom-right-radius:2px; font-weight:500;';
            msgDiv.innerHTML = `
                <div style="font-size:0.74rem; font-weight:800; color:#111111; margin-bottom:4px;">
                    Tú (${escapeHtml(msg.autor || 'Tú')})
                </div>
                <div style="font-size:0.92rem; line-height:1.45;">${escapeHtml(msg.mensaje)}</div>
                <div style="font-size:0.7rem; color:#333333; margin-top:5px; text-align:right;">
                    ${msg.fecha_envio || ''}
                </div>
            `;
        }

        chatBox.appendChild(msgDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // POLLING CADA 5 SEGUNDOS
    setInterval(function () {
        fetch("{{ route('cliente.chat.index') }}", {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.ok) return;

            // Actualizar estado en línea del admin
            if (badgeOnline) {
                if (data.admin_online) {
                    badgeOnline.style.background = '#065f46';
                    badgeOnline.style.color = '#d1fae5';
                    badgeOnline.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#34d399;"></i> En línea';
                } else {
                    badgeOnline.style.background = '#374151';
                    badgeOnline.style.color = '#e5e7eb';
                    badgeOnline.innerHTML = '<i class="fas fa-circle" style="font-size:0.5rem; vertical-align:middle; color:#9ca3af;"></i> Fuera de línea';
                }
            }

            // Detectar nuevos mensajes
            if (Array.isArray(data.mensajes)) {
                let hasNewAdminMsg = false;
                data.mensajes.forEach(msg => {
                    if (!knownMessageIds.has(msg.id_mensaje)) {
                        knownMessageIds.add(msg.id_mensaje);
                        if (msg.es_admin) {
                            hasNewAdminMsg = true;
                            appendMessage(msg, true);
                        } else {
                            appendMessage(msg, false);
                        }
                    }
                });
            }
        })
        .catch(err => console.debug('Error en polling de chat cliente:', err));
    }, 5000);
});
</script>
@endpush
