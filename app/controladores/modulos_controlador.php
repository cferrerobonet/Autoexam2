<?php
/**
 * Controlador de Módulos - AUTOEXAM2
 * 
 * Gestiona las operaciones CRUD de módulos del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

require_once APP_PATH . '/modelos/modulo_modelo.php';
require_once APP_PATH . '/utilidades/sesion.php';

class ModulosControlador {
    private $modulo;
    private $sesion;
    
    public function __construct() {
        try {
            // Cargar modelos
            require_once __DIR__ . '/../modelos/modulo_modelo.php';
            require_once __DIR__ . '/../utilidades/sesion.php';
            $this->modulo = new ModuloModelo();
            $this->sesion = new Sesion();
        } catch (Exception $e) {
            error_log('Error cargando modelos en ModulosControlador: ' . $e->getMessage());
            $this->modulo = null;
            $this->sesion = null;
        }
    }
    
    /**
     * Acción principal - listar módulos
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
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 15;
        
        // Obtener filtros y parámetros de ordenación
        $filtros = $this->obtenerFiltros();
        
        // Para profesores, mostrar solo sus módulos
        if ($rol == 'profesor') {
            $filtros['id_profesor'] = $_SESSION['id_usuario'];
        }
        
        // Obtener datos
        if ($this->modulo !== null) {
            try {
                $resultado = $this->modulo->obtenerTodos($limite, $pagina, $filtros);
            } catch (Exception $e) {
                error_log("Error al obtener módulos: " . $e->getMessage());
                $resultado = ['modulos' => [], 'total' => 0, 'paginas' => 1];
            }
        } else {
            $resultado = ['modulos' => [], 'total' => 0, 'paginas' => 1];
        }
        
        // Obtener profesores para el filtro (solo para administradores)
        $profesores = [];
        if ($rol == 'admin') {
            if ($this->modulo !== null) {
                try {
                    $profesores = $this->modulo->obtenerProfesores();
                } catch (Exception $e) {
                    $profesores = [];
                }
            }
        }
        
        // Datos para la vista
        $datos = [
            'titulo' => 'Gestión de Módulos',
            'modulos' => $resultado['modulos'],
            'total_registros' => $resultado['total'],
            'total_paginas' => $resultado['paginas'],
            'pagina_actual' => $pagina,
            'limite' => $limite,
            'filtros' => $filtros,
            'profesores' => $profesores,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar la vista
        if ($rol == 'admin') {
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/modulos/listar.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
        } else {
            require_once APP_PATH . '/vistas/parciales/head_profesor.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            require_once APP_PATH . '/vistas/profesor/modulos/listar.php';
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
            require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
        }
    }
    
    /**
     * Obtiene los filtros desde la URL
     */
    private function obtenerFiltros() {
        return [
            'buscar' => $_GET['buscar'] ?? '',
            'profesor' => $_GET['profesor'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'ordenar_por' => $_GET['ordenar_por'] ?? 'titulo',
            'orden' => $_GET['orden'] ?? 'ASC'
        ];
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function nuevo() {
        $rol = $_SESSION['rol'];
        
        // Obtener profesores para el selector (solo admin)
        $profesores = [];
        if ($rol === 'admin' && $this->modulo !== null) {
            try {
                $profesores = $this->modulo->obtenerProfesores();
            } catch (Exception $e) {
                error_log("Error al obtener profesores: " . $e->getMessage());
            }
        }
        
        // Obtener cursos para selección
        $cursos = [];
        if ($this->modulo !== null) {
            try {
                // Para admin: todos los cursos, Para profesor: solo sus cursos
                if ($rol === 'admin') {
                    $cursos = $this->modulo->obtenerCursos();
                } else {
                    $cursos = $this->modulo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
                }
            } catch (Exception $e) {
                error_log("Error al obtener cursos: " . $e->getMessage());
            }
        }
        
        // Datos para la vista
        $datos = [
            'titulo' => 'Nuevo Módulo',
            'profesores' => $profesores,
            'cursos' => $cursos,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar vista según rol
        if ($rol === 'admin') {
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            require_once APP_PATH . '/vistas/admin/modulos/formulario.php';
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
        } else {
            require_once APP_PATH . '/vistas/parciales/head_profesor.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            require_once APP_PATH . '/vistas/profesor/modulos/formulario.php';
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
            require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
        }
    }
    
    /**
     * Crear nuevo módulo
     */
    public function crear() {
        // Verificar token CSRF
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/modulos/nuevo');
            exit;
        }
        
        // Validar datos
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id_profesor = $_POST['id_profesor'] ?? '';
        $cursos = $_POST['cursos'] ?? [];
        
        if (empty($titulo)) {
            $_SESSION['error'] = 'El título del módulo es obligatorio';
            header('Location: ' . BASE_URL . '/modulos/nuevo');
            exit;
        }
        
        // Para profesores, usar su propio ID
        if ($_SESSION['rol'] === 'profesor') {
            $id_profesor = $_SESSION['id_usuario'];
        } elseif (empty($id_profesor)) {
            $_SESSION['error'] = 'Debe seleccionar un profesor';
            header('Location: ' . BASE_URL . '/modulos/nuevo');
            exit;
        }
        
        // Validar que se seleccione al menos un curso
        if (empty($cursos)) {
            $_SESSION['error'] = 'Debe seleccionar al menos un curso para el módulo';
            header('Location: ' . BASE_URL . '/modulos/nuevo');
            exit;
        }
        
        // Crear módulo
        if ($this->modulo !== null) {
            try {
                $id_modulo = $this->modulo->crear([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'id_profesor' => $id_profesor
                ]);
                
                if ($id_modulo) {
                    // Asignar módulo a cursos
                    $cursos_asignados = $this->modulo->asignarCursos($id_modulo, $cursos);
                    
                    if ($cursos_asignados) {
                        $_SESSION['exito'] = 'Módulo creado y asignado a cursos exitosamente';
                    } else {
                        $_SESSION['error'] = 'Módulo creado pero hubo error al asignar cursos';
                    }
                    header('Location: ' . BASE_URL . '/modulos');
                } else {
                    $_SESSION['error'] = 'Error al crear el módulo';
                    header('Location: ' . BASE_URL . '/modulos/nuevo');
                }
            } catch (Exception $e) {
                error_log("Error al crear módulo: " . $e->getMessage());
                $_SESSION['error'] = 'Error interno del servidor';
                header('Location: ' . BASE_URL . '/modulos/nuevo');
            }
        } else {
            $_SESSION['error'] = 'Error de conexión';
            header('Location: ' . BASE_URL . '/modulos/nuevo');
        }
        exit;
    }
    
    /**
     * Activar/Desactivar módulo
     */
    public function cambiarEstado() {
        $id_modulo = $_POST['id_modulo'] ?? 0;
        $accion = $_POST['accion'] ?? '';
        
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
        
        if ($this->modulo !== null && $id_modulo > 0) {
            try {
                if ($accion === 'activar') {
                    $resultado = $this->modulo->activar($id_modulo);
                    $mensaje = $resultado ? 'Módulo activado exitosamente' : 'Error al activar el módulo';
                } else {
                    $resultado = $this->modulo->desactivar($id_modulo);
                    $mensaje = $resultado ? 'Módulo desactivado exitosamente' : 'Error al desactivar el módulo';
                }
                
                $_SESSION[$resultado ? 'exito' : 'error'] = $mensaje;
            } catch (Exception $e) {
                error_log("Error al cambiar estado del módulo: " . $e->getMessage());
                $_SESSION['error'] = 'Error interno del servidor';
            }
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
    
    /**
     * Activar módulo
     */
    public function activar($id_modulo) {
        // Verificar permisos
        if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'profesor'])) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        $id_modulo = (int)$id_modulo;
        if ($id_modulo <= 0) {
            $_SESSION['error'] = 'ID de módulo inválido';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
        
        try {
            if ($this->modulo->activar($id_modulo)) {
                $_SESSION['exito'] = 'Módulo activado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al activar el módulo';
            }
        } catch (Exception $e) {
            error_log("Error al activar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
    
    /**
     * Desactivar módulo
     */
    public function desactivar($id_modulo) {
        // Verificar permisos
        if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'profesor'])) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        $id_modulo = (int)$id_modulo;
        if ($id_modulo <= 0) {
            $_SESSION['error'] = 'ID de módulo inválido';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
        
        try {
            if ($this->modulo->desactivar($id_modulo)) {
                $_SESSION['exito'] = 'Módulo desactivado exitosamente';
            } else {
                $_SESSION['error'] = 'Error al desactivar el módulo';
            }
        } catch (Exception $e) {
            error_log("Error al desactivar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
}
