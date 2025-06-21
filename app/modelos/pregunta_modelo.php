<?php
/**
 * Modelo para la gestión de preguntas
 * 
 * Este archivo contiene la clase Pregunta que gestiona todas las operaciones
 * relacionadas con preguntas en la base de datos.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class Pregunta {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene una pregunta por su ID
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return array|bool Datos de la pregunta o false si no existe
     */
    public function obtenerPorId($id_pregunta) {
        try {
            $query = "SELECT * FROM preguntas WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener pregunta: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/preguntas_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todas las preguntas de un examen
     * 
     * @param int $id_examen ID del examen
     * @param bool $solo_habilitadas Si solo devolver preguntas habilitadas
     * @return array Lista de preguntas
     */
    public function obtenerPorExamen($id_examen, $solo_habilitadas = false) {
        try {
            $condicion = $solo_habilitadas ? "AND habilitada = 1" : "";
            
            $query = "SELECT * FROM preguntas 
                      WHERE id_examen = ? $condicion 
                      ORDER BY orden ASC, id_pregunta ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener preguntas por examen: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear una nueva pregunta
     * 
     * @param array $datos Datos de la pregunta
     * @return int|bool ID de la pregunta creada o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO preguntas (id_examen, tipo, enunciado, media_tipo, 
                                           media_valor, habilitada, orden) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("issssii", 
                $datos['id_examen'],
                $datos['tipo'],
                $datos['enunciado'],
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['habilitada'] ?? 1,
                $datos['orden'] ?? 0
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una pregunta existente
     * 
     * @param array $datos Datos de la pregunta incluyendo ID
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE preguntas SET 
                      tipo = ?, enunciado = ?, media_tipo = ?, media_valor = ?,
                      habilitada = ?, orden = ?
                      WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssiii", 
                $datos['tipo'],
                $datos['enunciado'],
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['habilitada'] ?? 1,
                $datos['orden'] ?? 0,
                $datos['id_pregunta']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una pregunta
     * 
     * @param int $id_pregunta ID de la pregunta a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($id_pregunta) {
        try {
            // Las respuestas se eliminan automáticamente por CASCADE
            $query = "DELETE FROM preguntas WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar preguntas de un examen a otro
     * 
     * @param int $id_examen_origen ID del examen origen
     * @param int $id_examen_destino ID del examen destino
     * @return bool True si se duplicaron correctamente
     */
    public function duplicarDeExamen($id_examen_origen, $id_examen_destino) {
        try {
            $this->db->begin_transaction();
            
            // Obtener preguntas del examen origen
            $preguntas = $this->obtenerPorExamen($id_examen_origen);
            
            foreach ($preguntas as $pregunta) {
                // Crear nueva pregunta
                $nueva_pregunta = [
                    'id_examen' => $id_examen_destino,
                    'tipo' => $pregunta['tipo'],
                    'enunciado' => $pregunta['enunciado'],
                    'media_tipo' => $pregunta['media_tipo'],
                    'media_valor' => $pregunta['media_valor'],
                    'habilitada' => $pregunta['habilitada'],
                    'orden' => $pregunta['orden']
                ];
                
                $nueva_id_pregunta = $this->crear($nueva_pregunta);
                if (!$nueva_id_pregunta) {
                    throw new Exception("Error al duplicar pregunta");
                }
                
                // Si es tipo test, duplicar respuestas
                if ($pregunta['tipo'] == 'test') {
                    require_once __DIR__ . '/respuesta_modelo.php';
                    $respuesta_modelo = new Respuesta();
                    $respuesta_modelo->duplicarDePregunta($pregunta['id_pregunta'], $nueva_id_pregunta);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al duplicar preguntas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar orden de las preguntas
     * 
     * @param array $orden_preguntas Array con IDs en el nuevo orden
     * @return bool True si se actualizó correctamente
     */
    public function actualizarOrden($orden_preguntas) {
        try {
            $this->db->begin_transaction();
            
            foreach ($orden_preguntas as $orden => $id_pregunta) {
                $query = "UPDATE preguntas SET orden = ? WHERE id_pregunta = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("ii", $orden, $id_pregunta);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar orden de preguntas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Habilitar/deshabilitar pregunta
     * 
     * @param int $id_pregunta ID de la pregunta
     * @param bool $habilitada True para habilitar, false para deshabilitar
     * @return bool True si se actualizó correctamente
     */
    public function cambiarEstado($id_pregunta, $habilitada) {
        try {
            $query = "UPDATE preguntas SET habilitada = ? WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $estado = $habilitada ? 1 : 0;
            $stmt->bind_param("ii", $estado, $id_pregunta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar estado de pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener preguntas del banco para importar
     * 
     * @param int $id_profesor ID del profesor (para filtrar preguntas propias y públicas)
     * @param array $filtros Filtros adicionales
     * @return array Lista de preguntas del banco
     */
    public function obtenerDelBanco($id_profesor, $filtros = []) {
        try {
            $condiciones = ["(pb.id_profesor = ? OR pb.publica = 1)"];
            $parametros = [$id_profesor];
            $tipos = "i";
            
            // Agregar filtros
            if (!empty($filtros['tipo'])) {
                $condiciones[] = "pb.tipo = ?";
                $parametros[] = $filtros['tipo'];
                $tipos .= "s";
            }
            
            if (!empty($filtros['busqueda'])) {
                $condiciones[] = "pb.enunciado LIKE ?";
                $parametros[] = "%" . $filtros['busqueda'] . "%";
                $tipos .= "s";
            }
            
            $where = "WHERE " . implode(" AND ", $condiciones);
            
            $query = "SELECT pb.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor
                      FROM preguntas_banco pb
                      LEFT JOIN usuarios u ON pb.id_profesor = u.id_usuario
                      $where
                      ORDER BY pb.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($tipos, ...$parametros);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener preguntas del banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Importar pregunta del banco a un examen
     * 
     * @param int $id_pregunta_banco ID de la pregunta en el banco
     * @param int $id_examen ID del examen destino
     * @return int|bool ID de la nueva pregunta o false si hay error
     */
    public function importarDelBanco($id_pregunta_banco, $id_examen) {
        try {
            $this->db->begin_transaction();
            
            // Obtener pregunta del banco
            $query = "SELECT * FROM preguntas_banco WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta_banco);
            $stmt->execute();
            $pregunta_banco = $stmt->get_result()->fetch_assoc();
            
            if (!$pregunta_banco) {
                throw new Exception("Pregunta del banco no encontrada");
            }
            
            // Crear nueva pregunta en el examen
            $nueva_pregunta = [
                'id_examen' => $id_examen,
                'tipo' => $pregunta_banco['tipo'],
                'enunciado' => $pregunta_banco['enunciado'],
                'media_tipo' => $pregunta_banco['media_tipo'],
                'media_valor' => $pregunta_banco['media_valor'],
                'habilitada' => 1,
                'orden' => 0
            ];
            
            $nueva_id_pregunta = $this->crear($nueva_pregunta);
            if (!$nueva_id_pregunta) {
                throw new Exception("Error al crear pregunta en examen");
            }
            
            // Si es tipo test, importar respuestas
            if ($pregunta_banco['tipo'] == 'test') {
                require_once __DIR__ . '/respuesta_modelo.php';
                $respuesta_modelo = new Respuesta();
                $respuesta_modelo->importarDelBanco($id_pregunta_banco, $nueva_id_pregunta);
            }
            
            $this->db->commit();
            return $nueva_id_pregunta;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al importar pregunta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de preguntas por examen
     * 
     * @param int $id_examen ID del examen
     * @return array Estadísticas de preguntas
     */
    public function obtenerEstadisticas($id_examen) {
        try {
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN tipo = 'test' THEN 1 ELSE 0 END) as tipo_test,
                        SUM(CASE WHEN tipo = 'desarrollo' THEN 1 ELSE 0 END) as tipo_desarrollo,
                        SUM(CASE WHEN habilitada = 1 THEN 1 ELSE 0 END) as habilitadas,
                        SUM(CASE WHEN habilitada = 0 THEN 1 ELSE 0 END) as deshabilitadas
                      FROM preguntas 
                      WHERE id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de preguntas: " . $e->getMessage());
            return [];
        }
    }
}
