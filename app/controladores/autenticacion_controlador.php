<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/controladores/autenticacion_controlador.php

/**
 * Controlador de Autenticación - AUTOEXAM2
 * 
 * Gestiona el login, logout y recuperación de contraseña
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.3
 * @since 23/06/2025 Refactorizado con sanitización mejorada
 */
class AutenticacionControlador {
    private $sesion;
    private $usuarioModelo;
    private $sanitizador;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cargar utilidades necesarias
        require_once APP_PATH . '/utilidades/sesion.php';
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        require_once APP_PATH . '/utilidades/fuerza_bruta.php'; // Protección fuerza bruta
        require_once APP_PATH . '/utilidades/sanitizador.php'; // Sanitización de entradas
        
        $this->sesion = new Sesion();
        $this->usuarioModelo = new Usuario();
        $this->sanitizador = new Sanitizador();
    }
    
    /**
     * Método predeterminado - Redirige al login
     */
    public function index() {
        $this->login();
    }
    
    /**
     * Muestra y procesa el formulario de login
     */
    public function login() {
        // Verificar si ya hay sesión activa
        if ($this->sesion->validarSesionActiva()) {
            // Redirigir al dashboard
            header('Location: ' . BASE_URL . '/inicio');
            exit;
        }
        
        $datos = [
            'titulo' => 'Iniciar Sesión',
            'error' => null,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Procesar el formulario si se envió
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validación CSRF mejorada
                if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
                    throw new Exception('Error de seguridad. Por favor, inténtelo de nuevo.');
                }
                
                // Obtener y sanitizar campos del POST
                $datos_post = Sanitizador::post(['correo', 'contrasena'], [
                    'correo' => 'email'
                ]);
                
                // Validar campos obligatorios
                if (empty($datos_post['correo']) || empty($datos_post['contrasena'])) {
                    throw new Exception('Por favor, complete todos los campos.');
                }
                
                $correo = $datos_post['correo'];
                $contrasena = $datos_post['contrasena'];
                
                // Validación mejorada del correo electrónico
                if (!Sanitizador::esEmailValido($correo)) {
                    throw new Exception('El formato del correo electrónico no es válido.');
                }
                
                // Sanitizar IP
                $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
                
            } catch (Exception $e) {
                $datos['error'] = $e->getMessage();
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/login.php';
                return;
            }
            
            try {
                // Verificar que la base de datos esté disponible
                if (!$this->usuarioModelo->verificarConexion()) {
                    // Fallback a credenciales de desarrollo si no hay BD
                    if ($correo === ADMIN_EMAIL && $contrasena === ADMIN_PASSWORD) {
                        // Registrar el uso de credenciales de fallback
                        error_log("AVISO: Se usaron credenciales de administrador fallback debido a que la base de datos no está disponible.");
                        
                        $usuario = [
                            'id_usuario' => 1,
                            'nombre' => 'Administrador',
                            'apellidos' => 'Sistema',
                            'correo' => ADMIN_EMAIL,
                            'rol' => 'admin',
                            'activo' => 1
                        ];
                        
                        // Iniciar sesión
                        $this->sesion->iniciarSesion($usuario);
                        
                        // Redireccionar al dashboard
                        header('Location: ' . BASE_URL . '/inicio');
                        exit;
                    } else {
                        $datos['error'] = 'Base de datos no disponible. Use las credenciales de administrador (consulte al administrador del sistema).';
                        $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                    }
                } else {
                    // Inicializar la protección contra fuerza bruta
                    $conexionDB = $this->usuarioModelo->getConexion();
                    $proteccionFB = new FuerzaBruta(
                        $conexionDB, 
                        FB_MAX_INTENTOS, 
                        FB_TIEMPO_BLOQUEO
                    );
                    
                    // Verificar si la IP+correo está bloqueada (excepto para admin)
                    if ($correo !== ADMIN_EMAIL) {
                        $bloqueo = $proteccionFB->estaBloqueado($ip, $correo);
                        if ($bloqueo !== false && $bloqueo['bloqueado']) {
                            // Calcular minutos restantes de bloqueo y aplicar límites
                            $minutos = max(1, min(60, ceil($bloqueo['tiempo_restante'] / 60)));
                            $datos['error'] = "Demasiados intentos fallidos. Por favor, intente nuevamente después de {$minutos} minutos o utilice la opción de recuperar contraseña.";
                            $datos['mostrar_recuperacion'] = true;
                            $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                            
                            // Registrar el intento durante bloqueo
                            $this->registrarIntentoAccesoBloqueado($ip, $correo);
                            
                            require_once APP_PATH . '/vistas/autenticacion/login.php';
                            return;
                        }
                    }
                    
                    // Buscar usuario en la base de datos
                    $usuarioBD = $this->usuarioModelo->buscarPorCorreo($correo);
                    
                    if ($usuarioBD && $this->usuarioModelo->verificarContrasena($contrasena, $usuarioBD['contrasena'])) {
                        // Reiniciar contador de intentos fallidos
                        $proteccionFB->reiniciarIntentos($ip, $correo);
                        
                        // Preparar datos del usuario para la sesión
                        $usuario = [
                            'id_usuario' => $usuarioBD['id_usuario'],
                            'nombre' => $usuarioBD['nombre'],
                            'apellidos' => $usuarioBD['apellidos'],
                            'correo' => $usuarioBD['correo'],
                            'rol' => $usuarioBD['rol'],
                            'curso_asignado' => $usuarioBD['curso_asignado'],
                            'foto' => $usuarioBD['foto'],
                            'activo' => $usuarioBD['activo']
                        ];
                        
                        // Registrar último acceso
                        $this->usuarioModelo->registrarUltimoAcceso($usuarioBD['id_usuario']);
                        
                        // Registrar actividad de inicio de sesión
                        $this->registrarActividadLogin($usuarioBD['id_usuario'], $usuarioBD['nombre'], $usuarioBD['apellidos']);
                        
                        // Verificar si se solicitó la opción de sesión única
                        $sesionUnica = isset($_POST['sesion_unica']) ? true : false;
                        
                        // Iniciar sesión con o sin sesión única
                        $this->sesion->iniciarSesion($usuario, $sesionUnica);
                        
                        // Redireccionar al dashboard
                        header('Location: ' . BASE_URL . '/inicio');
                        exit;
                    } else {
                        // Correo especial de administrador
                        if ($correo === ADMIN_EMAIL) {
                            $datos['error'] = 'Credenciales de administrador incorrectas.';
                        } else {
                            // Registrar intento fallido
                            $resultado = $proteccionFB->registrarIntentoFallido($ip, $correo);
                            
                            // Mensaje genérico para no revelar información
                            $datos['error'] = 'Correo electrónico o contraseña incorrectos.';
                            
                            // Si está cerca de bloquearse, mostrar advertencia
                            if ($resultado && !$resultado['bloqueado'] && $resultado['intentos'] >= 3) {
                                $intentosRestantes = $resultado['maxIntentos'] - $resultado['intentos'];
                                if ($intentosRestantes > 0) {
                                    $datos['error'] .= " Le quedan {$intentosRestantes} intentos antes de que se bloquee temporalmente el acceso.";
                                }
                            }
                            
                            // Si ya se bloqueó en este intento
                            if ($resultado && $resultado['bloqueado']) {
                                $minutos = max(1, min(60, FB_TIEMPO_BLOQUEO));
                                $datos['error'] = "Demasiados intentos fallidos. Por favor, intente nuevamente después de {$minutos} minutos o utilice la opción de recuperar contraseña.";
                                $datos['mostrar_recuperacion'] = true;
                            }
                        }
                        
                        $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                    }
                }
                
            } catch (Exception $e) {
                // Error en la base de datos - usar fallback de desarrollo
                error_log("Error de autenticación: " . $e->getMessage());
                
                if ($correo === ADMIN_EMAIL && $contrasena === ADMIN_PASSWORD) {
                    // Registrar el uso de credenciales de fallback
                    error_log("AVISO: Se usaron credenciales de administrador fallback debido a error en la base de datos: " . $e->getMessage());
                    
                    $usuario = [
                        'id_usuario' => 1,
                        'nombre' => 'Administrador',
                        'apellidos' => 'Sistema',
                        'correo' => ADMIN_EMAIL,
                        'rol' => 'admin',
                        'activo' => 1
                    ];
                    
                    // Verificar si se solicitó la opción de sesión única
                    $sesionUnica = isset($_POST['sesion_unica']) ? true : false;
                    
                    // Iniciar sesión con o sin sesión única
                    $this->sesion->iniciarSesion($usuario, $sesionUnica);
                    
                    // Redireccionar al dashboard
                    header('Location: ' . BASE_URL . '/inicio');
                    exit;
                } else {
                    $datos['error'] = 'Error del sistema. Inténtelo más tarde o use las credenciales de administrador.';
                    $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                }
            }
        }
        
        // Mostrar la vista de login
        require_once APP_PATH . '/vistas/autenticacion/login.php';
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        $this->sesion->cerrarSesion();
        header('Location: ' . BASE_URL . '/autenticacion/login');
        exit;
    }
    
    /**
     * Muestra y procesa el formulario de recuperación de contraseña
     */
    public function recuperar() {
        // Verificar si ya hay sesión activa
        if ($this->sesion->validarSesionActiva()) {
            // Redirigir al dashboard
            header('Location: ' . BASE_URL . '/inicio');
            exit;
        }
        
        // Cargar servicios y modelos necesarios
        require_once APP_PATH . '/modelos/token_recuperacion_modelo.php';
        require_once APP_PATH . '/servicios/recuperacion_servicio.php';
        require_once APP_PATH . '/utilidades/correo.php';
        
        $tokenModelo = new TokenRecuperacion();
        $correoUtil = new Correo();
        $servicioRecuperacion = new RecuperacionServicio($tokenModelo, $this->usuarioModelo, $correoUtil);
        
        $datos = [
            'titulo' => 'Recuperar Contraseña',
            'mensaje' => null,
            'error' => null,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Procesar el formulario si se envió
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
                $datos['error'] = 'Error de seguridad. Por favor, inténtelo de nuevo.';
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/recuperar.php';
                return;
            }
            
            // Validar correo
            if (empty($_POST['correo'])) {
                $datos['error'] = 'Por favor, ingrese su correo electrónico.';
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/recuperar.php';
                return;
            }
            
            // Sanitizar entrada
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            
            try {
                // Usar el servicio de recuperación para procesar la solicitud
                $resultado = $servicioRecuperacion->procesarSolicitudRecuperacion($correo);
                
                // Transferir resultados a la vista
                $datos['mensaje'] = $resultado['mensaje'];
                $datos['error'] = $resultado['error'];
                
                // Limpiar tokens expirados después de cada solicitud para mantener la base de datos ordenada
                $servicioRecuperacion->limpiarTokensExpirados();
            } catch (Exception $e) {
                $datos['error'] = 'Ha ocurrido un error al procesar su solicitud. Por favor, inténtelo más tarde.';
                error_log("Error en recuperación de contraseña: " . $e->getMessage());
            }
            
            $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
        }
        
        // Mostrar la vista de recuperación
        require_once APP_PATH . '/vistas/autenticacion/recuperar.php';
    }
    
    /**
     * Página de restablecimiento de contraseña a partir de un token
     */
    public function restablecer($token = null) {
        // Registrar intento de acceso para debugging
        error_log("Acceso a restablecer contraseña con token: " . ($token ? substr($token, 0, 10) . '...' : 'no proporcionado'));
        
        // Verificar si ya hay sesión activa
        if ($this->sesion->validarSesionActiva()) {
            error_log("Usuario ya tiene sesión activa. Redirigiendo al dashboard.");
            // Redirigir al dashboard
            header('Location: ' . BASE_URL . '/inicio');
            exit;
        }
        
        // Si no se proporcionó un token, redirigir a la página de recuperación
        if (!$token) {
            error_log("No se proporcionó token. Redirigiendo a recuperación.");
            header('Location: ' . BASE_URL . '/autenticacion/recuperar');
            exit;
        }
        
        // Cargar servicios y modelos necesarios
        require_once APP_PATH . '/modelos/token_recuperacion_modelo.php';
        require_once APP_PATH . '/servicios/recuperacion_servicio.php';
        require_once APP_PATH . '/utilidades/validador_contrasena.php';
        require_once APP_PATH . '/utilidades/correo.php';
        
        $tokenModelo = new TokenRecuperacion();
        $correoUtil = new Correo();
        $servicioRecuperacion = new RecuperacionServicio($tokenModelo, $this->usuarioModelo, $correoUtil);
        $validadorContrasena = new ValidadorContrasena();
        
        // Datos para la vista
        $datos = [
            'titulo' => 'Restablecer Contraseña',
            'mensaje' => null,
            'error' => null,
            'token' => $token,
            'csrf_token' => $this->sesion->generarTokenCSRF(),
            'requisitos' => $validadorContrasena->obtenerRequisitos()
        ];
        
        // Verificar validez del token usando el servicio
        $verificacion = $servicioRecuperacion->validarToken($token);
        
        if (!$verificacion) {
            $datos['error'] = 'El enlace de restablecimiento es inválido o ha expirado. Por favor, solicite uno nuevo.';
            require_once APP_PATH . '/vistas/autenticacion/restablecer_error.php';
            return;
        }
        
        // Procesar el formulario si se envió
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
                $datos['error'] = 'Error de seguridad. Por favor, inténtelo de nuevo.';
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/restablecer.php';
                return;
            }
            
            // Validar campos
            if (empty($_POST['nueva_contrasena']) || empty($_POST['confirmar_contrasena'])) {
                $datos['error'] = 'Todos los campos son obligatorios.';
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/restablecer.php';
                return;
            }
            
            // Verificar coincidencia de contraseñas usando el validador
            $coincidencia = $validadorContrasena->validarCoincidencia(
                $_POST['nueva_contrasena'], 
                $_POST['confirmar_contrasena']
            );
            
            if (!$coincidencia['coinciden']) {
                $datos['error'] = $coincidencia['error'];
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/restablecer.php';
                return;
            }
            
            // Validar complejidad de la contraseña usando el validador
            $complejidad = $validadorContrasena->validarComplejidad($_POST['nueva_contrasena']);
            
            if (!$complejidad['valida']) {
                $datos['error'] = $complejidad['error'];
                $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
                require_once APP_PATH . '/vistas/autenticacion/restablecer.php';
                return;
            }
            
            try {
                // Actualizar la contraseña usando el servicio
                $actualizado = $servicioRecuperacion->actualizarContrasena(
                    $verificacion['id_usuario'], 
                    $verificacion['id_token'],
                    $_POST['nueva_contrasena']
                );
                
                if ($actualizado) {
                    // Redirigir a la página de éxito
                    $datos['mensaje'] = 'Su contraseña ha sido actualizada correctamente. Ahora puede iniciar sesión con su nueva contraseña.';
                    require_once APP_PATH . '/vistas/autenticacion/restablecer_exito.php';
                    return;
                } else {
                    $datos['error'] = 'No se pudo actualizar la contraseña. Por favor, inténtelo de nuevo.';
                }
            } catch (Exception $e) {
                $datos['error'] = 'Ha ocurrido un error al procesar su solicitud. Por favor, inténtelo más tarde.';
                error_log("Error al restablecer contraseña: " . $e->getMessage());
            }
            
            $datos['csrf_token'] = $this->sesion->generarTokenCSRF();
        }
        
        // Mostrar la vista del formulario para restablecer contraseña
        require_once APP_PATH . '/vistas/autenticacion/restablecer.php';
    }
    
    /**
     * Muestra una página de error de autenticación
     */
    public function error($mensaje = null) {
        $datos = [
            'titulo' => 'Error de Autenticación',
            'mensaje' => $mensaje ?? 'No tiene permiso para acceder a esta página.'
        ];
        
        require_once APP_PATH . '/vistas/error/error403.php';
    }
    
    /**
     * Registra un intento de acceso mientras la cuenta está bloqueada
     * 
     * @param string $ip Dirección IP del cliente
     * @param string $correo Correo electrónico utilizado en el intento
     */
    private function registrarIntentoAccesoBloqueado($ip, $correo) {
        try {
            // Solo registrar si tenemos conexión a base de datos
            if ($this->usuarioModelo->verificarConexion()) {
                $conexionDB = $this->usuarioModelo->getConexion();
                
                // Sanitizar entradas
                $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
                $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? 
                             substr(htmlspecialchars($_SERVER['HTTP_USER_AGENT']), 0, 255) : 
                             'Desconocido';
                
                // Limitar el correo para privacidad en logs
                $nombrePorcion = explode('@', $correo)[0] ?? 'desconocido';
                $correoParcial = substr($nombrePorcion, 0, 3) . '***@***';
                
                $stmt = $conexionDB->prepare("
                    INSERT INTO registro_actividad (
                        id_usuario, accion, descripcion, fecha, ip, user_agent, modulo
                    ) VALUES (
                        NULL, 'intento_login_bloqueado', :descripcion, NOW(), :ip, :user_agent, 'autenticacion'
                    )
                ");
                
                $descripcion = "Intento de login durante bloqueo para {$correoParcial}";
                
                $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
                $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Error al registrar intento de acceso bloqueado: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar actividad de inicio de sesión exitoso
     */
    private function registrarActividadLogin($idUsuario, $nombre, $apellidos) {
        try {
            require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
            $registroActividad = new RegistroActividad();
            
            $descripcion = "Inicio de sesión: {$apellidos}, {$nombre}";
            $registroActividad->registrar(
                $idUsuario,
                'inicio_sesion',
                $descripcion,
                'autenticacion'
            );
        } catch (Exception $e) {
            error_log("Error al registrar actividad de login: " . $e->getMessage());
        }
    }
}
