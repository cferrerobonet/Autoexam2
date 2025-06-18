<?php
/**
 * Modelo TokenRecuperacion - AUTOEXAM2
 * 
 * Maneja todas las operaciones relacionadas con los tokens de recuperación de contraseña
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */

class TokenRecuperacion {
    private $conexion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->conectarBaseDatos();
    }
    
    /**
     * Establecer conexión con la base de datos
     */
    private function conectarBaseDatos() {
        try {
            $this->conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Crear un nuevo token de recuperación para un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return string|false Token generado o false en caso de error
     */
    public function crearToken($idUsuario) {
        try {
            // Generar token único y seguro
            $token = bin2hex(random_bytes(32));
            
            // Desactivar tokens anteriores del usuario
            $this->desactivarTokensAnteriores($idUsuario);
            
            // Insertar en la base de datos
            $sql = "INSERT INTO tokens_recuperacion (id_usuario, token, fecha_creacion, usado) 
                    VALUES (:id_usuario, :token, NOW(), 0)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $token;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear token de recuperación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desactivar tokens anteriores del usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return bool Éxito de la operación
     */
    private function desactivarTokensAnteriores($idUsuario) {
        try {
            $sql = "UPDATE tokens_recuperacion SET usado = 1 
                    WHERE id_usuario = :id_usuario AND usado = 0";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al desactivar tokens anteriores: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar un token de recuperación
     * 
     * @param string $token Token de recuperación
     * @return array|false Datos del usuario asociado o false si el token no es válido
     */
    public function validarToken($token) {
        try {
            // Verificar token y obtener usuario
            $sql = "SELECT t.id_token, t.id_usuario, t.fecha_creacion, 
                           u.nombre, u.apellidos, u.correo, u.rol
                    FROM tokens_recuperacion t
                    JOIN usuarios u ON t.id_usuario = u.id_usuario
                    WHERE t.token = :token AND t.usado = 0 AND u.activo = 1";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                // Verificar si el token ha expirado (24 horas)
                $fechaCreacion = new DateTime($resultado['fecha_creacion']);
                $ahora = new DateTime();
                $diferencia = $ahora->diff($fechaCreacion);
                
                // Si han pasado más de 24 horas
                if ($diferencia->days >= 1) {
                    $this->marcarComoUsado($resultado['id_token']);
                    return false;
                }
                
                return $resultado;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al validar token: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar un token como usado
     * 
     * @param int $idToken ID del token
     * @return bool Éxito de la operación
     */
    public function marcarComoUsado($idToken) {
        try {
            $sql = "UPDATE tokens_recuperacion SET usado = 1 WHERE id_token = :id_token";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_token', $idToken, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al marcar token como usado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar tokens expirados
     * 
     * @return int Número de tokens eliminados
     */
    public function limpiarTokensExpirados() {
        try {
            $sql = "UPDATE tokens_recuperacion 
                    SET usado = 1
                    WHERE usado = 0 AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error al limpiar tokens expirados: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener los tokens activos del sistema
     * 
     * @return array|false Lista de tokens activos o false en caso de error
     */
    public function obtenerTokensActivos() {
        try {
            $stmt = $this->conexion->prepare("
                SELECT * FROM tokens_recuperacion
                WHERE usado = 0 AND fecha_expiracion > NOW()
                ORDER BY fecha_creacion DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener tokens activos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener tokens de recuperación de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return array|false Lista de tokens del usuario o false en caso de error
     */
    public function obtenerTokensUsuario($idUsuario) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT * FROM tokens_recuperacion
                WHERE id_usuario = :id_usuario
                ORDER BY fecha_creacion DESC
                LIMIT 5
            ");
            
            $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener tokens del usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>
