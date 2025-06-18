<?php

namespace App\Models;

use PDO;
use App\Models\MainModel;

/**
 * Modelo para gestionar los criterios de puntuación (CRUD completo)
 * 
 * Maneja las operaciones CRUD para la tabla criterio_puntuacion
 * con validaciones específicas según el tipo de criterio
 */
class CriteriaModel extends MainModel
{
  // Tipos de criterio válidos
  const TIPO_RANGO_NUMERICO = 'rango_numerico';
  const TIPO_VALOR_ESPECIFICO = 'valor_especifico';
  const TIPO_BOOLEANO = 'booleano';

  /**
   * Obtiene criterios filtrados por subcategoría con campos para DataTables
   * 
   * @param int|null $subcategoryId Filtrar por subcategoría específica
   * @return array Lista de criterios
   */
  public function getAllCriteria($subcategoryId = null)
  {
    try {
      $query = "SELECT cr.id,
                     cr.nombre as criterio,
                     cr.puntaje,
                     cr.estado,
                     cr.tipo_criterio,
                     cr.valor_minimo,
                     cr.valor_maximo,
                     cr.valor_texto,
                     cr.subcategoria_id,
                     s.nombre as subcategoria_nombre,
                     c.nombre as categoria_nombre
              FROM criterio_puntuacion cr
              JOIN subcategoria_criterio s ON cr.subcategoria_id = s.id
              JOIN categoria_criterio c ON s.categoria_id = c.id
              WHERE 1=1";

      $params = [];

      // Filtrar por subcategoría si se especifica
      if ($subcategoryId) {
        $query .= " AND cr.subcategoria_id = :subcategory_id";
        $params[':subcategory_id'] = $subcategoryId;
      }

      $query .= " ORDER BY cr.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllCriteria: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene todas las subcategorías para formularios
   * 
   * @return array Lista de subcategorías con información de categoría
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

  /**
   * Obtiene un criterio por su ID
   * 
   * @param int $criteriaId ID del criterio
   * @return object|false Datos del criterio o false si no existe
   */
  public function getCriteriaById($criteriaId)
  {
    try {
      $query = "SELECT cr.id,
                       cr.nombre as criterio,
                       cr.tipo_criterio,
                       cr.valor_minimo,
                       cr.valor_maximo,
                       cr.valor_texto,
                       cr.puntaje,
                       cr.estado,
                       cr.fecha_creacion,
                       cr.fecha_modificacion,
                       cr.subcategoria_id,
                       s.nombre as subcategoria_nombre,
                       c.id as categoria_id,
                       c.nombre as categoria_nombre
                FROM criterio_puntuacion cr
                JOIN subcategoria_criterio s ON cr.subcategoria_id = s.id
                JOIN categoria_criterio c ON s.categoria_id = c.id
                WHERE cr.id = :criteria_id";

      $resultado = $this->ejecutarConsulta($query, [':criteria_id' => $criteriaId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getCriteriaById: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Crea un nuevo criterio
   * 
   * @param array $data Datos del criterio
   * @return int|false ID del criterio creado o false si hubo error
   */
  public function createCriteria($data)
  {
    try {
      // Validar datos según el tipo de criterio
      $validationResult = $this->validateCriteriaData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación en createCriteria: " . implode(', ', $validationResult['errors']));
        return false;
      }

      $datosParaInsertar = [
        'subcategoria_id' => $data['subcategoria_id'],
        'nombre' => $data['nombre'],
        'tipo_criterio' => $data['tipo_criterio'],
        'puntaje' => $data['puntaje'],
        'estado' => 1,
        'fecha_creacion' => date("Y-m-d H:i:s"),
        'fecha_modificacion' => date("Y-m-d H:i:s"),
        'usuario_creacion_id' => $data['usuario_creacion_id'],
        'usuario_modificacion_id' => $data['usuario_creacion_id']
      ];

      // Agregar campos específicos según el tipo
      switch ($data['tipo_criterio']) {
        case self::TIPO_RANGO_NUMERICO:
          $datosParaInsertar['valor_minimo'] = $data['valor_minimo'];
          $datosParaInsertar['valor_maximo'] = isset($data['valor_maximo']) ? $data['valor_maximo'] : null;
          $datosParaInsertar['valor_texto'] = null;
          $datosParaInsertar['valor_booleano'] = null;
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $datosParaInsertar['valor_texto'] = $data['valor_texto'];
          $datosParaInsertar['valor_minimo'] = null;
          $datosParaInsertar['valor_maximo'] = null;
          $datosParaInsertar['valor_booleano'] = null;
          break;

        case self::TIPO_BOOLEANO:
          break;
      }

      $this->insertarDatos("criterio_puntuacion", $datosParaInsertar);
      return $this->getLastInsertId();
    } catch (\Exception $e) {
      error_log("Error en createCriteria: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualiza un criterio existente
   * 
   * @param int $criteriaId ID del criterio
   * @param array $data Datos a actualizar
   * @return bool True si se actualizó correctamente
   */
  public function updateCriteria($criteriaId, $data)
  {
    try {
      // Validar datos según el tipo de criterio
      $validationResult = $this->validateCriteriaData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación en updateCriteria: " . implode(', ', $validationResult['errors']));
        return false;
      }

      $datosActualizar = [
        [
          "campo_nombre" => "nombre",
          "campo_marcador" => ":nombre",
          "campo_valor" => $data['nombre']
        ],
        [
          "campo_nombre" => "tipo_criterio",
          "campo_marcador" => ":tipo_criterio",
          "campo_valor" => $data['tipo_criterio']
        ],
        [
          "campo_nombre" => "puntaje",
          "campo_marcador" => ":puntaje",
          "campo_valor" => $data['puntaje']
        ],
        [
          "campo_nombre" => "fecha_modificacion",
          "campo_marcador" => ":fecha_modificacion",
          "campo_valor" => date("Y-m-d H:i:s")
        ],
        [
          "campo_nombre" => "usuario_modificacion_id",
          "campo_marcador" => ":usuario_modificacion_id",
          "campo_valor" => $data['usuario_modificacion_id']
        ]
      ];

      // Agregar campos específicos según el tipo - resetear todos primero
      switch ($data['tipo_criterio']) {
        case self::TIPO_RANGO_NUMERICO:
          $datosActualizar[] = [
            "campo_nombre" => "valor_minimo",
            "campo_marcador" => ":valor_minimo",
            "campo_valor" => $data['valor_minimo']
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_maximo",
            "campo_marcador" => ":valor_maximo",
            "campo_valor" => isset($data['valor_maximo']) ? $data['valor_maximo'] : null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_texto",
            "campo_marcador" => ":valor_texto",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_booleano",
            "campo_marcador" => ":valor_booleano",
            "campo_valor" => null
          ];
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $datosActualizar[] = [
            "campo_nombre" => "valor_texto",
            "campo_marcador" => ":valor_texto",
            "campo_valor" => $data['valor_texto']
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_minimo",
            "campo_marcador" => ":valor_minimo",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_maximo",
            "campo_marcador" => ":valor_maximo",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_booleano",
            "campo_marcador" => ":valor_booleano",
            "campo_valor" => null
          ];
          break;

        case self::TIPO_BOOLEANO:
          // Para booleano resetear todos los campos de valor a null
          $datosActualizar[] = [
            "campo_nombre" => "valor_booleano",
            "campo_marcador" => ":valor_booleano",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_minimo",
            "campo_marcador" => ":valor_minimo",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_maximo",
            "campo_marcador" => ":valor_maximo",
            "campo_valor" => null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_texto",
            "campo_marcador" => ":valor_texto",
            "campo_valor" => null
          ];
          break;
      }

      $condicion = [
        "condicion_campo" => "id",
        "condicion_marcador" => ":id",
        "condicion_valor" => $criteriaId
      ];

      $this->actualizarDatos("criterio_puntuacion", $datosActualizar, $condicion);
      return true;
    } catch (\Exception $e) {
      error_log("Error en updateCriteria: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de un criterio
   * 
   * @param int $criteriaId ID del criterio
   * @param int $estado Nuevo estado (0 o 1)
   * @param int $userId ID del usuario que hace el cambio
   * @return bool True si se actualizó correctamente
   */
  public function toggleCriteriaStatus($criteriaId, $estado, $userId)
  {
    try {
      $datosActualizar = [
        [
          "campo_nombre" => "estado",
          "campo_marcador" => ":estado",
          "campo_valor" => $estado
        ],
        [
          "campo_nombre" => "fecha_modificacion",
          "campo_marcador" => ":fecha_modificacion",
          "campo_valor" => date("Y-m-d H:i:s")
        ],
        [
          "campo_nombre" => "usuario_modificacion_id",
          "campo_marcador" => ":usuario_modificacion_id",
          "campo_valor" => $userId
        ]
      ];

      $condicion = [
        "condicion_campo" => "id",
        "condicion_marcador" => ":id",
        "condicion_valor" => $criteriaId
      ];

      $this->actualizarDatos("criterio_puntuacion", $datosActualizar, $condicion);
      return true;
    } catch (\Exception $e) {
      error_log("Error en toggleCriteriaStatus: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Elimina un criterio permanentemente (hard delete)
   * 
   * @param int $criteriaId ID del criterio
   * @return bool True si se eliminó correctamente
   */
  public function deleteCriteria($criteriaId)
  {
    try {
      // Verificar que el criterio existe antes de eliminar
      $criterio = $this->getCriteriaById($criteriaId);
      if (!$criterio) {
        error_log("Intento de eliminar criterio inexistente: ID $criteriaId");
        return false;
      }

      // Realizar hard delete
      $query = "DELETE FROM criterio_puntuacion WHERE id = :criteria_id";
      $resultado = $this->ejecutarConsulta($query, [':criteria_id' => $criteriaId]);

      if ($resultado->rowCount() > 0) {
        error_log("Criterio eliminado permanentemente: ID $criteriaId - {$criterio->criterio}");
        return true;
      }

      return false;
    } catch (\Exception $e) {
      error_log("Error en deleteCriteria: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Valida los datos de un criterio según su tipo
   * 
   * @param array $data Datos a validar
   * @return array Resultado de validación con 'valid' y 'errors'
   */
  private function validateCriteriaData($data)
  {
    $result = ['valid' => false, 'errors' => []];

    // Validaciones básicas
    if (empty($data['nombre'])) {
      $result['errors'][] = "Nombre del criterio requerido";
    }

    if (empty($data['tipo_criterio'])) {
      $result['errors'][] = "Tipo de criterio requerido";
    }

    if (!isset($data['puntaje']) || !is_numeric($data['puntaje'])) {
      $result['errors'][] = "Puntaje requerido y debe ser numérico";
    }

    if (!isset($data['subcategoria_id']) || !is_numeric($data['subcategoria_id'])) {
      $result['errors'][] = "Subcategoría requerida";
    }

    // Validaciones específicas por tipo
    if (!empty($data['tipo_criterio'])) {
      switch ($data['tipo_criterio']) {
        case self::TIPO_RANGO_NUMERICO:
          if (!isset($data['valor_minimo']) || !is_numeric($data['valor_minimo'])) {
            $result['errors'][] = "Valor mínimo requerido para rango numérico";
          }
          if (
            isset($data['valor_minimo']) && isset($data['valor_maximo']) &&
            is_numeric($data['valor_minimo']) && is_numeric($data['valor_maximo']) &&
            $data['valor_minimo'] >= $data['valor_maximo']
          ) {
            $result['errors'][] = "Valor mínimo debe ser menor que valor máximo";
          }
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          if (empty($data['valor_texto'])) {
            $result['errors'][] = "Valor de texto requerido para valor específico";
          }
          break;

        case self::TIPO_BOOLEANO:
          if (!isset($data['valor_booleano']) || !in_array($data['valor_booleano'], [0, 1, '0', '1', true, false])) {
            $result['errors'][] = "Valor booleano requerido (0 o 1)";
          }
          break;
      }
    }

    $result['valid'] = empty($result['errors']);
    return $result;
  }
}
