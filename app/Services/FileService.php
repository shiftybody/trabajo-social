<?php

namespace app\Services;

/**
 * Clase FileService
 * 
 * Maneja las operaciones relacionadas con archivos
 * como subida, eliminación y validación
 */
class FileService
{
  private $uploadDir;
  private $allowedTypes;
  private $maxSize;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->uploadDir = "../views/fotos/";
    $this->allowedTypes = [
      "image/jpeg",
      "image/jpg",
      "image/png",
      "image/gif"
    ];
    $this->maxSize = 5 * 1024 * 1024; // 5MB
  }

  /**
   * Sube una imagen al servidor
   * 
   * @param array $file Archivo subido ($_FILES['campo'])
   * @param string $baseFilename Base para el nombre del archivo
   * @return array Resultado de la operación
   */
  public function subirImagen($file, $baseFilename)
  {
    // Verificar si el archivo está vacío
    if ($file['name'] == "" || $file['size'] <= 0) {
      return ['filename' => ''];
    }

    // Crear directorio si no existe
    if (!$this->verificarDirectorio()) {
      return [
        'error' => [
          'titulo' => 'Error en el directorio',
          'texto' => 'No se pudo crear el directorio de imágenes'
        ]
      ];
    }

    // Verificar tipo de archivo
    if (!$this->verificarTipoArchivo($file['tmp_name'])) {
      return [
        'error' => [
          'titulo' => 'Error en la imagen',
          'texto' => 'Solo se permiten imágenes jpg, png o gif'
        ]
      ];
    }

    // Verificar tamaño del archivo
    if (!$this->verificarTamañoArchivo($file['size'])) {
      return [
        'error' => [
          'titulo' => 'Error en la imagen',
          'texto' => 'La imagen es muy pesada (máximo 5MB)'
        ]
      ];
    }

    // Generar nombre único para el archivo
    $filename = $this->generarNombreArchivo($file, $baseFilename);

    // Mover archivo a directorio final
    if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $filename)) {
      return [
        'error' => [
          'titulo' => 'Error al subir la imagen',
          'texto' => 'No se pudo guardar la imagen'
        ]
      ];
    }

    return ['filename' => $filename];
  }

  /**
   * Elimina una imagen del servidor
   * 
   * @param string $filename Nombre del archivo a eliminar
   * @return bool True si se eliminó correctamente, false en caso contrario
   */
  public function eliminarImagen($filename)
  {
    if (empty($filename)) {
      return true;
    }

    $filepath = $this->uploadDir . $filename;

    if (is_file($filepath)) {
      chmod($filepath, 0777);
      return unlink($filepath);
    }

    return false;
  }

  /**
   * Verifica que el directorio de subida exista, lo crea si no existe
   * 
   * @return bool True si el directorio existe o se pudo crear, false en caso contrario
   */
  private function verificarDirectorio()
  {
    if (!file_exists($this->uploadDir)) {
      return mkdir($this->uploadDir, 0777, true);
    }
    return true;
  }

  /**
   * Verifica que el tipo de archivo sea válido
   * 
   * @param string $tmpPath Ruta temporal del archivo
   * @return bool True si el tipo es válido, false en caso contrario
   */
  private function verificarTipoArchivo($tmpPath)
  {
    $mimeType = mime_content_type($tmpPath);
    return in_array($mimeType, $this->allowedTypes);
  }

  /**
   * Verifica que el tamaño del archivo no exceda el máximo permitido
   * 
   * @param int $size Tamaño del archivo en bytes
   * @return bool True si el tamaño es válido, false en caso contrario
   */
  private function verificarTamañoArchivo($size)
  {
    return $size <= $this->maxSize;
  }

  /**
   * Genera un nombre único para el archivo
   * 
   * @param array $file Archivo subido
   * @param string $baseFilename Base para el nombre del archivo
   * @return string Nombre generado con extensión
   */
  private function generarNombreArchivo($file, $baseFilename)
  {
    // Limpiar nombre base
    $baseFilename = str_ireplace(" ", "_", $baseFilename);

    // Añadir número aleatorio para evitar colisiones
    $filename = $baseFilename . "_" . rand(0, 100);

    // Añadir extensión según tipo de archivo
    $mimeType = mime_content_type($file['tmp_name']);

    switch ($mimeType) {
      case "image/jpeg":
      case "image/jpg":
        $filename .= ".jpg";
        break;
      case "image/png":
        $filename .= ".png";
        break;
      case "image/gif":
        $filename .= ".gif";
        break;
      default:
        $filename .= ".jpg";
    }

    return $filename;
  }
}


