/**
 * Templates para diferentes tipos de modales
 * Usa sintaxis de template strings con placeholders {{variable}}
 */
const MODAL_TEMPLATES = {
  // Template para formularios generales
  form: `
    <form novalidate class="base-modal-form form-ajax" method="POST" enctype="multipart/form-data">
      {{fields}}
      <div class="base-modal-actions">
        <button class="btn-cancel" data-action="close">{{cancelText}}</button>
        <button type="submit" class="btn-primary" data-action="submit">{{submitText}}</button>
      </div>
    </form>
  `,

  // Template para mostrar detalles de usuario
  userDetails: `
    <div class="user-profile-section">
      <div class="user-avatar-large" style="background-image: url('{{avatarUrl}}')"></div>
      <div class="user-profile-info">
        <h3>{{fullName}}</h3>
        <p class="user-username">@{{username}}</p>
        <div class="user-status-badge {{statusClass}}">
          <span class="status-dot {{statusClass}}"></span>
          {{status}}
        </div>
      </div>
    </div>
 
    <div class="user-details-grid">
      <div class="user-detail-section">
        <h4>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          Información Personal
        </h4>
        {{personalDetails}}
      </div>
 
      <div class="user-detail-section">
        <h4>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
          </svg>
          Información de Cuenta
        </h4>
        {{accountDetails}}
      </div>
 
      <div class="user-detail-section">
        <h4>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12,6 12,12 16,14"></polyline>
          </svg>
          Información del Sistema
        </h4>
        {{systemDetails}}
      </div>
    </div>
  `,

  // Template para cambio de estado
  changeStatus: `
    <div class="user-info-section">
      <div class="user-info-header">
        <div class="user-avatar-status" style="background-image: url('{{avatarUrl}}')"></div>
        <div class="user-basic-info">
          <h3>{{userName}}</h3>
          <p>@{{username}}</p>
        </div>
      </div>
    </div>
 
    <div class="status-change-section">
      <div class="current-status">
        <label class="status-label">Estado actual:</label>
        <span class="status-badge {{currentStatusClass}}">{{currentStatus}}</span>
      </div>
      
      <div class="arrow-change">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14"></path>
          <path d="m12 5 7 7-7 7"></path>
        </svg>
      </div>
      
      <div class="new-status">
        <label class="status-label">Nuevo estado:</label>
        <span class="status-badge {{newStatusClass}}">{{newStatus}}</span>
      </div>
    </div>
 
    <form novalidate id="changeStatusForm" class="base-modal-form form-ajax" method="POST">
      <input type="hidden" name="estado" value="{{newStatusValue}}">
      
      <div class="base-modal-actions">
        <button class="btn-cancel btn-secondary" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">{{submitText}}</button>
      </div>
    </form>
  `,

  // Template específico para reset de contraseña
  resetPassword: `
    <form novalidate id="resetPasswordForm" class="base-modal-form form-ajax" method="POST">
      <div class="password-fields">
        <div class="input-field">
          <label for="newPassword" class="field-label">Nueva contraseña</label>
          <input type="password" name="password" id="newPassword" 
                 class="input-reset" placeholder="Nueva contraseña"
                 pattern="(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" 
                 maxlength="20" required>
        </div>
        
        <div class="input-field">
          <label for="confirmPassword" class="field-label">Confirmar contraseña</label>
          <input type="password" name="password2" id="confirmPassword" 
                 class="input-reset" placeholder="Confirmar contraseña"
                 pattern="(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" 
                 maxlength="20" required>
        </div>
      </div>
      
      <div class="base-modal-actions">
        <button class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">Resetear Contraseña</button>
      </div>
    </form>
  `,

  // Template para confirmación simple
  confirm: `
    <div class="confirm-content">
      <div class="confirm-icon {{iconClass}}">
        {{icon}}
      </div>
      <div class="confirm-message">{{message}}</div>
    </div>
    
    <div class="base-modal-actions">
      <button class="btn-cancel" data-action="close">{{cancelText}}</button>
      <button class="btn-primary" data-action="confirm">{{confirmText}}</button>
    </div>
  `,

  // Template de loading
  loading: `
    <div class="base-modal-loading">
      <div class="spinner"></div>
      <p>{{message}}</p>
    </div>
  `,

  // Template de error
  error: `
    <div class="base-modal-error">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
      <h3>{{title}}</h3>
      <p>{{message}}</p>
      {{retryButton}}
    </div>
  `,

  // Template para detalles de rol
  roleDetails: `
    <div class="role-profile-section">
      <div class="role-icon-large">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
        </svg>
      </div>
      <div class="role-profile-info">
        <h3>{{roleName}}</h3>
        <p class="role-description">Rol del sistema</p>
        <div class="role-status-badge active">
          <span class="status-dot active"></span>
          Activo
        </div>
      </div>
    </div>
 
    <div class="role-details-grid">
      <div class="role-detail-section">
        <h4>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          Información del Rol
        </h4>
        {{roleDetails}}
      </div>
 
      <div class="role-detail-section">
        <h4>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3a6.364 6.364 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
          </svg>
          Permisos Asignados
        </h4>
        <div class="permissions-list">
          {{permissionsList}}
        </div>
      </div>
    </div>
  `,
  
  // Template para editar rol
  createRole: `
    <form novalidate id="createRoleForm" class="base-modal-form form-ajax" method="POST">
      <div class="input-field">
        <label for="roleName" class="field-label">Nombre del Rol</label>
        <input type="text" name="nombre" id="roleName" 
               class="input-reset" placeholder="Ingrese el nombre del rol"
               maxlength="50" required>
      </div>
      
      <div class="input-field">
        <label for="baseRole" class="field-label">Rol Base (Opcional)</label>
        <select name="rol_base" id="baseRole" class="input-reset">
          <option value="">Sin rol base - Crear vacío</option>
          {{baseRolesOptions}}
        </select>
        <small class="field-help">Si selecciona un rol base, se copiarán sus permisos</small>
      </div>
      
      <div class="base-modal-actions">
        <button class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">Crear Rol</button>
      </div>
    </form>
  `,
};

/**
 * Generadores de sub-templates para contenido dinámico
 */
const TEMPLATE_GENERATORS = {
  // Genera campos de detalle de usuario
  generateUserDetailItem: (label, value) => `
    <div class="user-detail-item">
      <span class="user-detail-label">${label}:</span>
      <span class="user-detail-value">${value || "No especificado"}</span>
    </div>
  `,

  // Genera campos de formulario
  generateFormField: (field) => {
    const { type, name, label, placeholder, required, pattern, options } =
      field;

    let input = "";
    switch (type) {
      case "text":
      case "email":
      case "password":
        input = `
          <input type="${type}" name="${name}" id="${name}" 
                 class="input" placeholder="${placeholder || ""}"
                 ${pattern ? `pattern="${pattern}"` : ""}
                 ${required ? "required" : ""}>
        `;
        break;

      case "select":
        const optionsHtml = options
          .map((opt) => `<option value="${opt.value}">${opt.label}</option>`)
          .join("");
        input = `
          <select name="${name}" id="${name}" class="input" ${
          required ? "required" : ""
        }>
            <option value="">Selecciona una opción</option>
            ${optionsHtml}
          </select>
        `;
        break;

      case "textarea":
        input = `
          <textarea name="${name}" id="${name}" class="input" 
                    placeholder="${placeholder || ""}" ${
          required ? "required" : ""
        }></textarea>
        `;
        break;

      case "file":
        input = `
          <input type="file" name="${name}" id="${name}" 
                 class="input input-file" ${required ? "required" : ""}>
        `;
        break;
    }

    return `
      <div class="input-field">
        <label for="${name}" class="field-label">${label}</label>
        ${input}
      </div>
    `;
  },

  // Genera botón de retry
  generateRetryButton: (show = true) => {
    return show
      ? '<button class="btn-retry" data-action="retry">Reintentar</button>'
      : "";
  },
};

/**
 * Funciones helper para procesar templates complejos
 */
const TEMPLATE_HELPERS = {
  processCreateRole: (roles) => {
    const baseRolesOptions = roles
      .map(
        (role) =>
          `<option value="${role.rol_id}">${role.rol_nombre}</option>`
      )
      .join("");

    return {
      baseRolesOptions,
    };
  },

  // Procesa datos de usuario para el template de detalles
  processUserDetailsData: (user) => {
    const avatarUrl =
      user.avatar && user.avatar !== "default.jpg"
        ? `${APP_URL}public/photos/thumbnail/${user.avatar}`
        : `${APP_URL}public/photos/default.jpg`;

    const statusClass = user.estado_id == 1 ? "active" : "inactive";

    return {
      avatarUrl,
      fullName: user.nombre_completo,
      username: user.usuario,
      status: user.estado,
      statusClass,
      personalDetails: [
        ["Nombre", user.nombre],
        ["Apellido Paterno", user.apellido_paterno],
        ["Apellido Materno", user.apellido_materno],
        ["Teléfono", user.telefono],
      ]
        .map(([label, value]) =>
          TEMPLATE_GENERATORS.generateUserDetailItem(label, value)
        )
        .join(""),
      accountDetails: [
        ["Usuario", user.usuario],
        ["Email", user.email],
        ["Rol", user.rol],
        [
          "Estado",
          `<span class="user-status-badge ${statusClass}">
          <span class="status-dot ${statusClass}"></span>
          ${user.estado}
        </span>`,
        ],
      ]
        .map(([label, value]) =>
          TEMPLATE_GENERATORS.generateUserDetailItem(label, value)
        )
        .join(""),
      systemDetails: [
        ["ID", `#${user.id}`],
        ["Creado", user.fecha_creacion],
        ["Modificado", user.ultima_modificacion],
        ["Último acceso", user.ultimo_acceso],
      ]
        .map(([label, value]) =>
          TEMPLATE_GENERATORS.generateUserDetailItem(label, value)
        )
        .join(""),
    };
  },

  // Procesa datos para cambio de estado
  processChangeStatusData: (user) => {
    const avatarUrl =
      user.avatar && user.avatar !== "default.jpg"
        ? `${APP_URL}public/photos/thumbnail/${user.avatar}`
        : `${APP_URL}public/photos/default.jpg`;

    const currentStatus = user.estado_id === "1" ? "activo" : "inactivo";
    const newStatus = currentStatus === "activo" ? "inactivo" : "activo";
    const newStatusValue = currentStatus === "activo" ? "0" : "1";

    const submitText =
      newStatus === "activo" ? "Activar Usuario" : "Desactivar Usuario";

    return {
      avatarUrl,
      userName: `${user.nombre} ${user.apellido_paterno} ${user.apellido_materno}`,
      username: user.usuario,
      currentStatus,
      currentStatusClass: currentStatus,
      newStatus,
      newStatusClass: newStatus,
      newStatusValue,
      submitText,
    };
  },

  // Procesa datos para editar rol
  processEditRoleData: (role, allPermissions, rolePermissions) => {
    const assignedPermissionIds = rolePermissions.map((p) => p.permiso_id);

    return {
      roleName: role.rol_nombre,
      usuariosCount: role.usuarios_count || 0,
      permissionsHTML: TEMPLATE_HELPERS.generatePermissionsGrid(
        allPermissions,
        assignedPermissionIds
      ),
    };
  },

  // Procesa datos para detalles de rol
  processRoleDetailsData: (role) => {
    return {
      roleName: role.rol_nombre,
      roleDetails: [
        ["ID del Rol", `#${role.rol_id}`],
        ["Nombre", role.rol_nombre],
        ["Usuarios Asignados", role.usuarios_count || 0],
        ["Estado", "Activo"],
        ["Fecha de Creación", role.rol_fecha_creacion || "No disponible"],
        [
          "Última Modificación",
          role.rol_ultima_modificacion || "No disponible",
        ],
      ]
        .map(([label, value]) =>
          TEMPLATE_GENERATORS.generateUserDetailItem(label, value)
        )
        .join(""),
      permissionsList:
        role.usuarios_asignados && role.usuarios_asignados.length > 0
          ? role.usuarios_asignados
              .map(
                (usuario) =>
                  `<div class="permission-item">${usuario.usuario_nombre} ${usuario.usuario_apellido_paterno}</div>`
              )
              .join("")
          : '<div class="no-permissions">No hay usuarios asignados</div>',
    };
  },

  // Genera grid de permisos
  generatePermissionsGrid: (permissions, assignedIds) => {
    if (!permissions || permissions.length === 0) {
      return '<div class="no-permissions">No hay permisos disponibles</div>';
    }

    // Agrupar permisos por categoría (usando el prefijo del slug)
    const groupedPermissions = {};

    permissions.forEach((permission) => {
      const category = permission.permiso_slug.split(".")[0] || "otros";
      const categoryName = TEMPLATE_HELPERS.getCategoryDisplayName(category);

      if (!groupedPermissions[categoryName]) {
        groupedPermissions[categoryName] = [];
      }
      groupedPermissions[categoryName].push(permission);
    });

    let html = "";

    Object.keys(groupedPermissions).forEach((categoryName) => {
      html += `
        <div class="permission-category">
          <h5 class="category-title">${categoryName.toUpperCase()}</h5>
          <div class="category-permissions">
      `;

      groupedPermissions[categoryName].forEach((permission) => {
        const isChecked = assignedIds.includes(permission.permiso_id);
        html += `
          <div class="permission-item">
            <label class="permission-label">
              <input type="checkbox" name="permisos[]" value="${
                permission.permiso_id
              }" 
                     ${isChecked ? "checked" : ""} class="permission-checkbox">
              <span class="permission-name">${permission.permiso_nombre}</span>
              <span class="permission-description">${
                permission.permiso_descripcion || ""
              }</span>
            </label>
          </div>
        `;
      });

      html += `
          </div>
        </div>
      `;
    });

    return html;
  },

  // Obtiene nombre de categoría para mostrar
  getCategoryDisplayName: (category) => {
    const categoryNames = {
      users: "Usuarios",
      roles: "Roles",
      permissions: "Permisos",
      reports: "Reportes",
      search: "Buscar",
      notifications: "Notificaciones",
      patients: "Pacientes",
      donations: "Donaciones",
      stats: "Estadísticas",
      settings: "Configuración",
      profile: "Perfil"
    };

    return (
      categoryNames[category] ||
      category.charAt(0).toUpperCase() + category.slice(1)
    );
  },

  // Genera campos de formulario desde configuración
  generateFormFields: (fields) => {
    return fields
      .map((field) => TEMPLATE_GENERATORS.generateFormField(field))
      .join("");
  },
};

// Exportar para uso global
window.MODAL_TEMPLATES = MODAL_TEMPLATES;
window.TEMPLATE_GENERATORS = TEMPLATE_GENERATORS;
window.TEMPLATE_HELPERS = TEMPLATE_HELPERS;
