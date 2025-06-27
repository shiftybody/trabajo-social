<?php

namespace App\Models;

use PDO;
use Exception;
use App\Models\mainModel;

/**
 * Modelo para manejar la información de los pacientes
 */
class PatientModel extends mainModel
{

  public function getAllPatientsWithDetails($filters = [])
  {
    try {
      $conditions = ["p.estado_paciente = 1"]; // SOLO pacientes activos
      $params = [];

      // Aplicar filtros
      if (!empty($filters['search'])) {
        $conditions[] = "(p.codigo LIKE :search OR 
                                CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) LIKE :search)";
        $params[':search'] = '%' . $filters['search'] . '%';
      }

      $whereClause = implode(' AND ', $conditions);

      // Calcular total de registros
      $countQuery = "SELECT COUNT(*) FROM paciente p WHERE $whereClause";
      $totalRecords = $this->ejecutarConsulta($countQuery, $params)->fetchColumn();

      // Calcular paginación
      $page = max(1, $filters['page'] ?: 1);
      $limit = min(100, max(10, $filters['limit'] ?: 50));
      $offset = ($page - 1) * $limit;
      $totalPages = ceil($totalRecords / $limit);

      // Query principal
      $query = "
                SELECT 
                    p.id,
                    p.codigo,
                    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                    p.fecha_nacimiento,
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad,
                    p.protocolo,
                    p.instituto_procedencia
                FROM paciente p
                WHERE $whereClause
                ORDER BY p.apellido_paterno, p.apellido_materno, p.nombre
                LIMIT :limit OFFSET :offset
            ";

      $params[':limit'] = $limit;
      $params[':offset'] = $offset;

      $resultado = $this->ejecutarConsulta($query, $params);
      $patients = $resultado->fetchAll(PDO::FETCH_OBJ);

      return [
        'patients' => $patients,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages
      ];
    } catch (Exception $e) {
      error_log("Error en getAllPatientsWithDetails: " . $e->getMessage());
      throw new Exception("Error al obtener lista de pacientes");
    }
  }

  /**
   * Obtiene un paciente por su ID (verificando que esté activo)
   */
  public function getPatientById($patientId)
  {
    try {
      $query = "
                SELECT 
                    p.*,
                    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad
                FROM paciente p
                WHERE p.id = :patient_id AND p.estado_paciente = 1
            ";

      $resultado = $this->ejecutarConsulta($query, [':patient_id' => $patientId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getPatientById: " . $e->getMessage());
      throw new Exception("Error al obtener datos del paciente");
    }
  }

  /**
   * Obtiene un paciente con todos sus detalles (verificando que esté activo)
   */
  public function getPatientWithDetails($patientId)
  {
    try {
      $query = "
                SELECT 
                    p.*,
                    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad,
                    -- Estudio activo
                    ea.id as estudio_activo_id,
                    ea.folio as folio_activo,
                    ns.nivel as nivel_socioeconomico
                FROM paciente p
                LEFT JOIN estudios_socioeconomicos ea ON p.id = ea.paciente_id AND ea.estado = 1
                LEFT JOIN nivel_socioeconomico ns ON ea.nivel_socioeconomico_id = ns.id
                WHERE p.id = :patient_id AND p.estado_paciente = 1
            ";

      $resultado = $this->ejecutarConsulta($query, [':patient_id' => $patientId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getPatientWithDetails: " . $e->getMessage());
      throw new Exception("Error al obtener detalles del paciente");
    }
  }

  /**
   * Obtiene el estudio activo de un paciente (verificando que el paciente esté activo)
   */
  public function getActiveStudy($patientId)
  {
    try {
      $query = "
                SELECT 
                    e.*,
                    ns.nivel as nivel_socioeconomico
                FROM estudios_socioeconomicos e
                JOIN paciente p ON e.paciente_id = p.id
                LEFT JOIN nivel_socioeconomico ns ON e.nivel_socioeconomico_id = ns.id
                WHERE e.paciente_id = :patient_id 
                AND e.estado = 1 
                AND p.estado_paciente = 1
            ";

      $resultado = $this->ejecutarConsulta($query, [':patient_id' => $patientId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getActiveStudy: " . $e->getMessage());
      throw new Exception("Error al obtener estudio activo");
    }
  }

  /**
   * Obtiene datos para cálculo de aportación (verificando que el paciente esté activo)
   */
  public function getContributionData($patientId)
  {
    try {
      $query = "
                SELECT 
                    p.id,
                    p.codigo,
                    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) as nombre_completo,
                    TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) as edad,
                    ns.nivel as nivel_socioeconomico,
                    ns.id as nivel_id
                FROM paciente p
                LEFT JOIN estudios_socioeconomicos ea ON p.id = ea.paciente_id AND ea.estado = 1
                LEFT JOIN nivel_socioeconomico ns ON ea.nivel_socioeconomico_id = ns.id
                WHERE p.id = :patient_id AND p.estado_paciente = 1
            ";

      $resultado = $this->ejecutarConsulta($query, [':patient_id' => $patientId]);
      return $resultado->fetch(PDO::FETCH_OBJ);
    } catch (Exception $e) {
      error_log("Error en getContributionData: " . $e->getMessage());
      throw new Exception("Error al obtener datos de aportación");
    }
  }
}
