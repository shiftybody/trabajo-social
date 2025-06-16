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
   * @return array Resultado con success, data/errors
   */
  public function createLevel($data)
  {
    try {
      // Definir reglas de validación
      $validar = [
        'nivel' => [
          'requerido' => true,
          'min' => 1,
          'max' => 50,
          'formato' => '[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}',
          'sanitizar' => true
        ],
        'puntaje_minimo' => [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 0,
          'max_valor' => 1000
        ],
        'usuario_creacion_id' => [
          'requerido' => true,
          'formato' => 'entero'
        ]
      ];

      // Validar datos usando el método del MainModel
      $validationResult = $this->validarDatos($data, $validar);

      if (!empty($validationResult['errors'])) {
        error_log("Errores de validación en createLevel: " . json_encode($validationResult['errors']));
        return [
          'success' => false,
          'errors' => $validationResult['errors']
        ];
      }

      $datosValidados = $validationResult['datos'];

      // Verificar que no exista un nivel con el mismo nombre
      if ($this->getLevelByName($datosValidados['nivel'])) {
        error_log("Intento de crear nivel duplicado: " . $datosValidados['nivel']);
        return [
          'success' => false,
          'errors' => ['nivel' => 'Ya existe un nivel socioeconómico con este nombre']
        ];
      }

      // Verificar conflictos de rangos y orden lógico
      $conflictResult = $this->checkScoreConflict(
        $datosValidados['puntaje_minimo'],
        $datosValidados['nivel']
      );

      if (!$conflictResult['valid']) {
        error_log("Conflicto de rangos/orden: " . $conflictResult['message']);
        return [
          'success' => false,
          'errors' => ['puntaje_minimo' => $conflictResult['message']]
        ];
      }

      $datosParaInsertar = [
        'nivel' => $datosValidados['nivel'],
        'puntaje_minimo' => $datosValidados['puntaje_minimo'],
        'estado' => 1,
        'fecha_creacion' => date("Y-m-d H:i:s"),
        'fecha_modificacion' => date("Y-m-d H:i:s"),
        'usuario_creacion_id' => $datosValidados['usuario_creacion_id'],
        'usuario_modificacion_id' => $datosValidados['usuario_creacion_id']
      ];

      $resultado = $this->insertarDatos("nivel_socioeconomico", $datosParaInsertar);

      if ($resultado->rowCount() > 0) {
        return [
          'success' => true,
          'data' => $this->getLastInsertId()
        ];
      }

      return [
        'success' => false,
        'errors' => ['general' => 'No se pudo crear el nivel socioeconómico']
      ];
    } catch (\Exception $e) {
      error_log("Error en createLevel: " . $e->getMessage());
      return [
        'success' => false,
        'errors' => ['general' => 'Error interno del servidor']
      ];
    }
  }


  /**
   * Verifica si hay conflictos de rangos de puntaje con niveles existentes
   * LÓGICA: A mayor puntaje, mejor nivel (alfabéticamente anterior)
   * 
   * @param int $puntaje_minimo Puntaje mínimo a verificar
   * @param string $nivel_nombre Nombre del nivel a crear/editar
   * @param int|null $excludeId ID del nivel a excluir de la verificación
   * @return array Resultado con valid y message
   */
  private function checkScoreConflict($puntaje_minimo, $nivel_nombre, $excludeId = null)
  {
    try {
      $query = "SELECT * FROM nivel_socioeconomico WHERE estado = 1";
      $params = [];

      if ($excludeId !== null) {
        $query .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
      }

      $query .= " ORDER BY puntaje_minimo ASC";

      $resultado = $this->ejecutarConsulta($query, $params);
      $nivelesExistentes = $resultado->fetchAll(PDO::FETCH_OBJ);

      if (empty($nivelesExistentes)) {
        return ['valid' => true, 'message' => ''];
      }

      // 1. Verificar duplicados exactos
      foreach ($nivelesExistentes as $nivel) {
        if ($puntaje_minimo == $nivel->puntaje_minimo) {
          return [
            'valid' => false,
            'message' => "Ya existe un nivel con puntaje mínimo {$puntaje_minimo} (Nivel: {$nivel->nivel})"
          ];
        }
      }

      // 2. Verificar orden lógico: A MAYOR puntaje, MEJOR nivel (alfabéticamente anterior)
      $todosLosNiveles = $nivelesExistentes;

      // Agregar el nuevo nivel temporalmente para validar el orden
      $nuevoNivel = (object)[
        'nivel' => $nivel_nombre,
        'puntaje_minimo' => $puntaje_minimo
      ];
      $todosLosNiveles[] = $nuevoNivel;

      // Ordenar por puntaje ascendente (compatible con PHP 5.4)
      usort($todosLosNiveles, function ($a, $b) {
        if ($a->puntaje_minimo == $b->puntaje_minimo) {
          return 0;
        }
        return ($a->puntaje_minimo < $b->puntaje_minimo) ? -1 : 1;
      });

      // Verificar que el orden de puntajes (ascendente) coincida con orden alfabético (descendente)
      for ($i = 0; $i < count($todosLosNiveles) - 1; $i++) {
        $actual = $todosLosNiveles[$i];
        $siguiente = $todosLosNiveles[$i + 1];

        // Skip si alguno es "Excenta" o niveles especiales
        if ($actual->nivel === 'Excenta' || $siguiente->nivel === 'Excenta') {
          continue;
        }

        // Para niveles alfabéticos (A-Z), verificar que el orden sea correcto
        if (preg_match('/^[A-Z]$/', $actual->nivel) && preg_match('/^[A-Z]$/', $siguiente->nivel)) {
          // El nivel con menor puntaje debe ser alfabéticamente posterior
          if (strcmp($actual->nivel, $siguiente->nivel) <= 0) {
            return [
              'valid' => false,
              'message' => "Orden lógico incorrecto: El nivel '{$actual->nivel}' (puntaje: {$actual->puntaje_minimo}) debe ser alfabéticamente posterior al nivel '{$siguiente->nivel}' (puntaje: {$siguiente->puntaje_minimo}) porque tiene menor puntaje"
            ];
          }
        }
      }

      // 3. Verificar espaciado mínimo entre niveles
      $espaciadoMinimo = 1;
      foreach ($nivelesExistentes as $nivel) {
        if (abs($puntaje_minimo - $nivel->puntaje_minimo) < $espaciadoMinimo && $puntaje_minimo != $nivel->puntaje_minimo) {
          return [
            'valid' => false,
            'message' => "El puntaje debe tener al menos {$espaciadoMinimo} punto(s) de diferencia con el nivel '{$nivel->nivel}' (puntaje: {$nivel->puntaje_minimo})"
          ];
        }
      }

      return ['valid' => true, 'message' => ''];
    } catch (\Exception $e) {
      error_log("Error en checkScoreConflict: " . $e->getMessage());
      return [
        'valid' => false,
        'message' => 'Error al validar conflictos de puntaje'
      ];
    }
  }


  /**
   * Actualiza un nivel socioeconómico existente
   * 
   * @param int $levelId ID del nivel
   * @param array $data Datos a actualizar
   * @return array Resultado con success, data/errors
   */
  public function updateLevel($levelId, $data)
  {
    try {
      // Verificar que el nivel existe
      $nivelExistente = $this->getLevelById($levelId);
      if (!$nivelExistente) {
        return [
          'success' => false,
          'errors' => ['general' => 'Nivel socioeconómico no encontrado']
        ];
      }

      // Definir reglas de validación (solo para campos que se van a actualizar)
      $validar = [];

      if (isset($data['nivel'])) {
        $validar['nivel'] = [
          'requerido' => true,
          'min' => 1,
          'max' => 50,
          'formato' => '[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}',
          'sanitizar' => true
        ];
      }

      if (isset($data['puntaje_minimo'])) {
        $validar['puntaje_minimo'] = [
          'requerido' => true,
          'formato' => 'entero',
          'min_valor' => 0,
          'max_valor' => 1000
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
        error_log("Errores de validación en updateLevel: " . json_encode($validationResult['errors']));
        return [
          'success' => false,
          'errors' => $validationResult['errors']
        ];
      }

      $datosValidados = $validationResult['datos'];

      // Si se está cambiando el nombre, verificar que no exista otro con el mismo nombre
      if (isset($datosValidados['nivel']) && $datosValidados['nivel'] !== $nivelExistente->nivel) {
        $nivelConNombre = $this->getLevelByName($datosValidados['nivel']);
        if ($nivelConNombre && $nivelConNombre->id != $levelId) {
          error_log("Intento de actualizar nivel con nombre duplicado: " . $datosValidados['nivel']);
          return [
            'success' => false,
            'errors' => ['nivel' => 'Ya existe un nivel socioeconómico con este nombre']
          ];
        }
      }

      // Si se está cambiando el puntaje o el nombre, verificar conflictos de rangos y orden lógico
      $verificarConflictos = false;
      $nombreParaValidar = $datosValidados['nivel'] ?: $nivelExistente->nivel;
      $puntajeParaValidar = $datosValidados['puntaje_minimo'] ?: $nivelExistente->puntaje_minimo;

      if (isset($datosValidados['puntaje_minimo']) && $datosValidados['puntaje_minimo'] != $nivelExistente->puntaje_minimo) {
        $verificarConflictos = true;
      }

      if (isset($datosValidados['nivel']) && $datosValidados['nivel'] !== $nivelExistente->nivel) {
        $verificarConflictos = true;
      }

      if ($verificarConflictos) {
        $conflictResult = $this->checkScoreConflict(
          $puntajeParaValidar,
          $nombreParaValidar,
          $levelId // Excluir el nivel actual de la validación
        );

        if (!$conflictResult['valid']) {
          error_log("Conflicto de rangos/orden en actualización: " . $conflictResult['message']);
          return [
            'success' => false,
            'errors' => ['puntaje_minimo' => $conflictResult['message']]
          ];
        }
      }

      // Preparar campos para actualizar
      $camposActualizar = [];

      // Campos permitidos para actualizar
      $camposPermitidos = ['nivel', 'puntaje_minimo', 'estado'];

      foreach ($camposPermitidos as $campo) {
        if (isset($datosValidados[$campo])) {
          $camposActualizar[] = [
            "campo_nombre" => $campo,
            "campo_marcador" => ":{$campo}",
            "campo_valor" => $datosValidados[$campo]
          ];
        }
      }

      // Solo actualizar si hay campos para cambiar
      if (empty($camposActualizar)) {
        return [
          'success' => true,
          'message' => 'No se realizaron cambios'
        ];
      }

      // Campos de auditoría
      $camposActualizar[] = [
        "campo_nombre" => "fecha_modificacion",
        "campo_marcador" => ":fecha_modificacion",
        "campo_valor" => date("Y-m-d H:i:s")
      ];

      if (isset($datosValidados['usuario_modificacion_id'])) {
        $camposActualizar[] = [
          "campo_nombre" => "usuario_modificacion_id",
          "campo_marcador" => ":usuario_modificacion_id",
          "campo_valor" => $datosValidados['usuario_modificacion_id']
        ];
      }

      $condicion = [
        "condicion_campo" => "id",
        "condicion_marcador" => ":level_id",
        "condicion_valor" => $levelId
      ];

      $resultado = $this->actualizarDatos("nivel_socioeconomico", $camposActualizar, $condicion);

      if ($resultado->rowCount() > 0) {
        return [
          'success' => true,
          'data' => $levelId
        ];
      }

      return [
        'success' => false,
        'errors' => ['general' => 'No se pudo actualizar el nivel socioeconómico']
      ];
    } catch (\Exception $e) {
      error_log("Error en updateLevel: " . $e->getMessage());
      return [
        'success' => false,
        'errors' => ['general' => 'Error interno del servidor']
      ];
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
