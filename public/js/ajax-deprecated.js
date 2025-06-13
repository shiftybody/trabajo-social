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

// ==================== MANEJADORES ESPECÍFICOS DE FORMULARIOS ==================
const FormHandlers = {
  // Manejo para reset de contraseña
  resetPasswordForm: {
    async onSuccess(responseData) {
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
      await closeCurrentModal();
      await CustomDialog.success(
        "Estado Actualizado",
        responseData.message ||
          "El estado del usuario se actualizó correctamente"
      );
      if (typeof table !== 'undefined') {
        table.clear().draw();
      }
      if (typeof loadData === 'function') {
        loadData();
      }
    },
  },

  createRoleForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      await CustomDialog.success(
        "Rol Creado",
        responseData.message || "El rol se creó correctamente"
      );
      if (typeof loadData === 'function') {
        loadData();
      }
    },
  },

  // Manejo para formularios de creación
  createUserForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      await CustomDialog.success(
        "Usuario Creado",
        responseData.message || "El usuario se creó correctamente"
      );
      window.location.href = `${APP_URL}users`;
    },
  },

  // Manejo para formularios de edición
  editUserForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      if (responseData.redirect) {
        await CustomDialog.success(
          "Usuario Actualizado",
          responseData.message || "El usuario se actualizó correctamente"
        );
        window.location.href = responseData.redirect;
      } else {
        await CustomDialog.info(
          "Sin Cambios",
          "No se han enviado cambios al actualizar el usuario"
        );
      }
    },
  },

  // ==================== MANEJADORES DE CONFIGURACIÓN ====================

  // Manejo para formularios de niveles socioeconómicos
  levelForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      await CustomDialog.success(
        "Nivel Guardado",
        responseData.message || "El nivel socioeconómico se guardó correctamente"
      );
      // Recargar la sección actual en ConfigManager
      if (typeof configManager !== 'undefined' && configManager) {
        configManager.loadSection(configManager.currentSection);
      }
    },
  },

  // Manejo para formularios de reglas de aportación
  ruleForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      await CustomDialog.success(
        "Regla Guardada",
        responseData.message || "La regla de aportación se guardó correctamente"
      );
      // Recargar la sección actual en ConfigManager
      if (typeof configManager !== 'undefined' && configManager) {
        configManager.loadSection(configManager.currentSection);
      }
    },
  },

  // Manejo para formularios de criterios
  criteriaForm: {
    async onSuccess(responseData) {
      await closeCurrentModal();
      await CustomDialog.success(
        "Criterio Guardado",
        responseData.message || "El criterio se guardó correctamente"
      );
      // Recargar la sección actual en ConfigManager
      if (typeof configManager !== 'undefined' && configManager) {
        configManager.loadSection(configManager.currentSection);
      }
    },
  },
};

// ==================== IDENTIFICACIÓN DE TIPO DE FORMULARIO ====================
function getFormType(form) {
  // Formularios existentes
  if (form.id === "resetPasswordForm") return "resetPasswordForm";
  if (form.id === "changeStatusForm") return "changeStatusForm";
  if (form.id === "createRoleForm") return "createRoleForm";
  if (form.id === "editUserForm") return "editUserForm";
  if (form.id === "createUserForm") return "createUserForm";
  if (form.id === "levelForm") return "levelForm";
  if (form.id === "ruleForm") return "ruleForm";
  if (form.id === "criteriaForm") return "criteriaForm";

  return null;
}

// ==================== VALIDACIÓN DE FORMULARIOS ====================
function validateForm(form, data) {
  let isValid = true;
  const formType = getFormType(form);
  const iseditUserForm = formType === "editUserForm";
  const changePassword = iseditUserForm
    ? document.getElementById("change_password")?.value === "1"
    : false;

  // Limpiar errores previos
  form
    .querySelectorAll(".error-message")
    .forEach((errorMsg) => errorMsg.remove());
  form
    .querySelectorAll(".error-input")
    .forEach((errorInput) => errorInput.classList.remove("error-input"));

  // Validaciones específicas por tipo de formulario
  if (formType === "levelForm") {
    return validateLevelForm(form, data);
  } else if (formType === "ruleForm") {
    return validateRuleForm(form, data);
  } else if (formType === "criteriaForm") {
    return validateCriteriaForm(form, data);
  }

  // Validación genérica para otros formularios
  data.forEach((value, key) => {
    const input = form.querySelector(`[name="${key}"]`);
    if (!input || input.type === "hidden") return;

    // OMITIR validación de campos vacíos para createRoleForm
    if (formType === "createRoleForm") return;

    // Validar campos obligatorios
    if (typeof value === "string" && value.trim() === "" && key !== "avatar") {
      if (
        iseditUserForm &&
        (key === "password" || key === "password2") &&
        !changePassword
      ) {
        return;
      }

      let label =
        input.parentElement.querySelector("label")?.textContent || key;
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

// ==================== VALIDACIONES ESPECÍFICAS DE CONFIGURACIÓN ====================

function validateLevelForm(form, data) {
  let isValid = true;

  // Validar nombre del nivel
  const nivel = data.get('nivel');
  const nivelInput = form.querySelector('[name="nivel"]');
  if (!nivel || nivel.trim() === '') {
    showError(nivelInput, 'El nombre del nivel es requerido');
    isValid = false;
  } else if (nivel.length > 20) {
    showError(nivelInput, 'El nombre del nivel no debe exceder 20 caracteres');
    isValid = false;
  }

  // Validar puntaje mínimo
  const puntaje = data.get('puntaje_minimo');
  const puntajeInput = form.querySelector('[name="puntaje_minimo"]');
  if (!puntaje || puntaje.trim() === '') {
    showError(puntajeInput, 'El puntaje mínimo es requerido');
    isValid = false;
  } else if (isNaN(puntaje) || parseInt(puntaje) < 0) {
    showError(puntajeInput, 'El puntaje mínimo debe ser un número válido mayor o igual a 0');
    isValid = false;
  }

  return isValid;
}

function validateRuleForm(form, data) {
  let isValid = true;

  // Validar nivel socioeconómico
  const nivelId = data.get('nivel_socioeconomico_id');
  const nivelSelect = form.querySelector('[name="nivel_socioeconomico_id"]');
  if (!nivelId || nivelId === '') {
    showError(nivelSelect, 'Debe seleccionar un nivel socioeconómico');
    isValid = false;
  }

  // Validar edad
  const edad = data.get('edad');
  const edadInput = form.querySelector('[name="edad"]');
  if (!edad || edad.trim() === '') {
    showError(edadInput, 'La edad es requerida');
    isValid = false;
  } else if (isNaN(edad) || parseInt(edad) < 0 || parseInt(edad) > 150) {
    showError(edadInput, 'La edad debe ser un número válido entre 0 y 150');
    isValid = false;
  }

  // Validar periodicidad
  const periodicidad = data.get('periodicidad');
  const periodicidadSelect = form.querySelector('[name="periodicidad"]');
  if (!periodicidad || periodicidad === '') {
    showError(periodicidadSelect, 'Debe seleccionar una periodicidad');
    isValid = false;
  }

  // Validar monto de aportación
  const monto = data.get('monto_aportacion');
  const montoInput = form.querySelector('[name="monto_aportacion"]');
  if (!monto || monto.trim() === '') {
    showError(montoInput, 'El monto de aportación es requerido');
    isValid = false;
  } else if (isNaN(monto) || parseFloat(monto) < 0) {
    showError(montoInput, 'El monto debe ser un número válido mayor o igual a 0');
    isValid = false;
  }

  return isValid;
}

function validateCriteriaForm(form, data) {
  let isValid = true;

  // Validar nombre del criterio
  const nombre = data.get('nombre');
  const nombreInput = form.querySelector('[name="nombre"]');
  if (!nombre || nombre.trim() === '') {
    showError(nombreInput, 'El nombre del criterio es requerido');
    isValid = false;
  } else if (nombre.length > 100) {
    showError(nombreInput, 'El nombre del criterio no debe exceder 100 caracteres');
    isValid = false;
  }

  // Validar tipo de criterio
  const tipoCriterio = data.get('tipo_criterio');
  const tipoSelect = form.querySelector('[name="tipo_criterio"]');
  if (!tipoCriterio || tipoCriterio === '') {
    showError(tipoSelect, 'Debe seleccionar un tipo de criterio');
    isValid = false;
  }

  // Validar puntaje
  const puntaje = data.get('puntaje');
  const puntajeInput = form.querySelector('[name="puntaje"]');
  if (!puntaje || puntaje.trim() === '') {
    showError(puntajeInput, 'El puntaje es requerido');
    isValid = false;
  } else if (isNaN(puntaje) || parseInt(puntaje) < 0) {
    showError(puntajeInput, 'El puntaje debe ser un número válido mayor o igual a 0');
    isValid = false;
  }

  // Validaciones específicas según tipo de criterio
  if (tipoCriterio === 'rango_numerico') {
    const valorMinimo = data.get('valor_minimo');
    const valorMinimoInput = form.querySelector('[name="valor_minimo"]');
    if (!valorMinimo || valorMinimo.trim() === '') {
      showError(valorMinimoInput, 'El valor mínimo es requerido para rangos numéricos');
      isValid = false;
    } else if (isNaN(valorMinimo)) {
      showError(valorMinimoInput, 'El valor mínimo debe ser un número válido');
      isValid = false;
    }

    const valorMaximo = data.get('valor_maximo');
    if (valorMaximo && valorMaximo.trim() !== '') {
      if (isNaN(valorMaximo)) {
        const valorMaximoInput = form.querySelector('[name="valor_maximo"]');
        showError(valorMaximoInput, 'El valor máximo debe ser un número válido');
        isValid = false;
      } else if (parseFloat(valorMaximo) <= parseFloat(valorMinimo)) {
        const valorMaximoInput = form.querySelector('[name="valor_maximo"]');
        showError(valorMaximoInput, 'El valor máximo debe ser mayor al valor mínimo');
        isValid = false;
      }
    }
  } else if (tipoCriterio === 'valor_especifico') {
    const valorTexto = data.get('valor_texto');
    const valorTextoInput = form.querySelector('[name="valor_texto"]');
    if (!valorTexto || valorTexto.trim() === '') {
      showError(valorTextoInput, 'El valor de texto es requerido');
      isValid = false;
    } else if (valorTexto.length > 100) {
      showError(valorTextoInput, 'El valor de texto no debe exceder 100 caracteres');
      isValid = false;
    }
  }

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
          userAvatar.style.backgroundImage = `url(${APP_URL}public/photos/default.jpg)`;
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

window.attachAjaxFormHandlers = attachAjaxFormHandlers;
window.showError = showError;
window.clearError = clearError;