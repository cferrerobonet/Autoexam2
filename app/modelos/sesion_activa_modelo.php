<?php
/**
 * Modelo SesionActiva - AUTOEXAM2
 * 
 * Maneja todas las operaciones relacionadas con las sesiones activas de usuarios
 * Permite registrar, consultar y controlar las sesiones de los usuarios en el sistema
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */

class SesionActiva {
    private $conexion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->conectarBaseDatos();
    }
    
    /**
     * Devuelve la conexión a la base de datos
     * @return PDO Objeto de conexión a la base de datos
     */
    public function getConexion() {
        return $this->conexion;
    }
    
    /**
     * Establecer conexión con la base de datos
     */
    private function conectarBaseDatos() {
        try {
            $this->conexion = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            // Log del error
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Registra una nueva sesión activa en la base de datos
     * 
     * @param int $idUsuario ID del usuario que inicia sesión
     * @param string $token Token único de seguridad para la sesión
     * @param string $phpSessionId ID de sesión de PHP
     * @param string $ip Dirección IP del cliente
     * @param string $userAgent Información del navegador/dispositivo
     * @return int|bool ID de la sesión creada o false en caso de error
     */
    public function registrarSesion($idUsuario, $token, $phpSessionId, $ip, $userAgent) {
        try {
            $sql = "INSERT INTO sesiones_activas 
                    (id_usuario, token, php_session_id, fecha_inicio, ultima_actividad, ip, user_agent, activa) 
                    VALUES (:id_usuario, :token, :php_session_id, NOW(), NOW(), :ip, :user_agent, 1)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':php_session_id', $phpSessionId, PDO::PARAM_STR);
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->conexion->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al registrar sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el timestamp de última actividad de una sesión
     * 
     * @param string $token Token de la sesión a actualizar
     * @return bool Éxito de la operación
     */
    public function actualizarActividad($token) {
        try {
            $sql = "UPDATE sesiones_activas 
                    SET ultima_actividad = NOW() 
                    WHERE token = :token AND activa = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar actividad de sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cierra una sesión activa (marca como inactiva y registra fecha de fin)
     * 
     * @param string $token Token de la sesión a cerrar
     * @return bool Éxito de la operación
     */
    public function cerrarSesion($token) {
        try {
            $sql = "UPDATE sesiones_activas 
                    SET activa = 0, fecha_fin = NOW() 
                    WHERE token = :token AND activa = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al cerrar sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cierra todas las sesiones activas de un usuario excepto la actual
     * 
     * @param int $idUsuario ID del usuario
     * @param string $tokenActual Token de la sesión actual que no debe cerrarse
     * @return bool Éxito de la operación
     */
    public function cerrarOtrasSesionesUsuario($idUsuario, $tokenActual) {
        try {
            $sql = "UPDATE sesiones_activas 
                    SET activa = 0, fecha_fin = NOW() 
                    WHERE id_usuario = :id_usuario AND token != :token_actual AND activa = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':token_actual', $tokenActual, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al cerrar otras sesiones del usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un usuario tiene sesiones activas
     * 
     * @param int $idUsuario ID del usuario a verificar
     * @return int Número de sesiones activas del usuario
     */
    public function contarSesionesActivas($idUsuario) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM sesiones_activas 
                    WHERE id_usuario = :id_usuario AND activa = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al contar sesiones activas: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtiene todas las sesiones activas de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return array Listado de sesiones activas
     */
    public function obtenerSesionesUsuario($idUsuario) {
        try {
            $sql = "SELECT id_sesion, fecha_inicio, ultima_actividad, ip, user_agent, token 
                    FROM sesiones_activas 
                    WHERE id_usuario = :id_usuario AND activa = 1 
                    ORDER BY ultima_actividad DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener sesiones del usuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todas las sesiones activas del sistema (para administrador)
     * 
     * @param int $limite Número máximo de registros a devolver
     * @param int $offset Desplazamiento para paginación
     * @return array Listado de sesiones activas con información de usuarios
     */
    public function obtenerTodasSesionesActivas($limite = 100, $offset = 0) {
        try {
            $sql = "SELECT sa.id_sesion, sa.id_usuario, sa.fecha_inicio, sa.ultima_actividad, 
                    sa.ip, sa.user_agent, sa.token, u.nombre, u.apellidos, u.correo, u.rol 
                    FROM sesiones_activas sa 
                    JOIN usuarios u ON sa.id_usuario = u.id_usuario 
                    WHERE sa.activa = 1 
                    ORDER BY sa.ultima_actividad DESC 
                    LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener todas las sesiones activas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si un token de sesión es válido
     * 
     * @param string $token Token a verificar
     * @return bool|array Datos de la sesión si es válida o false
     */
    public function verificarToken($token) {
        try {
            $sql = "SELECT id_sesion, id_usuario, token, php_session_id, fecha_inicio, ultima_actividad 
                    FROM sesiones_activas 
                    WHERE token = :token AND activa = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return ($resultado) ? $resultado : false;
        } catch (PDOException $e) {
            error_log("Error al verificar token de sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina sesiones inactivas antiguas (limpieza periódica)
     * 
     * @param int $horasInactividad Horas de inactividad para considerar una sesión como expirada
     * @return int Número de sesiones cerradas
     */
    public function limpiarSesionesInactivas($horasInactividad = 24) {
        try {
            $sql = "UPDATE sesiones_activas 
                    SET activa = 0, fecha_fin = NOW() 
                    WHERE activa = 1 AND ultima_actividad < DATE_SUB(NOW(), INTERVAL :horas HOUR)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':horas', $horasInactividad, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al limpiar sesiones inactivas: " . $e->getMessage());
            return 0;
        }
    }
}
?>
