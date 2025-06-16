/**
 * Gestor de Configuración Simplificado
 *
 * Maneja únicamente las 3 funcionalidades principales:
 * - Niveles socioeconómicos
 * - Reglas de aportación
 * - Criterios básicos
 *
 * Utiliza DataTables para todas las tablas
 */
class ConfigManager {
  constructor() {
    this.currentSection = "niveles-socioeconomicos";
    this.baseUrl = `${APP_URL}api/settings`;
    this.contentArea = null;
    this.isLoading = false;
    this.currentTable = null; // Referencia a la tabla DataTable actual

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
   * Cachea elementos del DOM
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

      // Destruir tabla anterior si existe
      if (this.currentTable) {
        this.currentTable.destroy();
        this.currentTable = null;
      }

      // Solo manejar las 3 secciones principales
      switch (section) {
        case "niveles-socioeconomicos":
          await this.loadLevelsSection();
          break;
        case "reglas-aportacion":
          await this.loadRulesSection();
          break;
        case "criterios":
        case "protocolo":
        case "gasto-traslado":
        case "tiempo-traslado":
        case "integrantes":
        case "hijos":
        case "tipo-familia":
        case "tipo-vivienda":
        case "tenencia":
        case "zona":
        case "materiales":
        case "servicios":
          await this.loadCriteriaSection();
          break;
        default:
          this.showError("Sección no implementada");
      }
    } catch (error) {
      console.error("Error loading section:", error);
      this.showError("Error al cargar la sección");
    } finally {
      this.isLoading = false;
    }
  }

  // ==================== NIVELES SOCIOECONÓMICOS ====================

  /**
   * Carga la sección de niveles socioeconómicos
   */
  async loadLevelsSection() {
    const html = `
      <div class="config-content-header">
        <h2>Niveles Socioeconómicos</h2>
        <button class="btn-primary" onclick="configManager.openLevelModal()">
          <i class="icon-plus"></i> Nuevo Nivel
        </button>
      </div>
  
        <table id="levels-table" class="hover nowrap cell-borders" style="width: 100%;">
          <thead>
            <tr>
              <th class="dt-head-center">NIVEL</th>
              <th class="dt-head-center">PUNTAJE MÍNIMO</th>
              <th class="dt-head-center">ESTADO</th>
              <th class="dt-head-center">ACCIONES</th>
            </tr>
          </thead>
        </table>
      </div>
    `;

    this.contentArea.innerHTML = html;
    await this.initializeLevelsTable();
  }

  /**
   * Inicializa la tabla de niveles con DataTables
   */
  async initializeLevelsTable() {
    this.currentTable = new DataTable("#levels-table", {
      paging: false,
      info: false,
      ajax: {
        url: `${this.baseUrl}/levels`,
        dataSrc: "data",
      },
      columns: [

        { data: "nivel", className: "dt-body-center" },
        {
          data: "puntaje_minimo",
          className: "dt-body-center",
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: function (data, type, row) {
            const checked = data == 1 ? "checked" : "";
            return `
              <label class="toggle-switch">
                <input type="checkbox" ${checked} onchange="configManager.toggleLevelStatus(${row.id}, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            `;
          },
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: function (data, type, row) {
            return `
              <button type="button" class="editar" onclick="configManager.editLevel(${row.id})" title="Editar Nivel">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                <path d="M13.5 6.5l4 4" />
              </svg>
              </button>
              <button type="button" class="remover" onclick="configManager.deleteLevel(${row.id})" title="Eliminar Nivel">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 7l16 0" />
                <path d="M10 11l0 6" />
                <path d="M14 11l0 6" />
                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
              </svg>
              </button>
            `;
          },
        },
      ],
      language: {
        zeroRecords: "No se encontraron niveles",
        emptyTable: "Aún no hay niveles, crea uno nuevo",
        info: "Mostrando _START_ a _END_ de _TOTAL_ niveles",
        infoEmpty: "Mostrando 0 a 0 de 0 niveles",
        infoFiltered: "(filtrado de _MAX_ niveles totales)",
        processing: '<div class="table-spinner"></div>Procesando...',
      },
      drawCallback: () => {
        // Aplicar clases de estado
        document.querySelectorAll("td").forEach((td) => {
          if (td.textContent === "Inactivo") {
            td.classList.add("inactivo");
          }
          if (td.textContent === "Activo") {
            td.classList.add("activo");
          }
        });
      },
    });

    this.setupTableFilters("levels");
  }

  // ==================== REGLAS DE APORTACIÓN ====================

  /**
   * Carga la sección de reglas de aportación
   */
  async loadRulesSection() {
    const html = `
      <div class="config-content-header">
        <h2>Reglas de Aportación</h2>
        <button class="btn-primary" onclick="configManager.openRuleModal()">
          <i class="icon-plus"></i> Nueva Regla
        </button>
      </div>
      
      <div class="table-container">
        <div class="tools">
          <form class="filter_form" id="rules_filter_form">
            <div class="input-container">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                <path d="M21 21l-6 -6" />
              </svg>
              <input class="matching-search" id="rulesMatchingInput" placeholder="Buscar reglas">
              <span class="clear-button" id="rulesClearButton" style="display: none;">×</span>
            </div>
            <div class="select-container">
              <select class="custom-select" id="rulesFilterColumn">
                <option value="0">Todo</option>
                <option value="1">Nivel</option>
                <option value="2">Edad</option>
                <option value="3">Periodicidad</option>
                <option value="4">Estado</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-filter-icon">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
              </svg>
            </div>
          </form>
        </div>

        <table id="rules-table" class="hover nowrap cell-borders" style="width: 100%;">
          <thead>
            <tr>
              <th class="dt-head-center">ID</th>
              <th>NIVEL</th>
              <th class="dt-head-center">EDAD</th>
              <th>PERIODICIDAD</th>
              <th class="dt-head-center">MONTO</th>
              <th class="dt-head-center">ESTADO</th>
              <th class="dt-head-center">ACCIONES</th>
            </tr>
          </thead>
        </table>
      </div>
    `;

    this.contentArea.innerHTML = html;
    await this.initializeRulesTable();
  }

  /**
   * Inicializa la tabla de reglas con DataTables
   */
  async initializeRulesTable() {
    this.currentTable = new DataTable("#rules-table", {
      ajax: {
        url: `${this.baseUrl}/rules`,
        dataSrc: "data",
      },
      columns: [
        {
          data: "id",
          className: "dt-body-center",
        },
        { data: "nivel_nombre" },
        {
          data: "edad",
          className: "dt-body-center",
          render: function (data) {
            return `<span class="age-badge">${data} años</span>`;
          },
        },
        {
          data: "periodicidad",
          render: function (data) {
            return `<span class="periodicity-badge periodicity-${data}">${data}</span>`;
          },
        },
        {
          data: "monto_aportacion",
          className: "dt-body-center",
          render: function (data) {
            return `<strong>$${parseFloat(data).toLocaleString(
              "es-MX"
            )}</strong>`;
          },
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: function (data, type, row) {
            const checked = data == 1 ? "checked" : "";
            return `
              <label class="toggle-switch">
                <input type="checkbox" ${checked} onchange="configManager.toggleRuleStatus(${row.id}, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            `;
          },
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: function (data, type, row) {
            return `
              <button class="btn-secondary btn-sm" onclick="configManager.editRule(${row.id})" title="Editar">
                <i class="icon-edit"></i>
              </button>
              <button class="btn-danger btn-sm" onclick="configManager.deleteRule(${row.id})" title="Eliminar">
                <i class="icon-delete"></i>
              </button>
            `;
          },
        },
      ],
      language: {
        zeroRecords: "No se encontraron reglas",
        emptyTable: "Aún no hay reglas, crea una nueva",
        info: "Mostrando _START_ a _END_ de _TOTAL_ reglas",
        infoEmpty: "Mostrando 0 a 0 de 0 reglas",
        infoFiltered: "(filtrado de _MAX_ reglas totales)",
        processing: '<div class="table-spinner"></div>Procesando...',
      },
    });

    this.setupTableFilters("rules");
  }

  // ==================== CRITERIOS ====================

  /**
   * Carga la sección de criterios
   */
  async loadCriteriaSection() {
    const html = `
      <div class="config-content-header">
        <h2>Criterios de Puntuación</h2>
        <button class="btn-primary" onclick="configManager.openCriteriaModal()">
          <i class="icon-plus"></i> Nuevo Criterio
        </button>
      </div>
      
      <div class="table-container">
        <div class="tools">
          <form class="filter_form" id="criteria_filter_form">
            <div class="input-container">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                <path d="M21 21l-6 -6" />
              </svg>
              <input class="matching-search" id="criteriaMatchingInput" placeholder="Buscar criterios">
              <span class="clear-button" id="criteriaClearButton" style="display: none;">×</span>
            </div>
            <div class="select-container">
              <select class="custom-select" id="criteriaFilterColumn">
                <option value="0">Todo</option>
                <option value="1">Criterio</option>
                <option value="2">Categoría</option>
                <option value="3">Tipo</option>
                <option value="4">Estado</option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-filter-icon">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
              </svg>
            </div>
          </form>
        </div>

        <table id="criteria-table" class="hover nowrap cell-borders" style="width: 100%;">
          <thead>
            <tr>
              <th class="dt-head-center">ID</th>
              <th>CRITERIO</th>
              <th>CATEGORÍA</th>
              <th>TIPO</th>
              <th class="dt-head-center">PUNTUACIÓN</th>
              <th class="dt-head-center">ESTADO</th>
              <th class="dt-head-center">ACCIONES</th>
            </tr>
          </thead>
        </table>
      </div>
    `;

    this.contentArea.innerHTML = html;
    await this.initializeCriteriaTable();
  }

  /**
   * Inicializa la tabla de criterios con DataTables
   */
  async initializeCriteriaTable() {
    this.currentTable = new DataTable("#criteria-table", {
      ajax: {
        url: `${this.baseUrl}/criteria`,
        dataSrc: "data",
      },
      columns: [
        {
          data: "id",
          className: "dt-body-center",
        },
        { data: "criterio" },
        { data: "categoria_nombre" },
        {
          data: "tipo",
          render: function (data) {
            return `<span class="type-badge type-${data}">${data.replace(
              "_",
              " "
            )}</span>`;
          },
        },
        {
          data: "puntuacion",
          className: "dt-body-center",
          render: function (data) {
            return `<span class="score-badge">${data}</span>`;
          },
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: function (data, type, row) {
            const checked = data == 1 ? "checked" : "";
            return `
              <label class="toggle-switch">
                <input type="checkbox" ${checked} onchange="configManager.toggleCriteriaStatus(${row.id}, this.checked)">
                <span class="toggle-slider"></span>
              </label>
            `;
          },
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: function (data, type, row) {
            return `
              <button class="btn-secondary btn-sm" onclick="configManager.editCriteria(${row.id})" title="Editar">
                <i class="icon-edit"></i>
              </button>
              <button class="btn-danger btn-sm" onclick="configManager.deleteCriteria(${row.id})" title="Eliminar">
                <i class="icon-delete"></i>
              </button>
            `;
          },
        },
      ],
      language: {
        zeroRecords: "No se encontraron criterios",
        emptyTable: "Aún no hay criterios, crea uno nuevo",
        info: "Mostrando _START_ a _END_ de _TOTAL_ criterios",
        infoEmpty: "Mostrando 0 a 0 de 0 criterios",
        infoFiltered: "(filtrado de _MAX_ criterios totales)",
        processing: '<div class="table-spinner"></div>Procesando...',
      },
    });

    this.setupTableFilters("criteria");
  }

  // ==================== FILTROS Y BÚSQUEDA ====================

  /**
   * Configura los filtros de búsqueda para una tabla
   */
  setupTableFilters(tableType) {
    const matchingInput = document.getElementById(`${tableType}MatchingInput`);
    const filterColumn = document.getElementById(`${tableType}FilterColumn`);
    const clearButton = document.getElementById(`${tableType}ClearButton`);

    if (!matchingInput || !filterColumn || !clearButton) return;

    // Evento de búsqueda
    matchingInput.addEventListener("input", () => {
      this.applyTableFilter(tableType);
    });

    // Evento de cambio de columna
    filterColumn.addEventListener("change", () => {
      this.applyTableFilter(tableType);
    });

    // Evento de limpiar
    clearButton.addEventListener("click", () => {
      matchingInput.value = "";
      clearButton.style.display = "none";
      this.applyTableFilter(tableType);
      matchingInput.focus();
    });
  }

  /**
   * Aplica filtros a la tabla
   */
  applyTableFilter(tableType) {
    const matchingInput = document.getElementById(`${tableType}MatchingInput`);
    const filterColumn = document.getElementById(`${tableType}FilterColumn`);
    const clearButton = document.getElementById(`${tableType}ClearButton`);

    if (!matchingInput || !filterColumn || !clearButton || !this.currentTable)
      return;

    const searchValue = matchingInput.value.trim();
    const columnIndex = filterColumn.value;

    clearButton.style.display = searchValue ? "block" : "none";

    if (columnIndex == 0) {
      // Filtro global
      this.currentTable.columns().search("");
      this.currentTable.search(searchValue, false, true).draw();
    } else {
      // Filtro por columna
      this.currentTable.search("");
      this.currentTable.columns().search("");
      this.currentTable
        .column(columnIndex)
        .search(searchValue, false, true)
        .draw();
    }
  }

  // ==================== OPERACIONES CRUD ====================

  /**
   * Abre modal para nuevo nivel
   */
  openLevelModal(levelId = null) {
    // Implementar modal de nivel
    this.showInfo("Modal de nivel en desarrollo");
  }

  /**
   * Edita un nivel
   */
  editLevel(levelId) {
    this.openLevelModal(levelId);
  }

  /**
   * Cambia estado de nivel
   */
  async toggleLevelStatus(levelId, status) {
    try {
      const response = await fetch(`${this.baseUrl}/levels/${levelId}/status`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ estado: status }),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
      } else {
        this.showError(data.message);
        this.currentTable.ajax.reload(null, false);
      }
    } catch (error) {
      this.showError("Error de conexión");
      this.currentTable.ajax.reload(null, false);
    }
  }

  /**
   * Elimina un nivel
   */
  async deleteLevel(levelId) {
    if (!confirm("¿Está seguro de eliminar este nivel?")) return;

    try {
      const response = await fetch(`${this.baseUrl}/levels/${levelId}`, {
        method: "DELETE",
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
        this.currentTable.ajax.reload(null, false);
      } else {
        this.showError(data.message);
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
  }

  /**
   * Abre modal para nueva regla
   */
  openRuleModal(ruleId = null) {
    this.showInfo("Modal de regla en desarrollo");
  }

  /**
   * Edita una regla
   */
  editRule(ruleId) {
    this.openRuleModal(ruleId);
  }

  /**
   * Cambia estado de regla
   */
  async toggleRuleStatus(ruleId, status) {
    try {
      const response = await fetch(`${this.baseUrl}/rules/${ruleId}/status`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ estado: status }),
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
      } else {
        this.showError(data.message);
        this.currentTable.ajax.reload(null, false);
      }
    } catch (error) {
      this.showError("Error de conexión");
      this.currentTable.ajax.reload(null, false);
    }
  }

  /**
   * Elimina una regla
   */
  async deleteRule(ruleId) {
    if (!confirm("¿Está seguro de eliminar esta regla?")) return;

    try {
      const response = await fetch(`${this.baseUrl}/rules/${ruleId}`, {
        method: "DELETE",
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
        this.currentTable.ajax.reload(null, false);
      } else {
        this.showError(data.message);
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
  }

  /**
   * Abre modal para nuevo criterio
   */
  openCriteriaModal(criteriaId = null) {
    this.showInfo("Modal de criterio en desarrollo");
  }

  /**
   * Edita un criterio
   */
  editCriteria(criteriaId) {
    this.openCriteriaModal(criteriaId);
  }

  /**
   * Cambia estado de criterio
   */
  async toggleCriteriaStatus(criteriaId, status) {
    try {
      const response = await fetch(
        `${this.baseUrl}/criteria/${criteriaId}/status`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ estado: status }),
        }
      );

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
      } else {
        this.showError(data.message);
        this.currentTable.ajax.reload(null, false);
      }
    } catch (error) {
      this.showError("Error de conexión");
      this.currentTable.ajax.reload(null, false);
    }
  }

  /**
   * Elimina un criterio
   */
  async deleteCriteria(criteriaId) {
    if (!confirm("¿Está seguro de eliminar este criterio?")) return;

    try {
      const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`, {
        method: "DELETE",
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess(data.message);
        this.currentTable.ajax.reload(null, false);
      } else {
        this.showError(data.message);
      }
    } catch (error) {
      this.showError("Error de conexión");
    }
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
   * Muestra mensaje de éxito
   */
  showSuccess(message) {
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.toast(message, "success");
    } else {
      console.log("Success:", message);
    }
  }

  /**
   * Muestra mensaje de error
   */
  showError(message) {
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.toast(message, "error", 5000);
    } else {
      console.error("Error:", message);
      alert(message);
    }
  }

  /**
   * Muestra mensaje informativo
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
    // Destruir tabla actual si existe
    if (this.currentTable) {
      this.currentTable.destroy();
      this.currentTable = null;
    }

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

// Manejo de errores globales para este módulo
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
