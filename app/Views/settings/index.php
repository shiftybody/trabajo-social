<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<div class="container">
  <div class="content">
    <div class="content-header">
      <h1><?php echo $page_title; ?></h1>
    </div>

    <div class="config-container">
      <nav class="config-nav">

        <div class="config-nav-group">
          <h3>General</h3>
          <a href="#escalas-sociofamiliares" class="active">Escalas Socio familiares</a>
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
        <div class="config-content-header">
          <h2 id="criteria-title"><?php echo $current_criteria_title; ?></h2>
          <button class="btn-primary">Añadir Nuevo</button>
        </div>

        <p>Contenido de la sección seleccionada. Esta área se actualizará con JavaScript.</p>
      </main>
    </div>

  </div>
</div>

<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.config-nav a');
    const criteriaTitle = document.getElementById('criteria-title');
    const contentArea = document.getElementById('config-content-area');

    navLinks.forEach(link => {
      link.addEventListener('click', function(event) {
        event.preventDefault(); // Prevenir navegación normal

        // Quitar clase activa de todos los enlaces
        navLinks.forEach(l => l.classList.remove('active'));

        // Añadir clase activa al enlace clickeado
        this.classList.add('active');

        // Actualizar el título del contenido
        criteriaTitle.textContent = this.textContent;

        // ----- Lógica AJAX (futura implementación) -----
        // Aquí harías una llamada AJAX para obtener los datos
        // de la sección correspondiente y actualizar el HTML
        // de la tabla en `contentArea`.
        // fetch('/configuration/criteria/' + this.getAttribute('href').substring(1))
        //     .then(response => response.text())
        //     .then(html => {
        //         // Reemplazar el contenido de la tabla
        //         contentArea.querySelector('table').parentElement.innerHTML = html;
        //     });
        console.log('Cargando sección:', this.getAttribute('href').substring(1));
      });
    });
  });
</script>