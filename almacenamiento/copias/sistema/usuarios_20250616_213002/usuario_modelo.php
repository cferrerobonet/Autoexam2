<?php
/**
 * Modelo Usuario - AUTOEXAM2
 * 
 * Maneja todas las operaciones de base de datos relacionadas con usuarios
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.2
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
            // Log del error
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    /**
     * Buscar usuario por correo electrónico
     * 
     * @param string $correo Correo electrónico del usuario
     * @param bool $soloActivos Si es true, solo devolverá usuarios activos
     * @return array|null Datos del usuario o null si no existe
     */
    public function buscarPorCorreo($correo, $soloActivos = true) {
        try {
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
            
            if ($usuario) {
                return $usuario;
            }
            
            return null;
            
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
     */
    public function buscarPorId($id, $soloActivos = false) {
        try {
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
     */
    public function crear($datos) {
        try {
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
                return $this->conexion->lastInsertId();
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
     */
    public function actualizar($id, $datos) {
        try {
            $campos = [];
            $valores = [];
            
            // Construir dinámicamente la consulta SQL
            foreach ($datos as $campo => $valor) {
                if ($campo === 'contrasena') {
                    $campos[] = "$campo = :$campo";
                    $valores[":$campo"] = password_hash($valor, PASSWORD_DEFAULT);
                } else {
                    $campos[] = "$campo = :$campo";
                    $valores[":$campo"] = $valor;
                }
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
     */
    public function desactivar($id) {
        try {
            $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
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
     */
    public function activar($id) {
        try {
            $sql = "UPDATE usuarios SET activo = 1 WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al activar usuario: " . $e->getMessage());
            throw new Exception("Error al activar usuario");
        }
    }
    
    /**
     * Verificar si existe un usuario con el correo dado
     * 
     * @param string $correo Correo electrónico
     * @return bool True si existe
     */
    public function existeCorreo($correo) {
        try {
            $sql = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            
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
     */
    public function listar($filtros = [], $limite = 50, $offset = 0) {
        try {
            // Iniciar la construcción de la consulta base
            $sql = "SELECT id_usuario, nombre, apellidos, correo, rol, 
                           curso_asignado, foto, activo, ultimo_acceso 
                    FROM usuarios WHERE 1=1";
            
            // Array para almacenar los valores de los parámetros
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
                // Sanitizar término de búsqueda eliminando caracteres especiales
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
            
            // Agregar ordenamiento y paginación
            $sql .= " ORDER BY 
                        CASE 
                            WHEN rol = 'admin' THEN 1 
                            WHEN rol = 'profesor' THEN 2 
                            WHEN rol = 'alumno' THEN 3 
                        END, 
                        apellidos ASC, nombre ASC 
                      LIMIT ? OFFSET ?";
            
            // Agregar los parámetros de paginación al final
            $valores[] = (int)$limite;
            $valores[] = (int)$offset;
            
            // Preparar y ejecutar la consulta con parámetros posicionales
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($valores);
            
            // Obtener y devolver resultados
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            // Registrar error detallado para depuración
            error_log("Error al listar usuarios: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Valores: " . print_r($valores, true));
            // Propagar el error para su manejo en el controlador
            throw new Exception("Error al consultar usuarios: " . $e->getMessage());
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
            $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al registrar último acceso: " . $e->getMessage());
            // No lanzar excepción aquí ya que no es crítico
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
}
