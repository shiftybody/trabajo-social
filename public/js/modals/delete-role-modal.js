/**
 * Modal de confirmación para eliminar rol
 */
async function eliminarRol(rolId, nombreRol, usuariosCount) {

  if (usuariosCount > 0) {
    CustomDialog.error(
      "No se puede eliminar",
      `El rol "${nombreRol}" tiene ${usuariosCount} usuario(s) asignado(s). Primero debes reasignar estos usuarios a otro rol.`
    );
    return;
  }

  const confirmacion = await CustomDialog.confirm(
    "Confirmar Eliminación",
    `¿Está seguro de que desea eliminar el rol "${nombreRol}"?`,
    "Eliminar",
    "Cancelar"
  );

  if (confirmacion) {
    try {
      const response = await fetch(`${APP_URL}api/roles/${rolId}`, {
        method: "DELETE",
        headers: {
          Accept: "application/json",
        },
      });

      const data = await response.json();

      if (response.ok && data.status === "success") {
        await CustomDialog.success(
          "Operación exitosa",
          data.message || "Rol eliminado correctamente"
        );

          await loadData();
      } else {
        if (typeof hideTableLoading === "function") {
          hideTableLoading();
        }
        CustomDialog.error(
          "Error",
          data.message || "No se pudo eliminar el rol."
        );
      }
    } catch (error) {
      console.error("Error en la petición fetch:", error);

      if (typeof hideTableLoading === "function") {
        hideTableLoading();
      }

      CustomDialog.error(
        "Error de Red",
        "Ocurrió un problema al intentar conectar con el servidor."
      );
    }
  }
}

window.mostrarModalEliminarRol = eliminarRol;
