<?php
/**
 * Modelo para la gestión de exámenes
 * 
 * Este archivo contiene la clase Examen que gestiona todas las operaciones
 * relacionadas con exámenes en la base de datos.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class Examen {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene la conexión actual a la base de datos
     * 
     * @return PDO|mysqli Conexión a la base de datos
     */
    public function getConexion() {
        return $this->db;
    }

    /**
     * Obtiene un examen por su ID
     * 
     * @param int $id_examen ID del examen a obtener
     * @return array|bool Datos del examen o false si no existe
     */
    public function obtenerPorId($id_examen) {
        try {
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso,
                             u.nombre as nombre_profesor,
                             u.apellidos as apellidos_profesor
                      FROM examenes e 
                      LEFT JOIN modulos m ON e.id_modulo = m.id_modulo
                      LEFT JOIN cursos c ON e.id_curso = c.id_curso
                      LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                      WHERE e.id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener examen: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/examenes_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todos los exámenes
     * 
     * @return array Lista de exámenes
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso,
                             u.nombre as nombre_profesor,
                             u.apellidos as apellidos_profesor,
                             (SELECT COUNT(*) FROM preguntas p WHERE p.id_examen = e.id_examen) as total_preguntas
                      FROM examenes e 
                      LEFT JOIN modulos m ON e.id_modulo = m.id_modulo
                      LEFT JOIN cursos c ON e.id_curso = c.id_curso
                      LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                      ORDER BY e.fecha_inicio DESC, e.titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener exámenes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene exámenes por profesor
     * 
     * @param int $id_profesor ID del profesor
     * @return array Lista de exámenes del profesor
     */
    public function obtenerPorProfesor($id_profesor) {
        try {
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso,
                             (SELECT COUNT(*) FROM preguntas p WHERE p.id_examen = e.id_examen) as total_preguntas
                      FROM examenes e 
                      LEFT JOIN modulos m ON e.id_modulo = m.id_modulo
                      LEFT JOIN cursos c ON e.id_curso = c.id_curso
                      WHERE m.id_profesor = ? OR c.id_profesor = ?
                      ORDER BY e.fecha_inicio DESC, e.titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $id_profesor, $id_profesor);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener exámenes por profesor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene exámenes disponibles para un alumno
     * 
     * @param int $id_alumno ID del alumno
     * @param bool $solo_activos Si solo devolver exámenes activos
     * @return array Lista de exámenes disponibles
     */
    public function obtenerParaAlumno($id_alumno, $solo_activos = true) {
        try {
            $condicion_activo = $solo_activos ? "AND e.activo = 1 AND e.visible = 1" : "";
            $fecha_actual = date('Y-m-d H:i:s');
            
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso,
                             (SELECT COUNT(*) FROM preguntas p WHERE p.id_examen = e.id_examen AND p.habilitada = 1) as total_preguntas,
                             (SELECT COUNT(*) FROM intentos_examen ie WHERE ie.id_examen = e.id_examen AND ie.id_alumno = ?) as intentos_realizados
                      FROM examenes e 
                      INNER JOIN modulos m ON e.id_modulo = m.id_modulo
                      INNER JOIN cursos c ON e.id_curso = c.id_curso
                      INNER JOIN curso_alumno ca ON c.id_curso = ca.id_curso
                      WHERE ca.id_alumno = ? 
                      AND (e.fecha_inicio IS NULL OR e.fecha_inicio <= ?)
                      AND (e.fecha_fin IS NULL OR e.fecha_fin >= ?)
                      $condicion_activo
                      ORDER BY e.fecha_inicio ASC, e.titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiss", $id_alumno, $id_alumno, $fecha_actual, $fecha_actual);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener exámenes para alumno: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener exámenes disponibles para un alumno
     * 
     * @param int $id_alumno ID del alumno
     * @return array Lista de exámenes disponibles
     */
    public function obtenerExamenesDisponibles($id_alumno) {
        try {
            $fecha_actual = date('Y-m-d H:i:s');
            $condicion_activo = "AND e.activo = 1";
            
            $query = "SELECT e.*, m.titulo as nombre_modulo, c.nombre_curso,
                             (SELECT COUNT(*) FROM preguntas p WHERE p.id_examen = e.id_examen AND p.habilitada = 1) as total_preguntas,
                             (SELECT COUNT(*) FROM intentos_examen ie WHERE ie.id_examen = e.id_examen AND ie.id_alumno = ?) as intentos_realizados
                      FROM examenes e 
                      INNER JOIN modulos m ON e.id_modulo = m.id_modulo
                      INNER JOIN cursos c ON e.id_curso = c.id_curso
                      INNER JOIN curso_alumno ca ON c.id_curso = ca.id_curso
                      WHERE ca.id_alumno = ? 
                      AND (e.fecha_inicio IS NULL OR e.fecha_inicio <= ?)
                      AND (e.fecha_fin IS NULL OR e.fecha_fin >= ?)
                      $condicion_activo
                      ORDER BY e.fecha_inicio ASC, e.titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiss", $id_alumno, $id_alumno, $fecha_actual, $fecha_actual);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener exámenes disponibles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar si un alumno puede realizar un examen
     * 
     * @param int $id_examen ID del examen
     * @param int $id_alumno ID del alumno
     * @return array Resultado de la verificación
     */
    public function puedeRealizarExamen($id_examen, $id_alumno) {
        try {
            $examen = $this->obtenerPorId($id_examen);
            if (!$examen) {
                return ['puede' => false, 'razon' => 'Examen no encontrado'];
            }
            
            // Verificar inscripción en el curso
            $query_inscripcion = "SELECT COUNT(*) as count FROM curso_alumno 
                                 WHERE id_curso = ? AND id_alumno = ?";
            $stmt = $this->db->prepare($query_inscripcion);
            $stmt->bind_param("ii", $examen['id_curso'], $id_alumno);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $inscripcion = $resultado->fetch_assoc();
            
            if ($inscripcion['count'] == 0) {
                return ['puede' => false, 'razon' => 'No estás inscrito en este curso'];
            }
            
            // Verificar fechas
            $ahora = date('Y-m-d H:i:s');
            if ($examen['fecha_inicio'] && $examen['fecha_inicio'] > $ahora) {
                return ['puede' => false, 'razon' => 'El examen aún no está disponible'];
            }
            
            if ($examen['fecha_fin'] && $examen['fecha_fin'] < $ahora) {
                return ['puede' => false, 'razon' => 'El examen ya no está disponible'];
            }
            
            // Verificar intentos
            if ($examen['intentos_permitidos']) {
                $query_intentos = "SELECT COUNT(*) as count FROM intentos_examen 
                                  WHERE id_examen = ? AND id_alumno = ?";
                $stmt = $this->db->prepare($query_intentos);
                $stmt->bind_param("ii", $id_examen, $id_alumno);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $intentos = $resultado->fetch_assoc();
                
                if ($intentos['count'] >= $examen['intentos_permitidos']) {
                    return ['puede' => false, 'razon' => 'Has agotado el número de intentos permitidos'];
                }
            }
            
            // Verificar que esté activo
            if (!$examen['activo']) {
                return ['puede' => false, 'razon' => 'El examen no está activo'];
            }
            
            return ['puede' => true, 'razon' => 'Puede realizar el examen'];
            
        } catch (Exception $e) {
            error_log("Error al verificar si puede realizar examen: " . $e->getMessage());
            return ['puede' => false, 'razon' => 'Error interno del sistema'];
        }
    }
    
    /**
     * Obtener historial de intentos de un alumno
     * 
     * @param int $id_alumno ID del alumno
     * @param int $limite Límite de resultados
     * @return array Lista de intentos
     */
    public function obtenerHistorialIntentos($id_alumno, $limite = 20) {
        try {
            $query = "SELECT ie.*, e.titulo as titulo_examen, c.nombre_curso, m.titulo as nombre_modulo
                      FROM intentos_examen ie
                      INNER JOIN examenes e ON ie.id_examen = e.id_examen
                      INNER JOIN cursos c ON e.id_curso = c.id_curso
                      INNER JOIN modulos m ON e.id_modulo = m.id_modulo
                      WHERE ie.id_alumno = ?
                      ORDER BY ie.fecha_fin DESC
                      LIMIT ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $id_alumno, $limite);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener historial de intentos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear un nuevo examen
     * 
     * @param array $datos Datos del examen
     * @return int|bool ID del examen creado o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO examenes (titulo, id_modulo, id_curso, tiempo_limite, 
                                          aleatorio_preg, aleatorio_resp, fecha_inicio, 
                                          fecha_fin, visible, activo, estado) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'borrador')";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiiiissii", 
                $datos['titulo'],
                $datos['id_modulo'],
                $datos['id_curso'],
                $datos['tiempo_limite'],
                $datos['aleatorio_preg'],
                $datos['aleatorio_resp'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['visible'],
                $datos['activo']
            );
            
            if ($stmt->execute()) {
                return $this->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un examen existente
     * 
     * @param array $datos Datos del examen incluyendo ID
     * @return bool True si se actualizó correctamente
     */
    public function actualizar($datos) {
        try {
            $query = "UPDATE examenes SET 
                      titulo = ?, id_modulo = ?, id_curso = ?, tiempo_limite = ?,
                      aleatorio_preg = ?, aleatorio_resp = ?, fecha_inicio = ?,
                      fecha_fin = ?, visible = ?, activo = ?
                      WHERE id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiiiisssii", 
                $datos['titulo'],
                $datos['id_modulo'],
                $datos['id_curso'],
                $datos['tiempo_limite'],
                $datos['aleatorio_preg'],
                $datos['aleatorio_resp'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['visible'],
                $datos['activo'],
                $datos['id_examen']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un examen
     * 
     * @param int $id_examen ID del examen a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function eliminar($id_examen) {
        try {
            // Las preguntas y respuestas se eliminan automáticamente por CASCADE
            $query = "DELETE FROM examenes WHERE id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_examen);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Duplicar un examen existente
     * 
     * @param int $id_examen ID del examen original
     * @param array $datos_nuevos Datos para el nuevo examen
     * @return int|bool ID del nuevo examen o false si hay error
     */
    public function duplicar($id_examen, $datos_nuevos) {
        try {
            $this->db->begin_transaction();
            
            // Obtener examen original
            $examen_original = $this->obtenerPorId($id_examen);
            if (!$examen_original) {
                throw new Exception("Examen original no encontrado");
            }
            
            // Crear nuevo examen
            $nuevo_examen = [
                'titulo' => $datos_nuevos['titulo'] ?? $examen_original['titulo'] . ' (Copia)',
                'id_modulo' => $datos_nuevos['id_modulo'] ?? $examen_original['id_modulo'],
                'id_curso' => $datos_nuevos['id_curso'] ?? $examen_original['id_curso'],
                'tiempo_limite' => $examen_original['tiempo_limite'],
                'aleatorio_preg' => $examen_original['aleatorio_preg'],
                'aleatorio_resp' => $examen_original['aleatorio_resp'],
                'fecha_inicio' => $datos_nuevos['fecha_inicio'] ?? null,
                'fecha_fin' => $datos_nuevos['fecha_fin'] ?? null,
                'visible' => 0, // Por defecto oculto
                'activo' => 0   // Por defecto inactivo
            ];
            
            $nuevo_id = $this->crear($nuevo_examen);
            if (!$nuevo_id) {
                throw new Exception("Error al crear el nuevo examen");
            }
            
            // Actualizar referencia al examen origen
            $query = "UPDATE examenes SET id_examen_origen = ? WHERE id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $id_examen, $nuevo_id);
            $stmt->execute();
            
            // Duplicar preguntas
            require_once __DIR__ . '/pregunta_modelo.php';
            $pregunta_modelo = new Pregunta();
            $pregunta_modelo->duplicarDeExamen($id_examen, $nuevo_id);
            
            $this->db->commit();
            return $nuevo_id;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al duplicar examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambiar estado del examen
     * 
     * @param int $id_examen ID del examen
     * @param string $estado Nuevo estado (borrador, activo, finalizado)
     * @return bool True si se cambió correctamente
     */
    public function cambiarEstado($id_examen, $estado) {
        try {
            $estados_validos = ['borrador', 'activo', 'finalizado'];
            if (!in_array($estado, $estados_validos)) {
                return false;
            }
            
            $query = "UPDATE examenes SET estado = ? WHERE id_examen = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $estado, $id_examen);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar estado del examen: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas del examen
     * 
     * @param int $id_examen ID del examen
     * @return array Estadísticas del examen
     */
    public function obtenerEstadisticas($id_examen) {
        try {
            $query = "SELECT 
                        (SELECT COUNT(*) FROM preguntas WHERE id_examen = ? AND habilitada = 1) as total_preguntas,
                        (SELECT COUNT(*) FROM intentos_examen WHERE id_examen = ?) as total_intentos,
                        (SELECT COUNT(*) FROM intentos_examen WHERE id_examen = ? AND finalizado = 1) as intentos_finalizados,
                        (SELECT COUNT(DISTINCT id_alumno) FROM intentos_examen WHERE id_examen = ?) as alumnos_participantes,
                        (SELECT AVG(c.nota_final) FROM calificaciones c WHERE c.id_examen = ?) as nota_promedio";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iiiii", $id_examen, $id_examen, $id_examen, $id_examen, $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar exámenes con filtros
     * 
     * @param array $filtros Filtros de búsqueda
     * @return array Lista de exámenes filtrados
     */
    public function buscar($filtros = []) {
        try {
            $condiciones = [];
            $parametros = [];
            $tipos = "";
            
            // Construir condiciones WHERE
            if (!empty($filtros['titulo'])) {
                $condiciones[] = "e.titulo LIKE ?";
                $parametros[] = "%" . $filtros['titulo'] . "%";
                $tipos .= "s";
            }
            
            if (!empty($filtros['id_curso'])) {
                $condiciones[] = "e.id_curso = ?";
                $parametros[] = $filtros['id_curso'];
                $tipos .= "i";
            }
            
            if (!empty($filtros['id_modulo'])) {
                $condiciones[] = "e.id_modulo = ?";
                $parametros[] = $filtros['id_modulo'];
                $tipos .= "i";
            }
            
            if (!empty($filtros['estado'])) {
                $condiciones[] = "e.estado = ?";
                $parametros[] = $filtros['estado'];
                $tipos .= "s";
            }
            
            $where = !empty($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";
            
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso,
                             (SELECT COUNT(*) FROM preguntas p WHERE p.id_examen = e.id_examen) as total_preguntas
                      FROM examenes e 
                      LEFT JOIN modulos m ON e.id_modulo = m.id_modulo
                      LEFT JOIN cursos c ON e.id_curso = c.id_curso
                      $where
                      ORDER BY e.fecha_inicio DESC, e.titulo ASC";
            
            $stmt = $this->db->prepare($query);
            if (!empty($parametros)) {
                $stmt->bind_param($tipos, ...$parametros);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al buscar exámenes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de un examen para profesores
     * 
     * @param int $id_examen ID del examen
     * @return array Estadísticas del examen
     */
    public function obtenerEstadisticasExamen($id_examen) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT ie.id_alumno) as total_alumnos_intentaron,
                        COUNT(ie.id_intento) as total_intentos,
                        AVG(ie.calificacion) as promedio_calificacion,
                        MAX(ie.calificacion) as mejor_calificacion,
                        MIN(ie.calificacion) as peor_calificacion,
                        AVG(ie.tiempo_transcurrido) as tiempo_promedio,
                        SUM(CASE WHEN ie.calificacion >= 5 THEN 1 ELSE 0 END) as aprobados,
                        SUM(CASE WHEN ie.calificacion < 5 THEN 1 ELSE 0 END) as suspensos
                      FROM intentos_examen ie
                      WHERE ie.id_examen = ? AND ie.estado = 'completado'";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return [];
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas del examen: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los exámenes de un curso específico
     * 
     * @param int $id_curso ID del curso
     * @return array Lista de exámenes del curso
     */
    public function obtenerPorCurso($id_curso) {
        try {
            $query = "SELECT e.*, 
                             m.titulo as nombre_modulo,
                             c.nombre_curso
                      FROM examenes e 
                      LEFT JOIN modulos m ON e.id_modulo = m.id_modulo
                      LEFT JOIN cursos c ON e.id_curso = c.id_curso
                      WHERE e.id_curso = ?
                      ORDER BY e.fecha_inicio DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener exámenes por curso: " . $e->getMessage());
            return [];
        }
    }
}
