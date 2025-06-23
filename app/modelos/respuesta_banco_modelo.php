<?php
/**
 * Modelo para la gestión de respuestas del banco
 * 
 * Este archivo contiene la clase RespuestaBanco que gestiona todas las operaciones
 * relacionadas con respuestas de preguntas tipo test del banco global.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class RespuestaBanco {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene una respuesta del banco por su ID
     * 
     * @param int $id_respuesta ID de la respuesta
     * @return array|bool Datos de la respuesta o false si no existe
     */
    public function obtenerPorId($id_respuesta) {
        try {
            $query = "SELECT * FROM respuestas_banco WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_respuesta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener respuesta del banco: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/banco_preguntas_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todas las respuestas de una pregunta del banco
     * 
     * @param int $id_pregunta ID de la pregunta
     * @param bool $aleatorio Si devolver en orden aleatorio
     * @return array Lista de respuestas
     */
    public function obtenerPorPregunta($id_pregunta, $aleatorio = false) {
        try {
            $orden = $aleatorio ? "RAND()" : "id_respuesta ASC";
            
            $query = "SELECT * FROM respuestas_banco 
                      WHERE id_pregunta = ? 
                      ORDER BY $orden";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener respuestas del banco por pregunta: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear una nueva respuesta en el banco
     * 
     * @param array $datos Datos de la respuesta
     * @return int|bool ID de la respuesta creada o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO respuestas_banco (id_pregunta, texto, correcta, media_tipo, media_valor) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            
            // Asegurar valores por defecto correctos
            $correcta = isset($datos['correcta']) ? (int)$datos['correcta'] : 0;
            $media_tipo = $datos['media_tipo'] ?? 'ninguno';
            $media_valor = isset($datos['media_valor']) && $datos['media_valor'] !== null ? $datos['media_valor'] : '';
            
            $stmt->bind_param("isiss", 
                $datos['id_pregunta'],
                $datos['texto'],
                $correcta,
                $media_tipo,
                $media_valor
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear respuesta en banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una respuesta del banco
     * 
     * @param array $datos Datos de la respuesta incluyendo ID
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE respuestas_banco SET 
                      texto = ?, correcta = ?, media_tipo = ?, media_valor = ?
                      WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sissi", 
                $datos['texto'],
                $datos['correcta'] ?? 0,
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['id_respuesta']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar respuesta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una respuesta del banco
     * 
     * @param int $id_respuesta ID de la respuesta a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($id_respuesta) {
        try {
            $query = "DELETE FROM respuestas_banco WHERE id_respuesta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_respuesta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar respuesta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear múltiples respuestas para una pregunta del banco
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
            foreach ($respuestas as $respuesta) {
                if (empty($respuesta['texto'])) continue;
                
                $datos = [
                    'id_pregunta' => $id_pregunta,
                    'texto' => $respuesta['texto'],
                    'correcta' => isset($respuesta['correcta']) ? 1 : 0,
                    'media_tipo' => $respuesta['media_tipo'] ?? 'ninguno',
                    'media_valor' => $respuesta['media_valor'] ?? null
                ];
                
                if (!$this->crear($datos)) {
                    throw new Exception("Error al crear respuesta múltiple en banco");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al crear respuestas múltiples en banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar todas las respuestas de una pregunta del banco
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return bool True si se eliminaron correctamente
     */
    public function eliminarPorPregunta($id_pregunta) {
        try {
            $query = "DELETE FROM respuestas_banco WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar respuestas del banco por pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar respuestas del banco a una pregunta de examen
     * 
     * @param int $id_pregunta_banco ID de la pregunta en el banco
     * @param int $id_pregunta_destino ID de la pregunta destino en examen
     * @return bool True si se duplicaron correctamente
     */
    public function duplicarAExamen($id_pregunta_banco, $id_pregunta_destino) {
        try {
            $this->db->begin_transaction();
            
            // Obtener respuestas del banco
            $respuestas_banco = $this->obtenerPorPregunta($id_pregunta_banco);
            
            // Crear respuestas en el examen
            require_once __DIR__ . '/respuesta_modelo.php';
            $respuesta_modelo = new Respuesta();
            
            foreach ($respuestas_banco as $respuesta) {
                $nueva_respuesta = [
                    'id_pregunta' => $id_pregunta_destino,
                    'texto' => $respuesta['texto'],
                    'correcta' => $respuesta['correcta'],
                    'media_tipo' => $respuesta['media_tipo'],
                    'media_valor' => $respuesta['media_valor'],
                    'orden' => 0
                ];
                
                if (!$respuesta_modelo->crear($nueva_respuesta)) {
                    throw new Exception("Error al duplicar respuesta del banco a examen");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al duplicar respuestas del banco a examen: " . $e->getMessage());
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
            $query = "SELECT correcta FROM respuestas_banco WHERE id_respuesta = ?";
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
            error_log("Error al verificar respuesta correcta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener respuestas correctas de una pregunta del banco
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return array Lista de respuestas correctas
     */
    public function obtenerCorrectas($id_pregunta) {
        try {
            $query = "SELECT * FROM respuestas_banco 
                      WHERE id_pregunta = ? AND correcta = 1 
                      ORDER BY id_respuesta ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener respuestas correctas del banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar respuestas de una pregunta del banco
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
                      FROM respuestas_banco 
                      WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al contar respuestas del banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Importar respuestas de examen al banco
     * 
     * @param int $id_pregunta_examen ID de la pregunta en el examen
     * @param int $id_pregunta_banco ID de la pregunta en el banco
     * @return bool True si se importaron correctamente
     */
    public function importarDeExamen($id_pregunta_examen, $id_pregunta_banco) {
        try {
            $this->db->begin_transaction();
            
            // Obtener respuestas del examen
            require_once __DIR__ . '/respuesta_modelo.php';
            $respuesta_modelo = new Respuesta();
            $respuestas_examen = $respuesta_modelo->obtenerPorPregunta($id_pregunta_examen);
            
            // Crear respuestas en el banco
            foreach ($respuestas_examen as $respuesta) {
                $nueva_respuesta = [
                    'id_pregunta' => $id_pregunta_banco,
                    'texto' => $respuesta['texto'],
                    'correcta' => $respuesta['correcta'],
                    'media_tipo' => $respuesta['media_tipo'],
                    'media_valor' => $respuesta['media_valor']
                ];
                
                if (!$this->crear($nueva_respuesta)) {
                    throw new Exception("Error al importar respuesta de examen al banco");
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al importar respuestas de examen al banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar respuestas de una pregunta tipo test
     * 
     * @param array $respuestas Array de respuestas
     * @return array Array con errores (vacío si todo está bien)
     */
    public function validarRespuestas($respuestas) {
        $errores = [];
        
        if (empty($respuestas) || !is_array($respuestas)) {
            $errores[] = 'Debe proporcionar al menos 2 respuestas';
            return $errores;
        }
        
        $respuestas_validas = 0;
        $tiene_correcta = false;
        
        foreach ($respuestas as $respuesta) {
            if (!empty($respuesta['texto'])) {
                $respuestas_validas++;
                if (isset($respuesta['correcta']) && $respuesta['correcta']) {
                    $tiene_correcta = true;
                }
            }
        }
        
        if ($respuestas_validas < 2) {
            $errores[] = 'Debe tener al menos 2 respuestas válidas';
        }
        
        if (!$tiene_correcta) {
            $errores[] = 'Debe marcar al menos una respuesta como correcta';
        }
        
        return $errores;
    }
}
