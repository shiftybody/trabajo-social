/**
 * Gestor de Configuración
 *
 * Controlador principal para el módulo de configuración que maneja
 * la carga dinámica de contenido y las operaciones CRUD
 */
class ConfigManager {
  constructor() {
    this.currentSection = "niveles-socioeconomicos"; // Sección inicial
    this.baseUrl = `${APP_URL}api/settings`;
    this.contentArea = null;
    this.isLoading = false;

    this.init();
  }

  /**
   * Inicializa el gestor de configuración
   */
  init() {
    try {
      this.cacheElements();
      this.bindNavigationEvents();
      this.loadInitialSection();
    } catch (error) {
      console.error("Error initializing ConfigManager:", error);
      this.showError("Error al inicializar el gestor de configuración");
    }
  }

  /**
   * Cachea elementos del DOM que se usan frecuentemente
   */
  cacheElements() {
    this.contentArea = document.getElementById("config-content-area");
    if (!this.contentArea) {
      throw new Error("Elemento config-content-area no encontrado");
    }
  }

  /**
   * Configura los eventos de navegación
   */
  bindNavigationEvents() {
    const navLinks = document.querySelectorAll(".config-nav a");

    navLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();

        // Evitar navegación durante carga
        if (this.isLoading) return;

        // Actualizar navegación activa
        navLinks.forEach((l) => l.classList.remove("active"));
        link.classList.add("active");

        // Obtener sección del href
        const section = link.getAttribute("href").substring(1);
        this.loadSection(section);
      });
    });
  }

  /**
   * Carga la sección inicial
   */
  loadInitialSection() {
    this.loadSection(this.currentSection);
  }

  /**
   * Carga una sección específica
   */
  async loadSection(section) {
    if (this.isLoading) return;

    try {
      this.isLoading = true;
      this.showLoading();
      this.currentSection = section;

      const response = await fetch(
        `${this.baseUrl}/section?section=${section}`,
        {
          method: "GET",
          headers: {
            accept: "application/json",
          },
        }
      );

      const data = await response.json();

      if (data.status === "success") {
        this.renderSectionContent(data.data);
      } else {
        this.showError(data.message || "Error al cargar la sección");
      }
    } catch (error) {
      console.error("Error loading section:", error);
      this.showError("Error de conexión al cargar la sección");
    } finally {
      this.isLoading = false;
    }
  }

  /**
   * Renderiza el contenido de una sección
   */
  renderSectionContent(sectionData) {
    try {
      // Renderizar contenido según el tipo de sección
      switch (sectionData.type) {
        case "socioeconomic-levels":
          this.renderSocioeconomicLevels(sectionData);
          break;

        case "contribution-rules":
          this.renderContributionRules(sectionData);
          break;

        case "criteria":
          this.renderCriteria(sectionData);
          break;

        case "criteria-grouped":
          this.renderGroupedCriteria(sectionData);
          break;

        default:
          this.renderGenericSection(sectionData);
      }
    } catch (error) {
      console.error("Error rendering section:", error);
      this.showError("Error al renderizar la sección");
    }
  }

  /**
   * Renderiza la sección de niveles socioeconómicos
   */
  renderSocioeconomicLevels(data) {
    const html = `
      <div class="config-content-header">
        <h2 id="criteria-title">${data.title}</h2>
        <button class="btn-primary" onclick="configManager.openLevelModal()">
          <i class="icon-plus"></i> Añadir Nivel
        </button>
      </div>
      
      <div class="levels-container fade-in">
        <div class="levels-stats">
          <div class="stat-card">
            <span class="stat-number">${data.levels.length}</span>
            <span class="stat-label">Niveles Configurados</span>
          </div>
        </div>
        
        <div class="levels-table-container">
          <table class="criteria-table">
            <thead>
              <tr>
                <th>Nivel</th>
                <th>Puntaje Mínimo</th>
                <th>Reglas</th>
                <th>Estudios</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              ${this.renderLevelsRows(data.levels)}
            </tbody>
          </table>
        </div>
      </div>
    `;

    this.contentArea.innerHTML = html;
  }

  /**
   * Renderiza las filas de niveles socioeconómicos
   */
  renderLevelsRows(levels) {
    if (!levels || levels.length === 0) {
      return `
        <tr>
          <td colspan="6" class="text-center">
            <p>No hay niveles configurados</p>
            <button class="btn-primary btn-sm" onclick="configManager.openLevelModal()">
              Crear Primer Nivel
            </button>
          </td>
        </tr>
      `;
    }

    return levels
      .map(
        (level) => `
          <tr data-level-id="${level.id}">
            <td>
              <strong>${level.nivel}</strong>
            </td>
            <td>
              <span class="badge badge-info">${level.puntaje_minimo}+ pts</span>
            </td>
            <td>
              <span class="count-badge">${level.reglas_count || 0}</span>
            </td>
            <td>
              <span class="count-badge">${level.estudios_count || 0}</span>
            </td>
            <td>
              <label class="toggle-switch">
                <input type="checkbox" ${level.estado == 1 ? "checked" : ""} 
                       onchange="configManager.toggleLevelStatus(${
                         level.id
                       }, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            </td>
            <td class="actions">
              <button class="btn-secondary btn-sm" onclick="configManager.editLevel(${
                level.id
              })" title="Editar">
                <i class="icon-edit"></i>
              </button>
              <button class="btn-danger btn-sm" onclick="configManager.deleteLevel(${
                level.id
              })" title="Eliminar"
                      ${
                        level.reglas_count > 0 || level.estudios_count > 0
                          ? "disabled"
                          : ""
                      }>
                <i class="icon-delete"></i>
              </button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  /**
   * Renderiza la sección de reglas de aportación
   */
  renderContributionRules(data) {
    const html = `
      <div class="config-content-header">
        <h2 id="criteria-title">${data.title}</h2>
        <div class="header-actions">
          <button class="btn-secondary" onclick="configManager.openRulesMatrixModal()">
            <i class="icon-grid"></i> Vista Matriz
          </button>
          <button class="btn-primary" onclick="configManager.openRuleModal()">
            <i class="icon-plus"></i> Añadir Regla
          </button>
        </div>
      </div>
      
      <div class="rules-container fade-in">
        <div class="rules-filters">
          <div class="filter-group">
            <label>Nivel:</label>
            <select id="filter-nivel" onchange="configManager.filterRules()">
              <option value="">Todos los niveles</option>
              ${data.levels
                .map(
                  (level) =>
                    `<option value="${level.id}">${level.nivel}</option>`
                )
                .join("")}
            </select>
          </div>
          <div class="filter-group">
            <label>Periodicidad:</label>
            <select id="filter-periodicidad" onchange="configManager.filterRules()">
              <option value="">Todas</option>
              ${Object.entries(data.periodicities || {})
                .map(
                  ([key, value]) => `<option value="${key}">${value}</option>`
                )
                .join("")}
            </select>
          </div>
        </div>
        
        <div class="rules-table-container">
          <table class="criteria-table">
            <thead>
              <tr>
                <th>Nivel</th>
                <th>Edad</th>
                <th>Periodicidad</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="rules-table-body">
              ${this.renderRulesRows(data.rules)}
            </tbody>
          </table>
        </div>
      </div>
    `;

    this.contentArea.innerHTML = html;
  }

  /**
   * Renderiza las filas de reglas de aportación
   */
  renderRulesRows(rules) {
    if (!rules || rules.length === 0) {
      return `
        <tr>
          <td colspan="6" class="text-center">
            <p>No hay reglas configuradas</p>
            <button class="btn-primary btn-sm" onclick="configManager.openRuleModal()">
              Crear Primera Regla
            </button>
          </td>
        </tr>
      `;
    }

    return rules
      .map(
        (rule) => `
          <tr data-rule-id="${rule.id}">
            <td>
              <span class="level-badge">${rule.nivel_nombre}</span>
            </td>
            <td>
              <span class="age-badge">${rule.edad} años</span>
            </td>
            <td>
              <span class="periodicity-badge periodicity-${rule.periodicidad}">
                ${TEMPLATE_HELPERS.formatPeriodicity(rule.periodicidad)}
              </span>
            </td>
            <td>
              <strong class="amount">$${TEMPLATE_HELPERS.formatMoney(
                rule.monto_aportacion
              )}</strong>
            </td>
            <td>
              <label class="toggle-switch">
                <input type="checkbox" ${rule.estado == 1 ? "checked" : ""} 
                       onchange="configManager.toggleRuleStatus(${
                         rule.id
                       }, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            </td>
            <td class="actions">
              <button class="btn-secondary btn-sm" onclick="configManager.editRule(${
                rule.id
              })" title="Editar">
                <i class="icon-edit"></i>
              </button>
              <button class="btn-danger btn-sm" onclick="configManager.deleteRule(${
                rule.id
              })" title="Eliminar">
                <i class="icon-delete"></i>
              </button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  /**
   * Renderiza la sección de criterios
   */
  renderCriteria(data) {
    const html = `
      <div class="config-content-header">
        <h2 id="criteria-title">${data.title}</h2>
        <button class="btn-primary" onclick="configManager.openCriteriaModal(${
          data.subcategory.id
        })">
          <i class="icon-plus"></i> Añadir Criterio
        </button>
      </div>
      
      <div class="criteria-info fade-in">
        <div class="subcategory-info">
          <h4>${data.subcategory.categoria_nombre} > ${
      data.subcategory.nombre
    }</h4>
          <p>${data.subcategory.descripcion || "Sin descripción"}</p>
        </div>
      </div>
      
      <div class="criteria-table-container">
        <table class="criteria-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Valores</th>
              <th>Puntaje</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            ${this.renderCriteriaRows(data.criteria)}
          </tbody>
        </table>
      </div>
    `;

    this.contentArea.innerHTML = html;
  }

  /**
   * Renderiza las filas de criterios
   */
  renderCriteriaRows(criteria) {
    if (!criteria || criteria.length === 0) {
      return `
        <tr>
          <td colspan="6" class="text-center">
            <p>No hay criterios configurados</p>
            <button class="btn-primary btn-sm" onclick="configManager.openCriteriaModal()">
              Crear Primer Criterio
            </button>
          </td>
        </tr>
      `;
    }

    return criteria
      .map(
        (criterion) => `
          <tr data-criteria-id="${criterion.id}">
            <td>
              <strong>${criterion.nombre}</strong>
            </td>
            <td>
              <span class="type-badge type-${criterion.tipo_criterio}">
                ${TEMPLATE_HELPERS.formatCriteriaType(criterion.tipo_criterio)}
              </span>
            </td>
            <td>
              ${TEMPLATE_HELPERS.formatCriteriaValues(criterion)}
            </td>
            <td>
              <span class="score-badge">${criterion.puntaje} pts</span>
            </td>
            <td>
              <label class="toggle-switch">
                <input type="checkbox" ${
                  criterion.estado == 1 ? "checked" : ""
                } 
                       onchange="configManager.toggleCriteriaStatus(${
                         criterion.id
                       }, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            </td>
            <td class="actions">
              <button class="btn-secondary btn-sm" onclick="configManager.editCriteria(${
                criterion.id
              })" title="Editar">
                <i class="icon-edit"></i>
              </button>
              <button class="btn-danger btn-sm" onclick="configManager.deleteCriteria(${
                criterion.id
              })" title="Eliminar">
                <i class="icon-delete"></i>
              </button>
            </td>
          </tr>
        `
      )
      .join("");
  }

  /**
   * Renderiza criterios agrupados (para materiales y servicios)
   */
  renderGroupedCriteria(data) {
    const html = `
      <div class="config-content-header">
        <h2 id="criteria-title">${data.title}</h2>
        <button class="btn-primary" onclick="configManager.showGroupActions()">
          <i class="icon-plus"></i> Gestionar Criterios
        </button>
      </div>
      
      <div class="grouped-criteria-container fade-in">
        ${data.groups
          .map(
            (group) => `
            <div class="criteria-group">
              <div class="group-header">
                <h4>${group.subcategory.nombre}</h4>
                <p>${group.subcategory.descripcion || ""}</p>
                <button class="btn-secondary btn-sm" onclick="configManager.openCriteriaModal(${
                  group.subcategory.id
                })">
                  Añadir Criterio
                </button>
              </div>
              
              <div class="group-criteria">
                <table class="criteria-table">
                  <thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Tipo</th>
                      <th>Valores</th>
                      <th>Puntaje</th>
                      <th>Estado</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${this.renderCriteriaRows(group.criteria)}
                  </tbody>
                </table>
              </div>
            </div>
          `
          )
          .join("")}
      </div>
    `;

    this.contentArea.innerHTML = html;
  }

  /**
   * Renderiza sección genérica
   */
  renderGenericSection(data) {
    const html = `
      <div class="config-content-header">
        <h2 id="criteria-title">${data.title}</h2>
      </div>
      <div class="generic-section fade-in">
        <p>Sección en desarrollo: ${data.title}</p>
      </div>
    `;

    this.contentArea.innerHTML = html;
  }

  // ==================== OPERACIONES DE NIVELES SOCIOECONÓMICOS ====================

  /**
   * Abre el modal para crear/editar nivel
   */
  async openLevelModal(levelId = null) {
    const isEdit = levelId !== null;
    let levelData = null;

    if (isEdit) {
      try {
        const response = await fetch(`${this.baseUrl}/levels/${levelId}`);
        const data = await response.json();
        if (data.status === "success") {
          levelData = data.data;
        } else {
          this.showError("Error al cargar datos del nivel");
          return;
        }
      } catch (error) {
        this.showError("Error al cargar datos del nivel");
        return;
      }
    }

    const templateData = TEMPLATE_HELPERS.processLevelFormData(levelData);

    const modal = createModal("form", {
      title: isEdit
        ? "Editar Nivel Socioeconómico"
        : "Nuevo Nivel Socioeconómico",
      size: "medium",
      template: "levelForm",
      data: templateData,
      endpoint: isEdit
        ? `${this.baseUrl}/levels/${levelId}`
        : `${this.baseUrl}/levels`,
      onSubmit: async (formData, modalInstance) => {
        const success = isEdit
          ? await this.updateLevel(levelId, formData)
          : await this.createLevel(formData);

        if (success) {
          modalInstance.hide();
        }
      },
    });

    modal.show();
  }

  /**
   * Crea un nuevo nivel socioeconómico
   */
  async createLevel(formData) {
    try {
      const response = await fetch(`${this.baseUrl}/levels`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Nivel creado correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al crear nivel");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Actualiza un nivel socioeconómico
   */
  async updateLevel(levelId, formData) {
    try {
      const response = await fetch(`${this.baseUrl}/levels/${levelId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Nivel actualizado correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al actualizar nivel");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Cambia el estado de un nivel
   */
  async toggleLevelStatus(levelId, status) {
    try {
      const response = await fetch(`${this.baseUrl}/levels/${levelId}/status`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ estado: status }),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Estado actualizado");
      } else {
        this.showError(data.message || "Error al cambiar estado");
        // Revertir el toggle
        const checkbox = document.querySelector(
          `tr[data-level-id="${levelId}"] input[type="checkbox"]`
        );
        if (checkbox) checkbox.checked = !status;
      }
    } catch (error) {
      this.showError("Error de conexión");
      // Revertir el toggle
      const checkbox = document.querySelector(
        `tr[data-level-id="${levelId}"] input[type="checkbox"]`
      );
      if (checkbox) checkbox.checked = !status;
    }
  }

  /**
   * Elimina un nivel socioeconómico
   */
  async deleteLevel(levelId) {
    const confirmed = await CustomDialog.confirm(
      "Eliminar Nivel",
      "¿Estás seguro de que deseas eliminar este nivel? Esta acción no se puede deshacer.",
      "Eliminar",
      "Cancelar"
    );

    if (!confirmed) return;

    try {
      const response = await fetch(`${this.baseUrl}/levels/${levelId}`, {
        method: "DELETE",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Nivel eliminado correctamente");
        this.loadSection(this.currentSection);
      } else {
        this.showError(data.message || "Error al eliminar nivel");
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
  }

  // ==================== OPERACIONES DE REGLAS DE APORTACIÓN ====================

  /**
   * Abre el modal para crear/editar regla
   */
  async openRuleModal(ruleId = null) {
    const isEdit = ruleId !== null;
    let ruleData = null;

    // Cargar niveles para el formulario
    try {
      const levelsResponse = await fetch(`${this.baseUrl}/levels`);
      const levelsData = await levelsResponse.json();

      if (levelsData.status !== "success") {
        this.showError("Error al cargar niveles disponibles");
        return;
      }

      if (isEdit) {
        const response = await fetch(`${this.baseUrl}/rules/${ruleId}`);
        const data = await response.json();
        if (data.status === "success") {
          ruleData = data.data;
        } else {
          this.showError("Error al cargar datos de la regla");
          return;
        }
      }

      const templateData = TEMPLATE_HELPERS.processRuleFormData(
        ruleData,
        levelsData.data
      );

      const modal = createModal("form", {
        title: isEdit
          ? "Editar Regla de Aportación"
          : "Nueva Regla de Aportación",
        size: "medium",
        template: "ruleForm",
        data: templateData,
        endpoint: isEdit
          ? `${this.baseUrl}/rules/${ruleId}`
          : `${this.baseUrl}/rules`,
        onSubmit: async (formData, modalInstance) => {
          const success = isEdit
            ? await this.updateRule(ruleId, formData)
            : await this.createRule(formData);

          if (success) {
            modalInstance.hide();
          }
        },
      });

      modal.show();
    } catch (error) {
      this.showError("Error al cargar datos para el formulario");
    }
  }

  /**
   * Crea una nueva regla de aportación
   */
  async createRule(formData) {
    try {
      const response = await fetch(`${this.baseUrl}/rules`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Regla creada correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al crear regla");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Actualiza una regla de aportación
   */
  async updateRule(ruleId, formData) {
    try {
      const response = await fetch(`${this.baseUrl}/rules/${ruleId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Regla actualizada correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al actualizar regla");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Filtra las reglas según los criterios seleccionados
   */
  async filterRules() {
    const nivelSelect = document.getElementById("filter-nivel");
    const periodicidadSelect = document.getElementById("filter-periodicidad");

    if (!nivelSelect || !periodicidadSelect) return;

    const nivel = nivelSelect.value;
    const periodicidad = periodicidadSelect.value;

    const params = new URLSearchParams();
    if (nivel) params.append("nivel_id", nivel);
    if (periodicidad) params.append("periodicidad", periodicidad);

    try {
      const response = await fetch(
        `${this.baseUrl}/rules?${params.toString()}`
      );
      const data = await response.json();

      if (data.status === "success") {
        const tbody = document.getElementById("rules-table-body");
        if (tbody) {
          tbody.innerHTML = this.renderRulesRows(data.data);
        }
      }
    } catch (error) {
      this.showError("Error al filtrar reglas");
    }
  }

  /**
   * Cambia el estado de una regla
   */
  async toggleRuleStatus(ruleId, status) {
    try {
      const response = await fetch(`${this.baseUrl}/rules/${ruleId}/status`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ estado: status }),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Estado actualizado");
      } else {
        this.showError(data.message || "Error al cambiar estado");
        // Revertir el toggle
        const checkbox = document.querySelector(
          `tr[data-rule-id="${ruleId}"] input[type="checkbox"]`
        );
        if (checkbox) checkbox.checked = !status;
      }
    } catch (error) {
      this.showError("Error de conexión");
      // Revertir el toggle
      const checkbox = document.querySelector(
        `tr[data-rule-id="${ruleId}"] input[type="checkbox"]`
      );
      if (checkbox) checkbox.checked = !status;
    }
  }

  /**
   * Elimina una regla de aportación
   */
  async deleteRule(ruleId) {
    const confirmed = await CustomDialog.confirm(
      "Eliminar Regla",
      "¿Estás seguro de que deseas eliminar esta regla?",
      "Eliminar",
      "Cancelar"
    );

    if (!confirmed) return;

    try {
      const response = await fetch(`${this.baseUrl}/rules/${ruleId}`, {
        method: "DELETE",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Regla eliminada correctamente");
        this.loadSection(this.currentSection);
      } else {
        this.showError(data.message || "Error al eliminar regla");
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
  }

  // ==================== OPERACIONES DE CRITERIOS ====================

  /**
   * Abre el modal para crear/editar criterio
   */
  async openCriteriaModal(subcategoryId, criteriaId = null) {
    const isEdit = criteriaId !== null;
    let criteriaData = null;

    if (isEdit) {
      try {
        const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`);
        const data = await response.json();
        if (data.status === "success") {
          criteriaData = data.data;
          subcategoryId = criteriaData.subcategoria_id; // Obtener subcategoryId del criterio
        } else {
          this.showError("Error al cargar datos del criterio");
          return;
        }
      } catch (error) {
        this.showError("Error al cargar datos del criterio");
        return;
      }
    }

    const templateData = TEMPLATE_HELPERS.processCriteriaFormData(
      criteriaData,
      subcategoryId
    );

    const modal = createModal("form", {
      title: isEdit ? "Editar Criterio" : "Nuevo Criterio",
      size: "large",
      template: "criteriaForm",
      data: templateData,
      endpoint: isEdit
        ? `${this.baseUrl}/criteria/${criteriaId}`
        : `${this.baseUrl}/criteria`,
      onSubmit: async (formData, modalInstance) => {
        const success = isEdit
          ? await this.updateCriteria(criteriaId, formData)
          : await this.createCriteria(formData);

        if (success) {
          modalInstance.hide();
        }
      },
    });

    modal.show();
  }

  /**
   * Crea un nuevo criterio
   */
  async createCriteria(formData) {
    try {
      const response = await fetch(`${this.baseUrl}/criteria`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Criterio creado correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al crear criterio");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Actualiza un criterio
   */
  async updateCriteria(criteriaId, formData) {
    try {
      const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(formData),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Criterio actualizado correctamente");
        this.loadSection(this.currentSection);
        return true;
      } else {
        this.showError(data.message || "Error al actualizar criterio");
        return false;
      }
    } catch (error) {
      this.showError("Error de conexión");
      return false;
    }
  }

  /**
   * Cambia el estado de un criterio
   */
  async toggleCriteriaStatus(criteriaId, status) {
    try {
      const response = await fetch(
        `${this.baseUrl}/criteria/${criteriaId}/status`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
          },
          body: JSON.stringify({ estado: status }),
        }
      );

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Estado actualizado");
      } else {
        this.showError(data.message || "Error al cambiar estado");
        // Revertir el toggle
        const checkbox = document.querySelector(
          `tr[data-criteria-id="${criteriaId}"] input[type="checkbox"]`
        );
        if (checkbox) checkbox.checked = !status;
      }
    } catch (error) {
      this.showError("Error de conexión");
      // Revertir el toggle
      const checkbox = document.querySelector(
        `tr[data-criteria-id="${criteriaId}"] input[type="checkbox"]`
      );
      if (checkbox) checkbox.checked = !status;
    }
  }

  /**
   * Elimina un criterio
   */
  async deleteCriteria(criteriaId) {
    const confirmed = await CustomDialog.confirm(
      "Eliminar Criterio",
      "¿Estás seguro de que deseas eliminar este criterio?",
      "Eliminar",
      "Cancelar"
    );

    if (!confirmed) return;

    try {
      const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`, {
        method: "DELETE",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Criterio eliminado correctamente");
        this.loadSection(this.currentSection);
      } else {
        this.showError(data.message || "Error al eliminar criterio");
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
  }

  // ==================== FUNCIONES ESPECIALES ====================

  /**
   * Abre modal de vista matriz para reglas
   */
  async openRulesMatrixModal() {
    try {
      // Cargar niveles para mostrar matriz
      const levelsResponse = await fetch(`${this.baseUrl}/levels`);
      const levelsData = await levelsResponse.json();

      if (levelsData.status !== "success") {
        this.showError("Error al cargar niveles");
        return;
      }

      const templateData = TEMPLATE_HELPERS.processRulesMatrixData(
        levelsData.data
      );

      const modal = createModal("info", {
        title: "Matriz de Aportaciones",
        size: "large",
        template: "rulesMatrix",
        data: templateData,
        onShow: (modalInstance) => {
          // Cargar matriz para el primer nivel
          if (levelsData.data.length > 0) {
            this.loadMatrixForLevel(levelsData.data[0].id);
          }
        },
      });

      modal.show();
    } catch (error) {
      this.showError("Error al cargar matriz de reglas");
    }
  }

  /**
   * Carga matriz de aportaciones para un nivel específico
   */
  async loadMatrixForLevel(levelId) {
    try {
      const response = await fetch(
        `${this.baseUrl}/rules/matrix?nivel_id=${levelId}`
      );
      const data = await response.json();

      if (data.status === "success") {
        this.renderMatrix(data.data);
      } else {
        this.showError("Error al cargar matriz");
      }
    } catch (error) {
      this.showError("Error al cargar matriz");
    }
  }

  /**
   * Renderiza la matriz de aportaciones
   */
  renderMatrix(matrixData) {
    const container = document.getElementById("matrix-container");
    if (!container) return;

    const { edades, periodicidades, matrix } = matrixData;

    if (!edades || !periodicidades) {
      container.innerHTML = `
        <div class="matrix-error">
          <p>No hay datos suficientes para mostrar la matriz</p>
        </div>
      `;
      return;
    }

    let html = `
      <table class="matrix-table">
        <thead>
          <tr>
            <th>Edad</th>
            ${periodicidades
              .map((p) => `<th>${TEMPLATE_HELPERS.formatPeriodicity(p)}</th>`)
              .join("")}
          </tr>
        </thead>
        <tbody>
    `;

    edades.forEach((edad) => {
      html += `<tr><td class="edad-cell">${edad} años</td>`;
      periodicidades.forEach((periodicidad) => {
        const monto =
          matrix[edad] && matrix[edad][periodicidad]
            ? `$${TEMPLATE_HELPERS.formatMoney(matrix[edad][periodicidad])}`
            : '<span class="no-rule">-</span>';
        html += `<td class="monto-cell">${monto}</td>`;
      });
      html += "</tr>";
    });

    html += `
        </tbody>
      </table>
    `;

    container.innerHTML = html;
  }

  /**
   * Muestra acciones agrupadas para criterios múltiples
   */
  showGroupActions() {
    const modal = createModal("info", {
      title: "Gestionar Criterios",
      size: "medium",
      template: "groupActions",
      data: {},
    });

    modal.show();
  }

  /**
   * Cambia la visibilidad de los campos según el tipo de criterio
   */
  toggleCriteriaFields(tipo) {
    const rangoFields = document.getElementById("rango-fields");
    const textoFields = document.getElementById("texto-fields");
    const booleanoFields = document.getElementById("booleano-fields");

    if (!rangoFields || !textoFields || !booleanoFields) return;

    // Ocultar todos los campos
    rangoFields.style.display = "none";
    textoFields.style.display = "none";
    booleanoFields.style.display = "none";

    // Mostrar campos según el tipo
    switch (tipo) {
      case "rango_numerico":
        rangoFields.style.display = "block";
        break;
      case "valor_especifico":
        textoFields.style.display = "block";
        break;
      case "booleano":
        booleanoFields.style.display = "block";
        break;
    }
  }

  // ==================== FUNCIONES AUXILIARES ====================

  /**
   * Edita un nivel (alias para abrir modal)
   */
  editLevel(levelId) {
    this.openLevelModal(levelId);
  }

  /**
   * Edita una regla (alias para abrir modal)
   */
  editRule(ruleId) {
    this.openRuleModal(ruleId);
  }

  /**
   * Edita un criterio (alias para abrir modal)
   */
  editCriteria(criteriaId) {
    this.openCriteriaModal(null, criteriaId);
  }

  // ==================== FUNCIONES FUTURAS (PLACEHOLDERS) ====================

  /**
   * Funciones para funcionalidades futuras
   */
  showBulkCriteriaForm() {
    CustomDialog.info(
      "Función en desarrollo",
      "Esta funcionalidad estará disponible próximamente."
    );
  }

  exportCriteria() {
    CustomDialog.info(
      "Función en desarrollo",
      "Esta funcionalidad estará disponible próximamente."
    );
  }

  importCriteria() {
    CustomDialog.info(
      "Función en desarrollo",
      "Esta funcionalidad estará disponible próximamente."
    );
  }

  openBulkRulesModal() {
    CustomDialog.info(
      "Función en desarrollo",
      "Esta funcionalidad estará disponible próximamente."
    );
  }

  // ==================== FUNCIONES DE UTILIDAD ====================

  /**
   * Muestra loading
   */
  showLoading() {
    if (!this.contentArea) return;

    this.contentArea.innerHTML = `
      <div class="loading-container">
        <div class="loading-spinner"></div>
        <p>Cargando...</p>
      </div>
    `;
  }

  /**
   * Muestra mensaje de éxito usando CustomDialog
   */
  showSuccess(message) {
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.toast(message, "success");
    } else {
      console.log("Success:", message);
    }
  }

  /**
   * Muestra mensaje de error usando CustomDialog
   */
  showError(message) {
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.toast(message, "error", 5000);
    } else {
      console.error("Error:", message);
      alert(message); // Fallback
    }
  }

  /**
   * Muestra mensaje informativo usando CustomDialog
   */
  showInfo(message) {
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.toast(message, "info");
    } else {
      console.log("Info:", message);
    }
  }

  /**
   * Cleanup al destruir la instancia
   */
  destroy() {
    // Cerrar todos los modales abiertos
    if (typeof BaseModal !== "undefined") {
      BaseModal.closeAll();
    }

    // Limpiar referencias
    this.contentArea = null;

    // Resetear estado
    this.isLoading = false;
    this.currentSection = null;
  }
}

// ==================== INICIALIZACIÓN Y EXPORTACIÓN ====================

// Instancia global del gestor de configuración
let configManager;

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {
  try {
    configManager = new ConfigManager();
  } catch (error) {
    console.error("Error al inicializar ConfigManager:", error);

    // Mostrar error al usuario si es posible
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error(
        "Error de Inicialización",
        "No se pudo inicializar el gestor de configuración. Por favor, recarga la página."
      );
    }
  }
});

// Cleanup al descargar la página
window.addEventListener("beforeunload", function () {
  if (configManager) {
    configManager.destroy();
  }
});

// Exponer funciones globales para uso en HTML
window.configManager = configManager;

// Manejo de errors globales para this module
window.addEventListener("error", function (event) {
  if (event.filename && event.filename.includes("config-manager.js")) {
    console.error("Error en ConfigManager:", event.error);

    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error(
        "Error del Sistema",
        "Se ha producido un error en el gestor de configuración. La página se recargará automáticamente."
      );

      // Recargar después de 3 segundos
      setTimeout(() => {
        window.location.reload();
      }, 3000);
    }
  }
});
