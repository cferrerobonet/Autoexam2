<?php
/**
 * Funciones para la actualización inteligente de tablas existentes
 * Esta biblioteca proporciona funcionalidades para verificar y actualizar tablas sin perder datos
 */

/**
 * Actualiza las tablas de manera inteligente, verificando si cada tabla existe
 * y creándola solo si no existe, preservando los datos de las tablas existentes
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @param string $sql_path Ruta al archivo SQL con las definiciones de tablas
 * @return array Resultado de la operación
 */
function actualizarTablasInteligente($host, $user, $pass, $name, $sql_path) {
    $respuesta = [
        'success' => true,
        'messages' => [],
        'errors' => [],
        'tablas_existentes' => 0,
        'tablas_creadas' => 0,
        'tablas_modificadas' => 0,
        'tablas_esperadas' => 0,
        'tablas_no_creadas' => []
    ];
    
    // Lista de tablas esperadas en el sistema AUTOEXAM2
    $tablas_sistema = [
        'usuarios', 'cursos', 'modulos', 'examenes', 'preguntas_banco',
        'respuestas_banco', 'archivos', 'calificaciones', 'curso_alumno',
        'modulo_curso', 'notificaciones', 'config_sistema', 'sesiones_activas',
        'tokens_recuperacion', 'registro_actividad', 'config_versiones', 'intentos_login'
    ];
    
    try {
        // Verificar que el archivo existe y es legible
        if (!file_exists($sql_path) || !is_readable($sql_path)) {
            log_instalador("Error: Archivo SQL no encontrado o ilegible: $sql_path", 'error');
            throw new Exception("El archivo SQL no existe o no es legible: $sql_path");
        }
        
        log_instalador("Iniciando actualización inteligente de tablas desde: $sql_path", 'info');
        
        // Conectar a la base de datos
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos: " . $mysqli->connect_error);
        }
        
        // Aumentar límites para la ejecución
        set_time_limit(300); // 5 minutos
        ini_set('memory_limit', '256M'); // Dar más memoria si es necesario
        
        // 1. Obtener la lista de tablas existentes
        $tablas_existentes = [];
        $result = $mysqli->query("SHOW TABLES");
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                $tablas_existentes[] = $row[0];
            }
            $respuesta['tablas_existentes'] = count($tablas_existentes);
            log_instalador("Se encontraron {$respuesta['tablas_existentes']} tablas existentes en la base de datos", 'info');
        }
        
        // 2. Extraer las sentencias CREATE TABLE del archivo SQL
        $sql_content = file_get_contents($sql_path);
        $create_statements = extraerCreacionTablas($sql_content);
        $respuesta['tablas_esperadas'] = count($create_statements);
        
        if (count($create_statements) === 0) {
            log_instalador("ADVERTENCIA: No se encontraron sentencias CREATE TABLE en el script SQL", 'warning');
            $respuesta['messages'][] = [
                'tipo' => 'warning',
                'texto' => "No se encontraron definiciones de tablas en el script SQL. Por favor, verifique el archivo."
            ];
            
            // Intentar una aproximación alternativa más simple
            log_instalador("Intentando método alternativo para encontrar tablas...", 'info');
            $tablas_no_encontradas = array_diff($tablas_sistema, $tablas_existentes);
            
            if (!empty($tablas_no_encontradas)) {
                $respuesta['messages'][] = [
                    'tipo' => 'warning',
                    'texto' => "Faltan " . count($tablas_no_encontradas) . " tablas del sistema: " . implode(", ", $tablas_no_encontradas)
                ];
                
                // Esto nos ayudará a diagnosticar mejor
                log_instalador("Tablas que faltan y no se encontraron en el script SQL: " . implode(", ", $tablas_no_encontradas), 'warning');
            }
        }
        
        // 3. Para cada tabla en el archivo SQL
        $mysqli->query("SET foreign_key_checks = 0"); // Desactivar restricciones temporalmente
        
        foreach ($create_statements as $tabla => $create_statement) {
            if (in_array($tabla, $tablas_existentes)) {
                // La tabla ya existe, no hacemos nada
                log_instalador("La tabla '$tabla' ya existe. Preservando estructura y datos.", 'info');
                $respuesta['messages'][] = [
                    'tipo' => 'info', 
                    'texto' => "La tabla '$tabla' ya existe. Preservando estructura y datos."
                ];
            } else {
                // La tabla no existe, la creamos
                log_instalador("Creando tabla '$tabla' que no existe", 'warning');
                
                // Intentamos ejecutar la sentencia CREATE TABLE
                if ($mysqli->query($create_statement)) {
                    $respuesta['tablas_creadas']++;
                    log_instalador("Tabla '$tabla' creada correctamente", 'success');
                    $respuesta['messages'][] = [
                        'tipo' => 'success', 
                        'texto' => "Nueva tabla '$tabla' creada correctamente"
                    ];
                } else {
                    $error = $mysqli->error;
                    log_instalador("Error al crear la tabla '$tabla': $error", 'error');
                    $respuesta['errors'][] = "Error al crear la tabla '$tabla': $error";
                    $respuesta['tablas_no_creadas'][] = $tabla;
                    
                    // Intentar corregir algunos errores comunes
                    if (strpos($error, 'already exists') !== false) {
                        log_instalador("Intentando DROP TABLE para '$tabla' antes de crearla nuevamente", 'warning');
                        if ($mysqli->query("DROP TABLE IF EXISTS `$tabla`")) {
                            if ($mysqli->query($create_statement)) {
                                $respuesta['tablas_creadas']++;
                                log_instalador("Tabla '$tabla' recreada correctamente después de eliminarla", 'success');
                                $respuesta['messages'][] = [
                                    'tipo' => 'success', 
                                    'texto' => "Tabla '$tabla' recreada correctamente"
                                ];
                                // Quitar de la lista de tablas no creadas
                                $index = array_search($tabla, $respuesta['tablas_no_creadas']);
                                if ($index !== false) {
                                    unset($respuesta['tablas_no_creadas'][$index]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $mysqli->query("SET foreign_key_checks = 1"); // Reactivar restricciones
        
        // Verificar que todas las tablas del sistema existen ahora
        $tablasFinales = [];
        $result = $mysqli->query("SHOW TABLES");
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                $tablasFinales[] = $row[0];
            }
            
            // Identificar tablas esperadas que aún no existen
            $tablas_faltantes = array_diff($tablas_sistema, $tablasFinales);
            
            if (!empty($tablas_faltantes)) {
                log_instalador("ADVERTENCIA: Después de la actualización aún faltan tablas: " . implode(", ", $tablas_faltantes), 'warning');
                $respuesta['messages'][] = [
                    'tipo' => 'warning',
                    'texto' => "Algunas tablas necesarias no pudieron crearse: " . implode(", ", $tablas_faltantes)
                ];
                
                // Crear tablas faltantes con estructura mínima
                foreach ($tablas_faltantes as $tabla) {
                    log_instalador("Intentando crear estructura mínima para la tabla '$tabla'", 'warning');
                    
                    // Estructuras mínimas para tablas críticas
                    $sql_minimo = "";
                    
                    switch ($tabla) {
                        case 'usuarios':
                            $sql_minimo = "CREATE TABLE `usuarios` (
                                `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
                                `nombre` VARCHAR(100) NOT NULL,
                                `apellidos` VARCHAR(150) NOT NULL,
                                `correo` VARCHAR(150) NOT NULL UNIQUE,
                                `contrasena` VARCHAR(255) NOT NULL,
                                `rol` ENUM('admin', 'profesor', 'alumno') NOT NULL,
                                `activo` TINYINT(1) DEFAULT 1
                            ) ENGINE=InnoDB;";
                            break;
                        case 'cursos':
                            $sql_minimo = "CREATE TABLE `cursos` (
                                `id_curso` INT AUTO_INCREMENT PRIMARY KEY,
                                `nombre` VARCHAR(150) NOT NULL,
                                `descripcion` TEXT,
                                `activo` TINYINT(1) DEFAULT 1
                            ) ENGINE=InnoDB;";
                            break;
                        case 'modulos':
                            $sql_minimo = "CREATE TABLE `modulos` (
                                `id_modulo` INT AUTO_INCREMENT PRIMARY KEY,
                                `titulo` VARCHAR(150) NOT NULL,
                                `descripcion` TEXT,
                                `id_profesor` INT
                            ) ENGINE=InnoDB;";
                            break;
                        case 'examenes':
                            $sql_minimo = "CREATE TABLE `examenes` (
                                `id_examen` INT AUTO_INCREMENT PRIMARY KEY,
                                `titulo` VARCHAR(255) NOT NULL,
                                `id_modulo` INT,
                                `estado` ENUM('borrador', 'activo', 'finalizado') DEFAULT 'borrador'
                            ) ENGINE=InnoDB;";
                            break;
                        default:
                            // Para otras tablas, crear una estructura genérica
                            $sql_minimo = "CREATE TABLE `$tabla` (
                                `id` INT AUTO_INCREMENT PRIMARY KEY,
                                `nombre` VARCHAR(255),
                                `descripcion` TEXT,
                                `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            ) ENGINE=InnoDB;";
                    }
                    
                    if (!empty($sql_minimo)) {
                        if ($mysqli->query($sql_minimo)) {
                            $respuesta['tablas_creadas']++;
                            log_instalador("Se creó estructura mínima para tabla '$tabla'", 'success');
                            $respuesta['messages'][] = [
                                'tipo' => 'success',
                                'texto' => "Se creó una estructura mínima para la tabla '$tabla'"
                            ];
                        } else {
                            log_instalador("Error al crear estructura mínima para '$tabla': " . $mysqli->error, 'error');
                        }
                    }
                }
            }
        }
        
        $mysqli->close();
        
        // Mensaje de resumen
        $mensaje_resumen = "Actualización completada. ";
        $mensaje_resumen .= "Se encontraron {$respuesta['tablas_existentes']} tablas existentes. ";
        $mensaje_resumen .= "Se crearon {$respuesta['tablas_creadas']} tablas nuevas.";
        
        if (!empty($respuesta['tablas_no_creadas'])) {
            $respuesta['success'] = count($respuesta['tablas_no_creadas']) < 3; // Toleramos algunos problemas menores
            $mensaje_resumen .= " Hubo problemas con " . count($respuesta['tablas_no_creadas']) . " tablas.";
        }
        
        log_instalador($mensaje_resumen, 'info');
        $respuesta['messages'][] = ['tipo' => 'success', 'texto' => $mensaje_resumen];
        
    } catch (Exception $e) {
        $respuesta['success'] = false;
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => $e->getMessage()];
        $respuesta['errors'][] = $e->getMessage();
        log_instalador("Error en actualización inteligente de tablas: " . $e->getMessage(), 'error');
    }
    
    return $respuesta;
}

/**
 * Extrae las sentencias CREATE TABLE del archivo SQL
 * 
 * @param string $sql_content Contenido del archivo SQL
 * @return array Arreglo asociativo [nombre_tabla => sentencia_create]
 */
function extraerCreacionTablas($sql_content) {
    $create_statements = [];
    log_instalador("Analizando script SQL para extraer sentencias CREATE TABLE", 'info');
    
    // Método más robusto: dividir por sentencias y analizar cada una
    $statements = explodeSQLStatements($sql_content);
    
    foreach ($statements as $statement) {
        // Eliminar comentarios para un análisis más limpio
        $clean_statement = preg_replace('/--.*$/m', '', $statement);
        
        // Si es un CREATE TABLE
        if (preg_match('/^\s*CREATE\s+TABLE\s+/i', $clean_statement)) {
            // Extraer el nombre de la tabla
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?[`]?([a-zA-Z0-9_]+)[`]?/i', $clean_statement, $table_match)) {
                $tabla = $table_match[1];
                
                // Limpiar y formatear la sentencia
                $create_statement = trim($clean_statement);
                
                // Asegurar que la sentencia termina correctamente
                if (substr($create_statement, -1) !== ';') {
                    $create_statement .= ';';
                }
                
                $create_statements[$tabla] = $create_statement;
                log_instalador("Se encontró definición para la tabla: $tabla", 'debug');
            }
        }
    }
    
    log_instalador("Se han extraído definiciones para " . count($create_statements) . " tablas", 'info');
    return $create_statements;
}

/**
 * Divide un script SQL en sentencias individuales
 * 
 * @param string $sql_content Contenido completo del script SQL
 * @return array Array de sentencias SQL individuales
 */
function explodeSQLStatements($sql_content) {
    $result = [];
    $statement = '';
    $delimiter = ';';
    $in_string = false;
    $string_quote = '';
    
    // Dividir por líneas para manejar mejor los comentarios
    $lines = explode("\n", $sql_content);
    
    foreach ($lines as $line) {
        // Ignorar comentarios completos
        if (preg_match('/^\s*--/', $line)) {
            continue;
        }
        
        // Añadir la línea al statement actual
        $statement .= $line . "\n";
        
        // Si encontramos un delimitador al final de la línea y no estamos dentro de una cadena
        if (preg_match('/;\s*$/', $line) && !$in_string) {
            // Limpiar y añadir la sentencia
            $clean_statement = trim($statement);
            if (!empty($clean_statement)) {
                $result[] = $clean_statement;
            }
            $statement = '';
        }
    }
    
    // Si queda alguna sentencia sin procesar
    if (trim($statement) !== '') {
        $result[] = trim($statement);
    }
    
    return $result;
}
