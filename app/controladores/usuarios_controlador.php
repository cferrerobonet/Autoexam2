<?php
/**
 * Controlador de Usuarios - AUTOEXAM2
 * 
 * Gestiona el CRUD completo de usuarios del sistema
 * 
 * @author GitHub Copilot (refactorizado)
 * @version 2.0
 */
class UsuariosControlador {
    private $usuarioModelo;
    private $sesion;
    private $registroActividad;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Cargar utilidades necesarias
        require_once APP_PATH . '/utilidades/sesion.php';
        require_once APP_PATH . '/modelos/usuario_modelo.php';
        require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
        
        $this->sesion = new Sesion();
        $this->usuarioModelo = new Usuario();
        $this->registroActividad = new RegistroActividad();
        
        // Verificar sesión activa
        if (!$this->sesion->validarSesionActiva()) {
            header('Location: ' . BASE_URL . '/autenticacion/login');
            exit;
        }
        
        // Verificar permisos de administrador
        $this->verificarAccesoAdministrador();
    }
    
    /**
     * Verifica que el usuario tenga rol de administrador
     * 
     * @return void
     */
    private function verificarAccesoAdministrador() {
        if (!isset($_SESSION['rol'])) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
        
        // Permitir a los profesores acceder a ciertas funcionalidades específicas
        $metodo = isset($_GET['url']) ? explode('/', $_GET['url']) : [];
        $accion = isset($metodo[1]) ? $metodo[1] : 'index'; // Por defecto 'index'
        $esProfesor = ($_SESSION['rol'] === 'profesor');
        
        // Lista de acciones permitidas para profesores
        $accionesPermitidas = ['index', 'editar', 'actualizar', 'ver', 'todos', 'crear', 'guardar', 'eliminar'];
        
        // Si no es administrador ni un profesor en una acción permitida
        if ($_SESSION['rol'] !== 'admin' && !($esProfesor && in_array($accion, $accionesPermitidas))) {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
    }
    
    /**
     * Método predeterminado - Lista usuarios con paginación
     * 
     * @return void
     */
    public function index() {
        try {
            // Si es profesor, mostrar solo sus alumnos
            if ($_SESSION['rol'] === 'profesor') {
                $this->mostrarAlumnosDelProfesor();
                return;
            }
            
            // Parámetros de paginación y filtros para administradores
            $paginacion = $this->obtenerParametrosPaginacion();
            $filtros = $this->obtenerFiltrosBusqueda();
            
            // Obtener usuarios
            $usuarios = $this->usuarioModelo->listar($filtros, $paginacion['porPagina'], $paginacion['offset']);
            
            // Contar total para paginación
            $totalUsuarios = $this->usuarioModelo->contarTotal($filtros);
            $totalPaginas = ceil($totalUsuarios / $paginacion['porPagina']);
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Gestión de Usuarios',
                'usuarios' => $usuarios,
                'pagina_actual' => $paginacion['pagina'],
                'total_paginas' => $totalPaginas,
                'por_pagina' => $paginacion['porPagina'],
                'total_usuarios' => $totalUsuarios,
                'filtros' => $filtros,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Cargar vista para administradores
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/usuarios/listar.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            
        } catch (Exception $e) {
            // Registro detallado del error para fines de depuración
            error_log("Error al listar usuarios: " . $e->getMessage());
            
            // Mostrar error con detalles suficientes para diagnosticar sin exponer información sensible
            $_SESSION['error'] = 'Error al cargar la lista de usuarios';
            
            $this->mostrarListaVacia();
        }
    }
    
    /**
     * Obtiene los parámetros de paginación desde la petición
     * 
     * @return array Parámetros de paginación [pagina, porPagina, offset]
     */
    private function obtenerParametrosPaginacion() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 10;
        
        // Validar página mínima y por página
        if ($pagina < 1) $pagina = 1;
        if (!in_array($porPagina, [5, 10, 15, 20, 50, 100])) $porPagina = 10;
        
        // Calcular offset
        $offset = ($pagina - 1) * $porPagina;
        
        return [
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'offset' => $offset
        ];
    }
    
    /**
     * Obtiene y valida los filtros de búsqueda
     * 
     * @return array Filtros validados
     * @throws Exception Si el término de búsqueda es muy corto
     */
    private function obtenerFiltrosBusqueda() {
        $filtros = [];
        
        // Buscar
        if (isset($_GET['buscar'])) {
            $buscar = trim($_GET['buscar']);
            
            // Validar longitud mínima si hay texto
            if (strlen($buscar) > 0 && strlen($buscar) < 3) {
                throw new Exception("La búsqueda debe contener al menos 3 caracteres.");
            }
            
            if (!empty($buscar)) {
                $filtros['buscar'] = $buscar;
            }
        }
        
        // Rol
        if (isset($_GET['rol']) && in_array($_GET['rol'], ['admin', 'profesor', 'alumno'])) {
            $filtros['rol'] = $_GET['rol'];
        }
        
        // Activo
        if (isset($_GET['activo']) && $_GET['activo'] !== '') {
            $filtros['activo'] = (int)$_GET['activo'];
        }
        
        // Ordenación
        if (isset($_GET['ordenar_por']) && !empty($_GET['ordenar_por'])) {
            // Validar campos de ordenación permitidos
            $camposPermitidos = ['id_usuario', 'nombre', 'apellidos', 'rol', 'activo', 'ultimo_acceso'];
            
            if (in_array($_GET['ordenar_por'], $camposPermitidos)) {
                $filtros['ordenar_por'] = $_GET['ordenar_por'];
                
                // Dirección de ordenación (ASC/DESC)
                $filtros['orden'] = isset($_GET['orden']) && strtoupper($_GET['orden']) === 'DESC' ? 'DESC' : 'ASC';
            }
        }
        
        return $filtros;
    }
    
    /**
     * Muestra una lista vacía cuando hay errores
     * 
     * @return void
     */
    private function mostrarListaVacia() {
        $datos = [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => [],
            'pagina_actual' => 1,
            'total_paginas' => 0,
            'por_pagina' => 10,
            'total_usuarios' => 0,
            'filtros' => [
                'buscar' => isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '',
                'rol' => isset($_GET['rol']) ? htmlspecialchars($_GET['rol']) : '',
                'activo' => isset($_GET['activo']) ? htmlspecialchars($_GET['activo']) : ''
            ],
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar la vista con los datos vacíos
        require_once APP_PATH . '/vistas/admin/usuarios/listar.php';
    }
    
    /**
     * Mostrar formulario de creación de usuario
     * 
     * @return void
     */
    public function crear() {
        // Cargar el modelo de cursos para obtener la lista de cursos disponibles
        require_once APP_PATH . '/modelos/curso_modelo.php';
        $cursoModelo = new Curso();
        
        $datos = [
            'titulo' => 'Crear Usuario',
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        // Cargar la vista según el rol del usuario
        if ($_SESSION['rol'] === 'profesor') {
            // Para profesores, solo obtener sus propios cursos y solo pueden crear alumnos
            $datos['cursos'] = $cursoModelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
            require_once APP_PATH . '/vistas/profesor/usuarios/crear.php';
        } else {
            // Para admin, puede crear cualquier tipo de usuario
            // Obtener todos los cursos activos
            $cursos = $cursoModelo->obtenerTodos(100, 1, ['activo' => 1]);
            $datos['cursos'] = $cursos['cursos']; // Lista de cursos disponibles
            require_once APP_PATH . '/vistas/admin/usuarios/crear.php';
        }
    }
    
    /**
     * Procesar creación de usuario
     * 
     * @return void
     */
    public function guardar() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'usuarios/crear');
            
            // Validar campos obligatorios
            $this->validarCamposObligatorios(['nombre', 'apellidos', 'correo', 'contrasena', 'confirmar_contrasena', 'rol'], 'usuarios/crear');
            
            // Validar que las contraseñas coincidan
            $this->validarCoincidenciaContrasenas($_POST['contrasena'], $_POST['confirmar_contrasena'], 'usuarios/crear');
            
            // Sanitizar y validar datos
            $datos = $this->obtenerDatosUsuario();
            
            // Si es profesor, forzar que solo pueda crear alumnos
            if ($_SESSION['rol'] === 'profesor' && $datos['rol'] !== 'alumno') {
                $_SESSION['error'] = 'Como profesor, solo puede crear usuarios con rol de alumno.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
                exit;
            }
            
            // Verificar si el correo ya existe
            if ($this->usuarioModelo->existeCorreo($datos['correo'])) {
                $_SESSION['error'] = 'Ya existe un usuario con ese correo electrónico.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
                exit;
            }
            
            // Capturar curso_asignado antes de crear el usuario
            $cursoAsignadoId = null;
            if ($datos['rol'] === 'alumno' && isset($datos['curso_asignado'])) {
                $cursoAsignadoId = $datos['curso_asignado'];
                
                // Si es profesor, verificar que el curso pertenece al profesor
                if ($_SESSION['rol'] === 'profesor') {
                    require_once APP_PATH . '/modelos/curso_modelo.php';
                    $cursoModelo = new Curso();
                    $cursosProfesor = $cursoModelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
                    
                    $cursoPerteneceProfesor = false;
                    foreach ($cursosProfesor as $curso) {
                        if ($curso['id_curso'] == $cursoAsignadoId) {
                            $cursoPerteneceProfesor = true;
                            break;
                        }
                    }
                    
                    if (!$cursoPerteneceProfesor) {
                        $_SESSION['error'] = 'Solo puede asignar alumnos a sus propios cursos.';
                        header('Location: ' . BASE_URL . '/usuarios/crear');
                        exit;
                    }
                }
                
                // Eliminamos curso_asignado de $datos ya que no se usa en la tabla usuarios
                unset($datos['curso_asignado']);
            }
            
            // Crear usuario
            $idUsuario = $this->usuarioModelo->crear($datos);
            
            if ($idUsuario) {
                // Registrar actividad
                $this->registrarActividadCreacion($idUsuario, $datos);
                
                // Asignar curso al alumno si corresponde
                if ($datos['rol'] === 'alumno' && $cursoAsignadoId) {
                    require_once APP_PATH . '/modelos/curso_modelo.php';
                    $cursoModelo = new Curso();
                    $cursoModelo->asignarAlumno($cursoAsignadoId, $idUsuario);
                }
                
                $_SESSION['exito'] = 'Usuario creado exitosamente.';
                header('Location: ' . BASE_URL . '/usuarios');
            } else {
                $_SESSION['error'] = 'Error al crear el usuario.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el usuario: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/usuarios/crear');
        }
        
        exit;
    }
    
    /**
     * Validar que la petición usa método POST
     * 
     * @throws Exception Si no es método POST
     */
    private function verificarMetodoPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception("Método no permitido");
        }
    }
    
    /**
     * Validar token CSRF
     * 
     * @param string $token Token CSRF enviado
     * @param string $rutaError Ruta a redirigir en caso de error
     * @throws Exception Si el token no es válido
     */
    private function verificarTokenCSRF($token, $rutaError = 'usuarios') {
        if (empty($token) || !$this->sesion->validarTokenCSRF($token)) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/' . $rutaError);
            exit;
        }
    }
    
    /**
     * Validar campos obligatorios
     * 
     * @param array $campos Lista de campos a validar
     * @param string $rutaError Ruta a redirigir en caso de error
     * @throws Exception Si falta algún campo obligatorio
     */
    private function validarCamposObligatorios($campos, $rutaError = 'usuarios') {
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
                header('Location: ' . BASE_URL . '/' . $rutaError);
                exit;
            }
        }
    }
    
    /**
     * Validar que las contraseñas coincidan
     * 
     * @param string $contrasena Contraseña
     * @param string $confirmacion Confirmación de contraseña
     * @param string $rutaError Ruta a redirigir en caso de error
     * @throws Exception Si las contraseñas no coinciden
     */
    private function validarCoincidenciaContrasenas($contrasena, $confirmacion, $rutaError = 'usuarios') {
        if ($contrasena !== $confirmacion) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            header('Location: ' . BASE_URL . '/' . $rutaError);
            exit;
        }
    }
    
    /**
     * Obtener y validar datos del usuario desde POST
     * 
     * @return array Datos validados del usuario
     * @throws Exception Si hay errores de validación
     */
    private function obtenerDatosUsuario() {
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'apellidos' => trim($_POST['apellidos']),
            'correo' => filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL),
            'contrasena' => $_POST['contrasena'],
            'rol' => $_POST['rol'],
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Validar email
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no tiene un formato válido.');
        }
        
        // Validar rol
        if (!in_array($datos['rol'], ['admin', 'profesor', 'alumno'])) {
            throw new Exception('El rol seleccionado no es válido.');
        }
        
        // Procesar curso asignado si corresponde
        if (!empty($_POST['curso_asignado'])) {
            $datos['curso_asignado'] = (int)$_POST['curso_asignado'];
        }
        
        // Procesar foto si se subió
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $datos['foto'] = $this->procesarFotoPerfil($_FILES['foto']);
        }
        
        return $datos;
    }
    
    /**
     * Procesar foto de perfil subida
     * 
     * @param array $archivo Información del archivo subido
     * @return string Ruta relativa a la imagen guardada
     * @throws Exception Si hay error al procesar la foto
     */
    private function procesarFotoPerfil($archivo) {
        // Validar el archivo
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo. Código: " . $archivo['error']);
        }
        
        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            throw new Exception('Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG, GIF y WEBP.');
        }
        
        // Validar tamaño (máximo 2MB)
        if ($archivo['size'] > 2097152) { // 2MB en bytes
            throw new Exception('El archivo es demasiado grande. Máximo 2MB.');
        }
        
        // Utilizar la ruta configurada en storage.php
        $rutaSubidas = AVATARS_STORAGE_DIR . '/';
        if (!is_dir($rutaSubidas)) {
            if (!mkdir($rutaSubidas, 0755, true)) {
                throw new Exception('No se pudo crear el directorio para guardar la foto.');
            }
        }
        
        // Generar nombre único para el archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $nombreArchivo = uniqid('perfil_') . '.' . $extension;
        $rutaCompleta = $rutaSubidas . $nombreArchivo;
        
        // Mover el archivo subido
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception('No se pudo guardar la foto.');
        }
        
        // Devolver ruta relativa para guardar en DB
        return AVATARS_PUBLIC_SUBPATH . '/' . $nombreArchivo;
    }
    
    /**
     * Registrar actividad de creación de usuario
     * 
     * @param int $idUsuario ID del usuario creado
     * @param array $datos Datos del usuario
     * @return void
     */
    private function registrarActividadCreacion($idUsuario, $datos) {
        $this->registroActividad->registrar(
            $_SESSION['id_usuario'],
            'crear_usuario',
            "Usuario creado: {$datos['apellidos']}, {$datos['nombre']} ({$datos['correo']})",
            'usuarios',
            $idUsuario
        );
    }
    
    /**
     * Mostrar formulario de edición
     * 
     * @param int|null $id ID del usuario a editar
     * @return void
     */
    public function editar($id = null) {
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'ID de usuario no especificado o inválido.';
            $rutaRetorno = $_SESSION['rol'] === 'profesor' ? '/cursos' : '/usuarios';
            header('Location: ' . BASE_URL . $rutaRetorno);
            exit;
        }
        
        try {
            // Buscar usuario incluyendo inactivos
            $usuario = $this->usuarioModelo->buscarPorId($id, false);
            
            if (!$usuario) {
                $_SESSION['error'] = 'Usuario no encontrado.';
                $rutaRetorno = $_SESSION['rol'] === 'profesor' ? '/cursos' : '/usuarios';
                header('Location: ' . BASE_URL . $rutaRetorno);
                exit;
            }
            
            // Si es profesor, verificar que el usuario a editar sea un alumno
            if ($_SESSION['rol'] === 'profesor' && $usuario['rol'] !== 'alumno') {
                $_SESSION['error'] = 'Solo puede editar usuarios con rol de alumno.';
                header('Location: ' . BASE_URL . '/cursos');
                exit;
            }
            
            $datos = [
                'titulo' => 'Editar Usuario',
                'usuario' => $usuario,
                'csrf_token' => $this->sesion->generarTokenCSRF(),
                'es_admin_principal' => $this->usuarioModelo->esAdministradorPrincipal($id),
                'es_usuario_actual' => ($id == $_SESSION['id_usuario'])
            ];
            
            // Cargar la vista correspondiente según el rol
            if ($_SESSION['rol'] === 'profesor') {
                require_once APP_PATH . '/vistas/profesor/usuarios/editar.php';
            } else {
                require_once APP_PATH . '/vistas/admin/usuarios/editar.php';
            }
            
        } catch (Exception $e) {
            error_log("Error al cargar usuario para editar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el usuario.';
            $rutaRetorno = $_SESSION['rol'] === 'profesor' ? '/cursos' : '/usuarios';
            header('Location: ' . BASE_URL . $rutaRetorno);
            exit;
        }
    }
    
    /**
     * Procesar actualización de usuario
     * 
     * @param int|null $id ID del usuario a actualizar
     * @return void
     */
    public function actualizar($id = null) {
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'ID de usuario no especificado o inválido.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '', 'usuarios/editar/' . $id);
            
            // Validar campos obligatorios
            $this->validarCamposObligatorios(['nombre', 'apellidos', 'correo', 'rol'], 'usuarios/editar/' . $id);
            
            // Obtener usuario existente
            $usuarioExistente = $this->usuarioModelo->buscarPorId($id, false);
            if (!$usuarioExistente) {
                $_SESSION['error'] = 'Usuario no encontrado.';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Verificar permisos especiales
            $esUsuarioActual = ($id == $_SESSION['id_usuario']);
            $esAdminPrincipal = $this->usuarioModelo->esAdministradorPrincipal($id);
            
            // Preparar datos para actualización
            $datos = $this->prepararDatosActualizacion($usuarioExistente, $esUsuarioActual, $esAdminPrincipal);
            
            // Verificar correo único si cambió
            if ($datos['correo'] !== $usuarioExistente['correo']) {
                if ($this->usuarioModelo->existeCorreo($datos['correo'], $id)) {
                    $_SESSION['error'] = 'Ya existe otro usuario con ese correo electrónico.';
                    header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                    exit;
                }
            }
            
            // Procesar nueva foto si se subió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $nuevaFoto = $this->procesarFotoPerfil($_FILES['foto']);
                if ($nuevaFoto) {
                    // Eliminar foto anterior si existe
                    $this->eliminarFotoAnterior($usuarioExistente['foto']);
                    $datos['foto'] = $nuevaFoto;
                }
            }
            
            // Actualizar usuario
            $resultado = $this->usuarioModelo->actualizar($id, $datos);
            
            // Gestionar asignación de curso para alumnos
            if ($resultado && $datos['rol'] === 'alumno') {
                require_once APP_PATH . '/modelos/curso_modelo.php';
                $cursoModelo = new Curso();
                
                // Log para depuración
                error_log("Actualizando usuario ID: {$id}, rol: {$datos['rol']}", 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                error_log("Datos curso: " . json_encode($datos['nuevoCursoAsignado'] ?? 'no_definido'), 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                
                // Primero obtener el curso actual
                $cursoActual = $cursoModelo->obtenerCursoDeAlumno($id);
                error_log("Curso actual: " . json_encode($cursoActual), 0, 
                          __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                
                $nuevoCursoAsignado = isset($datos['nuevoCursoAsignado']) ? (int)$datos['nuevoCursoAsignado'] : null;
                
                // Siempre asignar el curso seleccionado si hay uno
                if ($nuevoCursoAsignado) {
                    // Si había un curso anterior y es diferente, desasignar primero
                    if ($cursoActual && $cursoActual != $nuevoCursoAsignado) {
                        $desasignado = $cursoModelo->desasignarAlumno($cursoActual, $id);
                        error_log("Desasignando curso anterior {$cursoActual}: " . ($desasignado ? "OK" : "FALLO"), 0, 
                                  __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                    }
                    
                    // Asignar el nuevo curso
                    $asignado = $cursoModelo->asignarAlumno($nuevoCursoAsignado, $id);
                    error_log("Asignando nuevo curso {$nuevoCursoAsignado}: " . ($asignado ? "OK" : "FALLO"), 0, 
                              __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                } else if ($cursoActual && !$nuevoCursoAsignado) {
                    // Si había curso pero ahora no se ha seleccionado ninguno, desasignar
                    $desasignado = $cursoModelo->desasignarAlumno($cursoActual, $id);
                    error_log("Eliminando asignación curso {$cursoActual}: " . ($desasignado ? "OK" : "FALLO"), 0, 
                              __DIR__ . "/../../almacenamiento/logs/app/usuarios_debug.log");
                }
            }
            
            if ($resultado) {
                try {
                    // Intentar registrar la actividad, pero no interrumpir si falla
                    $this->registrarActividadActualizacion($id, $datos);
                } catch (Exception $e) {
                    error_log("Error al registrar actividad de actualización: " . $e->getMessage());
                    // Continuar a pesar del error
                }
                
                $_SESSION['exito'] = 'Usuario actualizado exitosamente.';
                $rutaRetorno = $_SESSION['rol'] === 'profesor' ? '/cursos' : '/usuarios';
                header('Location: ' . BASE_URL . $rutaRetorno);
                exit;
            } else {
                $_SESSION['error'] = 'No se realizaron cambios en el usuario.';
                header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                exit;
            }
            
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el usuario: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            exit;
        }
    }
    
    /**
     * Preparar datos para actualización de usuario
     * 
     * @param array $usuarioExistente Datos actuales del usuario
     * @param bool $esUsuarioActual Si es el usuario que realiza la edición
     * @param bool $esAdminPrincipal Si es el administrador principal
     * @return array Datos para actualizar
     * @throws Exception Si hay errores de validación
     */
    private function prepararDatosActualizacion($usuarioExistente, $esUsuarioActual, $esAdminPrincipal) {
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'apellidos' => trim($_POST['apellidos']),
            'correo' => filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL)
        ];
        
        // Validar email
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El correo electrónico no tiene un formato válido.');
        }
        
        // Manejar el rol - no permitir cambios para el admin principal o el propio usuario
        if ($esAdminPrincipal) {
            // El administrador principal siempre es admin
            $datos['rol'] = 'admin';
        } else if ($esUsuarioActual) {
            // El usuario actual no puede cambiar su propio rol
            $datos['rol'] = $usuarioExistente['rol'];
        } else {
            // Para otros usuarios, permitir cambio de rol
            $datos['rol'] = $_POST['rol'];
            
            // Validar rol
            if (!in_array($datos['rol'], ['admin', 'profesor', 'alumno'])) {
                throw new Exception('El rol seleccionado no es válido.');
            }
        }
        
        // Manejar asignación de curso para alumnos
        if ($datos['rol'] === 'alumno') {
            // Para actualizar la relación en curso_alumno en lugar de curso_asignado
            require_once APP_PATH . '/modelos/curso_modelo.php';
            $cursoModelo = new Curso();
            
            // Guardar el nuevo curso asignado para actualizar después
            if (!empty($_POST['curso_asignado'])) {
                $nuevoCursoAsignado = (int)$_POST['curso_asignado'];
                // Añadimos a $datos para usarlo en la función actualizar
                $datos['nuevoCursoAsignado'] = $nuevoCursoAsignado;
            } else {
                $datos['nuevoCursoAsignado'] = null;
            }
            
            // El valor curso_asignado ya no se usa pero mantenemos para compatibilidad
            $datos['curso_asignado'] = null;
        } else {
            // Para roles profesor y admin, no aplica
            $datos['curso_asignado'] = null;
            $datos['nuevoCursoAsignado'] = null;
        }
        
        // Manejar el estado activo - solo actualizarlo cuando no sea el admin principal ni el usuario actual
        if (!$esUsuarioActual && !$esAdminPrincipal) {
            $datos['activo'] = isset($_POST['activo']) ? 1 : 0;
        } else if ($esAdminPrincipal) {
            // Administrador principal siempre activo
            $datos['activo'] = 1;
        }
        
        // Si se proporciona nueva contraseña, validar y actualizar
        if (!empty($_POST['contrasena'])) {
            $this->validarCoincidenciaContrasenas(
                $_POST['contrasena'], 
                $_POST['confirmar_contrasena'] ?? '', 
                'usuarios/editar/' . $usuarioExistente['id_usuario']
            );
            $datos['contrasena'] = $_POST['contrasena'];
        }
        
        return $datos;
    }
    
    /**
     * Eliminar foto de perfil anterior
     * 
     * @param string|null $rutaFoto Ruta de la foto a eliminar
     * @return bool Si se eliminó correctamente
     */
    private function eliminarFotoAnterior($rutaFoto) {
        if (empty($rutaFoto)) {
            return false;
        }
        
        // Obtener la ruta completa del archivo
        $rutaCompleta = STORAGE_PATH . '/subidas/imagenes/' . $rutaFoto;
        
        // Verificar que el archivo existe y eliminarlo
        if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
            return unlink($rutaCompleta);
        }
        
        return false;
    }
    
    /**
     * Registrar actividad de actualización de usuario
     * 
     * @param int $idUsuario ID del usuario actualizado
     * @param array $datos Datos del usuario
     * @return void
     */
    private function registrarActividadActualizacion($idUsuario, $datos) {
        try {
            if (!isset($this->registroActividad)) {
                require_once APP_PATH . '/modelos/registro_actividad_modelo.php';
                $this->registroActividad = new RegistroActividad();
            }
            
            $this->registroActividad->registrar(
                $_SESSION['id_usuario'],
                'actualizar_usuario',
                "Usuario actualizado: {$datos['apellidos']}, {$datos['nombre']} ({$datos['correo']})",
                'usuarios',
                $idUsuario
            );
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar actividad: " . $e->getMessage(), 0,
                      __DIR__ . "/../../almacenamiento/logs/app/usuarios_error.log");
            return false;
        }
    }
    
    /**
     * Desactivar usuario
     * 
     * @param int|null $id ID del usuario a desactivar
     * @return void
     */
    public function desactivar($id = null) {
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'ID de usuario no especificado o inválido.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '');
            
            // Verificar que el usuario exista
            $usuario = $this->usuarioModelo->buscarPorId($id, false);
            if (!$usuario) {
                throw new Exception('Usuario no encontrado.');
            }
            
            // Verificar si es administrador principal
            if ($this->usuarioModelo->esAdministradorPrincipal($id)) {
                throw new Exception('No se puede desactivar al administrador principal por seguridad del sistema.');
            }
            
            // No permitir desactivar el usuario actual
            if ($id == $_SESSION['id_usuario']) {
                throw new Exception('No puede desactivar su propio usuario.');
            }
            
            // Desactivar usuario
            $resultado = $this->usuarioModelo->desactivar($id);
            
            if ($resultado) {
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'desactivar_usuario',
                    "Usuario desactivado: {$usuario['apellidos']}, {$usuario['nombre']} ({$usuario['correo']})",
                    'usuarios',
                    $id
                );
                
                $_SESSION['exito'] = 'Usuario desactivado exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al desactivar el usuario.';
            }
            
        } catch (Exception $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al desactivar el usuario: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Activar usuario
     * 
     * @param int|null $id ID del usuario a activar
     * @return void
     */
    public function activar($id = null) {
        if (!$id || !is_numeric($id)) {
            $_SESSION['error'] = 'ID de usuario no especificado o inválido.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '');
            
            // Verificar que el usuario exista
            $usuario = $this->usuarioModelo->buscarPorId($id, false);
            if (!$usuario) {
                throw new Exception('Usuario no encontrado.');
            }
            
            // Activar usuario
            if ($this->usuarioModelo->activar($id)) {
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'activar_usuario',
                    "Usuario activado: {$usuario['apellidos']}, {$usuario['nombre']} ({$usuario['correo']})",
                    'usuarios',
                    $id
                );
                
                $_SESSION['exito'] = 'Usuario activado exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al activar el usuario.';
            }
            
        } catch (Exception $e) {
            error_log("Error al activar usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al activar el usuario: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Acciones masivas sobre usuarios
     * 
     * @return void
     */
    public function accionMasiva() {
        try {
            // Verificar método POST
            $this->verificarMetodoPost();
            
            // Verificar token CSRF
            $this->verificarTokenCSRF($_POST['csrf_token'] ?? '');
            
            // Verificar que existan IDs y acción seleccionados
            if (!isset($_POST['usuarios']) || !is_array($_POST['usuarios']) || empty($_POST['usuarios'])) {
                throw new Exception('No se seleccionaron usuarios.');
            }
            
            if (!isset($_POST['accion']) || empty($_POST['accion'])) {
                throw new Exception('No se seleccionó ninguna acción.');
            }
            
            $accion = $_POST['accion'];
            $idsUsuarios = array_map('intval', $_POST['usuarios']);
            
            // Verificar acciones válidas
            if (!in_array($accion, ['activar', 'desactivar'])) {
                throw new Exception('Acción inválida.');
            }
            
            $resultados = $this->procesarAccionMasiva($accion, $idsUsuarios);
            
            if ($resultados['exito'] > 0) {
                $_SESSION['exito'] = "Se " . ($accion == 'activar' ? 'activaron' : 'desactivaron') . 
                                     " exitosamente {$resultados['exito']} usuarios.";
            }
            
            if ($resultados['error'] > 0) {
                $_SESSION['advertencia'] = "No se pudieron " . ($accion == 'activar' ? 'activar' : 'desactivar') . 
                                           " {$resultados['error']} usuarios.";
            }
            
        } catch (Exception $e) {
            error_log("Error en acción masiva: " . $e->getMessage());
            $_SESSION['error'] = 'Error al procesar la acción: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Procesar acción masiva sobre usuarios
     * 
     * @param string $accion Acción a realizar ('activar' o 'desactivar')
     * @param array $idsUsuarios IDs de usuarios a procesar
     * @return array Resultados [exito, error]
     */
    private function procesarAccionMasiva($accion, $idsUsuarios) {
        $resultados = [
            'exito' => 0,
            'error' => 0
        ];
        
        $idUsuarioActual = $_SESSION['id_usuario'];
        
        foreach ($idsUsuarios as $idUsuario) {
            try {
                $usuario = $this->usuarioModelo->buscarPorId($idUsuario, false);
                
                // Saltear si no existe
                if (!$usuario) {
                    $resultados['error']++;
                    continue;
                }
                
                // No procesar el usuario actual ni administrador principal
                if ($idUsuario == $idUsuarioActual || $this->usuarioModelo->esAdministradorPrincipal($idUsuario)) {
                    $resultados['error']++;
                    continue;
                }
                
                $exito = false;
                
                if ($accion === 'activar') {
                    $exito = $this->usuarioModelo->activar($idUsuario);
                    if ($exito) {
                        $this->registroActividad->registrar(
                            $idUsuarioActual,
                            'activar_usuario',
                            "Usuario activado (masivo): {$usuario['apellidos']}, {$usuario['nombre']} ({$usuario['correo']})",
                            'usuarios',
                            $idUsuario
                        );
                    }
                } else if ($accion === 'desactivar') {
                    $exito = $this->usuarioModelo->desactivar($idUsuario);
                    if ($exito) {
                        $this->registroActividad->registrar(
                            $idUsuarioActual,
                            'desactivar_usuario',
                            "Usuario desactivado (masivo): {$usuario['apellidos']}, {$usuario['nombre']} ({$usuario['correo']})",
                            'usuarios',
                            $idUsuario
                        );
                    }
                }
                
                if ($exito) {
                    $resultados['exito']++;
                } else {
                    $resultados['error']++;
                }
                
            } catch (Exception $e) {
                error_log("Error en acción masiva para usuario ID $idUsuario: " . $e->getMessage());
                $resultados['error']++;
            }
        }
        
        return $resultados;
    }
    
    /**
     * Muestra los alumnos asignados a los cursos del profesor
     * 
     * @return void
     */
    private function mostrarAlumnosDelProfesor() {
        try {
            // Cargar modelo de curso
            require_once APP_PATH . '/modelos/curso_modelo.php';
            $cursoModelo = new Curso();
            
            // Obtener alumnos asignados a cursos del profesor
            $alumnosAsignados = $cursoModelo->obtenerAlumnosPorProfesor($_SESSION['id_usuario']);
            
            // Obtener solo alumnos sin asignar a ningún curso (no todos los alumnos)
            $alumnosSinAsignar = $cursoModelo->obtenerAlumnosSinAsignar();
            
            // Combinar: alumnos del profesor + alumnos disponibles para asignar
            $alumnos = array_merge($alumnosAsignados, $alumnosSinAsignar);
            
            // Calcular estadísticas
            $cursosConAlumnos = count(array_unique(array_column($alumnosAsignados, 'id_curso')));
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Mis Alumnos',
                'alumnos' => $alumnos,
                'total_alumnos' => count($alumnos),
                'alumnos_asignados' => count($alumnosAsignados),
                'alumnos_sin_asignar' => count($alumnosSinAsignar),
                'cursos_con_alumnos' => $cursosConAlumnos,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Cargar vista específica para profesor
            require_once APP_PATH . '/vistas/parciales/head_profesor.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/profesor/usuarios/mis_alumnos.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
            
        } catch (Exception $e) {
            error_log("Error al cargar alumnos del profesor: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la lista de alumnos';
            
            // Mostrar vista vacía
            $this->mostrarListaVaciaProfesor();
        }
    }

    /**
     * Muestra una lista vacía para profesores en caso de error
     * 
     * @return void
     */
    private function mostrarListaVaciaProfesor() {
        $datos = [
            'titulo' => 'Mis Alumnos',
            'alumnos' => [],
            'total_alumnos' => 0,
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        require_once APP_PATH . '/vistas/parciales/head_profesor.php';
        echo '<body class="bg-light">';
        require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
        echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
        
        require_once APP_PATH . '/vistas/profesor/usuarios/mis_alumnos.php';
        
        echo '</div></div></div>';
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
    }
    
    /**
     * Elimina un usuario del sistema
     * 
     * @param int|null $id ID del usuario a eliminar
     * @return void
     */
    public function eliminar($id = null) {
        try {
            // Validar ID del usuario
            if (!$id || !is_numeric($id)) {
                $_SESSION['error'] = 'ID de usuario inválido';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            $id = (int)$id;
            
            // Verificar si es una petición POST con token CSRF válido
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Método de petición inválido';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Verificar token CSRF
            if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Verificar que el usuario existe
            $usuario = $this->usuarioModelo->buscarPorId($id, false);
            if (!$usuario) {
                $_SESSION['error'] = 'El usuario no existe';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Si es profesor, solo puede eliminar alumnos
            if ($_SESSION['rol'] === 'profesor' && $usuario['rol'] !== 'alumno') {
                $_SESSION['error'] = 'Solo puedes eliminar alumnos';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // No permitir eliminar el propio usuario
            if ($id == $_SESSION['id_usuario']) {
                $_SESSION['error'] = 'No puedes eliminar tu propio usuario';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Intentar eliminar el usuario
            if ($this->usuarioModelo->eliminar($id)) {
                // Registrar la actividad
                $this->registroActividad->registrar([
                    'id_usuario' => $_SESSION['id_usuario'],
                    'actividad' => 'eliminar_usuario',
                    'detalles' => json_encode([
                        'usuario_eliminado' => $usuario['nombre'] . ' ' . $usuario['apellidos'],
                        'correo_eliminado' => $usuario['correo'],
                        'rol_eliminado' => $usuario['rol']
                    ]),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Desconocida',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido'
                ]);
                
                $_SESSION['exito'] = 'Usuario eliminado correctamente';
            } else {
                $_SESSION['error'] = 'No se pudo eliminar el usuario';
            }
            
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }
        
        // Redirigir según el rol del usuario
        if ($_SESSION['rol'] === 'profesor') {
            header('Location: ' . BASE_URL . '/usuarios');
        } else {
            header('Location: ' . BASE_URL . '/usuarios');
        }
        exit;
    }
    
    /**
     * Exportar usuarios filtrados a CSV/Excel
     */
    public function exportar() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para exportar usuarios';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }

            // Obtener filtros de la URL
            $filtros = $this->obtenerFiltrosBusqueda();
            
            // Obtener todos los usuarios según filtros (sin paginación)
            $usuarios = $this->usuarioModelo->listar($filtros, 9999999, 0);
            
            if (empty($usuarios)) {
                $_SESSION['error'] = 'No hay usuarios para exportar con los filtros aplicados';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }

            // Configurar headers para descarga CSV
            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
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
                'Nombre',
                'Apellidos', 
                'Correo',
                'Rol',
                'Estado',
                'Fecha Registro',
                'Último Acceso'
            ], ';');

            // Datos de usuarios
            foreach ($usuarios as $usuario) {
                fputcsv($output, [
                    $usuario['id_usuario'],
                    $usuario['nombre'],
                    $usuario['apellidos'],
                    $usuario['correo'],
                    ucfirst($usuario['rol']),
                    $usuario['activo'] ? 'Activo' : 'Inactivo',
                    $usuario['fecha_registro'],
                    $usuario['ultimo_acceso'] ?? 'Nunca'
                ], ';');
            }

            fclose($output);
            
            // Registrar actividad
            $this->registroActividad->registrar(
                $_SESSION['id_usuario'],
                'exportar_usuarios',
                'Exportación de ' . count($usuarios) . ' usuarios',
                'usuarios'
            );
            
        } catch (Exception $e) {
            error_log("Error al exportar usuarios: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar usuarios: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }

    /**
     * Mostrar página de importación de usuarios
     */
    public function importar() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para importar usuarios';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }

            $datos = [
                'titulo' => 'Importar Usuarios',
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];

            // Cargar vista
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/usuarios/importar.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            echo '</body></html>';
            
        } catch (Exception $e) {
            error_log("Error en página de importación: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la página de importación';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }

    /**
     * Procesar importación de usuarios desde CSV
     */
    public function procesarImportacion() {
        try {
            // Verificar permisos y método
            if ($_SESSION['rol'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Acceso no autorizado';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }

            // Verificar token CSRF
            if (!$this->sesion->validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Token de seguridad inválido';
                header('Location: ' . BASE_URL . '/usuarios/importar');
                exit;
            }

            // Verificar que se subió un archivo
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'No se pudo cargar el archivo';
                header('Location: ' . BASE_URL . '/usuarios/importar');
                exit;
            }

            // Validar tipo de archivo
            $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
            if ($extension !== 'csv') {
                $_SESSION['error'] = 'Solo se permiten archivos CSV';
                header('Location: ' . BASE_URL . '/usuarios/importar');
                exit;
            }

            // Procesar archivo CSV
            $archivo = $_FILES['archivo']['tmp_name'];
            $handle = fopen($archivo, 'r');
            
            if (!$handle) {
                $_SESSION['error'] = 'No se pudo leer el archivo';
                header('Location: ' . BASE_URL . '/usuarios/importar');
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

                    $nombre = trim($datos[0]);
                    $apellidos = trim($datos[1]);
                    $correo = trim($datos[2]);
                    $rol = trim(strtolower($datos[3]));

                    // Validaciones básicas
                    if (empty($nombre) || empty($apellidos) || empty($correo) || empty($rol)) {
                        $errores++;
                        continue;
                    }

                    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                        $errores++;
                        continue;
                    }

                    if (!in_array($rol, ['admin', 'profesor', 'alumno'])) {
                        $errores++;
                        continue;
                    }

                    // Verificar si el usuario ya existe
                    if ($this->usuarioModelo->existeCorreo($correo)) {
                        $errores++;
                        continue;
                    }

                    // Crear usuario
                    $password = bin2hex(random_bytes(8)); // Password temporal
                    $datosUsuario = [
                        'nombre' => $nombre,
                        'apellidos' => $apellidos,
                        'correo' => $correo,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'rol' => $rol,
                        'activo' => 1
                    ];

                    if ($this->usuarioModelo->crear($datosUsuario)) {
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
            $mensaje = "Importación completada: $importados usuarios importados";
            if ($errores > 0) {
                $mensaje .= ", $errores errores";
            }

            $_SESSION['exito'] = $mensaje;

            // Registrar actividad
            $this->registroActividad->registrar(
                $_SESSION['id_usuario'],
                'importar_usuarios',
                "Importados: $importados, Errores: $errores",
                'usuarios'
            );

            header('Location: ' . BASE_URL . '/usuarios');
            exit;

        } catch (Exception $e) {
            error_log("Error procesando importación: " . $e->getMessage());
            $_SESSION['error'] = 'Error procesando importación: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/usuarios/importar');
            exit;
        }
    }

    /**
     * Mostrar estadísticas de usuarios
     */
    public function estadisticas() {
        try {
            // Verificar permisos
            if ($_SESSION['rol'] !== 'admin') {
                $_SESSION['error'] = 'No tienes permisos para ver estadísticas';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }

            // Obtener estadísticas desde el modelo
            $estadisticas = $this->usuarioModelo->obtenerEstadisticas();

            $datos = [
                'titulo' => 'Estadísticas de Usuarios',
                'estadisticas' => $estadisticas,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];

            // Cargar vista
            require_once APP_PATH . '/vistas/parciales/head_admin.php';
            echo '<body class="bg-light">';
            require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
            echo '<div class="container-fluid mt-4"><div class="row"><div class="col-12">';
            
            require_once APP_PATH . '/vistas/admin/usuarios/estadisticas.php';
            
            echo '</div></div></div>';
            require_once APP_PATH . '/vistas/parciales/footer_admin.php';
            require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
            echo '</body></html>';
            
        } catch (Exception $e) {
            error_log("Error en estadísticas: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar estadísticas';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
}
