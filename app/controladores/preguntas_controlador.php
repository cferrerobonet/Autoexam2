<?php
/**
 * Controlador para la gestión de preguntas
 * 
 * Este archivo contiene la clase PreguntasControlador que gestiona todas las
 * operaciones relacionadas con preguntas en el sistema.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class PreguntasControlador {
    private $pregunta;
    private $respuesta;
    private $examen;
    private $registro_actividad;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        try {
            require_once __DIR__ . '/../modelos/pregunta_modelo.php';
            require_once __DIR__ . '/../modelos/respuesta_modelo.php';
            require_once __DIR__ . '/../modelos/examen_modelo.php';
            require_once __DIR__ . '/../modelos/registro_actividad_modelo.php';
            
            $this->pregunta = new Pregunta();
            $this->respuesta = new Respuesta();
            $this->examen = new Examen();
            $this->registro_actividad = new RegistroActividad();
        } catch (Exception $e) {
            error_log('Error cargando modelos en PreguntasControlador: ' . $e->getMessage());
            $this->pregunta = null;
        }
    }
    
    /**
     * Guardar pregunta (crear o actualizar)
     */
    public function guardar() {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Verificar permisos
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor')) {
            $this->responderJson(['error' => 'Sin permisos para esta acción']);
            return;
        }
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            // Verificar token CSRF para mayor seguridad
            if (!isset($_POST['csrf_token']) || !$this->verificarTokenCSRF($_POST['csrf_token'])) {
                throw new Exception('Error de validación de seguridad');
            }
            
            // Validar datos con sanitización integrada
            $datos = $this->validarDatosPregunta($_POST);
            
            // Verificar permisos sobre el examen
            if (!$this->verificarPermisoExamen($datos['id_examen'])) {
                throw new Exception('No tienes permisos sobre este examen');
            }
            
            if (!empty($datos['id_pregunta'])) {
                // Actualizar pregunta existente
                $resultado = $this->actualizarPregunta($datos);
                $accion = 'editar_pregunta';
                $mensaje = 'Pregunta actualizada correctamente';
            } else {
                // Crear nueva pregunta
                $resultado = $this->crearPregunta($datos);
                $accion = 'crear_pregunta';
                $mensaje = 'Pregunta creada correctamente';
            }
            
            if ($resultado) {
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => $accion,
                    'descripcion' => $mensaje,
                    'modulo' => 'preguntas',
                    'elemento_id' => $resultado
                ]);
                
                $this->responderJson(['success' => $mensaje, 'id' => $resultado]);
            } else {
                throw new Exception('Error al guardar la pregunta');
            }
            
        } catch (Exception $e) {
            error_log("Error al guardar pregunta: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Crear nueva pregunta
     */
    private function crearPregunta($datos) {
        try {
            $this->pregunta->db->begin_transaction();
            
            // Crear pregunta
            $id_pregunta = $this->pregunta->crear($datos);
            if (!$id_pregunta) {
                throw new Exception('Error al crear la pregunta');
            }
            
            // Si es tipo test, crear respuestas
            if ($datos['tipo'] == 'test' && !empty($datos['respuestas'])) {
                $this->crearRespuestas($id_pregunta, $datos['respuestas']);
            }
            
            $this->pregunta->db->commit();
            return $id_pregunta;
            
        } catch (Exception $e) {
            $this->pregunta->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Actualizar pregunta existente
     */
    private function actualizarPregunta($datos) {
        try {
            $this->pregunta->db->begin_transaction();
            
            // Actualizar pregunta
            if (!$this->pregunta->actualizar($datos)) {
                throw new Exception('Error al actualizar la pregunta');
            }
            
            // Si es tipo test, actualizar respuestas
            if ($datos['tipo'] == 'test' && !empty($datos['respuestas'])) {
                $this->respuesta->crearMultiples($datos['id_pregunta'], $datos['respuestas']);
            }
            
            $this->pregunta->db->commit();
            return $datos['id_pregunta'];
            
        } catch (Exception $e) {
            $this->pregunta->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Crear respuestas para una pregunta
     */
    private function crearRespuestas($id_pregunta, $respuestas) {
        foreach ($respuestas as $orden => $respuesta) {
            if (empty($respuesta['texto'])) continue;
            
            $datos_respuesta = [
                'id_pregunta' => $id_pregunta,
                'texto' => $respuesta['texto'],
                'correcta' => isset($respuesta['correcta']) ? 1 : 0,
                'orden' => $orden
            ];
            
            if (!$this->respuesta->crear($datos_respuesta)) {
                throw new Exception('Error al crear respuesta');
            }
        }
    }
    
    /**
     * Verificar token CSRF
     * 
     * @param string $token Token enviado a verificar
     * @return bool Resultado de la verificación
     */
    private function verificarTokenCSRF($token) {
        if (empty($token) || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * Eliminar pregunta
     */
    public function eliminar($id_pregunta) {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Verificar permisos
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor')) {
            $this->responderJson(['error' => 'Sin permisos para esta acción']);
            return;
        }
        
        try {
            // Sanitizar el ID
            $id_pregunta = Sanitizador::entero($id_pregunta);
            if (!$id_pregunta) {
                throw new Exception('ID de pregunta inválido');
            }
            
            // Obtener datos de la pregunta
            $pregunta = $this->pregunta->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos sobre el examen
            if (!$this->verificarPermisoExamen($pregunta['id_examen'])) {
                throw new Exception('No tienes permisos sobre este examen');
            }
            
            // Eliminar pregunta
            if ($this->pregunta->eliminar($id_pregunta)) {
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'eliminar_pregunta',
                    'descripcion' => 'Pregunta eliminada del examen',
                    'modulo' => 'preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $this->responderJson(['success' => 'Pregunta eliminada correctamente']);
            } else {
                throw new Exception('Error al eliminar la pregunta');
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar pregunta: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Cambiar estado de pregunta (habilitar/deshabilitar)
     */
    public function toggle($id_pregunta) {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Verificar permisos
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor')) {
            $this->responderJson(['error' => 'Sin permisos para esta acción']);
            return;
        }
        
        try {
            // Sanitizar ID de pregunta
            $id_pregunta = Sanitizador::entero($id_pregunta);
            if (!$id_pregunta) {
                throw new Exception('ID de pregunta inválido');
            }
            
            // Obtener y sanitizar datos del cuerpo JSON
            $rawData = file_get_contents('php://input');
            $datos = json_decode($rawData, true);
            
            if (!is_array($datos)) {
                throw new Exception('Formato de datos inválido');
            }
            
            $habilitada = isset($datos['habilitada']) ? (bool)$datos['habilitada'] : false;
            
            // Obtener datos de la pregunta
            $pregunta = $this->pregunta->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos sobre el examen
            if (!$this->verificarPermisoExamen($pregunta['id_examen'])) {
                throw new Exception('No tienes permisos sobre este examen');
            }
            
            // Cambiar estado
            if ($this->pregunta->cambiarEstado($id_pregunta, $habilitada)) {
                $accion = $habilitada ? 'habilitar_pregunta' : 'deshabilitar_pregunta';
                
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => $accion,
                    'descripcion' => 'Estado de pregunta cambiado',
                    'modulo' => 'preguntas',
                    'elemento_id' => $id_pregunta
                ]);
                
                $this->responderJson(['success' => 'Estado actualizado correctamente']);
            } else {
                throw new Exception('Error al cambiar el estado');
            }
            
        } catch (Exception $e) {
            error_log("Error al cambiar estado de pregunta: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Reordenar preguntas
     */
    public function ordenar() {
        // Verificar permisos
        if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (empty($datos['orden'])) {
                throw new Exception('Datos de orden no válidos');
            }
            
            $orden_preguntas = [];
            foreach ($datos['orden'] as $item) {
                $orden_preguntas[$item['orden']] = $item['id'];
            }
            
            if ($this->pregunta->actualizarOrden($orden_preguntas)) {
                $this->responderJson(['success' => 'Orden actualizado correctamente']);
            } else {
                throw new Exception('Error al actualizar el orden');
            }
            
        } catch (Exception $e) {
            error_log("Error al ordenar preguntas: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Obtener preguntas del banco
     */
    public function banco() {
        // Verificar permisos
        if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $filtros = [];
            
            if (!empty($_GET['tipo'])) {
                $filtros['tipo'] = $_GET['tipo'];
            }
            
            if (!empty($_GET['busqueda'])) {
                $filtros['busqueda'] = $_GET['busqueda'];
            }
            
            $preguntas = $this->pregunta->obtenerDelBanco($_SESSION['id_usuario'], $filtros);
            
            $this->responderJson(['success' => true, 'preguntas' => $preguntas]);
            
        } catch (Exception $e) {
            error_log("Error al obtener banco de preguntas: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Importar preguntas del banco
     */
    public function importar() {
        // Verificar permisos
        if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            
            if (empty($datos['preguntas']) || empty($datos['id_examen'])) {
                throw new Exception('Datos incompletos');
            }
            
            // Verificar permisos sobre el examen
            if (!$this->verificarPermisoExamen($datos['id_examen'])) {
                throw new Exception('No tienes permisos sobre este examen');
            }
            
            $importadas = 0;
            foreach ($datos['preguntas'] as $id_pregunta_banco) {
                if ($this->pregunta->importarDelBanco($id_pregunta_banco, $datos['id_examen'])) {
                    $importadas++;
                }
            }
            
            // Registrar actividad
            $this->registro_actividad->registrar([
                'id_usuario' => $_SESSION['id_usuario'],
                'accion' => 'importar_preguntas',
                'descripcion' => "Importadas $importadas preguntas del banco",
                'modulo' => 'preguntas',
                'elemento_id' => $datos['id_examen']
            ]);
            
            $this->responderJson(['success' => "Se importaron $importadas preguntas correctamente"]);
            
        } catch (Exception $e) {
            error_log("Error al importar preguntas: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Obtener pregunta para edición
     */
    public function obtener($id_pregunta) {
        // Verificar permisos
        if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $pregunta = $this->pregunta->obtenerPorId($id_pregunta);
            if (!$pregunta) {
                throw new Exception('Pregunta no encontrada');
            }
            
            // Verificar permisos sobre el examen
            if (!$this->verificarPermisoExamen($pregunta['id_examen'])) {
                throw new Exception('No tienes permisos sobre este examen');
            }
            
            // Si es tipo test, obtener respuestas
            if ($pregunta['tipo'] == 'test') {
                $pregunta['respuestas'] = $this->respuesta->obtenerPorPregunta($id_pregunta);
            }
            
            $this->responderJson(['success' => true, 'pregunta' => $pregunta]);
            
        } catch (Exception $e) {
            error_log("Error al obtener pregunta: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Validar datos de pregunta
     */
    private function validarDatosPregunta($datos) {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        $errores = [];
        
        // Sanitizar datos de entrada
        $campos = ['id_pregunta', 'id_examen', 'tipo', 'enunciado', 'media_tipo', 'media_valor', 'orden'];
        $tipos = [
            'id_pregunta' => 'entero',
            'id_examen' => 'entero',
            'tipo' => 'texto',
            'enunciado' => 'texto',
            'media_tipo' => 'texto',
            'media_valor' => 'texto',
            'orden' => 'entero'
        ];
        
        $datosSanitizados = Sanitizador::array($datos, $tipos);
        
        // Validaciones básicas
        if (empty($datosSanitizados['enunciado'])) {
            $errores[] = 'El enunciado es requerido';
        }
        
        if (empty($datosSanitizados['tipo']) || !in_array($datosSanitizados['tipo'], ['test', 'desarrollo'])) {
            $errores[] = 'El tipo de pregunta no es válido';
        }
        
        if (empty($datosSanitizados['id_examen'])) {
            $errores[] = 'El examen es requerido';
        }
        
        // Sanitizar y validar respuestas para tipo test
        if ($datosSanitizados['tipo'] == 'test') {
            if (empty($datos['respuestas']) || !is_array($datos['respuestas'])) {
                $errores[] = 'Las preguntas tipo test deben tener respuestas';
                $respuestasSanitizadas = [];
            } else {
                $tiene_correcta = false;
                $respuestas_validas = 0;
                $respuestasSanitizadas = [];
                
                foreach ($datos['respuestas'] as $indice => $respuesta) {
                    // Sanitizar texto de la respuesta
                    $textoRespuesta = isset($respuesta['texto']) ? Sanitizador::texto($respuesta['texto']) : '';
                    $correcta = isset($respuesta['correcta']) ? true : false;
                    
                    $respuestasSanitizadas[$indice] = [
                        'texto' => $textoRespuesta,
                        'correcta' => $correcta
                    ];
                    
                    if (!empty($textoRespuesta)) {
                        $respuestas_validas++;
                        if ($correcta) {
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
        } else {
            $respuestasSanitizadas = [];
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        return [
            'id_pregunta' => $datosSanitizados['id_pregunta'] ? $datosSanitizados['id_pregunta'] : null,
            'id_examen' => $datosSanitizados['id_examen'],
            'tipo' => $datosSanitizados['tipo'],
            'enunciado' => $datosSanitizados['enunciado'],
            'media_tipo' => $datosSanitizados['media_tipo'] ?? 'ninguno',
            'media_valor' => $datosSanitizados['media_valor'] ?? null,
            'habilitada' => isset($datos['habilitada']) ? 1 : 1, // Por defecto habilitada
            'orden' => $datosSanitizados['orden'] ?? 0,
            'respuestas' => $respuestasSanitizadas ?: []
        ];
    }
    
    /**
     * Verificar permisos sobre el examen
     */
    private function verificarPermisoExamen($id_examen) {
        try {
            $examen = $this->examen->obtenerPorId($id_examen);
            if (!$examen) return false;
            
            // Si es admin, siempre tiene permisos
            if ($_SESSION['rol'] == 'admin') return true;
            
            // Si es profesor, verificar que sea el propietario del módulo o curso
            require_once __DIR__ . '/../modelos/modulo_modelo.php';
            require_once __DIR__ . '/../modelos/curso_modelo.php';
            
            $modulo_modelo = new Modulo();
            $curso_modelo = new Curso();
            
            $modulo = $modulo_modelo->obtenerPorId($examen['id_modulo']);
            $curso = $curso_modelo->obtenerPorId($examen['id_curso']);
            
            return ($modulo && $modulo['id_profesor'] == $_SESSION['id_usuario']) ||
                   ($curso && $curso['id_profesor'] == $_SESSION['id_usuario']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Responder con JSON
     */
    private function responderJson($datos) {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }
}
