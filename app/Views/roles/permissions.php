<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<style>
  .permissions-container {
    padding: 2.5rem 10rem 0 10rem;
    min-height: calc(100vh - 4.1rem);
    background-color: #f9fafb;
  }

  .permissions-header {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 1.5rem;
    font-size: 14px;
    color: var(--modal-text-secondary);
  }

  .breadcrumb a {
    color: var(--btn-primary);
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .breadcrumb a:hover {
    color: var(--btn-primary-hover);
    text-decoration: underline;
  }

  .breadcrumb-separator {
    color: var(--modal-text-muted);
  }

  .role-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
  }

  .role-info h1 {
    font-size: 28px;
    font-weight: 700;
    color: var(--modal-text-primary);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .role-info .role-description {
    font-size: 16px;
    color: var(--modal-text-secondary);
    margin: 0;
  }

  .role-stats {
    display: flex;
    gap: 2rem;
    margin-top: 1rem;
  }

  .stat-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    min-width: 120px;
  }

  .stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--btn-primary);
    display: block;
  }

  .stat-label {
    font-size: 12px;
    color: var(--modal-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
  }

  .permissions-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .permissions-toolbar {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }

  .toolbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .search-container {
    position: relative;
    width: 300px;
  }

  .search-input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    border: 1px solid var(--modal-border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
    transition: border-color 150ms ease;
  }

  .search-input:focus {
    outline: none;
    border-color: var(--btn-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--modal-text-secondary);
    pointer-events: none;
  }

  .bulk-actions {
    display: flex;
    gap: 8px;
  }

  .bulk-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: white;
    border: 1px solid var(--modal-border);
    border-radius: 6px;
    font-size: 12px;
    color: var(--modal-text-secondary);
    cursor: pointer;
    transition: all 150ms ease;
  }

  .bulk-btn:hover {
    background: var(--btn-primary);
    color: white;
    border-color: var(--btn-primary);
  }

  .bulk-btn svg {
    width: 14px;
    height: 14px;
  }

  .permissions-counter {
    font-size: 14px;
    color: var(--modal-text-secondary);
  }

  .counter-badge {
    background: var(--btn-primary);
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 8px;
  }

  .permissions-grid {
    max-height: 60vh;
    overflow-y: auto;
  }

  .permission-category {
    border-bottom: 1px solid var(--modal-border);
  }

  .permission-category:last-child {
    border-bottom: none;
  }

  .category-header {
    background: #f3f4f6;
    padding: 12px 2rem;
    cursor: pointer;
    transition: background-color 150ms ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .category-header:hover {
    background: #e5e7eb;
  }

  .category-title {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: var(--modal-text-secondary);
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .category-toggle {
    transition: transform 200ms ease;
  }

  .category-header.collapsed .category-toggle {
    transform: rotate(-90deg);
  }

  .category-permissions {
    padding: 0;
  }

  .permission-item {
    padding: 0;
    border-bottom: 1px solid #f1f5f9;
  }

  .permission-item:last-child {
    border-bottom: none;
  }

  .permission-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 2rem;
    cursor: pointer;
    transition: background-color 150ms ease;
    margin: 0;
    width: 100%;
  }

  .permission-label:hover {
    background: rgba(59, 130, 246, 0.05);
  }

  .permission-label.selected {
    background: rgba(59, 130, 246, 0.1);
    border-left: 3px solid var(--btn-primary);
  }

  .permission-checkbox {
    margin: 0;
    width: 16px;
    height: 16px;
    accent-color: var(--btn-primary);
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
  }

  .permission-details {
    flex: 1;
  }

  .permission-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--modal-text-primary);
    margin: 0 0 4px 0;
  }

  .permission-description {
    font-size: 12px;
    color: var(--modal-text-secondary);
    margin: 0;
    line-height: 1.4;
  }

  .permissions-actions {
    padding: 2rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .actions-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .actions-right {
    display: flex;
    gap: 12px;
  }

  .btn-secondary {
    padding: 12px 24px;
    border: 1px solid var(--modal-border);
    background: white;
    color: var(--modal-text-primary);
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 150ms ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-secondary:hover {
    background: #f3f4f6;
    border-color: var(--modal-text-muted);
  }

  .btn-primary {
    padding: 12px 24px;
    background: var(--btn-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 150ms ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }

  .btn-primary:hover:not(:disabled) {
    background: var(--btn-primary-hover);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
  }

  .btn-primary:disabled {
    background: var(--modal-text-muted);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  .changes-indicator {
    font-size: 14px;
    color: var(--modal-warning);
    font-weight: 500;
  }

  .no-permissions {
    padding: 3rem;
    text-align: center;
    color: var(--modal-text-secondary);
  }

  .no-permissions svg {
    width: 48px;
    height: 48px;
    margin-bottom: 1rem;
    opacity: 0.5;
  }

  /* Loading específico para esta página */
  .permissions-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem;
    gap: 12px;
  }

  .permissions-spinner {
    width: 24px;
    height: 24px;
    border: 2px solid var(--modal-border);
    border-top: 2px solid var(--btn-primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  /* Responsive */
  @media (max-width: 1080px) {
    .permissions-container {
      padding: 2rem;
    }

    .role-header {
      flex-direction: column;
      gap: 1rem;
    }

    .role-stats {
      justify-content: center;
    }

    .permissions-toolbar {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .search-container {
      width: 100%;
    }

    .permissions-actions {
      flex-direction: column;
      gap: 1rem;
      align-items: stretch;
    }

    .actions-right {
      justify-content: center;
    }
  }
</style>

<div class="permissions-container">
  <!-- Header con información del rol -->
  <div class="permissions-header">
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
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3a6.364 6.364 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
          </svg>
          <span id="role-name">Cargando...</span>
        </h1>
        <p class="role-description">Gestión de permisos del rol</p>

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
    </div>
  </div>

  <!-- Contenido principal de permisos -->
  <div class="permissions-content">
    <!-- Toolbar con búsqueda y acciones -->
    <div class="permissions-toolbar">
      <div class="toolbar-left">
        <div class="search-container">
          <input type="text" id="permissions-search" class="search-input" placeholder="Buscar permisos...">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
        </div>

        <div class="bulk-actions">
          <button type="button" class="bulk-btn" onclick="selectAllVisiblePermissions()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9,11 12,14 22,4"></polyline>
              <path d="M21,12v7a2,2 0 0,1 -2,2H5a2,2 0 0,1 -2,-2V5a2,2 0 0,1 2,-2h11"></path>
            </svg>
            Seleccionar visibles
          </button>
          <button type="button" class="bulk-btn" onclick="deselectAllPermissions()">
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

    <!-- Grid de permisos -->
    <div class="permissions-grid" id="permissions-grid">
      <div class="permissions-loading">
        <div class="permissions-spinner"></div>
        <span>Cargando permisos...</span>
      </div>
    </div>
  </div>

  <!-- Acciones finales -->
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

      <button type="button" id="save-permissions" class="btn-primary" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17,21 17,13 7,13 7,21"></polyline>
          <polyline points="7,3 7,8 15,8"></polyline>
        </svg>
        Guardar Cambios
      </button>
    </div>
  </div>
</div>

<script src="<?= APP_URL ?>public/js/role-permissions.js"></script>