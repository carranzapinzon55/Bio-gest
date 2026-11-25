@extends('layouts.admin')

@section('title', 'Atención y Mensajes de Contacto | BIO-GEST Tenebrios Gold')
@section('header_title', 'Chats y Atención al Cliente')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Chats y Solicitudes de Contacto
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Canales de interacción directa con compradores de <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
    </div>

    <div class="tables-grid-2">
        <!-- Tarjeta 1: Mensajes de Chat -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2><i class="fa-solid fa-comments" style="color:#e6a100;"></i> Mensajes del Chatbot / Soporte</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mensajes as $msg)
                            <tr>
                                <td>
                                    <strong>{{ $msg->nombre }} {{ $msg->apellido }}</strong><br>
                                    <small style="color:#6b7280;">{{ $msg->correo }}</small>
                                </td>
                                <td>{{ $msg->mensaje }}</td>
                                <td style="white-space:nowrap; font-size:0.8rem; color:#6b7280;">
                                    {{ \Carbon\Carbon::parse($msg->fecha_envio)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:20px; color:#6b7280;">
                                    No hay mensajes de chat registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tarjeta 2: Formulario de Contacto -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2><i class="fa-solid fa-envelope" style="color:#e6a100;"></i> Contactos Web Recibidos</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nombre / Correo</th>
                            <th>Asunto / Mensaje</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contactos as $c)
                            <tr>
                                <td>
                                    <strong>{{ $c->nombre ?? 'Contacto' }}</strong><br>
                                    <small style="color:#6b7280;">{{ $c->correo ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong>{{ $c->asunto ?? 'Consulta general' }}</strong><br>
                                    <span style="font-size:0.82rem; color:#4b5563;">{{ $c->mensaje ?? '' }}</span>
                                </td>
                                <td style="white-space:nowrap; font-size:0.8rem; color:#6b7280;">
                                    {{ \Carbon\Carbon::parse($c->fecha_envio ?? now())->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align:center; padding:20px; color:#6b7280;">
                                    No hay solicitudes de contacto pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
