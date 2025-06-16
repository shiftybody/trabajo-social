
function createLevel() {
  const levelModal = createModal("createLevel", {
    title: "Crear Nuevo Nivel",
    size: "medium",
    endpoint: `${APP_URL}api/settings/levels`,
  });
  levelModal.show();
}

window.mostrarModalCrearNivel = createLevel;