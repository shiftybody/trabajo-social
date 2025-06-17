/** Modal de confirmacion para eliminar usuario */
async function eliminarUsuario(usuarioId, nombreUsuario) {
  const confirmacion = await CustomDialog.confirm(
    "Confirmar Eliminación",
    `¿Está seguro de que desea eliminar el usuario "${nombreUsuario}"?`,
    "Eliminar",
    "Cancelar"
  );

  if (confirmacion) {
    try {
      const response = await fetch(`${APP_URL}api/users/${usuarioId}`, {
        method: "DELETE",
        headers: {
          Accept: "application/json",
        },
      });

      const data = await response.json();

      if (response.ok && data.status === "success") {
        await CustomDialog.success(
          "Operación exitosa",
          data.message || "Usuario eliminado correctamente"
        );

        await loadData();
      } else {
        if (typeof hideTableLoading === "function") {
          hideTableLoading();
        }
        CustomDialog.error(
          "Error",
          data.message || "No se pudo eliminar el usuario."
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

window.mostrarModalEliminarUsuario = eliminarUsuario;
