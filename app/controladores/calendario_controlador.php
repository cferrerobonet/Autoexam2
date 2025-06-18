<?php
/**
 * Controlador del Calendario - AUTOEXAM2
 * 
 * Gestiona las funciones relacionadas con el calendario de exámenes y eventos
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class CalendarioControlador {
    
    /**
     * Constructor del controlador
     */
    public function __construct() {
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL . '/autenticacion/iniciar');
            exit;
        }
    }
    
    /**
     * Método por defecto - Muestra la vista del calendario
     */
    public function index() {
        $datos = [
            'titulo' => 'Calendario de Exámenes'
        ];
        
        // Cargar vista del calendario
        require_once APP_PATH . '/vistas/comunes/calendario.php';
    }
    
    /**
     * Carga eventos según el rol del usuario y los filtros aplicados (API)
     */
    public function cargarEventos() {
        // Esta función se usaría como endpoint API para cargar eventos dinámicamente
        // Por ahora usamos datos estáticos en la vista
        
        header('Content-Type: application/json');
        
        // Simulación de eventos para demostración
        $eventos = [
            [
                'title' => 'Examen Matemáticas - Ecuaciones',
                'start' => '2025-06-20',
                'end' => '2025-06-20',
                'color' => '#4285F4',
                'tipo' => 'examen',
                'modulo' => 'Álgebra',
                'curso' => 'Matemáticas 3º ESO',
                'descripcion' => 'Examen sobre resolución de ecuaciones de segundo grado'
            ],
            [
                'title' => 'Examen Literatura - La Celestina',
                'start' => '2025-06-22',
                'color' => '#34A853',
                'tipo' => 'examen',
                'modulo' => 'Literatura Medieval',
                'curso' => 'Lengua 3º ESO',
                'descripcion' => 'Examen sobre La Celestina y su contexto histórico'
            ],
            [
                'title' => 'Entrega trabajo Historia',
                'start' => '2025-06-25',
                'color' => '#EA4335',
                'tipo' => 'entrega',
                'modulo' => 'Historia Moderna',
                'curso' => 'Historia 4º ESO',
                'descripcion' => 'Entrega del trabajo sobre la Revolución Francesa'
            ]
        ];
        
        echo json_encode($eventos);
    }
}
