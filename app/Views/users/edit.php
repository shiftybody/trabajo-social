<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<style>
  main.container {
    display: flex;
    padding-top: 2rem;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }

  .content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: var(--8, 24px);
  }

  .form-container {
    display: flex;
    gap: 3rem;
  }

  input[type="file"] {
    border: 1px solid #ccc;
    border-radius: .5rem;
    display: inline-block;
    line-height: .5rem;
    background-color: #f9fafb;
  }

  input::file-selector-button {
    background-color: rgb(27, 31, 40);
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
    font-size: 20px;
    font-style: normal;
    font-weight: 700;
    line-height: 20px;
    align-self: stretch;
  }

  .form-helper {
    align-self: stretch;
    color: var(--gray-500, var(--gray-500, #677283));
    color: var(--gray-500, var(--gray-500, color(display-p3 0.4196 0.4471 0.502)));
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
  }

  .form-wrapper {
    display: flex;
    padding: var(--0, 0px);
    flex-direction: column;
    align-items: flex-start;
    gap: var(--4, 16px);
    align-self: stretch;
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

  .profile-picture-container {
    position: relative;
    width: 12rem;
    margin: 0 auto;
  }

  .profile-picture {
    width: 12.9rem;
    height: 12.9rem;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    border: 2px solid #e2e8f0;
  }

  .btn-upload-avatar {
    position: absolute;
    bottom: -10px;
    left: -10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 16px;
    background-color: white;
    color: #333;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .btn-upload-avatar:hover {
    background-color: #f8f8f8;
    border-color: #999;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .btn-upload-avatar svg {
    width: 1.2rem;
    height: 1.2rem;
  }

  button.btn-upload-avatar {
    width: auto;
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

  .left-side {
    width: 42rem;
  }
</style>

<main class="container">
  <div class="content">
    <div class="navigation-header">
      <a href="<?= APP_URL ?>users" class="return-btn">
        <span class="return-btn-symbol">
          < </span>
            <span class="return-btn-content">Regresar</span>
      </a>
    </div>
    <!-- <?= var_dump($usuario) ?> -->
    <div class="form-wrapper">
      <div class="form-information">
        <h1 class="form-title">
          Editar Usuario
        </h1>
        <p class="form-helper">Ingrese los datos del usuario que desea modificar</p>
      </div>
      <form class="form-container form-ajax" novalidate action="<?= APP_URL ?>api/users/<?= $usuario->usuario_id ?>" method="POST" enctype="multipart/form-data">

        <div class="left-side">
          <div class="general-information">

            <input type="hidden" name="usuario_id" value="<?= $usuario->usuario_id ?>">

            <input type="hidden" name="change_password" id="change_password" value="0">

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
              <button type="submit" class="btn btn-primary"><span class="plus-icon">+</span>Actualizar</button>
              <!-- <button type="reset" class="btn btn-secondary">Limpiar</button> -->
            </div>
          </div>
        </div>
        <div class="right-side">
          <!-- Avatar a la derecha -->
          <label class="file-label">Foto de perfil</label>
          <div class="profile-picture-container">
            <?php $avatar_url = (!empty($usuario->usuario_avatar) && $usuario->usuario_avatar !== 'default.jpg')
              ? APP_URL . 'public/photos/original/' . $usuario->usuario_avatar
              : APP_URL . 'public/photos/default.jpg';
            ?>
            <div class="profile-picture" style="background-image: url('<?= $avatar_url ?>')">
            </div>

            <input type="file" name="avatar" id="foto" accept="image/png, image/jpeg, image/gif" style="display: none;">

            <button type="button" id="upload_photo_btn" class="btn-upload-avatar" onclick="document.getElementById('foto').click()">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                <path d="M13.5 6.5l4 4" />
              </svg>
              <span class="btn-upload-avatar-text">Editar</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  </div>

  <script src="<?= APP_URL ?>public/js/ajax.js"></script>
  <script>
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

          // Comparamos el valor del rol con el rol actual del usuario
          if (rol.rol_id == rolActual) {
            option.selected = true; // Marcamos esta opción como seleccionada
          }

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

    // SCRIPT CORREGIDO: Funcionalidad para mostrar/ocultar sección de contraseña
    document.getElementById('toggle_password_section').addEventListener('click', async function(e) {
      e.preventDefault(); // Prevenir comportamiento por defecto del enlace

      const passwordSection = document.getElementById('password_section');
      const changePasswordField = document.getElementById('change_password');

      if (passwordSection.style.display === 'none' || passwordSection.style.display === '') {
        // Confirmar cambio de contraseña
        const shouldChangePassword = await CustomDialog.confirm(
          'Cambiar Contraseña',
          '¿Está seguro de que desea cambiar la contraseña de este usuario?',
          'Sí, cambiar',
          'Cancelar'
        );

        if (shouldChangePassword) {
          passwordSection.style.display = 'flex';
          changePasswordField.value = '1';
          this.textContent = 'Cancelar cambio de contraseña';

          // Focus en el primer campo de contraseña
          setTimeout(() => {
            document.getElementById('password').focus();
          }, 100);

          CustomDialog.toast('Ahora puede ingresar la nueva contraseña', 'info', 3000);
        }
      } else {
        passwordSection.style.display = 'none';
        changePasswordField.value = '0';
        // Limpiamos los campos de contraseña
        document.getElementById('password').value = '';
        document.getElementById('password2').value = '';
        // Limpiar errores de contraseña si existen
        ['password', 'password2'].forEach(fieldName => {
          const field = document.getElementById(fieldName);
          if (field && field.classList.contains('error-input')) {
            const errorMsg = field.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
            field.classList.remove('error-input');
          }
        });
        this.textContent = 'Cambiar contraseña';
        CustomDialog.toast('Cambio de contraseña cancelado', 'info', 2000);
      }
    });

    // Validación mejorada para contraseñas coincidentes
    document.querySelector('form').addEventListener('submit', function(e) {
      const changePassword = document.getElementById('change_password').value;

      if (changePassword === '1') {
        const password = document.getElementById('password').value;
        const password2 = document.getElementById('password2').value;

        if (password !== password2) {
          e.preventDefault();

          // Marcar campos con error (el CustomDialog se maneja en ajax.js)
          ['password', 'password2'].forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
              field.classList.add('error-input');
            }
          });

          return false;
        }
      }
    });

    // Mejorar el manejo de la carga de imagen de perfil
    document.getElementById('foto').addEventListener('change', function(e) {
      const file = e.target.files[0];

      if (file) {
        // Validar tamaño del archivo (5MB máximo)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
          CustomDialog.error(
            'Archivo muy grande',
            'La imagen no puede ser mayor a 5MB. Por favor, seleccione una imagen más pequeña.'
          );
          this.value = ''; // Limpiar el input
          return;
        }

        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
          CustomDialog.error(
            'Formato no válido',
            'Solo se permiten archivos de imagen (JPG, PNG, GIF).'
          );
          this.value = ''; // Limpiar el input
          return;
        }

        const reader = new FileReader();
        reader.onload = function(event) {
          document.querySelector('.profile-picture').style.backgroundImage = `url(${event.target.result})`;
          CustomDialog.toast('Imagen cargada correctamente', 'success', 2000);
        };
        reader.readAsDataURL(file);
      }
    });

    // Función para confirmar navegación si hay cambios sin guardar
    let formChanged = false;
    const form = document.querySelector('form');

    // Detectar cambios en el formulario
    form.addEventListener('input', function() {
      formChanged = true;
    });

    form.addEventListener('change', function() {
      formChanged = true;
    });

    // Advertir al usuario si intenta salir con cambios sin guardar
    window.addEventListener('beforeunload', function(e) {
      if (formChanged) {
        e.preventDefault();
        e.returnValue = '¿Está seguro de que desea salir? Los cambios no guardados se perderán.';
      }
    });

    // Limpiar la marca de cambios al enviar el formulario exitosamente
    form.addEventListener('submit', function() {
      formChanged = false;
    });
  </script>