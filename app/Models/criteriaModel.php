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
   * Obtiene todos los criterios activos
   * 
   * @param int|null $subcategoryId Filtrar por subcategoría específica
   * @return array Lista de criterios
   */
  public function getAllCriteria($subcategoryId = null)
  {
    try {
      $query = "SELECT cr.*, 
                             s.nombre as subcategoria_nombre,
                             c.nombre as categoria_nombre
                      FROM criterio_puntuacion cr
                      JOIN subcategoria_criterio s ON cr.subcategoria_id = s.id
                      JOIN categoria_criterio c ON s.categoria_id = c.id
                      WHERE cr.estado = 1";

      $params = [];
      if ($subcategoryId) {
        $query .= " AND cr.subcategoria_id = :subcategory_id";
        $params[':subcategory_id'] = $subcategoryId;
      }

      $query .= " ORDER BY c.nombre, s.nombre, cr.nombre ASC";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllCriteria: " . $e->getMessage());
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
      $query = "SELECT cr.*, 
                             s.nombre as subcategoria_nombre,
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
   * Obtiene criterios por subcategoría
   * 
   * @param int $subcategoryId ID de la subcategoría
   * @return array Lista de criterios de la subcategoría
   */
  public function getCriteriaBySubcategory($subcategoryId)
  {
    try {
      $query = "SELECT * FROM criterio_puntuacion 
                      WHERE subcategoria_id = :subcategory_id AND estado = 1 
                      ORDER BY nombre ASC";

      $resultado = $this->ejecutarConsulta($query, [':subcategory_id' => $subcategoryId]);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getCriteriaBySubcategory: " . $e->getMessage());
      return [];
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
          $datosParaInsertar['valor_maximo'] = $data['valor_maximo'] ?: null;
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
          $datosParaInsertar['valor_booleano'] = $data['valor_booleano'];
          $datosParaInsertar['valor_minimo'] = null;
          $datosParaInsertar['valor_maximo'] = null;
          $datosParaInsertar['valor_texto'] = null;
          break;
      }

      $resultado = $this->insertarDatos("criterio_puntuacion", $datosParaInsertar);

      if ($resultado->rowCount() > 0) {
        return $this->getLastInsertId();
      }

      return false;
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
   * @return bool True si se actualizó correctamente, false en caso contrario
   */
  public function updateCriteria($criteriaId, $data)
  {
    try {
      // Verificar que el criterio existe
      $criterioExistente = $this->getCriteriaById($criteriaId);
      if (!$criterioExistente) {
        return false;
      }

      // Validar datos según el tipo de criterio
      $validationResult = $this->validateCriteriaData($data, $criteriaId);
      if (!$validationResult['valid']) {
        error_log("Error de validación en updateCriteria: " . implode(', ', $validationResult['errors']));
        return false;
      }

      $camposActualizar = [];

      // Campos básicos
      $camposBasicos = ['nombre', 'tipo_criterio', 'puntaje', 'estado'];
      foreach ($camposBasicos as $campo) {
        if (isset($data[$campo])) {
          $camposActualizar[] = [
            "campo_nombre" => $campo,
            "campo_marcador" => ":{$campo}",
            "campo_valor" => $data[$campo]
          ];
        }
      }

      // Campos específicos del tipo de criterio - resetear todos primero
      $camposActualizar[] = [
        "campo_nombre" => "valor_minimo",
        "campo_marcador" => ":valor_minimo",
        "campo_valor" => null
      ];
      $camposActualizar[] = [
        "campo_nombre" => "valor_maximo",
        "campo_marcador" => ":valor_maximo",
        "campo_valor" => null
      ];
      $camposActualizar[] = [
        "campo_nombre" => "valor_texto",
        "campo_marcador" => ":valor_texto",
        "campo_valor" => null
      ];
      $camposActualizar[] = [
        "campo_nombre" => "valor_booleano",
        "campo_marcador" => ":valor_booleano",
        "campo_valor" => null
      ];

      // Establecer valores según el tipo
      $tipoCriterio = $data['tipo_criterio'] ?: $criterioExistente->tipo_criterio;
      switch ($tipoCriterio) {
        case self::TIPO_RANGO_NUMERICO:
          // Actualizar valores específicos
          $camposActualizar[count($camposActualizar) - 4]['campo_valor'] = $data['valor_minimo'] ?: null;
          $camposActualizar[count($camposActualizar) - 3]['campo_valor'] = $data['valor_maximo'] ?: null;
          break;

        case self::TIPO_VALOR_ESPECIFICO:
          $camposActualizar[count($camposActualizar) - 2]['campo_valor'] = $data['valor_texto'] ?: null;
          break;

        case self::TIPO_BOOLEANO:
          $camposActualizar[count($camposActualizar) - 1]['campo_valor'] = $data['valor_booleano'] ?: null;
          break;
      }

      // Campos de auditoría
      $camposActualizar[] = [
        "campo_nombre" => "fecha_modificacion",
        "campo_marcador" => ":fecha_modificacion",
        "campo_valor" => date("Y-m-d H:i:s")
      ];

      $camposActualizar[] = [
        "campo_nombre" => "usuario_modificacion_id",
        "campo_marcador" => ":usuario_modificacion_id",
        "campo_valor" => $data['usuario_modificacion_id']
      ];

      $condicion = [
        "condicion_campo" => "id",
        "condicion_marcador" => ":criteria_id",
        "condicion_valor" => $criteriaId
      ];

      $resultado = $this->actualizarDatos("criterio_puntuacion", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en updateCriteria: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de un criterio (activo/inactivo)
   * 
   * @param int $criteriaId ID del criterio
   * @param bool $estado Nuevo estado
   * @param int $userId ID del usuario que realiza el cambio
   * @return bool True si se cambió correctamente, false en caso contrario
   */
  public function toggleCriteriaStatus($criteriaId, $estado, $userId)
  {
    try {
      $camposActualizar = [
        [
          "campo_nombre" => "estado",
          "campo_marcador" => ":estado",
          "campo_valor" => $estado ? 1 : 0
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
        "condicion_marcador" => ":criteria_id",
        "condicion_valor" => $criteriaId
      ];

      $resultado = $this->actualizarDatos("criterio_puntuacion", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en toggleCriteriaStatus: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Elimina un criterio
   * 
   * @param int $criteriaId ID del criterio
   * @return bool True si se eliminó correctamente, false en caso contrario
   */
  public function deleteCriteria($criteriaId)
  {
    try {
      $resultado = $this->eliminarRegistro("criterio_puntuacion", "id", $criteriaId);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en deleteCriteria: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Valida los datos de un criterio según su tipo
   * 
   * @param array $data Datos a validar
   * @param int|null $criteriaId ID del criterio (para actualizaciones)
   * @return array Resultado de la validación
   */
  private function validateCriteriaData($data, $criteriaId = null)
  {
    $result = ['valid' => true, 'errors' => []];

    // Validaciones básicas
    if (empty($data['nombre'])) {
      $result['errors'][] = "El nombre es requerido";
    }

    if (empty($data['tipo_criterio']) || !in_array($data['tipo_criterio'], [
      self::TIPO_RANGO_NUMERICO,
      self::TIPO_VALOR_ESPECIFICO,
      self::TIPO_BOOLEANO
    ])) {
      $result['errors'][] = "Tipo de criterio inválido";
    }

    if (!isset($data['puntaje']) || !is_numeric($data['puntaje'])) {
      $result['errors'][] = "El puntaje debe ser un número";
    }

    if (empty($data['subcategoria_id']) || !is_numeric($data['subcategoria_id'])) {
      $result['errors'][] = "ID de subcategoría inválido";
    }

    // Validaciones específicas por tipo
    if (!empty($data['tipo_criterio'])) {
      switch ($data['tipo_criterio']) {
        case self::TIPO_RANGO_NUMERICO:
          if (!isset($data['valor_minimo']) || !is_numeric($data['valor_minimo'])) {
            $result['errors'][] = "Valor mínimo requerido para rango numérico";
          }
          if (isset($data['valor_maximo']) && !is_numeric($data['valor_maximo'])) {
            $result['errors'][] = "Valor máximo debe ser numérico";
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

  /**
   * Obtiene criterios agrupados por categoría y subcategoría
   * 
   * @return array Criterios agrupados
   */
  public function getCriteriaGrouped()
  {
    try {
      $query = "SELECT c.id as categoria_id, c.nombre as categoria_nombre,
                             s.id as subcategoria_id, s.nombre as subcategoria_nombre,
                             cr.id, cr.nombre, cr.tipo_criterio, cr.puntaje,
                             cr.valor_minimo, cr.valor_maximo, cr.valor_texto, cr.valor_booleano
                      FROM categoria_criterio c
                      JOIN subcategoria_criterio s ON c.id = s.categoria_id
                      JOIN criterio_puntuacion cr ON s.id = cr.subcategoria_id
                      WHERE c.estado = 1 AND s.estado = 1 AND cr.estado = 1
                      ORDER BY c.nombre, s.nombre, cr.nombre";

      $resultado = $this->ejecutarConsulta($query);
      $criterios = $resultado->fetchAll(PDO::FETCH_OBJ);

      // Agrupar resultados
      $grouped = [];
      foreach ($criterios as $criterio) {
        $catId = $criterio->categoria_id;
        $subId = $criterio->subcategoria_id;

        if (!isset($grouped[$catId])) {
          $grouped[$catId] = [
            'categoria' => [
              'id' => $criterio->categoria_id,
              'nombre' => $criterio->categoria_nombre
            ],
            'subcategorias' => []
          ];
        }

        if (!isset($grouped[$catId]['subcategorias'][$subId])) {
          $grouped[$catId]['subcategorias'][$subId] = [
            'subcategoria' => [
              'id' => $criterio->subcategoria_id,
              'nombre' => $criterio->subcategoria_nombre
            ],
            'criterios' => []
          ];
        }

        $grouped[$catId]['subcategorias'][$subId]['criterios'][] = [
          'id' => $criterio->id,
          'nombre' => $criterio->nombre,
          'tipo_criterio' => $criterio->tipo_criterio,
          'puntaje' => $criterio->puntaje,
          'valor_minimo' => $criterio->valor_minimo,
          'valor_maximo' => $criterio->valor_maximo,
          'valor_texto' => $criterio->valor_texto,
          'valor_booleano' => $criterio->valor_booleano
        ];
      }

      return $grouped;
    } catch (\Exception $e) {
      error_log("Error en getCriteriaGrouped: " . $e->getMessage());
      return [];
    }
  }
}
