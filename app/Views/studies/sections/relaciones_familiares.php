<?php
// app/Views/studies/sections/relaciones_familiares.php
?>
<div class="section-container">
  <h2 class="section-title">Relaciones Familiares</h2>

  <fieldset class="form-fieldset">
    <legend>Datos Culturales</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="pertenece_grupo_etnico">¿Pertenece a algún grupo étnico? *</label>
        <div class="radio-group">
          <label class="radio-label">
            <input type="radio" name="pertenece_grupo_etnico" value="1"
              data-criteria="relacion_familiar.pertenece_grupo_etnico">
            Sí
          </label>
          <label class="radio-label">
            <input type="radio" name="pertenece_grupo_etnico" value="0"
              data-criteria="relacion_familiar.pertenece_grupo_etnico">
            No
          </label>
        </div>
        <div class="criteria-result" id="criteria-pertenece_grupo_etnico"></div>
      </div>
      <div class="form-group">
        <label for="religion">Religión</label>
        <select id="religion" name="religion">
          <option value="">Seleccione...</option>
          <option value="catolica">Católica</option>
          <option value="protestante">Protestante</option>
          <option value="otra_cristiana">Otra cristiana</option>
          <option value="judaica">Judaica</option>
          <option value="islamica">Islámica</option>
          <option value="budista">Budista</option>
          <option value="otra">Otra</option>
          <option value="ninguna">Ninguna</option>
        </select>
      </div>
    </div>
  </fieldset>

  <fieldset class="form-fieldset">
    <legend>Red Social Familiar</legend>

    <!-- Integrantes de la Red (Campos Múltiples) -->
    <div class="form-group">
      <label class="fieldset-label">Integrantes de la red social familiar *</label>
      <div class="checkbox-group" id="integrantes-red">
        <label class="checkbox-label">
          <input type="checkbox" name="integrantes[]" value="familiares_lejos">
          Familiares que viven lejos
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="integrantes[]" value="familiares_cerca">
          Familiares que viven cerca
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="integrantes[]" value="vecinos">
          Vecinos
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="integrantes[]" value="amigos">
          Amigos
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="integrantes[]" value="otros">
          Otros
        </label>
      </div>
    </div>

    <!-- Actividades de la Red (Campos Múltiples) -->
    <div class="form-group">
      <label class="fieldset-label">Actividades de la red social *</label>
      <div class="checkbox-group" id="actividades-red">
        <label class="checkbox-label">
          <input type="checkbox" name="actividades[]" value="material">
          Apoyo material (dinero, alimentos, etc.)
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="actividades[]" value="instrumental">
          Apoyo instrumental (transporte, cuidado, etc.)
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="actividades[]" value="emocional">
          Apoyo emocional (escucha, consejos, etc.)
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="actividades[]" value="informativa">
          Apoyo informativo (información, orientación, etc.)
        </label>
      </div>
    </div>

    <!-- Tipos de Apoyo (Campos Múltiples) -->
    <div class="form-group">
      <label class="fieldset-label">¿De qué manera se cuenta con la red? *</label>
      <div class="checkbox-group" id="tipos-apoyo">
        <label class="checkbox-label">
          <input type="checkbox" name="tipos_apoyo[]" value="emergencia">
          En situaciones de emergencia
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="tipos_apoyo[]" value="esporadica">
          De manera esporádica
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="tipos_apoyo[]" value="habitual">
          De manera habitual
        </label>
        <label class="checkbox-label">
          <input type="checkbox" name="tipos_apoyo[]" value="ocasional">
          De manera ocasional
        </label>
      </div>
    </div>
  </fieldset>

  <div class="section-validation">
    <div class="validation-summary" id="relaciones-familiares-validation">
      <ul id="relaciones-familiares-errors"></ul>
    </div>
  </div>
</div>