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
    isModalActive: false,
    pendingStatusRequest: null,
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

    // Click fuera del modal - MODIFICADO para continuar verificando estatus
    dom.modal.addEventListener("click", (event) => {
      if (event.target === dom.modal) {
        event.stopPropagation(); // Evitar que se propague a handleUserActivity
        handleStayLoggedIn(); // Ocultar modal Y refrescar sesión
      }
    });

    // Prevenir propagación en el contenido del modal
    const modalContent = document.querySelector(".modal-content");
    if (modalContent) {
      modalContent.addEventListener("click", (event) => {
        event.stopPropagation();
      });
    }

    // Actividad del usuario
    window.addEventListener("click", handleUserActivity);

    // Cleanup al cerrar página
    window.addEventListener("beforeunload", cleanup);
  }

  function handleUserActivity(event) {
    // Verificar si es un elemento de logout
    if (isLogoutElement(event.target)) {
      cleanup();
      return;
    }

    // Si el clic fue en el modal (pero no en su contenido), no hacer nada
    if (event.target === dom.modal) {
      return;
    }

    // No refrescar si el modal está activo
    if (state.isModalActive) return;

    refreshSession();
  }

  function isLogoutElement(element) {
    if (!element) return false;

    let current = element;
    while (current && current !== document.body) {
      // Verificar href
      if (current.href?.includes("logout")) return true;

      // Verificar clases e IDs
      if (
        current.classList?.contains("logout-btn") ||
        current.id === "logout-btn"
      )
        return true;

      // Verificar texto
      const text = (current.textContent || "").toLowerCase();
      if (
        text.includes("cerrar sesión") ||
        text.includes("logout") ||
        text.includes("salir")
      ) {
        return true;
      }

      current = current.parentElement;
    }
    return false;
  }

  function showModal() {
    if (!dom.modal || state.isRememberedSession) return;

    // Usar las nuevas clases CSS
    dom.modal.classList.add("show");
    state.isModalActive = true;
  }

  function hideModal() {
    if (!dom.modal) return;

    // Usar las nuevas clases CSS
    dom.modal.classList.remove("show");
    state.isModalActive = false;

    if (state.modalCountdownIntervalId) {
      clearInterval(state.modalCountdownIntervalId);
      state.modalCountdownIntervalId = null;
    }

    if (dom.countdown) {
      dom.countdown.textContent = "";
      // Limpiar la clase de urgencia
      dom.countdown.classList.remove("countdown-urgent");
    }
  }

  function startCountdown(timeRemaining) {
    if (!dom.countdown || !dom.message) return;

    let countdown = Math.max(0, Math.floor(timeRemaining));
    dom.message.textContent = "Tu sesión expirará en";

    const updateCountdown = () => {
      dom.countdown.textContent = countdown;

      // Añadir animación de urgencia cuando quedan 10 segundos o menos
      if (countdown <= 10) {
        dom.countdown.classList.add("countdown-urgent");
      } else {
        dom.countdown.classList.remove("countdown-urgent");
      }

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

  function adjustCheckInterval(isRemembered) {
    if (isRemembered === state.isRememberedSession) return;

    state.isRememberedSession = isRemembered;
    state.currentCheckInterval = isRemembered
      ? REMEMBERED_CHECK_INTERVAL
      : DEFAULT_CHECK_INTERVAL;

    // Reiniciar el intervalo
    if (state.sessionCheckIntervalId) {
      clearInterval(state.sessionCheckIntervalId);
    }

    state.sessionCheckIntervalId = setInterval(
      checkSessionStatus,
      state.currentCheckInterval
    );
  }

  function buildUrl(type = "logout", isExpired = false) {
    const paths = {
      logout:
        state.serverConfig.logoutUrl ||
        (BASE_APP_URL ? `${BASE_APP_URL}/api/logout` : null),
      refresh:
        state.serverConfig.refreshUrl ||
        (BASE_APP_URL ? `${BASE_APP_URL}/api/session/refresh` : null),
      status: BASE_APP_URL ? `${BASE_APP_URL}/api/session/status` : null,
    };

    let url = paths[type];
    if (!url) return null;

    // Normalizar URL
    url = url.replace(/\/+/g, "/").replace(/:\//g, "://");

    if (type === "logout" && isExpired) {
      url += url.includes("?") ? "&expired=1" : "?expired=1";
    }

    return url;
  }

function performLogout(isExpired = false) {
  cleanup(); // Limpia intervalos y solicitudes pendientes

  const apiActionUrl = buildUrl("logout", isExpired);

  const fallbackLoginUrl = (BASE_APP_URL ? BASE_APP_URL + "/login" : "/login") +
                           (isExpired ? "?expired_session=1" : "");

  if (!apiActionUrl) {
    alert(
      "La URL de logout no está configurada. No se puede cerrar la sesión mediante API."
    );
    // Si la URL de la API no está disponible, redirigir a la URL de fallback.
    window.location.href = fallbackLoginUrl;
    return;
  }
  
  // Realiza la solicitud POST a la API para cerrar sesión
  fetch(apiActionUrl, {
    method: "POST",
    headers: {
      "Accept": "application/json",
    },
    credentials: "same-origin",
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`Error en la respuesta del servidor al cerrar sesión`);
    }
    return response.json();
  })
  .then(data => {
    if (data.status === "success") {
      // Redirigir a la URL de fallback después de cerrar sesión exitosamente
      window.location.href = data.redirect;
    } else {
    CustomDialog.error('Error', data.message || 'No se pudo cerrar la sesión.');
    }
  })
  .catch(error => {
        console.error('Error en la petición fetch:', error);
        CustomDialog.error('Error de Red', 'Ocurrió un problema al intentar conectar con el servidor.');
  });
}

  function refreshSession() {
    const refreshUrl = buildUrl("refresh");
    if (!refreshUrl) {
      console.error("URL de refresco no disponible");
      return;
    }

    fetch(refreshUrl, {
      method: "POST",
    })
      .then((response) => {
        if (!response.ok) throw new Error(`Error: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        console.log(data)
        if (data.success) {
          if (data.isRememberedSession !== undefined) {
            adjustCheckInterval(data.isRememberedSession);
          }
          hideModal();
          checkSessionStatus();
        } else {
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

  function checkSessionStatus() {
    if (state.pendingStatusRequest) {
      state.pendingStatusRequest.abort();
    }

    const statusUrl = buildUrl("status");
    if (!statusUrl) return;

    const abortController = new AbortController();
    state.pendingStatusRequest = abortController;

    fetch(statusUrl, {
      method: "GET",
      headers: {
        "Accept": "application/json",
      },
      credentials: "same-origin",
      signal: abortController.signal,
    })
      .then((response) => {
        if (state.pendingStatusRequest === abortController) {
          state.pendingStatusRequest = null;
        }

        if (response.status === 401) {
          performLogout(true);
          return Promise.reject(new Error("Session expired"));
        }

        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
        return response.json();
      })
      .then((data) => {
        if (!data) return;

        state.serverConfig.logoutUrl =
          data.logoutUrl || state.serverConfig.logoutUrl;
        state.serverConfig.refreshUrl =
          data.refreshUrl || state.serverConfig.refreshUrl;
        state.serverConfig.warningThreshold = data.warningThreshold || 30;

        if (!data.isActive) {
          hideModal();
          performLogout(true);
          return;
        }

        adjustCheckInterval(Boolean(data.isRememberedSession));

        if (!state.isRememberedSession) {
          if (
            data.timeRemaining <= state.serverConfig.warningThreshold &&
            !state.isModalActive
          ) {
            showModal();
            startCountdown(data.timeRemaining);
          } else if (
            data.timeRemaining > state.serverConfig.warningThreshold &&
            state.isModalActive
          ) {
            hideModal();
          }
        } else {
          hideModal();
        }
      })
      .catch((error) => {
        if (error.name !== "AbortError") {
          console.error("Error al verificar sesión:", error);
        }
      });
  }

  function cleanup() {
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

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
