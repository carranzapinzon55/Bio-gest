@extends('layouts.admin')

@section('title', 'Mi Perfil de Administrador | BIO-GEST Tenebrios Gold')
@section('header_title', 'Mi Perfil de Administrador')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
        <div>
            <h1 style="font-size:1.55rem; font-weight:800; color:#111111; margin:0;">
                Mi Perfil de Administrador
            </h1>
            <p style="margin:4px 0 0 0; color:#6b7280; font-size:0.88rem;">
                Actualiza tu información personal, correo electrónico corporativo y contraseña de acceso con estándar de seguridad BIO-GEST.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.ajustes.index') }}" class="tg-btn-outline">
                <i class="fa-solid fa-gear"></i> Ir a Ajustes del Sistema
            </a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: minmax(280px, 340px) minmax(0, 1fr); gap:24px; align-items:start;">
        <!-- Tarjeta Resumen del Administrador -->
        <div class="tg-card" style="text-align:center;">
            <div style="width:82px; height:82px; border-radius:50%; background:#111111; color:#e6a100; font-size:2rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 14px auto; border:2px solid #e6a100; box-shadow:0 4px 12px rgba(230,161,0,0.25);">
                {{ strtoupper(substr($admin->nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($admin->apellido ?? '', 0, 1)) }}
            </div>

            <h2 style="font-size:1.2rem; font-weight:800; color:#111111; margin:0 0 4px 0;">
                {{ $admin->nombre_completo }}
            </h2>
            <p style="color:#64748b; font-size:0.88rem; margin:0 0 12px 0;">
                {{ $admin->correo }}
            </p>

            <span style="background:#111111; color:#e6a100; font-size:0.76rem; font-weight:700; padding:4px 12px; border-radius:999px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-shield-halved"></i> {{ $admin->rol?->nombre_rol ?? 'Administrador' }}
            </span>

            <div style="border-top:1px solid #f1f5f9; margin-top:20px; padding-top:16px; text-align:left; font-size:0.84rem; display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#64748b;">ID de Usuario:</span>
                    <strong style="color:#111111;">USR-{{ str_pad((string) $admin->id_usuario, 3, '0', STR_PAD_LEFT) }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#64748b;">Teléfono:</span>
                    <strong style="color:#111111;">{{ $admin->telefono ?: 'No registrado' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#64748b;">Fecha de Registro:</span>
                    <strong style="color:#111111;">{{ $admin->fecha_registro ? \Carbon\Carbon::parse($admin->fecha_registro)->format('d/m/Y') : now()->format('d/m/Y') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span style="color:#64748b;">Estado:</span>
                    <strong style="color:#059669;"><i class="fa-solid fa-circle-check"></i> {{ $admin->estado }}</strong>
                </div>
            </div>
        </div>

        <!-- Formulario de Edición de Perfil del Administrador -->
        <div class="tg-card">
            <div class="card-header-flex" style="border-bottom:1px solid #f1f5f9; padding-bottom:14px; margin-bottom:20px;">
                <h2 style="font-size:1.12rem; font-weight:800; color:#111111; margin:0; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-user-pen" style="color:#e6a100;"></i> Editar Información de Mi Cuenta
                </h2>
                <span style="font-size:0.75rem; background:#fdf8f0; color:#92400e; padding:3px 10px; border-radius:999px; font-weight:700;">
                    Protegido con Hash Bcrypt
                </span>
            </div>

            <form action="{{ route('admin.perfil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Nombre(s) *
                        </label>
                        <input type="text" name="nombre" data-only-letters="true" required minlength="2" maxlength="100"
                               pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)"
                               value="{{ old('nombre', $admin->nombre) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Apellido(s) *
                        </label>
                        <input type="text" name="apellido" data-only-letters="true" required minlength="2" maxlength="100"
                               pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)"
                               value="{{ old('apellido', $admin->apellido) }}"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.9rem;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Correo Electrónico *
                        </label>
                        <input type="email" name="correo" required maxlength="150"
                               value="{{ old('correo', $admin->correo) }}"
                               placeholder="admin@tenebriosgold.com"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.9rem;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; font-size:0.84rem; color:#111111; margin-bottom:6px;">
                            Teléfono de Contacto
                        </label>
                        <input type="tel" name="telefono" data-only-digits="10" inputmode="numeric" maxlength="10"
                               pattern="^[0-9]{7,10}$" title="Solo números (entre 7 y 10 dígitos)"
                               value="{{ old('telefono', $admin->telefono) }}"
                               placeholder="Ej. 3243007206"
                               style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.9rem;">
                    </div>
                </div>

                <!-- Bloque de Cambio de Contraseña con Estándar Obligatorio -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:22px;">
                    <h3 style="margin:0 0 6px 0; font-size:0.96rem; font-weight:800; color:#111111;">
                        <i class="fa-solid fa-key" style="color:#e6a100;"></i> Actualizar Contraseña (Opcional)
                    </h3>
                    <p style="margin:0 0 14px 0; font-size:0.8rem; color:#64748b;">
                        Deja estos campos vacíos si deseas conservar tu contraseña actual. Si ingresas una nueva contraseña, debe cumplir con el estándar obligatorio: <strong>mínimo 8 caracteres, al menos una letra, al menos un número y confirmación</strong>.
                    </p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <label style="display:block; font-weight:700; font-size:0.82rem; color:#111111; margin-bottom:5px;">
                                Nueva Contraseña
                            </label>
                            <input type="password" name="contrasena" minlength="8"
                                   pattern="^(?=.*[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])(?=.*\d).{8,}$"
                                   title="Mínimo 8 caracteres, incluyendo al menos una letra y un número"
                                   placeholder="Mín. 8 caracteres (letras y números)"
                                   autocomplete="new-password"
                                   style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                        </div>

                        <div>
                            <label style="display:block; font-weight:700; font-size:0.82rem; color:#111111; margin-bottom:5px;">
                                Confirmar Nueva Contraseña
                            </label>
                            <input type="password" name="contrasena_confirmation" minlength="8"
                                   placeholder="Repite la nueva contraseña"
                                   autocomplete="new-password"
                                   style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:0.88rem;">
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <button type="submit" class="tg-btn-dark" style="padding:10px 24px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios en Mi Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
