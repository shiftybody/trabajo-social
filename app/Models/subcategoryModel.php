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
   * Obtiene subcategorías filtradas por categoría
   * 
   * @param int $categoryId ID de la categoría
   * @return array Lista de subcategorías
   */
  public function getSubcategoriesByCategory($categoryId)
  {
    try {
      $query = "SELECT s.id,
                       s.nombre,
                       s.descripcion,
                       s.categoria_id,
                       c.nombre as categoria_nombre
                FROM subcategoria_criterio s
                JOIN categoria_criterio c ON s.categoria_id = c.id
                WHERE s.categoria_id = :category_id 
                AND s.estado = 1 
                AND c.estado = 1
                ORDER BY s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, [':category_id' => $categoryId]);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getSubcategoriesByCategory: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene todas las subcategorías activas con información de categoría
   * 
   * @return array Lista de subcategorías
   */
  public function getAllSubcategories()
  {
    try {
      $query = "SELECT s.id,
                       s.nombre,
                       s.descripcion,
                       s.categoria_id,
                       c.nombre as categoria_nombre
                FROM subcategoria_criterio s
                JOIN categoria_criterio c ON s.categoria_id = c.id
                WHERE s.estado = 1 
                AND c.estado = 1
                ORDER BY c.nombre ASC, s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllSubcategories: " . $e->getMessage());
      return [];
    }
  }
}
