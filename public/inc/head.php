<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= APP_NAME ?></title>
<link rel="icon" href="<?= APP_URL ?>public/images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="<?= APP_URL ?>public/css/base.css">
<link rel="stylesheet" href="<?= APP_URL ?>public/css/global.css">

<script>
  const APP_URL = '<?= rtrim(APP_URL, '/') ?>/'; // Asegura una sola barra al final
  const SESSION_EXPIRATION_TIMOUT_SECONDS = <?= SESSION_EXPIRATION_TIMOUT ?>; 
   window.APP_URL = '<?= defined('APP_URL') ? APP_URL : '' ?>';
</script>
<link rel="preload" href="<?= APP_URL . 'public/css/datatables.min.css' ?>" as="style">
<link rel="stylesheet" href="<?= APP_URL . 'public/css/datatables.min.css' ?>">