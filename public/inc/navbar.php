<style>
  header#app-header {
    box-shadow: 0px 1px 3px 0px color(display-p3 0 0 0 / 0.10), 0px 1px 2px -1px color(display-p3 0 0 0 / 0.10);
    position: relative;
    top: 0;
    width: 100%;
    height: 4rem;
    background: #F2F2F2;
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
    border: 1px solid #F2F2F2;
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
    /* Posición final: en pantalla */
    /* Transición para abrir (fade-in y deslizamiento) */
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
    /* text-lg/font-semibold */
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: 1.25;
    /* 27px */
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
    backdrop-filter: blur(1px);
    background-color: rgba(0, 0, 0, 0.25);
    z-index: 999;
    /* display: none; */
    /* Eliminado: Reemplazado por opacity/visibility para la transición */
    opacity: 0;
    visibility: hidden;
    /* Transición para cerrar (fade-out) */
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
        <input type="text" class="search" placeholder="Escribe / para navegar">
        <!-- <button type="button" id="uwu">
          <img src="<?php echo APP_URL; ?>public/icons/search-outline.svg" alt="logo">
        </button> -->
        <img src="<?php echo APP_URL; ?>public/icons/v-line.svg" alt="">
        <button type="button" id="uwu">
          <img src="<?php echo APP_URL; ?>public/icons/bell.svg" alt="">
        </button>
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
          <img src="<?= APP_URL ?>public/icons/x.svg" alt="">
        </button>
      </a>
    </div>
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

<script>
  // cuando se presione un tag img con clase logo
  document.querySelectorAll("img.logo").forEach((logo) => {
    logo.addEventListener("click", function() {
      // redirigir a la página principal
      window.location.href = "<?= APP_URL ?>dashboard";
    });
  });
</script>