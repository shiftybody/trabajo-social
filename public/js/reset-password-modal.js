/**
 * Clase para manejar el modal de reset de contraseña
 */
class ResetPasswordModal {
  constructor(usuario_id) {
    this.modal = null;
    this.isOpen = false;
    this.currentUserId = usuario_id;
    this.init();
  }

  init() {
    this.createModal();
    this.bindEvents();
  }

  createModal() {
    // Crear el modal si no existe
    if (document.getElementById("resetPasswordModal")) {
      this.modal = document.getElementById("resetPasswordModal");
      return;
    }

    const modalHTML = `
      <div id="resetPasswordModal" class="reset-password-modal">
        <div class="reset-password-modal-content">
          <div class="reset-password-modal-header">
            <h2 class="reset-password-modal-title">Resetear Contraseña</h2>
            <button type="button" class="reset-password-modal-close" id="closeResetPasswordModal">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18"></path>
                <path d="M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="reset-password-modal-body" id="resetPasswordModalBody">
            <form novalidate id="resetPasswordForm" class="reset-password-form form-ajax" action="${APP_URL}/api/users/${this.currentUserId}/reset-password" method="POST">
              
              <div class="password-fields">
                <div class="input-field">
                  <label for="newPassword" class="field-label">Nueva contraseña</label>
                  <input type="password" name="password" id="newPassword" class="input-reset" placeholder="Nueva contraseña"
                    pattern="(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20" required>
                </div>
                
                <div class="input-field">
                  <label for="confirmPassword" class="field-label">Confirmar contraseña</label>
                  <input type="password" name="password2" id="confirmPassword" class="input-reset" placeholder="Confirmar contraseña"
                    pattern="(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20" required>
                </div>
              </div>
              
              <div class="reset-password-actions">
                <button type="button" class="btn-cancel" id="cancelResetPassword">Cancelar</button>
                <button type="submit" class="btn-reset">Resetear Contraseña</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML("beforeend", modalHTML);
    this.modal = document.getElementById("resetPasswordModal");

    // Vincular el formulario recién creado con los event listeners de ajax.js
    this.attachFormHandlers();
  }

  attachFormHandlers() {
    const form = document.getElementById("resetPasswordForm");
    if (!form) return;

    // Usar la función global de ajax.js para adjuntar los manejadores
    if (window.attachAjaxFormHandlers) {
      window.attachAjaxFormHandlers(form);
    } else {
      console.error(
        "La función attachAjaxFormHandlers no está disponible. Asegúrese de que ajax.js se haya cargado antes."
      );
    }
  }

  bindEvents() {
    let modalMouseDownTarget = null;

    // Guardar referencia a la instancia en el modal
    if (this.modal) {
      this.modal.__modalInstance = this;
    }

    // Cerrar modal con botón X
    document.addEventListener("click", (e) => {
      if (e.target.closest("#closeResetPasswordModal")) {
        this.close();
      }
    });

    // Cerrar modal con botón cancelar
    document.addEventListener("click", (e) => {
      if (e.target.id === "cancelResetPassword") {
        this.close();
      }
    });

    // Cerrar modal haciendo clic fuera (mejorado)
    this.modal.addEventListener("mousedown", (e) => {
      modalMouseDownTarget = e.target;
    });

    this.modal.addEventListener("mouseup", (e) => {
      if (
        modalMouseDownTarget === this.modal &&
        e.target === this.modal &&
        !e.target.closest(".reset-password-modal-content")
      ) {
        this.close();
      }
      modalMouseDownTarget = null;
    });

    // Cerrar modal con tecla ESC
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isOpen) {
        this.close();
      }
    });
  }

  async show(userId) {
    if (!userId || this.isOpen) return;

    this.currentUserId = userId;
    this.isOpen = true;

    // Actualizar la acción del formulario con el nuevo userId
    const form = document.getElementById("resetPasswordForm");
    if (form) {
      form.action = `${APP_URL}/api/users/${userId}/reset-password`;
    }

    // Mostrar modal
    this.modal.classList.add("show");
    document.body.style.overflow = "hidden";

    // Limpiar formulario
    this.resetForm();
  }

  close() {
    if (!this.isOpen) return;

    this.isOpen = false;
    this.currentUserId = null;

    // Ocultar modal con transición suave
    this.modal.classList.remove("show");
    document.body.style.overflow = "";

    // Limpiar el formulario cuando se cierre
    setTimeout(() => {
      this.resetForm();
    }, 300); // Esperar a que termine la transición CSS
  }

  resetForm() {
    const form = document.getElementById("resetPasswordForm");
    if (form) {
      form.reset();
      // Limpiar todos los mensajes de error
      document
        .querySelectorAll("#resetPasswordModal .error-message")
        .forEach((errorMsg) => errorMsg.remove());
      document
        .querySelectorAll("#resetPasswordModal .error-input")
        .forEach((errorInput) => errorInput.classList.remove("error-input"));
    }
  }
}
