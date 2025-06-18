#!/usr/bin/env php
<?php
/**
 * AUTOEXAM2 - Tests de Integración del Sistema de Seguridad
 * 
 * Este script ejecuta una suite completa de tests para validar
 * todas las mejoras de seguridad implementadas en AUTOEXAM2
 * 
 * Uso: php tests_integracion.php [--verbose] [--detailed]
 */

// Verificar que se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

class TestsIntegracion {
    
    private $root_path;
    private $verbose = false;
    private $detailed = false;
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $warnings = 0;
    
    // Colores para output en terminal
    private const COLOR_GREEN = "\033[32m";
    private const COLOR_RED = "\033[31m";
    private const COLOR_YELLOW = "\033[33m";
    private const COLOR_BLUE = "\033[34m";
    private const COLOR_RESET = "\033[0m";
    private const COLOR_BOLD = "\033[1m";
    
    public function __construct($argv) {
        // Buscar la raíz del proyecto (directorio que contiene index.php)
        $current_dir = __DIR__;
        while ($current_dir !== '/' && !file_exists($current_dir . '/index.php')) {
            $current_dir = dirname($current_dir);
        }
        
        if (!file_exists($current_dir . '/index.php')) {
            die("Error: No se pudo encontrar la raíz del proyecto AUTOEXAM2\n");
        }
        
        $this->root_path = $current_dir;
        $this->parseArguments($argv);
    }
    
    private function parseArguments($argv) {
        foreach ($argv as $arg) {
            if ($arg === '--verbose' || $arg === '-v') {
                $this->verbose = true;
            }
            if ($arg === '--detailed' || $arg === '-d') {
                $this->detailed = true;
            }
            if ($arg === '--help' || $arg === '-h') {
                $this->mostrarAyuda();
                exit(0);
            }
        }
    }
    
    private function mostrarAyuda() {
        echo "AUTOEXAM2 - Tests de Integración del Sistema de Seguridad\n\n";
        echo "Uso: php tests_integracion.php [opciones]\n\n";
        echo "Opciones:\n";
        echo "  --verbose, -v     Mostrar output detallado\n";
        echo "  --detailed, -d    Mostrar información técnica detallada\n";
        echo "  --help, -h        Mostrar esta ayuda\n\n";
    }
    
    private function log($message, $color = self::COLOR_RESET) {
        if ($this->verbose) {
            echo $color . $message . self::COLOR_RESET . "\n";
        }
    }
    
    private function success($test_name, $details = '') {
        $this->tests_passed++;
        echo self::COLOR_GREEN . "✓ " . self::COLOR_RESET . $test_name;
        if ($this->detailed && $details) {
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
    
    public function ejecutarTodos() {
        echo self::COLOR_BOLD . "AUTOEXAM2 - Tests de Integración del Sistema de Seguridad\n";
        echo "=" . str_repeat("=", 60) . self::COLOR_RESET . "\n\n";
        
        $this->testEstructuraArchivos();
        $this->testBibliotecaEnv();
        $this->testConfiguracion();
        $this->testScriptsSeguridad();
        $this->testPermisos();
        $this->testLogging();
        $this->testInstalador();
        $this->testDocumentacion();
        
        $this->mostrarResumen();
    }
    
    private function testEstructuraArchivos() {
        echo self::COLOR_BLUE . "\n[1] Verificando estructura de archivos...\n" . self::COLOR_RESET;
        
        $archivos_requeridos = [
            '.env.example' => 'Plantilla de configuración',
            'app/utilidades/env.php' => 'Biblioteca Env',
            'config/config.php' => 'Configuración principal',
            'herramientas/seguridad/migracion/migrar_configuracion.php' => 'Script de migración',
            'herramientas/seguridad/monitoreo/monitor_instalador.php' => 'Monitor de seguridad',
            'herramientas/seguridad/configuracion/configurar_cron.sh' => 'Configurador de cron',
            'herramientas/seguridad/testing/tests_integracion.php' => 'Este script de tests'
        ];
        
        foreach ($archivos_requeridos as $archivo => $descripcion) {
            $path = $this->root_path . '/' . $archivo;
            if (file_exists($path)) {
                $this->success("Archivo existe: $archivo", $descripcion);
            } else {
                $this->failure("Archivo faltante: $archivo", $descripcion);
            }
        }
        
        // Verificar directorios
        $directorios = [
            'app/utilidades' => 'Directorio de utilidades',
            'config' => 'Directorio de configuración',
            'tmp/logs' => 'Directorio de logs',
            'publico/instalador' => 'Directorio del instalador'
        ];
        
        foreach ($directorios as $dir => $descripcion) {
            $path = $this->root_path . '/' . $dir;
            if (is_dir($path)) {
                $this->success("Directorio existe: $dir", $descripcion);
            } else {
                $this->warning("Directorio faltante: $dir", "Se creará automáticamente si es necesario");
            }
        }
    }
    
    private function testBibliotecaEnv() {
        echo self::COLOR_BLUE . "\n[2] Verificando biblioteca Env...\n" . self::COLOR_RESET;
        
        $env_path = $this->root_path . '/app/utilidades/env.php';
        
        if (!file_exists($env_path)) {
            $this->failure("Biblioteca Env no encontrada");
            return;
        }
        
        // Verificar sintaxis PHP
        $output = shell_exec("php -l \"$env_path\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            $this->success("Sintaxis PHP válida", "env.php");
        } else {
            $this->failure("Error de sintaxis en env.php", $output);
            return;
        }
        
        // Cargar y probar la biblioteca
        require_once $env_path;
        
        if (class_exists('Env')) {
            $this->success("Clase Env disponible");
            
            // Verificar métodos requeridos
            $metodos_requeridos = ['cargar', 'obtener', 'existe', 'establecer'];
            foreach ($metodos_requeridos as $metodo) {
                if (method_exists('Env', $metodo)) {
                    $this->success("Método Env::$metodo() disponible");
                } else {
                    $this->failure("Método Env::$metodo() faltante");
                }
            }
            
            // Test funcional básico con archivo temporal
            $test_env_content = "TEST_VAR=test_value\nTEST_BOOL=true\nTEST_QUOTED=\"quoted_value\"";
            $test_env_file = $this->root_path . '/tmp/.env_test';
            
            if (!is_dir(dirname($test_env_file))) {
                mkdir(dirname($test_env_file), 0755, true);
            }
            
            file_put_contents($test_env_file, $test_env_content);
            
            if (Env::cargar($test_env_file)) {
                $this->success("Carga de archivo .env funcional");
                
                if (Env::obtener('TEST_VAR') === 'test_value') {
                    $this->success("Lectura de variables funcional");
                } else {
                    $this->failure("Error en lectura de variables");
                }
                
                if (Env::existe('TEST_VAR')) {
                    $this->success("Verificación de existencia funcional");
                } else {
                    $this->failure("Error en verificación de existencia");
                }
                
                if (Env::obtener('TEST_BOOL') === true) {
                    $this->success("Conversión de booleanos funcional");
                } else {
                    $this->warning("Conversión de booleanos", "Valor: " . var_export(Env::obtener('TEST_BOOL'), true));
                }
                
            } else {
                $this->failure("Error al cargar archivo de prueba");
            }
            
            // Limpiar archivo de prueba
            if (file_exists($test_env_file)) {
                unlink($test_env_file);
            }
            
        } else {
            $this->failure("Clase Env no disponible");
        }
    }
    
    private function testConfiguracion() {
        echo self::COLOR_BLUE . "\n[3] Verificando configuración del sistema...\n" . self::COLOR_RESET;
        
        $config_path = $this->root_path . '/config/config.php';
        
        if (!file_exists($config_path)) {
            $this->failure("Archivo config.php no encontrado");
            return;
        }
        
        // Verificar sintaxis
        $output = shell_exec("php -l \"$config_path\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            $this->success("Sintaxis PHP válida", "config.php");
        } else {
            $this->failure("Error de sintaxis en config.php", $output);
            return;
        }
        
        // Verificar que no hay datos hardcodeados sensibles
        $config_content = file_get_contents($config_path);
        
        $patrones_sensibles = [
            '/define\s*\(\s*[\'"]DB_PASS[\'"],\s*[\'"][^\'"]+[\'"]\s*\)/' => 'Contraseña BD hardcodeada',
            '/define\s*\(\s*[\'"]SMTP_PASS[\'"],\s*[\'"][^\'"]+[\'"]\s*\)/' => 'Contraseña SMTP hardcodeada',
            '/define\s*\(\s*[\'"]FTP_PASS[\'"],\s*[\'"][^\'"]+[\'"]\s*\)/' => 'Contraseña FTP hardcodeada'
        ];
        
        foreach ($patrones_sensibles as $patron => $descripcion) {
            if (!preg_match($patron, $config_content)) {
                $this->success("Sin datos sensibles hardcodeados", $descripcion);
            } else {
                $this->failure("Datos sensibles detectados", $descripcion);
            }
        }
        
        // Verificar uso de Env::obtener
        if (strpos($config_content, 'Env::obtener') !== false) {
            $this->success("Uso de biblioteca Env detectado");
        } else {
            $this->warning("No se detecta uso de Env::obtener", "Verificar implementación");
        }
        
        // Verificar plantilla .env.example
        $env_example_path = $this->root_path . '/.env.example';
        if (file_exists($env_example_path)) {
            $env_example = file_get_contents($env_example_path);
            
            $variables_requeridas = [
                'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
                'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS',
                'FTP_HOST', 'FTP_USER', 'FTP_PASS',
                'APP_URL', 'APP_NAME'
            ];
            
            foreach ($variables_requeridas as $var) {
                if (strpos($env_example, $var . '=') !== false) {
                    $this->success("Variable $var en plantilla");
                } else {
                    $this->warning("Variable $var faltante en plantilla");
                }
            }
        } else {
            $this->failure("Plantilla .env.example no encontrada");
        }
    }
    
    private function testScriptsSeguridad() {
        echo self::COLOR_BLUE . "\n[4] Verificando scripts de seguridad...\n" . self::COLOR_RESET;
        
        $scripts = [
            'herramientas/seguridad/migracion/migrar_configuracion.php' => 'Script de migración',
            'herramientas/seguridad/monitoreo/monitor_instalador.php' => 'Monitor de seguridad',
            'herramientas/seguridad/configuracion/configurar_cron.sh' => 'Configurador de cron'
        ];
        
        foreach ($scripts as $script => $descripcion) {
            $path = $this->root_path . '/' . $script;
            
            if (!file_exists($path)) {
                $this->failure("Script faltante: $script");
                continue;
            }
            
            // Verificar permisos de ejecución
            if (is_executable($path)) {
                $this->success("Permisos de ejecución: $script");
            } else {
                $this->warning("Sin permisos de ejecución: $script", "Ejecutar: chmod +x $script");
            }
            
            // Verificar sintaxis para scripts PHP
            if (pathinfo($script, PATHINFO_EXTENSION) === 'php') {
                $output = shell_exec("php -l \"$path\" 2>&1");
                if (strpos($output, 'No syntax errors') !== false) {
                    $this->success("Sintaxis válida: $script");
                } else {
                    $this->failure("Error de sintaxis: $script", $output);
                }
                
                // Verificar shebang para CLI
                $first_line = fgets(fopen($path, 'r'));
                if (strpos($first_line, '#!/usr/bin/env php') === 0) {
                    $this->success("Shebang CLI correcto: $script");
                } else {
                    $this->warning("Shebang CLI faltante: $script");
                }
            }
        }
    }
    
    private function testPermisos() {
        echo self::COLOR_BLUE . "\n[5] Verificando permisos de archivos...\n" . self::COLOR_RESET;
        
        $archivos_permisos = [
            '.env' => ['requerido' => false, 'permisos' => '600', 'descripcion' => 'Archivo de configuración'],
            '.env.example' => ['requerido' => true, 'permisos' => '644', 'descripcion' => 'Plantilla de configuración'],
            'config/config.php' => ['requerido' => true, 'permisos' => '644', 'descripcion' => 'Configuración principal'],
            'publico/instalador/.lock' => ['requerido' => false, 'permisos' => '644', 'descripcion' => 'Archivo de bloqueo']
        ];
        
        foreach ($archivos_permisos as $archivo => $config) {
            $path = $this->root_path . '/' . $archivo;
            
            if (!file_exists($path)) {
                if ($config['requerido']) {
                    $this->failure("Archivo requerido faltante: $archivo");
                } else {
                    $this->warning("Archivo opcional faltante: $archivo", $config['descripcion']);
                }
                continue;
            }
            
            $permisos_actuales = substr(sprintf('%o', fileperms($path)), -3);
            
            if ($permisos_actuales === $config['permisos']) {
                $this->success("Permisos correctos: $archivo ($permisos_actuales)", $config['descripcion']);
            } else {
                $this->warning("Permisos incorrectos: $archivo", "Actual: $permisos_actuales, Requerido: " . $config['permisos']);
            }
        }
    }
    
    private function testLogging() {
        echo self::COLOR_BLUE . "\n[6] Verificando sistema de logging...\n" . self::COLOR_RESET;
        
        $log_dir = $this->root_path . '/tmp/logs';
        
        if (!is_dir($log_dir)) {
            if (mkdir($log_dir, 0755, true)) {
                $this->success("Directorio de logs creado");
            } else {
                $this->failure("No se pudo crear directorio de logs");
                return;
            }
        } else {
            $this->success("Directorio de logs existe");
        }
        
        // Verificar permisos de escritura
        if (is_writable($log_dir)) {
            $this->success("Directorio de logs escribible");
        } else {
            $this->failure("Directorio de logs no escribible");
        }
        
        // Test de escritura de log
        $test_log_file = $log_dir . '/test_' . date('Y-m-d') . '.log';
        $test_content = date('Y-m-d H:i:s') . " [TEST] Test de integración del sistema de logging\n";
        
        if (file_put_contents($test_log_file, $test_content, FILE_APPEND | LOCK_EX)) {
            $this->success("Escritura de logs funcional");
            
            // Limpiar archivo de prueba
            if (file_exists($test_log_file) && filesize($test_log_file) < 1000) {
                unlink($test_log_file);
            }
        } else {
            $this->failure("Error en escritura de logs");
        }
    }
    
    private function testInstalador() {
        echo self::COLOR_BLUE . "\n[7] Verificando protección del instalador...\n" . self::COLOR_RESET;
        
        $instalador_path = $this->root_path . '/publico/instalador/index.php';
        
        if (!file_exists($instalador_path)) {
            $this->warning("Archivo del instalador no encontrado", "Ubicación esperada: $instalador_path");
            return;
        }
        
        // Verificar sintaxis
        $output = shell_exec("php -l \"$instalador_path\" 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            $this->success("Sintaxis del instalador válida");
        } else {
            $this->failure("Error de sintaxis en instalador", $output);
        }
        
        // Verificar contenido del instalador
        $instalador_content = file_get_contents($instalador_path);
        
        // Buscar verificaciones de seguridad
        $verificaciones = [
            'file_exists' => 'Verificación de archivos existentes',
            '.lock' => 'Verificación de archivo de bloqueo',
            '.env' => 'Verificación de archivo de configuración',
            'log_instalador' => 'Sistema de logging del instalador'
        ];
        
        foreach ($verificaciones as $buscar => $descripcion) {
            if (strpos($instalador_content, $buscar) !== false) {
                $this->success("Implementado: $descripcion");
            } else {
                $this->warning("No detectado: $descripcion");
            }
        }
    }
    
    private function testDocumentacion() {
        echo self::COLOR_BLUE . "\n[8] Verificando documentación...\n" . self::COLOR_RESET;
        
        $documentos = [
            'MEJORAS_IMPLEMENTADAS.md' => 'Resumen de mejoras',
            'documentacion/09_configuracion_mantenimiento/06_configuracion.md' => 'Configuración del sistema',
            'documentacion/01_estructura_presentacion/00_estructura_proyecto.md' => 'Estructura del proyecto'
        ];
        
        foreach ($documentos as $doc => $descripcion) {
            $path = $this->root_path . '/' . $doc;
            
            if (file_exists($path)) {
                $this->success("Documento existe: " . basename($doc), $descripcion);
                
                $size = filesize($path);
                if ($size > 1000) {
                    $this->success("Contenido substancial: " . basename($doc), number_format($size) . " bytes");
                } else {
                    $this->warning("Contenido limitado: " . basename($doc), number_format($size) . " bytes");
                }
            } else {
                $this->failure("Documento faltante: " . basename($doc), $descripcion);
            }
        }
    }
    
    private function mostrarResumen() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo self::COLOR_BOLD . "RESUMEN DE TESTS DE INTEGRACIÓN\n" . self::COLOR_RESET;
        echo str_repeat("=", 60) . "\n\n";
        
        echo self::COLOR_GREEN . "✓ Tests pasados: " . $this->tests_passed . self::COLOR_RESET . "\n";
        echo self::COLOR_RED . "✗ Tests fallidos: " . $this->tests_failed . self::COLOR_RESET . "\n";
        echo self::COLOR_YELLOW . "⚠ Advertencias: " . $this->warnings . self::COLOR_RESET . "\n\n";
        
        $total_tests = $this->tests_passed + $this->tests_failed;
        $porcentaje_exito = $total_tests > 0 ? round(($this->tests_passed / $total_tests) * 100, 1) : 0;
        
        echo "Porcentaje de éxito: ";
        if ($porcentaje_exito >= 90) {
            echo self::COLOR_GREEN . $porcentaje_exito . "%" . self::COLOR_RESET;
        } elseif ($porcentaje_exito >= 70) {
            echo self::COLOR_YELLOW . $porcentaje_exito . "%" . self::COLOR_RESET;
        } else {
            echo self::COLOR_RED . $porcentaje_exito . "%" . self::COLOR_RESET;
        }
        echo "\n\n";
        
        if ($this->tests_failed === 0) {
            echo self::COLOR_GREEN . "🎉 ¡TODOS LOS TESTS PRINCIPALES PASARON!" . self::COLOR_RESET . "\n";
            if ($this->warnings > 0) {
                echo self::COLOR_YELLOW . "⚠ Hay algunas advertencias que revisar" . self::COLOR_RESET . "\n";
            }
            echo "\nEl sistema está listo para producción.\n";
        } else {
            echo self::COLOR_RED . "❌ HAY TESTS FALLIDOS QUE REQUIEREN ATENCIÓN" . self::COLOR_RESET . "\n";
            echo "\nPor favor, corrija los errores antes de usar en producción.\n";
        }
        
        echo "\nPara más información detallada, ejecute con --verbose --detailed\n";
    }
}

// Ejecutar tests
$tests = new TestsIntegracion($argv);
$tests->ejecutarTodos();

exit(0);
