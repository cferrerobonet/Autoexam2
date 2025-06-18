<?php
/**
 * Modelo Registro de Actividad - AUTOEXAM2
 * 
 * Maneja el registro de todas las acciones del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class RegistroActividad {
    private $conexion;
    
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
            error_log("Error de conexión a la base de datos (Registro): " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Registrar una actividad
     */
    public function registrar($idUsuario, $accion, $descripcion, $modulo = 'usuarios', $elementoId = null) {
        try {
            $sql = "INSERT INTO registro_actividad 
                    (id_usuario, accion, descripcion, ip, user_agent, modulo, elemento_id) 
                    VALUES (:id_usuario, :accion, :descripcion, :ip, :user_agent, :modulo, :elemento_id)";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $idUsuario,
                ':accion' => $accion,
                ':descripcion' => $descripcion,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ':modulo' => $modulo,
                ':elemento_id' => $elementoId
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error al registrar actividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener historial de un usuario específico
     */
    public function obtenerHistorialUsuario($idUsuario, $limite = 50, $offset = 0) {
        try {
            $sql = "SELECT r.*, u.nombre, u.apellidos 
                    FROM registro_actividad r
                    LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
                    WHERE r.elemento_id = :id_usuario AND r.modulo = 'usuarios'
                    ORDER BY r.fecha DESC 
                    LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de actividad
     */
    public function obtenerEstadisticas($fechaInicio = null, $fechaFin = null) {
        try {
            $sql = "SELECT 
                        accion,
                        COUNT(*) as total,
                        DATE(fecha) as fecha_dia
                    FROM registro_actividad 
                    WHERE modulo = 'usuarios'";
            
            $params = [];
            
            if ($fechaInicio) {
                $sql .= " AND fecha >= :fecha_inicio";
                $params[':fecha_inicio'] = $fechaInicio;
            }
            
            if ($fechaFin) {
                $sql .= " AND fecha <= :fecha_fin";
                $params[':fecha_fin'] = $fechaFin;
            }
            
            $sql .= " GROUP BY accion, DATE(fecha) ORDER BY fecha_dia DESC, total DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
