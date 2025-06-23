<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?></title>
  <link rel="icon" href="<?= APP_URL ?>public/images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/base.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/common.css">

  <?php if (strpos($_SERVER['REQUEST_URI'], 'login') !== false): ?>
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/login.css">
  <?php else: ?>

    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/navbar.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/inactivity.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/dialog.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/modal.css">

    <?php if (strpos($_SERVER['REQUEST_URI'], 'home') !== false): ?>
      <!-- Estilos del home -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/home.css">
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'error') !== false): ?>
      <!-- Estilos de Error -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/errors.css">
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'users') !== false): ?>
      <!-- Estilos de Usuarios -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/users.css">
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'roles') !== false): ?>
      <!-- Estilos de Roles | Permisos -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/roles.css">
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'studies') !== false): ?>
      <!-- Estilos de Estudios -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/studies.css">
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'settings') !== false): ?>
      <!-- Estilos de Configuración -->
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/settings.css">
    <?php endif; ?>

  <?php endif; ?>
</head>