<style>
  #inactivityWarningModal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background-color: rgba(0, 0, 0, 0.16);
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .modal-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    background-color: #fff;
    padding: 1.5rem;
    border: none;
    max-width: 480px;
    text-align: center;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    position: relative;
  }

  .modal-close-btn {
    position: absolute;
    top: 10px;
    right: 1.25rem;
    font-size: 2rem;
    font-weight: var(--font-weight-thin);
    color: #888888;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    line-height: 1;
  }

  .modal-close-btn:hover {
    color: #555555;
  }

  #inactivityMessage {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.15rem;
    /* Ajustado para el mensaje */
    font-weight: 500;
    color: #333333;
  }

  #inactivityCountdown {
    display: flex;
    justify-content: center;
    font-size: 3.8rem;
    font-weight: 600;
    color: #333333;
    line-height: 1;
  }

  #legend {
    display: flex;
    justify-content: center;
    font-size: 1.2rem;
    color: #888888;
  }

  .modal-buttons {
    display: flex;
    justify-content: center;
    gap: 1.3rem;
    margin-top: 1.5rem;
  }

  .btn-modal {
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    border: 1px solid transparent;
    transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    text-decoration: none;
    display: inline-block;
    min-width: 150px;
  }

  .btn-modal-primary {
    background-color: #007bff;
    /* Azul primario */
    color: white;
    border-color: #007bff;
  }

  .btn-modal-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
  }

  .btn-modal-secondary {
    border-radius: var(--rounded-lg, 8px);
    border: 1px solid var(--gray-200, #E5E7EB);
    border: 1px solid var(--gray-200, color(display-p3 0.898 0.9059 0.9216));
    background: var(--white, #FFF);
    background: var(--white, color(display-p3 1 1 1));
  }

  .btn-modal-secondary:hover {
    background-color: #E5E7EB;
    border-color: #E5E7EB;
  }

  button {
    height: 3rem;
  }
</style>

<div id="inactivityWarningModal" class="modal" style="display: none;">
  <div class="modal-content">
    <button id="inactivityModalCloseBtn" class="modal-close-btn closebtn" aria-label="Cerrar">&times;</button>
    <h3 id="inactivityMessage"></h3>
    <p id="inactivityCountdown"></p>
    <p id="legend"> segundos </p>
    <div class="modal-buttons">
      <button id="inactivityStayLoggedInBtn" class="btn-modal btn-modal-primary">Continuar</button>
      <button id="inactivityLogoutBtn" class="btn-modal btn-modal-secondary">Cerrar Sesión</button>
    </div>
  </div>
</div>