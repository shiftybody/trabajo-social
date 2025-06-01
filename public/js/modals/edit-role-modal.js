/**
 * Modal para editar rol - Solo nombre (sin permisos)
 */
function editarRol(rolId) {
  const editModal = createModal("editRoleSimplified", {
    title: "Editar Rol",
    size: "medium", // Reducido de 'large'
    endpoint: `${APP_URL}/api/roles/${rolId}`,
    onShow: async (modal) => {
      modal.showLoading("Cargando información del rol...");

      try {
        const response = await fetch(`${APP_URL}/api/roles/${rolId}`);
        const roleData = await response.json();

        if (roleData.status === "success") {
          const templateData = TEMPLATE_HELPERS.processEditRoleSimplifiedData(
            roleData.data
          );
          modal.updateContent(templateData);
        } else {
          modal.showError("No se pudieron cargar los datos del rol");
        }
      } catch (error) {
        console.error("Error cargando datos del rol:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
  });

  editModal.show();
}

// Exportar función globalmente
window.editarRol = editarRol;
