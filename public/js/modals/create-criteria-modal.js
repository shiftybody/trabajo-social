async function createCriteria(subcategoryId) {
  try {
    const criteriaModal = createModal("createCriteria", {
      title: "Crear Nuevo Criterio",
      size: "medium",
      endpoint: `${APP_URL}api/settings/criteria`,
      data: {
        selectedSubcategoryId: subcategoryId || "",
      },
      onShow: (modal) => {
        // Limpiar estado anterior y inicializar para nuevo criterio
        clearCriteriaFieldsState();

        // Configurar event listeners para inputs en tiempo real
        setTimeout(() => {
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
    console.error("Error al crear modal de criterio:", error);
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error("Error", "No se pudo abrir el modal de creación");
    }
  }
}

/**
 * Configura guardado en tiempo real mientras el usuario escribe
 */
function setupRealtimeStateSaving() {
  // Event listeners para campos numéricos
  const valorMinimo = document.getElementById("valor_minimo");
  const valorMaximo = document.getElementById("valor_maximo");
  const valorTexto = document.getElementById("valor_texto");

  if (valorMinimo) {
    valorMinimo.addEventListener("input", () => {
      if (window.criteriaFieldsState) {
        window.criteriaFieldsState.rango_numerico.valor_minimo =
          valorMinimo.value;
      }
    });
  }

  if (valorMaximo) {
    valorMaximo.addEventListener("input", () => {
      if (window.criteriaFieldsState) {
        window.criteriaFieldsState.rango_numerico.valor_maximo =
          valorMaximo.value;
      }
    });
  }

  if (valorTexto) {
    valorTexto.addEventListener("input", () => {
      if (window.criteriaFieldsState) {
        window.criteriaFieldsState.valor_especifico.valor_texto =
          valorTexto.value;
      }
    });
  }
}

window.mostrarModalCrearCriterio = createCriteria;
window.setupRealtimeStateSaving = setupRealtimeStateSaving;
