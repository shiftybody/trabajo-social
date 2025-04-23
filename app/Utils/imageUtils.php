<?php

namespace App\Utils;

class ImageUtils
{
  /**
   * Redimensiona una imagen manteniendo su proporción
   * 
   * @param string $rutaOriginal Ruta de la imagen original
   * @param string $rutaDestino Ruta donde guardar la imagen redimensionada
   * @param int $anchoMaximo Ancho máximo en píxeles
   * @param int $altoMaximo Alto máximo en píxeles (opcional)
   * @param int $calidad Calidad de la imagen (0-100, solo para JPG)
   * @return bool True si se redimensionó correctamente, False en caso contrario
   */
  public static function createThumbnail($rutaOriginal, $rutaDestino, $anchoMaximo, $altoMaximo = null, $calidad = 80)
  {
    // Si no se especifica alto máximo, usar el mismo que el ancho
    if ($altoMaximo === null) {
      $altoMaximo = $anchoMaximo;
    }

    // Obtener información de la imagen
    $info = getimagesize($rutaOriginal);
    if ($info === false) {
      return false;
    }

    $tipo = $info[2];
    $ancho = $info[0];
    $alto = $info[1];

    // Calcular nuevas dimensiones manteniendo proporción
    $ratioOriginal = $ancho / $alto;

    if ($anchoMaximo / $altoMaximo > $ratioOriginal) {
      $nuevoAncho = $altoMaximo * $ratioOriginal;
      $nuevoAlto = $altoMaximo;
    } else {
      $nuevoAncho = $anchoMaximo;
      $nuevoAlto = $anchoMaximo / $ratioOriginal;
    }

    // Crear imagen desde el original según su tipo
    switch ($tipo) {
      case IMAGETYPE_JPEG:
        $imagenOriginal = imagecreatefromjpeg($rutaOriginal);
        break;
      case IMAGETYPE_PNG:
        $imagenOriginal = imagecreatefrompng($rutaOriginal);
        break;
      case IMAGETYPE_GIF:
        $imagenOriginal = imagecreatefromgif($rutaOriginal);
        break;
      default:
        return false;
    }

    if ($imagenOriginal === false) {
      return false;
    }

    // Crear imagen redimensionada
    $imagenNueva = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

    // Preservar transparencia para PNG y GIF
    if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_GIF) {
      imagecolortransparent($imagenNueva, imagecolorallocatealpha($imagenNueva, 0, 0, 0, 127));
      imagealphablending($imagenNueva, false);
      imagesavealpha($imagenNueva, true);
    }

    // Redimensionar
    imagecopyresampled(
      $imagenNueva,
      $imagenOriginal,
      0,
      0,
      0,
      0,
      $nuevoAncho,
      $nuevoAlto,
      $ancho,
      $alto
    );

    // Guardar imagen según el tipo
    $resultado = false;
    switch ($tipo) {
      case IMAGETYPE_JPEG:
        $resultado = imagejpeg($imagenNueva, $rutaDestino, $calidad);
        break;
      case IMAGETYPE_PNG:
        $pngCalidad = min(floor((100 - $calidad) / 11), 9);
        $resultado = imagepng($imagenNueva, $rutaDestino, $pngCalidad);
        break;
      case IMAGETYPE_GIF:
        $resultado = imagegif($imagenNueva, $rutaDestino);
        break;
    }

    // Liberar memoria
    imagedestroy($imagenOriginal);
    imagedestroy($imagenNueva);

    return $resultado;
  }
}


