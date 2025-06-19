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
    this.categoriesData = [];

    this.init();
  }

  /**
   * ACTUALIZADO: Eliminar el método bindNavigationEvents anterior
   * y actualizar el método init
   */
  async init() {
    try {
      this.cacheElements();
      await this.loadLevelsData();
      await this.loadCriteriaNavigation(); // Esto configurará todos los eventos
      this.loadInitialSection();
    } catch (error) {
      console.error("Error initializing ConfigManager:", error);
      this.showError("Error al inicializar el gestor de configuración");
    }
  }

  /**
   * Cachea los elementos necesarios del DOM para evitar búsquedas repetidas.
   */
  cacheElements() {
    this.contentArea = document.getElementById("config-content-area");
    if (!this.contentArea) {
      throw new Error("Elemento config-content-area no encontrado");
    }
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
        // solo activos y que no sea EXENTO
        this.levelsData = data.data.filter(
          (level) => level.estado == 1 && level.nivel.toUpperCase() !== "EXENTO"
        );
      }
    } catch (error) {
      console.error("Error loading levels data:", error);
    }
  }

  // ==================== NAVEGACIÓN DINÁMICA DE CRITERIOS ====================

  /**
   * ACTUALIZADO: loadCriteriaNavigation ahora configura eventos
   */
  async loadCriteriaNavigation() {
    try {
      const response = await fetch(`${this.baseUrl}/navigation`);
      const data = await response.json();

      if (data.status === "success") {
        this.categoriesData = data.data;
        this.renderCriteriaNavigation(data.data);
        // Los eventos ya se configuran dentro de renderCriteriaNavigation
      }
    } catch (error) {
      console.error("Error loading criteria navigation:", error);
      this.showError("Error al cargar navegación de criterios");
    }
  }

  /**
   * ACTUALIZADO: Renderiza toda la navegación dinámicamente
   * (reemplaza el contenido completo del nav)
   */
  renderCriteriaNavigation(categories) {
    const navigationContainer = document.querySelector(".config-nav");
    if (!navigationContainer) return;

    // Generar HTML completo de navegación
    let html = `
    <!-- Sección estática de configuración general -->
    <div class="config-nav-group">
      <h3>General</h3>
      <a href="#niveles-socioeconomicos" class="static-nav-link">Niveles Socioeconómicos</a>
      <a href="#reglas-aportacion" class="static-nav-link">Reglas de Aportación</a>
    </div>
  `;

    // Generar grupos dinámicos por categoría
    categories.forEach((category) => {
      html += `<div class="config-nav-group">`;
      html += `<h3>${category.nombre}</h3>`;

      category.subcategorias.forEach((subcategoria) => {
        html += `<a href="#criterio-${subcategoria.id}" 
                 data-subcategory-id="${subcategoria.id}" 
                 class="criteria-nav-link">
                 ${subcategoria.nombre}
               </a>`;
      });

      html += `</div>`;
    });

    navigationContainer.innerHTML = html;
    this.setupNavigationEvents(); // Reconfigurar todos los eventos
  }

  /**
   * ACTUALIZADO: Configura eventos para TODA la navegación
   * (tanto estática como dinámica)
   */
  setupNavigationEvents() {
    // Eventos para navegación estática (General)
    const staticLinks = document.querySelectorAll(".static-nav-link");
    staticLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        if (this.isLoading) return;

        const section = link.getAttribute("href").substring(1);
        this.loadSection(section);
      });
    });

    // Eventos para navegación dinámica (Criterios)
    const criteriaLinks = document.querySelectorAll(".criteria-nav-link");
    criteriaLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        if (this.isLoading) return;

        const subcategoryId = link.getAttribute("data-subcategory-id");
        this.loadCriteriaBySubcategory(subcategoryId);
      });
    });
  }
  /**
   * NUEVO: Carga criterios por subcategoría
   */
  async loadCriteriaBySubcategory(subcategoryId) {
    if (this.isLoading) return;

    try {
      this.isLoading = true;
      this.showLoading();

      // Encontrar el nombre de la subcategoría para el título
      let subcategoryName = "Criterios de Puntuación";
      for (const category of this.categoriesData) {
        const subcategoria = category.subcategorias.find(
          (sub) => sub.id == subcategoryId
        );
        if (subcategoria) {
          subcategoryName = subcategoria.nombre;
          break;
        }
      }

      this.currentSection = `criterio-${subcategoryId}`;
      localStorage.setItem("configManager_currentSection", this.currentSection);
      this.updateActiveNavLink(this.currentSection);

      // Destruir tabla anterior
      if (this.currentTable) {
        this.currentTable.destroy();
        this.currentTable = null;
      }

      // Crear HTML para la sección de criterios
      const html = `
        <div class="config-content-header">
          <h2>${subcategoryName}</h2>
          <button class="btn-primary" onclick="configManager.createCriteria(${subcategoryId})">
            Nuevo Criterio
          </button>
        </div>
        <div class="table-container">
          <table id="criteria-table" class="hover nowrap cell-borders" style="width: 100%;">
            <thead>
              <tr>
                <th>CRITERIO</th>
                <th class="dt-head-center">PUNTUACIÓN</th>
                <th class="dt-head-center">ACCIONES</th>
              </tr>
            </thead>
          </table>
        </div>
      `;

      this.contentArea.innerHTML = html;
      await this.initializeCriteriaTable(subcategoryId);
    } catch (error) {
      console.error("Error loading criteria by subcategory:", error);
      this.showError("Error al cargar criterios");
    } finally {
      this.isLoading = false;
      if (this.contentArea) {
        this.contentArea.classList.remove("content-loading");
      }
    }
  }

  /**
   * ACTUALIZADO: Inicializa DataTable para criterios con 4 columnas (CRITERIO | PUNTUACIÓN | ESTADO | ACCIONES)
   */
  async initializeCriteriaTable(subcategoryId) {
    this.currentTable = new DataTable("#criteria-table", {
      ajax: {
        url: `${this.baseUrl}/criteria?subcategory_id=${subcategoryId}`,
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
          render: (data, type, row) => {
            // Para ordenar, devuelve el valor numérico original
            if (type === "sort" || type === "type") {
              return data;
            }
            // Para mostrar, devuelve el formato con "pts"
            return `<span class="score-badge">${data} pts</span>`;
          },
        },
        {
          data: "estado",
          className: "dt-body-center",
          render: (data, type, row) => `
          <label class="toggle-switch">
            <input type="checkbox" ${data == 1 ? "checked" : ""} 
                   onchange="configManager.toggleCriteriaStatus(${
                     row.id
                   }, this.checked)">
            <span class="toggle-slider"></span>
          </label>`,
        },
        {
          data: "acciones",
          className: "dt-body-center",
          orderable: false,
          render: (data, type, row) => `
          <button type="button" class="editar" onclick="configManager.editCriteria(${row.id})" title="Editar Criterio">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
              <path d="M13.5 6.5l4 4" />
            </svg>
          </button>
          <button type="button" class="remover" onclick="configManager.deleteCriteria(${row.id})" title="Eliminar Criterio">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M4 7l16 0" />
              <path d="M10 11l0 6" />
              <path d="M14 11l0 6" />
              <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
              <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
            </svg>
          </button>`,
        },
      ],
      paging: false,
      info: false,
      order: [[1, "asc"]],
      language: {
        emptyTable: "No hay criterios configurados para esta subcategoría",
        processing: '<div class="table-spinner"></div>Procesando...',
        zeroRecords:
          "No se encontraron criterios que coincidan con la búsqueda",
      },
    });
  }

  /**
   * Configura los eventos de clic para los enlaces de navegación.
   */
  bindNavigationEvents() {
    const navLinks = document.querySelectorAll(
      ".config-nav a:not([data-subcategory-id])"
    );

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

    // Si es una sección de criterio, extraer el ID y cargar
    if (sectionToLoad.startsWith("criterio-")) {
      const subcategoryId = sectionToLoad.replace("criterio-", "");
      this.loadCriteriaBySubcategory(subcategoryId);
    } else {
      this.loadSection(sectionToLoad);
    }
  }

  /**
   * Carga una sección específica.
   */
  async loadSection(section) {
    if (this.isLoading) return;

    try {
      this.isLoading = true;
      this.showLoading();
      this.currentSection = section;

      localStorage.setItem("configManager_currentSection", section);
      this.updateActiveNavLink(section);

      if (this.currentTable) {
        this.currentTable.destroy();
        this.currentTable = null;
      }

      switch (section) {
        case "niveles-socioeconomicos":
          await this.loadLevelsSection();
          break;
        case "reglas-aportacion":
          await this.loadLevelsData();
          await this.loadRulesSection();
          break;
        default:
          this.showError("Sección no implementada");
      }
    } catch (error) {
      console.error("Error loading section:", error);
      this.showError("Error al cargar la sección");
    } finally {
      this.isLoading = false;
      if (this.contentArea) {
        this.contentArea.classList.remove("content-loading");
      }
    }
  }

  /**
   * Actualiza el enlace de navegación activo
   */
  updateActiveNavLink(section) {
    const allNavLinks = document.querySelectorAll(".config-nav a");
    allNavLinks.forEach((link) => {
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
              <th class="dt-head-center">EDAD</th><th class="dt-head-center">PERIODICIDAD</th>
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
      paging: false,
      info: false,
      order: [[0, "asc"]],
      language: {
        emptyTable:
          "El nivel no ha sido seleccionado o no tiene reglas configuradas",
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
   * Abre modal para crear criterio
   */
  createCriteria(subcategoryId) {
    if (typeof mostrarModalCrearCriterio === "function") {
      mostrarModalCrearCriterio(subcategoryId);
    } else {
      console.error("mostrarModalCrearCriterio no está disponible");
      this.showError("Error al abrir modal de creación");
    }
  }

  /**
   * Abre modal para editar criterio
   */
  editCriteria(criteriaId) {
    if (typeof mostrarModalEditarCriterio === "function") {
      mostrarModalEditarCriterio(criteriaId);
    } else {
      console.error("mostrarModalEditarCriterio no está disponible");
      this.showError("Error al abrir modal de edición");
    }
  }

  /**
   * Elimina criterio con confirmación
   */
  async deleteCriteria(criteriaId) {
    const confirm = await CustomDialog.confirm(
      "Confirmar Eliminación",
      "¿Está seguro de eliminar este criterio de puntuación? Esta acción no se puede deshacer.",
      "Eliminar",
      "Cancelar"
    );

    if (!confirm) return;

    try {
      const response = await fetch(`${this.baseUrl}/criteria/${criteriaId}`, {
        method: "DELETE",
      });

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Criterio eliminado correctamente");
        this.currentTable?.ajax.reload(null, false);
      } else {
        this.showError(data.message || "Error al eliminar criterio");
      }
    } catch (error) {
      console.error("Error deleting criteria:", error);
      this.showError("Error de conexión");
    }
  }

  /**
   *  Cambia estado de criterio
   */
  async toggleCriteriaStatus(criteriaId, estado) {
    try {
      const response = await fetch(
        `${this.baseUrl}/criteria/${criteriaId}/status`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ estado }),
        }
      );

      const data = await response.json();

      if (data.status === "success") {
        this.showSuccess("Estado actualizado correctamente");
      } else {
        this.showError(data.message || "Error al cambiar estado");
        this.currentTable?.ajax.reload(null, false);
      }
    } catch (error) {
      console.error("Error toggling criteria status:", error);
      this.showError("Error de conexión");
      this.currentTable?.ajax.reload(null, false);
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
        if (skipReload) this.currentTable?.ajax.reload(null, false);
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

// ==================== INICIALIZACIÓN ====================

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

/**
 * Función mejorada para alternar campos con guardado de estado
 */
function toggleCriteriaFields(tipoCriterio) {
  // Objeto para guardar el estado de los campos
  if (!window.criteriaFieldsState) {
    window.criteriaFieldsState = {
      rango_numerico: {
        valor_minimo: "",
        valor_maximo: "",
      },
      valor_especifico: {
        valor_texto: "",
      },
      // booleano no necesita estado ya que no tiene inputs
    };
  }

  // Guardar el estado actual antes de cambiar
  saveCurrentFieldsState();

  // Ocultar todos los campos
  const allFields = document.querySelectorAll(".criteria-fields");
  allFields.forEach((field) => {
    field.style.display = "none";
  });

  // Mostrar y restaurar campos según el tipo seleccionado
  switch (tipoCriterio) {
    case "rango_numerico":
      const numericFields = document.getElementById("numeric-fields");
      if (numericFields) {
        numericFields.style.display = "block";
        // Restaurar valores guardados
        restoreFieldValue("valor_minimo");
        restoreFieldValue("valor_maximo");
      }
      break;

    case "valor_especifico":
      const textFields = document.getElementById("text-fields");
      if (textFields) {
        textFields.style.display = "block";
        // Restaurar valor guardado
        restoreFieldValue("valor_texto");
      }
      break;

    case "booleano":
      const booleanFields = document.getElementById("boolean-fields");
      if (booleanFields) {
        booleanFields.style.display = "block";
      }
      break;
  }
}

/**
 * Guarda el estado actual de los campos visibles
 */
function saveCurrentFieldsState() {
  // Guardar campos numéricos si están visibles
  const numericFields = document.getElementById("numeric-fields");
  if (numericFields && numericFields.style.display !== "none") {
    const valorMinimo = document.getElementById("valor_minimo");
    const valorMaximo = document.getElementById("valor_maximo");

    if (valorMinimo) {
      window.criteriaFieldsState.rango_numerico.valor_minimo =
        valorMinimo.value;
    }
    if (valorMaximo) {
      window.criteriaFieldsState.rango_numerico.valor_maximo =
        valorMaximo.value;
    }
  }

  // Guardar campo de texto si está visible
  const textFields = document.getElementById("text-fields");
  if (textFields && textFields.style.display !== "none") {
    const valorTexto = document.getElementById("valor_texto");
    if (valorTexto) {
      window.criteriaFieldsState.valor_especifico.valor_texto =
        valorTexto.value;
    }
  }
}

/**
 * Restaura el valor de un campo específico desde el estado guardado
 */
function restoreFieldValue(fieldName) {
  const input = document.getElementById(fieldName);
  if (!input) return;

  let savedValue = "";

  // Buscar el valor guardado según el campo
  if (fieldName === "valor_minimo" || fieldName === "valor_maximo") {
    savedValue = window.criteriaFieldsState.rango_numerico[fieldName] || "";
  } else if (fieldName === "valor_texto") {
    savedValue = window.criteriaFieldsState.valor_especifico[fieldName] || "";
  }

  // Restaurar el valor
  input.value = savedValue;
}

/**
 * Inicializa el estado con valores existentes (para modo edición)
 */
function initializeCriteriaFieldsState(data) {
  if (!window.criteriaFieldsState) {
    window.criteriaFieldsState = {
      rango_numerico: {
        valor_minimo: "",
        valor_maximo: "",
      },
      valor_especifico: {
        valor_texto: "",
      },
    };
  }

  // Si hay datos existentes, inicializar el estado
  if (data) {
    if (data.tipo_criterio === "rango_numerico") {
      window.criteriaFieldsState.rango_numerico.valor_minimo =
        data.valor_minimo || "";
      window.criteriaFieldsState.rango_numerico.valor_maximo =
        data.valor_maximo || "";
    } else if (data.tipo_criterio === "valor_especifico") {
      window.criteriaFieldsState.valor_especifico.valor_texto =
        data.valor_texto || "";
    }
  }
}

/**
 * Limpia el estado guardado (útil al cerrar modal o crear nuevo)
 */
function clearCriteriaFieldsState() {
  window.criteriaFieldsState = {
    rango_numerico: {
      valor_minimo: "",
      valor_maximo: "",
    },
    valor_especifico: {
      valor_texto: "",
    },
  };
}

window.toggleCriteriaFields = toggleCriteriaFields;
window.initializeCriteriaFieldsState = initializeCriteriaFieldsState;
window.clearCriteriaFieldsState = clearCriteriaFieldsState;

window.addEventListener("beforeunload", () => {
  configManager?.destroy();
});
