<?php

namespace App\Models;

use PDO;
use App\Models\MainModel;

/**
 * Modelo para consultar las subcategorías de criterios (Solo lectura)
 * 
 * Las subcategorías son elementos estructurales del sistema y no deben
 * ser modificadas por los usuarios. Solo se proporcionan métodos de consulta.
 */
class SubcategoryModel extends MainModel
{
  /**
   * Obtiene todas las subcategorías activas
   * 
   * @param int|null $categoryId Filtrar por categoría específica
   * @return array Lista de subcategorías
   */
  public function getAllSubcategories($categoryId = null)
  {
    try {
      $query = "SELECT s.*, c.nombre as categoria_nombre 
                    FROM subcategoria_criterio s
                    JOIN categoria_criterio c ON s.categoria_id = c.id
                    WHERE s.estado = 1 AND c.estado = 1";
      $params = [];
      if ($categoryId) {
        $query .= " AND s.categoria_id = :category_id";
        $params[':category_id'] = $categoryId;
      }

      $query .= " ORDER BY c.nombre, s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllSubcategories: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene una subcategoría por su ID
   * 
   * @param int $subcategoryId ID de la subcategoría
   * @return object|false Datos de la subcategoría o false si no existe
   */
  public function getSubcategoryById($subcategoryId)
  {
    try {
      $query = "SELECT s.*, c.nombre as categoria_nombre 
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      WHERE s.id = :subcategory_id AND s.estado = 1 AND c.estado = 1";

      $resultado = $this->ejecutarConsulta($query, [':subcategory_id' => $subcategoryId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getSubcategoryById: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene subcategorías por categoría
   * 
   * @param int $categoryId ID de la categoría
   * @return array Lista de subcategorías de la categoría
   */
  public function getSubcategoriesByCategory($categoryId)
  {
    try {
      $query = "SELECT s.*, c.nombre as categoria_nombre 
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      WHERE s.categoria_id = :category_id AND s.estado = 1 AND c.estado = 1
                      ORDER BY s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, [':category_id' => $categoryId]);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getSubcategoriesByCategory: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene subcategorías con conteo de criterios
   * 
   * @param int|null $categoryId Filtrar por categoría específica
   * @return array Lista de subcategorías con información de criterios
   */
  public function getSubcategoriesWithCriteriaCount($categoryId = null)
  {
    try {
      $query = "SELECT s.id as subcategory_id,
                             s.nombre as subcategory_name,
                             s.descripcion as subcategory_description,
                             c.id as category_id,
                             c.nombre as category_name,
                             COUNT(cr.id) as criteria_count,
                             COUNT(CASE WHEN cr.estado = 1 THEN 1 END) as active_criteria_count
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      LEFT JOIN criterio_puntuacion cr ON s.id = cr.subcategoria_id
                      WHERE s.estado = 1 AND c.estado = 1";

      $params = [];
      if ($categoryId) {
        $query .= " AND s.categoria_id = :category_id";
        $params[':category_id'] = $categoryId;
      }

      $query .= " GROUP BY s.id, c.id ORDER BY c.nombre, s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getSubcategoriesWithCriteriaCount: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene el conteo de criterios por subcategoría
   * 
   * @param int $subcategoryId ID de la subcategoría
   * @param bool $activeOnly Solo criterios activos
   * @return int Número de criterios en la subcategoría
   */
  public function getCriteriaCountBySubcategory($subcategoryId, $activeOnly = true)
  {
    try {
      $query = "SELECT COUNT(*) FROM criterio_puntuacion 
                      WHERE subcategoria_id = :subcategory_id";

      if ($activeOnly) {
        $query .= " AND estado = 1";
      }

      $resultado = $this->ejecutarConsulta($query, [':subcategory_id' => $subcategoryId]);
      return (int)$resultado->fetchColumn();
    } catch (\Exception $e) {
      error_log("Error en getCriteriaCountBySubcategory: " . $e->getMessage());
      return 0;
    }
  }

  /**
   * Obtiene subcategorías agrupadas por categoría
   * 
   * @return array Subcategorías agrupadas por categoría
   */
  public function getSubcategoriesGroupedByCategory()
  {
    try {
      $query = "SELECT c.id as category_id,
                             c.nombre as category_name,
                             c.descripcion as category_description,
                             s.id as subcategory_id,
                             s.nombre as subcategory_name,
                             s.descripcion as subcategory_description,
                             COUNT(cr.id) as criteria_count
                      FROM categoria_criterio c
                      LEFT JOIN subcategoria_criterio s ON c.id = s.categoria_id AND s.estado = 1
                      LEFT JOIN criterio_puntuacion cr ON s.id = cr.subcategoria_id AND cr.estado = 1
                      WHERE c.estado = 1
                      GROUP BY c.id, s.id
                      ORDER BY c.nombre, s.nombre";

      $resultado = $this->ejecutarConsulta($query);
      $subcategories = $resultado->fetchAll(PDO::FETCH_OBJ);

      // Agrupar resultados por categoría
      $grouped = [];
      foreach ($subcategories as $subcategory) {
        $catId = $subcategory->category_id;

        if (!isset($grouped[$catId])) {
          $grouped[$catId] = [
            'category' => [
              'id' => $subcategory->category_id,
              'name' => $subcategory->category_name,
              'description' => $subcategory->category_description
            ],
            'subcategories' => []
          ];
        }

        // Solo agregar si la subcategoría existe (LEFT JOIN puede dar nulls)
        if ($subcategory->subcategory_id) {
          $grouped[$catId]['subcategories'][] = [
            'id' => $subcategory->subcategory_id,
            'name' => $subcategory->subcategory_name,
            'description' => $subcategory->subcategory_description,
            'criteria_count' => $subcategory->criteria_count
          ];
        }
      }

      return $grouped;
    } catch (\Exception $e) {
      error_log("Error en getSubcategoriesGroupedByCategory: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Verifica si una subcategoría existe y está activa
   * 
   * @param int $subcategoryId ID de la subcategoría
   * @return bool True si existe y está activa, false en caso contrario
   */
  public function subcategoryExists($subcategoryId)
  {
    try {
      $query = "SELECT COUNT(*) FROM subcategoria_criterio 
                      WHERE id = :subcategory_id AND estado = 1";

      $resultado = $this->ejecutarConsulta($query, [':subcategory_id' => $subcategoryId]);
      return $resultado->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en subcategoryExists: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene información completa de una subcategoría con su categoría padre
   * 
   * @param int $subcategoryId ID de la subcategoría
   * @return object|false Información completa o false si no existe
   */
  public function getSubcategoryWithCategory($subcategoryId)
  {
    try {
      $query = "SELECT s.id as subcategory_id,
                             s.nombre as subcategory_name,
                             s.descripcion as subcategory_description,
                             s.fecha_creacion as subcategory_created_at,
                             s.fecha_modificacion as subcategory_updated_at,
                             c.id as category_id,
                             c.nombre as category_name,
                             c.descripcion as category_description,
                             COUNT(cr.id) as total_criteria,
                             COUNT(CASE WHEN cr.estado = 1 THEN 1 END) as active_criteria
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      LEFT JOIN criterio_puntuacion cr ON s.id = cr.subcategoria_id
                      WHERE s.id = :subcategory_id AND s.estado = 1 AND c.estado = 1
                      GROUP BY s.id, c.id";

      $resultado = $this->ejecutarConsulta($query, [':subcategory_id' => $subcategoryId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getSubcategoryWithCategory: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene las opciones de subcategorías para un select/dropdown
   * 
   * @param int|null $categoryId Filtrar por categoría específica
   * @param bool $includeEmpty Incluir opción vacía al inicio
   * @return array Array con formato para select (id => nombre)
   */
  public function getSubcategoryOptions($categoryId = null, $includeEmpty = true)
  {
    try {
      $options = [];

      if ($includeEmpty) {
        $options[0] = 'Seleccione una subcategoría...';
      }

      $query = "SELECT s.id, s.nombre, c.nombre as categoria_nombre
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      WHERE s.estado = 1 AND c.estado = 1";

      $params = [];
      if ($categoryId) {
        $query .= " AND s.categoria_id = :category_id";
        $params[':category_id'] = $categoryId;
      }

      $query .= " ORDER BY c.nombre, s.nombre";

      $resultado = $this->ejecutarConsulta($query, $params);
      $subcategories = $resultado->fetchAll(PDO::FETCH_OBJ);

      foreach ($subcategories as $subcategory) {
        $label = $categoryId ? $subcategory->nombre :
          "{$subcategory->categoria_nombre} - {$subcategory->nombre}";
        $options[$subcategory->id] = $label;
      }

      return $options;
    } catch (\Exception $e) {
      error_log("Error en getSubcategoryOptions: " . $e->getMessage());
      return $includeEmpty ? [0 => 'Error al cargar opciones'] : [];
    }
  }

  /**
   * Busca subcategorías por texto en nombre o descripción
   * 
   * @param string $searchTerm Término de búsqueda
   * @param int|null $categoryId Filtrar por categoría específica
   * @return array Lista de subcategorías que coinciden con la búsqueda
   */
  public function searchSubcategories($searchTerm, $categoryId = null)
  {
    try {
      $query = "SELECT s.*, c.nombre as categoria_nombre 
                      FROM subcategoria_criterio s
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      WHERE s.estado = 1 AND c.estado = 1
                      AND (s.nombre LIKE :search_term OR s.descripcion LIKE :search_term)";

      $params = [':search_term' => "%{$searchTerm}%"];

      if ($categoryId) {
        $query .= " AND s.categoria_id = :category_id";
        $params[':category_id'] = $categoryId;
      }

      $query .= " ORDER BY c.nombre, s.nombre";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en searchSubcategories: " . $e->getMessage());
      return [];
    }
  }
}
