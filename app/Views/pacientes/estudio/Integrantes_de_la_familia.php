<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
  <title>Integrantes de la Familia</title>
</head>

<body>
  <h2>Integrantes de la Familia</h2>

  <div class="form-row">
    <div class="form-group">
      <label>Número de Integrantes de la Familia</label>
      <select name="numero_integrantes" id="numIntegrantes" disabled>
        <option value="">Seleccione</option>
      </select>
    </div>
    <div class="form-group">
      <label>Número de Hijos</label>
      <select name="numero_hijos" required>
        <option value="">Seleccione</option>
        <option>1</option>
        <option>2</option>
        <option>3</option>
        <option>4</option>
        <option>5</option>
        <option>6</option>
        <option>7</option>
        <option>8</option>
      </select>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Nombre Completo</th>
        <th>Género</th>
        <th>Edad</th>
        <th>Parentesco</th>
        <th>Escolaridad</th>
        <th>Estado Civil</th>
        <th>Ocupación</th>
        <th>Procedencia</th>
        <th>Entrevistado</th>
      </tr>
    </thead>
    <tbody id="tablaIntegrantes">
      <!-- Filas generadas dinámicamente -->
    </tbody>
  </table>

  <div class="form-row">
    <button type="button" onclick="agregarFila()">Agregar Integrante</button>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label>Tipo de familia</label>
      <select name="tipo_familia" required>
        <option value="">Seleccione</option>
        <option>Familia nuclear</option>
        <option>Familia extensa</option>
        <option>Familia ampliada</option>
        <option>Familia reconstruida</option>
        <option>Familia con parientes próximos</option>
        <option>Personas sin familia</option>
        <option>Equivalentes familiares</option>
      </select>
    </div>

    <div class="form-group">
      <label>Ciclo vital de la familia</label>
      <select name="ciclo_vital_familia" required>
        <option value="">Seleccione</option>
        <option>La formación de pareja</option>
        <option>La familia con hijos pequeños</option>
        <option>La familia con hijos en edad escolar o adolescentes</option>
        <option>La familia con hijos adultos</option>
      </select>
    </div>

    <div class="form-group">
      <label>Folio</label>
      <input type="text" name="folio" required />
    </div>

    <div class="form-row">
      <button type="submit">Guardar</button>
      <button type="reset">Cancelar</button>
    </div>

    <script>
      let contador = 0;

      function actualizarNumeroIntegrantes() {
        const select = document.getElementById("numIntegrantes");
        select.innerHTML = "<option value=''>Seleccione</option>";
        for (let i = 1; i <= contador; i++) {
          select.innerHTML += `<option selected>${i}</option>`;
        }
      }

      function agregarFila() {
        contador++;
        const tabla = document.getElementById("tablaIntegrantes");

        const fila = document.createElement("tr");
        fila.innerHTML = `
        <td><input type="text" name="nombre${contador}"></td>
        <td>
          <select name="genero${contador}">
            <option value="">Seleccione</option>
            <option>Hombre</option>
            <option>Mujer</option>
          </select>
        </td>
        <td><input type="number" name="edad${contador}" min="0"></td>
        <td>
          <select name="parentesco${contador}">
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
        <td>
          <select name="escolaridad${contador}">
            <option value="">Seleccione</option>
            <option>Sin instrucción</option>
            <option>Primaria completa</option>
            <option>Primaria incompleta</option>
            <option>Secundaria completa</option>
            <option>Secundaria incompleta</option>
            <option>Carrera técnica completa</option>
            <option>Carrera técnica incompleta</option>
            <option>Preparatoria completa</option>
            <option>Preparatoria incompleta</option>
            <option>Licenciatura completa</option>
            <option>Licenciatura incompleta</option>
            <option>Posgrado</option>
          </select>
        </td>
        <td>
          <select name="estado_civil${contador}">
            <option value="">Seleccione</option>
            <option>Soltero(a)</option>
            <option>Casado(a)</option>
            <option>Unión libre</option>
            <option>Separado(a)</option>
            <option>Divorciado(a)</option>
            <option>Viudo(a)</option>
          </select>
        </td>
        <td>
          <select name="ocupacion${contador}">
            <option value="">Seleccione</option>
            <option>Estudiante</option>
            <option>Hogar</option>
            <option>Empleado</option>
            <option>Desempleado</option>
            <option>Jubilado</option>
            <option>Otro</option>
          </select>
        </td>
        <td>
          <select name="procedencia${contador}">
            <option value="">Seleccione</option>
            <option>Aguascalientes</option><option>Baja California</option><option>Baja California Sur</option>
            <option>Campeche</option><option>Chiapas</option><option>Chihuahua</option><option>Ciudad de México</option>
            <option>Coahuila</option><option>Colima</option><option>Durango</option><option>Estado de México</option>
            <option>Guanajuato</option><option>Guerrero</option><option>Hidalgo</option><option>Jalisco</option>
            <option>Michoacán</option><option>Morelos</option><option>Nayarit</option><option>Nuevo León</option>
            <option>Oaxaca</option><option>Puebla</option><option>Querétaro</option><option>Quintana Roo</option>
            <option>San Luis Potosí</option><option>Sinaloa</option><option>Sonora</option><option>Tabasco</option>
            <option>Tamaulipas</option><option>Tlaxcala</option><option>Veracruz</option><option>Yucatán</option>
            <option>Zacatecas</option><option>Extranjero</option>
          </select>
        </td>
        <td>
          <select name="entrevistado${contador}">
            <option value="">Seleccione</option>
            <option>Sí</option>
            <option>No</option>
          </select>
        </td>
      `;

        tabla.appendChild(fila);
        actualizarNumeroIntegrantes();
      }

      // Agrega la primera fila automáticamente
      agregarFila();
    </script>
</body>

</html>