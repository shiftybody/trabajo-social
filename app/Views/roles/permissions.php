<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<div class="permissions-container">
  <div class="permissions-header">
    <div>
      <nav class="breadcrumb">
        <a href="<?= APP_URL ?>roles">Roles</a>
        <span class="breadcrumb-separator">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </span>
        <span>Gestionar Permisos</span>
      </nav>
      <div class="role-header">
        <div class="role-info">
          <h1>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shield">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
            </svg>
            <span id="role-name">Cargando...</span>
          </h1>
          <p class="role-description">Gestión de permisos del rol</p>
        </div>
      </div>
    </div>

    <div class="role-stats">
      <div class="stat-card">
        <span class="stat-number" id="total-permissions">0</span>
        <span class="stat-label">Total Permisos</span>
      </div>
      <div class="stat-card">
        <span class="stat-number" id="assigned-permissions">0</span>
        <span class="stat-label">Asignados</span>
      </div>
      <div class="stat-card">
        <span class="stat-number" id="users-count">0</span>
        <span class="stat-label">Usuarios</span>
      </div>
    </div>
  </div>

  <div class="permissions-content">
    <div class="permissions-toolbar">
      <div class="toolbar-left">

        <div class="search-container">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
            <path d="M21 21l-6 -6" />
          </svg>
          <input type="text" id="permissions-search" class="search-input" placeholder="Buscar permisos">
          <span class="clear-button">×</span>
        </div>

        <div class="bulk-actions">
          <button type="button" class="bulk-btn" id="select-all-visible-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9,11 12,14 22,4"></polyline>
              <path d="M21,12v7a2,2 0 0,1 -2,2H5a2,2 0 0,1 -2,-2V5a2,2 0 0,1 2,-2h11"></path>
            </svg>
            Seleccionar visibles
          </button>
          <button type="button" class="bulk-btn" id="deselect-all-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" width="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            </svg>
            Deseleccionar todos
          </button>
        </div>
      </div>

      <div class="permissions-counter">
        Permisos seleccionados: <span class="counter-badge" id="selected-count">0</span>
      </div>
    </div>

    <div class="permissions-grid" id="permissions-grid">
      <div class="permissions-loading">
        <div class="permissions-spinner"></div>
        <span>Cargando permisos...</span>
      </div>
    </div>
  </div>

  <div class="permissions-actions">
    <div class="actions-left">
      <span class="changes-indicator" id="changes-indicator" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M12 16v-4"></path>
          <path d="M12 8h.01"></path>
        </svg>
        Tienes cambios sin guardar
      </span>
    </div>

    <div class="actions-right">
      <a href="<?= APP_URL ?>roles" class="btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5"></path>
          <path d="M12 19l-7-7 7-7"></path>
        </svg>
        Volver
      </a>

      <button type="submit" id="save-permissions" class="btn-primary" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 5v14m-7-7h14" />
        </svg>
        Guardar Cambios
      </button>
    </div>
  </div>
</div>

<script src="<?= APP_URL ?>public/js/role-permissions.js"></script>