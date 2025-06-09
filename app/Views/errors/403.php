<?php
$errorCode = '403';
$errorTitle = 'Prohibido';
$errorMessage = 'Lo sentimos, no tienes permiso para acceder a este recurso en el servidor.';
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<main class="error-page-container">
  <div class="error-content-wrapper">
    <div class="error-code"><?= htmlspecialchars($errorCode) ?></div>
    <h1 class="error-title"><?= htmlspecialchars($errorTitle) ?></h1>
    <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>

    <?php if (\App\Core\Auth::can('search.view')): ?>
      <p class="info-message"> busca y accede a tus permisos&nbsp;<a> aquí <a> </p>
    <?php else: ?>
      <p class="info-message"> contacta al administrador del sistema.</p>
    <?php endif; ?>
  </div>
</main>
<?php
require_once APP_ROOT . 'public/inc/scripts.php';
?>
<script>
  const mainSearchInput = document.getElementById('mainSearchInput');
  document.querySelector('.info-message a').addEventListener('click', function(event) {
    event.preventDefault();
    openInputSearch();
  });
</script>