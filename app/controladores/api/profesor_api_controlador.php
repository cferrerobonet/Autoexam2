<?php
/**
 * API Controlador para Dashboard de Profesor - AUTOEXAM2
 * 
 * Proporciona endpoints para obtener datos del dashboard del profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class ProfesorAPIControlador {
    private $cursoModelo;
    private $examenModelo;
    private $usuarioModelo;
    
    public function __construct() {
        // Verificar autenticación
        if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'profesor') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }
        
        require_once APP_PATH . '/modelos/curso_modelo.php';
        require_once APP_PATH . '/modelos/examen_modelo.php';
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        
        $this->cursoModelo = new Curso();
        $this->examenModelo = new Examen();
        $this->usuarioModelo = new Usuario();
    }
    
    /**
     * Obtener cursos del profesor con información adicional
     */
    public function obtenerCursos() {
        try {
            $idProfesor = $_SESSION['id_usuario'];
            $cursos = $this->cursoModelo->obtenerCursosPorProfesor($idProfesor);
            
            // Enriquecer datos con información adicional
            foreach ($cursos as &$curso) {
                // Contar alumnos del curso
                $curso['num_alumnos'] = $this->usuarioModelo->contarAlumnosPorCurso($curso['id_curso']);
                
                // Contar exámenes activos del curso
                $curso['examenes_activos'] = $this->contarExamenesActivosPorCurso($curso['id_curso']);
                
                // Obtener nombre del módulo si existe
                if (isset($curso['id_modulo']) && $curso['id_modulo']) {
                    require_once APP_PATH . '/modelos/modulo_modelo.php';
                    $moduloModelo = new Modulo();
                    $modulo = $moduloModelo->obtenerPorId($curso['id_modulo']);
                    $curso['nombre_modulo'] = $modulo ? $modulo['titulo'] : null;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode($cursos);
            
        } catch (Exception $e) {
            error_log('Error en API cursos profesor: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener exámenes del profesor
     */
    public function obtenerExamenes() {
        try {
            $idProfesor = $_SESSION['id_usuario'];
            $examenes = $this->examenModelo->obtenerPorProfesor($idProfesor);
            
            // Ordenar por fecha de inicio descendente
            usort($examenes, function($a, $b) {
                return strtotime($b['fecha_inicio']) - strtotime($a['fecha_inicio']);
            });
            
            header('Content-Type: application/json');
            echo json_encode($examenes);
            
        } catch (Exception $e) {
            error_log('Error en API exámenes profesor: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener estadísticas del profesor
     */
    public function obtenerEstadisticas() {
        try {
            $idProfesor = $_SESSION['id_usuario'];
            
            // Obtener cursos del profesor
            $cursos = $this->cursoModelo->obtenerCursosPorProfesor($idProfesor);
            $totalCursos = count($cursos);
            
            // Obtener exámenes del profesor
            $examenes = $this->examenModelo->obtenerPorProfesor($idProfesor);
            $totalExamenes = count($examenes);
            
            // Contar exámenes por estado
            $examenesPendientes = 0;
            $examenesActivos = 0;
            $examenesCompletados = 0;
            
            foreach ($examenes as $examen) {
                $fechaInicio = strtotime($examen['fecha_inicio']);
                $fechaFin = strtotime($examen['fecha_fin']);
                $ahora = time();
                
                if ($examen['activo'] == 1 && $fechaInicio <= $ahora && $fechaFin >= $ahora) {
                    $examenesActivos++;
                } elseif ($examen['activo'] == 1 && $fechaInicio > $ahora) {
                    $examenesPendientes++;
                } else {
                    $examenesCompletados++;
                }
            }
            
            // Contar total de alumnos en los cursos del profesor
            $totalAlumnos = 0;
            foreach ($cursos as $curso) {
                $alumnosCurso = $this->usuarioModelo->contarAlumnosPorCurso($curso['id_curso']);
                $totalAlumnos += $alumnosCurso;
            }
            
            // Calcular promedio de notas (implementar más adelante)
            $promedioNotas = $this->calcularPromedioNotas($idProfesor);
            
            $estadisticas = [
                'total_cursos' => $totalCursos,
                'total_examenes' => $totalExamenes,
                'examenes_activos' => $examenesActivos,
                'examenes_pendientes' => $examenesPendientes,
                'examenes_completados' => $examenesCompletados,
                'total_alumnos' => $totalAlumnos,
                'promedio_notas' => $promedioNotas
            ];
            
            header('Content-Type: application/json');
            echo json_encode($estadisticas);
            
        } catch (Exception $e) {
            error_log('Error en API estadísticas profesor: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener notificaciones del profesor
     */
    public function obtenerNotificaciones() {
        try {
            $idProfesor = $_SESSION['id_usuario'];
            
            // Por ahora devolvemos notificaciones simuladas
            // Esto se puede expandir para obtener notificaciones reales de la BD
            $notificaciones = $this->obtenerNotificacionesReales($idProfesor);
            
            header('Content-Type: application/json');
            echo json_encode($notificaciones);
            
        } catch (Exception $e) {
            error_log('Error en API notificaciones profesor: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener eventos del calendario del profesor
     */
    public function obtenerCalendario() {
        try {
            $idProfesor = $_SESSION['id_usuario'];
            
            // Obtener exámenes como eventos del calendario
            $examenes = $this->examenModelo->obtenerPorProfesor($idProfesor);
            $eventos = [];
            
            foreach ($examenes as $examen) {
                $eventos[] = [
                    'id' => $examen['id_examen'],
                    'title' => $examen['titulo'],
                    'start' => $examen['fecha_inicio'],
                    'end' => $examen['fecha_fin'],
                    'backgroundColor' => $this->obtenerColorPorEstado($examen),
                    'borderColor' => $this->obtenerColorPorEstado($examen, true),
                    'textColor' => '#ffffff',
                    'url' => BASE_URL . '/examenes/ver/' . $examen['id_examen']
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode($eventos);
            
        } catch (Exception $e) {
            error_log('Error en API calendario profesor: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Métodos auxiliares privados
     */
    
    /**
     * Contar exámenes activos por curso
     */
    private function contarExamenesActivosPorCurso($idCurso) {
        try {
            $ahora = date('Y-m-d H:i:s');
            
            $query = "SELECT COUNT(*) as total FROM examenes 
                      WHERE id_curso = ? AND activo = 1 
                      AND fecha_inicio <= ? AND fecha_fin >= ?";
            
            $conexion = $this->examenModelo->getConexion();
            
            // Verificar si es PDO o mysqli
            if ($conexion instanceof PDO) {
                $stmt = $conexion->prepare($query);
                $stmt->execute([$idCurso, $ahora, $ahora]);
                $resultado = $stmt->fetch();
            } else {
                $stmt = $conexion->prepare($query);
                $stmt->bind_param("iss", $idCurso, $ahora, $ahora);
                $stmt->execute();
                $resultado = $stmt->get_result()->fetch_assoc();
            }
            
            return (int)($resultado['total'] ?? 0);
            
        } catch (Exception $e) {
            error_log('Error al contar exámenes activos por curso: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calcular promedio de notas del profesor
     */
    private function calcularPromedioNotas($idProfesor) {
        try {
            // Implementar cuando se tenga la tabla de calificaciones
            // Por ahora devolver 0
            return 0.0;
            
        } catch (Exception $e) {
            error_log('Error al calcular promedio de notas: ' . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Obtener notificaciones reales del profesor
     */
    private function obtenerNotificacionesReales($idProfesor) {
        try {
            // Verificar si existe tabla de notificaciones
            $conexion = $this->usuarioModelo->getConexion();
            
            if ($conexion instanceof PDO) {
                $stmt = $conexion->prepare("SHOW TABLES LIKE 'notificaciones'");
                $stmt->execute();
                $existeTabla = $stmt->rowCount() > 0;
            } else {
                $stmt = $conexion->prepare("SHOW TABLES LIKE 'notificaciones'");
                $stmt->execute();
                $existeTabla = $stmt->get_result()->num_rows > 0;
            }
            
            if ($existeTabla) {
                // Si existe la tabla, obtener notificaciones reales
                $query = "SELECT * FROM notificaciones 
                          WHERE destinatario_id = ? AND leida = 0 
                          ORDER BY fecha_creacion DESC LIMIT 10";
                
                if ($conexion instanceof PDO) {
                    $stmt = $conexion->prepare($query);
                    $stmt->execute([$idProfesor]);
                    return $stmt->fetchAll();
                } else {
                    $stmt = $conexion->prepare($query);
                    $stmt->bind_param("i", $idProfesor);
                    $stmt->execute();
                    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                }
            } else {
                // Si no existe la tabla, generar notificaciones basadas en datos reales
                return $this->generarNotificacionesBasadasEnDatos($idProfesor);
            }
            
        } catch (Exception $e) {
            error_log('Error al obtener notificaciones reales: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar notificaciones basadas en datos reales
     */
    private function generarNotificacionesBasadasEnDatos($idProfesor) {
        $notificaciones = [];
        
        try {
            // Verificar exámenes que cierran pronto
            $examenes = $this->examenModelo->obtenerPorProfesor($idProfesor);
            $ahora = time();
            
            foreach ($examenes as $examen) {
                $fechaFin = strtotime($examen['fecha_fin']);
                $diferencia = $fechaFin - $ahora;
                
                // Exámenes que cierran en las próximas 24 horas
                if ($diferencia > 0 && $diferencia <= 86400) {
                    $notificaciones[] = [
                        'titulo' => 'Recordatorio de examen',
                        'mensaje' => "El examen '{$examen['titulo']}' cierra mañana.",
                        'tipo' => 'recordatorio',
                        'fecha' => date('Y-m-d H:i:s', $ahora - 3600) // Hace 1 hora
                    ];
                }
            }
            
            // Verificar si hay exámenes sin corregir (implementar más adelante)
            
            return $notificaciones;
            
        } catch (Exception $e) {
            error_log('Error al generar notificaciones: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener color por estado del examen
     */
    private function obtenerColorPorEstado($examen, $esBorde = false) {
        $ahora = time();
        $fechaInicio = strtotime($examen['fecha_inicio']);
        $fechaFin = strtotime($examen['fecha_fin']);
        
        if ($examen['activo'] == 1 && $fechaInicio <= $ahora && $fechaFin >= $ahora) {
            return $esBorde ? '#2d8d47' : '#34A853'; // Verde
        } elseif ($examen['activo'] == 1 && $fechaInicio > $ahora) {
            return $esBorde ? '#e68a00' : '#FBBC05'; // Amarillo
        } else {
            return $esBorde ? '#3266c2' : '#4285F4'; // Azul
        }
    }
}
