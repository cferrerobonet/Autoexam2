<?php
/**
 * Controlador de Sesiones Activas - AUTOEXAM2
 * 
 * Gestiona la visualización y administración de sesiones activas en el sistema
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */
class SesionesActivasControlador {
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
        
        // Verificar si el usuario es administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            // Redirigir a página de acceso denegado
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
    }
    
    /**
     * Método predeterminado - Muestra todas las sesiones activas
     */
    public function index() {
        // Parámetros de paginación
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;
        
        // Obtener sesiones activas
        $sesionesActivas = $this->sesion->obtenerTodasSesionesActivas($porPagina, $offset);
        
        // Preparar datos para la vista
        $datos = [
            'titulo' => 'Sesiones Activas',
            'sesiones' => $sesionesActivas,
            'pagina_actual' => $pagina,
            'por_pagina' => $porPagina,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar vista
        require_once APP_PATH . '/vistas/admin/sesiones_activas/listar.php';
    }
    
    /**
     * Cierra una sesión específica
     */
    public function cerrar() {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            // Error CSRF
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/sesiones_activas');
            exit;
        }
        
        // Verificar que se recibió un token de sesión
        if (!isset($_POST['token_sesion'])) {
            $_SESSION['error'] = 'No se especificó la sesión a cerrar';
            header('Location: ' . BASE_URL . '/sesiones_activas');
            exit;
        }
        
        $token = $_POST['token_sesion'];
        
        // No permitir cerrar la sesión actual
        if ($token === $_SESSION['token_sesion']) {
            $_SESSION['error'] = 'No puede cerrar su sesión actual desde aquí. Utilice "Cerrar sesión"';
            header('Location: ' . BASE_URL . '/sesiones_activas');
            exit;
        }
        
        // Cerrar la sesión
        if ($this->sesion->cerrarSesionPorToken($token)) {
            $_SESSION['mensaje'] = 'La sesión ha sido cerrada correctamente';
        } else {
            $_SESSION['error'] = 'No se pudo cerrar la sesión';
        }
        
        // Redirigir a la lista
        header('Location: ' . BASE_URL . '/sesiones_activas');
        exit;
    }
    
    /**
     * Cierra todas las sesiones inactivas
     */
    public function limpiar() {
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            // Error CSRF
            $_SESSION['error'] = 'Error de validación de seguridad';
            header('Location: ' . BASE_URL . '/sesiones_activas');
            exit;
        }
        
        // Obtener horas de inactividad
        $horas = isset($_POST['horas_inactividad']) ? (int)$_POST['horas_inactividad'] : 24;
        
        // Limpiar sesiones inactivas
        $sesiones_cerradas = $this->sesion->limpiarSesionesInactivas($horas);
        
        // Mostrar mensaje de éxito
        $_SESSION['mensaje'] = "Se han cerrado $sesiones_cerradas sesiones inactivas";
        header('Location: ' . BASE_URL . '/sesiones_activas');
        exit;
    }
}
?>
