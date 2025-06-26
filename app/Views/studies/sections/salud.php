<?php
// app/Views/studies/sections/salud.php
?>
<div class="section-container">
  <h2 class="section-title">Información de Salud Familiar</h2>

  <fieldset class="form-fieldset">
    <legend>Acceso a Servicios de Salud</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="familia_con_seguridad_social">¿La familia cuenta con seguridad social? *</label>
        <div class="radio-group">
          <label class="radio-label">
            <input type="radio" name="familia_con_seguridad_social" value="1" required>
            Sí
          </label>
          <label class="radio-label">
            <input type="radio" name="familia_con_seguridad_social" value="0" required>
            No
          </label>
        </div>
      </div>
      <div class="form-group">
        <label for="frecuencia_asistencia_medico">Frecuencia de asistencia al médico *</label>
        <select id="frecuencia_asistencia_medico" name="frecuencia_asistencia_medico" required>
          <option value="">Seleccione...</option>
          <option value="semanal">Semanal</option>
          <option value="mensual">Mensual</option>
          <option value="trimestral">Trimestral</option>
          <option value="semestral">Semestral</option>
          <option value="anual">Anual</option>
          <option value="solo_emergencias">Solo en emergencias</option>
          <option value="nunca">Nunca</option>
        </select>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-fieldset">
    <legend>Enfermedades y Discapacidades</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="num_parientes_enf_disc">¿Cuántos parientes tienen enfermedad o discapacidad? *</label>
        <select id="num_parientes_enf_disc" name="num_parientes_enf_disc" required
          data-criteria="salud.num_parientes_enf_disc">
          <option value="">Seleccione...</option>
          <option value="0">Ninguno</option>
          <option value="1">1 pariente</option>
          <option value="2">2 parientes</option>
          <option value="3">3 parientes</option>
          <option value="4">4 parientes</option>
          <option value="5">5 o más parientes</option>
        </select>
        <div class="criteria-result" id="criteria-num_parientes_enf_disc"></div>
      </div>
    </div>

    <!-- Tabla dinámica para parientes enfermos -->
    <div class="dynamic-table-container" id="health-relatives-container" style="display: none;">
      <div class="table-header">
        <h3>Parientes con Enfermedades o Discapacidades</h3>
        <button type="button" class="btn btn-primary btn-sm" id="add-health-relative">
          + Agregar Pariente
        </button>
      </div>

      <div class="table-responsive">
        <table class="dynamic-table" id="health-relatives-table">
          <thead>
            <tr>
              <th>Parentesco *</th>
              <th>Enfermedad/Discapacidad *</th>
              <th>¿Tiene Tratamiento? *</th>
              <th>Institución de Tratamiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="health-relatives-body">
            <!-- Filas dinámicas se insertan aquí -->
          </tbody>
        </table>
      </div>
    </div>
  </fieldset>

  <div class="section-validation">
    <div class="validation-summary" id="salud-validation">
      <ul id="salud-errors"></ul>
    </div>
  </div>
</div>

<!-- Template para nueva fila de pariente enfermo -->
<template id="health-relative-row-template">
  <tr class="dynamic-row" data-row-index="">
    <td>
      <select name="health_relatives[INDEX][parentesco]" required class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="padre">Padre</option>
        <option value="madre">Madre</option>
        <option value="hijo">Hijo/a</option>
        <option value="hermano">Hermano/a</option>
        <option value="abuelo">Abuelo/a</option>
        <option value="tio">Tío/a</option>
        <option value="primo">Primo/a</option>
        <option value="conyugue">Cónyuge</option>
        <option value="otro">Otro</option>
      </select>
    </td>
    <td>
      <input type="text" name="health_relatives[INDEX][enfermedad_discapacidad]"
        placeholder="Describe la enfermedad o discapacidad" required class="form-input-sm">
    </td>
    <td>
      <select name="health_relatives[INDEX][tiene_tratamiento]" required class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="1">Sí</option>
        <option value="0">No</option>
      </select>
    </td>
    <td>
      <input type="text" name="health_relatives[INDEX][institucion_tratamiento]"
        placeholder="Institución (opcional)" class="form-input-sm">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-xs remove-row"
        title="Eliminar pariente">
        ✕
      </button>
    </td>
  </tr>
</template>