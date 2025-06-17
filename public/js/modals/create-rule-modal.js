// public/js/modals/create-rule-modal.js

async function createRule() {
  try {
    // Obtener los niveles socioeconómicos disponibles
    const response = await fetch(`${APP_URL}api/settings/levels`);
    const data = await response.json();

    let nivelOptions;
    if (data.status === "success" && data.data) {
      nivelOptions += data.data
        .filter((level) => level.estado == 1) // Solo niveles activos
        .map((level) => `<option value="${level.id}">${level.nivel}</option>`)
        .join("");
    }

    const ruleModal = createModal("createRule", {
      title: "Crear Nueva Regla de Aportación",
      size: "medium",
      endpoint: `${APP_URL}api/settings/rules`,
      data: {
        nivelOptions: nivelOptions,
        edad: "",
        monto_aportacion: "",
      },
    });

    ruleModal.show();
  } catch (error) {
    console.error("Error al cargar niveles:", error);
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error(
        "Error",
        "No se pudieron cargar los niveles socioeconómicos"
      );
    }
  }
}

window.mostrarModalCrearRegla = createRule;
