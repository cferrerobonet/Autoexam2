<?php
/**
 * Modelo para la gestión de preguntas del banco
 * 
 * Este archivo contiene la clase PreguntaBanco que gestiona todas las operaciones
 * relacionadas con preguntas del banco global de preguntas reutilizables.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class PreguntaBanco {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene una pregunta del banco por su ID
     * 
     * @param int $id_pregunta ID de la pregunta
     * @return array|bool Datos de la pregunta o false si no existe
     */
    public function obtenerPorId($id_pregunta) {
        try {
            $query = "SELECT pb.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor
                      FROM preguntas_banco pb
                      LEFT JOIN usuarios u ON pb.id_profesor = u.id_usuario
                      WHERE pb.id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener pregunta del banco: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/banco_preguntas_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todas las preguntas del banco (para admin)
     * 
     * @param array $filtros Filtros de búsqueda
     * @param int $limite Límite de resultados
     * @param int $pagina Página actual
     * @return array Lista de preguntas
     */
    public function obtenerTodas($filtros = [], $limite = 20, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $limite;
            $condiciones = ["1=1"];
            $parametros = [];
            $tipos = "";
            
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
            
            if (!empty($filtros['origen'])) {
                $condiciones[] = "pb.origen = ?";
                $parametros[] = $filtros['origen'];
                $tipos .= "s";
            }
            
            if (!empty($filtros['publica'])) {
                $condiciones[] = "pb.publica = ?";
                $parametros[] = $filtros['publica'] == 'si' ? 1 : 0;
                $tipos .= "i";
            }
            
            $where = "WHERE " . implode(" AND ", $condiciones);
            
            $query = "SELECT pb.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor,
                             (SELECT COUNT(*) FROM respuestas_banco rb WHERE rb.id_pregunta = pb.id_pregunta) as total_respuestas
                      FROM preguntas_banco pb
                      LEFT JOIN usuarios u ON pb.id_profesor = u.id_usuario
                      $where
                      ORDER BY pb.fecha_creacion DESC
                      LIMIT ? OFFSET ?";
            
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= "ii";
            
            $stmt = $this->db->prepare($query);
            if (!empty($parametros)) {
                $stmt->bind_param($tipos, ...$parametros);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener todas las preguntas del banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene preguntas del banco por profesor (propias + públicas)
     * 
     * @param int $id_profesor ID del profesor
     * @param array $filtros Filtros de búsqueda
     * @param int $limite Límite de resultados
     * @param int $pagina Página actual
     * @return array Lista de preguntas
     */
    public function obtenerPorProfesor($id_profesor, $filtros = [], $limite = 20, $pagina = 1) {
        try {
            $offset = ($pagina - 1) * $limite;
            $condiciones = ["(pb.id_profesor = ? OR pb.publica = 1)"];
            $parametros = [$id_profesor];
            $tipos = "i";
            
            // Agregar filtros adicionales
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
            
            if (!empty($filtros['origen'])) {
                $condiciones[] = "pb.origen = ?";
                $parametros[] = $filtros['origen'];
                $tipos .= "s";
            }
            
            $where = "WHERE " . implode(" AND ", $condiciones);
            
            $query = "SELECT pb.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor,
                             (SELECT COUNT(*) FROM respuestas_banco rb WHERE rb.id_pregunta = pb.id_pregunta) as total_respuestas
                      FROM preguntas_banco pb
                      LEFT JOIN usuarios u ON pb.id_profesor = u.id_usuario
                      $where
                      ORDER BY pb.fecha_creacion DESC
                      LIMIT ? OFFSET ?";
            
            $parametros[] = $limite;
            $parametros[] = $offset;
            $tipos .= "ii";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($tipos, ...$parametros);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener preguntas del banco por profesor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear una nueva pregunta en el banco
     * 
     * @param array $datos Datos de la pregunta
     * @return int|bool ID de la pregunta creada o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO preguntas_banco (tipo, enunciado, media_tipo, media_valor, 
                                                 origen, id_profesor, publica, fecha_creacion) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssssii", 
                $datos['tipo'],
                $datos['enunciado'],
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['origen'] ?? 'manual',
                $datos['id_profesor'],
                $datos['publica'] ?? 0
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear pregunta en banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una pregunta del banco
     * 
     * @param array $datos Datos de la pregunta incluyendo ID
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE preguntas_banco SET 
                      tipo = ?, enunciado = ?, media_tipo = ?, media_valor = ?,
                      publica = ?
                      WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssii", 
                $datos['tipo'],
                $datos['enunciado'],
                $datos['media_tipo'] ?? 'ninguno',
                $datos['media_valor'] ?? null,
                $datos['publica'] ?? 0,
                $datos['id_pregunta']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar pregunta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una pregunta del banco
     * 
     * @param int $id_pregunta ID de la pregunta a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($id_pregunta) {
        try {
            $this->db->begin_transaction();
            
            // Primero eliminar respuestas si existen
            $query_resp = "DELETE FROM respuestas_banco WHERE id_pregunta = ?";
            $stmt_resp = $this->db->prepare($query_resp);
            $stmt_resp->bind_param("i", $id_pregunta);
            $stmt_resp->execute();
            
            // Luego eliminar la pregunta
            $query = "DELETE FROM preguntas_banco WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_pregunta);
            $resultado = $stmt->execute();
            
            $this->db->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al eliminar pregunta del banco: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar visibilidad de una pregunta (pública/privada)
     * 
     * @param int $id_pregunta ID de la pregunta
     * @param bool $publica True para hacer pública, false para privada
     * @return bool True si se cambió correctamente
     */
    public function cambiarVisibilidad($id_pregunta, $publica) {
        try {
            $query = "UPDATE preguntas_banco SET publica = ? WHERE id_pregunta = ?";
            $stmt = $this->db->prepare($query);
            $valor_publica = $publica ? 1 : 0;
            $stmt->bind_param("ii", $valor_publica, $id_pregunta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar visibilidad de pregunta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar total de preguntas en el banco según filtros
     * 
     * @param array $filtros Filtros aplicados
     * @param int $id_profesor ID del profesor (opcional para filtros de acceso)
     * @return int Total de preguntas
     */
    public function contarTotal($filtros = [], $id_profesor = null) {
        try {
            $condiciones = [];
            $parametros = [];
            $tipos = "";
            
            // Si es profesor, solo ver propias + públicas
            if ($id_profesor !== null) {
                $condiciones[] = "(pb.id_profesor = ? OR pb.publica = 1)";
                $parametros[] = $id_profesor;
                $tipos .= "i";
            }
            
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
            
            if (!empty($filtros['origen'])) {
                $condiciones[] = "pb.origen = ?";
                $parametros[] = $filtros['origen'];
                $tipos .= "s";
            }
            
            if (!empty($filtros['publica'])) {
                $condiciones[] = "pb.publica = ?";
                $parametros[] = $filtros['publica'] == 'si' ? 1 : 0;
                $tipos .= "i";
            }
            
            $where = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";
            
            $query = "SELECT COUNT(*) as total FROM preguntas_banco pb $where";
            $stmt = $this->db->prepare($query);
            
            if (!empty($parametros)) {
                $stmt->bind_param($tipos, ...$parametros);
            }
            
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            
            return (int)$fila['total'];
        } catch (Exception $e) {
            error_log("Error al contar preguntas del banco: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas del banco de preguntas
     * 
     * @param int $id_profesor ID del profesor (opcional)
     * @return array Estadísticas del banco
     */
    public function obtenerEstadisticas($id_profesor = null) {
        try {
            $condicion = $id_profesor ? "WHERE pb.id_profesor = $id_profesor" : "";
            
            $query = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN pb.tipo = 'test' THEN 1 ELSE 0 END) as tipo_test,
                        SUM(CASE WHEN pb.tipo = 'desarrollo' THEN 1 ELSE 0 END) as tipo_desarrollo,
                        SUM(CASE WHEN pb.publica = 1 THEN 1 ELSE 0 END) as publicas,
                        SUM(CASE WHEN pb.publica = 0 THEN 1 ELSE 0 END) as privadas,
                        SUM(CASE WHEN pb.origen = 'manual' THEN 1 ELSE 0 END) as manuales,
                        SUM(CASE WHEN pb.origen = 'ia' THEN 1 ELSE 0 END) as por_ia,
                        SUM(CASE WHEN pb.origen = 'pdf' THEN 1 ELSE 0 END) as desde_pdf
                      FROM preguntas_banco pb
                      $condicion";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas del banco: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Duplicar pregunta del banco a un examen
     * 
     * @param int $id_pregunta_banco ID de la pregunta en el banco
     * @param int $id_examen ID del examen destino
     * @return int|bool ID de la nueva pregunta o false si hay error
     */
    public function duplicarAExamen($id_pregunta_banco, $id_examen) {
        try {
            require_once __DIR__ . '/pregunta_modelo.php';
            $pregunta_modelo = new Pregunta();
            
            return $pregunta_modelo->importarDelBanco($id_pregunta_banco, $id_examen);
        } catch (Exception $e) {
            error_log("Error al duplicar pregunta del banco a examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mover pregunta de examen al banco
     * 
     * @param int $id_pregunta ID de la pregunta en el examen
     * @param int $id_profesor ID del profesor
     * @param bool $publica Si hacer la pregunta pública
     * @return int|bool ID de la nueva pregunta del banco o false si hay error
     */
    public function moverDeExamen($id_pregunta, $id_profesor, $publica = false) {
        try {
            $this->db->begin_transaction();
            
            // Obtener pregunta del examen
            require_once __DIR__ . '/pregunta_modelo.php';
            $pregunta_modelo = new Pregunta();
            $pregunta = $pregunta_modelo->obtenerPorId($id_pregunta);
            
            if (!$pregunta) {
                throw new Exception("Pregunta no encontrada");
            }
            
            // Crear en el banco
            $datos_banco = [
                'tipo' => $pregunta['tipo'],
                'enunciado' => $pregunta['enunciado'],
                'media_tipo' => $pregunta['media_tipo'],
                'media_valor' => $pregunta['media_valor'],
                'origen' => 'manual',
                'id_profesor' => $id_profesor,
                'publica' => $publica ? 1 : 0
            ];
            
            $id_pregunta_banco = $this->crear($datos_banco);
            if (!$id_pregunta_banco) {
                throw new Exception("Error al crear pregunta en banco");
            }
            
            // Si es tipo test, mover respuestas
            if ($pregunta['tipo'] == 'test') {
                require_once __DIR__ . '/respuesta_modelo.php';
                require_once __DIR__ . '/respuesta_banco_modelo.php';
                
                $respuesta_modelo = new Respuesta();
                $respuesta_banco_modelo = new RespuestaBanco();
                
                $respuestas = $respuesta_modelo->obtenerPorPregunta($id_pregunta);
                foreach ($respuestas as $respuesta) {
                    $datos_respuesta_banco = [
                        'id_pregunta' => $id_pregunta_banco,
                        'texto' => $respuesta['texto'],
                        'correcta' => $respuesta['correcta'],
                        'media_tipo' => $respuesta['media_tipo'],
                        'media_valor' => $respuesta['media_valor']
                    ];
                    
                    $respuesta_banco_modelo->crear($datos_respuesta_banco);
                }
            }
            
            $this->db->commit();
            return $id_pregunta_banco;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al mover pregunta al banco: " . $e->getMessage());
            return false;
        }
    }
}
