<?php
require_once APP_ROOT . 'public/inc/head.php';
require_once APP_ROOT . 'public/inc/navbar.php';
?>

<div class="container">
  <div class="right_content">
    <div class="navigation-header">
      <nav class="breadcrumb" id="breadcrumb-nav">
        <a href="<?= APP_URL ?>home">Inicio</a>
        <span class="breadcrumb-separator">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </span>
        <span>Usuarios</span>
      </nav>
    </div>
    <div class="tools">
      <form class="filter_form" id="filter_form">
        <div class="input-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
            <circle cx="11" cy="11" r="8" />
            <path d="m21 21-4.35-4.35" />
          </svg>
          <input type="text" id="matchingInput" class="matching-search" placeholder="Buscar paciente">
          <span class="clear-button" id="clearButton" onclick="clearInput()">×</span>
        </div>

        <select id="filterColumn" class="custom-select">
          <option value="0">Todos los campos</option>
          <option value="0">Código</option>
          <option value="1">Nombre</option>
          <option value="3">Nivel</option>
          <option value="4">Estado</option>
        </select>
      </form>
    </div>

    <div class="table-container" id="table-container">
      <div class="table-loading-container" id="table-loading">
        <div class="table-loading">
          <div class="table-spinner"></div>
          <p>Cargando pacientes...</p>
        </div>
      </div>

      <table id="patients-table" class="hover nowrap cell-borders" style="display: none;">
        <thead>
          <tr>
            <th class="dt-head-center">CÓDIGO</th>
            <th>NOMBRE COMPLETO</th>
            <th class="dt-head-center">EDAD</th>
            <th class="dt-head-center">NIVEL ACTUAL</th>
            <th class="dt-head-center">ESTADO ESTUDIO</th>
            <th class="dt-head-center">ACCIONES</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<?php require_once APP_ROOT . 'public/inc/scripts.php' ?>

<script>
  let table;
  let matchingInput = document.getElementById('matchingInput');
  let filter_form = document.getElementById('filter_form');
  let filterColumn = document.getElementById('filterColumn');
  let clearButton = document.getElementById('clearButton');

  // --- BÚSQUEDA Y FILTRO DE REGISTROS ---
  matchingInput.addEventListener('input', () => {
    applyFilter();
  });

  filter_form.addEventListener('submit', (event) => {
    event.preventDefault();
  });

  filterColumn.addEventListener('change', () => {
    applyFilter();
  });

  function clearInput() {
    matchingInput.value = '';
    matchingInput.focus();
    clearButton.style.display = 'none';
    applyFilter();
  }

  function applyFilter() {
    let searchValue = matchingInput.value.trim();
    let columnIndex = filterColumn.value;
    clearButton.style.display = searchValue ? 'block' : 'none';

    if (columnIndex == 0) {
      // Filtro global
      table.columns().search('');
      table.search(searchValue, false, true).draw();
    } else {
      // Filtro por columna específica
      table.search('');
      table.columns().search('');
      table.column(columnIndex).search(searchValue, false, true).draw();
    }
  }

  // --- CARGA DE DATOS ---
  document.addEventListener('DOMContentLoaded', () => {
    // Mostrar la tabla para que DataTables pueda inicializarse
    document.getElementById('patients-table').style.display = '';

    table = new DataTable('#patients-table', {
      fixedColumns: {
        end: 1
      },
      scrollX: true,
      columnDefs: [{
        targets: [0, 2, 3, 4], // Ajustado para las nuevas columnas
        className: 'dt-body-center'
      }, {
        targets: [5], // Columna de acciones
      }],
      language: {
        "zeroRecords": "No se encontraron registros",
        "emptyTable": "Aún no hay pacientes registrados",
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
          if (td.textContent.includes('Activo')) {
            td.classList.add('activo');
          }
          if (td.textContent.includes('Inactivo')) {
            td.classList.add('inactivo');
          }
        });
      }
    });
  });

  // Cargar datos
  async function loadData() {
    try {
      showTableLoading('Cargando pacientes...');

      const response = await fetch('<?= APP_URL ?>api/studies/patients-info', {
        method: 'GET',
        headers: new Headers(),
        mode: 'cors',
        cache: 'no-cache',
      });

      if (!response.ok) {
        throw new Error(`Error HTTP: ${response.status}`);
      }

      const data = await response.json();

      if (data.status === "success" && data.data) {
        // Limpiar tabla
        table.clear();

        data.data.forEach(item => {

          // establecer la edad actual del paciente basado en la fecha de nacimiento utilizando el formato años y si tiene 0 años
          // mostrar meses y dias
          item.edad_completa = calcularEdadCompleta(item.fecha_nacimiento);

          // Construir los botones de acción
          let actionsHtml = '';

          // Botón Nuevo Estudio - siempre disponible
          <?php if (\App\Core\Auth::can('patients.create')): ?>
            actionsHtml += `
                    <button type="button" class="editar" onClick="createNewStudy(${item.id})" title="Crear nuevo estudio">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </button>`;
          <?php endif; ?>

          // Botones condicionales según el estado
          if (item.estado_estudios === 'CON_ESTUDIO_ACTIVO') {
            <?php if (\App\Core\Auth::can('patients.view')): ?>
              actionsHtml += `
                        <button type="button" class="opciones" onClick="viewActiveStudy(${item.id}, ${item.estudio_activo_id})" title="Ver estudio activo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>`;
            <?php endif; ?>

            <?php if (\App\Core\Auth::can('patients.edit')): ?>
              actionsHtml += `
                        <button type="button" class="editar" onClick="editActiveStudy(${item.id}, ${item.estudio_activo_id})" title="Editar estudio activo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>`;
            <?php endif; ?>
          }

          // Botón Historial - si tiene estudios
          if (item.total_estudios > 0) {
            <?php if (\App\Core\Auth::can('patients.view')): ?>
              actionsHtml += `
                        <button type="button" class="opciones" onClick="viewStudyHistory(${item.id})" title="Ver historial de estudios">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                        </button>`;
            <?php endif; ?>
          }

          // Botón Copiar - si tiene estudios previos
          if (item.total_estudios > 0) {
            <?php if (\App\Core\Auth::can('patients.create')): ?>
              actionsHtml += `
                        <button type="button" class="opciones" onClick="copyPreviousStudy(${item.id})" title="Copiar estudio anterior">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                            </svg>
                        </button>`;
            <?php endif; ?>
          }

          // Agregar fila a la tabla (sin la columna "Tiene Nivel")
          table.row.add([
            item.codigo || 'S/C',
            item.nombre_completo.toLowerCase().replace(/\b\w/g, char => char.toUpperCase()),
            item.edad_completa,
            item.nivel_socioeconomico || 'Sin asignar',
            getEstudioStatusText(item.estado_estudios),
            actionsHtml
          ]);
        });

        hideTableLoading();
        table.draw();

      } else {
        throw new Error("No se encontraron datos");
      }

    } catch (error) {
      console.error("Error al cargar datos:", error);
      showTableError('No se pudieron cargar los pacientes. Por favor, inténtalo de nuevo.');
    }
  }

  // Función para calcular edad completa
  function calcularEdadCompleta(fechaNacimiento) {
    const nacimiento = new Date(fechaNacimiento);
    const hoy = new Date();

    let años = hoy.getFullYear() - nacimiento.getFullYear();
    let meses = hoy.getMonth() - nacimiento.getMonth();
    let días = hoy.getDate() - nacimiento.getDate();

    // Ajustar si los días son negativos
    if (días < 0) {
      meses--;
      const ultimoDiaMesAnterior = new Date(hoy.getFullYear(), hoy.getMonth(), 0).getDate();
      días += ultimoDiaMesAnterior;
    }

    // Ajustar si los meses son negativos
    if (meses < 0) {
      años--;
      meses += 12;
    }

    // Formatear según la lógica requerida
    if (años === 0) {
      // Si tiene 0 años, mostrar solo meses y días
      if (meses === 0) {
        return `${días} día${días !== 1 ? 's' : ''}`;
      } else {
        return `${meses} mes${meses !== 1 ? 'es' : ''}, ${días} día${días !== 1 ? 's' : ''}`;
      }
    } else {
      // Si tiene 1 año o más, mostrar años
      return `${años} año${años !== 1 ? 's' : ''}`;
    }
  }

  function getEstudioStatusText(status) {
    switch (status) {
      case 'CON_ESTUDIO_ACTIVO':
        return 'Activo';
      case 'CON_ESTUDIOS_INACTIVOS':
        return 'Inactivo';
      case 'SIN_ESTUDIOS':
        return 'Sin estudios';
      default:
        return 'Desconocido';
    }
  }

  // Funciones de loading (permanecen igual)
  function showTableLoading(message = 'Cargando pacientes...') {
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

    const tableWrapper = document.getElementById('patients-table_wrapper');
    if (tableWrapper) {
      tableWrapper.classList.remove('show');
      tableWrapper.style.display = 'none';
    }

    container.classList.add('loading');
  }

  function hideTableLoading() {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');
    const tableWrapper = document.getElementById('patients-table_wrapper');

    if (loadingContainer) {
      loadingContainer.classList.remove('show');
      setTimeout(() => {
        loadingContainer.style.display = 'none';
      }, 300);
    }

    if (tableWrapper) {
      tableWrapper.style.display = '';
      setTimeout(() => {
        tableWrapper.classList.add('show');
      }, 50);
    }

    container.classList.remove('loading');
  }

  function showTableError(message) {
    const container = document.getElementById('table-container');
    const loadingContainer = document.getElementById('table-loading');

    if (loadingContainer) {
      loadingContainer.innerHTML = `
            <div class="table-loading">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
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

  // --- FUNCIONES DE ACCIÓN ---
  function createNewStudy(patientId) {
    window.location.href = `<?= APP_URL ?>studies/create/${patientId}`;
  }

  function viewActiveStudy(patientId, studyId) {
    window.location.href = `<?= APP_URL ?>studies/view/${patientId}/${studyId}`;
  }

  function editActiveStudy(patientId, studyId) {
    window.location.href = `<?= APP_URL ?>studies/edit/${patientId}/${studyId}`;
  }

  function viewStudyHistory(patientId) {
    window.location.href = `<?= APP_URL ?>studies/history/${patientId}`;
  }

  function copyPreviousStudy(patientId) {
    if (confirm('¿Desea crear una copia del último estudio socioeconómico de este paciente?')) {
      window.location.href = `<?= APP_URL ?>studies/copy/${patientId}`;
    }
  }
</script>