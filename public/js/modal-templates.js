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

  // ==================== TEMPLATES DE CONFIGURACIÓN ====================

  // Template para formulario de nivel socioeconómico
  createLevel: `
    <form novalidate id="createLevelForm" class="base-modal-form form-ajax config-form" method="POST">
      <div class="form-group">
        <label for="nivel" class="field-label">Nombre del Nivel</label>
        <input type="text" id="nivel" name="nivel" class="input-reset" 
               value="{{nivel}}" 
               maxlength="20" required>
      </div>
      
      <div class="form-group">
        <label for="puntaje_minimo" class="field-label">Puntaje Mínimo</label>
        <input type="number" id="puntaje_minimo" name="puntaje_minimo" class="input-reset"
               value="{{puntaje_minimo}}" min="0" required>
      </div>
      
      <div class="base-modal-actions">
        <button class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">Crear Nivel</button>
      </div>
    </form>
  `,

  editLevel: `
      <form novalidate id="editLevelForm" class="base-modal-form form-ajax config-form" method="POST">
      <div class="form-group">
        <label for="nivel" class="field-label">Nombre del Nivel</label>
        <input type="text" id="nivel" name="nivel" class="input-reset" 
               value="{{nivel}}" 
               maxlength="20" required>
      </div>
      
      <div class="form-group">
        <label for="puntaje_minimo" class="field-label">Puntaje Mínimo</label>
        <input type="number" id="puntaje_minimo" name="puntaje_minimo" class="input-reset"
               value="{{puntaje_minimo}}" min="0" required>
      </div>
      
      <div class="base-modal-actions">
        <button class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">Actualizar Nivel</button>
      </div>
    </form>
  `,

  // Template para formulario de regla de aportación
  ruleForm: `
    <form novalidate id="ruleForm" class="base-modal-form form-ajax config-form" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label for="nivel_socioeconomico_id" class="field-label">Nivel Socioeconómico *</label>
          <select id="nivel_socioeconomico_id" name="nivel_socioeconomico_id" class="input-reset" required>
            <option value="">Seleccione un nivel...</option>
            {{levelsOptions}}
          </select>
        </div>
        
        <div class="form-group">
          <label for="edad" class="field-label">Edad del Paciente *</label>
          <input type="number" id="edad" name="edad" class="input-reset"
                 value="{{edad}}" min="0" max="150" required>
          <small class="field-help">Edad en años</small>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="periodicidad" class="field-label">Periodicidad *</label>
          <select id="periodicidad" name="periodicidad" class="input-reset" required>
            <option value="">Seleccione periodicidad...</option>
            <option value="mensual" {{periodicidadMensualSelected}}>Mensual</option>
            <option value="semestral" {{periodicidadSemestralSelected}}>Semestral</option>
            <option value="anual" {{periodicidadAnualSelected}}>Anual</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="monto_aportacion" class="field-label">Monto de Aportación *</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" id="monto_aportacion" name="monto_aportacion" class="input-reset"
                   value="{{monto_aportacion}}" min="0" step="0.01" required>
          </div>
        </div>
      </div>
      
      <div class="base-modal-actions">
        <button type="button" class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">{{submitText}}</button>
      </div>
    </form>
  `,

  // Template para formulario de criterio
  criteriaForm: `
    <form novalidate id="criteriaForm" class="base-modal-form form-ajax config-form" method="POST">
      <input type="hidden" name="subcategoria_id" value="{{subcategoria_id}}">
      
      <div class="form-group">
        <label for="nombre" class="field-label">Nombre del Criterio *</label>
        <input type="text" id="nombre" name="nombre" class="input-reset"
               value="{{nombre}}" placeholder="ej: Casa propia, 3-4 integrantes" 
               maxlength="100" required>
      </div>
      
      <div class="form-group">
        <label for="tipo_criterio" class="field-label">Tipo de Criterio *</label>
        <select id="tipo_criterio" name="tipo_criterio" class="input-reset" required 
                onchange="window.configManager?.toggleCriteriaFields?.(this.value)">
          <option value="">Seleccione tipo...</option>
          <option value="rango_numerico" {{tipoRangoSelected}}>Rango Numérico</option>
          <option value="valor_especifico" {{tipoValorSelected}}>Valor Específico</option>
          <option value="booleano" {{tipoBoolSelected}}>Booleano (Sí/No)</option>
        </select>
      </div>
      
      <!-- Campos para rango numérico -->
      <div id="rango-fields" class="criteria-type-fields" style="display: {{rangoDisplay}}">
        <div class="form-row">
          <div class="form-group">
            <label for="valor_minimo" class="field-label">Valor Mínimo *</label>
            <input type="number" id="valor_minimo" name="valor_minimo" class="input-reset"
                   value="{{valor_minimo}}">
          </div>
          <div class="form-group">
            <label for="valor_maximo" class="field-label">Valor Máximo</label>
            <input type="number" id="valor_maximo" name="valor_maximo" class="input-reset"
                   value="{{valor_maximo}}">
            <small class="field-help">Dejar vacío para "sin límite"</small>
          </div>
        </div>
      </div>
      
      <!-- Campos para valor específico -->
      <div id="texto-fields" class="criteria-type-fields" style="display: {{textoDisplay}}">
        <div class="form-group">
          <label for="valor_texto" class="field-label">Valor de Texto *</label>
          <input type="text" id="valor_texto" name="valor_texto" class="input-reset"
                 value="{{valor_texto}}" placeholder="ej: Casa, Departamento, Sí, No" 
                 maxlength="100">
        </div>
      </div>
      
      <!-- Campos para booleano -->
      <div id="booleano-fields" class="criteria-type-fields" style="display: {{booleanoDisplay}}">
        <div class="form-group">
          <label for="valor_booleano" class="field-label">Valor Booleano *</label>
          <select id="valor_booleano" name="valor_booleano" class="input-reset">
            <option value="1" {{boolTrueSelected}}>Sí (Verdadero)</option>
            <option value="0" {{boolFalseSelected}}>No (Falso)</option>
          </select>
        </div>
      </div>
      
      <div class="form-group">
        <label for="puntaje" class="field-label">Puntaje *</label>
        <input type="number" id="puntaje" name="puntaje" class="input-reset"
               value="{{puntaje}}" min="0" required>
        <small class="field-help">Puntos que otorga este criterio</small>
      </div>
      
      <div class="base-modal-actions">
        <button type="button" class="btn-cancel" data-action="close">Cancelar</button>
        <button type="submit" class="btn-primary">{{submitText}}</button>
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

  // ==================== USUARIOS ==================

  processCreateRole: (roles) => {
    const baseRolesOptions = roles
      .map(
        (role) => `<option value="${role.rol_id}">${role.rol_nombre}</option>`
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

  // ================= ROLES ==================

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

  // ==================== HELPERS DE CONFIGURACIÓN ==================

  processEditLevelData: (level) => {
    return {
      nivel: level.nivel || "",
      puntaje_minimo: level.puntaje_minimo || "",
    }
  },

  // Procesa datos para formulario de regla
  processRuleFormData: (rule = null, levels = []) => {
    const levelsOptions = levels
      .map(
        (level) => `
        <option value="${level.id}" ${
          rule?.nivel_socioeconomico_id == level.id ? "selected" : ""
        }>
          ${level.nivel} (≥ ${level.puntaje_minimo} pts)
        </option>
      `
      )
      .join("");

    return {
      levelsOptions,
      edad: rule?.edad || "",
      periodicidadMensualSelected:
        rule?.periodicidad === "mensual" ? "selected" : "",
      periodicidadSemestralSelected:
        rule?.periodicidad === "semestral" ? "selected" : "",
      periodicidadAnualSelected:
        rule?.periodicidad === "anual" ? "selected" : "",
      monto_aportacion: rule?.monto_aportacion || "",
      submitText: rule ? "Actualizar Regla" : "Crear Regla",
    };
  },

  // Procesa datos para formulario de criterio
  processCriteriaFormData: (criteria = null, subcategoryId = null) => {
    const isRango = criteria?.tipo_criterio === "rango_numerico";
    const isTexto = criteria?.tipo_criterio === "valor_especifico";
    const isBooleano = criteria?.tipo_criterio === "booleano";

    return {
      subcategoria_id: subcategoryId || criteria?.subcategoria_id || "",
      nombre: criteria?.nombre || "",
      tipoRangoSelected: isRango ? "selected" : "",
      tipoValorSelected: isTexto ? "selected" : "",
      tipoBoolSelected: isBooleano ? "selected" : "",
      rangoDisplay: isRango ? "block" : "none",
      textoDisplay: isTexto ? "block" : "none",
      booleanoDisplay: isBooleano ? "block" : "none",
      valor_minimo: criteria?.valor_minimo || "",
      valor_maximo: criteria?.valor_maximo || "",
      valor_texto: criteria?.valor_texto || "",
      boolTrueSelected: criteria?.valor_booleano == 1 ? "selected" : "",
      boolFalseSelected: criteria?.valor_booleano == 0 ? "selected" : "",
      puntaje: criteria?.puntaje || "",
      submitText: criteria ? "Actualizar Criterio" : "Crear Criterio",
    };
  },

  // Procesa datos para matriz de reglas
  processRulesMatrixData: (levels = []) => {
    const levelsOptions = levels
      .map(
        (level) => `
        <option value="${level.id}">
          ${level.nivel} (≥ ${level.puntaje_minimo} pts)
        </option>
      `
      )
      .join("");

    return {
      levelsOptions,
    };
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
      profile: "Perfil",
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

  // ==================== UTILIDADES DE FORMATEO ====================

  // Formatea valores monetarios
  formatMoney: (amount) => {
    return new Intl.NumberFormat("es-MX", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(amount);
  },

  // Formatea periodicidad
  formatPeriodicity: (periodicity) => {
    const periodicities = {
      mensual: "Mensual",
      semestral: "Semestral",
      anual: "Anual",
    };
    return periodicities[periodicity] || periodicity;
  },

  // Formatea tipo de criterio
  formatCriteriaType: (type) => {
    const types = {
      rango_numerico: "Rango Numérico",
      valor_especifico: "Valor Específico",
      booleano: "Booleano",
    };
    return types[type] || type;
  },

  // Formatea valores de criterio según su tipo
  formatCriteriaValues: (criterion) => {
    switch (criterion.tipo_criterio) {
      case "rango_numerico":
        if (criterion.valor_maximo) {
          return `${criterion.valor_minimo} - ${criterion.valor_maximo}`;
        } else {
          return `${criterion.valor_minimo}+`;
        }

      case "valor_especifico":
        return `"${criterion.valor_texto}"`;

      case "booleano":
        return criterion.valor_booleano ? "Sí" : "No";

      default:
        return "Sin valor";
    }
  },
};

// Exportar para uso global
window.MODAL_TEMPLATES = MODAL_TEMPLATES;
window.TEMPLATE_GENERATORS = TEMPLATE_GENERATORS;
window.TEMPLATE_HELPERS = TEMPLATE_HELPERS;
