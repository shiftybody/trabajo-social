<!-- los campos de la tabla deben ser NO, NOMBRE COMPLETO, 
NOMBRE DE USUARIO, CORREO, ESTADO, ROL y ACCIONES -->

<!-- EL CAMPO ACCIONES debe de permitir editar ✏️, remover ❌, 
y 3 botoncitos para cambiar el estado o el rol -->

<!-- TODO: cambiar de img a iconos en svg -->

<style>
  .container {
    padding: 0 10rem;
  }

  .navigation {
    /* mover todo a la derecha utilizando flex */
    display: flex;
    justify-content: flex-end;
    /* espacio arriba y abajo de 2 rem por lado */
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
    /* text-sm/font-semibold */
    font-size: 14px;
    font-style: normal;
    font-weight: 600;
    line-height: 150%;
    /* 21px */
  }

  .body {
    display: grid;
    grid-template-columns: 0.7fr 3fr;
    gap: 2rem;
  }

  hr {
    width: 251px;
  }

  /* a partir de aqui es lo bueno  */
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
    /* Necesario para el indicador */
  }

  .side_button.active {
    background: color(display-p3 0.9255 0.9255 0.9255);
  }

  .side_button:hover {
    background: color(display-p3 0.9255 0.9255 0.9255);
  }

  .side_button:hover::before {
    content: "";
    position: absolute;
    left: -0.4rem;
    /* Separa el indicador del botón */
    top: 50%;
    transform: translateY(-50%);
    height: 80%;
    /* Altura del indicador */
    width: 4px;
    /* Ancho del indicador */
    background-color: #007bff;
    /* Azul del indicador */
    border-radius: 2px;
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

  /* right side styles */
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

    /* text-sm/font-bold */
    font-size: 14px;
    font-style: normal;
    font-weight: 700;
    line-height: 150%;
    /* 21px */
  }

  .filter_icon {
    display: flex;
    align-items: center;
  }


  table {
    border-radius: var(--rounded-lg, 8px);
    background: var(--white, #FFF);
    background: var(--white, color(display-p3 1 1 1));

    /* shadow */
    box-shadow: 0px 1px 3px 0px rgba(0, 0, 0, 0.10), 0px 1px 2px -1px rgba(0, 0, 0, 0.10);
    box-shadow: 0px 1px 3px 0px color(display-p3 0 0 0 / 0.10), 0px 1px 2px -1px color(display-p3 0 0 0 / 0.10);
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

    /* text-sm/font-normal */
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 150%;
    /* 21px */
  }

  /* ultimo td de un tr */
  td:last-child {
    display: flex;
    gap: 0.4rem;
  }

  /* botton con el atributo bottom y clase editar*/
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

  #matchingInput {
    padding-right: 24px;
    /* Espacio para el botón de limpiar */
  }

  .clear-button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #aaa;
    display: none;
    /* Ocultar por defecto */
  }

  .input-container input:focus+.clear-button,
  .input-container input:not(:placeholder-shown)+.clear-button {
    display: inline;
  }

  td.activo {
    color: #007bff;
    font-weight: bold;
  }

  td.inactivo {
    color: #dc3545;
    font-weight: bold;
  }
</style>
<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>
<div class="container">
  <div class=" navigation">
    <a href="../dashboard" class="volver">Regresar</a>
  </div>

  <!-- TODO: cambiar el nombre de la pagina mostrada en el header -->

  <div class="body">
    <!-- left side  -->
    <div class="left_side">
      <hr>
      <div class="side_button active">
        <a href="#" class="side_button_content">
          <img src="<?= APP_URL; ?>public/icons/user.svg" alt="" class="side_icon_button">
          Usuarios</a>
      </div>
      <div class="side_button">
        <a href="#" class="side_button_content">
          <img src="<?= APP_URL; ?>public/icons/golf.svg" alt="" class="side_icon_button">
          Roles</a>
      </div>
    </div>

    <!-- right side -->
    <div class="right_side">
      <div class="right_content">
        <div class="tools">
          <form action="" class="filter_form" id="filter_form">
            <select name="filterColumn" id="filterColumn">
              <option value="0">Todo</option>
              <option value="1">Nombre</option>
              <option value="2">Usuario</option>
              <option value="3">Correo</option>
              <option value="4">Estado</option>
              <option value="5">Rol</option>
            </select>
            <div class="input-container">
              <input type="text" name="matchingColumn" id="matchingInput" placeholder="Buscar">
              <span class="clear-button">×</span>
            </div>
          </form>

          <button class="action_create_new" onclick="goTo('users/create')">Nuevo</button>
        </div>
        <div class="table">
          <table id="myTable">
            <thead>
              <tr>
                <th>NO</th>
                <th>NOMBRE COMPLETO</th>
                <th>NOMBRE DE USUARIO</th>
                <th>CORREO</th>
                <th>ESTADO</th>
                <th>ROL</th>
                <th>ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <!--  -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
require_once APP_ROOT . 'public/inc/scripts.php';
?>
<script>
  let table = new DataTable('#myTable', {
    lengthChange: false,
    layout: {
      topStart: null,
      buttomStart: null,
      buttomEnd: null
    },
    language: {
      "zeroRecords": "No se encontraron registros",
      "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
      "infoEmpty": "Mostrando 0 a 0 de 0 registros",
      "infoFiltered": "(filtrado de _MAX_ registros totales)",
    }
  });

  let headers = new Headers();

  let data = new FormData();
  data.append('modulo_usuario', 'leer');

  let config = {
    method: 'POST',
    headers: headers,
    mode: 'cors',
    cache: 'no-cache',
    body: data
  };

  loadData();

  function loadData() {
    let incremental = 1;
    fetch('<?= APP_URL ?>public/js/ajax/usuarioAjax.php', config)
      .then(response => response.json())
      .then(data => {
        console.log(data);
        data.forEach(item => {
          table.row.add([
            incremental++,
            `${item.usuario_nombre} ${item.usuario_apellido_paterno} ${item.usuario_apellido_materno}`,
            item.usuario_usuario,
            item.usuario_email,
            item.rol_descripcion,
            item.usuario_estado === "1" ? 'activo' : 'inactivo',
            `<button type="button" class="editar" onClick="actualizar(${item.usuario_id})">
              <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg>
            </button>
            <button type="button" class="remover" onClick="remover(${item.usuario_id})">
              <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
            </button> 
            <button type="button" class="opciones" onClick="mostrarOpciones($item.usuario_id)">
              <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-dots-vertical"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /></svg>
            </button>`
          ]).draw()
        });
      });
  }

  let filter_form = document.getElementById('filter_form');

  let matchingInput = document.getElementById('matchingInput');
  let filterColumn = document.getElementById('filterColumn');

  filter_form.addEventListener('submit', () => {
    event.preventDefault();
  });

  // Evento para el input de búsqueda
  matchingInput.addEventListener('input', () => {
    applyFilter();
  });

  // Evento para el cambio en el select
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

    // cuando table cambie 
    table.on('draw', () => {
      document.querySelectorAll('td').forEach(td => {
        if (td.textContent === 'inactivo') {
          td.classList.add('inactivo');
        }
      });

      // si tiene la palabra activo se le agrega la clase activo
      document.querySelectorAll('td').forEach(td => {
        if (td.textContent === 'activo') {
          td.classList.add('activo');
        }
      });
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
        // volver a cargar la tabla
        table.clear();
        loadData();
      })
      .catch(error => console.error(error));
  }

  function actualizar(usuario_id) {
    window.location.href = `<?= APP_URL ?>users/update/${usuario_id}`;
  }
</script>