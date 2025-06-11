<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
  <title>Formulario de Datos Generales</title>
</head>
<form id="form-datos-generales" action="<?= APP_URL ?>api/pacientes/crear.php" method="POST">
  <label>Folio</label>
  <input type="text" name="folio" required>
  <!-- Otros campos... -->
  <button type="submit">Guardar</button>
</form>

<body>
  <h2>Datos Generales</h2>
  <form>
    <div class="form-group">
      <label for="folio">Folio</label>
      <input type="number" id="folio" name="folio" required>
    </div>

    <fieldset>
      <legend>Datos del Paciente</legend>
      <div class="form-row">
        <div class="form-group">
          <label>Apellido Paterno</label>
          <input type="text" name="apellido_paterno" required>

          <label>Apellido Materno</label>
          <input type="text" name="apellido_materno" required>

          <label>Nombres</label>
          <input type="text" name="nombres" required>

          <label>Fecha de Nacimiento</label>
          <input type="date" name="fecha_nacimiento" required>

          <label>Edad Corregida</label>
          <input type="text" name="edad_corregida" disabled required>

          <label>Lugar de Nacimiento</label>
          <select name="lugar_nacimiento" required>
            <option value="">Seleccione</option>
            <option>Aguascalientes</option><option>Baja California</option><option>Baja California Sur</option>
            <option>Campeche</option><option>Chiapas</option><option>Chihuahua</option>
            <option>Ciudad de México</option><option>Coahuila</option><option>Colima</option>
            <option>Durango</option><option>Estado de México</option><option>Guanajuato</option>
            <option>Guerrero</option><option>Hidalgo</option><option>Jalisco</option>
            <option>Michoacán</option><option>Morelos</option><option>Nayarit</option>
            <option>Nuevo León</option><option>Oaxaca</option><option>Puebla</option>
            <option>Querétaro</option><option>Quintana Roo</option><option>San Luis Potosí</option>
            <option>Sinaloa</option><option>Sonora</option><option>Tabasco</option>
            <option>Tamaulipas</option><option>Tlaxcala</option><option>Veracruz</option>
            <option>Yucatán</option><option>Zacatecas</option><option>Extranjero</option>
          </select>

          <label>Protocolo</label>
          <select name="protocolo" required>
            <option value="">Seleccione</option>
            <option>Neurohabilitación</option>
            <option>Cirugía Fetal</option>
            <option>Control de Niño Sano</option>
            <option>Comparativo</option>
          </select>

          <label>Fecha de Ingreso al INB</label>
          <input type="date" name="fecha_ingreso_inb" required>

          <label>Instituto de Procedencia</label>
          <select name="instituto_procedencia" required>
            <option value="">Seleccione</option>
            <option>IMSS</option><option>ISSSTE</option><option>PEMEX</option>
            <option>Seguro Popular</option><option>Marina</option><option>Hospital del Niño y la Mujer</option>
            <option>Hospital Infantil de México</option><option>Instituto Nacional de Pediatría</option>
            <option>Hospital General de Querétaro</option><option>Privada</option><option>Otro</option>
          </select>
        </div>

        <div class="form-group">
          <fieldset>
            <legend>Dirección</legend>

            <label>Calle</label>
            <input type="text" name="calle" required>

            <label>Número</label>
            <input type="number" name="numero" required>

            <label>Colonia</label>
            <input type="text" name="colonia" required>

            <label>Municipio</label>
            <input type="text" name="municipio" required>

            <label>C.P.</label>
            <input type="number" name="cp" required>

            <label>Entidad Federativa</label>
            <select name="entidad_federativa" required>
              <option value="">Seleccione</option>
              <option>Aguascalientes</option><option>Baja California</option><option>Baja California Sur</option>
              <option>Campeche</option><option>Chiapas</option><option>Chihuahua</option>
              <option>Ciudad de México</option><option>Coahuila</option><option>Colima</option>
              <option>Durango</option><option>Estado de México</option><option>Guanajuato</option>
              <option>Guerrero</option><option>Hidalgo</option><option>Jalisco</option>
              <option>Michoacán</option><option>Morelos</option><option>Nayarit</option>
              <option>Nuevo León</option><option>Oaxaca</option><option>Puebla</option>
              <option>Querétaro</option><option>Quintana Roo</option><option>San Luis Potosí</option>
              <option>Sinaloa</option><option>Sonora</option><option>Tabasco</option>
              <option>Tamaulipas</option><option>Tlaxcala</option><option>Veracruz</option>
              <option>Yucatán</option><option>Zacatecas</option><option>Extranjero</option>
            </select>

            <label>Tiempo de Traslado: Casa-Unidad</label>
            <select name="tiempo_traslado" required>
              <option value="">Seleccione</option>
              <option>Menos de quince minutos</option>
              <option>Más de quince minutos</option>
              <option>Media hora</option>
              <option>Una hora</option>
              <option>Hora y media</option>
              <option>Dos horas</option>
              <option>Dos horas y media</option>
              <option>Más de dos horas y media</option>
            </select>

            <label>Gasto de Traslado: Casa-Unidad (pesos)</label>
            <input type="number" name="gasto_traslado" required>
          </fieldset>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Datos de Contacto</legend>
      <div class="form-row">
        <div class="form-group">
          <label>Nombre de la persona a quien dejar recados</label>
          <input type="text" name="persona_recados" required>

          <label>Número de Teléfono fijo (incluir lada)</label>
          <input type="tel" name="telefono_fijo" required>

          <label>Número de Celular (Recados)</label>
          <input type="tel" name="celular_recados" required>

          <label>Correo Electrónico</label>
          <input type="email" name="correo" required>
        </div>
      </div>
    </fieldset>

    <div class="buttons">
      <button type="submit">Guardar</button>
      <button type="reset">Cancelar</button>
    </div>
  </form>
</body>
</html>

<script>
  document.getElementById('form-datos-generales').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
      const response = await fetch('<?= APP_URL ?>api/pacientes/store', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.status === 'success') {
        // Guardar ID o folio para los siguientes formularios si es necesario
        const folio = result.folio || result.data?.id;

        alert('Datos generales guardados correctamente.');

        // Cargar el siguiente formulario (por ejemplo: Alimentacion.php)
        fetch(`<?= APP_URL ?>pacientes/estudio/Alimentacion.php?folio=${folio}`)
          .then(res => res.text())
          .then(html => {
            document.getElementById('formulario-container').innerHTML = html;
            window.scrollTo({ top: 0, behavior: 'smooth' });
          });

      } else {
        alert('Ocurrió un error al guardar: ' + (result.message || 'Verifique los datos.'));
      }
    } catch (error) {
      console.error('Error al enviar el formulario:', error);
      alert('Error de conexión o del servidor.');
    }
  });
</script>
