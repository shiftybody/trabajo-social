<?php
// app/Views/studies/create.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $titulo ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/studies.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/form.css">
  <link rel="stylesheet" href="<?= APP_URL ?>public/css/inc/modal.css">
</head>

<body>
  <div class="study-container">
    <!-- Header del Estudio -->
    <div class="study-header">
      <div class="study-title">
        <h1>Nuevo Estudio Socioeconómico</h1>
        <p class="patient-info">
          <strong><?= $patient->nombre . ' ' . $patient->apellido_paterno . ' ' . $patient->apellido_materno ?></strong>
          <span class="patient-details">
            • Expediente: <?= $patient->numero_expediente ?>
            • Protocolo: <?= $patient->protocolo ?>
          </span>
        </p>
      </div>
      <div class="study-actions">
        <button type="button" class="btn btn-secondary" id="save-draft">
          Guardar Borrador
        </button>
        <button type="button" class="btn btn-primary" id="calculate-score" style="display: none;">
          Calcular Puntaje
        </button>
      </div>
    </div>

    <!-- Barra de Progreso -->
    <div class="progress-container">
      <div class="progress-bar">
        <div class="progress-fill" id="progress-fill"></div>
      </div>
      <span class="progress-text" id="progress-text">0% completado</span>
    </div>

    <!-- Navegación por Pestañas -->
    <div class="tabs-navigation">
      <div class="tabs-container">
        <button class="tab-button active" data-tab="datos-generales">
          <span class="tab-number">1</span>
          <span class="tab-label">Datos Generales</span>
          <span class="tab-status" id="status-datos-generales"></span>
        </button>
        <button class="tab-button" data-tab="familia">
          <span class="tab-number">2</span>
          <span class="tab-label">Familia</span>
          <span class="tab-status" id="status-familia"></span>
        </button>
        <button class="tab-button" data-tab="relaciones-familiares">
          <span class="tab-number">3</span>
          <span class="tab-label">Relaciones Familiares</span>
          <span class="tab-status" id="status-relaciones-familiares"></span>
        </button>
        <button class="tab-button" data-tab="salud">
          <span class="tab-number">4</span>
          <span class="tab-label">Salud</span>
          <span class="tab-status" id="status-salud"></span>
        </button>
        <button class="tab-button" data-tab="alimentacion">
          <span class="tab-number">5</span>
          <span class="tab-label">Alimentación</span>
          <span class="tab-status" id="status-alimentacion"></span>
        </button>
        <button class="tab-button" data-tab="vivienda">
          <span class="tab-number">6</span>
          <span class="tab-label">Vivienda</span>
          <span class="tab-status" id="status-vivienda"></span>
        </button>
        <button class="tab-button" data-tab="economia">
          <span class="tab-number">7</span>
          <span class="tab-label">Economía</span>
          <span class="tab-status" id="status-economia"></span>
        </button>
        <button class="tab-button" data-tab="resumen" disabled>
          <span class="tab-number">8</span>
          <span class="tab-label">Resumen</span>
          <span class="tab-status" id="status-resumen"></span>
        </button>
      </div>
    </div>

    <!-- Contenido Principal -->
    <div class="study-content">
      <form id="study-form" method="POST">
        <input type="hidden" id="patient_id" name="patient_id" value="<?= $patient->paciente_id ?>">
        <input type="hidden" id="study_id" name="study_id" value="">

        <!-- Sección 1: Datos Generales -->
        <div class="tab-content active" id="tab-datos-generales">
          <?php include APP_ROOT . 'app/Views/studies/sections/datos_generales.php'; ?>
        </div>

        <!-- Sección 2: Familia -->
        <div class="tab-content" id="tab-familia">
          <?php include APP_ROOT . 'app/Views/studies/sections/familia.php'; ?>
        </div>

        <!-- Sección 3: Relaciones Familiares -->
        <div class="tab-content" id="tab-relaciones-familiares">
          <?php include APP_ROOT . 'app/Views/studies/sections/relaciones_familiares.php'; ?>
        </div>

        <!-- Sección 4: Salud -->
        <div class="tab-content" id="tab-salud">
          <?php include APP_ROOT . 'app/Views/studies/sections/salud.php'; ?>
        </div>

        <!-- Sección 5: Alimentación -->
        <div class="tab-content" id="tab-alimentacion">
          <?php include APP_ROOT . 'app/Views/studies/sections/alimentacion.php'; ?>
        </div>

        <!-- Sección 6: Vivienda -->
        <div class="tab-content" id="tab-vivienda">
          <?php include APP_ROOT . 'app/Views/studies/sections/vivienda.php'; ?>
        </div>

        <!-- Sección 7: Economía -->
        <div class="tab-content" id="tab-economia">
          <?php include APP_ROOT . 'app/Views/studies/sections/economia.php'; ?>
        </div>

        <!-- Sección 8: Resumen -->
        <div class="tab-content" id="tab-resumen">
          <?php include APP_ROOT . 'app/Views/studies/sections/resumen.php'; ?>
        </div>
      </form>
    </div>

    <!-- Navegación Inferior -->
    <div class="study-navigation">
      <button type="button" class="btn btn-secondary" id="prev-tab" disabled>
        ← Anterior
      </button>
      <button type="button" class="btn btn-primary" id="next-tab">
        Siguiente →
      </button>
      <button type="button" class="btn btn-success" id="finalize-study" style="display: none;">
        Finalizar Estudio
      </button>
    </div>
  </div>

  <!-- Scripts -->
  <script src="<?= APP_URL ?>public/js/studies-form.js"></script>
  <script src="<?= APP_URL ?>public/js/dynamic-tables.js"></script>
  <script src="<?= APP_URL ?>public/js/multiple-options.js"></script>
  <script src="<?= APP_URL ?>public/js/criteria-evaluation.js"></script>
  <script src="<?= APP_URL ?>public/js/file-upload.js"></script>
  <script src="<?= APP_URL ?>public/js/form-validation.js"></script>

  <script>
    // Inicializar el formulario de estudios
    document.addEventListener('DOMContentLoaded', function() {
      const studyForm = new StudyForm({
        patientId: <?= $patient->paciente_id ?>,
        isEditing: false,
        apiBaseUrl: '<?= APP_URL ?>api/studies'
      });
    });
  </script>
</body>

</html>