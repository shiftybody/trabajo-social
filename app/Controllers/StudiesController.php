<?php

/**
 * Controlador de Estudios Socioeconómicos
 * 
 * Gestiona los estudios socioeconómicos de los pacientes:
 * - Creación y edición de estudios
 * - Gestión por secciones (Datos Generales, Familia, Salud, Vivienda, Economía)
 * - Cálculo automático de nivel socioeconómico
 * - Manejo de un solo estudio activo por paciente
 */

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\StudyModel;
use App\Models\PatientModel;
use App\Models\SocioeconomicLevelModel;
use App\Models\CriteriaModel;
use Exception;

class StudiesController
{
  private $studyModel;
  private $patientModel;
  private $levelModel;
  private $criteriaModel;

  public function __construct()
  {
    $this->patientModel = new PatientModel();
    $this->levelModel = new SocioeconomicLevelModel();
    $this->criteriaModel = new CriteriaModel();
  }

  /**
   * Vista principal de estudios de un paciente
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
      include APP_ROOT . 'app/Views/patients/studies/create.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en createView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * Vista para editar estudio existente
   */
  public function editView(Request $request)
  {
    try {
      $patientId = $request->param('id');
      $studyId = $request->param('study_id');

      $patient = $this->patientModel->getPatientById($patientId);
      $study = $this->studyModel->getStudyById($studyId);

      if (!$patient || !$study || $study->paciente_id != $patientId) {
        return Response::redirect(APP_URL . 'error/404');
      }

      ob_start();
      $titulo = 'Editar Estudio: ' . $patient->nombre . ' ' . $patient->apellido_paterno;
      include APP_ROOT . 'app/Views/patients/studies/edit.php';
      $content = ob_get_clean();
      return Response::html($content);
    } catch (Exception $e) {
      error_log("Error en editView: " . $e->getMessage());
      return Response::redirect(APP_URL . 'error/500');
    }
  }

  /**
   * API: Obtiene todos los estudios de un paciente
   */
  public function getStudiesByPatient(Request $request)
  {
    try {
      $patientId = $request->param('id');

      $studies = $this->studyModel->getStudiesByPatient($patientId);

      return Response::json([
        'status' => 'success',
        'data' => $studies
      ]);
    } catch (Exception $e) {
      error_log("Error en getStudiesByPatient: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener estudios del paciente'
      ], 500);
    }
  }

  /**
   * API: Obtiene un estudio específico con todos sus datos
   */
  public function getStudyById(Request $request)
  {
    try {
      $patientId = $request->param('patient_id');
      $studyId = $request->param('study_id');

      $study = $this->studyModel->getStudyWithAllData($studyId);

      if (!$study || $study->paciente_id != $patientId) {
        return Response::json([
          'status' => 'error',
          'message' => 'Estudio no encontrado'
        ], 404);
      }

      return Response::json([
        'status' => 'success',
        'data' => $study
      ]);
    } catch (Exception $e) {
      error_log("Error en getStudyById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener datos del estudio'
      ], 500);
    }
  }

  /**
   * API: Crea un nuevo estudio socioeconómico
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

      // Generar folio único automático
      $folio = $this->studyModel->generateUniqueFolio();

      $studyData = [
        'paciente_id' => $patientId,
        'folio_estudio' => $folio,
        'fecha_estudio' => $data['fecha_estudio'] ?: date('Y-m-d'),
        'motivo_estudio' => $data['motivo_estudio'] ?: 'Inicial',
        'estado' => 'en_proceso'
      ];

      $studyId = $this->studyModel->createStudy($studyData, Auth::getCurrentUserId());

      if ($studyId) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estudio creado correctamente',
          'data' => [
            'study_id' => $studyId,
            'folio' => $folio
          ]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al crear el estudio'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en createStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza datos generales del estudio
   */
  public function updateGeneralData(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateGeneralData($studyId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Datos generales actualizados correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar datos generales'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateGeneralData: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza información de la familia
   */
  public function updateFamily(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateFamily($studyId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Información familiar actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar información familiar'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateFamily: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Obtiene integrantes de la familia
   */
  public function getFamilyMembers(Request $request)
  {
    try {
      $studyId = $request->param('study_id');

      $members = $this->studyModel->getFamilyMembers($studyId);

      return Response::json([
        'status' => 'success',
        'data' => $members
      ]);
    } catch (Exception $e) {
      error_log("Error en getFamilyMembers: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener integrantes de la familia'
      ], 500);
    }
  }

  /**
   * API: Crea un nuevo integrante de la familia
   */
  public function createFamilyMember(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $memberId = $this->studyModel->createFamilyMember($studyId, $data, Auth::getCurrentUserId());

      if ($memberId) {
        return Response::json([
          'status' => 'success',
          'message' => 'Integrante agregado correctamente',
          'data' => ['member_id' => $memberId]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al agregar integrante'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en createFamilyMember: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza un integrante de la familia
   */
  public function updateFamilyMember(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $memberId = $request->param('member_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateFamilyMember($memberId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Integrante actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar integrante'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateFamilyMember: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Elimina un integrante de la familia
   */
  public function deleteFamilyMember(Request $request)
  {
    try {
      $memberId = $request->param('member_id');

      $deleted = $this->studyModel->deleteFamilyMember($memberId);

      if ($deleted) {
        return Response::json([
          'status' => 'success',
          'message' => 'Integrante eliminado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al eliminar integrante'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en deleteFamilyMember: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza información de salud
   */
  public function updateHealth(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateHealth($studyId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Información de salud actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar información de salud'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateHealth: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza información de vivienda
   */
  public function updateHousing(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateHousing($studyId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Información de vivienda actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar información de vivienda'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateHousing: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza información de economía
   */
  public function updateEconomy(Request $request)
  {
    try {
      $studyId = $request->param('study_id');
      $data = $request->POST();

      $updated = $this->studyModel->updateEconomy($studyId, $data, Auth::getCurrentUserId());

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Información económica actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar información económica'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en updateEconomy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Activa un estudio específico (desactiva automáticamente otros)
   */
  public function activateStudy(Request $request)
  {
    try {
      $patientId = $request->param('patient_id');
      $studyId = $request->param('study_id');

      // Verificar que el estudio pertenece al paciente
      $study = $this->studyModel->getStudyById($studyId);
      if (!$study || $study->paciente_id != $patientId) {
        return Response::json([
          'status' => 'error',
          'message' => 'Estudio no encontrado'
        ], 404);
      }

      // Verificar que el estudio esté completado
      if ($study->estado !== 'completado') {
        return Response::json([
          'status' => 'error',
          'message' => 'Solo se pueden activar estudios completados'
        ], 400);
      }

      $activated = $this->studyModel->activateStudy($patientId, $studyId, Auth::getCurrentUserId());

      if ($activated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estudio activado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al activar el estudio'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en activateStudy: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Calcula el nivel socioeconómico del estudio
   */
  public function calculateSocioeconomicLevel(Request $request)
  {
    try {
      $studyId = $request->param('study_id');

      // Obtener datos completos del estudio
      $study = $this->studyModel->getStudyWithAllData($studyId);
      if (!$study) {
        return Response::json([
          'status' => 'error',
          'message' => 'Estudio no encontrado'
        ], 404);
      }

      // Calcular puntaje total basado en criterios
      $totalScore = $this->calculateTotalScore($study);

      // Determinar nivel socioeconómico
      $level = $this->levelModel->getLevelByScore($totalScore);

      if (!$level) {
        return Response::json([
          'status' => 'error',
          'message' => 'No se pudo determinar el nivel socioeconómico'
        ], 400);
      }

      // Actualizar estudio con puntaje y nivel
      $updated = $this->studyModel->updateSocioeconomicLevel(
        $studyId,
        $totalScore,
        $level->id,
        Auth::getCurrentUserId()
      );

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel socioeconómico calculado correctamente',
          'data' => [
            'total_score' => $totalScore,
            'level' => $level->nivel,
            'level_id' => $level->id
          ]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar el nivel socioeconómico'
        ], 500);
      }
    } catch (Exception $e) {
      error_log("Error en calculateSocioeconomicLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * Calcula el puntaje total del estudio basado en criterios
   * 
   * @param object $study Datos completos del estudio
   * @return int Puntaje total calculado
   */
  private function calculateTotalScore($study)
  {
    try {
      $totalScore = 0;

      // Obtener todos los criterios de evaluación
      $criteria = $this->criteriaModel->getAllCriteriaWithSubcategories();

      foreach ($criteria as $criterion) {
        $score = $this->evaluateCriterion($criterion, $study);
        $totalScore += $score;
      }

      return $totalScore;
    } catch (Exception $e) {
      error_log("Error en calculateTotalScore: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Evalúa un criterio específico contra los datos del estudio
   * 
   * @param object $criterion Criterio a evaluar
   * @param object $study Datos del estudio
   * @return int Puntaje obtenido para este criterio
   */
  private function evaluateCriterion($criterion, $study)
  {
    try {
      // Lógica de evaluación según tipo de criterio
      switch ($criterion->tipo_criterio) {
        case 'rango_numerico':
          return $this->evaluateNumericRange($criterion, $study);
        case 'valor_especifico':
          return $this->evaluateSpecificValue($criterion, $study);
        case 'booleano':
          return $this->evaluateBoolean($criterion, $study);
        default:
          return 0;
      }
    } catch (Exception $e) {
      error_log("Error en evaluateCriterion: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Evalúa criterio de rango numérico
   */
  private function evaluateNumericRange($criterion, $study)
  {
    // Implementar lógica específica según subcategoría
    // Ejemplo: número de integrantes familia, dependientes económicos, etc.
    return 0; // Placeholder
  }

  /**
   * Evalúa criterio de valor específico
   */
  private function evaluateSpecificValue($criterion, $study)
  {
    // Implementar lógica específica según subcategoría
    // Ejemplo: tipo de vivienda, protocolo, etc.
    return 0; // Placeholder
  }

  /**
   * Evalúa criterio booleano
   */
  private function evaluateBoolean($criterion, $study)
  {
    // Implementar lógica específica según subcategoría
    // Ejemplo: tiene servicios, pertenece a grupo étnico, etc.
    return 0; // Placeholder
  }
}
