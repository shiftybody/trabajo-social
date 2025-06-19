// public/js/modals/edit-criteria-modal.js

async function editCriteria(criteriaId) {
  try {
    // Solo cargar datos del criterio
    const criteriaResponse = await fetch(
      `${APP_URL}api/settings/criteria/${criteriaId}`
    );
    const criteriaData = await criteriaResponse.json();

    if (criteriaData.status !== "success") {
      throw new Error(criteriaData.message || "Error al cargar criterio");
    }

    const criteriaModal = createModal("editCriteria", {
      title: "Editar Criterio",
      size: "large",
      endpoint: `${APP_URL}api/settings/criteria/${criteriaId}`,
      data: {
        ...criteriaData.data,
      },
      onShow: (modal) => {
        // Configurar el select del tipo de criterio y mostrar campos apropiados
        setTimeout(() => {
          const tipoCriterioSelect = document.getElementById("tipo_criterio");
          const tipoCriterio = criteriaData.data.tipo_criterio;

          if (tipoCriterioSelect && tipoCriterio) {
            // Establecer el valor correcto en el select
            tipoCriterioSelect.value = tipoCriterio;

            // Mostrar los campos apropiados
            toggleCriteriaFields(tipoCriterio);
          }
        }, 100);
      },
    });

    criteriaModal.show();
  } catch (error) {
    console.error("Error al cargar datos del criterio:", error);
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error(
        "Error",
        error.message || "No se pudieron cargar los datos del criterio"
      );
    }
  }
}

window.mostrarModalEditarCriterio = editCriteria;
