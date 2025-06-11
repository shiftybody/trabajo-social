<?php

namespace App\Models;

use PDO;
use App\Models\mainModel;

class patientModel extends mainModel
{
    private static function getDB()
    {
        return Database::getInstance()->getConnection();
    }

    public static function getAll()
    {
        $stmt = self::getDB()->query("SELECT * FROM paciente");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id)
    {
        $stmt = self::getDB()->prepare("SELECT * FROM paciente WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear($datos)
    {
        $db = self::getDB();
        $sql = "INSERT INTO paciente (
            id, codigo, nombre, apellido_paterno, apellido_materno,
            fecha_nacimiento, edad_corregida, lugar_nacimiento, protocolo,
            fecha_ingreso, instituto_procedencia, calle, numero, colonia, municipio,
            cp, entidad_federativa, tiempo_traslado, gasto_traslado,
            fecha_creacion, fecha_modificacion, usuario_creacion_id, usuario_modificacion_id
        ) VALUES (
            :id, :codigo, :nombre, :apellido_paterno, :apellido_materno,
            :fecha_nacimiento, :edad_corregida, :lugar_nacimiento, :protocolo,
            :fecha_ingreso, :instituto_procedencia, :calle, :numero, :colonia, :municipio,
            :cp, :entidad_federativa, :tiempo_traslado, :gasto_traslado,
            :fecha_creacion, :fecha_modificacion, :usuario_creacion_id, :usuario_modificacion_id
        )";

        $stmt = $db->prepare($sql);
        return $stmt->execute($datos);
    }

    public static function actualizar($id, $datos)
    {
        $db = self::getDB();
        $sql = "UPDATE paciente SET
            codigo = :codigo,
            nombre = :nombre,
            apellido_paterno = :apellido_paterno,
            apellido_materno = :apellido_materno,
            fecha_nacimiento = :fecha_nacimiento,
            edad_corregida = :edad_corregida,
            lugar_nacimiento = :lugar_nacimiento,
            protocolo = :protocolo,
            fecha_ingreso = :fecha_ingreso,
            instituto_procedencia = :instituto_procedencia,
            calle = :calle,
            numero = :numero,
            colonia = :colonia,
            municipio = :municipio,
            cp = :cp,
            entidad_federativa = :entidad_federativa,
            tiempo_traslado = :tiempo_traslado,
            gasto_traslado = :gasto_traslado,
            fecha_modificacion = :fecha_modificacion,
            usuario_modificacion_id = :usuario_modificacion_id
        WHERE id = :id";

        $datos['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($datos);
    }

    public static function eliminar($id)
    {
        $stmt = self::getDB()->prepare("DELETE FROM paciente WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
