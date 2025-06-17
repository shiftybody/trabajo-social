class FormManager {
  constructor() {
    this.validators = new Map();
    this.init();
  }

  init() {
    // Escuchar todos los formularios con clase .form-ajax
    document.addEventListener("submit", (e) => {
      if (e.target.classList.contains("form-ajax")) {
        e.preventDefault();
        this.handleFormSubmit(e.target);
      }
    });
  }

  // Registrar un validador para un formulario específico
  register(formId, config) {
    this.validators.set(formId, {
      validate: config.validate || (() => ({ isValid: true, errors: {} })),
      onSuccess: config.onSuccess || (() => {}),
      onError: config.onError || this.defaultErrorHandler,
      beforeSubmit: config.beforeSubmit || (() => {}),
      afterSubmit: config.afterSubmit || (() => {}),
    });
  }

async handleFormSubmit(form) {
  const formId = form.id;
  const config = this.validators.get(formId);

  if (!config) {
    console.warn(`No validator registered for form: ${formId}`);
    return;
  }

  try {
    // Ejecutar hook antes del envío
    config.beforeSubmit(form);

    // Validar el formulario
    const validationResult = await config.validate(form);

    if (!validationResult.isValid) {
      this.displayErrors(form, validationResult.errors);
      return;
    }

    // Limpiar errores previos
    this.clearErrors(form);

    // Preparar datos del formulario
    const formData = new FormData(form);

    // Realizar petición AJAX
    const response = await fetch(form.action, {
      method: form.method.toUpperCase(),
      body: formData,
      headers: {
        Accept: "application/json",
      },
    });

    const data = await response.json();

    if (response.ok && data.status === "success") {
      await config.onSuccess(data, form);
    } else {
      await config.onError(data, form);
    }
  } catch (error) {
    console.error("Error en FormManager:", error);
    await config.onError(
      {
        status: "error",
        message: "Error de conexión. Inténtelo de nuevo.",
      },
      form
    );
  } finally {
    config.afterSubmit(form);
  }
}

  displayErrors(form, errors) {
    // Limpiar errores previos
    this.clearErrors(form);

    Object.keys(errors).forEach((fieldName) => {
      const field = form.querySelector(`[name="${fieldName}"]`);
      if (field) {
        field.classList.add("error-input");

        const errorElement = document.createElement("p");
        errorElement.className = "error-message";
        errorElement.textContent = errors[fieldName];

        field.parentElement.appendChild(errorElement);
      }
    });

    CustomDialog.toast(
      "Corrija los errores marcados en el formulario",
      "error",
      2000
    );
  }

  clearErrors(form) {
    // Remover clases de error
    form.querySelectorAll(".error-input").forEach((field) => {
      field.classList.remove("error-input");
    });

    // Remover mensajes de error
    form.querySelectorAll(".error-message").forEach((error) => {
      error.remove();
    });
  }

  defaultErrorHandler = async (data, form) => {
    // Manejar tanto errores específicos como mensajes generales
    let message = "Ocurrió un error inesperado";

    if (data.message) {
      message = data.message;
    } else if (data.errors && typeof data.errors === "object") {
      // Si hay errores de campos pero también necesitamos un mensaje general
      const firstError = Object.values(data.errors)[0];
      if (firstError) {
        message = firstError;
      }
    }

    if (window.CustomDialog) {
      await CustomDialog.error("Error", message);
    } else {
      alert(message);
    }
  };
}

// Crear instancia global
window.FormManager = new FormManager();
