<?php

namespace App\Models;

use PDO;
use App\Models\mainModel;

class userModel extends mainModel
{
    /**
     * Registra un nuevo usuario
     * 
     * @param string $nombre Nombre del usuario
     * @param string $apellidoPaterno Apellido paterno
     * @param string $apellidoMaterno Apellido materno
     * @param string $telefono Teléfono
     * @param string $correo Correo electrónico
     * @param string $username Nombre de usuario
     * @param string $password Hash de la contraseña
     * @param string $foto Nombre del archivo de foto
     * @param int $rol ID del rol
     * @return bool True si el registro fue exitoso, false en caso contrario
     */
    public function registrarUsuario($data)
    {
        try {
            // Mapear las claves del array asociativo a los nombres de los campos en la BD
            $datosParaInsertar = [
                'usuario_nombre' => $data['nombre'],
                'usuario_apellido_paterno' => $data['apellidoPaterno'],
                'usuario_apellido_materno' => $data['apellidoMaterno'],
                'usuario_telefono' => $data['telefono'],
                'usuario_email' => $data['correo'],
                'usuario_usuario' => $data['username'],
                'usuario_password_hash' => $this->hashearContraseña($data['password']),
                'usuario_avatar' => $data['avatar'] ? $data['avatar'] : "default.png",
                'usuario_rol' => $data['rol'],
                'usuario_estado' => 1,
                'usuario_fecha_creacion' => date("Y-m-d H:i:s", time()),
                'usuario_ultima_modificacion' => date("Y-m-d H:i:s", time())
            ];

            $resultado = $this->insertarDatos("usuario", $datosParaInsertar);
            return $resultado->rowCount() == 1;
        } catch (\Exception $e) {
            error_log("Error en registrarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un usuario
     * 
     * @param int $id ID del usuario
     * @param array $datos Datos a actualizar
     * @return bool True si la actualización fue exitosa, false en caso contrario
     */
    public function actualizarUsuario($id, $datos)
    {
        try {
            $camposActualizar = [];

            error_log("Datos recibidos en actualizarUsuario: " . print_r($datos, true));

            // Mapeo de claves de entrada a nombres de columna de BD
            $mapeoColumnas = [
                'nombre' => 'usuario_nombre',
                'apellidoPaterno' => 'usuario_apellido_paterno',
                'apellidoMaterno' => 'usuario_apellido_materno',
                'telefono' => 'usuario_telefono',
                'correo' => 'usuario_email',
                'username' => 'usuario_usuario',
                // 'password' se maneja de forma especial más abajo
                'avatar' => 'usuario_avatar',
                'rol' => 'usuario_rol',
                'estado' => 'usuario_estado'
            ];

            // Construir array de datos a actualizar
            foreach ($datos as $claveEntrada => $valor) {
                if ($claveEntrada === 'password') {
                    // Solo actualizar y hashear la contraseña si se proporciona un valor no vacío
                    if (!empty($valor)) {
                        $camposActualizar[] = [
                            "campo_nombre" => "usuario_password_hash",
                            "campo_marcador" => ":password_hash", // Usar un marcador único para la contraseña
                            "campo_valor" => $this->hashearContraseña($valor)
                        ];
                    }
                    // Si el campo password está vacío en $datos, se ignora (no se actualiza la contraseña)
                } elseif (array_key_exists($claveEntrada, $mapeoColumnas)) {
                    $nombreColumnaBD = $mapeoColumnas[$claveEntrada];
                    $camposActualizar[] = [
                        "campo_nombre" => $nombreColumnaBD,
                        "campo_marcador" => ":" . $claveEntrada, // El marcador puede seguir usando la clave de entrada
                        "campo_valor" => $valor
                    ];
                }
            }

            // Si no hay campos para actualizar (ej. solo se envió un password vacío), no continuar
            if (empty($camposActualizar)) {
                // Opcionalmente, puedes retornar true si consideras que no hacer nada es un "éxito"
                // o false/un mensaje si esperabas alguna actualización.
                // Por ahora, si no hay cambios efectivos, no se añade la fecha de modificación y podría no ejecutar la consulta.
                // Considera el caso en que $datos solo trae un password vacío.
                // Si $camposActualizar está vacío, la llamada a actualizarDatos podría fallar o no hacer nada.
                // Podrías retornar true aquí si es un caso válido no actualizar nada.
                // O, si siempre se debe actualizar 'usuario_ultima_modificacion', manejarlo fuera de este if.
            }

            // Añadir fecha de última modificación solo si hay algo que actualizar
            if (!empty($camposActualizar)) {
                $camposActualizar[] = [
                    "campo_nombre" => "usuario_ultima_modificacion",
                    "campo_marcador" => ":ultimaModificacion",
                    "campo_valor" => date("Y-m-d H:i:s")
                ];

                // Condición para actualizar solo el usuario específico
                $condicion = [
                    "condicion_campo" => "usuario_id",
                    "condicion_marcador" => ":id",
                    "condicion_valor" => $id
                ];

                $resultado = $this->actualizarDatos("usuario", $camposActualizar, $condicion);
                return $resultado->rowCount() > 0;
            } else {
                // Si no hubo campos válidos para actualizar (ej. solo se envió un password vacío y nada más)
                // puedes decidir si esto es un éxito o no.
                // Retornar true podría ser apropiado si no se esperaba un cambio obligatorio.
                return true; // O false, dependiendo de la lógica de negocio.
            }
        } catch (\Exception $e) {
            error_log("Error en actualizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario por su ID
     * 
     * @param int $id ID del usuario
     * @return bool True si la eliminación fue exitosa, false en caso contrario
     */
    public function eliminarUsuario($id)
    {
        try {
            $resultado = $this->eliminarRegistro("usuario", "usuario_id", $id);
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en eliminarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los usuarios con información de roles
     * 
     * @return array Lista de usuarios
     */
    public function obtenerTodosUsuarios()
    {
        try {
            $query = "SELECT u.usuario_id, u.usuario_nombre, u.usuario_apellido_paterno, 
                     u.usuario_apellido_materno, u.usuario_usuario, u.usuario_email, 
                     r.rol_descripcion, u.usuario_estado 
                     FROM usuario u 
                     JOIN rol r ON u.usuario_rol = r.rol_id";

            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerTodosUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un usuario por su ID
     * 
     * @param int $id ID del usuario
     * @return object|false Datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorId($id)
    {
        try {
            $query = "SELECT u.*, r.rol_descripcion 
                     FROM usuario u 
                     JOIN rol r ON u.usuario_rol = r.rol_id 
                     WHERE u.usuario_id = :id";

            $resultado = $this->ejecutarConsulta($query, [':id' => $id]);
            return $resultado->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerUsuarioPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un usuario por su nombre de usuario
     * 
     * @param string $username Nombre de usuario
     * @return object|false Datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorUsername($username)
    {
        try {
            $query = "SELECT u.*, r.rol_descripcion 
                     FROM usuario u
                     JOIN rol r ON u.usuario_rol = r.rol_id 
                     WHERE u.usuario_usuario = :username";

            $resultado = $this->ejecutarConsulta($query, [':username' => $username]);
            return $resultado->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerUsuarioPorUsername: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un usuario por su correo electrónico
     * 
     * @param string $email Correo electrónico
     * @return object|false Datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorEmail($email)
    {
        try {
            $query = "SELECT u.*, r.rol_descripcion 
                     FROM usuario u 
                     JOIN rol r ON u.usuario_rol = r.rol_id 
                     WHERE u.usuario_email = :email";

            $resultado = $this->ejecutarConsulta($query, [':email' => $email]);
            return $resultado->fetch(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerUsuarioPorEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un nombre de usuario ya existe
     * 
     * @param string $username Nombre de usuario
     * @return bool True si existe, false en caso contrario
     */
    public function localizarUsuario($username)
    {
        try {
            $query = "SELECT COUNT(*) FROM usuario WHERE usuario_usuario = :username";
            $resultado = $this->ejecutarConsulta($query, [':username' => $username]);
            return $resultado->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("Error en localizarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Autentica a un usuario con sus credenciales
     * 
     * @param string $identificador Nombre de usuario o correo electrónico
     * @param string $password Contraseña
     * @return object|false Datos del usuario si la autenticación es exitosa, false en caso contrario
     */
    public function autenticarUsuario($identificador, $password)
    {
        try {
            // Sanitizar entrada
            $identificador = $this->sanitizarEntrada($identificador);

            // Determinar si es email o nombre de usuario
            $usuario = null;
            $tipo_usuario = null;
            if (filter_var($identificador, FILTER_VALIDATE_EMAIL)) {
                $tipo_usuario = "email";
                $usuario = $this->obtenerUsuarioPorEmail($identificador);
            } else {
                $tipo_usuario = "username";
                $usuario = $this->obtenerUsuarioPorUsername($identificador);
            }

            // Si no encontramos el usuario o está inactivo
            if (!$usuario || $usuario->usuario_estado != 1) {
                return false;
            }

            // Verificar contraseña con la función del mainModel
            if ($this->validarContraseña($password, $usuario->usuario_password_hash)) {
                return $usuario;
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error en autenticarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los roles disponibles
     * 
     * @return array Lista de roles
     */
    public function getRoles()
    {
        try {
            $query = "SELECT rol_id, rol_descripcion FROM rol WHERE rol_estado = 1";
            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en getRoles: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Actualiza la última fecha de acceso de un usuario
     * 
     * @param int $id ID del usuario
     * @return bool True si la actualización fue exitosa, false en caso contrario
     */
    public function actualizarUltimoAcceso($id)
    {
        try {
            $camposActualizar = [
                [
                    "campo_nombre" => "usuario_ultimo_acceso",
                    "campo_marcador" => ":ultima_actividad",
                    "campo_valor" => date("Y-m-d H:i:s", time())
                ]
            ];

            $condicion = [
                "condicion_campo" => "usuario_id",
                "condicion_marcador" => ":id",
                "condicion_valor" => $id
            ];

            $resultado = $this->actualizarDatos("usuario", $camposActualizar, $condicion);
            return $resultado->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error en actualizarUltimoAcceso: " . $e->getMessage());
            return false;
        }
    }

    //obtener todos los usuarios
    public function obtenerUsuarios()
    {
        try {
            $query = "SELECT u.usuario_id, u.usuario_nombre, u.usuario_apellido_paterno, 
                     u.usuario_apellido_materno, u.usuario_usuario, u.usuario_email, 
                     r.rol_descripcion, u.usuario_estado 
                     FROM usuario u 
                     JOIN rol r ON u.usuario_rol = r.rol_id";

            $resultado = $this->ejecutarConsulta($query);
            return $resultado->fetchAll(PDO::FETCH_OBJ);
        } catch (\Exception $e) {
            error_log("Error en obtenerUsuarios: " . $e->getMessage());
            return [];
        }
    }
}
