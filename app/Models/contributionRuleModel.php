<?php

namespace App\Models;

use PDO;
use App\Models\MainModel;

/**
 * Modelo para gestionar las reglas de aportación (CRUD completo)
 * 
 * Maneja las operaciones CRUD para la tabla regla_aportacion
 * que define los montos de aportación según nivel socioeconómico, edad y periodicidad
 */
class ContributionRuleModel extends MainModel
{
  // Periodicidades válidas
  const PERIODICIDAD_MENSUAL = 'mensual';
  const PERIODICIDAD_SEMESTRAL = 'semestral';
  const PERIODICIDAD_ANUAL = 'anual';

  /**
   * Obtiene todas ilas reglas de aportación actvas
   * 
   * @param array $filters Filtros opcionales (nivel_id, edad, periodicidad)
   * @return array Lista de reglas
   */
  public function getAllRules($filters = [])
  {
    try {
      $query = "SELECT r.*, 
                             n.nivel as nivel_nombre
                      FROM regla_aportacion r
                      JOIN nivel_socioeconomico n ON r.nivel_socioeconomico_id = n.id
                      WHERE r.estado = 1 AND n.estado = 1";

      $params = [];

      // Aplicar filtros
      if (!empty($filters['nivel_id'])) {
        $query .= " AND r.nivel_socioeconomico_id = :nivel_id";
        $params[':nivel_id'] = $filters['nivel_id'];
      }

      if (!empty($filters['edad'])) {
        $query .= " AND r.edad = :edad";
        $params[':edad'] = $filters['edad'];
      }

      if (!empty($filters['periodicidad'])) {
        $query .= " AND r.periodicidad = :periodicidad";
        $params[':periodicidad'] = $filters['periodicidad'];
      }

      $query .= " ORDER BY n.puntaje_minimo ASC, r.edad ASC, 
                        FIELD(r.periodicidad, 'mensual', 'semestral', 'anual')";

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllRules: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene una regla de aportación por su ID
   * 
   * @param int $ruleId ID de la regla
   * @return object|false Datos de la regla o false si no existe
   */
  public function getRuleById($ruleId)
  {
    try {
      $query = "SELECT r.*, 
                             n.nivel as nivel_nombre,
                             n.puntaje_minimo
                      FROM regla_aportacion r
                      JOIN nivel_socioeconomico n ON r.nivel_socioeconomico_id = n.id
                      WHERE r.id = :rule_id";

      $resultado = $this->ejecutarConsulta($query, [':rule_id' => $ruleId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getRuleById: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene el monto de aportación específico según criterios
   * 
   * @param int $nivelId ID del nivel socioeconómico
   * @param int $edad Edad del paciente
   * @param string $periodicidad Periodicidad de la aportación
   * @return object|false Regla encontrada o false si no existe
   */
  public function getContributionAmount($nivelId, $edad, $periodicidad)
  {
    try {
      $query = "SELECT r.*, n.nivel as nivel_nombre
                      FROM regla_aportacion r
                      JOIN nivel_socioeconomico n ON r.nivel_socioeconomico_id = n.id
                      WHERE r.nivel_socioeconomico_id = :nivel_id 
                      AND r.edad = :edad 
                      AND r.periodicidad = :periodicidad 
                      AND r.estado = 1 AND n.estado = 1";

      $params = [
        ':nivel_id' => $nivelId,
        ':edad' => $edad,
        ':periodicidad' => $periodicidad
      ];

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getContributionAmount: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Crea una nueva regla de aportación
   * 
   * @param array $data Datos de la regla
   * @return int|false ID de la regla creada o false si hubo error
   */
  public function createRule($data)
  {
    try {
      // Validar datos
      $validationResult = $this->validateRuleData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación en createRule: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Verificar que no exista una regla con la misma combinación
      if ($this->ruleExists($data['nivel_socioeconomico_id'], $data['edad'], $data['periodicidad'])) {
        error_log("Intento de crear regla duplicada: Nivel {$data['nivel_socioeconomico_id']}, Edad {$data['edad']}, Periodicidad {$data['periodicidad']}");
        return false;
      }

      $datosParaInsertar = [
        'nivel_socioeconomico_id' => $data['nivel_socioeconomico_id'],
        'edad' => $data['edad'],
        'periodicidad' => $data['periodicidad'],
        'monto_aportacion' => $data['monto_aportacion'],
        'estado' => 1,
        'fecha_creacion' => date("Y-m-d H:i:s"),
        'fecha_modificacion' => date("Y-m-d H:i:s"),
        'usuario_creacion_id' => $data['usuario_creacion_id'],
        'usuario_modificacion_id' => $data['usuario_creacion_id']
      ];

      $resultado = $this->insertarDatos("regla_aportacion", $datosParaInsertar);

      if ($resultado->rowCount() > 0) {
        return $this->getLastInsertId();
      }

      return false;
    } catch (\Exception $e) {
      error_log("Error en createRule: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualiza una regla de aportación existente
   * 
   * @param int $ruleId ID de la regla
   * @param array $data Datos a actualizar
   * @return bool True si se actualizó correctamente, false en caso contrario
   */
  public function updateRule($ruleId, $data)
  {
    try {
      // Verificar que la regla existe
      $reglaExistente = $this->getRuleById($ruleId);
      if (!$reglaExistente) {
        return false;
      }

      // Validar datos
      $validationResult = $this->validateRuleData($data, $ruleId);
      if (!$validationResult['valid']) {
        error_log("Error de validación en updateRule: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Si se están cambiendo los criterios únicos, verificar duplicados
      $criteriosCambiados = (
        (isset($data['nivel_socioeconomico_id']) && $data['nivel_socioeconomico_id'] != $reglaExistente->nivel_socioeconomico_id) ||
        (isset($data['edad']) && $data['edad'] != $reglaExistente->edad) ||
        (isset($data['periodicidad']) && $data['periodicidad'] != $reglaExistente->periodicidad)
      );

      if ($criteriosCambiados) {
        $nuevoNivel = $data['nivel_socioeconomico_id'] ?: $reglaExistente->nivel_socioeconomico_id;
        $nuevaEdad = $data['edad'] ?: $reglaExistente->edad;
        $nuevaPeriodicidad = $data['periodicidad'] ?: $reglaExistente->periodicidad;

        if ($this->ruleExists($nuevoNivel, $nuevaEdad, $nuevaPeriodicidad, $ruleId)) {
          error_log("Conflicto de regla única en actualización: Nivel {$nuevoNivel}, Edad {$nuevaEdad}, Periodicidad {$nuevaPeriodicidad}");
          return false;
        }
      }

      $camposActualizar = [];

      // Campos permitidos para actualizar
      $camposPermitidos = ['nivel_socioeconomico_id', 'edad', 'periodicidad', 'monto_aportacion', 'estado'];

      foreach ($camposPermitidos as $campo) {
        if (isset($data[$campo])) {
          $camposActualizar[] = [
            "campo_nombre" => $campo,
            "campo_marcador" => ":{$campo}",
            "campo_valor" => $data[$campo]
          ];
        }
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
        "condicion_marcador" => ":rule_id",
        "condicion_valor" => $ruleId
      ];

      $resultado = $this->actualizarDatos("regla_aportacion", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en updateRule: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de una regla de aportación (activa/inactiva)
   * 
   * @param int $ruleId ID de la regla
   * @param bool $estado Nuevo estado
   * @param int $userId ID del usuario que realiza el cambio
   * @return bool True si se cambió correctamente, false en caso contrario
   */
  public function toggleRuleStatus($ruleId, $estado, $userId)
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
        "condicion_marcador" => ":rule_id",
        "condicion_valor" => $ruleId
      ];

      $resultado = $this->actualizarDatos("regla_aportacion", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en toggleRuleStatus: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Elimina una regla de aportación
   * 
   * @param int $ruleId ID de la regla
   * @return bool True si se eliminó correctamente, false en caso contrario
   */
  public function deleteRule($ruleId)
  {
    try {
      // Verificar si hay solicitudes de pago que usan esta regla
      $querySolicitudes = "SELECT COUNT(*) FROM solicitud_pago sp
                                JOIN regla_aportacion r ON sp.nivel_socioeconomico_id = r.nivel_socioeconomico_id 
                                AND sp.edad_paciente = r.edad 
                                AND sp.periodicidad = r.periodicidad
                                WHERE r.id = :rule_id";

      $resultado = $this->ejecutarConsulta($querySolicitudes, [':rule_id' => $ruleId]);

      if ($resultado->fetchColumn() > 0) {
        // Hay solicitudes que usan esta regla, no se puede eliminar
        error_log("Intento de eliminar regla con solicitudes de pago asociadas: " . $ruleId);
        return false;
      }

      // Eliminar la regla
      $resultado = $this->eliminarRegistro("regla_aportacion", "id", $ruleId);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en deleteRule: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene reglas agrupadas por nivel socioeconómico
   * 
   * @return array Reglas agrupadas
   */
  public function getRulesGroupedByLevel()
  {
    try {
      $query = "SELECT r.*, n.nivel as nivel_nombre, n.puntaje_minimo
                      FROM regla_aportacion r
                      JOIN nivel_socioeconomico n ON r.nivel_socioeconomico_id = n.id
                      WHERE r.estado = 1 AND n.estado = 1
                      ORDER BY n.puntaje_minimo ASC, r.edad ASC, 
                               FIELD(r.periodicidad, 'mensual', 'semestral', 'anual')";

      $resultado = $this->ejecutarConsulta($query);
      $reglas = $resultado->fetchAll(PDO::FETCH_OBJ);

      // Agrupar por nivel
      $grouped = [];
      foreach ($reglas as $regla) {
        $nivelId = $regla->nivel_socioeconomico_id;

        if (!isset($grouped[$nivelId])) {
          $grouped[$nivelId] = [
            'nivel' => [
              'id' => $regla->nivel_socioeconomico_id,
              'nombre' => $regla->nivel_nombre,
              'puntaje_minimo' => $regla->puntaje_minimo
            ],
            'reglas' => []
          ];
        }

        $grouped[$nivelId]['reglas'][] = [
          'id' => $regla->id,
          'edad' => $regla->edad,
          'periodicidad' => $regla->periodicidad,
          'monto_aportacion' => $regla->monto_aportacion,
          'fecha_creacion' => $regla->fecha_creacion,
          'fecha_modificacion' => $regla->fecha_modificacion
        ];
      }

      return $grouped;
    } catch (\Exception $e) {
      error_log("Error en getRulesGroupedByLevel: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene matriz de aportaciones (edad x periodicidad) para un nivel
   * 
   * @param int $nivelId ID del nivel socioeconómico
   * @return array Matriz de aportaciones
   */
  public function getContributionMatrix($nivelId)
  {
    try {
      $query = "SELECT edad, periodicidad, monto_aportacion
                      FROM regla_aportacion
                      WHERE nivel_socioeconomico_id = :nivel_id AND estado = 1
                      ORDER BY edad ASC";

      $resultado = $this->ejecutarConsulta($query, [':nivel_id' => $nivelId]);
      $reglas = $resultado->fetchAll(PDO::FETCH_OBJ);

      // Crear matriz
      $matrix = [];
      $edades = [];
      $periodicidades = [self::PERIODICIDAD_MENSUAL, self::PERIODICIDAD_SEMESTRAL, self::PERIODICIDAD_ANUAL];

      foreach ($reglas as $regla) {
        if (!in_array($regla->edad, $edades)) {
          $edades[] = $regla->edad;
        }

        $matrix[$regla->edad][$regla->periodicidad] = $regla->monto_aportacion;
      }

      sort($edades);

      return [
        'edades' => $edades,
        'periodicidades' => $periodicidades,
        'matrix' => $matrix
      ];
    } catch (\Exception $e) {
      error_log("Error en getContributionMatrix: " . $e->getMessage());
      return ['edades' => [], 'periodicidades' => [], 'matrix' => []];
    }
  }

  /**
   * Crea reglas en lote para un nivel socioeconómico
   * 
   * @param int $nivelId ID del nivel socioeconómico
   * @param array $reglas Array de reglas con edad, periodicidad y monto
   * @param int $userId ID del usuario que crea las reglas
   * @return array Resultado con éxitos y errores
   */
  public function createBulkRules($nivelId, $reglas, $userId)
  {
    try {
      $resultado = ['success' => 0, 'errors' => 0, 'details' => []];

      foreach ($reglas as $regla) {
        $data = [
          'nivel_socioeconomico_id' => $nivelId,
          'edad' => $regla['edad'],
          'periodicidad' => $regla['periodicidad'],
          'monto_aportacion' => $regla['monto_aportacion'],
          'usuario_creacion_id' => $userId
        ];

        $ruleId = $this->createRule($data);

        if ($ruleId) {
          $resultado['success']++;
          $resultado['details'][] = "Regla creada: Edad {$regla['edad']}, {$regla['periodicidad']}";
        } else {
          $resultado['errors']++;
          $resultado['details'][] = "Error en regla: Edad {$regla['edad']}, {$regla['periodicidad']}";
        }
      }

      return $resultado;
    } catch (\Exception $e) {
      error_log("Error en createBulkRules: " . $e->getMessage());
      return ['success' => 0, 'errors' => count($reglas), 'details' => ['Error general en creación masiva']];
    }
  }

  /**
   * Obtiene estadísticas de uso de las reglas de aportación
   * 
   * @return array Estadísticas
   */
  public function getRuleUsageStats()
  {
    try {
      $query = "SELECT n.nivel as nivel_nombre,
                             COUNT(r.id) as total_reglas,
                             COUNT(CASE WHEN r.estado = 1 THEN 1 END) as reglas_activas,
                             MIN(r.monto_aportacion) as monto_minimo,
                             MAX(r.monto_aportacion) as monto_maximo,
                             AVG(r.monto_aportacion) as monto_promedio
                      FROM nivel_socioeconomico n
                      LEFT JOIN regla_aportacion r ON n.id = r.nivel_socioeconomico_id
                      WHERE n.estado = 1
                      GROUP BY n.id
                      ORDER BY n.puntaje_minimo ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getRuleUsageStats: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene las periodicidades disponibles
   * 
   * @return array Lista de periodicidades
   */
  public function getPeriodicityOptions()
  {
    return [
      self::PERIODICIDAD_MENSUAL => 'Mensual',
      self::PERIODICIDAD_SEMESTRAL => 'Semestral',
      self::PERIODICIDAD_ANUAL => 'Anual'
    ];
  }

  /**
   * Verifica si existe una regla con los criterios especificados
   * 
   * @param int $nivelId ID del nivel socioeconómico
   * @param int $edad Edad
   * @param string $periodicidad Periodicidad
   * @param int|null $excludeRuleId ID de regla a excluir de la verificación
   * @return bool True si existe, false en caso contrario
   */
  private function ruleExists($nivelId, $edad, $periodicidad, $excludeRuleId = null)
  {
    try {
      $query = "SELECT COUNT(*) FROM regla_aportacion 
                      WHERE nivel_socioeconomico_id = :nivel_id 
                      AND edad = :edad 
                      AND periodicidad = :periodicidad";

      $params = [
        ':nivel_id' => $nivelId,
        ':edad' => $edad,
        ':periodicidad' => $periodicidad
      ];

      if ($excludeRuleId) {
        $query .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeRuleId;
      }

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en ruleExists: " . $e->getMessage());
      return true; // En caso de error, asumir que existe por seguridad
    }
  }

  /**
   * Valida los datos de una regla de aportación
   * 
   * @param array $data Datos a validar
   * @param int|null $ruleId ID de la regla (para actualizaciones)
   * @return array Resultado de la validación
   */
  private function validateRuleData($data, $ruleId = null)
  {
    $result = ['valid' => true, 'errors' => []];

    // Validar nivel socioeconómico
    if (empty($data['nivel_socioeconomico_id']) || !is_numeric($data['nivel_socioeconomico_id'])) {
      $result['errors'][] = "ID de nivel socioeconómico inválido";
    }

    // Validar edad
    if (!isset($data['edad'])) {
      $result['errors'][] = "La edad es requerida";
    } elseif (!is_numeric($data['edad']) || $data['edad'] < 0 || $data['edad'] > 150) {
      $result['errors'][] = "La edad debe ser un número entre 0 y 150";
    }

    // Validar periodicidad
    if (empty($data['periodicidad'])) {
      $result['errors'][] = "La periodicidad es requerida";
    } elseif (!in_array($data['periodicidad'], [
      self::PERIODICIDAD_MENSUAL,
      self::PERIODICIDAD_SEMESTRAL,
      self::PERIODICIDAD_ANUAL
    ])) {
      $result['errors'][] = "Periodicidad inválida";
    }

    // Validar monto de aportación
    if (!isset($data['monto_aportacion'])) {
      $result['errors'][] = "El monto de aportación es requerido";
    } elseif (!is_numeric($data['monto_aportacion']) || $data['monto_aportacion'] < 0) {
      $result['errors'][] = "El monto de aportación debe ser un número positivo";
    } elseif ($data['monto_aportacion'] > 999999.99) {
      $result['errors'][] = "El monto de aportación no puede exceder $999,999.99";
    }

    // Validar usuarios
    if (empty($data['usuario_creacion_id']) && !$ruleId) {
      $result['errors'][] = "ID de usuario de creación requerido";
    }

    if (empty($data['usuario_modificacion_id']) && $ruleId) {
      $result['errors'][] = "ID de usuario de modificación requerido";
    }

    $result['valid'] = empty($result['errors']);
    return $result;
  }
}
