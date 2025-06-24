<?php
/**
 * Controlador para la gestión de exámenes
 * 
 * Este archivo contiene la clase ExamenesControlador que gestiona todas las
 * operaciones relacionadas con exámenes en el sistema.
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
 */

class ExamenesControlador {
    private $examen;
    private $pregunta;
    private $respuesta;
    private $usuario;
    private $curso;
    private $modulo;
    private $sesion;
    private $registro_actividad;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        try {
            // Cargar modelos
            require_once __DIR__ . '/../modelos/examen_modelo.php';
            require_once __DIR__ . '/../modelos/pregunta_modelo.php';
            require_once __DIR__ . '/../modelos/respuesta_modelo.php';
            require_once __DIR__ . '/../modelos/usuario_modelo.php';
            require_once __DIR__ . '/../modelos/curso_modelo.php';
            require_once __DIR__ . '/../modelos/modulo_modelo.php';
            require_once __DIR__ . '/../modelos/registro_actividad_modelo.php';
            require_once __DIR__ . '/../utilidades/sesion.php';
            
            $this->examen = new Examen();
            $this->pregunta = new Pregunta();
            $this->respuesta = new Respuesta();
            $this->usuario = new Usuario();
            $this->curso = new Curso();
            $this->modulo = new ModuloModelo();
            $this->registro_actividad = new RegistroActividad();
            $this->sesion = new Sesion();
        } catch (Exception $e) {
            error_log('Error cargando modelos en ExamenesControlador: ' . $e->getMessage());
            $this->examen = null;
        }
    }
    
    /**
     * Acción predeterminada - Listar exámenes
     */
    public function index() {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Definir controlador para el navbar
        $GLOBALS['controlador'] = 'examenes';
        
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        try {
            $id_usuario = $_SESSION['id_usuario'];
            
            // Obtener y sanitizar filtros
            $filtros = $this->obtenerFiltrosSanitizados();
            
            // Obtener exámenes según el rol
            if ($rol == 'admin') {
                $examenes = $this->examen->obtenerTodos($filtros);
            } else {
                $examenes = $this->examen->obtenerPorProfesor($id_usuario, $filtros);
            }
            
            // Obtener cursos y módulos para filtros
            $cursos_result = $this->curso->obtenerTodos(100);
            $cursos = isset($cursos_result['cursos']) ? $cursos_result['cursos'] : $cursos_result;
            
            $modulos_result = $this->modulo->obtenerTodos(100);
            $modulos = isset($modulos_result['modulos']) ? $modulos_result['modulos'] : $modulos_result;
            
            // Preparar datos para las vistas
            $datos = [
                'examenes' => $examenes,
                'cursos' => $cursos,
                'modulos' => $modulos,
                'filtros' => $filtros,
                'por_pagina' => $filtros['por_pagina'],
                'estadisticas' => [
                    'total' => count($examenes),
                    'activos' => count(array_filter($examenes, fn($e) => ($e['estado'] ?? 'borrador') === 'activo')),
                    'borradores' => count(array_filter($examenes, fn($e) => ($e['estado'] ?? 'borrador') === 'borrador')),
                    'alumnos_realizando' => 0 // TODO: Implementar conteo real
                ]
            ];
            
            // Cargar vista según el rol
            if ($rol == 'admin') {
                require_once __DIR__ . '/../vistas/admin/examenes.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/examenes.php';
            }
            
        } catch (Exception $e) {
            error_log("Error en ExamenesControlador::index(): " . $e->getMessage(), 0, 
                      __DIR__ . "/../../almacenamiento/logs/app/examenes_error.log");
            $this->mostrarError('Error al cargar la lista de exámenes');
        }
    }
    
    /**
     * Crear nuevo examen
     */
    public function crear() {
        // Definir controlador para el navbar
        $GLOBALS['controlador'] = 'examenes';
        
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
            $id_usuario = $_SESSION['id_usuario'];
            
            // Obtener cursos y módulos disponibles
            if ($_SESSION['rol'] == 'admin') {
                $cursos_result = $this->curso->obtenerTodos(100);
                $cursos = isset($cursos_result['cursos']) ? $cursos_result['cursos'] : [];
                
                $modulos = $this->modulo->obtenerParaFormularios();
            } else {
                $cursos = $this->curso->obtenerCursosPorProfesor($id_usuario);
                $modulos = $this->modulo->obtenerParaFormularios($id_usuario);
            }
            
            // Asegurar que tenemos arrays válidos
            $cursos = is_array($cursos) ? $cursos : [];
            $modulos = is_array($modulos) ? $modulos : [];
            
            // Preparar datos para la vista unificada
            $datos = [
                'cursos' => $cursos,
                'modulos' => $modulos,
                'csrf_token' => $_SESSION['csrf_token'],
                'examen' => [] // Inicializar array vacío para modo creación
            ];
            
            if ($_SESSION['rol'] === 'admin') {
                require_once __DIR__ . '/../vistas/admin/examenes/crear.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/examenes/crear.php';
            }
            
        } catch (Exception $e) {
            error_log("Error en mostrarFormularioCreacion(): " . $e->getMessage());
            $this->mostrarError('Error al cargar el formulario');
        }
    }
    
    /**
     * Procesar creación de examen
     */
    private function procesarCreacion() {
        try {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Token CSRF inválido');
            }
            
            // Validar datos requeridos
            $datos = $this->validarDatosExamen($_POST);
            
            // Crear examen
            $id_examen = $this->examen->crear($datos);
            
            if ($id_examen) {
                // Registrar actividad (sin bloquear el flujo si falla)
                try {
                    // Simplificar el contexto para evitar consultas adicionales
                    $contexto = '';
                    if (!empty($datos['id_curso'])) {
                        $contexto .= " (Curso ID: {$datos['id_curso']})";
                    }
                    if (!empty($datos['id_modulo'])) {
                        $contexto .= " (Módulo ID: {$datos['id_modulo']})";
                    }
                    
                    $this->registro_actividad->registrar(
                        $_SESSION['id_usuario'],
                        'crear_examen',
                        "Nuevo examen creado: '{$datos['titulo']}'{$contexto}",
                        'examenes',
                        $id_examen
                    );
                } catch (Exception $e) {
                    error_log("Error al registrar actividad de examen creado: " . $e->getMessage());
                    // No interrumpir el flujo, continuar con el éxito
                }
                
                $_SESSION['mensaje_exito'] = 'Examen creado correctamente';
                header("Location: " . BASE_URL . "/examenes/editar/" . $id_examen);
                exit;
            } else {
                throw new Exception('Error al crear el examen');
            }
            
        } catch (Exception $e) {
            error_log("Error al crear examen: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            $this->mostrarFormularioCreacion();
        }
    }
    
    /**
     * Editar examen existente
     */
    public function editar($id_examen) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        try {
            // Obtener examen
            $examen = $this->examen->obtenerPorId($id_examen);
            if (!$examen) {
                throw new Exception('Examen no encontrado');
            }
            
            // Verificar permisos específicos
            if ($rol == 'profesor' && !$this->verificarPermisoExamen($id_examen)) {
                throw new Exception('No tienes permisos para editar este examen');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->procesarEdicion($id_examen);
            } else {
                $this->mostrarFormularioEdicion($id_examen, $examen);
            }
            
        } catch (Exception $e) {
            error_log("Error al editar examen: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            header("Location: " . BASE_URL . "/examenes");
            exit;
        }
    }
    
    /**
     * Mostrar formulario de edición
     */
    private function mostrarFormularioEdicion($id_examen, $examen) {
        try {
            // Obtener preguntas del examen
            $preguntas = $this->pregunta->obtenerPorExamen($id_examen);
            
            // Obtener respuestas para cada pregunta tipo test
            foreach ($preguntas as &$pregunta) {
                if ($pregunta['tipo'] == 'test') {
                    $pregunta['respuestas'] = $this->respuesta->obtenerPorPregunta($pregunta['id_pregunta']);
                }
            }
            
            // Obtener cursos y módulos
            $id_usuario = $_SESSION['id_usuario'];
            if ($_SESSION['rol'] == 'admin') {
                $cursos_result = $this->curso->obtenerTodos(100);
                $cursos = isset($cursos_result['cursos']) ? $cursos_result['cursos'] : [];
                
                $modulos = $this->modulo->obtenerParaFormularios();
            } else {
                $cursos = $this->curso->obtenerCursosPorProfesor($id_usuario);
                $modulos = $this->modulo->obtenerParaFormularios($id_usuario);
            }
            
            // Asegurar que tenemos arrays válidos
            $cursos = is_array($cursos) ? $cursos : [];
            $modulos = is_array($modulos) ? $modulos : [];
            
            // Preparar datos para la vista unificada
            $datos = [
                'examen' => $examen,
                'cursos' => $cursos,
                'modulos' => $modulos,
                'csrf_token' => $_SESSION['csrf_token']
            ];
            
            if ($_SESSION['rol'] === 'admin') {
                require_once __DIR__ . '/../vistas/admin/examenes/crear.php';
            } else {
                require_once __DIR__ . '/../vistas/profesor/examenes/crear.php';
            }
            
        } catch (Exception $e) {
            error_log("Error en mostrarFormularioEdicion(): " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Procesar edición de examen
     */
    private function procesarEdicion($id_examen) {
        try {
            // Validar token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                throw new Exception('Token CSRF inválido');
            }
            
            // Validar datos
            $datos = $this->validarDatosExamen($_POST);
            $datos['id_examen'] = $id_examen;
            
            // Actualizar examen
            if ($this->examen->actualizar($datos)) {
                // Registrar actividad
                $this->registro_actividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'accion' => 'editar_examen',
                    'descripcion' => "Examen editado: {$datos['titulo']}",
                    'modulo' => 'examenes',
                    'elemento_id' => $id_examen
                ]);
                
                $_SESSION['mensaje_exito'] = 'Examen actualizado correctamente';
            } else {
                throw new Exception('Error al actualizar el examen');
            }
            
            header("Location: " . BASE_URL . "/examenes/editar/" . $id_examen);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al editar examen: " . $e->getMessage());
            $_SESSION['mensaje_error'] = $e->getMessage();
            $this->mostrarFormularioEdicion($id_examen, $this->examen->obtenerPorId($id_examen));
        }
    }
    
    /**
     * Eliminar examen
     */
    public function eliminar($id_examen) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            // Verificar permisos específicos
            if ($rol == 'profesor' && !$this->verificarPermisoExamen($id_examen)) {
                throw new Exception('No tienes permisos para eliminar este examen');
            }
            
            // Obtener datos del examen antes de eliminar
            $examen = $this->examen->obtenerPorId($id_examen);
            
            // Eliminar examen
            if ($this->examen->eliminar($id_examen)) {
                // Registrar actividad
                $this->registro_actividad->registrar(
                    $_SESSION['id_usuario'],
                    'eliminar_examen',
                    "Examen eliminado: {$examen['titulo']} (ID: {$id_examen})",
                    'examenes',
                    $id_examen
                );
                
                $this->responderJson(['success' => 'Examen eliminado correctamente']);
            } else {
                throw new Exception('Error al eliminar el examen');
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar examen: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Duplicar examen
     */
    public function duplicar($id_examen) {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            // Verificar permisos específicos
            if ($rol == 'profesor' && !$this->verificarPermisoExamen($id_examen)) {
                throw new Exception('No tienes permisos para duplicar este examen');
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Procesar duplicación
                $datos = json_decode(file_get_contents('php://input'), true);
                $nuevo_id = $this->examen->duplicar($id_examen, $datos);
                
                if ($nuevo_id) {
                    // Registrar actividad
                    $this->registro_actividad->registrar([
                        'id_usuario' => $_SESSION['id_usuario'],
                        'accion' => 'duplicar_examen',
                        'descripcion' => "Examen duplicado desde ID: $id_examen",
                        'modulo' => 'examenes',
                        'elemento_id' => $nuevo_id
                    ]);
                    
                    $this->responderJson(['success' => 'Examen duplicado correctamente', 'id' => $nuevo_id]);
                } else {
                    throw new Exception('Error al duplicar el examen');
                }
            }
            
        } catch (Exception $e) {
            error_log("Error al duplicar examen: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Validar datos del examen
     */
    private function validarDatosExamen($datos) {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        $errores = [];
        
        // Sanitizar datos de entrada
        $tipos = [
            'titulo' => 'texto',
            'id_modulo' => 'entero',
            'id_curso' => 'entero',
            'tiempo_limite' => 'entero',
            'fecha_inicio' => 'texto',
            'fecha_fin' => 'texto'
        ];
        
        $datosSanitizados = Sanitizador::array($datos, $tipos);
        
        // Validaciones obligatorias
        if (empty($datosSanitizados['titulo'])) {
            $errores[] = 'El título es requerido';
        }
        
        if (empty($datosSanitizados['id_modulo'])) {
            $errores[] = 'El módulo es requerido';
        }
        
        if (empty($datosSanitizados['id_curso'])) {
            $errores[] = 'El curso es requerido';
        }
        
        // Validar fechas
        if (!empty($datosSanitizados['fecha_inicio']) && !empty($datosSanitizados['fecha_fin'])) {
            try {
                // Sanitizar para prevenir inyección y verificar formato
                $fecha_inicio = Sanitizador::fecha($datosSanitizados['fecha_inicio'], 'Y-m-d H:i:s');
                $fecha_fin = Sanitizador::fecha($datosSanitizados['fecha_fin'], 'Y-m-d H:i:s');
                
                if (!$fecha_inicio || !$fecha_fin) {
                    $errores[] = 'Las fechas deben tener un formato válido (YYYY-MM-DD HH:MM:SS)';
                } else {
                    $timestamp_inicio = strtotime($fecha_inicio);
                    $timestamp_fin = strtotime($fecha_fin);
                    
                    if ($timestamp_inicio === false || $timestamp_fin === false) {
                        $errores[] = 'Error al procesar las fechas. Formato incorrecto.';
                    } else if ($timestamp_inicio >= $timestamp_fin) {
                        $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
                    }
                }
            } catch (Exception $e) {
                error_log("Error al validar fechas: " . $e->getMessage());
                $errores[] = 'Error al procesar las fechas';
            }
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        return [
            'titulo' => $datosSanitizados['titulo'],
            'id_modulo' => $datosSanitizados['id_modulo'],
            'id_curso' => $datosSanitizados['id_curso'],
            'tiempo_limite' => $datosSanitizados['tiempo_limite'] ? $datosSanitizados['tiempo_limite'] : null,
            'aleatorio_preg' => isset($datos['aleatorio_preg']) ? 1 : 0,
            'aleatorio_resp' => isset($datos['aleatorio_resp']) ? 1 : 0,
            'fecha_inicio' => $datosSanitizados['fecha_inicio'] ? $datosSanitizados['fecha_inicio'] : null,
            'fecha_fin' => $datosSanitizados['fecha_fin'] ? $datosSanitizados['fecha_fin'] : null,
            'visible' => isset($datos['visible']) ? 1 : 0,
            'activo' => isset($datos['activo']) ? 1 : 0
        ];
    }
    
    /**
     * Verificar si el usuario tiene permisos sobre el examen
     */
    private function verificarPermisoExamen($id_examen) {
        try {
            $examen = $this->examen->obtenerPorId($id_examen);
            if (!$examen) return false;
            
            // Si es admin, siempre tiene permisos
            if ($_SESSION['rol'] == 'admin') return true;
            
            // Si es profesor, verificar que sea el propietario del módulo o curso
            $modulo = $this->modulo->obtenerPorId($examen['id_modulo']);
            $curso = $this->curso->obtenerPorId($examen['id_curso']);
            
            return ($modulo && $modulo['id_profesor'] == $_SESSION['id_usuario']) ||
                   ($curso && $curso['id_profesor'] == $_SESSION['id_usuario']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Realizar examen (vista para alumnos)
     */
    public function realizar($id_examen) {
        // Solo alumnos pueden realizar exámenes
        if ($_SESSION['rol'] != 'alumno') {
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
        
        try {
            // Obtener datos del examen
            $examen = $this->examen->obtenerPorId($id_examen);
            if (!$examen) {
                throw new Exception('Examen no encontrado');
            }
            
            // Verificar que el alumno está inscrito en el curso
            if (!$this->verificarInscripcionAlumno($_SESSION['id_usuario'], $examen['id_curso'])) {
                throw new Exception('No estás inscrito en este curso');
            }
            
            // Verificar disponibilidad del examen
            $ahora = date('Y-m-d H:i:s');
            if ($examen['fecha_inicio'] && $examen['fecha_inicio'] > $ahora) {
                throw new Exception('El examen aún no está disponible');
            }
            
            if ($examen['fecha_fin'] && $examen['fecha_fin'] < $ahora) {
                throw new Exception('El examen ya no está disponible');
            }
            
            // Verificar intentos
            $intentos_realizados = $this->contarIntentosRealizados($_SESSION['id_usuario'], $id_examen);
            if ($examen['intentos_permitidos'] && $intentos_realizados >= $examen['intentos_permitidos']) {
                throw new Exception('Has agotado el número de intentos permitidos');
            }
            
            // Obtener preguntas del examen con respuestas
            $preguntas = $this->pregunta->obtenerPorExamen($id_examen, true);
            
            // Mezclar preguntas si está configurado
            if ($examen['preguntas_aleatorias']) {
                shuffle($preguntas);
            }
            
            // Para cada pregunta tipo test, obtener y mezclar respuestas
            foreach ($preguntas as &$pregunta) {
                if ($pregunta['tipo'] == 'test') {
                    $pregunta['respuestas'] = $this->respuesta->obtenerPorPregunta($pregunta['id_pregunta'], $examen['respuestas_aleatorias']);
                }
            }
            
            $intento_actual = $intentos_realizados + 1;
            
            // Cargar vista
            require_once __DIR__ . '/../vistas/alumno/realizar_examen.php';
            
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = $e->getMessage();
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
    }
    
    /**
     * Enviar examen completado
     */
    public function enviar($id_examen) {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        // Solo alumnos pueden enviar exámenes
        if ($_SESSION['rol'] != 'alumno' || $_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
        
        try {
            $id_alumno = $_SESSION['id_usuario'];
            
            // Sanitizar el ID del examen
            $id_examen = Sanitizador::entero($id_examen);
            if (!$id_examen) {
                throw new Exception('ID de examen no válido');
            }
            
            // Verificar que el examen existe y está disponible
            $examen = $this->examen->obtenerPorId($id_examen);
            if (!$examen) {
                throw new Exception('Examen no encontrado');
            }
            
            // Iniciar transacción
            $this->examen->db->begin_transaction();
            
            // Sanitizar y crear registro del intento
            $tiempo_transcurrido = Sanitizador::entero($_POST['tiempo_transcurrido'] ?? 0);
            $fecha_fin = date('Y-m-d H:i:s');
            $fecha_inicio = Sanitizador::fecha($_POST['inicio_examen'] ?? '', 'Y-m-d H:i:s');
            
            if (!$fecha_inicio) {
                $fecha_inicio = date('Y-m-d H:i:s', strtotime('-' . $tiempo_transcurrido . ' seconds'));
            }
            
            $datos_intento = [
                'id_examen' => $id_examen,
                'id_alumno' => $id_alumno,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'tiempo_transcurrido' => $tiempo_transcurrido,
                'estado' => 'completado'
            ];
            
            $id_intento = $this->crearIntentoExamen($datos_intento);
            if (!$id_intento) {
                throw new Exception('Error al registrar el intento');
            }
            
            // Procesar respuestas
            $puntuacion_total = 0;
            $preguntas_correctas = 0;
            $total_preguntas = 0;
            
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'respuesta_') === 0) {
                    // Sanitizar el ID de pregunta extraído del nombre del campo
                    $id_pregunta = Sanitizador::entero(str_replace('respuesta_', '', $key));
                    if (!$id_pregunta) continue;
                    
                    // Obtener datos de la pregunta
                    $pregunta = $this->pregunta->obtenerPorId($id_pregunta);
                    if (!$pregunta) continue;
                    
                    $total_preguntas++;
                    $puntos_pregunta = 0;
                    
                    if ($pregunta['tipo'] == 'test') {
                        // Evaluar pregunta tipo test
                        $respuestas_seleccionadas = [];
                        
                        // Sanitizar las respuestas seleccionadas
                        if (is_array($value)) {
                            foreach ($value as $resp_id) {
                                $resp_id_sanitizado = Sanitizador::entero($resp_id);
                                if ($resp_id_sanitizado) {
                                    $respuestas_seleccionadas[] = $resp_id_sanitizado;
                                }
                            }
                        } else {
                            $resp_id_sanitizado = Sanitizador::entero($value);
                            if ($resp_id_sanitizado) {
                                $respuestas_seleccionadas[] = $resp_id_sanitizado;
                            }
                        }
                        
                        $respuestas_correctas = $this->respuesta->obtenerCorrectas($id_pregunta);
                        
                        if (count($respuestas_seleccionadas) == count($respuestas_correctas)) {
                            $todas_correctas = true;
                            foreach ($respuestas_seleccionadas as $resp_seleccionada) {
                                if (!$this->respuesta->esCorrecta($resp_seleccionada)) {
                                    $todas_correctas = false;
                                    break;
                                }
                            }
                            
                            if ($todas_correctas) {
                                $puntos_pregunta = 1;
                                $preguntas_correctas++;
                            }
                        }
                        
                        // Guardar respuestas del alumno
                        foreach ($respuestas_seleccionadas as $resp_seleccionada) {
                            $this->guardarRespuestaAlumno($id_intento, $id_pregunta, $resp_seleccionada);
                        }
                    } else {
                        // Para preguntas de desarrollo, sanitizar y guardar la respuesta sin puntuar
                        $texto_respuesta = Sanitizador::texto($value);
                        $this->guardarRespuestaAlumno($id_intento, $id_pregunta, null, $texto_respuesta);
                    }
                    
                    $puntuacion_total += $puntos_pregunta;
                }
            }
            
            // Calcular calificación final
            $calificacion = $total_preguntas > 0 ? ($puntuacion_total / $total_preguntas) * 10 : 0;
            
            // Actualizar intento con la calificación
            $this->actualizarIntentoExamen($id_intento, [
                'puntuacion' => $puntuacion_total,
                'calificacion' => $calificacion,
                'preguntas_correctas' => $preguntas_correctas,
                'total_preguntas' => $total_preguntas
            ]);
            
            // Registrar actividad
            $this->registro_actividad->registrar([
                'id_usuario' => $id_alumno,
                'accion' => 'completar_examen',
                'descripcion' => "Examen completado: {$examen['titulo']} - Calificación: {$calificacion}",
                'modulo' => 'examenes',
                'elemento_id' => $id_examen
            ]);
            
            $this->examen->db->commit();
            
            // Limpiar localStorage del examen
            $_SESSION['examen_completado'] = true;
            
            // Redirigir a resultados
            header("Location: " . BASE_URL . "/examenes/resultado/$id_intento");
            exit;
            
        } catch (Exception $e) {
            $this->examen->db->rollback();
            error_log("Error al enviar examen: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error al procesar el examen: ' . $e->getMessage();
            header("Location: " . BASE_URL . "/examenes/realizar/$id_examen");
            exit;
        }
    }
    
    /**
     * Mostrar resultado de un intento
     */
    public function resultado($id_intento) {
        // Solo alumnos pueden ver sus resultados
        if ($_SESSION['rol'] != 'alumno') {
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
        
        try {
            // Obtener datos del intento
            $intento = $this->obtenerIntentoExamen($id_intento);
            if (!$intento || $intento['id_alumno'] != $_SESSION['id_usuario']) {
                throw new Exception('Intento no encontrado');
            }
            
            // Obtener datos del examen
            $examen = $this->examen->obtenerPorId($intento['id_examen']);
            
            // Obtener respuestas del alumno
            $respuestas_alumno = $this->obtenerRespuestasAlumno($id_intento);
            
            // Cargar vista de resultados
            require_once __DIR__ . '/../vistas/alumno/resultado_examen.php';
            
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = $e->getMessage();
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
    }
    
    /**
     * Mostrar mensaje de error
     */
    private function mostrarError($mensaje) {
        $_SESSION['mensaje_error'] = $mensaje;
        header("Location: " . BASE_URL . "/examenes");
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
     * Verificar si un alumno está inscrito en un curso
     */
    private function verificarInscripcionAlumno($id_alumno, $id_curso) {
        try {
            $query = "SELECT COUNT(*) as count FROM curso_alumno 
                      WHERE id_curso = ? AND id_alumno = ?";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("ii", $id_curso, $id_alumno);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            
            return $fila['count'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar inscripción: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar intentos realizados por un alumno en un examen
     */
    private function contarIntentosRealizados($id_alumno, $id_examen) {
        try {
            $query = "SELECT COUNT(*) as count FROM intentos_examen 
                      WHERE id_alumno = ? AND id_examen = ?";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("ii", $id_alumno, $id_examen);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            
            return (int)$fila['count'];
        } catch (Exception $e) {
            error_log("Error al contar intentos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Crear registro de intento de examen
     */
    private function crearIntentoExamen($datos) {
        try {
            $query = "INSERT INTO intentos_examen (id_examen, id_alumno, fecha_inicio, fecha_fin, 
                                                  tiempo_transcurrido, estado, puntuacion, calificacion,
                                                  preguntas_correctas, total_preguntas) 
                      VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0)";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("iissis", 
                $datos['id_examen'],
                $datos['id_alumno'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['tiempo_transcurrido'],
                $datos['estado']
            );
            
            if ($stmt->execute()) {
                return $this->examen->db->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear intento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar intento de examen con calificación
     */
    private function actualizarIntentoExamen($id_intento, $datos) {
        try {
            $query = "UPDATE intentos_examen SET 
                      puntuacion = ?, calificacion = ?, preguntas_correctas = ?, total_preguntas = ?
                      WHERE id_intento = ?";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("ddiii", 
                $datos['puntuacion'],
                $datos['calificacion'],
                $datos['preguntas_correctas'],
                $datos['total_preguntas'],
                $id_intento
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar intento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guardar respuesta del alumno
     */
    private function guardarRespuestaAlumno($id_intento, $id_pregunta, $id_respuesta = null, $texto_respuesta = null) {
        try {
            // Cargar la clase Sanitizador si aún no está disponible
            if (!class_exists('Sanitizador')) {
                require_once __DIR__ . '/../utilidades/sanitizador.php';
            }
            
            // Sanitizar todas las entradas
            $id_intento = Sanitizador::entero($id_intento);
            $id_pregunta = Sanitizador::entero($id_pregunta);
            $id_respuesta = $id_respuesta !== null ? Sanitizador::entero($id_respuesta) : null;
            $texto_respuesta = $texto_respuesta !== null ? Sanitizador::texto($texto_respuesta) : null;
            
            if (!$id_intento || !$id_pregunta) {
                return false;
            }
            
            $query = "INSERT INTO respuestas_estudiante (id_intento, id_pregunta, id_respuesta, texto_respuesta) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("iiis", $id_intento, $id_pregunta, $id_respuesta, $texto_respuesta);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al guardar respuesta del alumno: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener intento de examen
     */
    private function obtenerIntentoExamen($id_intento) {
        try {
            $query = "SELECT ie.*, e.titulo as titulo_examen, c.nombre_curso, m.titulo as nombre_modulo
                      FROM intentos_examen ie
                      INNER JOIN examenes e ON ie.id_examen = e.id_examen
                      INNER JOIN cursos c ON e.id_curso = c.id_curso
                      INNER JOIN modulos m ON e.id_modulo = m.id_modulo
                      WHERE ie.id_intento = ?";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("i", $id_intento);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if ($resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al obtener intento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener respuestas del alumno en un intento
     */
    private function obtenerRespuestasAlumno($id_intento) {
        try {
            $query = "SELECT re.*, p.enunciado, p.tipo as tipo_pregunta,
                             r.texto as texto_respuesta_correcta, r.correcta
                      FROM respuestas_estudiante re
                      INNER JOIN preguntas p ON re.id_pregunta = p.id_pregunta
                      LEFT JOIN respuestas r ON re.id_respuesta = r.id_respuesta
                      WHERE re.id_intento = ?
                      ORDER BY p.orden ASC, p.id_pregunta ASC";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("i", $id_intento);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener respuestas del alumno: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener lista de exámenes para AJAX
     */
    public function obtenerLista() {
        // Verificar permisos
        if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'profesor') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $filtros = [];
            
            // Solo exámenes del profesor
            if ($_SESSION['rol'] == 'profesor') {
                $filtros['id_profesor'] = $_SESSION['id_usuario'];
            }
            
            $examenes = $this->examen->obtenerTodos(50, 1, $filtros);
            
            $this->responderJson(['success' => true, 'examenes' => $examenes]);
            
        } catch (Exception $e) {
            error_log("Error al obtener lista de exámenes: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Mostrar exámenes para alumnos
     */
    public function misExamenes() {
        // Solo alumnos pueden acceder
        if ($_SESSION['rol'] != 'alumno') {
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
        
        try {
            $id_alumno = $_SESSION['id_usuario'];
            
            // Obtener exámenes disponibles
            $examenes_disponibles = $this->examen->obtenerExamenesDisponibles($id_alumno);
            
            // Obtener historial de intentos
            $historial_intentos = $this->examen->obtenerHistorialIntentos($id_alumno);
            
            // Cargar vista
            require_once __DIR__ . '/../vistas/alumno/examenes.php';
            
        } catch (Exception $e) {
            error_log("Error en misExamenes: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error al cargar los exámenes';
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
    }
    
    /**
     * Mostrar historial completo de exámenes
     */
    public function historial() {
        // Solo alumnos pueden acceder
        if ($_SESSION['rol'] != 'alumno') {
            header("Location: " . BASE_URL . "/inicio");
            exit;
        }
        
        try {
            $id_alumno = $_SESSION['id_usuario'];
            $historial_intentos = $this->examen->obtenerHistorialIntentos($id_alumno, 100);
            
            // Cargar vista de historial completo
            require_once __DIR__ . '/../vistas/alumno/historial_examenes.php';
            
        } catch (Exception $e) {
            error_log("Error en historial: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error al cargar el historial';
            header("Location: " . BASE_URL . "/examenes/mis-examenes");
            exit;
        }
    }
    
    /**
     * Obtener historial de un examen específico (AJAX)
     */
    public function historialExamen($id_examen) {
        // Solo alumnos pueden acceder
        if ($_SESSION['rol'] != 'alumno') {
            $this->responderJson(['error' => 'Sin permisos']);
            return;
        }
        
        try {
            $id_alumno = $_SESSION['id_usuario'];
            
            $query = "SELECT * FROM intentos_examen 
                      WHERE id_examen = ? AND id_alumno = ?
                      ORDER BY fecha_fin DESC";
            $stmt = $this->examen->db->prepare($query);
            $stmt->bind_param("ii", $id_examen, $id_alumno);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $intentos = $resultado->fetch_all(MYSQLI_ASSOC);
            
            $this->responderJson(['success' => true, 'intentos' => $intentos]);
            
        } catch (Exception $e) {
            error_log("Error en historialExamen: " . $e->getMessage());
            $this->responderJson(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Mostrar historial completo de exámenes para alumnos
     */
    public function historial_examenes() {
        // Verificar autenticación y rol
        if (!Sesion::esta_iniciada() || Sesion::obtener_rol() !== 'alumno') {
            header('Location: /autenticacion/iniciar-sesion');
            exit;
        }
        
        try {
            $usuario_id = Sesion::obtener_usuario_id();
            
            // Obtener filtros de la URL
            $filtros = [
                'curso_id' => $_GET['curso_id'] ?? '',
                'estado' => $_GET['estado'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
            ];
            
            // Paginación
            $pagina = max(1, intval($_GET['pagina'] ?? 1));
            $registros_por_pagina = 20;
            $offset = ($pagina - 1) * $registros_por_pagina;
            
            // Verificar si es una exportación
            if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
                $this->exportar_historial_csv($usuario_id, $filtros);
                return;
            }
            
            // Obtener historial con filtros
            $historial = $this->obtener_historial_filtrado($usuario_id, $filtros, $offset, $registros_por_pagina);
            $total_registros = $this->contar_historial_filtrado($usuario_id, $filtros);
            
            // Calcular paginación
            $total_paginas = ceil($total_registros / $registros_por_pagina);
            $paginacion = [
                'pagina' => $pagina,
                'total_paginas' => $total_paginas,
                'total_registros' => $total_registros,
                'registros_por_pagina' => $registros_por_pagina
            ];
            
            // Obtener cursos disponibles para el filtro
            $cursos = $this->obtener_cursos_alumno($usuario_id);
            
            // Obtener estadísticas
            $estadisticas = $this->obtener_estadisticas_historial($usuario_id, $filtros);
            
            // Registrar actividad
            $this->registro_actividad->registrar(
                $usuario_id,
                'consulta',
                'examenes',
                null,
                'Consultó historial de exámenes'
            );
            
            // Incluir vista
            include __DIR__ . '/../vistas/alumno/historial_examenes.php';
            
        } catch (Exception $e) {
            error_log('Error en historial_examenes: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el historial de exámenes.';
            header('Location: ' . BASE_URL . '/alumno/examenes');
            exit;
        }
    }
    
    /**
     * Obtener historial filtrado de exámenes
     */
    private function obtener_historial_filtrado($usuario_id, $filtros, $offset, $limite) {
        try {
            $db = $GLOBALS['db']; // Usar conexión MySQLi global
            
            $sql = "
                SELECT 
                    ie.id,
                    ie.examen_id,
                    ie.usuario_id,
                    ie.fecha_inicio,
                    ie.fecha_finalizacion,
                    ie.tiempo_utilizado,
                    ie.calificacion,
                    ie.estado,
                    ie.numero_intento,
                    e.titulo as titulo_examen,
                    e.descripcion as descripcion_examen,
                    e.tiempo_limite,
                    e.intentos_maximos as max_intentos,
                    c.nombre as nombre_curso,
                    c.id as curso_id,
                    m.nombre as nombre_modulo
                FROM intentos_examenes ie
                INNER JOIN examenes e ON ie.examen_id = e.id
                INNER JOIN modulos m ON e.modulo_id = m.id
                INNER JOIN cursos c ON m.curso_id = c.id
                WHERE ie.usuario_id = ?
            ";
            
            $tipos = 'i';
            $params = [$usuario_id];
            
            // Aplicar filtros
            if (!empty($filtros['curso_id'])) {
                $sql .= " AND c.id = ?";
                $tipos .= 'i';
                $params[] = $filtros['curso_id'];
            }
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND ie.estado = ?";
                $tipos .= 's';
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(ie.fecha_inicio) >= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(ie.fecha_inicio) <= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_hasta'];
            }
            
            $sql .= " ORDER BY ie.fecha_inicio DESC, ie.id DESC";
            $sql .= " LIMIT ? OFFSET ?";
            $tipos .= 'ii';
            $params[] = $limite;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $db->error);
            }
            
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log('Error al obtener historial filtrado: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar registros del historial filtrado
     */
    private function contar_historial_filtrado($usuario_id, $filtros) {
        try {
            $db = $GLOBALS['db']; // Usar conexión MySQLi global
            
            $sql = "
                SELECT COUNT(*) as total
                FROM intentos_examenes ie
                INNER JOIN examenes e ON ie.examen_id = e.id
                INNER JOIN modulos m ON e.modulo_id = m.id
                INNER JOIN cursos c ON m.curso_id = c.id
                WHERE ie.usuario_id = ?
            ";
            
            $tipos = 'i';
            $params = [$usuario_id];
            
            // Aplicar los mismos filtros
            if (!empty($filtros['curso_id'])) {
                $sql .= " AND c.id = ?";
                $tipos .= 'i';
                $params[] = $filtros['curso_id'];
            }
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND ie.estado = ?";
                $tipos .= 's';
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(ie.fecha_inicio) >= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(ie.fecha_inicio) <= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_hasta'];
            }
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $db->error);
            }
            
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            
            return intval($fila['total']);
            
        } catch (Exception $e) {
            error_log('Error al contar historial filtrado: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener cursos del alumno para filtros
     */
    private function obtener_cursos_alumno($usuario_id) {
        try {
            $db = $GLOBALS['db']; // Usar conexión MySQLi global
            
            $sql = "
                SELECT DISTINCT c.id, c.nombre
                FROM cursos c
                INNER JOIN inscripciones i ON c.id = i.curso_id
                WHERE i.usuario_id = ? 
                AND i.estado = 'activa'
                ORDER BY c.nombre
            ";
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $db->error);
            }
            
            $stmt->bind_param('i', $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            return $resultado->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log('Error al obtener cursos del alumno: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas del historial
     */
    private function obtener_estadisticas_historial($usuario_id, $filtros) {
        try {
            $db = $GLOBALS['db']; // Usar conexión MySQLi global
            
            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN ie.estado = 'completado' THEN 1 ELSE 0 END) as completados,
                    SUM(CASE WHEN ie.estado = 'en_progreso' THEN 1 ELSE 0 END) as en_progreso,
                    AVG(CASE WHEN ie.estado = 'completado' AND ie.calificacion IS NOT NULL 
                             THEN ie.calificacion ELSE NULL END) as promedio
                FROM intentos_examenes ie
                INNER JOIN examenes e ON ie.examen_id = e.id
                INNER JOIN modulos m ON e.modulo_id = m.id
                INNER JOIN cursos c ON m.curso_id = c.id
                WHERE ie.usuario_id = ?
            ";
            
            $tipos = 'i';
            $params = [$usuario_id];
            
            // Aplicar los mismos filtros
            if (!empty($filtros['curso_id'])) {
                $sql .= " AND c.id = ?";
                $tipos .= 'i';
                $params[] = $filtros['curso_id'];
            }
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND ie.estado = ?";
                $tipos .= 's';
                $params[] = $filtros['estado'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(ie.fecha_inicio) >= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(ie.fecha_inicio) <= ?";
                $tipos .= 's';
                $params[] = $filtros['fecha_hasta'];
            }
            
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error al preparar consulta: ' . $db->error);
            }
            
            $stmt->bind_param($tipos, ...$params);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            
            return [
                'total' => intval($fila['total']),
                'completados' => intval($fila['completados']),
                'en_progreso' => intval($fila['en_progreso']),
                'promedio' => floatval($fila['promedio'] ?? 0)
            ];
            
        } catch (Exception $e) {
            error_log('Error al obtener estadísticas del historial: ' . $e->getMessage());
            return [
                'total' => 0,
                'completados' => 0,
                'en_progreso' => 0,
                'promedio' => 0
            ];
        }
    }
    
    /**
     * Exportar historial a CSV
     */
    private function exportar_historial_csv($usuario_id, $filtros) {
        try {
            // Obtener todos los datos sin paginación
            $historial = $this->obtener_historial_filtrado($usuario_id, $filtros, 0, 10000);
            
            // Configurar headers para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="historial_examenes_' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Crear el archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            $encabezados = [
                'Examen',
                'Curso',
                'Fecha Inicio',
                'Fecha Finalización',
                'Estado',
                'Calificación (%)',
                'Tiempo Utilizado (min)',
                'Número de Intento',
                'Intentos Máximos'
            ];
            fputcsv($output, $encabezados, ';');
            
            // Datos
            foreach ($historial as $intento) {
                $fila = [
                    $intento['titulo_examen'],
                    $intento['nombre_curso'],
                    $intento['fecha_inicio'] ? date('d/m/Y H:i', strtotime($intento['fecha_inicio'])) : '',
                    $intento['fecha_finalizacion'] ? date('d/m/Y H:i', strtotime($intento['fecha_finalizacion'])) : '',
                    ucfirst($intento['estado']),
                    $intento['calificacion'] ? number_format($intento['calificacion'], 2, ',', '.') : '',
                    $intento['tiempo_utilizado'] ? round($intento['tiempo_utilizado'] / 60, 2) : '',
                    $intento['numero_intento'],
                    $intento['max_intentos'] == 0 ? 'Ilimitados' : $intento['max_intentos']
                ];
                fputcsv($output, $fila, ';');
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log('Error al exportar historial CSV: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar el historial.';
            header('Location: ' . BASE_URL . '/examenes/historial-examenes');
            exit;
        }
    }
    
    /**
     * Obtener y sanitizar filtros de la URL
     */
    private function obtenerFiltrosSanitizados() {
        // Cargar la clase Sanitizador si aún no está disponible
        if (!class_exists('Sanitizador')) {
            require_once __DIR__ . '/../utilidades/sanitizador.php';
        }
        
        try {
            return [
                'curso_id' => Sanitizador::entero($_GET['curso_id'] ?? 0),
                'modulo_id' => Sanitizador::entero($_GET['modulo_id'] ?? 0),
                'estado' => Sanitizador::texto($_GET['estado'] ?? ''),
                'busqueda' => Sanitizador::texto($_GET['busqueda'] ?? ''),
                'fecha_desde' => Sanitizador::fecha($_GET['fecha_desde'] ?? ''),
                'fecha_hasta' => Sanitizador::fecha($_GET['fecha_hasta'] ?? ''),
                'pagina' => max(1, Sanitizador::entero($_GET['pagina'] ?? 1)),
                'por_pagina' => min(100, max(5, Sanitizador::entero($_GET['por_pagina'] ?? 10)))
            ];
        } catch (Exception $e) {
            error_log("Error al sanitizar filtros: " . $e->getMessage());
            // Devolver valores por defecto si hay algún error
            return [
                'curso_id' => 0,
                'modulo_id' => 0,
                'estado' => '',
                'busqueda' => '',
                'fecha_desde' => null,
                'fecha_hasta' => null,
                'pagina' => 1,
                'por_pagina' => 10
            ];
        }
    }
}
