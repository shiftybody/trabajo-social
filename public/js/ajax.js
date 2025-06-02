const PATTERN_MSG = {
  "[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}":
    "El campo solo puede contener letras y espacios",
  "[0-9]{10}": "El campo debe contener diez digitos",
  "(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}":
    "Mínimo 8 caracteres, una minúscula, mayuscula, número y caracter especial",
  "^((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}|[a-zA-Z0-9._@!#$%^&*+\\-]{3,70})$":
    "El correo o nombre de usuario no es válido",
};

// ==================== UTILIDADES DE ERROR ====================
function showError(input, message) {
  clearError(input);
  const error = document.createElement("p");
  error.classList.add("error-message");
  error.textContent = message;

  if (input.type === "file") {
    const helper = input.parentElement.querySelector(".helper");
    if (helper) helper.style.display = "none";
  }

  input.parentElement.appendChild(error);
  input.classList.add("error-input");
}

function clearError(input) {
  const errorMessage = input.parentElement.querySelector(".error-message");
  if (errorMessage) errorMessage.remove();

  input.classList.remove("error-input");

  if (input.type === "file") {
    const helper = input.parentElement.querySelector(".helper");
    if (helper) helper.style.display = "";
  }
}

// ==================== UTILIDAD PARA CERRAR MODALES ====================
async function closeCurrentModal() {
  // Buscar el modal actualmente abierto
  const openModal = document.querySelector(".base-modal.show");

  if (openModal && openModal.__modalInstance) {
    const modalInstance = openModal.__modalInstance;

    // Cerrar el modal y esperar a que termine la transición
    return new Promise((resolve) => {
      // Si ya está cerrado, resolver inmediatamente
      if (!modalInstance.isOpen) {
        resolve();
        return;
      }

      // Configurar callback temporal para cuando se cierre
      const originalOnHide = modalInstance.config.onHide;
      modalInstance.config.onHide = function (modal) {
        // Llamar al callback original si existe
        if (originalOnHide) {
          originalOnHide(modal);
        }

        // Restaurar el callback original
        modalInstance.config.onHide = originalOnHide;

        // Resolver la promesa
        resolve();
      };

      // Cerrar el modal
      modalInstance.hide();
    });
  }

  return Promise.resolve();
}

// ==================== MANEJADORES ESPECÍFICOS DE FORMULARIOS (CORREGIDOS) ====================
const FormHandlers = {
  // Manejo para reset de contraseña
  resetPasswordForm: {
    async onSuccess(responseData) {
      // Cerrar modal actual
      await closeCurrentModal();

      await CustomDialog.success(
        "Contraseña Actualizada",
        responseData.message || "La contraseña se actualizó correctamente"
      );
    },
  },

  // Manejo para cambio de estado
  changeStatusForm: {
    async onSuccess(responseData) {
      // Cerrar modal actual
      await closeCurrentModal();

      await CustomDialog.success(
        "Estado Actualizado",
        responseData.message ||
          "El estado del usuario se actualizó correctamente"
      );

      // Recargar datos de la tabla si existe
      if (typeof loadData === "function" && typeof table !== "undefined") {
        table.clear().draw();
        loadData();
      }
    },
  },

  // Manejo para formularios de creación
  createForm: {
    async onSuccess(responseData) {
      // Cerrar modal actual
      await closeCurrentModal();

      await CustomDialog.success(
        "Usuario Creado",
        responseData.message || "El usuario se creó correctamente"
      );
      window.location.href = `${APP_URL}/users`;
    },
  },

  // Manejo para formularios de edición
  editForm: {
    async onSuccess(responseData) {
      await CustomDialog.success(
        "Usuario Actualizado",
        responseData.message || "El usuario se actualizó correctamente"
      );
      window.location.href = `${APP_URL}/users`;
    },
  },
};

// ==================== IDENTIFICACIÓN DE TIPO DE FORMULARIO ====================
function getFormType(form) {
  // Identificar por ID específico primero
  if (form.id === "resetPasswordForm") return "resetPasswordForm";
  if (form.id === "changeStatusForm") return "changeStatusForm";

  // Identificar por contexto
  if (window.location.href.includes("/edit/")) return "editForm";

  // Por defecto, formulario de creación
  return "createForm";
}

// ==================== VALIDACIÓN DE FORMULARIOS ====================
function validateForm(form, data) {
  let isValid = true;
  const formType = getFormType(form);
  const isEditForm = formType === "editForm";
  const changePassword = isEditForm
    ? document.getElementById("change_password")?.value === "1"
    : false;

  // Limpiar errores previos
  form
    .querySelectorAll(".error-message")
    .forEach((errorMsg) => errorMsg.remove());
  form
    .querySelectorAll(".error-input")
    .forEach((errorInput) => errorInput.classList.remove("error-input"));

  data.forEach((value, key) => {
    const input = form.querySelector(`[name="${key}"]`);
    if (!input || input.type === "hidden") return;

    // Validar campos obligatorios
    if (typeof value === "string" && value.trim() === "" && key !== "avatar") {
      if (
        isEditForm &&
        (key === "password" || key === "password2") &&
        !changePassword
      ) {
        return;
      }

      let label = input.parentElement.querySelector("label")?.textContent || key;
      if (input.tagName === "SELECT") {
        showError(input, `Selecciona un rol para el usuario`);
      } else {
        showError(
          input,
          `El campo ${label.toLowerCase()} no puede estar vacío`
        );
      }
      isValid = false;
      return;
    }

    // Validar email
    if (input.type === "email" && value.trim() !== "") {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(value)) {
        showError(input, "El correo electrónico no es válido");
        isValid = false;
      }
    }

    // Validar patrones
    if (input.hasAttribute("pattern") && value.trim() !== "") {
      const pattern = input.getAttribute("pattern");
      const regex = new RegExp(pattern);

      if (key === "password2" && value !== data.get("password")) {
        showError(input, "Las contraseñas no coinciden");
        isValid = false;
        return;
      }

      if (!regex.test(value)) {
        const errorMessage =
          PATTERN_MSG[pattern] ||
          "El valor no coincide con el patrón requerido";
        showError(input, errorMessage);
        isValid = false;
      }
    }
  });

  return isValid;
}

// ==================== MANEJO DE RESPUESTAS ====================
async function handleSuccessResponse(form, responseData) {
  const formType = getFormType(form);
  const handler = FormHandlers[formType];

  if (handler && handler.onSuccess) {
    await handler.onSuccess(responseData);
  } else {
    console.warn(
      `No se encontró manejador para el tipo de formulario: ${formType}`
    );
    await CustomDialog.success(
      "Operación Exitosa",
      responseData.message || "Operación completada correctamente"
    );
  }
}

function handleErrorResponse(form, responseData) {
  if (responseData.errores) {
    let hasFieldErrors = false;
    let generalErrors = [];

    Object.entries(responseData.errores).forEach(([key, message]) => {
      const input = form.querySelector(`[name="${key}"]`);

      if (input) {
        showError(input, message);
        hasFieldErrors = true;
      } else if (["general", "sistema", "server"].includes(key)) {
        generalErrors.push(message);
      } else {
        console.warn(`Campo '${key}' no encontrado en el formulario:`, message);
        generalErrors.push(`${key}: ${message}`);
      }
    });

    if (generalErrors.length > 0) {
      // cerrar modal actual
      closeCurrentModal();

      CustomDialog.error("Error del Sistema", generalErrors.join("\n"));
    }

    if (hasFieldErrors && generalErrors.length === 0) {
      CustomDialog.toast(
        "Por favor, corrige los errores marcados en el formulario",
        "error",
        3000
      );
    }
  } else {
    // cerrar modal actual
    closeCurrentModal();

    CustomDialog.error(
      "Error",
      responseData.message || "Ocurrió un error al procesar la solicitud"
    );
  }
}

// ==================== FUNCIÓN PRINCIPAL ====================
function attachAjaxFormHandlers(formulario) {
  // Event listener para reset
  formulario.addEventListener("reset", function (e) {
    formulario
      .querySelectorAll(".error-message")
      .forEach((errorMsg) => errorMsg.remove());
    formulario
      .querySelectorAll(".error-input")
      .forEach((errorInput) => errorInput.classList.remove("error-input"));
  });

  // Event listener para submit
  formulario.addEventListener("submit", async function (e) {
    e.preventDefault();

    const data = new FormData(this);

    // Validar formulario
    if (!validateForm(this, data)) return;

    // Deshabilitar botón de envío
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.textContent : "";
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Procesando...";
    }

    try {
      const response = await fetch(this.getAttribute("action"), {
        method: this.getAttribute("method"),
        body: data,
        credentials: "same-origin",
        headers: {
          Accept: "application/json",
        },
      });

      if (response.redirected) {
        window.location.href = response.url;
        return;
      }

      const responseData = await response.json();

      if (responseData.status === "success") {
        await handleSuccessResponse(this, responseData);
      } else if (responseData.status === "error") {
        handleErrorResponse(this, responseData);
      }
    } catch (error) {
      // cerrar modal actual
      closeCurrentModal();
      CustomDialog.error(
        "Error de Conexión",
        "Ocurrió un error al procesar la solicitud. Por favor, inténtalo de nuevo."
      );
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }
  });

  // Event listener para input
  formulario.addEventListener("input", function (e) {
    const input = e.target;
    if (input.classList.contains("error-input")) {
      clearError(input);
    }
  });
}

// ==================== INICIALIZACIÓN ====================
document.addEventListener("DOMContentLoaded", function () {
  // Adjuntar manejadores a formularios AJAX
  document.querySelectorAll(".form-ajax").forEach(attachAjaxFormHandlers);

  // Manejadores para inputs de archivo
  document.querySelectorAll(".input-file").forEach((input) => {
    input.addEventListener("change", function () {
      const file = this.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = function () {
        const userAvatar = document.querySelector(".user-avatar");
        if (userAvatar) {
          userAvatar.style.backgroundImage = `url(${reader.result})`;
        }
      };
      reader.readAsDataURL(file);
    });
  });

  // Manejadores para selects
  document.querySelectorAll("select").forEach((select) => {
    select.addEventListener("click", function () {
      if (this.classList.contains("error-input")) {
        clearError(this);
      }
    });
  });

  // Manejador para botón reset
  const resetButton = document.querySelector('button[type="reset"]');
  if (resetButton) {
    resetButton.addEventListener("click", async function (e) {
      e.preventDefault();

      const shouldReset = await CustomDialog.confirm(
        "Limpiar Formulario",
        "¿Está seguro de que desea limpiar todos los campos del formulario?",
        "Sí, limpiar",
        "Cancelar"
      );

      if (shouldReset) {
        const form = this.closest("form");
        if (form) form.reset();

        const userAvatar = document.querySelector(".user-avatar");
        if (userAvatar) {
          userAvatar.style.backgroundImage = `url(${APP_URL}/public/photos/default.jpg)`;
        }

        document.querySelectorAll(".error-input").forEach(clearError);
        document
          .querySelectorAll(".helper")
          .forEach((helper) => (helper.style.display = ""));

        CustomDialog.toast(
          "Formulario limpiado correctamente",
          "success",
          2000
        );
      }
    });
  }
});

// ==================== EXPORTAR FUNCIONES ==================
window.attachAjaxFormHandlers = attachAjaxFormHandlers;
window.showError = showError;
window.clearError = clearError;
