<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/controladores/inicio_controlador.php

/**
 * Controlador de Inicio - AUTOEXAM2
 * 
 * Gestiona la página principal y el dashboard según el rol del usuario
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.3
 */
class InicioControlador {
    private $usuarioModelo;
    private Sesion $sesion; // Se declara tipo Sesion
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cargar utilidades necesarias
        require_once APP_PATH . '/utilidades/sesion.php';
        $this->sesion = new Sesion();
        
        // Cargar modelos necesarios
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        $this->usuarioModelo = new Usuario();
        
        // Si existe el modelo de cursos, lo cargamos para poder utilizarlo en los dashboards
        if (file_exists(APP_PATH . '/modelos/curso_modelo.php')) {
            require_once APP_PATH . '/modelos/curso_modelo.php';
            $this->cursoModelo = new Curso();
        }
    }
    
    /**
     * Método predeterminado - Muestra el dashboard según rol
     */
    public function index() {
        // Verificar sesión (el ruteador ya hizo la verificación básica)
        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
            header('Location: ' . BASE_URL . '/autenticacion/login');
            exit;
        }
        
        // Determinar dashboard según rol
        $metodo = 'dashboard' . ucfirst($_SESSION['rol']);
        if (method_exists($this, $metodo)) {
            $this->$metodo();
        } else {
            $this->dashboardGenerico();
        }
    }
    
    /**
     * Dashboard genérico para roles no reconocidos
     */
    private function dashboardGenerico() {
        session_destroy();
        header('Location: ' . BASE_URL . '/autenticacion/login');
        exit;
    }
    
    /**
     * Dashboard para administradores
     */
    private function dashboardAdmin() {
        // Obtener datos del usuario actual
        $usuarioActual = $this->usuarioModelo->buscarPorId($_SESSION['id_usuario']);
        
        // Cargar estadísticas y conteos (serían reemplazados por datos reales)
        $estadisticas = $this->obtenerEstadisticasAdmin();
        
        // Definir datos para la vista
        $datos = [
            'titulo' => 'Panel de Administración',
            'usuario' => $usuarioActual,
            'estadisticas' => $estadisticas,
            'css_adicional' => [
                '/publico/recursos/css/admin.css'
            ],
            'js_adicional' => [
                '/publico/recursos/js/admin_dashboard.js'
            ]
        ];
        
        // Cargar vista
        require_once APP_PATH . '/vistas/admin/dashboard.php';
    }
    
    /**
     * Dashboard para profesores
     */
    private function dashboardProfesor() {
        try {
            // Obtener datos del usuario actual
            $usuarioActual = $this->usuarioModelo->buscarPorId($_SESSION['id_usuario']);
            
            // Si no se pudo obtener el usuario de la BD, usar datos de sesión
            if (!$usuarioActual) {
                $usuarioActual = [
                    'id_usuario' => $_SESSION['id_usuario'],
                    'nombre' => $_SESSION['nombre'] ?? 'Usuario',
                    'apellidos' => $_SESSION['apellidos'] ?? '',
                    'correo' => $_SESSION['correo'] ?? '',
                    'rol' => $_SESSION['rol'] ?? 'profesor'
                ];
            }
        } catch (Exception $e) {
            error_log('Error al obtener datos del usuario en dashboardProfesor: ' . $e->getMessage());
            // Si hay error, usar datos de sesión
            $usuarioActual = [
                'id_usuario' => $_SESSION['id_usuario'],
                'nombre' => $_SESSION['nombre'] ?? 'Usuario',
                'apellidos' => $_SESSION['apellidos'] ?? '',
                'correo' => $_SESSION['correo'] ?? '',
                'rol' => $_SESSION['rol'] ?? 'profesor'
            ];
        }
        
        // Cargar cursos y exámenes del profesor (serían reemplazados por datos reales)
        $cursos = $this->obtenerCursosProfesor($_SESSION['id_usuario']);
        $examenes = $this->obtenerExamenesProfesor($_SESSION['id_usuario']);
        
        // Definir datos para la vista
        $datos = [
            'titulo' => 'Panel de Profesor',
            'usuario' => $usuarioActual,
            'cursos' => $cursos,
            'examenes' => $examenes,
            'css_adicional' => [
                '/publico/recursos/css/profesor.css'
            ],
            'js_adicional' => [
                '/publico/recursos/js/profesor_dashboard.js'
            ]
        ];
        
        // Asegurar que el HTML esté completo
        $titulo = 'Panel de Profesor';
        require_once APP_PATH . '/vistas/parciales/head_profesor.php';
        echo '<body class="bg-light">';
        require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
        echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
        
        require_once APP_PATH . '/vistas/profesor/dashboard.php';
        
        echo '</div></div></div>';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
        echo '</body></html>';
    }
    
    /**
     * Dashboard para alumnos
     */
    private function dashboardAlumno() {
        // Obtener datos del usuario actual
        $usuarioActual = $this->usuarioModelo->buscarPorId($_SESSION['id_usuario']);
        
        // Cargar exámenes y calificaciones del alumno (serían reemplazados por datos reales)
        $examenesActivos = $this->obtenerExamenesAlumno($_SESSION['id_usuario'], true);
        $calificaciones = $this->obtenerCalificacionesAlumno($_SESSION['id_usuario']);
        
        // Cargar cursos del alumno
        $cursosAlumno = $this->obtenerCursosAlumno($_SESSION['id_usuario']);
        
        // Definir datos para la vista
        $datos = [
            'titulo' => 'Panel de Alumno',
            'usuario' => $usuarioActual,
            'examenes_activos' => $examenesActivos,
            'calificaciones' => $calificaciones,
            'cursos_alumno' => $cursosAlumno,
            'css_adicional' => [
                '/publico/recursos/css/alumno.css'
            ],
            'js_adicional' => [
                '/publico/recursos/js/alumno_dashboard.js'
            ]
        ];
        
        // Cargar vista
        require_once APP_PATH . '/vistas/alumno/dashboard.php';
    }
    
    /**
     * Obtiene estadísticas para el dashboard de admin
     * 
     * @return array Datos estadísticos 
     */
    private function obtenerEstadisticasAdmin() {
        // Aquí iría la lógica real para obtener estadísticas de la BD
        // Por ahora devolvemos datos de ejemplo
        return [
            'conteo' => [
                'administradores' => 3,
                'profesores' => 12,
                'alumnos' => 145,
                'cursos_activos' => 8
            ],
            'actividad_reciente' => [
                [
                    'tipo' => 'usuario_creado',
                    'descripcion' => 'María López (alumno) ha sido registrado',
                    'tiempo' => '30 minutos',
                    'usuario' => 'Admin'
                ],
                [
                    'tipo' => 'curso_modificado',
                    'descripcion' => 'Matemáticas 3º ESO - Añadido nuevo módulo',
                    'tiempo' => '2 horas',
                    'usuario' => 'Admin'
                ],
                [
                    'tipo' => 'backup_sistema',
                    'descripcion' => 'Backup automático completo: BD y archivos',
                    'tiempo' => '3 días',
                    'usuario' => 'Sistema'
                ]
            ]
        ];
    }
    
    /**
     * Obtiene los cursos asignados a un profesor
     * 
     * @param int $idProfesor ID del profesor
     * @return array Lista de cursos 
     */
    private function obtenerCursosProfesor($idProfesor) {
        // Aquí iría la lógica real para obtener cursos de la BD
        // Por ahora devolvemos datos de ejemplo
        return [
            [
                'id' => 1,
                'nombre' => 'Matemáticas 3º ESO',
                'curso_escolar' => '2024-2025',
                'modulo' => 'Álgebra',
                'num_alumnos' => 24,
                'examenes_activos' => 3
            ],
            [
                'id' => 2,
                'nombre' => 'Biología 4º ESO',
                'curso_escolar' => '2024-2025',
                'modulo' => 'Genética',
                'num_alumnos' => 18,
                'examenes_activos' => 1
            ]
        ];
    }
    
    /**
     * Obtiene los exámenes de un profesor
     * 
     * @param int $idProfesor ID del profesor
     * @return array Lista de exámenes 
     */
    private function obtenerExamenesProfesor($idProfesor) {
        // Aquí iría la lógica real para obtener exámenes de la BD
        // Por ahora devolvemos datos de ejemplo
        return [
            [
                'id' => 1,
                'titulo' => 'Ecuaciones de segundo grado',
                'curso' => 'Matemáticas 3º ESO',
                'modulo' => 'Álgebra',
                'fecha' => '15/06/2025',
                'estado' => 'activo'
            ],
            [
                'id' => 2,
                'titulo' => 'Teoría de la evolución',
                'curso' => 'Biología 4º ESO',
                'modulo' => 'Genética',
                'fecha' => '10/06/2025',
                'estado' => 'por_corregir'
            ],
            [
                'id' => 3,
                'titulo' => 'Problemas con fracciones',
                'curso' => 'Matemáticas 3º ESO',
                'modulo' => 'Álgebra',
                'fecha' => '01/06/2025',
                'estado' => 'finalizado'
            ]
        ];
    }
    
    /**
     * Obtiene los exámenes de un alumno
     * 
     * @param int $idAlumno ID del alumno
     * @param bool $soloActivos Si true, retorna solo exámenes activos
     * @return array Lista de exámenes
     */
    private function obtenerExamenesAlumno($idAlumno, $soloActivos = false) {
        // Aquí iría la lógica real para obtener exámenes de la BD
        // Por ahora devolvemos datos de ejemplo
        $examenes = [
            [
                'id' => 1,
                'titulo' => 'Ecuaciones de segundo grado',
                'curso' => 'Matemáticas 3º ESO',
                'modulo' => 'Álgebra',
                'fecha_limite' => '20/06/2025',
                'tiempo_restante' => '2d 5h 32m',
                'activo' => true
            ],
            [
                'id' => 2,
                'titulo' => 'Teoría de la evolución',
                'curso' => 'Biología 4º ESO',
                'modulo' => 'Genética',
                'fecha_limite' => '25/06/2025',
                'tiempo_restante' => '10d 2h 15m',
                'activo' => true
            ]
        ];
        
        if ($soloActivos) {
            return array_filter($examenes, function($examen) {
                return $examen['activo'] === true;
            });
        }
        
        return $examenes;
    }
    
    /**
     * Obtiene las calificaciones de un alumno
     * 
     * @param int $idAlumno ID del alumno
     * @return array Lista de calificaciones
     */
    private function obtenerCalificacionesAlumno($idAlumno) {
        // Aquí iría la lógica real para obtener calificaciones de la BD
        // Por ahora devolvemos datos de ejemplo
        return [
            [
                'id' => 1,
                'examen' => 'Problemas con fracciones',
                'curso' => 'Matemáticas 3º ESO',
                'modulo' => 'Álgebra',
                'fecha' => '01/06/2025',
                'calificacion' => 8.5,
                'estado' => 'aprobado'
            ],
            [
                'id' => 2,
                'examen' => 'Principios de genética',
                'curso' => 'Biología 4º ESO',
                'modulo' => 'Genética',
                'fecha' => '15/05/2025',
                'calificacion' => 4.2,
                'estado' => 'no_aprobado'
            ],
            [
                'id' => 3,
                'examen' => 'Preposiciones inglesas',
                'curso' => 'Inglés 3º ESO',
                'modulo' => 'Gramática',
                'fecha' => '05/05/2025',
                'calificacion' => 7.8,
                'estado' => 'aprobado'
            ]
        ];
    }
    
    /**
     * Obtiene los cursos de un alumno
     * 
     * @param int $idAlumno ID del alumno
     * @return array Lista de cursos
     */
    private function obtenerCursosAlumno($idAlumno) {
        // Aquí iría la lógica real para obtener cursos de la BD
        // Por ahora devolvemos datos de ejemplo que se mostrarán hasta que el módulo de cursos esté completamente integrado
        return [
            [
                'id_curso' => 1,
                'nombre_curso' => 'Matemáticas 3º ESO',
                'descripcion' => 'Curso de matemáticas para el nivel de 3º de ESO',
                'nombre_profesor' => 'Juan',
                'apellidos_profesor' => 'Martínez López',
                'activo' => 1
            ],
            [
                'id_curso' => 2,
                'nombre_curso' => 'Biología 4º ESO',
                'descripcion' => 'Curso de biología avanzada para 4º de ESO',
                'nombre_profesor' => 'Ana',
                'apellidos_profesor' => 'García Sánchez',
                'activo' => 1
            ],
            [
                'id_curso' => 3,
                'nombre_curso' => 'Inglés 3º ESO',
                'descripcion' => 'Curso de inglés con contenido multimedia',
                'nombre_profesor' => 'María',
                'apellidos_profesor' => 'Rodríguez Pérez',
                'activo' => 1
            ]
        ];
    }
}
