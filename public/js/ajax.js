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
  formulario.addEventListener("submit", async function (e) {
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

    // Deshabilitar botón de envío y mostrar estado de carga
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : "";
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Procesando...";
    }

    let method = this.getAttribute("method");
    let action = this.getAttribute("action");

    try {
      const response = await fetch(action, {
        method: method,
        body: data,
        credentials: "same-origin",
      });

      // Primero comprobar si es una respuesta de redirección
      if (response.redirected) {
        window.location.href = response.url;
        return;
      }

      // Si no es redirección, entonces procesamos el JSON
      const responseData = await response.json();

      if (responseData.status === "success") {
        if (!isEditForm) {
          // Para formulario de crear: mostrar éxito y redirigir
          await CustomDialog.success(
            "Usuario Creado",
            responseData.mensaje || "El usuario se creó correctamente"
          );
          // Redirigir inmediatamente después del modal
          window.location.href = `${APP_URL}users`;
        } else {
          // Para formulario de editar: mostrar éxito y redirigir (igual que crear)
          await CustomDialog.success(
            "Usuario Actualizado",
            responseData.mensaje || "El usuario se actualizó correctamente"
          );
          // Redirigir inmediatamente después del modal
          window.location.href = `${APP_URL}users`;
        }
      } else if (responseData.status === "error") {
        if (responseData.errores) {
          let hasGeneralError = false;

          Object.entries(responseData.errores).forEach(([key, message]) => {
            const input = formulario.querySelector(`[name="${key}"]`);
            if (input) {
              showError(input, message);
            } else if (key === "general") {
              hasGeneralError = true;
              CustomDialog.error("Error", message);
            }
          });

          // Si no hay errores generales, mostrar el primer error específico
          if (
            !hasGeneralError &&
            Object.keys(responseData.errores).length > 0
          ) {
            const firstError = Object.values(responseData.errores)[0];
            CustomDialog.error("Error de Validación", firstError);
          }
        } else {
          // Error sin detalles específicos
          CustomDialog.error(
            "Error",
            responseData.mensaje ||
              responseData.message ||
              "Ocurrió un error al procesar la solicitud"
          );
        }
      }
    } catch (error) {
      console.error("Error:", error);
      CustomDialog.error(
        "Error de Conexión",
        "Ocurrió un error al procesar la solicitud. Por favor, inténtalo de nuevo."
      );
    } finally {
      // Restaurar botón de envío
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }
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
document.addEventListener("DOMContentLoaded", function () {
  const resetButton = document.querySelector('button[type="reset"]');

  if (resetButton) {
    resetButton.addEventListener("click", async function (e) {
      e.preventDefault();

      // Confirmar reset con CustomDialog
      const shouldReset = await CustomDialog.confirm(
        "Limpiar Formulario",
        "¿Está seguro de que desea limpiar todos los campos del formulario?",
        "Sí, limpiar",
        "Cancelar"
      );

      if (shouldReset) {
        const form = this.closest("form");
        if (form) {
          form.reset();
        }

        // Restaurar imagen por defecto
        const userAvatar = document.querySelector(".user-avatar");
        if (userAvatar) {
          userAvatar.style.backgroundImage = `url(${APP_URL}public/photos/default.jpg)`;
        }

        // Limpiar todos los mensajes de error
        const errorInputs = document.querySelectorAll(".error-input");
        errorInputs.forEach((input) => {
          clearError(input);
        });

        // Asegurar que todos los helpers estén visibles
        document.querySelectorAll(".helper").forEach((helper) => {
          helper.style.display = "";
        });

        // Mostrar confirmación
        CustomDialog.toast(
          "Formulario limpiado correctamente",
          "success",
          2000
        );
      }
    });
  }
});
