<?php
/**
 * Modelo Usuario - AUTOEXAM2
 * 
 * Maneja todas las operaciones de base de datos relacionadas con usuarios
 * 
 * @author Carlos Ferrero Bonet (refactorizado)
 * @version 2.0
 */

class Usuario {
    private $conexion;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->conectarBaseDatos();
    }
    
    /**
     * Obtiene la conexión actual a la base de datos
     * 
     * @return PDO Conexión a la base de datos
     */
    public function getConexion() {
        return $this->conexion;
    }
    
    /**
     * Establecer conexión con la base de datos
     * @throws Exception Si hay un error de conexión
     */
    private function conectarBaseDatos() {
        try {
            $this->conexion = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Sanitiza los datos de entrada para prevenir inyección SQL y XSS
     * 
     * @param array $datos Datos a sanitizar
     * @return array Datos sanitizados
     */
    private function sanitizarDatos($datos) {
        $datosSanitizados = [];
        
        foreach ($datos as $campo => $valor) {
            switch ($campo) {
                case 'nombre':
                case 'apellidos':
                    $datosSanitizados[$campo] = filter_var(trim($valor), FILTER_SANITIZE_STRING);
                    break;
                case 'correo':
                    $datosSanitizados[$campo] = filter_var(trim($valor), FILTER_SANITIZE_EMAIL);
                    break;
                case 'rol':
                    // Verifica que el rol sea uno de los permitidos
                    $rol = trim($valor);
                    if (in_array($rol, ['admin', 'profesor', 'alumno'])) {
                        $datosSanitizados[$campo] = $rol;
                    } else {
                        $datosSanitizados[$campo] = 'alumno'; // valor por defecto
                    }
                    break;
                case 'activo':
                    $datosSanitizados[$campo] = (int)(bool)$valor;
                    break;
                case 'curso_asignado':
                    $datosSanitizados[$campo] = $valor !== null && $valor !== '' ? (int)$valor : null;
                    break;
                case 'contrasena':
                    // Para contraseñas no hacemos sanitización, son procesadas luego
                    $datosSanitizados[$campo] = $valor;
                    break;
                case 'foto':
                    // Para rutas de archivos sanitizamos eliminando caracteres peligrosos
                    $datosSanitizados[$campo] = $valor ? preg_replace('/[^a-zA-Z0-9\/_\-.]+/', '', $valor) : null;
                    break;
                default:
                    // Para otros campos hacemos sanitización básica
                    $datosSanitizados[$campo] = is_string($valor) ? trim($valor) : $valor;
                    break;
            }
        }
        
        return $datosSanitizados;
    }
    
    /**
     * Buscar usuario por correo electrónico
     * 
     * @param string $correo Correo electrónico del usuario
     * @param bool $soloActivos Si es true, solo devolverá usuarios activos
     * @return array|null Datos del usuario o null si no existe
     * @throws Exception Si hay error en la consulta
     */
    public function buscarPorCorreo($correo, $soloActivos = true) {
        try {
            $correo = filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
            
            $sql = "SELECT id_usuario, nombre, apellidos, correo, contrasena, pin, rol, 
                           curso_asignado, foto, activo 
                    FROM usuarios 
                    WHERE correo = :correo";
                    
            // Solo aplicar filtro de activo si se solicita explícitamente
            if ($soloActivos) {
                $sql .= " AND activo = 1";
            }
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch();
            
            return $usuario ?: null;
            
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por correo: " . $e->getMessage());
            throw new Exception("Error al consultar usuario");
        }
    }
    
    /**
     * Buscar usuario por ID
     * 
     * @param int $id ID del usuario
     * @param bool $soloActivos Si es true, solo devolverá usuarios activos
     * @return array|null Datos del usuario o null si no existe
     * @throws Exception Si hay error en la consulta
     */
    public function buscarPorId($id, $soloActivos = false) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return null;
            }
            
            $sql = "SELECT id_usuario, nombre, apellidos, correo, rol, 
                           curso_asignado, foto, activo 
                    FROM usuarios 
                    WHERE id_usuario = :id";
            
            // Solo aplicar filtro de activo si se solicita explícitamente
            if ($soloActivos) {
                $sql .= " AND activo = 1";
            }
                    
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Error al buscar usuario por ID: " . $e->getMessage());
            throw new Exception("Error al consultar usuario");
        }
    }
    
    /**
     * Verifica que el usuario especificado existe
     * 
     * @param int $id ID del usuario a verificar
     * @param bool $soloActivos Si es true, solo verifica usuarios activos
     * @return bool True si el usuario existe
     */
    public function existeUsuario($id, $soloActivos = false) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return false;
            }
            
            $sql = "SELECT 1 FROM usuarios WHERE id_usuario = :id";
            
            if ($soloActivos) {
                $sql .= " AND activo = 1";
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Error al verificar existencia de usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar contraseña
     * 
     * @param string $contrasena Contraseña en texto plano
     * @param string $hash Hash almacenado en la base de datos
     * @return bool True si la contraseña es correcta
     */
    public function verificarContrasena($contrasena, $hash) {
        return password_verify($contrasena, $hash);
    }
    
    /**
     * Crear nuevo usuario
     * 
     * @param array $datos Datos del usuario
     * @return int|false ID del usuario creado o false en caso de error
     * @throws Exception Si hay error al crear el usuario
     */
    public function crear($datos) {
        try {
            // Sanitizar datos de entrada
            $datos = $this->sanitizarDatos($datos);
            
            // Configurar campos obligatorios
            $campos = ['nombre', 'apellidos', 'correo', 'contrasena', 'rol'];
            foreach ($campos as $campo) {
                if (empty($datos[$campo])) {
                    throw new Exception("Campo obligatorio no proporcionado: {$campo}");
                }
            }
            
            // Validar email
            if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Formato de correo electrónico inválido");
            }
            
            // Validar rol
            if (!in_array($datos['rol'], ['admin', 'profesor', 'alumno'])) {
                throw new Exception("Rol inválido");
            }
            
            // Establecer valores predeterminados para campos opcionales
            $datos['foto'] = $datos['foto'] ?? null;
            $datos['activo'] = isset($datos['activo']) ? (int)(bool)$datos['activo'] : 1;
            
            $sql = "INSERT INTO usuarios (nombre, apellidos, correo, contrasena, rol, foto, activo) 
                    VALUES (:nombre, :apellidos, :correo, :contrasena, :rol, :foto, :activo)";
            
            $stmt = $this->conexion->prepare($sql);
            
            // Encriptar contraseña
            $contrasenaHash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellidos', $datos['apellidos'], PDO::PARAM_STR);
            $stmt->bindParam(':correo', $datos['correo'], PDO::PARAM_STR);
            $stmt->bindParam(':contrasena', $contrasenaHash, PDO::PARAM_STR);
            $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
            $stmt->bindParam(':foto', $datos['foto'], PDO::PARAM_STR);
            $stmt->bindParam(':activo', $datos['activo'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return (int)$this->conexion->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            throw new Exception("Error al crear usuario");
        }
    }
    
    /**
     * Actualizar datos del usuario
     * 
     * @param int $id ID del usuario
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws Exception Si hay error al actualizar el usuario
     */
    public function actualizar($id, $datos) {
        try {
            $id = (int)$id;
            if ($id <= 0 || empty($datos)) {
                return false;
            }
            
            // Verificar que el usuario existe
            if (!$this->existeUsuario($id, false)) {
                throw new Exception("Usuario no encontrado");
            }
            
            // Sanitizar datos
            $datos = $this->sanitizarDatos($datos);
            
            // Construir dinámicamente la consulta SQL
            $campos = [];
            $valores = [];
            
            foreach ($datos as $campo => $valor) {
                // Sólo permitir actualizar estos campos
                if (in_array($campo, ['nombre', 'apellidos', 'correo', 'contrasena', 'rol', 'curso_asignado', 'foto', 'activo'])) {
                    if ($campo === 'contrasena') {
                        // Para contraseña, hashear antes de guardar
                        $campos[] = "$campo = :$campo";
                        $valores[":$campo"] = password_hash($valor, PASSWORD_DEFAULT);
                    } else {
                        $campos[] = "$campo = :$campo";
                        $valores[":$campo"] = $valor;
                    }
                }
            }
            
            // Si no hay campos para actualizar, devolver false
            if (empty($campos)) {
                return false;
            }
            
            $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id_usuario = :id";
            $valores[':id'] = $id;
            
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute($valores);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            throw new Exception("Error al actualizar usuario");
        }
    }
    
    /**
     * Desactivar usuario
     * 
     * @param int $id ID del usuario
     * @return bool True si se desactivó correctamente
     * @throws Exception Si hay error al desactivar el usuario
     */
    public function desactivar($id) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return false;
            }
            
            // Verificar que el usuario no sea el admin principal (ID 1)
            if ($id === 1) {
                throw new Exception("No se puede desactivar al administrador principal");
            }
            
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id AND id_usuario <> 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al desactivar usuario: " . $e->getMessage());
            throw new Exception("Error al desactivar usuario");
        }
    }
    
    /**
     * Activar usuario
     * 
     * @param int $id ID del usuario
     * @return bool True si se activó correctamente
     * @throws Exception Si hay error al activar el usuario
     */
    public function activar($id) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return false;
            }
            
            $sql = "UPDATE usuarios SET activo = 1 WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al activar usuario: " . $e->getMessage());
            throw new Exception("Error al activar usuario");
        }
    }
    
    /**
     * Verificar si existe un usuario con el correo dado
     * 
     * @param string $correo Correo electrónico
     * @param int $exceptoId Excluir un ID específico de la verificación (útil para actualizar)
     * @return bool True si existe
     * @throws Exception Si hay error al verificar el correo
     */
    public function existeCorreo($correo, $exceptoId = null) {
        try {
            $correo = filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
            
            $sql = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo";
            $params = [':correo' => $correo];
            
            // Si hay ID a excluir, añadir condición
            if ($exceptoId !== null) {
                $exceptoId = (int)$exceptoId;
                $sql .= " AND id_usuario <> :id";
                $params[':id'] = $exceptoId;
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (PDOException $e) {
            error_log("Error al verificar existencia de correo: " . $e->getMessage());
            throw new Exception("Error al verificar correo");
        }
    }
    
    /**
     * Listar usuarios con filtros opcionales
     * 
     * @param array $filtros Filtros a aplicar
     * @param int $limite Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Lista de usuarios
     * @throws Exception Si hay error en la consulta
     */
    public function listar($filtros = [], $limite = 50, $offset = 0) {
        try {
            // Iniciar la construcción de la consulta base
            $sql = "SELECT u.id_usuario, u.nombre, u.apellidos, u.correo, u.rol, 
                           u.foto, u.activo, u.ultimo_acceso,
                           c.id_curso as curso_asignado, c.nombre_curso, c.descripcion as curso_descripcion
                    FROM usuarios u
                    LEFT JOIN curso_alumno ca ON u.id_usuario = ca.id_alumno
                    LEFT JOIN cursos c ON ca.id_curso = c.id_curso 
                    WHERE 1=1";
            
            // Array para almacenar los parámetros y sus valores
            $params = [];
            
            // Aplicar filtro de rol
            if (isset($filtros['rol']) && in_array($filtros['rol'], ['admin', 'profesor', 'alumno'])) {
                $sql .= " AND rol = :rol";
                $params[':rol'] = $filtros['rol'];
            }
            
            // Aplicar filtro de estado activo/inactivo
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $activo = (int)(bool)$filtros['activo'];
                $sql .= " AND activo = :activo";
                $params[':activo'] = $activo;
            }
            
            // Aplicar búsqueda por nombre, apellidos o correo
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $terminoBusqueda = trim($filtros['buscar']);
                if (strlen($terminoBusqueda) >= 3) {
                    // Usar parámetros nombrados para mayor seguridad
                    $sql .= " AND (apellidos LIKE :buscar OR nombre LIKE :buscar2 OR correo LIKE :buscar3)";
                    $termino = '%' . $terminoBusqueda . '%';
                    $params[':buscar'] = $termino;
                    $params[':buscar2'] = $termino;
                    $params[':buscar3'] = $termino;
                }
            }
            
            // Agregar ordenamiento
            if (isset($filtros['ordenar_por']) && isset($filtros['orden'])) {
                // Validar campos permitidos para evitar inyección SQL
                $camposPermitidos = [
                    'id_usuario' => 'id_usuario',
                    'nombre' => 'nombre',
                    'apellidos' => 'apellidos',
                    'rol' => 'rol',
                    'activo' => 'activo',
                    'ultimo_acceso' => 'ultimo_acceso'
                ];
                
                // Si el campo es válido, ordenar por él
                if (array_key_exists($filtros['ordenar_por'], $camposPermitidos)) {
                    $campo = $camposPermitidos[$filtros['ordenar_por']];
                    $orden = $filtros['orden'] === 'DESC' ? 'DESC' : 'ASC';
                    $sql .= " ORDER BY $campo $orden";
                    
                    // Ordenación secundaria para mantener consistencia
                    if ($campo !== 'apellidos' && $campo !== 'nombre') {
                        $sql .= ", apellidos ASC, nombre ASC";
                    }
                } else {
                    // Ordenación por defecto si el campo no es válido
                    $sql .= " ORDER BY 
                        CASE 
                            WHEN rol = 'admin' THEN 1 
                            WHEN rol = 'profesor' THEN 2 
                            WHEN rol = 'alumno' THEN 3 
                            ELSE 4 
                        END, 
                        apellidos ASC, nombre ASC";
                }
            } else {
                // Ordenación por defecto
                $sql .= " ORDER BY 
                        CASE 
                            WHEN rol = 'admin' THEN 1 
                            WHEN rol = 'profesor' THEN 2 
                            WHEN rol = 'alumno' THEN 3 
                            ELSE 4 
                        END, 
                        apellidos ASC, nombre ASC";
            }
            
            // Limitar resultados para paginación
            $sql .= " LIMIT :limite OFFSET :offset";
            $params[':limite'] = (int)$limite;
            $params[':offset'] = (int)$offset;
            
            $stmt = $this->conexion->prepare($sql);
            
            // Bindear valores con tipos específicos
            foreach ($params as $param => $valor) {
                if ($param === ':limite' || $param === ':offset' || $param === ':activo') {
                    $stmt->bindValue($param, $valor, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($param, $valor, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al listar usuarios: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parámetros: " . print_r($params, true));
            throw new Exception("Error al consultar usuarios");
        }
    }
    
    /**
     * Contar total de usuarios con filtros opcionales (para paginación)
     * 
     * @param array $filtros Filtros a aplicar
     * @return int Total de usuarios
     * @throws Exception Si hay error en la consulta
     */
    public function contarTotal($filtros = []) {
        try {
            // Iniciar la construcción de la consulta base
            $sql = "SELECT COUNT(*) FROM usuarios WHERE 1=1";
            
            // Array para almacenar los valores de los parámetros
            $params = [];
            
            // Aplicar filtro de rol
            if (isset($filtros['rol']) && in_array($filtros['rol'], ['admin', 'profesor', 'alumno'])) {
                $sql .= " AND rol = :rol";
                $params[':rol'] = $filtros['rol'];
            }
            
            // Aplicar filtro de estado activo/inactivo
            if (isset($filtros['activo']) && $filtros['activo'] !== '') {
                $activo = (int)(bool)$filtros['activo'];
                $sql .= " AND activo = :activo";
                $params[':activo'] = $activo;
            }
            
            // Aplicar búsqueda por nombre, apellidos o correo
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $terminoBusqueda = trim($filtros['buscar']);
                if (strlen($terminoBusqueda) >= 3) {
                    // Usar parámetros nombrados para mayor seguridad
                    $sql .= " AND (apellidos LIKE :buscar OR nombre LIKE :buscar2 OR correo LIKE :buscar3)";
                    $termino = '%' . $terminoBusqueda . '%';
                    $params[':buscar'] = $termino;
                    $params[':buscar2'] = $termino;
                    $params[':buscar3'] = $termino;
                }
            }
            
            $stmt = $this->conexion->prepare($sql);
            
            // Bindear valores
            foreach ($params as $param => $valor) {
                if ($param === ':activo') {
                    $stmt->bindValue($param, $valor, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($param, $valor, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            
            return (int)$stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error al contar usuarios: " . $e->getMessage());
            throw new Exception("Error al contar usuarios");
        }
    }
    
    /**
     * Registrar último acceso del usuario
     * 
     * @param int $id ID del usuario
     * @return bool True si se registró correctamente
     */
    public function registrarUltimoAcceso($id) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return false;
            }
            
            $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al registrar último acceso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si la base de datos está disponible
     * 
     * @return bool True si la conexión es exitosa
     */
    public function verificarConexion() {
        try {
            if ($this->conexion) {
                $stmt = $this->conexion->query("SELECT 1");
                return $stmt !== false;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Verificar si un usuario es el administrador principal
     * 
     * @param int $id ID del usuario a verificar
     * @return bool True si es el administrador principal
     */
    public function esAdministradorPrincipal($id) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                return false;
            }
            
            // El admin principal es el ID 1 o el usuario con rol admin y correo específico
            $sql = "SELECT 1 FROM usuarios WHERE 
                    (id_usuario = 1 OR 
                     (rol = 'admin' AND correo = 'no_contestar@autoexam.epla.es')) 
                    AND id_usuario = :id";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Error al verificar administrador principal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un usuario de la base de datos
     * 
     * @param int $id ID del usuario a eliminar
     * @return bool True si se eliminó correctamente
     * @throws Exception Si hay un error en la eliminación
     */
    public function eliminar($id) {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                throw new Exception("ID de usuario inválido");
            }
            
            // Verificar que no sea el administrador principal
            if ($this->esAdministradorPrincipal($id)) {
                throw new Exception("No se puede eliminar el administrador principal");
            }
            
            // Verificar que el usuario existe
            if (!$this->existeUsuario($id, false)) {
                throw new Exception("El usuario no existe");
            }
            
            // Iniciar transacción
            $this->conexion->beginTransaction();
            
            try {
                // Primero eliminar referencias en otras tablas relacionadas
                // Eliminar asignaciones de cursos (por id_alumno)
                $sql = "DELETE FROM curso_alumno WHERE id_alumno = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Eliminar calificaciones del alumno
                $sql = "DELETE FROM calificaciones WHERE id_alumno = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Eliminar notificaciones del usuario
                $sql = "DELETE FROM notificaciones WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Eliminar registros de actividad del usuario
                $sql = "DELETE FROM registro_actividad WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Eliminar sesiones activas
                $sql = "DELETE FROM sesiones_activas WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Eliminar tokens de recuperación
                $sql = "DELETE FROM tokens_recuperacion WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Finalmente eliminar el usuario
                $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $resultado = $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception("No se pudo eliminar el usuario");
                }
                
                // Confirmar transacción
                $this->conexion->commit();
                
                return true;
                
            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $this->conexion->rollBack();
                throw $e;
            }
            
        } catch (PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw new Exception("Error al eliminar el usuario de la base de datos");
        }
    }
    
    /**
     * Cuenta usuarios por rol específico
     * 
     * @param string $rol El rol a contar (admin, profesor, alumno)
     * @param bool $soloActivos Si es true, solo cuenta usuarios activos
     * @return int Número de usuarios con ese rol
     * @throws Exception Si hay error en la consulta
     */
    public function contarPorRol($rol, $soloActivos = true) {
        try {
            // Validar que el rol sea válido
            if (!in_array($rol, ['admin', 'profesor', 'alumno'])) {
                throw new Exception("Rol no válido: $rol");
            }
            
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = :rol";
            
            if ($soloActivos) {
                $sql .= " AND activo = 1";
            }
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            error_log("Error al contar usuarios por rol '$rol': " . $e->getMessage());
            throw new Exception("Error al consultar conteo de usuarios");
        }
    }
    
    /**
     * Obtiene estadísticas de conteo para el dashboard del admin
     * 
     * @return array Array con conteos por rol y cursos activos
     * @throws Exception Si hay error en la consulta
     */
    public function obtenerEstadisticasConteo() {
        try {
            $estadisticas = [
                'administradores' => [
                    'activos' => $this->contarPorRol('admin', true),
                    'inactivos' => $this->contarPorRol('admin', false) - $this->contarPorRol('admin', true),
                    'total' => $this->contarPorRol('admin', false)
                ],
                'profesores' => [
                    'activos' => $this->contarPorRol('profesor', true),
                    'inactivos' => $this->contarPorRol('profesor', false) - $this->contarPorRol('profesor', true),
                    'total' => $this->contarPorRol('profesor', false)
                ],
                'alumnos' => [
                    'activos' => $this->contarPorRol('alumno', true),
                    'inactivos' => $this->contarPorRol('alumno', false) - $this->contarPorRol('alumno', true),
                    'total' => $this->contarPorRol('alumno', false)
                ],
                'cursos_activos' => [
                    'activos' => $this->contarCursosActivos(),
                    'inactivos' => $this->contarCursosInactivos(),
                    'total' => $this->contarCursosActivos() + $this->contarCursosInactivos()
                ],
                'modulos' => [
                    'activos' => $this->contarModulosActivos(),
                    'inactivos' => $this->contarModulosInactivos(),
                    'total' => $this->contarModulosActivos() + $this->contarModulosInactivos()
                ],
                'examenes' => [
                    'activos' => $this->contarExamenesActivos(),
                    'inactivos' => $this->contarExamenesInactivos(),
                    'total' => $this->contarExamenesActivos() + $this->contarExamenesInactivos()
                ]
            ];
            
            return $estadisticas;
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de conteo: " . $e->getMessage());
            throw new Exception("Error al obtener estadísticas");
        }
    }
    
    /**
     * Cuenta los cursos activos en el sistema
     * 
     * @return int Número de cursos activos
     * @throws Exception Si hay error en la consulta
     */
    private function contarCursosActivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM cursos WHERE activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla cursos no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar cursos activos: " . $e->getMessage());
            throw new Exception("Error al consultar cursos activos");
        }
    }
    
    /**
     * Cuenta los cursos inactivos en el sistema
     * 
     * @return int Número de cursos inactivos
     * @throws Exception Si hay error en la consulta
     */
    private function contarCursosInactivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM cursos WHERE activo = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla cursos no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar cursos inactivos: " . $e->getMessage());
            throw new Exception("Error al consultar cursos inactivos");
        }
    }

    /**
     * Actualiza el perfil de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @param array $datos Datos a actualizar (nombre, apellidos, correo, foto)
     * @return bool True si se actualiza correctamente
     * @throws Exception Si hay error en la actualización
     */
    public function actualizarPerfil($idUsuario, $datos) {
        try {
            // Construir la consulta dinámicamente según los datos proporcionados
            $campos = [];
            $valores = [];
            
            if (isset($datos['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $datos['nombre'];
            }
            
            if (isset($datos['apellidos'])) {
                $campos[] = "apellidos = ?";
                $valores[] = $datos['apellidos'];
            }
            
            if (isset($datos['correo'])) {
                $campos[] = "correo = ?";
                $valores[] = $datos['correo'];
            }
            
            if (isset($datos['foto'])) {
                $campos[] = "foto = ?";
                $valores[] = $datos['foto'];
            }
            
            if (empty($campos)) {
                throw new Exception("No hay datos para actualizar");
            }
            
            // Agregar fecha de actualización
            $campos[] = "fecha_actualizacion = NOW()";
            $valores[] = $idUsuario;
            
            $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id_usuario = ?";
            
            $stmt = $this->conexion->prepare($sql);
            return $stmt->execute($valores);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            throw new Exception("Error al actualizar el perfil del usuario");
        }
    }

    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @param string $nuevaContrasena Nueva contraseña (sin hashear)
     * @return bool True si se actualiza correctamente
     * @throws Exception Si hay error en la actualización
     */
    public function actualizarContrasena($idUsuario, $nuevaContrasena) {
        try {
            $contrasenaHasheada = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
            
            $sql = "UPDATE usuarios SET contrasena = ?, fecha_actualizacion = NOW() WHERE id_usuario = ?";
            $stmt = $this->conexion->prepare($sql);
            
            return $stmt->execute([$contrasenaHasheada, $idUsuario]);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            throw new Exception("Error al actualizar la contraseña del usuario");
        }
    }

    /**
     * Obtener estadísticas completas de usuarios
     */
    public function obtenerEstadisticas() {
        try {
            $estadisticas = [];

            // Total de usuarios por rol
            $sql = "SELECT rol, COUNT(*) as total FROM usuarios GROUP BY rol";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $estadisticas['por_rol'] = $stmt->fetchAll();

            // Usuarios activos vs inactivos
            $sql = "SELECT activo, COUNT(*) as total FROM usuarios GROUP BY activo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            $estadisticas['por_estado'] = [
                'activos' => 0,
                'inactivos' => 0
            ];
            foreach ($resultados as $resultado) {
                if ($resultado['activo'] == 1) {
                    $estadisticas['por_estado']['activos'] = $resultado['total'];
                } else {
                    $estadisticas['por_estado']['inactivos'] = $resultado['total'];
                }
            }

            // Registros por mes (últimos 12 meses)
            // Temporalmente comentado hasta agregar columna fecha_registro
            /*$sql = "SELECT 
                        DATE_FORMAT(fecha_registro, '%Y-%m') as mes,
                        COUNT(*) as total
                    FROM usuarios 
                    WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY mes
                    ORDER BY mes";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $estadisticas['registros_por_mes'] = $stmt->fetchAll();*/
            
            // Alternativa temporal usando la fecha del último acceso
            $sql = "SELECT 
                        DATE_FORMAT(COALESCE(ultimo_acceso, NOW()), '%Y-%m') as mes,
                        COUNT(*) as total
                    FROM usuarios 
                    WHERE COALESCE(ultimo_acceso, NOW()) >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY mes
                    ORDER BY mes";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $estadisticas['registros_por_mes'] = $stmt->fetchAll();

            // Último acceso (usuarios activos en últimos 30 días)
            $sql = "SELECT COUNT(*) as activos_recientes 
                    FROM usuarios 
                    WHERE ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $estadisticas['activos_recientes'] = $stmt->fetch()['activos_recientes'];

            // Total general
            $sql = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $estadisticas['total_usuarios'] = $stmt->fetch()['total'];

            return $estadisticas;

        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            throw new Exception("Error al obtener estadísticas de usuarios");
        }
    }
    
    /**
     * Cuenta los módulos activos en el sistema
     * 
     * @return int Número de módulos activos
     */
    private function contarModulosActivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM modulos WHERE activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla módulos no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar módulos activos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta los módulos inactivos en el sistema
     * 
     * @return int Número de módulos inactivos
     */
    private function contarModulosInactivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM modulos WHERE activo = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla módulos no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar módulos inactivos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta los exámenes activos en el sistema
     * 
     * @return int Número de exámenes activos
     */
    private function contarExamenesActivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM examenes WHERE activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla exámenes no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar exámenes activos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cuenta los exámenes inactivos en el sistema
     * 
     * @return int Número de exámenes inactivos
     */
    private function contarExamenesInactivos() {
        try {
            $sql = "SELECT COUNT(*) as total FROM examenes WHERE activo = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            return (int)$resultado['total'];
            
        } catch (PDOException $e) {
            // Si la tabla exámenes no existe aún, devolver 0
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Table") !== false) {
                return 0;
            }
            
            error_log("Error al contar exámenes inactivos: " . $e->getMessage());
            return 0;
        }
    }
}
