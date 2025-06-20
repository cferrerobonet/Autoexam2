<?php
/**
 * Modelo de Módulos - AUTOEXAM2
 * 
 * Gestiona las operaciones de base de datos para módulos
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class ModuloModelo {
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtener todos los módulos con filtros y paginación
     * 
     * @param int $limite Registros por página
     * @param int $pagina Página actual
     * @param array $filtros Filtros a aplicar
     * @return array Resultado con módulos, total y páginas
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
            
            // Definir orden de la consulta
            $orderBy = " ORDER BY m.titulo ASC";
            
            // Aplicar ordenamiento si viene en los filtros
            if (isset($filtros['ordenar_por']) && isset($filtros['orden'])) {
                $camposPermitidos = [
                    'id_modulo' => 'm.id_modulo',
                    'titulo' => 'm.titulo',
                    'apellidos' => 'u.apellidos',
                    'total_examenes' => 'total_examenes'
                ];
                
                if (isset($camposPermitidos[$filtros['ordenar_por']])) {
                    $campo = $camposPermitidos[$filtros['ordenar_por']];
                    $direccion = (strtoupper($filtros['orden']) === 'DESC') ? 'DESC' : 'ASC';
                    $orderBy = " ORDER BY $campo $direccion";
                }
            }
            
            // Consulta principal con JOIN
            $query = "SELECT m.id_modulo, m.titulo, m.descripcion, m.id_profesor, m.activo,
                             u.nombre, u.apellidos,
                             COUNT(e.id_examen) as total_examenes
                      FROM modulos m
                      LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                      LEFT JOIN examenes e ON m.id_modulo = e.id_modulo
                      $where
                      GROUP BY m.id_modulo, m.titulo, m.descripcion, m.id_profesor, m.activo, u.nombre, u.apellidos
                      $orderBy
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
            
            // Contar total sin LIMIT
            $queryCount = "SELECT COUNT(DISTINCT m.id_modulo) as total
                          FROM modulos m
                          LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                          $where";
            
            // Usar los mismos parámetros pero sin LIMIT y OFFSET
            $paramsCount = array_slice($params, 0, -2);
            $tiposCount = substr($tipos, 0, -2);
            
            $stmtCount = $this->db->prepare($queryCount);
            if (!empty($paramsCount)) {
                $stmtCount->bind_param($tiposCount, ...$paramsCount);
            }
            $stmtCount->execute();
            $resultadoCount = $stmtCount->get_result();
            $total = $resultadoCount->fetch_assoc()['total'];
            
            return [
                'modulos' => $modulos,
                'total' => $total,
                'paginas' => ceil($total / $limite)
            ];
        } catch (Exception $e) {
            error_log("Error al obtener módulos: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return [
                'modulos' => [],
                'total' => 0,
                'paginas' => 1
            ];
        }
    }
    
    /**
     * Crear nuevo módulo
     * 
     * @param array $datos Datos del módulo
     * @return int|false ID del módulo creado o false en caso de error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO modulos (titulo, descripcion, id_profesor, activo) VALUES (?, ?, ?, 1)";
            $stmt = $this->conexion->prepare($query);
            
            if ($stmt->execute([$datos['titulo'], $datos['descripcion'], $datos['id_profesor']])) {
                $id_modulo = $this->conexion->lastInsertId();
                
                // Registrar actividad
                $this->registrarActividad(
                    'crear_modulo',
                    "Módulo creado: {$datos['titulo']}",
                    'modulos',
                    $id_modulo
                );
                
                return $id_modulo;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al crear módulo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener módulo por ID
     * 
     * @param int $id_modulo ID del módulo
     * @return array|null Datos del módulo
     */
    public function obtenerPorId($id_modulo) {
        try {
            $query = "SELECT m.*, u.nombre, u.apellidos 
                      FROM modulos m
                      LEFT JOIN usuarios u ON m.id_profesor = u.id_usuario
                      WHERE m.id_modulo = ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("i", $id_modulo);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener módulo por ID: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return null;
        }
    }
    
    /**
     * Actualizar módulo
     * 
     * @param int $id_modulo ID del módulo
     * @param array $datos Nuevos datos
     * @return bool Resultado de la operación
     */
    public function actualizar($id_modulo, $datos) {
        try {
            $query = "UPDATE modulos SET titulo = ?, descripcion = ?, id_profesor = ? WHERE id_modulo = ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("ssii", $datos['titulo'], $datos['descripcion'], $datos['id_profesor'], $id_modulo);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad(
                    'editar_modulo',
                    "Módulo editado: {$datos['titulo']}",
                    'modulos',
                    $id_modulo
                );
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al actualizar módulo: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return false;
        }
    }
    
    /**
     * Desactivar módulo (eliminación lógica)
     * 
     * @param int $id_modulo ID del módulo
     * @return bool Resultado de la operación
     */
    public function desactivar($id_modulo) {
        try {
            // Obtener información del módulo antes de desactivar
            $modulo = $this->obtenerPorId($id_modulo);
            if (!$modulo) {
                return false;
            }
            
            // Desactivar el módulo
            $query = "UPDATE modulos SET activo = 0 WHERE id_modulo = ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("i", $id_modulo);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad(
                    'desactivar_modulo',
                    "Módulo desactivado: {$modulo['titulo']}",
                    'modulos',
                    $id_modulo
                );
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al desactivar módulo: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return false;
        }
    }
    
    /**
     * Activar módulo
     * 
     * @param int $id_modulo ID del módulo
     * @return bool Resultado de la operación
     */
    public function activar($id_modulo) {
        try {
            // Obtener información del módulo
            $modulo = $this->obtenerPorId($id_modulo);
            if (!$modulo) {
                return false;
            }
            
            // Activar el módulo
            $query = "UPDATE modulos SET activo = 1 WHERE id_modulo = ?";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("i", $id_modulo);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad(
                    'activar_modulo',
                    "Módulo activado: {$modulo['titulo']}",
                    'modulos',
                    $id_modulo
                );
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error al activar módulo: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return false;
        }
    }
    
    /**
     * Obtener profesores disponibles
     * 
     * @return array Lista de profesores
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
            error_log("Error al obtener profesores: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return [];
        }
    }
    
    /**
     * Contar módulos por estado
     * 
     * @return array Estadísticas de módulos
     */
    public function obtenerEstadisticas() {
        try {
            $query = "SELECT 
                      COUNT(*) as total,
                      COUNT(CASE WHEN id_profesor IS NOT NULL THEN 1 END) as con_profesor,
                      COUNT(CASE WHEN id_profesor IS NULL THEN 1 END) as sin_profesor
                      FROM modulos";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de módulos: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return [
                'total' => 0,
                'con_profesor' => 0,
                'sin_profesor' => 0
            ];
        }
    }
    
    /**
     * Registrar actividad en el log
     * 
     * @param string $accion Acción realizada
     * @param string $descripcion Descripción de la acción
     * @param string $tabla Tabla afectada
     * @param int $id_registro ID del registro afectado
     */
    private function registrarActividad($accion, $descripcion, $tabla, $id_registro) {
        try {
            // Solo registrar si está habilitado el log de actividades
            if (defined('LOG_ACTIVIDADES') && LOG_ACTIVIDADES) {
                $archivo_log = __DIR__ . "/../../almacenamiento/logs/app/actividad_modulos.log";
                $timestamp = date('Y-m-d H:i:s');
                $usuario_id = $_SESSION['id_usuario'] ?? 'anonimo';
                $linea = "[{$timestamp}] Usuario:{$usuario_id} Acción:{$accion} Tabla:{$tabla} ID:{$id_registro} - {$descripcion}" . PHP_EOL;
                file_put_contents($archivo_log, $linea, FILE_APPEND | LOCK_EX);
            }
        } catch (Exception $e) {
            error_log("Error al registrar actividad: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener todos los cursos disponibles
     * 
     * @return array Lista de cursos
     */
    public function obtenerCursos() {
        try {
            $query = "SELECT id_curso, nombre_curso, descripcion 
                      FROM cursos 
                      WHERE activo = 1 
                      ORDER BY nombre_curso ASC";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cursos = [];
            while ($curso = $result->fetch_assoc()) {
                $cursos[] = $curso;
            }
            
            return $cursos;
        } catch (Exception $e) {
            error_log("Error al obtener cursos: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return [];
        }
    }
    
    /**
     * Obtener cursos de un profesor específico
     * 
     * @param int $id_profesor ID del profesor
     * @return array Lista de cursos del profesor
     */
    public function obtenerCursosPorProfesor($id_profesor) {
        try {
            $query = "SELECT id_curso, nombre_curso, descripcion 
                      FROM cursos 
                      WHERE id_profesor = ? AND activo = 1 
                      ORDER BY nombre_curso ASC";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("i", $id_profesor);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $cursos = [];
            while ($curso = $result->fetch_assoc()) {
                $cursos[] = $curso;
            }
            
            return $cursos;
        } catch (Exception $e) {
            error_log("Error al obtener cursos del profesor: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return [];
        }
    }
    
    /**
     * Asignar módulo a múltiples cursos
     * 
     * @param int $id_modulo ID del módulo
     * @param array $cursos_ids Array de IDs de cursos
     * @return bool Resultado de la operación
     */
    public function asignarCursos($id_modulo, $cursos_ids) {
        try {
            // Primero, eliminar asignaciones existentes
            $query_delete = "DELETE FROM modulo_curso WHERE id_modulo = ?";
            $stmt_delete = $this->conexion->prepare($query_delete);
            $stmt_delete->bind_param("i", $id_modulo);
            $stmt_delete->execute();
            
            // Luego, insertar las nuevas asignaciones
            $query_insert = "INSERT INTO modulo_curso (id_modulo, id_curso) VALUES (?, ?)";
            $stmt_insert = $this->conexion->prepare($query_insert);
            
            $exito = true;
            foreach ($cursos_ids as $id_curso) {
                $stmt_insert->bind_param("ii", $id_modulo, $id_curso);
                if (!$stmt_insert->execute()) {
                    $exito = false;
                    break;
                }
            }
            
            // Registrar actividad
            if ($exito) {
                $this->registrarActividad(
                    'asignar_cursos_modulo',
                    "Módulo asignado a " . count($cursos_ids) . " curso(s)",
                    'modulos',
                    $id_modulo
                );
            }
            
            return $exito;
        } catch (Exception $e) {
            error_log("Error al asignar cursos: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/modulos_error.log");
            return false;
        }
    }
}
