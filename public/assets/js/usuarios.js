/**
 * ============================================================================
 * MÓDULO DE GESTIÓN DE USUARIOS - TENEBRIOS GOLD (BIO-GEST ADMIN)
 * - Botón "Ver" abre ficha completa (datos personales, direcciones y resumen de pedidos)
 * - Botón "+ Nuevo Administrador" crea cuenta con id_rol = 1 (Administrador)
 * - Botón "Activar / Desactivar" cambia estado en MySQL sin errores 404/405/500
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
    const btnEmptyReset = document.getElementById('btnEmptyReset');
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

    // Inicializar tabla y contadores
    updateCounts();
    renderTable();

    // Event Listeners de Filtros
    if (searchInput) searchInput.addEventListener('input', renderTable);
    if (roleFilter) roleFilter.addEventListener('change', renderTable);
    if (statusFilter) statusFilter.addEventListener('change', renderTable);

    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (roleFilter) roleFilter.value = 'Todos';
        if (statusFilter) statusFilter.value = 'Todos';
        renderTable();
    }

    if (btnResetFilters) {
        btnResetFilters.addEventListener('click', () => {
            resetFilters();
            showToast('Filtros restablecidos.', 'info');
        });
    }
    if (btnEmptyReset) {
        btnEmptyReset.addEventListener('click', resetFilters);
    }

    // Abrir Modal "+ Nuevo Administrador"
    if (btnOpenCreateModal) {
        btnOpenCreateModal.addEventListener('click', () => {
            if (userForm) userForm.reset();
            clearErrors();
            openModal(userFormModal);
            document.getElementById('userNameInput')?.focus();
        });
    }

    // Enviar Formulario "+ Nuevo Administrador" (POST /admin/usuarios)
    if (userForm) {
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearErrors();

            const nombre = document.getElementById('userNameInput')?.value.trim() || '';
            const correo = document.getElementById('userEmailInput')?.value.trim() || '';
            const contrasena = document.getElementById('userPasswordInput')?.value || '';
            const telefono = document.getElementById('userPhoneInput')?.value.trim() || '';
            const estado = document.getElementById('userStatusInput')?.value || 'Activo';

            let hasError = false;
            if (!nombre) {
                showFieldError('nameError', 'El nombre completo del nuevo administrador es obligatorio.');
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
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
            }

            try {
                const response = await fetch(apiConfig.baseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': apiConfig.csrfToken
                    },
                    body: JSON.stringify({
                        nombre,
                        correo,
                        contrasena,
                        telefono,
                        estado,
                        id_rol: 1
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.ok) {
                    if (data.errors) {
                        if (data.errors.nombre) showFieldError('nameError', data.errors.nombre[0]);
                        if (data.errors.correo) showFieldError('emailError', data.errors.correo[0]);
                        if (data.errors.contrasena) showFieldError('passwordError', data.errors.contrasena[0]);
                    }
                    throw new Error(data.message || 'Verifica los campos del formulario.');
                }

                users.unshift(data.usuario);
                closeModal(userFormModal);
                userForm.reset();
                updateCounts();
                renderTable();
                showToast(data.message || 'Administrador creado exitosamente.', 'success');
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Registrar Administrador';
                }
            }
        });
    }

    // Cerrar Modales por botón, clic en fondo o tecla Escape
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-close-modal');
            const targetModal = document.getElementById(modalId);
            if (targetModal) closeModal(targetModal);
        });
    });

    [userFormModal, userDetailModal].forEach(modalEl => {
        if (!modalEl) return;
        modalEl.addEventListener('click', (e) => {
            if (e.target === modalEl) closeModal(modalEl);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(userFormModal);
            closeModal(userDetailModal);
        }
    });

    // Alternar Estado desde la Ficha Detallada
    if (btnToggleStatusFromModal) {
        btnToggleStatusFromModal.addEventListener('click', async () => {
            if (!currentDetailUserId) return;
            await toggleUserStatus(currentDetailUserId);
            await showUserDetail(currentDetailUserId);
        });
    }

    // Delegación de eventos en la tabla para "Ver" y "Activar / Desactivar"
    if (usersTableBody) {
        usersTableBody.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.btn-view-detail');
            if (viewBtn) {
                const id = parseInt(viewBtn.getAttribute('data-id'), 10);
                if (!isNaN(id)) showUserDetail(id);
                return;
            }

            const statusBtn = e.target.closest('.btn-status-toggle');
            if (statusBtn) {
                const id = parseInt(statusBtn.getAttribute('data-id'), 10);
                if (!isNaN(id)) toggleUserStatus(id);
            }
        });
    }

    // Renderizado de la tabla
    function renderTable() {
        if (!usersTableBody) return;

        const q = (searchInput?.value || '').toLowerCase().trim();
        const role = roleFilter?.value || 'Todos';
        const status = statusFilter?.value || 'Todos';

        const filtered = users.filter(u => {
            const matchesQuery = !q ||
                (u.nombre && u.nombre.toLowerCase().includes(q)) ||
                (u.nombre_pila && u.nombre_pila.toLowerCase().includes(q)) ||
                (u.apellido && u.apellido.toLowerCase().includes(q)) ||
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
                <tr data-user-row="${user.id_usuario}">
                    <td style="font-weight:700; color:#4b5563;">${escapeHtml(user.id)}</td>
                    <td>
                        <div style="font-weight:700; color:#111111;">${escapeHtml(user.nombre)}</div>
                        <div style="font-size:0.78rem; color:#6b7280;">${escapeHtml(user.notas || 'Alimentación continua')}</div>
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
                                title="Ver ficha completa de la cuenta"
                            >
                                <i class="fa-solid fa-eye" style="color:#e6a100;"></i> Ver
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
    }

    // Poblar interfaz del modal de detalle con un objeto usuario
    function populateDetailModal(u, addresses, orders, resumen) {
        const detailAvatar = document.getElementById('detailAvatar');
        const detailId = document.getElementById('detailId');
        const detailName = document.getElementById('detailName');
        const detailFirstName = document.getElementById('detailFirstName');
        const detailLastName = document.getElementById('detailLastName');
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
        const summaryTotalOrders = document.getElementById('summaryTotalOrders');
        const summaryTotalSpent = document.getElementById('summaryTotalSpent');
        const summaryLastOrder = document.getElementById('summaryLastOrder');
        const notice = document.getElementById('detailAccountStatusNotice');

        if (detailAvatar) detailAvatar.textContent = getInitials(u.nombre);
        if (detailId) detailId.textContent = u.id || `USR-${u.id_usuario}`;
        if (detailName) detailName.textContent = u.nombre || '-';
        if (detailFirstName) detailFirstName.textContent = u.nombre_pila || (u.nombre ? u.nombre.split(' ')[0] : '-');
        if (detailLastName) detailLastName.textContent = u.apellido || '-';
        if (detailEmail) detailEmail.textContent = u.correo || '-';
        if (detailPhone) detailPhone.textContent = u.telefono || 'No registrado';
        if (detailRegistered) detailRegistered.textContent = u.fecha_registro || '-';
        if (detailLastAccess) detailLastAccess.textContent = u.ultimoAcceso || u.fecha_registro || '-';
        if (detailPerfil) detailPerfil.textContent = u.perfil_uso || u.notas || 'Alimentación continua';

        const isAdmin = u.rol === 'Administrador';
        if (detailRoleBadge) {
            detailRoleBadge.innerHTML = isAdmin
                ? `<span class="role-badge-admin"><i class="fa-solid fa-shield-halved"></i> Administrador</span>`
                : `<span class="role-badge-cliente"><i class="fa-solid fa-user"></i> Cliente</span>`;
        }

        const isActivo = u.estado === 'Activo';
        if (detailStatusBadge) {
            detailStatusBadge.innerHTML = isActivo
                ? `<span class="status-badge status-active"><i class="fa-solid fa-circle-check"></i> Activo</span>`
                : `<span class="status-badge status-inactive"><i class="fa-solid fa-circle-xmark"></i> Inactivo</span>`;
        }

        if (notice) {
            notice.innerHTML = isActivo
                ? '<span style="color:#059669;"><i class="fa-solid fa-check-circle"></i> Cuenta habilitada con acceso al sistema</span>'
                : '<span style="color:#dc2626;"><i class="fa-solid fa-ban"></i> Cuenta inactiva / acceso bloqueado</span>';
        }

        if (btnToggleStatusFromModal) {
            btnToggleStatusFromModal.className = `user-action-btn ${isActivo ? 'btn-status-toggle is-active' : 'btn-status-toggle is-inactive'}`;
            btnToggleStatusFromModal.innerHTML = `<i class="fa-solid ${isActivo ? 'fa-toggle-on' : 'fa-toggle-off'}"></i> ${isActivo ? 'Desactivar Cuenta' : 'Activar Cuenta'}`;
        }

        // Direcciones
        const addrArray = Array.isArray(addresses) ? addresses : [];
        if (addressCount) addressCount.textContent = addrArray.length;
        if (addressList) {
            if (addrArray.length === 0) {
                addressList.innerHTML = '<p style="color:#9ca3af; font-size:0.84rem; font-style:italic;">No hay direcciones registradas para esta cuenta.</p>';
            } else {
                addressList.innerHTML = addrArray.map(d => `
                    <div class="address-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <strong style="color:#111111;"><i class="fa-solid fa-map-pin" style="color:#e6a100;"></i> ${escapeHtml(d.nombre_ubicacion || 'Principal')}</strong>
                            ${d.es_principal ? '<span style="background:#e0f2fe; color:#0369a1; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:999px;">Principal</span>' : ''}
                        </div>
                        <div style="color:#334155;">${escapeHtml(d.direccion)}</div>
                        <div style="color:#64748b; font-size:0.8rem;">${escapeHtml(d.ciudad)} · ${escapeHtml(d.departamento)} (CP: ${escapeHtml(d.codigo_postal || 'N/A')})</div>
                    </div>
                `).join('');
            }
        }

        // Resumen y lista de pedidos
        const ordArray = Array.isArray(orders) ? orders : [];
        if (orderCount) orderCount.textContent = ordArray.length;

        const totalSpentFormatted = resumen?.total_comprado_formateado ||
            ('$' + ordArray.filter(o => (o.estado_pedido || o.estado) !== 'Cancelado')
                .reduce((acc, o) => acc + (parseFloat(o.total) || 0), 0)
                .toLocaleString('es-CO'));

        if (summaryTotalOrders) summaryTotalOrders.textContent = resumen?.total_pedidos ?? ordArray.length;
        if (summaryTotalSpent) summaryTotalSpent.textContent = totalSpentFormatted;
        if (summaryLastOrder) {
            summaryLastOrder.textContent = ordArray.length > 0
                ? `${ordArray[0].codigo} (${ordArray[0].fecha})`
                : 'Sin pedidos';
        }

        if (orderList) {
            if (ordArray.length === 0) {
                orderList.innerHTML = '<p style="color:#9ca3af; font-size:0.84rem; font-style:italic;">No se han registrado compras o pedidos con esta cuenta.</p>';
            } else {
                orderList.innerHTML = ordArray.map(p => {
                    const est = p.estado_pedido || p.estado || 'Pendiente';
                    let statusColor = '#1e40af';
                    let statusBg = '#dbeafe';
                    if (est === 'Entregado' || est === 'Completado') { statusColor = '#059669'; statusBg = '#ecfdf5'; }
                    else if (est === 'Cancelado') { statusColor = '#dc2626'; statusBg = '#fef2f2'; }
                    else if (est === 'Despachado') { statusColor = '#7c3aed'; statusBg = '#f5f3ff'; }
                    else if (est === 'Pendiente') { statusColor = '#92400e'; statusBg = '#fef3c7'; }

                    return `
                        <div class="order-item-card">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <strong style="color:#111111;">${escapeHtml(p.codigo)} <span style="font-weight:normal; color:#64748b; font-size:0.78rem;">(${escapeHtml(p.metodo_pago || 'Contraentrega')})</span></strong>
                                <span class="order-badge-pill" style="background:${statusBg}; color:${statusColor};">
                                    ${escapeHtml(est)}
                                </span>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; color:#64748b; font-size:0.8rem;">
                                <span><i class="fa-regular fa-calendar"></i> ${escapeHtml(p.fecha)} · ${p.items_count ?? 1} unidad(es)</span>
                                <strong style="color:#111111; font-size:0.88rem;">${escapeHtml(p.total_formateado)}</strong>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    // Mostrar detalle de cuenta (puebla de inmediato con datos locales y refresca vía GET /admin/usuarios/{id})
    async function showUserDetail(idUsuario) {
        currentDetailUserId = idUsuario;
        openModal(userDetailModal);

        // 1. Mostrar inmediatamente datos pre-cargados si existen
        const cachedUser = users.find(u => Number(u.id_usuario) === Number(idUsuario));
        if (cachedUser) {
            populateDetailModal(
                cachedUser,
                cachedUser.direcciones || [],
                cachedUser.pedidos || [],
                cachedUser.resumen_pedidos || null
            );
        }

        // 2. Consultar datos frescos en tiempo real desde GET /admin/usuarios/{id}
        try {
            const response = await fetch(`${apiConfig.baseUrl}/${idUsuario}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('No fue posible actualizar la ficha del usuario.');

            const data = await response.json();
            if (data && data.ok && data.usuario) {
                const idx = users.findIndex(u => Number(u.id_usuario) === Number(idUsuario));
                if (idx !== -1) users[idx] = data.usuario;

                populateDetailModal(
                    data.usuario,
                    data.direcciones || data.usuario.direcciones || [],
                    data.pedidos || data.usuario.pedidos || [],
                    data.resumen_pedidos || data.usuario.resumen_pedidos || null
                );
            }
        } catch (err) {
            if (!cachedUser) {
                showToast(err.message, 'error');
            }
        }
    }

    // Alternar Estado Activo / Inactivo en MySQL
    async function toggleUserStatus(idUsuario) {
        try {
            const response = await fetch(`${apiConfig.baseUrl}/${idUsuario}/estado`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': apiConfig.csrfToken
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'No fue posible actualizar el estado de la cuenta.');
            }

            const idx = users.findIndex(u => Number(u.id_usuario) === Number(idUsuario));
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
        modal.classList.add('open', 'active');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('open', 'active');
        modal.setAttribute('aria-hidden', 'true');
    }

    function clearErrors() {
        document.querySelectorAll('.error-msg').forEach(el => {
            el.textContent = '';
        });
    }

    function showFieldError(id, msg) {
        const el = document.getElementById(id);
        if (el) el.textContent = msg;
    }

    function getInitials(fullName) {
        const parts = (fullName || '').trim().split(/\s+/);
        if (!parts.length || !parts[0]) return 'U';
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast-item toast-msg toast-${type}`;
        const icon = type === 'success'
            ? 'fa-circle-check'
            : (type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info');

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
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
