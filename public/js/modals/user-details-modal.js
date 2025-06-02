/**
 * Función para mostrar detalles de usuario usando el nuevo sistema
 */
function verDetalles(userId) {

  cerrarTodosLosMenus();

  // Crear modal de detalles
  const detailsModal = createModal("userDetails", {
    title: "Detalles del Usuario",
    size: "large",
    closable: true,
    onShow: async (modal) => {
      modal.showLoading("Cargando información del usuario...");

      try {
        const response = await fetch(`${APP_URL}/api/users/${userId}`);
        const userData = await response.json();

        if (userData.status === "success") {
          const templateData = TEMPLATE_HELPERS.processUserDetailsData(
            userData.data
          );
          modal.updateContent(templateData);
        } else {
          modal.showError("No se pudo cargar la información del usuario");
        }
      } catch (error) {
        console.error("Error al cargar detalles:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
  });

  detailsModal.show();
}

window.mostrarModalVerDetalles = verDetalles;