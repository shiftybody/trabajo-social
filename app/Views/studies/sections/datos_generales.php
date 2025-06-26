<?php
// app/Views/studies/sections/datos_generales.php
?>
<div class="section-container">
    <h2 class="section-title">Datos Generales del Estudio</h2>
    
    <fieldset class="form-fieldset">
        <legend>Información Básica</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="fecha_estudio">Fecha del Estudio *</label>
                <input type="date" id="fecha_estudio" name="fecha_estudio" 
                       value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="form-group">
                <label for="folio">Folio</label>
                <input type="text" id="folio" name="folio" placeholder="Se generará automáticamente">
            </div>
        </div>
    </fieldset>

    <fieldset class="form-fieldset">
        <legend>Datos de Contacto Principal</legend>
        <div class="form-row">
            <div class="form-group">
                <label for="contacto_integrante">Integrante de Familia *</label>
                <select id="contacto_integrante" name="contacto_integrante_id" required>
                    <option value="">Seleccione un integrante...</option>
                    <!-- Se carga dinámicamente desde la sección Familia -->
                </select>
                <small class="form-help">Primero debe agregar integrantes en la sección Familia</small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="telefono_fijo">Teléfono Fijo</label>
                <input type="tel" id="telefono_fijo" name="telefono_fijo" 
                       placeholder="Ej: 442-123-4567">
            </div>
            <div class="form-group">
                <label for="celular">Celular *</label>
                <input type="tel" id="celular" name="celular" required
                       placeholder="Ej: 442-123-4567">
            </div>
            <div class="form-group">
                <label for="correo_contacto">Correo Electrónico</label>
                <input type="email" id="correo_contacto" name="correo" 
                       placeholder="ejemplo@correo.com">
            </div>
        </div>
    </fieldset>

    <div class="section-validation">
        <div class="validation-summary" id="datos-generales-validation">
            <ul id="datos-generales-errors"></ul>
        </div>
    </div>
</div>