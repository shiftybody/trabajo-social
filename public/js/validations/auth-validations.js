// Esquemas de validación para autenticación
const AUTH_VALIDATION_SCHEMAS = {
  login: {
    username: {
      required: { message: "El campo correo o nombre de usuario es requerido" },
    },
    password: {
      required: { message: "El campo contraseña es requerido" },
    },
  },
};

// Funciones de validación
const AuthValidations = {
  validateLogin: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(formData, AUTH_VALIDATION_SCHEMAS.login);
  },
};

// Funciones auxiliares para manejo de estilos y toggle
const AuthUtils = {
  // Función para manejar estilos de error del password toggle
  togglePasswordErrorStyle: (input, hasError) => {
    const toggleButton = document.getElementById("password-toggle");
    
    if (input.id === "password" && toggleButton) {
      if (hasError) {
        toggleButton.classList.add("error-toggle");
      } else {
        toggleButton.classList.remove("error-toggle");
      }
    }
  },

  // Función para controlar la visibilidad del toggle basada en el contenido
  updateToggleVisibility: () => {
    const passwordInput = document.getElementById("password");
    const passwordToggle = document.getElementById("password-toggle");
    
    if (passwordInput && passwordToggle) {
      if (passwordInput.value.trim() === "") {
        passwordToggle.style.display = 'none';
      } else {
        passwordToggle.style.display = 'flex';
      }
    }
  },

  // Función para manejar el toggle de mostrar/ocultar contraseña
  handlePasswordToggle: () => {
    const passwordInput = document.getElementById("password");
    const passwordToggle = document.getElementById("password-toggle");
    
    if (!passwordInput || !passwordToggle) return;

    const eyeIcon = passwordToggle.querySelector('.eye-icon');
    const eyeOffIcon = passwordToggle.querySelector('.eye-off-icon');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      eyeIcon.style.display = 'none';
      eyeOffIcon.style.display = 'block';
    } else {
      passwordInput.type = 'password';
      eyeIcon.style.display = 'block';
      eyeOffIcon.style.display = 'none';
    }
  },

  // Función para limpiar todos los estilos de error
  clearAllErrorStyles: (form) => {
    // Limpiar errors de validación (excluyendo mensajes de estado)
    form.querySelectorAll(
      ".error-message:not(.expired-session-message):not(.account-disabled-message)"
    ).forEach((errorMsg) => errorMsg.remove());

    // Limpiar clases de error de inputs
    form.querySelectorAll(".error-input").forEach((errorInput) => {
      errorInput.classList.remove("error-input");
      // Limpiar estilo del toggle de password si aplica
      AuthUtils.togglePasswordErrorStyle(errorInput, false);
    });
  },

  // Función para aplicar estilos de error a todos los inputs
  applyErrorStylesToInputs: (form) => {
    const inputs = form.querySelectorAll("input[type='text'], input[type='password']");
    inputs.forEach((input) => {
      input.classList.add("error-input");
      // Aplicar estilo de error al toggle de password si aplica
      AuthUtils.togglePasswordErrorStyle(input, true);
    });
  },

  // Función para mostrar mensaje de error principal
  showMainError: (message, className = "account-disabled-message") => {
    const messageContainer = document.getElementById("message-container");
    if (!messageContainer) return;

    // Limpiar cualquier mensaje anterior
    AuthUtils.clearMainError();

    // Crear nuevo mensaje
    const errorP = document.createElement("p");
    errorP.className = className;
    errorP.textContent = message;

    // Añadir al contenedor y mostrarlo
    messageContainer.appendChild(errorP);
    messageContainer.classList.add("visible");
  },

  // Función para limpiar el mensaje de error principal
  clearMainError: () => {
    const messageContainer = document.getElementById("message-container");
    if (!messageContainer) return;

    const existingError = messageContainer.querySelector("p");
    if (existingError) {
      existingError.remove();
    }
    messageContainer.classList.remove("visible");
  },

  // Función para limpiar mensajes de estado (sesión expirada, cuenta deshabilitada)
  clearStatusMessages: () => {
    const messageContainer = document.getElementById("message-container");
    if (!messageContainer) return;

    const statusMessages = messageContainer.querySelectorAll(
      ".expired-session-message, .account-disabled-message"
    );
    
    if (statusMessages.length > 0) {
      statusMessages.forEach((msg) => msg.remove());
      if (!messageContainer.querySelector("p")) {
        messageContainer.classList.remove("visible");
      }
    }
  },

  // Función para limpiar mensajes de estado si no son de sesión/cuenta
  clearMainErrorIfNotStatus: () => {
    const messageContainer = document.getElementById("message-container");
    if (!messageContainer) return;

    const mainErrorMsg = messageContainer.querySelector("p");
    if (
      mainErrorMsg &&
      !mainErrorMsg.classList.contains("expired-session-message") &&
      !mainErrorMsg.classList.contains("account-disabled-message")
    ) {
      AuthUtils.clearMainError();
    }
  }
};

// Manejadores de respuesta
const AuthHandlers = {
  onLoginSuccess: async (data, form) => {

      if (data.redirect_url) {
        window.location.href = data.redirect_url;
      } else if (data.redirect) {
        window.location.href = data.redirect;
      } else {
        window.location.href = `${APP_URL}home`;
      }
  },

  onLoginError: async (data, form) => {
    // Mostrar mensaje de error principal
    AuthUtils.showMainError(data.message || "Ha ocurrido un error");

    // Aplicar estilos de error a todos los inputs
    AuthUtils.applyErrorStylesToInputs(form);
  },
};

// Función para inicializar funcionalidades del login
const initializeLoginFeatures = () => {
  const passwordToggle = document.getElementById('password-toggle');
  const passwordInput = document.getElementById('password');
  const usernameInput = document.getElementById('username');

  // Configurar toggle de contraseña
  if (passwordToggle && passwordInput) {
    // Inicializar visibilidad del toggle
    AuthUtils.updateToggleVisibility();

    // Event listeners para el toggle
    passwordInput.addEventListener('input', AuthUtils.updateToggleVisibility);
    passwordInput.addEventListener('paste', function() {
      setTimeout(AuthUtils.updateToggleVisibility, 0);
    });
    
    passwordToggle.addEventListener('click', AuthUtils.handlePasswordToggle);
  }

  // Auto-focus en el campo de usuario
  if (usernameInput) {
    usernameInput.focus();
  }

  // Enter en username pasa a password
  if (usernameInput && passwordInput) {
    usernameInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        passwordInput.focus();
      }
    });
  }
};

// Función para configurar event listeners para limpiar errors
const setupErrorClearingListeners = () => {
  const form = document.getElementById("login-form");
  if (!form) return;

  const inputs = form.querySelectorAll("input[type='text'], input[type='password']");
  
  inputs.forEach((input) => {
    // Limpiar errors al escribir
    input.addEventListener("input", function(e) {
      e.preventDefault();

      // Limpiar mensajes de estado la primera vez que el usuario escriba
      AuthUtils.clearStatusMessages();

      // Eliminar clase de error del input actual
      if (this.classList.contains("error-input")) {
        this.classList.remove("error-input");
        
        // Limpiar estilo del toggle de password si aplica
        AuthUtils.togglePasswordErrorStyle(this, false);
        
        // Eliminar mensaje de error específico para este campo
        const error = this.parentElement.querySelector(".error-message");
        if (error) error.remove();
      }

      // Ocultar error de autenticación si no es de estado
      AuthUtils.clearMainErrorIfNotStatus();
    });

    // También limpiar mensajes de estado al hacer focus
    input.addEventListener("focus", function(e) {
      AuthUtils.clearStatusMessages();
    });
  });
};

// Función para limpiar parámetros de URL
const cleanUrlParams = () => {
  const url = new URL(window.location);
  const hasParams =
    url.searchParams.has("expired_session") ||
    url.searchParams.has("account_disabled");

  if (hasParams) {
    // Eliminar los parámetros de la URL sin recargar la página
    url.searchParams.delete("expired_session");
    url.searchParams.delete("account_disabled");
    window.history.replaceState({}, document.title, url.pathname + url.search);
  }
};

// Registrar validaciones cuando se carga el DOM
document.addEventListener("DOMContentLoaded", function () {
  // Verificar que FormManager esté disponible
  if (typeof FormManager === 'undefined' || !FormManager) {
    console.error('FormManager no está disponible');
    return;
  }

  // Limpiar parámetros de URL
  cleanUrlParams();

  // Inicializar funcionalidades del login
  initializeLoginFeatures();

  // Configurar listeners para limpiar errors
  setupErrorClearingListeners();

  // Registrar formulario de login
  if (document.getElementById("login-form")) {
    FormManager.register("login-form", {
      validate: AuthValidations.validateLogin,
      onSuccess: AuthHandlers.onLoginSuccess,
      onError: AuthHandlers.onLoginError,
      beforeSubmit: (form) => {
        // Limpiar todos los estilos de error previos
        AuthUtils.clearAllErrorStyles(form);
        
        // Limpiar mensaje principal si no es de estado
        AuthUtils.clearMainErrorIfNotStatus();
      },
      afterSubmit: (form) => {
        // Lógica adicional después del submit si es necesario
      }
    });
  }
});