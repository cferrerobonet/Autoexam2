#!/usr/bin/env php
<?php
/**
 * AUTOEXAM2 - Validación Final del Sistema en Producción
 * 
 * Este script realiza una validación exhaustiva del sistema en un entorno
 * de servidor web real, incluyendo tests de redirecciones, conectividad
 * y funcionamiento completo del sistema de seguridad.
 * 
 * Uso: php validacion_produccion.php [--url=http://dominio.com] [--verbose]
 */

// Verificar que se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

class ValidacionProduccion {
    
    private $base_url = null;
    private $verbose = false;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $warnings = 0;
    private $curl_available = false;
    
    // Colores para output
    private const COLOR_GREEN = "\033[32m";
    private const COLOR_RED = "\033[31m";
    private const COLOR_YELLOW = "\033[33m";
    private const COLOR_BLUE = "\033[34m";
    private const COLOR_RESET = "\033[0m";
    private const COLOR_BOLD = "\033[1m";
    
    public function __construct($argv) {
        $this->parseArguments($argv);
        $this->curl_available = function_exists('curl_init');
    }
    
    private function parseArguments($argv) {
        foreach ($argv as $arg) {
            if (strpos($arg, '--url=') === 0) {
                $this->base_url = substr($arg, 6);
            }
            if ($arg === '--verbose' || $arg === '-v') {
                $this->verbose = true;
            }
            if ($arg === '--help' || $arg === '-h') {
                $this->mostrarAyuda();
                exit(0);
            }
        }
    }
    
    private function mostrarAyuda() {
        echo "AUTOEXAM2 - Validación Final del Sistema en Producción\n\n";
        echo "Uso: php validacion_produccion.php [opciones]\n\n";
        echo "Opciones:\n";
        echo "  --url=URL         URL base del sistema (ej: https://autoexam.epla.es)\n";
        echo "  --verbose, -v     Mostrar output detallado\n";
        echo "  --help, -h        Mostrar esta ayuda\n\n";
        echo "Ejemplo:\n";
        echo "  php validacion_produccion.php --url=https://autoexam.epla.es --verbose\n\n";
    }
    
    private function log($message, $color = self::COLOR_RESET) {
        if ($this->verbose) {
            echo $color . $message . self::COLOR_RESET . "\n";
        }
    }
    
    private function success($test_name, $details = '') {
        $this->tests_passed++;
        echo self::COLOR_GREEN . "✓ " . self::COLOR_RESET . $test_name;
        if ($details) {
            echo " - " . $details;
        }
        echo "\n";
    }
    
    private function failure($test_name, $error = '') {
        $this->tests_failed++;
        echo self::COLOR_RED . "✗ " . self::COLOR_RESET . $test_name;
        if ($error) {
            echo " - " . self::COLOR_RED . $error . self::COLOR_RESET;
        }
        echo "\n";
    }
    
    private function warning($test_name, $message = '') {
        $this->warnings++;
        echo self::COLOR_YELLOW . "⚠ " . self::COLOR_RESET . $test_name;
        if ($message) {
            echo " - " . self::COLOR_YELLOW . $message . self::COLOR_RESET;
        }
        echo "\n";
    }
    
    private function makeRequest($url, $follow_redirects = false) {
        if (!$this->curl_available) {
            return ['error' => 'cURL no disponible'];
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => $follow_redirects,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'AUTOEXAM2 Validation Script 1.0'
        ]);
        
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        return [
            'content' => $content,
            'http_code' => $http_code,
            'redirect_url' => $redirect_url,
            'error' => $error
        ];
    }
    
    public function ejecutarValidacion() {
        echo self::COLOR_BOLD . "AUTOEXAM2 - Validación Final del Sistema en Producción\n";
        echo "=" . str_repeat("=", 65) . self::COLOR_RESET . "\n\n";
        
        if (!$this->base_url) {
            echo "No se especificó URL base. Use --url=https://su-dominio.com\n";
            echo "Use --help para más información.\n";
            return;
        }
        
        echo "Validando sistema en: " . self::COLOR_BLUE . $this->base_url . self::COLOR_RESET . "\n\n";
        
        $this->testConectividad();
        $this->testRedirecciones();
        $this->testAccesoInstalador();
        $this->testArchivosEstaticos();
        $this->testSeguridadHeaders();
        $this->testBaseDatos();
        $this->testConfiguracion();
        
        $this->mostrarResumenFinal();
    }
    
    private function testConectividad() {
        echo self::COLOR_BLUE . "[1] Verificando conectividad básica...\n" . self::COLOR_RESET;
        
        if (!$this->curl_available) {
            $this->failure("cURL no disponible", "Instale php-curl para tests web");
            return;
        }
        
        $this->success("cURL disponible");
        
        // Test de conectividad básica
        $result = $this->makeRequest($this->base_url);
        
        if ($result['error']) {
            $this->failure("Error de conectividad", $result['error']);
            return;
        }
        
        if ($result['http_code'] >= 200 && $result['http_code'] < 300) {
            $this->success("Servidor responde", "HTTP " . $result['http_code']);
        } elseif ($result['http_code'] >= 300 && $result['http_code'] < 400) {
            $this->success("Servidor responde con redirección", "HTTP " . $result['http_code']);
        } else {
            $this->warning("Código HTTP inesperado", "HTTP " . $result['http_code']);
        }
    }
    
    private function testRedirecciones() {
        echo self::COLOR_BLUE . "\n[2] Verificando redirecciones del sistema...\n" . self::COLOR_RESET;
        
        // Test redirección desde raíz si no hay .env
        $root_result = $this->makeRequest($this->base_url);
        
        if ($root_result['http_code'] === 302 || $root_result['http_code'] === 301) {
            if (strpos($root_result['redirect_url'] ?? '', '/publico/instalador/') !== false) {
                $this->success("Redirección al instalador funcional");
            } else {
                $this->warning("Redirección inesperada", $root_result['redirect_url'] ?? 'No especificada');
            }
        } elseif ($root_result['http_code'] === 200) {
            $this->success("Sistema ya instalado", "Carga directa sin redirección");
        } else {
            $this->warning("Comportamiento de redirección inesperado", "HTTP " . $root_result['http_code']);
        }
        
        // Test acceso directo al público
        $publico_url = $this->base_url . '/publico/';
        $publico_result = $this->makeRequest($publico_url);
        
        if ($publico_result['http_code'] === 200) {
            $this->success("Acceso a directorio público funcional");
        } elseif ($publico_result['http_code'] === 302 || $publico_result['http_code'] === 301) {
            $this->success("Redirección desde público configurada");
        } else {
            $this->warning("Estado público inesperado", "HTTP " . $publico_result['http_code']);
        }
    }
    
    private function testAccesoInstalador() {
        echo self::COLOR_BLUE . "\n[3] Verificando protección del instalador...\n" . self::COLOR_RESET;
        
        $instalador_url = $this->base_url . '/publico/instalador/';
        $result = $this->makeRequest($instalador_url);
        
        if ($result['http_code'] === 200) {
            $content = $result['content'];
            
            if (strpos($content, 'AUTOEXAM') !== false) {
                $this->success("Instalador accesible");
                
                // Verificar mensajes de sistema ya instalado
                if (strpos($content, 'ya está instalado') !== false || 
                    strpos($content, 'already installed') !== false) {
                    $this->success("Protección de instalación múltiple activa");
                } else {
                    $this->warning("No se detecta protección de instalación múltiple");
                }
                
                // Verificar autocompletado de campos
                if (strpos($content, 'value=') !== false && 
                    strpos($content, 'cargarConfiguracionExistente') !== false) {
                    $this->success("Autocompletado de campos detectado");
                } else {
                    $this->warning("Autocompletado no detectado");
                }
                
            } else {
                $this->warning("Contenido del instalador inesperado");
            }
            
        } elseif ($result['http_code'] === 403) {
            $this->success("Instalador protegido (HTTP 403)");
        } elseif ($result['http_code'] === 404) {
            $this->warning("Instalador no encontrado (HTTP 404)");
        } else {
            $this->warning("Estado del instalador inesperado", "HTTP " . $result['http_code']);
        }
    }
    
    private function testArchivosEstaticos() {
        echo self::COLOR_BLUE . "\n[4] Verificando archivos estáticos críticos...\n" . self::COLOR_RESET;
        
        $archivos_criticos = [
            '/.env' => 'No debe ser accesible',
            '/config/config.php' => 'No debe mostrar código fuente',
            '/.env.example' => 'Puede ser accesible como plantilla',
            '/publico/instalador/.lock' => 'Estado de bloqueo del instalador'
        ];
        
        foreach ($archivos_criticos as $archivo => $descripcion) {
            $url = $this->base_url . $archivo;
            $result = $this->makeRequest($url);
            
            if ($archivo === '/.env') {
                if ($result['http_code'] === 403 || $result['http_code'] === 404) {
                    $this->success("Archivo .env protegido", $descripcion);
                } else {
                    $this->failure("Archivo .env accesible", "¡RIESGO DE SEGURIDAD!");
                }
            } elseif ($archivo === '/config/config.php') {
                if ($result['http_code'] === 403 || $result['http_code'] === 404) {
                    $this->success("Config.php protegido", $descripcion);
                } elseif ($result['http_code'] === 200 && strpos($result['content'], '<?php') === false) {
                    $this->success("Config.php procesado por servidor");
                } else {
                    $this->warning("Config.php puede mostrar código", "Verificar configuración del servidor");
                }
            } else {
                if ($result['http_code'] === 200) {
                    $this->success("Archivo accesible: " . basename($archivo));
                } elseif ($result['http_code'] === 404) {
                    $this->warning("Archivo no encontrado: " . basename($archivo));
                } else {
                    $this->warning("Estado inesperado: " . basename($archivo), "HTTP " . $result['http_code']);
                }
            }
        }
    }
    
    private function testSeguridadHeaders() {
        echo self::COLOR_BLUE . "\n[5] Verificando headers de seguridad...\n" . self::COLOR_RESET;
        
        $result = $this->makeRequest($this->base_url);
        
        if ($result['error']) {
            $this->warning("No se pudieron verificar headers", $result['error']);
            return;
        }
        
        // Obtener headers de respuesta usando get_headers
        $headers = get_headers($this->base_url, 1);
        
        if (!$headers) {
            $this->warning("No se pudieron obtener headers HTTP");
            return;
        }
        
        // Convertir a array plano si es necesario
        $headers_flat = [];
        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $headers_flat[strtolower($key)] = end($value);
            } else {
                $headers_flat[strtolower($key)] = $value;
            }
        }
        
        // Verificar headers de seguridad recomendados
        $security_headers = [
            'x-content-type-options' => 'nosniff',
            'x-frame-options' => ['DENY', 'SAMEORIGIN'],
            'x-xss-protection' => '1'
        ];
        
        foreach ($security_headers as $header => $expected) {
            if (isset($headers_flat[$header])) {
                if (is_array($expected)) {
                    if (in_array($headers_flat[$header], $expected)) {
                        $this->success("Header de seguridad: $header");
                    } else {
                        $this->warning("Header de seguridad valor inesperado: $header", $headers_flat[$header]);
                    }
                } else {
                    if (strpos($headers_flat[$header], $expected) !== false) {
                        $this->success("Header de seguridad: $header");
                    } else {
                        $this->warning("Header de seguridad valor inesperado: $header", $headers_flat[$header]);
                    }
                }
            } else {
                $this->warning("Header de seguridad faltante: $header");
            }
        }
        
        // Verificar que no se exponga información del servidor
        if (isset($headers_flat['server'])) {
            if (strpos(strtolower($headers_flat['server']), 'apache') !== false ||
                strpos(strtolower($headers_flat['server']), 'nginx') !== false ||
                strpos(strtolower($headers_flat['server']), 'php') !== false) {
                $this->warning("Información del servidor expuesta", $headers_flat['server']);
            } else {
                $this->success("Información del servidor oculta");
            }
        }
    }
    
    private function testBaseDatos() {
        echo self::COLOR_BLUE . "\n[6] Verificando conectividad de base de datos...\n" . self::COLOR_RESET;
        
        // Este test requiere acceso al archivo .env local
        $env_path = __DIR__ . '/.env';
        
        if (!file_exists($env_path)) {
            $this->warning("Archivo .env no encontrado localmente", "No se puede verificar BD");
            return;
        }
        
        // Cargar biblioteca Env
        $env_lib_path = __DIR__ . '/app/utilidades/env.php';
        if (!file_exists($env_lib_path)) {
            $this->warning("Biblioteca Env no encontrada", "No se puede cargar configuración");
            return;
        }
        
        require_once $env_lib_path;
        
        if (!Env::cargar($env_path)) {
            $this->warning("No se pudo cargar configuración .env");
            return;
        }
        
        $db_host = Env::obtener('DB_HOST');
        $db_name = Env::obtener('DB_NAME');
        $db_user = Env::obtener('DB_USER');
        $db_pass = Env::obtener('DB_PASS');
        
        if (!$db_host || !$db_name || !$db_user) {
            $this->warning("Configuración de BD incompleta en .env");
            return;
        }
        
        try {
            $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10
            ]);
            
            $this->success("Conexión a base de datos exitosa", "$db_host/$db_name");
            
            // Verificar tablas principales
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                $this->success("Base de datos contiene tablas", count($tables) . " tablas encontradas");
            } else {
                $this->warning("Base de datos vacía", "Puede necesitar instalación");
            }
            
        } catch (PDOException $e) {
            $this->failure("Error de conexión a BD", $e->getMessage());
        }
    }
    
    private function testConfiguracion() {
        echo self::COLOR_BLUE . "\n[7] Verificando configuración del sistema...\n" . self::COLOR_RESET;
        
        // Verificar que el archivo config.php carga correctamente
        $config_path = __DIR__ . '/config/config.php';
        
        if (!file_exists($config_path)) {
            $this->failure("Archivo config.php no encontrado");
            return;
        }
        
        // Capturar posibles errores de carga
        ob_start();
        $error_level = error_reporting(E_ALL);
        
        try {
            include $config_path;
            
            $this->success("Archivo config.php carga sin errores");
            
            // Verificar constantes críticas
            $constantes_criticas = ['DB_HOST', 'DB_NAME', 'DB_USER', 'BASE_URL'];
            
            foreach ($constantes_criticas as $constante) {
                if (defined($constante)) {
                    $this->success("Constante definida: $constante");
                } else {
                    $this->failure("Constante faltante: $constante");
                }
            }
            
        } catch (Throwable $e) {
            $this->failure("Error al cargar config.php", $e->getMessage());
        } finally {
            error_reporting($error_level);
            ob_end_clean();
        }
    }
    
    private function mostrarResumenFinal() {
        echo "\n" . str_repeat("=", 65) . "\n";
        echo self::COLOR_BOLD . "RESUMEN DE VALIDACIÓN EN PRODUCCIÓN\n" . self::COLOR_RESET;
        echo str_repeat("=", 65) . "\n\n";
        
        echo self::COLOR_GREEN . "✓ Tests pasados: " . $this->tests_passed . self::COLOR_RESET . "\n";
        echo self::COLOR_RED . "✗ Tests fallidos: " . $this->tests_failed . self::COLOR_RESET . "\n";
        echo self::COLOR_YELLOW . "⚠ Advertencias: " . $this->warnings . self::COLOR_RESET . "\n\n";
        
        $total_tests = $this->tests_passed + $this->tests_failed;
        $porcentaje_exito = $total_tests > 0 ? round(($this->tests_passed / $total_tests) * 100, 1) : 0;
        
        echo "Porcentaje de éxito: ";
        if ($porcentaje_exito >= 95) {
            echo self::COLOR_GREEN . $porcentaje_exito . "%" . self::COLOR_RESET;
        } elseif ($porcentaje_exito >= 80) {
            echo self::COLOR_YELLOW . $porcentaje_exito . "%" . self::COLOR_RESET;
        } else {
            echo self::COLOR_RED . $porcentaje_exito . "%" . self::COLOR_RESET;
        }
        echo "\n\n";
        
        if ($this->tests_failed === 0) {
            echo self::COLOR_GREEN . "🚀 ¡SISTEMA VALIDADO PARA PRODUCCIÓN!" . self::COLOR_RESET . "\n";
            if ($this->warnings > 0) {
                echo self::COLOR_YELLOW . "⚠ Revisar advertencias para optimización adicional" . self::COLOR_RESET . "\n";
            }
            echo "\nEl sistema de seguridad está funcionando correctamente en producción.\n";
        } else {
            echo self::COLOR_RED . "❌ HAY PROBLEMAS CRÍTICOS QUE REQUIEREN ATENCIÓN" . self::COLOR_RESET . "\n";
            echo "\nPor favor, corrija los errores antes de usar en producción.\n";
        }
        
        echo "\nRecomendaciones adicionales:\n";
        echo "• Configure tareas cron con: ./configurar_cron.sh\n";
        echo "• Monitoree logs en: tmp/logs/\n";
        echo "• Implemente HTTPS si no está configurado\n";
        echo "• Configure headers de seguridad adicionales\n";
        echo "• Realice backups regulares de .env y base de datos\n";
    }
}

// Ejecutar validación
$validacion = new ValidacionProduccion($argv);
$validacion->ejecutarValidacion();

exit(0);
