/**
 *
 */
function goTo(url) {
  window.location.href = `../${url}`;
}

/**
 *
 */
document.addEventListener('keydown', (e) => {
  if (e.key === '/') {
    document.querySelector('.search').focus();
  }
});

/**
 * * @description alerta para cerrar sesion lanzando el modal de 
 * inc/modal.php pasandole datos para personalizar y redireccionar
 */
if(document.getElementById("btn_exit") != null) {
  let btn_exit = document.getElementById("btn_exit");
}

// /**
//  * * @description funcion para mostrar un error en un input 
//  */
// function showError(input, message) {
//   const error = document.createElement('p');
//   error.classList.add('error-message');
//   error.textContent = message;
//   input.parentElement.appendChild(error);
//   input.classList.add('error-input');
// }