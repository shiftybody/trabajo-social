const formularios = document.querySelectorAll(".form-ajax");

const PATTERN_MSG = {
  '[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}': 'El campo solo puede contener letras y espacios',
  '[0-9]{10}': 'El campo debe contener diez digitos',
  '(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}': 'Como mínimo una minúscula, mayuscula, número y caracter especial',
  '^((?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}|[a-zA-Z0-9._@!#$%^&*+\\-]{3,70})$': 'El correo o nombre de usuario no es válido',
}

// Para cada input de tipo file en la pagina con la clase .input-file
document.querySelectorAll('.input-file').forEach(input => {

  console.log(document.querySelectorAll('.input-file'));
  console.log(document.querySelector('.user-avatar'));

  input.addEventListener('change', function () {
    const file = this.files[0];
    const reader = new FileReader();

    reader.onload = function () {
      document.querySelector('.user-avatar').style.backgroundImage = `url(${reader.result})`;
    }
    reader.readAsDataURL(file);
  });

});

// Para cada formulario en la pagina con la clase .form-api
formularios.forEach(formulario => {

  //escuchar el evento reset y limpiar los mensajes de error, estilos y valores de los inputs
  formulario.addEventListener("reset", function (e) {
    document.querySelectorAll('.error-message').forEach(errorMsg => errorMsg.remove());
    document.querySelectorAll('.error-input').forEach(errorInput => errorInput.classList.remove('error-input'));
  });

  // escuchar el evento submit validar los campos del formulario y enviar los datos
  formulario.addEventListener("submit", function (e) {

    e.preventDefault();

    let isValid = true;
    const data = new FormData(this);

    // imprimir los datos del formulario como un objeto
    console.log(Object.fromEntries(data));

    // Limpiar los mensajes de error previos
    document.querySelectorAll('.error-message').forEach(errorMsg => errorMsg.remove());
    document.querySelectorAll('.error-input').forEach(errorInput => errorInput.classList.remove('error-input'));

    // Para cada campo del formulario
    data.forEach((value, key) => {
      const input = formulario.querySelector(`[name="${key}"]`);

      // Validar campos obligatorios

      // Si el campo es un string vacío y no es el campo avatar
      if (typeof value === "string" && value.trim() === "" && key !== "avatar") {
        let label = input.parentElement.querySelector("label").textContent;
        // Si el campo es un select
        if (input.tagName === "select") {
          showError(input, `Selecciona un rol para el usuario`);
        } else {
          showError(input, `El campo ${label.toLowerCase()} no puede estar vacío`);
        }
        isValid = false;
        return; // Salir de la validación de este campo
      }

      // si el input es de tipo email y no es un string vacio
      if (input.type === "email" && value.trim() !== "") {
        // Validar formato de email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
          showError(input, "El correo electrónico no es válido");
          isValid = false;
        }
      }

      // Validar patrón si el campo tiene el atributo pattern
      if (input.hasAttribute('pattern')) {
        const pattern = input.getAttribute('pattern');
        const regex = new RegExp(pattern);

        // Validar si el campo contraseña2 es igual a contraseña
        if (key === 'password2' && value !== data.get('password')) {
          showError(input, 'Las contraseñas no coinciden');
          isValid = false;
          return; // Salir de la validación de este campo
        }

        // Validar si el campo cumple con el patrón de validación
        if (!regex.test(value)) {
          const errorMessage = PATTERN_MSG[pattern] || "El valor no coincide con el patrón requerido";
          showError(input, errorMessage);
          isValid = false;
        }
      }
    });

    // si no es valido, no hacer la peticion
    if (!isValid) return;

    let method = this.getAttribute("method");
    let action = this.getAttribute("action");
    console.log(data);

    let config = {
      method: method,
      headers: encabezados,
      mode: 'cors',
      cache: 'no-cache',
      body: data
    }; 

    // realizar la solicitud ajax

  });
});

// Mostrar mensaje de error
function showError(input, message) {
  const error = document.createElement('p');
  error.classList.add('error-message');
  error.textContent = message;
  input.parentElement.appendChild(error);
  input.classList.add('error-input');
}


// si button type["reset"] se hace click regresar el .user-avatar a la imagen por defecto

document.querySelector('button[type="reset"]').addEventListener('click', function () {
  document.querySelector('.user-avatar').style.backgroundImage = `url(../public/photos/avatar.jpg)`;
}
);
// borrar el formulario y la imagen de avatar por defecto
// document.querySelector(".form-api").reset();
// document.querySelector('.user-avatar').style.backgroundImage = `url(../fotos/avatar.jpg)`;

//Eliminar estilo de error al escribir en el input
formularios.forEach(formulario => {
  formulario.addEventListener("input", function (e) {
    const input = e.target;
    if (input.classList.contains('error-input')) {
      input.classList.remove('error-input');
      const errorMsg = input.parentElement.querySelector('.error-message');
      if (errorMsg) {
        errorMsg.remove();
      }
    }
  });
});