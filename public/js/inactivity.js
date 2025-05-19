// public/js/inactivity.js

(function() {
    const CHECK_INTERVAL = 15000; // Verificar cada 15 segundos (15000 ms)
    
    const BASE_APP_URL = (typeof APP_URL !== 'undefined' && APP_URL) ? APP_URL : '';

    let sessionCheckIntervalId = null;
    let inactivityModal = null;
    let inactivityMessage = null;
    let inactivityCountdownDisplay = null;
    let inactivityStayLoggedInBtn = null;
    let inactivityLogoutBtn = null;
    let modalCountdownIntervalId = null;

    let serverLogoutUrl = '';
    let serverRefreshUrl = '';
    let serverWarningThreshold = 30; // Segundos, se actualizará desde el servidor

    function getDOMReferences() {
        inactivityModal = document.getElementById('inactivityWarningModal');
        if (inactivityModal) {
            inactivityMessage = document.getElementById('inactivityMessage');
            inactivityCountdownDisplay = document.getElementById('inactivityCountdown');
            inactivityStayLoggedInBtn = document.getElementById('inactivityStayLoggedInBtn');
            inactivityLogoutBtn = document.getElementById('inactivityLogoutBtn');

            if (!inactivityMessage || !inactivityCountdownDisplay || !inactivityStayLoggedInBtn || !inactivityLogoutBtn) {
                console.warn('Inactivity Modal: Faltan algunos elementos hijos (mensaje, contador, botones). La funcionalidad del modal puede estar limitada.');
            }
        } else {
            console.warn('Modal de Inactividad con ID "inactivityWarningModal" no encontrado. Las advertencias de inactividad no se mostrarán visualmente.');
        }
    }

    function showModal() {
        if (inactivityModal) {
            console.log('showModal() llamado');
            inactivityModal.style.display = 'block';
        }
    }

    function hideModal() {
        if (inactivityModal) {
            console.log('hideModal() llamado');
            inactivityModal.style.display = 'none';
            if (modalCountdownIntervalId) {
                clearInterval(modalCountdownIntervalId);
                modalCountdownIntervalId = null;
            }
            if (inactivityCountdownDisplay) inactivityCountdownDisplay.textContent = '';
        }
    }

    function forceLogout() {
        console.log('forceLogout() llamado');
        if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
        if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);

        const logoutUrlToUse = serverLogoutUrl || (BASE_APP_URL ? BASE_APP_URL + 'logout' : '/logout');
        window.location.href = logoutUrlToUse;
    }

    function refreshSession() {
        console.log('refreshSession() llamado');
        if (!serverRefreshUrl) {
            console.error('URL de refresco de sesión no disponible.');
            if (inactivityModal && inactivityModal.style.display !== 'none') {
                forceLogout();
            }
            return;
        }

        fetch(serverRefreshUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error al refrescar sesión: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Sesión refrescada exitosamente.');
                hideModal();
                checkSessionStatus(); // Re-verificar inmediatamente
            } else {
                console.error('Fallo al refrescar sesión en el servidor:', data.message);
                forceLogout();
            }
        })
        .catch(error => {
            console.error('Error en la petición de refresco de sesión:', error);
            forceLogout();
        });
    }

    function showInactivityWarning(timeRemaining) {
        console.log(`showInactivityWarning() llamado con timeRemaining: ${timeRemaining}`);
        if (!inactivityModal || !inactivityMessage || !inactivityCountdownDisplay) {
            console.warn("Elementos del modal no encontrados, no se puede mostrar la advertencia de inactividad.");
            return;
        }

        showModal();
        let countdown = Math.max(0, Math.floor(timeRemaining));

        if (inactivityMessage) {
            inactivityMessage.textContent = `Tu sesión está a punto de expirar.`;
        }
        
        function updateCountdown() {
            if (inactivityCountdownDisplay) {
                inactivityCountdownDisplay.textContent = `Tiempo restante: ${countdown} segundos.`;
            }
            if (countdown <= 0) {
                clearInterval(modalCountdownIntervalId);
                modalCountdownIntervalId = null;
                console.log('Contador del modal llegó a cero. Forzando logout.');
                forceLogout();
            }
            countdown--;
        }

        if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);
        updateCountdown();
        modalCountdownIntervalId = setInterval(updateCountdown, 1000);
    }

    function checkSessionStatus() {
        console.log('checkSessionStatus() llamado');
        if (!BASE_APP_URL && (!serverLogoutUrl || !serverRefreshUrl)) {
            console.error('APP_URL no está configurada y no se han obtenido URLs del servidor. Deteniendo verificaciones de inactividad.');
            if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
            return;
        }
        const statusUrl = BASE_APP_URL + 'api/session/status';

        fetch(statusUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) {
                console.log('checkSessionStatus: Respuesta 401 del servidor, asumiendo desconexión.');
                forceLogout();
                return Promise.reject(new Error('Logged out (401)'));
            }
            if (!response.ok) {
                throw new Error(`Error HTTP al verificar estado de sesión: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data) {
                console.log('checkSessionStatus: No data received from server after .json() or promise was rejected earlier.');
                return;
            }

            console.log('checkSessionStatus - Datos recibidos del servidor:', JSON.parse(JSON.stringify(data))); // Log de los datos completos

            serverLogoutUrl = data.logoutUrl;
            serverRefreshUrl = data.refreshUrl;
            serverWarningThreshold = data.warningThreshold || 30;

            if (!data.isActive) {
                console.log('checkSessionStatus: Sesión INACTIVA según el servidor (data.isActive es false). Forzando logout.');
                hideModal();
                forceLogout();
                if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
                return;
            }
            
            console.log(`checkSessionStatus: Sesión ACTIVA. Verificando si es recordada. data.isRememberedSession = ${data.isRememberedSession} (tipo: ${typeof data.isRememberedSession})`);

            // Si la sesión está activa PERO es una sesión "recordada",
            // no mostramos el modal de inactividad corta.
            if (data.isRememberedSession === true || data.isRememberedSession === 'true' || data.isRememberedSession === 1 || data.isRememberedSession === '1') {
                console.log('checkSessionStatus: Sesión RECORDADA (data.isRememberedSession es true). Ocultando modal y retornando.');
                hideModal();
                return; // No hacer nada más, el usuario está "recordado"
            } else {
                console.log('checkSessionStatus: Sesión NO RECORDADA (data.isRememberedSession no es true).');
            }

            // Proceder solo para sesiones activas y NO recordadas:
            console.log(`checkSessionStatus: Verificando tiempo restante (${data.timeRemaining}) contra umbral (${serverWarningThreshold}) para sesión NO recordada.`);
            if (data.timeRemaining <= serverWarningThreshold) {
                console.log('checkSessionStatus: Tiempo restante MENOR o IGUAL al umbral. Mostrando/actualizando advertencia.');
                showInactivityWarning(data.timeRemaining);
            } else {
                console.log('checkSessionStatus: Tiempo restante MAYOR al umbral. Ocultando modal.');
                hideModal();
            }
        })
        .catch(error => {
            if (error.message && !error.message.startsWith('Logged out')) {
                 console.error('Error al obtener estado de la sesión:', error);
            }
        });
    }

    function init() {
        console.log('inactivity.js: init()');
        if (typeof APP_URL === 'undefined' || !APP_URL) {
            console.warn('Variable global APP_URL no está definida. El script de inactividad podría no funcionar correctamente.');
        }
        
        getDOMReferences();

        if (inactivityModal && inactivityStayLoggedInBtn && inactivityLogoutBtn) {
            inactivityStayLoggedInBtn.addEventListener('click', refreshSession);
            inactivityLogoutBtn.addEventListener('click', forceLogout);
        } else if (inactivityModal) {
             console.warn("Modal de inactividad encontrado, pero faltan botones. Las acciones del modal no funcionarán.");
        }

        checkSessionStatus();
        sessionCheckIntervalId = setInterval(checkSessionStatus, CHECK_INTERVAL);

        window.addEventListener('beforeunload', () => {
            console.log('inactivity.js: beforeunload - limpiando intervalos.');
            if (sessionCheckIntervalId) clearInterval(sessionCheckIntervalId);
            if (modalCountdownIntervalId) clearInterval(modalCountdownIntervalId);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
