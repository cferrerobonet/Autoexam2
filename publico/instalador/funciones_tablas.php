<?php
/**
 * Funciones para la gestión de tablas existentes en el instalador
 * Proporciona funciones para verificar, eliminar o vaciar tablas existentes
 */

// Asegurar que la función de log está disponible
if (!function_exists('log_instalador')) {
    function log_instalador($mensaje, $tipo = 'info') {
        $log_dir = __DIR__ . '/../../almacenamiento/logs/sistema/';
        
        // Asegurar que existe el directorio
        if (!file_exists($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        // Si no se puede crear, intentar usar el directorio del instalador
        if (!is_dir($log_dir) || !is_writable($log_dir)) {
            $log_dir = __DIR__ . '/';
        }
        
        $log_file = $log_dir . 'instalador.log';
        $fecha = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
        $log_mensaje = "[$fecha][$tipo][$ip] $mensaje\n";
        
        @file_put_contents($log_file, $log_mensaje, FILE_APPEND);
    }
}

/**
 * Verifica si ya existen tablas del sistema AUTOEXAM2 en la base de datos
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @return array Respuesta con información sobre tablas existentes
 */
function verificarTablasExistentes($host, $user, $pass, $name) {
    $respuesta = [
        'tablas_existen' => false,
        'cantidad_tablas' => 0,
        'tablas_encontradas' => [],
        'tablas_sistema' => [
            'usuarios', 'cursos', 'modulos', 'examenes', 'preguntas_banco',
            'respuestas_banco', 'archivos', 'calificaciones', 'curso_alumno',
            'modulo_curso', 'notificaciones', 'config_sistema', 'sesiones_activas',
            'tokens_recuperacion', 'registro_actividad', 'config_versiones', 'intentos_login'
        ],
        'messages' => []
    ];
    
    try {
        // Conectar a la base de datos específica
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos '$name': " . $mysqli->connect_error);
        }
        
        // Verificar cada tabla del sistema
        foreach ($respuesta['tablas_sistema'] as $tabla) {
            $result = $mysqli->query("SHOW TABLES LIKE '$tabla'");
            if ($result && $result->num_rows > 0) {
                $respuesta['tablas_encontradas'][] = $tabla;
            }
        }
        
        // Contar y determinar si hay tablas existentes
        $respuesta['cantidad_tablas'] = count($respuesta['tablas_encontradas']);
        $respuesta['tablas_existen'] = ($respuesta['cantidad_tablas'] > 0);
        
        // Generar mensaje adecuado
        if ($respuesta['tablas_existen']) {
            $mensaje = "Se encontraron {$respuesta['cantidad_tablas']} tablas existentes del sistema AUTOEXAM2 en la base de datos.";
            $respuesta['messages'][] = ['tipo' => 'info', 'texto' => $mensaje];
            log_instalador($mensaje, 'info');
            
            // Verificar si hay registros en las tablas principales
            $tablas_con_datos = [];
            $tablas_para_verificar = ['usuarios', 'cursos', 'examenes', 'modulos'];
            
            foreach ($tablas_para_verificar as $tabla) {
                if (in_array($tabla, $respuesta['tablas_encontradas'])) {
                    $result = $mysqli->query("SELECT COUNT(*) as total FROM `$tabla`");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        if ($row['total'] > 0) {
                            $tablas_con_datos[] = $tabla . ' (' . $row['total'] . ' registros)';
                        }
                    }
                }
            }
            
            if (!empty($tablas_con_datos)) {
                $mensaje = "Las siguientes tablas ya contienen datos: " . implode(', ', $tablas_con_datos);
                $respuesta['messages'][] = ['tipo' => 'warning', 'texto' => $mensaje];
                $respuesta['hay_datos'] = true;
                log_instalador($mensaje, 'warning');
            } else {
                $respuesta['hay_datos'] = false;
            }
        } else {
            $respuesta['messages'][] = ['tipo' => 'info', 'texto' => "No se encontraron tablas del sistema AUTOEXAM2 en la base de datos."];
            log_instalador("Base de datos limpia - no hay tablas existentes del sistema", 'info');
        }
        
        $mysqli->close();
        
    } catch (Exception $e) {
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => "Excepción al verificar tablas: " . $e->getMessage()];
        log_instalador("Excepción al verificar tablas: " . $e->getMessage(), 'error');
    }
    
    return $respuesta;
}

/**
 * Crea las tablas necesarias para el sistema AUTOEXAM2
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @return array Respuesta con estado y mensajes
 */
function crearTablasAutoexam2($host, $user, $pass, $name) {
    $respuesta = [
        'success' => false,
        'messages' => []
    ];
    
    try {
        $sql_path = realpath(__DIR__ . '/../../base_datos/migraciones/001_esquema_completo.sql');
        
        if (!$sql_path || !is_readable($sql_path)) {
            throw new Exception('No se encontró el archivo SQL para crear tablas o no es legible: ' . __DIR__ . '/../../base_datos/migraciones/001_esquema_completo.sql');
        }
        
        // Conectar a la base de datos
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos: " . $mysqli->connect_error);
        }
        
        // Ejecutar el script de creación
        $sql_content = file_get_contents($sql_path);
        
        if (!$mysqli->multi_query($sql_content)) {
            throw new Exception("Error ejecutando SQL de creación: " . $mysqli->error);
        }
        
        // Procesar todos los resultados
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
        
        if ($mysqli->error) {
            throw new Exception("Error en la creación de tablas: " . $mysqli->error);
        }
        
        $respuesta['success'] = true;
        $respuesta['messages'][] = ['tipo' => 'success', 'texto' => "Las tablas necesarias han sido creadas correctamente"];
        log_instalador("Tablas creadas correctamente", 'success');
        
        $mysqli->close();
        
    } catch (Exception $e) {
        $respuesta['success'] = false;
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => $e->getMessage()];
        log_instalador("Error al crear tablas: " . $e->getMessage(), 'error');
    }
    
    return $respuesta;
}

/**
 * Ejecuta el script SQL para eliminar todas las tablas existentes
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @return array Respuesta con estado y mensajes
 */
function eliminarTablasExistentes($host, $user, $pass, $name) {
    $respuesta = [
        'success' => false,
        'messages' => []
    ];
    
    try {
        $sql_path = realpath(__DIR__ . '/../../base_datos/mantenimiento/eliminar_todas_tablas.sql');
        
        if (!$sql_path || !is_readable($sql_path)) {
            throw new Exception('No se encontró el archivo SQL para eliminar tablas o no es legible');
        }
        
        // Conectar a la base de datos
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos: " . $mysqli->connect_error);
        }
        
        // Ejecutar el script de eliminación
        $sql_content = file_get_contents($sql_path);
        
        if (!$mysqli->multi_query($sql_content)) {
            throw new Exception("Error ejecutando SQL de eliminación: " . $mysqli->error);
        }
        
        // Procesar todos los resultados
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->next_result());
        
        $respuesta['success'] = true;
        $respuesta['messages'][] = ['tipo' => 'success', 'texto' => 'Todas las tablas existentes han sido eliminadas.'];
        log_instalador("Todas las tablas existentes han sido eliminadas de la base de datos '$name'", 'info');
        
        $mysqli->close();
        
    } catch (Exception $e) {
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => "Error al eliminar tablas: " . $e->getMessage()];
        log_instalador("Error al eliminar tablas: " . $e->getMessage(), 'error');
    }
    
    return $respuesta;
}

/**
 * Vacía todas las tablas existentes del sistema AUTOEXAM2
 * 
 * @param string $host Servidor de base de datos
 * @param string $user Usuario para conectar
 * @param string $pass Contraseña del usuario
 * @param string $name Nombre de la base de datos
 * @return array Respuesta con estado y mensajes
 */
function vaciarTablasExistentes($host, $user, $pass, $name) {
    $respuesta = [
        'success' => false,
        'messages' => [],
        'tablas_vaciadas' => 0
    ];
    
    try {
        $sql_path = realpath(__DIR__ . '/../../base_datos/mantenimiento/vaciar_todas_tablas.sql');
        
        if (!$sql_path || !is_readable($sql_path)) {
            throw new Exception('No se encontró el archivo SQL para vaciar tablas o no es legible');
        }
        
        // Conectar a la base de datos
        $mysqli = new mysqli($host, $user, $pass, $name);
        
        if ($mysqli->connect_error) {
            throw new Exception("Error conectando a la base de datos: " . $mysqli->connect_error);
        }
        
        // Ejecutar el script de vaciado
        $sql_content = file_get_contents($sql_path);
        
        if (!$mysqli->multi_query($sql_content)) {
            throw new Exception("Error ejecutando SQL de vaciado: " . $mysqli->error);
        }
        
        // Procesar todos los resultados
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->next_result());
        
        // Consultar cuántas tablas se han vaciado
        $result = $mysqli->query("SHOW TABLES");
        $tablas_vaciadas = $result ? $result->num_rows : 0;
        
        $respuesta['success'] = true;
        $respuesta['tablas_vaciadas'] = $tablas_vaciadas;
        $respuesta['messages'][] = ['tipo' => 'success', 'texto' => 'Todas las tablas existentes han sido vaciadas.'];
        log_instalador("Todas las tablas existentes ({$tablas_vaciadas} tablas) han sido vaciadas de la base de datos '$name'", 'info');
        
        $mysqli->close();
        
    } catch (Exception $e) {
        $respuesta['messages'][] = ['tipo' => 'error', 'texto' => "Error al vaciar tablas: " . $e->getMessage()];
        log_instalador("Error al vaciar tablas: " . $e->getMessage(), 'error');
    }
    
    return $respuesta;
}
