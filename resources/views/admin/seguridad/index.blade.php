@extends('layouts.admin')

@section('title', 'Seguridad, Roles y Políticas | BIO-GEST Tenebrios Gold')
@section('header_title', 'Seguridad, Roles y Políticas')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Seguridad, Roles y Políticas
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Control de privilegios estrictos (HU-02 / RF-002: Administrador y Cliente), normativas institucionales y auditoría en <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.usuarios.index') }}" class="tg-btn-dark">
                <i class="fa-solid fa-users"></i> Gestión de Cuentas
            </a>
        </div>
    </div>

    <!-- REJILLA DE 2 COLUMNAS: ROLES FIJOS A LA IZQUIERDA Y POLÍTICAS EDITABLES A LA DERECHA -->
    <div class="admin-grid-2-balanced" style="margin-bottom:28px;">

        <!-- COLUMNA IZQUIERDA: 2 ROLES FIJOS CON CAPACIDADES DE SOLO LECTURA Y CONTEO REAL -->
        <div class="tg-card" style="display:flex; flex-direction:column; gap:20px;">
            <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:4px;">
                <h2 style="font-size:1.1rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-shield-halved" style="color:#e6a100;"></i> Roles Definidos en BIO-GEST
                </h2>
                <span style="font-size:0.75rem; background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:999px; font-weight:700;">
                    2 Roles Vigentes
                </span>
            </div>

            <p style="color:#64748b; font-size:0.84rem; margin:0;">
                El sistema opera bajo un modelo estricto de solo dos roles predeterminados. Los permisos son estructurales y de solo lectura.
            </p>

            @foreach($rolesData as $rol)
                <div style="border:1px solid #e2e8f0; border-radius:10px; padding:18px; background:#ffffff; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <h3 style="font-size:1.05rem; font-weight:800; color:#111111; margin:0;">
                                    {{ $rol['nombre'] }}
                                </h3>
                                <span style="background:{{ $rol['badge_color'] }}; color:{{ $rol['badge_text'] }}; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px;">
                                    {{ $rol['badge'] }}
                                </span>
                            </div>
                            <div style="color:#64748b; font-size:0.8rem; margin-top:2px;">
                                ID Rol: <strong>#{{ $rol['id_rol'] }}</strong>
                            </div>
                        </div>

                        <!-- Conteo Real de Usuarios -->
                        <div style="text-align:right;">
                            <span style="font-size:1.35rem; font-weight:800; color:#111111;">{{ $rol['usuarios_count'] }}</span>
                            <div style="font-size:0.72rem; color:#64748b; text-transform:uppercase; font-weight:700;">Cuentas</div>
                        </div>
                    </div>

                    <p style="color:#475569; font-size:0.84rem; margin:0 0 12px; line-height:1.4;">
                        {{ $rol['descripcion'] }}
                    </p>

                    <div style="background:#f8fafc; border-radius:8px; padding:12px; border:1px solid #f1f5f9;">
                        <strong style="display:block; font-size:0.75rem; text-transform:uppercase; color:#475569; letter-spacing:0.5px; margin-bottom:8px;">
                            <i class="fa-solid fa-lock" style="color:#e6a100;"></i> Capacidades Asignadas:
                        </strong>
                        <ul style="margin:0; padding-left:18px; color:#334155; font-size:0.82rem; line-height:1.6;">
                            @foreach($rol['capacidades'] as $cap)
                                <li>{{ $cap }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- COLUMNA DERECHA: BLOQUE EDITABLE DE POLÍTICAS GUARDADAS EN LA TABLA CONFIGURACION -->
        <div class="tg-card">
            <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:16px;">
                <h2 style="font-size:1.1rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-file-shield" style="color:#e6a100;"></i> Políticas de Seguridad, Privacidad y Uso
                </h2>
                <span style="font-size:0.75rem; background:#fef3c7; color:#92400e; padding:3px 8px; border-radius:999px; font-weight:700;">
                    Almacenado en MySQL
                </span>
            </div>

            <p style="color:#64748b; font-size:0.84rem; margin-bottom:18px;">
                Estas directrices normativas se aplican a nivel institucional en <strong>BIO-GEST</strong>. Puedes editar y guardar los términos actualizados directamente en la base de datos:
            </p>

            <form action="{{ route('admin.seguridad.politicas.update') }}" method="POST">
                @csrf

                <!-- 1. Política de Contraseñas y Sesiones -->
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#111111; margin-bottom:6px;">
                        <i class="fa-solid fa-key" style="color:#e6a100;"></i> 1. Política de Contraseñas y Sesiones
                    </label>
                    <textarea name="politica_contrasenas_sesiones" rows="3" required
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; font-family:inherit; line-height:1.45;">{{ old('politica_contrasenas_sesiones', $politicas['politica_contrasenas_sesiones']) }}</textarea>
                </div>

                <!-- 2. Protección de Datos Personales -->
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#111111; margin-bottom:6px;">
                        <i class="fa-solid fa-user-lock" style="color:#e6a100;"></i> 2. Protección de Datos Personales (Habeas Data)
                    </label>
                    <textarea name="politica_datos_personales" rows="3" required
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; font-family:inherit; line-height:1.45;">{{ old('politica_datos_personales', $politicas['politica_datos_personales']) }}</textarea>
                </div>

                <!-- 3. Términos de Garantía y Reembolso -->
                <div style="margin-bottom:18px;">
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#111111; margin-bottom:6px;">
                        <i class="fa-solid fa-box-open" style="color:#e6a100;"></i> 3. Términos de Garantía de Llegada Viva y Reembolsos
                    </label>
                    <textarea name="politica_garantia_reembolso" rows="3" required
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; font-family:inherit; line-height:1.45;">{{ old('politica_garantia_reembolso', $politicas['politica_garantia_reembolso']) }}</textarea>
                </div>

                <!-- 4. Control de Acceso por Rol -->
                <div style="margin-bottom:22px;">
                    <label style="display:block; font-weight:700; font-size:0.85rem; color:#111111; margin-bottom:6px;">
                        <i class="fa-solid fa-network-wired" style="color:#e6a100;"></i> 4. Control de Acceso y Segregación de Roles
                    </label>
                    <textarea name="politica_control_acceso" rows="3" required
                              style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.85rem; font-family:inherit; line-height:1.45;">{{ old('politica_control_acceso', $politicas['politica_control_acceso']) }}</textarea>
                </div>

                <div style="text-align:right;">
                    <button type="submit" class="tg-btn-dark" style="padding:10px 22px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Políticas en Base de Datos
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BLOQUE INFERIOR: TABLA DE BITÁCORA RECIENTE DE INICIOS DE SESIÓN Y CAMBIOS DE ESTADO -->
    <div class="tg-card">
        <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:16px;">
            <div>
                <h2 style="font-size:1.15rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#e6a100;"></i> Bitácora de Auditoría y Eventos del Sistema
                </h2>
                <p style="margin:3px 0 0; color:#6b7280; font-size:0.82rem;">
                    Registro cronológico de autenticaciones, altas de administradores y cambios de estado de cuentas.
                </p>
            </div>
            <span style="font-size:0.78rem; color:#475569; font-weight:600;">
                Últimos {{ count($bitacora) }} eventos
            </span>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width:170px;">Fecha y Hora</th>
                        <th>Usuario / Actor</th>
                        <th>Acción Auditada</th>
                        <th>Detalle del Suceso</th>
                        <th>Dirección IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bitacora as $item)
                        <tr>
                            <td style="font-size:0.82rem; color:#4b5563;">
                                <i class="fa-regular fa-clock" style="color:#9ca3af;"></i>
                                {{ $item->fecha ? $item->fecha->format('d/m/Y h:i a') : '-' }}
                            </td>
                            <td>
                                @if($item->usuario)
                                    <strong style="color:#111111;">{{ $item->usuario->nombre_completo }}</strong>
                                    <div style="font-size:0.75rem; color:#6b7280;">{{ $item->usuario->correo }}</div>
                                @else
                                    <span style="color:#6b7280; font-style:italic;">Sistema / Anónimo</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700; color:#1e293b; font-size:0.84rem;">
                                    {{ $item->accion }}
                                </span>
                            </td>
                            <td style="color:#475569; font-size:0.84rem;">
                                {{ $item->descripcion }}
                            </td>
                            <td style="font-family:monospace; font-size:0.8rem; color:#6b7280;">
                                {{ $item->ip_origen ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:24px; color:#9ca3af;">
                                No se registran eventos recientes en la bitácora de seguridad.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
