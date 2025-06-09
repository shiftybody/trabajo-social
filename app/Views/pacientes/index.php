<?php
use App\Core\Auth;
use App\Core\View;
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
            <option value="1">Folio</option>
            <option value="2">Nombre</option>
            <option value="3">Fecha de Nacimiento</option>
            <option value="4">Lugar de Nacimiento</option>
            <option value="5">Fecha de Ingreso</option>
          </select>
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="select-filter-icon icon icon-tabler icons-tabler-outline icon-tabler-filter">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
          </svg>
        </div>
      </form>

      <?php if (Auth::can('users.create')): ?>
        <button type="button" class="action_create_new" onclick="cargarFormularioEstudio()">Nuevo</button>
      <?php endif; ?>
    </div>

    <!-- Loading inicial -->
    <div class="table-loading-container" id="table-loading">
      <div class="table-loading">
        <div class="table-spinner"></div>
        <p>Cargando usuarios...</p>
      </div>
    </div>

    <div id="form-container" style="display: none;"></div>

    <div id="tabla-pacientes">
      <!-- Aquí se cargará la tabla con los datos generales -->
    </div>
  </div>
</div>

<script>
  let formularios = [
    'form_Datos_Generales.php',
    'form_Integrantes_de_la_familia.php',
    'form_Relaciones_familiares.php',
    'form_Salud.php',
    'form_Alimentacion.php',
    'form_vivienda.php',
    'form_Economia.php', 
  ];
  let indiceFormulario = 0;
  let table;
  let matchingInput = document.getElementById('matchingInput');
  let filter_form = document.getElementById('filter_form');
  let filterColumn = document.getElementById('filterColumn');
  let clearButton = document.getElementById('clearButton');

  async function cargarFormularioEstudio() {
    indiceFormulario = 0;
    await cargarSiguienteFormulario();
  }

  async function cargarSiguienteFormulario() {
    const contenedor = document.getElementById('form-container');

    if (indiceFormulario < formularios.length) {
      try {
        const res = await fetch('pacientes/estudio/' + formularios[indiceFormulario]);
        const html = await res.text();
        contenedor.innerHTML = html;
        contenedor.style.display = 'block';
        indiceFormulario++;
      } catch (err) {
        contenedor.innerHTML = '<p>Error al cargar el formulario.</p>';
      }
    } else {
      contenedor.style.display = 'none';
      loadData();
    }
  }

  async function loadData() {
    try {
      showTableLoading('Cargando usuarios...');

      const response = await fetch('<?= APP_URL ?>pacientes/get_datos_generales.php');
      const html = await response.text();
      document.getElementById('tabla-pacientes').innerHTML = html;

      const tabla = document.getElementById('tabla-datos-generales');
      if (!tabla) return;

      table = new DataTable('#tabla-datos-generales', {
        fixedColumns: { end: 1 },
        scrollX: true,
        columnDefs: [{ targets: [0, 4, 5], className: 'dt-body-center' }],
        layout: { topStart: null, buttomStart: null, buttomEnd: null },
        language: {
          zeroRecords: "No se encontraron registros",
          emptyTable: "Aún no hay registros, crea uno nuevo aquí",
          info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
          infoEmpty: "Mostrando 0 a 0 de 0 registros",
          infoFiltered: "(filtrado de _MAX_ registros totales)",
          processing: '<div class="table-spinner"></div>Procesando...'
        },
        drawCallback: () => {
          document.querySelectorAll('td').forEach(td => {
            if (td.textContent === 'Inactivo') td.classList.add('inactivo');
            if (td.textContent === 'Activo') td.classList.add('activo');
          });
        }
      });

      // Filtros y búsqueda
      matchingInput.addEventListener('input', applyFilter);
      filter_form.addEventListener('submit', (e) => e.preventDefault());
      filterColumn.addEventListener('change', applyFilter);

    } catch (error) {
      console.error("Error al cargar datos:", error);
    }
  }

  function applyFilter() {
    let searchValue = matchingInput.value.trim();
    let columnIndex = filterColumn.value;
    clearButton.style.display = searchValue ? 'block' : 'none';

    if (columnIndex == 0) {
      table.columns().search('');
      table.search(searchValue, false, true).draw();
    } else {
      table.search('');
      table.columns().search('');
      table.column(columnIndex).search(searchValue, false, true).draw();
    }
  }

  function clearInput() {
    matchingInput.value = '';
    matchingInput.focus();
    clearButton.style.display = 'none';
    applyFilter();
  }

  function showTableLoading(message = 'Cargando...') {
    document.getElementById('table-loading').style.display = 'flex';
  }

  function hideTableLoading() {
    document.getElementById('table-loading').style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('tabla-pacientes').innerHTML = '<p>Cargando tabla...</p>';
    loadData();
  });
</script>
