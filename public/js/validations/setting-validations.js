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
        message:
          "El campo nivel solo puede contener letras",
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

function registerAvailableForms(container) {
  // Asegurarse de que el contenedor sea un elemento válido antes de usar querySelector
  if (!container || typeof container.querySelector !== 'function') {
    return;
  }

  // Verificar que FormManager esté disponible
  if (typeof FormManager === "undefined" || !FormManager) {
    console.error("FormManager no está disponible");
    return;
  }

  // Registrar formulario de creación de nivel 
  let createForm = container.querySelector("#createLevelForm");
  // También verificar si el container mismo es el formulario
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

  // Registrar formulario de edición de nivel
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
