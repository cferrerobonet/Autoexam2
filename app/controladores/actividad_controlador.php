<?php
/**
 * Controlador de Actividad - AUTOEXAM2
 * 
 * Gestiona el historial de actividad del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class ActividadControlador {
    private $registroModelo;
    
    public function __construct() {
        // Verificar sesión
        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
            header('Location: ' . BASE_URL . '/autenticacion/login');
            exit;
        }
        
        // Solo admins pueden ver el historial completo
        if ($_SESSION['rol'] !== 'admin') {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
        $this->registroModelo = new RegistroActividad();
    }
    
    /**
     * Mostrar historial completo de actividad
     */
    public function index() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 20;
        $offset = ($pagina - 1) * $porPagina;
        
        try {
            // Obtener actividades con paginación
            $actividades = $this->obtenerTodasActividades($porPagina, $offset);
            $totalActividades = $this->contarTotalActividades();
            $totalPaginas = ceil($totalActividades / $porPagina);
            
            $datos = [
                'actividades' => $actividades,
                'pagina_actual' => $pagina,
                'total_paginas' => $totalPaginas,
                'total_actividades' => $totalActividades
            ];
            
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            require_once APP_PATH . '/vistas/admin/actividad/historial.php';
            
        } catch (Exception $e) {
            error_log('Error en historial de actividad: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el historial de actividad';
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
    
    /**
     * Obtener todas las actividades con paginación
     */
    private function obtenerTodasActividades($limite = 20, $offset = 0) {
        try {
            // Usar la conexión a través del modelo
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            $sql = "SELECT 
                        r.accion,
                        r.descripcion,
                        r.fecha,
                        r.modulo,
                        r.ip,
                        u.nombre,
                        u.apellidos
                    FROM registro_actividad r
                    LEFT JOIN usuarios u ON r.id_usuario = u.id_usuario
                    ORDER BY r.fecha DESC 
                    LIMIT :limite OFFSET :offset";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener actividades: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar total de actividades
     */
    private function contarTotalActividades() {
        try {
            // Usar la conexión a través del modelo
            $conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            $sql = "SELECT COUNT(*) as total FROM registro_actividad";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Error al contar actividades: " . $e->getMessage());
            return 0;
        }
    }
}
