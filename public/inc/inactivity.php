<div id="inactivityWarningModal">
  <div class="modal-content">
    <button id="inactivityModalCloseBtn" class="btn-close" aria-label="Cerrar">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 6l-12 12" />
        <path d="M6 6l12 12" />
      </svg>
    </button>

    <div class="warning-icon"></div>

    <h3 id="inactivityMessage">Tu sesión expirará en</h3>
    <div id="inactivityCountdown">30</div>
    <p id="legend">segundos</p>

    <div class="modal-buttons">
      <button id="inactivityStayLoggedInBtn" class="btn-modal btn-modal-primary">
        Continuar sesión
      </button>
      <button id="inactivityLogoutBtn" class="btn-modal btn-modal-secondary">
        Cerrar sesión
      </button>
    </div>
  </div>
</div>