/**
 * Función para cambiar estado de usuario usando el nuevo sistema
 */
function cambiarEstado(usuario_id) {
  cerrarTodosLosMenus();

  // Crear el modal usando BaseModal
  const statusModal = createModal("changeStatus", {
    title: "Cambiar Estado de Usuario",
    size: "medium",
    endpoint: `${APP_URL}/api/users/${usuario_id}/status`,
    onShow: async (modal) => {
      
      modal.showLoading("Cargando información del usuario...");

      try {
        // Cargar datos del usuario
        const response = await fetch(`${APP_URL}/api/users/${usuario_id}`);
        const userData = await response.json();

        if (userData.status === "success") {
          // Procesar datos para el template
          const templateData = TEMPLATE_HELPERS.processChangeStatusData(
            userData.data
          );
          modal.updateContent(templateData);
        } else {
          modal.showError("No se pudieron cargar los datos del usuario");
        }
      } catch (error) {
        console.error("Error:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
  });

  statusModal.show();
}
