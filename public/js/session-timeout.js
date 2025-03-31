document.addEventListener('DOMContentLoaded', function() {
  let timeout;
  const inactivityTime = 5 * 60 * 1000; // 5 minutos en milisegundos
  
  // Función para restablecer el temporizador
  function resetTimer() {
      clearTimeout(timeout);
      
      // Actualizar una marca de actividad en el servidor cada cierto tiempo
      // para evitar múltiples solicitudes, actualizamos cada 60 segundos
      if (!window.lastPingTime || (Date.now() - window.lastPingTime > 60000)) {
          updateActivity();
          window.lastPingTime = Date.now();
      }
      
      // Configurar temporizador para verificar la sesión
      timeout = setTimeout(checkSession, inactivityTime);
  }
  
  // Enviar ping al servidor para actualizar la actividad
  function updateActivity() {
      fetch('/api/auth/ping', {
          method: 'GET',
          headers: {
              'Content-Type': 'application/json'
          },
          credentials: 'include' // Incluir cookies
      }).catch(error => console.error('Error actualizando actividad:', error));
  }
  
  // Verificar estado de la sesión
  function checkSession() {
      fetch('/api/auth/check-session', {
          method: 'GET',
          headers: {
              'Content-Type': 'application/json'
          },
          credentials: 'include' // Incluir cookies
      })
      .then(response => response.json())
      .then(data => {
          if (!data.valid) {
              // Sesión expirada, redirigir al login
              window.location.href = '/login?expired=1';
          } else {
              // Sesión válida, reiniciar temporizador
              resetTimer();
          }
      })
      .catch(error => {
          console.error('Error verificando sesión:', error);
      });
  }
  
  // Eventos que reinician el temporizador
  const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
  events.forEach(event => {
      document.addEventListener(event, resetTimer, true);
  });
  
  // Iniciar el temporizador cuando la página carga
  resetTimer();
  
});
