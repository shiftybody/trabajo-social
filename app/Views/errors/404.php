<?php
$errorCode = '404';
$errorTitle = 'Página no encontrada';
$errorMessage =  'Lo sentimos, la página que estás buscando no existe.';
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<main class="error-page-container">
  <div class="error-content-wrapper">
    <div class="error-code"><?= htmlspecialchars($errorCode) ?></div>
    <h1 class="error-title"><?= htmlspecialchars($errorTitle) ?></h1>
    <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
  </div>
</main>