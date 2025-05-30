/**
 * JavaScript específico para la gestión de roles
 */

// Variables globales específicas de roles
let rolesTable;
let permissionsCache = null;

/**
 * Inicialización específica de roles
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar funcionalidades específicas de roles si estamos en la página correcta
    if (window.location.pathname.includes('/roles')) {
        initializeRolesPage();
    }
});

/**
 * Inicializa la página de roles
 */
function initializeRolesPage() {
    // Precargar permisos para mejorar rendimiento
    preloadPermissions();
    
    // Configurar eventos específicos de roles
    setupRoleEvents();
}

/**
 * Precarga los permisos disponibles
 */
async function preloadPermissions() {
    try {
        const response = await fetch(`${APP_URL}/api/permissions`);
        const data = await response.json();
        
        if (data.status === 'success') {
            permissionsCache = data.data;
            console.log('Permisos precargados:', permissionsCache.length);
        }
    } catch (error) {
        console.warn('No se pudieron precargar los permisos:', error);
    }
}

/**
 * Configura eventos específicos de roles
 */
function setupRoleEvents() {
    // Event delegation para manejar checkboxes de permisos dinámicamente
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('permission-checkbox')) {
            handlePermissionChange(e.target);
        }
    });
    
    // Manejar búsqueda en permisos
    document.addEventListener('input', function(e) {
        if (e.target.id === 'permissionSearch') {
            filterPermissions(e.target.value);
        }
    });
}

/**
 * Maneja cambios en checkboxes de permisos
 */
function handlePermissionChange(checkbox) {
    const permissionItem = checkbox.closest('.permission-item');
    const permissionLabel = permissionItem.querySelector('.permission-label');
    
    // Añadir efecto visual al seleccionar/deseleccionar
    if (checkbox.checked) {
        permissionLabel.style.background = 'rgba(59, 130, 246, 0.1)';
        permissionLabel.style.borderLeft = '3px solid var(--btn-primary)';
    } else {
        permissionLabel.style.background = '';
        permissionLabel.style.borderLeft = '';
    }
    
    // Actualizar contador de permisos seleccionados
    updatePermissionsCounter();
}

/**
 * Actualiza el contador de permisos seleccionados
 */
function updatePermissionsCounter() {
    const modal = document.querySelector('.base-modal.show');
    if (!modal) return;
    
    const checkboxes = modal.querySelectorAll('.permission-checkbox');
    const checkedCount = modal.querySelectorAll('.permission-checkbox:checked').length;
    const totalCount = checkboxes.length;
    
    // Buscar o crear contador
    let counter = modal.querySelector('.permissions-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.className = 'permissions-counter';
        const permissionsTitle = modal.querySelector('.permissions-title');
        if (permissionsTitle) {
            permissionsTitle.appendChild(counter);
        }
    }
    
    counter.innerHTML = `<span class="counter-badge">${checkedCount}/${totalCount}</span>`;
}

/**
 * Filtra permisos por texto de búsqueda
 */
function filterPermissions(searchText) {
    const modal = document.querySelector('.base-modal.show');
    if (!modal) return;
    
    const permissionItems = modal.querySelectorAll('.permission-item');
    const searchLower = searchText.toLowerCase();
    
    permissionItems.forEach(item => {
        const permissionName = item.querySelector('.permission-name')?.textContent || '';
        const permissionDesc = item.querySelector('.permission-description')?.textContent || '';
        
        const matches = permissionName.toLowerCase().includes(searchLower) ||
                       permissionDesc.toLowerCase().includes(searchLower);
        
        item.style.display = matches ? '' : 'none';
    });
    
    // Mostrar/ocultar categorías vacías
    const categories = modal.querySelectorAll('.permission-category');
    categories.forEach(category => {
        const visibleItems = category.querySelectorAll('.permission-item:not([style*="display: none"])');
        category.style.display = visibleItems.length > 0 ? '' : 'none';
    });
}

/**
 * Funciones específicas para modales de roles
 */

/**
 * Crear nuevo rol
 */
function crearRol() {
    const createModal = createModal('createRole', {
        title: 'Crear Nuevo Rol',
        size: 'large',
        endpoint: `${APP_URL}/api/roles`,
        onShow: async (modal) => {
            if (permissionsCache) {
                // Usar permisos precargados
                const templateData = TEMPLATE_HELPERS.processCreateRoleData(permissionsCache);
                modal.updateContent(templateData);
                setupPermissionsInteractions(modal);
            } else {
                // Cargar permisos si no están en cache
                modal.showLoading('Cargando permisos disponibles...');
                
                try {
                    const response = await fetch(`${APP_URL}/api/permissions`);
                    const permissionsData = await response.json();

                    if (permissionsData.status === 'success') {
                        permissionsCache = permissionsData.data; // Guardar en cache
                        const templateData = TEMPLATE_HELPERS.processCreateRoleData(permissionsData.data);
                        modal.updateContent(templateData);
                        setupPermissionsInteractions(modal);
                    } else {
                        modal.showError('No se pudieron cargar los permisos');
                    }
                } catch (error) {
                    console.error('Error cargando permisos:', error);
                    modal.showError('Error al conectar con el servidor');
                }
            }
        }
    });

    createModal.show();
}

/**
 * Editar rol existente
 */
function editarRol(rolId) {
    const editModal = createModal('editRole', {
        title: 'Editar Rol',
        size: 'large',
        endpoint: `${APP_URL}/api/roles/${rolId}`,
        onShow: async (modal) => {
            modal.showLoading('Cargando información del rol...');

            try {
                // Cargar datos en paralelo para mejor rendimiento
                const promises = [
                    fetch(`${APP_URL}/api/roles/${rolId}`),
                    permissionsCache ? Promise.resolve({json: () => ({status: 'success', data: permissionsCache})}) : fetch(`${APP_URL}/api/permissions`),
                    fetch(`${APP_URL}/api/roles/${rolId}/permissions`)
                ];

                const [roleResponse, permissionsResponse, rolePermissionsResponse] = await Promise.all(promises);

                const roleData = await roleResponse.json();
                const permissionsData = await permissionsResponse.json();
                const rolePermissionsData = await rolePermissionsResponse.json();

                if (roleData.status === 'success' && permissionsData.status === 'success') {
                    // Actualizar cache si es necesario
                    if (!permissionsCache) {
                        permissionsCache = permissionsData.data;
                    }
                    
                    const templateData = TEMPLATE_HELPERS.processEditRoleData(
                        roleData.data, 
                        permissionsData.data, 
                        rolePermissionsData.data || []
                    );
                    modal.updateContent(templateData);
                    setupPermissionsInteractions(modal);
                    
                    // Actualizar endpoint para incluir permisos
                    modal.config.endpoint = `${APP_URL}/api/roles/${rolId}`;
                    
                    // Configurar envío de permisos
                    setupPermissionsSubmission(modal, rolId);
                } else {
                    modal.showError('No se pudieron cargar los datos del rol');
                }
            } catch (error) {
                console.error('Error cargando datos del rol:', error);
                modal.showError('Error al conectar con el servidor');
            }
        }
    });

    editModal.show();
}

/**
 * Configura las interacciones dentro del modal de permisos
 */
function setupPermissionsInteractions(modal) {
    const modalElement = modal.modal;
    
    // Agregar buscador de permisos
    addPermissionsSearch(modalElement);
    
    // Agregar botones de selección masiva
    addBulkSelectionButtons(modalElement);
    
    // Inicializar contador
    updatePermissionsCounter();
    
    // Configurar scroll suave para categorías
    setupCategoryNavigation(modalElement);
}

/**
 * Agrega buscador de permisos al modal
 */
function addPermissionsSearch(modalElement) {
    const permissionsTitle = modalElement.querySelector('.permissions-title');
    if (!permissionsTitle) return;
    
    const searchContainer = document.createElement('div');
    searchContainer.className = 'permissions-search-container';
    searchContainer.innerHTML = `
        <div class="search-input-container">
            <input type="text" id="permissionSearch" class="permission-search-input" 
                   placeholder="Buscar permisos...">
            <svg class="search-input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </div>
    `;
    
    permissionsTitle.parentNode.insertBefore(searchContainer, permissionsTitle.nextSibling);
}

/**
 * Agrega botones de selección masiva
 */
function addBulkSelectionButtons(modalElement) {
    const permissionsGrid = modalElement.querySelector('.permissions-grid');
    if (!permissionsGrid) return;
    
    const bulkButtons = document.createElement('div');
    bulkButtons.className = 'bulk-selection-buttons';
    bulkButtons.innerHTML = `
        <button type="button" class="bulk-btn" onclick="selectAllPermissions()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9,11 12,14 22,4"></polyline>
                <path d="M21,12v7a2,2 0 0,1 -2,2H5a2,2 0 0,1 -2,-2V5a2,2 0 0,1 2,-2h11"></path>
            </svg>
            Seleccionar todos
        </button>
        <button type="button" class="bulk-btn" onclick="deselectAllPermissions()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            </svg>
            Deseleccionar todos
        </button>
    `;
    
    permissionsGrid.parentNode.insertBefore(bulkButtons, permissionsGrid);
}

/**
 * Configura navegación por categorías
 */
function setupCategoryNavigation(modalElement) {
    const categories = modalElement.querySelectorAll('.category-title');
    
    categories.forEach(categoryTitle => {
        categoryTitle.style.cursor = 'pointer';
        categoryTitle.addEventListener('click', function() {
            const categoryPermissions = this.nextElementSibling;
            const isCollapsed = categoryPermissions.style.display === 'none';
            
            categoryPermissions.style.display = isCollapsed ? '' : 'none';
            
            // Rotar icono si existe
            const icon = this.querySelector('svg');
            if (icon) {
                icon.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        });
    });
}

/**
 * Configura el envío de permisos junto con datos del rol
 */
function setupPermissionsSubmission(modal, rolId) {
    const form = modal.modal.querySelector('form');
    if (!form) return;
    
    // Interceptar el submit para enviar permisos por separado
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            // Deshabilitar botón
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
            
            // Enviar datos del rol
            const roleResponse = await fetch(`${APP_URL}/api/roles/${rolId}`, {
                method: 'POST',
                body: formData
            });
            
            const roleResult = await roleResponse.json();
            
            if (roleResult.status === 'success') {
                // Enviar permisos por separado
                const permissionsFormData = new FormData();
                const selectedPermissions = form.querySelectorAll('.permission-checkbox:checked');
                
                selectedPermissions.forEach(checkbox => {
                    permissionsFormData.append('permisos[]', checkbox.value);
                });
                
                const permissionsResponse = await fetch(`${APP_URL}/api/roles/${rolId}/permissions`, {
                    method: 'POST',
                    body: permissionsFormData
                });
                
                const permissionsResult = await permissionsResponse.json();
                
                if (permissionsResult.status === 'success') {
                    // Cerrar modal
                    modal.hide();
                    
                    // Mostrar éxito
                    await CustomDialog.success(
                        'Rol Actualizado',
                        'El rol y sus permisos se actualizaron correctamente'
                    );
                    
                    // Recargar tabla
                    if (typeof loadData === 'function') {
                        loadData();
                    }
                } else {
                    throw new Error(permissionsResult.message || 'Error al actualizar permisos');
                }
            } else {
                throw new Error(roleResult.message || 'Error al actualizar rol');
            }
        } catch (error) {
            console.error('Error:', error);
            CustomDialog.error('Error', error.message || 'Error al actualizar el rol');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
}

/**
 * Funciones de utilidad para permisos
 */

/**
 * Selecciona todos los permisos visibles
 */
function selectAllPermissions() {
    const modal = document.querySelector('.base-modal.show');
    if (!modal) return;
    
    const visibleCheckboxes = modal.querySelectorAll('.permission-checkbox:not([style*="display: none"])');
    visibleCheckboxes.forEach(checkbox => {
        if (!checkbox.checked) {
            checkbox.checked = true;
            handlePermissionChange(checkbox);
        }
    });
    
    CustomDialog.toast(`${visibleCheckboxes.length} permisos seleccionados`, 'success', 2000);
}

/**
 * Deselecciona todos los permisos
 */
function deselectAllPermissions() {
    const modal = document.querySelector('.base-modal.show');
    if (!modal) return;
    
    const checkboxes = modal.querySelectorAll('.permission-checkbox:checked');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        handlePermissionChange(checkbox);
    });
    
    CustomDialog.toast('Todos los permisos deseleccionados', 'info', 2000);
}

/**
 * Ver detalles completos de un rol
 */
function verDetallesRol(rolId) {
    cerrarTodosLosMenus();
    
    const detailsModal = createModal('roleDetails', {
        title: 'Detalles del Rol',
        size: 'large',
        closable: true,
        onShow: async (modal) => {
            modal.showLoading('Cargando información del rol...');

            try {
                const [roleResponse, permissionsResponse] = await Promise.all([
                    fetch(`${APP_URL}/api/roles/${rolId}`),
                    fetch(`${APP_URL}/api/roles/${rolId}/permissions`)
                ]);

                const roleData = await roleResponse.json();
                const permissionsData = await permissionsResponse.json();

                if (roleData.status === 'success') {
                    // Agregar permisos a los datos del rol
                    roleData.data.permisos_asignados = permissionsData.data || [];
                    
                    const templateData = TEMPLATE_HELPERS.processRoleDetailsData(roleData.data);
                    modal.updateContent(templateData);
                } else {
                    modal.showError('No se pudo cargar la información del rol');
                }
            } catch (error) {
                console.error('Error al cargar detalles del rol:', error);
                modal.showError('Error al conectar con el servidor');
            }
        }
    });

    detailsModal.show();
}

/**
 * Gestionar permisos (alias para editar rol)
 */
function gestionarPermisos(rolId) {
    cerrarTodosLosMenus();
    editarRol(rolId);
}

// Exportar funciones globales
window.crearRol = crearRol;
window.editarRol = editarRol;
window.verDetallesRol = verDetallesRol;
window.gestionarPermisos = gestionarPermisos;
window.selectAllPermissions = selectAllPermissions;
window.deselectAllPermissions = deselectAllPermissions;