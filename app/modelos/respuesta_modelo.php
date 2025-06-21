<?php
/**
 * Modelo para la gestión de respuestas
 * 
 * Este archivo contiene la clase Respuesta que gestiona todas las operaciones
 * relacionadas con respuestas de preguntas tipo test en la base de datos.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class Respuesta {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene una respuesta por su ID
     * 
     * @param int $id_respuesta ID de la respuesta
     * @return array|bool Datos de la respuesta o false si no existe
     */
    public function obtenerPorId($id_respuesta) {
        try {
            $query = "SELECT * FROM respuestas WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_respuesta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener respuesta: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/respuestas_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todas las respuestas de una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @param bool $aleatorio Si devolver en orden aleatorio
     * @return array Lista de respuestas
     */
    public function obtenerPorPregunta($id_pregunta, $aleatorio = false) {
        try {
            $orden = $aleatorio ? "RAND()" : "orden ASC, id_respuesta ASC";
            
            $query = "SELECT * FROM respuestas 
                      WHERE id_pregunta = ? 
                      ORDER BY $orden";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener respuestas por pregunta: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear una nueva respuesta
     * 
     * @param array $datos Datos de la respuesta
     * @return int|bool ID de la respuesta creada o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO respuestas (id_pregunta, texto, correcta, media_tipo, 
                                            media_valor, orden) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isissi", 
                $datos['id_pregunta'],
                $datos['texto'],
                $datos['correcta'] ?? 0,
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['orden'] ?? 0
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear respuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una respuesta existente
     * 
     * @param array $datos Datos de la respuesta incluyendo ID
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE respuestas SET 
                      texto = ?, correcta = ?, media_tipo = ?, media_valor = ?, orden = ?
                      WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sissii", 
                $datos['texto'],
                $datos['correcta'] ?? 0,
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['orden'] ?? 0,
                $datos['id_respuesta']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar respuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una respuesta
     * 
     * @param int $id_respuesta ID de la respuesta a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($id_respuesta) {
        try {
            $query = "DELETE FROM respuestas WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_respuesta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar respuesta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar respuestas de una pregunta a otra
     * 
     * @param int $id_pregunta_origen ID de la pregunta origen
     * @param int $id_pregunta_destino ID de la pregunta destino
     * @return bool True si se duplicaron correctamente
     */
    public function duplicarDePregunta($id_pregunta_origen, $id_pregunta_destino) {
        try {
            $this->db->begin_transaction();
            
            // Obtener respuestas de la pregunta origen
            $respuestas = $this->obtenerPorPregunta($id_pregunta_origen);
            
            foreach ($respuestas as $respuesta) {
                $nueva_respuesta = [
                    'id_pregunta' => $id_pregunta_destino,
                    'texto' => $respuesta['texto'],
                    'correcta' => $respuesta['correcta'],
                    'media_tipo' => $respuesta['media_tipo'],
                    'media_valor' => $respuesta['media_valor'],
                    'orden' => $respuesta['orden']
                ];
                
                if (!$this->crear($nueva_respuesta)) {
                    throw new Exception("Error al duplicar respuesta");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al duplicar respuestas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Importar respuestas del banco a una pregunta
     * 
     * @param int $id_pregunta_banco ID de la pregunta en el banco
     * @param int $id_pregunta_destino ID de la pregunta destino
     * @return bool True si se importaron correctamente
     */
    public function importarDelBanco($id_pregunta_banco, $id_pregunta_destino) {
        try {
            $this->db->begin_transaction();
            
            // Obtener respuestas del banco
            $query = "SELECT * FROM respuestas_banco WHERE id_pregunta = ? ORDER BY id_respuesta ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta_banco);
            $stmt->execute();
            $respuestas_banco = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($respuestas_banco as $respuesta) {
                $nueva_respuesta = [
                    'id_pregunta' => $id_pregunta_destino,
                    'texto' => $respuesta['texto'],
                    'correcta' => $respuesta['correcta'],
                    'media_tipo' => $respuesta['media_tipo'],
                    'media_valor' => $respuesta['media_valor'],
                    'orden' => 0
                ];
                
                if (!$this->crear($nueva_respuesta)) {
                    throw new Exception("Error al importar respuesta del banco");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al importar respuestas del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear múltiples respuestas para una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @param array $respuestas Array de respuestas
     * @return bool True si se crearon correctamente
     */
    public function crearMultiples($id_pregunta, $respuestas) {
        try {
            $this->db->begin_transaction();
            
            // Primero eliminar respuestas existentes
            $this->eliminarPorPregunta($id_pregunta);
            
            // Crear nuevas respuestas
            foreach ($respuestas as $orden => $respuesta) {
                $datos = [
                    'id_pregunta' => $id_pregunta,
                    'texto' => $respuesta['texto'],
                    'correcta' => isset($respuesta['correcta']) ? 1 : 0,
                    'media_tipo' => $respuesta['media_tipo'] ?? 'ninguno',
                    'media_valor' => $respuesta['media_valor'] ?? null,
                    'orden' => $orden
                ];
                
                if (!$this->crear($datos)) {
                    throw new Exception("Error al crear respuesta múltiple");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al crear respuestas múltiples: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar todas las respuestas de una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return bool True si se eliminaron correctamente
     */
    public function eliminarPorPregunta($id_pregunta) {
        try {
            $query = "DELETE FROM respuestas WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar respuestas por pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar orden de las respuestas
     * 
     * @param array $orden_respuestas Array con IDs en el nuevo orden
     * @return bool True si se actualizó correctamente
     */
    public function actualizarOrden($orden_respuestas) {
        try {
            $this->db->begin_transaction();
            
            foreach ($orden_respuestas as $orden => $id_respuesta) {
                $query = "UPDATE respuestas SET orden = ? WHERE id_respuesta = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ii", $orden, $id_respuesta);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar orden de respuestas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si una respuesta es correcta
     * 
     * @param int $id_respuesta ID de la respuesta
     * @return bool True si es correcta
     */
    public function esCorrecta($id_respuesta) {
        try {
            $query = "SELECT correcta FROM respuestas WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_respuesta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $respuesta = $resultado->fetch_assoc();
                return (bool)$respuesta['correcta'];
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al verificar respuesta correcta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener respuestas correctas de una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return array Lista de respuestas correctas
     */
    public function obtenerCorrectas($id_pregunta) {
        try {
            $query = "SELECT * FROM respuestas 
                      WHERE id_pregunta = ? AND correcta = 1 
                      ORDER BY orden ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener respuestas correctas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar respuestas de una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return array Contadores por tipo
     */
    public function contarPorPregunta($id_pregunta) {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(correcta) as correctas,
                        COUNT(*) - SUM(correcta) as incorrectas
                      FROM respuestas 
                      WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al contar respuestas: " . $e->getMessage());
            return [];
        }
    }
}
