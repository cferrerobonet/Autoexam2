<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/utilidades/verifica_sesiones.php

/**
 * Utilidad para verificar la estructura de la tabla de sesiones activas
 * 
 * Este script sirve para diagnosticar y solucionar problemas relacionados
 * con la autenticación y sesiones de usuario.
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */

// Para ejecutar via CLI o como inclusión
define('CLI_MODE', php_sapi_name() === 'cli');

if (CLI_MODE) {
    // Si se ejecuta en CLI, necesitamos cargar la configuración
    if (file_exists(__DIR__ . '/../../config/config.php')) {
        require_once __DIR__ . '/../../config/config.php';
    } else {
        echo "No se puede encontrar el archivo de configuración.\n";
        exit(1);
    }
}

/**
 * Verifica la estructura de la tabla de sesiones activas
 */
function verificarTablaSesionesActivas() {
    try {
        $conexion = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // 1. Verificar si la tabla existe
        $sql = "SELECT COUNT(*) FROM information_schema.tables 
                WHERE table_schema = :dbname 
                AND table_name = 'sesiones_activas'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['dbname' => DB_NAME]);
        
        $existe = (int)$stmt->fetchColumn();
        
        if (!$existe) {
            $mensaje = "ERROR: La tabla 'sesiones_activas' no existe en la base de datos.\n";
            $mensaje .= "Ejecute el script de migración en /base_datos/migraciones/001_esquema_completo.sql\n";
            
            if (CLI_MODE) {
                echo $mensaje;
            } else {
                error_log($mensaje);
                return [
                    'error' => true,
                    'mensaje' => $mensaje
                ];
            }
        } else {
            // 2. Verificar estructura de la tabla
            $sql = "DESCRIBE sesiones_activas";
            $stmt = $conexion->query($sql);
            $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $columnasEsperadas = [
                'id_sesion', 'id_usuario', 'token', 'php_session_id', 
                'fecha_inicio', 'ultima_actividad', 'fecha_fin', 
                'ip', 'user_agent', 'activa'
            ];
            
            $faltanColumnas = array_diff($columnasEsperadas, $columnas);
            
            if (!empty($faltanColumnas)) {
                $mensaje = "ERROR: La tabla 'sesiones_activas' no tiene la estructura correcta.\n";
                $mensaje .= "Faltan las siguientes columnas: " . implode(', ', $faltanColumnas) . "\n";
                
                if (CLI_MODE) {
                    echo $mensaje;
                } else {
                    error_log($mensaje);
                    return [
                        'error' => true, 
                        'mensaje' => $mensaje
                    ];
                }
            } else {
                $mensaje = "La tabla 'sesiones_activas' existe y tiene la estructura correcta.\n";
                
                // 3. Verificar índices
                $sql = "SHOW INDEX FROM sesiones_activas WHERE Key_name = 'idx_sesiones_token'";
                $stmt = $conexion->query($sql);
                $tieneIndiceToken = $stmt->rowCount() > 0;
                
                if (!$tieneIndiceToken) {
                    $mensaje .= "Falta el índice para la columna 'token'.\n";
                }
                
                if (CLI_MODE) {
                    echo $mensaje;
                } else {
                    error_log($mensaje);
                    return [
                        'error' => false,
                        'mensaje' => $mensaje
                    ];
                }
            }
        }
        
    } catch (PDOException $e) {
        $mensaje = "ERROR de conexión a la base de datos: " . $e->getMessage() . "\n";
        
        if (CLI_MODE) {
            echo $mensaje;
            exit(1);
        } else {
            error_log($mensaje);
            return [
                'error' => true,
                'mensaje' => $mensaje
            ];
        }
    }
}

// Si se ejecuta directamente, iniciar verificación
if (CLI_MODE) {
    echo "Verificando tabla de sesiones activas...\n";
    verificarTablaSesionesActivas();
}

/**
 * Verifica si hay sesiones activas para un usuario
 */
function verificarSesionesUsuario($idUsuario) {
    try {
        $conexion = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Contar sesiones activas
        $sql = "SELECT COUNT(*) FROM sesiones_activas 
                WHERE id_usuario = :id_usuario AND activa = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id_usuario' => $idUsuario]);
        
        $count = (int)$stmt->fetchColumn();
        
        $mensaje = "El usuario ID: $idUsuario tiene $count sesión(es) activa(s).\n";
        
        if ($count > 0) {
            // Mostrar detalles de las sesiones
            $sql = "SELECT id_sesion, token, php_session_id, fecha_inicio, ultima_actividad, ip 
                    FROM sesiones_activas 
                    WHERE id_usuario = :id_usuario AND activa = 1";
            $stmt = $conexion->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            
            $sesiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $mensaje .= "Detalle de sesiones:\n";
            foreach ($sesiones as $sesion) {
                $mensaje .= "- ID: {$sesion['id_sesion']}, Token: " . substr($sesion['token'], 0, 10) . "..., ";
                $mensaje .= "PHP ID: " . substr($sesion['php_session_id'], 0, 10) . "..., ";
                $mensaje .= "Inicio: {$sesion['fecha_inicio']}, ";
                $mensaje .= "Última actividad: {$sesion['ultima_actividad']}\n";
            }
        }
        
        if (CLI_MODE) {
            echo $mensaje;
        } else {
            error_log($mensaje);
        }
        
        return [
            'count' => $count,
            'mensaje' => $mensaje,
            'sesiones' => $sesiones ?? []
        ];
        
    } catch (PDOException $e) {
        $mensaje = "ERROR al verificar sesiones de usuario: " . $e->getMessage() . "\n";
        
        if (CLI_MODE) {
            echo $mensaje;
        } else {
            error_log($mensaje);
        }
        
        return [
            'error' => true,
            'mensaje' => $mensaje
        ];
    }
}

// Para ejecutar desde CLI con un ID de usuario
if (CLI_MODE && isset($argv[1]) && is_numeric($argv[1])) {
    verificarSesionesUsuario($argv[1]);
}
