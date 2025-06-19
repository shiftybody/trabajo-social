// public/js/modals/create-criteria-modal.js

async function createCriteria(subcategoryId) {
  try {
    const criteriaModal = createModal("createCriteria", {
      title: "Crear Nuevo Criterio",
      size: "medium",
      endpoint: `${APP_URL}api/settings/criteria`,
      data: {
        selectedSubcategoryId: subcategoryId || "",
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

window.mostrarModalCrearCriterio = createCriteria;
