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
   * Crea una nueva regla de aportación
   * 
   * @param array $data Datos de la regla
   * @return array Resultado con success, data/errors
   */
  public function createRule($data)
  {
    try {
      // Definir reglas de validación
      $validar = [
        'nivel_socioeconomico_id' => [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 1
        ],
        'edad' => [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 0,
          'max_valor' => 8
        ],
        'periodicidad' => [
          'requerido' => true,
          'valores_permitidos' => [self::PERIODICIDAD_MENSUAL, self::PERIODICIDAD_SEMESTRAL, self::PERIODICIDAD_ANUAL],
          'sanitizar' => true
        ],
        'monto_aportacion' => [
          'requerido' => true,
        ],
        'usuario_creacion_id' => [
          'requerido' => true,
          'formato' => 'entero'
        ]
      ];

      // Validar datos usando el método del MainModel
      $validationResult = $this->validarDatos($data, $validar);

      if (!empty($validationResult['errors'])) {
        error_log("Errores de validación en createRule: " . json_encode($validationResult['errors']));
        return [
          'success' => false,
          'errors' => $validationResult['errors']
        ];
      }

      $datosValidados = $validationResult['datos'];

      // Verificar que el nivel socioeconómico existe y está activo
      $nivelExiste = $this->verificarNivelExiste($datosValidados['nivel_socioeconomico_id']);
      if (!$nivelExiste['existe']) {
        return [
          'success' => false,
          'errors' => ['nivel_socioeconomico_id' => $nivelExiste['mensaje']]
        ];
      }

      // Verificar que no exista una regla con la misma combinación nivel + edad
      if ($this->ruleExists($datosValidados['nivel_socioeconomico_id'], $datosValidados['edad'])) {
        error_log("Intento de crear regla duplicada: Nivel {$datosValidados['nivel_socioeconomico_id']}, Edad {$datosValidados['edad']}");
        return [
          'success' => false,
          'errors' => ['edad' => 'Ya existe una regla de aportación para este nivel socioeconómico y edad']
        ];
      }

      $datosParaInsertar = [
        'nivel_socioeconomico_id' => $datosValidados['nivel_socioeconomico_id'],
        'edad' => $datosValidados['edad'],
        'periodicidad' => $datosValidados['periodicidad'],
        'monto_aportacion' => $datosValidados['monto_aportacion'],
        'estado' => 1,
        'fecha_creacion' => date("Y-m-d H:i:s"),
        'fecha_modificacion' => date("Y-m-d H:i:s"),
        'usuario_creacion_id' => $datosValidados['usuario_creacion_id'],
        'usuario_modificacion_id' => $datosValidados['usuario_creacion_id']
      ];

      $resultado = $this->insertarDatos("regla_aportacion", $datosParaInsertar);

      if ($resultado->rowCount() > 0) {
        return [
          'success' => true,
          'data' => $this->getLastInsertId()
        ];
      }

      return [
        'success' => false,
        'errors' => ['general' => 'No se pudo crear la regla de aportación']
      ];
    } catch (\Exception $e) {
      error_log("Error en createRule: " . $e->getMessage());
      return [
        'success' => false,
        'errors' => ['general' => 'Error interno del servidor']
      ];
    }
  }

  /**
   * Actualiza una regla de aportación existente
   * 
   * @param int $ruleId ID de la regla
   * @param array $data Datos a actualizar
   * @return array Resultado con success, data/errors
   */
  public function updateRule($ruleId, $data)
  {
    try {
      // Verificar que la regla existe
      $reglaExistente = $this->getRuleById($ruleId);
      if (!$reglaExistente) {
        return [
          'success' => false,
          'errors' => ['general' => 'Regla de aportación no encontrada']
        ];
      }

      // Definir reglas de validación (solo para campos que se van a actualizar)
      $validar = [];

      if (isset($data['nivel_socioeconomico_id'])) {
        $validar['nivel_socioeconomico_id'] = [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 1
        ];
      }

      if (isset($data['edad'])) {
        $validar['edad'] = [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 0,
          'max_valor' => 150
        ];
      }

      if (isset($data['periodicidad'])) {
        $validar['periodicidad'] = [
          'requerido' => true,
          'valores_permitidos' => [self::PERIODICIDAD_MENSUAL, self::PERIODICIDAD_SEMESTRAL, self::PERIODICIDAD_ANUAL],
          'sanitizar' => true
        ];
      }

      if (isset($data['monto_aportacion'])) {
        $validar['monto_aportacion'] = [
          'requerido' => true,
        ];
      }

      if (isset($data['estado'])) {
        $validar['estado'] = [
          'requerido' => true,
          'formato' => 'entero'
        ];
      }

      if (isset($data['usuario_modificacion_id'])) {
        $validar['usuario_modificacion_id'] = [
          'requerido' => true,
          'formato' => 'entero'
        ];
      }

      // Validar datos usando el método del MainModel
      $validationResult = $this->validarDatos($data, $validar);

      if (!empty($validationResult['errors'])) {
        error_log("Errores de validación en updateRule: " . json_encode($validationResult['errors']));
        return [
          'success' => false,
          'errors' => $validationResult['errors']
        ];
      }

      $datosValidados = $validationResult['datos'];

      // Si se está cambiando el nivel, verificar que existe y está activo
      if (isset($datosValidados['nivel_socioeconomico_id']) && $datosValidados['nivel_socioeconomico_id'] != $reglaExistente->nivel_socioeconomico_id) {
        $nivelExiste = $this->verificarNivelExiste($datosValidados['nivel_socioeconomico_id']);
        if (!$nivelExiste['existe']) {
          return [
            'success' => false,
            'errors' => ['nivel_socioeconomico_id' => $nivelExiste['mensaje']]
          ];
        }
      }

      // Si se están cambiando nivel o edad, verificar duplicados
      $criteriosCambiados = (
        (isset($datosValidados['nivel_socioeconomico_id']) && $datosValidados['nivel_socioeconomico_id'] != $reglaExistente->nivel_socioeconomico_id) ||
        (isset($datosValidados['edad']) && $datosValidados['edad'] != $reglaExistente->edad)
      );

      if ($criteriosCambiados) {
        $nuevoNivel = $datosValidados['nivel_socioeconomico_id'] ?: $reglaExistente->nivel_socioeconomico_id;
        $nuevaEdad = $datosValidados['edad'] ?: $reglaExistente->edad;

        if ($this->ruleExists($nuevoNivel, $nuevaEdad, null, $ruleId)) {
          return [
            'success' => false,
            'errors' => ['edad' => 'Ya existe una regla de aportación para este nivel socioeconómico y edad']
          ];
        }
      }

      // Preparar datos para actualización usando el formato del MainModel
      $camposActualizar = [];

      // Procesar cada campo validado
      foreach ($datosValidados as $campo => $valor) {
        $camposActualizar[] = [
          "campo_nombre" => $campo,
          "campo_marcador" => ":{$campo}",
          "campo_valor" => $valor
        ];
      }

      // Agregar campos de auditoría
      $camposActualizar[] = [
        "campo_nombre" => "fecha_modificacion",
        "campo_marcador" => ":fecha_modificacion",
        "campo_valor" => date("Y-m-d H:i:s")
      ];

      // Definir condición para la actualización
      $condicion = [
        "condicion_campo" => "id",
        "condicion_marcador" => ":rule_id",
        "condicion_valor" => $ruleId
      ];

      // Ejecutar actualización
      $resultado = $this->actualizarDatos("regla_aportacion", $camposActualizar, $condicion);

      if ($resultado->rowCount() > 0) {
        return [
          'success' => true,
          'data' => $ruleId
        ];
      }

      return [
        'success' => false,
        'errors' => ['general' => 'No se realizaron cambios en la regla']
      ];
    } catch (\Exception $e) {
      error_log("Error en updateRule: " . $e->getMessage());
      return [
        'success' => false,
        'errors' => ['general' => 'Error interno del servidor']
      ];
    }
  }


  /**
   * Verifica que un nivel socioeconómico existe y está activo
   * 
   * @param int $nivelId ID del nivel
   * @return array Resultado con existe y mensaje
   */
  private function verificarNivelExiste($nivelId)
  {
    try {
      $query = "SELECT id, nivel, estado FROM nivel_socioeconomico WHERE id = :nivel_id";
      $resultado = $this->ejecutarConsulta($query, [':nivel_id' => $nivelId]);
      $nivel = $resultado->fetch(PDO::FETCH_OBJ);

      if (!$nivel) {
        return [
          'existe' => false,
          'mensaje' => 'El nivel socioeconómico seleccionado no existe'
        ];
      }

      if ($nivel->estado != 1) {
        return [
          'existe' => false,
          'mensaje' => 'El nivel socioeconómico seleccionado está inactivo'
        ];
      }

      return [
        'existe' => true,
        'mensaje' => 'Nivel válido'
      ];
    } catch (\Exception $e) {
      error_log("Error en verificarNivelExiste: " . $e->getMessage());
      return [
        'existe' => false,
        'mensaje' => 'Error al verificar el nivel socioeconómico'
      ];
    }
  }

  /**
   * Verifica si ya existe una regla para la misma combinación nivel + edad
   * Solo puede existir UNA regla por nivel y edad, independiente de la periodicidad
   * 
   * @param int $nivelId ID del nivel
   * @param int $edad Edad
   * @param string $periodicidad Periodicidad (no se usa en la validación, pero se mantiene por consistencia)
   * @param int|null $excludeId ID de regla a excluir (para updates)
   * @return bool True si existe, false si no
   */
  private function ruleExists($nivelId, $edad, $periodicidad = null, $excludeId = null)
  {
    try {
      // Verificar si ya existe una regla para este nivel y edad
      // (sin importar la periodicidad)
      $query = "SELECT COUNT(*) FROM regla_aportacion 
                WHERE nivel_socioeconomico_id = :nivel_id 
                AND edad = :edad";

      $params = [
        ':nivel_id' => $nivelId,
        ':edad' => $edad
      ];

      if ($excludeId) {
        $query .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
      }

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en ruleExists: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de una regla de aportación
   * 
   * @param int $ruleId ID de la regla
   * @param int $estado Nuevo estado (0 o 1)
   * @param int $userId ID del usuario que hace el cambio
   * @return bool True si se actualizó correctamente, false en caso contrario
   */
  public function toggleRuleStatus($ruleId, $estado, $userId)
  {
    try {
      $camposActualizar = [
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
                      JOIN nivel_socioeconomico n ON r.nivel_socioeconomico_id = n.id";

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
}
