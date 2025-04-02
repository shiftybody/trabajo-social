/**
 * Script optimizado para detectar inactividad del usuario con verificación del lado del servidor
 * y ping inteligente que solo ocurre cuando es necesario para mantener la sesión activa
 */
document.addEventListener('DOMContentLoaded', function() {
    // Tiempo de inactividad configurado en el servidor (en segundos)
    const SESSION_INACTIVE_TIMEOUT = 120; // 5 minutos
    
    // Mostrar advertencia cuando queden 60 segundos (1 minuto)
    const WARNING_BEFORE_TIMEOUT = 60;
    
    // Cuánto tiempo antes del timeout se debe enviar un ping (en segundos)
    const PING_BEFORE_TIMEOUT = 30; // Ping 30 segundos antes del timeout
    
    // Determinar la URL base de la aplicación
    const appUrl = document.querySelector('meta[name="app-url"]')?.content || '/';
    
    // Timers y estado
    let inactivityTimer;
    let pingTimer;
    let countdownInterval;
    let warningShown = false;
    let monitoringActive = false;
    let modalContainer = null;
    let lastActivityTime = Date.now();
    
    // Log con timestamp para debugging
    function logWithTime(message) {
        const now = new Date();
        const timeStr = now.toLocaleTimeString() + '.' + String(now.getMilliseconds()).padStart(3, '0');
        console.log(`[${timeStr}] ${message}`);
    }
    
    // Verificar con el servidor si se debe monitorear inactividad
    async function checkServerForMonitoring() {
        try {
            const response = await fetch(appUrl + 'api/session/status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                logWithTime("No hay sesión activa o error en la verificación");
                return false;
            }
            
            const data = await response.json();
            
            // Si el servidor indica que hay cookie de recordatorio, no monitorear
            if (data.status === 'success' && data.session && data.session.remember === true) {
                logWithTime("Servidor indica recordatorio activo, no se monitoreará inactividad");
                return false;
            }
            
            // En cualquier otro caso, sí monitorear
            return true;
        } catch (error) {
            console.error("Error verificando estado de sesión:", error);
            // Por seguridad, en caso de error, monitoreamos
            return true;
        }
    }
    
    // Cargar el HTML del modal desde un archivo PHP
    async function loadModalTemplate() {
        try {
            const response = await fetch(appUrl + 'public/inc/modal.php', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                console.error("Error al cargar la plantilla del modal");
                return null;
            }
            
            return await response.text();
        } catch (error) {
            console.error("Error cargando plantilla del modal:", error);
            return null;
        }
    }
    
    // Hacer ping al servidor para mantener la sesión activa
    async function pingServer() {
        logWithTime("Enviando ping al servidor para mantener la sesión activa");
        
        try {
            const response = await fetch(appUrl + 'api/session/ping', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                logWithTime("Ping exitoso, sesión renovada");
                // Actualizar el tiempo de última actividad después de un ping exitoso
                lastActivityTime = Date.now();
                return true;
            } else {
                logWithTime("Error en el ping, el servidor respondió con error");
                return false;
            }
        } catch (error) {
            console.error('Error al hacer ping al servidor:', error);
            return false;
        }
    }
    
    // Configurar todos los temporizadores basados en la última actividad
    function setupTimers() {
        // Limpiar temporizadores existentes
        clearTimeout(inactivityTimer);
        clearTimeout(pingTimer);
        
        if (warningShown || !monitoringActive) {
            return;
        }
        
        const currentTime = Date.now();
        const elapsedTime = (currentTime - lastActivityTime) / 1000; // Convertir a segundos
        const remainingTime = SESSION_INACTIVE_TIMEOUT - elapsedTime;
        
        logWithTime(`Configurando temporizadores. Tiempo transcurrido: ${elapsedTime.toFixed(1)}s, Tiempo restante: ${remainingTime.toFixed(1)}s`);
        
        // Si el tiempo restante es negativo, mostrar la advertencia inmediatamente
        if (remainingTime <= WARNING_BEFORE_TIMEOUT) {
            logWithTime(`Tiempo restante (${remainingTime.toFixed(1)}s) es menor que el umbral de advertencia (${WARNING_BEFORE_TIMEOUT}s)`);
            showWarning();
            return;
        }
        
        // Calcular cuándo mostrar la advertencia
        const timeUntilWarning = remainingTime - WARNING_BEFORE_TIMEOUT;
        
        // Calcular cuándo enviar el ping (solo si es antes de la advertencia)
        const timeUntilPing = remainingTime - PING_BEFORE_TIMEOUT;
        
        logWithTime(`Advertencia en: ${timeUntilWarning.toFixed(1)}s, Ping en: ${timeUntilPing.toFixed(1)}s`);
        
        // Configurar temporizador para mostrar la advertencia
        inactivityTimer = setTimeout(() => {
            logWithTime("Temporizador expirado: Mostrando advertencia");
            showWarning();
        }, timeUntilWarning * 1000);
        
        // Configurar temporizador para enviar ping solo si es antes de la advertencia
        if (timeUntilPing > 0 && timeUntilPing < timeUntilWarning) {
            pingTimer = setTimeout(() => {
                logWithTime(`Ejecutando ping programado (${PING_BEFORE_TIMEOUT}s antes del timeout)`);
                pingServer().then(success => {
                    if (success) {
                        setupTimers(); // Reiniciar temporizadores después de un ping exitoso
                    }
                });
            }, timeUntilPing * 1000);
        }
    }
    
    // Registro de actividad del usuario
    function recordActivity() {
        if (warningShown || !monitoringActive) {
            return;
        }
        
        const now = Date.now();
        const sinceLastActivity = (now - lastActivityTime) / 1000;
        
        // Solo registrar y reiniciar temporizadores si pasó un tiempo significativo (más de 1 segundo)
        // para evitar múltiples reinicios durante eventos frecuentes como scroll o mousemove
        if (sinceLastActivity > 1) {
            logWithTime(`Actividad detectada después de ${sinceLastActivity.toFixed(1)}s de inactividad`);
            lastActivityTime = now;
            setupTimers();
        }
    }
    
    // Mostrar advertencia de sesión a punto de expirar
    function showWarning() {
        // Verificar que el monitoreo siga activo
        if (!monitoringActive) {
            return;
        }
        
        // Marcar que la advertencia está activa
        warningShown = true;
        clearTimeout(inactivityTimer);
        clearTimeout(pingTimer);
        
        logWithTime("Mostrando advertencia de sesión por expirar");
        
        // Buscar o crear el contenedor del modal
        let warningModal = document.getElementById('session-warning-modal');
        
        if (!warningModal) {
            if (modalContainer) {
                // Añadir el modal al body
                document.body.appendChild(modalContainer);
                
                // Asignar el modal a la variable warningModal
                warningModal = document.getElementById('session-warning-modal');
                
                // Configurar los event listeners para los botones
                const keepButton = document.getElementById('keep-session-button');
                if (keepButton) {
                    keepButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        logWithTime("Usuario eligió mantener la sesión");
                        pingServer().then(() => {
                            hideWarning();
                        });
                    });
                }
                
                const logoutButton = document.getElementById('logout-now-button');
                if (logoutButton) {
                    logoutButton.addEventListener('click', function(e) {
                        e.stopPropagation();
                        logWithTime("Usuario eligió cerrar sesión");
                        window.location.href = appUrl + 'logout';
                    });
                }
            }
        } else {
            warningModal.style.display = 'flex';
        }
        
        // Iniciar cuenta regresiva
        let secondsLeft = WARNING_BEFORE_TIMEOUT;
        const countdownEl = document.getElementById('session-countdown');
        
        clearInterval(countdownInterval);
        
        countdownInterval = setInterval(function() {
            // Verificar que el elemento siga existiendo
            if (countdownEl) {
                countdownEl.textContent = secondsLeft;
            }
            
            secondsLeft--;
            
            if (secondsLeft < 0) {
                logWithTime("Cuenta regresiva terminada, cerrando sesión");
                clearInterval(countdownInterval);
                window.location.href = appUrl + 'logout';
            }
        }, 1000);
    }
    
    // Ocultar advertencia y restablecer temporizadores
    function hideWarning() {
        const warningModal = document.getElementById('session-warning-modal');
        if (warningModal) {
            warningModal.style.display = 'none';
        }
        
        logWithTime("Ocultando advertencia y restableciendo temporizadores");
        warningShown = false;
        clearInterval(countdownInterval);
        lastActivityTime = Date.now();
        setupTimers();
    }
    
    // Iniciar el monitoreo solo si es necesario
    async function initMonitoring() {
        const shouldMonitor = await checkServerForMonitoring();
        
        if (!shouldMonitor) {
            logWithTime("No se iniciará monitoreo de inactividad");
            return;
        }
        
        logWithTime("Iniciando monitoreo de inactividad");
        monitoringActive = true;
        lastActivityTime = Date.now();
        
        // Precargar la plantilla del modal
        const modalTemplate = await loadModalTemplate();
        if (!modalTemplate) {
            console.error("No se pudo cargar la plantilla del modal, utilizando fallback");
        } else {
            // Crear un contenedor temporal para parsear el HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = modalTemplate;
            
            // Guardar el modal para usarlo después
            modalContainer = tempDiv.firstElementChild;
        }
        
        // Lista de eventos que registran actividad
        const activityEvents = [
            'mousedown', 'mousemove', 'keypress', 
            'scroll', 'touchstart', 'click', 'keydown'
        ];
        
        // Variable para limitar la frecuencia de actualización en eventos de alta frecuencia
        let throttlePaused = false;
        
        // Registrar eventos para detectar actividad
        activityEvents.forEach(function(eventName) {
            document.addEventListener(eventName, function(e) {
                // No reiniciar si el evento viene del modal
                if (!warningShown && monitoringActive && 
                    (!e.target.closest || !e.target.closest('#session-warning-modal'))) {
                    
                    // Usar throttling para eventos de alta frecuencia (mousemove, scroll)
                    if ((eventName === 'mousemove' || eventName === 'scroll') && throttlePaused) {
                        return;
                    }
                    
                    if (eventName === 'mousemove' || eventName === 'scroll') {
                        throttlePaused = true;
                        setTimeout(() => { throttlePaused = false; }, 1000);
                    }
                    
                    recordActivity();
                }
            });
        });
        
        // Verificar periódicamente con el servidor si debemos seguir monitoreando (cada 5 minutos)
        const monitorCheckInterval = setInterval(async function() {
            if (monitoringActive) {
                logWithTime("Verificando con el servidor si se debe seguir monitoreando");
                const shouldMonitor = await checkServerForMonitoring();
                
                if (!shouldMonitor) {
                    logWithTime("Servidor indica que ya no es necesario monitorear");
                    monitoringActive = false;
                    clearTimeout(inactivityTimer);
                    clearTimeout(pingTimer);
                    clearInterval(monitorCheckInterval);
                    hideWarning();
                }
            }
        }, 300000); // 5 minutos
        
        // Limpiar intervalos al cerrar página
        window.addEventListener('beforeunload', function() {
            clearInterval(monitorCheckInterval);
            clearInterval(countdownInterval);
            clearTimeout(inactivityTimer);
            clearTimeout(pingTimer);
        });
        
        // Iniciar los temporizadores
        setupTimers();
    }
    
    // Iniciar todo el proceso
    logWithTime("Iniciando script de monitoreo de sesión");
    initMonitoring();
});