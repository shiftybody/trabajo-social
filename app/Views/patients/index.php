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
        <input class="matching-search" id="matchingInput" placeholder="Buscar pacientes">
        <span class="clear-button" id="clearButton">×</span>
      </div>
    </form>

   <?php if (\App\Core\Auth::can('patients.create')): ?>

      <button type="button" onclick="mostrarFormularioDatosGenerales()">Nuevo Paciente</button>
    <?php endif; ?>
  </div>

  <div id="formulario-container"></div>

  <div class="table-container" id="table-container">
    <div class="table-loading-container" id="table-loading">
      <div class="table-loading">
        <div class="table-spinner"></div>
        <p>Cargando pacientes...</p>
      </div>
    </div>

    <table id="paciente-table" class="hover nowrap cell-borders" style="display: none;">
      <thead>
        <tr>
          <th class="dt-head-left">FOLIO</th>
          <th class="dt-head-left">NOMBRE COMPLETO</th>
          <th class="dt-head-left">FECHA DE NACIMIENTO</th>
          <th class="dt-head-left">PROTOCOLO</th>
          <th class="dt-head-left">ACCIONES</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<?= require_once APP_ROOT . 'public/inc/scripts.php' ?>

<script>
  let table;
  document.addEventListener('DOMContentLoaded', () => {
    showTableLoading('Cargando pacientes...');
    document.getElementById('paciente-table').style.display = '';

    table = new DataTable('#paciente-table', {
      columnDefs: [{
        targets: "_all",
        className: 'dt-body-left'
      }],
      language: {
        zeroRecords: "No se encontraron pacientes",
        emptyTable: "Aún no hay pacientes, crea uno nuevo aquí",
        info: "Mostrando _START_ a _END_ de _TOTAL_ pacientes",
        infoEmpty: "Mostrando 0 a 0 de 0 pacientes",
        infoFiltered: "(filtrado de _MAX_ pacientes totales)",
        processing: '<div class="table-spinner"></div>Procesando...'
      },
      initComplete: function() {
        loadData();
      }
    });

    // Filtro en la búsqueda
    const matchingInput = document.getElementById('matchingInput');
    const clearButton = document.getElementById('clearButton');

    matchingInput.addEventListener('input', () => {
      table.search(matchingInput.value.trim()).draw();
      clearButton.style.display = matchingInput.value ? 'inline' : 'none';
    });

    clearButton.addEventListener('click', () => {
      matchingInput.value = '';
      matchingInput.focus();
      clearButton.style.display = 'none';
      table.search('').draw();
    });
  });

  // Cargar pacientes
  async function loadData() {
    try {
      const response = await fetch('<?= APP_URL ?>api/pacientes');
      if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
      const data = await response.json();

      if (data.status === 'success' && data.data) {
        table.clear();

        data.data.forEach(item => {
          const nombreCompleto = `${item.nombre} ${item.apellido_paterno} ${item.apellido_materno}`;
          const acciones = `
            <button onclick="editarPaciente(${item.id})">Editar</button>
            <button onclick="eliminarPaciente(${item.id})">Eliminar</button>
            <button onclick="agregarEstudio(${item.id})">Agregar Estudio</button>`;

          table.row.add([
            item.id,
            nombreCompleto,
            item.fecha_nacimiento,
            item.protocolo,
            acciones
          ]);
        });

        table.draw();
        hideTableLoading();
      } else {
        throw new Error('No se encontraron datos');
      }
    } catch (error) {
      console.error('Error al cargar datos:', error);
      showTableError('No se pudieron cargar los pacientes.');
    }
  }

  function mostrarFormularioDatosGenerales() {
    fetch('<?= APP_URL ?>patients/studies/DatosGenerales.php')
      .then(res => res.text())
      .then(html => {
        document.getElementById('formulario-container').innerHTML = html;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
  }

  function editarPaciente(id) {
    window.location.href = `<?= APP_URL ?>pacientes/editar/${id}`;
  }

  function eliminarPaciente(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este paciente?')) {
      // Aquí la lógica para eliminar con fetch POST/DELETE
    }
  }

  function agregarEstudio(id) {
    window.location.href = `<?= APP_URL ?>patients/studies/${id}`;
  }
</script>
