<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<!-- Agregar CSS específico para configuración -->
<link rel="stylesheet" href="<?php echo APP_URL; ?>public/css/views/settings.css">

<div class="container">
  <div class="content">
    <div class="content-header">
      <h1><?php echo $page_title; ?></h1>
    </div>

    <div class="config-container">
      <nav class="config-nav">
        <div class="config-nav-group">
          <h3>General</h3>
          <a href="#niveles-socioeconomicos" class="active">Niveles Socioeconómico</a>
          <a href="#reglas-aportacion">Reglas de Aportación</a>
        </div>

        <!-- Aqui deben de ir los criterios -->

      </nav>

      <main class="config-content content-loading" id="config-content-area">
        <div class="loading-container">
        </div>
      </main>
    </div>
  </div>
</div>

<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>