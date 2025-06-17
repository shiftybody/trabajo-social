/**
 * Modal para crear nuevos criterios
 * Maneja la creación de criterios según el tipo de subcategoría actual
 */

/**
 * Abre el modal para crear un nuevo criterio
 * @param {number} subcategoryId ID de la subcategoría
 */
function createCriteria(subcategoryId = null) {
  // Si no se pasa subcategoryId, obtenerlo de la sección actual
  if (!subcategoryId) {
    subcategoryId = getSubcategoryIdFromSection(
      window.configManager.currentSection
    );
  }

  if (!subcategoryId) {
    CustomDialog.error(
      "Error de Configuración",
      "No se pudo determinar la subcategoría para esta sección."
    );
    return;
  }

  const criteriaModal = createModal("createCriteria", {
    title: "Crear Nuevo Criterio",
    size: "large",
    endpoint: `${APP_URL}api/settings/criteria`,
    template: "criteriaForm",
    data: {
      subcategoria_id: subcategoryId,
      nombre: "",
      tipo_criterio: "",
      valor_minimo: "",
      valor_maximo: "",
      valor_texto: "",
      valor_booleano: "",
      puntaje: "",
      submitText: "Crear Criterio",
      // Selecciones para campos select
      rangoSelected: "",
      valorSelected: "",
      booleanoSelected: "",
      siSelected: "",
      noSelected: "",
    },
    onSuccess: function (response) {
      CustomDialog.success("Éxito", "Criterio creado correctamente");

      // Recargar tabla si existe
      if (window.configManager && window.configManager.currentTable) {
        window.configManager.currentTable.ajax.reload(null, false);
      }
    },
    onShow: function () {
      // Configurar eventos especiales después de mostrar el modal
      setupCriteriaFormEvents();
    },
  });

  criteriaModal.show();
}

/**
 * Obtiene el ID de subcategoría basado en la sección actual
 * @param {string} section Nombre de la sección
 * @returns {number|null} ID de la subcategoría o null si no se encuentra
 */
function getSubcategoryIdFromSection(section) {
  const sectionToSubcategoryMap = {
    protocolo: 1,
    "tiempo-traslado": 2,
    "gasto-traslado": 3,
    integrantes: 4,
    hijos: 5,
    "tipo-familia": 6,
    "grupo-etnico": 7,
    "parientes-enfermos": 8,
    "tipo-vivienda": 9,
    tenencia: 10,
    zona: 11,
    materiales: 12,
    techo: 13,
    piso: 14,
    servicios: 15,
    luz: 16,
    dependientes: 17,
    aporte: 18,
  };

  return sectionToSubcategoryMap[section] || null;
}

/**
 * Configura eventos especiales para el formulario de criterios
 */
function setupCriteriaFormEvents() {
  const form = document.getElementById("criteriaForm");
  if (!form) return;

  // Validación en tiempo real para rangos numéricos
  const valorMinimo = document.getElementById("valor_minimo");
  const valorMaximo = document.getElementById("valor_maximo");

  if (valorMinimo && valorMaximo) {
    valorMinimo.addEventListener("input", validateNumericRange);
    valorMaximo.addEventListener("input", validateNumericRange);
  }

  // Validación del formulario antes del envío
  form.addEventListener("submit", function (e) {
    if (!validateCriteriaForm()) {
      e.preventDefault();
      return false;
    }
  });
}

/**
 * Valida el rango numérico
 */
function validateNumericRange() {
  const valorMinimo = document.getElementById("valor_minimo");
  const valorMaximo = document.getElementById("valor_maximo");

  if (!valorMinimo || !valorMaximo) return;

  const min = parseFloat(valorMinimo.value);
  const max = parseFloat(valorMaximo.value);

  // Limpiar errores previos
  valorMinimo.classList.remove("error");
  valorMaximo.classList.remove("error");

  // Validar si ambos tienen valor y min >= max
  if (!isNaN(min) && !isNaN(max) && min >= max) {
    valorMinimo.classList.add("error");
    valorMaximo.classList.add("error");

    // Mostrar mensaje de error
    let errorMsg = valorMaximo.parentNode.querySelector(".error-message");
    if (!errorMsg) {
      errorMsg = document.createElement("small");
      errorMsg.className = "error-message";
      valorMaximo.parentNode.appendChild(errorMsg);
    }
    errorMsg.textContent = "El valor máximo debe ser mayor que el mínimo";
  } else {
    // Eliminar mensaje de error
    const errorMsg = valorMaximo.parentNode.querySelector(".error-message");
    if (errorMsg) {
      errorMsg.remove();
    }
  }
}

/**
 * Valida todo el formulario de criterios
 * @returns {boolean} True si es válido
 */
function validateCriteriaForm() {
  const form = document.getElementById("criteriaForm");
  if (!form) return false;

  const tipoCriterio = document.getElementById("tipo_criterio").value;
  const nombre = document.getElementById("nombre").value.trim();
  const puntaje = document.getElementById("puntaje").value;

  // Validaciones básicas
  if (!nombre) {
    CustomDialog.error("Validación", "El nombre del criterio es requerido");
    return false;
  }

  if (!tipoCriterio) {
    CustomDialog.error("Validación", "Debe seleccionar un tipo de criterio");
    return false;
  }

  if (!puntaje || isNaN(puntaje) || puntaje < 0) {
    CustomDialog.error(
      "Validación",
      "La puntuación debe ser un número válido mayor o igual a 0"
    );
    return false;
  }

  // Validaciones específicas por tipo
  switch (tipoCriterio) {
    case "rango_numerico":
      const valorMinimo = document.getElementById("valor_minimo").value;
      const valorMaximo = document.getElementById("valor_maximo").value;

      if (!valorMinimo || isNaN(valorMinimo)) {
        CustomDialog.error(
          "Validación",
          "El valor mínimo es requerido para rangos numéricos"
        );
        return false;
      }

      if (
        valorMaximo &&
        !isNaN(valorMaximo) &&
        parseFloat(valorMinimo) >= parseFloat(valorMaximo)
      ) {
        CustomDialog.error(
          "Validación",
          "El valor mínimo debe ser menor que el máximo"
        );
        return false;
      }
      break;

    case "valor_especifico":
      const valorTexto = document.getElementById("valor_texto").value.trim();
      if (!valorTexto) {
        CustomDialog.error("Validación", "El valor específico es requerido");
        return false;
      }
      break;

    case "booleano":
      const valorBooleano = document.getElementById("valor_booleano").value;
      if (valorBooleano === "") {
        CustomDialog.error("Validación", "Debe seleccionar un valor booleano");
        return false;
      }
      break;
  }

  return true;
}

// Exponer función globalmente
window.mostrarModalCrearCriterio = createCriteria;
window.createCriteria = createCriteria;
