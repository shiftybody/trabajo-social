<?php

use App\Core\Auth;
?>
<!-- header navbar -->
<header id="app-header">
  <section id="container">
    <div id="left-side">
      <button type="button" id="leftMenu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-menu-2">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M4 6l16 0" />
          <path d="M4 12l16 0" />
          <path d="M4 18l16 0" />
        </svg>
      </button>
      <div id="info-container">
        <img src="<?php echo APP_URL; ?>public/images/logotipo-neurodesarrollo.png" alt="" class="logo">
        <span id="page-name"><?php echo $titulo; ?></span>
      </div>
    </div>
    <div id="right-side">
      <div id="search-container">
        <?php if (Auth::can('search.view')): ?>
          <input id="mainSearchInput" class="search" placeholder="Escribe / para navegar">
        <?php endif; ?>
        <?php if (Auth::can('notifications.view')): ?>
          <button type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
              <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
            </svg>
          </button>
        <?php endif; ?>
      </div>
      <!-- avatar -->
      <?php
      if (file_exists(APP_ROOT . 'public/photos/thumbnail/' . $_SESSION['trabajo_social']['avatar'])) {
        echo '<style>
        #avatar {
          background: url(' . APP_URL . 'public/photos/thumbnail/' . $_SESSION['trabajo_social']['avatar'] . ') lightgray 50% / cover no-repeat;
        }
      </style>';
      } else {
        echo '<style>
        #avatar {
          background: url(' . APP_URL . 'public/photos/default.jpg) lightgray 50% / cover no-repeat;
        }
      </style>';
      }
      ?>
      <div id="avatar">
        <span id="avatar-status">
        </span>
      </div>
    </div>
  </section>
</header>

<!-- used for the blur effect when the sidebar is open -->
<div class="contentblur"></div>

<!-- leftSidebar -->
<div id="leftSidebar" class="sidebar left">
  <div class="sidebar-header">
    <img src="<?php echo APP_URL; ?>public/images/logotipo-neurodesarrollo.png" alt="" class="logo">
    <a href="javascript:void(0)" id="leftCloseButton">
      <button class="btn-close" id="menu">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M18 6l-12 12" />
          <path d="M6 6l12 12" />
        </svg>
      </button>
    </a>
  </div>
  <div id="sidebar-options">
    <a href="<?= APP_URL ?>home" <?= stripos($titulo, 'inicio') !== false ? 'class="active"' : '' ?>>
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-layout-home">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
        <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
        <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
        <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
      </svg>
      Inicio
    </a>
    <?php if (Auth::canAny(['users.view', 'users.create', 'users.edit', 'users.delete'])): ?>
      <?php
      // Determinar la URL según los permisos
      $userUrl = APP_URL . 'users';
      if (!Auth::canAny(['users.view', 'users.edit', 'users.delete']) && Auth::can('users.create')) {
        $userUrl = APP_URL . 'users/create';
      }
      ?>
      <a href="<?= $userUrl ?>" <?= stripos($titulo, 'usuario') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
          <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
        </svg>
        Usuarios
      </a>
    <?php endif; ?>
    <?php if (Auth::canAny(['roles.view', 'roles.create', 'roles.delete', 'permissions.view', 'permissions.assign'])): ?>
      <a href="<?= APP_URL ?>roles" <?= (stripos($titulo, 'rol') !== false) || (stripos($titulo, 'permisos') !== false) ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shield">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
        </svg>
        Roles
      </a>
    <?php endif; ?>
    <?php if (Auth::can('studies.view')): ?>
      <a href="<?= APP_URL ?>studies " <?= stripos($titulo, 'estudios') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-receipt">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" />
        </svg>
        Estudios
      </a>
    <?php endif; ?>
    <?php if (Auth::can('donators.view')): ?>
      <a href="<?= APP_URL ?>donations" <?= stripos($titulo, 'donacion') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-heart-handshake">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
          <path d="M12 6l-3.293 3.293a1 1 0 0 0 0 1.414l.543 .543c.69 .69 1.81 .69 2.5 0l1 -1a3.182 3.182 0 0 1 4.5 0l2.25 2.25" />
          <path d="M12.5 15.5l2 2" />
          <path d="M15 13l2 2" />
        </svg>
        Contribuyentes
      </a>
    <?php endif; ?>
    <?php if (Auth::can('donations.view')): ?>
      <a href="<?= APP_URL ?>donations" <?= stripos($titulo, 'donacion') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-coins">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" />
          <path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" />
          <path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" />
          <path d="M3 6v10c0 .888 .772 1.45 2 2" />
          <path d="M3 11c0 .888 .772 1.45 2 2" />
        </svg>
        Aportaciones
      </a>
    <?php endif; ?>
    <?php if (Auth::can('reports.view')): ?>
      <a href="<?= APP_URL ?>reports" <?= stripos($titulo, 'reporte') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
          <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
        </svg>
        Reportes
      </a>
    <?php endif; ?>
    <?php if (Auth::can('stats.view')): ?>
      <a href="<?= APP_URL ?>stats" <?= stripos($titulo, 'estadisticas') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chart-bar">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
          <path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
          <path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
          <path d="M4 20h14" />
        </svg>
        Estadisticas
      </a>
    <?php endif; ?>
    <?php if (Auth::can('notifications.view')): ?>
      <a href="<?= APP_URL ?>notifications" <?= stripos($titulo, 'notificacion') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
          <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        Notificaciones
      </a>
    <?php endif; ?>
    <?php if (Auth::can('settings.view')): ?>
      <a href="<?= APP_URL ?>settings" <?= stripos($titulo, 'configuración') !== false ? 'class="active"' : '' ?>>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
          <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        </svg>
        Configuración
      </a>
    <?php endif; ?>
  </div>
</div>
</div>

<!-- rightSidebar  -->
<div class="sidebar right" id="rightSidebar">
  <div class="sidebar-header">
    <div class="user-container">
      <div id="avatar">
        <span id="avatar-status"></span>
      </div>
      <div class="user-info" id="user-info">
        <span id="user-name">
          <?php echo $_SESSION['trabajo_social']['username'] ?> </span>
        <span id="user-role">
          <?php echo $_SESSION['trabajo_social']['rol_nombre']; ?>
        </span>

      </div>
    </div>
    <div class="close-container">
      <a href="javascript:void(0)" id="rightCloseButton">
        <button class="btn-close" id="menu">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M18 6l-12 12" />
            <path d="M6 6l12 12" />
          </svg>
        </button>
      </a>
    </div>
  </div>
  <div id="sidebar-options">
    <a id="btn_exit" onclick="logout()">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-logout">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
        <path d="M9 12h12l-3 -3" />
        <path d="M18 15l3 -3" />
      </svg>
      Salir
    </a>
  </div>
</div>
</div>
<div id="searchBackdrop" class="search-backdrop"></div>
<!-- Modal de búsqueda instantánea -->

<div id="instantSearchContainer" class="instant-search-container">
  <div class="instant-search-modal">
    <div class="input-container">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon icon icon-tabler icons-tabler-outline icon-tabler-search">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
        <path d="M21 21l-6 -6" />
      </svg>
      <input type="text" id="modalSearchInput" class="instant-search-input" placeholder="Buscar" autofocus>
      <span class="clear-button" id="clearInstantSearch">×</span>
    </div>
    <div id="searchResultsList" class="search-results-list">
      <!-- Los resultados se insertarán aquí dinámicamente -->
    </div>
  </div>
</div>
<?php
require_once 'inactivity.php';
?>