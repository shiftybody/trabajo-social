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
                <span>Crear Usuario</span>
            </nav>
        </div>
        <div class="form-wrapper">
            <div class="form-information">
                <h1 class="form-title">
                    Crear nuevo usuario
                </h1>
                <p class="form-helper">Ingrese los datos del usuario que desea crear</p>
            </div>

            <form novalidate action="<?= APP_URL ?>api/users" method="POST" class="form-content form-ajax" id="createUserForm" enctype="multipart/form-data">

                <!-- Avatar -->
                <div class="upload-avatar">
                    <label for="file-input" class="file-label">Escoge una imagen de perfil</label>
                    <div class="file-section">
                        <span class="user-avatar">
                        </span>
                        <div class="file-upload">
                            <input id="file-input" type="file" name="avatar" accept="image/png, image/jpeg, image/gif"
                                class="input input-file" />
                            <p class="helper" id="file_input_help">jpg, jpeg, png, gif tamaño máximo 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Nombre Completo & Apellido Paterno -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="nombre" class="file-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="input" placeholder="Nombre"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
                    </div>
                    <div class="input-field">
                        <label for="apellidoPaterno" class="file-label">Apellido Paterno</label>
                        <input type="text" name="apellidoPaterno" id="apellidoPaterno" class="input"
                            placeholder="Apellido Paterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
                    </div>
                </div>

                <!-- Apellido Materno & Telefono -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="apellidoMaterno" class="file-label">Apellido Materno</label>
                        <input type="text" name="apellidoMaterno" id="apellidoMaterno" class="input"
                            placeholder="Apellido Materno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
                    </div>
                    <div class="input-field">
                        <label for="telefono" class="file-label">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" class="input" placeholder="Telefono"
                            pattern="[0-9]{10}" maxlength="10">
                    </div>
                </div>


                <!-- correo y rol -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="correo" class="file-label">Correo</label>
                        <input type="email" name="correo" id="correo" class="input" placeholder="Correo"
                            maxlength="100">
                    </div>
                    <div class="input-field">
                        <label for="rol" class="file-label">Rol</label>
                        <select name="rol" id="rol" class="input">
                            <option value="" selected>Selecciona un rol</option>
                        </select>
                    </div>
                </div>

                <!-- Nombre de usuario -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="username" class="file-label">Nombre de Usuario</label>
                        <input type="text" name="username" id="username" class="input" placeholder="Nombre de Usuario"
                            pattern="[a-zA-Z0-9._@!#$%^&*+\-]{3,70}" maxlength="70">
                    </div>
                </div>

                <!-- Contraseña & confirmar contraseña -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="password" class="file-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="input" placeholder="Contraseña"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20">
                    </div>
                    <div class="input-field">
                        <label for="password2" class="file-label">Confirmar Contraseña</label>
                        <input type="password" name="password2" id="password2" class="input"
                            placeholder="Confirmar Contraseña"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" maxlength="20" autocomplete="new-password">
                    </div>
                </div>

                <!-- clear and submit -->
                <div class="buttons-options">
                    <button type="submit"><span class="plus-icon">+</span>Crear Usuario</button>
                    <button type="reset">Limpiar</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require_once APP_ROOT . 'public/inc/scripts.php' ?>
<script>
    fetch("<?= APP_URL ?>api/roles", {
            method: "GET",
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(responseData => {
            const select = document.getElementById("rol");

            const roles = responseData.data;

            if (!roles || roles.length === 0) {
                console.warn('No se encontraron roles disponibles');
                return;
            }

            roles.forEach(rol => {
                const option = document.createElement("option");
                option.value = rol.rol_id;
                option.textContent = rol.rol_nombre;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar los roles:', error);
            CustomDialog.error(
                'Error de Carga',
                'No se pudieron cargar los roles disponibles. Por favor, recargue la página e inténtelo de nuevo.'
            );
        });

    // Mejorar el manejo de la carga de imagen de perfil
    document.getElementById('file-input').addEventListener('change', function(e) {
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
                document.querySelector('.user-avatar').style.backgroundImage = `url(${event.target.result})`;
                CustomDialog.toast('Imagen cargada correctamente', 'success', 2000);
            };
            reader.onerror = function() {
                CustomDialog.error(
                    'Error de Lectura',
                    'No se pudo leer el archivo seleccionado. Por favor, inténtelo de nuevo.'
                );
            };
            reader.readAsDataURL(file);
        }
    });

    // Validación en tiempo real para contraseñas
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const password2 = document.getElementById('password2');

        // Validar fortaleza de contraseña
        const strongPassword = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}/;

        // Limpiar errores previos
        if (this.classList.contains('error-input')) {
            this.classList.remove('error-input');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }

        // Si password2 tiene valor, validar coincidencia
        if (password2.value && password !== password2.value) {
            // Marcar password2 como error si no coincide
            if (!password2.classList.contains('error-input')) {
                password2.classList.add('error-input');
                const existingError = password2.parentElement.querySelector('.error-message');
                if (!existingError) {
                    const error = document.createElement('p');
                    error.classList.add('error-message');
                    error.textContent = 'Las contraseñas no coinciden';
                    password2.parentElement.appendChild(error);
                }
            }
        } else if (password2.value && password === password2.value) {
            // Limpiar error de password2 si coinciden
            password2.classList.remove('error-input');
            const errorMsg = password2.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }
    });

    document.getElementById('password2').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const password2 = this.value;

        // Limpiar errores previos
        if (this.classList.contains('error-input')) {
            this.classList.remove('error-input');
            const errorMsg = this.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        }

        // Validar coincidencia si ambos campos tienen valor
        if (password && password2 && password !== password2) {
            if (!this.classList.contains('error-input')) {
                this.classList.add('error-input');
                const error = document.createElement('p');
                error.classList.add('error-message');
                error.textContent = 'Las contraseñas no coinciden';
                this.parentElement.appendChild(error);
            }
        }
    });

    // Validación adicional del email en tiempo real
    document.getElementById('correo').addEventListener('blur', function() {
        const email = this.value.trim();

        if (email && !this.checkValidity()) {
            if (!this.classList.contains('error-input')) {
                this.classList.add('error-input');
                const error = document.createElement('p');
                error.classList.add('error-message');
                error.textContent = 'Por favor, ingrese un correo electrónico válido';
                this.parentElement.appendChild(error);
            }
        }
    });

    // Validación del nombre de usuario en tiempo real
    document.getElementById('username').addEventListener('blur', async function() {
        const username = this.value.trim();

        if (username && username.length >= 3) {
            // Aquí podrías agregar una validación AJAX para verificar si el username ya existe
            // Por ahora solo validamos el patrón
            const pattern = /^[a-zA-Z0-9._@!#$%^&*+\-]{3,70}$/;
            if (!pattern.test(username)) {
                if (!this.classList.contains('error-input')) {
                    this.classList.add('error-input');
                    const error = document.createElement('p');
                    error.classList.add('error-message');
                    error.textContent = 'El nombre de usuario contiene caracteres no válidos';
                    this.parentElement.appendChild(error);
                }
            }
        }
    });

    // Manejo de cambios en el formulario
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