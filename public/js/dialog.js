// Sistema de Diálogos Personalizado
class CustomDialog {
  static icons = {
    success: "✓",
    error: "✕",
    warning: "!",
    info: "i",
    question: "?",
  };

  static create(options) {
    return new Promise((resolve) => {
      // Crear elementos
      const overlay = document.createElement("div");
      overlay.className = "dialog-overlay";
      const container = document.createElement("div");
      container.className = "dialog-container";

      // Header con icono y botón de cerrar
      const header = document.createElement("div");
      header.className = "dialog-header";

      // Contenedor flex para icono/título y botón cerrar
      const headerContent = document.createElement("div");
      headerContent.className = "dialog-header-content";

      // Contenedor para icono y título
      const titleContainer = document.createElement("div");
      titleContainer.className = "dialog-title-container";

      if (options.icon) {
        const iconDiv = document.createElement("div");
        iconDiv.className = `dialog-icon ${options.icon}`;
        // NO agregar texto - solo es un punto de color
        titleContainer.appendChild(iconDiv);
      }

      if (options.title) {
        const title = document.createElement("h3");
        title.className = "dialog-title";
        title.textContent = options.title;
        titleContainer.appendChild(title);
      }

      headerContent.appendChild(titleContainer);

      // Botón de cerrar con SVG (solo si no se especifica que no se muestre)
      if (options.showCloseButton !== false) {
        const closeBtn = document.createElement("button");
        closeBtn.className = "dialog-close-btn";
        closeBtn.setAttribute("aria-label", "Cerrar");
        closeBtn.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M18 6l-12 12" />
            <path d="M6 6l12 12" />
          </svg>
        `;
        closeBtn.onclick = () => closeDialog({ isDismissed: true });
        headerContent.appendChild(closeBtn);
      }

      header.appendChild(headerContent);
      container.appendChild(header);

      // Mensaje
      if (options.text) {
        const message = document.createElement("p");
        message.className = "dialog-message";
        message.innerHTML = options.text;
        container.appendChild(message);
      }

      // Input si es necesario
      let inputElement = null;
      if (options.input) {
        const inputDiv = document.createElement("div");
        inputDiv.className = "dialog-input";
        inputElement = document.createElement("input");
        inputElement.type = options.input;
        inputElement.placeholder = options.inputPlaceholder || "";
        inputElement.value = options.inputValue || "";
        inputDiv.appendChild(inputElement);
        container.appendChild(inputDiv);
        // Focus en el input cuando se muestre
        setTimeout(() => inputElement.focus(), 100);
      }

      // Botones
      const buttonsDiv = document.createElement("div");
      buttonsDiv.className = "dialog-buttons";

      // Función para cerrar el diálogo
      const closeDialog = (value) => {
        overlay.classList.remove("show");
        setTimeout(() => {
          document.body.removeChild(overlay);
          resolve(value);
        }, 300);
      };

      // Botón de confirmación
      if (options.showConfirmButton !== false) {
        const confirmBtn = document.createElement("button");
        confirmBtn.className = `dialog-btn ${
          options.confirmButtonClass || "dialog-btn-primary"
        }`;
        confirmBtn.textContent = options.confirmButtonText || "Aceptar";
        confirmBtn.onclick = () => {
          const value = inputElement ? inputElement.value : true;
          closeDialog({ isConfirmed: true, value });
        };
        buttonsDiv.appendChild(confirmBtn);
      }

      // Botón de cancelación
      if (options.showCancelButton) {
        const cancelBtn = document.createElement("button");
        cancelBtn.className = `dialog-btn ${
          options.cancelButtonClass || "dialog-btn-secondary"
        }`;
        cancelBtn.textContent = options.cancelButtonText || "Cancelar";
        cancelBtn.onclick = () => closeDialog({ isConfirmed: false });
        buttonsDiv.appendChild(cancelBtn);
      }

      // Botón de negación
      if (options.showDenyButton) {
        const denyBtn = document.createElement("button");
        denyBtn.className = `dialog-btn ${
          options.denyButtonClass || "dialog-btn-danger"
        }`;
        denyBtn.textContent = options.denyButtonText || "Denegar";
        denyBtn.onclick = () => closeDialog({ isDenied: true });
        buttonsDiv.appendChild(denyBtn);
      }

      container.appendChild(buttonsDiv);
      overlay.appendChild(container);

      // Cerrar con ESC
      const handleEsc = (e) => {
        if (e.key === "Escape" && options.allowEscapeKey !== false) {
          closeDialog({ isDismissed: true });
          document.removeEventListener("keydown", handleEsc);
        }
      };
      document.addEventListener("keydown", handleEsc);

      // Cerrar al hacer clic fuera
      if (options.allowOutsideClick !== false) {
        overlay.onclick = (e) => {
          if (e.target === overlay) {
            closeDialog({ isDismissed: true });
          }
        };
      }

      // Agregar al DOM y mostrar
      document.body.appendChild(overlay);
      requestAnimationFrame(() => {
        overlay.classList.add("show");
      });

      // Timer automático
      if (options.timer) {
        setTimeout(() => {
          closeDialog({ isDismissed: true });
        }, options.timer);
      }
    });
  }

  // Métodos de conveniencia
  static async confirm(title, text, confirmText = "Sí", cancelText = "No") {
    const result = await this.create({
      title,
      text,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: cancelText,
      confirmButtonClass: "dialog-btn-primary",
      cancelButtonClass: "dialog-btn-secondary",
    });
    return result.isConfirmed;
  }

  static async alert(title, text, type = "info") {
    await this.create({
      title,
      text,
      icon: type,
      confirmButtonText: "Aceptar",
    });
  }

  static async success(title, text) {
    await this.alert(title, text, "success");
  }

  static async error(title, text) {
    await this.alert(title, text, "error");
  }

  static async warning(title, text) {
    await this.alert(title, text, "warning");
  }

  static async info(title, text) {
    await this.alert(title, text, "info");
  }

  static async input(title, text, inputType = "text", placeholder = "") {
    const result = await this.create({
      title,
      text,
      icon: "question",
      input: inputType,
      inputPlaceholder: placeholder,
      showCancelButton: true,
      confirmButtonText: "Aceptar",
      cancelButtonText: "Cancelar",
    });
    return result.isConfirmed ? result.value : null;
  }

  // Sistema de Toast
  static toast(message, type = "info", duration = 3000) {
    const container =
      document.getElementById("toastContainer") || this.createToastContainer();

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    const icon = document.createElement("span");
    icon.className = "toast-icon";

    const messageEl = document.createElement("span");
    messageEl.className = "toast-message";
    messageEl.textContent = message;

    const closeBtn = document.createElement("button");
    closeBtn.className = "toast-close";
    closeBtn.textContent = "×";
    closeBtn.onclick = () => removeToast();

    toast.appendChild(icon);
    toast.appendChild(messageEl);
    toast.appendChild(closeBtn);

    container.appendChild(toast);

    // Animación de entrada
    requestAnimationFrame(() => {
      toast.classList.add("show");
    });

    // Función para remover el toast
    const removeToast = () => {
      toast.classList.remove("show");
      setTimeout(() => {
        container.removeChild(toast);
      }, 300);
    };

    // Auto-remover después de la duración
    if (duration) {
      setTimeout(removeToast, duration);
    }
  }

  static createToastContainer() {
    const container = document.createElement("div");
    container.className = "toast-container";
    container.id = "toastContainer";
    document.body.appendChild(container);
    return container;
  }
}

// Crear el contenedor de toasts al cargar
document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("toastContainer")) {
    const container = document.createElement("div");
    container.className = "toast-container";
    container.id = "toastContainer";
    document.body.appendChild(container);
  }
});
