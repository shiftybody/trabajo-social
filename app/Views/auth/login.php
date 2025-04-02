<?php require_once APP_ROOT . 'public/inc/head.php' ?>
<style>
  body {
    overflow: hidden;
    background-color: #F7F7F7;
  }

  h1 {
    padding-bottom: 0.5rem;
  }

  p {
    justify-content: center;
    color: var(--gray-500, #677283);
    color: var(--gray-500, color(display-p3 0.4196 0.4471 0.502));
    text-align: center;
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 150%;
    /* 21px */
  }

  header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    width: 100%;
    height: 6.25rem;
  }

  main {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: calc(100vh - 12rem);
    /* Resta la altura del header */
  }

  header #imagotipo {
    width: 14rem;
    margin-left: 10%;
  }

  header #escudo {
    width: 4rem;
    margin-right: 10%;
  }

  main #section-container {
    display: flex;
    position: absolute;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 1rem;
    width: 28rem;
  }

  main #section-container #login-form {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
    width: 100%;
    height: 100%;
    background-color: #ffffff;
    border-radius: .5rem;
    padding: 2rem;
    gap: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.10), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  }

  main #section-container #logotipo {
    width: 4.5rem;
    filter: drop-shadow(0px 2px 4px rgba(0, 0, 0, 0.25));
  }

  main #section-container #login-form #inputs {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    gap: var(--2, 8px);
    align-self: stretch;
  }

  main #section-container #login-form #inputs #username-input,
  #password-input {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
    gap: var(--2, 8px);
    align-self: stretch;
  }

  div#check {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 0.5rem;
  }

  #login-error {
    color: red;
    margin-bottom: 10px;
    display: none;
  }

  @media (max-height: 800px) {
    main #section-container #logotipo {
      display: none;
    }

    main {
      height: calc(100vh - 6.25rem);
    }
  }

  @media (max-width: 500px) {
    main #section-container {
      width: 100%;
      padding: 0 5%;
      /* height: 26rem; */
    }

    header #imagotipo {
      width: 14rem;
      margin-left: 5%;
    }

    header #escudo {
      width: 4rem;
      margin-right: 5%;
    }
  }
</style>

<header>
  <img src="<?= APP_URL ?>public/images/imagotipo-neurodesarrollo.png" alt="imagotipo neurodesarrollo" id="imagotipo">
  <img src="<?= APP_URL ?>public/images/logo-unam.svg" alt="escudo UNAM" id="escudo">
</header>
<main>
  <section id="section-container">
    <img src="<?= APP_URL ?>public/images/logotipo-neurodesarrollo.png" alt="logitipo neurodesarrollo" id="logotipo">

    <form novalidate action="<?= APP_URL ?>login" method="POST" id="login-form" class="form-ajax">
      <div id="login-info">
        <h1>Iniciar Sesión</h1>
        <p>Ingresa tu usuario & contraseña para acceder a tu cuenta</p>
      </div>

      <div id="login-error"></div>

      <?php if (isset($_GET['expired']) && $_GET['expired'] == 1): ?>
        <div class="alert alert-warning">
          Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.
        </div>
      <?php endif; ?>

      <div id="inputs">
        <div id="username-input">
          <label for="username">Correo o Nombre de Usuario</label>
          <input type="text" name="username" id="username" placeholder="usuario@dominio.com" pattern="^((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}|[a-zA-Z0-9._@!#$%^&*+\-]{3,70})$">
        </div>
        <div id="password-input">
          <label for="password">Contraseña</label>
          <input type="password" name="password" id="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%]).{8,20}" placeholder="•••••••••••">
        </div>
      </div>

      <div id="check">
        <label for="recordar">Recordar Sesión</label>
        <input type="checkbox" name="recordar" id="recordar">
      </div>

      <button type="submit">Iniciar Sesión</button>
    </form>
  </section>
</main>

<!-- Scripts necesarios -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Capturar envío de formularios con clase form-ajax
    const forms = document.querySelectorAll('.form-ajax');

    forms.forEach(form => {
      form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const action = this.getAttribute('action');
        const method = this.getAttribute('method') || 'POST';

        // Mostrar indicador de carga si existe
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerText : '';

        if (submitBtn) {
          submitBtn.innerText = 'Procesando...';
          submitBtn.disabled = true;
        }

        // Ocultar mensaje de error anterior
        const errorElement = document.getElementById('login-error');
        if (errorElement) {
          errorElement.style.display = 'none';
          errorElement.textContent = '';
        }

        try {
          const response = await fetch(action, {
            method: method,
            body: formData,
            credentials: 'same-origin' // Importante para cookies de sesión
          });

          const data = await response.json();

          if (data.status === 'success') {
            // Si hay redirección, navegar a esa URL
            if (data.redirect) {
              window.location.href = data.redirect;
            }
          } else {
            // Mostrar mensaje de error si existe
            if (errorElement) {
              errorElement.textContent = data.message || 'Ha ocurrido un error';
              errorElement.style.display = 'block';
            } else {
              alert(data.message || 'Ha ocurrido un error');
            }
          }
        } catch (error) {
          console.error('Error en envío de formulario:', error);
          if (errorElement) {
            errorElement.textContent = 'Error de conexión. Intente nuevamente.';
            errorElement.style.display = 'block';
          } else {
            alert('Error de conexión. Intente nuevamente.');
          }
        } finally {
          // Restaurar el botón
          if (submitBtn) {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
          }
        }
      });
    });
  });
</script>