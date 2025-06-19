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
   * Actualizar método createCriteria para incluir validaciones
   */
  public function createCriteria($data)
  {
    try {
      // Validaciones básicas
      $validationResult = $this->validateCriteriaData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación básica en createCriteria: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Validaciones de conflictos
      $conflictValidation = $this->validateCriteriaConflicts($data);
      if (!$conflictValidation['valid']) {
        error_log("Error de conflictos en createCriteria: " . implode(', ', $conflictValidation['errors']));
        return ['error' => 'validation', 'errors' => $conflictValidation['errors']];
      }

      // Resto del código de creación...
      $datosParaInsertar = [
        'subcategoria_id' => $data['subcategoria_id'],
        'nombre' => trim($data['nombre']), // Aplicar trim
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
          $datosParaInsertar['valor_maximo'] = isset($data['valor_maximo']) && $data['valor_maximo'] !== '' ? $data['valor_maximo'] : null;
          $datosParaInsertar['valor_texto'] = null;
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $datosParaInsertar['valor_texto'] = trim($data['valor_texto']);
          $datosParaInsertar['valor_minimo'] = null;
          $datosParaInsertar['valor_maximo'] = null;
          break;

        case self::TIPO_BOOLEANO:
          $datosParaInsertar['valor_minimo'] = null;
          $datosParaInsertar['valor_maximo'] = null;
          $datosParaInsertar['valor_texto'] = null;
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
   * Actualizar método updateCriteria para incluir validaciones
   */
  public function updateCriteria($criteriaId, $data)
  {
    try {
      // Validaciones básicas
      $validationResult = $this->validateCriteriaData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación básica en updateCriteria: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Validaciones de conflictos (excluyendo el criterio actual)
      $conflictValidation = $this->validateCriteriaConflicts($data, $criteriaId);
      if (!$conflictValidation['valid']) {
        error_log("Error de conflictos en updateCriteria: " . implode(', ', $conflictValidation['errors']));
        return ['error' => 'validation', 'errors' => $conflictValidation['errors']];
      }

      // Resto del código de actualización...
      $datosActualizar = [
        [
          "campo_nombre" => "nombre",
          "campo_marcador" => ":nombre",
          "campo_valor" => trim($data['nombre']) // Aplicar trim
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

      // Agregar campos específicos según el tipo
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
            "campo_valor" => isset($data['valor_maximo']) && $data['valor_maximo'] !== '' ? $data['valor_maximo'] : null
          ];
          $datosActualizar[] = [
            "campo_nombre" => "valor_texto",
            "campo_marcador" => ":valor_texto",
            "campo_valor" => null
          ];
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $datosActualizar[] = [
            "campo_nombre" => "valor_texto",
            "campo_marcador" => ":valor_texto",
            "campo_valor" => trim($data['valor_texto'])
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
          break;

        case self::TIPO_BOOLEANO:
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
      }
    }

    $result['valid'] = empty($result['errors']);
    return $result;
  }

  /**
   * Valida que no existan conflictos con criterios existentes
   * 
   * @param array $data Datos del criterio a validar
   * @param int|null $excludeCriteriaId ID del criterio a excluir (para edición)
   * @return array Resultado de validación con 'valid' y 'errors'
   */
  public function validateCriteriaConflicts($data, $excludeCriteriaId = null)
  {
    $result = ['valid' => true, 'errors' => []];

    try {
      // 1. Validar nombre único (con trim)
      $result = $this->validateUniqueName($data, $excludeCriteriaId, $result);

      // 2. Validar consistencia de tipo de criterio
      $result = $this->validateCriteriaTypeConsistency($data, $excludeCriteriaId, $result);

      // 3. Validaciones específicas por tipo
      switch ($data['tipo_criterio']) {
        case self::TIPO_RANGO_NUMERICO:
          $result = $this->validateNumericRangeConflicts($data, $excludeCriteriaId, $result);
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $result = $this->validateSpecificValueConflicts($data, $excludeCriteriaId, $result);
          break;

        case self::TIPO_BOOLEANO:
          // Los booleanos no tienen conflictos de valor
          break;
      }
    } catch (\Exception $e) {
      error_log("Error en validateCriteriaConflicts: " . $e->getMessage());
      $result['valid'] = false;
      $result['errors'][] = 'Error interno al validar criterios';
    }

    return $result;
  }

  /**
   * Valida que el nombre del criterio sea único en la subcategoría
   */
  private function validateUniqueName($data, $excludeCriteriaId, $result)
  {
    $nombreTrimmed = trim($data['nombre']);

    $query = "SELECT id FROM criterio_puntuacion 
              WHERE subcategoria_id = :subcategoria_id 
              AND TRIM(nombre) = :nombre 
              AND estado = 1";

    $params = [
      ':subcategoria_id' => $data['subcategoria_id'],
      ':nombre' => $nombreTrimmed
    ];

    // Excluir el criterio actual en caso de edición
    if ($excludeCriteriaId) {
      $query .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeCriteriaId;
    }

    $resultado = $this->ejecutarConsulta($query, $params);
    $existingCriteria = $resultado->fetch(PDO::FETCH_OBJ);

    if ($existingCriteria) {
      $result['valid'] = false;
      $result['errors']['nombre'] = 'Ya existe un criterio con este nombre en la subcategoría';
    }

    return $result;
  }

  /**
   * Valida que el tipo de criterio sea consistente con los existentes
   */
  private function validateCriteriaTypeConsistency($data, $excludeCriteriaId, $result)
  {
    $query = "SELECT DISTINCT tipo_criterio FROM criterio_puntuacion 
              WHERE subcategoria_id = :subcategoria_id 
              AND estado = 1";

    $params = [':subcategoria_id' => $data['subcategoria_id']];

    // Excluir el criterio actual en caso de edición
    if ($excludeCriteriaId) {
      $query .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeCriteriaId;
    }

    $resultado = $this->ejecutarConsulta($query, $params);
    $existingTypes = $resultado->fetchAll(PDO::FETCH_COLUMN);

    // Si hay criterios existentes y tienen tipo diferente
    if (!empty($existingTypes) && !in_array($data['tipo_criterio'], $existingTypes)) {
      $existingTypeNames = [
        'rango_numerico' => 'Rango Numérico',
        'valor_especifico' => 'Valor Específico',
        'booleano' => 'Booleano'
      ];

      $existingTypeName = $existingTypeNames[$existingTypes[0]] ?: $existingTypes[0];

      $result['valid'] = false;
      $result['errors']['tipo_criterio'] = "Los criterios de esta subcategoría deben ser de tipo: {$existingTypeName}";
    }

    return $result;
  }

  /**
   * Valida conflictos en rangos numéricos
   */
  private function validateNumericRangeConflicts($data, $excludeCriteriaId, $result)
  {
    $valorMinimo = (int)$data['valor_minimo'];
    $valorMaximo = isset($data['valor_maximo']) && $data['valor_maximo'] !== ''
      ? (int)$data['valor_maximo']
      : null;

    $query = "SELECT id, nombre, valor_minimo, valor_maximo 
              FROM criterio_puntuacion 
              WHERE subcategoria_id = :subcategoria_id 
              AND tipo_criterio = 'rango_numerico' 
              AND estado = 1";

    $params = [':subcategoria_id' => $data['subcategoria_id']];

    if ($excludeCriteriaId) {
      $query .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeCriteriaId;
    }

    $resultado = $this->ejecutarConsulta($query, $params);
    $existingRanges = $resultado->fetchAll(PDO::FETCH_OBJ);

    foreach ($existingRanges as $range) {
      $existingMin = (int)$range->valor_minimo;
      $existingMax = $range->valor_maximo !== null ? (int)$range->valor_maximo : null;

      // Verificar si hay solapamiento
      if ($this->rangesOverlap($valorMinimo, $valorMaximo, $existingMin, $existingMax)) {
        $result['valid'] = false;

        $rangeText = $existingMax !== null
          ? "{$existingMin} - {$existingMax}"
          : "{$existingMin} o más";

        $result['errors']['valor_minimo'] = "El rango se superpone con el criterio '{$range->nombre}' (rango: {$rangeText})";
        break;
      }
    }

    return $result;
  }

  /**
   * Determina si dos rangos se superponen
   */
  private function rangesOverlap($min1, $max1, $min2, $max2)
  {
    // Caso 1: Rango 1 tiene máximo, Rango 2 tiene máximo
    if ($max1 !== null && $max2 !== null) {
      return !($max1 < $min2 || $max2 < $min1);
    }

    // Caso 2: Rango 1 sin máximo, Rango 2 con máximo
    if ($max1 === null && $max2 !== null) {
      return $min1 <= $max2;
    }

    // Caso 3: Rango 1 con máximo, Rango 2 sin máximo
    if ($max1 !== null && $max2 === null) {
      return $max1 >= $min2;
    }

    // Caso 4: Ambos sin máximo (siempre se superponen)
    return true;
  }

  /**
   * Valida conflictos en valores específicos
   */
  private function validateSpecificValueConflicts($data, $excludeCriteriaId, $result)
  {
    $valorTexto = trim($data['valor_texto']);

    $query = "SELECT id, nombre FROM criterio_puntuacion 
              WHERE subcategoria_id = :subcategoria_id 
              AND tipo_criterio = 'valor_especifico' 
              AND TRIM(valor_texto) = :valor_texto 
              AND estado = 1";

    $params = [
      ':subcategoria_id' => $data['subcategoria_id'],
      ':valor_texto' => $valorTexto
    ];

    if ($excludeCriteriaId) {
      $query .= " AND id != :exclude_id";
      $params[':exclude_id'] = $excludeCriteriaId;
    }

    $resultado = $this->ejecutarConsulta($query, $params);
    $existingCriteria = $resultado->fetch(PDO::FETCH_OBJ);

    if ($existingCriteria) {
      $result['valid'] = false;
      $result['errors']['valor_texto'] = "Ya existe el criterio '{$existingCriteria->nombre}' con este valor en la subcategoría";
    }

    return $result;
  }
}
