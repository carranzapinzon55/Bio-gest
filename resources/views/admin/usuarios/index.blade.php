@extends('layouts.admin')

@section('title', 'Gestión de Usuarios | BIO-GEST Tenebrios Gold')
@section('header_title', 'Dashboard General BIO-GEST')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/usuarios.css') }}">
@endpush

@section('content')
    <!-- 1. ENCABEZADO DE SECCIÓN -->
    <section class="section-header">
        <div class="section-title-group">
            <h1 class="section-title">Usuarios</h1>
            <p class="section-subtitle">Gestionar los usuarios del sistema</p>
        </div>
        <div class="section-actions">
            <button type="button" class="tg-btn-dark" id="btnOpenCreateModal">
                <i class="fa-solid fa-plus"></i> Crear usuario
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
                        placeholder="Buscar por nombre, correo o ID..."
                        autocomplete="off"
                    >
                </div>
            </div>

            <!-- Selector de Rol -->
            <div class="filter-item">
                <label for="roleFilter" class="filter-label">Rol</label>
                <div class="select-wrapper">
                    <select id="roleFilter" class="form-select">
                        <option value="Todos">Todos</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Vendedor">Vendedor</option>
                        <option value="Cliente">Cliente</option>
                        <option value="Inventario">Inventario</option>
                    </select>
                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                </div>
            </div>

            <!-- Selector de Estado -->
            <div class="filter-item">
                <label for="statusFilter" class="filter-label">Estado</label>
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
                    <i class="fa-solid fa-filter"></i>
                    <span id="filterBtnText">Filtros</span>
                </button>
            </div>
        </div>
    </section>

    <!-- 3. TABLA DE USUARIOS (Tarjeta blanca) -->
    <section class="tg-card table-card">
        <div class="table-toolbar">
            <div class="table-count">
                Mostrando <strong id="visibleCount">0</strong> de <strong id="totalCount">0</strong> usuarios registrados
            </div>
            <div class="bulk-info" id="bulkSelectionInfo" hidden>
                <span id="selectedCountText">0 seleccionados</span>
                <button type="button" class="btn-bulk-toggle" id="btnBulkToggleStatus">
                    <i class="fa-solid fa-repeat"></i> Alternar estado
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="custom-table" id="usersTable">
                <thead>
                    <tr>
                        <th class="col-checkbox">
                            <input type="checkbox" id="selectAllCheckbox" aria-label="Seleccionar todos los usuarios">
                        </th>
                        <th>ID</th>
                        <th>Nombre completo</th>
                        <th>Correo electrónico</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha registro / acceso</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Renderizado dinámicamente desde usuarios.js -->
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

    <!-- MODAL CREAR / EDITAR USUARIO -->
    <div class="modal-backdrop" id="userFormModal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-labelledby="userModalTitle" aria-modal="true">
            <header class="modal-header">
                <div class="modal-title-wrap">
                    <i class="fa-solid fa-user-pen modal-title-icon" id="userModalIcon"></i>
                    <h2 id="userModalTitle">Crear usuario</h2>
                </div>
                <button type="button" class="modal-close-btn" data-close-modal="userFormModal" aria-label="Cerrar modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <form id="userForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="userIdInput">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userNameInput">Nombre completo <span class="required">*</span></label>
                            <input type="text" id="userNameInput" class="form-control" placeholder="Ej. María López" required>
                            <small class="error-msg" id="nameError"></small>
                        </div>

                        <div class="form-group">
                            <label for="userEmailInput">Correo electrónico <span class="required">*</span></label>
                            <input type="email" id="userEmailInput" class="form-control" placeholder="Ej. maria@bio-gest.com" required>
                            <small class="error-msg" id="emailError"></small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userRoleInput">Rol en el sistema <span class="required">*</span></label>
                            <div class="select-wrapper">
                                <select id="userRoleInput" class="form-select" required>
                                    <option value="">Seleccione un rol...</option>
                                    <option value="Administrador">Administrador</option>
                                    <option value="Vendedor">Vendedor</option>
                                    <option value="Cliente">Cliente</option>
                                    <option value="Inventario">Inventario</option>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                            <small class="error-msg" id="roleError"></small>
                        </div>

                        <div class="form-group">
                            <label for="userStatusInput">Estado de cuenta <span class="required">*</span></label>
                            <div class="select-wrapper">
                                <select id="userStatusInput" class="form-select" required>
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="userPhoneInput">Teléfono de contacto</label>
                            <input type="tel" id="userPhoneInput" class="form-control" placeholder="Ej. 3104567890">
                        </div>

                        <div class="form-group">
                            <label for="userNotesInput">Perfil de uso / Área</label>
                            <div class="select-wrapper">
                                <select id="userNotesInput" class="form-select">
                                    <option value="Alimentación continua">Alimentación continua</option>
                                    <option value="Inicio de criadero">Inicio de criadero</option>
                                    <option value="Proyecto comercial / Gran escala">Proyecto comercial / Gran escala</option>
                                    <option value="Mascota única">Mascota única</option>
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
                        <i class="fa-solid fa-floppy-disk"></i> Guardar usuario
                    </button>
                </footer>
            </form>
        </div>
    </div>

    <!-- MODAL VER DETALLE DE USUARIO -->
    <div class="modal-backdrop" id="userDetailModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-sm" role="dialog" aria-labelledby="detailModalTitle" aria-modal="true">
            <header class="modal-header">
                <div class="modal-title-wrap">
                    <i class="fa-solid fa-id-card modal-title-icon"></i>
                    <h2 id="detailModalTitle">Ficha del Usuario</h2>
                </div>
                <button type="button" class="modal-close-btn" data-close-modal="userDetailModal" aria-label="Cerrar detalle">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </header>

            <div class="modal-body">
                <div class="user-profile-header">
                    <div class="detail-avatar" id="detailAvatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="detail-headline">
                        <span class="detail-id-code" id="detailId">USR-001</span>
                        <h3 id="detailName">María López</h3>
                        <p id="detailEmail">maria@bio-gest.com</p>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Rol asignado</span>
                        <div id="detailRoleBadge"></div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Estado actual</span>
                        <div id="detailStatusBadge"></div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Fecha registro / acceso</span>
                        <strong id="detailLastAccess">-</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Teléfono</span>
                        <strong id="detailPhone">-</strong>
                    </div>
                    <div class="detail-item detail-item-full">
                        <span class="detail-label">Perfil de uso / Área</span>
                        <strong id="detailNotes">-</strong>
                    </div>
                </div>
            </div>

            <footer class="modal-footer">
                <button type="button" class="btn-filter-secondary" data-close-modal="userDetailModal">
                    Cerrar
                </button>
                <button type="button" class="tg-btn-gold" id="btnEditFromDetail">
                    <i class="fa-solid fa-pen"></i> Editar datos
                </button>
            </footer>
        </div>
    </div>

    <div class="toast-container" id="toastContainer" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        window.BIOGEST_USERS_DATA = @json($usuariosIniciales ?? []);
        window.BIOGEST_USERS_API = {
            baseUrl: "{{ url('/admin/usuarios') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('assets/js/usuarios.js') }}"></script>
@endpush
