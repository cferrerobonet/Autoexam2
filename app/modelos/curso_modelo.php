<?php
/**
 * Modelo para la gestión de cursos
 * 
 * Este archivo contiene la clase Curso que gestiona todas las operaciones
 * relacionadas con cursos en la base de datos.
 * 
 * @package AUTOEXAM2
 * @author Copilot, basado en documentación de Carlos Ferrero
 * @version 1.0
 * @since 16/06/2025
 */

class Curso {
    private $db;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->db = $GLOBALS['db'];
    }
    
    /**
     * Obtiene un curso por su ID
     * 
     * @param int $id_curso ID del curso a obtener
     * @return array|bool Datos del curso o false si no existe
     */
    public function obtenerPorId($id_curso) {
        try {
            $query = "SELECT c.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor 
                      FROM cursos c 
                      LEFT JOIN usuarios u ON c.id_profesor = u.id_usuario 
                      WHERE c.id_curso = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todos los cursos con paginación y filtros opcionales
     * 
     * @param int $limite Número de registros por página
     * @param int $pagina Número de página
     * @param array $filtros Filtros a aplicar (opcionales)
     * @return array Datos de los cursos y total de registros
     */
    public function obtenerTodos($limite = 10, $pagina = 1, $filtros = []) {
        try {
            $offset = ($pagina - 1) * $limite;
            $where = "WHERE 1=1";
            $params = [];
            $tipos = "";
            
            // Aplicar filtros
            if (!empty($filtros['nombre_curso'])) {
                $where .= " AND c.nombre_curso LIKE ?";
                $params[] = "%" . $filtros['nombre_curso'] . "%";
                $tipos .= "s";
            }
            
            if (isset($filtros['activo'])) {
                $where .= " AND c.activo = ?";
                $params[] = $filtros['activo'];
                $tipos .= "i";
            }
            
            if (!empty($filtros['id_profesor'])) {
                $where .= " AND c.id_profesor = ?";
                $params[] = $filtros['id_profesor'];
                $tipos .= "i";
            }
            
            // Definir orden de la consulta
            $orderBy = " ORDER BY c.nombre_curso ASC";
            
            // Aplicar ordenamiento si viene en los filtros
            if (isset($filtros['ordenar_por']) && isset($filtros['orden'])) {
                // Validar campos permitidos para evitar inyección SQL
                $camposPermitidos = [
                    'id_curso' => 'c.id_curso',
                    'nombre_curso' => 'c.nombre_curso',
                    'id_profesor' => 'c.id_profesor',
                    'activo' => 'c.activo',
                    'fecha_creacion' => 'c.fecha_creacion'
                ];
                
                // Si el campo es válido, ordenar por él
                if (array_key_exists($filtros['ordenar_por'], $camposPermitidos)) {
                    $campo = $camposPermitidos[$filtros['ordenar_por']];
                    $orden = $filtros['orden'] === 'DESC' ? 'DESC' : 'ASC';
                    $orderBy = " ORDER BY $campo $orden";
                    
                    // Ordenación secundaria para mantener consistencia
                    if ($campo !== 'c.nombre_curso') {
                        $orderBy .= ", c.nombre_curso ASC";
                    }
                }
            }
            
            // Consulta principal
            $query = "SELECT c.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor,
                      (SELECT COUNT(*) FROM curso_alumno ca WHERE ca.id_curso = c.id_curso) as num_alumnos 
                      FROM cursos c 
                      LEFT JOIN usuarios u ON c.id_profesor = u.id_usuario 
                      $where 
                      $orderBy 
                      LIMIT ?, ?";
            
            // Preparar y ejecutar la consulta
            $stmt = $this->db->prepare($query);
            
            // Añadir parámetros de límite
            $params[] = $offset;
            $params[] = $limite;
            $tipos .= "ii";
            
            // Enlazar parámetros dinámicamente
            if (!empty($params)) {
                $stmt->bind_param($tipos, ...$params);
            }
            
            $stmt->execute();
            $resultado = $stmt->get_result();
            $cursos = $resultado->fetch_all(MYSQLI_ASSOC);
            
            // Obtener el total de registros
            $queryTotal = "SELECT COUNT(*) as total FROM cursos c $where";
            $stmtTotal = $this->db->prepare($queryTotal);
            
            // Eliminar los últimos 2 elementos (offset y límite)
            array_pop($params);
            array_pop($params);
            
            if (!empty($params)) {
                $stmtTotal->bind_param(substr($tipos, 0, -2), ...$params);
            }
            
            $stmtTotal->execute();
            $resultadoTotal = $stmtTotal->get_result();
            $totalRegistros = $resultadoTotal->fetch_assoc()['total'];
            
            return [
                'cursos' => $cursos,
                'total' => $totalRegistros,
                'paginas' => ceil($totalRegistros / $limite)
            ];
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener cursos: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [
                'cursos' => [],
                'total' => 0,
                'paginas' => 0
            ];
        }
    }
    
    /**
     * Crea un nuevo curso
     * 
     * @param array $datos Datos del curso a crear
     * @return int|bool ID del curso creado o false si hay error
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO cursos (nombre_curso, descripcion, id_profesor, activo) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $activo = isset($datos['activo']) ? 1 : 0;
            $stmt->bind_param("ssii", 
                $datos['nombre_curso'], 
                $datos['descripcion'], 
                $datos['id_profesor'],
                $activo
            );
            
            if ($stmt->execute()) {
                $id_curso = $stmt->insert_id;
                
                // Registrar actividad
                $this->registrarActividad('crear', $id_curso);
                
                return $id_curso;
            } else {
                // Registrar error específico de MySQL
                error_log("Error al ejecutar consulta de creación de curso: " . $stmt->error, 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
                return false;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al crear curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Actualiza un curso existente
     * 
     * @param int $id_curso ID del curso a actualizar
     * @param array $datos Datos a actualizar
     * @return bool Éxito de la operación
     */
    public function actualizar($id_curso, $datos) {
        try {
            $query = "UPDATE cursos SET 
                      nombre_curso = ?, 
                      descripcion = ?, 
                      id_profesor = ?, 
                      activo = ? 
                      WHERE id_curso = ?";
            $stmt = $this->db->prepare($query);
            $activo = isset($datos['activo']) ? 1 : 0;
            $stmt->bind_param("ssiii", 
                $datos['nombre_curso'], 
                $datos['descripcion'], 
                $datos['id_profesor'],
                $activo,
                $id_curso
            );
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad('actualizar', $id_curso);
                
                return true;
            } else {
                // Registrar error específico de MySQL
                error_log("Error al ejecutar consulta de actualización de curso: " . $stmt->error, 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
                return false;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al actualizar curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Desactiva un curso (eliminación lógica)
     * 
     * @param int $id_curso ID del curso a desactivar
     * @return bool Éxito de la operación
     */
    public function desactivar($id_curso) {
        try {
            $query = "UPDATE cursos SET activo = 0 WHERE id_curso = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad('desactivar', $id_curso);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al desactivar curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Elimina físicamente un curso (solo modo mantenimiento)
     * 
     * @param int $id_curso ID del curso a eliminar
     * @return bool Éxito de la operación
     */
    public function eliminar($id_curso) {
        try {
            // Verificar que no tenga módulos ni exámenes asociados
            if ($this->tieneModulosOExamenes($id_curso)) {
                return false;
            }
            
            // Eliminar relaciones con alumnos
            $query1 = "DELETE FROM curso_alumno WHERE id_curso = ?";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->bind_param("i", $id_curso);
            $stmt1->execute();
            
            // Eliminar el curso
            $query2 = "DELETE FROM cursos WHERE id_curso = ?";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bind_param("i", $id_curso);
            
            if ($stmt2->execute()) {
                // Registrar actividad
                $this->registrarActividad('eliminar', $id_curso);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al eliminar curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Verifica si un curso tiene módulos o exámenes asociados
     * 
     * @param int $id_curso ID del curso a verificar
     * @return bool True si tiene módulos o exámenes, false en caso contrario
     */
    private function tieneModulosOExamenes($id_curso) {
        try {
            $query = "SELECT COUNT(*) as total FROM modulo_curso WHERE id_curso = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_assoc()['total'] > 0;
        } catch (Exception $e) {
            // En caso de error, asumir que sí tiene para prevenir eliminación incorrecta
            return true;
        }
    }
    
    /**
     * Asigna un alumno a un curso
     * 
     * @param int $id_curso ID del curso
     * @param int $id_alumno ID del alumno
     * @return bool Éxito de la operación
     */
    public function asignarAlumno($id_curso, $id_alumno) {
        try {
            // Validar que los IDs son enteros positivos
            $id_curso = (int)$id_curso;
            $id_alumno = (int)$id_alumno;
            
            if ($id_curso <= 0 || $id_alumno <= 0) {
                error_log("Error: IDs inválidos - curso: {$id_curso}, alumno: {$id_alumno}", 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
                return false;
            }
            
            // Verificar que no exista ya la asignación
            $queryVerificar = "SELECT COUNT(*) as total FROM curso_alumno WHERE id_curso = ? AND id_alumno = ?";
            $stmtVerificar = $this->db->prepare($queryVerificar);
            $stmtVerificar->bind_param("ii", $id_curso, $id_alumno);
            $stmtVerificar->execute();
            $resultado = $stmtVerificar->get_result();
            
            if ($resultado->fetch_assoc()['total'] > 0) {
                error_log("Asignación ya existe - curso: {$id_curso}, alumno: {$id_alumno}", 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_debug.log");
                return true; // Ya está asignado
            }
            
            $query = "INSERT INTO curso_alumno (id_curso, id_alumno) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $id_curso, $id_alumno);
            
            if ($stmt->execute()) {
                // Registrar actividad y resultado en logs
                error_log("Éxito: Alumno {$id_alumno} asignado a curso {$id_curso}", 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_debug.log");
                $this->registrarActividad('asignar_alumno', $id_curso, $id_alumno);
                return true;
            }
            
            error_log("Error en execute() - " . $this->db->error, 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al asignar alumno a curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Desasigna un alumno de un curso
     * 
     * @param int $id_curso ID del curso
     * @param int $id_alumno ID del alumno
     * @return bool Éxito de la operación
     */
    public function desasignarAlumno($id_curso, $id_alumno) {
        try {
            $query = "DELETE FROM curso_alumno WHERE id_curso = ? AND id_alumno = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $id_curso, $id_alumno);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad('desasignar_alumno', $id_curso, $id_alumno);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al desasignar alumno de curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene los alumnos asignados a un curso
     * 
     * @param int $id_curso ID del curso
     * @return array Lista de alumnos
     */
    public function obtenerAlumnos($id_curso) {
        try {
            $query = "SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, ca.fecha_asignacion  
                      FROM curso_alumno ca 
                      JOIN usuarios u ON ca.id_alumno = u.id_usuario 
                      WHERE ca.id_curso = ? 
                      ORDER BY u.apellidos, u.nombre";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener alumnos de curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [];
        }
    }
    
    /**
     * Obtiene los alumnos que no están asignados a un curso
     * 
     * @param int $id_curso ID del curso
     * @return array Lista de alumnos no asignados
     */
    public function obtenerAlumnosNoAsignados($id_curso) {
        try {
            $query = "SELECT id_usuario, nombre, apellidos, correo 
                      FROM usuarios 
                      WHERE rol = 'alumno' AND activo = 1 
                      AND id_usuario NOT IN (
                          SELECT id_alumno FROM curso_alumno WHERE id_curso = ?
                      )
                      ORDER BY apellidos, nombre";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener alumnos no asignados: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [];
        }
    }
    
    /**
     * Obtiene los cursos para un profesor específico
     * 
     * @param int $id_profesor ID del profesor
     * @param bool $solo_activos Solo incluir cursos activos
     * @return array Lista de cursos
     */
    public function obtenerCursosPorProfesor($id_profesor, $solo_activos = true) {
        try {
            $query = "SELECT * FROM cursos WHERE id_profesor = ?";
            
            if ($solo_activos) {
                $query .= " AND activo = 1";
            }
            
            $query .= " ORDER BY nombre_curso";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_profesor);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener cursos por profesor: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [];
        }
    }
    

    
    /**
     * Obtiene los cursos para un alumno específico
     * 
     * @param int $id_alumno ID del alumno
     * @param bool $solo_activos Solo incluir cursos activos
     * @return array Lista de cursos
     */
    public function obtenerCursosPorAlumno($id_alumno, $solo_activos = true) {
        try {
            $query = "SELECT c.*, u.nombre as nombre_profesor, u.apellidos as apellidos_profesor
                      FROM cursos c
                      JOIN curso_alumno ca ON c.id_curso = ca.id_curso
                      JOIN usuarios u ON c.id_profesor = u.id_usuario
                      WHERE ca.id_alumno = ?";
            
            if ($solo_activos) {
                $query .= " AND c.activo = 1";
            }
            
            $query .= " ORDER BY c.nombre_curso";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_alumno);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener cursos por alumno: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [];
        }
    }
    
    /**
     * Obtener profesores disponibles para asignar a cursos
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
            // Registrar error en log
            error_log("Error al obtener profesores: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return [];
        }
    }
    
    /**
     * Registra actividad relacionada con cursos
     * 
     * @param string $accion Tipo de acción realizada
     * @param int $id_curso ID del curso afectado
     * @param int $id_relacionado ID adicional relacionado (opcional)
     * @return bool Éxito del registro
     */
    private function registrarActividad($accion, $id_curso, $id_relacionado = null) {
        // Verificar si existe la tabla y clase de registro de actividad
        if (class_exists('RegistroActividad')) {
            try {
                require_once __DIR__ . '/registro_actividad_modelo.php';
                $registro = new RegistroActividad();
                
                $datos = [
                    'modulo' => 'cursos',
                    'accion' => $accion,
                    'id_registro' => $id_curso
                ];
                
                if ($id_relacionado) {
                    $datos['datos_adicionales'] = json_encode(['id_relacionado' => $id_relacionado]);
                }
                
                return $registro->registrar($datos);
            } catch (Exception $e) {
                error_log("Error al registrar actividad: " . $e->getMessage(), 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
                return false;
            }
        }
        
        return true; // Si no existe el sistema de registro, consideramos exitosa la operación
    }
    
    /**
     * Actualiza el estado de activación de un curso
     * 
     * @param int $id_curso ID del curso
     * @param int $activo Nuevo estado (0=inactivo, 1=activo)
     * @return bool true si se actualizó correctamente
     * @throws Exception Si hay error en la actualización
     */
    public function actualizarEstado($id_curso, $activo) {
        try {
            $id_curso = (int)$id_curso;
            $activo = (int)$activo;
            
            if ($id_curso <= 0) {
                throw new Exception("ID de curso inválido");
            }
            
            // Solo permitir valores 0 o 1
            if ($activo !== 0 && $activo !== 1) {
                $activo = 0;
            }
            
            $query = "UPDATE cursos SET activo = ?, fecha_modificacion = NOW() WHERE id_curso = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $activo, $id_curso);
            $result = $stmt->execute();
            
            if ($result && $stmt->affected_rows > 0) {
                return true;
            } else if ($result) {
                // No hubo cambios pero la consulta fue exitosa
                return true;
            } else {
                throw new Exception("Error al actualizar estado del curso");
            }
        } catch (Exception $e) {
            error_log("Error al actualizar estado de curso ID $id_curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            throw $e;
        }
    }
    
    /**
     * Activa un curso previamente desactivado
     * 
     * @param int $id_curso ID del curso a activar
     * @return bool Éxito de la operación
     */
    public function activar($id_curso) {
        try {
            $query = "UPDATE cursos SET activo = 1 WHERE id_curso = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            
            if ($stmt->execute()) {
                // Registrar actividad
                $this->registrarActividad('activar', $id_curso);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al activar curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene todos los alumnos asignados a un curso específico
     * 
     * @param int $id_curso ID del curso
     * @return array|bool Lista de alumnos o false si hay error
     */
    public function obtenerAlumnosPorCurso($id_curso) {
        try {
            $query = "SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, u.activo, 
                      ca.fecha_asignacion
                      FROM usuarios u 
                      INNER JOIN curso_alumno ca ON u.id_usuario = ca.id_alumno 
                      WHERE ca.id_curso = ? AND u.rol = 'alumno'
                      ORDER BY u.apellidos, u.nombre";
            
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_curso);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            $alumnos = [];
            while ($alumno = $resultado->fetch_assoc()) {
                $alumnos[] = $alumno;
            }
            
            return $alumnos;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener alumnos del curso: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return false;
        }
    }
    
    /**
     * Obtiene el ID del curso al que está asignado un alumno
     * 
     * @param int $id_alumno ID del alumno
     * @return int|null ID del curso o null si no está asignado
     */
    public function obtenerCursoDeAlumno($id_alumno) {
        try {
            $query = "SELECT id_curso FROM curso_alumno WHERE id_alumno = ? LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id_alumno);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                $row = $resultado->fetch_assoc();
                return $row['id_curso'];
            }
            
            return null;
        } catch (Exception $e) {
            // Registrar error en log
            error_log("Error al obtener curso del alumno: " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/cursos_error.log");
            return null;
        }
    }
}
