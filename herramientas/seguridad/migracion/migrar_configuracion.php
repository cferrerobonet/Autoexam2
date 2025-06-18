#!/usr/bin/env php
<?php
/**
 * AUTOEXAM2 - Script de Migración de Configuración
 * 
 * Este script migra configuraciones existentes (hardcodeadas) al nuevo sistema de .env
 * 
 * Uso:
 *   php migrar_configuracion.php [--dry-run] [--backup] [--force]
 * 
 * Opciones:
 *   --dry-run    Solo mostrar cambios sin aplicarlos
 *   --backup     Crear respaldo de archivos antes de modificar
 *   --force      Sobrescribir .env existente sin preguntar
 */

// Verificar que se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script debe ejecutarse desde la línea de comandos.\n");
}

// Incluir la biblioteca Env
require_once __DIR__ . '/app/utilidades/env.php';

class MigradorConfiguracion {
    
    private $options = [];
    private $root_path;
    private $config_path;
    private $env_path;
    private $env_example_path;
    
    public function __construct($argv) {
        $this->root_path = __DIR__;
        $this->config_path = $this->root_path . '/config/config.php';
        $this->env_path = $this->root_path . '/.env';
        $this->env_example_path = $this->root_path . '/.env.example';
        
        $this->parseArguments($argv);
    }
    
    private function parseArguments($argv) {
        $this->options = [
            'dry-run' => false,
            'backup' => false,
            'force' => false
        ];
        
        foreach ($argv as $arg) {
            if ($arg === '--dry-run') {
                $this->options['dry-run'] = true;
            } elseif ($arg === '--backup') {
                $this->options['backup'] = true;
            } elseif ($arg === '--force') {
                $this->options['force'] = true;
            }
        }
    }
    
    public function ejecutar() {
        $this->mostrarBanner();
        
        // Verificar archivos necesarios
        if (!$this->verificarArchivos()) {
            return false;
        }
        
        // Verificar si ya existe .env
        if (file_exists($this->env_path) && !$this->options['force']) {
            echo "⚠️  El archivo .env ya existe. Use --force para sobrescribir.\n";
            return false;
        }
        
        // Crear backup si se solicita
        if ($this->options['backup']) {
            $this->crearBackup();
        }
        
        // Extraer configuración del config.php actual
        $configuracion = $this->extraerConfiguracion();
        
        if (empty($configuracion)) {
            echo "❌ No se encontró configuración hardcodeada para migrar.\n";
            return false;
        }
        
        // Mostrar configuración encontrada
        $this->mostrarConfiguracionEncontrada($configuracion);
        
        // Generar archivo .env
        if ($this->options['dry-run']) {
            echo "\n🔍 MODO DRY-RUN: Solo mostrando cambios\n";
            $this->mostrarContenidoEnv($configuracion);
        } else {
            $this->generarArchivoEnv($configuracion);
            $this->modificarConfigPHP();
            echo "\n✅ Migración completada exitosamente!\n";
        }
        
        $this->mostrarProximosPasos();
        return true;
    }
    
    private function mostrarBanner() {
        echo "
╔══════════════════════════════════════════════════════════════════╗
║               AUTOEXAM2 - Migrador de Configuración              ║
║                                                                  ║
║  Este script migra configuraciones hardcodeadas al sistema .env ║
╚══════════════════════════════════════════════════════════════════╝

";
    }
    
    private function verificarArchivos() {
        $archivos_requeridos = [
            'config/config.php' => $this->config_path,
            '.env.example' => $this->env_example_path,
            'app/utilidades/env.php' => $this->root_path . '/app/utilidades/env.php'
        ];
        
        $errores = false;
        foreach ($archivos_requeridos as $nombre => $ruta) {
            if (!file_exists($ruta)) {
                echo "❌ Archivo requerido no encontrado: $nombre\n";
                $errores = true;
            }
        }
        
        if ($errores) {
            echo "\n💡 Asegúrese de ejecutar este script desde la raíz del proyecto AUTOEXAM2\n";
            return false;
        }
        
        return true;
    }
    
    private function crearBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_dir = $this->root_path . '/tmp/backup_migracion_' . $timestamp;
        
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Backup del config.php
        if (file_exists($this->config_path)) {
            copy($this->config_path, $backup_dir . '/config.php.backup');
            echo "📄 Backup creado: config.php -> $backup_dir/config.php.backup\n";
        }
        
        // Backup del .env si existe
        if (file_exists($this->env_path)) {
            copy($this->env_path, $backup_dir . '/.env.backup');
            echo "📄 Backup creado: .env -> $backup_dir/.env.backup\n";
        }
        
        echo "\n";
    }
    
    private function extraerConfiguracion() {
        $contenido = file_get_contents($this->config_path);
        $configuracion = [];
        
        // Patrones para extraer defines hardcodeados
        $patrones = [
            // Base de datos
            'DB_HOST' => "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DB_NAME' => "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DB_USER' => "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DB_PASS' => "/define\s*\(\s*['\"]DB_PASS['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DB_PORT' => "/define\s*\(\s*['\"]DB_PORT['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DB_CHARSET' => "/define\s*\(\s*['\"]DB_CHARSET['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            
            // SMTP
            'SMTP_HOST' => "/define\s*\(\s*['\"]SMTP_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'SMTP_PORT' => "/define\s*\(\s*['\"]SMTP_PORT['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'SMTP_USER' => "/define\s*\(\s*['\"]SMTP_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'SMTP_PASS' => "/define\s*\(\s*['\"]SMTP_PASS['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'SMTP_SECURE' => "/define\s*\(\s*['\"]SMTP_SECURE['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            
            // FTP
            'FTP_HOST' => "/define\s*\(\s*['\"]FTP_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'FTP_PORT' => "/define\s*\(\s*['\"]FTP_PORT['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'FTP_USER' => "/define\s*\(\s*['\"]FTP_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'FTP_PASS' => "/define\s*\(\s*['\"]FTP_PASS['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'FTP_PATH' => "/define\s*\(\s*['\"]FTP_PATH['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'FTP_SECURE' => "/define\s*\(\s*['\"]FTP_SECURE['\"]\s*,\s*(true|false)\s*\)/",
            
            // Sistema
            'BASE_URL' => "/define\s*\(\s*['\"]BASE_URL['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            'DEBUG' => "/define\s*\(\s*['\"]DEBUG['\"]\s*,\s*(true|false)\s*\)/",
            'MODO_MANTENIMIENTO' => "/define\s*\(\s*['\"]MODO_MANTENIMIENTO['\"]\s*,\s*(true|false)\s*\)/"
        ];
        
        foreach ($patrones as $variable => $patron) {
            if (preg_match($patron, $contenido, $matches)) {
                $configuracion[$variable] = $matches[1];
            }
        }
        
        return $configuracion;
    }
    
    private function mostrarConfiguracionEncontrada($configuracion) {
        echo "🔍 Configuración hardcodeada encontrada:\n\n";
        
        $categorias = [
            'Base de Datos' => ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT', 'DB_CHARSET'],
            'SMTP' => ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_SECURE'],
            'FTP/SFTP' => ['FTP_HOST', 'FTP_PORT', 'FTP_USER', 'FTP_PASS', 'FTP_PATH', 'FTP_SECURE'],
            'Sistema' => ['BASE_URL', 'DEBUG', 'MODO_MANTENIMIENTO']
        ];
        
        foreach ($categorias as $categoria => $variables) {
            $encontradas = false;
            foreach ($variables as $var) {
                if (isset($configuracion[$var])) {
                    if (!$encontradas) {
                        echo "  📂 $categoria:\n";
                        $encontradas = true;
                    }
                    $valor = $var === 'DB_PASS' || $var === 'SMTP_PASS' || $var === 'FTP_PASS' 
                           ? str_repeat('*', strlen($configuracion[$var])) 
                           : $configuracion[$var];
                    echo "     ✓ $var = $valor\n";
                }
            }
            if ($encontradas) echo "\n";
        }
    }
    
    private function generarArchivoEnv($configuracion) {
        // Cargar plantilla .env.example
        $plantilla = file_get_contents($this->env_example_path);
        
        // Reemplazar valores en la plantilla
        foreach ($configuracion as $variable => $valor) {
            $patron = "/^$variable=.*$/m";
            $reemplazo = "$variable=$valor";
            $plantilla = preg_replace($patron, $reemplazo, $plantilla);
        }
        
        // Añadir comentario de migración
        $header = "# Archivo de configuración de AUTOEXAM2\n";
        $header .= "# Migrado automáticamente desde config.php hardcodeado: " . date('Y-m-d H:i:s') . "\n";
        $header .= "# NO MODIFIQUE ESTE ARCHIVO MANUALMENTE\n\n";
        
        $contenido_final = $header . $plantilla;
        
        if (file_put_contents($this->env_path, $contenido_final) !== false) {
            echo "✅ Archivo .env creado exitosamente\n";
            chmod($this->env_path, 0600); // Permisos seguros
            echo "🔒 Permisos seguros aplicados al archivo .env (600)\n";
        } else {
            echo "❌ Error al crear el archivo .env\n";
            return false;
        }
        
        return true;
    }
    
    private function modificarConfigPHP() {
        $contenido = file_get_contents($this->config_path);
        
        // Backup del archivo original
        $backup_path = $this->config_path . '.pre-migracion.' . date('YmdHis');
        file_put_contents($backup_path, $contenido);
        
        // Generar nuevo contenido para config.php
        $nuevo_contenido = "<?php\n";
        $nuevo_contenido .= "/**\n";
        $nuevo_contenido .= " * AUTOEXAM2 - Configuración Principal\n";
        $nuevo_contenido .= " * \n";
        $nuevo_contenido .= " * Configuración migrada al sistema .env\n";
        $nuevo_contenido .= " * Generado automáticamente: " . date('Y-m-d H:i:s') . "\n";
        $nuevo_contenido .= " */\n\n";
        
        $nuevo_contenido .= "// Incluir la biblioteca de variables de entorno\n";
        $nuevo_contenido .= "require_once __DIR__ . '/../app/utilidades/env.php';\n\n";
        
        $nuevo_contenido .= "// Cargar configuración desde archivo .env\n";
        $nuevo_contenido .= "\$env_path = __DIR__ . '/../.env';\n";
        $nuevo_contenido .= "if (!file_exists(\$env_path)) {\n";
        $nuevo_contenido .= "    throw new Exception('Archivo de configuración .env no encontrado');\n";
        $nuevo_contenido .= "}\n\n";
        
        $nuevo_contenido .= "if (!Env::cargar(\$env_path)) {\n";
        $nuevo_contenido .= "    throw new Exception('No se pudo cargar la configuración del sistema');\n";
        $nuevo_contenido .= "}\n\n";
        
        $nuevo_contenido .= "// Configuración de base de datos\n";
        $nuevo_contenido .= "define('DB_HOST', Env::obtener('DB_HOST', 'localhost'));\n";
        $nuevo_contenido .= "define('DB_NAME', Env::obtener('DB_NAME'));\n";
        $nuevo_contenido .= "define('DB_USER', Env::obtener('DB_USER'));\n";
        $nuevo_contenido .= "define('DB_PASS', Env::obtener('DB_PASS'));\n";
        $nuevo_contenido .= "define('DB_PORT', Env::obtener('DB_PORT', '3306'));\n";
        $nuevo_contenido .= "define('DB_CHARSET', Env::obtener('DB_CHARSET', 'utf8mb4'));\n\n";
        
        $nuevo_contenido .= "// Configuración SMTP\n";
        $nuevo_contenido .= "define('SMTP_HOST', Env::obtener('SMTP_HOST'));\n";
        $nuevo_contenido .= "define('SMTP_PORT', Env::obtener('SMTP_PORT', '587'));\n";
        $nuevo_contenido .= "define('SMTP_USER', Env::obtener('SMTP_USER'));\n";
        $nuevo_contenido .= "define('SMTP_PASS', Env::obtener('SMTP_PASS'));\n";
        $nuevo_contenido .= "define('SMTP_SECURE', Env::obtener('SMTP_SECURE', 'tls'));\n\n";
        
        $nuevo_contenido .= "// Configuración FTP\n";
        $nuevo_contenido .= "define('FTP_HOST', Env::obtener('FTP_HOST'));\n";
        $nuevo_contenido .= "define('FTP_PORT', Env::obtener('FTP_PORT', '21'));\n";
        $nuevo_contenido .= "define('FTP_USER', Env::obtener('FTP_USER'));\n";
        $nuevo_contenido .= "define('FTP_PASS', Env::obtener('FTP_PASS'));\n";
        $nuevo_contenido .= "define('FTP_PATH', Env::obtener('FTP_PATH', '/'));\n";
        $nuevo_contenido .= "define('FTP_SECURE', Env::obtener('FTP_SECURE', false));\n\n";
        
        $nuevo_contenido .= "// Configuración del sistema\n";
        $nuevo_contenido .= "define('BASE_URL', Env::obtener('BASE_URL'));\n";
        $nuevo_contenido .= "define('DEBUG', Env::obtener('DEBUG', false));\n";
        $nuevo_contenido .= "define('MODO_MANTENIMIENTO', Env::obtener('MODO_MANTENIMIENTO', false));\n";
        $nuevo_contenido .= "define('TIMEZONE', Env::obtener('TIMEZONE', 'Europe/Madrid'));\n\n";
        
        $nuevo_contenido .= "// Configuración de seguridad\n";
        $nuevo_contenido .= "define('HASH_COST', Env::obtener('HASH_COST', 12));\n";
        $nuevo_contenido .= "define('SESSION_LIFETIME', Env::obtener('SESSION_LIFETIME', 7200));\n\n";
        
        $nuevo_contenido .= "// Configuración de archivos\n";
        $nuevo_contenido .= "define('MAX_UPLOAD_SIZE', Env::obtener('MAX_UPLOAD_SIZE', 5242880));\n";
        $nuevo_contenido .= "define('ALLOWED_EXTENSIONS', Env::obtener('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx'));\n\n";
        
        $nuevo_contenido .= "// Configurar zona horaria\n";
        $nuevo_contenido .= "date_default_timezone_set(TIMEZONE);\n\n";
        
        $nuevo_contenido .= "// Configurar nivel de errores según DEBUG\n";
        $nuevo_contenido .= "if (DEBUG) {\n";
        $nuevo_contenido .= "    error_reporting(E_ALL);\n";
        $nuevo_contenido .= "    ini_set('display_errors', 1);\n";
        $nuevo_contenido .= "} else {\n";
        $nuevo_contenido .= "    error_reporting(0);\n";
        $nuevo_contenido .= "    ini_set('display_errors', 0);\n";
        $nuevo_contenido .= "}\n";
        
        if (file_put_contents($this->config_path, $nuevo_contenido) !== false) {
            echo "✅ Archivo config.php actualizado para usar el sistema .env\n";
            echo "📄 Backup del archivo original: " . basename($backup_path) . "\n";
        } else {
            echo "❌ Error al actualizar config.php\n";
            return false;
        }
        
        return true;
    }
    
    private function mostrarContenidoEnv($configuracion) {
        echo "\n📄 Contenido que se escribiría en .env:\n";
        echo "════════════════════════════════════════════════\n";
        
        // Cargar plantilla y mostrar resultado
        $plantilla = file_get_contents($this->env_example_path);
        
        foreach ($configuracion as $variable => $valor) {
            $patron = "/^$variable=.*$/m";
            $reemplazo = "$variable=$valor";
            $plantilla = preg_replace($patron, $reemplazo, $plantilla);
        }
        
        echo $plantilla;
        echo "════════════════════════════════════════════════\n";
    }
    
    private function mostrarProximosPasos() {
        echo "\n📋 PRÓXIMOS PASOS:\n\n";
        
        if (!$this->options['dry-run']) {
            echo "1. ✅ Verificar que el sistema funciona correctamente\n";
            echo "2. 🔒 Asegurar permisos del archivo .env (ya aplicados: 600)\n";
            echo "3. 🌐 Configurar servidor web para bloquear acceso a .env\n";
            echo "4. 📚 Revisar documentación en: documentacion/09_configuracion_mantenimiento/06_configuracion.md\n";
            echo "5. 🗑️  Eliminar archivos de backup si todo funciona correctamente\n";
        } else {
            echo "1. 🚀 Ejecutar sin --dry-run para aplicar cambios\n";
            echo "2. 💾 Considerar usar --backup para crear respaldos\n";
        }
        
        echo "\n⚠️  IMPORTANTE:\n";
        echo "   • El archivo .env contiene información sensible\n";
        echo "   • NO incluir .env en el control de versiones\n";
        echo "   • Asegurar que el servidor web no sirve archivos .env\n";
        echo "\n🔧 Para revertir cambios:\n";
        echo "   • Los archivos de backup están en tmp/backup_migracion_*\n";
        echo "   • El config.php original está respaldado como .pre-migracion.*\n\n";
    }
}

// Ejecutar migrador
$migrador = new MigradorConfiguracion($argv);
$resultado = $migrador->ejecutar();

exit($resultado ? 0 : 1);
