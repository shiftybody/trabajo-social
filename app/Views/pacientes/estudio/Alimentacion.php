<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulario de Alimentación</title>
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
</head>
<body>
  <h2>Alimentación</h2>

  <form>
    <div class="form-row">
      <div class="form-group">
        <label for="comidas_familia">Número de comidas que hace al día la familia</label>
        <select name="comidas_familia" id="comidas_familia" required>
          <option value="">Seleccione</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>
      </div>

      <div class="form-group">
        <label for="comidas_paciente">Número de comidas que hace al día el paciente</label>
        <select name="comidas_paciente" id="comidas_paciente" required>
          <option value="">Seleccione</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>
      </div>
    </div>

    <h3 style="margin-left: 10px; color: var(--gray-800);">Frecuencia de Consumo de Alimentos (1 a 7)</h3>

    <div class="form-row">
      <div class="form-group">
        <label for="res">Carne de res</label>
        <select name="res" id="res" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="pollo">Carne de pollo</label>
        <select name="pollo" id="pollo" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="cerdo">Carne de cerdo</label>
        <select name="cerdo" id="cerdo" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="pescado">Carne de pescado</label>
        <select name="pescado" id="pescado" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="lacteos">Lácteos y derivados</label>
        <select name="lacteos" id="lacteos" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="cereales">Cereales</label>
        <select name="cereales" id="cereales" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="huevo">Huevo</label>
        <select name="huevo" id="huevo" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="frutas">Frutas</label>
        <select name="frutas" id="frutas" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="verduras">Verduras</label>
        <select name="verduras" id="verduras" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
      <div class="form-group">
        <label for="leguminosas">Leguminosas</label>
        <select name="leguminosas" id="leguminosas" required>
          <option value="">Seleccione</option>
          <option>1</option><option>2</option><option>3</option>
          <option>4</option><option>5</option><option>6</option><option>7</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="folio">Folio</label>
        <input type="text" id="folio" name="folio" required>
      </div>
    </div>

    <div class="form-row">
      <button type="submit">Guardar</button>
      <button type="reset">Cancelar</button>
    </div>
  </form>

</body>
</html>
