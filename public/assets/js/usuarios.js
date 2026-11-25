/**
 * ============================================================================
 * MÓDULO DE GESTIÓN DE USUARIOS - TENEBRIOS GOLD (BIO-GEST ADMIN)
 * Solo roles Administrador y Cliente (HU-02 / RF-002)
 * Reglas de negocio:
 * 1. Clientes se registran por autoservicio en tienda.
 * 2. Administrador SOLO puede: Crear Nuevo Administrador, Consultar Ficha y Activar/Desactivar.
 * 3. Prohibido editar datos personales de terceros o eliminar usuarios.
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    const apiConfig = window.BIOGEST_USERS_API || {
        baseUrl: '/admin/usuarios',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    };

    let users = Array.isArray(window.BIOGEST_USERS_DATA) ? [...window.BIOGEST_USERS_DATA] : [];
    let currentDetailUserId = null;

    // Elementos del DOM
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const btnResetFilters = document.getElementById('btnResetFilters');
    const usersTableBody = document.getElementById('usersTableBody');
    const visibleCount = document.getElementById('visibleCount');
    const totalCount = document.getElementById('totalCount');
    const emptyState = document.getElementById('emptyState');

    // Modales
    const userFormModal = document.getElementById('userFormModal');
    const userDetailModal = document.getElementById('userDetailModal');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const userForm = document.getElementById('userForm');
    const btnToggleStatusFromModal = document.getElementById('btnToggleStatusFromModal');

    // Inicializar
    updateCounts();
    renderTable();

    // Event Listeners de Filtros
    if (searchInput) searchInput.addEventListener('input', renderTable);
    if (roleFilter) roleFilter.addEventListener('change', renderTable);
    if (statusFilter) statusFilter.addEventListener('change', renderTable);
    if (btnResetFilters) {
        btnResetFilters.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (roleFilter) roleFilter.value = 'Todos';
            if (statusFilter) statusFilter.value = 'Todos';
            renderTable();
            showToast('Filtros restablecidos.', 'info');
        });
    }

    // Modal Crear Administrador
    if (btnOpenCreateModal) {
        btnOpenCreateModal.addEventListener('click', () => {
            userForm.reset();
            clearErrors();
            openModal(userFormModal);
        });
    }

    // Submit Crear Administrador
    if (userForm) {
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors();

            const nombre = document.getElementById('userNameInput').value.trim();
            const correo = document.getElementById('userEmailInput').value.trim();
            const contrasena = document.getElementById('userPasswordInput').value;
            const telefono = document.getElementById('userPhoneInput').value.trim();
            const estado = document.getElementById('userStatusInput').value;

            let hasError = false;
            if (!nombre) {
                showFieldError('nameError', 'El nombre completo es obligatorio.');
                hasError = true;
            }
            if (!correo) {
                showFieldError('emailError', 'El correo corporativo es obligatorio.');
                hasError = true;
            }
            if (!contrasena || contrasena.length < 6) {
                showFieldError('passwordError', 'La contraseña debe tener mínimo 6 caracteres.');
                hasError = true;
            }
            if (hasError) return;

            const submitBtn = document.getElementById('btnSaveUser');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

            try {
                const response = await fetch(apiConfig.baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': apiConfig.csrfToken
                    },
                    body: JSON.stringify({
                        nombre,
                        correo,
                        contrasena,
                        telefono,
                        estado
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Error al crear el nuevo administrador.');
                }

                users.unshift(data.usuario);
                closeModal(userFormModal);
                userForm.reset();
                updateCounts();
                renderTable();
                showToast(data.message, 'success');
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Registrar Administrador';
            }
        });
    }

    // Cerrar Modales
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-close-modal');
            const targetModal = document.getElementById(modalId);
            if (targetModal) closeModal(targetModal);
        });
    });

    // Alternar Estado desde la Ficha Detallada
    if (btnToggleStatusFromModal) {
        btnToggleStatusFromModal.addEventListener('click', async () => {
            if (!currentDetailUserId) return;
            await toggleUserStatus(currentDetailUserId);
            // Recargar ficha
            await showUserDetail(currentDetailUserId);
        });
    }

    // Función Render Table
    function renderTable() {
        const q = (searchInput?.value || '').toLowerCase().trim();
        const role = roleFilter?.value || 'Todos';
        const status = statusFilter?.value || 'Todos';

        const filtered = users.filter(u => {
            const matchesQuery = !q ||
                (u.nombre && u.nombre.toLowerCase().includes(q)) ||
                (u.correo && u.correo.toLowerCase().includes(q)) ||
                (u.id && u.id.toLowerCase().includes(q));

            const matchesRole = (role === 'Todos') || (u.rol === role);
            const matchesStatus = (status === 'Todos') || (u.estado === status);

            return matchesQuery && matchesRole && matchesStatus;
        });

        if (visibleCount) visibleCount.textContent = filtered.length;

        if (filtered.length === 0) {
            usersTableBody.innerHTML = '';
            if (emptyState) emptyState.hidden = false;
            return;
        }

        if (emptyState) emptyState.hidden = true;

        usersTableBody.innerHTML = filtered.map(user => {
            const isActivo = user.estado === 'Activo';
            const isAdmin = user.rol === 'Administrador';

            const roleBadge = isAdmin
                ? `<span class="role-badge-admin"><i class="fa-solid fa-shield-halved"></i> Administrador</span>`
                : `<span class="role-badge-cliente"><i class="fa-solid fa-user"></i> Cliente</span>`;

            const statusBadge = isActivo
                ? `<span class="status-badge status-active"><i class="fa-solid fa-circle-check"></i> Activo</span>`
                : `<span class="status-badge status-inactive"><i class="fa-solid fa-circle-xmark"></i> Inactivo</span>`;

            return `
                <tr>
                    <td style="font-weight:700; color:#4b5563;">${escapeHtml(user.id)}</td>
                    <td>
                        <div style="font-weight:700; color:#111111;">${escapeHtml(user.nombre)}</div>
                        <div style="font-size:0.78rem; color:#6b7280;">${escapeHtml(user.notas || 'Cliente regular')}</div>
                    </td>
                    <td style="color:#1e293b;">${escapeHtml(user.correo)}</td>
                    <td>${roleBadge}</td>
                    <td>${statusBadge}</td>
                    <td style="font-size:0.82rem; color:#6b7280;">${escapeHtml(user.ultimoAcceso || user.fecha_registro || '-')}</td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex; gap:8px;">
                            <button
                                type="button"
                                class="user-action-btn btn-view-detail"
                                data-id="${user.id_usuario}"
                                title="Consultar ficha de cuenta"
                            >
                                <i class="fa-solid fa-id-card" style="color:#e6a100;"></i> Ver Ficha
                            </button>
                            <button
                                type="button"
                                class="user-action-btn btn-status-toggle ${isActivo ? 'is-active' : 'is-inactive'}"
                                data-id="${user.id_usuario}"
                                title="${isActivo ? 'Desactivar cuenta' : 'Activar cuenta'}"
                            >
                                <i class="fa-solid ${isActivo ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                                <span>${isActivo ? 'Desactivar' : 'Activar'}</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Eventos a botones de la tabla
        usersTableBody.querySelectorAll('.btn-view-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                showUserDetail(id);
            });
        });

        usersTableBody.querySelectorAll('.btn-status-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                toggleUserStatus(id);
            });
        });
    }

    // Consulta detallada de información de cualquier cuenta (Req 3b)
    async function showUserDetail(idUsuario) {
        currentDetailUserId = idUsuario;
        openModal(userDetailModal);

        // Referencias
        const detailId = document.getElementById('detailId');
        const detailName = document.getElementById('detailName');
        const detailEmail = document.getElementById('detailEmail');
        const detailPhone = document.getElementById('detailPhone');
        const detailRegistered = document.getElementById('detailRegistered');
        const detailLastAccess = document.getElementById('detailLastAccess');
        const detailPerfil = document.getElementById('detailPerfil');
        const detailRoleBadge = document.getElementById('detailRoleBadgeContainer');
        const detailStatusBadge = document.getElementById('detailStatusBadgeContainer');
        const addressCount = document.getElementById('addressCount');
        const addressList = document.getElementById('addressListContainer');
        const orderCount = document.getElementById('orderCount');
        const orderList = document.getElementById('orderListContainer');
        const notice = document.getElementById('detailAccountStatusNotice');

        addressList.innerHTML = '<p style="color:#6b7280; font-size:0.84rem; font-style:italic;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando información detallada...</p>';
        orderList.innerHTML = '<p style="color:#6b7280; font-size:0.84rem; font-style:italic;"><i class="fa-solid fa-spinner fa-spin"></i> Cargando pedidos...</p>';

        try {
            const response = await fetch(`${apiConfig.baseUrl}/${idUsuario}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('No fue posible consultar la ficha del usuario.');

            const data = await response.json();
            const u = data.usuario;
            const addresses = data.direcciones || [];
            const orders = data.pedidos || [];

            detailId.textContent = u.id;
            detailName.textContent = u.nombre;
            detailEmail.textContent = u.correo;
            detailPhone.textContent = u.telefono;
            detailRegistered.textContent = u.fecha_registro || '-';
            detailLastAccess.textContent = u.ultimoAcceso || '-';
            detailPerfil.textContent = u.notas || 'Cliente general';

            const isAdmin = u.rol === 'Administrador';
            detailRoleBadge.innerHTML = isAdmin
                ? `<span class="role-badge-admin"><i class="fa-solid fa-shield-halved"></i> Administrador</span>`
                : `<span class="role-badge-cliente"><i class="fa-solid fa-user"></i> Cliente</span>`;

            const isActivo = u.estado === 'Activo';
            detailStatusBadge.innerHTML = isActivo
                ? `<span class="status-badge status-active"><i class="fa-solid fa-circle-check"></i> Activo</span>`
                : `<span class="status-badge status-inactive"><i class="fa-solid fa-circle-xmark"></i> Inactivo</span>`;

            if (notice) {
                notice.innerHTML = isActivo
                    ? '<span style="color:#059669;"><i class="fa-solid fa-check-circle"></i> Cuenta habilitada con acceso al sistema</span>'
                    : '<span style="color:#dc2626;"><i class="fa-solid fa-ban"></i> Cuenta bloqueada temporalmente</span>';
            }

            if (btnToggleStatusFromModal) {
                btnToggleStatusFromModal.className = `user-action-btn ${isActivo ? 'btn-status-toggle is-active' : 'btn-status-toggle is-inactive'}`;
                btnToggleStatusFromModal.innerHTML = `<i class="fa-solid ${isActivo ? 'fa-toggle-on' : 'fa-toggle-off'}"></i> ${isActivo ? 'Desactivar Cuenta' : 'Activar Cuenta'}`;
            }

            // Renderizar Direcciones
            addressCount.textContent = addresses.length;
            if (addresses.length === 0) {
                addressList.innerHTML = '<p style="color:#9ca3af; font-size:0.84rem; font-style:italic;">No hay direcciones registradas para este usuario.</p>';
            } else {
                addressList.innerHTML = addresses.map(d => `
                    <div class="address-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <strong style="color:#111111;"><i class="fa-solid fa-map-pin" style="color:#e6a100;"></i> ${escapeHtml(d.nombre_ubicacion)}</strong>
                            ${d.es_principal ? '<span style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px;">Principal</span>' : ''}
                        </div>
                        <div style="color:#334155;">${escapeHtml(d.direccion)}</div>
                        <div style="color:#64748b; font-size:0.8rem;">${escapeHtml(d.ciudad)} · ${escapeHtml(d.departamento)} (CP: ${escapeHtml(d.codigo_postal || 'N/A')})</div>
                    </div>
                `).join('');
            }

            // Renderizar Pedidos
            orderCount.textContent = orders.length;
            if (orders.length === 0) {
                orderList.innerHTML = '<p style="color:#9ca3af; font-size:0.84rem; font-style:italic;">No se han registrado compras o pedidos con esta cuenta.</p>';
            } else {
                orderList.innerHTML = orders.map(p => {
                    let statusColor = '#3b82f6';
                    let statusBg = '#eff6ff';
                    if (p.estado === 'Entregado') { statusColor = '#059669'; statusBg = '#ecfdf5'; }
                    else if (p.estado === 'Cancelado') { statusColor = '#dc2626'; statusBg = '#fef2f2'; }
                    else if (p.estado === 'Enviado') { statusColor = '#8b5cf6'; statusBg = '#f5f3ff'; }

                    return `
                        <div class="order-item-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <strong style="color:#111111;">${escapeHtml(p.codigo)}</strong>
                                <span class="order-badge-pill" style="background:${statusBg}; color:${statusColor};">
                                    ${escapeHtml(p.estado)}
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; color:#64748b; font-size:0.8rem;">
                                <span><i class="fa-regular fa-calendar"></i> ${escapeHtml(p.fecha)} · ${p.items_count} producto(s)</span>
                                <strong style="color:#111111; font-size:0.88rem;">${escapeHtml(p.total_formateado)}</strong>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        } catch (err) {
            addressList.innerHTML = `<p style="color:#dc2626; font-size:0.84rem;">${escapeHtml(err.message)}</p>`;
            orderList.innerHTML = '';
        }
    }

    // Alternar Estado Activo / Inactivo en MySQL (Req 3c)
    async function toggleUserStatus(idUsuario) {
        try {
            const response = await fetch(`${apiConfig.baseUrl}/${idUsuario}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': apiConfig.csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible actualizar el estado de la cuenta.');
            }

            // Actualizar arreglo local
            const idx = users.findIndex(u => u.id_usuario === idUsuario);
            if (idx !== -1) {
                users[idx] = data.usuario;
            }

            renderTable();
            showToast(data.message, 'success');
        } catch (err) {
            showToast(err.message, 'error');
        }
    }

    function updateCounts() {
        if (totalCount) totalCount.textContent = users.length;
    }

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    }

    function clearErrors() {
        document.querySelectorAll('.error-msg').forEach(el => el.textContent = '');
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) el.textContent = msg;
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast-msg toast-${type}`;
        const icon = type === 'success' ? 'fa-circle-check' : (type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info');

        toast.innerHTML = `
            <i class="fa-solid ${icon}"></i>
            <span>${escapeHtml(message)}</span>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
