<?php require_once APP_ROOT . 'public/inc/head.php'; ?>

<header>
  <img src="<?= APP_URL ?>public/images/imagotipo-neurodesarrollo.png" alt="imagotipo neurodesarrollo" id="imagotipo">
  <img src="<?= APP_URL ?>public/images/logo-unam.svg" alt="escudo UNAM" id="escudo">
</header>

<main>
  <section id="section-container">
    <img src="<?= APP_URL ?>public/images/logotipo-neurodesarrollo.png" alt="logitipo neurodesarrollo" id="logotipo">

    <!-- Cambios principales: 
         - Cambié 'ajax-form' por 'form-ajax' (para consistencia con nuestro sistema)
         - Mantuve 'novalidate' 
         - El ID y action se mantienen igual -->
    <form novalidate action="<?= APP_URL ?>api/login" method="POST" id="login-form" class="form-ajax">

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
          <!-- Agregué class="input" para consistencia con el resto del sistema -->
          <input type="text" name="username" id="username" class="input"
            placeholder="usuario@dominio.com" autocomplete="username" maxlength="100">
        </div>

        <div id="password-input">
          <label for="password">Contraseña</label>
          <!-- Agregué class="input" y autocomplete para mejor UX -->
          <input type="password" name="password" id="password" class="input"
            placeholder="•••••••••••" autocomplete="current-password" maxlength="50">

          <!-- Cambié de <a> a <button> para mejor accesibilidad -->
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

      <div id="remember-check">
        <label for="remember">Recordar Sesión</label>
        <input type="checkbox" name="remember" id="remember">
      </div>

      <button type="submit">Iniciar Sesión</button>

    </form>
  </section>
</main>

<?php require_once APP_ROOT . 'public/inc/scripts.php'; ?>