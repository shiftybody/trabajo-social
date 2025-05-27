<script>
  const APP_URL = '<?= rtrim(APP_URL, '/') ?>/'; // Asegura una sola barra al final
  const SESSION_EXPIRATION_TIMOUT_SECONDS = <?= SESSION_EXPIRATION_TIMOUT ?>;
  window.APP_URL = '<?= defined('APP_URL') ? APP_URL : '' ?>';
</script>
<script src=" <?= APP_URL . 'public/js/main.js' ?>"></script>
<script src=" <?= APP_URL . 'public/js/inactivity.js' ?>"></script>