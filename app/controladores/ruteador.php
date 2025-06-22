<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/controladores/ruteador.php

/**
 * Ruteador - Sistema MVC de AUTOEXAM2
 * 
 * Esta clase se encarga de procesar las peticiones HTTP y direccionar
 * al controlador y acción correspondiente según la URL.
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.2
 */
class Ruteador {
    private $controladorPredeterminado = 'inicio';
    private $accionPredeterminada = 'index';
    private $controlador;
    private $accion;
    private $parametros = [];

    /**
     * Constructor que inicializa el sistema de rutas
     */
    public function __construct() {
        // Registrar manejador de errores personalizado
        set_error_handler([$this, 'manejarError']);
        set_exception_handler([$this, 'manejarExcepcion']);
    }

    /**
     * Procesa la URL actual y dirige la petición
     */
    public function procesarPeticion() {
        // Debug: Registrar inicio del procesamiento
        error_log('RUTEADOR DEBUG: Iniciando procesarPeticion()');
        
        try {
            // Obtener la URL
            $url = $this->obtenerUrl();
            error_log('RUTEADOR DEBUG: URL obtenida: ' . print_r($url, true));
            
            // Validar controlador
            if (isset($url[0]) && !empty($url[0])) {
                // Casos especiales para controladores con nombres específicos
                if ($url[0] === 'calendario' && file_exists(APP_PATH . '/controladores/calendario_controlador.php')) {
                    $this->controlador = 'calendario';
                    unset($url[0]);
                } elseif ($url[0] === 'configuracion' && file_exists(APP_PATH . '/controladores/configuracion_controlador.php')) {
                    $this->controlador = 'configuracion';
                    unset($url[0]);
                } elseif ($url[0] === 'mantenimiento' && file_exists(APP_PATH . '/controladores/mantenimiento_controlador.php')) {
                    $this->controlador = 'mantenimiento';
                    unset($url[0]);
                } elseif ($url[0] === 'banco-preguntas' && file_exists(APP_PATH . '/controladores/banco_preguntas_controlador.php')) {
                    $this->controlador = 'banco_preguntas';
                    unset($url[0]);
                } elseif ($url[0] === 'sesiones-activas' && file_exists(APP_PATH . '/controladores/sesiones_activas_controlador.php')) {
                    $this->controlador = 'sesiones_activas';
                    unset($url[0]);
                } elseif ($url[0] === 'examenes' && file_exists(APP_PATH . '/controladores/examenes_controlador.php')) {
                    $this->controlador = 'examenes';
                    unset($url[0]);
                } elseif ($url[0] === 'preguntas' && file_exists(APP_PATH . '/controladores/preguntas_controlador.php')) {
                    $this->controlador = 'preguntas';
                    unset($url[0]);
                } elseif (file_exists(APP_PATH . '/controladores/' . $url[0] . '_controlador.php')) {
                    $this->controlador = $url[0];
                    unset($url[0]);
                } else {
                    $this->controlador = $this->controladorPredeterminado;
                }
            } else {
                $this->controlador = $this->controladorPredeterminado;
            }

            // Cargar el archivo del controlador
            $archivo_controlador = APP_PATH . '/controladores/' . $this->controlador . '_controlador.php';
            if (!file_exists($archivo_controlador)) {
                throw new Exception("Controlador no encontrado: " . $archivo_controlador);
            }
            
            require_once $archivo_controlador;
            
            // Instanciar el controlador
            $nombreClase = $this->obtenerNombreClase($this->controlador);
            if (!class_exists($nombreClase)) {
                throw new Exception("Clase de controlador no encontrada: " . $nombreClase);
            }
            
            $this->controlador = new $nombreClase();
            
            // Validar acción
            if (isset($url[1]) && !empty($url[1])) {
                // Convertir guiones a guiones bajos para los nombres de métodos
                $accion_con_guiones_bajos = str_replace('-', '_', $url[1]);
                
                if (method_exists($this->controlador, $accion_con_guiones_bajos)) {
                    $this->accion = $accion_con_guiones_bajos;
                    unset($url[1]);
                } elseif (method_exists($this->controlador, $url[1])) {
                    $this->accion = $url[1];
                    unset($url[1]);
                } else {
                    $this->accion = $this->accionPredeterminada;
                }
            } else {
                $this->accion = $this->accionPredeterminada;
            }
            
            // Obtener parámetros
            $this->parametros = $url ? array_values($url) : [];
            
            // Verificar si es necesario comprobar la sesión para esta acción
            $accionesPublicas = ['login', 'recuperar', 'restablecer', 'verificar', 'error'];
            
            // Si la acción no es pública, verificamos que haya una sesión activa
            if (!in_array($this->accion, $accionesPublicas) && !is_a($this->controlador, 'InstaladorControlador')) {
                // Verificación simplificada de sesión activa
                if (!isset($_SESSION['id_usuario'])) {
                    header('Location: ' . BASE_URL . '/autenticacion/login');
                    exit;
                }
            }
            
            // Llamar al método del controlador con los parámetros
            call_user_func_array([$this->controlador, $this->accion], $this->parametros);
            
        } catch (Exception $e) {
            // Manejar cualquier excepción que ocurra durante el procesamiento
            $this->manejarExcepcion($e);
        }
    }
    
    /**
     * Obtiene y procesa la URL de la petición
     * @return array Partes de la URL procesada
     */
    private function obtenerUrl() {
        if (isset($_GET['url'])) {
            // Limpiar URL
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
    
    /**
     * Manejador personalizado de errores
     */
    public function manejarError($nivel, $mensaje, $archivo, $linea) {
        // Registrar error en el log
        $this->registrarError('ERROR', $mensaje, $archivo, $linea);
        
        // Si es un error crítico, mostrar página de error
        if (in_array($nivel, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            require_once APP_PATH . '/vistas/error/error500.php';
            exit;
        }
    }
    
    /**
     * Manejador personalizado de excepciones
     */
    public function manejarExcepcion($excepcion) {
        // Registrar excepción en el log
        $this->registrarError(
            'EXCEPCION', 
            $excepcion->getMessage(), 
            $excepcion->getFile(), 
            $excepcion->getLine()
        );
        
        // Mostrar página de error apropiada
        $this->mostrarPaginaError500();
        exit;
    }
    
    /**
     * Registra errores en el archivo de log
     */
    private function registrarError($tipo, $mensaje, $archivo, $linea) {
        // Formatear mensaje de error de manera segura
        $mensajeLog = $tipo . " | " . $mensaje . " | " . $archivo . ":" . $linea;
        
        // Utilizar la función de log centralizada si está disponible
        if (function_exists('log_message')) {
            log_message($mensajeLog, 'errors', 'error');
        } else {
            // Fallback: escribir directamente al log de errores de PHP
            error_log($mensajeLog);
        }
    }
    
    /**
     * Muestra la página de error 500 de manera segura
     */
    private function mostrarPaginaError500() {
        // Limpiar cualquier output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Establecer código de respuesta HTTP
        http_response_code(500);
        
        // Intentar cargar la vista de error personalizada
        $error_page = APP_PATH . '/vistas/error/error500.php';
        if (file_exists($error_page)) {
            require_once $error_page;
        } else {
            // Fallback: página de error básica
            echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .error-container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .error-title { color: #e74c3c; font-size: 24px; margin-bottom: 15px; }
        .error-message { color: #333; line-height: 1.6; margin-bottom: 20px; }
        .error-actions { text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Error interno del servidor</h1>
        <p class="error-message">Lo sentimos, ha ocurrido un error interno en el servidor. Nuestro equipo técnico ha sido notificado y está trabajando para resolver el problema.</p>
        <div class="error-actions">
            <a href="' . (defined('BASE_URL') ? BASE_URL : '/') . '" class="btn">Volver al inicio</a>
            <a href="' . (defined('BASE_URL') ? BASE_URL : '/') . '/autenticacion/login" class="btn">Iniciar sesión</a>
        </div>
    </div>
</body>
</html>';
        }
    }
    
    /**
     * Obtiene el nombre correcto de la clase del controlador
     * 
     * @param string $controlador Nombre del controlador
     * @return string Nombre de la clase
     */
    private function obtenerNombreClase($controlador) {
        // Casos especiales para nombres de clase compuestos
        switch ($controlador) {
            case 'banco_preguntas':
                return 'BancoPreguntasControlador';
            case 'sesiones_activas':
                return 'SesionesActivasControlador';
            default:
                return ucfirst($controlador) . 'Controlador';
        }
    }
}
