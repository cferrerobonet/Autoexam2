<?php
/**
 * Controlador de Módulos - AUTOEXAM2
 * 
 * Gestiona las operaciones CRUD de módulos del sistema
 * Refactorizado siguiendo el patrón de controladores seguros
 * 
 * @author GitHub Copilot
 * @version 3.0
 */

class ModulosControlador {
    private $modelo;
    private $sesion;
    private $registroActividad;
    
    public function __construct() {
        // Cargar dependencias
        require_once APP_PATH . '/modelos/modulo_modelo.php';
        require_once APP_PATH . '/utilidades/sesion.php';
        require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
        
        $this->modelo = new ModuloModelo();
        $this->sesion = new Sesion();
        $this->registroActividad = new RegistroActividad();
        
        // Verificar sesión activa
        if (!$this->sesion->validarSesionActiva()) {
            header('Location: ' . BASE_URL . '/autenticacion/login');
            exit;
        }
        
        // Verificar permisos de acceso
        $this->verificarAccesoModulos();
    }
    
    /**
     * Verificar permisos de acceso a módulos
     */
    private function verificarAccesoModulos() {
        if (!isset($_SESSION['rol'])) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        $rolesPermitidos = ['admin', 'profesor'];
        if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
    }
    
    
    /**
     * Listado principal de módulos con paginación y filtros
     */
    public function index() {
        try {
            // Obtener parámetros de paginación y filtros
            $paginacion = $this->obtenerParametrosPaginacion();
            $filtros = $this->obtenerFiltrosBusqueda();
            
            // Para profesores, filtrar solo sus módulos
            if ($_SESSION['rol'] === 'profesor') {
                $filtros['id_profesor'] = $_SESSION['id_usuario'];
            }
            
            // Obtener datos
            $resultado = $this->modelo->obtenerTodos($paginacion['porPagina'], $paginacion['pagina'], $filtros);
            
            // Obtener datos adicionales para filtros
            $profesores = ($_SESSION['rol'] === 'admin') ? $this->modelo->obtenerProfesores() : [];
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Gestión de Módulos',
                'modulos' => $resultado['modulos'],
                'pagina_actual' => $paginacion['pagina'],
                'total_paginas' => $resultado['paginas'],
                'por_pagina' => $paginacion['porPagina'],
                'total_registros' => $resultado['total'],
                'filtros' => $filtros,
                'profesores' => $profesores,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Cargar vista según rol
            $this->cargarVista('listar', $datos);
            
        } catch (Exception $e) {
            error_log("Error en listado de módulos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar los módulos';
            $this->cargarVista('listar', ['modulos' => [], 'error' => true]);
        }
    }
    
    /**
     * Obtener parámetros de paginación validados
     */
    private function obtenerParametrosPaginacion() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['limite']) ? (int)$_GET['limite'] : 15;
        
        // Validar rangos
        $pagina = max(1, $pagina);
        $porPagina = max(5, min(100, $porPagina));
        
        return [
            'pagina' => $pagina,
            'porPagina' => $porPagina
        ];
    }
    
    /**
     * Obtener filtros de búsqueda sanitizados
     */
    private function obtenerFiltrosBusqueda() {
        $filtros = [];
        
        if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
            $filtros['buscar'] = $this->sanitizarTexto($_GET['buscar']);
        }
        
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filtros['activo'] = (int)$_GET['activo'];
        }
        
        if (isset($_GET['id_profesor']) && is_numeric($_GET['id_profesor'])) {
            $filtros['id_profesor'] = (int)$_GET['id_profesor'];
        }
        
        // Ordenación
        if (isset($_GET['ordenar_por']) && !empty($_GET['ordenar_por'])) {
            // Validar campos de ordenación permitidos
            $camposPermitidos = ['id_modulo', 'titulo', 'apellidos', 'total_examenes', 'activo', 'fecha_creacion'];
            
            if (in_array($_GET['ordenar_por'], $camposPermitidos)) {
                $filtros['ordenar_por'] = $_GET['ordenar_por'];
                
                // Dirección de ordenación (ASC/DESC)
                $filtros['orden'] = isset($_GET['orden']) && strtoupper($_GET['orden']) === 'DESC' ? 'DESC' : 'ASC';
            }
        }
        
        return $filtros;
    }
    
    
    /**
     * Mostrar formulario de creación de módulo
     */
    public function nuevo() {
        try {
            // Obtener datos para formulario
            $profesores = ($_SESSION['rol'] === 'admin') ? $this->modelo->obtenerProfesores() : [];
            $cursos = $this->obtenerCursosSegunRol();
            
            // Generar o reutilizar token CSRF existente
            $csrfToken = $this->sesion->generarTokenCSRF();
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Nuevo Módulo',
                'profesores' => $profesores,
                'cursos' => $cursos,
                'csrf_token' => $csrfToken
            ];
            
            $this->cargarVista('formulario', $datos);
            
        } catch (Exception $e) {
            error_log("Error al cargar formulario de módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el formulario';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }
    
    /**
     * Crear nuevo módulo
     */
    public function crear() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF (sin consumir aún)
            if (empty($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'], false)) {
                $_SESSION['error'] = 'Error de validación de seguridad.';
                header('Location: ' . BASE_URL . '/modulos/nuevo');
                exit;
            }
            
            // Obtener y validar datos
            $datos = $this->obtenerDatosModulo();
            $this->validarDatosModulo($datos);
            
            // Ajustar datos según rol
            if ($_SESSION['rol'] === 'profesor') {
                $datos['id_profesor'] = $_SESSION['id_usuario'];
            }
            
            // Crear módulo
            $idModulo = $this->modelo->crear($datos);
            
            if (!$idModulo) {
                throw new Exception('Error al crear el módulo en la base de datos');
            }
            
            // Asignar módulo a cursos
            if (!empty($datos['cursos'])) {
                $this->modelo->asignarCursos($idModulo, $datos['cursos']);
            }
            
            // Consumir token CSRF solo después del éxito
            $this->sesion->validarTokenCSRF($_POST['csrf_token'], true);
            
            // Registrar actividad
            $this->registrarActividad('modulo_creado', [
                'id_modulo' => $idModulo,
                'titulo' => $datos['titulo'],
                'id_profesor' => $datos['id_profesor']
            ]);
            
            $_SESSION['exito'] = 'Módulo creado exitosamente';
            header('Location: ' . BASE_URL . '/modulos');
            
        } catch (Exception $e) {
            error_log("Error al crear módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el módulo: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos/nuevo');
        }
        
        exit;
    }
    
    
    /**
     * Activar/Desactivar módulo
     */
    public function alternarEstado() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'modulos');
            
            // Validar parámetros
            $this->validarCamposObligatorios(['id_modulo'], 'modulos');
            
            $idModulo = (int)$_POST['id_modulo'];
            $activo = isset($_POST['activo']) ? 1 : 0;
            
            // Verificar que el módulo existe y el usuario tiene permisos
            $modulo = $this->modelo->obtenerPorId($idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tiene permisos para modificar este módulo');
            }
            
            // Actualizar estado
            if (!$this->modelo->cambiarEstado($idModulo, $activo)) {
                throw new Exception('Error al actualizar el estado del módulo');
            }
            
            // Registrar actividad
            $this->registrarActividad('modulo_estado_cambiado', [
                'id_modulo' => $idModulo,
                'titulo' => $modulo['titulo'],
                'nuevo_estado' => $activo ? 'activo' : 'inactivo'
            ]);
            
            $_SESSION['exito'] = 'Estado del módulo actualizado correctamente';
            
        } catch (Exception $e) {
            error_log("Error al cambiar estado de módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el estado: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
    
    /**
     * Ver detalles de un módulo
     */
    public function ver($idModulo = null) {
        try {
            if (!$idModulo) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            $modulo = $this->modelo->obtenerPorId((int)$idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                $_SESSION['error'] = 'No tiene permisos para ver este módulo';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }
            
            // Obtener exámenes del módulo
            $examenes = $this->modelo->obtenerExamenes($idModulo);
            
            $datos = [
                'titulo' => 'Detalles del Módulo',
                'modulo' => $modulo,
                'examenes' => $examenes
            ];
            
            $this->cargarVista('ver', $datos);
            
        } catch (Exception $e) {
            error_log("Error al ver módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el módulo: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }
    
    /**
     * Mostrar formulario de edición de módulo
     */
    public function editar($idModulo = null) {
        try {
            if (!$idModulo) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            $modulo = $this->modelo->obtenerPorId((int)$idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                $_SESSION['error'] = 'No tiene permisos para editar este módulo';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }
            
            // Obtener datos para formulario
            $profesores = ($_SESSION['rol'] === 'admin') ? $this->modelo->obtenerProfesores() : [];
            $cursos = $this->obtenerCursosSegunRol();
            $cursosAsignados = $this->modelo->obtenerCursosAsignados($idModulo);
            
            $datos = [
                'titulo' => 'Editar Módulo',
                'modulo' => $modulo,
                'profesores' => $profesores,
                'cursos' => $cursos,
                'cursos_asignados' => $cursosAsignados,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            $this->cargarVista('formulario', $datos);
            
        } catch (Exception $e) {
            error_log("Error al cargar formulario de edición: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el formulario: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }
    
    /**
     * Actualizar módulo
     */
    public function actualizar() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF (sin consumir aún)
            if (empty($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'], false)) {
                $_SESSION['error'] = 'Error de validación de seguridad.';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }
            
            // Validar parámetros
            $this->validarCamposObligatorios(['id_modulo'], 'modulos');
            
            $idModulo = (int)$_POST['id_modulo'];
            
            // Verificar que el módulo existe y el usuario tiene permisos
            $moduloExistente = $this->modelo->obtenerPorId($idModulo);
            if (!$moduloExistente) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $moduloExistente['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tiene permisos para modificar este módulo');
            }
            
            // Obtener y validar datos
            $datos = $this->obtenerDatosModulo();
            $this->validarDatosModulo($datos);
            
            // Ajustar datos según rol
            if ($_SESSION['rol'] === 'profesor') {
                $datos['id_profesor'] = $_SESSION['id_usuario'];
            }
            
            // Actualizar módulo
            if (!$this->modelo->actualizar($idModulo, $datos)) {
                throw new Exception('Error al actualizar el módulo en la base de datos');
            }
            
            // Actualizar cursos asignados
            if (isset($datos['cursos'])) {
                $this->modelo->actualizarCursosAsignados($idModulo, $datos['cursos']);
            }
            
            // Consumir token CSRF solo después del éxito
            $this->sesion->validarTokenCSRF($_POST['csrf_token'], true);
            
            // Registrar actividad
            $this->registrarActividad('modulo_actualizado', [
                'id_modulo' => $idModulo,
                'titulo' => $datos['titulo'],
                'id_profesor' => $datos['id_profesor']
            ]);
            
            $_SESSION['exito'] = 'Módulo actualizado exitosamente';
            header('Location: ' . BASE_URL . '/modulos');
            
        } catch (Exception $e) {
            error_log("Error al actualizar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el módulo: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos');
        }
        
        exit;
    }
    
    /**
     * Activar módulo
     */
    public function activar($idModulo = null) {
        try {
            if (!$idModulo) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            $modulo = $this->modelo->obtenerPorId((int)$idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tiene permisos para modificar este módulo');
            }
            
            // Activar módulo
            if (!$this->modelo->cambiarEstado($idModulo, 1)) {
                throw new Exception('Error al activar el módulo');
            }
            
            // Registrar actividad
            $this->registrarActividad('modulo_activado', [
                'id_modulo' => $idModulo,
                'titulo' => $modulo['titulo']
            ]);
            
            $_SESSION['exito'] = 'Módulo activado correctamente';
            
        } catch (Exception $e) {
            error_log("Error al activar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al activar el módulo: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
    
    /**
     * Desactivar módulo
     */
    public function desactivar($idModulo = null) {
        try {
            if (!$idModulo) {
                throw new Exception('ID de módulo no proporcionado');
            }
            
            $modulo = $this->modelo->obtenerPorId((int)$idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tiene permisos para modificar este módulo');
            }
            
            // Desactivar módulo
            if (!$this->modelo->cambiarEstado($idModulo, 0)) {
                throw new Exception('Error al desactivar el módulo');
            }
            
            // Registrar actividad
            $this->registrarActividad('modulo_desactivado', [
                'id_modulo' => $idModulo,
                'titulo' => $modulo['titulo']
            ]);
            
            $_SESSION['exito'] = 'Módulo desactivado correctamente';
            
        } catch (Exception $e) {
            error_log("Error al desactivar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al desactivar el módulo: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }
    
    /**
     * Eliminar módulo
     */
    public function eliminar() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'modulos');
            
            // Validar parámetros
            $this->validarCamposObligatorios(['id_modulo'], 'modulos');
            
            $idModulo = (int)$_POST['id_modulo'];
            
            // Verificar que el módulo existe y el usuario tiene permisos
            $modulo = $this->modelo->obtenerPorId($idModulo);
            if (!$modulo) {
                throw new Exception('Módulo no encontrado');
            }
            
            // Si es profesor, verificar que es su módulo
            if ($_SESSION['rol'] === 'profesor' && $modulo['id_profesor'] != $_SESSION['id_usuario']) {
                throw new Exception('No tiene permisos para eliminar este módulo');
            }
            
            // Verificar que no tenga exámenes asociados
            $examenes = $this->modelo->obtenerExamenes($idModulo);
            if (!empty($examenes)) {
                throw new Exception('No se puede eliminar el módulo porque tiene exámenes asociados');
            }
            
            // Eliminar módulo
            if (!$this->modelo->eliminar($idModulo)) {
                throw new Exception('Error al eliminar el módulo');
            }
            
            // Registrar actividad
            $this->registrarActividad('modulo_eliminado', [
                'id_modulo' => $idModulo,
                'titulo' => $modulo['titulo']
            ]);
            
            $_SESSION['exito'] = 'Módulo eliminado correctamente';
            
        } catch (Exception $e) {
            error_log("Error al eliminar módulo: " . $e->getMessage());
            $_SESSION['error'] = 'Error al eliminar el módulo: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/modulos');
        exit;
    }

    /**
     * Exportar módulos filtrados
     */
    public function exportar() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para exportar módulos';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }

            // Obtener filtros
            $filtros = $this->obtenerFiltrosBusqueda();
            
            // Obtener todos los módulos según filtros
            $resultado = $this->modelo->obtenerTodos(999999, 1, $filtros);
            $modulos = $resultado['modulos'];

            if (empty($modulos)) {
                $_SESSION['error'] = 'No hay módulos para exportar con los filtros aplicados';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }

            // Configurar headers para descarga CSV
            $filename = 'modulos_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 en Excel
            fwrite($output, "\xEF\xBB\xBF");
            
            // Encabezados
            fputcsv($output, [
                'ID',
                'Título',
                'Descripción',
                'Curso',
                'Profesor',
                'Orden',
                'Estado',
                'Fecha Creación'
            ], ';');

            // Datos de módulos
            foreach ($modulos as $modulo) {
                fputcsv($output, [
                    $modulo['id_modulo'],
                    $modulo['titulo'],
                    $modulo['descripcion'],
                    $modulo['nombre_curso'] ?? 'Sin curso',
                    $modulo['nombre_profesor'] ?? 'Sin profesor',
                    $modulo['orden'],
                    $modulo['activo'] ? 'Activo' : 'Inactivo',
                    $modulo['fecha_creacion']
                ], ';');
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("Error al exportar módulos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar módulos: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }

    /**
     * Mostrar página de importación de módulos
     */
    public function importar() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para importar módulos';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }

            $datos = [
                'titulo' => 'Importar Módulos',
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];

            $this->cargarVista('importar', $datos);
            
        } catch (Exception $e) {
            error_log("Error en página de importación de módulos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la página de importación';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }

    /**
     * Procesar importación de módulos desde CSV
     */
    public function procesarImportacion() {
        try {
            // Verificar permisos y método
            if ($_SESSION['rol'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Acceso no autorizado';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }

            // Verificar token CSRF
            if (!$this->sesion->validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/modulos/importar');
                exit;
            }

            // Verificar que se subió un archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'No se pudo cargar el archivo';
                header('Location: ' . BASE_URL . '/modulos/importar');
                exit;
            }

            // Validar tipo de archivo
            $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if ($extension !== 'csv') {
                $_SESSION['error'] = 'Solo se permiten archivos CSV';
                header('Location: ' . BASE_URL . '/modulos/importar');
                exit;
            }

            // Procesar archivo CSV
            $archivo = $_FILES['archivo']['tmp_name'];
            $handle = fopen($archivo, 'r');
            
            if (!$handle) {
                $_SESSION['error'] = 'No se pudo leer el archivo';
                header('Location: ' . BASE_URL . '/modulos/importar');
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
                    if (count($datos) < 4) {
                        $errores++;
                        continue;
                    }

                    $titulo = trim($datos[0]);
                    $descripcion = trim($datos[1]);
                    $idCurso = (int)trim($datos[2]);
                    $orden = (int)trim($datos[3]);

                    // Validaciones básicas
                    if (empty($titulo) || empty($descripcion) || $idCurso <= 0) {
                        $errores++;
                        continue;
                    }

                    // Crear módulo
                    $datosModulo = [
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'id_curso' => $idCurso,
                        'orden' => $orden > 0 ? $orden : 1,
                        'activo' => 1
                    ];

                    if ($this->modelo->crear($datosModulo)) {
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
            $mensaje = "Importación completada: $importados módulos importados";
            if ($errores > 0) {
                $mensaje .= ", $errores errores";
            }

            $_SESSION['exito'] = $mensaje;
            header('Location: ' . BASE_URL . '/modulos');
            exit;

        } catch (Exception $e) {
            error_log("Error procesando importación de módulos: " . $e->getMessage());
            $_SESSION['error'] = 'Error procesando importación: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/modulos/importar');
            exit;
        }
    }

    /**
     * Mostrar estadísticas de módulos
     */
    public function estadisticas() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para ver estadísticas';
                header('Location: ' . BASE_URL . '/modulos');
                exit;
            }

            // Obtener estadísticas desde el modelo
            $estadisticas = $this->modelo->obtenerEstadisticas();

            $datos = [
                'titulo' => 'Estadísticas de Módulos',
                'estadisticas' => $estadisticas,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];

            $this->cargarVista('estadisticas', $datos);
            
        } catch (Exception $e) {
            error_log("Error en estadísticas de módulos: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar estadísticas';
            header('Location: ' . BASE_URL . '/modulos');
            exit;
        }
    }

    // ============ MÉTODOS HELPER Y VALIDACIÓN ============
    
    /**
     * Verificar que la petición usa método POST
     */
    private function verificarMetodoPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Método no permitido");
        }
    }
    
    /**
     * Verificar token CSRF
     */
    private function verificarTokenCSRF($token, $rutaError = 'modulos', $consumir = true) {
        if (empty($token) || !$this->sesion->validarTokenCSRF($token, $consumir)) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/' . $rutaError);
            exit;
        }
    }
    
    /**
     * Validar campos obligatorios
     */
    private function validarCamposObligatorios($campos, $rutaError = 'modulos') {
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
                header('Location: ' . BASE_URL . '/' . $rutaError);
                exit;
            }
        }
    }
    
    /**
     * Obtener y sanitizar datos del módulo
     */
    private function obtenerDatosModulo() {
        return [
            'titulo' => $this->sanitizarTexto($_POST['titulo'] ?? ''),
            'descripcion' => $this->sanitizarTexto($_POST['descripcion'] ?? ''),
            'id_profesor' => isset($_POST['id_profesor']) ? (int)$_POST['id_profesor'] : null,
            'cursos' => isset($_POST['cursos']) && is_array($_POST['cursos']) ? 
                       array_map('intval', $_POST['cursos']) : []
        ];
    }
    
    /**
     * Validar datos del módulo
     */
    private function validarDatosModulo($datos) {
        if (empty($datos['titulo'])) {
            throw new Exception('El título del módulo es obligatorio');
        }
        
        if (strlen($datos['titulo']) > 255) {
            throw new Exception('El título no puede exceder 255 caracteres');
        }
        
        if ($_SESSION['rol'] === 'admin' && empty($datos['id_profesor'])) {
            throw new Exception('Debe seleccionar un profesor');
        }
        
        if (empty($datos['cursos'])) {
            throw new Exception('Debe seleccionar al menos un curso para el módulo');
        }
    }
    
    /**
     * Sanitizar texto de entrada
     */
    private function sanitizarTexto($texto) {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Obtener cursos según el rol del usuario
     */
    private function obtenerCursosSegunRol() {
        if ($_SESSION['rol'] === 'admin') {
            return $this->modelo->obtenerCursos();
        } else {
            return $this->modelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
        }
    }
    
    /**
     * Cargar vista según el rol del usuario
     */
    private function cargarVista($vista, $datos = []) {
        $rol = $_SESSION['rol'];
        $rutaVista = ($rol === 'admin') ? 'admin' : 'profesor';
        
        require_once APP_PATH . "/vistas/parciales/head_{$rol}.php";
        echo '<body class="bg-light">';
        require_once APP_PATH . "/vistas/parciales/navbar_{$rol}.php";
        echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
        require_once APP_PATH . "/vistas/{$rutaVista}/modulos/{$vista}.php";
        echo '</div></div></div>';
        require_once APP_PATH . "/vistas/parciales/footer_{$rol}.php";
        require_once APP_PATH . "/vistas/parciales/scripts_{$rol}.php";
        echo '</body></html>';
    }
    
    /**
     * Registrar actividad del usuario
     */
    private function registrarActividad($accion, $detalles = []) {
        try {
            $descripcion = $accion;
            if (!empty($detalles)) {
                $descripcion .= ': ' . json_encode($detalles);
            }
            
            $this->registroActividad->registrar(
                $_SESSION['id_usuario'],
                $accion,
                $descripcion,
                'modulos',
                $detalles['id_modulo'] ?? null
            );
        } catch (Exception $e) {
            error_log("Error registrando actividad: " . $e->getMessage());
        }
    }
}
