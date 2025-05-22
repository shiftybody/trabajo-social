const formularios = document.querySelectorAll(".form-ajax");

const PATTERN_MSG = {
  "[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}":
    "El campo solo puede contener letras y espacios",
  "[0-9]{10}": "El campo debe contener diez digitos",
  "(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}":
    "Como mínimo una minúscula, mayuscula, número y caracter especial",
  "^((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}|[a-zA-Z0-9._@!#$%^&*+\\-]{3,70})$":
    "El correo o nombre de usuario no es válido",
};

// Para cada input de tipo file en la pagina con la clase .input-file
document.querySelectorAll(".input-file").forEach((input) => {
  input.addEventListener("change", function () {
    const file = this.files[0];
    const reader = new FileReader();

    reader.onload = function () {
      document.querySelector(
        ".user-avatar"
      ).style.backgroundImage = `url(${reader.result})`;
    };
    reader.readAsDataURL(file);
  });
});

// Para cada formulario en la pagina con la clase .form-api
formularios.forEach((formulario) => {
  //escuchar el evento reset y limpiar los mensajes de error, estilos y valores de los inputs
  formulario.addEventListener("reset", function (e) {
    document
      .querySelectorAll(".error-message")
      .forEach((errorMsg) => errorMsg.remove());
    document
      .querySelectorAll(".error-input")
      .forEach((errorInput) => errorInput.classList.remove("error-input"));
  });

  // escuchar el evento submit validar los campos del formulario y enviar los datos
  formulario.addEventListener("submit", function (e) {
    e.preventDefault();

    let isValid = true;
    const data = new FormData(this);

    // Verificar si estamos en el formulario de edición
    const isEditForm = window.location.href.includes("/edit/");
    const changePassword = isEditForm
      ? document.getElementById("change_password")?.value === "1"
      : false;

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

      // Si no hay input o es un campo oculto, saltamos la validación
      if (!input || input.type === "hidden") return;

      // Validar campos obligatorios
      // Si el campo es un string vacío y no es el campo avatar
      if (
        typeof value === "string" &&
        value.trim() === "" &&
        key !== "avatar"
      ) {
        // Excepción para campos de contraseña en formulario de edición
        if (
          isEditForm &&
          (key === "password" || key === "password2") &&
          !changePassword
        ) {
          return; // No validar contraseñas en edición si no se ha activado el cambio
        }

        let label =
          input.parentElement.querySelector("label")?.textContent || key;
        // Si el campo es un select
        if (input.tagName === "SELECT") {
          showError(input, `Selecciona un rol para el usuario`);
        } else {
          showError(
            input,
            `El campo ${label.toLowerCase()} no puede estar vacío`
          );
        }
        isValid = false;
        return; // Salir de la validación de este campo
      }

      // si el input es de tipo email y no es un string vacio
      if (input.type === "email" && value.trim() !== "") {
        // Validar formato de email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
          showError(input, "El correo electrónico no es válido");
          isValid = false;
        }
      }

      // Validar patrón si el campo tiene el atributo pattern
      if (input.hasAttribute("pattern") && value.trim() !== "") {
        const pattern = input.getAttribute("pattern");
        const regex = new RegExp(pattern);

        // Validar si el campo contraseña2 es igual a contraseña
        if (key === "password2" && value !== data.get("password")) {
          showError(input, "Las contraseñas no coinciden");
          isValid = false;
          return; // Salir de la validación de este campo
        }

        // Validar si el campo cumple con el patrón de validación
        if (!regex.test(value)) {
          const errorMessage =
            PATTERN_MSG[pattern] ||
            "El valor no coincide con el patrón requerido";
          showError(input, errorMessage);
          isValid = false;
        }
      }
    });

    if (!isValid) return;

    let method = this.getAttribute("method");
    let action = this.getAttribute("action");

    fetch(action, {
      method: method,
      body: data,
      credentials: "same-origin",
    })
      .then((response) => {
        // Primero comprobar si es una respuesta de redirección
        if (response.redirected) {
          window.location.href = response.url;
          return;
        }

        // Si no es redirección, entonces procesamos el JSON
        return response.json().then((responseData) => {
          if (responseData.status === "success") {
            // Mostrar mensaje de éxito
            // TODO: llamar a un modal para mostrar el mensaje de éxito
            alert(responseData.mensaje || "Operación completada con éxito");
            // TODO: decidir si ocurrira o no esta redireccion
            // Opcional: redireccionar si se quiere volver a la lista
            window.location.href = window.location.href.replace("/create", "");
          } else if (responseData.status === "error") {
            // Mostrar errores en formulario
            if (responseData.errores) {
              Object.entries(responseData.errores).forEach(([key, message]) => {
                const input = formulario.querySelector(`[name="${key}"]`);
                if (input) {
                  showError(input, message);
                } else if (key === "general") {
                  // TODO: llamar a un modal para mostrar el error general
                  alert(message);
                }
              });
            }
          }
        });
      })
      .catch((error) => {
        console.error("Error:", error);
        alert(
          "Ocurrió un error al procesar la solicitud. Por favor, inténtalo de nuevo."
        );
      });
  });
});

//Eliminar estilo de error al escribir en el input
formularios.forEach((formulario) => {
  formulario.addEventListener("input", function (e) {
    const input = e.target;
    if (input.classList.contains("error-input")) {
      input.classList.remove("error-input");
      const errorMsg = input.parentElement.querySelector(".error-message");
      if (errorMsg) {
        errorMsg.remove();
      }
    }
  });
});

// Si es un select tambien eliminar el estilo de error al hacer click
document.querySelectorAll("select").forEach((select) => {
  select.addEventListener("click", function (e) {
    if (this.classList.contains("error-input")) {
      this.classList.remove("error-input");
      const errorMsg = this.parentElement.querySelector(".error-message");
      if (errorMsg) {
        errorMsg.remove();
      }
    }
  });
});

// Mostrar mensaje de error
function showError(input, message) {
  // Eliminar mensajes de error previos si existen
  clearError(input);

  const error = document.createElement("p");
  error.classList.add("error-message");
  error.textContent = message;

  // Si es un input de tipo file, buscar y ocultar el helper
  if (input.type === "file") {
    const helper = input.parentElement.querySelector(".helper");
    if (helper) {
      helper.style.display = "none";
    }
  }

  input.parentElement.appendChild(error);
  input.classList.add("error-input");
}

// Función para limpiar errores
function clearError(input) {
  // Eliminar mensaje de error si existe
  const errorMessage = input.parentElement.querySelector(".error-message");
  if (errorMessage) {
    errorMessage.remove();
  }

  // Quitar clase de error del input
  input.classList.remove("error-input");

  // Si es un input de tipo file, mostrar el helper nuevamente
  if (input.type === "file") {
    const helper = input.parentElement.querySelector(".helper");
    if (helper) {
      helper.style.display = "";
    }
  }
}

// Manejar el evento reset para limpiar todos los errores y restaurar la imagen por defecto
document
  .querySelector('button[type="reset"]')
  .addEventListener("click", function () {
    // Restaurar imagen por defecto
    document.querySelector(
      ".user-avatar"
    ).style.backgroundImage = `url(../public/photos/default.jpg)`;

    // Limpiar todos los mensajes de error
    const errorInputs = document.querySelectorAll(".error-input");
    errorInputs.forEach((input) => {
      clearError(input);
    });

    // Asegurar que todos los helpers estén visibles
    document.querySelectorAll(".helper").forEach((helper) => {
      helper.style.display = "";
    });
  });
