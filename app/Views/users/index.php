<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<div class="container">
  <div class="right_content">
    <div class="tools">
      <form class="filter_form" id="filter_form">
        <div class="input-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon icon icon-tabler icons-tabler-outline icon-tabler-search">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
            <path d="M21 21l-6 -6" />
          </svg>
          <input class="matching-search" name="matchingColumn" id="matchingInput" placeholder="Buscar">
          <span class="clear-button" id="clearButton">×</span>
        </div>
        <div class="select-container">
          <select class="custom-select" name="filterColumn" title="" id="filterColumn">
            <option value="0">Todo</option>
            <option value="1">Nombre</option>
            <option value="2">Usuario</option>
            <option value="3">Correo</option>
            <option value="4">Estado</option>
            <option value="5">Rol</option>
          </select>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-filter-icon icon icon-tabler icons-tabler-outline icon-tabler-filter">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
          </svg>
          </svg>
        </div>
      </form>

      <?php if (\App\Core\Auth::can('users.create')): ?>
        <button type="submit" class="action_create_new" onclick="goTo('users/create')">Nuevo</button>
      <?php endif; ?>
    </div>

    <div class="table-container" id="table-container">

      <!-- Loading inicial -->
      <div class="table-loading-container" id="table-loading">
        <div class="table-loading">
          <div class="table-spinner"></div>
          <p>Cargando usuarios...</p>
        </div>
      </div>

      <table id="users-table" class="hover nowrap cell-borders" style="display: none;">
        <thead>
          <tr>
            <th class="dt-head-center">NO</th>
            <th>NOMBRE COMPLETO</th>
            <th>NOMBRE DE USUARIO</th>
            <th>CORREO</th>
            <th class="dt-head-center">ESTADO</th>
            <th class="dt-head-center">ROL</th>
            <th>ACCIONES</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . 'public/inc/scripts.php' ?>
<script>
  let table;
  let isFirstLoad = true;
  let jsTooltipElement = null;
  let currentTooltipButton = null;
  let matchingInput = document.getElementById('matchingInput');
  let filter_form = document.getElementById('filter_form');
  let filterColumn = document.getElementById('filterColumn');
  let clearButton = document.getElementById('clearButton');

  // --- BUSQUEDA Y FILTRO DE REGISTROS ---

  // Manejo de eventos de entrada en el campo de búsqueda
  matchingInput.addEventListener('input', () => {
    applyFilter();
  });

  // Manejo del formulario de filtro
  filter_form.addEventListener('submit', () => {
    event.preventDefault();
  });

  // Manejo del cambio en el select de filtro
  filterColumn.addEventListener('change', () => {
    applyFilter();
  });

  // Función para limpiar el input de búsqueda
  function clearInput() {
    matchingInput.value = '';
    matchingInput.focus();
    clearButton.style.display = 'none';
    applyFilter();
  }

  // Función para aplicar el filtro global o por columna
  function applyFilter() {
    let searchValue = matchingInput.value.trim();
    let columnIndex = filterColumn.value;
    clearButton.style.display = searchValue ? 'block' : 'none';

    if (columnIndex == 0) {
      // Filtro global
      table.columns().search('');
      table.search(searchValue, false, true).draw();

    } else {

      table.search('');
      table.columns().search('');
      table.column(columnIndex).search(searchValue, false, true).draw();

    }
  }

  // --- CARGA DE DATOS ---

  // Inicializar DataTable
  document.addEventListener('DOMContentLoaded', () => {
    // Mostrar loading inicial
    showTableLoading('Inicializando tabla...');

    // Mostrar la tabla para que DataTables pueda inicializarse
    document.getElementById('users-table').style.display = '';

    table = new DataTable('#users-table', {
      fixedColumns: {
        end: 1
      },
      scrollX: true,
      columnDefs: [{
        targets: [0, 4, 5],
        className: 'dt-body-center'
      }],
      layout: {
        topStart: null,
        buttomStart: null,
        buttomEnd: null
      },
      language: {
        "zeroRecords": "No se encontraron registros",
        "emptyTable": "Aún no hay registros, crea uno nuevo aquí",
        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
        "infoEmpty": "Mostrando 0 a 0 de 0 registros",
        "infoFiltered": "(filtrado de _MAX_ registros totales)",
        "processing": '<div class="table-spinner"></div>Procesando...'
      },
      initComplete: () => {
        loadData();
      },
      drawCallback: () => {
        // Aplicar clases de estado
        document.querySelectorAll('td').forEach(td => {
          if (td.textContent === 'Inactivo') {
            td.classList.add('inactivo');
          }
          if (td.textContent === 'Activo') {
            td.classList.add('activo');
          }
        });
      }
    });
  });

  // Cargar datos al iniciar
  async function loadData() {
    try {
      showTableLoading('Cargando usuarios...');

      const response = await fetch('<?= APP_URL ?>api/users', {
        method: 'GET',
        headers: new Headers(),
        mode: 'cors',
        cache: 'no-cache',
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();
      console.log(data);

      if (data.status === "success" && data.data) {
        // Limpiar tabla
        table.clear();

        // Agregar datos
        let incremental = 1;
        data.data.forEach(item => {
          // Construir los botones de acción basándose en permisos
          let actionsHtml = '';

          <?php if (\App\Core\Auth::can('users.edit')): ?>
            actionsHtml += `
            <button type="button" class="editar" onClick="actualizar(${item.usuario_id})" title="Editar Usuario">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                <path d="M13.5 6.5l4 4" />
              </svg>
            </button>`;
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('users.delete')): ?>
            actionsHtml += `
            <button type="button" class="remover" onClick="remover(${item.usuario_id}, '${(item.usuario_nombre + ' ' + item.usuario_apellido_paterno + ' ' + item.usuario_apellido_materno).replace(/'/g, "\\'")}')" title="Eliminar Usuario">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 7l16 0" />
                <path d="M10 11l0 6" />
                <path d="M14 11l0 6" />
                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
              </svg>
            </button>`;
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('users.edit')): ?>
            actionsHtml += `
            <button type="button" class="opciones" onClick="mostrarOpciones(${item.usuario_id})" title="Más Opciones">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-dots-vertical">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
              </svg>
            </button>`;
          <?php endif; ?>

          table.row.add([
            incremental++,
            `${item.usuario_nombre} ${item.usuario_apellido_paterno} ${item.usuario_apellido_materno}`,
            item.usuario_usuario,
            item.usuario_email,
            item.usuario_estado === "1" ? 'Activo' : 'Inactivo',
            item.rol_descripcion,
            actionsHtml
          ]);
        });

        hideTableLoading();
        table.draw();
        isFirstLoad = false;

      } else {
        throw new Error("No se encontraron datos");
      }

    } catch (error) {
      console.error("Error al cargar datos:", error);
      showTableError('No se pudieron cargar los usuarios. Por favor, inténtalo de nuevo.');
      CustomDialog.toast('Error al cargar los datos', 'error', 3000);
    }
  }

  // Función para mostrar loading
  function showTableLoading(message = 'Cargando usuarios...') {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');

    if (!loadingContainer) {
      // Crear loading si no existe
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
      // Actualizar message si ya existe
      const messageEl = loadingContainer.querySelector('p');
      if (messageEl) messageEl.textContent = message;
      loadingContainer.style.display = 'flex';
    }

    // Animar entrada del loading
    setTimeout(() => {
      const loading = document.getElementById('table-loading');
      if (loading) loading.classList.add('show');
    }, 10);

    // Ocultar tabla y wrapper
    const tableWrapper = document.getElementById('users-table_wrapper');
    if (tableWrapper) {
      tableWrapper.classList.remove('show');
      tableWrapper.style.display = 'none';
    }

    container.classList.add('loading');
  }

  function hideTableLoading() {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');
    const tableWrapper = document.getElementById('users-table_wrapper');

    const loaderFadeOutDuration = 10;
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

      setTimeout(() => {
        tableWrapper.classList.add('show');

        if (table) {
          table.columns.adjust();
          table.draw();
        }
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

  // --- FUNCIONES DE LOS ACTION BUTTONS

  function actualizar(usuario_id) {
    window.location.href = `<?= APP_URL ?>users/edit/${usuario_id}`;
  }

  function remover(usuario_id, nombreUsuario) {
    console.log('ejecutando remover')
    CustomDialog.confirm(
      'Confirmar Eliminación',
      `¿Está seguro de que desea eliminar a ${nombreUsuario}?`,
      'Eliminar',
      'Cancelar'
    ).then(async (confirmado) => {
      if (confirmado) {
        showTableLoading('Eliminando usuario...');

        try {
          const response = await fetch(`<?= APP_URL; ?>api/users/${usuario_id}`, {
            method: "DELETE",
            headers: {
              'Accept': 'application/json'
            }
          });

          const data = await response.json();

          if (response.ok && data.status === 'success') {
            await CustomDialog.success('Operación exitosa', data.message || 'Usuario eliminado correctamente');
            await loadData();
          } else if (response.ok && data.status === 'error') {
            hideTableLoading();
            await CustomDialog.error('Error', data.message || 'No se pudo eliminar el usuario.');
          } else {
            hideTableLoading();
            CustomDialog.error('Error', data.message || 'No se pudo eliminar el usuario.');
          }
        } catch (error) {
          console.error('Error en la petición fetch:', error);
          CustomDialog.error('Error de Red', 'Ocurrió un problema al intentar conectar con el servidor.');
        }
      }
    });
  }

  function mostrarOpciones(usuario_id) {
    // Prevenir comportamiento por defecto
    event.preventDefault();
    event.stopPropagation();

    // Cerrar cualquier menú abierto anteriormente
    cerrarTodosLosMenus();

    // Obtener el botón que se hizo clic
    const boton = event.currentTarget;

    // Verificar si ya existe un menú para este usuario
    let menu = document.getElementById(`menu-${usuario_id}`);

    // Si no existe, crear el menú
    if (!menu) {
      menu = document.createElement('div');
      menu.id = `menu-${usuario_id}`;
      menu.className = 'dropdown-menu';
      menu.innerHTML = `
      <div class="dropdown-item" onclick="verDetalles(${usuario_id})">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="8" r="1"></circle><line x1="12" y1="12" x2="12" y2="16"></line>
        </svg>
        Ver detalles
      </div>
      <div class="dropdown-item" onclick="cambiarEstado(${usuario_id})">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
        </svg>
        Cambiar estado
      </div>
      <div class="dropdown-item" onclick="resetearPassword(${usuario_id})">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        Resetear contraseña
      </div>
    `;
      document.body.appendChild(menu);
    }

    // Posicionar el menú
    posicionarMenu(boton, menu);

    // Mostrar el menú
    menu.classList.add('show');

    // Agregar listener para cerrar el menú cuando se haga clic fuera de él
    setTimeout(() => {
      document.addEventListener('click', cerrarMenuAlClickearFuera);
    }, 10);
  }

  // User Details Modal
  function verDetalles(userId) {
    mostrarModalVerDetalles(userId);
  }

  // Change Status Modal
  function cambiarEstado(usuario_id) {
    mostrarModalCambiarEstado(usuario_id);
  }

  function resetearPassword(userId) {
    mostrarModalResetearPassword(userId);
  }


  // --- FUNCIONES DE POSICIONAMIENTO DEL OPTION MENU ---

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

  // --- Adjuntar Eventos para los tooltips ---
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

  // --- Manejadores de Eventos ---
  function handleMouseOver(event) {
    const button = event.target.closest('.editar, .remover, .opciones');
    const createButton = event.target.closest('.action_create_new[title]');
    const targetElement = button || createButton;

    if (targetElement) {
      showJsTooltipForButton(targetElement);
    }
  }

  function handleMouseOut(event) {
    const button = event.target.closest('.editar, .remover, .opciones');
    const createButton = event.target.closest('.action_create_new[title]');
    const targetElement = button || createButton;

    if (targetElement) {
      if (currentTooltipButton === targetElement && (!event.relatedTarget || !targetElement.contains(event.relatedTarget))) {
        hideActiveJsTooltip();
      }
    }
  }

  // Manejo del botón de limpiar
  clearButton.addEventListener('click', () => {
    clearInput();
  });
</script>