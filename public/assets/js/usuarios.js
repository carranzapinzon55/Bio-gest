/**
 * ============================================================================
 * MÓDULO DE GESTIÓN DE USUARIOS - TENEBRIOS GOLD (BIO-GEST ADMIN)
 * Lógica de renderizado dinámico, filtrado en tiempo real, sincronización
 * con base de datos MySQL (Laravel) + fallback mock data para vista standalone.
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. DATOS DE RESPALDO (SOLO PARA APERTURA DIRECTA DE usuarios.html SIN BACKEND)
    const fallbackMockUsers = [
        {
            id_usuario: 1,
            id: 'USR-001',
            nombre: 'María López',
            correo: 'maria@bio-gest.com',
            rol: 'Administrador',
            estado: 'Activo',
            ultimoAcceso: '25/08/2025 10:24 a. m.',
            telefono: '+57 310 452 1901',
            notas: 'Proyecto comercial / Gran escala'
        },
        {
            id_usuario: 2,
            id: 'USR-002',
            nombre: 'Carlos Ramírez',
            correo: 'carlos@bio-gest.com',
            rol: 'Vendedor',
            estado: 'Activo',
            ultimoAcceso: '25/08/2025 09:15 a. m.',
            telefono: '+57 315 890 3341',
            notas: 'Alimentación continua'
        },
        {
            id_usuario: 3,
            id: 'USR-003',
            nombre: 'Laura Torres',
            correo: 'laura@bio-gest.com',
            rol: 'Cliente',
            estado: 'Activo',
            ultimoAcceso: '24/08/2025 06:42 p. m.',
            telefono: '+57 300 712 4588',
            notas: 'Inicio de criadero'
        },
        {
            id_usuario: 4,
            id: 'USR-004',
            nombre: 'Andrés Gómez',
            correo: 'andres@bio-gest.com',
            rol: 'Inventario',
            estado: 'Activo',
            ultimoAcceso: '24/08/2025 04:37 p. m.',
            telefono: '+57 318 654 9023',
            notas: 'Proyecto comercial / Gran escala'
        },
        {
            id_usuario: 5,
            id: 'USR-005',
            nombre: 'Sofía Rojas',
            correo: 'sofia@bio-gest.com',
            rol: 'Cliente',
            estado: 'Inactivo',
            ultimoAcceso: '20/08/2025 11:12 a. m.',
            telefono: '+57 311 234 5567',
            notas: 'Mascota única'
        }
    ];

    // Detectar si estamos conectados al backend Laravel con datos reales de MySQL
    const hasLiveBackend = Array.isArray(window.BIOGEST_USERS_DATA) && Boolean(window.BIOGEST_USERS_API);
    let users = hasLiveBackend ? [...window.BIOGEST_USERS_DATA] : [...fallbackMockUsers];
    let selectedUserIds = new Set();
    let currentDetailUserId = null;

    // 2. REFERENCIAS AL DOM
    const usersTableBody = document.getElementById('usersTableBody');
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const btnResetFilters = document.getElementById('btnResetFilters');
    const filterBtnText = document.getElementById('filterBtnText');
    const btnEmptyReset = document.getElementById('btnEmptyReset');
    const emptyState = document.getElementById('emptyState');
    const visibleCountEl = document.getElementById('visibleCount');
    const totalCountEl = document.getElementById('totalCount');

    // Selección múltiple
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkSelectionInfo = document.getElementById('bulkSelectionInfo');
    const selectedCountText = document.getElementById('selectedCountText');
    const btnBulkToggleStatus = document.getElementById('btnBulkToggleStatus');

    // Modal Crear / Editar
    const userFormModal = document.getElementById('userFormModal');
    const userModalTitle = document.getElementById('userModalTitle');
    const userForm = document.getElementById('userForm');
    const userIdInput = document.getElementById('userIdInput');
    const userNameInput = document.getElementById('userNameInput');
    const userEmailInput = document.getElementById('userEmailInput');
    const userRoleInput = document.getElementById('userRoleInput');
    const userStatusInput = document.getElementById('userStatusInput');
    const userPhoneInput = document.getElementById('userPhoneInput');
    const userNotesInput = document.getElementById('userNotesInput');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnSaveUser = document.getElementById('btnSaveUser');

    // Mensajes de error del formulario
    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const roleError = document.getElementById('roleError');

    // Modal Detalle
    const userDetailModal = document.getElementById('userDetailModal');
    const detailAvatar = document.getElementById('detailAvatar');
    const detailId = document.getElementById('detailId');
    const detailName = document.getElementById('detailName');
    const detailEmail = document.getElementById('detailEmail');
    const detailRoleBadge = document.getElementById('detailRoleBadge');
    const detailStatusBadge = document.getElementById('detailStatusBadge');
    const detailLastAccess = document.getElementById('detailLastAccess');
    const detailPhone = document.getElementById('detailPhone');
    const detailNotes = document.getElementById('detailNotes');
    const btnEditFromDetail = document.getElementById('btnEditFromDetail');

    // Sidebar Móvil
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const adminSidebar = document.getElementById('adminSidebar');

    // 3. FUNCIONES AUXILIARES
    function getInitials(fullName) {
        const parts = (fullName || '').trim().split(/\s+/);
        if (parts.length === 0 || !parts[0]) return 'U';
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function formatNowDate() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'p. m.' : 'a. m.';
        hours = hours % 12 || 12;
        const hoursStr = String(hours).padStart(2, '0');
        return `${day}/${month}/${year} ${hoursStr}:${minutes} ${ampm}`;
    }

    function generateNextId() {
        const maxNum = users.reduce((max, u) => {
            const num = parseInt(String(u.id).replace('USR-', ''), 10);
            return !isNaN(num) && num > max ? num : max;
        }, 0);
        return `USR-${String(maxNum + 1).padStart(3, '0')}`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function showToast(message, iconClass = 'fa-circle-check') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast-item';
        toast.innerHTML = `<i class="fa-solid ${iconClass}"></i><span>${escapeHtml(message)}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all 0.25s ease';
            setTimeout(() => toast.remove(), 250);
        }, 3200);
    }

    async function apiRequest(endpoint, method = 'GET', body = null) {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.BIOGEST_USERS_API.csrfToken
        };

        const options = { method, headers };
        if (body) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }

        const response = await fetch(endpoint, options);
        const data = await response.json();

        if (!response.ok) {
            const errorMessage = data.message || 'Ocurrió un error al procesar la solicitud.';
            const error = new Error(errorMessage);
            error.errors = data.errors || {};
            throw error;
        }

        return data;
    }

    // 4. FILTRADO Y RENDERIZADO DINÁMICO DE LA TABLA
    function getFilteredUsers() {
        const query = searchInput.value.trim().toLowerCase();
        const role = roleFilter.value;
        const status = statusFilter.value;

        const hasActiveFilter = query !== '' || role !== 'Todos' || status !== 'Todos';
        btnResetFilters.classList.toggle('has-active-filters', hasActiveFilter);
        filterBtnText.textContent = hasActiveFilter ? 'Limpiar' : 'Filtros';

        return users.filter(user => {
            const matchesQuery =
                user.nombre.toLowerCase().includes(query) ||
                user.correo.toLowerCase().includes(query) ||
                user.id.toLowerCase().includes(query);

            const matchesRole = role === 'Todos' || user.rol === role;
            const matchesStatus = status === 'Todos' || user.estado === status;

            return matchesQuery && matchesRole && matchesStatus;
        });
    }

    function renderTable() {
        const filtered = getFilteredUsers();
        totalCountEl.textContent = users.length;
        visibleCountEl.textContent = filtered.length;

        if (filtered.length === 0) {
            usersTableBody.innerHTML = '';
            emptyState.hidden = false;
            selectAllCheckbox.checked = false;
            updateBulkToolbar();
            return;
        }

        emptyState.hidden = true;

        usersTableBody.innerHTML = filtered.map(user => {
            const isSelected = selectedUserIds.has(user.id);
            const isActivo = user.estado === 'Activo';
            const statusClass = isActivo ? 'status-active' : 'status-inactive';
            const avatarClass = user.rol === 'Administrador' ? 'user-avatar avatar-gold' : 'user-avatar';

            return `
                <tr data-user-id="${escapeHtml(user.id)}" class="${isSelected ? 'row-selected' : ''}">
                    <td class="col-checkbox">
                        <input
                            type="checkbox"
                            class="row-checkbox"
                            data-id="${escapeHtml(user.id)}"
                            ${isSelected ? 'checked' : ''}
                            aria-label="Seleccionar ${escapeHtml(user.nombre)}"
                        >
                    </td>
                    <td class="user-id-cell">${escapeHtml(user.id)}</td>
                    <td>
                        <div class="user-name-cell">
                            <span class="${avatarClass}" title="${escapeHtml(user.nombre)}">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <span class="user-fullname">${escapeHtml(user.nombre)}</span>
                        </div>
                    </td>
                    <td class="user-email-cell">${escapeHtml(user.correo)}</td>
                    <td>
                        <span class="role-badge">${escapeHtml(user.rol)}</span>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">${escapeHtml(user.estado)}</span>
                    </td>
                    <td class="user-access-cell">${escapeHtml(user.ultimoAcceso)}</td>
                    <td class="col-actions">
                        <div class="actions-group">
                            <button
                                type="button"
                                class="action-btn btn-edit"
                                data-id="${escapeHtml(user.id)}"
                                title="Editar usuario"
                                aria-label="Editar ${escapeHtml(user.nombre)}"
                            >
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button
                                type="button"
                                class="action-btn btn-view"
                                data-id="${escapeHtml(user.id)}"
                                title="Ver detalle"
                                aria-label="Ver detalle de ${escapeHtml(user.nombre)}"
                            >
                                <i class="fa-regular fa-eye"></i>
                            </button>
                            <div class="dropdown-menu-wrapper">
                                <button
                                    type="button"
                                    class="action-btn btn-more"
                                    data-id="${escapeHtml(user.id)}"
                                    title="Más opciones"
                                    aria-label="Más opciones para ${escapeHtml(user.nombre)}"
                                >
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div class="context-menu" data-menu-for="${escapeHtml(user.id)}">
                                    <button type="button" class="context-menu-item btn-toggle-status" data-id="${escapeHtml(user.id)}">
                                        <i class="fa-solid ${isActivo ? 'fa-user-lock' : 'fa-user-check'}"></i>
                                        ${isActivo ? 'Marcar como Inactivo' : 'Activar usuario'}
                                    </button>
                                    <button type="button" class="context-menu-item btn-edit" data-id="${escapeHtml(user.id)}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Editar información
                                    </button>
                                    <button type="button" class="context-menu-item danger btn-delete-user" data-id="${escapeHtml(user.id)}">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Eliminar registro
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Actualizar estado del checkbox maestro
        const allFilteredSelected = filtered.length > 0 && filtered.every(u => selectedUserIds.has(u.id));
        selectAllCheckbox.checked = allFilteredSelected;
        updateBulkToolbar();
    }

    function updateBulkToolbar() {
        const count = selectedUserIds.size;
        if (count > 0) {
            bulkSelectionInfo.hidden = false;
            selectedCountText.textContent = `${count} ${count === 1 ? 'usuario seleccionado' : 'usuarios seleccionados'}`;
        } else {
            bulkSelectionInfo.hidden = true;
        }
    }

    // 5. GESTIÓN DE MODALES
    function openModal(modalEl) {
        modalEl.classList.add('open');
        modalEl.setAttribute('aria-hidden', 'false');
    }

    function closeModal(modalEl) {
        modalEl.classList.remove('open');
        modalEl.setAttribute('aria-hidden', 'true');
    }

    function clearFormErrors() {
        nameError.textContent = '';
        emailError.textContent = '';
        roleError.textContent = '';
    }

    function openCreateUserModal() {
        clearFormErrors();
        userForm.reset();
        userIdInput.value = '';
        userStatusInput.value = 'Activo';
        userModalTitle.textContent = 'Crear nuevo usuario';
        openModal(userFormModal);
        userNameInput.focus();
    }

    function openEditUserModal(userId) {
        const user = users.find(u => u.id === userId);
        if (!user) return;

        clearFormErrors();
        userIdInput.value = user.id;
        userNameInput.value = user.nombre;
        userEmailInput.value = user.correo;
        userRoleInput.value = user.rol;
        userStatusInput.value = user.estado;
        userPhoneInput.value = user.telefono && user.telefono !== 'No registrado' ? user.telefono : '';

        if (userNotesInput) {
            userNotesInput.value = user.notas || 'Alimentación continua';
        }

        userModalTitle.textContent = `Editar usuario (${user.id})`;
        openModal(userFormModal);
        userNameInput.focus();
    }

    function openDetailUserModal(userId) {
        const user = users.find(u => u.id === userId);
        if (!user) return;

        currentDetailUserId = user.id;
        detailAvatar.textContent = getInitials(user.nombre);
        detailId.textContent = user.id;
        detailName.textContent = user.nombre;
        detailEmail.textContent = user.correo;
        detailRoleBadge.innerHTML = `<span class="role-badge">${escapeHtml(user.rol)}</span>`;

        const statusClass = user.estado === 'Activo' ? 'status-active' : 'status-inactive';
        detailStatusBadge.innerHTML = `<span class="status-badge ${statusClass}">${escapeHtml(user.estado)}</span>`;

        detailLastAccess.textContent = user.ultimoAcceso || 'Sin registro';
        detailPhone.textContent = user.telefono || 'No registrado';
        detailNotes.textContent = user.notas || 'Sin observaciones adicionales';

        openModal(userDetailModal);
    }

    // 6. ACCIONES DE USUARIO (CAMBIAR ESTADO, GUARDAR, ELIMINAR)
    async function toggleUserStatus(userId) {
        const user = users.find(u => u.id === userId);
        if (!user) return;

        if (hasLiveBackend && user.id_usuario) {
            try {
                const res = await apiRequest(
                    `${window.BIOGEST_USERS_API.baseUrl}/${user.id_usuario}/estado`,
                    'PATCH'
                );
                const idx = users.findIndex(u => u.id === userId);
                if (idx !== -1) users[idx] = res.usuario;
                renderTable();
                showToast(
                    res.message,
                    res.usuario.estado === 'Activo' ? 'fa-user-check' : 'fa-user-lock'
                );
            } catch (err) {
                showToast(err.message, 'fa-triangle-exclamation');
            }
            return;
        }

        user.estado = user.estado === 'Activo' ? 'Inactivo' : 'Activo';
        renderTable();
        showToast(
            `Estado de ${user.nombre} actualizado a ${user.estado}`,
            user.estado === 'Activo' ? 'fa-user-check' : 'fa-user-lock'
        );
    }

    async function deleteUser(userId) {
        const user = users.find(u => u.id === userId);
        if (!user) return;

        if (hasLiveBackend && user.id_usuario) {
            try {
                const res = await apiRequest(
                    `${window.BIOGEST_USERS_API.baseUrl}/${user.id_usuario}`,
                    'DELETE'
                );
                if (res.deleted) {
                    users = users.filter(u => u.id !== userId);
                    selectedUserIds.delete(userId);
                } else if (res.usuario) {
                    const idx = users.findIndex(u => u.id === userId);
                    if (idx !== -1) users[idx] = res.usuario;
                }
                renderTable();
                showToast(res.message, 'fa-trash-can');
            } catch (err) {
                showToast(err.message, 'fa-triangle-exclamation');
            }
            return;
        }

        users = users.filter(u => u.id !== userId);
        selectedUserIds.delete(userId);
        renderTable();
        showToast(`Usuario ${user.nombre} (${user.id}) eliminado`, 'fa-trash-can');
    }

    function validateForm() {
        clearFormErrors();
        let isValid = true;

        const nombre = userNameInput.value.trim();
        const correo = userEmailInput.value.trim();
        const rol = userRoleInput.value;
        const editingId = userIdInput.value;

        if (nombre.length < 3) {
            nameError.textContent = 'Ingresa un nombre completo válido (mínimo 3 caracteres).';
            isValid = false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            emailError.textContent = 'Ingresa un correo electrónico válido.';
            isValid = false;
        } else {
            const duplicate = users.some(
                u => u.correo.toLowerCase() === correo.toLowerCase() && u.id !== editingId
            );
            if (duplicate) {
                emailError.textContent = 'Este correo electrónico ya está asignado a otro usuario.';
                isValid = false;
            }
        }

        if (!rol) {
            roleError.textContent = 'Selecciona un rol para el usuario.';
            isValid = false;
        }

        return isValid;
    }

    // 7. EVENT LISTENERS
    searchInput.addEventListener('input', renderTable);
    roleFilter.addEventListener('change', renderTable);
    statusFilter.addEventListener('change', renderTable);

    function resetAllFilters() {
        searchInput.value = '';
        roleFilter.value = 'Todos';
        statusFilter.value = 'Todos';
        renderTable();
    }

    btnResetFilters.addEventListener('click', resetAllFilters);
    btnEmptyReset.addEventListener('click', resetAllFilters);

    btnOpenCreateModal.addEventListener('click', openCreateUserModal);

    userForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (!validateForm()) return;

        const editingId = userIdInput.value;
        const payload = {
            nombre: userNameInput.value.trim(),
            correo: userEmailInput.value.trim().toLowerCase(),
            rol: userRoleInput.value,
            estado: userStatusInput.value,
            telefono: userPhoneInput.value.trim() || null,
            notas: userNotesInput ? userNotesInput.value.trim() : 'Alimentación continua'
        };

        if (hasLiveBackend) {
            if (btnSaveUser) btnSaveUser.disabled = true;
            try {
                if (editingId) {
                    const existing = users.find(u => u.id === editingId);
                    const res = await apiRequest(
                        `${window.BIOGEST_USERS_API.baseUrl}/${existing.id_usuario}`,
                        'PUT',
                        payload
                    );
                    const index = users.findIndex(u => u.id === editingId);
                    if (index !== -1) users[index] = res.usuario;
                    showToast(res.message);
                } else {
                    const res = await apiRequest(
                        window.BIOGEST_USERS_API.baseUrl,
                        'POST',
                        payload
                    );
                    users.unshift(res.usuario);
                    showToast(res.message);
                }
                closeModal(userFormModal);
                renderTable();
            } catch (err) {
                if (err.errors && err.errors.correo) {
                    emailError.textContent = err.errors.correo[0];
                } else {
                    showToast(err.message, 'fa-triangle-exclamation');
                }
            } finally {
                if (btnSaveUser) btnSaveUser.disabled = false;
            }
            return;
        }

        // Modo standalone en memoria
        if (editingId) {
            const index = users.findIndex(u => u.id === editingId);
            if (index !== -1) {
                users[index] = {
                    ...users[index],
                    ...payload,
                    telefono: payload.telefono || 'No registrado'
                };
                showToast(`Datos de ${payload.nombre} actualizados correctamente`);
            }
        } else {
            const newUser = {
                id: generateNextId(),
                ...payload,
                telefono: payload.telefono || 'No registrado',
                ultimoAcceso: formatNowDate()
            };
            users.unshift(newUser);
            showToast(`Usuario ${newUser.nombre} (${newUser.id}) creado con éxito`);
        }

        closeModal(userFormModal);
        renderTable();
    });

    // Delegación de eventos en la tabla de usuarios
    usersTableBody.addEventListener('click', event => {
        const target = event.target;

        if (target.classList.contains('row-checkbox')) {
            const id = target.getAttribute('data-id');
            if (target.checked) {
                selectedUserIds.add(id);
            } else {
                selectedUserIds.delete(id);
            }
            renderTable();
            return;
        }

        const editBtn = target.closest('.btn-edit');
        if (editBtn) {
            const id = editBtn.getAttribute('data-id');
            closeAllContextMenus();
            openEditUserModal(id);
            return;
        }

        const viewBtn = target.closest('.btn-view');
        if (viewBtn) {
            const id = viewBtn.getAttribute('data-id');
            closeAllContextMenus();
            openDetailUserModal(id);
            return;
        }

        const moreBtn = target.closest('.btn-more');
        if (moreBtn) {
            event.stopPropagation();
            const id = moreBtn.getAttribute('data-id');
            const menu = usersTableBody.querySelector(`.context-menu[data-menu-for="${id}"]`);
            const isOpen = menu && menu.classList.contains('open');
            closeAllContextMenus();
            if (menu && !isOpen) {
                menu.classList.add('open');
            }
            return;
        }

        const toggleBtn = target.closest('.btn-toggle-status');
        if (toggleBtn) {
            const id = toggleBtn.getAttribute('data-id');
            closeAllContextMenus();
            toggleUserStatus(id);
            return;
        }

        const deleteBtn = target.closest('.btn-delete-user');
        if (deleteBtn) {
            const id = deleteBtn.getAttribute('data-id');
            closeAllContextMenus();
            deleteUser(id);
        }
    });

    selectAllCheckbox.addEventListener('change', () => {
        const filtered = getFilteredUsers();
        if (selectAllCheckbox.checked) {
            filtered.forEach(u => selectedUserIds.add(u.id));
        } else {
            filtered.forEach(u => selectedUserIds.delete(u.id));
        }
        renderTable();
    });

    btnBulkToggleStatus.addEventListener('click', async () => {
        if (selectedUserIds.size === 0) return;
        const idsToToggle = Array.from(selectedUserIds);

        for (const id of idsToToggle) {
            await toggleUserStatus(id);
        }

        selectedUserIds.clear();
        renderTable();
    });

    btnEditFromDetail.addEventListener('click', () => {
        if (!currentDetailUserId) return;
        closeModal(userDetailModal);
        openEditUserModal(currentDetailUserId);
    });

    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-close-modal');
            const modalEl = document.getElementById(modalId);
            if (modalEl) closeModal(modalEl);
        });
    });

    [userFormModal, userDetailModal].forEach(modalEl => {
        modalEl.addEventListener('click', event => {
            if (event.target === modalEl) {
                closeModal(modalEl);
            }
        });
    });

    function closeAllContextMenus() {
        document.querySelectorAll('.context-menu.open').forEach(menu => {
            menu.classList.remove('open');
        });
    }

    document.addEventListener('click', () => {
        closeAllContextMenus();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeAllContextMenus();
            closeModal(userFormModal);
            closeModal(userDetailModal);
        }
    });

    if (btnToggleSidebar && adminSidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            adminSidebar.classList.toggle('open');
        });
    }

    renderTable();
});
