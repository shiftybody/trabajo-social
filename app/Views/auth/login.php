<?php
// Incluir encabezado común
require_once APP_ROOT . 'public/inc/head.php';


// Obtener mensaje flash si existe
$sessionService = new \App\Services\SessionService();
$flash = $sessionService->getFlash();
?>

<div class="login-container">
  <div class="login-form-wrapper">
    <div class="login-header">
      <h1>Sistema de Trabajo Social</h1>
      <p>Ingrese sus credenciales para acceder</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo $flash['message']; ?>
      </div>
    <?php endif; ?>

    <form class="login-form" action="/login" method="POST">
      <div class="form-group">
        <label for="usuario">Usuario o Correo Electrónico</label>
        <input type="text" id="usuario" name="usuario" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <div class="form-group checkbox-group">
        <label class="checkbox-container">
          <input type="checkbox" id="remember_me" name="remember_me" value="1">
          <span class="checkbox-text">Mantener sesión iniciada</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
    </form>
  </div>
</div>