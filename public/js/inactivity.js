(function () {
  const DEFAULT_CHECK_INTERVAL = 5000; // 5 segundos
  const REMEMBERED_CHECK_INTERVAL = 600000; // 10 minutos
  const BASE_APP_URL = typeof APP_URL !== "undefined" && APP_URL ? APP_URL : "";

  // Estado de la aplicación
  let state = {
    sessionCheckIntervalId: null,
    modalCountdownIntervalId: null,
    isRememberedSession: false,
    currentCheckInterval: DEFAULT_CHECK_INTERVAL,
    isLoggingOut: false,
    isModalActive: false, // Nueva bandera para controlar el estado del modal
    pendingStatusRequest: null, // Para manejar las peticiones pendientes
    serverConfig: {
      logoutUrl: "",
      refreshUrl: "",
      warningThreshold: 30,
    },
  };

  // Referencias DOM
  let dom = {
    modal: null,
    message: null,
    countdown: null,
    stayLoggedBtn: null,
    logoutBtn: null,
    closeBtn: null,
  };

  function initializeDOMElements() {
    dom.modal = document.getElementById("inactivityWarningModal");

    if (!dom.modal) {
      console.warn("Modal de inactividad no encontrado");
      return false;
    }

    dom.message = document.getElementById("inactivityMessage");
    dom.countdown = document.getElementById("inactivityCountdown");
    dom.stayLoggedBtn = document.getElementById("inactivityStayLoggedInBtn");
    dom.logoutBtn = document.getElementById("inactivityLogoutBtn");
    dom.closeBtn = document.getElementById("inactivityModalCloseBtn");

    return true;
  }

  function setupEventListeners() {
    if (!dom.modal) return;

    // Event listeners del modal
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
        handleStayLoggedIn();
      }
    });

    // Prevenir que los clicks dentro del modal-content detengan el countdown
    const modalContent = document.querySelector(".modal-content");
    if (modalContent) {
      modalContent.addEventListener("click", (event) => {
        // Detener la propagación para que no se active handleUserActivity
        event.stopPropagation();
      });
    }

    // Actividad del usuario
    window.addEventListener("click", handleUserActivity);

    // Cleanup al cerrar o cambiar de página
    window.addEventListener("beforeunload", cleanup);
    window.addEventListener("pagehide", cleanup);
    window.addEventListener("unload", cleanup);
  }

  function handleUserActivity(event) {
    if (state.isLoggingOut) return;

    if (isLogoutElement(event.target)) {
      state.isLoggingOut = true;
      cleanup();
      return;
    }

    // No refrescar si el modal está activo
    if (state.isModalActive) {
      return;
    }

    // Si el modal no está visible, refrescar normalmente
    refreshSession();
  }

  function isLogoutElement(element) {
    if (!element) return false;

    // Verificar el elemento actual y sus padres
    let current = element;
    while (current && current !== document.body) {
      // Verificar href
      if (current.href && current.href.includes("logout")) return true;

      // Verificar clases e IDs
      if (
        current.classList.contains("logout-btn") ||
        current.id === "logout-btn"
      )
        return true;

      // Verificar texto
      const text = current.textContent?.toLowerCase() || "";
      if (
        text.includes("cerrar sesión") ||
        text.includes("logout") ||
        text.includes("salir")
      ) {
        return true;
      }

      // Verificar onclick
      if (current.onclick && current.onclick.toString().includes("logout"))
        return true;

      current = current.parentElement;
    }

    return false;
  }

  function showModal() {
    if (!dom.modal || state.isRememberedSession) return;
    dom.modal.style.display = "flex";
    state.isModalActive = true;
  }

  function hideModal() {
    if (!dom.modal) return;

    dom.modal.style.display = "none";
    state.isModalActive = false;

    if (state.modalCountdownIntervalId) {
      clearInterval(state.modalCountdownIntervalId);
      state.modalCountdownIntervalId = null;
    }

    if (dom.countdown) {
      dom.countdown.textContent = "";
    }
  }

  function adjustCheckInterval(isRemembered) {
    if (isRemembered === state.isRememberedSession) return false;

    state.isRememberedSession = isRemembered;
    const newInterval = isRemembered
      ? REMEMBERED_CHECK_INTERVAL
      : DEFAULT_CHECK_INTERVAL;

    if (newInterval === state.currentCheckInterval) return false;

    state.currentCheckInterval = newInterval;

    // Reiniciar el intervalo
    if (state.sessionCheckIntervalId) {
      clearInterval(state.sessionCheckIntervalId);
    }

    state.sessionCheckIntervalId = setInterval(
      checkSessionStatus,
      state.currentCheckInterval
    );
    return true;
  }

  function buildLogoutUrl(isExpired = false) {
    let url = state.serverConfig.logoutUrl;

    if (!url) {
      if (!BASE_APP_URL) return null;
      url = BASE_APP_URL + (BASE_APP_URL.endsWith("/") ? "" : "/") + "logout";
    }

    if (isExpired) {
      const separator = url.includes("?") ? "&" : "?";
      url += separator + "expired=1";
    }

    return url;
  }

  function performLogout(isExpired = false) {
    cleanup();

    const logoutUrl = buildLogoutUrl(isExpired);
    if (!logoutUrl) {
      alert(
        "Tu sesión ha expirado. Por favor, cierra esta ventana y vuelve a iniciar sesión."
      );
      return;
    }

    console.log("Redirigiendo a logout:", logoutUrl);
    window.location.href = logoutUrl;
  }

  function buildRefreshUrl() {
    if (state.serverConfig.refreshUrl) {
      return state.serverConfig.refreshUrl;
    }

    if (BASE_APP_URL) {
      return (
        BASE_APP_URL +
        (BASE_APP_URL.endsWith("/") ? "" : "/") +
        "api/session/refresh"
      );
    }

    return null;
  }

  function refreshSession() {
    const refreshUrl = buildRefreshUrl();
    if (!refreshUrl) {
      console.error("URL de refresco no disponible");
      return;
    }

    fetch(refreshUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Error al refrescar sesión: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        if (data.success) {
          if (data.isRememberedSession !== undefined) {
            adjustCheckInterval(data.isRememberedSession);
          }
          hideModal();
          checkSessionStatus();
        } else {
          console.error("Fallo al refrescar sesión:", data.message);
          performLogout();
        }
      })
      .catch((error) => {
        console.error("Error en refresco de sesión:", error);
        performLogout();
      });
  }

  function handleStayLoggedIn() {
    hideModal();
    refreshSession();
  }

  function startCountdown(timeRemaining) {
    if (!dom.countdown || !dom.message) return;

    let countdown = Math.max(0, Math.floor(timeRemaining));

    dom.message.textContent = "Tu sesión expirará en";

    const updateCountdown = () => {
      dom.countdown.innerHTML = `<span class="countdown-number">${countdown}</span>`;

      if (countdown <= 0) {
        clearInterval(state.modalCountdownIntervalId);
        state.modalCountdownIntervalId = null;
        performLogout(true);
        return;
      }

      countdown--;
    };

    if (state.modalCountdownIntervalId) {
      clearInterval(state.modalCountdownIntervalId);
    }

    updateCountdown();
    state.modalCountdownIntervalId = setInterval(updateCountdown, 1000);
  }

  function showInactivityWarning(timeRemaining) {
    if (state.isRememberedSession) {
      hideModal();
      return;
    }

    showModal();
    startCountdown(timeRemaining);
  }

  function checkSessionStatus() {
    // Si el modal está activo, no interrumpir
    if (state.isModalActive) {
      return;
    }

    // Cancelar petición pendiente si existe
    if (state.pendingStatusRequest) {
      state.pendingStatusRequest.abort();
    }

    // Ocultar modal si es sesión recordada
    if (
      state.isRememberedSession &&
      dom.modal &&
      dom.modal.style.display !== "none"
    ) {
      hideModal();
    }

    const statusUrl =
      BASE_APP_URL +
      (BASE_APP_URL.endsWith("/") ? "" : "/") +
      "api/session/status";

    // Crear un nuevo AbortController para esta petición
    const abortController = new AbortController();
    state.pendingStatusRequest = abortController;

    fetch(statusUrl, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
      signal: abortController.signal,
    })
      .then((response) => {
        // Limpiar la referencia a la petición pendiente
        if (state.pendingStatusRequest === abortController) {
          state.pendingStatusRequest = null;
        }

        if (response.status === 401) {
          performLogout(true);
          return Promise.reject(new Error("Session expired (401)"));
        }

        if (!response.ok) {
          throw new Error(`Error HTTP: ${response.status}`);
        }

        return response.json();
      })
      .then((data) => {
        if (!data) return;

        // Actualizar configuración del servidor
        state.serverConfig.logoutUrl =
          data.logoutUrl || state.serverConfig.logoutUrl;
        state.serverConfig.refreshUrl =
          data.refreshUrl || state.serverConfig.refreshUrl;
        state.serverConfig.warningThreshold = data.warningThreshold || 30;

        // Verificar si la sesión está activa
        if (!data.isActive) {
          hideModal();
          performLogout(true);
          return;
        }

        // Verificar cambio en estado de recordado
        const isRemembered = Boolean(data.isRememberedSession);
        adjustCheckInterval(isRemembered);

        // Para sesiones recordadas, no mostrar advertencias
        if (isRemembered) {
          hideModal();
          return;
        }

        // Solo mostrar advertencia si el modal no está activo
        if (
          data.timeRemaining <= state.serverConfig.warningThreshold &&
          !state.isModalActive
        ) {
          showInactivityWarning(data.timeRemaining);
        } else if (data.timeRemaining > state.serverConfig.warningThreshold) {
          hideModal();
        }
      })
      .catch((error) => {
        // Ignorar errores de peticiones abortadas
        if (error.name === "AbortError") {
          return;
        }

        if (!error.message.includes("Session expired")) {
          console.error("Error al verificar sesión:", error);
        }
      });
  }

  function cleanup() {
    // Cancelar cualquier petición pendiente
    if (state.pendingStatusRequest) {
      state.pendingStatusRequest.abort();
      state.pendingStatusRequest = null;
    }

    if (state.sessionCheckIntervalId) {
      clearInterval(state.sessionCheckIntervalId);
      state.sessionCheckIntervalId = null;
    }

    if (state.modalCountdownIntervalId) {
      clearInterval(state.modalCountdownIntervalId);
      state.modalCountdownIntervalId = null;
    }
  }

  function init() {
    if (!BASE_APP_URL) {
      console.warn("APP_URL no está definida. Funcionalidad limitada.");
    }

    if (!initializeDOMElements()) {
      console.warn("No se pudieron inicializar los elementos DOM");
      return;
    }

    setupEventListeners();
    checkSessionStatus();

    // Iniciar verificaciones periódicas
    state.sessionCheckIntervalId = setInterval(
      checkSessionStatus,
      DEFAULT_CHECK_INTERVAL
    );
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
