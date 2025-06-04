<?php

use App\Core\Auth;

$avatarFilename = $_SESSION['trabajo_social']['avatar'] ?: '';
$avatarPath = APP_ROOT . 'public/photos/thumbnail/' . $avatarFilename;
$avatarUrl = APP_URL . 'public/photos/thumbnail/' . $avatarFilename;
$defaultAvatarUrl = APP_URL . 'public/photos/default.jpg';

// Validamos si el archivo existe y que no sea una cadena vacía
$finalAvatarUrl = (file_exists($avatarPath) && !empty($avatarFilename))
  ? $avatarUrl
  : $defaultAvatarUrl;
?>
<style>
  header#app-header {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.10), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    position: relative;
    top: 0;
    width: 100%;
    height: 4.1rem;
    background: #F2F2F2;
    border-bottom: 1px solid var(--gray-300);
  }

  header #container {
    display: flex;
    height: 100%;
    width: 100%;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 0 3rem;
  }

  header #container #left-side {
    display: flex;
    align-items: center;
    gap: 1.35rem;
  }

  header #container #left-side .logo {
    width: 3.125rem;
    height: auto;
  }

  .logo {
    width: 3.125rem;
    height: auto;
  }

  header #container #left-side .logo:hover {
    cursor: pointer;
  }

  header #container #left-side #page-name {
    color: #1F2329;
    color: color(display-p3 0.1255 0.1373 0.1569);
    text-align: center;
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
    letter-spacing: -0.64px;
  }

  header #container #left-side #info-container {
    display: flex;
    align-items: center;
    gap: .25rem;
  }

  header #container #right-side #search-container {
    display: flex;
    gap: 0.625rem;
  }

  header #container #right-side {
    display: flex;
    gap: 0.75rem;
  }

  input.search {
    display: flex;
    padding: var(--3, 12px) var(--4, 16px);
    align-items: center;
    gap: var(--25, 10px);
    align-self: stretch;
    border-radius: var(--rounded-lg, 8px);
    border: 1px solid var(--gray-300, #CFD5DD);
    border: 1px solid var(--gray-300, color(display-p3 0.8196 0.8353 0.8588));
    background: #F2F2F2;
    background: color(display-p3 0.95 0.95 0.95);
    color: var(--gray-500, var(--gray-500, #677283));
    color: var(--gray-500, var(--gray-500, color(display-p3 0.4196 0.4471 0.502)));

    /* leading-tight/text-sm/font-normal */
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 125%;
    /* 17.5px */
  }

  #avatar {
    display: flex;
    width: 3rem;
    height: 3rem;
    padding: 0px 0px 28px 34px;
    cursor: pointer;
    border-radius: 100px;
    border: 1px solid #F2F2F2;
    background: url('<?= htmlspecialchars($finalAvatarUrl, ENT_QUOTES, 'UTF-8') ?>') lightgray 50% / cover no-repeat;
  }

  #avatar-status {
    position: absolute;
    width: 1rem;
    height: 1rem;
    border-radius: var(--rounded-lg, 8px);
    border: 2px solid #F2F2F2;
    background: var(--green-400, #00CB84);
  }

  /* Estilos del menú lateral */
  .sidebar.left {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    height: 100%;
    /* width: 0; */
    /* Eliminado: el ancho ahora es fijo, la visibilidad se controla con transform/opacity */
    width: 20rem;
    /* Ancho deseado cuando está abierto */
    position: fixed;
    top: 0;
    left: 0;
    background-color: #fbfbfb;
    overflow-x: hidden;
    /* Importante para que el contenido no se vea durante la transformación */
    /* transition: 0.05s; */
    /* Eliminado: reemplazado por la nueva transición */
    z-index: 1000;
    border: 1px solid #d2d2d2;
    border-radius: 0 1rem 1rem 0;

    /* Estilos de animación */
    opacity: 0;
    visibility: hidden;
    transform: translateX(-100%);
    /* Posición inicial: fuera de pantalla a la izquierda */
    /* Transición para cerrar (fade-out y deslizamiento) */
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s 0.2s;
  }

  .sidebar.left.open {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s 0s;
  }

  .sidebar.right {
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    height: 100%;
    /* width: 0; */
    /* Eliminado */
    width: 20rem;
    /* Ancho deseado cuando está abierto */
    position: fixed;
    top: 0;
    right: 0;
    background-color: #fbfbfb;
    overflow-x: hidden;
    /* transition: 0.05s; */
    /* Eliminado */
    z-index: 1000;
    border: 1px solid #d2d2d2;
    border-radius: 1rem 0 0 1rem;

    /* Estilos de animación */
    opacity: 0;
    visibility: hidden;
    transform: translateX(100%);
    /* Posición inicial: fuera de pantalla a la derecha */
    /* Transición para cerrar (fade-out y deslizamiento) */
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s 0.2s;
  }

  .sidebar.right.open {
    opacity: 1;
    visibility: visible;
    transform: translateX(0);
    /* Posición final: en pantalla */
    /* Transición para abrir (fade-in y deslizamiento) */
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s 0s;
  }

  .sidebar a {
    color: var(--gray-600, #465566);
    color: var(--gray-600, color(display-p3 0.2941 0.3333 0.3882));
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 150%;
  }


  #sidebar-options a {
    display: flex;
    min-width: 200px;
    height: 3rem;
    padding: var(--25, 10px) var(--5, 20px) var(--25, 10px) var(--3, 12px);
    align-items: center;
    gap: .5rem;
    flex-shrink: 0;
    border-radius: var(--rounded-lg, 8px);
    background: #fbfbfb;
  }

  #sidebar-options {
    display: flex;
    flex-direction: column;
    padding: 1rem 1rem 1rem 1.75rem;
    gap: 0.2rem;
  }


  #sidebar-options a:hover {
    background-color: #ececec;
    color: #1F2329;
  }

  .sidebar .sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 4rem;
    padding: 1rem 1rem 1rem 1.75rem;
  }

  .sidebar .sidebar-header .logo:hover {
    cursor: pointer;
  }

  .user-container {
    display: flex;
    ;
  }

  .close-container {
    display: flex;
  }

  .user-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-left: 1rem;
  }

  .user-info #user-name {
    color: var(--gray-900, var(--gray-900, #0C192A));
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: 1.25;
  }

  .user-info #user-role {
    color: var(--gray-500, #677283);
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 1.25;
  }

  .contentblur {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
    background-color: rgba(0, 0, 0, 0.2);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0s 0.2s;
  }

  .contentblur.active {
    opacity: 1;
    visibility: visible;
    /* Transición para abrir (fade-in) */
    transition: opacity 0.2s ease, visibility 0s 0s;
  }

  .option-icon {
    width: 1.25rem;
    height: auto;
  }

  .search-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    z-index: 998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.2s ease, visibility 0s 0.2s;
  }

  .search-backdrop.active {
    opacity: 1;
    visibility: visible;
    transition: opacity 0.2s ease, visibility 0s 0s;
  }

  .instant-search-container {
    position: fixed;
    top: 10%;
    left: 50%;
    transform: translateX(-50%) scale(0.95);
    max-width: 600px;
    width: 90%;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .instant-search-container.active {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) scale(1);
  }

  .instant-search-modal {
    max-height: 70vh;
    display: flex;
    flex-direction: column;
    padding: 1.5rem;
  }

  /* Contenedor del input con el mismo estilo que index.php */
  .instant-search-modal .input-container {
    position: relative;
    display: block;
    width: 100%;
  }

  .instant-search-modal .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    stroke: #465566;
    pointer-events: none;
  }

  #modalSearchInput {
    padding-left: 40px;
    padding-right: 20px !important;
  }

  .instant-search-input {
    width: 100%;
    border: 1px solid var(--gray-300);
    border-radius: var(--rounded-lg);
    font-size: 14px;
    font-weight: 400;
    line-height: 125%;
    background: var(--gray-50);
    color: var(--gray-900);
    transition: all 0.2s ease;
  }

  .instant-search-input::placeholder {
    color: var(--gray-500);
  }

  /* Botón clear con el mismo estilo */
  .instant-search-modal .clear-button {
    position: absolute;
    right: 1rem;
    top: 46%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 2rem;
    font-weight: 300;
    color: #aaa;
    display: none;
    line-height: 1;
    transition: color 0.2s ease;
  }

  .instant-search-modal .clear-button:hover {
    color: #666;
  }

  /* Mostrar el botón clear cuando hay texto */
  .instant-search-input:not(:placeholder-shown)+.clear-button {
    display: inline;
  }

  /* Lista de resultados */
  .search-results-list {
    overflow-y: auto;
    max-height: calc(70vh - 100px);
    margin: 0 -1.5rem;
    padding: 1.5rem;
  }

  .search-result-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    color: var(--gray-700);
    text-decoration: none;
    transition: all 0.15s ease;
    gap: 0.75rem;
    border-bottom: 1px solid var(--gray-300);
  }

  .search-result-item:last-child {
    border-bottom: none;
  }

  .search-result-item:hover {
    background: #f8f9fa;
    color: var(--gray-900);
  }

  .search-result-item:focus {
    outline: none;
    background: #f3f4f6;
    box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.2);
  }

  .search-result-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: var(--gray-500);
  }

  .search-result-item:hover .search-result-icon {
    color: var(--gray-700);
  }

  .search-result-text {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
  }

  .search-result-text strong {
    font-weight: 700;
    color: var(--gray-900);
  }

  .search-result-section {
    padding: 0.5rem 0;
  }

  .search-result-section:first-child {
    padding-top: 0;
  }

  .search-section-title {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--gray-500);
    padding: 0.5rem 1.5rem;
    letter-spacing: 0.5px;
    background: #f9fafb;
    margin: 0.5rem 0;
  }

  .search-result-section:first-child .search-section-title:first-child {
    margin-top: 0;
  }

  .no-results {
    text-align: center;
    color: var(--gray-500);
    font-size: 14px;
    margin-top: 1.5rem;
  }

  .search-result-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 4px;
    background: #e3f2fd;
    color: #1976d2;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Responsive */
  @media (max-width: 640px) {
    .instant-search-container {
      width: 95%;
      top: 5%;
    }

    .instant-search-modal {
      padding: 1rem;
    }

    .search-results-list {
      max-height: calc(80vh - 80px);
      margin: 0 -1rem;
    }

    .search-result-item,
    .search-section-title {
      padding-left: 1rem;
      padding-right: 1rem;
    }
  }
</style>

<!-- header navbar -->
<header id="app-header">
  <section id="container">
    <div id="left-side">
      <button type="button" id="left-menu">
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
          <input type="text" id="mainSearchInput" class="search" placeholder="Escribe / para navegar">
        <?php endif; ?>
        <?php if (Auth::can('notifications.view')): ?>
          <button type="button" id="uwu">
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

<!-- left-sidebar -->
<div id="left-sidebar" class="sidebar left">
  <div class="sidebar-header">
    <img src="<?php echo APP_URL; ?>public/images/logotipo-neurodesarrollo.png" alt="" class="logo">
    <a href="javascript:void(0)" class="closebtn" id="left-closeButton">
      <button type="button" id="menu" class="closebtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M18 6l-12 12" />
          <path d="M6 6l12 12" />
        </svg>
      </button>
    </a>
  </div>
  <div id="sidebar-options">
    <?php if (Auth::can('home.view')): ?>
      <a href="<?= APP_URL ?>home">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-layout-home">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
          <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
          <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
          <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
        </svg>
        Inicio
      </a>
    <?php endif; ?>
    <?php if (Auth::can('users.view')): ?>
      <a href="<?= APP_URL ?>users">
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
    <?php if (Auth::can('roles.view')): ?>
      <a href="<?= APP_URL ?>roles">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shield">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
        </svg>
        Roles
      </a>
    <?php endif; ?>
    <?php if (Auth::can('patients.view')): ?>
      <a href="<?= APP_URL ?>patients ">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-horse-toy">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M3.5 17.5c5.667 4.667 11.333 4.667 17 0" />
          <path d="M19 18.5l-2 -8.5l1 -2l2 1l1.5 -1.5l-2.5 -4.5c-5.052 .218 -5.99 3.133 -7 6h-6a3 3 0 0 0 -3 3" />
          <path d="M5 18.5l2 -9.5" />
          <path d="M8 20l2 -5h4l2 5" />
        </svg>
        Pacientes
      </a>
    <?php endif; ?>
    <?php if (Auth::can('donations.view')): ?>
      <a href="<?= APP_URL ?>donations">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-hearts">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M14.017 18l-2.017 2l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 0 1 8.153 5.784" />
          <path d="M15.99 20l4.197 -4.223a2.81 2.81 0 0 0 0 -3.948a2.747 2.747 0 0 0 -3.91 -.007l-.28 .282l-.279 -.283a2.747 2.747 0 0 0 -3.91 -.007a2.81 2.81 0 0 0 -.007 3.948l4.182 4.238z" />
        </svg>
        Donaciones
      </a>
    <?php endif; ?>
    <?php if (Auth::can('reports.view')): ?>
      <a href="<?= APP_URL ?>reports">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
          <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
        </svg>
        Reportes
      </a>
    <?php endif; ?>
    <?php if (Auth::can('stats.view')): ?>
      <a href="<?= APP_URL ?>stats">
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
      <a href="<?= APP_URL ?>notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
          <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        Notificaciones
      </a>
    <?php endif; ?>
    <?php if (Auth::can('settings.view')): ?>
      <a href="<?= APP_URL ?>settings">
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

<!-- right-sidebar  -->
<div class="sidebar right" id="right-sidebar">
  <div class="sidebar-header">
    <div class="user-container">
      <div id="avatar">
        <span id="avatar-status"></span>
      </div>
      <div class="user-info" id="user-info">
        <span id="user-name">
          <?php echo $_SESSION['trabajo_social']['username'] ?> </span>
        <span id="user-role">
          <?php echo $_SESSION['trabajo_social']['rol_descripcion']; ?>
        </span>

      </div>
    </div>
    <div class="close-container">
      <a href="javascript:void(0)" class="closebtn" id="right-closeButton">
        <button type="button" id="menu" class="closebtn">
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
    <?php if (Auth::can('profile.view')): ?>
      <a href="<?= APP_URL . "users/profile/" . $_SESSION['id'] . "/"; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user-square-rounded">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" />
          <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
          <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05" />
        </svg>
        Mi Perfil
      </a>
    <? endif; ?>
    <hr>
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
<?php require_once APP_ROOT . 'public/inc/scripts.php'; ?>
<script>
  // cuando se presione un tag img con clase logo
  document.querySelectorAll("img.logo").forEach((logo) => {
    logo.addEventListener("click", function() {
      // redirigir a la página principal
      window.location.href = "<?= APP_URL ?>home";
    });
  });

  async function logout() {

    sidebarAvatar.classList.remove("open");
    contentBlur.classList.remove("active");

    const confirmacion = await CustomDialog.confirm(
      'Cerrar Sesión',
      `¿Está seguro de que deseas cerrar sesión?`,
      'Cerrar Sesión',
      'Cancelar'
    );

    if (confirmacion) {
      try {
        fetch("<?= APP_URL ?>api/logout", {
            method: "POST",
            headers: {
              "Accept": "application/json"
            },
            credentials: "same-origin",
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
          })
          .then(data => {
            console.log(data);
            if (data.status === 'success') {
              window.location.href = data.redirect;
            } else {
              CustomDialog.error('Error', data.message);
            }
          });

      } catch (error) {
        console.error('Error en la petición fetch:', error);
        CustomDialog.error('Error de Red', 'Ocurrió un problema al intentar conectar con el servidor.');
      }
    }
  }
</script>



<?php
require_once 'inactivity-modal.php'
?>