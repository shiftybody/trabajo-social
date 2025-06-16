<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<main class="container">
    <div class="content">
        <div class="navigation-header">
            <?php if (\App\Core\Auth::can('users.view')): ?>
                <nav class="breadcrumb" id="breadcrumb-nav">
                    <a href="<?= APP_URL ?>home">Inicio</a>
                    <span class="breadcrumb-separator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6"></path>
                        </svg>
                    </span>
                    <a href="<?= APP_URL ?>users">Usuarios</a>
                    <span class="breadcrumb-separator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18l6-6-6-6"></path>
                        </svg>
                    </span>
                    <span>Crear Usuario</span>
                </nav>
            <?php else: ?>
                <nav class="breadcrumb" id="breadcrumb-nav">
                    <a href="<?= APP_URL ?>home">Ir a Inicio</a>
                </nav>
            <?php endif; ?>
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
                        <label for="apellido_paterno" class="file-label">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" class="input"
                            placeholder="Apellido Paterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,70}" maxlength="70">
                    </div>
                </div>

                <!-- Apellido Materno & Telefono -->
                <div class="row-layout">
                    <div class="input-field">
                        <label for="apellido_materno" class="file-label">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" class="input"
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