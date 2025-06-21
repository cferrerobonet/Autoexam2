<?php
/**
 * Controlador de Configuración - AUTOEXAM2 - SIMPLIFICADO
 * 
 * Gestiona la configuración del sistema para administradores
 * 
 * @author GitHub Copilot
 * @version 1.1
 */

require_once APP_PATH . '/utilidades/sesion.php';
require_once APP_PATH . '/modelos/configuracion_modelo.php';

class ConfiguracionControlador {
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
     * Mostrar panel principal de configuración
     */
    public function index() {
        // Obtener configuración actual
        $configuracion = $this->obtenerConfiguracionActual();
        $infoSistema = $this->modelo->obtenerInfoSistema();
        
        // Datos para la vista
        $datos = [
            'titulo' => 'Configuración del Sistema',
            'configuracion' => $configuracion,
            'info_sistema' => $infoSistema,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar vista
        require_once APP_PATH . '/vistas/parciales/head_admin.php';
        echo '<body class="bg-light">';
        require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
        echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
        require_once APP_PATH . '/vistas/admin/configuracion/index.php';
        echo '</div></div></div>';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
        echo '</body></html>';
    }
    
    /**
     * Actualizar configuración del sistema - CON PATRÓN CORRECTO
     */
    public function actualizar() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'configuracion');
            
            // Obtener sección a actualizar
            $seccion = $_POST['seccion'] ?? '';
            
            switch ($seccion) {
                case 'sistema':
                    $this->actualizarConfiguracionSistema();
                    break;
                case 'correo':
                    $this->actualizarConfiguracionCorreo();
                    break;
                case 'base_datos':
                    $this->actualizarConfiguracionBaseDatos();
                    break;
                case 'archivos':
                    $this->actualizarConfiguracionArchivos();
                    break;
                case 'sftp':
                    $this->actualizarConfiguracionSFTP();
                    break;
                default:
                    throw new Exception('Sección de configuración no válida');
            }
            
            $_SESSION['exito'] = 'Configuración actualizada exitosamente';
            header('Location: ' . BASE_URL . '/configuracion');
            
        } catch (Exception $e) {
            error_log("Error al actualizar configuración: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar la configuración: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/configuracion');
        }
        
        exit;
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
    private function verificarTokenCSRF($token, $rutaError = 'configuracion') {
        if (empty($token) || !$this->sesion->validarTokenCSRF($token)) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/' . $rutaError);
            exit;
        }
    }
    
    /**
     * Actualizar configuración del sistema
     */
    private function actualizarConfiguracionSistema() {
        $nombre_app = trim($_POST['nombre_app'] ?? '');
        $modo_debug = isset($_POST['modo_debug']) ? 'true' : 'false';
        $modo_mantenimiento = isset($_POST['modo_mantenimiento']) ? 'true' : 'false';
        
        if (empty($nombre_app)) {
            throw new Exception('El nombre de la aplicación es obligatorio');
        }
        
        // Actualizar archivo .env
        $datos = [
            'APP_NAME' => $nombre_app,
            'DEBUG' => $modo_debug,
            'MODO_MANTENIMIENTO' => $modo_mantenimiento
        ];
        
        if (!$this->modelo->actualizarConfiguracionEnv($datos)) {
            throw new Exception('Error al actualizar la configuración del sistema');
        }
        
        // Registrar actividad
        $this->registrarActividad('configuracion_sistema_actualizada', [
            'nombre_app' => $nombre_app,
            'modo_debug' => $modo_debug,
            'modo_mantenimiento' => $modo_mantenimiento
        ]);
    }
    
    /**
     * Actualizar configuración de correo
     */
    private function actualizarConfiguracionCorreo() {
        $smtp_host = trim($_POST['smtp_host'] ?? '');
        $smtp_puerto = trim($_POST['smtp_puerto'] ?? '');
        $smtp_usuario = trim($_POST['smtp_usuario'] ?? '');
        $smtp_contrasena = trim($_POST['smtp_contrasena'] ?? '');
        $smtp_seguridad = trim($_POST['smtp_seguridad'] ?? 'tls');
        $smtp_from_email = trim($_POST['smtp_from_email'] ?? '');
        $smtp_from_name = trim($_POST['smtp_from_name'] ?? '');
        
        if (empty($smtp_host) || empty($smtp_puerto) || empty($smtp_usuario)) {
            throw new Exception('Los campos Host, Puerto y Usuario SMTP son obligatorios');
        }
        
        // Validar puerto
        if (!is_numeric($smtp_puerto) || $smtp_puerto < 1 || $smtp_puerto > 65535) {
            throw new Exception('El puerto SMTP debe ser un número válido');
        }
        
        // Validar email
        if (!empty($smtp_from_email) && !filter_var($smtp_from_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email FROM no es válido');
        }
        
        // Actualizar archivo .env
        $datos = [
            'SMTP_HOST' => $smtp_host,
            'SMTP_PORT' => $smtp_puerto,
            'SMTP_USER' => $smtp_usuario,
            'SMTP_SECURE' => $smtp_seguridad,
            'SMTP_FROM_EMAIL' => $smtp_from_email,
            'SMTP_FROM_NAME' => $smtp_from_name
        ];
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($smtp_contrasena)) {
            $datos['SMTP_PASS'] = $smtp_contrasena;
        }
        
        if (!$this->modelo->actualizarConfiguracionEnv($datos)) {
            throw new Exception('Error al actualizar la configuración de correo');
        }
        
        $this->registrarActividad('configuracion_smtp_actualizada', [
            'host' => $smtp_host,
            'puerto' => $smtp_puerto,
            'usuario' => $smtp_usuario,
            'seguridad' => $smtp_seguridad
        ]);
    }
    
    /**
     * Métodos placeholder para otras configuraciones
     */
    private function actualizarConfiguracionBaseDatos() {
        throw new Exception('Actualización de base de datos en desarrollo');
    }
    
    private function actualizarConfiguracionArchivos() {
        throw new Exception('Actualización de archivos en desarrollo');
    }
    
    private function actualizarConfiguracionSFTP() {
        throw new Exception('Actualización de SFTP en desarrollo');
    }
    
    // Aquí copio los métodos que no tienen problemas del archivo original...
    private function obtenerConfiguracionActual() {
        $configEnv = $this->modelo->leerConfiguracionEnv();
        
        return [
            'sistema' => [
                'nombre_app' => $configEnv['APP_NAME'] ?? 'AUTOEXAM2',
                'base_url' => $configEnv['BASE_URL'] ?? BASE_URL,
                'modo_debug' => ($configEnv['DEBUG'] ?? 'false') === 'true',
                'modo_mantenimiento' => ($configEnv['MODO_MANTENIMIENTO'] ?? 'false') === 'true'
            ],
            'base_datos' => [
                'host' => $configEnv['DB_HOST'] ?? 'localhost',
                'nombre' => $configEnv['DB_NAME'] ?? '',
                'usuario' => $configEnv['DB_USER'] ?? '',
                'puerto' => $configEnv['DB_PORT'] ?? '3306',
                'estado_conexion' => 'no_verificado'
            ],
            'correo' => [
                'host' => $configEnv['SMTP_HOST'] ?? '',
                'puerto' => $configEnv['SMTP_PORT'] ?? '587',
                'usuario' => $configEnv['SMTP_USER'] ?? '',
                'seguridad' => $configEnv['SMTP_SECURE'] ?? 'tls',
                'from_email' => $configEnv['SMTP_FROM_EMAIL'] ?? '',
                'from_name' => $configEnv['SMTP_FROM_NAME'] ?? '',
                'estado_conexion' => 'no_verificado'
            ],
            'archivos' => [
                'tamaño_maximo' => $configEnv['UPLOAD_MAX_SIZE'] ?? ini_get('upload_max_filesize'),
                'tipos_permitidos' => $configEnv['ALLOWED_EXTENSIONS'] ?? 'jpg,png,gif,pdf',
                'directorio_subidas' => $configEnv['STORAGE_PATH'] ?? 'almacenamiento/subidas'
            ],
            'sftp' => [
                'host' => $configEnv['FTP_HOST'] ?? '',
                'puerto' => $configEnv['FTP_PORT'] ?? '21',
                'usuario' => $configEnv['FTP_USER'] ?? '',
                'seguro' => ($configEnv['FTP_SECURE'] ?? 'false') === 'true',
                'estado_conexion' => 'no_verificado'
            ],
            'backups' => []
        ];
    }
    
    /**
     * Registrar actividad de usuario
     */
    private function registrarActividad($accion, $detalles = []) {
        try {
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            $registroActividad = new RegistroActividad();
            
            $registroActividad->registrar([
                'usuario_id' => $_SESSION['usuario_id'],
                'accion' => $accion,
                'detalles' => json_encode($detalles),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido'
            ]);
        } catch (Exception $e) {
            error_log("Error registrando actividad: " . $e->getMessage());
        }
    }
}
