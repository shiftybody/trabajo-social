<?php
$errorCode = '401';
$errorTitle = ' Desautorizado';
$errorMessage = 'No estás autorizado para acceder a esta página.';
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<style>
  html,
  body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  main.error-page-container {
    flex-grow: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding-bottom: 4rem;
    background-color: #fdfdfd;
    color: #4a4a4a;
  }

  .error-content-wrapper {
    max-width: 700px;
    width: 100%;
  }

  .error-content-wrapper .error-code {
    font-size: 10rem;
    font-weight: 600;
    color: #363636;
    line-height: 0.8;
  }

  .error-content-wrapper .error-title {
    font-size: 2.5rem;
    color: #4a4a4a;
    font-weight: 500;
  }

  .error-content-wrapper .error-message {
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.1rem;
    color: #7a7a7a;
    line-height: 1.6;
  }

  .error-content-wrapper .error-link {
    display: inline-block;
    padding: 10px 25px;
    background-color: #5c87ff;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    transition: background-color 0.2s ease, transform 0.15s ease;
  }

  .error-content-wrapper .error-link:hover,
  .error-content-wrapper .error-link:focus {
    background-color: #4a70e0;
    transform: translateY(-2px);
    outline: none;
  }

  @media (max-width: 768px) {
    .error-content-wrapper .error-code {
      font-size: 7rem;
    }

    .error-content-wrapper .error-title {
      font-size: 2rem;
    }

    .error-content-wrapper .error-message {
      font-size: 1rem;
    }
  }

  @media (max-width: 480px) {
    .error-content-wrapper .error-code {
      font-size: 5rem;
    }

    .error-content-wrapper .error-title {
      font-size: 1.6rem;
    }
  }
</style>

<main class="error-page-container">
  <div class="error-content-wrapper">
    <div class="error-code"><?= htmlspecialchars($errorCode) ?></div>
    <h1 class="error-title"><?= htmlspecialchars($errorTitle) ?></h1>
    <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
  </div>
</main>