<?php
$errorCode = '500';
$errorTitle = 'Error Interno del Servidor';
$errorMessage = ' Lo sentimos, ha ocurrido un error interno en el servidor.';
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