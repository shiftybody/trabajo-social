<?php
require_once '../../config/database.php'; // Ajusta la ruta a tu archivo de conexión

header('Content-Type: text/html; charset=UTF-8');

// Conexión a la base de datos
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener datos generales del paciente
$sql = "SELECT 
          folio,
          CONCAT(apellido_paterno, ' ', apellido_materno, ' ', nombres) AS nombre_completo,
          fecha_nacimiento,
          lugar_nacimiento,
          fecha_ingreso_inb
        FROM datosgenerales";

$result = $conn->query($sql);

echo '<table id="tabla-datos-generales" class="hover nowrap cell-borders" style="display: none; width:100%;">
        <thead>
          <tr>
            <th>FOLIO</th>
            <th>NOMBRE COMPLETO</th>
            <th>FECHA NACIMIENTO</th>
            <th>LUGAR NACIMIENTO</th>
            <th>FECHA INGRESO</th>
            <th>ACCIONES</th>
          </tr>
        </thead>
        <tbody>';

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo '<tr>
            <td>' . htmlspecialchars($row['folio']) . '</td>
            <td>' . htmlspecialchars($row['nombre_completo']) . '</td>
            <td>' . htmlspecialchars($row['fecha_nacimiento']) . '</td>
            <td>' . htmlspecialchars($row['lugar_nacimiento']) . '</td>
            <td>' . htmlspecialchars($row['fecha_ingreso_inb']) . '</td>
            <td>
              <button onclick="editar(' . $row['folio'] . ')" title="Editar">✏️</button>
              <button onclick="eliminar(' . $row['folio'] . ')" title="Eliminar">🗑️</button>
              <button onclick="cargarFormularioEstudio(' . $row['folio'] . ')" title="Agregar Estudio">➕</button>
            </td>
          </tr>';
  }
}

echo '</tbody></table>';

$conn->close();
