// Esquemas de validación para usuarios
const USER_VALIDATION_SCHEMAS = {
  create: {
    nombre: {
      required: { message: "El campo nombre no puede estar vacío" },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$",
        message: "solo se permiten letras y espacios, mínimo 2 caracteres",
      },
    },
    apellido_paterno: {
      required: { message: "El campo apellido paterno no puede estar vacío" },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$",
        message: "solo se permiten letras y espacios, mínimo 2 caracteres",
      },
    },
    apellido_materno: {
      required: { message: "El campo apellido materno no puede estar vacío" },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$",
        message: "solo se permiten letras y espacios, mínimo 2 caracteres",
      },
    },
    telefono: {
      required: { message: "El campo teléfono no puede estar vacío" },
      pattern: {
        value: "^[0-9]{10}$",
        message: "El número de teléfono debe tener 10 dígitos",
      },
    },
    correo: {
      required: { message: "El campo correo no puede estar vacío" },
      email: { message: "Ingrese un correo electrónico válido" },
    },
    rol: {
      required: { message: "Selecciona un rol para el usuario" },
    },
    username: {
      required: { message: "El campo nombre de usuario no puede estar vacío" },
      pattern: {
        value: "^[a-zA-Z0-9._@!#$%^&*+\\-]{3,70}$",
        message: "El nombre de usuario debe tener letras, números o símbolos",
      },
    },
    password: {
      required: { message: "El campo contraseña no puede estar vacío" },
      pattern: {
        value: "^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}$",
        message:
          "La contraseña debe tener minimo 8 caracteres, un número, una mayúscula, una minúscula y un símbolo (@#$%)",
      },
    },
    password2: {
      required: {
        message: "El campo confirmar contraseña no puede estar vacío",
      },
      matches: {
        field: "password",
        message: "Las contraseñas no coinciden",
      },
    },
    avatar: {
      fileSize: {
        value: 5 * 1024 * 1024,
        message: "La imagen no puede ser mayor a 5MB",
      },
      fileType: {
        value: ["image/jpeg", "image/jpg", "image/png", "image/gif"],
        message: "Solo se permiten archivos de imagen (JPG, PNG, GIF)",
      },
    },
  },

  edit: {
    nombre: {
      required: { message: "El campo nombre no puede estar vacío" },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$",
        message: "Solo se permiten letras y espacios",
      },
    },
    apellido_paterno: {
      required: { message: "El campo apellido paterno no puede estar vacío" },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}$",
          message: "Solo se permiten letras y espacios",
        },
    },
    apellido_materno: {
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]*$",
        message: "Solo se permiten letras y espacios",
      },
    },
    telefono: {
      pattern: {
        value: "^[0-9]{10}$",
        message: "El número de teléfono debe tener 10 dígitos",
      },
    },
    correo: {
      required: { message: "El campo correo no puede estar vacío" },
      email: { message: "El correo electrónico no es válido" },
    },
    rol: {
      required: { message: "Selecciona un rol para el usuario" },
    },
    username: {
      required: { message: "El campo nombre de usuario no puede estar vacío" },
      pattern: {
        value: "^[a-zA-Z0-9._@!#$%^&*+\\-]{3,70}$",
        message: "Solo se permiten letras, números o símbolos",
      },
    },
    estado: {
      required: { message: "El campo estado no puede estar vacío" },
    },
    password: {
      pattern: {
        value: "^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}$",
        message: "La contraseña debe tener mínimo 8 caracteres, un número, una mayúscula, una minúscula y un símbolo (@#$%)",
      },
    },
    password2: {
      matches: {
        field: "password",
        message: "Las contraseñas no coinciden",
      },
    },
    avatar: {
      fileSize: {
        value: 5 * 1024 * 1024,
        message: "La imagen no puede ser mayor a 5MB",
      },
      fileType: {
        value: ["image/jpeg", "image/jpg", "image/png", "image/gif"],
        message: "Solo se permiten archivos de imagen (JPG, PNG, GIF)",
      },
    },
  },

  resetPassword: {
    password: {
      required: { message: "El campo contraseña no puede estar vacío" },
      pattern: {
        value: "^(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}$",
        message:
          "La contraseña debe tener mínimo 8 caracteres, un número, una mayúscula, una minúscula y un símbolo (@#$%)",
      },
    },
    password2: {
      required: {
        message: "El campo confirmar contraseña no puede estar vacío",
      },
      matches: {
        field: "password",
        message: "Las contraseñas no coinciden",
      },
    },
  }
};

// Funciones de validación para usuarios
const UserValidations = {
  validateCreate: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      USER_VALIDATION_SCHEMAS.create
    );
  },

  validateEdit: async (form) => {
    const formData = new FormData(form);

    // Verificar si se está cambiando la contraseña
    const changePassword =
      document.getElementById("change_password")?.value === "1";
    let schema = { ...USER_VALIDATION_SCHEMAS.edit };

    if (!changePassword) {
      const password = formData.get("password");
      const password2 = formData.get("password2");

      // Si los campos de contraseña están vacíos y no se cambió la contraseña, no validarlos
      if (!password && !password2) {
        delete schema.password;
        delete schema.password2;
      }
    }

    return await FormValidator.validate(formData, schema);
  },

  validateResetPassword: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(
      formData,
      USER_VALIDATION_SCHEMAS.resetPassword
    );
  }
};

// Manejadores para respuestas del servidor
const UserHandlers = {
  onCreateSuccess: async (data, form) => {
    await CustomDialog.success(
      "Usuario Creado",
      data.message || "El usuario se creó correctamente"
    );
    window.location.href = `${APP_URL}users`;
  },

  onEditSuccess: async (data, form) => {
    if (data.redirect) {
      await CustomDialog.success(
        "Usuario Actualizado",
        data.message || "El usuario se actualizó correctamente"
      );
      window.location.href = data.redirect;
    } else {
      await CustomDialog.info(
        "Sin Cambios",
        "No se han enviado cambios al actualizar el usuario"
      );
    }
  },

  onResetPasswordSuccess: async (data, form) => {
    console.log(data);
  },

  onError: async (data, form) => {
    console.log("se ejecuta")
    if (data.errors) {
      for (const [field, message] of Object.entries(data.errors)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
          input.classList.add("error-input");
          const errorMessage = document.createElement("div");
          errorMessage.className = "error-message";
          errorMessage.textContent = message;
          input.parentElement.appendChild(errorMessage);
        }
      }
    }

    CustomDialog.toast("Corrija los errores marcados en el formulario", "error", 2000);
  },
};

// Utilidades para formularios de usuarios
const UserUtils = {
  // Función para cargar roles
  loadRoles: async () => {
    try {
      const response = await fetch(`${APP_URL}api/roles`, {
        method: "GET",
      });

      if (!response.ok) {
        throw new Error(`Error ${response.status}: ${response.statusText}`);
      }

      const responseData = await response.json();
      const select = document.getElementById("rol");
      const roles = responseData.data;

      if (!roles || roles.length === 0) {
        console.warn("No se encontraron roles disponibles");
        return;
      }

      roles.forEach((rol) => {
        const option = document.createElement("option");
        option.value = rol.rol_id;
        option.textContent = rol.rol_nombre;
        select.appendChild(option);
      });
    } catch (error) {
      console.error("Error al cargar los roles:", error);
      CustomDialog.error(
        "Error de Carga",
        "No se pudieron cargar los roles disponibles. Por favor, recargue la página e inténtelo de nuevo."
      );
    }
  },

  // Función para manejar la carga de imagen de perfil
  setupAvatarUpload: () => {
    const fileInput = document.getElementById("file-input");
    if (!fileInput) return;

    fileInput.addEventListener("change", function (e) {
      const file = e.target.files[0];

      if (file) {
        // Validar tamaño del archivo (5MB máximo)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
          CustomDialog.error(
            "Archivo muy grande",
            "La imagen no puede ser mayor a 5MB. Por favor, seleccione una imagen más pequeña."
          );
          this.value = ""; // Limpiar el input
          return;
        }

        // Validar tipo de archivo
        const allowedTypes = [
          "image/jpeg",
          "image/jpg",
          "image/png",
          "image/gif",
        ];
        if (!allowedTypes.includes(file.type)) {
          CustomDialog.error(
            "Formato no válido",
            "Solo se permiten archivos de imagen (JPG, PNG, GIF)."
          );
          this.value = "";
          return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
          const avatar = document.querySelector(".user-avatar");
          if (avatar) {
            avatar.style.backgroundImage = `url(${event.target.result})`;
          }
          CustomDialog.toast("Imagen cargada correctamente", "success", 2000);
        };
        reader.onerror = function () {
          CustomDialog.error(
            "Error de Lectura",
            "No se pudo leer el archivo seleccionado. Por favor, inténtelo de nuevo."
          );
        };
        reader.readAsDataURL(file);
      }
    });
  },

  // Función para limpiar formulario y errores
  resetForm: async (form) => {
    const performReset = await CustomDialog.confirm(
      "Confirmar",
      "¿Estás seguro de que deseas limpiar el formulario?",
      "Sí, limpiar",
      "Cancelar"
    );

    if (!performReset) return;

    // Limpiar todos los campos del formulario
    form.reset();

    // Limpiar estilos de error
    form.querySelectorAll(".error-input").forEach((field) => {
      field.classList.remove("error-input");
    });

    // Limpiar mensajes de error
    form.querySelectorAll(".error-message").forEach((error) => {
      error.remove();
    });

    // Si existe FormManager, también limpiar sus errores
    if (window.FormManager) {
      FormManager.clearErrors(form);
    }

    // Limpiar imagen de avatar si existe
    const avatar = document.querySelector(".user-avatar");
    if (avatar) {
      avatar.style.backgroundImage = "";
    }

    CustomDialog.toast("Formulario limpiado correctamente", "info", 2000);
  },

  setupErrorClearingOnInput: () => {
    const form = document.querySelector("form");
    if (!form) return;

    // Agregar listener a todos los inputs y selects
    const inputs = form.querySelectorAll("input, select, textarea");

    inputs.forEach((input) => {
      input.addEventListener("input", function () {
        // Limpiar error del campo actual si lo tiene
        if (this.classList.contains("error-input")) {
          this.classList.remove("error-input");

          // Remover mensaje de error específico de este campo
          const errorMessage =
            this.parentElement.querySelector(".error-message");
          if (errorMessage) {
            errorMessage.remove();
          }
        }
      });

      // Para los selects, también escuchar el evento 'change'
      if (input.tagName === "SELECT") {
        input.addEventListener("change", function () {
          if (this.classList.contains("error-input")) {
            this.classList.remove("error-input");

            const errorMessage =
              this.parentElement.querySelector(".error-message");
            if (errorMessage) {
              errorMessage.remove();
            }
          }
        });
      }
    });
  },

  // Configurar navegación con confirmación de cambios
  setupNavigationConfirmation: () => {
    const form = document.querySelector("form");
    if (!form) return;

    let formChanged = false;
    let isSubmitting = false;

    form.addEventListener("input", () => {
      formChanged = true;
    });

    form.addEventListener("change", () => {
      formChanged = true;
    });

    form.addEventListener("submit", () => {
      isSubmitting = true;
      formChanged = false;
    });

    // Función global para confirmar navegación
    window.confirmAndNavigate = async (url) => {
      if (!formChanged || isSubmitting) {
        window.location.href = url;
        return;
      }

      const userConfirmed = await CustomDialog.confirm(
        "Cambios sin guardar",
        "Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?",
        "Sí, salir",
        "Cancelar"
      );

      if (userConfirmed) {
        formChanged = false;
        window.location.href = url;
      }
    };

    // Interceptar TODOS los clics en enlaces <a>
    document.addEventListener("click", (e) => {
      const link = e.target.closest("a");

      if (link && link.href) {
        if (link.target === "_blank") return;
        if (link.getAttribute("href").startsWith("#")) return;
        if (link.href.startsWith("mailto:") || link.href.startsWith("tel:"))
          return;

        e.preventDefault();
        window.confirmAndNavigate(link.href);
      }
    });


    // Advertencia al cerrar ventana
    window.addEventListener("beforeunload", (e) => {
      if (formChanged && !isSubmitting) {
        e.preventDefault();
        e.returnValue = "";
        return "";
      }
    });
  },

  // Configurar manejadores de eventos para el formulario
  setupFormEvents: (formId) => {
    const form = document.getElementById(formId);
    if (!form) return;

    // Manejador para botón reset
    const resetButton = form.querySelector('button[type="reset"]');
    if (resetButton) {
      resetButton.addEventListener("click", (e) => {
        e.preventDefault(); // Prevenir el reset por defecto
        UserUtils.resetForm(form);
      });
    }
  },
};

// 1. Mueve toda la lógica de registro a una función reutilizable
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

  // --- Registrar formulario de creación ---
  // Usamos querySelector para buscar solo dentro del contenedor
  if (container.querySelector("#createUserForm")) {
    console.log("Registrando createUserForm...");
    UserUtils.loadRoles();
    UserUtils.setupAvatarUpload();
    UserUtils.setupErrorClearingOnInput();
    UserUtils.setupNavigationConfirmation();
    FormManager.register("createUserForm", {
      validate: UserValidations.validateCreate,
      onSuccess: UserHandlers.onCreateSuccess,
      onError: UserHandlers.onError,
    });
    UserUtils.setupFormEvents("createUserForm");
  }

  // --- Registrar formulario de edición ---
  if (container.querySelector("#editUserForm")) {
    console.log("Registrando editUserForm...");
    FormManager.register("editUserForm", {
      validate: UserValidations.validateEdit,
      onSuccess: UserHandlers.onEditSuccess,
      onError: UserHandlers.onError,
    });
  }

  // --- Registrar formulario de reseteo de contraseña ---
  if (container.querySelector("#resetPasswordForm")) {
    console.log("Registrando resetPasswordForm...");
    FormManager.register("resetPasswordForm", {
      validate: UserValidations.validateResetPassword,
      onSucess: UserHandlers.onResetPasswordSuccess,
      onError: UserHandlers.onError,
    });
  }
}


// 2. Ejecutar al cargar la página inicial
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
