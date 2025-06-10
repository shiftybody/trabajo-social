<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<main class="container">
  <div class="content">
    <div class="navigation-header">
      <nav class="breadcrumb" id="breadcrumb-nav">
        <a href="<?= APP_URL ?>users">Usuarios</a>
        <span class="breadcrumb-separator">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </span>
        <span>Editar Usuario</span>
      </nav>
    </div>
    <div class="form-wrapper">
      <div class="form-information">
        <h1 class="form-title">
          Editar Usuario
        </h1>
        <p class="form-helper">Ingrese los datos del usuario que desea modificar</p>
      </div>
      <form class="form-container form-ajax" id="editUserForm" novalidate action="<?= APP_URL ?>api/users/<?= $usuario->usuario_id ?>" method="POST" enctype="multipart/form-data">

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
              <button type="submit"><span class="plus-icon">+</span>Actualizar</button>
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

            <button id="upload_photo_btn" class="btn-upload-avatar">
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
  <?php require_once APP_ROOT . 'public/inc/scripts.php'; ?>
  <script>
    const rolActual = "<?= $usuario->usuario_rol ?>";
    const estadoActual = "<?= $usuario->usuario_estado ?>";

    // Objetos de estado para el select
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

    // Cargar los roles disponibles 
    fetch("<?= APP_URL ?>api/roles", {
        method: "GET",
      })
      .then(response => response.json())
      .then(data => {
        const select = document.getElementById("rol");
        data.data.forEach(rol => {
          const option = document.createElement("option");
          option.value = rol.rol_id;
          option.textContent = rol.rol_nombre;

          if (rol.rol_id == rolActual) {
            option.selected = true;
          }

          select.appendChild(option);

        });
      })
      .catch(error => console.error('Error al cargar los roles:', error));

    // Recorrer los estados y agregarlos al select
    estados.forEach(estado => {
      const option = document.createElement("option");
      option.value = estado.id;
      option.textContent = estado.descripcion;

      if (estado.id == estadoActual) {
        option.selected = true;
      }

      estadoSelect.appendChild(option);
    });

    // Manejo del evento de clic de la seccion de contraseña
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

    // Manejo del evento de clic en el botón de subir foto
    document.getElementById('upload_photo_btn').addEventListener('click', function(e) {
      e.preventDefault(); // Prevenir comportamiento por defecto del enlace
      document.getElementById('foto').click(); // Simular clic en el input de archivo
    });

    const form = document.querySelector('form');
    const breadcrumbNav = document.getElementById('breadcrumb-nav');
    let formChanged = false;
    let isSubmitting = false;

    form.addEventListener('input', () => {
      formChanged = true;
    });

    form.addEventListener('change', () => {
      formChanged = true;
    });

    form.addEventListener('submit', () => {
      isSubmitting = true;
      formChanged = false;
    });

    async function confirmAndNavigate(url) {
      // Verifica si hay cambios sin guardar o si el formulario está siendo enviado.
      if (!formChanged || isSubmitting) {
        window.location.href = url;
        return;
      }
      // Si hay cambios sin guardar, muestra un diálogo de confirmación.
      const userConfirmed = await CustomDialog.confirm(
        'Cambios sin guardar',
        'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?',
        'Sí, salir',
        'Cancelar'
      );

      if (userConfirmed) {
        formChanged = false;
        window.location.href = url;
      }
    }

    async function confirmAndNavigate(url) {
      // Verifica si hay cambios sin guardar o si el formulario está siendo enviado.
      if (!formChanged || isSubmitting) {
        window.location.href = url;
        return;
      }
      // Si hay cambios sin guardar, muestra un diálogo de confirmación.
      const userConfirmed = await CustomDialog.confirm(
        'Cambios sin guardar',
        'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?',
        'Sí, salir',
        'Cancelar'
      );

      if (userConfirmed) {
        formChanged = false;
        window.location.href = url;
      }
    }

    // Interceptar TODOS los clics en enlaces <a>
    document.addEventListener('click', (e) => {

      const link = e.target.closest('a');
      console.log(link)

      if (link && link.href) {
        if (link.target === '_blank') return;

        if (link.getAttribute('href').startsWith('#')) return;

        if (link.href.startsWith('mailto:') || link.href.startsWith('tel:')) return;

        e.preventDefault();
        confirmAndNavigate(link.href);
      }
    });

    window.addEventListener('beforeunload', (e) => {
      if (formChanged && !isSubmitting) {
        e.preventDefault();
        e.returnValue = '';
        return '';
      }
    });
  </script>