(function () {
  const DEFAULT_CHECK_INTERVAL = 5000;
  const REMEMBERED_CHECK_INTERVAL = 5000;

  const BASE_APP_URL = typeof APP_URL !== "undefined" && APP_URL ? APP_URL : "";

  let sessionCheckIntervalId = null;
  let inactivityModal = null;
  let inactivityMessage = null;
  let inactivityCountdownDisplay = null;
  let inactivityStayLoggedInBtn = null;
  let inactivityLogoutBtn = null;
  let modalCountdownIntervalId = null;

  let isRememberedSession = false;
  let currentCheckInterval = DEFAULT_CHECK_INTERVAL;

  // URLs del servidor
  let serverLogoutUrl = "";
  let serverRefreshUrl = "";
  let serverWarningThreshold = 30; // Segundos, se actualizará desde el servidor

  function getDOMReferences() {
    // ... en inactivity.js dentro de getDOMReferences()
    let inactivityModalCloseBtn = document.getElementById(
      "inactivityModalCloseBtn"
    );

    // ... y luego, donde configuras los event listeners:
    if (inactivityModalCloseBtn) {
      inactivityModalCloseBtn.addEventListener("click", function () {
        console.log("Botón X del modal clickeado. Ocultando modal.");
        hideModal();
        refreshSession();
      });
    }

    inactivityModal = document.getElementById("inactivityWarningModal");
    if (inactivityModal) {
      inactivityMessage = document.getElementById("inactivityMessage");
      inactivityCountdownDisplay = document.getElementById(
        "inactivityCountdown"
      );
      inactivityStayLoggedInBtn = document.getElementById(
        "inactivityStayLoggedInBtn"
      );
      inactivityLogoutBtn = document.getElementById("inactivityLogoutBtn");

      // Event listener para clic fuera del modal (en el overlay)
      inactivityModal.addEventListener("click", function (event) {
        if (event.target === inactivityModal) {
          // Si el clic fue directamente en el overlay
          console.log(
            "Clic detectado fuera del contenido del modal. Refrescando sesión."
          );
          refreshSession();
        }
      });

      if (
        !inactivityMessage ||
        !inactivityCountdownDisplay ||
        !inactivityStayLoggedInBtn ||
        !inactivityLogoutBtn
      ) {
        console.warn(
          "Inactivity Modal: Faltan algunos elementos hijos (mensaje, contador, botones). La funcionalidad del modal puede estar limitada."
        );
      }
    } else {
      console.warn(
        'Modal de Inactividad con ID "inactivityWarningModal" no encontrado. Las advertencias de inactividad no se mostrarán visualmente.'
      );
    }
  }

  function showModal() {
    if (inactivityModal) {
      console.log("showModal() llamado");
      inactivityModal.style.display = "flex"; // Cambiado de "block" a "flex" para el centrado
    }
  }

  function hideModal() {
    if (inactivityModal) {
      console.log("hideModal() llamado");
      inactivityModal.style.display = "none";
      if (modalCountdownIntervalId) {
        clearInterval(modalCountdownIntervalId);
        modalCountdownIntervalId = null;
      }
      if (inactivityCountdownDisplay)
        inactivityCountdownDisplay.textContent = "";
    }
  }

  function adjustCheckInterval(isRemembered, newInterval) {
    // Si isRemembered cambió o se proporcionó un intervalo explícito, ajustar el intervalo
    if (isRemembered !== isRememberedSession || newInterval) {
      isRememberedSession = isRemembered;

      // Usar el intervalo proporcionado explícitamente o seleccionar basado en el estado de recordado
      let interval =
        newInterval ||
        (isRemembered ? REMEMBERED_CHECK_INTERVAL : DEFAULT_CHECK_INTERVAL);

      // Solo cambiar el intervalo si es diferente al actual
      if (interval !== currentCheckInterval) {
        currentCheckInterval = interval;

        // Reiniciar el intervalo con la nueva frecuencia
        if (sessionCheckIntervalId) {
          clearInterval(sessionCheckIntervalId);
        }

        console.log(
          `Ajustando intervalo de verificación: ${currentCheckInterval}ms (sesión ${
            isRemembered ? "recordada" : "normal"
          })`
        );
        sessionCheckIntervalId = setInterval(
          checkSessionStatus,
          currentCheckInterval
        );

        return true; // El intervalo se ajustó
      }
    }

    return false; // No se necesitó ajuste
  }

  function forceLogout() {
    console.log("forceLogout() llamado");
    if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
    if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);

    const logoutUrlToUse =
      serverLogoutUrl || (BASE_APP_URL ? BASE_APP_URL + "logout" : "/logout");
    window.location.href = logoutUrlToUse;
  }

  function refreshSession() {
    console.log("refreshSession() llamado");
    if (!serverRefreshUrl && BASE_APP_URL) {
      // Asegurar que serverRefreshUrl se use si está disponible
      // Si serverRefreshUrl no está seteado pero BASE_APP_URL sí, construirlo.
      // Esto es un fallback, idealmente serverRefreshUrl viene del status.
      serverRefreshUrl = BASE_APP_URL + "api/session/refresh";
      console.warn(
        "serverRefreshUrl no estaba definido, usando fallback: " +
          serverRefreshUrl
      );
    } else if (!serverRefreshUrl && !BASE_APP_URL) {
      console.error(
        "URL de refresco de sesión no disponible y APP_URL tampoco. No se puede refrescar."
      );
      // Considerar si forzar logout aquí es apropiado si el modal está visible
      if (inactivityModal && inactivityModal.style.display !== "none") {
        // forceLogout(); // Descomentar si se desea cerrar sesión en este caso extremo
      }
      return;
    }

    fetch(serverRefreshUrl, {
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
          console.log("Sesión refrescada exitosamente.");

          // Verificar si el estado de recordar cambió y actualizar el intervalo si es necesario
          // data.checkFrequency ya no se envía desde el servidor
          if (data.isRememberedSession !== undefined) {
            adjustCheckInterval(data.isRememberedSession); // Se pasa solo isRememberedSession
          }

          hideModal();
          checkSessionStatus(); // Re-verificar inmediatamente
        } else {
          console.error(
            "Fallo al refrescar sesión en el servidor:",
            data.message
          );
          forceLogout();
        }
      })
      .catch((error) => {
        console.error("Error en la petición de refresco de sesión:", error);
        forceLogout();
      });
  }

  function showInactivityWarning(timeRemaining) {
    console.log(
      `showInactivityWarning() llamado con timeRemaining: ${timeRemaining}`
    );
    if (!inactivityModal || !inactivityMessage || !inactivityCountdownDisplay) {
      console.warn(
        "Elementos del modal no encontrados, no se puede mostrar la advertencia de inactividad."
      );
      return;
    }

    // No mostrar advertencia para sesiones recordadas
    if (isRememberedSession) {
      console.log("Sesión recordada, ignorando advertencia de inactividad.");
      hideModal();
      return;
    }

    showModal();
    let countdown = Math.max(0, Math.floor(timeRemaining));

    if (inactivityMessage) {
      inactivityMessage.textContent = `Tu sesión expirará en`;
    }

    function updateCountdown() {
      if (inactivityCountdownDisplay) {
        // Modificado para usar innerHTML y la clase para el número
        inactivityCountdownDisplay.innerHTML = `<span class="countdown-number">${countdown}</span>`;
      }
      if (countdown <= 0) {
        clearInterval(modalCountdownIntervalId);
        modalCountdownIntervalId = null;
        console.log("Contador del modal llegó a cero. Forzando logout.");
        forceLogout();
      }
      countdown--;
    }

    if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);
    updateCountdown(); // Llamar una vez inmediatamente para mostrar el valor inicial
    modalCountdownIntervalId = setInterval(updateCountdown, 1000);
  }

  function checkSessionStatus() {
    // Si es una sesión recordada y el modal está abierto, ocultarlo
    if (
      isRememberedSession &&
      inactivityModal &&
      inactivityModal.style.display !== "none"
    ) {
      console.log(
        "checkSessionStatus: Sesión recordada con modal abierto, ocultando."
      );
      hideModal();
    }

    console.log(
      `checkSessionStatus() llamado (sesión ${
        isRememberedSession ? "recordada" : "normal"
      })`
    );

    if (!BASE_APP_URL && (!serverLogoutUrl || !serverRefreshUrl)) {
      console.error(
        "APP_URL no está configurada y no se han obtenido URLs del servidor. Deteniendo verificaciones de inactividad."
      );
      if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
      return;
    }

    const statusUrl = BASE_APP_URL + "api/session/status";

    fetch(statusUrl, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => {
        if (response.status === 401) {
          console.log(
            "checkSessionStatus: Respuesta 401 del servidor, asumiendo desconexión."
          );
          forceLogout();
          return Promise.reject(new Error("Logged out (401)"));
        }
        if (!response.ok) {
          throw new Error(
            `Error HTTP al verificar estado de sesión: ${response.status}`
          );
        }
        return response.json();
      })
      .then((data) => {
        if (!data) {
          console.log(
            "checkSessionStatus: No data received from server after .json() or promise was rejected earlier."
          );
          return;
        }

        console.log(
          "checkSessionStatus - Datos recibidos del servidor:",
          JSON.parse(JSON.stringify(data))
        );

        // Actualizar URLs y parámetros del servidor
        serverLogoutUrl = data.logoutUrl;
        serverRefreshUrl = data.refreshUrl;
        serverWarningThreshold = data.warningThreshold || 30;

        // Si la sesión no está activa, forzar cierre
        if (!data.isActive) {
          console.log(
            "checkSessionStatus: Sesión INACTIVA según el servidor (data.isActive es false). Forzando logout."
          );
          hideModal();
          forceLogout();
          if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
          return;
        }

        // Comprobar si el estado de "recordado" ha cambiado
        const newRememberedState =
          data.isRememberedSession === true ||
          data.isRememberedSession === "true" ||
          data.isRememberedSession === 1 ||
          data.isRememberedSession === "1";

        console.log(
          `checkSessionStatus: Sesión ACTIVA. Verificando si es recordada. data.isRememberedSession = ${
            data.isRememberedSession
          } (tipo: ${typeof data.isRememberedSession}), estado actual: ${isRememberedSession}`
        );

        // Si el estado de recordado cambió, actualizar el intervalo de verificación
        if (newRememberedState !== isRememberedSession) {
          console.log(
            `El estado de recordado cambió de ${isRememberedSession} a ${newRememberedState}`
          );

          // Usar el checkFrequency del servidor si está disponible, o valores predeterminados
          // data.checkFrequency ya no se envía, adjustCheckInterval usará sus valores por defecto.
          adjustCheckInterval(newRememberedState); // No se pasa data.checkFrequency

          // Si pasó de recordada a normal, verificar inmediatamente el tiempo restante
          if (
            !newRememberedState &&
            data.timeRemaining <= serverWarningThreshold
          ) {
            console.log(
              `Sesión cambió de recordada a normal con poco tiempo restante (${data.timeRemaining}s). Mostrando advertencia.`
            );
            showInactivityWarning(data.timeRemaining);
            return;
          }
        }

        // Para sesiones recordadas, nunca mostrar modal y retornar temprano
        if (newRememberedState) {
          console.log(
            "checkSessionStatus: Sesión RECORDADA. Ocultando modal y retornando."
          );
          hideModal();
          return;
        }

        // Proceder solo para sesiones NO recordadas con verificación de tiempo
        console.log(
          `checkSessionStatus: Verificando tiempo restante (${data.timeRemaining}) contra umbral (${serverWarningThreshold}) para sesión NO recordada.`
        );
        if (data.timeRemaining <= serverWarningThreshold) {
          console.log(
            "checkSessionStatus: Tiempo restante MENOR o IGUAL al umbral. Mostrando/actualizando advertencia."
          );
          showInactivityWarning(data.timeRemaining);
        } else {
          console.log(
            "checkSessionStatus: Tiempo restante MAYOR al umbral. Ocultando modal."
          );
          hideModal();
        }
      })
      .catch((error) => {
        if (error.message && !error.message.startsWith("Logged out")) {
          console.error("Error al obtener estado de la sesión:", error);
        }
      });
  }

  function init() {
    console.log("inactivity.js: init()");
    if (typeof APP_URL === "undefined" || !APP_URL) {
      console.warn(
        "Variable global APP_URL no está definida. El script de inactividad podría no funcionar correctamente."
      );
    }

    getDOMReferences(); // getDOMReferences ahora también añade el listener de clic exterior

    if (inactivityModal && inactivityStayLoggedInBtn && inactivityLogoutBtn) {
      inactivityStayLoggedInBtn.addEventListener("click", refreshSession);
      inactivityLogoutBtn.addEventListener("click", forceLogout);
    } else if (inactivityModal) {
      console.warn(
        "Modal de inactividad encontrado, pero faltan botones. Las acciones del modal no funcionarán."
      );
    }

    // Ejecutar la primera verificación de estado para inicializar
    checkSessionStatus();

    // El intervalo inicial se configurará en la primera verificación basado en data.isRememberedSession
    // Pero establecer uno predeterminado por ahora
    sessionCheckIntervalId = setInterval(
      checkSessionStatus,
      DEFAULT_CHECK_INTERVAL
    );

    window.addEventListener("beforeunload", () => {
      console.log("inactivity.js: beforeunload - limpiando intervalos.");
      if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
      if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
