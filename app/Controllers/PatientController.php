<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\patientModel;

class PatientController
{
  // Mostrar la vista principal
  public function indexView()
  {
    ob_start();
    include APP_ROOT . 'app/Views/patients/index.php';
    $content = ob_get_clean();
    return Response::html($content);
  }

  // Obtener todos los pacientes (API)
  public function getAll()
  {
    $pacientes = patientModel::getAll();
    return Response::json([
      'status' => 'success',
      'data' => $pacientes
    ]);
  }

  // Guardar nuevo paciente (API)
  public function store()
  {
    $data = Request::json(); // o Request::all() si se usa POST form-data

    // Validación simple (opcional)
    if (empty($data['nombre']) || empty($data['apellido_paterno'])) {
      return Response::json(['status' => 'error', 'message' => 'Faltan campos obligatorios'], 400);
    }

    $insertado = patientModel::crear($data);

    if ($insertado) {
      return Response::json(['status' => 'success', 'message' => 'Paciente guardado correctamente']);
    } else {
      return Response::json(['status' => 'error', 'message' => 'No se pudo guardar el paciente'], 500);
    }
  }

  // Actualizar un paciente existente
  public function update($id)
  {
    $data = Request::json();

    if (!patientModel::actualizar($id, $data)) {
      return Response::json([
        'status' => 'error',
        'message' => 'Error al actualizar el paciente'
      ], 500);
    }

    return Response::json([
      'status' => 'success',
      'message' => 'Paciente actualizado correctamente'
    ]);
  }

  // Eliminar paciente
  public function delete($id)
  {
    if (!patientModel::eliminar($id)) {
      return Response::json([
        'status' => 'error',
        'message' => 'Error al eliminar el paciente'
      ], 500);
    }

    return Response::json([
      'status' => 'success',
      'message' => 'Paciente eliminado correctamente'
    ]);
  }
}
