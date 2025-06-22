<?php
/**
 * Controlador para la gestión de cursos
 * 
 * Este archivo contiene la clase CursosControlador que gestiona todas las
 * operaciones relacionadas con cursos en el sistema.
 * 
 * @package AUTOEXAM2
 * @author Copilot, basado en documentación de Carlos Ferrero
 * @version 1.0
 * @since 16/06/2025
 */

class CursosControlador {
    private $curso;
    private $usuario;
    private $sesion;
    private $registroActividad;
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        // Cargar modelos
        require_once __DIR__ . '/../modelos/curso_modelo.php';
        require_once __DIR__ . '/../modelos/usuario_modelo.php';
        require_once __DIR__ . '/../modelos/registro_actividad_modelo.php';
        require_once __DIR__ . '/../utilidades/sesion.php';
        
        $this->curso = new Curso();
        $this->usuario = new Usuario();
        $this->sesion = new Sesion();
        $this->registroActividad = new RegistroActividad();
    }
    
    /**
     * Acción predeterminada - Listar cursos
     */
    public function index() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Parámetros de paginación y filtrado
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
        
        // Obtener filtros y parámetros de ordenación
        $filtros = $this->obtenerFiltros();
        
        // Para profesores, mostrar solo sus cursos (incluyendo inactivos)
        if ($rol == 'profesor') {
            $filtros['id_profesor'] = $_SESSION['id_usuario'];
            // Permitir ver todos los cursos, tanto activos como inactivos
        }
        
        // Obtener datos
        $resultado = $this->curso->obtenerTodos($limite, $pagina, $filtros);
        
        // Obtener profesores para el filtro (solo para administradores)
        $profesores = [];
        if ($rol == 'admin') {
            $profesores = $this->curso->obtenerProfesores();
        }
        
        // Cargar la vista
        if ($rol == 'admin') {
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once __DIR__ . '/../vistas/admin/cursos.php';
            
            echo '</div></div></div>';
        } else {
            // Obtener información del usuario para la vista
            $datosUsuario = [];
            if (isset($_SESSION['id_usuario'])) {
                $datosUsuario = $this->usuario->buscarPorId($_SESSION['id_usuario']);
            }
            
            // Pasar datos a la vista
            $datos = [
                'usuario' => $datosUsuario,
                'titulo' => 'Mis Cursos'
            ];
            
            require_once APP_PATH . '/vistas/parciales/head_profesor.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once __DIR__ . '/../vistas/profesor/cursos.php';
            
            echo '</div></div></div>';
        }
        
        require_once APP_PATH . '/vistas/parciales/footer_profesor.php'; // Alineado con rol profesor
        require_once APP_PATH . '/vistas/parciales/scripts_profesor.php'; // Alineado con rol profesor
    }
    
    /**
     * Obtiene datos de ejemplo para desarrollo
     */
    private function obtenerDatosEjemplo() {
        return [
            'cursos' => [
                [
                    'id_curso' => 1,
                    'nombre_curso' => 'Matemáticas 3º ESO',
                    'descripcion' => 'Curso de matemáticas para tercer año de ESO',
                    'activo' => 1,
                    'id_profesor' => 1,
                    'nombre_profesor' => 'Juan',
                    'apellidos_profesor' => 'García Martínez',
                    'total_alumnos' => 25
                ],
                [
                    'id_curso' => 2,
                    'nombre_curso' => 'Lengua y Literatura 4º ESO',
                    'descripcion' => 'Curso de lengua castellana y literatura',
                    'activo' => 1,
                    'id_profesor' => 2,
                    'nombre_profesor' => 'María',
                    'apellidos_profesor' => 'López González',
                    'total_alumnos' => 20
                ],
                [
                    'id_curso' => 3,
                    'nombre_curso' => 'Historia Universal',
                    'descripcion' => 'Curso de historia universal para bachillerato',
                    'activo' => 0,
                    'id_profesor' => 1,
                    'nombre_profesor' => 'Juan',
                    'apellidos_profesor' => 'García Martínez',
                    'total_alumnos' => 15
                ]
            ],
            'total' => 3,
            'paginas' => 1
        ];
    }
    
    /**
     * Obtiene profesores de ejemplo para desarrollo
     */
    private function obtenerProfesoresEjemplo() {
        return [
            [
                'id_usuario' => 1,
                'nombre' => 'Juan',
                'apellidos' => 'García Martínez'
            ],
            [
                'id_usuario' => 2,
                'nombre' => 'María',
                'apellidos' => 'López González'
            ]
        ];
    }
    
    /**
     * Acción para ver el formulario de creación de curso
     */
    public function nuevo() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        $profesores = [];
        // Si es administrador, mostrar lista de profesores para asignar
        if ($rol == 'admin') {
            $profesores = $this->curso->obtenerProfesores();
        }
        
        // Cargar la vista
        require_once __DIR__ . '/../vistas/comunes/cabecera.php';
        
        if ($rol == 'admin') {
            require_once __DIR__ . '/../vistas/admin/formulario_curso.php';
        } else {
            require_once __DIR__ . '/../vistas/profesor/formulario_curso.php';
        }
        
        require_once APP_PATH . '/vistas/parciales/footer_admin.php'; require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Acción para guardar un nuevo curso
     */
    public function crear() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['mensaje'] = "Error de seguridad: token CSRF inválido.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos/nuevo");
            exit;
        }
        
        // Validar datos del formulario
        $errores = $this->validarFormulario($_POST);
        
        if (count($errores) > 0) {
            $_SESSION['errores'] = $errores;
            $_SESSION['datos_form'] = $_POST;
            header("Location: " . BASE_URL . "/cursos/nuevo");
            exit;
        }
        
        // Preparar datos para crear curso
        $datos = [
            'nombre_curso' => $_POST['nombre_curso'],
            'descripcion' => $_POST['descripcion'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Si es profesor, asignar su ID
        if ($rol == 'profesor') {
            $datos['id_profesor'] = $_SESSION['id_usuario'];
        } else {
            // Si es admin, tomar el profesor seleccionado
            $datos['id_profesor'] = $_POST['id_profesor'];
        }
        
        // Crear el curso
        $resultado = $this->curso->crear($datos);
        
        if ($resultado) {
            // Registrar la actividad (sin bloquear el flujo si falla)
            try {
                $profesorNombre = 'Sistema';
                if ($rol == 'profesor') {
                    $profesorNombre = $_SESSION['nombre'] . ' ' . $_SESSION['apellidos'];
                } else {
                    // Simplificar para evitar consultas adicionales que puedan fallar
                    $profesorNombre = 'Profesor asignado (ID: ' . $datos['id_profesor'] . ')';
                }
                
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'crear_curso',
                    "Nuevo curso creado: '{$datos['nombre_curso']}' - Creado por: {$profesorNombre}",
                    'cursos',
                    $resultado
                );
            } catch (Exception $e) {
                error_log("Error al registrar actividad de curso creado: " . $e->getMessage());
                // No interrumpir el flujo, continuar con el éxito
            }
            
            $_SESSION['mensaje'] = "Curso creado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al crear el curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            $_SESSION['datos_form'] = $_POST;
            header("Location: " . BASE_URL . "/cursos/nuevo");
            exit;
        }
    }
    
    /**
     * Acción para ver el formulario de edición de curso
     */
    public function editar() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Validar que se haya proporcionado ID
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $id_curso = (int)$_GET['id'];
        $curso = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Si es profesor, verificar que le pertenezca el curso
        if ($rol == 'profesor' && $curso['id_profesor'] != $_SESSION['id_usuario']) {
            $_SESSION['mensaje'] = "No tienes permiso para editar este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $profesores = [];
        // Si es administrador, mostrar lista de profesores para asignar
        if ($rol == 'admin') {
            $profesores = $this->curso->obtenerProfesores();
        }
        
        // Cargar la vista
        require_once __DIR__ . '/../vistas/comunes/cabecera.php';
        
        if ($rol == 'admin') {
            require_once __DIR__ . '/../vistas/admin/formulario_curso.php';
        } else {
            require_once __DIR__ . '/../vistas/profesor/formulario_curso.php';
        }
        
        require_once APP_PATH . '/vistas/parciales/footer_admin.php'; require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Acción para actualizar un curso existente
     */
    public function actualizar() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['mensaje'] = "Error de seguridad: token CSRF inválido.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Validar que se haya proporcionado ID
        if (!isset($_POST['id_curso']) || empty($_POST['id_curso'])) {
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $id_curso = (int)$_POST['id_curso'];
        $curso_original = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso_original) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Si es profesor, verificar que le pertenezca el curso
        if ($rol == 'profesor' && $curso_original['id_profesor'] != $_SESSION['id_usuario']) {
            $_SESSION['mensaje'] = "No tienes permiso para editar este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Validar datos del formulario
        $errores = $this->validarFormulario($_POST);
        
        if (count($errores) > 0) {
            $_SESSION['errores'] = $errores;
            $_SESSION['datos_form'] = $_POST;
            header("Location: " . BASE_URL . "/cursos/editar?id=" . $id_curso);
            exit;
        }
        
        // Preparar datos para actualizar curso
        $datos = [
            'nombre_curso' => $_POST['nombre_curso'],
            'descripcion' => $_POST['descripcion'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Si es profesor, mantener su ID
        if ($rol == 'profesor') {
            $datos['id_profesor'] = $_SESSION['id_usuario'];
        } else {
            // Si es admin, tomar el profesor seleccionado
            $datos['id_profesor'] = $_POST['id_profesor'];
        }
        
        // Actualizar el curso
        $resultado = $this->curso->actualizar($id_curso, $datos);
        
        if ($resultado) {
            $_SESSION['mensaje'] = "Curso actualizado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "/cursos");
        } else {
            $_SESSION['mensaje'] = "Error al actualizar el curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            $_SESSION['datos_form'] = $_POST;
            header("Location: " . BASE_URL . "/cursos/editar?id=" . $id_curso);
        }
        exit;
    }
    
    /**
     * Acción para desactivar un curso
     */
    public function desactivar() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['mensaje'] = "Error de seguridad: token CSRF inválido.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Validar que se haya proporcionado ID
        if (!isset($_POST['id_curso']) || empty($_POST['id_curso'])) {
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $id_curso = (int)$_POST['id_curso'];
        $curso = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Si es profesor, verificar que le pertenezca el curso
        if ($rol == 'profesor' && $curso['id_profesor'] != $_SESSION['id_usuario']) {
            $_SESSION['mensaje'] = "No tienes permiso para desactivar este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Desactivar el curso
        $resultado = $this->curso->desactivar($id_curso);
        
        if ($resultado) {
            $_SESSION['mensaje'] = "Curso desactivado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al desactivar el curso.";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: " . BASE_URL . "/cursos");
        exit;
    }
    
    /**
     * Acción para activar un curso
     */
    public function activar() {
        // Verificar CSRF token
        if (!isset($_GET['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Error de seguridad. Inténtelo de nuevo.";
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
        
        // Verificar que se proporcionó un ID
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = "ID de curso no válido";
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
        
        $id_curso = (int)$_GET['id'];
        
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            $_SESSION['error'] = "No tienes permisos para esta acción";
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
        
        // Si es profesor, verificar que el curso le pertenezca
        if ($rol == 'profesor') {
            $curso = $this->curso->obtenerPorId($id_curso);
            if (!$curso || $curso['id_profesor'] != $_SESSION['id_usuario']) {
                $_SESSION['error'] = "No tienes permiso para activar este curso.";
                $_SESSION['tipo_mensaje'] = "danger";
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }
        }
        
        // Activar el curso
        $resultado = $this->curso->activar($id_curso);
        if ($resultado) {
            $_SESSION['mensaje'] = "Curso activado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al activar el curso.";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        // Redirigir
        header('Location: ' . BASE_URL . '/cursos');
        exit;
    }
    
    /**
     * Acción para ver el formulario de asignación de alumnos
     */
    public function asignarAlumnos() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Validar que se haya proporcionado ID
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $id_curso = (int)$_GET['id'];
        $curso = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Si es profesor, verificar que le pertenezca el curso
        if ($rol == 'profesor' && $curso['id_profesor'] != $_SESSION['id_usuario']) {
            $_SESSION['mensaje'] = "No tienes permiso para gestionar este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Obtener alumnos asignados y no asignados
        $alumnos_asignados = $this->curso->obtenerAlumnos($id_curso);
        $alumnos_disponibles = $this->curso->obtenerAlumnosNoAsignados($id_curso);
        
        // Cargar la vista
        require_once __DIR__ . '/../vistas/comunes/cabecera.php';
        
        if ($rol == 'admin') {
            require_once __DIR__ . '/../vistas/admin/asignar_alumnos.php';
        } else {
            require_once __DIR__ . '/../vistas/profesor/asignar_alumnos.php';
        }
        
        require_once APP_PATH . '/vistas/parciales/footer_admin.php'; require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Acción para procesar la asignación de alumnos a un curso
     */
    public function procesarAsignacion() {
        // Verificar permisos (solo admin y profesor)
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['mensaje'] = "Error de seguridad: token CSRF inválido.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Validar que se haya proporcionado ID
        if (!isset($_POST['id_curso']) || empty($_POST['id_curso'])) {
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        $id_curso = (int)$_POST['id_curso'];
        $curso = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Si es profesor, verificar que le pertenezca el curso
        if ($rol == 'profesor' && $curso['id_profesor'] != $_SESSION['id_usuario']) {
            $_SESSION['mensaje'] = "No tienes permiso para gestionar este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
        
        // Procesar asignaciones
        $exito = true;
        $errores = [];
        
        // Asignar alumnos seleccionados
        if (isset($_POST['alumnos']) && is_array($_POST['alumnos'])) {
            foreach ($_POST['alumnos'] as $id_alumno) {
                $resultado = $this->curso->asignarAlumno($id_curso, (int)$id_alumno);
                if (!$resultado) {
                    $exito = false;
                    $errores[] = "Error al asignar alumno con ID: {$id_alumno}";
                }
            }
        }
        
        // Desasignar alumnos seleccionados
        if (isset($_POST['desasignar']) && is_array($_POST['desasignar'])) {
            foreach ($_POST['desasignar'] as $id_alumno) {
                $resultado = $this->curso->desasignarAlumno($id_curso, (int)$id_alumno);
                if (!$resultado) {
                    $exito = false;
                    $errores[] = "Error al desasignar alumno con ID: {$id_alumno}";
                }
            }
        }
        
        if ($exito) {
            $_SESSION['mensaje'] = "Asignación de alumnos actualizada correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Se produjeron algunos errores al actualizar las asignaciones.";
            $_SESSION['tipo_mensaje'] = "warning";
            $_SESSION['errores'] = $errores;
        }
        
        header("Location: " . BASE_URL . "/cursos/asignarAlumnos?id=" . $id_curso);
        exit;
    }
    
    /**
     * Acción para mostrar cursos de un alumno
     */
    public function misCursos() {
        // Verificar que el usuario sea alumno
        if ($_SESSION['rol'] != 'alumno') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Obtener cursos del alumno
        $id_alumno = $_SESSION['id_usuario'];
        $cursos = $this->curso->obtenerCursosPorAlumno($id_alumno);
        
        // Cargar la vista
        require_once __DIR__ . '/../vistas/comunes/cabecera.php';
        require_once __DIR__ . '/../vistas/alumno/mis_cursos.php';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php'; require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Acción para mostrar detalles de un curso específico
     */
    public function ver() {
        // Validar que se haya proporcionado ID
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: " . BASE_URL);
            exit;
        }
        
        $id_curso = (int)$_GET['id'];
        $curso = $this->curso->obtenerPorId($id_curso);
        
        // Verificar que el curso exista
        if (!$curso) {
            $_SESSION['mensaje'] = "El curso solicitado no existe.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL);
            exit;
        }
        
        $rol = $_SESSION['rol'];
        $id_usuario = $_SESSION['id_usuario'];
        $puede_ver = false;
        
        // Verificar permisos según rol
        if ($rol == 'admin') {
            $puede_ver = true;
        } else if ($rol == 'profesor' && $curso['id_profesor'] == $id_usuario) {
            $puede_ver = true;
        } else if ($rol == 'alumno') {
            // Verificar si el alumno está en este curso
            $alumnos = $this->curso->obtenerAlumnos($id_curso);
            foreach ($alumnos as $alumno) {
                if ($alumno['id_usuario'] == $id_usuario) {
                    $puede_ver = true;
                    break;
                }
            }
        }
        
        if (!$puede_ver) {
            $_SESSION['mensaje'] = "No tienes permiso para ver este curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Cargar datos adicionales
        $alumnos = $this->curso->obtenerAlumnos($id_curso);
        
        // Cargar la vista según el rol
        require_once __DIR__ . '/../vistas/comunes/cabecera.php';
        
        if ($rol == 'admin') {
            require_once __DIR__ . '/../vistas/admin/ver_curso.php';
        } elseif ($rol == 'profesor') {
            require_once __DIR__ . '/../vistas/profesor/ver_curso.php';
        } else {
            require_once __DIR__ . '/../vistas/alumno/ver_curso.php';
        }
        
        require_once APP_PATH . '/vistas/parciales/footer_admin.php'; require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    }
    
    /**
     * Ver alumnos de un curso (para profesor)
     * 
     * @param int $id_curso ID del curso
     * @return void
     */
    public function alumnos() {
        // Verificar que el usuario sea profesor
        if ($_SESSION['rol'] !== 'profesor') {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        // Verificar que se haya proporcionado ID por GET
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            $_SESSION['error'] = 'ID de curso no especificado';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
        
        $id_curso = (int)$_GET['id'];
        
        // Verificar que el curso exista
        if (!$id_curso || !is_numeric($id_curso)) {
            $_SESSION['error'] = 'ID de curso no especificado o inválido';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
        
        try {
            // Verificar que el curso pertenece al profesor actual
            $curso = $this->curso->obtenerPorId($id_curso);
            
            if (!$curso) {
                $_SESSION['error'] = 'Curso no encontrado';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }
            
            if ($curso['id_profesor'] != $_SESSION['id_usuario']) {
                $_SESSION['error'] = 'No tiene permisos para ver este curso';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }
            
            // Obtener los alumnos asignados al curso
            $alumnos = $this->curso->obtenerAlumnosPorCurso($id_curso);
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Alumnos del Curso',
                'curso' => $curso,
                'alumnos' => $alumnos,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Mostrar vista
            require_once APP_PATH . '/vistas/parciales/head_profesor.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/profesor/usuarios/listar.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            
        } catch (Exception $e) {
            error_log('Error al obtener alumnos del curso: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar los datos de los alumnos';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
    }
    
    /**
     * Eliminar un curso individual
     * 
     * @param int|null $id ID del curso a eliminar
     * @return void
     */
    public function eliminar($id = null) {
        // Verificar permisos
        if ($_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = 'Solo administradores pueden eliminar cursos';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }

        try {
            // Validar ID
            if (!$id || !is_numeric($id)) {
                $_SESSION['error'] = 'ID de curso inválido';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }

            // Obtener datos del curso antes de eliminarlo
            $curso = $this->curso->obtenerPorId($id);
            if (!$curso) {
                $_SESSION['error'] = 'Curso no encontrado';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }

            // Intentar eliminar el curso
            if ($this->curso->eliminar($id)) {
                // Registrar la actividad
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'eliminar_curso',
                    "Curso eliminado: {$curso['nombre_curso']} (ID: {$id})",
                    'cursos',
                    $id
                );
                
                $_SESSION['exito'] = 'Curso eliminado correctamente';
            } else {
                $_SESSION['error'] = 'No se pudo eliminar el curso';
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar curso: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/cursos');
        exit;
    }

    /**
     * Valida los datos del formulario de curso
     * 
     * @param array $datos Datos a validar
     * @return array Errores encontrados
     */
    private function validarFormulario($datos) {
        $errores = [];
        $rol = $_SESSION['rol'];
        
        // Validar nombre
        if (!isset($datos['nombre_curso']) || empty($datos['nombre_curso'])) {
            $errores['nombre_curso'] = "El nombre del curso es obligatorio.";
        } elseif (strlen($datos['nombre_curso']) < 3 || strlen($datos['nombre_curso']) > 100) {
            $errores['nombre_curso'] = "El nombre debe tener entre 3 y 100 caracteres.";
        }
        
        // Validar profesor (solo para administradores)
        if ($rol == 'admin') {
            if (!isset($datos['id_profesor']) || empty($datos['id_profesor'])) {
                $errores['id_profesor'] = "Debe seleccionar un profesor.";
            } else {
                // Verificar que el profesor exista
                $profesor = $this->usuario->buscarPorId($datos['id_profesor']);
                if (!$profesor || $profesor['rol'] != 'profesor') {
                    $errores['id_profesor'] = "El profesor seleccionado no existe o no es válido.";
                }
            }
        }
        
        return $errores;
    }
    
    /**
     * Obtiene los filtros aplicados desde GET para la búsqueda
     * 
     * @return array Filtros aplicados
     */
    private function obtenerFiltros() {
        $filtros = [];
        
        // Filtro por nombre del curso
        if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
            $nombre = trim($_GET['nombre']);
            if (strlen($nombre) >= 3) {
                $filtros['nombre_curso'] = $nombre;
            }
        }
        
        // Filtro por profesor
        if (isset($_GET['profesor']) && !empty($_GET['profesor'])) {
            $filtros['id_profesor'] = (int)$_GET['profesor'];
        }
        
        // Filtro por estado activo/inactivo
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filtros['activo'] = (int)$_GET['activo'];
        }
        
        // Ordenación
        if (isset($_GET['ordenar_por']) && !empty($_GET['ordenar_por'])) {
            // Validar campos de ordenación permitidos
            $camposPermitidos = ['id_curso', 'nombre_curso', 'id_profesor', 'activo', 'fecha_creacion'];
            
            if (in_array($_GET['ordenar_por'], $camposPermitidos)) {
                $filtros['ordenar_por'] = $_GET['ordenar_por'];
                
                // Dirección de ordenación (ASC/DESC)
                $filtros['orden'] = isset($_GET['orden']) && strtoupper($_GET['orden']) === 'DESC' ? 'DESC' : 'ASC';
            }
        }
        
        return $filtros;
    }
    
    /**
     * Procesa acciones masivas sobre los cursos seleccionados
     * 
     * @return void
     */
    public function accionMasiva() {
        // Verificar método de solicitud
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método de solicitud incorrecto";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }

        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol !== 'admin' && $rol !== 'profesor') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Error de verificación de seguridad";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }

        // Verificar que se hayan seleccionado cursos
        if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
            $_SESSION['error'] = "No se seleccionaron cursos";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }

        // Verificar la acción a realizar
        if (!isset($_POST['accion']) || empty($_POST['accion'])) {
            $_SESSION['error'] = "No se especificó una acción";
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }

        $accion = $_POST['accion'];
        $ids = array_map('intval', $_POST['ids']);
        $totalCursos = count($ids);
        
        // Si es profesor, validar que todos los cursos le pertenezcan
        if ($rol === 'profesor') {
            try {
                foreach ($ids as $id) {
                    $curso = $this->curso->obtenerPorId($id);
                    if (!$curso || $curso['id_profesor'] != $_SESSION['id_usuario']) {
                        $_SESSION['error'] = "No tienes permisos para modificar algunos de los cursos seleccionados";
                        header("Location: " . BASE_URL . "/cursos");
                        exit;
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al verificar permisos: " . $e->getMessage();
                header("Location: " . BASE_URL . "/cursos");
                exit;
            }
        }
        
        try {
            switch ($accion) {
                case 'desactivar':
                    $this->desactivarMasivo($ids);
                    $_SESSION['exito'] = "Se han desactivado $totalCursos cursos correctamente";
                    break;
                    
                case 'eliminar':
                    if ($rol !== 'admin') {
                        throw new Exception("Solo administradores pueden eliminar cursos");
                    }
                    $this->eliminarMasivo($ids);
                    $_SESSION['exito'] = "Se han eliminado $totalCursos cursos correctamente";
                    break;
                    
                case 'exportar':
                    $this->exportarSeleccionados($ids);
                    // La redirección la maneja el método exportarSeleccionados
                    return;
                    
                default:
                    throw new Exception("Acción no reconocida");
            }
            
            header("Location: " . BASE_URL . "/cursos");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al procesar la acción: " . $e->getMessage();
            header("Location: " . BASE_URL . "/cursos");
            exit;
        }
    }
    
    /**
     * Desactiva múltiples cursos de forma masiva
     * 
     * @param array $ids IDs de los cursos a desactivar
     * @return bool Resultado de la operación
     * @throws Exception Si hay error en la desactivación
     */
    private function desactivarMasivo($ids) {
        if (!is_array($ids) || empty($ids)) {
            throw new Exception("No se proporcionaron IDs de cursos para desactivar");
        }
        
        try {
            foreach ($ids as $id) {
                $this->curso->actualizarEstado($id, 0);
            }
            return true;
        } catch (Exception $e) {
            error_log("Error al desactivar cursos masivamente: " . $e->getMessage());
            throw new Exception("Error al desactivar los cursos");
        }
    }
    
    /**
     * Elimina múltiples cursos de forma masiva
     * 
     * @param array $ids IDs de los cursos a eliminar
     * @return bool Resultado de la operación
     * @throws Exception Si hay error en la eliminación
     */
    private function eliminarMasivo($ids) {
        if (!is_array($ids) || empty($ids)) {
            throw new Exception("No se proporcionaron IDs de cursos para eliminar");
        }
        
        try {
            foreach ($ids as $id) {
                // Obtener datos del curso antes de eliminarlo
                $curso = $this->curso->obtenerPorId($id);
                if ($curso) {
                    // Eliminar el curso
                    $this->curso->eliminar($id);
                    
                    // Registrar la actividad
                    $this->registroActividad->registrar(
                        $_SESSION['id_usuario'],
                        'eliminar_curso',
                        "Curso eliminado: {$curso['nombre_curso']} (ID: {$id})",
                        'cursos',
                        $id
                    );
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Error al eliminar cursos masivamente: " . $e->getMessage());
            throw new Exception("Error al eliminar los cursos");
        }
    }
    
    /**
     * Exporta múltiples cursos seleccionados
     * 
     * @param array $ids IDs de los cursos a exportar
     * @return void
     * @throws Exception Si hay error en la exportación
     */
    private function exportarSeleccionados($ids) {
        if (!is_array($ids) || empty($ids)) {
            throw new Exception("No se proporcionaron IDs de cursos para exportar");
        }
        
        try {
            // Obtener datos de los cursos seleccionados
            $cursos = [];
            foreach ($ids as $id) {
                $curso = $this->curso->obtenerPorId($id);
                if ($curso) {
                    $cursos[] = $curso;
                }
            }
            
            // Generar CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="cursos_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Encabezados CSV
            fputcsv($output, [
                'ID', 'Nombre del Curso', 'Descripción', 'Profesor', 
                'Fecha Creación', 'Estado', 'Alumnos'
            ]);
            
            // Datos de los cursos
            foreach ($cursos as $curso) {
                // Obtener nombre completo del profesor
                $profesor = $this->usuario->buscarPorId($curso['id_profesor']);
                $nombreProfesor = $profesor ? $profesor['nombre'] . ' ' . $profesor['apellidos'] : 'Sin asignar';
                
                // Obtener número de alumnos en el curso si es necesario
                $alumnosCount = 0; // Implementar método para contar alumnos en el curso si es necesario
                
                fputcsv($output, [
                    $curso['id_curso'],
                    $curso['nombre_curso'],
                    $curso['descripcion'] ?? '',
                    $nombreProfesor,
                    $curso['fecha_creacion'],
                    $curso['activo'] ? 'Activo' : 'Inactivo',
                    $alumnosCount
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al exportar cursos: " . $e->getMessage());
            throw new Exception("Error al exportar los cursos seleccionados");
        }
    }
    
    /**
     * Mostrar página de importación de cursos
     */
    public function importar() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para importar cursos';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }

            // Cargar vista
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/cursos/importar.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            echo '</body></html>';
            
        } catch (Exception $e) {
            error_log("Error en página de importación de cursos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la página de importación';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
    }

    /**
     * Procesar importación de cursos desde CSV
     */
    public function procesarImportacion() {
        try {
            // Verificar permisos y método
            if ($_SESSION['rol'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Acceso no autorizado';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }

            // Verificar que se subió un archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'No se pudo cargar el archivo';
                header('Location: ' . BASE_URL . '/cursos/importar');
                exit;
            }

            // Validar tipo de archivo
            $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if ($extension !== 'csv') {
                $_SESSION['error'] = 'Solo se permiten archivos CSV';
                header('Location: ' . BASE_URL . '/cursos/importar');
                exit;
            }

            // Procesar archivo CSV
            $archivo = $_FILES['archivo']['tmp_name'];
            $handle = fopen($archivo, 'r');
            
            if (!$handle) {
                $_SESSION['error'] = 'No se pudo leer el archivo';
                header('Location: ' . BASE_URL . '/cursos/importar');
                exit;
            }

            $importados = 0;
            $errores = 0;
            $fila = 0;

            // Saltar encabezados
            fgetcsv($handle, 1000, ';');

            while (($datos = fgetcsv($handle, 1000, ';')) !== FALSE) {
                $fila++;
                
                try {
                    // Validar datos mínimos
                    if (count($datos) < 3) {
                        $errores++;
                        continue;
                    }

                    $nombre = trim($datos[0]);
                    $descripcion = trim($datos[1]);
                    $correoProfesor = trim($datos[2]);

                    // Validaciones básicas
                    if (empty($nombre) || empty($descripcion) || empty($correoProfesor)) {
                        $errores++;
                        continue;
                    }

                    // Buscar profesor por correo
                    $profesor = $this->curso->obtenerUsuarioPorCorreo($correoProfesor);
                    if (!$profesor || $profesor['rol'] !== 'profesor') {
                        $errores++;
                        continue;
                    }

                    // Crear curso
                    $datosCurso = [
                        'nombre_curso' => $nombre,
                        'descripcion' => $descripcion,
                        'id_profesor' => $profesor['id_usuario'],
                        'activo' => 1
                    ];

                    if ($this->curso->crear($datosCurso)) {
                        $importados++;
                    } else {
                        $errores++;
                    }

                } catch (Exception $e) {
                    error_log("Error importando fila $fila: " . $e->getMessage());
                    $errores++;
                }
            }

            fclose($handle);

            // Mensaje de resultado
            $mensaje = "Importación completada: $importados cursos importados";
            if ($errores > 0) {
                $mensaje .= ", $errores errores";
            }

            $_SESSION['exito'] = $mensaje;
            header('Location: ' . BASE_URL . '/cursos');
            exit;

        } catch (Exception $e) {
            error_log("Error procesando importación de cursos: " . $e->getMessage());
            $_SESSION['error'] = 'Error procesando importación: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/cursos/importar');
            exit;
        }
    }

    /**
     * Mostrar estadísticas de cursos
     */
    public function estadisticas() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para ver estadísticas';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }

            // Obtener estadísticas desde el modelo
            $estadisticas = $this->curso->obtenerEstadisticas();

            // Preparar datos para la vista
            $datos = [
                'titulo' => 'Estadísticas de Cursos',
                'estadisticas' => $estadisticas
            ];

            // Cargar vista
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/cursos/estadisticas.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            echo '</body></html>';
            
        } catch (Exception $e) {
            error_log("Error en estadísticas de cursos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar estadísticas';
            header('Location: ' . BASE_URL . '/cursos');
            exit;
        }
    }
}
