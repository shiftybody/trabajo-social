<?php

/**
 * Controlador de Estudios Socioeconómicos
 * 
 * Gestiona los estudios socioeconómicos de los pacientes:
 * - Creación y edición de estudios
 * - Gestión por secciones (Datos Generales, Familia, Salud, Vivienda, Economía)
 * - Cálculo automático de nivel socioeconómico
 * - Manejo de un solo estudio activo por paciente
 */

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\StudyModel;
use App\Models\PatientModel;
use App\Models\SocioeconomicLevelModel;
use App\Models\CriteriaModel;
use Exception;

class StudiesController
{

  /**
   * Vista principal de estudios de un paciente
   */
  public function indexView(Request $request)
  {
    ob_start();
    $titulo = 'Estudios Socioeconómicos';
    include APP_ROOT . 'app/Views/studies/index.php';
    $content = ob_get_clean();
    return Response::html($content);
  }
}
