<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\CriteriaModel;
use App\Models\SocioeconomicLevelModel;
use App\Models\ContributionRuleModel;
use Exception;

/**
 * Controlador de Configuración
 * 
 * Maneja únicamente las operaciones básicas de configuración:
 * - Niveles socioeconómicos
 * - Reglas de aportación
 * - Criterios básicos
 */
class SettingController
{
  private $criteriaModel;
  private $levelModel;
  private $ruleModel;

  public function __construct()
  {
    $this->criteriaModel = new CriteriaModel();
    $this->levelModel = new SocioeconomicLevelModel();
    $this->ruleModel = new ContributionRuleModel();
  }

  /**
   * Vista principal de configuración
   */
  public function indexView()
  {
    ob_start();
    $titulo = 'Configuración';
    include APP_ROOT . 'app/Views/settings/index.php';
    $contenido = ob_get_clean();
    return Response::html($contenido);
  }

  // ==================== NIVELES SOCIOECONÓMICOS ====================

  /**
   * API: Obtiene todos los niveles socioeconómicos
   */
  public function getAllLevels()
  {
    try {
      $levels = $this->levelModel->getAllLevels(true); // incluir inactivos

      return Response::json([
        'status' => 'success',
        'data' => $levels
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllLevels: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener niveles'
      ], 500);
    }
  }



  /**
   * API: Obtiene un nivel socioeconómico por ID
   */
  public function getLevelById(Request $request)
  {
    try {
      $id = $request->param('id');
      $level = $this->levelModel->getLevelById($id);

      if (!$level) {
        return Response::json([
          'status' => 'error',
          'message' => 'Nivel no encontrado'
        ], 404);
      }

      return Response::json([
        'status' => 'success',
        'data' => $level
      ]);
    } catch (Exception $e) {
      error_log("Error en getLevelById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener nivel'
      ], 500);
    }
  }

  /**
   * API: Crea un nuevo nivel socioeconómico
   */
  public function createLevel(Request $request)
  {
    try {
      $data = $request->post();
      $data['usuario_creacion_id'] = Auth::user()->usuario_id;

      $levelId = $this->levelModel->createLevel($data);

      if ($levelId) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel creado correctamente',
          'data' => ['id' => $levelId]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'El nivel ya existe o el puntaje es inválido'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en createLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza un nivel socioeconómico
   */
  public function updateLevel(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $data['usuario_modificacion_id'] = Auth::user()->usuario_id;

      $updated = $this->levelModel->updateLevel($id, $data);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar nivel'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en updateLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Cambia el estado de un nivel socioeconómico
   */
  public function toggleLevelStatus(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $estado = $data['estado'] === 'true' ? 1 : 0;
      $userId = Auth::user()->usuario_id;

      $updated = $this->levelModel->toggleLevelStatus($id, $estado, $userId);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estado actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al cambiar estado'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en toggleLevelStatus: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Elimina un nivel socioeconómico
   */
  public function deleteLevel(Request $request)
  {
    try {
      $id = $request->param('id');

      $deleted = $this->levelModel->deleteLevel($id);

      if ($deleted) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel eliminado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Nivel asociado a una regla o estudio, no se puede eliminar'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en deleteLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  // ==================== REGLAS DE APORTACIÓN ====================

  /**
   * API: Obtiene todas las reglas de aportación para
   */
  public function getAllRules(Request $request)
  {
    try {
      $filters = [
        'nivel_id' => $request->get('nivel_id'),
        'edad' => $request->get('edad'),
        'periodicidad' => $request->get('periodicidad')
      ];

      $rules = $this->ruleModel->getAllRules($filters);

      return Response::json([
        'status' => 'success',
        'data' => $rules
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllRules: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener reglas'
      ], 500);
    }
  }

  /**
   * API: Obtiene una regla de aportación por ID
   */
  public function getRuleById(Request $request)
  {
    try {
      $id = $request->param('id');
      $rule = $this->ruleModel->getRuleById($id);

      if (!$rule) {
        return Response::json([
          'status' => 'error',
          'message' => 'Regla no encontrada'
        ], 404);
      }

      return Response::json([
        'status' => 'success',
        'data' => $rule
      ]);
    } catch (Exception $e) {
      error_log("Error en getRuleById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener regla'
      ], 500);
    }
  }

  /**
   * API: Crea una nueva regla de aportación
   */
  public function createRule(Request $request)
  {
    try {
      $data = $request->post();
      $data['usuario_creacion_id'] = Auth::user()->usuario_id;

      $ruleId = $this->ruleModel->createRule($data);

      if ($ruleId) {
        return Response::json([
          'status' => 'success',
          'message' => 'Regla creada correctamente',
          'data' => ['id' => $ruleId]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al crear regla'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en createRule: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza una regla de aportación
   */
  public function updateRule(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $data['usuario_modificacion_id'] = Auth::user()->usuario_id;

      $updated = $this->ruleModel->updateRule($id, $data);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Regla actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar regla'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en updateRule: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Cambia el estado de una regla de aportación
   */
  public function toggleRuleStatus(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $estado = $data['estado'] === 'true' ? 1 : 0;
      $userId = Auth::user()->usuario_id;

      $updated = $this->ruleModel->toggleRuleStatus($id, $estado, $userId);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estado actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al cambiar estado'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en toggleRuleStatus: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Elimina una regla de aportación
   */
  public function deleteRule(Request $request)
  {
    try {
      $id = $request->param('id');

      $deleted = $this->ruleModel->deleteRule($id);

      if ($deleted) {
        return Response::json([
          'status' => 'success',
          'message' => 'Regla eliminada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al eliminar regla'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en deleteRule: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  // ==================== CRITERIOS ====================

  /**
   * API: Obtiene todos los criterios
   */
  public function getAllCriteria()
  {
    try {
      $criteria = $this->criteriaModel->getAllCriteria();

      return Response::json([
        'status' => 'success',
        'data' => $criteria
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllCriteria: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener criterios'
      ], 500);
    }
  }

  /**
   * API: Obtiene un criterio por ID
   */
  public function getCriteriaById(Request $request)
  {
    try {
      $id = $request->param('id');
      $criteria = $this->criteriaModel->getCriteriaById($id);

      if (!$criteria) {
        return Response::json([
          'status' => 'error',
          'message' => 'Criterio no encontrado'
        ], 404);
      }

      return Response::json([
        'status' => 'success',
        'data' => $criteria
      ]);
    } catch (Exception $e) {
      error_log("Error en getCriteriaById: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener criterio'
      ], 500);
    }
  }

  /**
   * API: Crea un nuevo criterio
   */
  public function createCriteria(Request $request)
  {
    try {
      $data = $request->post();
      $data['usuario_creacion_id'] = Auth::user()->usuario_id;

      $criteriaId = $this->criteriaModel->createCriteria($data);

      if ($criteriaId) {
        return Response::json([
          'status' => 'success',
          'message' => 'Criterio creado correctamente',
          'data' => ['id' => $criteriaId]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al crear criterio'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en createCriteria: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Actualiza un criterio
   */
  public function updateCriteria(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $data['usuario_modificacion_id'] = Auth::user()->usuario_id;

      $updated = $this->criteriaModel->updateCriteria($id, $data);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Criterio actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al actualizar criterio'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en updateCriteria: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Cambia el estado de un criterio
   */
  public function toggleCriteriaStatus(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $estado = $data['estado'] === 'true' ? 1 : 0;
      $userId = Auth::user()->usuario_id;

      $updated = $this->criteriaModel->toggleCriteriaStatus($id, $estado, $userId);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estado actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al cambiar estado'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en toggleCriteriaStatus: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }

  /**
   * API: Elimina un criterio
   */
  public function deleteCriteria(Request $request)
  {
    try {
      $id = $request->param('id');

      $deleted = $this->criteriaModel->deleteCriteria($id);

      if ($deleted) {
        return Response::json([
          'status' => 'success',
          'message' => 'Criterio eliminado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al eliminar criterio'
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en deleteCriteria: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error interno del servidor'
      ], 500);
    }
  }
}
