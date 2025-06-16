const SETTING_VALIDATION_SCHEMAS = {
  'levels': {
    'nivel': {
      required: {
        message: 'El campo nivel no puede estar vacío.'
      },
      minLength: {
        value: 1,
        message: 'El campo nivel debe tener al menos 1 carácter.'
      },
      maxLength: {
        value: 20,
        message: 'El campo nivel debe tener como máximo 20 caracteres.'
      },
      pattern: {
        value: "^[a-zA-Z0-9 ]+$",
        message: 'El campo nivel solo puede contener letras, números y espacios.'
      }
    },
    'puntaje_minimo': {
      required: {
        message: 'El campo puntaje mínimo no puede estar vacío.'
      },
      min: {
        value: 0,
        message: 'El campo puntaje mínimo debe ser mayor o igual a 0.'
      },
      max: {
        value: 1000,
        message: 'El campo puntaje mínimo debe tener como máximo 1000.'
      }
    },
  }
};

const LevelValidations = {
  validateCreate: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      SETTING_VALIDATION_SCHEMAS.levels
    );
  },
};

const LevelHandlers = {
  onCreateSuccess: async (form, response) => {

    console.log("Nivel creado exitosamente:", response);
  },

  onError: async (data, aform) => {

    Modal.closeAll();

    await CustomDialog.error(
      "Error",
      data.message || "Ocurrió un error al procesar la solicitud."
    )
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
  if (container.querySelector("#createLevelForm")) {
    console.log("Registrando createLevelForm...");

    FormManager.register("createLevelForm", {
      validate: LevelValidations.validateCreate,
      onSuccess: LevelHandlers.onCreateSuccess,
      onError: LevelHandlers.onError,
    });
  }

  // Registrar formulario de edicion de nivel
  if (container.querySelector("#editLevelForm")) {
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

  // 3. Configurar el observador para futuros cambios en el DOM
  const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      // Si se han añadido nodos (como un modal)
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        mutation.addedNodes.forEach(node => {
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


