<?php
// app/Views/studies/sections/economia.php
?>
<div class="section-container">
  <h2 class="section-title">Información Económica</h2>

  <fieldset class="form-fieldset">
    <legend>Información General</legend>
    <div class="form-row">
      <div class="form-group">
        <label for="principal_sosten_economico">Principal sostén económico *</label>
        <select id="principal_sosten_economico" name="principal_sosten_economico" required>
          <option value="">Seleccione...</option>
          <option value="padre">Padre</option>
          <option value="madre">Madre</option>
          <option value="ambos">Ambos padres</option>
          <option value="hijos">Hijos</option>
          <option value="abuelos">Abuelos</option>
          <option value="tios">Tíos</option>
          <option value="otros_familiares">Otros familiares</option>
          <option value="externos">Externos a la familia</option>
        </select>
      </div>
      <div class="form-group">
        <label for="numero_dependientes_economicos">Número de dependientes económicos *</label>
        <input type="number" id="numero_dependientes_economicos"
          name="numero_dependientes_economicos" min="0" max="20" required
          data-criteria="economia.numero_dependientes_economicos">
        <div class="criteria-result" id="criteria-numero_dependientes_economicos"></div>
      </div>
    </div>
  </fieldset>

  <!-- Tabla dinámica de aportadores económicos -->
  <fieldset class="form-fieldset">
    <legend>Aportadores Económicos</legend>

    <div class="dynamic-table-container">
      <div class="table-header">
        <h3>Personas que Aportan Económicamente</h3>
        <button type="button" class="btn btn-primary btn-sm" id="add-economic-provider">
          + Agregar Aportador
        </button>
      </div>

      <div class="table-responsive">
        <table class="dynamic-table" id="economic-providers-table">
          <thead>
            <tr>
              <th>Parentesco *</th>
              <th>Empresa/Lugar de Trabajo</th>
              <th>Puesto</th>
              <th>Ingreso Neto *</th>
              <th>Aportación Mensual *</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="economic-providers-body">
            <!-- Filas dinámicas se insertan aquí -->
          </tbody>
        </table>
      </div>

      <div class="totals-summary">
        <div class="total-row">
          <strong>Total Ingresos Netos: $<span id="total-ingresos">0.00</span></strong>
        </div>
        <div class="total-row">
          <strong>Total Aportaciones: $<span id="total-aportaciones">0.00</span></strong>
        </div>
      </div>
    </div>

    <input type="hidden" id="total_ingreso_neto" name="total_ingreso_neto" value="0">
    <input type="hidden" id="total_aportacion_mensual" name="total_aportacion_mensual"
      value="0" data-criteria="economia.total_aportacion_mensual">
    <div class="criteria-result" id="criteria-total_aportacion_mensual"></div>
  </fieldset>

  <!-- Egresos -->
  <fieldset class="form-fieldset">
    <legend>Egresos Mensuales</legend>
    <div class="form-grid">
      <div class="form-group">
        <label for="alimentacion_egreso">Alimentación *</label>
        <input type="number" id="alimentacion_egreso" name="alimentacion"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="luz_egreso">Luz *</label>
        <input type="number" id="luz_egreso" name="luz"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="agua_egreso">Agua *</label>
        <input type="number" id="agua_egreso" name="agua"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="gas_egreso">Gas *</label>
        <input type="number" id="gas_egreso" name="gas"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="renta_egreso">Renta/Hipoteca</label>
        <input type="number" id="renta_egreso" name="renta"
          min="0" step="0.01" class="egreso-input">
      </div>
      <div class="form-group">
        <label for="transporte_egreso">Transporte *</label>
        <input type="number" id="transporte_egreso" name="transporte"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="educacion_egreso">Educación</label>
        <input type="number" id="educacion_egreso" name="educacion"
          min="0" step="0.01" class="egreso-input">
      </div>
      <div class="form-group">
        <label for="salud_egreso">Salud *</label>
        <input type="number" id="salud_egreso" name="salud"
          min="0" step="0.01" required class="egreso-input">
      </div>
      <div class="form-group">
        <label for="vestimenta_egreso">Vestimenta</label>
        <input type="number" id="vestimenta_egreso" name="vestimenta"
          min="0" step="0.01" class="egreso-input">
      </div>
      <div class="form-group">
        <label for="otros_egresos">Otros gastos</label>
        <input type="number" id="otros_egresos" name="otros_egresos"
          min="0" step="0.01" class="egreso-input">
      </div>
    </div>

    <div class="totals-summary">
      <div class="total-row">
        <strong>Total Egresos: $<span id="total-egresos">0.00</span></strong>
      </div>
      <div class="balance-row" id="balance-row">
        <strong>Balance: $<span id="balance-amount">0.00</span></strong>
        <span class="balance-status" id="balance-status"></span>
      </div>
    </div>

    <input type="hidden" id="total_egreso_mensual" name="total_egreso_mensual" value="0">
  </fieldset>

  <!-- Subida de Comprobantes -->
  <fieldset class="form-fieldset">
    <legend>Documentos Comprobatorios</legend>
    <p class="form-help">Suba imágenes o archivos PDF de los comprobantes solicitados (opcional)</p>

    <div class="documents-grid">
      <div class="document-upload-group">
        <label for="comprobante_ingresos">Comprobante de Ingresos</label>
        <div class="file-upload-area" data-document-type="comprobante_ingresos">
          <input type="file" id="comprobante_ingresos" name="comprobante_ingresos"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">📄</div>
            <p>Haz clic para seleccionar archivo</p>
            <small>JPG, PNG o PDF (máx. 5MB)</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>

      <div class="document-upload-group">
        <label for="recibo_luz">Recibo de Luz</label>
        <div class="file-upload-area" data-document-type="recibo_luz">
          <input type="file" id="recibo_luz" name="recibo_luz"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">💡</div>
            <p>Seleccionar recibo de luz</p>
            <small>JPG, PNG o PDF</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>

      <div class="document-upload-group">
        <label for="recibo_agua">Recibo de Agua</label>
        <div class="file-upload-area" data-document-type="recibo_agua">
          <input type="file" id="recibo_agua" name="recibo_agua"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">💧</div>
            <p>Seleccionar recibo de agua</p>
            <small>JPG, PNG o PDF</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>

      <div class="document-upload-group">
        <label for="recibo_gas">Recibo de Gas</label>
        <div class="file-upload-area" data-document-type="recibo_gas">
          <input type="file" id="recibo_gas" name="recibo_gas"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">🔥</div>
            <p>Seleccionar recibo de gas</p>
            <small>JPG, PNG o PDF</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>

      <div class="document-upload-group">
        <label for="recibo_renta">Recibo de Renta/Hipoteca</label>
        <div class="file-upload-area" data-document-type="recibo_renta">
          <input type="file" id="recibo_renta" name="recibo_renta"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">🏠</div>
            <p>Seleccionar comprobante</p>
            <small>JPG, PNG o PDF</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>

      <div class="document-upload-group">
        <label for="recibo_predial">Recibo Predial</label>
        <div class="file-upload-area" data-document-type="recibo_predial">
          <input type="file" id="recibo_predial" name="recibo_predial"
            accept="image/*,.pdf" class="file-input">
          <div class="upload-placeholder">
            <div class="upload-icon">📋</div>
            <p>Seleccionar recibo predial</p>
            <small>JPG, PNG o PDF</small>
          </div>
          <div class="file-preview" style="display: none;">
            <img class="preview-image" style="display: none;">
            <div class="file-info">
              <span class="file-name"></span>
              <button type="button" class="btn btn-danger btn-xs remove-file">✕</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </fieldset>

  <div class="section-validation">
    <div class="validation-summary" id="economia-validation">
      <ul id="economia-errors"></ul>
    </div>
  </div>
</div>

<!-- Template para nueva fila de aportador económico -->
<template id="economic-provider-row-template">
  <tr class="dynamic-row" data-row-index="">
    <td>
      <select name="providers[INDEX][parentesco]" required class="form-input-sm">
        <option value="">Seleccione...</option>
        <option value="paciente">Paciente</option>
        <option value="padre">Padre</option>
        <option value="madre">Madre</option>
        <option value="hijo">Hijo/a</option>
        <option value="hermano">Hermano/a</option>
        <option value="abuelo">Abuelo/a</option>
        <option value="tio">Tío/a</option>
        <option value="conyugue">Cónyuge</option>
        <option value="otro">Otro</option>
      </select>
    </td>
    <td>
      <input type="text" name="providers[INDEX][empresa]"
        placeholder="Empresa o lugar de trabajo" class="form-input-sm">
    </td>
    <td>
      <input type="text" name="providers[INDEX][puesto]"
        placeholder="Puesto o actividad" class="form-input-sm">
    </td>
    <td>
      <input type="number" name="providers[INDEX][ingreso_neto]"
        min="0" step="0.01" required class="form-input-sm ingreso-input"
        placeholder="0.00">
    </td>
    <td>
      <input type="number" name="providers[INDEX][aportacion_mensual]"
        min="0" step="0.01" required class="form-input-sm aportacion-input"
        placeholder="0.00">
    </td>
    <td>
      <button type="button" class="btn btn-danger btn-xs remove-row"
        title="Eliminar aportador">
        ✕
      </button>
    </td>
  </tr>
</template>