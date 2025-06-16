/**
 * Clase BaseModal - Maneja la funcionalidad común de todos los modales
 */
class BaseModal {
  constructor(type, config = {}) {
    this.type = type;
    this.config = {
      id: config.id || `modal-${Date.now()}`,
      title: config.title || "",
      size: config.size || "medium", // small, medium, large
      closable: config.closable !== false,
      backdrop: config.backdrop !== false,
      keyboard: config.keyboard !== false,
      template: config.template || type,
      data: config.data || {},
      onShow: config.onShow || null,
      onHide: config.onHide || null,
      onSubmit: config.onSubmit || null,
      endpoint: config.endpoint || null,
      ...config,
    };

    this.modal = null;
    this.isOpen = false;
    this.modalMouseDownTarget = null;

    this.init();
  }

  init() {
    this.createModal();
    this.bindEvents();
  }

  createModal() {
    // Eliminar modal existente si existe
    const existingModal = document.getElementById(this.config.id);
    if (existingModal) {
      existingModal.remove();
    }

    // Crear estructura base del modal
    const modalHTML = `
      <div id="${this.config.id}" class="base-modal modal-${this.type} modal-${
      this.config.size
    }">
        <div class="base-modal-content">
          <div class="base-modal-header">
            <h2 class="base-modal-title">${this.config.title}</h2>
            ${
              this.config.closable
                ? `
              <button class="base-modal-close" data-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 6L6 18"></path>
                  <path d="M6 6l12 12"></path>
                </svg>
              </button>
            `
                : ""
            }
          </div>
          <div class="base-modal-body" id="${this.config.id}-body">
            ${this.getTemplate()}
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML("beforeend", modalHTML);
    this.modal = document.getElementById(this.config.id);

    console.log("intentando crear modal");
    
    this.attachFormEvents();
  }

  getTemplate() {
    if (
      typeof MODAL_TEMPLATES !== "undefined" &&
      MODAL_TEMPLATES[this.config.template]
    ) {
      return this.processTemplate(
        MODAL_TEMPLATES[this.config.template],
        this.config.data
      );
    }

    // Fallback para templates inline
    if (this.config.content) {
      return this.config.content;
    }

    return '<div class="base-modal-loading">Cargando...</div>';
  }

  processTemplate(template, data) {
    return template.replace(/\{\{(\w+)\}\}/g, (match, key) => {
      return data[key] || "";
    });
  }

  bindEvents() {
    // Guardar referencia a la instancia
    this.modal.__modalInstance = this;

    // Click en botones con data-action
    this.modal.addEventListener("click", (e) => {
      const action = e.target.closest("[data-action]")?.dataset.action;
      if (action) {
        e.preventDefault();
        this.handleAction(action, e.target);
      }
    });

    // Cerrar con ESC
    if (this.config.keyboard) {
      document.addEventListener("keydown", this.handleKeydown.bind(this));
    }

    // Cerrar al hacer click fuera
    if (this.config.backdrop) {
      this.modal.addEventListener("mousedown", this.handleMouseDown.bind(this));
      this.modal.addEventListener("mouseup", this.handleMouseUp.bind(this));
    }
  }

  handleAction(action, element) {
    switch (action) {
      case "close":
        this.hide();
        break;
      case "submit":
        this.handleSubmit(element);
        break;
      default:
        // Permitir acciones personalizadas
        if (
          this.config[`on${action.charAt(0).toUpperCase() + action.slice(1)}`]
        ) {
          this.config[`on${action.charAt(0).toUpperCase() + action.slice(1)}`](
            element,
            this
          );
        }
    }
  }

  handleKeydown(e) {
    if (e.key === "Escape" && this.isOpen) {
      this.hide();
    }
  }

  handleMouseDown(e) {
    if (e.target === this.modal) {
      this.modalMouseDownTarget = e.target;
    }
  }

  handleMouseUp(e) {
    if (this.modalMouseDownTarget === this.modal && e.target === this.modal) {
      this.hide();
    }
    this.modalMouseDownTarget = null;
  }

  attachFormEvents() {
    const form = this.modal.querySelector("form");
    if (form) {

      console.log("intentando agregar action al formulario", this.config.endpoint);

      // Configurar endpoint si se proporciona
      if (this.config.endpoint) {
        form.setAttribute("action", this.config.endpoint);
      }
    }
  }

  async handleSubmit(submitButton) {
    const form = this.modal.querySelector("form");
    if (!form) return;

    if (this.config.onSubmit) {
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      try {
        await this.config.onSubmit(data, this);
      } catch (error) {
        console.error("Error en onSubmit:", error);
      }
    } else {
      // Usar el sistema ajax.js existente
      form.dispatchEvent(new Event("submit"));
    }
  }

  show(data = null) {
    if (this.isOpen) return;

    // Actualizar datos si se proporcionan
    if (data) {
      this.updateContent(data);
    }

    this.isOpen = true;

    // Asegúrate de que esté visible (evita display: none en CSS)
    this.modal.style.display = "flex"; // Por si acaso

    // Forzar un reflow antes de añadir clase .show
    requestAnimationFrame(() => {
      this.modal.classList.add("show");
    });

    document.body.style.overflow = "hidden";

    // Callback onShow
    if (this.config.onShow) {
      this.config.onShow(this);
    }

    return this;
  }

  hide() {
    if (!this.isOpen) return;

    this.isOpen = false;
    this.modal.classList.remove("show");

    // Esperar fin de transición para ocultar del todo
    const handleTransitionEnd = (e) => {
      if (e.propertyName === "opacity") {
        this.modal.style.display = "none";
        this.modal.removeEventListener("transitionend", handleTransitionEnd);
      }
    };

    this.modal.addEventListener("transitionend", handleTransitionEnd);

    document.body.style.overflow = "";

    if (this.config.onHide) {
      this.config.onHide(this);
    }

    // Limpiar formulario si existe
    const form = this.modal.querySelector("form");
    if (form) {
      this.clearFormErrors(form);
    }

    return this;
  }

  updateContent(data) {
    this.config.data = { ...this.config.data, ...data };
    const body = this.modal.querySelector(".base-modal-body");
    if (body) {
      body.innerHTML = this.getTemplate();
      this.attachFormEvents();
    }
  }

  updateTitle(title) {
    const titleElement = this.modal.querySelector(".base-modal-title");
    if (titleElement) {
      titleElement.textContent = title;
    }
  }

  showLoading(message = "Cargando...") {
    const body = this.modal.querySelector(".base-modal-body");
    if (body) {
      body.innerHTML = `
        <div class="base-modal-loading">
          <div class="spinner"></div>
          <p>${message}</p>
        </div>
      `;
    }
  }

  showError(message = "Ha ocurrido un error") {
    const body = this.modal.querySelector(".base-modal-body");
    if (body) {
      body.innerHTML = `
        <div class="base-modal-error">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          <h3>Error</h3>
          <p>${message}</p>
          <button class="btn-retry" data-action="retry">Reintentar</button>
        </div>
      `;
    }
  }

  clearFormErrors(form) {
    // Integrar con el sistema de errors existente
    form.querySelectorAll(".error-message").forEach((el) => el.remove());
    form
      .querySelectorAll(".error-input")
      .forEach((el) => el.classList.remove("error-input"));
  }

  destroy() {
    if (this.modal) {
      // Remover listeners
      document.removeEventListener("keydown", this.handleKeydown.bind(this));

      // Cerrar si está abierto
      if (this.isOpen) {
        this.hide();
      }

      // Remover del DOM
      this.modal.remove();
      this.modal = null;
    }
  }

  // Métodos de utilidad
  static closeAll() {
    document.querySelectorAll(".base-modal.show").forEach((modal) => {
      if (modal.__modalInstance) {
        modal.__modalInstance.hide();
      }
    });
  }

  static getOpenModals() {
    return Array.from(document.querySelectorAll(".base-modal.show"))
      .map((modal) => modal.__modalInstance)
      .filter((instance) => instance);
  }
}

// Factory function simple
function createModal(type, config = {}) {
  return new BaseModal(type, config);
}

// Funciones de conveniencia
const Modal = {
  create: createModal,
  closeAll: BaseModal.closeAll,
  getOpen: BaseModal.getOpenModals,

  // Shortcuts para tipos comunes
  form: (config) => createModal("form", config),
  info: (config) => createModal("info", config),
  confirm: (config) => createModal("confirm", config),
};

// Exportar para uso global
window.BaseModal = BaseModal;
window.createModal = createModal;
window.Modal = Modal;
