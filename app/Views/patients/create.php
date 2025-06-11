<?php
require_once 'config/env.php'; // tu conexión DB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $folio = $_POST['id'] ?? null;
  $nombre = $_POST['nombre'] ?? '';
  // ...

  $stmt = $pdo->prepare("INSERT INTO paciente (id, nombre) VALUES (?, ?)");
  $stmt->execute([$folio, $nombre]);

  echo json_encode(['status' => 'success']);
}
