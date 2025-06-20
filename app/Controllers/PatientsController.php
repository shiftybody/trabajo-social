<?php

/**
 * Controlador de Pacientes
 * 
 * Gestiona la visualización, edición y consulta de pacientes
 */

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\PatientModel;
use App\Models\SocioeconomicLevelModel;
use Exception;

class PatientsController
{
  private $patientModel;
  private $levelModel;

  public function __construct()
  {
    $this->patientModel = new PatientModel();
    $this->levelModel = new SocioeconomicLevelModel();
  }

  /**
   * Vista principal de la lista de pacientes
   */
  public function indexView()
  {
    ob_start();
    $titulo = 'Pacientes';
    include APP_ROOT . 'app/Views/patients/index.php';
    $content = ob_get_clean();
    return Response::html($content);
  }

  /**
   * Vista de detalles de un paciente específico
   */
  public function viewView(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $patient = $this->patientModel->getPatientById($patientId);
      if (!$patient) {
        return Response::redirect(APP_URL . 'error/404');
      }

      ob_start();
      $titulo = 'Detalles del Paciente: ' . $patient->nombre . ' ' . $patient->apellido_paterno;
      include APP_ROOT . 'app/Views/patients/view.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en viewView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * Vista de edición de paciente (solo campos permitidos)
   */
  public function editView(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $patient = $this->patientModel->getPatientById($patientId);
      if (!$patient) {
        return Response::redirect(APP_URL . 'error/404');
      }

      ob_start();
      $titulo = 'Editar Paciente: ' . $patient->nombre . ' ' . $patient->apellido_paterno;
      include APP_ROOT . 'app/Views/patients/edit.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en editView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * API: Obtiene todos los pacientes con paginación y filtros
   */
  public function getAllPatients(Request $request)
  {
    try {
      $filters = [
        'search' => $request->get('search', ''),
        'protocol' => $request->get('protocol', ''),
        'min_age' => $request->get('min_age', 0),
        'max_age' => $request->get('max_age', 150),
        'study_status' => $request->get('study_status', ''),
        'page' => (int)$request->get('page', 1),
        'limit' => (int)$request->get('limit', 50)
      ];

      $result = $this->patientModel->getAllPatientsWithDetails($filters);

      return Response::json([
        'status' => 'success',
        'data' => $result['patients'],
        'pagination' => [
          'current_page' => $filters['page'],
          'total_pages' => $result['total_pages'],
          'total_records' => $result['total_records'],
          'limit' => $filters['limit']
        ]
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllPatients: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener la lista de pacientes'
      ], 500);
    }
  }

  /**
   * API: Obtiene un paciente por ID con detalles completos
   */
  public function getPatientById(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $patient = $this->patientModel->getPatientWithDetails($patientId);
      if (!$patient) {
        return Response::json([
          'status' => 'error',
          'message' => 'Paciente no encontrado'
        ], 404);
      }

      return Response::json([
        'status' => 'success',
        'data' => $patient
      ]);
    } catch (Exception $e) {
      error_log("Error en getPatientById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener datos del paciente'
      ], 500);
    }
  }

  /**
   * API: Actualiza datos del paciente (excluyendo campos protegidos)
   */
  public function updatePatient(Request $request)
  {
    try {
      $patientId = $request->param('id');
      $data = $request->POST();

      // CRÍTICO: Verificar que no se intenten modificar campos protegidos
      $protectedFields = ['id', 'estado', 'codigo'];
      foreach ($protectedFields as $field) {
        if (isset($data[$field])) {
          return Response::json([
            'status' => 'error',
            'message' => "El campo '$field' no puede ser modificado"
          ], 400);
        }
      }

      // Verificar que el paciente existe
      $patient = $this->patientModel->getPatientById($patientId);
      if (!$patient) {
        return Response::json([
          'status' => 'error',
          'message' => 'Paciente no encontrado'
        ], 404);
      }

      // Actualizar datos permitidos
      $updated = $this->patientModel->updatePatient($patientId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Datos del paciente actualizados correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'No se pudo actualizar el paciente'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updatePatient: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Obtiene el estudio socioeconómico activo de un paciente
   */
  public function getActiveStudy(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $activeStudy = $this->patientModel->getActiveStudy($patientId);

      return Response::json([
        'status' => 'success',
        'data' => $activeStudy
      ]);
    } catch (Exception $e) {
      error_log("Error en getActiveStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener estudio activo'
      ], 500);
    }
  }

  /**
   * API: Obtiene datos para cálculo de aportación del paciente
   */
  public function getContributionData(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $contributionData = $this->patientModel->getContributionData($patientId);

      return Response::json([
        'status' => 'success',
        'data' => $contributionData
      ]);
    } catch (Exception $e) {
      error_log("Error en getContributionData: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener datos de aportación'
      ], 500);
    }
  }
}
