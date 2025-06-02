/**
 * Modal para crear nuevo rol
 */
function crearRol() {
  const roleModal = createModal("createRole", {
    title: "Crear Nuevo Rol",
    size: "medium",
    endpoint: `${APP_URL}/api/roles`,
    onShow: (modal) => {
      modal.showLoading("Cargando roles base disponibles...");

      console.log(`Cargando roles base desde: ${APP_URL}/api/roles`);

      fetch( `${APP_URL}/api/roles`, {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        credentials: "same-origin",
      })
        .then((response) => response.json())
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            const templateData = TEMPLATE_HELPERS.processCreateRole(data.data);
            modal.updateContent(templateData);
          } else {
            modal.showError("No se pudieron cargar los roles disponibles");
          }
        })
        .catch((error) => {
          console.error("Error cargando roles:", error);
          modal.showError("Error al conectar con el servidor");
        });
    },
  });

  roleModal.show();
}

window.mostrarModalCrearRol = crearRol;