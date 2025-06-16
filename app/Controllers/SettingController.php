<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\SubcategoryModel;
use App\Models\CriteriaModel;
use App\Models\SocioeconomicLevelModel;
use App\Models\ContributionRuleModel;
use Exception;

/**
 * Controlador de Configuración
 * 
 * Maneja todas las operaciones de configuración del sistema
 * incluyendo criterios, niveles socioeconómicos y reglas de aportación
 */
class SettingController
{
  private $categoryModel;
  private $subcategoryModel;
  private $criteriaModel;
  private $levelModel;
  private $ruleModel;

  public function __construct()
  {
    $this->categoryModel = new CategoryModel();
    $this->subcategoryModel = new SubcategoryModel();
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

  // ==================== SECCIONES PRINCIPALES ====================

  /**
   * API: Obtiene el contenido de una sección específica
   */
  public function getSection(Request $request)
  {
    try {
      $section = $request->get('section');

      if (!$section) {
        return Response::json([
          'status' => 'error',
          'message' => 'Sección no especificada'
        ], 400);
      }

      switch ($section) {
        case 'niveles-socioeconomicos':
          return $this->getSocioeconomicLevelsSection();

        case 'reglas-aportacion':
          return $this->getContributionRulesSection();

        case 'protocolo':
          return $this->getCriteriaSection('protocolo', 1);

        case 'gasto-traslado':
          return $this->getCriteriaSection('gasto-traslado', 3);

        case 'tiempo-traslado':
          return $this->getCriteriaSection('tiempo-traslado', 2);

        case 'integrantes':
          return $this->getCriteriaSection('integrantes', 4);

        case 'hijos':
          return $this->getCriteriaSection('hijos', 5);

        case 'tipo-familia':
          return $this->getCriteriaSection('tipo-familia', 6);

        case 'tipo-vivienda':
          return $this->getCriteriaSection('tipo-vivienda', 9);

        case 'tenencia':
          return $this->getCriteriaSection('tenencia', 10);

        case 'zona':
          return $this->getCriteriaSection('zona', 11);

        case 'materiales':
          return $this->getMaterialsSection();

        case 'servicios':
          return $this->getServicesSection();

        default:
          return Response::json([
            'status' => 'error',
            'message' => 'Sección no válida'
          ], 400);
      }
    } catch (Exception $e) {
      error_log("Error en getSection: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener sección'
      ], 500);
    }
  }

  // ==================== NIVELES SOCIOECONÓMICOS ====================

  /**
   * Obtiene la sección de niveles socioeconómicos
   */
  private function getSocioeconomicLevelsSection()
  {
    $levels = $this->levelModel->getLevelsWithDetails();

    return Response::json([
      'status' => 'success',
      'data' => [
        'type' => 'socioeconomic-levels',
        'title' => 'Niveles Socioeconómicos',
        'levels' => $levels,
        'actions' => ['create', 'edit', 'delete', 'toggle']
      ]
    ]);
  }

  /**
   * API: Obtiene todos los niveles socioeconómicos
   */
  public function getAllLevels()
  {
    try {
      $levels = $this->levelModel->getAllLevels();

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
          'message' => 'Error al crear nivel'
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
      $estado = $data['estado'] ?: false;
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

      // Verificar si se puede eliminar
      if (!$this->levelModel->canDeleteLevel($id)) {
        return Response::json([
          'status' => 'error',
          'message' => 'No se puede eliminar el nivel porque tiene dependencias'
        ], 400);
      }

      $deleted = $this->levelModel->deleteLevel($id);

      if ($deleted) {
        return Response::json([
          'status' => 'success',
          'message' => 'Nivel eliminado correctamente'
        ]);
      } else {
        return Response::json([
          'status' => 'error',
          'message' => 'Error al eliminar nivel'
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
   * Obtiene la sección de reglas de aportación
   */
  private function getContributionRulesSection()
  {
    $rules = $this->ruleModel->getAllRules();
    $levels = $this->levelModel->getAllLevels();
    $periodicities = $this->ruleModel->getPeriodicityOptions();

    return Response::json([
      'status' => 'success',
      'data' => [
        'type' => 'contribution-rules',
        'title' => 'Reglas de Aportación',
        'rules' => $rules,
        'levels' => $levels,
        'periodicities' => $periodicities,
        'actions' => ['create', 'edit', 'delete', 'bulk-create']
      ]
    ]);
  }

  /**
   * API: Obtiene todas las reglas de aportación
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
   * API: Obtiene matriz de aportaciones para un nivel
   */
  public function getContributionMatrix(Request $request)
  {
    try {
      $nivelId = $request->get('nivel_id');

      if (!$nivelId) {
        return Response::json([
          'status' => 'error',
          'message' => 'ID de nivel requerido'
        ], 400);
      }

      $matrix = $this->ruleModel->getContributionMatrix($nivelId);

      return Response::json([
        'status' => 'success',
        'data' => $matrix
      ]);
    } catch (Exception $e) {
      error_log("Error en getContributionMatrix: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener matriz'
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
   * API: Crea reglas en lote
   */
  public function createBulkRules(Request $request)
  {
    try {
      $data = $request->post();
      $nivelId = $data['nivel_id'];
      $reglas = $data['reglas'];
      $userId = Auth::user()->usuario_id;

      $resultado = $this->ruleModel->createBulkRules($nivelId, $reglas, $userId);

      return Response::json([
        'status' => 'success',
        'message' => "Proceso completado: {$resultado['success']} éxitos, {$resultado['errors']} errors",
        'data' => $resultado
      ]);
    } catch (Exception $e) {
      error_log("Error en createBulkRules: " . $e->getMessage());
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
   * API: Cambia el estado de una regla de aportación
   */
  public function toggleRuleStatus(Request $request)
  {
    try {
      $id = $request->param('id');
      $data = $request->post();
      $estado = $data['estado'] ?: false;
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

  // ==================== CRITERIOS ====================

  /**
   * Obtiene la sección de criterios por subcategoría
   */
  private function getCriteriaSection($sectionName, $subcategoryId)
  {
    $subcategory = $this->subcategoryModel->getSubcategoryById($subcategoryId);
    $criteria = $this->criteriaModel->getCriteriaBySubcategory($subcategoryId);

    return Response::json([
      'status' => 'success',
      'data' => [
        'type' => 'criteria',
        'title' => $subcategory ? $subcategory->nombre : $sectionName,
        'subcategory' => $subcategory,
        'criteria' => $criteria,
        'actions' => ['create', 'edit', 'delete', 'toggle']
      ]
    ]);
  }

  /**
   * Obtiene la sección de materiales (múltiples subcategorías)
   */
  private function getMaterialsSection()
  {
    $subcategories = [12, 13, 14]; // Paredes, Techo, Piso
    $allCriteria = [];

    foreach ($subcategories as $subcatId) {
      $subcategory = $this->subcategoryModel->getSubcategoryById($subcatId);
      $criteria = $this->criteriaModel->getCriteriaBySubcategory($subcatId);

      $allCriteria[] = [
        'subcategory' => $subcategory,
        'criteria' => $criteria
      ];
    }

    return Response::json([
      'status' => 'success',
      'data' => [
        'type' => 'criteria-grouped',
        'title' => 'Materiales de Construcción',
        'groups' => $allCriteria,
        'actions' => ['create', 'edit', 'delete', 'toggle']
      ]
    ]);
  }

  /**
   * Obtiene la sección de servicios (múltiples subcategorías)
   */
  private function getServicesSection()
  {
    $subcategories = [15, 16]; // Agua, Luz
    $allCriteria = [];

    foreach ($subcategories as $subcatId) {
      $subcategory = $this->subcategoryModel->getSubcategoryById($subcatId);
      $criteria = $this->criteriaModel->getCriteriaBySubcategory($subcatId);

      $allCriteria[] = [
        'subcategory' => $subcategory,
        'criteria' => $criteria
      ];
    }

    return Response::json([
      'status' => 'success',
      'data' => [
        'type' => 'criteria-grouped',
        'title' => 'Servicios de la Vivienda',
        'groups' => $allCriteria,
        'actions' => ['create', 'edit', 'delete', 'toggle']
      ]
    ]);
  }

  /**
   * API: Obtiene todos los criterios con filtros
   */
  public function getAllCriteria(Request $request)
  {
    try {
      $subcategoryId = $request->get('subcategory_id');
      $criteria = $this->criteriaModel->getAllCriteria($subcategoryId);

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
      $estado = $data['estado'] ?: false;
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

  // ==================== DATOS AUXILIARES ====================

  /**
   * API: Obtiene todas las categorías
   */
  public function getAllCategories()
  {
    try {
      $categories = $this->categoryModel->getAllCategories();

      return Response::json([
        'status' => 'success',
        'data' => $categories
      ]);
    } catch (Exception $e) {
      error_log("Error en getAllCategories: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener categorías'
      ], 500);
    }
  }

  /**
   * API: Obtiene subcategorías por categoría
   */
  public function getSubcategoriesByCategory(Request $request)
  {
    try {
      $categoryId = $request->get('category_id');
      $subcategories = $this->subcategoryModel->getSubcategoriesByCategory($categoryId);

      return Response::json([
        'status' => 'success',
        'data' => $subcategories
      ]);
    } catch (Exception $e) {
      error_log("Error en getSubcategoriesByCategory: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener subcategorías'
      ], 500);
    }
  }

  /**
   * API: Obtiene estadísticas generales de configuración
   */
  public function getConfigStats()
  {
    try {
      $stats = [
        'levels' => count($this->levelModel->getAllLevels()),
        'rules' => count($this->ruleModel->getAllRules()),
        'criteria' => count($this->criteriaModel->getAllCriteria()),
        'categories' => count($this->categoryModel->getAllCategories()),
        'subcategories' => count($this->subcategoryModel->getAllSubcategories())
      ];

      return Response::json([
        'status' => 'success',
        'data' => $stats
      ]);
    } catch (Exception $e) {
      error_log("Error en getConfigStats: " . $e->getMessage());
      return Response::json([
        'status' => 'error',
        'message' => 'Error al obtener estadísticas'
      ], 500);
    }
  }
}
