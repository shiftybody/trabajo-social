<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<div class="container">
  <div class="content">
    <div class="config-container">
      <nav class="config-nav">
        <div class="config-nav-group">
          <h3>General</h3>
          <hr>
        </div>
        <div class="config-nav-group">
          <h3>Criterios</h3>
        </div>
      </nav>
      <main class="config-content content-loading" id="config-content-area">
        <div class="loading-container">
        </div>
      </main>
    </div>
  </div>
</div>

<?php require_once APP_ROOT . 'public/inc/scripts.php' ?>