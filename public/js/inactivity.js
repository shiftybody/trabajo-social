(function () {
  const CHECK_INTERVAL = 5000; // 5 segundos
  const REFRESH_COOLDOWN = 1000; // 1 segundo de cooldown entre refrescos
  const BASE_APP_URL = typeof APP_URL !== "undefined" && APP_URL ? APP_URL : "";

  // Estado simplificado
  let state = {
    sessionCheckInterval: null,
    modalCountdownInterval: null,
    isModalActive: false,
    warningThreshold: 30,
    lastRefreshTime: 0, // Para evitar refrescos múltiples
    isRefreshing: false, // Para evitar llamadas concurrentes
  };

  // Referencias DOM
  let dom = {};

  function initializeDOMElements() {
    dom.modal = document.getElementById("inactivityWarningModal");
    if (!dom.modal) {
      console.warn("Modal de inactividad no encontrado");
      return false;
    }

    dom.countdown = document.getElementById("inactivityCountdown");
    dom.stayLoggedBtn = document.getElementById("inactivityStayLoggedInBtn");
    dom.logoutBtn = document.getElementById("inactivityLogoutBtn");
    dom.closeBtn = document.getElementById("inactivityModalCloseBtn");

    return true;
  }

  function setupEventListeners() {
    if (!dom.modal) return;

    // Botones del modal
    if (dom.stayLoggedBtn) {
      dom.stayLoggedBtn.addEventListener("click", handleStayLoggedIn);
    }

    if (dom.logoutBtn) {
      dom.logoutBtn.addEventListener("click", () => performLogout(false));
    }

    if (dom.closeBtn) {
      dom.closeBtn.addEventListener("click", handleStayLoggedIn);
    }

    // Click fuera del modal
    dom.modal.addEventListener("click", (event) => {
      if (event.target === dom.modal) {
        event.stopPropagation(); // Evitar que se propague al document
        handleStayLoggedIn();
      }
    });

    // Prevenir propagación de clics dentro del contenido del modal
    const modalContent = dom.modal.querySelector(".modal-content");
    if (modalContent) {
      modalContent.addEventListener("click", (event) => {
        event.stopPropagation();
      });
    }

    // Actividad del usuario - Refrescar sesión al hacer clic
    document.addEventListener("click", handleUserActivity);

    // Cleanup al cerrar página
    window.addEventListener("beforeunload", cleanup);
  }

  function handleUserActivity(event) {
    // No refrescar si el modal está activo
    if (state.isModalActive) return;

    // Verificar si es un elemento de logout
    const target = event.target;
    if (target && (
      target.id === "btn_exit" || 
      target.closest("#btn_exit") ||
      target.textContent?.toLowerCase().includes("salir") ||
      target.textContent?.toLowerCase().includes("logout")
    )) {
      return; // No refrescar si es un clic en logout
    }

    // Evitar refrescos múltiples con cooldown
    const now = Date.now();
    if (now - state.lastRefreshTime < REFRESH_COOLDOWN) {
      return;
    }

    // Evitar llamadas concurrentes
    if (state.isRefreshing) {
      return;
    }

    state.lastRefreshTime = now;
    refreshSession();
  }

  function showModal(timeRemaining) {
    if (!dom.modal || state.isModalActive) return;

    dom.modal.classList.add("show");
    state.isModalActive = true;
    startCountdown(timeRemaining);
  }

  function hideModal() {
    if (!dom.modal) return;

    dom.modal.classList.remove("show");
    state.isModalActive = false;

    if (state.modalCountdownInterval) {
      clearInterval(state.modalCountdownInterval);
      state.modalCountdownInterval = null;
    }

    if (dom.countdown) {
      dom.countdown.textContent = "";
      dom.countdown.classList.remove("countdown-urgent");
    }
  }

  function startCountdown(timeRemaining) {
    if (!dom.countdown) return;

    let countdown = Math.max(0, Math.floor(timeRemaining));

    const updateCountdown = () => {
      dom.countdown.textContent = countdown;

      if (countdown <= 10) {
        dom.countdown.classList.add("countdown-urgent");
      }

      if (countdown <= 0) {
        clearInterval(state.modalCountdownInterval);
        performLogout(true);
        return;
      }
      countdown--;
    };

    if (state.modalCountdownInterval) {
      clearInterval(state.modalCountdownInterval);
    }

    updateCountdown();
    state.modalCountdownInterval = setInterval(updateCountdown, 1000);
  }

  function performLogout(isExpired = false) {
    cleanup();

    const logoutUrl = `${BASE_APP_URL}/api/logout${isExpired ? "?expired=1" : ""}`;

    fetch(logoutUrl, {
      method: "POST",
      headers: {
        "Accept": "application/json",
      },
      credentials: "same-origin",
    })
      .then(response => response.json())
      .then(data => {
        if (data.status === "success" && data.redirect) {
          window.location.href = data.redirect;
        } else {
          // Fallback
          window.location.href = `${BASE_APP_URL}/login${isExpired ? "?expired_session=1" : ""}`;
        }
      })
      .catch(error => {
        console.error("Error al cerrar sesión:", error);
        // Fallback en caso de error
        window.location.href = `${BASE_APP_URL}/login${isExpired ? "?expired_session=1" : ""}`;
      });
  }

  function refreshSession() {
    // Evitar llamadas concurrentes
    if (state.isRefreshing) return;
    
    state.isRefreshing = true;
    const refreshUrl = `${BASE_APP_URL}/api/session/refresh`;

    fetch(refreshUrl, {
      method: "POST",
      headers: {
        "Accept": "application/json",
      },
      credentials: "same-origin",
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          hideModal();
        } else {
          performLogout(true);
        }
      })
      .catch(error => {
        console.error("Error al refrescar sesión:", error);
        // No hacer logout en caso de error de red temporal
      })
      .finally(() => {
        state.isRefreshing = false;
      });
  }

  function handleStayLoggedIn() {
    hideModal();
    refreshSession();
  }

  function checkSessionStatus() {
    const statusUrl = `${BASE_APP_URL}/api/session/status`;

    fetch(statusUrl, {
      method: "GET",
      headers: {
        "Accept": "application/json",
      },
      credentials: "same-origin",
    })
      .then(response => {
        if (response.status === 401) {
          performLogout(true);
          return null;
        }
        return response.json();
      })
      .then(data => {
        if (!data) return;

        // Actualizar threshold si viene del servidor
        if (data.warningThreshold) {
          state.warningThreshold = data.warningThreshold;
        }

        // Si la sesión no está activa, cerrar sesión
        if (!data.isActive) {
          performLogout(true);
          return;
        }

        // Si es una sesión recordada, no mostrar advertencias
        if (data.isRememberedSession) {
          hideModal();
          return;
        }

        // Manejar advertencia de expiración
        if (data.timeRemaining <= state.warningThreshold && !state.isModalActive) {
          showModal(data.timeRemaining);
        } else if (data.timeRemaining > state.warningThreshold && state.isModalActive) {
          hideModal();
        }
      })
      .catch(error => {
        console.error("Error al verificar sesión:", error);
        // No hacer logout automático en caso de error de red
        // para evitar cerrar sesión por problemas temporales
      });
  }

  function cleanup() {
    if (state.sessionCheckInterval) {
      clearInterval(state.sessionCheckInterval);
      state.sessionCheckInterval = null;
    }

    if (state.modalCountdownInterval) {
      clearInterval(state.modalCountdownInterval);
      state.modalCountdownInterval = null;
    }
  }

  function init() {
    if (!BASE_APP_URL) {
      console.warn("APP_URL no está definida. Funcionalidad limitada.");
      return;
    }

    if (!initializeDOMElements()) {
      console.warn("No se pudieron inicializar los elementos DOM");
      return;
    }

    setupEventListeners();
    
    // Verificar estado inicial
    checkSessionStatus();

    // Iniciar verificaciones periódicas
    state.sessionCheckInterval = setInterval(checkSessionStatus, CHECK_INTERVAL);
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
