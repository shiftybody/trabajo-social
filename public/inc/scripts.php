<script>
  const APP_URL = '<?= rtrim(APP_URL, '/') ?>';
  const SESSION_EXPIRATION_TIMEOUT_SECONDS = <?= SESSION_EXPIRATION_TIMEOUT ?>;
  window.APP_URL = '<?= defined('APP_URL') ? APP_URL : '' ?>';
</script>

<script src="<?= APP_URL . 'public/js/main.js' ?>"></script>
<script src="<?= APP_URL . "public/js/navbar.js" ?>"></script>
<script src="<?= APP_URL . 'public/js/ajax.js' ?>"></script>
<script src="<?= APP_URL . 'public/js/inactivity.js' ?>"></script>
<script src="<?= APP_URL . 'public/js/dialog.js' ?>"></script>

<script src="<?= APP_URL . 'public/js/base-modal.js' ?>"></script>
<script src="<?= APP_URL . 'public/js/modal-templates.js' ?>"></script>
<?php
$modalFiles = glob(APP_ROOT . 'public/js/modals/*.js');
foreach ($modalFiles as $file) {
  $fileName = basename($file);
  echo "<script src=\"" . APP_URL . "public/js/modals/{$fileName}\"></script>\n";
}
?>

<!-- TODO: Mejorar la logica para que solo muestre los archivos de modals para los que el usuario tiene permisos -->