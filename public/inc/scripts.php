<script>
  const APP_URL = '<?= APP_URL ?>';
  const SESSION_EXPIRATION_TIMEOUT_SECONDS = <?= SESSION_EXPIRATION_TIMEOUT ?>;
  window.APP_URL = '<?= defined('APP_URL') ? APP_URL : '' ?>';
</script>

<?php if (strpos($_SERVER['REQUEST_URI'], 'login') !== false) : ?>
  <script src="<?= APP_URL . 'public/js/login.js' ?>"></script>
<?php else : ?>
  <script src="<?= APP_URL . "public/js/navbar.js" ?>"></script>
  <script src="<?= APP_URL . 'public/js/main.js' ?>"></script>
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

  <script src="<?= APP_URL . 'public/js/libs/datatables.min.js' ?>"></script>
<?php endif; ?>