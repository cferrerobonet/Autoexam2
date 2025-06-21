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
     * Método mágico para manejar rutas con guiones
     */
    public function __call($metodo, $argumentos) {
        // Convertir guiones a camelCase
        $metodoReal = str_replace('-', '', lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $metodo)))));
        
        // Casos especiales
        if ($metodo === 'cambiar-contrasena') {
            // Si hay argumentos y el primero es 'procesar', llamar al método de procesamiento
            if (!empty($argumentos) && $argumentos[0] === 'procesar') {
                $metodoReal = 'procesarCambioContrasena';
            } else {
                $metodoReal = 'cambiarContrasena';
            }
        }
        
        if (method_exists($this, $metodoReal)) {
            return call_user_func_array([$this, $metodoReal], $argumentos);
        }
        
        // Si no existe el método, redirigir al index
        header('Location: ' . BASE_URL . '/perfil');
        exit;
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
                echo '<body class="bg-light">'; // Asumiendo una clase de body común
                require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
                break;
            case 'profesor':
                require_once APP_PATH . '/vistas/parciales/head_profesor.php';
                echo '<body class="bg-light">'; // Asumiendo una clase de body común
                require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
                break;
            case 'alumno':
                require_once APP_PATH . '/vistas/parciales/head_alumno.php';
                echo '<body class="bg-light">'; // Asumiendo una clase de body común
                require_once APP_PATH . '/vistas/parciales/navbar_alumno.php';
                break;
            default:
                // Fallback o error, idealmente no debería llegar aquí con sesión validada
                require_once APP_PATH . '/vistas/parciales/head_alumno.php'; // O un head genérico
                echo '<body class="bg-light">';
                require_once APP_PATH . '/vistas/parciales/navbar_alumno.php'; // O un navbar genérico
        }
        
        require_once APP_PATH . '/vistas/perfil/index.php';
        
        // Cargar footer y scripts según el rol
        switch ($_SESSION['rol']) {
            case 'admin':
                require_once APP_PATH . '/vistas/parciales/footer_admin.php';
                require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
                break;
            case 'profesor':
                require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
                require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
                break;
            case 'alumno':
                require_once APP_PATH . '/vistas/parciales/footer_alumno.php';
                require_once APP_PATH . '/vistas/parciales/scripts_alumno.php';
                break;
        }
        echo '</body>'; // Cerrar etiqueta body
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
    
    /**
     * Actualizar información del perfil
     */
    public function actualizar() {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/perfil');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/perfil');
            exit;
        }

        try {
            // Sanitizar datos
            $datos = [
                'nombre' => trim($_POST['nombre']),
                'apellidos' => trim($_POST['apellidos']),
                'correo' => filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL)
            ];

            // Validaciones básicas
            if (empty($datos['nombre']) || empty($datos['apellidos']) || empty($datos['correo'])) {
                $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
                header('Location: ' . BASE_URL . '/perfil');
                exit;
            }

            if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'El correo electrónico no tiene un formato válido.';
                header('Location: ' . BASE_URL . '/perfil');
                exit;
            }

            // Verificar si el correo ya existe (excluyendo el usuario actual)
            if ($this->usuarioModelo->existeCorreo($datos['correo'], $_SESSION['id_usuario'])) {
                $_SESSION['error'] = 'Ya existe otro usuario con ese correo electrónico.';
                header('Location: ' . BASE_URL . '/perfil');
                exit;
            }

            // Manejar subida de foto si se proporciona
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $foto = $this->procesarSubidaFoto($_FILES['foto']);
                if ($foto) {
                    $datos['foto'] = $foto;
                }
            }

            // Actualizar en la base de datos
            if ($this->usuarioModelo->actualizarPerfil($_SESSION['id_usuario'], $datos)) {
                // Actualizar datos de sesión
                $_SESSION['nombre'] = $datos['nombre'];
                $_SESSION['apellidos'] = $datos['apellidos'];
                $_SESSION['correo'] = $datos['correo'];
                
                $_SESSION['exito'] = 'Su perfil ha sido actualizado correctamente.';
            } else {
                $_SESSION['error'] = 'No se pudo actualizar el perfil. Intente de nuevo.';
            }

        } catch (Exception $e) {
            error_log('Error al actualizar perfil: ' . $e->getMessage());
            $_SESSION['error'] = 'Ocurrió un error al actualizar el perfil.';
        }

        header('Location: ' . BASE_URL . '/perfil');
        exit;
    }

    /**
     * Vista para cambiar contraseña
     */
    public function cambiarContrasena() {
        $datos = [
            'titulo' => 'Cambiar Contraseña',
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];

        // Cargar vista según el rol
        switch ($_SESSION['rol']) {
            case 'admin':
                require_once APP_PATH . '/vistas/parciales/head_admin.php';
                echo '<body class="bg-light">';
                require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
                break;
            case 'profesor':
                require_once APP_PATH . '/vistas/parciales/head_profesor.php';
                echo '<body class="bg-light">';
                require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
                break;
            case 'alumno':
                require_once APP_PATH . '/vistas/parciales/head_alumno.php';
                echo '<body class="bg-light">';
                require_once APP_PATH . '/vistas/parciales/navbar_alumno.php';
                break;
        }

        require_once APP_PATH . '/vistas/perfil/cambiar_contrasena.php';
    }

    /**
     * Alias para cambiar contraseña con guiones bajos (compatibilidad ruteador)
     */
    public function cambiar_contrasena() {
        return $this->cambiarContrasena();
    }

    /**
     * Procesar cambio de contraseña
     */
    public function procesarCambioContrasena() {
        // Verificar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
            exit;
        }

        try {
            $contrasenaActual = $_POST['contrasena_actual'];
            $nuevaContrasena = $_POST['nueva_contrasena'];
            $confirmarContrasena = $_POST['confirmar_contrasena'];

            // Validaciones básicas
            if (empty($contrasenaActual) || empty($nuevaContrasena) || empty($confirmarContrasena)) {
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
                header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
                exit;
            }

            if ($nuevaContrasena !== $confirmarContrasena) {
                $_SESSION['error'] = 'Las nuevas contraseñas no coinciden.';
                header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
                exit;
            }

            if (strlen($nuevaContrasena) < 6) {
                $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
                header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
                exit;
            }

            // Verificar contraseña actual
            $usuario = $this->usuarioModelo->buscarPorId($_SESSION['id_usuario']);
            if (!$usuario || !password_verify($contrasenaActual, $usuario['contrasena'])) {
                $_SESSION['error'] = 'La contraseña actual es incorrecta.';
                header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
                exit;
            }

            // Actualizar contraseña
            if ($this->usuarioModelo->actualizarContrasena($_SESSION['id_usuario'], $nuevaContrasena)) {
                // Cerrar todas las otras sesiones activas
                $this->sesion->cerrarOtrasSesiones();
                
                $_SESSION['exito'] = 'Su contraseña ha sido cambiada exitosamente. Se han cerrado sus otras sesiones.';
                header('Location: ' . BASE_URL . '/perfil');
            } else {
                $_SESSION['error'] = 'No se pudo cambiar la contraseña. Intente de nuevo.';
                header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
            }

        } catch (Exception $e) {
            error_log('Error al cambiar contraseña: ' . $e->getMessage());
            $_SESSION['error'] = 'Ocurrió un error al cambiar la contraseña.';
            header('Location: ' . BASE_URL . '/perfil/cambiar-contrasena');
        }

        exit;
    }

    /**
     * Procesar subida de foto de perfil
     */
    private function procesarSubidaFoto($archivo) {
        try {
            // Validar archivo
            $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
            $tamanoMaximo = 2 * 1024 * 1024; // 2MB
            
            $info = pathinfo($archivo['name']);
            $extension = strtolower($info['extension']);
            
            if (!in_array($extension, $extensionesPermitidas)) {
                $_SESSION['error'] = 'Solo se permiten archivos JPG, PNG o GIF.';
                return false;
            }
            
            if ($archivo['size'] > $tamanoMaximo) {
                $_SESSION['error'] = 'El archivo debe ser menor a 2MB.';
                return false;
            }
            
            // Crear directorio si no existe
            $directorioDestino = 'almacenamiento/subidas/imagenes/';
            if (!file_exists($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }
            
            // Generar nombre único
            $nombreArchivo = 'perfil_' . $_SESSION['id_usuario'] . '_' . time() . '.' . $extension;
            $rutaCompleta = $directorioDestino . $nombreArchivo;
            
            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return $rutaCompleta;
            } else {
                $_SESSION['error'] = 'No se pudo subir la imagen.';
                return false;
            }
            
        } catch (Exception $e) {
            error_log('Error al procesar foto de perfil: ' . $e->getMessage());
            return false;
        }
    }
}
?>
