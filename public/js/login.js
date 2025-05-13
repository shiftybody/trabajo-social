const formulario = document.getElementById("login-form");

formulario.addEventListener("submit", async function (e) {
  e.preventDefault();

  let isValid = true;
  const data = new FormData(this);
  const action = this.getAttribute("action");
  const method = this.getAttribute("method") || "POST";

  // Ocultar mensaje de error anterior
  const errorDiv = document.getElementById("error-msg");

  // Limpiar los mensajes de error previos
  document
    .querySelectorAll(".error-message")
    .forEach((errorMsg) => errorMsg.remove());
  document
    .querySelectorAll(".error-input")
    .forEach((errorInput) => errorInput.classList.remove("error-input"));

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
  const originalText = submitBtn ? submitBtn.innerText : "";

  if (submitBtn) {
    submitBtn.innerText = "Procesando...";
    submitBtn.disabled = true;
  }

  // obtener el elemento con id error-msg
  const errorMsg = document.getElementById("error-msg");

  try {
    const response = await fetch(action, {
      method: method,
      body: data,
      credentials: "same-origin",
    });

    const result = await response.json();

    if (result.status === "success") {
      if (result.redirect) {
        window.location.href = data.redirect;
      }
    } else if (result.status === "error") {
      if (errorDiv) {
        let errorP = errorDiv.querySelector("p");
        if (!errorP) {
          errorP = document.createElement("p");
          errorDiv.appendChild(errorP);
          errorP.classList.add("error-message");
        }

        // mostrar el
        errorDiv.hidden = false;
        errorP.textContent = result.message || "Ha ocurrido un error";

        submitBtn.innerText = "Enviar";
        submitBtn.disabled = false;
      }

      // mostrar todos los inputs y agregarle la clase error-input
      const inputs = formulario.querySelectorAll("input");

      inputs.forEach((input) => {
        input.classList.add("error-input");
      });
    }
  } catch (error) {
    errorMsg.textContent = "Error al enviar el formulario. Inténtalo de nuevo.";
    errorMsg.classList.add("error-message");
  }
});

//Eliminar estilo de error al escribir en el input
formulario.querySelectorAll("input").forEach((input) => {
  input.addEventListener("input", function (e) {
    e.preventDefault();
    if (this.classList.contains("error-input")) {
      this.classList.remove("error-input");
      const error = this.parentElement.querySelector(".error-message");
      if (error) error.remove();
    }
    // esconder el mensaje de error si existe
    if (document.getElementById("error-msg")) {
      const errorDiv = document.getElementById("error-msg");
      errorDiv.hidden = true;
    }
  });
});

// Mostrar mensaje de error
function showError(input, message) {
  const error = document.createElement("p");
  error.classList.add("error-message");
  error.textContent = message;
  input.parentElement.appendChild(error);
  input.classList.add("error-input");
}
