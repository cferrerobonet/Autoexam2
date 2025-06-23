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
            // Cargar configuración básica
            require_once __DIR__ . '/../../config/config.php';
            
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
        // Definir controlador para navegación activa
        $GLOBALS['controlador'] = 'banco_preguntas';
        
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
            
            // Cargar vista según el rol
            if ($rol == 'admin') {
                require_once __DIR__ . '/../vistas/admin/banco_preguntas.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/banco_preguntas.php';
            }
            
        } catch (Exception $e) {
            error_log("Error en BancoPreguntasControlador::index(): " . $e->getMessage());
            $this->mostrarError('Error al cargar el banco de preguntas');
        }
    }
    
    /**
     * Crear nueva pregunta en el banco
     */
    public function crear() {
        // Definir controlador para navegación activa
        $GLOBALS['controlador'] = 'banco_preguntas';
        
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
            $rol = $_SESSION['rol'];
            
            // Cargar vista según el rol
            if ($rol == 'admin') {
                require_once __DIR__ . '/../vistas/admin/nueva_pregunta_banco.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/nueva_pregunta_banco.php';
            }
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
                $this->registro_actividad->registrar(
                    $_SESSION['id_usuario'],
                    'crear_pregunta_banco',
                    "Pregunta creada en banco: {$datos['tipo']}",
                    'banco_preguntas',
                    $id_pregunta
                );
                
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
        // Definir controlador para navegación activa
        $GLOBALS['controlador'] = 'banco_preguntas';
        
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
            
            $rol = $_SESSION['rol'];
            
            // Cargar vista según el rol
            if ($rol == 'admin') {
                require_once __DIR__ . '/../vistas/admin/nueva_pregunta_banco.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/nueva_pregunta_banco.php';
            }
            
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
            $datos = $this->validarDatosPregunta($_POST, $id_pregunta);
            $datos['id_pregunta'] = $id_pregunta;
            
            // Actualizar pregunta
            if ($this->pregunta_banco->actualizar($datos)) {
                // Si es tipo test, actualizar respuestas
                if ($datos['tipo'] == 'test' && !empty($datos['respuestas'])) {
                    $this->respuesta_banco->crearMultiples($id_pregunta, $datos['respuestas']);
                }
                
                // Registrar actividad
                $this->registro_actividad->registrar(
                    $_SESSION['id_usuario'],
                    'editar_pregunta_banco',
                    "Pregunta editada en banco",
                    'banco_preguntas',
                    $id_pregunta
                );
                
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
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->responderJson(['error' => 'Sin permisos']);
            } else {
                $_SESSION['mensaje_error'] = 'No tienes permisos para eliminar preguntas';
                header("Location: " . BASE_URL . "/banco-preguntas");
                exit;
            }
            return;
        }
        
        try {
            // Sanitizar el ID
            $id_pregunta = Sanitizador::entero($id_pregunta);
            if (!$id_pregunta) {
                throw new Exception('ID de pregunta inválido');
            }
            
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
                $this->registro_actividad->registrar(
                    $_SESSION['id_usuario'],
                    'eliminar_pregunta_banco',
                    "Pregunta eliminada del banco",
                    'banco_preguntas',
                    $id_pregunta
                );
                
                // Manejar respuesta según el tipo de solicitud
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->responderJson(['success' => 'Pregunta eliminada correctamente']);
                } else {
                    $_SESSION['mensaje_exito'] = 'Pregunta eliminada correctamente';
                    header("Location: " . BASE_URL . "/banco-preguntas");
                    exit;
                }
            } else {
                throw new Exception('Error al eliminar la pregunta');
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar pregunta del banco: " . $e->getMessage());
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->responderJson(['error' => $e->getMessage()]);
            } else {
                $_SESSION['mensaje_error'] = $e->getMessage();
                header("Location: " . BASE_URL . "/banco-preguntas");
                exit;
            }
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
                $this->registro_actividad->registrar(
                    $_SESSION['id_usuario'],
                    $accion,
                    'Visibilidad de pregunta cambiada',
                    'banco_preguntas',
                    $id_pregunta
                );
                
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
            $this->registro_actividad->registrar(
                $_SESSION['id_usuario'],
                'exportar_banco_preguntas',
                "Banco exportado en formato $formato",
                'banco_preguntas'
            );
            
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
     * Procesar archivo multimedia subido
     */
    private function procesarArchivoMultimedia($media_tipo, $pregunta_id = null) {
        if (!isset($_FILES['media_archivo']) || $_FILES['media_archivo']['error'] !== UPLOAD_ERR_OK) {
            return '';
        }
        
        $archivo = $_FILES['media_archivo'];
        $ruta_destino = '';
        
        switch ($media_tipo) {
            case 'imagen':
                $ruta_destino = $this->subirImagen($archivo, $pregunta_id);
                break;
            case 'pdf':
                $ruta_destino = $this->subirPDF($archivo, $pregunta_id);
                break;
        }
        
        return $ruta_destino;
    }
    
    /**
     * Subir imagen
     */
    private function subirImagen($archivo, $pregunta_id = null) {
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $tamano_maximo = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($archivo['type'], $tipos_permitidos)) {
            throw new Exception('Tipo de imagen no permitido. Solo JPG, PNG y GIF');
        }
        
        if ($archivo['size'] > $tamano_maximo) {
            throw new Exception('La imagen es demasiado grande. Máximo 5MB');
        }
        
        $directorio = STORAGE_PATH . '/subidas/imagenes';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'pregunta_' . ($pregunta_id ?? time()) . '_' . uniqid() . '.' . $extension;
        $ruta_completa = $directorio . '/' . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            return 'subidas/imagenes/' . $nombre_archivo;
        } else {
            throw new Exception('Error al subir la imagen');
        }
    }
    
    /**
     * Subir PDF
     */
    private function subirPDF($archivo, $pregunta_id = null) {
        $tipos_permitidos = ['application/pdf'];
        $tamano_maximo = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($archivo['type'], $tipos_permitidos)) {
            throw new Exception('Solo se permiten archivos PDF');
        }
        
        if ($archivo['size'] > $tamano_maximo) {
            throw new Exception('El PDF es demasiado grande. Máximo 10MB');
        }
        
        $directorio = STORAGE_PATH . '/subidas/documentos';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $nombre_archivo = 'pregunta_' . ($pregunta_id ?? time()) . '_' . uniqid() . '.pdf';
        $ruta_completa = $directorio . '/' . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            return 'subidas/documentos/' . $nombre_archivo;
        } else {
            throw new Exception('Error al subir el PDF');
        }
    }

    /**
     * Validar datos de pregunta
     */
    private function validarDatosPregunta($datos, $pregunta_id = null) {
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
                
                foreach ($datos['respuestas'] as $index => $respuesta) {
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
        
        // Procesar multimedia
        $media_tipo = isset($datos['media_tipo']) ? $datos['media_tipo'] : 'ninguno';
        $media_valor = '';
        
        if ($media_tipo !== 'ninguno') {
            if (in_array($media_tipo, ['imagen', 'pdf']) && isset($_FILES['media_archivo'])) {
                // Si hay archivo, procesarlo
                $media_valor = $this->procesarArchivoMultimedia($media_tipo, $pregunta_id);
            } elseif (in_array($media_tipo, ['video', 'url']) && !empty($datos['media_valor'])) {
                // Si es URL, validar formato
                if (!filter_var($datos['media_valor'], FILTER_VALIDATE_URL)) {
                    throw new Exception('La URL proporcionada no es válida');
                }
                $media_valor = $datos['media_valor'];
            } elseif (isset($datos['media_valor_actual']) && !empty($datos['media_valor_actual'])) {
                // Mantener archivo actual en edición
                $media_valor = $datos['media_valor_actual'];
            }
        }
        
        return [
            'tipo' => $datos['tipo'],
            'enunciado' => trim($datos['enunciado']),
            'categoria' => isset($datos['categoria']) ? $datos['categoria'] : 'otra',
            'dificultad' => isset($datos['dificultad']) ? $datos['dificultad'] : 'media',
            'etiquetas' => isset($datos['etiquetas']) ? trim($datos['etiquetas']) : '',
            'media_tipo' => $media_tipo,
            'media_valor' => $media_valor,
            'origen' => 'manual',
            'publica' => isset($datos['publica']) ? 1 : 0,
            'respuestas' => isset($datos['respuestas']) && is_array($datos['respuestas']) ? $datos['respuestas'] : []
        ];
    }
    
    /**
     * Obtener filtros de la URL
     */
    private function obtenerFiltros() {
        return [
            'tipo' => $_GET['tipo'] ?? '',
            'categoria' => $_GET['categoria'] ?? '',
            'dificultad' => $_GET['dificultad'] ?? '',
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
    
    // Nota: Este método ya está definido anteriormente en la clase
}
