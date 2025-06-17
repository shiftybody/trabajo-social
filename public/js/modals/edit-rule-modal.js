// public/js/modals/edit-rule-modal.js

async function editRule(ruleId) {
  try {
    // Obtener datos de la regla y niveles en paralelo
    const [ruleResponse, levelsResponse] = await Promise.all([
      fetch(`${APP_URL}api/settings/rules/${ruleId}`),
      fetch(`${APP_URL}api/settings/levels`),
    ]);

    const ruleData = await ruleResponse.json();
    const levelsData = await levelsResponse.json();

    if (ruleData.status !== "success") {
      throw new Error(ruleData.message || "Error al cargar regla");
    }

    if (levelsData.status !== "success") {
      throw new Error(levelsData.message || "Error al cargar niveles");
    }

    const rule = ruleData.data;

    // Generar opciones de niveles con el actual seleccionado
    let nivelOptions;
    if (levelsData.data) {
      nivelOptions += levelsData.data
        .filter((level) => level.estado == 1)
        .map((level) => {
          const selected =
            level.id == rule.nivel_socioeconomico_id ? "selected" : "";
          return `<option value="${level.id}" ${selected}>${level.nivel}</option>`;
        })
        .join("");
    }

    const editModal = createModal("editRule", {
      title: "Editar Regla de Aportación",
      size: "medium",
      endpoint: `${APP_URL}api/settings/rules/${ruleId}`,
      data: {
        nivelOptions: nivelOptions,
        edad: rule.edad || "",
        monto_aportacion: rule.monto_aportacion || "",
        mensualSelected: rule.periodicidad === "mensual" ? "selected" : "",
        semestralSelected: rule.periodicidad === "semestral" ? "selected" : "",
        anualSelected: rule.periodicidad === "anual" ? "selected" : "",
      },
    });

    editModal.show();
  } catch (error) {
    console.error("Error al cargar regla:", error);
    if (typeof CustomDialog !== "undefined") {
      CustomDialog.error(
        "Error",
        error.message || "No se pudo cargar la regla"
      );
    }
  }
}

window.mostrarModalEditarRegla = editRule;
