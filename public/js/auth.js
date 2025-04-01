/**
 * Funciones para manejo de autenticación y peticiones AJAX
 */

// Almacenamiento del token JWT
const TOKEN_KEY = 'auth_token';

/**
 * Guarda el token JWT en localStorage
 * @param {string} token - Token JWT
 */
function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token);
}

/**
 * Obtiene el token JWT del localStorage
 * @returns {string|null} - Token JWT o null si no existe
 */
function getToken() {
  return localStorage.getItem(TOKEN_KEY);
}

/**
 * Elimina el token JWT del localStorage
 */
function removeToken() {
  localStorage.removeItem(TOKEN_KEY);
}

/**
 * Verifica si hay un token JWT almacenado
 * @returns {boolean} - True si hay token, false en caso contrario
 */
function isAuthenticated() {
  return getToken() !== null;
}

/**
 * Iniciar sesión mediante API
 * @param {string} username - Nombre de usuario o correo
 * @param {string} password - Contraseña
 * @returns {Promise} - Promesa con el resultado de la autenticación
 */
async function login(username, password) {
  try {
    const response = await fetch(`${window.location.origin}/api/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    });

    const data = await response.json();

    if (data.status === 'success' && data.token) {
      setToken(data.token);
      return { success: true, user: data.usuario };
    } else {
      return { success: false, message: data.message || 'Error de autenticación' };
    }
  } catch (error) {
    console.error('Error en login:', error);
    return { success: false, message: 'Error de conexión' };
  }
}

/**
 * Cerrar sesión
 */
function logout() {
  removeToken();
  window.location.href = `${window.location.origin}/login`;
}

/**
 * Renovar token JWT
 * @returns {Promise} - Promesa con el resultado de la renovación
 */
async function refreshToken() {
  try {
    const token = getToken();
    
    if (!token) {
      return { success: false, message: 'No hay token para renovar' };
    }

    const response = await fetch(`${window.location.origin}/api/auth/refresh`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    const data = await response.json();

    if (data.status === 'success' && data.token) {
      setToken(data.token);
      return { success: true };
    } else {
      return { success: false, message: data.message || 'Error al renovar token' };
    }
  } catch (error) {
    console.error('Error en refreshToken:', error);
    return { success: false, message: 'Error de conexión' };
  }
}

/**
 * Realiza una petición a la API con el token JWT
 * @param {string} url - URL de la API
 * @param {Object} options - Opciones de fetch
 * @returns {Promise} - Promesa con la respuesta
 */
async function apiRequest(url, options = {}) {
  try {
    const token = getToken();
    
    if (!token) {
      return { success: false, message: 'No autenticado' };
    }

    // Configurar headers
    const headers = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers
    };

    // Realizar petición
    const response = await fetch(`${window.location.origin}/api/${url}`, {
      ...options,
      headers
    });

    // Si el token expiró (401), intentar renovarlo
    if (response.status === 401) {
      const refreshResult = await refreshToken();
      
      if (refreshResult.success) {
        // Reintentar con el nuevo token
        const newToken = getToken();
        headers.Authorization = `Bearer ${newToken}`;
        
        const retryResponse = await fetch(`${window.location.origin}/api/${url}`, {
          ...options,
          headers
        });
        
        return await retryResponse.json();
      } else {
        // Si no se pudo renovar, redireccionar al login
        logout();
        return { success: false, message: 'Sesión expirada' };
      }
    }

    return await response.json();
  } catch (error) {
    console.error('Error en apiRequest:', error);
    return { success: false, message: 'Error de conexión' };
  }
}

/**
 * Inicializa el manejo de formularios AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
  // Capturar envío de formularios con clase form-ajax
  const forms = document.querySelectorAll('.form-ajax');
  
  forms.forEach(form => {
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      const action = this.getAttribute('action');
      const method = this.getAttribute('method') || 'POST';
      
      // Mostrar indicador de carga si existe
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.innerText;
        submitBtn.innerText = 'Procesando...';
        submitBtn.disabled = true;
      }
      
      try {
        const response = await fetch(action, {
          method: method,
          body: formData,
          credentials: 'same-origin' // Importante para cookies de sesión
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
          // Si recibimos un token, almacenarlo
          if (data.token) {
            setToken(data.token);
          }
          
          // Si hay redirección, navegar a esa URL
          if (data.redirect) {
            window.location.href = data.redirect;
          }
        } else {
          // Mostrar mensaje de error si existe
          const errorMsg = data.message || 'Ha ocurrido un error';
          
          // Verificar si hay un elemento para mostrar errores
          const errorElement = document.getElementById('login-error');
          if (errorElement) {
            errorElement.textContent = errorMsg;
            errorElement.style.display = 'block';
          } else {
            alert(errorMsg);
          }
        }
      } catch (error) {
        console.error('Error en envío de formulario:', error);
        alert('Error de conexión');
      } finally {
        // Restaurar el botón
        if (submitBtn) {
          submitBtn.innerText = 'Enviar';
          submitBtn.disabled = false;
        }
      }
    });
  });
  
  // Añadir div para mostrar errores de login si no existe
  const loginForm = document.getElementById('login-form');
  if (loginForm && !document.getElementById('login-error')) {
    const errorDiv = document.createElement('div');
    errorDiv.id = 'login-error';
    errorDiv.style.color = 'red';
    errorDiv.style.marginBottom = '10px';
    errorDiv.style.display = 'none';
    
    // Insertar después del div login-info
    const loginInfo = loginForm.querySelector('#login-info');
    if (loginInfo) {
      loginInfo.parentNode.insertBefore(errorDiv, loginInfo.nextSibling);
    } else {
      loginForm.insertBefore(errorDiv, loginForm.firstChild);
    }
  }
});