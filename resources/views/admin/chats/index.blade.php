@extends('layouts.admin')

@section('title', 'Centro de Mensajes y Chatbot | BIO-GEST Tenebrios Gold')
@section('header_title', 'Centro de Mensajes y Chatbot BIO-GEST')

@push('styles')
<style>
    .chat-layout-grid {
        display: grid;
        grid-template-columns: 310px 1fr 340px;
        gap: 20px;
        align-items: start;
        min-height: calc(100vh - 170px);
    }
    @media (max-width: 1200px) {
        .chat-layout-grid {
            grid-template-columns: 280px 1fr;
        }
        .col-faq {
            grid-column: span 2;
        }
    }
    @media (max-width: 860px) {
        .chat-layout-grid {
            grid-template-columns: 1fr;
        }
        .col-faq {
            grid-column: span 1;
        }
    }
    .conversation-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        text-decoration: none;
        color: inherit;
        margin-bottom: 6px;
    }
    .conversation-item:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    .conversation-item.active {
        background-color: #fdf8f0;
        border-color: #e6a100;
    }
    .chat-bubble {
        max-width: 78%;
        padding: 10px 14px;
        border-radius: 12px;
        margin-bottom: 12px;
        font-size: 0.88rem;
        line-height: 1.45;
        position: relative;
    }
    .chat-bubble.cliente {
        background-color: #f1f5f9;
        color: #1e293b;
        border-bottom-left-radius: 2px;
        align-self: flex-start;
    }
    .chat-bubble.admin {
        background-color: #111111;
        color: #ffffff;
        border-bottom-right-radius: 2px;
        align-self: flex-end;
        border: 1px solid #333333;
    }
    .chat-bubble.admin .admin-badge-chat {
        color: #e6a100;
        font-size: 0.72rem;
        font-weight: 700;
        margin-bottom: 4px;
        display: block;
    }
    .chat-bubble-time {
        font-size: 0.7rem;
        margin-top: 4px;
        display: block;
        opacity: 0.75;
    }
    .tab-btn-pill {
        flex: 1;
        padding: 8px 12px;
        border: none;
        background: #f1f5f9;
        color: #475569;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .tab-btn-pill.active {
        background: #111111;
        color: #e6a100;
    }
    .faq-item-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        font-size: 0.84rem;
    }
</style>
@endpush

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Centro de Mensajes y Chatbot BIO-GEST
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Atención directa a compradores, resolución de solicitudes web y gestión de la base de conocimiento del Chatbot.
            </p>
        </div>
        <div>
            <button type="button" class="tg-btn-dark" onclick="openFaqModal()">
                <i class="fa-solid fa-plus"></i> Nueva pregunta frecuente
            </button>
        </div>
    </div>

    <!-- REJILLA DE 3 COLUMNAS -->
    <div class="chat-layout-grid">

        <!-- COLUMNA 1: CONVERSACIONES Y CONTACTOS -->
        <div class="tg-card" style="padding:16px; display:flex; flex-direction:column; height:calc(100vh - 190px);">
            <!-- Pestañas Chats / Formularios de Contacto -->
            <div style="display:flex; gap:6px; margin-bottom:14px; background:#f1f5f9; padding:3px; border-radius:8px;">
                <button type="button" class="tab-btn-pill active" id="tabBtnChats" onclick="switchLeftTab('chats')">
                    <i class="fa-solid fa-comments"></i> Chats en Vivo
                </button>
                <button type="button" class="tab-btn-pill" id="tabBtnContactos" onclick="switchLeftTab('contactos')">
                    <i class="fa-solid fa-envelope"></i> Contacto ({{ $contactos->where('estado', 'Pendiente')->count() }})
                </button>
            </div>

            <!-- Panel Lista de Chats -->
            <div id="panelChatsList" style="flex:1; overflow-y:auto;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700; margin-bottom:8px; padding-left:4px;">
                    Conversaciones Activas ({{ count($clientesConversaciones) }})
                </div>

                @forelse($clientesConversaciones as $c)
                    @php $isActive = $usuarioActivo && $usuarioActivo->id_usuario == $c['id_usuario']; @endphp
                    <a href="{{ route('admin.chats.index', ['usuario' => $c['id_usuario']]) }}"
                       class="conversation-item {{ $isActive ? 'active' : '' }}">
                        <div style="width:38px; height:38px; border-radius:50%; background:#111111; color:#e6a100; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; flex-shrink:0;">
                            {{ strtoupper(substr($c['nombre'], 0, 1)) }}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <strong style="font-size:0.86rem; color:#111111; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $c['nombre'] }}
                                </strong>
                                <span style="font-size:0.7rem; color:#94a3b8; flex-shrink:0;">{{ $c['fecha_ultimo'] }}</span>
                            </div>
                            <div style="font-size:0.78rem; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                @if($c['es_admin_last'])
                                    <span style="color:#e6a100; font-weight:bold;">Tú: </span>
                                @endif
                                {{ $c['ultimo_mensaje'] }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="text-align:center; padding:24px 10px; color:#94a3b8; font-size:0.84rem;">
                        <i class="fa-regular fa-comment-dots" style="font-size:1.8rem; margin-bottom:8px; display:block;"></i>
                        No hay chats activos.
                    </div>
                @endforelse

                @if($otrosClientes->isNotEmpty())
                    <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700; margin:16px 0 8px; padding-left:4px;">
                        Iniciar Chat con Comprador
                    </div>
                    @foreach($otrosClientes as $oc)
                        <a href="{{ route('admin.chats.index', ['usuario' => $oc->id_usuario]) }}"
                           class="conversation-item" style="opacity:0.85;">
                            <div style="width:34px; height:34px; border-radius:50%; background:#e2e8f0; color:#475569; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.82rem; flex-shrink:0;">
                                {{ strtoupper(substr($oc->nombre, 0, 1)) }}
                            </div>
                            <div style="flex:1; min-width:0;">
                                <strong style="font-size:0.84rem; color:#111111; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $oc->nombre_completo }}
                                </strong>
                                <span style="font-size:0.75rem; color:#64748b;">Iniciar conversación</span>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Panel Lista de Contactos Web -->
            <div id="panelContactosList" style="flex:1; overflow-y:auto; display:none;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700; margin-bottom:8px; padding-left:4px;">
                    Solicitudes Web ({{ count($contactos) }})
                </div>

                @forelse($contactos as $con)
                    <div class="conversation-item" onclick="verDetalleContactoPorId({{ $con->id_mensaje }})" style="flex-direction:column; align-items:flex-start; gap:4px;">
                        <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                            <strong style="font-size:0.85rem; color:#111111;">{{ $con->nombre }}</strong>
                            <span style="font-size:0.68rem; padding:1px 6px; border-radius:999px; font-weight:700; background:{{ $con->estado === 'Atendido' ? '#dcfce7' : '#fee2e2' }}; color:{{ $con->estado === 'Atendido' ? '#166534' : '#991b1b' }};">
                                {{ $con->estado }}
                            </span>
                        </div>
                        <div style="font-size:0.78rem; font-weight:600; color:#334155; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%;">
                            {{ $con->asunto ?: 'Consulta general' }}
                        </div>
                        <div style="font-size:0.72rem; color:#64748b; display:flex; justify-content:space-between; width:100%;">
                            <span>{{ $con->correo }}</span>
                            <span>{{ $con->fecha_envio?->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:24px 10px; color:#94a3b8; font-size:0.84rem;">
                        No hay solicitudes de contacto web.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- COLUMNA 2: ÁREA DE MENSAJES Y RESPUESTAS / DETALLE DE CONTACTO -->
        <div class="tg-card" style="padding:0; display:flex; flex-direction:column; height:calc(100vh - 190px); overflow:hidden;">
            <!-- Vista de Chat Activo -->
            <div id="viewChatArea" style="display:flex; flex-direction:column; height:100%;">
                @if($usuarioActivo)
                    <!-- Header del Chat -->
                    <div style="padding:14px 18px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#ffffff;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:50%; background:#111111; color:#e6a100; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem;">
                                {{ strtoupper(substr($usuarioActivo->nombre, 0, 1)) }}
                            </div>
                            <div>
                                <h3 style="font-size:0.95rem; font-weight:800; color:#111111; margin:0;">
                                    {{ $usuarioActivo->nombre_completo }}
                                </h3>
                                <div style="font-size:0.78rem; color:#64748b;">
                                    <i class="fa-regular fa-envelope"></i> {{ $usuarioActivo->correo }} ·
                                    <i class="fa-solid fa-phone"></i> {{ $usuarioActivo->telefono ?: 'Sin teléfono' }}
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span id="badgeNuevoMensajeAdmin" style="display:none; font-size:0.75rem; background:#dcfce7; color:#166534; font-weight:700; padding:3px 10px; border-radius:999px; animation: pulse 2s infinite;">
                                <i class="fa-solid fa-bell fa-shake"></i> Nuevo Mensaje
                            </span>
                            <span style="font-size:0.75rem; background:#ecfdf5; color:#065f46; font-weight:700; padding:3px 10px; border-radius:999px;">
                                <i class="fa-solid fa-circle" style="font-size:0.5rem; vertical-align:middle;"></i> Chat Activo
                            </span>
                        </div>
                    </div>

                    <!-- Mensajes Hilo -->
                    <div id="chatMessagesContainer" style="flex:1; overflow-y:auto; padding:18px; display:flex; flex-direction:column; background:#f8fafc;">
                        @forelse($mensajesHilo as $msg)
                            @if($msg->es_admin)
                                <!-- Mensaje Administrador -->
                                <div class="chat-bubble admin" data-msg-id="{{ $msg->id_mensaje }}">
                                    <span class="admin-badge-chat">
                                        <i class="fa-solid fa-shield-halved"></i> {{ optional($msg->admin)->nombre ?? 'Administrador BIO-GEST' }}
                                    </span>
                                    <div>{{ $msg->mensaje }}</div>
                                    <span class="chat-bubble-time" style="text-align:right;">
                                        {{ $msg->fecha_envio?->format('d/m/Y h:i a') }}
                                    </span>
                                </div>
                            @else
                                <!-- Mensaje Cliente -->
                                <div class="chat-bubble cliente" data-msg-id="{{ $msg->id_mensaje }}">
                                    <div style="font-weight:700; font-size:0.75rem; color:#475569; margin-bottom:3px;">
                                        {{ $usuarioActivo->nombre }}
                                    </div>
                                    <div>{{ $msg->mensaje }}</div>
                                    <span class="chat-bubble-time">
                                        {{ $msg->fecha_envio?->format('d/m/Y h:i a') }}
                                    </span>
                                </div>
                            @endif
                        @empty
                            <div id="adminEmptyChatNotice" style="text-align:center; padding:40px 10px; color:#94a3b8; font-size:0.86rem; margin:auto;">
                                <i class="fa-regular fa-comments" style="font-size:2.5rem; margin-bottom:10px; display:block; color:#cbd5e1;"></i>
                                No hay mensajes intercambiados todavía con este cliente.<br>
                                Escribe el primer mensaje a continuación para asesorarle.
                            </div>
                        @endforelse
                    </div>

                    <!-- Input Box Inferior -->
                    <div style="padding:14px 18px; border-top:1px solid #e2e8f0; background:#ffffff;">
                        <form id="formAdminResponder" action="{{ route('admin.chats.responder') }}" method="POST" style="display:flex; gap:10px; align-items:center;">
                            @csrf
                            <input type="hidden" name="id_usuario" id="adminTargetUsuarioId" value="{{ $usuarioActivo->id_usuario }}">
                            <input type="text" name="mensaje" id="adminInputMensaje" required placeholder="Escribe tu respuesta para {{ $usuarioActivo->nombre }}..."
                                   style="flex:1; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none;" autocomplete="off">
                            <button type="submit" id="btnAdminEnviar" class="tg-btn-dark" style="padding:11px 20px; white-space:nowrap;">
                                <i class="fa-solid fa-paper-plane" style="color:#e6a100;"></i> Enviar Respuesta
                            </button>
                        </form>
                    </div>
                @else
                    <div style="text-align:center; padding:60px 20px; color:#94a3b8; margin:auto;">
                        <i class="fa-regular fa-message" style="font-size:3rem; margin-bottom:12px; display:block; color:#cbd5e1;"></i>
                        <h3 style="font-size:1.1rem; color:#475569; margin-bottom:6px;">Selecciona una conversación</h3>
                        <p style="font-size:0.85rem; max-width:320px; margin:0 auto;">
                            Elige un cliente de la lista izquierda para consultar el historial de dudas o enviar una respuesta.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Vista de Detalle de Contacto Web (Oculta por defecto) -->
            <div id="viewContactoDetail" style="display:none; flex-direction:column; height:100%; padding:20px; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:18px;">
                    <div>
                        <h3 id="contactoNombre" style="font-size:1.15rem; font-weight:800; color:#111111; margin:0;">Nombre Contacto</h3>
                        <div id="contactoCorreo" style="font-size:0.82rem; color:#64748b; margin-top:2px;">correo@ejemplo.com</div>
                    </div>
                    <button type="button" class="btn-filter-secondary" onclick="volverAChats()">
                        <i class="fa-solid fa-arrow-left"></i> Volver a Chats
                    </button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:18px;">
                    <strong style="display:block; font-size:0.75rem; text-transform:uppercase; color:#64748b; margin-bottom:4px;">Asunto de la consulta</strong>
                    <h4 id="contactoAsunto" style="font-size:0.98rem; font-weight:700; color:#111111; margin:0;">Asunto</h4>
                </div>

                <div style="margin-bottom:20px;">
                    <strong style="display:block; font-size:0.75rem; text-transform:uppercase; color:#64748b; margin-bottom:6px;">Mensaje recibido del cliente</strong>
                    <div id="contactoMensaje" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; font-size:0.88rem; line-height:1.5; color:#1e293b; min-height:80px;"></div>
                </div>

                <form id="formAtenderContacto" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Registrar Respuesta / Nota de Atención
                        </label>
                        <textarea name="respuesta_admin" id="contactoRespuestaAdmin" rows="3" placeholder="Registra la solución brindada o respuesta enviada por correo..."
                                  style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem;"></textarea>
                    </div>

                    <button type="submit" class="tg-btn-dark" style="padding:10px 20px;">
                        <i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Marcar como Solicitud Atendida
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUMNA 3: GESTOR DE PREGUNTAS FRECUENTES DEL CHATBOT -->
        <div class="tg-card col-faq" style="padding:16px; display:flex; flex-direction:column; height:calc(100vh - 190px);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:12px; margin-bottom:14px;">
                <div>
                    <h2 style="font-size:1.05rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-robot" style="color:#e6a100;"></i> Preguntas Frecuentes
                    </h2>
                    <span style="font-size:0.74rem; color:#64748b;">Base de Respuestas Automáticas</span>
                </div>
                <button type="button" class="user-action-btn" onclick="openFaqModal()" style="font-size:0.78rem; padding:4px 9px;">
                    <i class="fa-solid fa-plus"></i> Nueva
                </button>
            </div>

            <!-- Lista de Preguntas Frecuentes -->
            <div style="flex:1; overflow-y:auto; padding-right:2px;">
                @forelse($faqs as $faq)
                    <div class="faq-item-card">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:6px;">
                            <span style="background:#fef3c7; color:#92400e; font-size:0.68rem; font-weight:700; padding:2px 7px; border-radius:999px;">
                                {{ $faq->categoria ?: 'General' }}
                            </span>
                            <div style="display:flex; gap:6px;">
                                <button type="button" onclick="editFaqPorId({{ $faq->id_faq }})" style="background:none; border:none; cursor:pointer; color:#64748b; font-size:0.8rem;" title="Editar pregunta frecuente">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.chats.faq.destroy', $faq->id_faq) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta pregunta frecuente?');" style="margin:0; display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none; border:none; cursor:pointer; color:#dc2626; font-size:0.8rem;" title="Eliminar pregunta frecuente">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <strong style="display:block; color:#111111; font-size:0.84rem; margin-bottom:4px;">
                            {{ $faq->pregunta }}
                        </strong>
                        <p style="color:#475569; font-size:0.78rem; margin:0; line-height:1.4;">
                            {{ $faq->respuesta }}
                        </p>
                    </div>
                @empty
                    <div style="text-align:center; padding:30px 10px; color:#94a3b8; font-size:0.82rem;">
                        No hay preguntas frecuentes registradas.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MODAL CREAR / EDITAR FAQ -->
    <div class="modal-backdrop" id="faqModal" aria-hidden="true">
        <div class="modal-dialog" style="max-width:520px;" role="dialog" aria-modal="true">
            <header class="modal-header">
                <div class="modal-title-wrap">
                    <i class="fa-solid fa-robot modal-title-icon" style="color:#e6a100;"></i>
                    <h2 id="faqModalTitle">Nueva Pregunta Frecuente</h2>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeFaqModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <form id="faqForm" method="POST" action="{{ route('admin.chats.faq.store') }}">
                @csrf
                <input type="hidden" name="_method" id="faqFormMethod" value="POST">

                <div class="modal-body">
                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-weight:700; font-size:0.84rem; margin-bottom:4px; color:#111111;">Categoría Temática *</label>
                        <input type="text" name="categoria" id="faqCategoria" list="categoriasList" required placeholder="Ej. Envíos, Cuidados, Pagos, Nutrición"
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.86rem;">
                        <datalist id="categoriasList">
                            @foreach($categoriasFaq as $cf)
                                <option value="{{ $cf }}">
                            @endforeach
                            <option value="Envíos y Cobertura">
                            <option value="Cuidados y Manejo">
                            <option value="Pie de Cría">
                            <option value="Abono Frass">
                        </datalist>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-weight:700; font-size:0.84rem; margin-bottom:4px; color:#111111;">Pregunta del Cliente *</label>
                        <input type="text" name="pregunta" id="faqPregunta" required placeholder="Ej. ¿Cómo se garantiza la llegada viva?"
                               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.86rem;">
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block; font-weight:700; font-size:0.84rem; margin-bottom:4px; color:#111111;">Respuesta Automática del Chatbot *</label>
                        <textarea name="respuesta" id="faqRespuesta" rows="4" required placeholder="Redacta la respuesta que el chatbot devolverá de inmediato..."
                                  style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.86rem; line-height:1.4;"></textarea>
                    </div>
                </div>

                <footer class="modal-footer">
                    <button type="button" class="btn-filter-secondary" onclick="closeFaqModal()">Cancelar</button>
                    <button type="submit" class="tg-btn-dark">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar en Chatbot
                    </button>
                </footer>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const CONTACTOS_MAP = @json($contactos->keyBy('id_mensaje'));
    const FAQS_MAP = @json($faqs->keyBy('id_faq'));

    function switchLeftTab(tab) {
        const btnChats = document.getElementById('tabBtnChats');
        const btnContactos = document.getElementById('tabBtnContactos');
        const pChats = document.getElementById('panelChatsList');
        const pContactos = document.getElementById('panelContactosList');

        if (tab === 'chats') {
            btnChats.classList.add('active');
            btnContactos.classList.remove('active');
            pChats.style.display = 'block';
            pContactos.style.display = 'none';
        } else {
            btnContactos.classList.add('active');
            btnChats.classList.remove('active');
            pContactos.style.display = 'block';
            pChats.style.display = 'none';
        }
    }

    function verDetalleContactoPorId(id) {
        const contacto = CONTACTOS_MAP[id];
        if (contacto) {
            verDetalleContacto(contacto);
        }
    }

    function verDetalleContacto(contacto) {
        if (!contacto) return;
        document.getElementById('viewChatArea').style.display = 'none';
        const detail = document.getElementById('viewContactoDetail');
        detail.style.display = 'flex';

        document.getElementById('contactoNombre').textContent = contacto.nombre || 'Contacto';
        document.getElementById('contactoCorreo').textContent = contacto.correo || 'N/A';
        document.getElementById('contactoAsunto').textContent = contacto.asunto || 'Consulta General';
        document.getElementById('contactoMensaje').textContent = contacto.mensaje || '';
        document.getElementById('contactoRespuestaAdmin').value = contacto.respuesta_admin || '';

        const form = document.getElementById('formAtenderContacto');
        form.action = `{{ url('/admin/chats/contacto') }}/${contacto.id_mensaje}/atender`;
    }

    function volverAChats() {
        document.getElementById('viewContactoDetail').style.display = 'none';
        document.getElementById('viewChatArea').style.display = 'flex';
    }

    // Modal FAQ
    function openFaqModal() {
        const modal = document.getElementById('faqModal');
        const form = document.getElementById('faqForm');
        form.reset();
        form.action = "{{ route('admin.chats.faq.store') }}";
        document.getElementById('faqFormMethod').value = 'POST';
        document.getElementById('faqModalTitle').textContent = 'Nueva Pregunta Frecuente';
        modal.classList.add('active');
    }

    function editFaqPorId(id) {
        const faq = FAQS_MAP[id];
        if (faq) {
            editFaq(faq);
        }
    }

    function editFaq(faq) {
        if (!faq) return;
        const modal = document.getElementById('faqModal');
        const form = document.getElementById('faqForm');
        form.action = `{{ url('/admin/chats/faq') }}/${faq.id_faq}`;
        document.getElementById('faqFormMethod').value = 'PUT';
        document.getElementById('faqModalTitle').textContent = 'Editar Pregunta Frecuente';

        document.getElementById('faqCategoria').value = faq.categoria || '';
        document.getElementById('faqPregunta').value = faq.pregunta || '';
        document.getElementById('faqRespuesta').value = faq.respuesta || '';

        modal.classList.add('active');
    }

    function closeFaqModal() {
        document.getElementById('faqModal').classList.remove('active');
    }

    // Auto-scroll chat al fondo y Polling Web Audio
    function playAdminNotificationSound() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(440, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);

            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.35);
        } catch (e) {
            console.warn('AudioContext bloqueado:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('chatMessagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        const targetUserIdEl = document.getElementById('adminTargetUsuarioId');
        if (!targetUserIdEl) return;

        const targetUserId = targetUserIdEl.value;
        const knownAdminMsgIds = new Set();
        document.querySelectorAll('#chatMessagesContainer [data-msg-id]').forEach(el => {
            const id = parseInt(el.getAttribute('data-msg-id'), 10);
            if (id) knownAdminMsgIds.add(id);
        });

        // Polling cada 5 segundos para recibir nuevos mensajes del cliente en tiempo real
        setInterval(() => {
            fetch(`{{ route('admin.chats.index') }}?usuario=${targetUserId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.ok || !Array.isArray(data.mensajes)) return;

                let hasNewClientMsg = false;
                const notice = document.getElementById('adminEmptyChatNotice');

                data.mensajes.forEach(m => {
                    if (!knownAdminMsgIds.has(m.id_mensaje)) {
                        knownAdminMsgIds.add(m.id_mensaje);
                        if (notice) notice.remove();

                        const div = document.createElement('div');
                        div.className = m.es_admin ? 'chat-bubble admin' : 'chat-bubble cliente';
                        div.setAttribute('data-msg-id', m.id_mensaje);

                        if (m.es_admin) {
                            div.innerHTML = `
                                <span class="admin-badge-chat"><i class="fa-solid fa-shield-halved"></i> ${m.autor}</span>
                                <div>${escapeAdminHtml(m.mensaje)}</div>
                                <span class="chat-bubble-time" style="text-align:right;">${m.fecha_envio}</span>
                            `;
                        } else {
                            hasNewClientMsg = true;
                            div.innerHTML = `
                                <div style="font-weight:700; font-size:0.75rem; color:#475569; margin-bottom:3px;">${escapeAdminHtml(m.autor)}</div>
                                <div>${escapeAdminHtml(m.mensaje)}</div>
                                <span class="chat-bubble-time">${m.fecha_envio}</span>
                            `;
                            div.style.boxShadow = '0 0 12px rgba(230,161,0,0.5)';
                            div.style.backgroundColor = '#fef3c7';
                        }

                        container.appendChild(div);
                        container.scrollTop = container.scrollHeight;
                    }
                });

                if (hasNewClientMsg) {
                    playAdminNotificationSound();
                    const badge = document.getElementById('badgeNuevoMensajeAdmin');
                    if (badge) {
                        badge.style.display = 'inline-flex';
                        setTimeout(() => { badge.style.display = 'none'; }, 8000);
                    }
                }
            })
            .catch(err => console.debug('Error en polling admin:', err));
        }, 5000);
    });

    function escapeAdminHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
</script>
@endpush
