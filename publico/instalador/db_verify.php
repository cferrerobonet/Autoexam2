<?php
/**
 * Funciones de verificación de la base de datos para el instalador
 * Proporciona funciones más avanzadas para verificar la conexión y permisos a la base de datos
 */

/**
 * Verifica la conexión a la base de datos y sus permisos
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @return array Respuesta con el estado y mensajes
 */
function verificarBaseDatos($host, $user, $pass, $name) {
    $respuesta = [
        'success' => false,
        'messages' => [],
        'db_exists' => false,
        'can_create' => false,
        'permissions' => [],
        'server_info' => '',
        'connection_id' => '',
        'validated' => false
    ];
    
    try {
        // Registrar intentos de conexión en logs
        log_instalador("Intentando verificar base de datos: host=$host, usuario=$user, base=$name", 'info');
        
        // Intentar conexión sin especificar base de datos
        $mysqli_base = new mysqli($host, $user, $pass);
        
        if ($mysqli_base->connect_error) {
            log_instalador("Error de conexión al servidor MySQL/MariaDB: " . $mysqli_base->connect_error, 'error');
            throw new Exception("Error en la conexión básica: " . $mysqli_base->connect_error);
        }
        
        // Guardar información del servidor
        $respuesta['server_info'] = $mysqli_base->server_info;
        $respuesta['connection_id'] = $mysqli_base->thread_id;
        
        // Verificar versión mínima de MySQL/MariaDB
        $version = $mysqli_base->server_version;
        $version_major = intval($version / 10000);
        $version_minor = intval(($version % 10000) / 100);
        
        $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Conexión al servidor MySQL/MariaDB exitosa (Versión {$respuesta['server_info']})"];
        
        // Verificar versión mínima requerida
        if ($version_major < 5 || ($version_major == 5 && $version_minor < 6)) {
            $respuesta['messages'][] = ['tipo' => 'warning', 'texto' => "Su versión de MySQL ($version_major.$version_minor) es antigua. Se recomienda MySQL 5.6+ o MariaDB 10.0+"];
        }
        
        // Verificar si la base de datos existe
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
        $stmt = $mysqli_base->prepare($query);
        
        if (!$stmt) {
            log_instalador("Error preparando consulta de verificación de BD: " . $mysqli_base->error, 'error');
            throw new Exception("Error preparando consulta: " . $mysqli_base->error);
        }
        
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $respuesta['db_exists'] = true;
            $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Base de datos '$name' encontrada"];
            log_instalador("Base de datos '$name' encontrada", 'info');
        } else {
            $respuesta['db_exists'] = false;
            $respuesta['messages'][] = ['tipo' => 'info', 'texto' => "Base de datos '$name' no existe, se intentará crear"];
            log_instalador("Base de datos '$name' no existe, intentando crear", 'info');
            
            // Intentar crear la base de datos
            if ($mysqli_base->query("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $respuesta['can_create'] = true;
                $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Base de datos '$name' creada correctamente"];
                log_instalador("Base de datos '$name' creada correctamente", 'success');
            } else {
                $respuesta['can_create'] = false;
                $respuesta['messages'][] = ['tipo' => 'error', 'texto' => "No se pudo crear la base de datos: " . $mysqli_base->error];
                log_instalador("Error al crear la base de datos: " . $mysqli_base->error, 'error');
                throw new Exception("Error al crear la base de datos");
            }
        }
        
        // Conectar a la base de datos específica
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos '$name': " . $mysqli->connect_error);
        }
        
        // Verificar permisos del usuario en la base de datos
        $permisos = [
            'CREATE' => false,
            'ALTER' => false,
            'DROP' => false,
            'SELECT' => false,
            'INSERT' => false,
            'UPDATE' => false,
            'DELETE' => false,
            'INDEX' => false
        ];
        
        // Verificar permisos usando una tabla temporal
        $tabla_prueba = 'temp_installer_' . rand(1000, 9999);
        
        // Intentar crear una tabla
        $create_table = $mysqli->query("CREATE TABLE `$tabla_prueba` (id INT AUTO_INCREMENT PRIMARY KEY, dato VARCHAR(50))");
        if ($create_table) {
            $permisos['CREATE'] = true;
            $respuesta['permissions']['CREATE'] = true;
            
            // Intentar crear un índice
            $create_index = $mysqli->query("CREATE INDEX idx_dato ON `$tabla_prueba`(dato)");
            if ($create_index) {
                $permisos['INDEX'] = true;
                $respuesta['permissions']['INDEX'] = true;
            }
            
            // Intentar insertar datos
            $insert = $mysqli->query("INSERT INTO `$tabla_prueba` (dato) VALUES ('test')");
            if ($insert) {
                $permisos['INSERT'] = true;
                $respuesta['permissions']['INSERT'] = true;
            }
            
            // Intentar seleccionar datos
            $select = $mysqli->query("SELECT * FROM `$tabla_prueba`");
            if ($select) {
                $permisos['SELECT'] = true;
                $respuesta['permissions']['SELECT'] = true;
            }
            
            // Intentar actualizar datos
            $update = $mysqli->query("UPDATE `$tabla_prueba` SET dato = 'updated' WHERE dato = 'test'");
            if ($update) {
                $permisos['UPDATE'] = true;
                $respuesta['permissions']['UPDATE'] = true;
            }
            
            // Intentar eliminar datos
            $delete = $mysqli->query("DELETE FROM `$tabla_prueba` WHERE dato = 'updated'");
            if ($delete) {
                $permisos['DELETE'] = true;
                $respuesta['permissions']['DELETE'] = true;
            }
            
            // Intentar alterar la tabla
            $alter = $mysqli->query("ALTER TABLE `$tabla_prueba` ADD COLUMN extra VARCHAR(20)");
            if ($alter) {
                $permisos['ALTER'] = true;
                $respuesta['permissions']['ALTER'] = true;
            }
            
            // Intentar eliminar la tabla
            $drop = $mysqli->query("DROP TABLE `$tabla_prueba`");
            if ($drop) {
                $permisos['DROP'] = true;
                $respuesta['permissions']['DROP'] = true;
            }
        }
        
        // Verificar resultado de permisos
        $permisos_faltantes = array_keys(array_filter($permisos, function($v) { return $v === false; }));
        $todos_permisos = empty($permisos_faltantes);
        
        if ($todos_permisos) {
            $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Permisos suficientes para todas las operaciones necesarias"];
            log_instalador("Verificación de permisos: Todos los permisos necesarios disponibles", 'info');
        } else {
            $respuesta['messages'][] = ['tipo' => 'warning', 'texto' => "Faltan algunos permisos: " . implode(', ', $permisos_faltantes)];
            log_instalador("Permisos faltantes en BD: " . implode(', ', $permisos_faltantes), 'warning');
        }
        
        // Permisos críticos para la instalación
        $permisos_criticos = ['CREATE', 'INSERT', 'SELECT', 'UPDATE'];
        $tiene_permisos_criticos = true;
        
        foreach ($permisos_criticos as $permiso) {
            if (!isset($permisos[$permiso]) || !$permisos[$permiso]) {
                $tiene_permisos_criticos = false;
                break;
            }
        }
        
        // Verificar si tiene permisos suficientes para la instalación básica
        if ($tiene_permisos_criticos) {
            $respuesta['success'] = true;
            $respuesta['validated'] = true; // Marcar explícitamente como validada
            $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Permisos mínimos para la instalación verificados correctamente"];
            log_instalador("Validación de BD completada con éxito: host=$host, db=$name", 'success');
        } else {
            $respuesta['messages'][] = ['tipo' => 'error', 'texto' => "No tiene los permisos mínimos necesarios (CREATE, INSERT, SELECT, UPDATE) para instalar el sistema"];
            log_instalador("Permisos críticos faltantes. No se puede continuar con esta configuración", 'error');
        }
        
        // Cerrar conexiones
        $mysqli->close();
        $mysqli_base->close();
        
        // Guardar el estado de validación en la sesión para su recuperación posterior
        if (isset($_SESSION)) {
            $_SESSION['db_verificada'] = $respuesta['success'];
            $_SESSION['db_permisos'] = $respuesta['permissions'];
            $_SESSION['db_server_info'] = $respuesta['server_info'];
            
            log_instalador("Estado de verificación de BD guardado en sesión: " . 
                          ($respuesta['success'] ? 'VERIFICADA' : 'NO VERIFICADA'), 
                          $respuesta['success'] ? 'info' : 'warning');
        }
        
    } catch (Exception $e) {
        $respuesta['success'] = false;
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => $e->getMessage()];
    }
    
    return $respuesta;
}

/**
 * Ejecuta el script SQL para crear las tablas del sistema
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @param string $sql_path Ruta al archivo SQL
 * @return array Respuesta con el estado y mensajes
 */
function ejecutarScriptSQL($host, $user, $pass, $name, $sql_path) {
    $respuesta = [
        'success' => false,
        'messages' => [],
        'errors' => [],
        'statements_processed' => 0,
        'statements_success' => 0,
        'execution_time' => 0
    ];
    
    try {
        // Verificar que el archivo existe y es legible
        if (!file_exists($sql_path) || !is_readable($sql_path)) {
            log_instalador("Error: Archivo SQL no encontrado o ilegible: $sql_path", 'error');
            throw new Exception("El archivo SQL no existe o no es legible: $sql_path");
        }
        
        // Obtener el tamaño del archivo para verificar si es muy grande
        $file_size = filesize($sql_path);
        $file_size_mb = round($file_size / (1024 * 1024), 2);
        
        $respuesta['messages'][] = ['tipo' => 'info', 'texto' => "Tamaño del archivo SQL: $file_size_mb MB"];
        log_instalador("Tamaño del archivo SQL: $file_size_mb MB", 'info');
        
        // Si el archivo es muy grande, podría dar problemas
        $execute_in_chunks = ($file_size > 1024 * 1024); // Más de 1MB
        
        // Registramos el método que usaremos
        log_instalador($execute_in_chunks ? 
            "Ejecutando SQL en partes debido al tamaño del archivo" : 
            "Ejecutando SQL con método estándar", 'info');
        
        // Conectar a la base de datos
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos: " . $mysqli->connect_error);
        }
        
        // Aumentar límites para la ejecución
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
        set_time_limit(300); // 5 minutos
        
        // Registrar tiempo de inicio para monitorear rendimiento
        $tiempo_inicio = microtime(true);
        
        if ($execute_in_chunks) {
            // Ejecutar en partes para archivos grandes
            $sql_content = file_get_contents($sql_path);
            
            // Dividir en sentencias individuales de manera más robusta
            // Esto maneja mejor los delimitadores y procedimientos almacenados
            $statements = [];
            $current = '';
            $delimiter = ';';
            $in_quote = false;
            $quote_char = '';
            $in_comment = false;
            
            for ($i = 0; $i < strlen($sql_content); $i++) {
                $char = $sql_content[$i];
                $next_char = ($i < strlen($sql_content) - 1) ? $sql_content[$i + 1] : '';
                
                // Manejar comentarios
                if (!$in_quote && $char == '-' && $next_char == '-') {
                    $in_comment = true;
                }
                
                if ($in_comment && $char == "\n") {
                    $in_comment = false;
                }
                
                // Saltar si estamos en comentario
                if ($in_comment) {
                    $current .= $char;
                    continue;
                }
                
                // Manejar cadenas entrecomilladas
                if (($char == "'" || $char == '"') && (!$in_quote || $quote_char == $char)) {
                    if ($in_quote) {
                        $in_quote = false;
                        $quote_char = '';
                    } else {
                        $in_quote = true;
                        $quote_char = $char;
                    }
                }
                
                // Manejar cambio de delimitador (para procedimientos)
                if (!$in_quote && !$in_comment && trim($current) == 'DELIMITER' && $next_char == ' ') {
                    $current = '';
                    $i += 9; // saltar 'DELIMITER '
                    $delimiter = '';
                    while ($i < strlen($sql_content) && $sql_content[$i] != "\n") {
                        $delimiter .= $sql_content[$i];
                        $i++;
                    }
                    continue;
                }
                
                // Verificar fin de sentencia
                if (!$in_quote && !$in_comment && $char == $delimiter[0]) {
                    $is_delimiter = true;
                    for ($j = 1; $j < strlen($delimiter); $j++) {
                        if (($i + $j) >= strlen($sql_content) || $sql_content[$i + $j] != $delimiter[$j]) {
                            $is_delimiter = false;
                            break;
                        }
                    }
                    
                    if ($is_delimiter) {
                        $current .= $delimiter;
                        $statements[] = $current;
                        $current = '';
                        $i += (strlen($delimiter) - 1);
                        continue;
                    }
                }
                
                $current .= $char;
            }
            
            if (!empty(trim($current))) {
                $statements[] = $current;
            }
            
            $executed = 0;
            $errors = 0;
            $statements_count = count($statements);
            
            log_instalador("Procesando $statements_count sentencias SQL", 'info');
            $respuesta['statements_processed'] = $statements_count;
            
            // Agrupamos sentencias similares para mejor rendimiento (CREATE TABLE, INSERT, etc)
            $stmt_groups = [];
            foreach ($statements as $idx => $stmt) {
                $trimmed = trim($stmt);
                if (empty($trimmed)) continue;
                
                // Agrupar por tipo de sentencia para optimizar la ejecución
                $stmt_type = 'other';
                if (preg_match('/^CREATE\s+TABLE/i', $trimmed)) {
                    $stmt_type = 'create_table';
                } elseif (preg_match('/^INSERT\s+INTO/i', $trimmed)) {
                    $stmt_type = 'insert';
                } elseif (preg_match('/^ALTER\s+TABLE/i', $trimmed)) {
                    $stmt_type = 'alter';
                }
                
                if (!isset($stmt_groups[$stmt_type])) {
                    $stmt_groups[$stmt_type] = [];
                }
                $stmt_groups[$stmt_type][] = $trimmed;
            }
            
            // Ejecutar las sentencias por grupos (primero CREATE, luego ALTER, después INSERT)
            $execution_order = ['create_table', 'alter', 'insert', 'other'];
            
            foreach ($execution_order as $group) {
                if (isset($stmt_groups[$group])) {
                    foreach ($stmt_groups[$group] as $stmt) {
                        if (!empty($stmt)) {
                            try {
                                if ($mysqli->query($stmt)) {
                                    $executed++;
                                } else {
                                    $errors++;
                                    $error_msg = $mysqli->error . " en: " . substr($stmt, 0, 100) . "...";
                                    $respuesta['errors'][] = $error_msg;
                                    log_instalador("Error SQL: $error_msg", 'error');
                                }
                            } catch (Exception $e) {
                                $errors++;
                                $error_msg = $e->getMessage() . " en: " . substr($stmt, 0, 100) . "...";
                                $respuesta['errors'][] = $error_msg;
                                log_instalador("Excepción SQL: $error_msg", 'error');
                            }
                        }
                    }
                }
            }
            
            $respuesta['statements_success'] = $executed;
            $respuesta['messages'][] = ['tipo' => 'info', 'texto' => "Ejecutadas $executed sentencias SQL con $errors errores"];
        } else {
            // Para archivos pequeños, usar multi_query
            $sql_content = file_get_contents($sql_path);
            
            if (!$mysqli->multi_query($sql_content)) {
                throw new Exception("Error ejecutando SQL: " . $mysqli->error);
            }
            
            // Procesar todos los resultados
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            
            if ($mysqli->error) {
                $respuesta['errors'][] = $mysqli->error;
            } else {
                $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Script SQL ejecutado correctamente"];
            }
        }
        
        // Calcular tiempo de ejecución
        $tiempo_fin = microtime(true);
        $tiempo_ejecucion = round($tiempo_fin - $tiempo_inicio, 2);
        $respuesta['execution_time'] = $tiempo_ejecucion;
        
        log_instalador("Tiempo total de ejecución SQL: $tiempo_ejecucion segundos", 'info');
        
        // Si hay errores pero no son críticos
        if (count($respuesta['errors']) > 0) {
            $mensaje_error = "Se encontraron " . count($respuesta['errors']) . " errores al ejecutar el SQL";
            $respuesta['messages'][] = ['tipo' => 'warning', 'texto' => $mensaje_error];
            log_instalador($mensaje_error, 'warning');
        }
        
        // Verificar tablas críticas para asegurar que la instalación es funcional
        $tablas_criticas = ['usuarios', 'config_sistema', 'cursos', 'examenes', 'modulos']; // Corregido 'config' a 'config_sistema'
        $tablas_faltantes = [];
        
        foreach ($tablas_criticas as $tabla) {
            $result = $mysqli->query("SHOW TABLES LIKE '$tabla'");
            if (!$result || $result->num_rows == 0) {
                $tablas_faltantes[] = $tabla;
            }
        }
        
        if (!empty($tablas_faltantes)) {
            $mensaje_tablas = "Faltan algunas tablas críticas: " . implode(', ', $tablas_faltantes);
            $respuesta['messages'][] = ['tipo' => 'error', 'texto' => $mensaje_tablas];
            log_instalador($mensaje_tablas, 'error');
        }
        
        // Criterio para determinar éxito:
        // 1. Si hay menos de 5 errores y todas las tablas críticas existen -> Éxito
        // 2. Si hay hasta 10 errores pero todas las tablas críticas existen -> Advertencia pero éxito
        // 3. Si hay más de 10 errores o faltan tablas críticas -> Fallo
        
        if (empty($tablas_faltantes) && count($respuesta['errors']) <= 5) {
            $respuesta['success'] = true;
            log_instalador("Ejecución SQL exitosa", 'success');
        } else if (empty($tablas_faltantes) && count($respuesta['errors']) <= 10) {
            $respuesta['success'] = true;
            $respuesta['messages'][] = ['tipo' => 'warning', 'texto' => "La instalación puede continuar pero se encontraron algunos errores. Verifique el funcionamiento del sistema."];
            log_instalador("Ejecución SQL completada con advertencias", 'warning');
        } else {
            $respuesta['success'] = false;
            log_instalador("Ejecución SQL fallida - demasiados errores o faltan tablas críticas", 'error');
        }
        
        $mysqli->close();
        
    } catch (Exception $e) {
        $respuesta['success'] = false;
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => $e->getMessage()];
    }
    
    return $respuesta;
}
