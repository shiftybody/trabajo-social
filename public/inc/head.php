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

    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/dialog.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/modal.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/navbar.css">
    <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/inactivity.css">

    <!-- Estilos del home -->
    <?php if (strpos($_SERVER['REQUEST_URI'], 'home') !== false): ?>
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/home.css">
    <?php endif; ?>

    <!-- Estilos de Error -->
    <?php if (strpos($_SERVER['REQUEST_URI'], 'error') !== false): ?>
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/errors.css">
    <?php endif; ?>

    <!-- Estilos de Usuarios -->
    <?php if (strpos($_SERVER['REQUEST_URI'], 'users') !== false): ?>
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/users.css">
    <?php endif; ?>

    <!-- Estilos de Roles | Permisos -->
    <?php if (strpos($_SERVER['REQUEST_URI'], 'roles') !== false): ?>
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/libs/datatables.min.css">
      <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/roles.css">
    <?php endif; ?>

  <?php endif; ?>
</head>