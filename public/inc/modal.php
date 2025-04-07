<?php

/**
 * Plantilla para el modal de advertencia de sesión por inactividad
 * Este archivo debe ubicarse en templates/session-warning-modal.php
 */
?>
<div>
  <style>
    #session-warning-modal {
      font-family: 'Inter', sans-serif;
      display: flex;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      display: flex;
      padding: 19px 22.555px 18px 23px;
      justify-content: center;
      align-items: center;
      border-radius: 8px;
      background: #FAFAFA;
      background: color(display-p3 0.9821 0.9821 0.9821);
    }

    .message {
      display: flex;
      width: 25.5rem;
      flex-direction: column;
      align-items: center;
      gap: 13px;
      flex-shrink: 0;
    }

    .message h3 {
      margin: 0;
      color: var(--gray-700, #314155);
      color: var(--gray-700, color(display-p3 0.2157 0.2549 0.3176));
      font-size: 24px;
      font-style: normal;
      font-weight: 600;
      line-height: 150%;
      /* 36px */
    }

    .message p {
      margin: 0;
      color: var(--gray-900, var(--gray-900, #0C192A));
      color: var(--gray-900, var(--gray-900, color(display-p3 0.0667 0.098 0.1569)));

      font-size: 14px;
      font-style: normal;
      font-weight: 400;
      line-height: 125%;

      display: block;
      margin-bottom: 10px;
      /* 17.5px */
    }


    #session-countdown {
      margin: 10px 0;
      font-weight: bold;
      font-size: 1.2em;
    }

    .buttons {
      display: flex;
      padding: var(--0, 0px);
      align-items: flex-start;
      gap: var(--5, 20px);
      align-self: stretch;
    }

    button {
      display: flex;
      padding: var(--25, 10px) var(--5, 20px);
      justify-content: center;
      align-items: center;
      gap: var(--2, 8px);
      flex: 1 0 0;
    }

    button#keep-session-button {
      border-radius: var(--rounded-lg, 8px);
      background: #000;
      background: color(display-p3 0 0 0);

      background: #000;
      background: color(display-p3 0 0 0);
      color: var(--white, var(--white, #FFF));
      color: var(--white, var(--white, color(display-p3 1 1 1)));

      /* text-sm/font-medium */
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 150%;
      /* 21px */
    }

    button#logout-now-button {
      border-radius: var(--rounded-lg, 8px);
      border: 1px solid var(--gray-200, #E5E7EB);
      border: 1px solid var(--gray-200, color(display-p3 0.898 0.9059 0.9216));
      background: var(--white, #FFF);
      background: var(--white, color(display-p3 1 1 1));
      color: var(--gray-900, #0C192A);
      color: var(--gray-900, color(display-p3 0.0667 0.098 0.1569));

      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 150%;
    }
  </style>
  <div id="session-warning-modal">
    <div class="modal-content">
      <div class="message">
        <h3>Su sesión está a punto de expirar</h3>

        <p>Debido a inactividad, su sesión se cerrará en&nbsp;<span id="session-countdown">30</span>&nbsp;segundos.</p>

        <div class="buttons">
          <button id="keep-session-button">
            Mantener sesión activa
          </button>

          <button id="logout-now-button">
            Cerrar sesión ahora
          </button>
        </div>
      </div>
    </div>
  </div>

</div>