<?php
/**
 * Controlador para la gestión del banco de preguntas
 * 
 * Este archivo contiene la clase BancoPreguntasControlador que gestiona
 * el banco global de preguntas reutilizables del sistema.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class BancoPreguntasControlador {
    private $pregunta_banco;
    private $respuesta_banco;
    private $registro_actividad;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        try {
            require_once __DIR__ . '/../modelos/pregunta_banco_modelo.php';
            require_once __DIR__ . '/../modelos/respuesta_banco_modelo.php';
            require_once __DIR__ . '/../modelos/registro_actividad_modelo.php';
            
            $this->pregunta_banco = new PreguntaBanco();
            $this->respuesta_banco = new RespuestaBanco();
            $this->registro_actividad = new RegistroActividad();
        } catch (Exception $e) {
            error_log('Error cargando modelos en BancoPreguntasControlador: ' . $e->getMessage());
            $this->pregunta_banco = null;
        }
    }
    
    /**
     * Acción predeterminada - Listar preguntas del banco
     */
    public function index() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        try {
            $id_usuario = $_SESSION['id_usuario'];
            $filtros = $this->obtenerFiltros();
            
            // Obtener preguntas del banco
            if ($rol == 'admin') {
                $preguntas = $this->pregunta_banco->obtenerTodas($filtros);
            } else {
                $preguntas = $this->pregunta_banco->obtenerPorProfesor($id_usuario, $filtros);
            }
            
            // Cargar vista
            require_once __DIR__ . '/../vistas/profesor/banco_preguntas.php';
            
        } catch (Exception $e) {
            error_log("Error en BancoPreguntasControlador::index(): " . $e->getMessage());
            $this->mostrarError('Error al cargar el banco de preguntas');
        }
    }
    
    /**
     * Crear nueva pregunta en el banco
     */
    public function crear() {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->procesarCreacion();
        } else {
            $this->mostrarFormularioCreacion();
        }
    }
    
    /**
     * Mostrar formulario de creación
     */
    private function mostrarFormularioCreacion() {
        try {
            require_once __DIR__ . '/../vistas/profesor/nueva_pregunta_banco.php';
        } catch (Exception $e) {
            error_log("Error en mostrarFormularioCreacion(): " . $e->getMessage());
            $this->mostrarError('Error al cargar el formulario');
        }
    }
    
    /**
     * Procesar creación de pregunta
     */
    private function procesarCreacion() {
        try {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Token CSRF inválido');
            }
            
            // Validar datos
            $datos = $this->validarDatosPregunta($_POST);
            $datos['id_profesor'] = $_SESSION['id_usuario'];
            
            // Crear pregunta
            $id_pregunta = $this->pregunta_banco->crear($datos);
            
            if ($id_pregunta) {
                // Si es tipo test, crear respuestas
                if ($datos['tipo'] == 'test' && !empty($datos['respuestas'])) {
                    $this->crearRespuestasBanco($id_pregunta, $datos['respuestas']);
                }
                
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'crear_pregunta_banco',
                    'descripcion' => "Pregunta creada en banco: {$datos['tipo']}",
                    'modulo' => 'banco_preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $_SESSION['mensaje_exito'] = 'Pregunta creada en el banco correctamente';
                header("Location: " . BASE_URL . "/banco-preguntas");
                exit;
            } else {
                throw new Exception('Error al crear la pregunta en el banco');
            }
            
        } catch (Exception $e) {
            error_log("Error al crear pregunta en banco: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            $this->mostrarFormularioCreacion();
        }
    }
    
    /**
     * Editar pregunta del banco
     */
    public function editar($id_pregunta) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        try {
            // Obtener pregunta
            $pregunta = $this->pregunta_banco->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos específicos
            if ($rol == 'profesor' && $pregunta['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tienes permisos para editar esta pregunta');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->procesarEdicion($id_pregunta, $pregunta);
            } else {
                $this->mostrarFormularioEdicion($id_pregunta, $pregunta);
            }
            
        } catch (Exception $e) {
            error_log("Error al editar pregunta del banco: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            header("Location: " . BASE_URL . "/banco-preguntas");
            exit;
        }
    }
    
    /**
     * Mostrar formulario de edición
     */
    private function mostrarFormularioEdicion($id_pregunta, $pregunta) {
        try {
            // Si es tipo test, obtener respuestas
            if ($pregunta['tipo'] == 'test') {
                $pregunta['respuestas'] = $this->respuesta_banco->obtenerPorPregunta($id_pregunta);
            }
            
            require_once __DIR__ . '/../vistas/profesor/nueva_pregunta_banco.php';
            
        } catch (Exception $e) {
            error_log("Error en mostrarFormularioEdicion(): " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Procesar edición de pregunta
     */
    private function procesarEdicion($id_pregunta, $pregunta_original) {
        try {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Token CSRF inválido');
            }
            
            // Validar datos
            $datos = $this->validarDatosPregunta($_POST);
            $datos['id_pregunta'] = $id_pregunta;
            
            // Actualizar pregunta
            if ($this->pregunta_banco->actualizar($datos)) {
                // Si es tipo test, actualizar respuestas
                if ($datos['tipo'] == 'test' && !empty($datos['respuestas'])) {
                    $this->respuesta_banco->crearMultiples($id_pregunta, $datos['respuestas']);
                }
                
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'editar_pregunta_banco',
                    'descripcion' => "Pregunta editada en banco",
                    'modulo' => 'banco_preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $_SESSION['mensaje_exito'] = 'Pregunta actualizada correctamente';
            } else {
                throw new Exception('Error al actualizar la pregunta');
            }
            
            header("Location: " . BASE_URL . "/banco-preguntas");
            exit;
            
        } catch (Exception $e) {
            error_log("Error al editar pregunta: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            $this->mostrarFormularioEdicion($id_pregunta, $pregunta_original);
        }
    }
    
    /**
     * Eliminar pregunta del banco
     */
    public function eliminar($id_pregunta) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            // Obtener pregunta
            $pregunta = $this->pregunta_banco->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos específicos
            if ($rol == 'profesor' && $pregunta['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tienes permisos para eliminar esta pregunta');
            }
            
            // Eliminar pregunta
            if ($this->pregunta_banco->eliminar($id_pregunta)) {
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'eliminar_pregunta_banco',
                    'descripcion' => "Pregunta eliminada del banco",
                    'modulo' => 'banco_preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $this->responderJson(['success' => 'Pregunta eliminada correctamente']);
            } else {
                throw new Exception('Error al eliminar la pregunta');
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar pregunta del banco: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Cambiar visibilidad de pregunta (pública/privada)
     */
    public function cambiarVisibilidad($id_pregunta) {
        // Solo admin puede cambiar visibilidad
        if ($_SESSION['rol'] != 'admin') {
            $this->responderJson(['error' => 'Solo los administradores pueden cambiar la visibilidad']);
            return;
        }
        
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $publica = isset($datos['publica']) ? (bool)$datos['publica'] : false;
            
            if ($this->pregunta_banco->cambiarVisibilidad($id_pregunta, $publica)) {
                $accion = $publica ? 'hacer_publica_pregunta' : 'hacer_privada_pregunta';
                
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => $accion,
                    'descripcion' => 'Visibilidad de pregunta cambiada',
                    'modulo' => 'banco_preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $this->responderJson(['success' => 'Visibilidad actualizada correctamente']);
            } else {
                throw new Exception('Error al cambiar la visibilidad');
            }
            
        } catch (Exception $e) {
            error_log("Error al cambiar visibilidad: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Exportar preguntas del banco
     */
    public function exportar() {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        try {
            $formato = $_GET['formato'] ?? 'excel';
            $filtros = $this->obtenerFiltros();
            
            // Obtener preguntas según permisos
            if ($rol == 'admin') {
                $preguntas = $this->pregunta_banco->obtenerTodas($filtros);
            } else {
                $preguntas = $this->pregunta_banco->obtenerPorProfesor($_SESSION['id_usuario'], $filtros);
            }
            
            // Generar exportación
            require_once __DIR__ . '/../utilidades/exportar_banco_preguntas.php';
            $exportador = new ExportadorBancoPreguntas();
            
            if ($formato == 'pdf') {
                $exportador->exportarPDF($preguntas);
            } else {
                $exportador->exportarExcel($preguntas);
            }
            
            // Registrar actividad
            $this->registro_actividad->registrar([
                'id_usuario' => $_SESSION['id_usuario'],
                'accion' => 'exportar_banco_preguntas',
                'descripcion' => "Banco exportado en formato $formato",
                'modulo' => 'banco_preguntas'
            ]);
            
        } catch (Exception $e) {
            error_log("Error al exportar banco: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error al exportar el banco de preguntas';
            header("Location: " . BASE_URL . "/banco-preguntas");
            exit;
        }
    }
    
    /**
     * Crear respuestas en el banco
     */
    private function crearRespuestasBanco($id_pregunta, $respuestas) {
        foreach ($respuestas as $orden => $respuesta) {
            if (empty($respuesta['texto'])) continue;
            
            $datos_respuesta = [
                'id_pregunta' => $id_pregunta,
                'texto' => $respuesta['texto'],
                'correcta' => isset($respuesta['correcta']) ? 1 : 0
            ];
            
            if (!$this->respuesta_banco->crear($datos_respuesta)) {
                throw new Exception('Error al crear respuesta en el banco');
            }
        }
    }
    
    /**
     * Validar datos de pregunta
     */
    private function validarDatosPregunta($datos) {
        $errores = [];
        
        // Validaciones básicas
        if (empty($datos['enunciado'])) {
            $errores[] = 'El enunciado es requerido';
        }
        
        if (empty($datos['tipo']) || !in_array($datos['tipo'], ['test', 'desarrollo'])) {
            $errores[] = 'El tipo de pregunta no es válido';
        }
        
        // Validar respuestas para tipo test
        if ($datos['tipo'] == 'test') {
            if (empty($datos['respuestas']) || !is_array($datos['respuestas'])) {
                $errores[] = 'Las preguntas tipo test deben tener respuestas';
            } else {
                $tiene_correcta = false;
                $respuestas_validas = 0;
                
                foreach ($datos['respuestas'] as $respuesta) {
                    if (!empty($respuesta['texto'])) {
                        $respuestas_validas++;
                        if (isset($respuesta['correcta'])) {
                            $tiene_correcta = true;
                        }
                    }
                }
                
                if ($respuestas_validas < 2) {
                    $errores[] = 'Las preguntas tipo test deben tener al menos 2 respuestas';
                }
                
                if (!$tiene_correcta) {
                    $errores[] = 'Debe marcar al menos una respuesta como correcta';
                }
            }
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        return [
            'tipo' => $datos['tipo'],
            'enunciado' => trim($datos['enunciado']),
            'media_tipo' => $datos['media_tipo'] ?? 'ninguno',
            'media_valor' => $datos['media_valor'] ?? null,
            'origen' => 'manual',
            'publica' => isset($datos['publica']) ? 1 : 0,
            'respuestas' => $datos['respuestas'] ?? []
        ];
    }
    
    /**
     * Obtener filtros de la URL
     */
    private function obtenerFiltros() {
        return [
            'tipo' => $_GET['tipo'] ?? '',
            'busqueda' => $_GET['busqueda'] ?? '',
            'origen' => $_GET['origen'] ?? '',
            'publica' => $_GET['publica'] ?? ''
        ];
    }
    
    /**
     * Mostrar mensaje de error
     */
    private function mostrarError($mensaje) {
        $_SESSION['mensaje_error'] = $mensaje;
        header("Location: " . BASE_URL . "/banco-preguntas");
        exit;
    }
    
    /**
     * Responder con JSON
     */
    private function responderJson($datos) {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
    
    /**
     * Obtener pregunta específica (para AJAX)
     */
    public function obtener($id_pregunta) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $pregunta = $this->pregunta_banco->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos específicos (solo propias + públicas para profesores)
            if ($rol == 'profesor' && $pregunta['id_profesor'] != $_SESSION['id_usuario'] && !$pregunta['publica']) {
                throw new Exception('No tienes permisos para ver esta pregunta');
            }
            
            // Si es tipo test, obtener respuestas
            if ($pregunta['tipo'] == 'test') {
                $pregunta['respuestas'] = $this->respuesta_banco->obtenerPorPregunta($id_pregunta);
            }
            
            $this->responderJson(['success' => true, 'pregunta' => $pregunta]);
            
        } catch (Exception $e) {
            error_log("Error al obtener pregunta del banco: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
}
