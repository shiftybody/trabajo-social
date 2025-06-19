async function editCriteria(criteriaId) {
  try {
    const criteriaResponse = await fetch(
      `${APP_URL}api/settings/criteria/${criteriaId}`
    );
    const criteriaData = await criteriaResponse.json();

    if (criteriaData.status !== "success") {
      throw new Error(criteriaData.message || "Error al cargar criterio");
    }

    const criteriaModal = createModal("editCriteria", {
      title: "Editar Criterio",
      size: "medium",
      endpoint: `${APP_URL}api/settings/criteria/${criteriaId}`,
      data: {
        ...criteriaData.data,
      },
      onShow: (modal) => {
        setTimeout(() => {
          // Inicializar estado con valores existentes
          initializeCriteriaFieldsState(criteriaData.data);

          // Configurar select y mostrar campos
          const tipoCriterioSelect = document.getElementById("tipo_criterio");
          const tipoCriterio = criteriaData.data.tipo_criterio;

          if (tipoCriterioSelect && tipoCriterio) {
            tipoCriterioSelect.value = tipoCriterio;
            toggleCriteriaFields(tipoCriterio);
          }

          // Configurar guardado en tiempo real
          setupRealtimeStateSaving();
        }, 100);
      },
      onHide: () => {
        // Limpiar estado al cerrar modal
        clearCriteriaFieldsState();
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
