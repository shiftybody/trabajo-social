const formulario = document.getElementById("login-form");
const errorMsg = document.getElementById("message-container");

// Limpiar la URL de parámetros de estado al cargar la página
document.addEventListener("DOMContentLoaded", function () {
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

  // Inicializar funcionalidad de mostrar/ocultar contraseña
  initPasswordToggle();
});


formulario.addEventListener("submit", async function (e) {
  e.preventDefault();

  let isValid = true;
  const data = new FormData(this);

  const action = this.getAttribute("action");
  const method = this.getAttribute("method") || "POST";

  // Limpiar los messages de error previos de validación
  document
    .querySelectorAll(
      ".error-message:not(.expired-session-message):not(.account-disabled-message)"
    )
    .forEach((errorMsgElement) => errorMsgElement.remove());

  document.querySelectorAll(".error-input").forEach((errorInput) => {
    errorInput.classList.remove("error-input");
    // Limpiar estilo del toggle de password si aplica
    togglePasswordErrorStyle(errorInput, false);
  });

  // Limpiar el message principal si no es de estado (sesión expirada o cuenta deshabilitada)
  const mainErrorMsg = errorMsg.querySelector("p");
  if (
    mainErrorMsg &&
    !mainErrorMsg.classList.contains("expired-session-message") &&
    !mainErrorMsg.classList.contains("account-disabled-message")
  ) {
    clearMainError();
  }

  // Para cada campo del formulario
  data.forEach((value, key) => {
    const input = formulario.querySelector(`[name="${key}"]`);

    // Solo validar inputs que existen
    if (!input) {
      return;
    }

    // Validar campos obligatorios
    // Si el campo es un string vacío y no es el campo avatar
    if (typeof value === "string" && value.trim() === "") {
      // Buscar el label - primero en el parent directo, luego en el grandparent
      let label = input.parentElement.querySelector("label");
      if (!label) {
        // Si no se encuentra en el parent, buscar en el grandparent (caso del password)
        label = input.parentElement.parentElement.querySelector("label");
      }

      // Verificar que existe un label
      if (label) {
        showError(
          input,
          `El campo ${label.textContent.toLowerCase()} no puede estar vacío`
        );
        isValid = false;
      }
      return; // Salir de la validación de este campo
    }
  });

  // si no es valido, no hacer la peticion
  if (!isValid) {
    return;
  }

  const submitBtn = this.querySelector('button[type="submit"]');

  if (submitBtn) {
    submitBtn.innerText = "Procesando...";
    submitBtn.disabled = true;
  }

  try {
    const response = await fetch(action, {
      method: method,
      body: data,
      credentials: "same-origin",
    });

    const result = await response.json();

    if (result.status === "success") {
      if (result.redirect) {
        window.location.href = result.redirect;
      }
    } else if (result.status === "error") {
      // Mostrar message de error de autenticación
      showMainError(result.message || "Ha ocurrido un error");

      // Agregar estilo de error a los inputs
      const inputs = formulario.querySelectorAll(
        "input[type='text'], input[type='password']"
      );
      inputs.forEach((input) => {
        input.classList.add("error-input");
        // Aplicar estilo de error al toggle de password si aplica
        togglePasswordErrorStyle(input, true);
      });

      // Restaurar el botón
      if (submitBtn) {
        submitBtn.innerText = "Iniciar Sesión";
        submitBtn.disabled = false;
      }
    }
  } catch (error) {
    // Mostrar error genérico
    showMainError("Error al enviar el formulario. Inténtalo de nuevo.");

    // Restaurar el botón
    if (submitBtn) {
      submitBtn.innerText = "Iniciar Sesión";
      submitBtn.disabled = false;
    }
  }
});

// Eliminar estilo de error al escribir en el input
formulario
  .querySelectorAll("input[type='text'], input[type='password']")
  .forEach((input) => {
    input.addEventListener("input", function (e) {
      e.preventDefault();

      // Limpiar messages de estado la primera vez que el usuario escriba
      clearStatusMessages();

      // Eliminar clase de error del input actual
      if (this.classList.contains("error-input")) {
        this.classList.remove("error-input");

        // Limpiar estilo del toggle de password si aplica
        togglePasswordErrorStyle(this, false);

        // Eliminar message de error específico para este campo
        const error = this.parentElement.querySelector(".error-message");
        if (error) error.remove();
      }

      // Ocultar error de autenticación
      const mainErrorMsg = errorMsg.querySelector("p");
      if (
        mainErrorMsg &&
        !mainErrorMsg.classList.contains("expired-session-message") &&
        !mainErrorMsg.classList.contains("account-disabled-message")
      ) {
        clearMainError();
      }
    });
  });

// También limpiar los messages de estado al hacer click en cualquier input
formulario
  .querySelectorAll("input[type='text'], input[type='password']")
  .forEach((input) => {
    input.addEventListener("focus", function (e) {
      clearStatusMessages();
    });
  });

// Mostrar message de error
function showError(input, message) {
  const error = document.createElement("p");
  error.classList.add("error-message");
  error.textContent = message;
  input.parentElement.appendChild(error);
  input.classList.add("error-input");

  togglePasswordErrorStyle(input, true);
}

// Función auxiliar para manejar los estilos de error del input de password
function togglePasswordErrorStyle(input, hasError) {
  const toggleButton = document.getElementById("password-toggle");

  if (input.id === "password" && toggleButton) {
    if (hasError) {
      toggleButton.classList.add("error-toggle");
    } else {
      toggleButton.classList.remove("error-toggle");
    }
  }
}

// Función para inicializar el toggle de contraseña
function initPasswordToggle() {
  const passwordInput = document.getElementById("password");
  const toggleButton = document.getElementById("password-toggle");

  if (passwordInput && toggleButton) {
    // Función para mostrar/ocultar el botón según el contenido
    function toggleButtonVisibility() {
      if (passwordInput.value.trim() === "") {
        toggleButton.style.display = "none";
      } else {
        toggleButton.style.display = "flex";
      }
    }

    // Inicializar la visibilidad del botón
    toggleButtonVisibility();

    // Escuchar cambios en el input
    passwordInput.addEventListener("input", toggleButtonVisibility);
    passwordInput.addEventListener("paste", function () {
      // Usar setTimeout para esperar a que se procese el paste
      setTimeout(toggleButtonVisibility, 0);
    });

    toggleButton.addEventListener("click", function () {
      const isPassword = passwordInput.type === "password";

      // Cambiar el tipo del input
      passwordInput.type = isPassword ? "text" : "password";

      // Cambiar el icono
      const eyeIcon = toggleButton.querySelector(".eye-icon");
      const eyeOffIcon = toggleButton.querySelector(".eye-off-icon");

      if (isPassword) {
        eyeIcon.style.display = "none";
        eyeOffIcon.style.display = "block";
      } else {
        eyeIcon.style.display = "block";
        eyeOffIcon.style.display = "none";
      }
    });
  }
}

// Función para mostrar un message de error principal
function showMainError(message, className = "account-disabled-message") {
  // Limpiar cualquier message anterior
  clearMainError();

  // Crear nuevo message
  const errorP = document.createElement("p");
  errorP.className = className;
  errorP.textContent = message;

  // Añadir al contenedor y mostrarlo
  errorMsg.appendChild(errorP);
  errorMsg.classList.add("visible");
  
}

// Función para limpiar el message de error principal
function clearMainError() {
  const existingError = errorMsg.querySelector("p");
  if (existingError) {
    existingError.remove();
  }
  errorMsg.classList.remove("visible");
}

// Limpiar messages de estado cuando el usuario comience a interactuar
function clearStatusMessages() {
  const statusMessages = errorMsg.querySelectorAll(
    ".expired-session-message, .account-disabled-message"
  );
  if (statusMessages.length > 0) {
    statusMessages.forEach((msg) => msg.remove());
    if (!errorMsg.querySelector("p")) {
      errorMsg.classList.remove("visible");
    }
  }
}
