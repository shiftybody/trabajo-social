<script>
  const APP_URL = '<?= APP_URL ?>';
  const SESSION_EXPIRATION_TIMEOUT_SECONDS = <?= SESSION_EXPIRATION_TIMEOUT ?>;
  window.APP_URL = '<?= defined('APP_URL') ? APP_URL : '' ?>';
</script>

<script src="<?= APP_URL . 'public/js/modules/form-manager.js' ?>" type="module"></script>
<script src="<?= APP_URL . 'public/js/modules/form-validator.js' ?>" type="module"></script>

<?php if (strpos($_SERVER['REQUEST_URI'], 'login') !== false) : ?>
  <script src="<?= APP_URL . 'public/js/validations/auth-validations.js' ?>"></script>
<?php else : ?>
  <script src="<?= APP_URL . "public/js/navbar.js" ?>"></script>
  <script src="<?= APP_URL . 'public/js/main.js' ?>"></script>
  <script src="<?= APP_URL . 'public/js/inactivity.js' ?>"></script>
  <script src="<?= APP_URL . 'public/js/dialog.js' ?>"></script>
  <script src="<?= APP_URL . 'public/js/base-modal.js' ?>"></script>
  <script src="<?= APP_URL . 'public/js/modal-templates.js' ?>"></script>

  <?php if (strpos($_SERVER['REQUEST_URI'], 'users') !== false): ?>
    <script src="<?= APP_URL . 'public/js/modals/change-status-modal.js' ?>"></script>
    <script src="<?= APP_URL . 'public/js/modals/reset-password-modal.js' ?>"></script>
    <script src="<?= APP_URL . 'public/js/modals/user-details-modal.js' ?>"></script>
    <script src="<?= APP_URL . 'public/js/modals/delete-user-modal.js' ?>"></script>
  <?php endif; ?>

  <?php if (strpos($_SERVER['REQUEST_URI'], 'roles') !== false): ?>
    <script src="<?= APP_URL . 'public/js/modals/create-role-modal.js' ?>"></script>
    <script src="<?= APP_URL . 'public/js/modals/edit-role-modal.js' ?>"></script>
    <script src="<?= APP_URL . 'public/js/modals/delete-role-modal.js' ?>"></script>
  <?php endif; ?>

  <script src="<?= APP_URL . 'public/js/libs/datatables.min.js' ?>"></script>
<?php endif; ?>