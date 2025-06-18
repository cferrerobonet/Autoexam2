<?php
/**
 * Controlador de Perfil de Usuario - AUTOEXAM2
 * 
 * Permite al usuario gestionar sus datos personales y sesiones activas
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */
class PerfilControlador {
    private $sesion;
    private $usuarioModelo;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cargar utilidades necesarias
        require_once APP_PATH . '/utilidades/sesion.php';
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        
        $this->sesion = new Sesion();
        $this->usuarioModelo = new Usuario();
        
        // Verificar si el usuario está autenticado
        if (!$this->sesion->validarSesionActiva()) {
            header('Location: ' . BASE_URL . '/autenticacion/login');
            exit;
        }
    }
    
    /**
     * Método predeterminado - Ver/editar perfil
     */
    public function index() {
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
                    'rol' => $_SESSION['rol'] ?? ''
                ];
            }
        } catch (Exception $e) {
            error_log('Error al obtener datos del usuario en PerfilControlador: ' . $e->getMessage());
            // Si hay error, usar datos de sesión
            $usuarioActual = [
                'id_usuario' => $_SESSION['id_usuario'],
                'nombre' => $_SESSION['nombre'] ?? 'Usuario',
                'apellidos' => $_SESSION['apellidos'] ?? '',
                'correo' => $_SESSION['correo'] ?? '',
                'rol' => $_SESSION['rol'] ?? ''
            ];
        }
        
        $datos = [
            'titulo' => 'Mi Perfil',
            'csrf_token' => $this->sesion->generarTokenCSRF(),
            'usuario' => $usuarioActual
        ];
        
        // Cargar vista según el rol
        switch ($_SESSION['rol']) {
            case 'admin':
                require_once APP_PATH . '/vistas/parciales/head_admin.php';
                require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
                break;
            case 'profesor':
                require_once APP_PATH . '/vistas/comunes/cabecera.php';
                break;
            case 'alumno':
                require_once APP_PATH . '/vistas/comunes/cabecera.php';
                break;
            default:
                require_once APP_PATH . '/vistas/comunes/cabecera.php';
        }
        
        require_once APP_PATH . '/vistas/perfil/index.php';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Ver y gestionar sesiones activas del propio usuario
     */
    public function sesiones() {
        // Obtener sesiones activas del usuario actual
        $sesionesActivas = $this->sesion->obtenerSesionesActivasUsuario();
        
        $datos = [
            'titulo' => 'Mis Sesiones Activas',
            'sesiones' => $sesionesActivas,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        require_once APP_PATH . '/vistas/perfil/sesiones.php';
    }
    
    /**
     * Cerrar una sesión específica del usuario actual
     */
    public function cerrarSesion() {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            // Error CSRF
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/perfil/sesiones');
            exit;
        }
        
        // Verificar que se recibió un token de sesión
        if (!isset($_POST['token_sesion'])) {
            $_SESSION['error'] = 'No se especificó la sesión a cerrar';
            header('Location: ' . BASE_URL . '/perfil/sesiones');
            exit;
        }
        
        $token = $_POST['token_sesion'];
        
        // No permitir cerrar la sesión actual
        if ($token === $_SESSION['token_sesion']) {
            $_SESSION['error'] = 'No puede cerrar su sesión actual desde aquí. Utilice "Cerrar sesión"';
            header('Location: ' . BASE_URL . '/perfil/sesiones');
            exit;
        }
        
        // Cerrar la sesión
        if ($this->sesion->cerrarSesionPorToken($token)) {
            $_SESSION['mensaje'] = 'La sesión ha sido cerrada correctamente';
        } else {
            $_SESSION['error'] = 'No se pudo cerrar la sesión';
        }
        
        // Redirigir a la lista
        header('Location: ' . BASE_URL . '/perfil/sesiones');
        exit;
    }
    
    /**
     * Cerrar todas las otras sesiones del usuario
     */
    public function cerrarOtrasSesiones() {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            // Error CSRF
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/perfil/sesiones');
            exit;
        }
        
        // Cerrar todas las otras sesiones
        if ($this->sesion->cerrarOtrasSesiones()) {
            $_SESSION['mensaje'] = 'Todas sus otras sesiones han sido cerradas correctamente';
        } else {
            $_SESSION['error'] = 'No se pudieron cerrar las otras sesiones';
        }
        
        // Redirigir a la lista
        header('Location: ' . BASE_URL . '/perfil/sesiones');
        exit;
    }
}
?>
