<style>
  header#app-header {
    position: relative;
    top: 0;
    width: 100%;
    height: 4rem;
    background-color: red;
    border: 1px solid #D2D2D2;
    border: 1px solid color(display-p3 0.825 0.825 0.825);
    background: #F2F2F2;
    background: color(display-p3 0.95 0.95 0.95);
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
    width: 2.75rem;
    height: 2.75rem;
    padding: 0px 0px 28px 34px;
    cursor: pointer;
    border-radius: 100px;
    border: 1px solid var(--white, #FFF);
    border: 1px solid var(--white, color(display-p3 1 1 1));
  }

  #avatar-status {
    position: absolute;
    width: 1rem;
    height: 1rem;
    border-radius: var(--rounded-lg, 8px);
    border: 2px solid var(--white, #FFF);
    border: 2px solid var(--white, color(display-p3 1 1 1));
    background: var(--green-400, #00CB84);
    background: var(--green-400, color(display-p3 0.1922 0.7686 0.5529));
  }

  /* Estilos del menú lateral */
  .sidebar.left {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #fbfbfb;
    overflow-x: hidden;
    transition: 0.2s;
    z-index: 1000;
    border: 1px solid #d2d2d2;
    border-radius: 0 1rem 1rem 0;
  }

  .sidebar.right {
    height: 100%;
    width: 0;
    position: fixed;
    top: 0;
    right: 0;
    background-color: #fbfbfb;
    overflow-x: hidden;
    transition: 0.2s;
    z-index: 1000;
    border: 1px solid #d2d2d2;
    border-radius: 1rem 0 0 1rem;
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
    height: 30px;
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
    gap: 0.4rem;
  }


  #sidebar-options a:hover {
    background-color: #ececec;
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
</style>
<!-- header navbar -->
<header id="app-header">
  <section id="container">
    <div id="left-side">
      <button type="button" id="left-menu">
        <img src="<?php echo APP_URL; ?>public/icons/menu.svg" alt="">
      </button>
      <div id="info-container">
        <img src="<?php echo APP_URL; ?>public/images/logotipo-neurodesarrollo.png" alt="" class="logo">
        <span id="page-name"><?php echo $titulo; ?></span>
      </div>
    </div>
    <div id="right-side">
      <div id="search-container">
        <input type="text" class="search" placeholder="Escribe / para buscar">
        <button type="button" id="uwu">
          <img src="<?php echo APP_URL; ?>public/icons/search-outline.svg" alt="logo">
        </button>
        <img src="<?php echo APP_URL; ?>public/icons/v-line.svg" alt="">
        <button type="button" id="uwu">
          <img src="<?php echo APP_URL; ?>public/icons/bell.svg" alt="">
        </button>
      </div>
      <!-- avatar -->
      <?php
      if (is_file("./app/views/fotos/" . $_SESSION['foto'])) {

        echo '<style>
        #avatar {
          background: url(' . APP_URL . 'app/views/fotos/' . $_SESSION['foto'] . ') lightgray 50% / cover no-repeat;
        }
      </style>';
      } else {
        echo '<style>
        #avatar {
          background: url(' . APP_URL . 'public/photos/avatar.jpg) lightgray 50% / cover no-repeat;
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

<style>
  .contentblur {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* filter blur*/
    backdrop-filter: blur(1px);
    /* darker */
    background-color: rgba(0, 0, 0, 0.1);
    z-index: 999;
    display: none;
  }

  .option-icon {
    width: 1.25rem;
    height: auto;
  }
</style>
<div class="contentblur"></div>

<!-- left-sidebar -->
<div id="left-sidebar" class="sidebar left">
  <div class="sidebar-header">
    <img src="<?php echo APP_URL; ?>public/images/logotipo-neurodesarrollo.png" alt="" class="logo">
    <a href="javascript:void(0)" class="closebtn" id="left-closeButton">
      <button type="button" id="menu" class="closebtn">
        <img src="<?= APP_URL ?>public/icons/x.svg" alt="">
      </button>
    </a>
  </div>
  <div id="sidebar-options">
    <a href="<?= APP_URL ?>dashboard">
      <img src="<?= APP_URL ?>public/icons/home.svg" alt="" class="option-icon">
      Pagina Principal
    </a>
    <a href="<?= APP_URL ?>users">
      <img src="<?= APP_URL ?>public/icons/users.svg" class="option-icon">
      Usuarios
    </a>
  </div>
</div>
</div>

<!-- right-sidebar  -->
<div id="right-sidebar" class="sidebar right">
  <div class="sidebar-header">
    <div id="avatar">
      <span id="avatar-status"></span>
    </div>
    <a href="javascript:void(0)" class="closebtn" id="right-closeButton">
      <button type="button" id="menu" class="closebtn">
        <img src="<?= APP_URL ?>public/icons/x.svg" alt="">
      </button>
    </a>
  </div>
  <div id="sidebar-options">
    <a href="<?= APP_URL . "userUpdates" . $_SESSION['id'] . "/"; ?>">
      <img src="<?= APP_URL; ?>public/icons/user.svg" alt="" class="option-icon">
      Mi Perfil
    </a>
    <img src="<?= APP_URL; ?>public/icons/h-line.svg" alt="">
    <a href="<?= APP_URL; ?>logout" id="btn_exit">
      <img src="<?= APP_URL; ?>public/icons/logout.svg" class="option-icon">
      Salir
    </a>
  </div>
</div>
</div>

<script src="<?= APP_URL . "public/js/navbar.js" ?>"></script>