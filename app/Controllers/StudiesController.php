<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\StudyModel;
use App\Models\PatientModel;
use App\Models\SocioeconomicLevelModel;
use Exception;

class StudiesController
{
  private $studyModel;
  private $patientModel;
  private $levelModel;

  public function __construct()
  {
    $this->studyModel = new StudyModel();
    $this->patientModel = new PatientModel();
    $this->levelModel = new SocioeconomicLevelModel();
  }

  /**
   * Vista principal de estudios
   */
  public function indexView(Request $request)
  {
    try {
      ob_start();
      $titulo = 'Estudios Socioeconómicos';
      include APP_ROOT . 'app/Views/studies/index.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en indexView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * API: Obtiene todos los pacientes con información de estudios
   */
  public function getPatientsWithStudyInfo(Request $request)
  {
    try {
      $patients = $this->studyModel->getPatientsWithStudyInfo();

      return Response::json([
        'status' => 'success',
        'data' => $patients
      ]);
    } catch (Exception $e) {
      error_log("Error en getPatientsWithStudyInfo: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener información de pacientes'
      ], 500);
    }
  }

  /**
   * Vista para crear nuevo estudio
   */
  public function createView(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $patient = $this->patientModel->getPatientById($patientId);
      if (!$patient) {
        return Response::redirect(APP_URL . 'error/404');
      }

      ob_start();
      $titulo = 'Nuevo Estudio: ' . $patient->nombre . ' ' . $patient->apellido_paterno;
      include APP_ROOT . 'app/Views/studies/create.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en createView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * API: Crea un nuevo estudio
   */
  public function createStudy(Request $request)
  {
    try {
      $patientId = $request->param('id');
      $data = $request->POST();

      // Verificar que el paciente existe
      $patient = $this->patientModel->getPatientById($patientId);
      if (!$patient) {
        return Response::json([
          'status' => 'error',
          'message' => 'Paciente no encontrado'
        ], 404);
      }

      $data['paciente_id'] = $patientId;
      $studyId = $this->studyModel->createStudy($data);

      return Response::json([
        'status' => 'success',
        'message' => 'Estudio creado exitosamente',
        'data' => ['study_id' => $studyId]
      ]);
    } catch (Exception $e) {
      error_log("Error en createStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al crear el estudio'
      ], 500);
    }
  }

  /**
   * API: Obtiene historial de estudios de un paciente
   */
  public function getPatientStudyHistory(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $studies = $this->studyModel->getStudiesByPatient($patientId);

      return Response::json([
        'status' => 'success',
        'data' => $studies
      ]);
    } catch (Exception $e) {
      error_log("Error en getPatientStudyHistory: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener historial de estudios'
      ], 500);
    }
  }

  /**
   * API: Activa un estudio específico
   */
  public function activateStudy(Request $request)
  {
    try {
      $patientId = $request->param('patient_id');
      $studyId = $request->param('study_id');

      $success = $this->studyModel->activateStudy($studyId, $patientId);

      if ($success) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estudio activado exitosamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'No se pudo activar el estudio'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en activateStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al activar el estudio'
      ], 500);
    }
  }

  /**
   * API: Copia un estudio anterior
   */
  public function copyStudy(Request $request)
  {
    try {
      $patientId = $request->param('id');
      $originalStudyId = $request->POST('original_study_id');

      if (!$originalStudyId) {
        return Response::json([
          'status' => 'error',
          'message' => 'ID del estudio original requerido'
        ], 400);
      }

      $newStudyId = $this->studyModel->copyStudy($originalStudyId, $patientId);

      return Response::json([
        'status' => 'success',
        'message' => 'Estudio copiado exitosamente',
        'data' => ['study_id' => $newStudyId]
      ]);
    } catch (Exception $e) {
      error_log("Error en copyStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al copiar el estudio'
      ], 500);
    }
  }
}
