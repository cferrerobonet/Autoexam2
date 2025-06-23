<?php
/**
 * Router API para Dashboard de Profesor - AUTOEXAM2
 * 
 * Maneja las rutas de la API para el dashboard del profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Configurar headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuración
require_once __DIR__ . '/../../config/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación básica
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener la ruta de la API
$ruta = $_GET['ruta'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

// Verificar que es un profesor
if ($_SESSION['rol'] !== 'profesor') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Cargar el controlador API
require_once APP_PATH . '/controladores/api/profesor_api_controlador.php';
$apiControlador = new ProfesorAPIControlador();

try {
    // Enrutar las peticiones
    switch ($ruta) {
        case 'cursos':
            if ($metodo === 'GET') {
                $apiControlador->obtenerCursos();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;
            
        case 'examenes':
            if ($metodo === 'GET') {
                $apiControlador->obtenerExamenes();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;
            
        case 'estadisticas':
            if ($metodo === 'GET') {
                $apiControlador->obtenerEstadisticas();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;
            
        case 'notificaciones':
            if ($metodo === 'GET') {
                $apiControlador->obtenerNotificaciones();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;
            
        case 'calendario':
            if ($metodo === 'GET') {
                $apiControlador->obtenerCalendario();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Error en API profesor: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
