<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\CriteriaModel;
use App\Models\CategoryModel;
use App\Models\SubcategoryModel;
use App\Models\SocioeconomicLevelModel;
use App\Models\ContributionRuleModel;
use Exception;

/**
 * Controlador de Configuración
 * 
 * Maneja las operaciones de configuración:
 * - Niveles socioeconómicos
 * - Reglas de aportación
 * - Criterios de puntuación
 */
class SettingController
{
  private $criteriaModel;
  private $categoryModel;
  private $subcategoryModel;
  private $levelModel;
  private $ruleModel;

  public function __construct()
  {
    $this->criteriaModel = new CriteriaModel();
    $this->categoryModel = new CategoryModel();
    $this->subcategoryModel = new SubcategoryModel();
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

  // ==================== NAVEGACIÓN JERÁRQUICA ====================

  /**
   * API: Obtiene la estructura jerárquica categorías->subcategorías
   */
  public function getNavigationStructure()
  {
    try {
      $categories = $this->categoryModel->getCategoriesWithSubcategories();

      return Response::json([
        'status' => 'success',
        'data' => $categories
      ]);
    } catch (Exception $e) {
      error_log("Error en getNavigationStructure: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener estructura de navegación'
      ], 500);
    }
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

      // Validar datos usando la validación del modelo
      $resultado = $this->levelModel->createLevel($data);

      if ($resultado['success']) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel socioeconómico creado correctamente',
          'data' => ['id' => $resultado['data']]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'errors' => $resultado['errors']
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en createLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'errors' => ['general' => 'Error interno del servidor']
      ], 500);
    }
  }

  /**
   * API: Actualiza un nivel socioeconómico existente
   */
  public function updateLevel(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();

      $data['usuario_modificacion_id'] = Auth::user()->usuario_id;

      // Verificar que el nivel existe
      $nivelExistente = $this->levelModel->getLevelById($id);
      if (!$nivelExistente) {
        return Response::json([
          'status' => 'error',
          'errors' => ['general' => 'Nivel socioeconómico no encontrado']
        ], 404);
      }

      // Validar que se hayan enviado cambios
      $cambiosDetectados = false;
      if (isset($data['nivel']) && $data['nivel'] !== $nivelExistente->nivel) {
        $cambiosDetectados = true;
      }
      if (isset($data['puntaje_minimo']) && $data['puntaje_minimo'] != $nivelExistente->puntaje_minimo) {
        $cambiosDetectados = true;
      }
      if (isset($data['estado']) && $data['estado'] != $nivelExistente->estado) {
        $cambiosDetectados = true;
      }

      if (!$cambiosDetectados) {
        return Response::json([
          'status' => 'success',
          'message' => 'No se realizaron cambios en el nivel'
        ]);
      }

      // Actualizar usando la validación del modelo
      $resultado = $this->levelModel->updateLevel($id, $data);

      if ($resultado['success']) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel socioeconómico actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'errors' => $resultado['errors']
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en updateLevel: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'errors' => ['general' => 'Error interno del servidor']
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
      $data = $request->json();
      $estado = $data['status'];
      $userId = Auth::user()->usuario_id;

      // imprimir contenido de data
      error_log("toggleLevelStatus data: " . print_r($data, true));

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
   * API: Obtiene todas las reglas de aportación
   */
  public function getAllRules(Request $request)
  {
    try {
      $filters = [
        'nivel_id' => $request->get('nivel_id'),
      ];

      $rules = [];

      if ($filters['nivel_id'] !== "0") {
        error_log(" el filtro es diferente de 0");
        $rules = $this->ruleModel->getAllRules($filters);
      }

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

      // Validar datos usando la validación del modelo
      $resultado = $this->ruleModel->createRule($data);

      if ($resultado['success']) {
        return Response::json([
          'status' => 'success',
          'message' => 'Regla de aportación creada correctamente',
          'data' => ['id' => $resultado['data']]
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'errors' => $resultado['errors']
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en createRule: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'errors' => ['general' => 'Error interno del servidor']
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

      $resultado = $this->ruleModel->updateRule($id, $data);

      if ($resultado['success']) {
        return Response::json([
          'status' => 'success',
          'message' => 'Regla de aportación actualizada correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'errors' => $resultado['errors']
        ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en updateRule: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'errors' => ['general' => 'Error interno del servidor']
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
      $data = $request->json();
      $estado = $data['status'];
      $userId = Auth::user()->usuario_id;

      error_log("toggleRuleStatus data: " . print_r($data, true));

      $updated = $this->ruleModel->toggleRuleStatus($id, $estado, $userId);

      if ($updated) {
        return Response::json([
          'status' => 'success',
          'message' => 'Estado de regla actualizado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al cambiar estado de la regla'
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
          'message' => 'La regla está asociada a solicitudes de pago, no se puede eliminar'
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
  // ==================== CRITERIOS DE PUNTUACIÓN ====================

  /**
   * API: Obtiene criterios filtrados por subcategoría (simplificado)
   */
  public function getAllCriteria(Request $request)
  {
    try {

      $subcategoryId = $request->get('subcategory_id');

      if ($subcategoryId && !is_numeric($subcategoryId)) {
        return Response::json([
          'status' => 'error',
          'message' => 'ID de subcategoría inválido'
        ], 400);
      }

      $criteria = $this->criteriaModel->getAllCriteria($subcategoryId);

      return Response::json([
        'status' => 'success',
        'data' => $criteria,
        'subcategory_id' => $subcategoryId
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

      $result = $this->criteriaModel->createCriteria($data);

      if (is_array($result) && isset($result['error']) && $result['error'] === 'validation') {
        return Response::json([
          'status' => 'error',
          'message' => 'Error de validación',
          'errors' => $result['errors']
        ], 422);
      }

      if ($result) {
        return Response::json([
          'status' => 'success',
          'message' => 'Criterio creado correctamente',
          'data' => ['id' => $result]
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

      $result = $this->criteriaModel->updateCriteria($id, $data);

      if (is_array($result) && isset($result['error']) && $result['error'] === 'validation') {
        return Response::json([
          'status' => 'error',
          'message' => 'Error de validación',
          'errors' => $result['errors']
        ], 422);
      }

      if ($result) {
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
      $data = $request->json();
      $estado = $data['estado'] ? 1 : 0;
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

  /**
   * API: Obtiene todas las subcategorías usando SubcategoryModel
   */
  public function getAllSubcategories()
  {
    try {
      $subcategories = $this->subcategoryModel->getAllSubcategories();

      return Response::json([
        'status' => 'success',
        'data' => $subcategories
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllSubcategories: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener subcategorías'
      ], 500);
    }
  }
}
