const formulario = document.getElementById("login-form");
const errorMsg = document.getElementById("message-container");

// Limpiar la URL del parámetro expired_session al cargar la página
document.addEventListener("DOMContentLoaded", function () {
  const url = new URL(window.location);
  if (url.searchParams.has("expired_session")) {
    // Eliminar el parámetro de la URL sin recargar la página
    url.searchParams.delete("expired_session");
    window.history.replaceState({}, document.title, url.pathname + url.search);
  }

  // Inicializar funcionalidad de mostrar/ocultar contraseña
  initPasswordToggle();
});

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

// Función para mostrar mensaje de error en el contenedor principal
function showMainError(message, className = "error-message") {
  // Limpiar cualquier mensaje anterior
  clearMainError();

  // Crear nuevo mensaje
  const errorP = document.createElement("p");
  errorP.className = className;
  errorP.textContent = message;

  // Añadir al contenedor y mostrarlo
  errorMsg.appendChild(errorP);
  errorMsg.classList.add("visible");
}

// Función para limpiar el mensaje de error principal
function clearMainError() {
  const existingError = errorMsg.querySelector("p");
  if (existingError) {
    existingError.remove();
  }
  errorMsg.classList.remove("visible");
}

// Limpiar el mensaje de sesión expirada cuando el usuario comience a interactuar
function clearExpiredSessionMessage() {
  const sessionMsg = errorMsg.querySelector(".expired-session-message");
  if (sessionMsg) {
    clearMainError();
  }
}

formulario.addEventListener("submit", async function (e) {
  e.preventDefault();

  let isValid = true;
  const data = new FormData(this);

  // imprimir los valores del formulario
  // Display the key/value pairs
  for (var pair of data.entries()) {
    console.log(pair[0] + ", " + pair[1]);
  }

  const action = this.getAttribute("action");
  const method = this.getAttribute("method") || "POST";

  // Limpiar los mensajes de error previos de validación
  document
    .querySelectorAll(".error-message:not(.expired-session-message)")
    .forEach((errorMsgElement) => errorMsgElement.remove());

  document
    .querySelectorAll(".error-input")
    .forEach((errorInput) => errorInput.classList.remove("error-input"));

  // Limpiar el mensaje principal si no es de sesión expirada
  const mainErrorMsg = errorMsg.querySelector("p");
  if (
    mainErrorMsg &&
    !mainErrorMsg.classList.contains("expired-session-message")
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
    errorMsg.classList.add("visible"); // Asegurarse de que el contenedor de errores sea visible si hay errores de validación
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
      // Mostrar mensaje de error de autenticación
      showMainError(result.message || "Ha ocurrido un error");

      // Agregar estilo de error a los inputs
      const inputs = formulario.querySelectorAll(
        "input[type='text'], input[type='password']"
      );
      inputs.forEach((input) => {
        input.classList.add("error-input");
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

      // Limpiar mensaje de sesión expirada la primera vez que el usuario escriba
      clearExpiredSessionMessage();

      // Eliminar clase de error del input actual
      if (this.classList.contains("error-input")) {
        this.classList.remove("error-input");

        // Eliminar mensaje de error específico para este campo
        const error = this.parentElement.querySelector(".error-message");
        if (error) error.remove();
      }

      // Ocultar error de autenticación
      const mainErrorMsg = errorMsg.querySelector("p");
      if (
        mainErrorMsg &&
        !mainErrorMsg.classList.contains("expired-session-message")
      ) {
        clearMainError();
      }
    });
  });

// También limpiar el mensaje de sesión expirada al hacer click en cualquier input
formulario
  .querySelectorAll("input[type='text'], input[type='password']")
  .forEach((input) => {
    input.addEventListener("focus", function (e) {
      clearExpiredSessionMessage();
    });
  });

// Mostrar mensaje de error
function showError(input, message) {
  const error = document.createElement("p");
  error.classList.add("error-message");
  error.textContent = message;
  input.parentElement.appendChild(error);
  input.classList.add("error-input");

  // Mostrar el contenedor de errores
  errorMsg.classList.add("visible");
}
