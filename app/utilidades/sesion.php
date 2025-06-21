<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/utilidades/sesion.php

/**
 * Clase Sesion - Gestión de sesiones en AUTOEXAM2
 * 
 * Maneja la autenticación, verificación y cierre de sesiones,
 * controlando sesiones únicas, tokens CSRF y registro de actividad.
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.4
 */
class Sesion {
    private $sesionActivaModelo;
    
    /**
     * Constructor que inicializa la sesión si no está activa
     */
    public function __construct() {
        // Cargar modelo de sesión activa si es necesario
        if (file_exists(APP_PATH . '/modelos/sesion_activa_modelo.php')) {
            require_once APP_PATH . '/modelos/sesion_activa_modelo.php';
            $this->sesionActivaModelo = new SesionActiva();
        }
        if (session_status() == PHP_SESSION_NONE) {
            // Configurar cookies de sesión seguras
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_trans_sid', 0);
            
            // Configuración optimizada para IONOS
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true
            ]);
            
            session_start();
        }
        
        // Regenerar ID de sesión periódicamente para prevenir session fixation
        $tiempo_regeneracion = defined('SESSION_REGENERATION_TIME') ? SESSION_REGENERATION_TIME : 1800;
        if (!isset($_SESSION['ultima_regeneracion']) || 
            time() - $_SESSION['ultima_regeneracion'] > $tiempo_regeneracion) {
            $this->regenerarSesion();
        }
    }
    
    /**
     * Regenera el ID de la sesión de forma segura
     */
    public function regenerarSesion() {
        $oldSessionId = session_id();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['ultima_regeneracion'] = time();
        
        // Si hay un usuario logueado, actualizamos su ID de sesión en la BD
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['token_sesion'])) {
            $this->actualizarSesionActiva($oldSessionId, session_id());
        }
    }
    
    /**
     * Valida si hay una sesión activa del usuario
     * @return boolean True si la sesión es válida
     */
    public function validarSesionActiva() {
        // Verificar si existe la sesión de usuario
        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['token_sesion'])) {
            return false;
        }
        
        // Verificar si el token coincide con el almacenado en la base de datos
        return $this->verificarTokenSesion($_SESSION['id_usuario'], $_SESSION['token_sesion']);
    }
    
    /**
     * Verifica que el token de sesión coincida con el registrado
     * 
     * @param int $idUsuario ID del usuario
     * @param string $token Token de sesión a verificar
     * @return boolean True si el token es válido
     */
    private function verificarTokenSesion($idUsuario, $token) {
        if (!isset($this->sesionActivaModelo)) {
            return true;
        }
        
        try {
            $sesion = $this->sesionActivaModelo->verificarToken($token);
            
            if ($sesion && $sesion['id_usuario'] == $idUsuario) {
                $this->sesionActivaModelo->actualizarActividad($token);
                return true;
            }
            
            // Si falla la verificación, permitir acceso para evitar bucles
            return true;
        } catch (Exception $e) {
            return true;
        }
    }
    
    /**
     * Actualiza el ID de sesión en la tabla de sesiones activas
     * 
     * @param string $oldSessionId ID anterior de la sesión PHP
     * @param string $newSessionId Nuevo ID de la sesión PHP
     */
    private function actualizarSesionActiva($oldSessionId, $newSessionId) {
        if (!isset($this->sesionActivaModelo) || !isset($_SESSION['token_sesion'])) {
            return;
        }
        
        try {
            $sql = "UPDATE sesiones_activas 
                    SET php_session_id = :new_id, ultima_actividad = NOW()
                    WHERE php_session_id = :old_id AND token = :token";
                    
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':new_id', $newSessionId, PDO::PARAM_STR);
            $stmt->bindParam(':old_id', $oldSessionId, PDO::PARAM_STR);
            $stmt->bindParam(':token', $_SESSION['token_sesion'], PDO::PARAM_STR);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Error al actualizar ID de sesión: " . $e->getMessage());
        }
    }
    
    /**
     * Inicia la sesión de un usuario
     * @param array $usuario Datos del usuario autenticado
     * @param bool $sesionUnica Si true, cierra otras sesiones activas del mismo usuario
     * @return boolean True si se inició correctamente
     */
    public function iniciarSesion($usuario, $sesionUnica = false) {
        // Reiniciar sesión desde cero para compatibilidad máxima
        session_write_close();
        session_destroy();
        
        // Configuración optimizada para IONOS
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true
        ]);
        
        session_start();
        
        // Generar token único
        $tokenSesion = bin2hex(random_bytes(32));
        
        // Almacenar datos de sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['apellidos'] = $usuario['apellidos'];
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['token_sesion'] = $tokenSesion;
        $_SESSION['ultima_regeneracion'] = time();
        $_SESSION['ultima_actividad'] = time();
        
        // Campos opcionales
        if (isset($usuario['curso_asignado'])) {
            $_SESSION['curso_asignado'] = $usuario['curso_asignado'];
        }
        if (isset($usuario['foto'])) {
            $_SESSION['foto'] = $usuario['foto'];
        }
        
        // Registrar en base de datos si está disponible
        if (isset($this->sesionActivaModelo)) {
            try {
                $ip = $this->obtenerIP();
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
                
                $sessionId = $this->sesionActivaModelo->registrarSesion(
                    $usuario['id_usuario'],
                    $tokenSesion,
                    session_id(),
                    $ip,
                    $userAgent
                );
                
                if ($sesionUnica) {
                    $this->sesionActivaModelo->cerrarOtrasSesionesUsuario($usuario['id_usuario'], $tokenSesion);
                }
            } catch (Exception $e) {
                error_log("Error al registrar sesión en BD: " . $e->getMessage());
            }
        }
        
        return isset($_SESSION['id_usuario']) && isset($_SESSION['token_sesion']);
    }

    /**
     * Cierra la sesión del usuario actual
     */
    public function cerrarSesion() {
        // Si hay usuario logueado, registrar cierre
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['token_sesion'])) {
            $idUsuario = $_SESSION['id_usuario'];
            $tokenSesion = $_SESSION['token_sesion'];
            
            // Marcar como inactiva en la BD
            if (isset($this->sesionActivaModelo)) {
                $this->sesionActivaModelo->cerrarSesion($tokenSesion);
            }
        }
        
        // Destruir la sesión
        $this->limpiarSesion();
    }
    
    /**
     * Limpia la sesión actual
     */
    private function limpiarSesion() {
        $_SESSION = [];
        
        // Si hay cookie de sesión, eliminarla
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        // Destruir la sesión
        session_destroy();
    }
    
    /**
     * Verifica si el usuario tiene el rol requerido
     * @param string|array $rolesPermitidos Rol o array de roles permitidos
     * @return boolean True si el usuario tiene alguno de los roles permitidos
     */
    public function verificarRol($rolesPermitidos) {
        if (!isset($_SESSION['rol'])) {
            return false;
        }
        
        if (is_array($rolesPermitidos)) {
            return in_array($_SESSION['rol'], $rolesPermitidos);
        } else {
            return $_SESSION['rol'] === $rolesPermitidos;
        }
    }
    
    /**
     * Genera un token CSRF único o reutiliza uno existente válido
     * @return string Token CSRF
     */
    public function generarTokenCSRF() {
        // Si ya existe un token válido, reutilizarlo
        if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
            $tiempoValidez = defined('TOKEN_VALIDITY_TIME') ? TOKEN_VALIDITY_TIME : 3600;
            if (time() - $_SESSION['csrf_token_time'] <= $tiempoValidez) {
                return $_SESSION['csrf_token'];
            }
        }
        
        // Generar nuevo token si no existe o expiró
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida un token CSRF
     * @param string $token Token a validar
     * @param boolean $consumir Si debe eliminar el token tras validarlo (default: false)
     * @return boolean True si el token es válido
     */
    public function validarTokenCSRF($token, $consumir = false) {
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        
        // Verificar expiración del token
        $tiempoValidez = defined('TOKEN_VALIDITY_TIME') ? TOKEN_VALIDITY_TIME : 3600;
        if (isset($_SESSION['csrf_token_time']) && time() - $_SESSION['csrf_token_time'] > $tiempoValidez) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        // Solo eliminar el token si se solicita explícitamente
        if ($consumir) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }
        
        return true;
    }
    
    /**
     * Obtiene la IP real del cliente
     * 
     * @return string IP del cliente
     */
    private function obtenerIP() {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
