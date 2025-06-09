<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../../public/css/views/estudios.css">
  <title>Formulario - Economía</title>
</head>
<body>
<h2>Economía</h2>
<form>
  <fieldset>
    <legend>Ingreso Mensual Familiar</legend>
    <div class="form-row">
      <div class="form-group">
        <label>Principal Sostén económico</label>
        <select name="sosten" required>
          <option value="">Seleccione…</option>
          <option>Padre</option>
          <option>Madre</option>
          <option>Ambos</option>
          <option>Hijos</option>
          <option>Tío(a)</option>
          <option>Primo(a)</option>
          <option>Suegro(a)</option>
          <option>Otros familiares</option>
        </select>
      </div>
      <div class="form-group">
        <label>Número de dependientes económicos</label>
        <select name="numerodependienteseconomicos_id" id="numerodependienteseconomicos_id" required>
          <option value="">Seleccione…</option>
          <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
          <option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
          <option>11</option><option>12</option><option>13</option><option>14</option><option>15</option>
          <option>16</option><option>17</option><option>18</option><option>19</option><option>20</option>
        </select>
      </div>
    </div>
    <table>
      <thead>
        <tr>
          <th>Aportador</th>
          <th>Parentesco</th>
          <th>Empresa</th>
          <th>Puesto</th>
          <th>Contrato</th>
          <th>Ingreso Neto</th>
          <th>Aportación mensual</th>
        </tr>
      </thead>
      <tbody id="aportadores-body"></tbody>
    </table>
    <button type="button" onclick="sumar('ingreso_neto','total_ingreso_neto','input_total_ingreso_neto')">Sumar Ingresos</button>
    <button type="button" onclick="sumar('aportacion_mensual','total_aporte','input_total_aporte')">Sumar Aportaciones</button>
    <p class="totals">Total Ingreso Neto: <span id="total_ingreso_neto">0.00</span></p>
    <input type="hidden" name="total_ingreso_neto" id="input_total_ingreso_neto" value="0.00">
    <p class="totals">Total Aportación Mensual: <span id="total_aporte">0.00</span></p>
    <input type="hidden" name="total_aporte" id="input_total_aporte" value="0.00">
    <div class="form-row">
      <div class="form-group">
        <label>¿Cuentan con un programa social?</label>
        <select name="programa_social" required>
          <option value="">Seleccione…</option>
          <option value="1">Sí</option>
          <option value="0">No</option>
        </select>
      </div>
      <div class="form-group">
        <label>Monto del Programa Social</label>
        <input type="number" name="monto_programa">
      </div>
    </div>
  </fieldset>

  <fieldset>
    <legend>Egreso Familiar Mensual</legend>
    <div class="form-row">
      <div class="form-group"><label>Alimentación<input type="number" name="alimentacion" class="egreso"></label></div>
      <div class="form-group"><label>Despensa<input type="number" name="despensa" class="egreso"></label></div>
      <div class="form-group"><label>Leche y pañales<input type="number" name="leche_panales" class="egreso"></label></div>
      <div class="form-group"><label>Transporte (Pasajes)<input type="number" name="transporte" class="egreso"></label></div>
      <div class="form-group"><label>Educación<input type="number" name="educacion" class="egreso"></label></div>
      <div class="form-group"><label>Predio/Renta<input type="number" name="predio_renta" class="egreso"></label></div>
      <div class="form-group"><label>Luz<input type="number" name="luz" class="egreso"></label></div>
      <div class="form-group"><label>Teléfono<input type="number" name="telefono" class="egreso"></label></div>
      <div class="form-group"><label>Gas<input type="number" name="gas" class="egreso"></label></div>
      <div class="form-group"><label>Internet<input type="number" name="internet" class="egreso"></label></div>
      <div class="form-group"><label>TV de paga<input type="number" name="tv_paga" class="egreso"></label></div>
      <div class="form-group"><label>Salud<input type="number" name="salud" class="egreso"></label></div>
      <div class="form-group"><label>Deudas<input type="number" name="deudas" class="egreso"></label></div>
      <div class="form-group"><label>Ropa y calzado<input type="number" name="ropa_calzado" class="egreso"></label></div>
      <div class="form-group"><label>Gasolina de coche<input type="number" name="gasolina_coche" class="egreso"></label></div>
    </div>
    <button type="button" onclick="sumar('egreso','total_egreso','input_total_egreso')">Sumar Egresos</button>
    <p class="totals">Total de Egresos: <span id="total_egreso">0.00</span></p>
    <input type="hidden" name="total_egreso_mensual" id="input_total_egreso" value="0.00">
  </fieldset>

  <fieldset>
    <legend>Documentos Probatorios</legend>
      <div class="form-row">
        <div class="form-group"><label>Comprobante de ingresos<select name="comprobante_ingresos" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Carta de ingresos<select name="carta_ingresos" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de luz<select name="recibo_luz" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de agua<select name="recibo_agua" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de gas<select name="recibo_gas" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de renta<select name="recibo_renta" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de colegiaturas<select name="recibo_colegiaturas" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
        <div class="form-group"><label>Recibo de predial<select name="recibo_predial" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
      </div>
  </fieldset>

  <fieldset>
    <legend>Comparativo</legend>
    <div class="form-row">
      <div class="form-group"><label>¿Existe déficit?<select name="deficit" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
      <div class="form-group"><label>¿Cómo cubre el déficit?<select name="como_cubre_deficit"><option value="">Seleccione…</option><option>Préstamo familiar</option><option>Préstamo bancario</option><option>Tarjetas de crédito</option><option>Venta de muebles</option><option>Venta de autos/inmuebles</option><option>Ahorros</option><option>Otro</option></select></label></div>
      <div class="form-group"><label>¿Cubre necesidades básicas?<select name="cubre_necesidades_basicas" required><option value="">Seleccione…</option><option value="1">Sí</option><option value="0">No</option></select></label></div>
    </div>
  </fieldset>

  <div class="form-row">
    <div class="form-group"><label>Folio<input type="text" name="folio" required></label></div>
  </div>
  <div class="form-row">
    <button type="submit">Guardar</button>
    <button type="reset">Cancelar</button>
  </div>
</form>

<script>
  const tblBody = document.getElementById('aportadores-body');
  const selDependientes = document.getElementById('numerodependienteseconomicos_id');

  selDependientes.addEventListener('change', ()=>{
    const n = parseInt(selDependientes.value) || 0;
    renderAportadores(n);
    document.getElementById('total_ingreso_neto').textContent='0.00';
    document.getElementById('total_aporte').textContent='0.00';
    document.getElementById('input_total_ingreso_neto').value='0.00';
    document.getElementById('input_total_aporte').value='0.00';
  });

  function renderAportadores(n){
    tblBody.innerHTML='';
    for(let i=1;i<=n;i++){
      const tr=document.createElement('tr');
      tr.innerHTML=`
<td>Aportador ${i}</td>
<td><select name="parentesco_${i}" required><option value="">Seleccione…</option><option>Padre</option><option>Madre</option><option>Paciente</option><option>Abuelo(a)</option><option>Tío(a)</option><option>Primo(a)</option><option>Hermano(a)</option><option>Suegro(a)</option><option>Otros familiares</option><option>No familiar</option></select></td>
<td><input type="text" name="empresa_${i}" required></td>
<td><input type="text" name="puesto_${i}" required></td>
<td><select name="tipo_contrato_${i}" required><option value="">Seleccione…</option><option>Indefinido</option><option>Definido</option><option>Tiempo parcial</option><option>Base</option><option>Sin contrato</option></select></td>
<td><input type="number" class="ingreso_neto" name="ingreso_neto_${i}" required></td>
<td><input type="number" class="aportacion_mensual" name="aportacion_mensual_${i}" required></td>
      `;
      tblBody.appendChild(tr);
    }
  }

  function sumar(clase,idRes,idInput){
    let total=0;
    document.querySelectorAll('.'+clase).forEach(el=> total+= parseFloat(el.value)||0);
    document.getElementById(idRes).textContent=total.toFixed(2);
    document.getElementById(idInput).value=total.toFixed(2);
  }

  renderAportadores(0);
</script>
</body>
</html>