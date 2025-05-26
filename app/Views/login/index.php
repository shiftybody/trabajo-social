<?php require_once APP_ROOT . 'public/inc/head.php';

$expired_message = '';
if (isset($_GET['expired_session']) && $_GET['expired_session'] == '1') {
  $expired_message = '<p class="expired-session-message">Tu sesión ha expirado. Por favor, inicia sesión de nuevo.</p>';
}
?>

<style>
  body {
    overflow: hidden;
    background-color: #F7F7F7;
  }

  p {
    color: var(--gray-500, #677283);
    color: var(--gray-500, color(display-p3 0.4196 0.4471 0.502));
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 150%;
  }

  header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    width: 100%;
    padding-top: 2rem;
  }

  main {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: calc(100vh - 12rem);
  }

  header #imagotipo {
    width: 19rem;
    margin-left: 10%;
  }

  header #escudo {
    width: 6rem;
    margin-right: 10%;
  }

  main #section-container {
    display: flex;
    position: absolute;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 1rem;
    width: 28.6rem;
    padding-bottom: 3rem;
  }

  main #section-container #login-form {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
    width: 100%;
    height: 100%;
    background-color: #ffffff;
    border-radius: var(--rounded-lg);
    padding: 2rem;
    gap: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.10), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  }

  main #section-container #logotipo {
    width: 6rem;
    filter: drop-shadow(0px 2px 4px rgba(0, 0, 0, 0.25));
  }

  main #section-container #login-form #login-inputs {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    gap: var(--2, 8px);
    align-self: stretch;
  }

  main #section-container #login-form #login-inputs #username-input,
  #password-input {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    gap: var(--2, 8px);
    align-self: stretch
  }

  /* Estilos para el contenedor de contraseña con icono */
  .password-input-container {
    position: relative;
    width: 100%;
  }

  .password-input-container input {
    width: 100%;
    padding-right: 2rem;
    /* Espacio para el icono */
  }

  .password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    display: none;
    /* Inicialmente oculto */
    align-items: center;
    justify-content: center;
    color: var(--gray-500, #677283);
    transition: color 0.2s ease;
  }

  .password-toggle:hover {
    color: var(--gray-700, #374151);
  }

  .password-toggle svg {
    width: 20px;
    height: 20px;
  }

  .eye-off-icon {
    display: none;
  }

  div#remember-check {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.5rem;
    align-items: center;
  }

  div#remember-check label:hover {
    cursor: pointer;
  }

  /* Estilo para el contenedor de mensajes de error */
  #message-container {
    display: none;
    flex-direction: column;
    align-items: center;
    color: var(--red-600);
    font-family: var(--font-family);
    font-size: var(--font-size-small);
    font-weight: var(--font-weight-medium);
    line-height: var(--line-height-large);
  }

  #message-container.visible {
    display: flex;
  }


  @media (max-height: 800px) {
    main #section-container #logotipo {
      display: none;
    }

    main {
      height: calc(100vh - 6.25rem);
    }
  }

  @media (max-width: 500px) {
    main #section-container {
      width: 100%;
      padding: 0 5%;
      /* height: 26rem; */
    }

    header #imagotipo {
      width: 14rem;
      margin-left: 5%;
    }

    header #escudo {
      width: 4rem;
      margin-right: 5%;
    }
  }
</style>

<header>
  <img src="<?= APP_URL ?>public/images/imagotipo-neurodesarrollo.png" alt="imagotipo neurodesarrollo" id="imagotipo">
  <img src="<?= APP_URL ?>public/images/logo-unam.svg" alt="escudo UNAM" id="escudo">
</header>

<main>
  <section id="section-container">
    <img src="<?= APP_URL ?>public/images/logotipo-neurodesarrollo.png" alt="logitipo neurodesarrollo" id="logotipo">

    <form novalidate action="<?= APP_URL ?>login" method="POST" id="login-form" class="ajax-form">

      <div id="login-info">
        <h1>Iniciar Sesión</h1>
        <p>Ingresa tu usuario & contraseña para acceder a tu cuenta</p>
      </div>

      <!-- Contenedor de mensajes de error -->
      <div id="message-container" <?= $expired_message ? 'class="visible"' : '' ?>>
        <?= $expired_message ?>
      </div>

      <div id="login-inputs">
        <div id="username-input">
          <label for="username">Correo o Nombre de Usuario</label>
          <input type="text" name="username" id="username" placeholder="usuario@dominio.com">
        </div>
        <!-- el patron puede ser cualquiera -->
        <div id="password-input">
          <label for="password">Contraseña</label>
          <div class="password-input-container">
            <input type="password" name="password" id="password" placeholder="•••••••••••">
            <button type="button" class="password-toggle" id="password-toggle">
              <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
              </svg>
              <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" />
                <path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" />
                <path d="M3 3l18 18" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div id="remember-check">
        <label for="remember">Recordar Sesión</label>
        <input type="checkbox" name="remember" id="remember">
      </div>

      <button type="submit" class="dark-button">Iniciar Sesión</button>

    </form>
  </section>
</main>

<script src="<?= APP_URL ?>public/js/login.js"></script>