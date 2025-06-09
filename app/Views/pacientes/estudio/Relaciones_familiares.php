<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../public/css/views/estudios.css">
    <title>Formulario de Relaciones Familiares</title>
</head>
<body>
  <h2>Relaciones Familiares</h2>

  <div class="form-row">
    <div class="form-group">
      <label>Pertenece a un grupo étnico</label>
      <select name="pertenece_a_grupo_etnico" required>
        <option value="">Seleccione</option>
        <option value="1">Sí</option>
        <option value="0">No</option>
      </select>
    </div>

    <div class="form-group">
      <label>Habla alguna lengua indígena</label>
      <select name="habla_lengua_indigena" required>
        <option value="">Seleccione</option>
        <option value="1">Sí</option>
        <option value="0">No</option>
      </select>
    </div>

    <div class="form-group">
      <label>Lengua indígena</label>
        <select name="lengua_indigena" required>
        <option value="">Seleccione</option>
        <option>Náhuatl</option>
        <option>Huichol</option>
        <option>Mixteco</option>
        <option>Cora</option>
        <option>Zapoteco</option>
        <option>Totonaco</option>
        <option>Huasteco</option>
        <option>Otro</option>
      </select>
    </div>

    <div class="form-group">
      <label>Otra Lengua</label>
      <input type="text" name="otra_lengua" required>
    </div>

    <div class="form-group">
      <label>Religión</label>
      <select name="religion" required>
        <option value="">Seleccione</option>
        <option>Catolicismo</option>
        <option>Cristianismo</option>
        <option>Protestantismo</option>
        <option>Islam</option>
        <option>Judaísmo</option>
        <option>Budismo</option>
        <option>Hinduismo</option>
        <option>Taoísmo</option>
        <option>Espiritualismo</option>
        <option>Testigos de Jehová</option>
        <option>Mormonismo</option>
        <option>Ateísmo</option>
        <option>Agnosticismo</option>
        <option>Otra</option>
      </select>
    </div>

    <div class="form-group">
      <label>Red Social Familiar</label>
      <select name="red_social_familiar" required>
        <option value="">Seleccione</option>
        <option>Sí</option>
        <option>No</option>
      </select>
    </div>

    <div class="form-group">
      <label>Integrantes de la Red</label>
      <select name="integrantes_red" required>
        <option value="">Seleccione</option>
        <option>Otros familiares que viven lejos</option>
        <option>Vecinos</option>
        <option>Conocidos</option>
        <option>Otros familiares que viven cerca</option>
        <option>Amigos</option>
        <option>Otros</option>
      </select>
    </div>

    <div class="form-group">
      <label>Se cuenta de manera</label>
      <select name="se_cuenta_de_manera" required>
        <option value="">Seleccione</option>
        <option>Sólo en casos de emergencia</option>
        <option>Esporádica</option>
        <option>Habitual</option>
        <option>Todas las anteriores</option>
      </select>
    </div>

    <div class="form-group">
      <label>Actividades de la Red</label>
      <select name="actividades_red" required>
        <option value="">Seleccione</option>
        <option>Material</option>
        <option>Instrumentales</option>
        <option>Emocionales</option>
        <option>Cognitivo</option>
      </select>
    </div>

    <div class="form-group">
      <label>Actividad que realiza con mayor frecuencia en familia</label>
      <select name="actividad_frecuente_familia" required>
        <option value="">Seleccione</option>
        <option>Material</option>
        <option>Instrumentales</option>
        <option>Emocionales</option>
        <option>Cognitivo</option>
      </select>
    </div>

    <div class="form-group">
      <label>Folio</label>
      <input type="text" name="folio" required>
    </div>
  </div>

  <div class="form-row">
    <button type="submit">Guardar</button>
    <button type="reset">Cancelar</button>
  </div>

</body>
</html>