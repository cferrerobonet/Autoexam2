<?php
/**
 * Controlador de Módulos - AUTOEXAM2
 * 
 * Gestiona las operaciones CRUD de módulos del sistema
 * Reescrito completamente siguiendo el patrón de controladores funcionales
 * 
 * @author GitHub Copilot
 * @version 2.0
 */

require_once APP_PATH . '/modelos/modulo_modelo.php';
require_once APP_PATH . '/utilidades/sesion.php';

class ModulosControlador {
    private $modulo;
    private $sesion;
    
    public function __construct() {
        $this->modulo = new ModuloModelo();
        $this->sesion = new Sesion();
    }
    
    /**
     * Acción principal - listar módulos
     */
    public function index() {
        // Verificar permisos
        $rol = $_SESSION['rol'];
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Parámetros de paginación y filtrado
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 15;
        
        // Obtener filtros
        $filtros = [];
        if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
            $filtros['buscar'] = $_GET['buscar'];
        }
        
        // Para profesores, mostrar solo sus módulos
        if ($rol == 'profesor') {
            $filtros['id_profesor'] = $_SESSION['id_usuario'];
        }
        
        // Obtener datos
        $resultado = $this->modulo->obtenerTodos($limite, $pagina, $filtros);
        
        // Obtener profesores para el filtro (solo para administradores)
        $profesores = [];
        if ($rol == 'admin') {
            $profesores = $this->modulo->obtenerProfesores();
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
        
        // Cargar la vista según el rol
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
        echo '</body></html>';
    }
    
    /**
     * Mostrar formulario de creación
     */
    public function nuevo() {
        $rol = $_SESSION['rol'];
        
        // Verificar permisos
        if ($rol != 'admin' && $rol != 'profesor') {
            header("Location: " . BASE_URL);
            exit;
        }
        
        // Obtener profesores para el selector (solo admin)
        $profesores = [];
        if ($rol === 'admin') {
            $profesores = $this->modulo->obtenerProfesores();
        }
        
        // Obtener cursos para selección
        $cursos = [];
        if ($rol === 'admin') {
            $cursos = $this->modulo->obtenerCursos();
        } else {
            $cursos = $this->modulo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
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
        echo '</body></html>';
    }
    
    /**
     * Crear nuevo módulo
     */
    public function crear() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/modulos/nuevo');
            exit;
        }
        
        // Verificar token CSRF
        if (!$this->sesion->verificarTokenCSRF($_POST['csrf_token'] ?? '')) {
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
        exit;
    }
    
    /**
     * Activar/Desactivar módulo
     */
    public function toggle() {
        if (!isset($_POST['id_modulo']) || !is_numeric($_POST['id_modulo'])) {
            $_SESSION['error'] = 'ID de módulo inválido';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
        
        $id_modulo = (int)$_POST['id_modulo'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if ($this->modulo->cambiarEstado($id_modulo, $activo)) {
            $_SESSION['exito'] = 'Estado del módulo actualizado correctamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el estado del módulo';
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
}
