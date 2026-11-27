@extends('layouts.admin')

@section('title', 'Gestión de Usuarios | BIO-GEST Tenebrios Gold')
@section('header_title', 'Administración de Usuarios y Accesos')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/usuarios.css') }}?v={{ file_exists(public_path('assets/css/usuarios.css')) ? filemtime(public_path('assets/css/usuarios.css')) : time() }}">
    <style>
        .role-badge-admin {
            background-color: #111111;
            color: #e6a100;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .role-badge-cliente {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .user-action-btn {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 6px;
            padding: 6px 11px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .user-action-btn:hover {
            background-color: #f9fafb;
            border-color: #cbd5e1;
        }
        .btn-status-toggle.is-active {
            color: #059669;
            border-color: #a7f3d0;
            background: #ecfdf5;
        }
        .btn-status-toggle.is-active:hover {
            background: #d1fae5;
        }
        .btn-status-toggle.is-inactive {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
        }
        .btn-status-toggle.is-inactive:hover {
            background: #fee2e2;
        }
        .role-fixed-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
            border-radius: 6px;
            font-weight: 700;
            color: #111111;
            font-size: 0.88rem;
        }
        .address-card, .order-item-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            font-size: 0.84rem;
        }
        .order-badge-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }
        .order-summary-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        @media (max-width: 540px) {
            .order-summary-strip {
                grid-template-columns: 1fr;
            }
        }
        .order-summary-box {
            background: #fdf8f0;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .order-summary-box span {
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #92400e;
            font-weight: 700;
        }
        .order-summary-box strong {
            font-size: 0.95rem;
            color: #111111;
        }
    </style>
@endpush

@section('content')
    @if(isset($errors) && $errors->any())
        <div class="alert-box alert-danger" style="margin-bottom:18px; background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; border-left:4px solid #dc2626;">
            <strong><i class="fa-solid fa-circle-exclamation"></i> Corrige los siguientes errores al registrar el administrador:</strong>
            <ul style="margin:6px 0 0 18px; padding:0;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. ENCABEZADO DE SECCIÓN -->
    <section class="section-header">
        <div class="section-title-group">
            <h1 class="section-title">Usuarios</h1>
            <p class="section-subtitle">
                Cuentas registradas en BIO-GEST. Los clientes se registran por autoservicio en la tienda; el panel permite dar de alta nuevos administradores y auditar cuentas.
            </p>
        </div>
        <div class="section-actions">
            <button type="button" class="tg-btn-dark" id="btnOpenCreateModal">
                <i class="fa-solid fa-user-shield"></i> + Nuevo Administrador
            </button>
        </div>
    </section>

    <!-- 2. BARRA DE FILTROS Y BÚSQUEDA (Tarjeta blanca) -->
    <section class="tg-card filter-card" aria-label="Filtros de búsqueda de usuarios">
        <div class="filter-grid">
            <!-- Buscador -->
            <div class="filter-item filter-search">
                <label for="searchInput" class="filter-label">Búsqueda rápida</label>
                <div class="search-input-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Buscar por nombre, apellido, correo o ID..."
                        autocomplete="off"
                    >
                </div>
            </div>

            <!-- Selector de Rol (SOLO Administrador y Cliente - HU-02 / RF-002) -->
            <div class="filter-item">
                <label for="roleFilter" class="filter-label">Rol en el sistema</label>
                <div class="select-wrapper">
                    <select id="roleFilter" class="form-select">
                        <option value="Todos">Todos los roles</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Cliente">Cliente</option>
                    </select>
                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                </div>
            </div>

            <!-- Selector de Estado -->
            <div class="filter-item">
                <label for="statusFilter" class="filter-label">Estado de cuenta</label>
                <div class="select-wrapper">
                    <select id="statusFilter" class="form-select">
                        <option value="Todos">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                </div>
            </div>

            <!-- Botón Filtros / Limpiar -->
            <div class="filter-item filter-actions">
                <button type="button" class="btn-filter-secondary" id="btnResetFilters" title="Restablecer filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span id="filterBtnText">Restablecer</span>
                </button>
            </div>
        </div>
    </section>

    <!-- 3. TABLA DE USUARIOS (Tarjeta blanca) -->
    <section class="tg-card table-card">
        <div class="table-toolbar">
            <div class="table-count">
                Mostrando <strong id="visibleCount">{{ count($usuariosIniciales ?? []) }}</strong> de <strong id="totalCount">{{ count($usuariosIniciales ?? []) }}</strong> cuentas registradas
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table" id="usersTable">
                <thead>
                    <tr>
                        <th style="width:70px;">ID</th>
                        <th>Nombre y Apellido</th>
                        <th>Correo Electrónico</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro / Acceso</th>
                        <th style="text-align:right;">Acciones Permitidas</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($usuariosIniciales ?? [] as $u)
                        @php
                            $isActivo = ($u['estado'] ?? 'Activo') === 'Activo';
                            $isAdmin = ($u['rol'] ?? 'Cliente') === 'Administrador';
                        @endphp
                        <tr data-user-row="{{ $u['id_usuario'] }}">
                            <td style="font-weight:700; color:#4b5563;">{{ $u['id'] }}</td>
                            <td>
                                <div style="font-weight:700; color:#111111;">{{ $u['nombre'] }}</div>
                                <div style="font-size:0.78rem; color:#6b7280;">{{ $u['notas'] ?? 'Alimentación continua' }}</div>
                            </td>
                            <td style="color:#1e293b;">{{ $u['correo'] }}</td>
                            <td>
                                @if($isAdmin)
                                    <span class="role-badge-admin"><i class="fa-solid fa-shield-halved"></i> Administrador</span>
                                @else
                                    <span class="role-badge-cliente"><i class="fa-solid fa-user"></i> Cliente</span>
                                @endif
                            </td>
                            <td>
                                @if($isActivo)
                                    <span class="status-badge status-active"><i class="fa-solid fa-circle-check"></i> Activo</span>
                                @else
                                    <span class="status-badge status-inactive"><i class="fa-solid fa-circle-xmark"></i> Inactivo</span>
                                @endif
                            </td>
                            <td style="font-size:0.82rem; color:#6b7280;">{{ $u['ultimoAcceso'] ?? $u['fecha_registro'] ?? '-' }}</td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:8px;">
                                    <button
                                        type="button"
                                        class="user-action-btn btn-view-detail"
                                        data-id="{{ $u['id_usuario'] }}"
                                        title="Ver ficha completa de la cuenta"
                                    >
                                        <i class="fa-solid fa-eye" style="color:#e6a100;"></i> Ver
                                    </button>
                                    <button
                                        type="button"
                                        class="user-action-btn btn-status-toggle {{ $isActivo ? 'is-active' : 'is-inactive' }}"
                                        data-id="{{ $u['id_usuario'] }}"
                                        title="{{ $isActivo ? 'Desactivar cuenta' : 'Activar cuenta' }}"
                                    >
                                        <i class="fa-solid {{ $isActivo ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        <span>{{ $isActivo ? 'Desactivar' : 'Activar' }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:25px; color:#6b7280;">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="empty-state" id="emptyState" hidden>
            <div class="empty-state-icon">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h3>No se encontraron usuarios</h3>
            <p>Intenta ajustar los criterios de búsqueda o restablecer los filtros activos.</p>
            <button type="button" class="tg-btn-gold" id="btnEmptyReset">
                <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
            </button>
        </div>
    </section>

    <!-- MODAL REGISTRAR NUEVO ADMINISTRADOR (Exclusivo Administrador id_rol = 1) -->
    @php $hasErrors = isset($errors) && $errors->any(); @endphp
    <div class="modal-backdrop {{ $hasErrors ? 'open active' : '' }}" id="userFormModal" aria-hidden="{{ $hasErrors ? 'false' : 'true' }}">
        <div class="modal-dialog" role="dialog" aria-labelledby="userModalTitle" aria-modal="true">
            <header class="modal-header">
                <div class="modal-title-wrap">
                    <i class="fa-solid fa-user-shield modal-title-icon" style="color:#e6a100;"></i>
                    <h2 id="userModalTitle">Nuevo Administrador BIO-GEST</h2>
                </div>
                <button type="button" class="modal-close-btn" data-close-modal="userFormModal" aria-label="Cerrar modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <form id="userForm" action="{{ route('admin.usuarios.store') }}" method="POST" novalidate>
                @csrf
                <input type="hidden" name="id_rol" value="1">
                <div class="modal-body">
                    <div style="background:#fdf8f0; border:1px solid #fde68a; border-radius:6px; padding:10px 14px; margin-bottom:16px; font-size:0.83rem; color:#92400e;">
                        <i class="fa-solid fa-circle-info"></i> <strong>Regla de negocio:</strong> Los clientes se registran por su cuenta en la tienda web. Este formulario otorga credenciales con privilegios de <strong>Administrador (Rol #1)</strong> para operar la plataforma.
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userNameInput">Nombre y Apellido <span class="required">*</span></label>
                            <input type="text" id="userNameInput" name="nombre" value="{{ old('nombre') }}" class="form-control" placeholder="Ej. Camila Restrepo " minlength="3" maxlength="100" pattern="^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]+$" title="Solo se permiten letras y espacios (sin números)" required>
                            <small class="error-msg" id="nameError">{{ isset($errors) ? $errors->first('nombre') : '' }}</small>
                        </div>

                        <div class="form-group">
                            <label for="userEmailInput">Correo corporativo <span class="required">*</span></label>
                            <input type="email" id="userEmailInput" name="correo" value="{{ old('correo') }}" class="form-control" placeholder="Ej. camila@tenebriosgold.com" required>
                            <small class="error-msg" id="emailError">{{ isset($errors) ? $errors->first('correo') : '' }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userPasswordInput">Contraseña inicial (mín. 8 caracteres, letra y número) <span class="required">*</span></label>
                            <input type="password" id="userPasswordInput" name="contrasena" class="form-control" placeholder="Mín. 8 caracteres (letras y números)" required minlength="8" autocomplete="new-password">
                            <small class="error-msg" id="passwordError">{{ isset($errors) ? $errors->first('contrasena') : '' }}</small>
                        </div>

                        <div class="form-group">
                            <label for="userPasswordConfirmInput">Confirmar contraseña <span class="required">*</span></label>
                            <input type="password" id="userPasswordConfirmInput" name="contrasena_confirmation" class="form-control" placeholder="Repite la contraseña" required minlength="8" autocomplete="new-password">
                            <small class="error-msg" id="passwordConfirmError"></small>
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- ROL FIJADO ESTRICTAMENTE COMO ADMINISTRADOR -->
                        <div class="form-group">
                            <label>Rol asignado (Fijo)</label>
                            <div class="role-fixed-box">
                                <i class="fa-solid fa-shield-halved" style="color:#e6a100;"></i>
                                <span>Administrador</span>
                                <span style="margin-left:auto; font-size:0.75rem; color:#6b7280; font-weight:normal;">id_rol = 1</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="userPhoneInput">Teléfono de contacto</label>
                            <input type="tel" id="userPhoneInput" name="telefono" value="{{ old('telefono') }}" class="form-control" placeholder="Ej. 3101234567 (solo números)" inputmode="numeric" maxlength="10" pattern="^[0-9]{7,10}$" title="Solo números (7 a 10 dígitos)">
                            <small class="error-msg" id="phoneError">{{ isset($errors) ? $errors->first('telefono') : '' }}</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userStatusInput">Estado inicial</label>
                            <div class="select-wrapper">
                                <select id="userStatusInput" name="estado" class="form-select">
                                    <option value="Activo" {{ old('estado', 'Activo') === 'Activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="Inactivo" {{ old('estado') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="modal-footer">
                    <button type="button" class="btn-filter-secondary" data-close-modal="userFormModal">
                        Cancelar
                    </button>
                    <button type="submit" class="tg-btn-dark" id="btnSaveUser">
                        <i class="fa-solid fa-check"></i> Registrar Administrador
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <!-- MODAL CONSULTA DE FICHA DE INFORMACIÓN DE CUENTA -->
    <div class="modal-backdrop" id="userDetailModal" aria-hidden="true">
        <div class="modal-dialog" style="max-width:740px;" role="dialog" aria-labelledby="detailModalTitle" aria-modal="true">
            <header class="modal-header">
                <div class="modal-title-wrap">
                    <i class="fa-solid fa-id-card modal-title-icon" style="color:#e6a100;"></i>
                    <h2 id="detailModalTitle">Ficha de Información de Cuenta</h2>
                </div>
                <button type="button" class="modal-close-btn" data-close-modal="userDetailModal" aria-label="Cerrar detalle">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="modal-body" style="max-height:calc(85vh - 140px); overflow-y:auto;">
                <!-- Encabezado Perfil -->
                <div class="user-profile-header" style="margin-bottom:18px;">
                    <div class="detail-avatar" id="detailAvatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="detail-headline">
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="detail-id-code" id="detailId">USR-001</span>
                            <span id="detailRoleBadgeContainer"></span>
                            <span id="detailStatusBadgeContainer"></span>
                        </div>
                        <h3 id="detailName" style="margin-top:4px;">Nombre Usuario</h3>
                        <p id="detailEmail">correo@usuario.com</p>
                    </div>
                </div>

                <!-- Datos Personales y de Cuenta -->
                <div class="detail-grid" style="margin-bottom:20px;">
                    <div class="detail-item">
                        <span class="detail-label">Nombre(s)</span>
                        <strong id="detailFirstName">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Apellido(s)</span>
                        <strong id="detailLastName">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Teléfono de Contacto</span>
                        <strong id="detailPhone">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Perfil de Uso</span>
                        <strong id="detailPerfil">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fecha de Registro</span>
                        <strong id="detailRegistered">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Último Acceso / Actividad</span>
                        <strong id="detailLastAccess">-</strong>
                    </div>
                </div>

                <!-- Bloque Direcciones Registradas -->
                <div style="margin-bottom:20px;">
                    <h4 style="font-size:0.92rem; font-weight:700; color:#111111; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-location-dot" style="color:#e6a100;"></i> Direcciones Registradas (<span id="addressCount">0</span>)
                    </h4>
                    <div id="addressListContainer">
                        <p style="color:#6b7280; font-size:0.84rem; font-style:italic;">Cargando direcciones...</p>
                    </div>
                </div>

                <!-- Bloque Resumen e Historial de Pedidos -->
                <div>
                    <h4 style="font-size:0.92rem; font-weight:700; color:#111111; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-box-archive" style="color:#e6a100;"></i> Resumen e Historial de Pedidos (<span id="orderCount">0</span>)
                    </h4>
                    <div class="order-summary-strip" id="orderSummaryStrip">
                        <div class="order-summary-box">
                            <span>Total Pedidos</span>
                            <strong id="summaryTotalOrders">0</strong>
                        </div>
                        <div class="order-summary-box">
                            <span>Total Comprado</span>
                            <strong id="summaryTotalSpent">$0</strong>
                        </div>
                        <div class="order-summary-box">
                            <span>Último Pedido</span>
                            <strong id="summaryLastOrder">-</strong>
                        </div>
                    </div>
                    <div id="orderListContainer">
                        <p style="color:#6b7280; font-size:0.84rem; font-style:italic;">Cargando pedidos...</p>
                    </div>
                </div>
            </div>

            <footer class="modal-footer" style="justify-content:space-between;">
                <div>
                    <span id="detailAccountStatusNotice" style="font-size:0.82rem; color:#6b7280;"></span>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn-filter-secondary" data-close-modal="userDetailModal">
                        Cerrar Ficha
                    </button>
                    <button type="button" class="user-action-btn" id="btnToggleStatusFromModal">
                        <i class="fa-solid fa-power-off"></i> Alternar Estado
                    </button>
                </div>
            </footer>
        </div>
    </div>

    <div class="toast-container" id="toastContainer" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        window.BIOGEST_USERS_DATA = @json($usuariosIniciales ?? []);
        window.BIOGEST_USERS_API = {
            baseUrl: "{{ route('admin.usuarios.index') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('assets/js/usuarios.js') }}?v={{ file_exists(public_path('assets/js/usuarios.js')) ? filemtime(public_path('assets/js/usuarios.js')) : time() }}"></script>
@endpush
