/**
 * Script para detectar inactividad del usuario con verificación del lado del servidor
 * para respetar la cookie de recordatorio
 */
document.addEventListener('DOMContentLoaded', function() {
    // Tiempo de inactividad configurado en el servidor (en segundos)
    const SESSION_INACTIVE_TIMEOUT = 300; // 5 minutos
    
    // Mostrar advertencia cuando queden 60 segundos (1 minuto)
    const WARNING_BEFORE_TIMEOUT = 60; 
    
    // Determinar la URL base de la aplicación
    const appUrl = document.querySelector('meta[name="app-url"]')?.content || '/';
    
    // Timers
    let inactivityTimer;
    let countdownInterval;
    let warningShown = false;
    let monitoringActive = false;
    
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
                console.log("No hay sesión activa o error en la verificación");
                return false;
            }
            
            const data = await response.json();
            
            // Si el servidor indica que hay cookie de recordatorio, no monitorear
            if (data.status === 'success' && data.session && data.session.remember === true) {
                console.log("Servidor indica recordatorio activo, no se monitoreará inactividad");
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
    
    // Iniciar el monitoreo solo si es necesario
    async function initMonitoring() {
        const shouldMonitor = await checkServerForMonitoring();
        
        if (!shouldMonitor) {
            console.log("No se iniciará monitoreo de inactividad");
            return;
        }
        
        console.log("Iniciando monitoreo de inactividad");
        monitoringActive = true;
        
        // Función para reiniciar el temporizador de inactividad
        function resetInactivityTimer() {
            if (!warningShown && monitoringActive) {
                clearTimeout(inactivityTimer);
                
                inactivityTimer = setTimeout(function() {
                    showWarning();
                }, (SESSION_INACTIVE_TIMEOUT - WARNING_BEFORE_TIMEOUT) * 1000);
            }
        }
        
        // Hacer ping al servidor para mantener la sesión activa
        function pingServer() {
            return fetch(appUrl + 'api/session/ping', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .catch(error => {
                console.error('Error al hacer ping al servidor:', error);
            });
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
            
            // Crear el modal si no existe
            let warningModal = document.getElementById('session-warning-modal');
            
            if (!warningModal) {
                // Crear contenedor principal
                warningModal = document.createElement('div');
                warningModal.id = 'session-warning-modal';
                warningModal.style.display = 'flex';
                warningModal.style.position = 'fixed';
                warningModal.style.top = '0';
                warningModal.style.left = '0';
                warningModal.style.right = '0';
                warningModal.style.bottom = '0';
                warningModal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                warningModal.style.zIndex = '9999';
                warningModal.style.justifyContent = 'center';
                warningModal.style.alignItems = 'center';
                
                // Crear contenido del modal
                const modalContent = document.createElement('div');
                modalContent.style.backgroundColor = '#fff';
                modalContent.style.padding = '20px';
                modalContent.style.borderRadius = '5px';
                modalContent.style.maxWidth = '400px';
                modalContent.style.width = '100%';
                modalContent.style.boxShadow = '0 3px 10px rgba(0, 0, 0, 0.2)';
                modalContent.style.zIndex = '10000';
                modalContent.style.pointerEvents = 'auto';
                
                // Título
                const title = document.createElement('h3');
                title.textContent = 'Su sesión está a punto de expirar';
                title.style.margin = '0 0 15px 0';
                modalContent.appendChild(title);
                
                // Mensaje
                const message = document.createElement('p');
                message.innerHTML = 'Debido a inactividad, su sesión se cerrará en <span id="session-countdown">60</span> segundos.';
                modalContent.appendChild(message);
                
                // Contenedor de botones
                const buttonsContainer = document.createElement('div');
                buttonsContainer.style.display = 'flex';
                buttonsContainer.style.justifyContent = 'space-between';
                buttonsContainer.style.marginTop = '20px';
                
                // Botón para mantener sesión
                const keepButton = document.createElement('button');
                keepButton.textContent = 'Mantener sesión activa';
                keepButton.style.padding = '8px 16px';
                keepButton.style.backgroundColor = '#3498db';
                keepButton.style.color = 'white';
                keepButton.style.border = 'none';
                keepButton.style.borderRadius = '4px';
                keepButton.style.cursor = 'pointer';
                
                keepButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    pingServer().then(() => {
                        hideWarning();
                    });
                });
                
                // Botón para cerrar sesión
                const logoutButton = document.createElement('button');
                logoutButton.textContent = 'Cerrar sesión ahora';
                logoutButton.style.padding = '8px 16px';
                logoutButton.style.backgroundColor = '#95a5a6';
                logoutButton.style.color = 'white';
                logoutButton.style.border = 'none';
                logoutButton.style.borderRadius = '4px';
                logoutButton.style.cursor = 'pointer';
                
                logoutButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    window.location.href = appUrl + 'logout';
                });
                
                // Agregar botones al contenedor
                buttonsContainer.appendChild(keepButton);
                buttonsContainer.appendChild(logoutButton);
                
                // Agregar contenedor de botones al modal
                modalContent.appendChild(buttonsContainer);
                
                // Agregar contenido al modal
                warningModal.appendChild(modalContent);
                
                // Agregar modal al body
                document.body.appendChild(warningModal);
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
                    clearInterval(countdownInterval);
                    window.location.href = appUrl + 'logout?expired=1';
                }
            }, 1000);
        }
        
        // Ocultar advertencia y restablecer temporizadores
        function hideWarning() {
            const warningModal = document.getElementById('session-warning-modal');
            if (warningModal) {
                warningModal.style.display = 'none';
            }
            
            warningShown = false;
            clearInterval(countdownInterval);
            resetInactivityTimer();
        }
        
        // Lista de eventos que reinician el temporizador
        const resetEvents = [
            'mousedown', 'mousemove', 'keypress', 
            'scroll', 'touchstart', 'click', 'keydown'
        ];
        
        // Registrar eventos para reiniciar el temporizador
        resetEvents.forEach(function(event) {
            document.addEventListener(event, function(e) {
                // No reiniciar si el evento viene del modal
                if (!warningShown && monitoringActive && 
                    (!e.target.closest || !e.target.closest('#session-warning-modal'))) {
                    resetInactivityTimer();
                }
            });
        });
        
        // Ping periódico al servidor (cada 2 minutos)
        const pingInterval = setInterval(function() {
            if (!warningShown && monitoringActive) {
                pingServer().then(() => {
                    // Verificar si después del ping debemos seguir monitoreando
                    checkServerForMonitoring().then(shouldMonitor => {
                        if (!shouldMonitor) {
                            // Desactivar monitoreo si cambiaron las condiciones
                            monitoringActive = false;
                            clearTimeout(inactivityTimer);
                            clearInterval(pingInterval);
                            hideWarning();
                        }
                    });
                });
            }
        }, 120000);
        
        // Limpiar intervalos al cerrar página
        window.addEventListener('beforeunload', function() {
            clearInterval(pingInterval);
            clearInterval(countdownInterval);
            clearTimeout(inactivityTimer);
        });
        
        // Iniciar el temporizador
        resetInactivityTimer();
    }
    
    // Iniciar todo el proceso
    initMonitoring();
});