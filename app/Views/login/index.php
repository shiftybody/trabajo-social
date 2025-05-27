<?php
// Manejo de mensajes de estado
$status_message = '';
$message_class = '';

// Mensaje de sesión expirada
if (isset($_GET['expired_session']) && $_GET['expired_session'] == '1') {
  $status_message = 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.';
  $message_class = 'expired-session-message';
}

// Mensaje de cuenta deshabilitada
if (isset($_GET['account_disabled']) && $_GET['account_disabled'] == '1') {
  $status_message = 'Tu cuenta ha sido deshabilitada. Contacta al administrador para más información.';
  $message_class = 'account-disabled-message';
}
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?></title>
  <link rel="icon" href="<?= APP_URL ?>public/images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/base.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/global.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/login.css">
  <style>
    .account-disabled-message {
      color: var(--red-600);
      font-family: var(--font-family);
      font-size: var(--font-size-small);
      font-weight: var(--font-weight-medium);
      line-height: var(--line-height-large);
      background-color: #fef2f2;
      border: 1px solid #f87171;
      border-radius: 6px;
      padding: 12px;
      margin: 8px 0;
    }
  </style>
</head>

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

      <div id="message-container" <?= $status_message ? 'class="visible"' : '' ?>>
        <?php if ($status_message): ?>
          <p class="<?= $message_class ?>"><?= htmlspecialchars($status_message) ?></p>
        <?php endif; ?>
      </div>

      <div id="login-inputs">
        <div id="username-input">
          <label for="username">Correo o Nombre de Usuario</label>
          <input type="text" name="username" id="username" placeholder="usuario@dominio.com">
        </div>

        <div id="password-input">
          <label for="password">Contraseña</label>
          <input type="password" name="password" id="password" placeholder="•••••••••••">
          <a type="button" class="password-toggle" id="password-toggle">
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
          </a>
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