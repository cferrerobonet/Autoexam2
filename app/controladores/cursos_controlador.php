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
    
    /**
     * Constructor de la clase
     */
    public function __construct() {
        try {
            // Cargar modelos
            require_once __DIR__ . '/../modelos/curso_modelo.php';
            require_once __DIR__ . '/../modelos/usuario_modelo.php';
            require_once __DIR__ . '/../utilidades/sesion.php';
            $this->curso = new Curso();
            $this->usuario = new Usuario();
            $this->sesion = new Sesion();
        } catch (Exception $e) {
            // Si hay error de conexión a BD, usar modo desarrollo
            error_log('Error cargando modelos en CursosControlador: ' . $e->getMessage());
            $this->curso = null;
            $this->usuario = null;
            $this->sesion = null;
        }
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
        if ($this->curso !== null) {
            try {
                $resultado = $this->curso->obtenerTodos($limite, $pagina, $filtros);
            } catch (Exception $e) {
                // Si falla la BD, usar datos de ejemplo
                $resultado = $this->obtenerDatosEjemplo();
            }
        } else {
            // Sin conexión a BD, usar datos de ejemplo
            $resultado = $this->obtenerDatosEjemplo();
        }
        
        // Obtener profesores para el filtro (solo para administradores)
        $profesores = [];
        if ($rol == 'admin') {
            if ($this->curso !== null) {
                try {
                    $profesores = $this->curso->obtenerProfesores();
                } catch (Exception $e) {
                    $profesores = $this->obtenerProfesoresEjemplo();
                }
            } else {
                $profesores = $this->obtenerProfesoresEjemplo();
            }
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
                try {
                    $datosUsuario = $this->usuario->buscarPorId($_SESSION['id_usuario']);
                } catch (Exception $e) {
                    error_log('Error al obtener datos del usuario en CursosControlador: ' . $e->getMessage());
                    // Si hay error, usar datos de sesión
                    $datosUsuario = [
                        'nombre' => $_SESSION['nombre'] ?? 'Usuario',
                        'apellidos' => $_SESSION['apellidos'] ?? '',
                        'correo' => $_SESSION['correo'] ?? '',
                        'rol' => $_SESSION['rol'] ?? 'profesor'
                    ];
                }
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
        
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
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
            $_SESSION['mensaje'] = "Curso creado correctamente.";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: " . BASE_URL . "/cursos");
        } else {
            $_SESSION['mensaje'] = "Error al crear el curso.";
            $_SESSION['tipo_mensaje'] = "danger";
            $_SESSION['datos_form'] = $_POST;
            header("Location: " . BASE_URL . "/cursos/nuevo");
        }
        exit;
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
                $profesor = $this->usuario->obtenerPorId($datos['id_profesor']);
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
                $filtros['orden'] = isset($_GET['direccion']) && strtoupper($_GET['direccion']) === 'DESC' ? 'DESC' : 'ASC';
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
}
