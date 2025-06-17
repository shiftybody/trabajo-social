/**
 * Modal para editar criterios existentes
 */

/**
 * Abre el modal para editar un criterio
 * @param {number} criteriaId ID del criterio a editar
 */
async function editCriteria(criteriaId) {
  if (!criteriaId) {
    CustomDialog.error("Error", "ID de criterio no válido");
    return;
  }

  try {
    // Obtener datos del criterio
    const response = await fetch(
      `${APP_URL}api/settings/criteria/${criteriaId}`
    );
    const result = await response.json();

    if (result.status !== "success") {
      CustomDialog.error(
        "Error",
        result.message || "No se pudo cargar el criterio"
      );
      return;
    }

    const criteria = result.data;

    // Preparar datos para el template
    const templateData = {
      subcategoria_id: criteria.subcategoria_id,
      nombre: criteria.nombre || criteria.criterio,
      tipo_criterio: criteria.tipo_criterio,
      valor_minimo: criteria.valor_minimo || "",
      valor_maximo: criteria.valor_maximo || "",
      valor_texto: criteria.valor_texto || "",
      valor_booleano:
        criteria.valor_booleano !== null ? criteria.valor_booleano : "",
      puntaje: criteria.puntaje,
      submitText: "Actualizar Criterio",

      // Selecciones para campos select
      rangoSelected:
        criteria.tipo_criterio === "rango_numerico" ? "selected" : "",
      valorSelected:
        criteria.tipo_criterio === "valor_especifico" ? "selected" : "",
      booleanoSelected: criteria.tipo_criterio === "booleano" ? "selected" : "",
      siSelected: criteria.valor_booleano == 1 ? "selected" : "",
      noSelected: criteria.valor_booleano == 0 ? "selected" : "",
    };

    const criteriaModal = createModal("editCriteria", {
      title: "Editar Criterio",
      size: "large",
      endpoint: `${APP_URL}api/settings/criteria/${criteriaId}`,
      template: "criteriaForm",
      data: templateData,
      onSuccess: function (response) {
        CustomDialog.success("Éxito", "Criterio actualizado correctamente");

        // Recargar tabla si existe
        if (window.configManager && window.configManager.currentTable) {
          window.configManager.currentTable.ajax.reload(null, false);
        }
      },
      onShow: function () {
        // Configurar eventos especiales después de mostrar el modal
        setupCriteriaFormEvents();

        // Mostrar campos correspondientes al tipo actual
        if (criteria.tipo_criterio) {
          window.configManager.toggleCriteriaFields(criteria.tipo_criterio);
        }
      },
    });

    criteriaModal.show();
  } catch (error) {
    console.error("Error loading criteria:", error);
    CustomDialog.error("Error", "Error de conexión al cargar el criterio");
  }
}

// Exponer función globalmente
window.mostrarModalEditarCriterio = editCriteria;
window.editCriteria = editCriteria;
