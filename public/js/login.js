const formulario = document.getElementById("login-form");
const errorMsg = document.getElementById("error-msg");
const authError = document.getElementById("auth-error");

// Limpiar la URL del parámetro expired_session al cargar la página
document.addEventListener("DOMContentLoaded", function () {
  const url = new URL(window.location);
  if (url.searchParams.has("expired_session")) {
    // Eliminar el parámetro de la URL sin recargar la página
    url.searchParams.delete("expired_session");
    window.history.replaceState({}, document.title, url.pathname + url.search);
  }
});

// Limpiar el mensaje de sesión expirada cuando el usuario comience a interactuar
function clearExpiredSessionMessage() {
  const sessionMsg = errorMsg.querySelector(".expired-session-message");
  if (sessionMsg) {
    sessionMsg.remove();

    // Si no hay otros errores, ocultar el contenedor
    const authErrorText = authError ? authError.textContent.trim() : "";
    if (!authErrorText) {
      errorMsg.classList.remove("visible");
    }
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
    .forEach((errorMsg) => errorMsg.remove());

  document
    .querySelectorAll(".error-input")
    .forEach((errorInput) => errorInput.classList.remove("error-input"));

  // Limpiar el mensaje de error de autenticación
  if (authError) {
    authError.textContent = "";
    authError.classList.remove("error-message");
  }

  // Conservar solo el mensaje de sesión expirada si existe
  const sessionMsg = errorMsg.querySelector(".expired-session-message");
  if (sessionMsg) {
    // Limpia cualquier otro contenido que no sea el mensaje de sesión expirada
    Array.from(errorMsg.childNodes).forEach((node) => {
      if (
        node !== sessionMsg &&
        node.nodeType === Node.ELEMENT_NODE &&
        !node.classList.contains("expired-session-message") &&
        node.id !== "auth-error"
      ) {
        errorMsg.removeChild(node);
      }
    });
  }

  // Para cada campo del formulario
  data.forEach((value, key) => {
    const input = formulario.querySelector(`[name="${key}"]`);

    // Validar campos obligatorios
    // Si el campo es un string vacío y no es el campo avatar
    if (typeof value === "string" && value.trim() === "") {
      let label = input.parentElement.querySelector("label").textContent;
      showError(input, `El campo ${label.toLowerCase()} no puede estar vacío`);
      isValid = false;
      return; // Salir de la validación de este campo
    }
  });

  // si no es valido, no hacer la peticion
  if (!isValid) return;

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
      // Mostrar el contenedor de errores
      errorMsg.classList.add("visible");

      // Limpiar cualquier mensaje de autenticación anterior
      if (authError) {
        // Asegurarse de que esté vacío antes de añadir el nuevo error
        authError.textContent = "";

        // Mostrar el mensaje de error de autenticación
        authError.textContent = result.message || "Ha ocurrido un error";
        authError.classList.add("error-message");
      }

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
    errorMsg.classList.add("visible");
    if (authError) {
      authError.textContent =
        "Error al enviar el formulario. Inténtalo de nuevo.";
      authError.classList.add("error-message");
    }

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
      if (authError) {
        authError.textContent = "";
        authError.classList.remove("error-message");

        // Si no hay más mensajes, ocultar todo el contenedor
        const sessionMsg = errorMsg.querySelector(".expired-session-message");
        if (!sessionMsg) {
          errorMsg.classList.remove("visible");
        }
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
