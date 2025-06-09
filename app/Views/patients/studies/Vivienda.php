<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
  <title>Formulario de Vivienda</title>
</head>
<body>
  <h2>Formulario de Vivienda</h2>
  <form>
    <!-- Estructura -->
    <fieldset>
      <legend>Estructura</legend>

      <label>Zona:
        <select name="zona_id" required>
          <option selected disabled>Seleccione una opción</option>
          <option value="urbana">Urbana</option>
          <option value="rural">Rural</option>
          <option value="suburbana">Suburbana</option>
        </select>
      </label>

      <label>Tiempo de residencia en el domicilio:
        <input type="number" name="tiempo_residencia" min="0">
        <select name="unidad_tiempo">
          <option selected disabled>Seleccione una opción</option>
          <option value="meses">Meses</option>
          <option value="años">Años</option>
        </select>
      </label>

      <label>Tipo de vivienda:
        <select name="tipo_vivienda_id" required>
          <option selected disabled>Seleccione una opción</option>
          <option value="casa">Casa</option>
          <option value="departamento">Departamento</option>
          <option value="cuarto_redondo">Cuarto redondo</option>
        </select>
      </label>

      <label>Tipo de tenencia:
        <select name="tenencia_id" required>
          <option selected disabled>Seleccione una opción</option>
          <option value="propia">Propia</option>
          <option value="rentada">Rentada</option>
          <option value="prestada">Prestada</option>
          <option value="hipotecada">Hipotecada</option>
        </select>
      </label>

      <label>Habitaciones de la vivienda:
        <select name="habitaciones" id="habitaciones">
          <option selected disabled>Seleccione una opción</option>
        </select>
      </label>

      <label>Personas por dormitorio:
        <select name="personas_dormitorio" id="personas_dormitorio">
          <option selected disabled>Seleccione una opción</option>
        </select>
      </label>

      <label>Personas por cama:
        <select name="personas_cama" id="personas_cama">
          <option selected disabled>Seleccione una opción</option>
        </select>
      </label>

      <label>Material de las paredes:
        <select name="material_paredes_id" required>
          <option selected disabled>Seleccione una opción</option>
          <option value="carton">Cartón</option>
          <option value="madera">Madera</option>
          <option value="tabique">Tabique</option>
        </select>
      </label>

      <label>Material del techo:
        <select name="material_techo_id">
          <option selected disabled>Seleccione una opción</option>
          <option value="asbesto">Láminas de asbesto</option>
          <option value="carton">Láminas de cartón</option>
          <option value="metal">Láminas de metal</option>
          <option value="concreto">Concreto</option>
        </select>
      </label>

      <label>Material del piso:
        <select name="material_piso_id">
          <option selected disabled>Seleccione una opción</option>
          <option value="tierra_apisonada">Tierra apisonada</option>
          <option value="adoquin">Adoquín</option>
          <option value="cemento">Cemento</option>
          <option value="loseta">Loseta</option>
          <option value="mosaico">Mosaico</option>
        </select>
      </label>
    </fieldset>

    <!-- Servicios Extradomiciliarios -->
    <fieldset>
      <legend>Servicios Extradomiciliarios</legend>

      <label>Agua potable:
        <select name="servicio_agua_potable">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Drenaje:
        <select name="servicio_drenaje">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Alcantarillado:
        <select name="servicio_alcantarillado">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Alumbrado público:
        <select name="servicio_alumbrado_publico">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Pavimento:
        <select name="servicio_pavimento">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Recolección de basura:
        <select name="servicio_recoleccion_basura">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Vigilancia:
        <select name="servicio_vigilancia">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>
    </fieldset>

    <!-- Servicios Intradomiciliarios -->
    <fieldset>
      <legend>Servicios Intradomiciliarios</legend>

      <label>Luz:
        <select name="servicio_luz_id">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Agua potable:
        <select name="servicio_agua_id">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Teléfono fijo:
        <select name="servicio_telefono_fijo">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>Internet:
        <select name="servicio_internet">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>

      <label>TV de paga:
        <select name="servicio_tv_paga">
          <option selected disabled>Seleccione una opción</option>
          <option value="si">Sí</option>
          <option value="no">No</option>
        </select>
      </label>
    </fieldset>

    <label>Folio:
      <input type="text" name="folio">
    </label><br><br>

    <button type="submit">Guardar</button>
    <button type="reset">Cancelar</button>
  </form>

  <script>
    const generarOpciones = (selectId) => {
      const select = document.getElementById(selectId);
      for (let i = 1; i <= 20; i++) {
        const option = document.createElement("option");
        option.value = i;
        option.textContent = i;
        select.appendChild(option);
      }
    };

    generarOpciones("habitaciones");
    generarOpciones("personas_dormitorio");
    generarOpciones("personas_cama");
  </script>
</body>
</html>
