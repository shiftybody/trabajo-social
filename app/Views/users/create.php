<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<style>
    .body-container {
        display: flex;
        padding: var(--8, 32px) var(--0, 0px);
        flex-direction: column;
        align-items: center;
        gap: var(--4, 16px);
    }

    .container {
        display: flex;
        width: 672px;
        /* padding: var(--4, 16px) var(--0, 0px); */
        flex-direction: column;
        align-items: flex-start;
        gap: var(--8, 24px);
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
</style>

<div class="body-container">
    <div class="container">
        <div class="navigation-header">
            <!-- boton para regresar -->
            <style>
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
            </style>
            <a href="<?= APP_URL ?>users" class="return-btn">
                <span class="return-btn-symbol">
                    < </span>
                        <span class="return-btn-content">Regresar</span>
            </a>
        </div>
        <div class="general-information">
            <div class="form-information">
                <h1 class="form-title">
                    Crear nuevo usuario
                </h1>
                <p class="helper">Ingrese los datos del usuario que desea crear</p>
            </div>

            <form novalidate action="<?= APP_URL ?>api/users" method="POST" class="form-layout form-ajax" enctype="multipart/form-data">

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
                        <label for="password2" class="file-label
            ">Confirmar Contraseña</label>
                        <input type="password" name="password2" id="password2" class="input"
                            placeholder="Confirmar Contraseña"
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
                    <button type="submit" class="btn btn-primary"><span class="plus-icon">+</span>Crear Usuario</button>
                    <button type="reset" class="btn btn-secondary">Limpiar</button>
                </div>

            </form>
        </div>
    </div>
</div>
<script src="<?= APP_URL ?>public/js/ajax.js"></script>
<script>
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
                select.appendChild(option);
            });
        })
        // to string
        .catch(error => console.error('Error al cargar los roles:', error));
</script>