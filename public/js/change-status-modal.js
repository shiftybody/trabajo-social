/**
 * Clase para manejar el modal de cambio de estado de usuario
 */
class ChangeStatusModal {
  constructor() {
    this.modal = null;
    this.isOpen = false;
    this.currentUserId = null;
    this.currentUserData = null;
    this.init();
  }

  init() {
    this.createModal();
    this.bindEvents();
  }

  createModal() {
    // Crear el modal si no existe
    if (document.getElementById("changeStatusModal")) {
      this.modal = document.getElementById("changeStatusModal");
      return;
    }

    const modalHTML = `
      <div id="changeStatusModal" class="change-status-modal">
        <div class="change-status-modal-content">
          <div class="change-status-modal-header">
            <h2 class="change-status-modal-title">Cambiar Estado de Usuario</h2>
            <button type="button" class="change-status-modal-close" id="closeChangeStatusModal">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18"></path>
                <path d="M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="change-status-modal-body" id="changeStatusModalBody">
            <div class="user-info-section">
              <div class="user-info-header">
                <div class="user-avatar-status" id="userAvatar"></div>
                <div class="user-basic-info">
                  <h3 id="userNameStatus"></h3>
                  <p id="userUsername"></p>
                </div>
              </div>
            </div>

            <div class="status-change-section">
              <div class="current-status">
                <label class="status-label">Estado actual:</label>
                <span id="currentStatusBadge" class="status-badge"></span>
              </div>
              
              <div class="arrow-change">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14"></path>
                  <path d="m12 5 7 7-7 7"></path>
                </svg>
              </div>
              
              <div class="new-status">
                <label class="status-label">Nuevo estado:</label>
                <span id="newStatusBadge" class="status-badge"></span>
              </div>
            </div>

            <div class="status-warning" id="statusWarning">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              </svg>
              <p id="warningMessage"></p>
            </div>

            <form novalidate id="changeStatusForm" class="change-status-form form-ajax" method="POST">
              <input type="hidden" name="estado" id="newStatusValue">
              
              <div class="change-status-actions">
                <button type="button" class="btn-cancel" id="cancelChangeStatus">Cancelar</button>
                <button type="submit" class="btn-change-status" id="confirmChangeStatus">Cambiar Estado</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML("beforeend", modalHTML);
    this.modal = document.getElementById("changeStatusModal");

    // Agregar estilos CSS
    this.addStyles();

    // Vincular el formulario con los event listeners de ajax.js
    this.attachFormHandlers();
  }

  addStyles() {
    if (document.getElementById("changeStatusModalStyles")) return;

    const styles = `
      <style id="changeStatusModalStyles">
        .change-status-modal {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
          display: flex;
          justify-content: center;
          align-items: center;
          z-index: 10000;
          opacity: 0;
          visibility: hidden;
          transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .change-status-modal.show {
          opacity: 1;
          visibility: visible;
        }

        .change-status-modal-content {
          background: white;
          border-radius: 12px;
          padding: 0;
          width: 90%;
          max-width: 500px;
          max-height: 90vh;
          overflow-y: auto;
          box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .change-status-modal-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 24px 24px 0 24px;
          border-bottom: 1px solid #e5e7eb;
          margin-bottom: 24px;
        }

        .change-status-modal-title {
          font-size: 18px;
          font-weight: 600;
          color: #111827;
          margin: 0;
        }

        .change-status-modal-close {
          background: none;
          border: none;
          cursor: pointer;
          padding: 8px;
          border-radius: 6px;
          color: #6b7280;
          transition: all 0.2s;
        }

        .change-status-modal-close:hover {
          background-color: #f3f4f6;
          color: #374151;
        }

        .change-status-modal-body {
          padding: 0 24px 24px 24px;
        }

        .user-info-section {
          margin-bottom: 24px;
        }

        .user-info-header {
          display: flex;
          align-items: center;
          gap: 16px;
          padding: 16px;
          background-color: #f9fafb;
          border-radius: 8px;
        }

        .user-avatar-status {
          width: 48px;
          height: 48px;
          border-radius: 50%;
          background-color: #e5e7eb;
          background-image: url('${APP_URL}/public/photos/default.jpg');
          background-size: cover;
          background-position: center;
        }

        .user-basic-info h3 {
          margin: 0 0 4px 0;
          font-size: 16px;
          font-weight: 600;
          color: #111827;
        }

        .user-basic-info p {
          margin: 0;
          font-size: 14px;
          color: #6b7280;
        }

        .status-change-section {
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin: 24px 0;
          padding: 20px;
          background-color: #f8fafc;
          border-radius: 8px;
          border: 1px solid #e2e8f0;
        }

        .current-status, .new-status {
          text-align: center;
          flex: 1;
        }

        .status-label {
          display: block;
          font-size: 13px;
          font-weight: 500;
          color: #64748b;
          margin-bottom: 8px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .status-badge {
          display: inline-block;
          padding: 6px 12px;
          border-radius: 20px;
          font-size: 14px;
          font-weight: 600;
          text-transform: capitalize;
        }

        .status-badge.activo {
          background-color:#dcf6fc;
          color:#007bff;
        }

        .status-badge.inactivo {
          background-color: #fee2e2;
          color: #dc2626;
        }

        .arrow-change {
          margin: 0 16px;
          color: #64748b;
        }

        .status-warning {
          display: flex;
          align-items: flex-start;
          gap: 12px;
          padding: 16px;
          background-color: #fffbeb;
          border: 1px solid #fed7aa;
          border-radius: 8px;
          margin: 16px 0;
        }

        .status-warning svg {
          color: #d97706;
          flex-shrink: 0;
          margin-top: 2px;
        }

        .status-warning p {
          margin: 0;
          font-size: 14px;
          color: #92400e;
          line-height: 1.5;
        }

        .change-status-actions {
          display: flex;
          gap: 12px;
          justify-content: flex-end;
          margin-top: 32px;
        }

        .btn-cancel {
          padding: 10px 20px;
          border: 1px solid #d1d5db;
          background-color: white;
          color: #374151;
          border-radius: 6px;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s;
        }

        .btn-cancel:hover {
          background-color: #f9fafb;
          border-color: #9ca3af;
        }

        .btn-change-status {
          padding: 10px 20px;
          border: none;
          background-color: #3b82f6;
          color: white;
          border-radius: 6px;
          font-size: 14px;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.2s;
        }

        .btn-change-status:hover {
          background-color: #2563eb;
        }

        .btn-change-status:disabled {
          background-color: #9ca3af;
          cursor: not-allowed;
        }
      </style>
    `;

    document.head.insertAdjacentHTML("beforeend", styles);
  }

  attachFormHandlers() {
    const form = document.getElementById("changeStatusForm");
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
      if (e.target.closest("#closeChangeStatusModal")) {
        this.close();
      }
    });

    // Cerrar modal con botón cancelar
    document.addEventListener("click", (e) => {
      if (e.target.id === "cancelChangeStatus") {
        this.close();
      }
    });

    // Cerrar modal haciendo clic fuera
    this.modal.addEventListener("mousedown", (e) => {
      modalMouseDownTarget = e.target;
    });

    this.modal.addEventListener("mouseup", (e) => {
      if (
        modalMouseDownTarget === this.modal &&
        e.target === this.modal &&
        !e.target.closest(".change-status-modal-content")
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

    try {
      // Obtener datos del usuario
      await this.loadUserData(userId);

      // Configurar el formulario
      this.setupForm();

      // Mostrar modal
      this.modal.classList.add("show");
      document.body.style.overflow = "hidden";
    } catch (error) {
      console.error("Error al cargar datos del usuario:", error);
      this.close();

      if (typeof CustomDialog !== "undefined") {
        CustomDialog.error(
          "Error",
          "No se pudieron cargar los datos del usuario."
        );
      }
    }
  }

  async loadUserData(userId) {
    try {
      const response = await fetch(`${APP_URL}/api/users/${userId}`, {
        method: "GET",
        credentials: "same-origin",
      });

      if (!response.ok) {
        throw new Error("Error al obtener datos del usuario");
      }

      const data = await response.json();

      if (data.status === "success" && data.data) {
        this.currentUserData = data.data;
        console.log(this.currentUserData);
      } else {
        throw new Error(data.message || "Error al cargar datos del usuario");
      }
    } catch (error) {
      console.error("Error:", error);
      throw error;
    }
  }

  setupForm() {
    if (!this.currentUserData) return;

    const userData = this.currentUserData;
    const currentStatus = userData.estado_id === "1" ? "activo" : "inactivo";
    const newStatus = currentStatus === "activo" ? "inactivo" : "activo";
    const newStatusValue = currentStatus === "activo" ? "0" : "1";

    // Actualizar información del usuario
    document.getElementById(
      "userAvatar"
    ).style.backgroundImage = `url('${APP_URL}/public/photos/thumbnail/${userData.avatar}')`;
    document.getElementById(
      "userNameStatus"
    ).textContent = `${userData.nombre} ${userData.apellido_paterno} ${userData.apellido_materno}`;
    document.getElementById(
      "userUsername"
    ).textContent = `@${userData.usuario}`;

    // Actualizar badges de estado
    const currentBadge = document.getElementById("currentStatusBadge");
    const newBadge = document.getElementById("newStatusBadge");

    currentBadge.textContent = currentStatus;
    currentBadge.className = `status-badge ${currentStatus}`;

    newBadge.textContent = newStatus;
    newBadge.className = `status-badge ${newStatus}`;

    // Configurar mensaje de advertencia
    const warningMessage = document.getElementById("warningMessage");
    if (newStatus === "inactivo") {
      warningMessage.textContent =
        "El usuario no podrá acceder al sistema hasta que su estado sea reactivado.";
    } else {
      warningMessage.textContent =
        "El usuario podrá acceder nuevamente al sistema con sus credenciales actuales.";
    }

    // Configurar formulario
    document.getElementById("newStatusValue").value = newStatusValue;
    const form = document.getElementById("changeStatusForm");
    form.action = `${APP_URL}/api/users/${this.currentUserId}/status`;

    // Actualizar texto del botón
    const submitBtn = document.getElementById("confirmChangeStatus");
    submitBtn.textContent =
      newStatus === "activo" ? "Activar Usuario" : "Desactivar Usuario";
  }

  close() {
    return new Promise((resolve) => {
      if (!this.isOpen) {
        resolve();
        return;
      }

      this.isOpen = false;
      this.currentUserId = null;
      this.currentUserData = null;

      // Ocultar modal
      this.modal.classList.remove("show");
      document.body.style.overflow = "";

      // Esperar a que termine la transición
      setTimeout(() => {
        resolve();
      }, 300);
    });
  }
}
