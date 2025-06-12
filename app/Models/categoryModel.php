<?php

namespace App\Models;

use PDO;
use App\Models\MainModel;

/**
 * Modelo para consultar las categorías de criterios (Solo lectura)
 * 
 * Las categorías son elementos estructurales del sistema y no deben
 * ser modificadas por los usuarios. Solo se proporcionan métodos de consulta.
 */
class CategoryModel extends MainModel
{
  /**
   * Obtiene todas las categorías activas
   * 
   * @return array Lista de categorías
   */
  public function getAllCategories()
  {
    try {
      $query = "SELECT * FROM categoria_criterio WHERE estado = 1 ORDER BY nombre ASC";
      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllCategories: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene una categoría por su ID
   * 
   * @param int $categoryId ID de la categoría
   * @return object|false Datos de la categoría o false si no existe
   */
  public function getCategoryById($categoryId)
  {
    try {
      $query = "SELECT * FROM categoria_criterio WHERE id = :category_id AND estado = 1";
      $resultado = $this->ejecutarConsulta($query, [':category_id' => $categoryId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getCategoryById: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene categorías con sus subcategorías y conteo de criterios
   * 
   * @return array Lista de categorías con información completa
   */
  public function getCategoriesWithDetails()
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
                      ORDER BY c.nombre ASC, s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getCategoriesWithDetails: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene el conteo total de criterios por categoría
   * 
   * @param int $categoryId ID de la categoría
   * @return int Número total de criterios en la categoría
   */
  public function getCriteriaCountByCategory($categoryId)
  {
    try {
      $query = "SELECT COUNT(cr.id) 
                      FROM categoria_criterio c 
                      JOIN subcategoria_criterio s ON c.id = s.categoria_id 
                      JOIN criterio_puntuacion cr ON s.id = cr.subcategoria_id 
                      WHERE c.id = :category_id AND c.estado = 1 AND s.estado = 1 AND cr.estado = 1";

      $resultado = $this->ejecutarConsulta($query, [':category_id' => $categoryId]);
      return (int)$resultado->fetchColumn();
    } catch (\Exception $e) {
      error_log("Error en getCriteriaCountByCategory: " . $e->getMessage());
      return 0;
    }
  }
}
