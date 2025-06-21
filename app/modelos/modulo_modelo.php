<?php
/**
 * Modelo de Módulos - AUTOEXAM2
 * 
 * Gestiona las operaciones de base de datos para módulos
 * Reescrito completamente siguiendo el patrón de otros modelos funcionales
 * 
 * @author GitHub Copilot
 * @version 2.0
 */

class ModuloModelo {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }

    /**
     * Obtener todos los módulos con filtros y paginación
     */
    public function obtenerTodos($limite = 15, $pagina = 1, $filtros = []) {
        try {
            $offset = ($pagina - 1) * $limite;
            $where = "WHERE 1=1";
            $params = [];
            $tipos = "";
            
            // Aplicar filtros
            if (!empty($filtros['buscar'])) {
                $where .= " AND (m.titulo LIKE ? OR m.descripcion LIKE ? OR u.nombre LIKE ? OR u.apellidos LIKE ?)";
                $buscar = "%" . $filtros['buscar'] . "%";
                $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
                $tipos .= "ssss";
            }
            
            if (!empty($filtros['id_profesor'])) {
                $where .= " AND m.id_profesor = ?";
                $params[] = $filtros['id_profesor'];
                $tipos .= "i";
            }
            
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $where .= " AND m.activo = ?";
                $params[] = $filtros['activo'];
                $tipos .= "i";
            }
            
            // Consulta principal
            $query = "SELECT m.id_modulo, m.titulo, m.descripcion, m.id_profesor, m.activo,
                             u.nombre, u.apellidos,
                             COUNT(DISTINCT e.id_examen) as total_examenes,
                             GROUP_CONCAT(DISTINCT c.nombre_curso ORDER BY c.nombre_curso SEPARATOR ', ') as cursos_asignados
                      FROM modulos m
                      LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                      LEFT JOIN examenes e ON m.id_modulo = e.id_modulo
                      LEFT JOIN modulo_curso mc ON m.id_modulo = mc.id_modulo
                      LEFT JOIN cursos c ON mc.id_curso = c.id_curso
                      $where
                      GROUP BY m.id_modulo, m.titulo, m.descripcion, m.id_profesor, m.activo, u.nombre, u.apellidos
                      ORDER BY m.titulo ASC
                      LIMIT ? OFFSET ?";
            
            $params[] = $limite;
            $params[] = $offset;
            $tipos .= "ii";
            
            $stmt = $this->db->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($tipos, ...$params);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $modulos = [];
            while ($modulo = $resultado->fetch_assoc()) {
                $modulos[] = $modulo;
            }
            
            // Contar total de registros
            $query_count = "SELECT COUNT(DISTINCT m.id_modulo) as total
                           FROM modulos m
                           LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                           $where";
            
            $stmt_count = $this->db->prepare($query_count);
            if (!empty($params) && count($params) > 2) {
                $params_count = array_slice($params, 0, -2);
                $tipos_count = substr($tipos, 0, -2);
                $stmt_count->bind_param($tipos_count, ...$params_count);
            }
            $stmt_count->execute();
            $total = $stmt_count->get_result()->fetch_assoc()['total'];
            
            return [
                'modulos' => $modulos,
                'total' => $total,
                'paginas' => ceil($total / $limite)
            ];
        } catch (Exception $e) {
            error_log("Error al obtener módulos: " . $e->getMessage());
            return ['modulos' => [], 'total' => 0, 'paginas' => 1];
        }
    }

    /**
     * Crear nuevo módulo con validaciones
     */
    public function crear($datos) {
        try {
            // Validar datos de entrada
            $this->validarDatosModulo($datos);
            
            // Verificar que no existe otro módulo con el mismo título para el mismo profesor
            if ($this->existeTituloProfesor($datos['titulo'], $datos['id_profesor'])) {
                throw new Exception('Ya existe un módulo con ese título para este profesor');
            }
            
            $query = "INSERT INTO modulos (titulo, descripcion, id_profesor, activo) 
                     VALUES (?, ?, ?, 1)";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $this->db->error);
            }
            
            $stmt->bind_param("ssi", 
                $datos['titulo'], 
                $datos['descripcion'], 
                $datos['id_profesor']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
            }
            
            $idModulo = $stmt->insert_id;
            $stmt->close();
            
            return $idModulo;
            
        } catch (Exception $e) {
            error_log("Error al crear módulo: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener módulo por ID
     */
    public function obtenerPorId($idModulo) {
        try {
            $query = "SELECT m.*, u.nombre, u.apellidos 
                     FROM modulos m 
                     LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario 
                     WHERE m.id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $idModulo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener módulo por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener exámenes de un módulo
     */
    public function obtenerExamenes($idModulo) {
        try {
            $query = "SELECT id_examen, titulo, descripcion, activo 
                     FROM examenes 
                     WHERE id_modulo = ? 
                     ORDER BY titulo ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $idModulo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $examenes = [];
            while ($examen = $resultado->fetch_assoc()) {
                $examenes[] = $examen;
            }
            return $examenes;
        } catch (Exception $e) {
            error_log("Error al obtener exámenes del módulo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Eliminar un módulo
     */
    public function eliminar($idModulo) {
        try {
            // Primero eliminar las asignaciones de cursos
            $this->eliminarAsignacionesCursos($idModulo);
            
            // Luego eliminar el módulo
            $query = "DELETE FROM modulos WHERE id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $idModulo);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar módulo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener cursos asignados a un módulo
     */
    public function obtenerCursosAsignados($idModulo) {
        try {
            $query = "SELECT mc.id_curso 
                     FROM modulo_curso mc 
                     WHERE mc.id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $idModulo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $cursos = [];
            while ($fila = $resultado->fetch_assoc()) {
                $cursos[] = $fila['id_curso'];
            }
            return $cursos;
        } catch (Exception $e) {
            error_log("Error al obtener cursos asignados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar cursos asignados a un módulo
     */
    public function actualizarCursosAsignados($idModulo, $cursosNuevos) {
        try {
            // Eliminar asignaciones existentes
            $this->eliminarAsignacionesCursos($idModulo);
            
            // Agregar nuevas asignaciones
            if (!empty($cursosNuevos)) {
                $this->asignarCursos($idModulo, $cursosNuevos);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error al actualizar cursos asignados: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar asignaciones de cursos de un módulo
     */
    private function eliminarAsignacionesCursos($idModulo) {
        try {
            $query = "DELETE FROM modulo_curso WHERE id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $idModulo);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al eliminar asignaciones de cursos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Asignar módulo a múltiples cursos
     */
    public function asignarCursos($id_modulo, $cursos_ids) {
        try {
            if (empty($cursos_ids) || !is_array($cursos_ids)) {
                return false;
            }
            
            // Eliminar asignaciones existentes
            $query_delete = "DELETE FROM modulo_curso WHERE id_modulo = ?";
            $stmt_delete = $this->db->prepare($query_delete);
            $stmt_delete->bind_param("i", $id_modulo);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Insertar las nuevas asignaciones
            $query_insert = "INSERT INTO modulo_curso (id_modulo, id_curso) VALUES (?, ?)";
            $stmt_insert = $this->db->prepare($query_insert);
            
            $exito = true;
            foreach ($cursos_ids as $id_curso) {
                $curso_id = (int)$id_curso;
                $stmt_insert->bind_param("ii", $id_modulo, $curso_id);
                if (!$stmt_insert->execute()) {
                    $exito = false;
                    break;
                }
            }
            
            $stmt_insert->close();
            return $exito;
        } catch (Exception $e) {
            error_log("Error al asignar cursos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener profesores para el selector
     */
    public function obtenerProfesores() {
        try {
            $query = "SELECT id_usuario, nombre, apellidos, correo 
                      FROM usuarios 
                      WHERE rol = 'profesor' AND activo = 1
                      ORDER BY apellidos, nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener profesores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los cursos disponibles
     */
    public function obtenerCursos() {
        try {
            $query = "SELECT id_curso, nombre_curso, descripcion 
                      FROM cursos 
                      WHERE activo = 1 
                      ORDER BY nombre_curso ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cursos = [];
            while ($curso = $result->fetch_assoc()) {
                $cursos[] = $curso;
            }
            
            return $cursos;
        } catch (Exception $e) {
            error_log("Error al obtener cursos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener cursos de un profesor específico
     */
    public function obtenerCursosPorProfesor($id_profesor) {
        try {
            $query = "SELECT id_curso, nombre_curso, descripcion 
                      FROM cursos 
                      WHERE id_profesor = ? AND activo = 1 
                      ORDER BY nombre_curso ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_profesor);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cursos = [];
            while ($curso = $result->fetch_assoc()) {
                $cursos[] = $curso;
            }
            
            return $cursos;
        } catch (Exception $e) {
            error_log("Error al obtener cursos del profesor: " . $e->getMessage());
            return [];
        }
    }
    
    // ============ MÉTODOS DE VALIDACIÓN Y UTILIDADES ============
    
    /**
     * Validar datos del módulo
     */
    private function validarDatosModulo($datos) {
        if (empty($datos['titulo'])) {
            throw new Exception('El título del módulo es obligatorio');
        }
        
        if (strlen($datos['titulo']) > 255) {
            throw new Exception('El título no puede exceder 255 caracteres');
        }
        
        if (empty($datos['id_profesor']) || !is_numeric($datos['id_profesor'])) {
            throw new Exception('El ID del profesor es inválido');
        }
    }
    
    /**
     * Verificar si existe un módulo con el mismo título para un profesor
     */
    private function existeTituloProfesor($titulo, $idProfesor, $excluirId = null) {
        try {
            $query = "SELECT id_modulo FROM modulos WHERE titulo = ? AND id_profesor = ?";
            $params = [$titulo, $idProfesor];
            $tipos = "si";
            
            if ($excluirId) {
                $query .= " AND id_modulo != ?";
                $params[] = $excluirId;
                $tipos .= "i";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            
            $resultado = $stmt->get_result();
            $existe = $resultado->num_rows > 0;
            $stmt->close();
            
            return $existe;
            
        } catch (Exception $e) {
            error_log("Error al verificar título duplicado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar módulo
     */
    public function actualizar($id_modulo, $datos) {
        try {
            $query = "UPDATE modulos SET titulo = ?, descripcion = ?, id_profesor = ? WHERE id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssii", $datos['titulo'], $datos['descripcion'], $datos['id_profesor'], $id_modulo);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar módulo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar/Desactivar módulo
     */
    public function cambiarEstado($id_modulo, $activo) {
        try {
            $query = "UPDATE modulos SET activo = ? WHERE id_modulo = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $activo, $id_modulo);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al cambiar estado del módulo: " . $e->getMessage());
            return false;
        }
    }
}
