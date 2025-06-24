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
        require_once APP_PATH . '/utilidades/env.php';
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
        
        // Cargar estadísticas y conteos
        $estadisticas = $this->obtenerEstadisticasAdmin();
        
        // Estado básico del sistema
        $estadoSistema = [
            'base_datos' => ['estado' => 'operativa', 'mensaje' => 'Conexión OK'],
            'almacenamiento' => ['estado' => 'ok', 'uso' => 25, 'libre' => 75],
            'sesiones_activas' => 1,
            'intentos_fallidos' => 0,
            'usuarios_online' => 1,
            'examenes_activos' => 0
        ];
        
        // Definir datos para la vista
        $datos = [
            'titulo' => 'Panel de Administración',
            'usuario' => $usuarioActual,
            'estadisticas' => $estadisticas,
            'actividad_reciente' => $estadisticas['actividad_reciente'],
            'estado_sistema' => $estadoSistema,
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
        
        // Cargar datos reales del profesor
        require_once APP_PATH . '/modelos/curso_modelo.php';
        require_once APP_PATH . '/modelos/examen_modelo.php';
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        
        $cursoModelo = new Curso();
        $examenModelo = new Examen();
        
        $cursos = $cursoModelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
        $examenes = $examenModelo->obtenerPorProfesor($_SESSION['id_usuario']);
        
        // Obtener estadísticas del profesor
        $estadisticas = $this->obtenerEstadisticasProfesor($_SESSION['id_usuario']);
        
        // Definir datos para la vista - Solo información básica, el resto se carga via API
        $datos = [
            'titulo' => 'Panel de Profesor',
            'usuario' => $usuarioActual,
            'carga_via_api' => true, // Indicador para usar API
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
        try {
            // Obtener conteos reales de la base de datos
            $conteo = $this->usuarioModelo->obtenerEstadisticasConteo();
            
            // Obtener actividades recientes reales
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            $registroModelo = new RegistroActividad();
            $actividadesRecientes = $registroModelo->obtenerActividadesRecientes(4);
            
            return [
                'conteo' => $conteo,
                'actividad_reciente' => $actividadesRecientes
            ];
        } catch (Exception $e) {
            error_log('Error al obtener estadísticas del admin: ' . $e->getMessage());
            // Si hay error, devolver datos de respaldo
            return [
                'conteo' => [
                    'administradores' => ['activos' => 0, 'inactivos' => 0, 'total' => 0],
                    'profesores' => ['activos' => 0, 'inactivos' => 0, 'total' => 0],
                    'alumnos' => ['activos' => 0, 'inactivos' => 0, 'total' => 0],
                    'cursos_activos' => ['activos' => 0, 'inactivos' => 0, 'total' => 0]
                ],
                'actividad_reciente' => []
            ];
        }
    }
    
    /**
     * Obtiene el estado actual del sistema
     * 
     * @return array Estado del sistema
     */
    private function obtenerEstadoSistema() {
        $estadoBasico = [
            'base_datos' => ['estado' => 'operativa', 'mensaje' => 'Conexión OK'],
            'smtp' => ['estado' => 'desconocido', 'mensaje' => 'No verificado'],
            'almacenamiento' => ['estado' => 'ok', 'uso' => 25, 'libre' => 75],
            'backup' => ['estado' => 'ok', 'ultimo' => time(), 'dias' => 1, 'mensaje' => 'Ayer'],
            'sesiones_activas' => 0,
            'intentos_fallidos' => 0
        ];
        
        try {
            // Verificar base de datos
            $estadoBasico['base_datos'] = $this->verificarBaseDatos();
            
            // Verificar SMTP de forma segura
            $estadoBasico['smtp'] = $this->verificarSMTP();
            
            // Verificar almacenamiento
            $estadoBasico['almacenamiento'] = $this->verificarAlmacenamiento();
            
            // Verificar backup
            $estadoBasico['backup'] = $this->verificarUltimoBackup();
            
            // Contar sesiones activas
            $estadoBasico['sesiones_activas'] = $this->contarSesionesActivas();
            
            // Contar intentos fallidos
            $estadoBasico['intentos_fallidos'] = $this->contarIntentosFallidos();
            
        } catch (Exception $e) {
            error_log('Error al obtener estado del sistema: ' . $e->getMessage());
        }
        
        return $estadoBasico;
    }
    
    /**
     * Obtiene estado del sistema de forma simple y segura
     */
    private function obtenerEstadoSistemaSimple() {
        $estado = [
            'base_datos' => ['estado' => 'error', 'mensaje' => 'Error'],
            'smtp' => ['estado' => 'no_configurado', 'mensaje' => 'No configurado'],
            'almacenamiento' => ['estado' => 'ok', 'uso' => 0, 'libre' => 100],
            'backup' => ['estado' => 'sin_backups', 'mensaje' => 'No hay backups'],
            'sesiones_activas' => 0,
            'intentos_fallidos' => 0
        ];
        
        // Verificar BD
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $stmt = $conexion->query("SELECT 1");
            $estado['base_datos'] = ['estado' => 'operativa', 'mensaje' => 'Conexión OK'];
        } catch (Exception $e) {
            // Ya está por defecto como error
        }
        
        // Verificar almacenamiento
        try {
            if (defined('STORAGE_PATH') && is_dir(STORAGE_PATH)) {
                $espacioTotal = disk_total_space(STORAGE_PATH);
                $espacioLibre = disk_free_space(STORAGE_PATH);
                if ($espacioTotal && $espacioLibre) {
                    $porcentajeUso = round((($espacioTotal - $espacioLibre) / $espacioTotal) * 100, 1);
                    $estado['almacenamiento'] = [
                        'estado' => $porcentajeUso > 90 ? 'critico' : ($porcentajeUso > 75 ? 'advertencia' : 'ok'),
                        'uso' => $porcentajeUso,
                        'libre' => round(100 - $porcentajeUso, 1)
                    ];
                }
            }
        } catch (Exception $e) {
            // Mantener valores por defecto
        }
        
        // Contar sesiones activas
        try {
            if (file_exists(APP_PATH . '/modelos/sesion_activa_modelo.php')) {
                require_once APP_PATH . '/modelos/sesion_activa_modelo.php';
                $sesionModelo = new SesionActiva();
                $estado['sesiones_activas'] = $sesionModelo->contarSesionesActivas();
            }
        } catch (Exception $e) {
            // Mantener en 0
        }
        
        // Contar intentos fallidos
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $sql = "SELECT COUNT(*) as total FROM registro_actividad 
                    WHERE accion = 'login_fallido' AND fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $conexion->query($sql);
            $resultado = $stmt->fetch();
            $estado['intentos_fallidos'] = $resultado['total'] ?? 0;
        } catch (Exception $e) {
            // Mantener en 0
        }
        
        return $estado;
    }
    
    /**
     * Verifica el estado de la base de datos
     */
    private function verificarBaseDatos() {
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $conexion->query("SELECT 1");
            return ['estado' => 'operativa', 'mensaje' => 'Conexión exitosa'];
        } catch (PDOException $e) {
            return ['estado' => 'error', 'mensaje' => 'Error de conexión'];
        }
    }
    
    /**
     * Verifica el estado del servidor SMTP
     */
    private function verificarSMTP() {
        // Verificar configuración SMTP desde .env
        $smtpHost = Env::obtener('SMTP_HOST');
        $smtpUser = Env::obtener('SMTP_USER');
        
        if (empty($smtpHost) || empty($smtpUser)) {
            return ['estado' => 'no_configurado', 'mensaje' => 'SMTP no configurado'];
        }
        
        // Intentar conexión básica
        try {
            $smtpPort = Env::obtener('SMTP_PORT', 587);
            $conexion = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 5);
            if ($conexion) {
                fclose($conexion);
                return ['estado' => 'operativo', 'mensaje' => 'Servidor accesible'];
            } else {
                return ['estado' => 'error', 'mensaje' => 'No se puede conectar'];
            }
        } catch (Exception $e) {
            return ['estado' => 'error', 'mensaje' => 'Error de conexión'];
        }
    }
    
    /**
     * Verifica el estado del almacenamiento
     */
    private function verificarAlmacenamiento() {
        try {
            $dirAlmacenamiento = STORAGE_PATH;
            if (!is_dir($dirAlmacenamiento)) {
                return ['estado' => 'error', 'uso' => 0, 'mensaje' => 'Directorio no existe'];
            }
            
            $espacioTotal = disk_total_space($dirAlmacenamiento);
            $espacioLibre = disk_free_space($dirAlmacenamiento);
            
            if ($espacioTotal === false || $espacioLibre === false) {
                return ['estado' => 'error', 'uso' => 0, 'mensaje' => 'No se puede verificar espacio'];
            }
            
            $porcentajeUso = round((($espacioTotal - $espacioLibre) / $espacioTotal) * 100, 1);
            
            $estado = 'ok';
            if ($porcentajeUso > 90) {
                $estado = 'critico';
            } elseif ($porcentajeUso > 75) {
                $estado = 'advertencia';
            }
            
            return [
                'estado' => $estado,
                'uso' => $porcentajeUso,
                'libre' => round(100 - $porcentajeUso, 1),
                'espacio_total' => $this->formatearBytes($espacioTotal),
                'espacio_libre' => $this->formatearBytes($espacioLibre)
            ];
        } catch (Exception $e) {
            return ['estado' => 'error', 'uso' => 0, 'mensaje' => 'Error al verificar'];
        }
    }
    
    /**
     * Verifica el último backup
     */
    private function verificarUltimoBackup() {
        try {
            $dirBackups = STORAGE_PATH . '/copias/db';
            if (!is_dir($dirBackups)) {
                return ['estado' => 'sin_backups', 'ultimo' => null, 'mensaje' => 'No hay backups'];
            }
            
            $archivos = glob($dirBackups . '/*.sql');
            if (empty($archivos)) {
                return ['estado' => 'sin_backups', 'ultimo' => null, 'mensaje' => 'No hay backups'];
            }
            
            $ultimoArchivo = max(array_map('filemtime', $archivos));
            $diasDesdeBackup = floor((time() - $ultimoArchivo) / (24 * 3600));
            
            $estado = 'ok';
            if ($diasDesdeBackup > 7) {
                $estado = 'advertencia';
            } elseif ($diasDesdeBackup > 14) {
                $estado = 'critico';
            }
            
            return [
                'estado' => $estado,
                'ultimo' => $ultimoArchivo,
                'dias' => $diasDesdeBackup,
                'mensaje' => $diasDesdeBackup === 0 ? 'Hoy' : "Hace {$diasDesdeBackup} días"
            ];
        } catch (Exception $e) {
            return ['estado' => 'error', 'ultimo' => null, 'mensaje' => 'Error al verificar'];
        }
    }
    
    /**
     * Cuenta sesiones activas
     */
    private function contarSesionesActivas() {
        try {
            require_once APP_PATH . '/modelos/sesion_activa_modelo.php';
            $sesionModelo = new SesionActiva();
            return $sesionModelo->contarSesionesActivas();
        } catch (Exception $e) {
            error_log('Error al contar sesiones activas: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta intentos de login fallidos recientes
     */
    private function contarIntentosFallidos() {
        try {
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            $registroModelo = new RegistroActividad();
            
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $sql = "SELECT COUNT(*) as total 
                    FROM registro_actividad 
                    WHERE accion = 'login_fallido' 
                    AND fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            
            $stmt = $conexion->query($sql);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error al contar intentos fallidos: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Formatea bytes a unidades legibles
     */
    private function formatearBytes($bytes) {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($unidades) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $unidades[$pow];
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
    
    /**
     * Cuenta sesiones activas reales
     */
    private function contarSesionesReales() {
        try {
            if (file_exists(APP_PATH . '/modelos/sesion_activa_modelo.php')) {
                require_once APP_PATH . '/modelos/sesion_activa_modelo.php';
                $sesionModelo = new SesionActiva();
                return $sesionModelo->contarSesionesActivas();
            }
            return 1; // Al menos la sesión actual
        } catch (Exception $e) {
            return 1;
        }
    }
    
    /**
     * Cuenta intentos fallidos reales
     */
    private function contarIntentosFallidosReales() {
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $sql = "SELECT COUNT(*) as total FROM registro_actividad 
                    WHERE accion = 'login_fallido' AND fecha >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $conexion->query($sql);
            $resultado = $stmt->fetch();
            return $resultado['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Cuenta usuarios online (activos en los últimos 15 minutos)
     */
    private function contarUsuariosOnline() {
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $sql = "SELECT COUNT(DISTINCT id_usuario) as total FROM registro_actividad 
                    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
            $stmt = $conexion->query($sql);
            $resultado = $stmt->fetch();
            return $resultado['total'] ?? 1;
        } catch (Exception $e) {
            return 1;
        }
    }
    
    /**
     * Cuenta exámenes activos
     */
    private function contarExamenesActivos() {
        try {
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            // Verificar si existe la tabla examenes
            $sql = "SHOW TABLES LIKE 'examenes'";
            $stmt = $conexion->query($sql);
            if ($stmt->rowCount() > 0) {
                $sql = "SELECT COUNT(*) as total FROM examenes WHERE activo = 1";
                $stmt = $conexion->query($sql);
                $resultado = $stmt->fetch();
                return $resultado['total'] ?? 0;
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene estadísticas para el dashboard del profesor
     * 
     * @param int $idProfesor ID del profesor
     * @return array Estadísticas del profesor
     */
    private function obtenerEstadisticasProfesor($idProfesor) {
        try {
            require_once APP_PATH . '/modelos/curso_modelo.php';
            require_once APP_PATH . '/modelos/examen_modelo.php';
            require_once APP_PATH . '/modelos/usuario_modelo.php';
            
            $cursoModelo = new Curso();
            $examenModelo = new Examen();
            $usuarioModelo = new Usuario();
            
            // Obtener cursos del profesor
            $cursos = $cursoModelo->obtenerCursosPorProfesor($idProfesor);
            $totalCursos = count($cursos);
            
            // Obtener exámenes del profesor
            $examenes = $examenModelo->obtenerPorProfesor($idProfesor);
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
                $alumnosCurso = $usuarioModelo->contarAlumnosPorCurso($curso['id_curso']);
                $totalAlumnos += $alumnosCurso;
            }
            
            // Calcular promedio de notas (se puede implementar más adelante)
            $promedioNotas = 0.0;
            
            return [
                'total_cursos' => $totalCursos,
                'total_examenes' => $totalExamenes,
                'examenes_activos' => $examenesActivos,
                'examenes_pendientes' => $examenesPendientes,
                'examenes_completados' => $examenesCompletados,
                'total_alumnos' => $totalAlumnos,
                'promedio_notas' => $promedioNotas
            ];
            
        } catch (Exception $e) {
            error_log('Error al obtener estadísticas del profesor: ' . $e->getMessage());
            return [
                'total_cursos' => 0,
                'total_examenes' => 0,
                'examenes_activos' => 0,
                'examenes_pendientes' => 0,
                'examenes_completados' => 0,
                'total_alumnos' => 0,
                'promedio_notas' => 0.0
            ];
        }
    }
}
