<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= APP_NAME ?></title>
<link rel="icon" href="<?= APP_URL ?>public/images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="<?= APP_URL ?>public/css/base.css">
<link rel="stylesheet" href="<?= APP_URL ?>public/css/common.css">

<?php if (strpos($_SERVER['REQUEST_URI'], 'login') !== false): ?>
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/views/login.css">
<?php else: ?>
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/dialog.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/modal.css">
<?php endif; ?>