<?php
/**
 * Controlador de Configuración - AUTOEXAM2
 * 
 * Gestiona la configuración del sistema para administradores
 * 
 * @author GitHub Copilot
 * @version 1.0
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
     * Obtener configuración actual del sistema
     */
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
                'estado_conexion' => $this->verificarConexionBD()
            ],
            'correo' => [
                'host' => $configEnv['SMTP_HOST'] ?? '',
                'puerto' => $configEnv['SMTP_PORT'] ?? '587',
                'usuario' => $configEnv['SMTP_USER'] ?? '',
                'seguridad' => $configEnv['SMTP_SECURE'] ?? 'tls',
                'from_email' => $configEnv['SMTP_FROM_EMAIL'] ?? '',
                'from_name' => $configEnv['SMTP_FROM_NAME'] ?? '',
                'estado_conexion' => $this->verificarConexionSMTP()
            ],
            'archivos' => [
                'tamaño_maximo' => $configEnv['UPLOAD_MAX_SIZE'] ?? ini_get('upload_max_filesize'),
                'tipos_permitidos' => $configEnv['ALLOWED_EXTENSIONS'] ?? 'jpg,png,gif,pdf',
                'directorio_subidas' => $configEnv['STORAGE_PATH'] ?? (UPLOADS_PATH ?? 'almacenamiento/subidas')
            ],
            'sftp' => [
                'host' => $configEnv['FTP_HOST'] ?? '',
                'puerto' => $configEnv['FTP_PORT'] ?? '21',
                'usuario' => $configEnv['FTP_USER'] ?? '',
                'seguro' => ($configEnv['FTP_SECURE'] ?? 'false') === 'true',
                'estado_conexion' => $this->verificarConexionFTP()
            ],
            'backups' => $this->modelo->listarBackups()
        ];
    }
    
    /**
     * Verificar conexión a base de datos
     */
    private function verificarConexionBD() {
        try {
            if (isset($GLOBALS['db']) && $GLOBALS['db']->ping()) {
                return 'conectado';
            }
            return 'error';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    /**
     * Verificar configuración SMTP
     */
    private function verificarConexionSMTP() {
        $host = $_ENV['SMTP_HOST'] ?? '';
        $usuario = $_ENV['SMTP_USER'] ?? '';
        
        if (empty($host) || empty($usuario)) {
            return 'no_configurado';
        }
        
        return 'configurado';
    }
    
    /**
     * Verificar configuración FTP
     */
    private function verificarConexionFTP() {
        $configEnv = $this->modelo->leerConfiguracionEnv();
        $host = $configEnv['FTP_HOST'] ?? '';
        $usuario = $configEnv['FTP_USER'] ?? '';
        
        if (empty($host) || empty($usuario)) {
            return 'no_configurado';
        }
        
        return 'configurado';
    }
    
    /**
     * Actualizar configuración del sistema
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
            case 'basedatos':
                $this->actualizarConfiguracionBaseDatos();
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
                $_SESSION['error'] = 'Sección de configuración no válida';
                header('Location: ' . BASE_URL . '/configuracion');
                exit;
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
     * Actualizar configuración de base de datos
     */
    private function actualizarConfiguracionBaseDatos() {
        $host = trim($_POST['db_host'] ?? '');
        $nombre = trim($_POST['db_nombre'] ?? '');
        $usuario = trim($_POST['db_usuario'] ?? '');
        $contrasena = trim($_POST['db_contrasena'] ?? '');
        $puerto = trim($_POST['db_puerto'] ?? '3306');
        
        if (empty($host) || empty($nombre) || empty($usuario)) {
            throw new Exception('Todos los campos de base de datos son obligatorios excepto la contraseña');
        }
        
        // Validar puerto
        if (!is_numeric($puerto) || $puerto < 1 || $puerto > 65535) {
            throw new Exception('El puerto debe ser un número válido');
        }
        
        // Probar conexión antes de guardar
        $validacion = $this->modelo->validarConfiguracionBD($host, $nombre, $usuario, $contrasena, $puerto);
        
        if (!$validacion['valido']) {
            throw new Exception('Error de conexión: ' . $validacion['mensaje']);
        }
        
        // Actualizar archivo .env
        $datos = [
            'DB_HOST' => $host,
            'DB_NAME' => $nombre,
            'DB_USER' => $usuario,
            'DB_PORT' => $puerto
        ];
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($contrasena)) {
            $datos['DB_PASS'] = $contrasena;
        }
        
        if (!$this->modelo->actualizarConfiguracionEnv($datos)) {
            throw new Exception('Error al actualizar la configuración de base de datos');
        }
        
        $this->registrarActividad('configuracion_bd_actualizada', [
            'host' => $host,
            'nombre' => $nombre,
            'usuario' => $usuario,
            'puerto' => $puerto
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
        
        // Probar conexión SMTP si se proporciona contraseña
        if (!empty($smtp_contrasena)) {
            $validacion = $this->modelo->probarConfiguracionSMTP($smtp_host, $smtp_puerto, $smtp_usuario, $smtp_contrasena, $smtp_seguridad);
            
            if (!$validacion['valido']) {
                throw new Exception('Error de conexión SMTP: ' . $validacion['mensaje']);
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
     * Actualizar configuración de archivos
     */
    private function actualizarConfiguracionArchivos() {
        $upload_max_size = trim($_POST['upload_max_size'] ?? '');
        $allowed_extensions = trim($_POST['allowed_extensions'] ?? '');
        
        if (empty($upload_max_size) || empty($allowed_extensions)) {
            throw new Exception('Todos los campos son obligatorios');
        }
        
        // Validar tamaño máximo
        if (!preg_match('/^\d+[KMGT]?B?$/i', $upload_max_size)) {
            throw new Exception('El tamaño máximo debe tener formato válido (ej: 2MB, 10MB)');
        }
        $extensiones = array_map('trim', explode(',', $allowed_extensions));
        foreach ($extensiones as $ext) {
            if (!preg_match('/^[a-z0-9]+$/i', $ext)) {
                $_SESSION['error'] = 'Las extensiones solo pueden contener letras y números';
                return;
            }
        }
        
        // Actualizar archivo .env
        $datos = [
            'UPLOAD_MAX_SIZE' => $upload_max_size,
            'ALLOWED_EXTENSIONS' => $allowed_extensions
        ];
        
        if ($this->modelo->actualizarConfiguracionEnv($datos)) {
            $this->registrarActividad('configuracion_archivos_actualizada', [
                'upload_max_size' => $upload_max_size,
                'allowed_extensions' => $allowed_extensions
            ]);
            
            $_SESSION['exito'] = 'Configuración de archivos actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la configuración de archivos';
        }
    }
    
    /**
     * Actualizar configuración SFTP/FTP
     */
    private function actualizarConfiguracionSFTP() {
        $ftp_host = trim($_POST['ftp_host'] ?? '');
        $ftp_puerto = trim($_POST['ftp_puerto'] ?? '21');
        $ftp_usuario = trim($_POST['ftp_usuario'] ?? '');
        $ftp_contrasena = trim($_POST['ftp_contrasena'] ?? '');
        $ftp_seguro = isset($_POST['ftp_seguro']) ? 'true' : 'false';
        
        if (empty($ftp_host) || empty($ftp_usuario)) {
            $_SESSION['error'] = 'Los campos Host y Usuario FTP son obligatorios';
            return;
        }
        
        // Validar puerto
        if (!is_numeric($ftp_puerto) || $ftp_puerto < 1 || $ftp_puerto > 65535) {
            $_SESSION['error'] = 'El puerto FTP debe ser un número válido';
            return;
        }
        
        // Probar conexión FTP si se proporciona contraseña
        if (!empty($ftp_contrasena)) {
            $validacion = $this->modelo->probarConfiguracionFTP($ftp_host, $ftp_puerto, $ftp_usuario, $ftp_contrasena, $ftp_seguro === 'true');
            
            if (!$validacion['valido']) {
                $_SESSION['error'] = 'Error de conexión FTP: ' . $validacion['mensaje'];
                return;
            }
        }
        
        // Actualizar archivo .env
        $datos = [
            'FTP_HOST' => $ftp_host,
            'FTP_PORT' => $ftp_puerto,
            'FTP_USER' => $ftp_usuario,
            'FTP_SECURE' => $ftp_seguro
        ];
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($ftp_contrasena)) {
            $datos['FTP_PASS'] = $ftp_contrasena;
        }
        
        if ($this->modelo->actualizarConfiguracionEnv($datos)) {
            $this->registrarActividad('configuracion_ftp_actualizada', [
                'host' => $ftp_host,
                'puerto' => $ftp_puerto,
                'usuario' => $ftp_usuario,
                'seguro' => $ftp_seguro
            ]);
            
            $_SESSION['exito'] = 'Configuración FTP actualizada correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar la configuración FTP';
        }
    }
    
    /**
     * Crear backup de la configuración
     */
    public function crearBackup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        try {
            $resultado = $this->modelo->crearBackupConfiguracion();
            
            if ($resultado['exito']) {
                $this->registrarActividad('backup_configuracion_creado', [
                    'archivo' => $resultado['archivo'],
                    'tamaño' => $resultado['tamaño']
                ]);
                
                $_SESSION['exito'] = 'Backup creado correctamente: ' . $resultado['archivo'];
            } else {
                $_SESSION['error'] = 'Error al crear backup: ' . $resultado['error'];
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al crear backup: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/configuracion');
        exit;
    }
    
    /**
     * Restaurar backup de configuración
     */
    public function restaurarBackup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        $archivo_backup = trim($_POST['archivo_backup'] ?? '');
        
        if (empty($archivo_backup)) {
            $_SESSION['error'] = 'Debe seleccionar un archivo de backup';
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        try {
            $resultado = $this->modelo->restaurarBackupConfiguracion($archivo_backup);
            
            if ($resultado['exito']) {
                $this->registrarActividad('backup_configuracion_restaurado', [
                    'archivo' => $archivo_backup
                ]);
                
                $_SESSION['exito'] = 'Configuración restaurada correctamente desde: ' . $archivo_backup;
            } else {
                $_SESSION['error'] = 'Error al restaurar backup: ' . $resultado['error'];
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error al restaurar backup: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/configuracion');
        exit;
    }
    
    /**
     * Probar conexión SMTP
     */
    public function probarSMTP() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }
        
        // Verificar token CSRF
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token de seguridad inválido']);
            exit;
        }
        
        $email_prueba = trim($_POST['email_prueba'] ?? '');
        
        if (empty($email_prueba) || !filter_var($email_prueba, FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Email de prueba no válido']);
            exit;
        }
        
        // Intentar enviar email de prueba
        try {
            require_once APP_PATH . '/utilidades/correo.php';
            
            $correo = new Correo();
            $resultado = $correo->enviar(
                $email_prueba,
                'Prueba de configuración SMTP - AUTOEXAM2',
                'Este es un email de prueba para verificar la configuración SMTP.'
            );
            
            if ($resultado) {
                header('Content-Type: application/json');
                echo json_encode(['exito' => 'Email de prueba enviado correctamente']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Error al enviar email de prueba']);
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error de conexión SMTP: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Mostrar logs del sistema
     */
    public function logs() {
        $tipo = $_GET['tipo'] ?? 'app';
        $limite = (int) ($_GET['limite'] ?? 100);
        
        $logs = $this->modelo->obtenerLogs($tipo, $limite);
        
        header('Content-Type: application/json');
        echo json_encode([
            'logs' => $logs,
            'tipo' => $tipo,
            'total' => count($logs)
        ]);
        exit;
    }
    
    /**
     * Limpiar logs antiguos
     */
    public function limpiarLogs() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        $dias = (int) ($_POST['dias'] ?? 30);
        
        if ($dias < 1 || $dias > 365) {
            $_SESSION['error'] = 'El número de días debe estar entre 1 y 365';
            header('Location: ' . BASE_URL . '/configuracion');
            exit;
        }
        
        $archivosEliminados = $this->modelo->limpiarLogs($dias);
        
        $this->registrarActividad('logs_limpiados', [
            'dias' => $dias,
            'archivos_eliminados' => $archivosEliminados
        ]);
        
        $_SESSION['exito'] = "Se eliminaron $archivosEliminados archivos de log";
        header('Location: ' . BASE_URL . '/configuracion');
        exit;
    }
    
    /**
     * Probar conexión de base de datos
     */
    public function probarBaseDatos() {
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
        
        $host = trim($_POST['host'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        $puerto = trim($_POST['puerto'] ?? '3306');
        
        if (empty($host) || empty($nombre) || empty($usuario)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Faltan datos requeridos']);
            exit;
        }
        
        $resultado = $this->modelo->validarConfiguracionBD($host, $nombre, $usuario, $contrasena, $puerto);
        
        header('Content-Type: application/json');
        if ($resultado['valido']) {
            echo json_encode(['exito' => $resultado['mensaje']]);
        } else {
            echo json_encode(['error' => $resultado['mensaje']]);
        }
        exit;
    }
    
    /**
     * Registrar actividad del administrador
     */
    private function registrarActividad($accion, $detalles = []) {
        try {
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            
            $registroModelo = new RegistroActividadModelo();
            $registroModelo->registrar([
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
