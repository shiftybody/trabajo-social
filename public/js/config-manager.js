/**
 * Gestor de Configuración
 *
 * Maneja la navegación, carga de datos y persistencia de la sección activa
 * en el modulo de configuracion
 *
 */
class ConfigManager {
  constructor() {
    this.currentSection = "niveles-socioeconomicos"; // Sección por defecto si no hay nada en localStorage
    this.baseUrl = `${APP_URL}api/settings`;
    this.contentArea = null;
    this.isLoading = false;
    this.currentTable = null;
    this.levelsData = [];

    this.init();
  }

  /**
   * Inicializa el gestor de configuración
   */
  init() {
    try {
      this.cacheElements();
      this.bindNavigationEvents();
      this.loadLevelsData();
      this.loadInitialSection();
    } catch (error) {
      console.error("Error initializing ConfigManager:", error);
      this.showError("Error al inicializar el gestor de configuración");
    }
  }

  /**
   * MODIFICADO: Cachea elementos y se asegura de que el área de contenido esté oculta
   * para prevenir el "flash" de contenido al cargar la página.
   */
  cacheElements() {
    this.contentArea = document.getElementById("config-content-area");
    if (!this.contentArea) {
      throw new Error("Elemento config-content-area no encontrado");
    }
    // Añade la clase para ocultar el contenedor hasta que todo esté listo.
    // Requiere que la clase 'content-loading' esté definida en el CSS.
    this.contentArea.classList.add("content-loading");
  }

  /**
   * Carga los datos de niveles socioeconómicos en caché para uso posterior.
   */
  async loadLevelsData() {
    try {
      const response = await fetch(`${this.baseUrl}/levels`);
      const data = await response.json();

      if (data.status === "success") {
        this.levelsData = data.data.filter((level) => level.estado == 1); // Solo activos
      }
    } catch (error) {
      console.error("Error loading levels data:", error);
    }
  }

  /**
   * Configura los eventos de clic para los enlaces de navegación.
   */
  bindNavigationEvents() {
    const navLinks = document.querySelectorAll(".config-nav a");

    navLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        if (this.isLoading) return;

        const section = link.getAttribute("href").substring(1);
        this.loadSection(section);
      });
    });
  }

  /**
   * Carga la sección inicial, obteniéndola de localStorage o usando la de por defecto.
   */
  loadInitialSection() {
    const savedSection = localStorage.getItem("configManager_currentSection");
    const sectionToLoad = savedSection || this.currentSection;
    this.loadSection(sectionToLoad);
  }

  /**
   * MODIFICADO: Carga una sección específica. Solo al final de todo el proceso
   * hace visible el contenedor para evitar el parpadeo.
   */
  async loadSection(section) {
    if (this.isLoading) return;

    try {
      this.isLoading = true;
      this.showLoading(); // Muestra el spinner (dentro del contenedor aún invisible)
      this.currentSection = section;

      // Guarda la sección actual para persistencia y actualiza la navegación
      localStorage.setItem("configManager_currentSection", section);
      this.updateActiveNavLink(section);

      // Destruye la instancia de DataTable anterior si existe
      if (this.currentTable) {
        this.currentTable.destroy();
        this.currentTable = null;
      }

      // Carga el contenido de la sección correspondiente
      switch (section) {
        case "niveles-socioeconomicos":
          await this.loadLevelsSection();
          break;
        case "reglas-aportacion":
          await this.loadRulesSection();
          break;
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
          await this.loadCriteriaSection(section);
          break;
        default:
          this.showError("Sección no implementada");
      }
    } catch (error) {
      console.error("Error loading section:", error);
      this.showError("Error al cargar la sección");
    } finally {
      this.isLoading = false;
      // Al final de todo (éxito o error), mostramos el contenedor.
      // Esto es clave para evitar el "flash".
      if (this.contentArea) {
        this.contentArea.classList.remove("content-loading");
      }
    }
  }

  /**
   * NUEVO MÉTODO: Actualiza el estado visual de los enlaces de navegación.
   */
  updateActiveNavLink(section) {
    const navLinks = document.querySelectorAll(".config-nav a");
    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href") === `#${section}`) {
        link.classList.add("active");
      }
    });
  }

  // ==================== NIVELES SOCIOECONÓMICOS ====================

  async loadLevelsSection() {
    const html = `
      <div class="config-content-header">
        <h2>Niveles Socioeconómicos</h2>
        <button class="btn-primary" onclick="configManager.createLevel()">
          Nuevo Nivel
        </button>
      </div>
      <div class="table-container">
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

  async initializeLevelsTable() {
    this.currentTable = new DataTable("#levels-table", {
      ajax: { url: `${this.baseUrl}/levels`, dataSrc: "data" },
      columns: [
        { data: "nivel", className: "dt-body-center" },
        { data: "puntaje_minimo", className: "dt-body-center" },
        {
          data: "estado",
          className: "dt-body-center",
          render: (data, type, row) => `
            <label class="toggle-switch">
              <input type="checkbox" ${
                data == 1 ? "checked" : ""
              } onchange="configManager.toggleLevelStatus(${
            row.id
          }, this.checked)">
              <span class="toggle-slider"></span>
            </label>`,
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: (data, type, row) => `
            <button type="button" class="editar" onclick="configManager.editLevel(${row.id})" title="Editar Nivel">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
            </button>
            <button type="button" class="remover" onclick="configManager.deleteLevel(${row.id})" title="Eliminar Nivel">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
            </button>`,
        },
      ],
      order: [[1, "asc"]],
      paging: false,
      info: false,
      language: {
        zeroRecords: "No se encontraron niveles",
        emptyTable: "Aún no hay niveles, crea uno nuevo",
        processing: '<div class="table-spinner"></div>Procesando...',
      },
    });
  }

  // ==================== REGLAS DE APORTACIÓN ====================

  async loadRulesSection() {
    const levelOptions = this.levelsData
      .map((level) => `<option value="${level.id}">${level.nivel}</option>`)
      .join("");
    const html = `
      <div class="config-content-header">
        <h2>Reglas de Aportación</h2>
        <button class="btn-primary" onclick="configManager.openRuleModal()"><i class="icon-plus"></i> Nueva Regla</button>
      </div>
      <div class="table-container">
        <div class="tools">
          <form class="filter_form" id="rules_filter_form">
            <div class="select-container">
              <label class="label-filter" for="levelFilter">Filtrar por Nivel:</label>
              <select class="custom-select" id="levelFilter" style="min-width: 200px;">
                <option value="0">Seleccione un nivel</option>
                ${levelOptions}
              </select>
            </div>
          </form>
        </div>
        <table id="rules-table" class="hover nowrap cell-borders" style="width: 100%;">
          <thead>
            <tr>
              <th class="dt-head-center">NIVEL</th><th class="dt-head-center">EDAD</th><th class="dt-head-center">PERIODICIDAD</th>
              <th class="dt-head-center">MONTO</th><th class="dt-head-center">ESTADO</th><th class="dt-head-center">ACCIONES</th>
            </tr>
          </thead>
        </table>
      </div>`;
    this.contentArea.innerHTML = html;
    await this.initializeRulesTable();
  }

  async initializeRulesTable() {
    this.currentTable = new DataTable("#rules-table", {
      ajax: {
        url: `${this.baseUrl}/rules`,
        dataSrc: "data",
        data: (d) => {
          const levelFilter = document.getElementById("levelFilter");
          if (levelFilter && levelFilter.value) d.nivel_id = levelFilter.value;
          return d;
        },
      },
      columns: [
        { data: "nivel_nombre", className: "dt-body-center" },
        {
          data: "edad",
          className: "dt-body-center",
          render: (data) => `<span class="age-badge">${data} años</span>`,
        },
        {
          data: "periodicidad",
          className: "dt-body-center",
          render: (data) =>
            `<span class="periodicity-badge periodicity-${data}">${data}</span>`,
        },
        {
          data: "monto_aportacion",
          className: "dt-body-center",
          render: (data) =>
            `<strong>$${parseFloat(data).toLocaleString("es-MX")}</strong>`,
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: (data, type, row) =>
            `<label class="toggle-switch"><input type="checkbox" ${
              data == 1 ? "checked" : ""
            } onchange="configManager.toggleRuleStatus(${
              row.id
            }, this.checked)"><span class="toggle-slider"></span></label>`,
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: (data, type, row) => `
            <button type="button" class="editar" onclick="configManager.editRule(${row.id})" title="Editar Regla"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg></button>
            <button type="button" class="remover" onclick="configManager.deleteRule(${row.id})" title="Eliminar Regla"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>`,
        },
      ],
      order: [
        [0, "asc"],
        [1, "asc"],
      ],
      language: {
        zeroRecords: "No se encontraron reglas para el nivel seleccionado",
        emptyTable: "No se encontraron reglas de aportación",
        processing: '<div class="table-spinner"></div>Procesando...',
        info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty: "Mostrando 0 a 0 de 0 registros",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
      },
    });
    this.setupLevelFilter();
  }

  setupLevelFilter() {
    const levelFilter = document.getElementById("levelFilter");
    if (levelFilter) {
      levelFilter.addEventListener("change", () =>
        this.currentTable?.ajax.reload()
      );
    }
  }

  // ==================== CRITERIOS ====================

  async loadCriteriaSection(section) {
    const sectionTitles = {
      protocolo: "Protocolo",
      "tiempo-traslado": "Tiempo de Traslado",
      "gasto-traslado": "Gasto de Traslado",
      integrantes: "Integrantes de Familia",
      hijos: "Número de Hijos",
      "tipo-familia": "Tipo de Familia",
      "tipo-vivienda": "Tipo de Vivienda",
      tenencia: "Tenencia",
      zona: "Zona",
      materiales: "Materiales",
      servicios: "Servicios",
    };
    const sectionTitle = sectionTitles[section] || "Criterios de Puntuación";
    const html = `
      <div class="config-content-header">
        <h2>${sectionTitle}</h2>
        <button class="btn-primary" onclick="configManager.openCriteriaModal()"><i class="icon-plus"></i> Nuevo Criterio</button>
      </div>
      <div class="table-container">
        <table id="criteria-table" class="hover nowrap cell-borders" style="width: 100%;">
          <thead>
            <tr><th>CRITERIO</th><th class="dt-head-center">PUNTUACIÓN</th><th class="dt-head-center">ESTADO</th><th class="dt-head-center">ACCIONES</th></tr>
          </thead>
        </table>
      </div>`;
    this.contentArea.innerHTML = html;
    await this.initializeCriteriaTable();
  }

  async initializeCriteriaTable() {
    this.currentTable = new DataTable("#criteria-table", {
      ajax: {
        url: `${this.baseUrl}/criteria`,
        data: { section: this.currentSection },
        dataSrc: "data",
      },
      columns: [
        {
          data: "criterio",
          render: (data) => `<span class="criteria-name">${data}</span>`,
        },
        {
          data: "puntaje",
          className: "dt-body-center",
          render: (data) => `<span class="score-badge">${data} pts</span>`,
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: (data, type, row) =>
            `<label class="toggle-switch"><input type="checkbox" ${
              data == 1 ? "checked" : ""
            } onchange="configManager.toggleCriteriaStatus(${
              row.id
            }, this.checked)"><span class="toggle-slider"></span></label>`,
        },
        {
          data: null,
          className: "dt-body-center",
          orderable: false,
          render: (data, type, row) => `
            <button type="button" class="editar" onclick="configManager.editCriteria(${row.id})" title="Editar Criterio"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg></button>
            <button type="button" class="remover" onclick="configManager.deleteCriteria(${row.id})" title="Eliminar Criterio"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg></button>`,
        },
      ],
      paging: false,
      info: false,
      language: {
        zeroRecords: `No hay criterios configurados para ${this.currentSection}`,
        emptyTable: "Aún no hay criterios, crea uno nuevo",
        processing: '<div class="table-spinner"></div>Procesando...',
      },
    });
  }

  // ==================== OPERACIONES CRUD Y UTILIDAD (SIN CAMBIOS) ====================

  createLevel() {
    mostrarModalCrearNivel();
  }
  editLevel(levelId) {
    mostrarModalEditarNivel(levelId);
  }
  async deleteLevel(levelId) {
    const confirm = await CustomDialog.confirm(
      "Confirmar Eliminación",
      "¿Está seguro de eliminar este nivel socioeconómico? Esta acción no se puede deshacer.",
      "Eliminar",
      "Cancelar"
    );
    if (!confirm) return;
    this.performFetch(
      `${this.baseUrl}/levels/${levelId}`,
      { method: "DELETE" },
      "Nivel eliminado correctamente."
    );
  }
  async toggleLevelStatus(levelId, status) {
    await this.performFetch(
      `${this.baseUrl}/levels/${levelId}/status`,
      {
        method: "POST",
        body: JSON.stringify({ status: status ? 1 : 0 }),
        headers: { "Content-Type": "application/json" },
      },
      "Estado del nivel actualizado.",
      true
    );
    await this.loadLevelsData(); // Recargar caché de niveles activos
  }

  openRuleModal() {
    window.mostrarModalCrearRegla?.();
  }
  editRule(ruleId) {
    window.mostrarModalEditarRegla?.(ruleId);
  }
  async deleteRule(ruleId) {
    const confirm = await CustomDialog.confirm(
      "Confirmar Eliminación",
      "¿Está seguro de eliminar esta regla de aportación?",
      "Eliminar",
      "Cancelar"
    );
    if (!confirm) return;
    this.performFetch(
      `${this.baseUrl}/rules/${ruleId}`,
      { method: "DELETE" },
      "Regla eliminada correctamente."
    );
  }
  toggleRuleStatus(ruleId, status) {
    this.performFetch(
      `${this.baseUrl}/rules/${ruleId}/status`,
      {
        method: "POST",
        body: JSON.stringify({ status: status ? 1 : 0 }),
        headers: { "Content-Type": "application/json" },
      },
      "Estado de la regla actualizado."
    );
  }

  /**
   * Abre modal para nuevo criterio
   */
  openCriteriaModal(criteriaId = null) {
    if (criteriaId) {
      // Editar criterio existente
      editCriteria(criteriaId);
    } else {
      // Crear nuevo criterio
      createCriteria();
    }
  }

  /**
   * Edita un criterio
   */
  editCriteria(criteriaId) {
    window.editCriteria(criteriaId);
  }

  /**
   * Elimina un criterio con confirmación
   */
  async deleteCriteria(criteriaId) {
    if (!criteriaId) {
      this.showError("ID de criterio no válido");
      return;
    }

    try {
      // Obtener datos del criterio primero para mostrar el nombre
      const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`);
      const result = await response.json();

      let criteriaName = "este criterio";
      if (result.status === "success" && result.data) {
        criteriaName = `"${result.data.nombre || result.data.criterio}"`;
      }

      // Confirmar eliminación usando CustomDialog
      const confirmDelete = await CustomDialog.confirm(
        "Confirmar Eliminación",
        `¿Está seguro de eliminar el criterio ${criteriaName}?<br><br>
       <strong>Esta acción no se puede deshacer.</strong><br>
       El criterio se eliminará permanentemente del sistema.`,
        "Eliminar",
        "Cancelar"
      );

      if (!confirmDelete) return;

      // Proceder con la eliminación
      const deleteResponse = await fetch(
        `${this.baseUrl}/criteria/${criteriaId}`,
        {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json",
          },
        }
      );

      const deleteResult = await deleteResponse.json();

      if (deleteResult.status === "success") {
        this.showSuccess(deleteResult.message);

        // Recargar tabla
        if (this.currentTable) {
          this.currentTable.ajax.reload(null, false);
        }
      } else {
        this.showError(deleteResult.message || "Error al eliminar el criterio");
      }
    } catch (error) {
      console.error("Error deleting criteria:", error);
      this.showError("Error de conexión al eliminar el criterio");
    }
  }

  /**
   * Alterna la visibilidad de campos según el tipo de criterio
   * @param {string} tipoCriterio Tipo seleccionado
   */
  toggleCriteriaFields(tipoCriterio) {
    // Ocultar todos los campos primero
    const allFields = document.querySelectorAll(".criteria-fields");
    allFields.forEach((field) => (field.style.display = "none"));

    // Mostrar campos relevantes según el tipo
    switch (tipoCriterio) {
      case "rango_numerico":
        const numericFields = document.getElementById("numeric-fields");
        if (numericFields) numericFields.style.display = "block";
        break;

      case "valor_especifico":
        const textFields = document.getElementById("text-fields");
        if (textFields) textFields.style.display = "block";
        break;

      case "booleano":
        const booleanFields = document.getElementById("boolean-fields");
        if (booleanFields) booleanFields.style.display = "block";
        break;
    }
  }

  async performFetch(url, options, successMessage, skipReload = false) {
    try {
      const response = await fetch(url, options);
      const data = await response.json();
      if (data.status === "success") {
        this.showSuccess(successMessage || data.message);
        if (!skipReload) this.currentTable?.ajax.reload(null, false);
      } else {
        this.showError(data.message || "Ocurrió un error.");
        if (skipReload) this.currentTable?.ajax.reload(null, false); // Recargar en caso de error para revertir el toggle visual
      }
    } catch (error) {
      this.showError("Error de conexión.");
      if (skipReload) this.currentTable?.ajax.reload(null, false);
    }
  }

  showLoading() {
    if (!this.contentArea) return;
    this.contentArea.innerHTML = `<div class="loading-container"><div class="loading-spinner"></div><p>Cargando...</p></div>`;
  }
  showSuccess(message) {
    CustomDialog?.toast(message, "success");
  }
  showError(message) {
    CustomDialog?.toast(message, "error", 3000);
  }
  showInfo(message) {
    CustomDialog?.toast(message, "info");
  }

  destroy() {
    this.currentTable?.destroy();
    BaseModal?.closeAll();
    this.contentArea = null;
    this.isLoading = false;
  }
}

// ==================== INICIALIZACIÓN Y MANEJO DE ERRORES ====================

let configManager;
document.addEventListener("DOMContentLoaded", () => {
  try {
    configManager = new ConfigManager();
    window.configManager = configManager;
    window.reloadLevelsTable = () => {
      if (configManager.currentSection === "niveles-socioeconomicos") {
        configManager.currentTable?.ajax.reload();
      }
    };
  } catch (error) {
    console.error("Error fatal al inicializar ConfigManager:", error);
    CustomDialog?.error(
      "Error de Inicialización",
      "No se pudo inicializar el gestor de configuración. Por favor, recarga la página."
    );
  }
});

window.addEventListener("beforeunload", () => {
  configManager?.destroy();
});
