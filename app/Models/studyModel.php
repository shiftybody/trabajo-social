<?php

namespace App\Models;

use PDO;
use Exception;
use App\Models\mainModel;
use App\Core\Auth;

/**
 * Modelo para manejar estudios socioeconómicos
 */
class StudyModel extends mainModel
{
  /**
   * Obtiene todos los pacientes con información de sus estudios
   */
  public function getPatientsWithStudyInfo()
  {
    try {
      $query = "
            SELECT 
                p.id,
                p.codigo,
                CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                p.fecha_nacimiento,
                -- Cálculo de edad completa en años, meses y días
                CONCAT(
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()), ' años, ',
                    TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()) % 12, ' meses, ',
                    DATEDIFF(
                        CURDATE(), 
                        DATE_ADD(
                            DATE_ADD(p.fecha_nacimiento, 
                                INTERVAL TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) YEAR
                            ), 
                            INTERVAL TIMESTAMPDIFF(MONTH, p.fecha_nacimiento, CURDATE()) % 12 MONTH
                        )
                    ), ' días'
                ) as edad_completa,
                -- Estudio activo (estado = 1)
                ea.id as estudio_activo_id,
                ea.folio as folio_activo,
                ea.estado as estudio_estado,
                -- Nivel socioeconómico
                ns.nivel as nivel_socioeconomico,
                ns.id as nivel_id,
                -- Contadores
                (SELECT COUNT(*) FROM estudios_socioeconomicos WHERE paciente_id = p.id) as total_estudios,
                CASE 
                    WHEN ea.id IS NOT NULL AND ea.estado = 1 THEN 'CON_ESTUDIO_ACTIVO'
                    WHEN (SELECT COUNT(*) FROM estudios_socioeconomicos WHERE paciente_id = p.id) > 0 THEN 'CON_ESTUDIOS_INACTIVOS'
                    ELSE 'SIN_ESTUDIOS'
                END as estado_estudios
            FROM paciente p
            LEFT JOIN estudios_socioeconomicos ea ON p.id = ea.paciente_id AND ea.estado = 1
            LEFT JOIN nivel_socioeconomico ns ON ea.nivel_socioeconomico_id = ns.id
            WHERE p.estado_paciente = 1 -- SOLO pacientes activos
            ORDER BY p.apellido_paterno, p.apellido_materno, p.nombre
        ";

      $resultado = $this->ejecutarConsulta($query);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getPatientsWithStudyInfo: " . $e->getMessage());
      throw new Exception("Error al obtener información de pacientes");
    }
  }

  /**
   * Genera un folio único para un nuevo estudio
   */
  public function generateUniqueFolio()
  {
    try {
      // Obtener el último folio utilizado
      $query = "SELECT folio FROM estudios_socioeconomicos 
                     WHERE folio LIKE 'F%' 
                     ORDER BY id DESC LIMIT 1";

      $resultado = $this->ejecutarConsulta($query);
      $ultimoFolio = $resultado->fetch(PDO::FETCH_OBJ);

      if ($ultimoFolio) {
        // Extraer el número del folio (F000001 -> 1)
        $numero = intval(substr($ultimoFolio->folio, 1));
        $nuevoNumero = $numero + 1;
      } else {
        $nuevoNumero = 1;
      }

      // Formatear con ceros a la izquierda (F000001)
      return 'F' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
      error_log("Error en generateUniqueFolio: " . $e->getMessage());
      throw new Exception("Error al generar folio único");
    }
  }

  /**
   * Obtiene todos los estudios de un paciente
   */
  public function getStudiesByPatient($patientId)
  {
    try {
      $query = "
                SELECT 
                    e.id,
                    e.folio,
                    e.fecha_estudio,
                    e.estado,
                    e.puntaje_total,
                    ns.nivel as nivel_socioeconomico,
                    ns.id as nivel_id,
                    e.fecha_creacion,
                    u.usuario_nombre as creado_por
                FROM estudios_socioeconomicos e
                LEFT JOIN nivel_socioeconomico ns ON e.nivel_socioeconomico_id = ns.id
                LEFT JOIN usuario u ON e.usuario_creacion_id = u.usuario_id
                WHERE e.paciente_id = :patient_id
                ORDER BY e.fecha_creacion DESC
            ";

      $resultado = $this->ejecutarConsulta($query, [':patient_id' => $patientId]);
      return $resultado->fetchAll(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getStudiesByPatient: " . $e->getMessage());
      throw new Exception("Error al obtener estudios del paciente");
    }
  }

  /**
   * Obtiene un estudio específico con todos sus datos
   */
  public function getStudyById($studyId)
  {
    try {
      $query = "
                SELECT 
                    e.*,
                    p.nombre as paciente_nombre,
                    p.apellido_paterno,
                    p.apellido_materno,
                    p.codigo as paciente_codigo,
                    ns.nivel as nivel_socioeconomico,
                    ns.id as nivel_id
                FROM estudios_socioeconomicos e
                JOIN paciente p ON e.paciente_id = p.id
                LEFT JOIN nivel_socioeconomico ns ON e.nivel_socioeconomico_id = ns.id
                WHERE e.id = :study_id
            ";

      $resultado = $this->ejecutarConsulta($query, [':study_id' => $studyId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getStudyById: " . $e->getMessage());
      throw new Exception("Error al obtener datos del estudio");
    }
  }

  /**
   * Crea un nuevo estudio socioeconómico
   */
  public function createStudy($data)
  {
    try {
      // Desactivar otros estudios del paciente (estado = 0)
      $this->deactivatePatientStudies($data['paciente_id']);

      // Datos para insertar
      $datosEstudio = [
        'folio' => $this->generateUniqueFolio(),
        'paciente_id' => $data['paciente_id'],
        'fecha_estudio' => $data['fecha_estudio'] ?: date('Y-m-d'),
        'estado' => 1, // Activo
        'puntaje_total' => null,
        'nivel_socioeconomico_id' => null,
        'notas' => $data['notas'] ?: null,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'fecha_modificacion' => date('Y-m-d H:i:s'),
        'usuario_creacion_id' => Auth::user()->usuario_id,
        'usuario_modificacion_id' => Auth::user()->usuario_id
      ];

      $resultado = $this->insertarDatos('estudios_socioeconomicos', $datosEstudio);

      if ($resultado->rowCount() > 0) {
        return $this->getLastInsertId();
      }

      throw new Exception("No se pudo crear el estudio");
    } catch (Exception $e) {
      error_log("Error en createStudy: " . $e->getMessage());
      throw new Exception("Error al crear el estudio socioeconómico");
    }
  }

  /**
   * Desactiva todos los estudios de un paciente
   */
  private function deactivatePatientStudies($patientId)
  {
    try {
      $query = "UPDATE estudios_socioeconomicos 
                     SET estado = 0, 
                         fecha_modificacion = NOW(),
                         usuario_modificacion_id = :user_id
                     WHERE paciente_id = :patient_id";

      $this->ejecutarConsulta($query, [
        ':patient_id' => $patientId,
        ':user_id' => Auth::user()->usuario_id
      ]);
    } catch (Exception $e) {
      error_log("Error en deactivatePatientStudies: " . $e->getMessage());
      throw new Exception("Error al desactivar estudios anteriores");
    }
  }

  /**
   * Activa un estudio específico
   */
  public function activateStudy($studyId, $patientId)
  {
    try {
      // Primero desactivar todos los estudios del paciente
      $this->deactivatePatientStudies($patientId);

      // Activar el estudio específico
      $query = "UPDATE estudios_socioeconomicos 
                     SET estado = 1,
                         fecha_modificacion = NOW(),
                         usuario_modificacion_id = :user_id
                     WHERE id = :study_id AND paciente_id = :patient_id";

      $resultado = $this->ejecutarConsulta($query, [
        ':study_id' => $studyId,
        ':patient_id' => $patientId,
        ':user_id' => Auth::user()->usuario_id
      ]);

      return $resultado->rowCount() > 0;
    } catch (Exception $e) {
      error_log("Error en activateStudy: " . $e->getMessage());
      throw new Exception("Error al activar el estudio");
    }
  }

  /**
   * Copia un estudio anterior para crear uno nuevo
   */
  public function copyStudy($originalStudyId, $patientId)
  {
    try {
      // Obtener datos del estudio original
      $original = $this->getStudyById($originalStudyId);

      if (!$original || $original->paciente_id != $patientId) {
        throw new Exception("Estudio original no encontrado");
      }

      // Crear nuevo estudio base
      $newStudyData = [
        'paciente_id' => $patientId,
        'fecha_estudio' => date('Y-m-d'),
        'notas' => 'Copiado del estudio anterior: ' . $original->folio
      ];

      $newStudyId = $this->createStudy($newStudyData);

      // Aquí se copiarían las secciones del estudio original
      // TODO: Implementar copia de secciones en fases posteriores
      // - familia
      // - integrante_familia  
      // - salud
      // - pariente_salud
      // - alimentacion
      // - vivienda
      // - economia
      // - aportador_economia

      return $newStudyId;
    } catch (Exception $e) {
      error_log("Error en copyStudy: " . $e->getMessage());
      throw new Exception("Error al copiar el estudio");
    }
  }
}
