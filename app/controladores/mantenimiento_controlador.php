<?php
/**
 * Controlador de Mantenimiento - AUTOEXAM2
 * 
 * Gestiona herramientas de mantenimiento y limpieza del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

require_once APP_PATH . '/utilidades/sesion.php';
require_once APP_PATH . '/modelos/configuracion_modelo.php';

class MantenimientoControlador {
    private $sesion;
    private $modelo;
    
    public function __construct() {
        $this->sesion = new Sesion();
        $this->modelo = new ConfiguracionModelo();
        
        // Verificar que solo administradores accedan
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
    }
    
    /**
     * Mostrar panel de mantenimiento
     */
    public function index() {
        // Datos para la vista
        $datos = [
            'titulo' => 'Herramientas de Mantenimiento',
            'csrf_token' => $this->sesion->generarTokenCSRF(),
            'estadisticas' => $this->obtenerEstadisticas()
        ];
        
        // Cargar vista
        require_once APP_PATH . '/vistas/parciales/head_admin.php';
        echo '<body class="bg-light">';
        require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
        echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
        require_once APP_PATH . '/vistas/admin/mantenimiento/index.php';
        echo '</div></div></div>';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
        echo '</body></html>';
    }
    
    /**
     * Limpiar caché del sistema
     */
    public function limpiarCache() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'mantenimiento');
            
            $archivosEliminados = $this->limpiarCacheArchivos();
            
            $this->registrarActividad('cache_limpiado', [
                'archivos_eliminados' => $archivosEliminados
            ]);
            
            $_SESSION['exito'] = "Caché limpiado correctamente. Se eliminaron $archivosEliminados archivos.";
            
        } catch (Exception $e) {
            error_log("Error al limpiar caché: " . $e->getMessage());
            $_SESSION['error'] = 'Error al limpiar caché: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/mantenimiento');
        exit;
    }
    
    /**
     * Verificar integridad del sistema
     */
    public function verificarSistema() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token de seguridad inválido']);
            exit;
        }
        
        try {
            $resultados = $this->ejecutarVerificacionSistema();
            
            $this->registrarActividad('sistema_verificado', [
                'resultados' => $resultados
            ]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'exito' => true,
                'resultados' => $resultados
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Regenerar archivos de configuración
     */
    public function regenerarConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/mantenimiento');
            exit;
        }
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/mantenimiento');
            exit;
        }
        
        try {
            // Crear backup antes de regenerar
            $backup = $this->modelo->crearBackupConfiguracion();
            
            if (!$backup['exito']) {
                throw new Exception('No se pudo crear backup de seguridad');
            }
            
            $this->regenerarArchivosConfiguracion();
            
            $this->registrarActividad('configuracion_regenerada', [
                'backup_seguridad' => $backup['archivo']
            ]);
            
            $_SESSION['exito'] = 'Configuración regenerada correctamente. Backup de seguridad: ' . $backup['archivo'];
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al regenerar configuración: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/mantenimiento');
        exit;
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    private function obtenerEstadisticas() {
        try {
            global $db;
            
            $estadisticas = [];
            
            // Estadísticas de usuarios
            $stmt = $db->prepare("SELECT COUNT(*) as total, rol FROM usuarios GROUP BY rol");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estadisticas['usuarios'] = [];
            foreach ($usuarios as $usuario) {
                $estadisticas['usuarios'][$usuario['rol']] = $usuario['total'];
            }
            
            // Estadísticas de archivos
            $estadisticas['archivos'] = [
                'cache' => $this->contarArchivos(CACHE_PATH),
                'logs' => $this->contarArchivos(LOGS_PATH),
                'backups' => $this->contarArchivos(BACKUP_PATH),
                'uploads' => $this->contarArchivos(UPLOADS_PATH)
            ];
            
            // Estadísticas de espacio
            $estadisticas['espacio'] = [
                'cache_size' => $this->calcularTamanoDirectorio(CACHE_PATH),
                'logs_size' => $this->calcularTamanoDirectorio(LOGS_PATH),
                'uploads_size' => $this->calcularTamanoDirectorio(UPLOADS_PATH)
            ];
            
            return $estadisticas;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Limpiar archivos de caché
     */
    private function limpiarCacheArchivos() {
        $directorios = [
            APP_CACHE_PATH,
            VIEW_CACHE_PATH,
            DATA_CACHE_PATH,
            TMP_PATH . '/uploads'
        ];
        
        $archivosEliminados = 0;
        
        foreach ($directorios as $directorio) {
            if (!is_dir($directorio)) continue;
            
            $archivos = glob($directorio . '/*');
            
            foreach ($archivos as $archivo) {
                if (is_file($archivo) && basename($archivo) !== '.gitkeep') {
                    if (unlink($archivo)) {
                        $archivosEliminados++;
                    }
                }
            }
        }
        
        return $archivosEliminados;
    }
    
    /**
     * Ejecutar verificación completa del sistema
     */
    private function ejecutarVerificacionSistema() {
        $resultados = [
            'archivos_configuracion' => [],
            'permisos_directorios' => [],
            'extensiones_php' => [],
            'base_datos' => [],
            'espacio_disco' => []
        ];
        
        // Verificar archivos de configuración
        $archivosConfig = [
            ROOT_PATH . '/.env',
            ROOT_PATH . '/config/config.php',
            ROOT_PATH . '/config/database.php',
            ROOT_PATH . '/config/storage.php'
        ];
        
        foreach ($archivosConfig as $archivo) {
            $resultados['archivos_configuracion'][basename($archivo)] = [
                'existe' => file_exists($archivo),
                'legible' => file_exists($archivo) && is_readable($archivo),
                'escribible' => file_exists($archivo) && is_writable($archivo)
            ];
        }
        
        // Verificar permisos de directorios
        $directorios = [
            STORAGE_PATH,
            LOGS_PATH,
            CACHE_PATH,
            UPLOADS_PATH,
            TMP_PATH
        ];
        
        foreach ($directorios as $directorio) {
            $resultados['permisos_directorios'][basename($directorio)] = [
                'existe' => is_dir($directorio),
                'escribible' => is_dir($directorio) && is_writable($directorio),
                'permisos' => is_dir($directorio) ? substr(sprintf('%o', fileperms($directorio)), -4) : 'N/A'
            ];
        }
        
        // Verificar extensiones PHP
        $extensionesRequeridas = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'openssl', 'zip'];
        
        foreach ($extensionesRequeridas as $extension) {
            $resultados['extensiones_php'][$extension] = extension_loaded($extension);
        }
        
        // Verificar conexión a base de datos
        try {
            global $db;
            if ($db && $db->ping()) {
                $resultados['base_datos']['conexion'] = true;
                
                // Verificar tablas principales
                $stmt = $db->query("SHOW TABLES");
                $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $resultados['base_datos']['tablas_count'] = count($tablas);
            } else {
                $resultados['base_datos']['conexion'] = false;
            }
        } catch (Exception $e) {
            $resultados['base_datos']['conexion'] = false;
            $resultados['base_datos']['error'] = $e->getMessage();
        }
        
        // Verificar espacio en disco
        $espacioTotal = disk_total_space(ROOT_PATH);
        $espacioLibre = disk_free_space(ROOT_PATH);
        
        $resultados['espacio_disco'] = [
            'total' => $espacioTotal,
            'libre' => $espacioLibre,
            'usado' => $espacioTotal - $espacioLibre,
            'porcentaje_usado' => round((($espacioTotal - $espacioLibre) / $espacioTotal) * 100, 2)
        ];
        
        return $resultados;
    }
    
    /**
     * Regenerar archivos de configuración básicos
     */
    private function regenerarArchivosConfiguracion() {
        // Regenerar archivos que no contengan datos sensibles
        $infoSistema = $this->modelo->obtenerInfoSistema();
        
        // Crear archivo de información del sistema
        $contenidoInfo = "<?php\n";
        $contenidoInfo .= "// Información del sistema - Generado automáticamente\n";
        $contenidoInfo .= "// Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        $contenidoInfo .= "define('SISTEMA_INFO_PHP', '" . PHP_VERSION . "');\n";
        $contenidoInfo .= "define('SISTEMA_INFO_SERVIDOR', '" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "');\n";
        $contenidoInfo .= "define('SISTEMA_INFO_GENERADO', '" . date('Y-m-d H:i:s') . "');\n";
        
        file_put_contents(ROOT_PATH . '/config/sistema_info.php', $contenidoInfo);
    }
    
    /**
     * Contar archivos en un directorio
     */
    private function contarArchivos($directorio) {
        if (!is_dir($directorio)) return 0;
        
        $archivos = glob($directorio . '/*');
        return count($archivos);
    }
    
    /**
     * Calcular tamaño de directorio en bytes
     */
    private function calcularTamanoDirectorio($directorio) {
        if (!is_dir($directorio)) return 0;
        
        $tamano = 0;
        $archivos = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorio),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($archivos as $archivo) {
            if ($archivo->isFile()) {
                $tamano += $archivo->getSize();
            }
        }
        
        return $tamano;
    }
    
    /**
     * Registrar actividad del administrador
     */
    private function registrarActividad($accion, $detalles = []) {
        try {
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            
            $registroModelo = new RegistroActividadModelo();
            $registroModelo->registrar([
                'usuario_id' => $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'],
                'accion' => $accion,
                'detalles' => json_encode($detalles),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'
            ]);
        } catch (Exception $e) {
            error_log("Error registrando actividad: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar que la petición usa método POST
     */
    private function verificarMetodoPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Método no permitido");
        }
    }
    
    /**
     * Verificar token CSRF
     */
    private function verificarTokenCSRF($token, $rutaError = 'mantenimiento') {
        if (empty($token) || !$this->sesion->validarTokenCSRF($token)) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/' . $rutaError);
            exit;
        }
    }
}
