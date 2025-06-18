<?php
/**
 * Controlador de Usuarios - AUTOEXAM2
 * 
 * Gestiona el CRUD completo de usuarios del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
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
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: ' . BASE_URL . '/error/acceso');
            exit;
        }
    }
    
    /**
     * Método predeterminado - Lista usuarios con paginación
     */
    public function index() {
        try {
            // Parámetros de paginación y filtros
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 15;
            
            // Validar el campo de búsqueda
            $buscar = '';
            if (isset($_GET['buscar'])) {
                // Simplemente obtener el valor limpio sin sanitizar aquí
                // La sanitización se realizará en el modelo
                $buscar = trim($_GET['buscar']);
                
                // Asegurarse de que si hay texto, tenga al menos 3 caracteres
                if (strlen($buscar) > 0 && strlen($buscar) < 3) {
                    throw new Exception("La búsqueda debe contener al menos 3 caracteres.");
                }
            }
            
            $rol = isset($_GET['rol']) ? $_GET['rol'] : '';
            $activo = isset($_GET['activo']) ? $_GET['activo'] : '';
            
            // Validar página mínima y por página
            if ($pagina < 1) $pagina = 1;
            if (!in_array($porPagina, [5, 10, 15, 20, 50, 100])) $porPagina = 15;
            
            // Calcular offset
            $offset = ($pagina - 1) * $porPagina;
            
            // Preparar filtros para el modelo
            $filtros = [];
            if (!empty($buscar)) {
                $filtros['buscar'] = $buscar;
            }
            if (!empty($rol) && in_array($rol, ['admin', 'profesor', 'alumno'])) {
                $filtros['rol'] = $rol;
            }
            if ($activo !== '') {
                $filtros['activo'] = (int)$activo;
            }
            
            // Obtener usuarios
            $usuarios = $this->usuarioModelo->listar($filtros, $porPagina, $offset);
            
            // Contar total para paginación (simplificado)
            $totalUsuarios = $this->contarUsuarios($filtros);
            $totalPaginas = ceil($totalUsuarios / $porPagina);
            
            // Datos para la vista
            $datos = [
                'titulo' => 'Gestión de Usuarios',
                'usuarios' => $usuarios,
                'pagina_actual' => $pagina,
                'total_paginas' => $totalPaginas,
                'por_pagina' => $porPagina,
                'total_usuarios' => $totalUsuarios,
                'filtros' => [
                    'buscar' => $buscar,
                    'rol' => $rol,
                    'activo' => $activo
                ],
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Cargar vista
            require_once APP_PATH . '/vistas/admin/usuarios/listar.php';
            
        } catch (Exception $e) {
            // Registro detallado del error para fines de depuración
            error_log("Error al listar usuarios: " . $e->getMessage());
            
            // Mostrar error con detalles suficientes para diagnosticar sin exponer información sensible
            $_SESSION['error'] = 'Error al cargar la lista de usuarios: Error al consultar usuarios';
            
            // Crear datos vacíos para la vista
            $datos = [
                'titulo' => 'Gestión de Usuarios',
                'usuarios' => [],
                'pagina_actual' => 1,
                'total_paginas' => 0,
                'por_pagina' => $porPagina,
                'total_usuarios' => 0,
                'filtros' => [
                    'buscar' => isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '',
                    'rol' => isset($_GET['rol']) ? htmlspecialchars($_GET['rol']) : '',
                    'activo' => isset($_GET['activo']) ? htmlspecialchars($_GET['activo']) : ''
                ],
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            // Cargar la vista con los datos vacíos y el mensaje de error
            require_once APP_PATH . '/vistas/admin/usuarios/listar.php';
            exit;
        }
    }
    
    /**
     * Mostrar formulario de creación de usuario
     */
    public function crear() {
        $datos = [
            'titulo' => 'Crear Usuario',
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        require_once APP_PATH . '/vistas/admin/usuarios/crear.php';
    }
    
    /**
     * Procesar creación de usuario
     */
    public function guardar() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios/crear');
            exit;
        }
        
        // Validar campos obligatorios
        $camposRequeridos = ['nombre', 'apellidos', 'correo', 'contrasena', 'confirmar_contrasena', 'rol'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
                exit;
            }
        }

        // Validar que las contraseñas coincidan
        if ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            header('Location: ' . BASE_URL . '/usuarios/crear');
            exit;
        }
        
        // Sanitizar datos
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'apellidos' => trim($_POST['apellidos']),
            'correo' => filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL),
            'contrasena' => $_POST['contrasena'],
            'rol' => $_POST['rol'],
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'curso_asignado' => !empty($_POST['curso_asignado']) ? (int)$_POST['curso_asignado'] : null
        ];
        
        // Validar email
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no tiene un formato válido.';
            header('Location: ' . BASE_URL . '/usuarios/crear');
            exit;
        }
        
        // Validar rol
        if (!in_array($datos['rol'], ['admin', 'profesor', 'alumno'])) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            header('Location: ' . BASE_URL . '/usuarios/crear');
            exit;
        }
        
        try {
            // Procesar foto si se subió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    $datos['foto'] = $this->procesarFotoPerfil($_FILES['foto']);
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Error con la foto: ' . $e->getMessage();
                    header('Location: ' . BASE_URL . '/usuarios/crear');
                    exit;
                }
            }
            
            // Verificar si el correo ya existe
            if ($this->usuarioModelo->existeCorreo($datos['correo'])) {
                $_SESSION['error'] = 'Ya existe un usuario con ese correo electrónico.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
                exit;
            }
            
            // Crear usuario
            $idUsuario = $this->usuarioModelo->crear($datos);
            
            if ($idUsuario) {
                // Registrar actividad
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'crear_usuario',
                    "Usuario creado: {$datos['apellidos']}, {$datos['nombre']} ({$datos['correo']})",
                    'usuarios',
                    $idUsuario
                );
                
                $_SESSION['exito'] = 'Usuario creado exitosamente.';
                header('Location: ' . BASE_URL . '/usuarios');
            } else {
                $_SESSION['error'] = 'Error al crear el usuario.';
                header('Location: ' . BASE_URL . '/usuarios/crear');
            }
            
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al crear el usuario.';
            header('Location: ' . BASE_URL . '/usuarios/crear');
        }
        
        exit;
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function editar($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            // Buscar usuario incluyendo inactivos
            $usuario = $this->usuarioModelo->buscarPorId($id, false);
            
            if (!$usuario) {
                $_SESSION['error'] = 'Usuario no encontrado.';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            $datos = [
                'titulo' => 'Editar Usuario',
                'usuario' => $usuario,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            require_once APP_PATH . '/vistas/admin/usuarios/editar.php';
            
        } catch (Exception $e) {
            error_log("Error al cargar usuario para editar: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el usuario.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
    
    /**
     * Procesar actualización de usuario
     */
    public function actualizar($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            exit;
        }
        
        // Validar campos obligatorios
        $camposRequeridos = ['nombre', 'apellidos', 'correo', 'rol'];
        foreach ($camposRequeridos as $campo) {
            if (empty($_POST[$campo])) {
                $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
                header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                exit;
            }
        }
        
        // Verificar si es el usuario que realiza la edición o el administrador principal
        $usuarioExistente = $this->usuarioModelo->buscarPorId($id, false);
        if (!$usuarioExistente) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        $esUsuarioActual = ($id == $_SESSION['id_usuario']);
        $esAdminPrincipal = ($usuarioExistente['id_usuario'] == 1 || $usuarioExistente['rol'] == 'admin' && $usuarioExistente['correo'] == 'no_contestar@autoexam.epla.es');
        
        // Sanitizar datos
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'apellidos' => trim($_POST['apellidos']),
            'correo' => filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL)
        ];
        
        // Manejar el rol - no permitir cambios para el administrador principal o el propio usuario
        if ($esAdminPrincipal) {
            // Forzar que el administrador principal mantenga el rol admin
            $datos['rol'] = 'admin';
        } else if ($esUsuarioActual) {
            // El usuario actual no puede cambiar su rol, mantener el actual
            $datos['rol'] = $usuarioExistente['rol'];
        } else {
            // Para otros usuarios, permitir cambio de rol
            $datos['rol'] = $_POST['rol'];
        }
        
        // Añadir curso asignado si es aplicable
        if (!empty($_POST['curso_asignado'])) {
            $datos['curso_asignado'] = (int)$_POST['curso_asignado'];
        } else {
            $datos['curso_asignado'] = null;
        }
        
        // Manejar el estado activo - solo actualizarlo cuando no sea el admin principal ni el usuario actual
        if (!$esUsuarioActual && !$esAdminPrincipal) {
            $datos['activo'] = isset($_POST['activo']) ? 1 : 0;
        } else if ($esAdminPrincipal) {
            // Si es un administrador principal, forzar a que esté siempre activo
            $datos['activo'] = 1;
        }
        // Si es el usuario actual, no modificamos el estado activo, dejamos que permanezca como está
        
        // Si se proporciona nueva contraseña, validar confirmación
        if (!empty($_POST['contrasena'])) {
            if (empty($_POST['confirmar_contrasena'])) {
                $_SESSION['error'] = 'Debe confirmar la nueva contraseña.';
                header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                exit;
            }
            
            if ($_POST['contrasena'] !== $_POST['confirmar_contrasena']) {
                $_SESSION['error'] = 'Las contraseñas no coinciden.';
                header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                exit;
            }
            
            $datos['contrasena'] = $_POST['contrasena'];
        }
        
        // Validaciones
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no tiene un formato válido.';
            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            exit;
        }
        
        if (!in_array($datos['rol'], ['admin', 'profesor', 'alumno'])) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            exit;
        }
        
        try {
            // Ya verificamos si el usuario existe previamente para determinar si es el admin principal
            // Si el correo cambió, verificar que no exista otro usuario con ese correo
            if ($datos['correo'] !== $usuarioExistente['correo']) {
                if ($this->usuarioModelo->existeCorreo($datos['correo'])) {
                    $_SESSION['error'] = 'Ya existe otro usuario con ese correo electrónico.';
                    header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                    exit;
                }
            }
            
            // Procesar nueva foto si se subió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    $nuevaFoto = $this->procesarFotoPerfil($_FILES['foto']);
                    if ($nuevaFoto) {
                        // Eliminar foto anterior si existe
                        $this->eliminarFotoAnterior($usuarioExistente['foto']);
                        $datos['foto'] = $nuevaFoto;
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Error con la foto: ' . $e->getMessage();
                    header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
                    exit;
                }
            }
            
            // Actualizar usuario
            $resultado = $this->usuarioModelo->actualizar($id, $datos);
            
            if ($resultado) {
                // Registrar actividad
                $this->registroActividad->registrar(
                    $_SESSION['id_usuario'],
                    'actualizar_usuario',
                    "Usuario actualizado: {$datos['apellidos']}, {$datos['nombre']} ({$datos['correo']})",
                    'usuarios',
                    $id
                );
                
                $_SESSION['exito'] = 'Usuario actualizado exitosamente.';
                header('Location: ' . BASE_URL . '/usuarios');
            } else {
                $_SESSION['error'] = 'Error al actualizar el usuario.';
                header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
            }
            
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al actualizar el usuario.';
            header('Location: ' . BASE_URL . '/usuarios/editar/' . $id);
        }
        
        exit;
    }
    
    /**
     * Desactivar usuario
     */
    public function desactivar($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar que no se esté intentando desactivar al administrador principal
        // Usamos soloActivos=false para que busque incluso usuarios inactivos
        $usuario = $this->usuarioModelo->buscarPorId($id, false);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar si es administrador principal
        $esAdminPrincipal = ($usuario['id_usuario'] == 1 || 
                          ($usuario['rol'] == 'admin' && 
                           $usuario['correo'] == 'no_contestar@autoexam.epla.es'));
                           
        if ($esAdminPrincipal) {
            $_SESSION['error'] = 'No se puede desactivar al administrador principal por seguridad del sistema.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // No permitir desactivar el usuario actual
        if ($id == $_SESSION['id_usuario']) {
            $_SESSION['error'] = 'No puede desactivar su propio usuario.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            $resultado = $this->usuarioModelo->desactivar($id);
            
            if ($resultado) {
                $_SESSION['exito'] = 'Usuario desactivado exitosamente.';
            } else {
                $_SESSION['error'] = 'Error al desactivar el usuario.';
            }
            
        } catch (Exception $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            $_SESSION['error'] = 'Error al desactivar el usuario.';
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Activar usuario
     */
    public function activar($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar que el usuario exista
        // Usamos soloActivos=false para buscar incluso usuarios inactivos
        $usuario = $this->usuarioModelo->buscarPorId($id, false);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            if ($this->usuarioModelo->activar($id)) {
                // Registrar actividad
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
            $_SESSION['error'] = 'Error al activar el usuario.';
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Acciones masivas sobre usuarios
     */
    public function accionMasiva() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Validar acción
        $accion = $_POST['accion'] ?? '';
        $usuariosSeleccionados = $_POST['usuarios_seleccionados'] ?? [];
        
        if (empty($accion) || empty($usuariosSeleccionados)) {
            $_SESSION['error'] = 'Debe seleccionar usuarios y una acción.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        // Obtener usuario administrador principal para protegerlo
        $adminPrincipal = null;
        try {
            // Intentar obtener por ID 1 primero
            $adminPrincipal = $this->usuarioModelo->buscarPorId(1);
            if (!$adminPrincipal) {
                // Si no existe el ID 1, buscar por correo específico
                $adminPrincipal = $this->usuarioModelo->buscarPorCorreo('no_contestar@autoexam.epla.es');
            }
        } catch (Exception $e) {
            // Si hay error, continuar pero log del error
            error_log("Error al buscar admin principal: " . $e->getMessage());
        }
        
        // Filtrar IDs válidos y excluir usuario actual y admin principal
        $idsValidos = array_filter($usuariosSeleccionados, function($id) use ($adminPrincipal) {
            if (!is_numeric($id)) return false;
            if ($id == $_SESSION['id_usuario']) return false;
            
            // Proteger al administrador principal
            if ($adminPrincipal && ($id == $adminPrincipal['id_usuario'])) return false;
            
            return true;
        });
        
        if (empty($idsValidos)) {
            $_SESSION['error'] = 'No se seleccionaron usuarios válidos o solo se seleccionaron usuarios protegidos.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            $procesados = 0;
            $protegidos = count($usuariosSeleccionados) - count($idsValidos);
            
            switch ($accion) {
                case 'desactivar':
                    foreach ($idsValidos as $id) {
                        // Verificación adicional de seguridad
                        $usuario = $this->usuarioModelo->buscarPorId($id);
                        if ($usuario && ($usuario['id_usuario'] == 1 || 
                                       ($usuario['rol'] == 'admin' && 
                                        $usuario['correo'] == 'no_contestar@autoexam.epla.es'))) {
                            $protegidos++;
                            continue; // Proteger al admin principal
                        }
                        
                        if ($this->usuarioModelo->desactivar($id)) {
                            $procesados++;
                        }
                    }
                    
                    $mensaje = "Se desactivaron $procesados usuarios exitosamente.";
                    if ($protegidos > 0) {
                        $mensaje .= " ($protegidos usuarios protegidos no fueron modificados)";
                    }
                    $_SESSION['exito'] = $mensaje;
                    break;
                    
                case 'activar':
                    foreach ($idsValidos as $id) {
                        // Verificación adicional de seguridad
                        $usuario = $this->usuarioModelo->buscarPorId($id);
                        if ($usuario && ($usuario['id_usuario'] == 1 || 
                                       ($usuario['rol'] == 'admin' && 
                                        $usuario['correo'] == 'no_contestar@autoexam.epla.es'))) {
                            $protegidos++;
                            continue; // Proteger al admin principal
                        }
                        
                        if ($this->usuarioModelo->activar($id)) {
                            $procesados++;
                        }
                    }
                    
                    $mensaje = "Se activaron $procesados usuarios exitosamente.";
                    if ($protegidos > 0) {
                        $mensaje .= " ($protegidos usuarios protegidos no fueron modificados)";
                    }
                    $_SESSION['exito'] = $mensaje;
                    break;
                    
                case 'exportar':
                    $this->exportarUsuarios($idsValidos);
                    return; // No redirigir, se descarga archivo
                    
                default:
                    $_SESSION['error'] = 'Acción no válida.';
            }
            
        } catch (Exception $e) {
            error_log("Error en acción masiva: " . $e->getMessage());
            $_SESSION['error'] = 'Error al procesar la acción masiva.';
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Exportar usuarios a CSV
     */
    public function exportar() {
        // Obtener parámetros de filtro
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $rol = isset($_GET['rol']) ? $_GET['rol'] : '';
        $activo = isset($_GET['activo']) ? $_GET['activo'] : '';
        
        // Preparar filtros
        $filtros = [];
        if (!empty($buscar)) $filtros['buscar'] = $buscar;
        if (!empty($rol)) $filtros['rol'] = $rol;
        if ($activo !== '') $filtros['activo'] = (int)$activo;
        
        try {
            // Obtener todos los usuarios con filtros aplicados
            $usuarios = $this->usuarioModelo->listar($filtros, 10000, 0); // Límite alto para exportar todo
            
            $this->exportarUsuarios(null, $usuarios);
            
        } catch (Exception $e) {
            error_log("Error al exportar usuarios: " . $e->getMessage());
            $_SESSION['error'] = 'Error al exportar usuarios.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
    
    /**
     * Generar archivo CSV de usuarios
     */
    private function exportarUsuarios($ids = null, $usuarios = null) {
        try {
            // Si se proporcionaron IDs específicos, obtener esos usuarios
            if ($ids && !$usuarios) {
                $usuarios = [];
                foreach ($ids as $id) {
                    $usuario = $this->usuarioModelo->buscarPorId($id);
                    if ($usuario) {
                        $usuarios[] = $usuario;
                    }
                }
            }
            
            // Configurar headers para descarga
            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fwrite($output, "\xEF\xBB\xBF");
            
            // Headers del CSV
            fputcsv($output, [
                'ID',
                'Apellidos',
                'Nombre', 
                'Correo',
                'Rol',
                'Estado',
                'Curso Asignado',
                'Último Acceso'
            ]);
            
            // Datos
            foreach ($usuarios as $usuario) {
                fputcsv($output, [
                    $usuario['id_usuario'],
                    $usuario['apellidos'],
                    $usuario['nombre'],
                    $usuario['correo'],
                    ucfirst($usuario['rol']),
                    $usuario['activo'] ? 'Activo' : 'Inactivo',
                    $usuario['curso_asignado'] ?? 'Sin asignar',
                    $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca'
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error al generar CSV: " . $e->getMessage());
            $_SESSION['error'] = 'Error al generar el archivo de exportación.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
    
    /**
     * Contar usuarios con filtros aplicados
     */
    private function contarUsuarios($filtros = []) {
        try {
            // Construir la consulta base
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE 1=1";
            $valores = [];
            
            // Aplicar filtro de rol
            if (isset($filtros['rol']) && !empty($filtros['rol'])) {
                $sql .= " AND rol = ?";
                $valores[] = $filtros['rol'];
            }
            
            // Aplicar filtro de estado activo/inactivo
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $sql .= " AND activo = ?";
                $valores[] = (int)$filtros['activo'];
            }
            
            // Aplicar búsqueda por nombre, apellidos o correo
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $terminoBusqueda = trim($filtros['buscar']);
                if (!empty($terminoBusqueda)) {
                    // Construir condición LIKE con parámetros posicionales
                    $sql .= " AND (apellidos LIKE ? OR nombre LIKE ? OR correo LIKE ?)";
                    $termino = '%' . $terminoBusqueda . '%';
                    $valores[] = $termino;
                    $valores[] = $termino;
                    $valores[] = $termino;
                }
            }
            
            // Preparar y ejecutar la consulta
            $stmt = $this->usuarioModelo->getConexion()->prepare($sql);
            $stmt->execute($valores);
            
            // Obtener resultado
            $resultado = $stmt->fetch();
            return $resultado['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Error al contar usuarios: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Valores: " . print_r($valores, true));
            return 0;
        }
    }
    
    /**
     * Método para cerrar sesión
     */
    public function cerrarSesion() {
        $this->sesion->cerrarSesion();
        header('Location: ' . BASE_URL . '/autenticacion/login');
        exit;
    }
    
    /**
     * Ver historial de cambios de un usuario
     */
    public function historial($id = null) {
        if (!$id) {
            $_SESSION['error'] = 'ID de usuario no especificado.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
        
        try {
            // Obtener datos del usuario
            $usuario = $this->usuarioModelo->buscarPorId($id);
            if (!$usuario) {
                $_SESSION['error'] = 'Usuario no encontrado.';
                header('Location: ' . BASE_URL . '/usuarios');
                exit;
            }
            
            // Obtener historial
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $porPagina = 20;
            $offset = ($pagina - 1) * $porPagina;
            
            $historial = $this->registroActividad->obtenerHistorialUsuario($id, $porPagina, $offset);
            
            $datos = [
                'titulo' => 'Historial de ' . $usuario['apellidos'] . ', ' . $usuario['nombre'],
                'usuario' => $usuario,
                'historial' => $historial,
                'pagina_actual' => $pagina,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            require_once APP_PATH . '/vistas/admin/usuarios/historial.php';
            
        } catch (Exception $e) {
            error_log("Error al mostrar historial: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el historial del usuario.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
    
    /**
     * Mostrar formulario de importación masiva
     */
    public function importar() {
        $datos = [
            'titulo' => 'Importar Usuarios',
            'csrf_token' => $this->sesion->generarTokenCSRF()
        ];
        
        require_once APP_PATH . '/vistas/admin/usuarios/importar.php';
    }
    
    /**
     * Procesar importación masiva de usuarios
     */
    public function procesarImportacion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/usuarios/importar');
            exit;
        }
        
        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || !$this->sesion->validarTokenCSRF($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Error de validación de seguridad.';
            header('Location: ' . BASE_URL . '/usuarios/importar');
            exit;
        }
        
        // Verificar archivo subido
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error al subir el archivo.';
            header('Location: ' . BASE_URL . '/usuarios/importar');
            exit;
        }
        
        try {
            $resultado = $this->procesarArchivoCSV($_FILES['archivo']);
            
            // Registrar actividad
            $this->registroActividad->registrar(
                $_SESSION['id_usuario'],
                'importar_usuarios',
                "Importación masiva: {$resultado['creados']} usuarios creados, {$resultado['errores']} errores",
                'usuarios'
            );
            
            $_SESSION['exito'] = "Importación completada: {$resultado['creados']} usuarios creados.";
            if ($resultado['errores'] > 0) {
                $_SESSION['warning'] = "Se encontraron {$resultado['errores']} errores durante la importación.";
            }
            
        } catch (Exception $e) {
            error_log("Error en importación: " . $e->getMessage());
            $_SESSION['error'] = 'Error durante la importación: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
    
    /**
     * Procesar archivo CSV
     */
    private function procesarArchivoCSV($archivo) {
        $extensiones_validas = ['csv', 'txt'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_validas)) {
            throw new Exception('Formato de archivo no válido. Solo se permiten archivos CSV.');
        }
        
        $handle = fopen($archivo['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception('No se pudo leer el archivo.');
        }
        
        $creados = 0;
        $errores = 0;
        $linea = 0;
        
        // Leer cabeceras
        $cabeceras = fgetcsv($handle, 0, ',');
        if (!$cabeceras || !$this->validarCabecerasCSV($cabeceras)) {
            fclose($handle);
            throw new Exception('Formato de cabeceras incorrecto. Esperado: nombre,apellidos,correo,rol,curso_asignado');
        }
        
        while (($datos = fgetcsv($handle, 0, ',')) !== FALSE) {
            $linea++;
            
            try {
                if (count($datos) < 4) {
                    $errores++;
                    continue;
                }
                
                $usuario = [
                    'nombre' => trim($datos[0]),
                    'apellidos' => trim($datos[1]),
                    'correo' => filter_var(trim($datos[2]), FILTER_SANITIZE_EMAIL),
                    'rol' => trim($datos[3]),
                    'curso_asignado' => !empty($datos[4]) ? (int)$datos[4] : null,
                    'contrasena' => $this->generarContrasenaAleatoria(),
                    'activo' => 1
                ];
                
                // Validaciones
                if (empty($usuario['nombre']) || empty($usuario['apellidos']) || 
                    empty($usuario['correo']) || empty($usuario['rol'])) {
                    $errores++;
                    continue;
                }
                
                if (!filter_var($usuario['correo'], FILTER_VALIDATE_EMAIL)) {
                    $errores++;
                    continue;
                }
                
                if (!in_array($usuario['rol'], ['admin', 'profesor', 'alumno'])) {
                    $errores++;
                    continue;
                }
                
                // Verificar si ya existe
                if ($this->usuarioModelo->existeCorreo($usuario['correo'])) {
                    $errores++;
                    continue;
                }
                
                // Crear usuario
                if ($this->usuarioModelo->crear($usuario)) {
                    $creados++;
                    
                    // Enviar email con credenciales (si está configurado)
                    $this->enviarCredencialesPorEmail($usuario);
                } else {
                    $errores++;
                }
                
            } catch (Exception $e) {
                error_log("Error procesando línea $linea: " . $e->getMessage());
                $errores++;
            }
        }
        
        fclose($handle);
        
        return [
            'creados' => $creados,
            'errores' => $errores
        ];
    }
    
    /**
     * Validar cabeceras del CSV
     */
    private function validarCabecerasCSV($cabeceras) {
        $esperadas = ['nombre', 'apellidos', 'correo', 'rol'];
        
        for ($i = 0; $i < count($esperadas); $i++) {
            if (!isset($cabeceras[$i]) || strtolower(trim($cabeceras[$i])) !== $esperadas[$i]) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generar contraseña aleatoria
     */
    private function generarContrasenaAleatoria() {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $longitud = 12;
        $contrasena = '';
        
        for ($i = 0; $i < $longitud; $i++) {
            $contrasena .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        return password_hash($contrasena, PASSWORD_DEFAULT);
    }
    
    /**
     * Enviar credenciales por email
     */
    private function enviarCredencialesPorEmail($usuario) {
        // Solo si el correo está configurado
        if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
            return false;
        }
        
        try {
            require_once APP_PATH . '/utilidades/correo.php';
            
            $correo = new Correo();
            $asunto = 'Credenciales de acceso - ' . (defined('NOMBRE_SISTEMA') ? NOMBRE_SISTEMA : 'AUTOEXAM2');
            $mensaje = $this->generarMensajeBienvenida($usuario);
            
            return $correo->enviar($usuario['correo'], $asunto, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando credenciales por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar mensaje de bienvenida
     */
    private function generarMensajeBienvenida($usuario) {
        $sistemaUrl = defined('BASE_URL') ? BASE_URL : $_SERVER['HTTP_HOST'];
        
        return "
        <h2>Bienvenido/a al sistema</h2>
        <p>Hola {$usuario['apellidos']}, {$usuario['nombre']},</p>
        <p>Se ha creado tu cuenta en el sistema con los siguientes datos:</p>
        <ul>
            <li><strong>Correo:</strong> {$usuario['correo']}</li>
            <li><strong>Rol:</strong> " . ucfirst($usuario['rol']) . "</li>
        </ul>
        <p>Para acceder al sistema, dirígete a: <a href='$sistemaUrl'>$sistemaUrl</a></p>
        <p>Se te pedirá que cambies tu contraseña en el primer acceso.</p>
        <p>Si tienes alguna pregunta, contacta con el administrador del sistema.</p>
        ";
    }
    
    /**
     * Ver estadísticas de usuarios
     */
    public function estadisticas() {
        try {
            // Estadísticas generales
            $stats = [
                'total_usuarios' => $this->contarUsuarios(),
                'usuarios_activos' => $this->contarUsuarios(['activo' => 1]),
                'usuarios_inactivos' => $this->contarUsuarios(['activo' => 0]),
                'por_rol' => [
                    'admin' => $this->contarUsuarios(['rol' => 'admin']),
                    'profesor' => $this->contarUsuarios(['rol' => 'profesor']),
                    'alumno' => $this->contarUsuarios(['rol' => 'alumno'])
                ]
            ];
            
            // Actividad reciente
            $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            $actividad = $this->registroActividad->obtenerEstadisticas($fechaInicio);
            
            $datos = [
                'titulo' => 'Estadísticas de Usuarios',
                'estadisticas' => $stats,
                'actividad_reciente' => $actividad,
                'csrf_token' => $this->sesion->generarTokenCSRF()
            ];
            
            require_once APP_PATH . '/vistas/admin/usuarios/estadisticas.php';
            
        } catch (Exception $e) {
            error_log("Error al mostrar estadísticas: " . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar las estadísticas.';
            header('Location: ' . BASE_URL . '/usuarios');
            exit;
        }
    }
    
    /**
     * Descargar plantilla CSV para importación
     */
    public function descargarPlantilla() {
        // Configurar headers para descarga
        $filename = 'plantilla_usuarios.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fwrite($output, "\xEF\xBB\xBF");
        
        // Headers del CSV
        fputcsv($output, ['nombre', 'apellidos', 'correo', 'rol', 'curso_asignado']);
        
        // Ejemplos
        fputcsv($output, ['Juan', 'Pérez García', 'juan.perez@email.com', 'alumno', '1']);
        fputcsv($output, ['María', 'García López', 'maria.garcia@email.com', 'profesor', '']);
        fputcsv($output, ['Pedro', 'Martín Ruiz', 'pedro.martin@email.com', 'admin', '']);
        
        fclose($output);
        exit;
    }
    
    /**
     * Procesar subida de foto de perfil
     */
    private function procesarFotoPerfil($archivo) {
        if (!isset($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validar tamaño (2MB máximo)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($archivo['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 2MB.');
        }
        
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $archivo['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Formato de archivo no permitido. Solo JPG, PNG y GIF.');
        }
        
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'avatar_' . uniqid() . '.' . strtolower($extension);
        
        // Directorio de destino usando la nueva constante AVATARS_STORAGE_DIR
        $directorioDestino = AVATARS_STORAGE_DIR . '/'; // Asegurarse de que termina con barra
        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de avatares: ' . $directorioDestino);
            }
        }
        
        $rutaCompleta = $directorioDestino . $nombreArchivo;
        
        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            // Devolver la ruta relativa pública usando AVATARS_PUBLIC_SUBPATH
            return AVATARS_PUBLIC_SUBPATH . '/' . $nombreArchivo;
        } else {
            throw new Exception('Error al guardar el archivo en: ' . $rutaCompleta);
        }
    }
    
    /**
     * Eliminar foto anterior si existe
     */
    private function eliminarFotoAnterior($rutaFotoRelativaPublica) {
        if (!empty($rutaFotoRelativaPublica)) {
            // Construir la ruta física completa desde la ruta relativa pública
            $rutaCompleta = ROOT_PATH . '/publico/' . $rutaFotoRelativaPublica;
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
        }
    }
}
