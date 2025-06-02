/**
 * Modal para crear nuevo rol
 */
function crearRol() {
  const createModal = createModal("createRole", {
    title: "Crear Nuevo Rol",
    size: "medium",
    endpoint: `${APP_URL}/api/roles`,
    onShow: (modal) => {
      modal.showLoading("Cargando roles base disponibles...");

      fetch({
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        credentials: "same-origin",
        url: `${APP_URL}/api/roles`,
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

  createModal.show();
}

window.mostrarModalCrearRol = crearRol;