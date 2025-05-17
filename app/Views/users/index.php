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

  .volver {
    display: flex;
    width: 15rem;
    height: 2.06rem;
    padding: var(--25, 10px) var(--5, 20px);
    justify-content: center;
    align-items: center;
    gap: var(--2, .5rem);
    flex-shrink: 0;
    border-radius: var(--rounded-lg, 8px);
    border: 1px solid var(--gray-300, #CFD5DD);
    border: 1px solid var(--gray-300, color(display-p3 0.8196 0.8353 0.8588));
    background: #ECECEC;
    background: color(display-p3 0.9255 0.9255 0.9255);
    color: var(--gray-600, #465566);
    color: var(--gray-600, color(display-p3 0.2941 0.3333 0.3882));
    font-size: 14px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
  }

  hr {
    width: 251px;
  }

  .dt-info {
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
  }

  .side_button {
    display: flex;
    width: 251px;
    height: 30px;
    padding: var(--25, 10px) var(--5, 20px) var(--25, 10px) 18px;
    align-items: center;
    gap: var(--2, 8px);
    flex-shrink: 0;
    border-radius: var(--rounded-lg, 8px);
    color: var(--gray-600, #465566);
    color: var(--gray-600, color(display-p3 0.2941 0.3333 0.3882));
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: 150%;
    position: relative;
  }

  .side_button.active {
    background: color(display-p3 0.9255 0.9255 0.9255);
  }

  .side_button:hover {
    background: color(display-p3 0.9255 0.9255 0.9255);
  }

  .side_button::before {
    content: "";
    position: absolute;
    left: -0.4rem;
    top: 50%;
    height: 80%;
    width: 4px;
    background-color: #007bff;
    border-radius: 2px;
    opacity: 0;
    transform: translateY(-50%) translateX(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease;
  }

  .side_button:hover::before {
    opacity: 1;
    transform: translateY(-50%) translateX(0);
  }

  .side_button_content {
    display: flex;
    align-items: center;
  }

  .side_icon_button {
    width: 1.375rem;
    height: 1.375rem;
  }

  .left_side {
    display: flex;
    gap: .5rem;
    flex-direction: column;
  }

  .right_side {
    display: flex;
    flex-direction: column;
    padding-top: .5rem;
  }

  .right_content {
    display: flex;
    flex-direction: column;
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
    background: color(display-p3 0.0941 0.0941 0.1059);

    color: var(--white, #FFF);
    color: var(--white, color(display-p3 1 1 1));

    font-size: 14px;
    font-style: normal;
    font-weight: 700;
    line-height: 150%;
  }

  .filter_icon {
    display: flex;
    align-items: center;
  }




  tbody>tr:last-child>* {
    border: 0 !important;
  }

  th {
    padding-top: 1.2rem !important;
    padding-bottom: 1.2rem !important;

    background-color: #fbfbfb;
    border: 0 !important;

    color: var(--gray-500, var(--gray-500, #677283));
    color: var(--gray-500, var(--gray-500, color(display-p3 0.4196 0.4471 0.502)));
    font-size: 12px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
    text-transform: uppercase;
  }

  tr {
    height: 4rem !important;
    border-bottom: 1px solid #E5E5E5;
    border-bottom: 1px solid color(display-p3 0.898 0.898 0.898);
  }

  /* el ultimo tr */
  tr:last-child {
    border-bottom: 0;
  }

  td {
    color: var(--gray-900, var(--gray-900, #0C192A));
    color: var(--gray-900, var(--gray-900, color(display-p3 0.0667 0.098 0.1569)));
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

  button[bottom].editar {
    background: #007bff;
    background: color(display-p3 0 0.4824 1);
    color: #FFF;
    color: color(display-p3 1 1 1);
    border: 0;
    border-radius: 4px;
    padding: 0.5rem 1rem;
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
    font-size: 1.5rem;
    color: #aaa;
    display: none;
  }

  .input-container input:focus+.clear-button,
  .input-container input:not(:placeholder-shown)+.clear-button {
    display: inline;
  }

  th {
    background-color: #f2f2f2;
  }

  td.activo {
    color: #007bff;
    font-weight: bold;
  }

  td.inactivo {
    color: #dc3545;
    font-weight: bold;
  }

  .select-container {
    position: relative;
  }

  .custom-select {
    width: 100%;
    padding: 8px 36px 8px 16px;
    border: 1px solid #ccc;
    border-radius: 8px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    font-size: 14px;
    cursor: pointer;
    background-color: var(--gray-50);
  }

  .select-filter-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    pointer-events: none;
    stroke: #465566;
  }

  .custom-select:focus {
    outline: none;
    border-color: rgb(152, 152, 152);
    box-shadow: 0 0 0 2px rgba(222, 222, 222, 0.2);
  }

  .custom-select:-moz-focusring {
    color: transparent;
    text-shadow: 0 0 0 #000;
  }
  .editar:hover svg {
  stroke: #f0c231;
}

.remover:hover svg {
  stroke: red;
}


  .editar:hover {
    background-color: 	lightgray;
  }

  .remover:hover {
    background-color: 	lightgray;
  }

  .opciones:hover {
    background-color: lightgray;
  }
  .editar,
  .remover,
  .opciones {
    border: none;
    padding: 6px;
    border-radius: 6px;
    transition: background-color 0.2s ease;
    cursor: pointer;
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


  #users-table_wrapper {
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
  }

  thead:nth-child(2) {
    visibility: collapse;
  }

  table.dataTable thead tr>.dtfc-fixed-start,
  table.dataTable thead tr>.dtfc-fixed-end,
  table.dataTable tfoot tr>.dtfc-fixed-start,
  table.dataTable tfoot tr>.dtfc-fixed-end {
    background-color: #f2f2f2 !important;
  }



  table.dataTable.cell-border tbody th,
  table.dataTable.cell-border tbody td {
    border-top: 1px solid #ddd;
    border-right: 1px solid red;
  }

  table.dataTable.cell-border tbody tr th:first-child,
  table.dataTable.cell-border tbody tr td:first-child {
    border-left: 1px solid red;
  }

  table.dataTable.no-footer {
    border-bottom: 0px solid #111;
  }

  /* lamda */

  div.dt-layout-table {
    overflow: hidden;
    background-color: #fff;
    border-radius: var(--rounded-lg, 8px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.10), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
  }
</style>
<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<div class="container">
  <div class="right_content">
    <div class="tools">

      <form class="filter_form" id="filter_form">
        <div class="input-container">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" name="matchingColumn" id="matchingInput" placeholder="Buscar">
          <span class="clear-button">×</span>
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
          <svg class="select-filter-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172" />
          </svg>
        </div>
      </form>

      <?php if (\App\Core\Auth::can('users.create')): ?>
        <button class="action_create_new" onclick="goTo('users/create')">Nuevo</button>
      <?php endif; ?>
    </div>

    <table id="users-table" class="hover nowrap cell-borders">
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
<?php require_once APP_ROOT . 'public/inc/scripts.php'; ?>
<script src="<?= APP_URL ?>public/js/datatables.min.js"></script>
<script>
  let table = new DataTable('#users-table', {
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
      "emptyTable": "Aún no hay registros crea uno nuevo aquí",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
      "infoEmpty": "Mostrando 0 a 0 de 0 registros",
      "infoFiltered": "(filtrado de _MAX_ registros totales)",
    },
    initComplete: () => {
      loadData();
    },
    drawCallback: () => {
      document.querySelectorAll('td').forEach(td => {
        if (td.textContent === 'inactivo') {
          td.classList.add('inactivo');
        }
        if (td.textContent === 'activo') {
          td.classList.add('activo');
        }
      });
    }
  });

  function loadData() {
    let incremental = 1;
    fetch('<?= APP_URL ?>api/users', {
        method: 'GET',
        headers: new Headers(),
        mode: 'cors',
        cache: 'no-cache',
      })
      .then(response => response.json())
      .then(response => {
        console.log(response);

        if (response.status === "success" && response.data) {

          response.data.forEach(item => {
            table.row.add([
              incremental++,
              `${item.usuario_nombre} ${item.usuario_apellido_paterno} ${item.usuario_apellido_materno}`,
              item.usuario_usuario,
              item.usuario_email,
              item.usuario_estado === "1" ? 'activo' : 'inactivo',
              item.rol_descripcion,
              `<button type="button" class="editar" onClick="actualizar(${item.usuario_id})">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
              </button>
              <button type="button" class="remover" onClick="remover(${item.usuario_id})">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
              </button>
              <button type="button" class="opciones" onClick="mostrarOpciones(${item.usuario_id})">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-dots-vertical"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
              </button>`
            ]);
          });
          table.draw();
          table.columns.adjust();
          document.getElementById('users-table_wrapper').style.opacity = '1';
        } else {
          console.error("Error al cargar los datos o no hay datos disponibles");
          document.getElementById('users-table_wrapper').style.opacity = '1';
        }
      })
      .catch(error => {
        console.error("Error al obtener datos:", error);
        document.getElementById('users-table_wrapper').style.opacity = '1';
      });
  }

  let matchingInput = document.getElementById('matchingInput');
  let filter_form = document.getElementById('filter_form');
  let filterColumn = document.getElementById('filterColumn');

  // Evento para el input de búsqueda
  matchingInput.addEventListener('input', () => {
    applyFilter();
  });

  filter_form.addEventListener('submit', () => {
    event.preventDefault();
  });

  filterColumn.addEventListener('change', () => {
    applyFilter();
  });

  // Función para aplicar el filtro global o por columna
  function applyFilter() {
    let searchValue = matchingInput.value.trim(); // Elimina espacios en blanco
    let columnIndex = filterColumn.value;

    if (columnIndex == 0) {
      // Filtro global
      table.search(searchValue, false, true).draw();
    } else {
      // Filtro por columna
      table.columns().search(''); // Limpia todos los filtros de columna
      table.column(columnIndex).search(searchValue, false, true).draw();
    }
  }

  // se agrega el evento de click al boton de limpiar cuando el documento este cargado
  document.addEventListener('DOMContentLoaded', function() {

    const clearButton = document.querySelector('.clear-button');

    /** cuando se presione el boton de limpiar*/
    clearButton.addEventListener('click', clearInput);

    function clearInput() {
      matchingInput.value = '';
      matchingInput.focus();
      clearButton.style.display = 'none';
      applyFilter();
    }

    matchingInput.addEventListener('input', () => {
      if (matchingInput.value) {
        clearButton.style.display = 'inline';
      } else {
        clearButton.style.display = 'none';
      }
    });
  });

  let legacyInput = document.querySelector('.dt-layout-row');
  legacyInput.style.display = 'none';

  function remover(usuario_id) {
    const params = new URLSearchParams({
      "modulo_usuario": "remover",
      "usuario_id": usuario_id
    });

    fetch("<?= APP_URL; ?>app/ajax/usuarioAjax.php", {
        method: "POST",
        body: params
      })
      .then(response => response.json())
      .then(data => {
        console.log(data);
        table.clear();
        loadData();
      })
      .catch(error => console.error(error));
  }

  function actualizar(usuario_id) {
    window.location.href = `<?= APP_URL ?>users/edit/${usuario_id}`;
  }
  // Función para mostrar y posicionar el menú desplegable
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

  // Funciones de ejemplo para las acciones del menú
  function verDetalles(usuario_id) {
    console.log(`Ver detalles del usuario ${usuario_id}`);
    // Implementa tu lógica aquí
    cerrarTodosLosMenus();
  }

  function cambiarEstado(usuario_id) {
    console.log(`Cambiar estado del usuario ${usuario_id}`);
    // Implementa tu lógica aquí
    cerrarTodosLosMenus();
  }

  function resetearPassword(usuario_id) {
    console.log(`Resetear contraseña del usuario ${usuario_id}`);
    // Implementa tu lógica aquí
    cerrarTodosLosMenus();
  }
</script>