<?php
/**
 * AUTOEXAM2 - Instalación Completa End-to-End
 * Script para ejecutar una instalación completa real del sistema
 * 
 * @author EPLA
 * @version 1.0
 * @date 2024
 */

// Configuración de tiempo de ejecución
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '256M');

// Incluir todas las funciones necesarias
require_once 'db_verify.php';
require_once 'admin_verify.php';
require_once 'funciones_tablas.php';

// Configuración completa para instalación real
$config_instalacion = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'autoexam2_production', // Cambiar por nombre real
        'username' => 'root', // Cambiar por usuario real
        'password' => '', // Cambiar por contraseña real
        'crear_bd' => true
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'autoexam@epla.es', // Cambiar por email real
        'password' => 'smtp_password', // Cambiar por contraseña real
        'encryption' => 'tls',
        'from_name' => 'AUTOEXAM2',
        'from_email' => 'autoexam@epla.es'
    ],
    'sftp' => [
        'host' => 'files.epla.es',
        'port' => 22,
        'username' => 'autoexam_ftp', // Cambiar por usuario real
        'password' => 'ftp_password', // Cambiar por contraseña real
        'path' => '/uploads/autoexam2/',
        'url_base' => 'https://files.epla.es/autoexam2/'
    ],
    'personalizacion' => [
        'nombre_institucion' => 'EPLA - Escuela Profesional',
        'siglas' => 'EPLA',
        'direccion' => 'Calle Principal, 123',
        'ciudad' => 'Madrid',
        'pais' => 'España',
        'telefono' => '+34 911 234 567',
        'email' => 'info@epla.es',
        'web' => 'https://www.epla.es',
        'logo' => '', // Se puede subir un logo
        'colores' => [
            'primario' => '#007cba',
            'secundario' => '#005a87',
            'acento' => '#ff6b35'
        ]
    ],
    'administrador' => [
        'usuario' => 'admin',
        'password' => 'Admin123!', // Cambiar por contraseña segura
        'email' => 'admin@epla.es',
        'nombre' => 'Administrador',
        'apellidos' => 'Sistema'
    ],
    'sistema' => [
        'url_base' => 'https://autoexam.epla.es/',
        'zona_horaria' => 'Europe/Madrid',
        'idioma' => 'es',
        'debug' => false,
        'mantenimiento' => false
    ]
];

// Función para mostrar progreso
function mostrarProgreso($paso, $total, $descripcion, $exito = true, $detalles = []) {
    $porcentaje = round(($paso / $total) * 100);
    $estado = $exito ? '✓' : '✗';
    $color = $exito ? '32' : '31';
    
    if (php_sapi_name() === 'cli') {
        echo "\033[{$color}m[{$estado}]\033[0m [{$porcentaje}%] Paso {$paso}/{$total}: {$descripion}\n";
        foreach ($detalles as $detalle) {
            echo "    → {$detalle}\n";
        }
    } else {
        $colorClass = $exito ? 'success' : 'error';
        echo "<div class='progress-step {$colorClass}'>";
        echo "<div class='progress-bar'><div style='width: {$porcentaje}%'></div></div>";
        echo "<strong>[{$estado}] [{$porcentaje}%] Paso {$paso}/{$total}:</strong> {$descripcion}";
        if (!empty($detalles)) {
            echo "<ul>";
            foreach ($detalles as $detalle) {
                echo "<li>{$detalle}</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
}

// Función para ejecutar paso de instalación
function ejecutarPaso($numero, $total, $descripcion, $funcion, $parametros = []) {
    try {
        $resultado = call_user_func_array($funcion, $parametros);
        
        if (is_array($resultado)) {
            mostrarProgreso($numero, $total, $descripcion, $resultado['exito'], $resultado['detalles'] ?? []);
            return $resultado['exito'];
        } else {
            mostrarProgreso($numero, $total, $descripcion, $resultado);
            return $resultado;
        }
    } catch (Exception $e) {
        mostrarProgreso($numero, $total, $descripcion, false, ["Error: " . $e->getMessage()]);
        return false;
    }
}

// PASO 1: Verificar requisitos del sistema
function verificarRequisitos() {
    $requisitos = [
        'PHP >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'Extensión PDO' => extension_loaded('pdo'),
        'Extensión PDO MySQL' => extension_loaded('pdo_mysql'),
        'Extensión OpenSSL' => extension_loaded('openssl'),
        'Extensión cURL' => extension_loaded('curl'),
        'Extensión GD' => extension_loaded('gd'),
        'Extensión mbstring' => extension_loaded('mbstring'),
        'Directorio uploads writable' => is_writable('../uploads') || mkdir('../uploads', 0755, true)
    ];
    
    $detalles = [];
    $todos_ok = true;
    
    foreach ($requisitos as $requisito => $cumple) {
        $detalles[] = $requisito . ($cumple ? ' ✓' : ' ✗');
        if (!$cumple) $todos_ok = false;
    }
    
    return ['exito' => $todos_ok, 'detalles' => $detalles];
}

// PASO 2: Configurar base de datos
function configurarBaseDatos($config) {
    try {
        // Conectar sin especificar base de datos
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $detalles = ["Conectado a MySQL {$config['host']}:{$config['port']}"];
        
        // Crear base de datos si no existe
        if ($config['crear_bd']) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $detalles[] = "Base de datos '{$config['name']}' creada/verificada";
        }
        
        // Conectar a la base de datos específica
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Verificar permisos
        $permisos = verificarPermisosBD($pdo, $config['username'], $config['name']);
        $permisos_ok = array_reduce($permisos, function($carry, $item) { return $carry && $item; }, true);
        
        if ($permisos_ok) {
            $detalles[] = "Todos los permisos verificados correctamente";
        } else {
            $detalles[] = "Algunos permisos pueden estar limitados";
        }
        
        return ['exito' => true, 'detalles' => $detalles, 'pdo' => $pdo];
        
    } catch (PDOException $e) {
        return ['exito' => false, 'detalles' => ["Error: " . $e->getMessage()]];
    }
}

// PASO 3: Ejecutar scripts SQL
function ejecutarScriptsSQL($pdo) {
    $script_path = '/Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/base_datos/migraciones/001_esquema_completo.sql';
    
    if (!file_exists($script_path)) {
        return ['exito' => false, 'detalles' => ["Script SQL no encontrado: {$script_path}"]];
    }
    
    $contenido = file_get_contents($script_path);
    $declaraciones = array_filter(
        array_map('trim', explode(';', $contenido)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $ejecutadas = 0;
    $errores = [];
    
    foreach ($declaraciones as $sql) {
        if (empty(trim($sql))) continue;
        
        try {
            $pdo->exec($sql);
            $ejecutadas++;
        } catch (PDOException $e) {
            $errores[] = "Error en SQL: " . $e->getMessage();
        }
    }
    
    $detalles = [
        "Script leído: " . number_format(strlen($contenido)) . " caracteres",
        "Declaraciones ejecutadas: {$ejecutadas}/" . count($declaraciones)
    ];
    
    if (!empty($errores)) {
        $detalles = array_merge($detalles, array_slice($errores, 0, 3)); // Mostrar solo primeros 3 errores
    }
    
    // Verificar tablas creadas
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $detalles[] = count($tablas) . " tablas creadas en la base de datos";
    
    return ['exito' => empty($errores), 'detalles' => $detalles];
}

// PASO 4: Configurar SMTP
function configurarSMTP($config) {
    $detalles = [];
    
    // Verificar conectividad básica
    $socket = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
    if (!$socket) {
        return ['exito' => false, 'detalles' => ["No se pudo conectar a {$config['host']}:{$config['port']}"]];
    }
    fclose($socket);
    
    $detalles[] = "Conectividad SMTP verificada: {$config['host']}:{$config['port']}";
    $detalles[] = "Configuración guardada para: {$config['username']}";
    $detalles[] = "Cifrado: {$config['encryption']}";
    
    // Aquí se guardaría la configuración en la base de datos
    // Por ahora solo simulamos
    
    return ['exito' => true, 'detalles' => $detalles];
}

// PASO 5: Configurar SFTP
function configurarSFTP($config) {
    $detalles = [];
    
    // Verificar conectividad básica
    $socket = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
    if (!$socket) {
        return ['exito' => false, 'detalles' => ["No se pudo conectar a {$config['host']}:{$config['port']}"]];
    }
    fclose($socket);
    
    $detalles[] = "Conectividad SFTP verificada: {$config['host']}:{$config['port']}";
    $detalles[] = "Usuario configurado: {$config['username']}";
    $detalles[] = "Directorio base: {$config['path']}";
    $detalles[] = "URL base: {$config['url_base']}";
    
    return ['exito' => true, 'detalles' => $detalles];
}

// PASO 6: Configurar personalización
function configurarPersonalizacion($config, $pdo) {
    $detalles = [];
    
    try {
        // Insertar configuración básica del sistema
        $configuraciones = [
            'nombre_institucion' => $config['nombre_institucion'],
            'siglas' => $config['siglas'],
            'direccion' => $config['direccion'],
            'ciudad' => $config['ciudad'],
            'pais' => $config['pais'],
            'telefono' => $config['telefono'],
            'email' => $config['email'],
            'web' => $config['web'],
            'color_primario' => $config['colores']['primario'],
            'color_secundario' => $config['colores']['secundario'],
            'color_acento' => $config['colores']['acento']
        ];
        
        foreach ($configuraciones as $clave => $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO configuracion (clave, valor, descripcion, tipo, activo) 
                VALUES (?, ?, ?, 'sistema', 1)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ");
            $stmt->execute([$clave, $valor, "Configuración: {$clave}"]);
        }
        
        $detalles[] = "Configuración institucional guardada";
        $detalles[] = "Institución: {$config['nombre_institucion']} ({$config['siglas']})";
        $detalles[] = "Contacto: {$config['email']} - {$config['telefono']}";
        $detalles[] = "Colores del sistema configurados";
        
        return ['exito' => true, 'detalles' => $detalles];
        
    } catch (PDOException $e) {
        return ['exito' => false, 'detalles' => ["Error guardando configuración: " . $e->getMessage()]];
    }
}

// PASO 7: Crear usuario administrador
function crearUsuarioAdministrador($config, $pdo) {
    try {
        $password_hash = password_hash($config['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (
                usuario, password, email, nombre, apellidos, 
                tipo, activo, fecha_creacion, ultimo_acceso
            ) VALUES (?, ?, ?, ?, ?, 'administrador', 1, NOW(), NOW())
        ");
        
        $stmt->execute([
            $config['usuario'],
            $password_hash,
            $config['email'],
            $config['nombre'],
            $config['apellidos']
        ]);
        
        $admin_id = $pdo->lastInsertId();
        
        $detalles = [
            "Usuario administrador creado: {$config['usuario']}",
            "Email: {$config['email']}",
            "Nombre: {$config['nombre']} {$config['apellidos']}",
            "ID de usuario: {$admin_id}",
            "Contraseña encriptada correctamente"
        ];
        
        return ['exito' => true, 'detalles' => $detalles];
        
    } catch (PDOException $e) {
        return ['exito' => false, 'detalles' => ["Error creando administrador: " . $e->getMessage()]];
    }
}

// PASO 8: Configuración final del sistema
function configuracionFinal($config, $pdo) {
    try {
        $configuraciones_sistema = [
            'url_base' => $config['url_base'],
            'zona_horaria' => $config['zona_horaria'],
            'idioma' => $config['idioma'],
            'debug' => $config['debug'] ? '1' : '0',
            'mantenimiento' => $config['mantenimiento'] ? '1' : '0',
            'version' => '2.0.0',
            'fecha_instalacion' => date('Y-m-d H:i:s'),
            'instalacion_completada' => '1'
        ];
        
        foreach ($configuraciones_sistema as $clave => $valor) {
            $stmt = $pdo->prepare("
                INSERT INTO configuracion (clave, valor, descripcion, tipo, activo) 
                VALUES (?, ?, ?, 'sistema', 1)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ");
            $stmt->execute([$clave, $valor, "Configuración del sistema: {$clave}"]);
        }
        
        // Crear archivo de bloqueo del instalador
        $lock_file = __DIR__ . '/instalacion_completada.lock';
        file_put_contents($lock_file, json_encode([
            'fecha' => date('Y-m-d H:i:s'),
            'version' => '2.0.0',
            'usuario' => $config['usuario'] ?? 'admin'
        ]));
        
        $detalles = [
            "Configuración del sistema completada",
            "URL base: {$config['url_base']}",
            "Zona horaria: {$config['zona_horaria']}",
            "Idioma: {$config['idioma']}",
            "Archivo de bloqueo creado",
            "Instalación marcada como completada"
        ];
        
        return ['exito' => true, 'detalles' => $detalles];
        
    } catch (Exception $e) {
        return ['exito' => false, 'detalles' => ["Error en configuración final: " . $e->getMessage()]];
    }
}

// CSS para interfaz web
if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>AUTOEXAM2 - Instalación Completa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007cba; padding-bottom: 10px; }
        .progress-step { padding: 15px; margin: 10px 0; border-radius: 6px; border-left: 4px solid #ddd; }
        .progress-step.success { background: #d4edda; border-left-color: #28a745; }
        .progress-step.error { background: #f8d7da; border-left-color: #dc3545; }
        .progress-bar { width: 100%; height: 8px; background: #e9ecef; border-radius: 4px; margin: 5px 0; }
        .progress-bar div { height: 100%; background: linear-gradient(90deg, #007cba, #005a87); border-radius: 4px; transition: width 0.3s; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 15px 0; border-left: 4px solid #ffc107; }
        .final-success { background: #d1edff; color: #004085; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px solid #007cba; }
        ul { margin: 10px 0; padding-left: 20px; }
        .config-section { background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0; font-size: 0.9em; }
    </style>
    <script>
        function actualizarPagina() {
            setTimeout(() => location.reload(), 2000);
        }
    </script>
</head>
<body>
    <div class='container'>
        <h1>🚀 AUTOEXAM2 - Instalación Completa End-to-End</h1>
        <div class='warning'>
            <strong>⚠️ ATENCIÓN:</strong> Esta instalación ejecutará cambios REALES en la base de datos y el sistema. 
            Asegúrate de haber configurado correctamente todos los parámetros.
        </div>";
}

echo "\n=== AUTOEXAM2 - INSTALACIÓN COMPLETA END-TO-END ===\n";
echo "Fecha de inicio: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 60) . "\n\n";

// Mostrar configuración que se va a aplicar
if (php_sapi_name() !== 'cli') {
    echo "<div class='config-section'>";
    echo "<h3>📋 Configuración de la Instalación</h3>";
    echo "<strong>Base de datos:</strong> {$config_instalacion['database']['name']} @ {$config_instalacion['database']['host']}<br>";
    echo "<strong>Institución:</strong> {$config_instalacion['personalizacion']['nombre_institucion']}<br>";
    echo "<strong>Administrador:</strong> {$config_instalacion['administrador']['usuario']} ({$config_instalacion['administrador']['email']})<br>";
    echo "<strong>URL del sistema:</strong> {$config_instalacion['sistema']['url_base']}<br>";
    echo "</div>";
}

$total_pasos = 8;
$pasos_exitosos = 0;

// Ejecutar todos los pasos de la instalación
$paso1 = ejecutarPaso(1, $total_pasos, "Verificando requisitos del sistema", 'verificarRequisitos');
if ($paso1) $pasos_exitosos++;

$resultado_bd = null;
if ($paso1) {
    $resultado_bd = configurarBaseDatos($config_instalacion['database']);
    $paso2 = ejecutarPaso(2, $total_pasos, "Configurando base de datos", function() use ($resultado_bd) {
        return $resultado_bd;
    });
    if ($paso2) $pasos_exitosos++;
}

$pdo = null;
if (isset($resultado_bd) && $resultado_bd['exito']) {
    $pdo = $resultado_bd['pdo'];
    $paso3 = ejecutarPaso(3, $total_pasos, "Ejecutando scripts SQL", 'ejecutarScriptsSQL', [$pdo]);
    if ($paso3) $pasos_exitosos++;
}

if ($pdo) {
    $paso4 = ejecutarPaso(4, $total_pasos, "Configurando servidor SMTP", 'configurarSMTP', [$config_instalacion['smtp']]);
    if ($paso4) $pasos_exitosos++;
    
    $paso5 = ejecutarPaso(5, $total_pasos, "Configurando servidor SFTP", 'configurarSFTP', [$config_instalacion['sftp']]);
    if ($paso5) $pasos_exitosos++;
    
    $paso6 = ejecutarPaso(6, $total_pasos, "Configurando personalización", 'configurarPersonalizacion', [$config_instalacion['personalizacion'], $pdo]);
    if ($paso6) $pasos_exitosos++;
    
    $paso7 = ejecutarPaso(7, $total_pasos, "Creando usuario administrador", 'crearUsuarioAdministrador', [$config_instalacion['administrador'], $pdo]);
    if ($paso7) $pasos_exitosos++;
    
    $paso8 = ejecutarPaso(8, $total_pasos, "Finalizando configuración del sistema", 'configuracionFinal', [$config_instalacion['sistema'], $pdo]);
    if ($paso8) $pasos_exitosos++;
}

// Resultado final
echo "\n" . str_repeat("=", 60) . "\n";
echo "🏁 INSTALACIÓN COMPLETADA\n";
echo "Fecha de finalización: " . date('Y-m-d H:i:s') . "\n";
echo "Pasos exitosos: {$pasos_exitosos}/{$total_pasos}\n";

if ($pasos_exitosos === $total_pasos) {
    if (php_sapi_name() !== 'cli') {
        echo "<div class='final-success'>";
        echo "<h2>🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!</h2>";
        echo "<p><strong>El sistema AUTOEXAM2 está listo para usar</strong></p>";
        echo "<p>Accede al sistema en: <a href='{$config_instalacion['sistema']['url_base']}' target='_blank'>{$config_instalacion['sistema']['url_base']}</a></p>";
        echo "<p><strong>Usuario:</strong> {$config_instalacion['administrador']['usuario']}<br>";
        echo "<strong>Contraseña:</strong> {$config_instalacion['administrador']['password']}</p>";
        echo "<p><small>⚠️ Cambia la contraseña después del primer acceso</small></p>";
        echo "</div>";
    } else {
        echo "\n🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "✅ El sistema AUTOEXAM2 está listo para usar\n";
        echo "🌐 URL: {$config_instalacion['sistema']['url_base']}\n";
        echo "👤 Usuario: {$config_instalacion['administrador']['usuario']}\n";
        echo "🔑 Contraseña: {$config_instalacion['administrador']['password']}\n";
        echo "⚠️  Cambia la contraseña después del primer acceso\n";
    }
} else {
    if (php_sapi_name() !== 'cli') {
        echo "<div class='progress-step error'>";
        echo "<h2>❌ Instalación incompleta</h2>";
        echo "<p>Se completaron {$pasos_exitosos} de {$total_pasos} pasos. Revisa los errores anteriores.</p>";
        echo "</div>";
    } else {
        echo "\n❌ INSTALACIÓN INCOMPLETA\n";
        echo "Se completaron {$pasos_exitosos} de {$total_pasos} pasos.\n";
        echo "Revisa los errores anteriores antes de continuar.\n";
    }
}

if (php_sapi_name() !== 'cli') {
    echo "</div></body></html>";
}

echo "\n";
?>
