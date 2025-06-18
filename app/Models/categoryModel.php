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
   * Obtiene todas las categorías activas ordenadas por nombre
   * 
   * @return array Lista de categorías
   */
  public function getAllCategories()
  {
    try {
      $query = "SELECT id, nombre, descripcion, estado
                FROM categoria_criterio 
                WHERE estado = 1 
                ORDER BY nombre ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllCategories: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene categorías con sus subcategorías para navegación jerárquica
   * 
   * @return array Lista de categorías con subcategorías
   */
  public function getCategoriesWithSubcategories()
  {
    try {
      $query = "SELECT c.id as categoria_id,
                       c.nombre as categoria_nombre,
                       c.descripcion as categoria_descripcion,
                       s.id as subcategoria_id,
                       s.nombre as subcategoria_nombre,
                       s.descripcion as subcategoria_descripcion
                FROM categoria_criterio c
                LEFT JOIN subcategoria_criterio s ON c.id = s.categoria_id 
                WHERE c.estado = 1 AND (s.estado = 1 OR s.estado IS NULL)
                ORDER BY c.nombre ASC, s.nombre ASC";

      $resultado = $this->ejecutarConsulta($query);
      $rows = $resultado->fetchAll(PDO::FETCH_OBJ);

      // Agrupar subcategorías por categoría
      $categories = [];
      foreach ($rows as $row) {
        $categoryId = $row->categoria_id;

        // Crear categoría si no existe
        if (!isset($categories[$categoryId])) {
          $categories[$categoryId] = [
            'id' => $row->categoria_id,
            'nombre' => $row->categoria_nombre,
            'descripcion' => $row->categoria_descripcion,
            'subcategorias' => []
          ];
        }

        // Agregar subcategoría si existe
        if ($row->subcategoria_id) {
          $categories[$categoryId]['subcategorias'][] = [
            'id' => $row->subcategoria_id,
            'nombre' => $row->subcategoria_nombre,
            'descripcion' => $row->subcategoria_descripcion
          ];
        }
      }

      return array_values($categories);
    } catch (\Exception $e) {
      error_log("Error en getCategoriesWithSubcategories: " . $e->getMessage());
      return [];
    }
  }
}
