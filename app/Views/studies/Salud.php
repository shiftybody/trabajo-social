<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
  <title>Formulario de Salud</title>
</head>
<body>

  <h2>Formulario de Salud</h2>
  <div class="container">
    <div class="form-row">
      <div class="form-group">
        <label>Familia con Seguridad Social</label>
        <select name="familia_con_seguridad_social" required>
          <option value="">Seleccione</option>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <label>Tipo de Seguridad Social</label>
        <select name="tipo_seguridad_social"  required>
          <option value="">Seleccione</option>
          <option>IMSS</option>
          <option>ISSSTE</option>
          <option>PEMEX</option>
          <option>Seguro Popular</option>
          <option>Marina</option>
          <option>Hospital del Niño y la Mujer</option>
          <option>Hospital Infantil de México</option>
          <option>Instituto Nacional de Pediatría</option>
          <option>Hospital General de Querétaro</option>
          <option>Privada</option>
          <option>Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Miembros de la familia con acceso</label>
        <select name="miembros_con_seguridad_social" required>
          <option value="">Seleccione</option>
          <option>Todos</option>
          <option>Algunos</option>
          <option>Ninguno</option>
        </select>
      </div>
      <div class="form-group">
        <label>Frecuencia de asistencia al médico</label>
        <select name="frecuencia_asistencia_medico" required>
          <option value="">Seleccione</option>
          <option>Semanal</option>
          <option>Mensual</option>
          <option>Semestral</option>
          <option>Anual</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Otros problemas del paciente en otra institución</label>
        <select name="otros_problemas_paciente" required>
          <option value="">Seleccione</option>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <label>¿Cuál?</label>
        <select name="cual_problema_paciente" required>
          <option value="">Seleccione</option>
          <option>IMSS</option>
          <option>ISSSTE</option>
          <option>PEMEX</option>
          <option>Seguro Popular</option>
          <option>Marina</option>
          <option>Hospital del Niño y la Mujer</option>
          <option>Hospital Infantil de México</option>
          <option>Instituto Nacional de Pediatría</option>
          <option>Hospital General de Querétaro</option>
          <option>Privada</option>
          <option>Otro</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>¿Algún pariente padece enfermedad o discapacidad?</label>
        <select name="algun_pariente_enfermo_o_discapacitado" required>
          <option value="">Seleccione</option>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <label>¿Cuántos?</label>
        <select name="num_parientes_enf_o_disc"  id="cuantosParientes">
          <option value="">Seleccione</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Parentesco</th>
          <th>Enfermedad/Discapacidad</th>
          <th>Tratamiento</th>
          <th>Institución donde lleva el tratamiento</th>
        </tr>
      </thead>
      <tbody id="tablaParientes">
        <!-- Filas dinámicas aquí -->
      </tbody>
    </table>

    <div class="form-row">
      <div class="form-group">
        <label>¿Cuentan con seguro de gastos médicos mayores?</label>
        <select name="seguro_gastos_medicos_mayores" required>
          <option value="">Seleccione</option>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <label>¿Cuál?</label>
        <input type="text" name="nombre_seguro_gastos_medicos_mayores" required>
      </div>
      <div class="form-group">
        <label>Folio</label>
        <input type="text" required>
      </div>
    </div>

    <div class="form-row">
      <button class="guardar" type="submit">Guardar</button>
      <button class="cancelar" type="reset">Cancelar</button>
    </div>
  </div>

  <script>
    const cuantosSelect = document.getElementById("cuantosParientes");
    const tablaBody = document.getElementById("tablaParientes");

    cuantosSelect.addEventListener("change", () => {
      const cantidad = parseInt(cuantosSelect.value);
      tablaBody.innerHTML = "";

      if (!isNaN(cantidad) && cantidad > 0) {
        for (let i = 1; i <= cantidad; i++) {
          const row = document.createElement("tr");

          row.innerHTML = `
            <td>
              <select name="parentesco${i}">
                <option value="">Seleccione</option>
                <option>Padre</option>
                <option>Madre</option>
                <option>Paciente</option>
                <option>Abuelo(a)</option>
                <option>Tío(a)</option>
                <option>Primo(a)</option>
                <option>Hermano(a)</option>
                <option>Suegro(a)</option>
              </select>
            </td>
            <td><input type="text" name="enfermedad_o_discapacidad${i}"></td>
            <td>
              <select name="tratamiento${i}">
                <option value="">Seleccione</option>
                <option>Sí</option>
                <option>No</option>
              </select>
            </td>
            <td>
              <select name="institucion_tratamiento${i}">
                <option value="">Seleccione</option>
                <option>IMSS</option>
                <option>ISSSTE</option>
                <option>PEMEX</option>
                <option>Seguro Popular</option>
                <option>Marina</option>
                <option>Hospital del Niño y la Mujer</option>
                <option>Hospital Infantil de México</option>
                <option>Instituto Nacional de Pediatría</option>
                <option>Hospital General de Querétaro</option>
                <option>Privada</option>
                <option>Otro</option>
              </select>
            </td>
          `;

          tablaBody.appendChild(row);
        }
      }
    });
  </script>

</body>
</html>
