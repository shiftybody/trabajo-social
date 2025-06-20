<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<div class="container">
  <div class="right_content">
    <div class="navigation-header">
      <nav class="breadcrumb" id="breadcrumb-nav">
        <a href="<?= APP_URL ?>home">Inicio</a>
        <span class="breadcrumb-separator">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </span>
        <span>Estudios</span>
      </nav>
    </div>
    <div class="tools">
      <form class="filter_form" id="filter_form">
        <div class="input-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon icon icon-tabler icons-tabler-outline icon-tabler-search">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
            <path d="M21 21l-6 -6" />
          </svg>
          <input class="matching-search" name="matchingColumn" id="matchingInput" placeholder="Buscar paciente">
          <span class="clear-button" id="clearButton">×</span>
        </div>
        <div class="select-container">
          <select class="custom-select" name="filterColumn" title="" id="filterColumn">
            <option value="0">Todo</option>
            <option value="1">Código</option>
            <option value="2">Nombre</option>
            <option value="4">Nivel Socioeconómico</option>
            <option value="5">Estado del Estudio</option>
          </select>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-filter-icon icon icon-tabler icons-tabler-outline icon-tabler-filter">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
          </svg>
        </div>
      </form>

      <?php if (\App\Core\Auth::can('patients.create')): ?>
        <button type="submit" class="action_create_new" title="Crear nuevo estudio" onclick="showCreateStudyModal()">
          Nuevo Estudio
        </button>
      <?php endif; ?>
    </div>

    <div class="table-container" id="table-container">
      <!-- Loading inicial -->
      <div class="table-loading-container" id="table-loading">
        <div class="table-loading">
          <div class="table-spinner"></div>
          <p>Cargando pacientes...</p>
        </div>
      </div>

      <table id="patients-table" class="hover nowrap cell-borders" style="display: none;">
        <thead>
          <tr>
            <th class="dt-head-center">CÓDIGO</th>
            <th>NOMBRE COMPLETO</th>
            <th class="dt-head-center">FECHA DE NACIMIENTO</th>
            <th class="dt-head-center">EDAD</th>
            <th class="dt-head-center">NIVEL</th>
            <th class="dt-head-center">ESTADO ESTUDIO</th>
            <th>ACCIONES</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<?php require_once APP_ROOT . 'public/inc/scripts.php' ?>