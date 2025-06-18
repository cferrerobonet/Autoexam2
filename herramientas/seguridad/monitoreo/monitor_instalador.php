#!/usr/bin/env php
<?php
/**
 * AUTOEXAM2 - Monitor de Seguridad del Instalador
 * 
 * Este script monitorea intentos de acceso al instalador cuando el sistema ya está instalado
 * y puede configurarse para enviar alertas automáticas.
 * 
 * Uso:
 *   php monitor_instalador.php [--alert-email=email@domain.com] [--daemon] [--check-once]
 * 
 * Opciones:
 *   --alert-email    Email para enviar alertas de seguridad
 *   --daemon         Ejecutar como demonio (monitoreo continuo)
 *   --check-once     Ejecutar una sola verificación
 *   --help           Mostrar ayuda
 */

// Verificar que se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

class MonitorInstalador {
    
    private $root_path;
    private $log_path;
    private $env_path;
    private $lock_path;
    private $config_path;
    private $alert_email = null;
    private $daemon_mode = false;
    private $check_once = false;
    private $last_check_file;
    
    public function __construct($argv) {
        $this->root_path = __DIR__;
        $this->log_path = $this->root_path . '/tmp/logs';
        $this->env_path = $this->root_path . '/.env';
        $this->lock_path = $this->root_path . '/publico/instalador/.lock';
        $this->config_path = $this->root_path . '/config/config.php';
        $this->last_check_file = $this->log_path . '/monitor_last_check.txt';
        
        $this->parseArguments($argv);
        $this->crearDirectorioLogs();
    }
    
    private function parseArguments($argv) {
        foreach ($argv as $arg) {
            if (strpos($arg, '--alert-email=') === 0) {
                $this->alert_email = substr($arg, 14);
            } elseif ($arg === '--daemon') {
                $this->daemon_mode = true;
            } elseif ($arg === '--check-once') {
                $this->check_once = true;
            } elseif ($arg === '--help') {
                $this->mostrarAyuda();
                exit(0);
            }
        }
    }
    
    private function mostrarAyuda() {
        echo "
╔══════════════════════════════════════════════════════════════════╗
║            AUTOEXAM2 - Monitor de Seguridad del Instalador       ║
╚══════════════════════════════════════════════════════════════════╝

DESCRIPCIÓN:
  Monitorea intentos de acceso al instalador cuando el sistema ya está 
  instalado y envía alertas de seguridad.

USO:
  php monitor_instalador.php [opciones]

OPCIONES:
  --alert-email=email@domain.com    Email para alertas de seguridad
  --daemon                         Ejecutar como demonio (continuo)
  --check-once                     Una sola verificación
  --help                           Mostrar esta ayuda

EJEMPLOS:
  # Verificación única
  php monitor_instalador.php --check-once
  
  # Monitoreo continuo con alertas por email
  php monitor_instalador.php --daemon --alert-email=admin@domain.com
  
  # Configurar como cron (cada 15 minutos)
  echo '*/15 * * * * /usr/bin/php /path/to/monitor_instalador.php --check-once --alert-email=admin@domain.com' | crontab -

ARCHIVOS DE LOG:
  tmp/logs/monitor_seguridad.log     - Log del monitor
  tmp/logs/intentos_instalador.log   - Intentos de acceso detectados
  tmp/logs/monitor_last_check.txt    - Última verificación

";
    }
    
    private function crearDirectorioLogs() {
        if (!is_dir($this->log_path)) {
            mkdir($this->log_path, 0755, true);
        }
    }
    
    private function log($mensaje, $tipo = 'info', $archivo = 'monitor_seguridad.log') {
        $log_file = $this->log_path . '/' . $archivo;
        $timestamp = date('Y-m-d H:i:s');
        $pid = getmypid();
        $entry = "[$timestamp][$tipo][PID:$pid] $mensaje\n";
        
        file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
        
        // También mostrar en consola
        $prefix = [
            'info' => '🔍',
            'warning' => '⚠️ ',
            'error' => '❌',
            'alert' => '🚨',
            'success' => '✅'
        ];
        
        echo ($prefix[$tipo] ?? '📝') . " [$timestamp] $mensaje\n";
    }
    
    public function ejecutar() {
        $this->log('Iniciando monitor de seguridad del instalador', 'info');
        
        if ($this->daemon_mode) {
            $this->ejecutarDemonio();
        } else {
            $this->verificarSeguridad();
        }
    }
    
    private function ejecutarDemonio() {
        $this->log('Modo demonio activado - Monitoreo continuo iniciado', 'info');
        
        // Configurar manejadores de señales para terminación limpia
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [$this, 'terminarDemonio']);
            pcntl_signal(SIGINT, [$this, 'terminarDemonio']);
        }
        
        $intervalo = 300; // 5 minutos entre verificaciones
        
        while (true) {
            $this->verificarSeguridad();
            
            // Procesar señales
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
            
            $this->log("Siguiente verificación en $intervalo segundos", 'info');
            sleep($intervalo);
        }
    }
    
    public function terminarDemonio() {
        $this->log('Recibida señal de terminación - Finalizando monitor', 'info');
        exit(0);
    }
    
    private function verificarSeguridad() {
        $this->log('Iniciando verificación de seguridad', 'info');
        
        // 1. Verificar estado de instalación
        $instalacion_completa = $this->verificarInstalacionCompleta();
        
        if (!$instalacion_completa) {
            $this->log('Sistema no instalado completamente - Monitor en pausa', 'warning');
            return;
        }
        
        // 2. Verificar logs de acceso al instalador
        $this->verificarLogsAcceso();
        
        // 3. Verificar integridad de archivos críticos
        $this->verificarIntegridadArchivos();
        
        // 4. Verificar intentos de acceso desde logs web
        $this->verificarLogsWeb();
        
        // 5. Actualizar timestamp de última verificación
        file_put_contents($this->last_check_file, time());
        
        $this->log('Verificación de seguridad completada', 'success');
    }
    
    private function verificarInstalacionCompleta() {
        $archivos_criticos = [
            '.env' => $this->env_path,
            'instalador/.lock' => $this->lock_path,
            'config/config.php' => $this->config_path
        ];
        
        $faltantes = [];
        foreach ($archivos_criticos as $nombre => $ruta) {
            if (!file_exists($ruta)) {
                $faltantes[] = $nombre;
            }
        }
        
        if (!empty($faltantes)) {
            $this->log('Archivos críticos faltantes: ' . implode(', ', $faltantes), 'warning');
            return false;
        }
        
        return true;
    }
    
    private function verificarLogsAcceso() {
        $log_instalador = $this->log_path . '/instalador.log';
        
        if (!file_exists($log_instalador)) {
            return;
        }
        
        // Obtener timestamp de última verificación
        $ultima_verificacion = 0;
        if (file_exists($this->last_check_file)) {
            $ultima_verificacion = (int)file_get_contents($this->last_check_file);
        }
        
        // Leer log del instalador línea por línea
        $handle = fopen($log_instalador, 'r');
        if ($handle) {
            $intentos_sospechosos = [];
            
            while (($line = fgets($handle)) !== false) {
                // Buscar entradas después de la última verificación
                if (preg_match('/\\[(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2})\\]\\[warning\\]\\[(.*?)\\] Instalación previa detectada/', $line, $matches)) {
                    $timestamp_log = strtotime($matches[1]);
                    $ip = $matches[2];
                    
                    if ($timestamp_log > $ultima_verificacion) {
                        $intentos_sospechosos[] = [
                            'timestamp' => $matches[1],
                            'ip' => $ip,
                            'mensaje' => 'Intento de acceso al instalador'
                        ];
                    }
                }
            }
            
            fclose($handle);
            
            if (!empty($intentos_sospechosos)) {
                $this->procesarIntentosSospechosos($intentos_sospechosos);
            }
        }
    }
    
    private function verificarIntegridadArchivos() {
        // Verificar que los archivos críticos no han sido modificados sospechosamente
        $archivos_criticos = [
            '.env' => $this->env_path,
            'config/config.php' => $this->config_path
        ];
        
        $archivo_checksums = $this->log_path . '/checksums.txt';
        $checksums_actuales = [];
        $checksums_anteriores = [];
        
        // Calcular checksums actuales
        foreach ($archivos_criticos as $nombre => $ruta) {
            if (file_exists($ruta)) {
                $checksums_actuales[$nombre] = md5_file($ruta);
            }
        }
        
        // Cargar checksums anteriores si existen
        if (file_exists($archivo_checksums)) {
            $checksums_anteriores = json_decode(file_get_contents($archivo_checksums), true) ?: [];
        }
        
        // Comparar y detectar cambios
        foreach ($checksums_actuales as $archivo => $checksum) {
            if (isset($checksums_anteriores[$archivo]) && $checksums_anteriores[$archivo] !== $checksum) {
                $this->log("Archivo crítico modificado: $archivo", 'alert');
                $this->enviarAlerta("Archivo crítico modificado", "El archivo $archivo ha sido modificado desde la última verificación");
            }
        }
        
        // Guardar checksums actuales
        file_put_contents($archivo_checksums, json_encode($checksums_actuales, JSON_PRETTY_PRINT));
    }
    
    private function verificarLogsWeb() {
        // Buscar en logs comunes de servidores web
        $logs_web = [
            '/var/log/apache2/access.log',
            '/var/log/nginx/access.log',
            '/var/log/httpd/access_log',
            '/opt/lampp/logs/access_log'
        ];
        
        foreach ($logs_web as $log_web) {
            if (file_exists($log_web) && is_readable($log_web)) {
                $this->analizarLogWeb($log_web);
            }
        }
    }
    
    private function analizarLogWeb($log_file) {
        // Analizar últimas 1000 líneas del log web
        $cmd = "tail -1000 " . escapeshellarg($log_file) . " | grep '/instalador/'";
        $output = shell_exec($cmd);
        
        if ($output) {
            $lineas = explode("\n", trim($output));
            $intentos_recientes = [];
            
            foreach ($lineas as $linea) {
                if (empty($linea)) continue;
                
                // Extraer IP y timestamp del log
                if (preg_match('/^(\S+).*?\\[(.*?)\\].*?"[^"]*\\/instalador\\//', $linea, $matches)) {
                    $ip = $matches[1];
                    $timestamp = $matches[2];
                    
                    $intentos_recientes[] = [
                        'ip' => $ip,
                        'timestamp' => $timestamp,
                        'linea_completa' => $linea
                    ];
                }
            }
            
            if (!empty($intentos_recientes)) {
                $this->log("Detectados " . count($intentos_recientes) . " accesos al instalador en logs web", 'warning');
                $this->registrarIntentosWeb($intentos_recientes);
            }
        }
    }
    
    private function procesarIntentosSospechosos($intentos) {
        $count = count($intentos);
        $this->log("Detectados $count intentos sospechosos de acceso al instalador", 'alert');
        
        // Registrar intentos en log específico
        $log_intentos = $this->log_path . '/intentos_instalador.log';
        foreach ($intentos as $intento) {
            $entry = "[{$intento['timestamp']}][{$intento['ip']}] {$intento['mensaje']}\n";
            file_put_contents($log_intentos, $entry, FILE_APPEND | LOCK_EX);
        }
        
        // Analizar patrones sospechosos
        $ips = array_column($intentos, 'ip');
        $ips_frecuentes = array_count_values($ips);
        
        foreach ($ips_frecuentes as $ip => $frecuencia) {
            if ($frecuencia > 3) {
                $mensaje = "IP $ip realizó $frecuencia intentos de acceso al instalador";
                $this->log($mensaje, 'alert');
                $this->enviarAlerta("Intentos múltiples detectados", $mensaje);
            }
        }
        
        // Enviar alerta general
        if ($count > 0) {
            $this->enviarAlerta(
                "Intentos de acceso al instalador detectados",
                "Se detectaron $count intentos de acceso al instalador en un sistema ya instalado.\n\nDetalles:\n" . 
                implode("\n", array_map(function($i) { return "- {$i['timestamp']} desde {$i['ip']}"; }, $intentos))
            );
        }
    }
    
    private function registrarIntentosWeb($intentos) {
        $log_intentos = $this->log_path . '/intentos_instalador.log';
        
        foreach ($intentos as $intento) {
            $entry = "[{$intento['timestamp']}][{$intento['ip']}] Acceso web al instalador: {$intento['linea_completa']}\n";
            file_put_contents($log_intentos, $entry, FILE_APPEND | LOCK_EX);
        }
    }
    
    private function enviarAlerta($asunto, $mensaje) {
        if (!$this->alert_email) {
            $this->log("Alerta de seguridad (no configurado email): $asunto", 'warning');
            return;
        }
        
        $hostname = gethostname() ?: 'servidor-desconocido';
        $timestamp = date('Y-m-d H:i:s');
        
        $email_body = "ALERTA DE SEGURIDAD - AUTOEXAM2\n\n";
        $email_body .= "Servidor: $hostname\n";
        $email_body .= "Timestamp: $timestamp\n";
        $email_body .= "Tipo: $asunto\n\n";
        $email_body .= "Detalles:\n$mensaje\n\n";
        $email_body .= "---\n";
        $email_body .= "Este mensaje fue generado automáticamente por el monitor de seguridad de AUTOEXAM2.\n";
        $email_body .= "Para desactivar las alertas, modificar la configuración del monitor.\n";
        
        $headers = [
            'From: autoexam2-security@' . ($hostname ?: 'localhost'),
            'Reply-To: noreply@' . ($hostname ?: 'localhost'),
            'X-Mailer: AUTOEXAM2 Security Monitor',
            'X-Priority: 1',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        $email_subject = "[AUTOEXAM2-SECURITY] $asunto - $hostname";
        
        if (mail($this->alert_email, $email_subject, $email_body, implode("\r\n", $headers))) {
            $this->log("Alerta enviada por email a: {$this->alert_email}", 'success');
        } else {
            $this->log("Error enviando alerta por email a: {$this->alert_email}", 'error');
        }
    }
    
    public function mostrarEstado() {
        echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
        echo   "║                   ESTADO DEL MONITOR DE SEGURIDAD                ║\n";
        echo   "╚══════════════════════════════════════════════════════════════════╝\n\n";
        
        // Estado de instalación
        $instalado = $this->verificarInstalacionCompleta();
        echo "🔧 Estado de instalación: " . ($instalado ? "✅ COMPLETA" : "❌ INCOMPLETA") . "\n";
        
        // Última verificación
        if (file_exists($this->last_check_file)) {
            $ultima = date('Y-m-d H:i:s', (int)file_get_contents($this->last_check_file));
            echo "⏰ Última verificación: $ultima\n";
        } else {
            echo "⏰ Última verificación: NUNCA\n";
        }
        
        // Configuración de alertas
        echo "📧 Email de alertas: " . ($this->alert_email ?: "NO CONFIGURADO") . "\n";
        
        // Archivos de log
        echo "\n📋 ARCHIVOS DE LOG:\n";
        $logs = [
            'monitor_seguridad.log' => 'Log del monitor',
            'intentos_instalador.log' => 'Intentos de acceso',
            'checksums.txt' => 'Checksums de archivos'
        ];
        
        foreach ($logs as $archivo => $descripcion) {
            $ruta = $this->log_path . '/' . $archivo;
            if (file_exists($ruta)) {
                $size = filesize($ruta);
                $modified = date('Y-m-d H:i:s', filemtime($ruta));
                echo "  ✅ $archivo ($descripcion) - {$size}B - Modificado: $modified\n";
            } else {
                echo "  ❌ $archivo ($descripcion) - NO EXISTE\n";
            }
        }
        
        echo "\n";
    }
}

// Procesamiento de argumentos y ejecución
if (in_array('--help', $argv) || in_array('-h', $argv)) {
    $monitor = new MonitorInstalador($argv);
    exit(0);
}

$monitor = new MonitorInstalador($argv);

// Si no se especifica acción, mostrar estado
if (!in_array('--daemon', $argv) && !in_array('--check-once', $argv)) {
    $monitor->mostrarEstado();
} else {
    $monitor->ejecutar();
}
