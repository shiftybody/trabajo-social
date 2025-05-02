<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<style>
  .general-container {
    display: flex;
    padding: var(--8, 32px) var(--0, 0px);
    flex-direction: column;
    align-items: center;
    gap: var(--4, 16px);
  }

  .content-container {
    display: flex;
    padding: var(--4, 16px) var(--0, 0px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--8, 24px);
  }

  .form-container {
    display: flex;
    gap: 4rem;
  }

  input[type="file"] {
    border: 1px solid #ccc;
    border-radius: .5rem;
    display: inline-block;
    line-height: .5rem;
    background-color: #f9fafb;
  }

  input::file-selector-button {
    background-color: #14171d;
    background-position-x: 0%;
    background-size: 100%;
    border: 0;
    border-radius: 0;
    color: #fff;
    padding: .8rem 1.25rem;
    margin-right: 1rem;
  }

  input::file-selector-button:hover {
    background-color: #384051;
  }

  .form-layout {
    display: flex;
    padding: var(--0, 0px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--4, 16px);
    align-self: stretch;
  }

  .form-title {
    display: flex;
    color: var(--gray-900, var(--gray-900, #0C192A));
    color: var(--gray-900, var(--gray-900, color(display-p3 0.0667 0.098 0.1569)));
    /* leading-none/text-xl/font-bold */
    font-size: 20px;
    font-style: normal;
    font-weight: 700;
    line-height: 20px;
    align-self: stretch;
    /* 100% */
  }

  .helper {
    align-self: stretch;
    color: var(--gray-500, var(--gray-500, #677283));
    color: var(--gray-500, var(--gray-500, color(display-p3 0.4196 0.4471 0.502)));
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 12px;
    padding: 0;
  }

  .file-upload {
    display: flex;
    flex-direction: column;
    /* ancho del 100 */
    width: 100%;
    align-items: flex-start;
    gap: var(--2, .5rem);
    flex: 1 0 0;
  }

  .file-section {
    display: flex;
    padding: var(--0, 0px);
    align-items: center;
    gap: var(--4, 16px);
    align-self: stretch;
  }

  .general-information {
    display: flex;
    padding: var(--0, 0px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--4, 16px);
    align-self: stretch;
  }

  .file-label {
    align-self: stretch;
    color: var(--gray-900, var(--gray-900, #0C192A));
    color: var(--gray-900, var(--gray-900, color(display-p3 0.0667 0.098 0.1569)));
    font-size: 14px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
  }

  .form-information {
    display: flex;
    flex-direction: column;
    gap: .4rem;
  }

  .user-avatar {
    display: flex;
    width: 5rem;
    height: 5rem;
    padding: 0px 0px 28px 34px;
    background-image: url("<?= APP_URL ?>public/photos/default.jpg");
    background-position: 50%;
    background-size: cover;
    background-repeat: no-repeat;
    border-radius: 100px;
  }

  .upload-avatar {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: var(--2, .7rem);
    align-self: stretch;
  }

  /* Estilos para la imagen de perfil */
    .profile-picture-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }

  .profile-picture {
    width: 14rem;
    height: 14rem;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    position: relative;
    border: 1px solid #e2e8f0;
    overflow: hidden;
  }

  .profile-picture-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.5);
    overflow: hidden;
    width: 100%;
    height: 0;
    transition: .5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .profile-picture:hover .profile-picture-overlay {
    height: 40px;
  }

  .edit-button {
    color: white;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
  }

  .upload-photo-btn {
    width: 100%;
    text-align: center;
  }

  .upload-photo-btn button {
    background: none;
    border: none;
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
    padding: 5px 0;
  }

  .upload-photo-btn button:hover {
    text-decoration: underline;
  }

  #file-input {
    width: 100%;
    color: var(--gray-900, var(--gray-900, #0C192A));
    color: var(--gray-900, var(--gray-900, color(display-p3 0.0667 0.098 0.1569)));
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 125%;
    color: #9da3ae;
  }

  .row-layout {
    display: flex;
    padding: var(--0, 0px);
    align-items: flex-start;
    gap: var(--4, 16px);
    align-self: stretch;
    height: 100%;
  }

  .input-field {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--2, 8px);
    flex: 1 0 0;
  }

  .buttons-options {
    display: flex;
    flex-direction: row-reverse;
    gap: var(--4, .5rem);
    justify-content: flex-start;
    width: 100%;
  }

  .error-message {
    text-align: left;
  }

  .return-btn:hover {
    /* TODO: mejorar el comportamiento on hover */
    color: rgb(42, 42, 42);
  }

  .return-btn-symbol {
    text-decoration: none;
  }

  .return-btn-content {
    text-decoration: underline;
  }

  /* Estilos para el cambio de contraseña */
  .password-toggle {
    color: #3b82f6;
    cursor: pointer;
    font-size: 14px;
    text-decoration: underline;
    margin-top: 8px;
    display: inline-block;
  }

  .password-toggle:hover {
    color: #2563eb;
  }

  .password-section {
    display: none;
    transition: all 0.3s ease;
  }

  /* Estilos para el toggle de estado */
  .toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 30px;
  }

  .toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
  }

  input:checked+.slider {
    background-color: #2563eb;
  }

  input:focus+.slider {
    box-shadow: 0 0 1px #2563eb;
  }

  input:checked+.slider:before {
    transform: translateX(30px);
  }

  .status-label {
    margin-left: 10px;
    font-size: 14px;
    font-weight: 600;
  }

  .status-text-active {
    color: #059669;
  }

  .status-text-inactive {
    color: #dc2626;
  }

  .right-side {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
</style>
<div class="general-container">
  <div class="content-container">
    <div class="form-information">
      <h1 class="form-title">
        Editar Usuario
      </h1>
      <p class="helper">Ingrese los datos del usuario que desea modificar</p>
    </div>
    <form class="form-container form-ajax" novalidate action="<?= APP_URL ?>api/users/<?= $usuario->usuario_id ?>" method="POST" enctype="multipart/form-data">
      <div class="left-side">
        <div class="general-information">
          <!-- Nombre Completo & Apellido Paterno -->
          <div class="row-layout">
            <div class="input-field">
              <label for="nombre" class="file-label">Nombre</label>
              <input type="text" name="nombre" id="nombre" value="<?= $usuario->usuario_nombre ?>" class="input" placeholder="Nombre"
                pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
            </div>
            <div class="input-field">
              <label for="apellidoPaterno" class="file-label">Apellido Paterno</label>
              <input type="text" name="apellidoPaterno" id="apellidoPaterno" value="<?= $usuario->usuario_apellido_paterno ?>" class="input"
                placeholder="Apellido Paterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
            </div>
          </div>

          <!-- Apellido Materno & Telefono -->
          <div class="row-layout">
            <div class="input-field">
              <label for="apellidoMaterno" class="file-label">Apellido Materno</label>
              <input type="text" name="apellidoMaterno" id="apellidoMaterno" value="<?= $usuario->usuario_apellido_materno ?>" class="input"
                placeholder="Apellido Materno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
            </div>
            <div class="input-field">
              <label for="telefono" class="file-label">Teléfono</label>
              <input type="text" name="telefono" id="telefono" value="<?= $usuario->usuario_telefono ?>" class="input" placeholder="Telefono"
                pattern="[0-9]{10}" maxlength="10">
            </div>
          </div>

          <!-- correo y rol -->
          <div class="row-layout">
            <div class="input-field">
              <label for="correo" class="file-label">Correo</label>
              <input type="email" name="correo" id="correo" value="<?= $usuario->usuario_email ?>" class="input" placeholder="Correo"
                maxlength="100">
            </div>
            <div class="input-field">
              <label for="rol" class="file-label">Rol</label>
              <select name="rol" id="rol" class="input">
                <option value="" selected>Selecciona un rol</option>
              </select>
            </div>
          </div>

          <!-- Nombre de usuario y estado -->
          <div class="row-layout">
            <div class="input-field">
              <label for="username" class="file-label">Nombre de Usuario</label>
              <input type="text" name="username" id="username" value="<?= $usuario->usuario_usuario ?>" class="input" placeholder="Nombre de Usuario"
                pattern="[a-zA-Z0-9._@!#$%^&*+\-]{3,70}" maxlength="70">
            </div>
            <div class="input-field">
              <label for="estado" class="file-label">Estado</label>
              <select name="estado" id="estado" class="input">
                <option value="" selected>Selecciona un estado</option>
              </select>
            </div>

          </div>

          <!-- Enlace para cambiar contraseña -->
          <div class="row-layout">
            <div class="input-field">
              <span id="toggle_password_section" class="password-toggle">Cambiar contraseña</span>
            </div>
          </div>

          <!-- Contraseña & confirmar contraseña (oculto inicialmente) -->
          <div id="password_section" class="row-layout password-section">
            <div class="input-field">
              <label for="password" class="file-label">Nueva contraseña</label>
              <input type="password" name="password" id="password" class="input" placeholder="Nueva contraseña"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20">
            </div>
            <div class="input-field">
              <label for="password2" class="file-label">Confirmar nueva contraseña</label>
              <input type="password" name="password2" id="password2" class="input"
                placeholder="Confirmar nueva contraseña"
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20" autocomplete="new-password">
            </div>
          </div>

          <!-- clear and submit -->
          <div class="buttons-options">
            <style>
              .plus-icon {
                font-weight: 300;
                font-size: 1.2em;
                font-family: 'Helvetica Neue', Arial, sans-serif;
              }
            </style>
            <button type="submit" class="btn btn-primary"><span class="plus-icon">+</span>Guardar</button>
            <button type="reset" class="btn btn-secondary">Limpiar</button>
          </div>
        </div>
      </div>
      <div class="right-side">
        <!-- Avatar a la derecha -->
        <label for="estado" class="file-label">Foto de perfil</label>
        <div class="profile-picture-container">
          <div class="profile-picture" style="background-image: url('<?= !empty($usuario->usuario_foto) ? APP_URL . 'public/photos/' . $usuario->usuario_foto : APP_URL . 'public/photos/default.jpg' ?>')">
            <div class="profile-picture-overlay">
              <label for="foto" class="edit-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z" />
                </svg>
                Editar
              </label>
            </div>
          </div>
          <div class="upload-photo-btn">
            <button type="button" id="upload_photo_btn" onclick="document.getElementById('foto').click()">
              Subir una foto...
            </button>
            <input type="file" name="foto" id="foto" accept="image/*" style="display: none;">
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?= APP_URL ?>public/js/ajax.js"></script>
<script>
  // Guardamos el rol actual del usuario en una variable JavaScript
  const rolActual = "<?= $usuario->usuario_rol ?>";
  const estadoActual = "<?= $usuario->usuario_estado ?>";

  // Cargar los roles disponibles
  fetch("<?= APP_URL ?>api/roles", {
      method: "GET",
    })
    .then(response => response.json())
    .then(data => {
      const select = document.getElementById("rol");
      data.forEach(rol => {
        const option = document.createElement("option");
        option.value = rol.rol_id;
        option.textContent = rol.rol_descripcion;
        (rol.rol_id == rolActualoption.selected) ? true: false;
        select.appendChild(option);
      });

      const estadoSelect = document.getElementById("estado");
      const estados = [{
          id: 1,
          descripcion: "Activo"
        },
        {
          id: 0,
          descripcion: "Inactivo"
        }
      ];
      estados.forEach(estado => {
        const option = document.createElement("option");
        option.value = estado.id;
        option.textContent = estado.descripcion;

        // Comparamos el valor del estado con el estado actual del usuario
        if (estado.id == estadoActual) {
          option.selected = true; // Marcamos esta opción como seleccionada
        }

        estadoSelect.appendChild(option);
      });
    })
    .catch(error => console.error('Error al cargar los roles:', error));

  // Funcionalidad para mostrar/ocultar sección de contraseña
  document.getElementById('toggle_password_section').addEventListener('click', function() {
    const passwordSection = document.getElementById('password_section');
    const changePasswordField = document.getElementById('change_password');

    if (passwordSection.style.display === 'none' || passwordSection.style.display === '') {
      passwordSection.style.display = 'flex';
      changePasswordField.value = '1';
      this.textContent = 'Cancelar cambio de contraseña';
    } else {
      passwordSection.style.display = 'none';
      changePasswordField.value = '0';
      // Limpiamos los campos de contraseña
      document.getElementById('password').value = '';
      document.getElementById('password2').value = '';
      this.textContent = 'Cambiar contraseña';
    }
  });

  // Validación para contraseñas coincidentes
  document.querySelector('form').addEventListener('submit', function(e) {
    const changePassword = document.getElementById('change_password').value;

    if (changePassword === '1') {
      const password = document.getElementById('password').value;
      const password2 = document.getElementById('password2').value;

      if (password !== password2) {
        e.preventDefault();
        alert('Las contraseñas no coinciden. Por favor, verifique.');
        return false;
      }
    }
  });

  // Manejo de la carga de imagen de perfil
  document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(event) {
        document.querySelector('.profile-picture').style.backgroundImage = `url(${event.target.result})`;
      };
      reader.readAsDataURL(file);

      // Cambiar el texto del botón para indicar que se ha seleccionado un archivo
      document.getElementById('upload_photo_btn').textContent = file.name.length > 20 ?
        file.name.substring(0, 17) + '...' :
        file.name;
    }
  });
</script>