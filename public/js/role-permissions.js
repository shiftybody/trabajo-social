// public/js/role-permissions.js

(function () {
  // Variables globales
  let allPermissions = [];
  let currentPermissions = []; // Array de IDs de permisos seleccionados
  let originalPermissions = [];
  let roleId = null;
  let roleName = "";
  let hasChanges = false;
  let managePermissions = {}; // Mapa de permisos manage por categoría

  // Elementos DOM
  const dom = {
    roleName: document.getElementById("role-name"),
    totalPermissions: document.getElementById("total-permissions"),
    assignedPermissions: document.getElementById("assigned-permissions"),
    usersCount: document.getElementById("users-count"),
    permissionsGrid: document.getElementById("permissions-grid"),
    searchInput: document.getElementById("permissions-search"),
    selectedCount: document.getElementById("selected-count"),
    saveButton: document.getElementById("save-permissions"),
    changesIndicator: document.getElementById("changes-indicator"),
  };

  // Inicialización
  document.addEventListener("DOMContentLoaded", function () {
    // Obtener el ID del rol desde la URL
    const pathParts = window.location.pathname.split("/");
    const roleIndex = pathParts.indexOf("roles");
    if (roleIndex !== -1 && pathParts[roleIndex + 1]) {
      roleId = pathParts[roleIndex + 1];
      if (pathParts[roleIndex + 2] === "permissions") {
        loadRoleData();
      }
    } else {
      showError("No se pudo identificar el rol");
    }

    // Configurar event listeners
    setupEventListeners();
  });

  // Configurar event listeners
  function setupEventListeners() {
    // Búsqueda
    if (dom.searchInput) {
      dom.searchInput.addEventListener("input", debounce(handleSearch, 300));
    }

    // Botón guardar
    if (dom.saveButton) {
      dom.saveButton.addEventListener("click", savePermissions);
    }

    // Delegación de eventos para checkboxes
    if (dom.permissionsGrid) {
      dom.permissionsGrid.addEventListener("change", function (e) {
        if (e.target.type === "checkbox" && e.target.name === "permisos[]") {
          handlePermissionChange(e.target);
        }
      });

      // Click en categorías para expandir/colapsar
      dom.permissionsGrid.addEventListener("click", function (e) {
        const categoryHeader = e.target.closest(".category-header");
        if (categoryHeader) {
          toggleCategory(categoryHeader);
        }
      });
    }
  }

  // Cargar datos del rol
  async function loadRoleData() {
    try {
      showLoading("Cargando información del rol...");

      // Cargar información del rol
      const roleResponse = await fetch(`${APP_URL}/api/roles/${roleId}`);
      const roleData = await roleResponse.json();

      if (roleData.status !== "success") {
        throw new Error("Error al cargar información del rol");
      }

      // Actualizar información del rol
      roleName = roleData.data.rol_descripcion;
      dom.roleName.textContent = roleName;
      dom.usersCount.textContent = roleData.data.usuarios_count || 0;

      // Cargar todos los permisos disponibles
      const permissionsResponse = await fetch(`${APP_URL}/api/permissions`);
      const permissionsData = await permissionsResponse.json();

      if (permissionsData.status !== "success") {
        throw new Error("Error al cargar permisos disponibles");
      }

      allPermissions = permissionsData.data;
      dom.totalPermissions.textContent = allPermissions.length;

      // Identificar permisos "manage"
      identifyManagePermissions();

      // Cargar permisos asignados al rol
      const rolePermissionsResponse = await fetch(
        `${APP_URL}/api/roles/${roleId}/permissions`
      );
      const rolePermissionsData = await rolePermissionsResponse.json();

      if (rolePermissionsData.status !== "success") {
        throw new Error("Error al cargar permisos del rol");
      }

      // IMPORTANTE: Convertir a números los IDs de permisos
      currentPermissions = rolePermissionsData.data.map((p) =>
        parseInt(p.permiso_id)
      );
      originalPermissions = [...currentPermissions];

      // Actualizar contadores
      dom.assignedPermissions.textContent = currentPermissions.length;
      dom.selectedCount.textContent = currentPermissions.length;

      // Renderizar permisos
      renderPermissions();
    } catch (error) {
      console.error("Error:", error);
      showError("Error al cargar los datos. Por favor, recarga la página.");
    }
  }

  // Identificar permisos "manage" por categoría
  function identifyManagePermissions() {
    managePermissions = {};

    allPermissions.forEach((permission) => {
      const category = permission.permiso_slug.split(".")[0];

      // Si el slug contiene "manage"
      if (permission.permiso_slug.includes("manage")) {
        if (!managePermissions[category]) {
          managePermissions[category] = [];
        }
        managePermissions[category].push(parseInt(permission.permiso_id));
      }
    });
  }

  // Obtener todos los permisos de una categoría
  function getCategoryPermissions(category) {
    return allPermissions
      .filter(
        (permission) => permission.permiso_slug.split(".")[0] === category
      )
      .map((permission) => parseInt(permission.permiso_id));
  }

  // Verificar si un permiso es "manage"
  function isManagePermission(permissionId) {
    for (const category in managePermissions) {
      if (managePermissions[category].includes(permissionId)) {
        return category;
      }
    }
    return null;
  }

  // Renderizar permisos agrupados por categoría
  function renderPermissions() {
    if (!allPermissions || allPermissions.length === 0) {
      dom.permissionsGrid.innerHTML =
        '<div class="no-permissions">No hay permisos disponibles</div>';
      return;
    }

    // Agrupar permisos por categoría
    const groupedPermissions = groupPermissionsByCategory(allPermissions);

    // Crear HTML
    let html = "";
    Object.entries(groupedPermissions).forEach(([category, permissions]) => {
      html += createCategoryHTML(category, permissions);
    });

    dom.permissionsGrid.innerHTML = html;
  }

  // Agrupar permisos por categoría
  function groupPermissionsByCategory(permissions) {
    const grouped = {};

    permissions.forEach((permission) => {
      const category = permission.permiso_slug.split(".")[0] || "otros";
      if (!grouped[category]) {
        grouped[category] = [];
      }
      grouped[category].push(permission);
    });

    // Ordenar categorías
    const sortedGrouped = {};
    Object.keys(grouped)
      .sort()
      .forEach((key) => {
        sortedGrouped[key] = grouped[key];
      });

    return sortedGrouped;
  }

  // Crear HTML para una categoría
  function createCategoryHTML(category, permissions) {
    const categoryName = getCategoryDisplayName(category);
    const categoryId = `category-${category}`;

    let html = `
      <div class="permission-category" data-category="${category}">
        <div class="category-header" data-category-id="${categoryId}">
          <h5 class="category-title">${categoryName.toUpperCase()}</h5>
          <svg class="category-toggle" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </div>
        <div class="category-permissions" id="${categoryId}">
    `;

    permissions.forEach((permission) => {
      // IMPORTANTE: Convertir a número para la comparación
      const permissionId = parseInt(permission.permiso_id);
      const isChecked = currentPermissions.includes(permissionId);
      html += createPermissionHTML(permission, isChecked);
    });

    html += `
        </div>
      </div>
    `;

    return html;
  }

  // Crear HTML para un permiso
  function createPermissionHTML(permission, isChecked) {
    const isManage = permission.permiso_slug.includes("manage");

    return `
      <div class="permission-item" data-permission-id="${
        permission.permiso_id
      }">
        <label class="permission-label ${isChecked ? "selected" : ""} ${
      isManage ? "manage-permission" : ""
    }">
          <input type="checkbox" 
                 name="permisos[]" 
                 value="${permission.permiso_id}" 
                 ${isChecked ? "checked" : ""} 
                 class="permission-checkbox"
                 data-permission-name="${permission.permiso_nombre.toLowerCase()}"
                 data-permission-description="${(
                   permission.permiso_descripcion || ""
                 ).toLowerCase()}"
                 data-permission-slug="${permission.permiso_slug}">
          <div class="permission-details">
            <span class="permission-name">
              ${permission.permiso_nombre}
              ${
                isManage
                  ? '<span class="manage-badge">GESTIÓN COMPLETA</span>'
                  : ""
              }
            </span>
            ${
              permission.permiso_descripcion
                ? `<span class="permission-description">${permission.permiso_descripcion}</span>`
                : ""
            }
          </div>
        </label>
      </div>
    `;
  }

  // Obtener nombre de categoría para mostrar
  function getCategoryDisplayName(category) {
    const categoryNames = {
      users: "Usuarios",
      roles: "Roles",
      permissions: "Permisos",
      reports: "Reportes",
      settings: "Configuración",
      system: "Sistema",
    };

    return (
      categoryNames[category] ||
      category.charAt(0).toUpperCase() + category.slice(1)
    );
  }

  // Manejar cambio de permiso
  function handlePermissionChange(checkbox) {
    const permissionId = parseInt(checkbox.value);
    const label = checkbox.closest(".permission-label");
    const permissionSlug = checkbox.dataset.permissionSlug;

    if (checkbox.checked) {
      // Agregar permiso si no existe
      if (!currentPermissions.includes(permissionId)) {
        currentPermissions.push(permissionId);
      }
      label.classList.add("selected");

      // Si es un permiso "manage", seleccionar todos los permisos de la categoría
      if (permissionSlug && permissionSlug.includes("manage")) {
        const category = permissionSlug.split(".")[0];
        const categoryPermissions = getCategoryPermissions(category);

        let addedCount = 0;
        categoryPermissions.forEach((catPermId) => {
          if (!currentPermissions.includes(catPermId)) {
            currentPermissions.push(catPermId);
            // Actualizar visualmente el checkbox
            const catCheckbox = dom.permissionsGrid.querySelector(
              `input[value="${catPermId}"]`
            );
            if (catCheckbox && !catCheckbox.checked) {
              catCheckbox.checked = true;
              catCheckbox
                .closest(".permission-label")
                .classList.add("selected");
              addedCount++;
            }
          }
        });

        if (addedCount > 0) {
          CustomDialog.toast(
            `Se seleccionaron automáticamente ${addedCount} permisos adicionales de ${getCategoryDisplayName(
              category
            )}`,
            "info",
            3000
          );
        }
      }
    } else {
      // Remover permiso
      const index = currentPermissions.indexOf(permissionId);
      if (index > -1) {
        currentPermissions.splice(index, 1);
      }
      label.classList.remove("selected");

      // Si es un permiso "manage" que se deselecciona, mantener los otros permisos seleccionados
      if (permissionSlug && permissionSlug.includes("manage")) {
        CustomDialog.toast(
          "Los permisos individuales se mantienen seleccionados",
          "info",
          2000
        );
      }
    }

    // Actualizar UI
    checkForChanges();
    updateSelectedCount();
  }

  // Verificar si hay cambios
  function checkForChanges() {
    // Ordenar ambos arrays para comparar
    const sortedCurrent = [...currentPermissions].sort((a, b) => a - b);
    const sortedOriginal = [...originalPermissions].sort((a, b) => a - b);

    // Comparar arrays
    hasChanges =
      sortedCurrent.length !== sortedOriginal.length ||
      !sortedCurrent.every((val, index) => val === sortedOriginal[index]);

    // Actualizar UI
    dom.saveButton.disabled = !hasChanges;
    dom.changesIndicator.style.display = hasChanges ? "flex" : "none";
  }

  // Actualizar contador de seleccionados
  function updateSelectedCount() {
    const count = currentPermissions.length;
    dom.selectedCount.textContent = count;
    dom.assignedPermissions.textContent = count;
  }

  // Búsqueda de permisos
  function handleSearch() {
    const searchTerm = dom.searchInput.value.toLowerCase().trim();
    const permissionItems =
      dom.permissionsGrid.querySelectorAll(".permission-item");
    const categories = dom.permissionsGrid.querySelectorAll(
      ".permission-category"
    );

    if (!searchTerm) {
      // Mostrar todos si no hay búsqueda
      permissionItems.forEach((item) => (item.style.display = ""));
      categories.forEach((category) => {
        category.style.display = "";
        const permissions = category.querySelector(".category-permissions");
        if (permissions) permissions.style.display = "";
      });
      return;
    }

    // Ocultar todas las categorías inicialmente
    categories.forEach((category) => {
      category.style.display = "none";
    });

    // Filtrar permisos
    permissionItems.forEach((item) => {
      const checkbox = item.querySelector(".permission-checkbox");
      const name = checkbox.dataset.permissionName || "";
      const description = checkbox.dataset.permissionDescription || "";

      const matches =
        name.includes(searchTerm) || description.includes(searchTerm);

      if (matches) {
        item.style.display = "";
        // Mostrar la categoría padre
        const parentCategory = item.closest(".permission-category");
        if (parentCategory) {
          parentCategory.style.display = "";
          const permissions = parentCategory.querySelector(
            ".category-permissions"
          );
          if (permissions) permissions.style.display = "";
        }
      } else {
        item.style.display = "none";
      }
    });

    // Verificar categorías vacías después del filtrado
    categories.forEach((category) => {
      if (category.style.display !== "none") {
        const visibleItems = category.querySelectorAll(
          '.permission-item:not([style*="display: none"])'
        );
        if (visibleItems.length === 0) {
          category.style.display = "none";
        }
      }
    });
  }

  // Seleccionar todos los permisos visibles
  window.selectAllVisiblePermissions = function () {
    const visibleCheckboxes = dom.permissionsGrid.querySelectorAll(
      '.permission-item:not([style*="display: none"]) .permission-checkbox'
    );

    let addedCount = 0;
    visibleCheckboxes.forEach((checkbox) => {
      if (!checkbox.checked) {
        checkbox.checked = true;
        handlePermissionChange(checkbox);
        addedCount++;
      }
    });

    if (addedCount > 0) {
      CustomDialog.toast(`${addedCount} permisos agregados`, "success", 2000);
    } else {
      CustomDialog.toast(
        "Todos los permisos visibles ya estaban seleccionados",
        "info",
        2000
      );
    }
  };

  // Deseleccionar todos los permisos
  window.deselectAllPermissions = function () {
    const checkboxes = dom.permissionsGrid.querySelectorAll(
      ".permission-checkbox:checked"
    );

    if (checkboxes.length === 0) {
      CustomDialog.toast("No hay permisos seleccionados", "info", 2000);
      return;
    }

    checkboxes.forEach((checkbox) => {
      checkbox.checked = false;
      handlePermissionChange(checkbox);
    });

    CustomDialog.toast("Todos los permisos deseleccionados", "info", 2000);
  };

  // Expandir/colapsar categoría
  function toggleCategory(header) {
    const categoryId = header.dataset.categoryId;
    const permissions = document.getElementById(categoryId);
    const toggle = header.querySelector(".category-toggle");

    if (permissions) {
      const isHidden = permissions.style.display === "none";
      permissions.style.display = isHidden ? "" : "none";
      header.classList.toggle("collapsed", !isHidden);
    }
  }

  // Guardar permisos
  async function savePermissions() {
    if (!hasChanges) return;

    const confirmSave = await CustomDialog.confirm(
      "Guardar Permisos",
      `¿Está seguro de que desea actualizar los permisos del rol "${roleName}"?`,
      "Guardar",
      "Cancelar"
    );

    if (!confirmSave) return;

    try {
      dom.saveButton.disabled = true;
      dom.saveButton.innerHTML =
        '<span class="spinner-small"></span> Guardando...';

      // Preparar datos para enviar
      const formData = new FormData();
      currentPermissions.forEach((permissionId) => {
        formData.append("permisos[]", permissionId);
      });

      const response = await fetch(
        `${APP_URL}/api/roles/${roleId}/permissions`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: formData,
        }
      );

      const data = await response.json();

      if (data.status === "success") {
        // Actualizar permisos originales
        originalPermissions = [...currentPermissions];
        hasChanges = false;
        checkForChanges();

        await CustomDialog.success(
          "Permisos Actualizados",
          data.message || "Los permisos del rol se actualizaron correctamente"
        );

        window.location.href = `${APP_URL}/roles`;
      } else {
        throw new Error(data.message || "Error al actualizar permisos");
      }
    } catch (error) {
      console.error("Error al guardar:", error);
      CustomDialog.error(
        "Error",
        error.message ||
          "No se pudieron guardar los permisos. Por favor, inténtalo de nuevo."
      );
    } finally {
      dom.saveButton.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17,21 17,13 7,13 7,21"></polyline>
          <polyline points="7,3 7,8 15,8"></polyline>
        </svg>
        Guardar Cambios
      `;
      checkForChanges();
    }
  }

  // Mostrar loading
  function showLoading(message) {
    dom.permissionsGrid.innerHTML = `
      <div class="permissions-loading">
        <div class="permissions-spinner"></div>
        <span>${message}</span>
      </div>
    `;
  }

  // Mostrar error
  function showError(message) {
    dom.permissionsGrid.innerHTML = `
      <div class="permissions-error">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <p>${message}</p>
      </div>
    `;
  }

  // Utilidad debounce
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Agregar estilos CSS para el spinner y badge de manage
  const style = document.createElement("style");
  style.textContent = `
    .spinner-small {
      display: inline-block;
      width: 14px;
      height: 14px;
      border: 2px solid #ffffff;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
    }
    
    .permissions-error {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      color: var(--modal-error);
    }
    
    .permissions-error svg {
      margin-bottom: 1rem;
    }
    
    .permissions-error p {
      font-size: 16px;
      text-align: center;
      margin: 0;
    }
    
    .manage-badge {
      display: inline-block;
      padding: 2px 8px;
      background: var(--btn-primary);
      color: white;
      font-size: 10px;
      font-weight: 600;
      border-radius: 4px;
      margin-left: 8px;
      letter-spacing: 0.5px;
    }
    
    .permission-label.manage-permission {
      background-color: rgba(59, 130, 246, 0.03);
      border-left: 3px solid var(--btn-primary);
    }
    
    .permission-label.manage-permission:hover {
      background-color: rgba(59, 130, 246, 0.08);
    }
  `;
  document.head.appendChild(style);
})();
