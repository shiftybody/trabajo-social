<?php
// app/Views/studies/sections/resumen.php
?>
<div class="section-container">
  <h2 class="section-title">Resumen del Estudio Socioeconómico</h2>

  <!-- Resumen de Datos Capturados -->
  <div class="summary-section">
    <h3>Datos Capturados</h3>
    <div class="summary-grid">
      <div class="summary-card">
        <h4>Información Familiar</h4>
        <div class="summary-content" id="family-summary">
          <!-- Se llena dinámicamente -->
        </div>
      </div>

      <div class="summary-card">
        <h4>Información de Salud</h4>
        <div class="summary-content" id="health-summary">
          <!-- Se llena dinámicamente -->
        </div>
      </div>

      <div class="summary-card">
        <h4>Información Económica</h4>
        <div class="summary-content" id="economic-summary">
          <!-- Se llena dinámicamente -->
        </div>
      </div>

      <div class="summary-card">
        <h4>Información de Vivienda</h4>
        <div class="summary-content" id="housing-summary">
          <!-- Se llena dinámicamente -->
        </div>
      </div>
    </div>
  </div>

  <!-- Cálculo de Puntaje Automático -->
  <div class="score-section">
    <h3>Evaluación Socioeconómica</h3>

    <div class="score-calculation">
      <div class="score-header">
        <button type="button" class="btn btn-primary" id="calculate-total-score">
          Calcular Puntaje Total
        </button>
        <div class="calculation-status" id="calculation-status" style="display: none;">
          Calculando...
        </div>
      </div>

      <!-- Desglose de Puntajes -->
      <div class="score-breakdown" id="score-breakdown" style="display: none;">
        <h4>Desglose de Puntuación</h4>
        <div class="breakdown-table">
          <table>
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Campo</th>
                <th>Valor</th>
                <th>Criterio Aplicado</th>
                <th>Puntaje</th>
              </tr>
            </thead>
            <tbody id="breakdown-tbody">
              <!-- Se llena dinámicamente -->
            </tbody>
            <tfoot>
              <tr class="total-row">
                <td colspan="4"><strong>PUNTAJE TOTAL</strong></td>
                <td><strong id="total-score-display">0</strong> puntos</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Nivel Socioeconómico -->
  <div class="level-section">
    <h3>Nivel Socioeconómico</h3>

    <div class="level-automatic">
      <h4>Nivel Calculado Automáticamente</h4>
      <div class="level-result" id="automatic-level-result">
        <div class="level-info">
          <span class="level-name" id="automatic-level-name">-</span>
          <span class="level-description" id="automatic-level-description">-</span>
        </div>
        <div class="level-score">
          Basado en <span id="automatic-score">0</span> puntos
        </div>
      </div>
    </div>

    <!-- Edición Manual del Nivel -->
    <div class="level-manual">
      <h4>Edición Manual (Opcional)</h4>
      <div class="manual-override">
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" id="enable-manual-level">
            Editar nivel manualmente
          </label>
        </div>

        <div class="manual-fields" id="manual-level-fields" style="display: none;">
          <div class="form-row">
            <div class="form-group">
              <label for="manual-level-select">Nivel Socioeconómico *</label>
              <select id="manual-level-select" name="nivel_id_manual">
                <option value="">Seleccionar nivel...</option>
                <!-- Se cargan dinámicamente desde la BD -->
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="manual-justification">Justificación del cambio *</label>
            <textarea id="manual-justification" name="justificacion_nivel_manual"
              rows="4" placeholder="Explique por qué se modifica el nivel automático..."></textarea>
            <small class="form-help">Requerido cuando se edita el nivel manualmente</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Nivel Final -->
    <div class="level-final" id="final-level-section" style="display: none;">
      <h4>Nivel Final Asignado</h4>
      <div class="final-level-result">
        <div class="level-badge" id="final-level-badge">
          <span class="level-name" id="final-level-name">-</span>
          <span class="level-type" id="final-level-type">Automático</span>
        </div>
        <div class="level-justification" id="final-justification" style="display: none;">
          <strong>Justificación:</strong>
          <p id="justification-text"></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Validación Final -->
  <div class="final-validation">
    <h3>Validación Final</h3>
    <div class="validation-checklist" id="final-checklist">
      <div class="checklist-item" data-section="datos-generales">
        <span class="check-icon">⏳</span>
        <span class="check-label">Datos Generales</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="familia">
        <span class="check-icon">⏳</span>
        <span class="check-label">Información Familiar</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="relaciones-familiares">
        <span class="check-icon">⏳</span>
        <span class="check-label">Relaciones Familiares</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="salud">
        <span class="check-icon">⏳</span>
        <span class="check-label">Información de Salud</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="alimentacion">
        <span class="check-icon">⏳</span>
        <span class="check-label">Alimentación
          <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="vivienda">
        <span class="check-icon">⏳</span>
        <span class="check-label">Información de Vivienda</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="economia">
        <span class="check-icon">⏳</span>
        <span class="check-label">Información Económica</span>
        <span class="check-status">Pendiente</span>
      </div>
      <div class="checklist-item" data-section="nivel-socioeconomico">
        <span class="check-icon">⏳</span>
        <span class="check-label">Nivel Socioeconómico Asignado</span>
        <span class="check-status">Pendiente</span>
      </div>
    </div>
  </div>

  <!-- Botones de Acción Final -->
  <div class="final-actions">
    <div class="action-buttons">
      <button type="button" class="btn btn-secondary" id="save-final-draft">
        Guardar como Borrador
      </button>
      <button type="button" class="btn btn-success" id="finalize-study-btn" disabled>
        Finalizar y Activar Estudio
      </button>
    </div>

    <div class="final-warnings" id="final-warnings" style="display: none;">
      <div class="warning-message">
        <strong>⚠️ Atención:</strong>
        <ul id="warning-list"></ul>
      </div>
    </div>
  </div>

  <!-- Inputs ocultos para el formulario -->
  <input type="hidden" id="puntaje_automatico" name="puntaje_automatico" value="0">
  <input type="hidden" id="nivel_id_automatico" name="nivel_id_automatico" value="">
  <input type="hidden" id="desglose_puntuacion" name="desglose_puntuacion" value="">
</div>