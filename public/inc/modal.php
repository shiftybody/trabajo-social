<!-- Coloca esto donde se renderiza tu modal, por ejemplo, al final del body o via modal.php -->
<div id="inactivityWarningModal" class="modal" style="display:none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
  <div class="modal-content" style="background-color: #fff; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; text-align: center; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
    <h3 id="inactivityMessage" style="margin-top: 0;">Tu sesión está a punto de expirar.</h3>
    <p id="inactivityCountdown" style="font-size: 1.2em; margin: 20px 0;"></p>
    <button id="inactivityStayLoggedInBtn" style="padding: 10px 20px; margin-right: 10px; cursor: pointer;">Permanecer Conectado</button>
    <button id="inactivityLogoutBtn" style="padding: 10px 20px; cursor: pointer;">Cerrar Sesión</button>
  </div>
</div>