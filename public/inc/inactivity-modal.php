<style>
  :root {
    --text: #000;
    --text-secondary: #666;
    --text-tertiary: #999;
    --bg: #fff;
    --bg-overlay: rgba(0, 0, 0, 0.5);
    --border: rgba(0, 0, 0, 0.08);
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.12);
    --danger: #ff3b30;
    --success: #34c759;
    --warning: #ff9500;
    --info: #007aff;
  }

  /* Overlay minimalista del modal de inactividad */
  #inactivityWarningModal {
    position: fixed;
    inset: 0;
    background: var(--bg-overlay);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    opacity: 0;
    transition: opacity 300ms ease;
  }

  #inactivityWarningModal.show {
    display: flex;
    opacity: 1;
  }

  /* Container del modal mejorado */
  .modal-content {
    background: var(--bg);
    border-radius: 16px;
    box-shadow: var(--shadow-lg);
    max-width: 420px;
    width: calc(100% - 32px);
    padding: 32px;
    transform: scale(0.95) translateY(10px);
    transition: all 300ms cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    text-align: center;
  }

  #inactivityWarningModal.show .modal-content {
    transform: scale(1) translateY(0);
  }

  /* Botón de cerrar minimalista */
  .modal-close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    cursor: pointer;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 150ms;
    color: var(--text-tertiary);
  }

  .modal-close-btn:hover {
    background: rgba(0, 0, 0, 0.06);
    color: var(--text-secondary);
  }

  .modal-close-btn svg {
    width: 18px;
    height: 18px;
  }

  /* Icono de advertencia */
  .warning-icon {
    width: 8px;
    height: 8px;
    background: var(--warning);
    border-radius: 50%;
    margin: 0 auto 20px;
    display: block;
  }

  /* Mensaje principal */
  #inactivityMessage {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
    margin: 0 0 8px 0;
    letter-spacing: -0.01em;
  }

  /* Contador grande */
  #inactivityCountdown {
    font-size: 48px;
    font-weight: 700;
    color: var(--text);
    line-height: 1;
    margin: 16px 0 8px 0;
    font-feature-settings: 'tnum';
    letter-spacing: -0.02em;
  }

  /* Leyenda */
  #legend {
    font-size: 14px;
    color: var(--text-secondary);
    margin: 0 0 24px 0;
    font-weight: 400;
    justify-content: center;
    ;
  }

  /* Botones del modal */
  .modal-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 24px;
  }

  .btn-modal {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 150ms;
    position: relative;
    min-width: 120px;
  }

  .btn-modal-primary {
    background: var(--text);
    color: var(--bg);
  }

  .btn-modal-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
  }

  .btn-modal-secondary {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border);
  }

  .btn-modal-secondary::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 8px;
    background: var(--text);
    opacity: 0;
    transition: opacity 150ms;
    z-index: -1;
  }

  .btn-modal-secondary:hover::after {
    opacity: 0.06;
  }

  .btn-modal-secondary:hover {
    transform: translateY(-1px);
  }

  .btn-modal:active {
    transform: scale(0.98);
  }

  .btn-modal:focus-visible {
    outline: 2px solid var(--text);
    outline-offset: 2px;
  }

  /* Animación del contador */
  @keyframes pulse {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
    }
  }

  .countdown-urgent {
    animation: pulse 1s ease-in-out infinite;
    color: var(--danger);
  }

  /* Responsive */
  @media (max-width: 640px) {
    .modal-content {
      padding: 24px;
      max-width: calc(100% - 24px);
    }

    #inactivityCountdown {
      font-size: 40px;
    }

    .modal-buttons {
      flex-direction: column;
    }

    .btn-modal {
      width: 100%;
    }
  }

  /* Reset para que no interfiera con otros estilos */
  #inactivityWarningModal * {
    box-sizing: border-box;
  }
</style>

<div id="inactivityWarningModal">
  <div class="modal-content">
    <button id="inactivityModalCloseBtn" class="modal-close-btn" aria-label="Cerrar">
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