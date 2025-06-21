<?php
/**
 * Modelo de Configuración - AUTOEXAM2
 * 
 * Gestiona la lectura y escritura de configuración del sistema
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class ConfiguracionModelo {
    private $rutaEnv;
    private $rutaConfig;
    
    public function __construct() {
        $this->rutaEnv = ROOT_PATH . '/.env';
        $this->rutaConfig = ROOT_PATH . '/config/config.php';
    }
    
    /**
     * Leer archivo .env y retornar array con configuración
     */
    public function leerConfiguracionEnv() {
        $config = [];
        
        if (!file_exists($this->rutaEnv)) {
            return $config;
        }
        
        $lineas = file($this->rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            
            // Saltar comentarios
            if (strpos($linea, '#') === 0) {
                continue;
            }
            
            // Procesar variables
            if (strpos($linea, '=') !== false) {
                list($clave, $valor) = explode('=', $linea, 2);
                $clave = trim($clave);
                $valor = trim($valor, '"\'');
                $config[$clave] = $valor;
            }
        }
        
        return $config;
    }
    
    /**
     * Actualizar configuración en archivo .env
     */
    public function actualizarConfiguracionEnv($datos) {
        try {
            $configuracionActual = $this->leerConfiguracionEnv();
            
            // Actualizar con nuevos datos
            foreach ($datos as $clave => $valor) {
                $configuracionActual[$clave] = $valor;
            }
            
            // Preparar contenido del archivo
            $contenido = "# Configuración AUTOEXAM2\n";
            $contenido .= "# Generado automáticamente - " . date('Y-m-d H:i:s') . "\n\n";
            
            // Agrupar configuraciones
            $grupos = [
                'APP' => ['APP_NAME', 'BASE_URL', 'DEBUG', 'MODO_MANTENIMIENTO'],
                'DATABASE' => ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_CHARSET'],
                'SMTP' => ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_SECURE', 'SMTP_FROM_EMAIL', 'SMTP_FROM_NAME'],
                'ARCHIVOS' => ['UPLOAD_MAX_SIZE', 'ALLOWED_EXTENSIONS', 'STORAGE_PATH']
            ];
            
            foreach ($grupos as $nombreGrupo => $claves) {
                $contenido .= "# $nombreGrupo\n";
                foreach ($claves as $clave) {
                    if (isset($configuracionActual[$clave])) {
                        $valor = $configuracionActual[$clave];
                        // Escapar valores si contienen espacios
                        if (strpos($valor, ' ') !== false) {
                            $valor = '"' . $valor . '"';
                        }
                        $contenido .= "$clave=$valor\n";
                    }
                }
                $contenido .= "\n";
            }
            
            // Crear respaldo del archivo actual
            if (file_exists($this->rutaEnv)) {
                $rutaRespaldo = $this->rutaEnv . '.backup.' . date('Y-m-d_H-i-s');
                copy($this->rutaEnv, $rutaRespaldo);
            }
            
            // Escribir nuevo archivo
            $resultado = file_put_contents($this->rutaEnv, $contenido);
            
            if ($resultado === false) {
                throw new Exception('No se pudo escribir el archivo .env');
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error actualizando configuración: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar configuración de base de datos
     */
    public function validarConfiguracionBD($host, $nombre, $usuario, $contrasena, $puerto = 3306) {
        try {
            $dsn = "mysql:host=$host;port=$puerto;dbname=$nombre;charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $conexion = new PDO($dsn, $usuario, $contrasena, $opciones);
            
            // Probar una consulta simple
            $consulta = $conexion->query("SELECT 1");
            
            return [
                'valido' => true,
                'mensaje' => 'Conexión exitosa'
            ];
            
        } catch (PDOException $e) {
            return [
                'valido' => false,
                'mensaje' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Probar configuración SMTP
     */
    public function probarConfiguracionSMTP($host, $puerto, $usuario, $contrasena, $seguridad = 'tls') {
        try {
            require_once ROOT_PATH . '/vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $usuario;
            $mail->Password = $contrasena;
            $mail->SMTPSecure = $seguridad;
            $mail->Port = $puerto;
            
            // Probar conexión
            $mail->smtpConnect();
            $mail->smtpClose();
            
            return [
                'valido' => true,
                'mensaje' => 'Conexión SMTP exitosa'
            ];
            
        } catch (Exception $e) {
            return [
                'valido' => false,
                'mensaje' => 'Error SMTP: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Probar configuración FTP/SFTP
     */
    public function probarConfiguracionFTP($host, $puerto, $usuario, $contrasena, $seguro = false) {
        try {
            // Crear conexión FTP
            if ($seguro) {
                $conexion = ftp_ssl_connect($host, $puerto, 10);
            } else {
                $conexion = ftp_connect($host, $puerto, 10);
            }
            
            if (!$conexion) {
                return [
                    'valido' => false,
                    'mensaje' => 'No se pudo conectar al servidor FTP'
                ];
            }
            
            // Intentar login
            $login = ftp_login($conexion, $usuario, $contrasena);
            
            if (!$login) {
                ftp_close($conexion);
                return [
                    'valido' => false,
                    'mensaje' => 'Credenciales FTP incorrectas'
                ];
            }
            
            // Probar listar directorio
            $lista = ftp_nlist($conexion, '.');
            ftp_close($conexion);
            
            return [
                'valido' => true,
                'mensaje' => 'Conexión FTP exitosa'
            ];
            
        } catch (Exception $e) {
            return [
                'valido' => false,
                'mensaje' => 'Error FTP: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear backup de configuración
     */
    public function crearBackupConfiguracion() {
        try {
            $fechaHora = date('Y-m-d_H-i-s');
            $nombreBackup = "configuracion_backup_$fechaHora.zip";
            $rutaBackup = ROOT_PATH . '/almacenamiento/copias/configuracion/' . $nombreBackup;
            
            // Crear directorio si no existe
            $directorioBackup = dirname($rutaBackup);
            if (!is_dir($directorioBackup)) {
                mkdir($directorioBackup, 0755, true);
            }
            
            // Crear zip
            $zip = new ZipArchive();
            
            if ($zip->open($rutaBackup, ZipArchive::CREATE) !== TRUE) {
                throw new Exception('No se pudo crear el archivo ZIP');
            }
            
            // Agregar archivo .env
            if (file_exists($this->rutaEnv)) {
                $zip->addFile($this->rutaEnv, '.env');
            }
            
            // Agregar archivos de configuración
            $archivosConfig = [
                ROOT_PATH . '/config/config.php',
                ROOT_PATH . '/config/database.php',
                ROOT_PATH . '/config/storage.php'
            ];
            
            foreach ($archivosConfig as $archivo) {
                if (file_exists($archivo)) {
                    $zip->addFile($archivo, 'config/' . basename($archivo));
                }
            }
            
            // Agregar información del sistema
            $infoSistema = json_encode([
                'fecha_backup' => date('Y-m-d H:i:s'),
                'version_php' => PHP_VERSION,
                'sistema_operativo' => php_uname(),
                'info_sistema' => $this->obtenerInfoSistema()
            ], JSON_PRETTY_PRINT);
            
            $zip->addFromString('info_sistema.json', $infoSistema);
            
            $zip->close();
            
            return [
                'exito' => true,
                'archivo' => $nombreBackup,
                'ruta' => $rutaBackup,
                'tamaño' => filesize($rutaBackup)
            ];
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Restaurar backup de configuración
     */
    public function restaurarBackupConfiguracion($nombreArchivo) {
        try {
            $rutaBackup = ROOT_PATH . '/almacenamiento/copias/configuracion/' . $nombreArchivo;
            
            if (!file_exists($rutaBackup)) {
                throw new Exception('El archivo de backup no existe');
            }
            
            // Crear respaldo de seguridad antes de restaurar
            $respaldoSeguridad = $this->crearBackupConfiguracion();
            
            if (!$respaldoSeguridad['exito']) {
                throw new Exception('No se pudo crear respaldo de seguridad');
            }
            
            // Extraer backup
            $zip = new ZipArchive();
            
            if ($zip->open($rutaBackup) !== TRUE) {
                throw new Exception('No se pudo abrir el archivo de backup');
            }
            
            // Restaurar .env
            if ($zip->locateName('.env') !== false) {
                $contenidoEnv = $zip->getFromName('.env');
                
                if ($contenidoEnv !== false) {
                    file_put_contents($this->rutaEnv, $contenidoEnv);
                }
            }
            
            // Restaurar archivos de configuración
            $archivosConfig = ['config.php', 'database.php', 'storage.php'];
            
            foreach ($archivosConfig as $archivo) {
                $nombreZip = 'config/' . $archivo;
                
                if ($zip->locateName($nombreZip) !== false) {
                    $contenido = $zip->getFromName($nombreZip);
                    
                    if ($contenido !== false) {
                        $rutaDestino = ROOT_PATH . '/config/' . $archivo;
                        file_put_contents($rutaDestino, $contenido);
                    }
                }
            }
            
            $zip->close();
            
            return [
                'exito' => true,
                'mensaje' => 'Configuración restaurada correctamente',
                'respaldo_seguridad' => $respaldoSeguridad['archivo']
            ];
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Listar backups disponibles
     */
    public function listarBackups() {
        $directorioBackup = ROOT_PATH . '/almacenamiento/copias/configuracion';
        
        if (!is_dir($directorioBackup)) {
            return [];
        }
        
        $archivos = glob($directorioBackup . '/configuracion_backup_*.zip');
        $backups = [];
        
        foreach ($archivos as $archivo) {
            $nombre = basename($archivo);
            $backups[] = [
                'nombre' => $nombre,
                'ruta' => $archivo,
                'tamaño' => filesize($archivo),
                'fecha' => filemtime($archivo),
                'fecha_formateada' => date('Y-m-d H:i:s', filemtime($archivo))
            ];
        }
        
        // Ordenar por fecha descendente
        usort($backups, function($a, $b) {
            return $b['fecha'] - $a['fecha'];
        });
        
        return $backups;
    }
    
    /**
     * Obtener información del sistema
     */
    public function obtenerInfoSistema() {
        return [
            'php_version' => PHP_VERSION,
            'servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
            'memoria_limite' => ini_get('memory_limit'),
            'tiempo_ejecucion' => ini_get('max_execution_time'),
            'upload_max_size' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'extensiones_requeridas' => $this->verificarExtensionesRequeridas()
        ];
    }
    
    /**
     * Verificar extensiones de PHP requeridas
     */
    private function verificarExtensionesRequeridas() {
        $extensiones = [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'json' => extension_loaded('json'),
            'curl' => extension_loaded('curl'),
            'openssl' => extension_loaded('openssl'),
            'fileinfo' => extension_loaded('fileinfo')
        ];
        
        return $extensiones;
    }
    
    /**
     * Obtener logs del sistema
     */
    public function obtenerLogs($tipo = 'app', $limite = 100) {
        $rutasLogs = [
            'app' => APP_LOGS_PATH . '/app.log',
            'errores' => ERROR_LOGS_PATH . '/php_errors.log',
            'acceso' => ACCESS_LOGS_PATH . '/access.log',
            'sistema' => SYSTEM_LOGS_PATH . '/system.log'
        ];
        
        if (!isset($rutasLogs[$tipo]) || !file_exists($rutasLogs[$tipo])) {
            return [];
        }
        
        $lineas = file($rutasLogs[$tipo]);
        
        if ($lineas === false) {
            return [];
        }
        
        // Obtener las últimas líneas
        $lineas = array_slice($lineas, -$limite);
        
        // Procesar y formatear logs
        $logs = [];
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (!empty($linea)) {
                $logs[] = [
                    'timestamp' => $this->extraerTimestamp($linea),
                    'nivel' => $this->extraerNivel($linea),
                    'mensaje' => $linea
                ];
            }
        }
        
        return array_reverse($logs);
    }
    
    /**
     * Limpiar logs antiguos
     */
    public function limpiarLogs($dias = 30) {
        $directorios = [
            APP_LOGS_PATH,
            ERROR_LOGS_PATH,
            ACCESS_LOGS_PATH,
            SYSTEM_LOGS_PATH
        ];
        
        $archivosEliminados = 0;
        $fechaLimite = time() - ($dias * 24 * 60 * 60);
        
        foreach ($directorios as $directorio) {
            if (!is_dir($directorio)) continue;
            
            $archivos = glob($directorio . '/*.log*');
            
            foreach ($archivos as $archivo) {
                if (filemtime($archivo) < $fechaLimite) {
                    if (unlink($archivo)) {
                        $archivosEliminados++;
                    }
                }
            }
        }
        
        return $archivosEliminados;
    }
    
    /**
     * Extraer timestamp de línea de log
     */
    private function extraerTimestamp($linea) {
        if (preg_match('/^\[([^\]]+)\]/', $linea, $matches)) {
            return $matches[1];
        }
        return date('Y-m-d H:i:s');
    }
    
    /**
     * Extraer nivel de log de la línea
     */
    private function extraerNivel($linea) {
        if (stripos($linea, 'error') !== false) return 'error';
        if (stripos($linea, 'warning') !== false) return 'warning';
        if (stripos($linea, 'info') !== false) return 'info';
        return 'debug';
    }
}
