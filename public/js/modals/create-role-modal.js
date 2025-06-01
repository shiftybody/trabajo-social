/**
 * Modal para crear nuevo rol - Versión simplificada
 */
function crearRol() {
  const createModal = createModal("createRole", {
    title: "Crear Nuevo Rol",
    size: "medium", // Cambiar de 'large' a 'medium' por simplicidad
    endpoint: `${APP_URL}/api/roles`,
    onShow: async (modal) => {
      modal.showLoading("Cargando roles base disponibles...");

      try {
        // Cargar solo los roles existentes para usar como base
        const response = await fetch(`${APP_URL}/api/roles`);
        const rolesData = await response.json();

        if (rolesData.status === "success") {
          const templateData = TEMPLATE_HELPERS.processCreateRole(
            rolesData.data
          );
          modal.updateContent(templateData);
        } else {
          modal.showError("No se pudieron cargar los roles disponibles");
        }
      } catch (error) {
        console.error("Error cargando roles:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
  });

  createModal.show();
}
