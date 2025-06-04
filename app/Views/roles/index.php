<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';


?>
<style>
  .container {
    padding: 2.5rem calc(23rem + 1vw) 0 calc(23rem + 1vw);
  }

  .navigation {
    display: flex;
    justify-content: flex-end;
    padding: 2rem 0;
    padding-bottom: 1.5rem;
  }

  .tools {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    justify-content: space-between;
  }

  .filter_form {
    display: flex;
    gap: 1rem;
  }

  .action_create_new {
    height: fit-content;
    width: 8rem;
    display: flex;
    padding: var(--25, 10px) var(--5, 20px);
    justify-content: center;
    align-items: center;
    gap: var(--2, 8px);
    align-self: stretch;
    border-radius: var(--rounded-lg, 8px);
    background: #18181B;
    color: var(--white);
    font-size: 14px;
    font-style: normal;
    font-weight: 700;
    line-height: 150%;
  }

  .input-container {
    position: relative;
    display: inline-block;
  }

  .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    stroke: #465566;
    pointer-events: none;
  }

  #matchingInput {
    padding-left: 40px;
    padding-right: 24px;
  }

  .clear-button {
    position: absolute;
    right: 1rem;
    top: 46%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 2rem;
    font-weight: 300;
    color: #aaa;
    display: none;
  }

  /* Estilos de tabla - reutilizando de users/index.php */
  tbody>tr:last-child>* {
    border: 0 !important;
  }

  th {
    padding-left: 1.25rem !important;
    padding-top: 1.2rem !important;
    padding-bottom: 1.2rem !important;
    background-color: #fbfbfb;
    border: 0 !important;
    color: var(--gray-500);
    font-size: 12px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
    text-transform: uppercase;
  }

  tr {
    padding-left: 1.25rem !important;
    height: 4rem !important;
    border-bottom: 1px solid #E5E5E5;
  }

  tr:last-child {
    border-bottom: 0;
  }

  td {
    padding-left: 1.25rem !important;
    color: var(--gray-900);
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 150%;
  }

  td:not(.dt-empty):last-child {
    display: flex;
    gap: 0.4rem;
    padding: 11px;
  }

  td.dt-empty {
    align-content: center;
  }

  .editar,
  .remover,
  .opciones {
    border: none;
    padding: 6px;
    border-radius: 6px;
    background-color: transparent;
    transition: all 0.25s ease;
    cursor: pointer;
  }

  .editar:hover {
    background-color: #f2f2f2;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .editar:hover svg {
    transform: translateY(-1px) scale(1.05);
    stroke: #007bff;
    filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
  }

  .remover:hover {
    background-color: #f2f2f2;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .remover:hover svg {
    transform: translateY(-1px) rotate(-5deg);
    stroke: var(--red-600);
    filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
  }

  .opciones:hover {
    background-color: #f2f2f2;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .opciones:hover svg {
    transform: translateY(-1px);
    filter: drop-shadow(0 1px 1px var(--sombra));
    stroke: #14171d;
  }

  .editar svg,
  .remover svg,
  .opciones svg {
    transition: all 0.25s ease;
    stroke: #465566;
  }

  /* REEMPLAZAR estilos de botones con: */

  /* Contenedor de botones de acción */
  td:not(.dt-empty):last-child {
    padding: 11px;
  }

  td:not(.dt-empty):last-child .action-buttons {
    display: flex;
    gap: 0.3rem;
    align-items: center;
    justify-content: center;
  }

  /* Estilos base para todos los botones de acción */
  .editar,
  .remover,
  .permisos-btn {
    border: none;
    padding: 8px;
    border-radius: 6px;
    background-color: transparent;
    transition: all 0.25s ease;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .permisos-btn:hover {
    background-color: #e8f5e8;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .permisos-btn:hover svg {
    transform: translateY(-1px) scale(1.05);
    stroke: #1976d2;
    filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
  }

  .remover:hover {
    background-color: #ffebee;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .remover:hover svg {
    transform: translateY(-1px) rotate(-5deg);
    stroke: var(--red-600);
    filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
  }

  /* Transiciones para los SVG */
  .editar svg,
  .remover svg,
  .permisos-btn svg {
    transition: all 0.25s ease;
    stroke: #465566;
  }

  /* Tooltips mejorados para los botones */
  .action-buttons button[title] {
    position: relative;
  }

  .action-buttons button[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 8px;
    pointer-events: none;
    font-weight: 500;
    letter-spacing: 0.02em;
  }

  .action-buttons button[title]:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    margin-bottom: 3px;
    pointer-events: none;
  }

  /* Responsive para tabla */
  @media (max-width: 768px) {
    .action-buttons {
      flex-direction: column;
      gap: 0.2rem;
    }

    .action-buttons button {
      padding: 6px;
    }

    .action-buttons button svg {
      width: 18px;
      height: 18px;
    }

    /* Ocultar tooltips en móvil */
    .action-buttons button[title]:hover::after,
    .action-buttons button[title]:hover::before {
      display: none;
    }
  }

  div.dt-layout-table {
    overflow: hidden;
    background-color: #fff;
    border-radius: var(--rounded-lg, 8px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.10), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--gray-300);
  }

  /* Loading y estados - reutilizando de users/index.php */
  .table-loading-container {
    position: relative;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--modal-bg, #ffffff);
    border-radius: var(--modal-border-radius, 12px);
    opacity: 0;
    transition: opacity 300ms ease;
  }

  .table-loading-container.show {
    opacity: 1;
  }

  .table-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    gap: 16px;
  }

  .table-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--modal-border, #e5e7eb);
    border-top: 3px solid var(--btn-primary, #3b82f6);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  .table-loading p {
    color: var(--modal-text-secondary, #6b7280);
    font-size: 16px;
    margin: 0;
  }

  #roles-table_wrapper {
    opacity: 0;
    transform: translateY(10px);
    transition: opacity 300ms cubic-bezier(0.215, 0.610, 0.355, 1),
      transform 300ms cubic-bezier(0.215, 0.610, 0.355, 1);
  }

  #roles-table_wrapper.show {
    opacity: 1;
    transform: translateY(0);
  }

  .table-container {
    position: relative;
    min-height: 400px;
  }

  .dt-layout-row:first-of-type {
    display: none !important;
  }

  @media (max-width: 1200px) {
    .container {
      padding: 2rem 2rem;
    }
  }

  /* Estilos para roles protegidos (Administrador) */
  .role-protected {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
    background-color: #fafafa !important;
  }

  .role-protected:hover {
    background-color: #fafafa !important;
    box-shadow: none !important;
    transform: none !important;
  }

  .role-protected:hover svg {
    transform: none !important;
    stroke: #bbb !important;
    filter: none !important;
  }

  .role-protected svg {
    stroke: #bbb !important;
  }

  .role-protected[title]:hover::after {
    background: rgba(200, 100, 100, 0.9) !important;
    color: white !important;
    font-weight: 500;
  }

  .role-protected[title]:hover::before {
    border-top-color: rgba(200, 100, 100, 0.9) !important;
  }
</style>
<div class="container">
  <div class="tools">
    <form class="filter_form" id="filter_form">
      <div class="input-container">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon icon icon-tabler icons-tabler-outline icon-tabler-search">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
          <path d="M21 21l-6 -6" />
        </svg>
        <input type="text" name="matchingColumn" id="matchingInput" placeholder="Buscar roles">
        <span class="clear-button">×</span>
      </div>
    </form>

    <?php if (\App\Core\Auth::can('roles.create')): ?>
      <button class="action_create_new dark-button" onclick="crearRol()">Nuevo Rol</button>
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
          <th class="dt-head-left">ACCIONES</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<script src="<?= APP_URL ?>public/js/libs/datatables.min.js"></script>
<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>
<script>
  // Variables globales
  let table;
  let isFirstLoad = true;

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
          <button type="button" class="btn-reload" onclick="loadData()">Reintentar</button>
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
    const clearButton = document.querySelector('.clear-button');
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

          const isAdminRole = item.rol_descripcion === 'Administrador' || item.rol_id === 1;
          let buttonsHtml = '<div class="action-buttons">';

          <?php if (\App\Core\Auth::can('roles.edit')): ?>
            buttonsHtml += `
            <button type="button" 
                    class="permisos-btn ${isAdminRole ? 'role-protected' : ''}" 
                    ${isAdminRole ? 'disabled' : `onClick="gestionarPermisos(${item.rol_id})"`} 
                    title="${isAdminRole ? 'No se puede modificar el rol de Administrador' : 'Gestionar Permisos'}">
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
                    class="remover ${isAdminRole ? 'role-protected' : ''}" 
                    ${isAdminRole ? 'disabled' : `onClick="eliminarRol(${item.rol_id}, '${item.rol_descripcion.replace(/'/g, "\\'")}', ${item.usuarios_count})"`} 
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
            item.rol_descripcion,
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
    window.location.href = `${APP_URL}/roles/${rolId}/permissions`;
  }


  async function eliminarRol(rolId, nombreRol, usuariosCount) {
    mostrarModalEliminarRol(rolId, nombreRol, usuariosCount);
  }


  // Función para posicionar el menú correctamente
  function posicionarMenu(boton, menu) {
    const rect = boton.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

    // Calcular posición inicial (debajo y a la derecha del botón)
    let top = rect.bottom + scrollTop;
    let left = rect.left + scrollLeft;

    // Mostrar temporalmente el menú para obtener sus dimensiones
    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    menu.style.opacity = '0';

    // Obtener dimensiones del menú
    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;

    // Obtener dimensiones de la ventana
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // Ajustar posición si el menú se sale por la derecha
    if (left + menuWidth > windowWidth - 20) {
      left = rect.right + scrollLeft - menuWidth;
    }

    // Ajustar posición si el menú se sale por abajo
    if (top + menuHeight > windowHeight + scrollTop - 20) {
      top = rect.top + scrollTop - menuHeight;
    }

    // Aplicar posición
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;

    // Restaurar visibilidad
    menu.style.visibility = '';
    menu.style.display = '';
    menu.style.opacity = '';
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

  document.addEventListener('DOMContentLoaded', function() {
    // Verificar si hay un message de éxito de actualización de usuario
    const updateSuccess = sessionStorage.getItem('userUpdateSuccess');

    if (updateSuccess) {
      try {
        const successData = JSON.parse(updateSuccess);

        // Verificar que el message no sea muy antiguo (máximo 10 segundos)
        const now = Date.now();
        const messageAge = now - successData.timestamp;

        if (messageAge < 10000) { // 10 segundos
          // Esperar a que la página se cargue completamente antes de mostrar el modal
          setTimeout(async () => {
            await CustomDialog.success(
              'Usuario Actualizado',
              successData.message
            );
          }, 500); // Pequeño delay para asegurar que todo esté cargado
        }

        // Limpiar el message del sessionStorage
        sessionStorage.removeItem('userUpdateSuccess');

      } catch (error) {
        console.error('Error al procesar message de éxito:', error);
        sessionStorage.removeItem('userUpdateSuccess');
      }
    }
  });
</script>