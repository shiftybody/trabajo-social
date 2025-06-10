<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<div class="container role-table-container">
  <div class="tools">
    <form class="filter_form" id="filter_form">
      <div class="input-container">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon icon icon-tabler icons-tabler-outline icon-tabler-search">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
          <path d="M21 21l-6 -6" />
        </svg>
        <input class="matching-search" name="matchingColumn" id="matchingInput" placeholder="Buscar roles">
        <span class="clear-button" id="clearButton">×</span>
      </div>
    </form>

    <?php if (\App\Core\Auth::can('roles.create')): ?>
      <button type="submit" onclick="crearRol()">Nuevo Rol</button>
    <?php endif; ?>
  </div>

  <div class="table-container" id="table-container">
    <!-- Loading inicial -->
    <div class="table-loading-container" id="table-loading">
      <div class="table-loading">
        <div class="table-spinner"></div>
        <p>Cargando roles...</p>
      </div>
    </div>

    <!-- Tabla (oculta inicialmente) -->
    <table id="roles-table" class="hover nowrap cell-borders" style="display: none;">
      <thead>
        <tr>
          <th class="dt-head-left">ROL</th>
          <th class="dt-head-left">USUARIOS</th>
          <?php if (\App\Core\Auth::canAny(['permissions.assign', 'permissions.view', 'roles.delete'])): ?>
            <th class="dt-head-left">ACCIONES</th>
          <?php endif; ?>
        </tr>
      </thead>
    </table>
  </div>
</div>
<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>
<script>
  // Variables globales
  let table;
  let isFirstLoad = true;
  let jsTooltipElement = null;
  let currentTooltipButton = null;

  // Funciones de loading 
  function showTableLoading(message = 'Cargando roles...') {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');

    if (!loadingContainer) {
      const loadingHTML = `
        <div class="table-loading-container" id="table-loading">
          <div class="table-loading">
            <div class="table-spinner"></div>
            <p>${message}</p>
          </div>
        </div>
      `;
      container.insertAdjacentHTML('afterbegin', loadingHTML);
    } else {
      const messageEl = loadingContainer.querySelector('p');
      if (messageEl) messageEl.textContent = message;
      loadingContainer.style.display = 'flex';
    }

    setTimeout(() => {
      const loading = document.getElementById('table-loading');
      if (loading) loading.classList.add('show');
    }, 10);

    const tableWrapper = document.getElementById('roles-table_wrapper');
    if (tableWrapper) {
      tableWrapper.classList.remove('show');
      tableWrapper.style.display = 'none';
    }

    container.classList.add('loading');
  }

  function hideTableLoading() {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');
    const tableWrapper = document.getElementById('roles-table_wrapper');

    const loaderFadeOutDuration = 300;
    let delayForTableAnimationStart = 10;

    if (loadingContainer) {
      const isActiveLoader = window.getComputedStyle(loadingContainer).display !== 'none';

      if (isActiveLoader) {
        loadingContainer.classList.remove('show');
        setTimeout(() => {
          loadingContainer.style.display = 'none';
        }, loaderFadeOutDuration);
        delayForTableAnimationStart = loaderFadeOutDuration;
      } else {
        loadingContainer.style.display = 'none';
      }
    }

    container.classList.remove('loading');

    if (tableWrapper) {
      tableWrapper.style.display = '';
      if (table) {
        table.columns.adjust();
      }

      setTimeout(() => {
        tableWrapper.classList.add('show');
      }, delayForTableAnimationStart);
    }
  }

  function showTableError(message = 'Error al cargar los datos') {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');

    if (loadingContainer) {
      loadingContainer.innerHTML = `
        <div class="table-error">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          <h3>Error</h3>
          <p>${message}</p>
          <button class="btn-reload" onclick="loadData()">Reintentar</button>
        </div>
      `;
      loadingContainer.style.display = 'flex';
      loadingContainer.classList.add('show');
    }
  }

  // Inicializar DataTable
  document.addEventListener('DOMContentLoaded', () => {
    showTableLoading('Cargando roles...');
    document.getElementById('roles-table').style.display = '';

    table = new DataTable('#roles-table', {
      layout: {
        topStart: null,
        buttomStart: null,
        buttomEnd: null,
      },
      columnDefs: [{
        targets: "_all",
        className: 'dt-body-left'
      }],
      language: {
        "zeroRecords": "No se encontraron roles",
        "emptyTable": "Aún no hay roles, crea uno nuevo aquí",
        "info": "Mostrando _START_ a _END_ de _TOTAL_ roles",
        "infoEmpty": "Mostrando 0 a 0 de 0 roles",
        "infoFiltered": "(filtrado de _MAX_ roles totales)",
        "processing": '<div class="table-spinner"></div>Procesando...'
      },
      initComplete: function() {
        loadData();
      }
    });

    // Configurar búsqueda
    const matchingInput = document.getElementById('matchingInput');
    matchingInput.addEventListener('input', () => {
      table.search(matchingInput.value.trim()).draw();
    });

    // Configurar botón limpiar
    const clearButton = document.getElementById('clearButton');
    clearButton.addEventListener('click', () => {
      matchingInput.value = '';
      matchingInput.focus();
      clearButton.style.display = 'none';
      table.search('').draw();
    });

    matchingInput.addEventListener('input', () => {
      clearButton.style.display = matchingInput.value ? 'inline' : 'none';
    });
  });

  async function loadData() {
    try {
      const response = await fetch('<?= APP_URL ?>api/roles');

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.status === "success" && data.data) {
        table.clear();

        let incremental = 1;
        data.data.forEach(item => {

          const isAdminRole = item.rol_nombre === 'Administrador' || item.rol_id === 1;
          let buttonsHtml = '<div class="action-buttons">';

          <?php if (\App\Core\Auth::canAny(['permissions.assign', 'permissions.view'])): ?>
            buttonsHtml += `
            <button type="button" 
                    class="editar ${isAdminRole ? 'protected-btn' : ''}"
                    ${isAdminRole ? 'disabled' : `onClick="gestionarPermisos(${item.rol_id})"`} 
                    title="${isAdminRole ? 'No se puede editar el rol de administrador' : 'Gestionar Permisos'}">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                <path d="M13.5 6.5l4 4" />
              </svg>
            </button>`;
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('roles.delete')): ?>
            buttonsHtml += `
            <button type="button" 
                    class="remover ${isAdminRole ? 'protected-btn' : ''}" 
                    ${isAdminRole ? 'disabled' : `onClick="eliminarRol(${item.rol_id}, '${item.rol_nombre.replace(/'/g, "\\'")}', ${item.usuarios_count})"`} 
                    title="${isAdminRole ? 'No se puede eliminar el rol de Administrador' : 'Eliminar Rol'}">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 7l16 0" />
                <path d="M10 11l0 6" />
                <path d="M14 11l0 6" />
                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
              </svg>
            </button>`;
          <?php endif; ?>

          buttonsHtml += '</div>';

          table.row.add([
            item.rol_nombre,
            item.usuarios_count || 0,
            buttonsHtml
          ]);

        });

        table.draw();
        hideTableLoading();
        isFirstLoad = false;

      } else {
        throw new Error("No se encontraron datos");
      }

    } catch (error) {
      console.error("Error al cargar datos:", error);
      showTableError('No se pudieron cargar los roles. Por favor, inténtalo de nuevo.');
      CustomDialog.toast('Error al cargar los datos', 'error', 3000);
    }
  }

  function crearRol() {
    mostrarModalCrearRol();
  }

  function gestionarPermisos(rolId) {
    window.location.href = `${APP_URL}roles/${rolId}/permissions`;
  }

  async function eliminarRol(rolId, nombreRol, usuariosCount) {
    mostrarModalEliminarRol(rolId, nombreRol, usuariosCount);
  }



  // Función para cerrar todos los menús
  function cerrarTodosLosMenus() {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      menu.classList.remove('show');
    });
  }

  // Función para cerrar el menú cuando se hace clic fuera de él
  function cerrarMenuAlClickearFuera(event) {
    const menus = document.querySelectorAll('.dropdown-menu.show');
    let clickDentroDeMenu = false;

    menus.forEach(menu => {
      if (menu.contains(event.target)) {
        clickDentroDeMenu = true;
      }
    });

    // Si el clic no fue dentro de un menú o en un botón de opciones
    if (!clickDentroDeMenu && !event.target.classList.contains('opciones')) {
      cerrarTodosLosMenus();
      document.removeEventListener('click', cerrarMenuAlClickearFuera);
    }
  }

  // --- FUNCIONES DE TOOLTIP PERSONALIZADO ---

  document.addEventListener('DOMContentLoaded', () => {
    const tableContainer = document.getElementById('table-container');
    if (tableContainer) {
      tableContainer.addEventListener('mouseover', handleMouseOver);
      tableContainer.addEventListener('mouseout', handleMouseOut);
    }

    const toolsContainer = document.querySelector('.tools');
    if (toolsContainer) {
      toolsContainer.addEventListener('mouseover', handleMouseOver);
      toolsContainer.addEventListener('mouseout', handleMouseOut);
    } else {
      document.body.addEventListener('mouseover', handleMouseOver);
      document.body.addEventListener('mouseout', handleMouseOut);
    }
  });

  function ensureTooltipElement() {
    if (!jsTooltipElement) {
      jsTooltipElement = document.createElement('div');
      jsTooltipElement.className = 'custom-js-tooltip';
      document.body.appendChild(jsTooltipElement);
    }
  }

  function positionJsTooltip(targetButton, tooltipEl) {
    if (!tooltipEl || !targetButton) return;

    const rect = targetButton.getBoundingClientRect();
    const tooltipRect = tooltipEl.getBoundingClientRect();

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

    let topPosition;
    let isAbove = true;

    topPosition = rect.top + scrollTop - tooltipRect.height - 8; // Intentar ARRIBA

    if (topPosition < scrollTop + 5) { // No hay espacio arriba, intentar ABAJO
      topPosition = rect.bottom + scrollTop + 8;
      isAbove = false;
    }

    let leftPosition = rect.left + scrollLeft + (rect.width / 2) - (tooltipRect.width / 2);

    if (leftPosition + tooltipRect.width > window.innerWidth - 5) {
      leftPosition = window.innerWidth - tooltipRect.width - 5;
    }
    if (leftPosition < 5) {
      leftPosition = 5;
    }

    tooltipEl.style.top = `${topPosition}px`;
    tooltipEl.style.left = `${leftPosition}px`;

    if (isAbove) {
      tooltipEl.style.transform = 'translateY(5px)';
    } else {
      tooltipEl.style.transform = 'translateY(-5px)';
    }
  }

  function clearTooltipStateForButton(button) {
    if (button) {
      const originalTitle = button.getAttribute('data-original-title');
      if (originalTitle) {
        button.setAttribute('title', originalTitle);
        button.removeAttribute('data-original-title');
      }
    }
  }

  function showJsTooltipForButton(button) {
    if (!button) return;
    if (currentTooltipButton === button && jsTooltipElement && jsTooltipElement.classList.contains('show')) {
      return;
    }

    const tooltipText = button.getAttribute('title') || button.getAttribute('data-original-title');
    if (!tooltipText) return;

    if (currentTooltipButton && currentTooltipButton !== button) {
      clearTooltipStateForButton(currentTooltipButton);
      if (jsTooltipElement) {
        jsTooltipElement.classList.remove('show');
      }
    }

    if (button.getAttribute('title')) {
      button.setAttribute('data-original-title', tooltipText);
      button.removeAttribute('title');
    }

    ensureTooltipElement();

    jsTooltipElement.classList.remove('protected-tooltip');

    if (button.classList.contains('protected-btn')) {
      jsTooltipElement.classList.add('protected-tooltip');
    }

    currentTooltipButton = button;

    jsTooltipElement.textContent = tooltipText;
    jsTooltipElement.style.display = 'block';

    requestAnimationFrame(() => {
      positionJsTooltip(button, jsTooltipElement);
      requestAnimationFrame(() => {
        if (jsTooltipElement) jsTooltipElement.classList.add('show');
      });
    });
  }

  function hideActiveJsTooltip() {
    if (currentTooltipButton) {
      clearTooltipStateForButton(currentTooltipButton);
    }
    if (jsTooltipElement) {
      jsTooltipElement.classList.remove('show');
      setTimeout(() => {
        if (jsTooltipElement && !jsTooltipElement.classList.contains('show')) {
          jsTooltipElement.style.display = 'none';
        }
      }, 150);
    }
    currentTooltipButton = null;
  }

  function handleMouseOver(event) {
    const button = event.target.closest('.editar, .remover');

    if (button) {
      showJsTooltipForButton(button);
    }
  }

  function handleMouseOut(event) {
    const button = event.target.closest('.editar, .remover');

    if (!button) {
      // Si no hay botón en el elemento actual, ocultar tooltip activo
      hideActiveJsTooltip();
      return;
    }

    // Solo ocultar si estamos saliendo del botón actual que tiene el tooltip activo
    if (currentTooltipButton === button) {
      // Verificar si realmente estamos saliendo del botón
      const relatedTarget = event.relatedTarget;

      if (!relatedTarget || !button.contains(relatedTarget)) {
        hideActiveJsTooltip();
      }
    }
  }
</script>