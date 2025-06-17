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
  });

  resetModal.show();
}

window.mostrarModalResetearPassword = resetearPassword;
