<style>
  .container {
    padding: 2.5rem 10rem 0 10rem;
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
    height: 4rem !important;
    border-bottom: 1px solid #E5E5E5;
  }

  tr:last-child {
    border-bottom: 0;
  }

  td {
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

  .dropdown-menu {
    position: absolute;
    background-color: white;
    border-radius: var(--rounded-lg);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 160px;
    z-index: 1000;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
  }

  .dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: #465566;
    font-size: 14px;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.2s;
  }

  .dropdown-item:hover {
    background-color: #f8f8f8;
  }

  .dropdown-item svg {
    margin-right: 8px;
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

  @media (max-width: 1080px) {
    .container {
      padding: 2rem 2rem;
    }
  }
</style>

<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

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
          <th class="dt-head-center">NO</th>
          <th>ROL</th>
          <th class="dt-head-center">USUARIOS</th>
          <th>ACCIONES</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<script src="<?= APP_URL ?>public/js/datatables.min.js"></script>
<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>
<script>
  // Variables globales
  let table;
  let isFirstLoad = true;

  // Funciones de loading - reutilizadas de users/index.php
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

  // Inicializar DataTable
  document.addEventListener('DOMContentLoaded', () => {
    showTableLoading('Inicializando tabla...');
    document.getElementById('roles-table').style.display = '';

    table = new DataTable('#roles-table', {
      scrollX: true,
      columnDefs: [{
        targets: [0, 2],
        className: 'dt-body-center'
      }],
      layout: {
        topStart: null,
        buttomStart: null,
        buttomEnd: null
      },
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

  // Cargar datos
  async function loadData() {
    try {
      if (!isFirstLoad) {
        showTableLoading('Actualizando roles...');
      }

      const response = await fetch('<?= APP_URL ?>api/roles');

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.status === "success" && data.data) {
        table.clear();

        let incremental = 1;
        data.data.forEach(item => {
          table.row.add([
            incremental++,
            item.rol_descripcion,
            item.usuarios_count || 0,
            `<?php if (\App\Core\Auth::can('roles.edit')): ?>
              <button type="button" class="editar" onClick="editarRol(${item.rol_id})">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                  <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                  <path d="M13.5 6.5l4 4" />
                </svg>
              </button>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('roles.delete')): ?>
              <button type="button" class="remover" onClick="eliminarRol(${item.rol_id}, '${item.rol_descripcion.replace(/'/g, "\\'")}', ${item.usuarios_count})">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                  <path d="M4 7l16 0" />
                  <path d="M10 11l0 6" />
                  <path d="M14 11l0 6" />
                  <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                  <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                </svg>
              </button>
            <?php endif; ?>
            <button type="button" class="opciones" onClick="mostrarOpciones(${item.rol_id})">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
              </svg>
            </button>`
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

  // Funciones de acciones
  function crearRol() {
    const createModal = createModal('createRole', {
      title: 'Crear Nuevo Rol',
      size: 'large',
      endpoint: `${APP_URL}/api/roles`,
      onShow: async (modal) => {
        modal.showLoading('Cargando permisos disponibles...');

        try {
          const response = await fetch(`${APP_URL}/api/permissions`);
          const permissionsData = await response.json();

          if (permissionsData.status === 'success') {
            const templateData = TEMPLATE_HELPERS.processCreateRoleData(permissionsData.data);
            modal.updateContent(templateData);
          } else {
            modal.showError('No se pudieron cargar los permisos');
          }
        } catch (error) {
          modal.showError('Error al conectar con el servidor');
        }
      }
    });

    createModal.show();
  }

  function editarRol(rolId) {
    const editModal = createModal('editRole', {
      title: 'Editar Rol',
      size: 'large',
      endpoint: `${APP_URL}/api/roles/${rolId}`,
      onShow: async (modal) => {
        modal.showLoading('Cargando información del rol...');

        try {
          const [roleResponse, permissionsResponse, rolePermissionsResponse] = await Promise.all([
            fetch(`${APP_URL}/api/roles/${rolId}`),
            fetch(`${APP_URL}/api/permissions`),
            fetch(`${APP_URL}/api/roles/${rolId}/permissions`)
          ]);

          const roleData = await roleResponse.json();
          const permissionsData = await permissionsResponse.json();
          const rolePermissionsData = await rolePermissionsResponse.json();

          if (roleData.status === 'success' && permissionsData.status === 'success') {
            const templateData = TEMPLATE_HELPERS.processEditRoleData(
              roleData.data,
              permissionsData.data,
              rolePermissionsData.data || []
            );
            modal.updateContent(templateData);
          } else {
            modal.showError('No se pudieron cargar los datos del rol');
          }
        } catch (error) {
          modal.showError('Error al conectar con el servidor');
        }
      }
    });

    editModal.show();
  }

  async function eliminarRol(rolId, nombreRol, usuariosCount) {
    if (usuariosCount > 0) {
      CustomDialog.error(
        'No se puede eliminar',
        `El rol "${nombreRol}" tiene ${usuariosCount} usuario(s) asignado(s). Primero debes reasignar estos usuarios a otro rol.`
      );
      return;
    }

    const confirmacion = await CustomDialog.confirm(
      'Confirmar Eliminación',
      `¿Está seguro de que desea eliminar el rol "${nombreRol}"?`,
      'Eliminar',
      'Cancelar'
    );

    if (confirmacion) {
      showTableLoading('Eliminando rol...');

      try {
        const response = await fetch(`${APP_URL}/api/roles/${rolId}`, {
          method: "DELETE",
          headers: {
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (response.ok && data.status === 'success') {
          await CustomDialog.success('Operación exitosa', data.message || 'Rol eliminado correctamente');
          await loadData();
        } else {
          hideTableLoading();
          CustomDialog.error('Error', data.message || 'No se pudo eliminar el rol.');
        }
      } catch (error) {
        console.error('Error en la petición fetch:', error);
        hideTableLoading();
        CustomDialog.error('Error de Red', 'Ocurrió un problema al intentar conectar con el servidor.');
      }
    }
  }

  function mostrarOpciones(rolId) {
    event.preventDefault();
    event.stopPropagation();

    cerrarTodosLosMenus();

    const boton = event.currentTarget;
    let menu = document.getElementById(`menu-${rolId}`);

    if (!menu) {
      menu = document.createElement('div');
      menu.id = `menu-${rolId}`;
      menu.className = 'dropdown-menu';
      menu.innerHTML = `
        <div class="dropdown-item" onclick="gestionarPermisos(${rolId})">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3a6.364 6.364 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
          </svg>
          Gestionar permisos
        </div>
        <div class="dropdown-item" onclick="verDetallesRol(${rolId})">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="8" r="1"></circle>
            <line x1="12" y1="12" x2="12" y2="16"></line>
          </svg>
          Ver detalles
        </div>
      `;
      document.body.appendChild(menu);
    }

    posicionarMenu(boton, menu);
    menu.classList.add('show');

    setTimeout(() => {
      document.addEventListener('click', cerrarMenuAlClickearFuera);
    }, 10);
  }

  function posicionarMenu(boton, menu) {
    const rect = boton.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

    let top = rect.bottom + scrollTop;
    let left = rect.left + scrollLeft;

    menu.style.visibility = 'hidden';
    menu.style.display = 'block';
    menu.style.opacity = '0';

    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    if (left + menuWidth > windowWidth - 20) {
      left = rect.right + scrollLeft - menuWidth;
    }

    if (top + menuHeight > windowHeight + scrollTop - 20) {
      top = rect.top + scrollTop - menuHeight;
    }

    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;
    menu.style.visibility = '';
    menu.style.display = '';
    menu.style.opacity = '';
  }

  function cerrarTodosLosMenus() {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
      menu.classList.remove('show');
    });
  }

  function cerrarMenuAlClickearFuera(event) {
    const menus = document.querySelectorAll('.dropdown-menu.show');
    let clickDentroDeMenu = false;

    menus.forEach(menu => {
      if (menu.contains(event.target)) {
        clickDentroDeMenu = true;
      }
    });

    if (!clickDentroDeMenu && !event.target.classList.contains('opciones')) {
      cerrarTodosLosMenus();
      document.removeEventListener('click', cerrarMenuAlClickearFuera);
    }
  }

  function gestionarPermisos(rolId) {
    cerrarTodosLosMenus();
    editarRol(rolId); // Reutilizar el modal de edición
  }

  function verDetallesRol(rolId) {
    cerrarTodosLosMenus();

    const detailsModal = createModal('roleDetails', {
      title: 'Detalles del Rol',
      size: 'large',
      onShow: async (modal) => {
        modal.showLoading('Cargando información del rol...');

        try {
          const response = await fetch(`${APP_URL}/api/roles/${rolId}`);
          const roleData = await response.json();

          if (roleData.status === 'success') {
            const templateData = TEMPLATE_HELPERS.processRoleDetailsData(roleData.data);
            modal.updateContent(templateData);
          } else {
            modal.showError('No se pudo cargar la información del rol');
          }
        } catch (error) {
          modal.showError('Error al conectar con el servidor');
        }
      }
    });

    detailsModal.show();
  }
</script>