<?php
// app/Views/studies/sections/familia.php
?>
<div class="section-container">
  <h2 class="section-title">Información Familiar</h2>

  <fieldset class="form-fieldset">
    <legend>Datos Básicos de la Familia</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="numero_integrantes">Número de Integrantes *</label>
        <input type="number" id="numero_integrantes" name="numero_integrantes"
          min="1" max="20" required data-criteria="familia.numero_integrantes">
        <div class="criteria-result" id="criteria-numero_integrantes"></div>
      </div>
      <div class="form-group">
        <label for="numero_hijos">Número de Hijos *</label>
        <input type="number" id="numero_hijos" name="numero_hijos"
          min="0" max="15" required data-criteria="familia.numero_hijos">
        <div class="criteria-result" id="criteria-numero_hijos"></div>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="tipo_familia">Tipo de Familia *</label>
        <select id="tipo_familia" name="tipo_familia" required
          data-criteria="familia.tipo_familia">
          <option value="">Seleccione...</option>
          <option value="nuclear">Nuclear</option>
          <option value="extensa">Extensa</option>
          <option value="monoparental">Monoparental</option>
          <option value="reconstituida">Reconstituida</option>
          <option value="unipersonal">Unipersonal</option>
        </select>
        <div class="criteria-result" id="criteria-tipo_familia"></div>
      </div>
      <div class="form-group">
        <label for="ciclo_vital_familia">Ciclo Vital de la Familia *</label>
        <select id="ciclo_vital_familia" name="ciclo_vital_familia" required>
          <option value="">Seleccione...</option>
          <option value="formacion">Formación</option>
          <option value="expansion">Expansión</option>
          <option value="consolidacion">Consolidación</option>
          <option value="contraccion">Contracción</option>
          <option value="independencia">Independencia</option>
        </select>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-fieldset">
    <legend>Integrantes de la Familia</legend>

    <div class="dynamic-table-container">
      <div class="table-header">
        <h3>Lista de Integrantes</h3>
        <button type="button" class="btn btn-primary btn-sm" id="add-family-member">
          + Agregar Integrante
        </button>
      </div>

      <div class="table-responsive">
        <table class="dynamic-table" id="family-members-table">
          <thead>
            <tr>
              <th>Nombre Completo *</th>
              <th>Género *</th>
              <th>Edad *</th>
              <th>Parentesco *</th>
              <th>Escolaridad</th>
              <th>Estado Civil</th>
              <th>Ocupación</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="family-members-body">
            <!-- Filas dinámicas se insertan aquí -->
          </tbody>
        </table>
      </div>

      <div class="table-summary">
        <p>Total de integrantes: <span id="family-members-count">0</span></p>
      </div>
    </div>
  </fieldset>

  <div class="section-validation">
    <div class="validation-summary" id="familia-validation">
      <ul id="familia-errors"></ul>
    </div>
  </div>
</div>

<!-- Template para nueva fila de integrante -->
<template id="family-member-row-template">
  <tr class="dynamic-row" data-row-index="">
    <td>
      <input type="text" name="members[INDEX][nombre_completo]"
        placeholder="Nombre completo" required class="form-input-sm">
    </td>
    <td>
      <select name="members[INDEX][genero]" required class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="masculino">Masculino</option>
        <option value="femenino">Femenino</option>
        <option value="otro">Otro</option>
      </select>
    </td>
    <td>
      <input type="number" name="members[INDEX][edad]"
        min="0" max="120" required class="form-input-sm">
    </td>
    <td>
      <select name="members[INDEX][parentesco]" required class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="paciente">Paciente</option>
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
      <select name="members[INDEX][escolaridad]" class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="sin_estudios">Sin estudios</option>
        <option value="primaria_incompleta">Primaria incompleta</option>
        <option value="primaria_completa">Primaria completa</option>
        <option value="secundaria_incompleta">Secundaria incompleta</option>
        <option value="secundaria_completa">Secundaria completa</option>
        <option value="preparatoria_incompleta">Preparatoria incompleta</option>
        <option value="preparatoria_completa">Preparatoria completa</option>
        <option value="universidad_incompleta">Universidad incompleta</option>
        <option value="universidad_completa">Universidad completa</option>
        <option value="posgrado">Posgrado</option>
      </select>
    </td>
    <td>
      <select name="members[INDEX][estado_civil]" class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="soltero">Soltero/a</option>
        <option value="casado">Casado/a</option>
        <option value="union_libre">Unión libre</option>
        <option value="divorciado">Divorciado/a</option>
        <option value="viudo">Viudo/a</option>
        <option value="separado">Separado/a</option>
      </select>
    </td>
    <td>
      <input type="text" name="members[INDEX][ocupacion]"
        placeholder="Ocupación" class="form-input-sm">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-xs remove-row"
        title="Eliminar integrante">
        ✕
      </button>
    </td>
  </tr>
</template>