/**
 * Clase para manejar el modal de detalles de usuario
 */
class UserDetailsModal {
  constructor() {
    this.modal = null;
    this.isOpen = false;
    this.currentUserId = null;
    this.modalMouseDownTarget = null;
    this.init();
  }

  init() {
    this.createModal();
    this.bindEvents();
  }

  createModal() {
    // Crear el modal si no existe
    if (document.getElementById("userDetailsModal")) {
      this.modal = document.getElementById("userDetailsModal");
      return;
    }

    const modalHTML = `
      <div id="userDetailsModal" class="user-details-modal">
        <div class="user-details-modal-content">
          <div class="user-details-modal-header">
            <h2 class="user-details-modal-title">Detalles del Usuario</h2>
            <button type="button" class="user-details-modal-close" id="closeUserDetailsModal">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18"></path>
                <path d="M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="user-details-modal-scroll-container">
            <div class="user-details-modal-body" id="userDetailsModalBody">
              <!-- El contenido se carga dinámicamente -->
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML("beforeend", modalHTML);
    this.modal = document.getElementById("userDetailsModal");
  }

  bindEvents() {
    // Guardar referencia a la instancia en el modal
    if (this.modal) {
      this.modal.__modalInstance = this;
    }

    // Cerrar modal con botón X
    document.addEventListener("click", (e) => {
      if (e.target.closest("#closeUserDetailsModal")) {
        this.close();
      }
    });

    // Cerrar modal haciendo clic fuera (mejorado)
    this.modal.addEventListener("mousedown", (e) => {
      // Solo guardar el target si el mousedown fue en el modal mismo (overlay)
      if (e.target === this.modal) {
        this.modalMouseDownTarget = e.target;
      }
    });

    this.modal.addEventListener("mouseup", (e) => {
      // Solo cerrar si:
      // - El mousedown fue en el overlay
      // - El mouseup también fue en el overlay
      if (this.modalMouseDownTarget === this.modal && e.target === this.modal) {
        this.close();
      }
      this.modalMouseDownTarget = null;
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

    // Mostrar modal
    this.modal.classList.add("show");
    document.body.style.overflow = "hidden";

    // Mostrar estado de carga
    this.showLoading();

    try {
      // Cargar datos del usuario
      const userData = await this.fetchUserDetails(userId);
      this.showUserDetails(userData);
    } catch (error) {
      console.error("Error al cargar detalles del usuario:", error);
      this.showError("No se pudo cargar la información del usuario");
    }
  }

  close() {
    if (!this.isOpen) return;

    this.isOpen = false;
    this.currentUserId = null;
    this.modalMouseDownTarget = null;

    // Ocultar modal
    this.modal.classList.remove("show");
    document.body.style.overflow = "";

    // Limpiar el contenido del modal
    const body = document.getElementById("userDetailsModalBody");
    if (body) {
      body.innerHTML = "";
    }
  }

  async fetchUserDetails(userId) {
    const response = await fetch(`${APP_URL}/api/users/${userId}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.status !== "success") {
      throw new Error(data.mensaje || "Error al obtener datos del usuario");
    }

    return data.data;
  }

  showLoading() {
    const body = document.getElementById("userDetailsModalBody");
    body.innerHTML = `
      <div class="user-details-loading">
        <div class="spinner"></div>
        <p>Cargando información del usuario...</p>
      </div>
    `;
  }

  showError(message) {
    const body = document.getElementById("userDetailsModalBody");
    body.innerHTML = `
      <div class="user-details-error">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <h3>Error al cargar</h3>
        <p>${message}</p>
        <button type="button" class="btn-retry" onclick="userDetailsModal.retry()">
          Reintentar
        </button>
      </div>
    `;
  }

  retry() {
    if (this.currentUserId) {
      this.show(this.currentUserId);
    }
  }

  showUserDetails(user) {
    // Determinar la URL del avatar
    const avatarUrl = this.getAvatarUrl(user.avatar);

    // Determinar el estado del usuario
    const isActive = user.estado_id == 1;
    const statusClass = isActive ? "active" : "inactive";
    const statusText = user.estado || (isActive ? "Activo" : "Inactivo");

    const body = document.getElementById("userDetailsModalBody");
    body.innerHTML = `
      <!-- Sección de perfil -->
      <div class="user-profile-section">
        <div class="user-avatar-large" style="background-image: url('${avatarUrl}')"></div>
        <div class="user-profile-info">
          <h3>${this.escapeHtml(user.nombre_completo)}</h3>
          <p class="user-username">@${this.escapeHtml(user.usuario)}</p>
          <div class="user-status-badge ${statusClass}">
            <span class="status-dot ${statusClass}"></span>
            ${this.escapeHtml(statusText)}
          </div>
        </div>
      </div>
  
      <!-- Flex de detalles -->
      <div class="user-details-grid">
        <!-- Información personal -->
        <div class="user-detail-section">
          <h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Información Personal
          </h4>
          <div class="user-detail-item">
            <span class="user-detail-label">Nombre:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.nombre
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Apellido Paterno:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.apellido_paterno
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Apellido Materno:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.apellido_materno || "No especificado"
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Teléfono:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.telefono
            )}</span>
          </div>
        </div>
  
        <!-- Información de cuenta -->
        <div class="user-detail-section">
          <h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Información de Cuenta
          </h4>
          <div class="user-detail-item">
            <span class="user-detail-label">Usuario:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.usuario
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Email:</span>
            <span class="user-detail-value">${this.escapeHtml(
              user.email
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Rol:</span>
            <span class="user-detail-value">${this.escapeHtml(user.rol)}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Estado:</span>
            <span class="user-detail-value">
              <span class="user-status-badge ${statusClass}">
                <span class="status-dot ${statusClass}"></span>
                ${this.escapeHtml(statusText)}
              </span>
            </span>
          </div>
        </div>
  
        <!-- Información del sistema -->
        <div class="user-detail-section">
          <h4>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12,6 12,12 16,14"></polyline>
            </svg>
            Información del Sistema
          </h4>
          <div class="user-detail-item">
            <span class="user-detail-label">ID:</span>
            <span class="user-detail-value">#${user.id}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Creado:</span>
            <span class="user-detail-value">${this.formatDate(
              user.fecha_creacion
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Modificado:</span>
            <span class="user-detail-value">${this.formatDate(
              user.ultima_modificacion
            )}</span>
          </div>
          <div class="user-detail-item">
            <span class="user-detail-label">Último acceso:</span>
            <span class="user-detail-value">${this.formatDate(
              user.ultimo_acceso
            )}</span>
          </div>
        </div>
      </div>
    `;
  }

  getAvatarUrl(avatar) {
    if (!avatar || avatar === "default.jpg") {
      return `${APP_URL}/public/photos/default.jpg`;
    }

    // Verificar si existe la imagen en thumbnail, si no usar original
    return `${APP_URL}/public/photos/thumbnail/${avatar}`;
  }

  escapeHtml(text) {
    if (!text) return "";
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.toString().replace(/[&<>"']/g, (m) => map[m]);
  }

  formatDate(dateString) {
    if (!dateString) return "No disponible";

    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) return dateString;

      const options = {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      };

      return date.toLocaleDateString("es-MX", options);
    } catch (error) {
      return dateString;
    }
  }
}

// Instancia global del modal
let userDetailsModal;

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function () {
  userDetailsModal = new UserDetailsModal();
});

// Función global para mostrar detalles (llamada desde el HTML)
function verDetalles(userId) {
  if (typeof cerrarTodosLosMenus === "function") {
    cerrarTodosLosMenus();
  }

  if (userDetailsModal) {
    userDetailsModal.show(userId);
  } else {
    console.error("Modal de detalles no inicializado");
  }
}
