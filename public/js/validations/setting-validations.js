const SETTING_VALIDATION_SCHEMAS = {
  levels: {
    nivel: {
      required: {
        message: "El campo nivel no puede estar vacío",
      },
      minLength: {
        value: 1,
        message: "El campo nivel debe tener al menos 1 carácter",
      },
      maxLength: {
        value: 50,
        message: "El campo nivel debe tener como máximo 50 caracteres",
      },
      pattern: {
        value: "^[a-zA-Z]+$",
        message: "El campo nivel solo puede contener letras",
      },
    },
    puntaje_minimo: {
      required: {
        message: "El campo puntaje mínimo no puede estar vacío",
      },
      pattern: {
        value: "^[0-9]+$",
        message: "El campo puntaje mínimo debe ser un número entero",
      },
      min: {
        value: 0,
        message: "El campo puntaje mínimo debe ser mayor o igual a 0",
      },
      max: {
        value: 1000,
        message: "El campo puntaje mínimo no puede exceder 1000",
      },
    },
  },
  rules: {
    nivel_socioeconomico_id: {
      required: {
        message: "El nivel socioeconómico no puede estar vacío",
      },
      pattern: {
        value: "^[0-9]+$",
        message: "Debe seleccionar un nivel válido",
      },
    },
    edad: {
      required: {
        message: "El campo edad no puede estar vacío",
      },
      pattern: {
        value: "^[0-9]+$",
        message: "La edad debe ser un número entero",
      },
      min: {
        value: 0,
        message: "La edad debe ser mayor o igual a 0",
      },
      max: {
        value: 150,
        message: "La edad no puede ser mayor a 150",
      },
    },
    periodicidad: {
      required: {
        message: "Debe seleccionar una periodicidad",
      },
      custom: async (value, formData) => {
        const validPeriodicities = ["mensual", "semestral", "anual"];
        if (!validPeriodicities.includes(value)) {
          return "Debe seleccionar una periodicidad válida";
        }
        return null;
      },
    },
    monto_aportacion: {
      required: {
        message: "El campo monto de aportación no puede estar vacío",
      },
      pattern: {
        value: "^[0-9]+(\\.[0-9]{1,2})?$",
        message: "El monto debe ser un número válido con máximo 2 decimales",
      },
      min: {
        value: 0.01,
        message: "El monto debe ser mayor a 0",
      },
      max: {
        value: 999999.99,
        message: "El monto no puede exceder $999,999.99",
      },
    },
  },
};

const LevelValidations = {
  validateCreate: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      SETTING_VALIDATION_SCHEMAS.levels
    );
  },

  validateEdit: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      SETTING_VALIDATION_SCHEMAS.levels
    );
  },
};

const LevelHandlers = {
  onCreateSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== "undefined") {
      BaseModal.closeAll();
    }

    await CustomDialog.success(
      "Nivel Creado",
      data.message || "El nivel socioeconómico se creó correctamente"
    );

    // Recargar datos si existe la función
    if (typeof window.reloadLevelsTable === "function") {
      await window.reloadLevelsTable();
    } else {
      window.location.reload();
    }
  },

  onEditSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== "undefined") {
      BaseModal.closeAll();
    }

    await CustomDialog.success(
      "Nivel Actualizado",
      data.message || "El nivel socioeconómico se actualizó correctamente"
    );

    // Recargar datos si existe la función
    if (typeof window.reloadLevelsTable === "function") {
      await window.reloadLevelsTable();
    } else {
      window.location.reload();
    }
  },

  onError: async (data, form) => {
    if (data.errors) {
      // Mostrar errores específicos de campos
      for (const [field, message] of Object.entries(data.errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add("error-input");

          // Crear mensaje de error
          const errorElement = document.createElement("p");
          errorElement.className = "error-message";
          errorElement.textContent = message;

          // Insertar el mensaje después del input
          input.parentElement.appendChild(errorElement);
        }
      }

      // Si hay errores específicos de campos, mostrar toast genérico
      CustomDialog.toast(
        "Corrija los errores marcados en el formulario",
        "error",
        3000
      );
    } else {
      // Si no hay errores específicos, mostrar el mensaje general
      await CustomDialog.error(
        "Error",
        data.message || "Ocurrió un error al procesar la solicitud"
      );
    }
  },
};

const RuleValidations = {
  validateCreate: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      SETTING_VALIDATION_SCHEMAS.rules
    );
  },

  validateEdit: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      SETTING_VALIDATION_SCHEMAS.rules
    );
  },
};

const RuleHandlers = {
  onCreateSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== "undefined") {
      BaseModal.closeAll();
    }

    await CustomDialog.success(
      "Regla Creada",
      data.message || "La regla de aportación se creó correctamente"
    );

    // Recargar datos si existe la función
    if (typeof window.reloadRulesTable === "function") {
      await window.reloadRulesTable();
    } else if (
      typeof configManager !== "undefined" &&
      configManager.currentTable
    ) {
      configManager.currentTable.ajax.reload();
    } else {
      window.location.reload();
    }
  },

  onEditSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== "undefined") {
      BaseModal.closeAll();
    }

    await CustomDialog.success(
      "Regla Actualizada",
      data.message || "La regla de aportación se actualizó correctamente"
    );

    // Recargar datos si existe la función
    if (typeof window.reloadRulesTable === "function") {
      await window.reloadRulesTable();
    } else if (
      typeof configManager !== "undefined" &&
      configManager.currentTable
    ) {
      configManager.currentTable.ajax.reload();
    } else {
      window.location.reload();
    }
  },

  onError: async (data, form) => {
    if (data.errors) {
      // Mostrar errores específicos de campos
      for (const [field, message] of Object.entries(data.errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add("error-input");

          // Crear mensaje de error
          const errorElement = document.createElement("p");
          errorElement.className = "error-message";
          errorElement.textContent = message;

          // Insertar el mensaje después del input
          input.parentElement.appendChild(errorElement);
        }
      }

      // Si hay errores específicos de campos, mostrar toast genérico
      CustomDialog.toast(
        "Corrija los errores marcados en el formulario",
        "error",
        3000
      );
    } else {
      // Si no hay errores específicos, mostrar el mensaje general
      await CustomDialog.error(
        "Error",
        data.message || "Ocurrió un error al procesar la solicitud"
      );
    }
  },
};

// public/js/validations/setting-validations.js (modificar la función registerAvailableForms)

function registerAvailableForms(container) {
  // Asegurarse de que el contenedor sea un elemento válido antes de usar querySelector
  if (!container || typeof container.querySelector !== "function") {
    return;
  }

  // Verificar que FormManager esté disponible
  if (typeof FormManager === "undefined" || !FormManager) {
    console.error("FormManager no está disponible");
    return;
  }

  // Registrar formularios de niveles (código existente)
  let createForm = container.querySelector("#createLevelForm");
  if (!createForm && container.id === "createLevelForm") {
    createForm = container;
  }

  if (createForm) {
    console.log("Registrando createLevelForm...");
    FormManager.register("createLevelForm", {
      validate: LevelValidations.validateCreate,
      onSuccess: LevelHandlers.onCreateSuccess,
      onError: LevelHandlers.onError,
    });
  }

  let editForm = container.querySelector("#editLevelForm");
  if (!editForm && container.id === "editLevelForm") {
    editForm = container;
  }

  if (editForm) {
    console.log("Registrando editLevelForm...");
    FormManager.register("editLevelForm", {
      validate: LevelValidations.validateEdit,
      onSuccess: LevelHandlers.onEditSuccess,
      onError: LevelHandlers.onError,
    });
  }

  // NUEVOS: Registrar formularios de reglas de aportación
  let createRuleForm = container.querySelector("#createRuleForm");
  if (!createRuleForm && container.id === "createRuleForm") {
    createRuleForm = container;
  }

  if (createRuleForm) {
    console.log("Registrando createRuleForm...");
    FormManager.register("createRuleForm", {
      validate: RuleValidations.validateCreate,
      onSuccess: RuleHandlers.onCreateSuccess,
      onError: RuleHandlers.onError,
    });
  }

  let editRuleForm = container.querySelector("#editRuleForm");
  if (!editRuleForm && container.id === "editRuleForm") {
    editRuleForm = container;
  }

  if (editRuleForm) {
    console.log("Registrando editRuleForm...");
    FormManager.register("editRuleForm", {
      validate: RuleValidations.validateEdit,
      onSuccess: RuleHandlers.onEditSuccess,
      onError: RuleHandlers.onError,
    });
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // Registra los formularios que ya existen al cargar la página
  registerAvailableForms(document.body);

  // Configurar el observador para futuros cambios en el DOM
  const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      // Si se han añadido nodos (como un modal)
      if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
        mutation.addedNodes.forEach((node) => {
          // Nos aseguramos de que el nodo sea un elemento HTML (nodeType 1)
          if (node.nodeType === 1) {
            registerAvailableForms(node);
          }
        });
      }
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });
});
