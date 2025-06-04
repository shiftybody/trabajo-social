/**
 * Función para mostrar modal de reset password usando el nuevo sistema
 */
function resetearPassword(userId) {
  cerrarTodosLosMenus();

  // Crear el modal usando el sistema BaseModal
  const resetModal = createModal("resetPassword", {
    title: "Resetear Contraseña",
    size: "medium",
    endpoint: `${APP_URL}api/users/${userId}/reset-password`,
    data: {
      userName: "Cargando...", // Se actualizará con datos reales
    },
    onShow: async (modal) => {
      // Mostrar loading mientras cargamos datos del usuario
      modal.showLoading("Cargando información del usuario...");

      try {
        // Cargar datos del usuario
        const response = await fetch(`${APP_URL}api/users/${userId}`);
        const userData = await response.json();

        if (userData.status === "success") {
          // Actualizar el modal con los datos del usuario
          modal.updateContent({
            userName: `${userData.data.usuario_nombre} ${userData.data.usuario_apellido_paterno}`,
          });
        } else {
          modal.showError("No se pudieron cargar los datos del usuario");
        }
      } catch (error) {
        console.error("Error al cargar usuario:", error);
        modal.showError("Error al conectar con el servidor");
      }
    },
    onHide: () => {
      console.log("Modal de reset cerrado");
    },
  });

  resetModal.show();
}

window.mostrarModalResetearPassword = resetearPassword;