/**
 * Modal para editar rol - Solo nombre (sin permisos)
 */
function editarNivel(nivelId) {
  const editModal = createModal("editLevel", {
    title: "Editar Nivel Socioeconómico",
    size: "medium", // Reducido de 'large'
    endpoint: `${APP_URL}api/settings/levels/${nivelId}`,
    onShow: async (modal) => {
      modal.showLoading("Cargando información del nivel...");

      try {
        const response = await fetch(`${APP_URL}api/settings/levels/${nivelId}`);
        const nivelData = await response.json();

        if (nivelData.status === "success") {
          const templateData = TEMPLATE_HELPERS.processEditLevelData(
            nivelData.data
          );
          modal.updateContent(templateData);
        } else {
          modal.showError("No se pudieron cargar los datos del nivel");
        }
      } catch (error) {
        console.error("Error cargando datos del nivel:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
  });

  editModal.show();
}

// Exportar función globalmente
window.mostrarModalEditarNivel = editarNivel;
