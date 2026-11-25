@extends('layouts.admin')

@section('title', 'Seguridad y Roles | BIO-GEST Tenebrios Gold')
@section('header_title', 'Seguridad, Roles y Accesos')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Seguridad y Roles
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Políticas de acceso, roles asignados y auditoría de cuentas en <strong>TENEBRIOS GOLD</strong>.
            </p>
        </div>
        <a href="{{ route('admin.usuarios.index') }}" class="tg-btn-dark">
            <i class="fa-solid fa-users"></i> Gestionar Usuarios
        </a>
    </div>

    <div class="tables-grid-2">
        <!-- Roles -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2><i class="fa-solid fa-shield-halved" style="color:#e6a100;"></i> Roles Definidos en el Sistema</h2>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Rol</th>
                            <th>Descripción</th>
                            <th style="text-align:center;">Usuarios Asignados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $r)
                            <tr>
                                <td style="font-weight:700;">ROL-{{ $r->id_rol }}</td>
                                <td><strong style="color:#111111;">{{ $r->nombre_rol }}</strong></td>
                                <td style="color:#6b7280; font-size:0.83rem;">{{ $r->descripcion }}</td>
                                <td style="text-align:center;">
                                    <span class="badge {{ $r->nombre_rol === 'Administrador' ? 'badge-pendiente' : 'badge-confirmado' }}">
                                        {{ $r->usuarios_count }} usuarios
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Parámetros de Seguridad -->
        <div class="tg-card">
            <div class="card-header-flex">
                <h2><i class="fa-solid fa-lock" style="color:#e6a100;"></i> Parámetros de Seguridad Activos</h2>
            </div>
            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:14px;">
                <li style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                    <div>
                        <strong>Autenticación por Sesión y Middleware Role</strong>
                        <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Protección estricta contra accesos no autorizados al prefijo /admin</p>
                    </div>
                    <span class="badge badge-activo"><i class="fa-solid fa-check"></i> Activo</span>
                </li>
                <li style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                    <div>
                        <strong>Cifrado Bcrypt en Contraseñas</strong>
                        <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Todas las credenciales están aseguradas mediante hash irreversible</p>
                    </div>
                    <span class="badge badge-activo"><i class="fa-solid fa-check"></i> Activo</span>
                </li>
                <li style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #f3f4f6;">
                    <div>
                        <strong>Tokens CSRF en Formularios</strong>
                        <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Prevención contra ataques Cross-Site Request Forgery</p>
                    </div>
                    <span class="badge badge-activo"><i class="fa-solid fa-check"></i> Activo</span>
                </li>
                <li style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <strong>Middleware EnsureUserIsActive</strong>
                        <p style="margin:2px 0 0; font-size:0.8rem; color:#6b7280;">Bloqueo inmediato de accesos a cuentas en estado Inactivo</p>
                    </div>
                    <span class="badge badge-activo"><i class="fa-solid fa-check"></i> Activo</span>
                </li>
            </ul>
        </div>
    </div>
@endsection
