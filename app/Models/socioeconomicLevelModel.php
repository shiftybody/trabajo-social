<?php

namespace App\Models;

use PDO;
use App\Models\MainModel;

/**
 * Modelo para gestionar los niveles socioeconómicos (CRUD completo)
 * 
 * Maneja las operaciones CRUD para la tabla nivel_socioeconomico
 * que define los rangos de puntaje para clasificar a los pacientes
 */
class SocioeconomicLevelModel extends MainModel
{
  /**
   * Obtiene todos los niveles socioeconómicos activos
   * 
   * @param bool $includeInactive Incluir niveles inactivos
   * @return array Lista de niveles ordenados por puntaje mínimo
   */
  public function getAllLevels($includeInactive = false)
  {
    try {
      $query = "SELECT * FROM nivel_socioeconomico";

      if (!$includeInactive) {
        $query .= " WHERE estado = 1";
      }

      $query .= " ORDER BY puntaje_minimo ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getAllLevels: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Obtiene un nivel socioeconómico por su ID
   * 
   * @param int $levelId ID del nivel
   * @return object|false Datos del nivel o false si no existe
   */
  public function getLevelById($levelId)
  {
    try {
      $query = "SELECT * FROM nivel_socioeconomico WHERE id = :level_id";
      $resultado = $this->ejecutarConsulta($query, [':level_id' => $levelId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getLevelById: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene un nivel socioeconómico por su nombre
   * 
   * @param string $nivel Nombre del nivel
   * @return object|false Datos del nivel o false si no existe
   */
  public function getLevelByName($nivel)
  {
    try {
      $query = "SELECT * FROM nivel_socioeconomico WHERE nivel = :nivel";
      $resultado = $this->ejecutarConsulta($query, [':nivel' => $nivel]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getLevelByName: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Determina el nivel socioeconómico según un puntaje
   * 
   * @param int $puntaje Puntaje obtenido en el estudio
   * @return object|false Nivel correspondiente o false si no se encuentra
   */
  public function getLevelByScore($puntaje)
  {
    try {
      $query = "SELECT * FROM nivel_socioeconomico 
                      WHERE :puntaje >= puntaje_minimo AND estado = 1
                      ORDER BY puntaje_minimo DESC 
                      LIMIT 1";

      $resultado = $this->ejecutarConsulta($query, [':puntaje' => $puntaje]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getLevelByScore: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Crea un nuevo nivel socioeconómico
   * 
   * @param array $data Datos del nivel (nivel, puntaje_minimo, usuario_creacion_id)
   * @return int|false ID del nivel creado o false si hubo error
   */
  public function createLevel($data)
  {
    error_log("Intentando crear nivel socioeconómico: " . json_encode($data));

    try {
      // Validar datos
      $validationResult = $this->validateLevelData($data);
      if (!$validationResult['valid']) {
        error_log("Error de validación en createLevel: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Verificar que no exista un nivel con el mismo nombre
      if ($this->getLevelByName($data['nivel'])) {
        error_log("Intento de crear nivel duplicado: " . $data['nivel']);
        return false;
      }

      // Verificar que no haya conflictos de rangos
      if ($this->hasScoreConflict($data['puntaje_minimo'])) {
        error_log("Conflicto de rangos de puntaje: " . $data['puntaje_minimo']);
        return false;
      }

      $datosParaInsertar = [
        'nivel' => $data['nivel'],
        'puntaje_minimo' => $data['puntaje_minimo'],
        'estado' => 1,
        'fecha_creacion' => date("Y-m-d H:i:s"),
        'fecha_modificacion' => date("Y-m-d H:i:s"),
        'usuario_creacion_id' => $data['usuario_creacion_id'],
        'usuario_modificacion_id' => $data['usuario_creacion_id']
      ];

      $resultado = $this->insertarDatos("nivel_socioeconomico", $datosParaInsertar);

      if ($resultado->rowCount() > 0) {
        return $this->getLastInsertId();
      }

      return false;
    } catch (\Exception $e) {
      error_log("Error en createLevel: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Actualiza un nivel socioeconómico existente
   * 
   * @param int $levelId ID del nivel
   * @param array $data Datos a actualizar
   * @return bool True si se actualizó correctamente, false en caso contrario
   */
  public function updateLevel($levelId, $data)
  {
    try {
      // Verificar que el nivel existe
      $nivelExistente = $this->getLevelById($levelId);
      if (!$nivelExistente) {
        return false;
      }

      // Validar datos
      $validationResult = $this->validateLevelData($data, $levelId);
      if (!$validationResult['valid']) {
        error_log("Error de validación en updateLevel: " . implode(', ', $validationResult['errors']));
        return false;
      }

      // Si se está cambiando el nombre, verificar que no exista otro con el mismo nombre
      if (isset($data['nivel']) && $data['nivel'] !== $nivelExistente->nivel) {
        $nivelConNombre = $this->getLevelByName($data['nivel']);
        if ($nivelConNombre && $nivelConNombre->id != $levelId) {
          error_log("Intento de actualizar nivel con nombre duplicado: " . $data['nivel']);
          return false;
        }
      }

      // Si se está cambiando el puntaje, verificar conflictos
      if (isset($data['puntaje_minimo']) && $data['puntaje_minimo'] != $nivelExistente->puntaje_minimo) {
        if ($this->hasScoreConflict($data['puntaje_minimo'], $levelId)) {
          error_log("Conflicto de rangos de puntaje en actualización: " . $data['puntaje_minimo']);
          return false;
        }
      }

      $camposActualizar = [];

      // Campos permitidos para actualizar
      $camposPermitidos = ['nivel', 'puntaje_minimo', 'estado'];

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
        "condicion_marcador" => ":level_id",
        "condicion_valor" => $levelId
      ];

      $resultado = $this->actualizarDatos("nivel_socioeconomico", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en updateLevel: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Cambia el estado de un nivel socioeconómico (activo/inactivo)
   * 
   * @param int $levelId ID del nivel
   * @param bool $estado Nuevo estado
   * @param int $userId ID del usuario que realiza el cambio
   * @return bool True si se cambió correctamente, false en caso contrario
   */
  public function toggleLevelStatus($levelId, $estado, $userId)
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
        "condicion_marcador" => ":level_id",
        "condicion_valor" => $levelId
      ];

      $resultado = $this->actualizarDatos("nivel_socioeconomico", $camposActualizar, $condicion);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en toggleLevelStatus: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Elimina un nivel socioeconómico (solo si no tiene reglas de aportación asociadas)
   * 
   * @param int $levelId ID del nivel
   * @return bool True si se eliminó correctamente, false en caso contrario
   */
  public function deleteLevel($levelId)
  {
    try {
      // Verificar si hay reglas de aportación asociadas
      $queryReglas = "SELECT COUNT(*) FROM regla_aportacion WHERE nivel_socioeconomico_id = :level_id";
      $resultado = $this->ejecutarConsulta($queryReglas, [':level_id' => $levelId]);

      if ($resultado->fetchColumn() > 0) {
        // Hay reglas asociadas, no se puede eliminar
        error_log("Intento de eliminar nivel con reglas de aportación asociadas: " . $levelId);
        return false;
      }

      // Verificar si hay estudios que usan este nivel
      $queryEstudios = "SELECT COUNT(*) FROM estudios_socioeconomicos WHERE nivel_socioeconomico_id = :level_id";
      $resultado = $this->ejecutarConsulta($queryEstudios, [':level_id' => $levelId]);

      if ($resultado->fetchColumn() > 0) {
        // Hay estudios que usan este nivel, no se puede eliminar
        error_log("Intento de eliminar nivel con estudios asociados: " . $levelId);
        return false;
      }

      // Eliminar el nivel
      $resultado = $this->eliminarRegistro("nivel_socioeconomico", "id", $levelId);
      return $resultado->rowCount() > 0;
    } catch (\Exception $e) {
      error_log("Error en deleteLevel: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene niveles con información adicional (conteo de reglas y estudios)
   * 
   * @param bool $includeInactive Incluir niveles inactivos
   * @return array Lista de niveles con información completa
   */
  public function getLevelsWithDetails($includeInactive = false)
  {
    try {
      $query = "SELECT n.*, 
                             COUNT(DISTINCT r.id) as reglas_count,
                             COUNT(DISTINCT e.id) as estudios_count,
                             u1.usuario_nombre as usuario_creacion_nombre,
                             u2.usuario_nombre as usuario_modificacion_nombre
                      FROM nivel_socioeconomico n
                      LEFT JOIN regla_aportacion r ON n.id = r.nivel_socioeconomico_id
                      LEFT JOIN estudios_socioeconomicos e ON n.id = e.nivel_socioeconomico_id
                      LEFT JOIN usuario u1 ON n.usuario_creacion_id = u1.usuario_id
                      LEFT JOIN usuario u2 ON n.usuario_modificacion_id = u2.usuario_id";

      if (!$includeInactive) {
        $query .= " WHERE n.estado = 1";
      }

      $query .= " GROUP BY n.id ORDER BY n.puntaje_minimo ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getLevelsWithDetails: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Verifica si un nivel puede ser eliminado (sin dependencias)
   * 
   * @param int $levelId ID del nivel
   * @return bool True si puede ser eliminado, false en caso contrario
   */
  public function canDeleteLevel($levelId)
  {
    try {
      // Verificar reglas de aportación
      $queryReglas = "SELECT COUNT(*) FROM regla_aportacion WHERE nivel_socioeconomico_id = :level_id";
      $resultado = $this->ejecutarConsulta($queryReglas, [':level_id' => $levelId]);

      if ($resultado->fetchColumn() > 0) {
        return false;
      }

      // Verificar estudios socioeconómicos
      $queryEstudios = "SELECT COUNT(*) FROM estudios_socioeconomicos WHERE nivel_socioeconomico_id = :level_id";
      $resultado = $this->ejecutarConsulta($queryEstudios, [':level_id' => $levelId]);

      return $resultado->fetchColumn() == 0;
    } catch (\Exception $e) {
      error_log("Error en canDeleteLevel: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene las opciones de niveles para un select/dropdown
   * 
   * @param bool $includeEmpty Incluir opción vacía al inicio
   * @return array Array con formato para select (id => nombre)
   */
  public function getLevelOptions($includeEmpty = true)
  {
    try {
      $options = [];

      if ($includeEmpty) {
        $options[0] = 'Seleccione un nivel...';
      }

      $niveles = $this->getAllLevels();

      foreach ($niveles as $nivel) {
        $options[$nivel->id] = $nivel->nivel . " (≥ {$nivel->puntaje_minimo} pts)";
      }

      return $options;
    } catch (\Exception $e) {
      error_log("Error en getLevelOptions: " . $e->getMessage());
      return $includeEmpty ? [0 => 'Error al cargar opciones'] : [];
    }
  }

  /**
   * Obtiene los rangos de puntaje para mostrar en interfaz
   * 
   * @return array Array con los rangos definidos
   */
  public function getScoreRanges()
  {
    try {
      $niveles = $this->getAllLevels();
      $ranges = [];

      for ($i = 0; $i < count($niveles); $i++) {
        $nivel = $niveles[$i];
        $rangeText = $nivel->puntaje_minimo . " pts";

        // Si hay un siguiente nivel, mostrar el rango
        if ($i + 1 < count($niveles)) {
          $siguienteNivel = $niveles[$i + 1];
          $rangeText = $nivel->puntaje_minimo . " - " . ($siguienteNivel->puntaje_minimo - 1) . " pts";
        } else {
          $rangeText = $nivel->puntaje_minimo . "+ pts";
        }

        $ranges[] = [
          'id' => $nivel->id,
          'nivel' => $nivel->nivel,
          'puntaje_minimo' => $nivel->puntaje_minimo,
          'range_text' => $rangeText
        ];
      }

      return $ranges;
    } catch (\Exception $e) {
      error_log("Error en getScoreRanges: " . $e->getMessage());
      return [];
    }
  }

  /**
   * Valida los datos de un nivel socioeconómico
   * 
   * @param array $data Datos a validar
   * @param int|null $levelId ID del nivel (para actualizaciones)
   * @return array Resultado de la validación
   */
  private function validateLevelData($data, $levelId = null)
  {
    $result = ['valid' => true, 'errors' => []];

    // Validar nombre del nivel
    if (empty($data['nivel'])) {
      $result['errors'][] = "El nombre del nivel es requerido";
    } elseif (strlen($data['nivel']) > 20) {
      $result['errors'][] = "El nombre del nivel no debe exceder 20 caracteres";
    }

    // Validar puntaje mínimo
    if (!isset($data['puntaje_minimo'])) {
      $result['errors'][] = "El puntaje mínimo es requerido";
    } elseif (!is_numeric($data['puntaje_minimo'])) {
      $result['errors'][] = "El puntaje mínimo debe ser un número";
    } elseif ($data['puntaje_minimo'] < 0) {
      $result['errors'][] = "El puntaje mínimo no puede ser negativo";
    }

    // Validar usuario de creación
    if (empty($data['usuario_creacion_id']) && !$levelId) {
      $result['errors'][] = "ID de usuario de creación requerido";
    }

    // Validar usuario de modificación
    if (empty($data['usuario_modificacion_id']) && $levelId) {
      $result['errors'][] = "ID de usuario de modificación requerido";
    }

    $result['valid'] = empty($result['errors']);
    return $result;
  }

  /**
   * Verifica si hay conflicto de rangos de puntaje
   * 
   * @param int $puntajeMinimo Puntaje mínimo a verificar
   * @param int|null $excludeLevelId ID del nivel a excluir de la verificación
   * @return bool True si hay conflicto, false en caso contrario
   */
  private function hasScoreConflict($puntajeMinimo, $excludeLevelId = null)
  {
    try {
      $query = "SELECT COUNT(*) FROM nivel_socioeconomico 
                      WHERE puntaje_minimo = :puntaje_minimo AND estado = 1";

      $params = [':puntaje_minimo' => $puntajeMinimo];

      if ($excludeLevelId) {
        $query .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeLevelId;
      }

      $resultado = $this->ejecutarConsulta($query, $params);
      return $resultado->fetchColumn() > 0;
    } catch (\Exception $e) {
      error_log("Error en hasScoreConflict: " . $e->getMessage());
      return true; // En caso de error, asumir que hay conflicto por seguridad
    }
  }

  /**
   * Obtiene estadísticas de uso de los niveles socioeconómicos
   * 
   * @return array Estadísticas de uso
   */
  public function getLevelUsageStats()
  {
    try {
      $query = "SELECT n.id, n.nivel, n.puntaje_minimo,
                    COUNT(DISTINCT e.id) as estudios_realizados,
                    COUNT(DISTINCT r.id) as reglas_configuradas,
                    MAX(e.fecha_creacion) as ultimo_estudio
                    FROM nivel_socioeconomico n
                    LEFT JOIN estudios_socioeconomicos e ON n.id = e.nivel_socioeconomico_id
                    LEFT JOIN regla_aportacion r ON n.id = r.nivel_socioeconomico_id
                    WHERE n.estado = 1
                    GROUP BY n.id
                    ORDER BY n.puntaje_minimo ASC";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (\Exception $e) {
      error_log("Error en getLevelUsageStats: " . $e->getMessage());
      return [];
    }
  }
}
