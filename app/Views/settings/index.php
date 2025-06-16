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

        <div class="config-nav-group">
          <h3>Criterios Paciente</h3>
          <a href="#protocolo">Protocolo</a>
          <a href="#gasto-traslado">Gasto de Traslado</a>
          <a href="#tiempo-traslado">Tiempo de Traslado</a>
        </div>

        <div class="config-nav-group">
          <h3>Criterios Familia</h3>
          <a href="#integrantes">Integrantes de Familia</a>
          <a href="#hijos">Número de Hijos</a>
          <a href="#tipo-familia">Tipo de Familia</a>
        </div>

        <div class="config-nav-group">
          <h3>Criterios Vivienda</h3>
          <a href="#tipo-vivienda">Tipo de Vivienda</a>
          <a href="#tenencia">Tenencia</a>
          <a href="#zona">Zona</a>
          <a href="#materiales">Materiales</a>
          <a href="#servicios">Servicios</a>
        </div>
      </nav>

      <main class="config-content" id="config-content-area">
        <div class="loading-container">
        </div>
      </main>
    </div>
  </div>
</div>

<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>
