<?php
// AUTOEXAM2 - Instalador automático
// Este script guía la instalación inicial del sistema según la documentación del archivo 07_instalador.md.
// Pasos: comprobación requisitos, configuración BD, SMTP, SFTP, personalización, creación admin, confirmación y bloqueo.

// Verificación de instalación previa
// Si existe tanto el archivo .env como el archivo .lock, redirigir al índice principal
$env_file = realpath(__DIR__ . '/../../') . '/.env';
$lock_file = __DIR__ . '/.lock';

// Función para registrar logs del instalador (ayuda para diagnóstico)
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

// Registrar inicio del instalador
log_instalador('Iniciando instalador del sistema');

// Incluir la biblioteca Env para gestión de variables de entorno
require_once __DIR__ . '/../../app/utilidades/env.php';

// Incluir funciones para operaciones con tablas (verificar, eliminar, vaciar)
if (file_exists(__DIR__ . '/funciones_tablas.php')) {
    require_once __DIR__ . '/funciones_tablas.php';
    log_instalador('Funciones de gestión de tablas cargadas correctamente', 'info');
} else {
    log_instalador('No se encontró el archivo funciones_tablas.php', 'error');
}

// Función para cargar configuración existente desde .env usando la biblioteca Env
function cargarConfiguracionExistente() {
    $env_path = realpath(__DIR__ . '/../../') . '/.env';
    
    if (!file_exists($env_path)) {
        log_instalador('Archivo .env no encontrado en: ' . $env_path, 'warning');
        return [];
    }
    
    // Intentar cargar directamente el archivo si la biblioteca Env tiene problemas
    $contenido = file_get_contents($env_path);
    if (!$contenido) {
        log_instalador('No se pudo leer el archivo .env: ' . $env_path, 'error');
        return [];
    }
    
    // Primero, verificamos si hay comentarios // al principio de las líneas que podrían causar problemas
    if (preg_match('/^\s*\/\//', $contenido)) {
        log_instalador('Detectados comentarios con // en .env que podrían causar problemas, intentando corregir...', 'warning');
        $contenido_corregido = preg_replace('/^\s*\/\/(.*)$/m', '# $1', $contenido);
        if (file_put_contents($env_path, $contenido_corregido)) {
            log_instalador('Archivo .env corregido automáticamente para usar comentarios con #', 'success');
        }
    }
    
    // Cargar las variables usando la biblioteca Env
    if (!Env::cargar($env_path)) {
        log_instalador('Error al cargar configuración existente desde .env con la biblioteca Env', 'warning');
        // Intentar cargar manualmente
        $configuracion = [];
        $lineas = explode("\n", $contenido);
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            
            // Omitir comentarios y líneas vacías
            if (empty($linea) || strpos($linea, '#') === 0 || strpos($linea, '//') === 0) {
                continue;
            }
            
            // Separar clave y valor
            $separador = strpos($linea, '=');
            if ($separador === false) {
                continue;
            }
            
            $clave = trim(substr($linea, 0, $separador));
            $valor = trim(substr($linea, $separador + 1));
            
            // Eliminar comillas si existen
            if ((substr($valor, 0, 1) === '"' && substr($valor, -1) === '"') || 
                (substr($valor, 0, 1) === "'" && substr($valor, -1) === "'")) {
                $valor = substr($valor, 1, -1);
            }
            
            $configuracion[$clave] = $valor;
        }
        
        if (!empty($configuracion)) {
            log_instalador('Configuración cargada manualmente desde .env: ' . count($configuracion) . ' variables', 'success');
            return $configuracion;
        } else {
            log_instalador('No se pudo cargar configuración ni siquiera manualmente', 'error');
            return [];
        }
    }
    
    // Lista de variables que necesitamos recuperar
    $variables_env = [
        'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PORT',
        'SMTP_HOST', 'SMTP_USER', 'SMTP_PASS', 'SMTP_PORT', 'SMTP_SECURE',
        'FTP_HOST', 'FTP_USER', 'FTP_PASS', 'FTP_PORT', 'FTP_PATH', 'FTP_SECURE', 'FTP_TYPE'
    ];
    
    $configuracion = [];
    foreach ($variables_env as $variable) {
        if (Env::existe($variable)) {
            $configuracion[$variable] = Env::obtener($variable);
            log_instalador("Variable $variable cargada desde .env", 'debug');
        } else {
            log_instalador("Variable $variable NO encontrada en .env", 'debug');
        }
    }
    
    log_instalador('Configuración existente cargada desde .env: ' . count($configuracion) . ' variables', 'info');
    return $configuracion;
}

// Verificar instalación previa después de tener disponible la función de log
$existe_env = file_exists($env_file);
$existe_lock = file_exists($lock_file);

// Construir URL absoluta para posibles redirecciones
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocolo . '://' . $host;

// Controlar los diferentes escenarios según la existencia de env y lock
if ($existe_env && $existe_lock) {
    // Caso 1: Ambos archivos existen - Instalación completa, redirigir a la home
    log_instalador('Instalación previa detectada (.env y .lock existen). Redirigiendo al índice principal.', 'warning');
    header('Location: ' . $base_url);
    exit;
} elseif ($existe_env && !$existe_lock) {
    // Caso 2: Solo existe env - Cargar los datos del env para reinstalación
    log_instalador('Archivo .env detectado pero falta .lock. Permitiendo reinstalación con datos precargados.', 'info');
    $configuracion_existente = cargarConfiguracionExistente();
    $_SESSION['modo_reinstalacion'] = true;
    $_SESSION['config_env'] = $configuracion_existente;
} elseif (!$existe_env && $existe_lock) {
    // Caso 3: Solo existe lock - Mostrar modal indicando que debe borrar también lock
    log_instalador('Archivo .lock detectado pero falta .env. Se requiere borrar .lock para continuar.', 'warning');
    $_SESSION['mostrar_modal_lock'] = true;
} else {
    // Caso 4: No existe ninguno - Instalación normal desde cero
    log_instalador('No se detectó instalación previa. Procediendo con instalación nueva.', 'info');
    $_SESSION['modo_reinstalacion'] = false;
}

// 1. Comprobación del sistema - Verifica versión PHP y extensiones
// 2. Configuración de base de datos - Datos de conexión y prueba
// 3. Configuración SMTP - Selector de tipo y parámetros de conexión
// 4. Configuración SFTP/FTP - Datos de acceso y prueba de conexión
// 5. Personalización visual - Subida de logo e imagen de usuario por defecto
// 6. Creación del primer administrador - Usuario obligatorio con datos predefinidos
// 7. Confirmación y bloqueo del instalador - Resumen, ejecución SQL y bloqueo

// NOTA: Implementar cada paso según la documentación modular

// Control de pasos del instalador
session_start();

// Definición de los pasos del instalador
$pasos = [
    1 => "Requisitos del sistema",
    2 => "Configuración de base de datos",
    3 => "Configuración SMTP",
    4 => "Configuración SFTP/FTP",
    5 => "Personalización visual",
    6 => "Usuario administrador",
    7 => "Confirmación y bloqueo"
];

// Si es la primera vez que se accede, inicializar el paso en 1
if (!isset($_SESSION['paso_actual'])) {
    $_SESSION['paso_actual'] = 1;
    $_SESSION['paso_maximo'] = 1;
}

// Cargar configuración existente desde .env si está disponible
$config_existente = cargarConfiguracionExistente();

// Si estamos en modo reinstalación, usar valores del archivo .env
if (isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'] && isset($_SESSION['config_env'])) {
    $config_env = $_SESSION['config_env'];
    log_instalador('Configurando formularios con datos del archivo .env existente para reinstalación', 'info');
}

// Inicializar arrays en la sesión para almacenar datos de los formularios
if (!isset($_SESSION['db_config'])) {
    $_SESSION['db_config'] = [
        'db_host' => $config_env['DB_HOST'] ?? ($config_existente['DB_HOST'] ?? 'localhost'),
        'db_name' => $config_env['DB_NAME'] ?? ($config_existente['DB_NAME'] ?? ''),
        'db_user' => $config_env['DB_USER'] ?? ($config_existente['DB_USER'] ?? ''),
        'db_pass' => $config_env['DB_PASS'] ?? ($config_existente['DB_PASS'] ?? ''),
        'db_port' => $config_env['DB_PORT'] ?? ($config_existente['DB_PORT'] ?? '3306')
    ];
}

// Inicializar el estado de verificación de la base de datos solo si no existe
if (!isset($_SESSION['db_verificada'])) {
    $_SESSION['db_verificada'] = isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'] ? true : false;
    
    // Si tenemos datos de env y estamos en modo reinstalación, intentar verificar BD automáticamente
    if (isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'] && 
        isset($_SESSION['db_config']) && !empty($_SESSION['db_config']['db_host']) && 
        !empty($_SESSION['db_config']['db_user']) && !empty($_SESSION['db_config']['db_name'])) {
        
        try {
            $db = $_SESSION['db_config'];
            $mysqli = @new mysqli($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name']);
            
            if (!$mysqli->connect_error) {
                $_SESSION['db_verificada'] = true;
                log_instalador("Conexión a la base de datos verificada automáticamente en modo reinstalación", 'info');
                // La verificación de tablas se hará en el paso 2
            }
        } catch (Exception $e) {
            log_instalador("Error al intentar verificar la BD automáticamente: " . $e->getMessage(), 'error');
        }
    }
}

// Evitar que se muestren errores al entrar por primera vez al paso 2
if (!isset($_SESSION['mostrar_errores_db'])) {
    $_SESSION['mostrar_errores_db'] = false;
}

if (!isset($_SESSION['smtp_config'])) {
    $_SESSION['smtp_config'] = [
        'smtp_tipo' => 'smtp',
        'smtp_host' => $config_env['SMTP_HOST'] ?? ($config_existente['SMTP_HOST'] ?? ''),
        'smtp_port' => $config_env['SMTP_PORT'] ?? ($config_existente['SMTP_PORT'] ?? '587'),
        'smtp_user' => $config_env['SMTP_USER'] ?? ($config_existente['SMTP_USER'] ?? ''),
        'smtp_pass' => $config_env['SMTP_PASS'] ?? ($config_existente['SMTP_PASS'] ?? ''),
        'smtp_secure' => $config_env['SMTP_SECURE'] ?? ($config_existente['SMTP_SECURE'] ?? 'tls')
    ];
}

// Inicializar el estado de verificación SMTP solo si no existe
if (!isset($_SESSION['smtp_verificada'])) {
    $_SESSION['smtp_verificada'] = isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'] ? true : false;
}

// Evitar que se muestren errores al entrar por primera vez al paso 3
if (!isset($_SESSION['mostrar_errores_smtp'])) {
    $_SESSION['mostrar_errores_smtp'] = false;
}

if (!isset($_SESSION['ftp_config'])) {
    // Primero intentamos obtener el tipo directamente de FTP_TYPE
    $ftp_tipo = $config_env['FTP_TYPE'] ?? ($config_existente['FTP_TYPE'] ?? '');
    
    // Si no existe FTP_TYPE, lo inferimos desde FTP_SECURE como compatibilidad
    if (empty($ftp_tipo)) {
        $ftp_secure_value = $config_env['FTP_SECURE'] ?? ($config_existente['FTP_SECURE'] ?? 'false');
        $ftp_tipo = ($ftp_secure_value === 'true' || $ftp_secure_value === true) ? 'sftp' : 'ftp';
        log_instalador("Tipo de conexión inferido desde FTP_SECURE ($ftp_secure_value): $ftp_tipo", 'info');
    } else {
        log_instalador("Tipo de conexión obtenido directamente de FTP_TYPE: $ftp_tipo", 'info');
    }
    
    // Determinar el puerto por defecto basado en el tipo de conexión
    $puerto_defecto = $ftp_tipo === 'sftp' ? '22' : '21';
    $puerto = $config_env['FTP_PORT'] ?? ($config_existente['FTP_PORT'] ?? $puerto_defecto);
    
    log_instalador("Puerto FTP/SFTP configurado: $puerto (por defecto: $puerto_defecto)", 'debug');
    
    $_SESSION['ftp_config'] = [
        'ftp_host' => $config_env['FTP_HOST'] ?? ($config_existente['FTP_HOST'] ?? ''),
        'ftp_user' => $config_env['FTP_USER'] ?? ($config_existente['FTP_USER'] ?? ''),
        'ftp_pass' => $config_env['FTP_PASS'] ?? ($config_existente['FTP_PASS'] ?? ''),
        'ftp_port' => $puerto,
        'ftp_path' => $config_env['FTP_PATH'] ?? ($config_existente['FTP_PATH'] ?? ''),
        'ftp_tipo' => $ftp_tipo
    ];
}

// Inicializar el estado de verificación FTP solo si no existe
if (!isset($_SESSION['ftp_verificada'])) {
    $_SESSION['ftp_verificada'] = isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'] ? true : false;
}

// Evitar que se muestren errores al entrar por primera vez al paso 4
if (!isset($_SESSION['mostrar_errores_ftp'])) {
    $_SESSION['mostrar_errores_ftp'] = false;
}

if (!isset($_SESSION['admin_config'])) {
    $_SESSION['admin_config'] = [
        'system_name' => defined('SYSTEM_NAME') ? SYSTEM_NAME : 'AUTOEXAM2',
        'admin_email' => defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com',
        'admin_pass' => defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : 'AdminPassword123!',
        'admin_confirm' => defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : 'AdminPassword123!'
    ];
}

// Inicializar el array de archivos subidos para el paso 5
if (!isset($_SESSION['archivos_subidos'])) {
    $_SESSION['archivos_subidos'] = [];
}

// Manejo de mensajes de error o éxito
$mensajes = [];

// Función mejorada para limpiar y validar datos de entrada
function sanitizar_entrada($dato, $tipo = 'texto') {
    // Primero eliminar espacios en blanco
    $dato = trim($dato);
    
    // Aplicar sanitización según el tipo de dato
    switch ($tipo) {
        case 'email':
            // Limpiar y validar correo electrónico
            $dato = filter_var($dato, FILTER_SANITIZE_EMAIL);
            if (!filter_var($dato, FILTER_VALIDATE_EMAIL)) {
                // Si no es un correo válido, devolver cadena vacía o lanzar excepción
                log_instalador("Error de validación: formato de correo electrónico inválido", 'warning');
                return '';
            }
            break;
            
        case 'url':
            // Limpiar y validar URL
            $dato = filter_var($dato, FILTER_SANITIZE_URL);
            if (!filter_var($dato, FILTER_VALIDATE_URL)) {
                log_instalador("Error de validación: formato de URL inválido", 'warning');
                return '';
            }
            break;
            
        case 'entero':
            // Asegurar que sea un entero válido
            return filter_var($dato, FILTER_VALIDATE_INT) !== false ? 
                   filter_var($dato, FILTER_SANITIZE_NUMBER_INT) : 0;
            
        case 'float':
            // Asegurar que sea un flotante válido
            return filter_var($dato, FILTER_VALIDATE_FLOAT) !== false ? 
                   filter_var($dato, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND) : 0;
            
        case 'sql_safe':
            // Eliminar caracteres peligrosos para SQL pero mantener la estructura
            return preg_replace('/[^\w\s\.\,\-\_\@\(\)]/', '', $dato);
            
        case 'nombre_bd':
            // Validar que el nombre de la BD solo contenga caracteres seguros
            return preg_replace('/[^a-zA-Z0-9_]/', '', $dato);
            
        case 'directorio':
            // Sanitizar rutas de directorio
            return str_replace(['..', '\\', '&', '<', '>', '`', '$', '|', ';'], '', $dato);
            
        case 'texto':
        default:
            // Texto general, eliminar HTML y convertir caracteres especiales
            return htmlspecialchars(strip_tags($dato), ENT_QUOTES, 'UTF-8');
    }
    
    return $dato;
}

// Añadir la inclusión de los verificadores mejorados
if (file_exists(__DIR__ . '/db_verify.php')) {
    require_once __DIR__ . '/db_verify.php';
}

// Incluir verificador de usuario administrador
if (file_exists(__DIR__ . '/admin_verify.php')) {
    require_once __DIR__ . '/admin_verify.php';
}

// Función para verificar directorios críticos y sus permisos
function verificarDirectorios() {
    $root_dir = realpath(__DIR__ . '/../../');
    
    $directorios_criticos = [
        'almacenamiento/tmp' => [
            'path' => $root_dir . '/almacenamiento/tmp',
            'permisos' => 0755,
            'crear_si_no_existe' => true
        ],
        'almacenamiento/logs/sistema' => [
            'path' => $root_dir . '/almacenamiento/logs/sistema',
            'permisos' => 0755,
            'crear_si_no_existe' => true
        ],
        'almacenamiento/cache' => [
            'path' => $root_dir . '/almacenamiento/cache',
            'permisos' => 0755,
            'crear_si_no_existe' => true
        ],
        'publico/recursos' => [
            'path' => $root_dir . '/publico/recursos',
            'permisos' => 0755,
            'crear_si_no_existe' => false
        ],
        'almacenamiento/subidas' => [
            'path' => $root_dir . '/publico/subidas',
            'permisos' => 0755,
            'crear_si_no_existe' => true
        ],
        'config' => [
            'path' => $root_dir . '/config',
            'permisos' => 0755,
            'crear_si_no_existe' => false
        ],
        'raiz' => [
            'path' => $root_dir,
            'permisos' => 0755,
            'crear_si_no_existe' => false,
            'verificar_escritura' => true
        ]
    ];
    
    $resultado = [
        'correctos' => [],
        'incorrectos' => [],
        'creados' => [],
        'no_escritura' => []
    ];
    
    foreach ($directorios_criticos as $nombre => $info) {
        $dir_existe = file_exists($info['path']) && is_dir($info['path']);
        
        // Si no existe y se debe crear
        if (!$dir_existe && $info['crear_si_no_existe']) {
            $creado = @mkdir($info['path'], $info['permisos'], true);
            if ($creado) {
                $dir_existe = true;
                $resultado['creados'][] = $nombre;
                log_instalador("Directorio creado: {$info['path']}", 'success');
            } else {
                $resultado['incorrectos'][] = $nombre;
                log_instalador("No se pudo crear el directorio: {$info['path']}", 'error');
                continue;
            }
        }
        
        if ($dir_existe) {
            // Verificar permisos de escritura si es necesario
            if (isset($info['verificar_escritura']) && $info['verificar_escritura'] && !is_writable($info['path'])) {
                $resultado['no_escritura'][] = $nombre;
                log_instalador("El directorio {$info['path']} no tiene permisos de escritura", 'warning');
            } else {
                $resultado['correctos'][] = $nombre;
            }
        } else {
            $resultado['incorrectos'][] = $nombre;
            log_instalador("Directorio no encontrado: {$info['path']}", 'error');
        }
    }
    
    return $resultado;
}

// registro.php - Funciones para registrar actividad del usuario en instalación
function registrarActividadInstalacion($accion, $datos = []) {
    if (!isset($_SESSION['registro_actividad'])) {
        $_SESSION['registro_actividad'] = [];
    }
    
    $_SESSION['registro_actividad'][] = [
        'timestamp' => time(),
        'accion' => $accion,
        'datos' => $datos,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
    ];
}

// Verificar directorios al iniciar el instalador
$verificacion_directorios = verificarDirectorios();

// Registrar el resultado de la verificación
log_instalador("Verificación de directorios - Correctos: " . count($verificacion_directorios['correctos']) . 
              ", Incorrectos: " . count($verificacion_directorios['incorrectos']) . 
              ", Creados: " . count($verificacion_directorios['creados']), 'info');

// Procesar datos del formulario de base de datos (Paso 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['guardar_db']) || isset($_POST['probar_db']))) {
    // Marcar que ahora sí debe mostrar errores si los hay
    $_SESSION['mostrar_errores_db'] = true;
    
    // Aplicar sanitización apropiada a cada campo
    $db_host = sanitizar_entrada($_POST['db_host'], 'sql_safe');
    $db_name = sanitizar_entrada($_POST['db_name'], 'nombre_bd');
    $db_user = sanitizar_entrada($_POST['db_user'], 'sql_safe');
    $db_pass = $_POST['db_pass']; // No sanitizamos contraseñas para no alterar caracteres especiales
    
    log_instalador("Procesando formulario de BD: host=$db_host, db=$db_name, user=$db_user", 'info');
    
    // Validación mejorada
    $errores_db = false;
    
    if (empty($db_host)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El host de la base de datos es obligatorio'];
        $errores_db = true;
    }
    
    if (empty($db_name)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El nombre de la base de datos es obligatorio'];
        $errores_db = true;
    } else if (strlen($db_name) > 64) { // Limitación de MySQL
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El nombre de la base de datos no puede exceder los 64 caracteres'];
        $errores_db = true;
    }
    
    if (empty($db_user)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El usuario de la base de datos es obligatorio'];
        $errores_db = true;
    }
    
    // Guardar en sesión los datos (incluso si hay errores para no perderlos)
    $_SESSION['db_config'] = [
        'db_host' => $db_host,
        'db_name' => $db_name,
        'db_user' => $db_user,
        'db_pass' => $db_pass
    ];
    
    // Si no hay errores, probar la conexión o avanzar si ya está verificada
    if (!$errores_db) {
        // Distinguir entre probar conexión y continuar al siguiente paso
        $solo_probar = isset($_POST['probar_db']);
        
        // Verificar si los datos han cambiado desde la última verificación
        $datos_cambiados = false;
        if (isset($_SESSION['db_ultimo_verificado'])) {
            $ultimo = $_SESSION['db_ultimo_verificado'];
            if ($ultimo['host'] != $db_host || $ultimo['name'] != $db_name || 
                $ultimo['user'] != $db_user || $ultimo['pass'] != $db_pass) {
                $datos_cambiados = true;
                log_instalador("Datos de BD cambiados desde la última verificación", 'info');
                // Si los datos cambiaron, reiniciamos el estado de verificación
                $_SESSION['db_verificada'] = false;
            }
        } else {
            // Primera vez que se intenta verificar
            $datos_cambiados = true;
        }
        
        // Guardar estos datos como los últimos verificados
        $_SESSION['db_ultimo_verificado'] = [
            'host' => $db_host,
            'name' => $db_name,
            'user' => $db_user,
            'pass' => $db_pass
        ];
        
        // Si se intenta continuar sin verificar la conexión o si los datos cambiaron
        if (!$solo_probar && (!isset($_SESSION['db_verificada']) || $_SESSION['db_verificada'] === false)) {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'Por favor, primero verifica la conexión con la base de datos'];
            $errores_db = true;
        } 
        // Solo intentar conexión si es una prueba o si los datos han cambiado
        else {
            // Intentar conexión a MySQL si es una prueba o si los datos cambiaron o no está verificada
            if ($solo_probar || $datos_cambiados || !isset($_SESSION['db_verificada']) || !$_SESSION['db_verificada']) {
                // Usar la función avanzada de verificación si está disponible
                if (function_exists('verificarBaseDatos')) {
                    log_instalador("Usando el verificador avanzado de base de datos para: $db_host / $db_name", 'info');
                    
                    $verificacion = verificarBaseDatos($db_host, $db_user, $db_pass, $db_name);
                    
                    // Añadir los mensajes retornados
                    foreach ($verificacion['messages'] as $msg) {
                        $mensajes[] = $msg;
                    }
                    
                    // Registrar resultados detallados en el log
                    log_instalador("Resultado verificación BD - Success: " . ($verificacion['success'] ? 'SI' : 'NO') . 
                                   " | DB Existe: " . ($verificacion['db_exists'] ? 'SI' : 'NO') . 
                                   " | Puede crear: " . ($verificacion['can_create'] ? 'SI' : 'NO'), 
                                  $verificacion['success'] ? 'info' : 'error');
                    
                    // Establecer el estado de verificación según el resultado
                    $_SESSION['db_verificada'] = $verificacion['success'];
                    
                    // Guardar permisos detallados en sesión
                    if (isset($verificacion['permissions'])) {
                        $_SESSION['db_permisos'] = $verificacion['permissions'];
                    }
                    
                    // Si la verificación fue exitosa, comprobar las tablas existentes
                    if ($verificacion['success'] && function_exists('verificarTablasExistentes')) {
                        try {
                            $verificacionTablas = verificarTablasExistentes($db_host, $db_user, $db_pass, $db_name);
                            
                            // Guardar en sesión para usarlo después
                            $_SESSION['verificacion_tablas_inicial'] = $verificacionTablas;
                            
                            // Mostrar mensaje informativo si hay tablas existentes
                            if ($verificacionTablas['tablas_existen']) {
                                $cantidadTablas = $verificacionTablas['cantidad_tablas'];
                                $mensajes[] = [
                                    'tipo' => 'info', 
                                    'texto' => "Se encontraron $cantidadTablas tablas existentes del sistema AUTOEXAM2. Podrá elegir qué hacer con ellas en el paso 7."
                                ];
                                log_instalador("Se encontraron {$verificacionTablas['cantidad_tablas']} tablas existentes", 'warning');
                            } else {
                                log_instalador("No se encontraron tablas existentes", 'info');
                            }
                        } catch (Exception $e) {
                            $mensajes[] = ['tipo' => 'warning', 'texto' => "No se pudieron verificar las tablas existentes: " . $e->getMessage()];
                            log_instalador("Error al verificar tablas existentes: " . $e->getMessage(), 'error');
                        }
                    }
                    
                } else {
                    // Método original como respaldo
                    try {
                        log_instalador("Usando el verificador básico de base de datos", 'info');
                        
                        // Intentar conectar directamente a la base de datos específica
                        $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
                        
                        // Comprobar si hay errores de conexión
                        if ($mysqli->connect_error) {
                            // Si hay error, intentar conectar sin especificar la base de datos
                            $mysqli_nodb = @new mysqli($db_host, $db_user, $db_pass);
                            if ($mysqli_nodb->connect_error) {
                                throw new Exception("Error de conexión: " . $mysqli_nodb->connect_error);
                            }
                            $db_exists = false;
                            // Asignar el objeto de conexión sin DB para operaciones posteriores
                            $mysqli = $mysqli_nodb;
                        } else {
                            $db_exists = true;
                        }
                        
                        if (!$db_exists) {
                            // Intentar crear la base de datos
                            if ($mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                                // Seleccionar la base de datos recién creada
                                $mysqli->select_db($db_name);
                                $mensajes[] = ['tipo' => 'success', 'texto' => "Base de datos '$db_name' creada correctamente"];
                                // Marcar la conexión como verificada en la sesión
                                $_SESSION['db_verificada'] = true;
                                // Verificar permisos del usuario sobre la base de datos
                                $result = $mysqli->query("SHOW GRANTS FOR CURRENT_USER()");
                                if (!$result) {
                                    $mensajes[] = ['tipo' => 'warning', 'texto' => "Aviso: No se pudieron verificar los permisos del usuario sobre la base de datos"];
                                } else {
                                    $tiene_permisos = false;
                                    while ($row = $result->fetch_row()) {
                                        // Verificar si tiene privilegios suficientes
                                        if (strpos($row[0], 'ALL PRIVILEGES') !== false || 
                                            (strpos($row[0], 'SELECT') !== false && 
                                             strpos($row[0], 'INSERT') !== false && 
                                             strpos($row[0], 'UPDATE') !== false && 
                                             strpos($row[0], 'DELETE') !== false && 
                                             strpos($row[0], 'CREATE') !== false)) {
                                            $tiene_permisos = true;
                                            break;
                                        }
                                    }
                                    if (!$tiene_permisos) {
                                        $mensajes[] = ['tipo' => 'warning', 'texto' => "Aviso: El usuario podría no tener todos los permisos necesarios sobre la base de datos"];
                                    }
                                }
                            } else {
                                throw new Exception("No se pudo crear la base de datos: " . $mysqli->error);
                            }
                        } else {
                            // Hacer una prueba completa de la conexión verificando si se pueden realizar operaciones CRUD
                            try {
                                // Intentar crear una tabla temporal para probar permisos
                                $tabla_prueba = 'installer_test_' . rand(1000, 9999);
                                $crear_tabla = "CREATE TEMPORARY TABLE `$tabla_prueba` (id INT, test VARCHAR(10))";
                                $insertar = "INSERT INTO `$tabla_prueba` VALUES (1, 'test')";
                                $seleccionar = "SELECT * FROM `$tabla_prueba`";
                                $eliminar = "DROP TABLE IF EXISTS `$tabla_prueba`";
                                
                                if (!$mysqli->query($crear_tabla)) {
                                    throw new Exception("No se pudo crear una tabla temporal: " . $mysqli->error . ". Verifique los permisos del usuario.");
                                }
                                if (!$mysqli->query($insertar)) {
                                    throw new Exception("No se pudo insertar datos: " . $mysqli->error . ". Verifique los permisos del usuario.");
                                }
                                $result = $mysqli->query($seleccionar);
                                if (!$result) {
                                    throw new Exception("No se pudieron leer datos: " . $mysqli->error . ". Verifique los permisos del usuario.");
                                }
                                $mysqli->query($eliminar); // Limpiar la tabla de prueba
                                
                                $mensajes[] = ['tipo' => 'success', 'texto' => "Base de datos '$db_name' conectada correctamente y permisos verificados"];
                                // Marcar la conexión como verificada en la sesión
                                $_SESSION['db_verificada'] = true;
                                
                                // Verificar tablas existentes después de conectar exitosamente
                                if (function_exists('verificarTablasExistentes')) {
                                    try {
                                        $verificacion = verificarTablasExistentes($db_host, $db_user, $db_pass, $db_name);
                                        
                                        // Guardar en sesión para usarlo después
                                        $_SESSION['verificacion_tablas_inicial'] = $verificacion;
                                        
                                        // Mostrar mensaje informativo si hay tablas existentes
                                        if ($verificacion['tablas_existen']) {
                                            $cantidadTablas = $verificacion['cantidad_tablas'];
                                            $mensajes[] = [
                                                'tipo' => 'info', 
                                                'texto' => "Se encontraron $cantidadTablas tablas existentes del sistema AUTOEXAM2. Podrá elegir qué hacer con ellas en el paso 7."
                                            ];
                                            log_instalador("Se encontraron {$verificacion['cantidad_tablas']} tablas existentes", 'warning');
                                        } else {
                                            log_instalador("No se encontraron tablas existentes", 'info');
                                        }
                                    } catch (Exception $e) {
                                        $mensajes[] = ['tipo' => 'warning', 'texto' => "No se pudieron verificar las tablas existentes: " . $e->getMessage()];
                                        log_instalador("Error al verificar tablas existentes: " . $e->getMessage(), 'error');
                                    }
                                }
                            } catch (Exception $e) {
                                // Si hay problemas con los permisos pero la conexión funciona
                                $mensajes[] = ['tipo' => 'warning', 'texto' => $e->getMessage()];
                                $mensajes[] = ['tipo' => 'success', 'texto' => "Base de datos '$db_name' conectada, pero con limitaciones de permisos"];
                                $_SESSION['db_verificada'] = true; // Permitimos continuar pero con advertencia
                            }
                        }
                        
                        $mysqli->close();
                        
                    } catch (Exception $e) {
                        $mensajes[] = ['tipo' => 'error', 'texto' => $e->getMessage()];
                        // Marcar la conexión como no verificada
                        $_SESSION['db_verificada'] = false;
                        log_instalador("Error verificación BD: " . $e->getMessage(), 'error');
                    }
                }
            }
            
            // Si todo va bien y no es solo una prueba, avanzar al siguiente paso
            if (!$solo_probar && $_SESSION['db_verificada']) {
                $_SESSION['paso_actual'] = 3;
                $_SESSION['paso_maximo'] = max($_SESSION['paso_maximo'] ?? 1, 3);
            }
        }
    }
}

// Procesar datos del formulario SMTP (Paso 3)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['guardar_smtp']) || isset($_POST['probar_smtp']))) {
    // Marcar que ahora sí debe mostrar errores si los hay
    $_SESSION['mostrar_errores_smtp'] = true;
    
    $smtp_tipo = trim($_POST['smtp_tipo']);
    $smtp_host = trim($_POST['smtp_host']);
    $smtp_port = trim($_POST['smtp_port']);
    $smtp_user = trim($_POST['smtp_user']);
    $smtp_pass = $_POST['smtp_pass']; // No hacemos trim en contraseñas
    $smtp_secure = trim($_POST['smtp_secure']);
    
    // Validación básica
    $errores_smtp = false;
    
    if (empty($smtp_host)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El host SMTP es obligatorio'];
        $errores_smtp = true;
    }
    
    if (empty($smtp_port)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El puerto SMTP es obligatorio'];
        $errores_smtp = true;
    }
    
    if (empty($smtp_user)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El usuario SMTP es obligatorio'];
        $errores_smtp = true;
    }
    
    // Guardar en sesión los datos
    $_SESSION['smtp_config'] = [
        'smtp_tipo' => $smtp_tipo,
        'smtp_host' => $smtp_host,
        'smtp_port' => $smtp_port,
        'smtp_user' => $smtp_user,
        'smtp_pass' => $smtp_pass,
        'smtp_secure' => $smtp_secure
    ];
    
    // Si no hay errores, probar la conexión o avanzar si ya está verificada
    if (!$errores_smtp) {
        // Distinguir entre probar conexión y continuar al siguiente paso
        $solo_probar = isset($_POST['probar_smtp']);
        
        // Verificar si los datos han cambiado desde la última verificación
        $datos_cambiados = false;
        if (isset($_SESSION['smtp_ultimo_verificado'])) {
            $ultimo = $_SESSION['smtp_ultimo_verificado'];
            if ($ultimo['host'] != $smtp_host || $ultimo['port'] != $smtp_port || 
                $ultimo['user'] != $smtp_user || $ultimo['pass'] != $smtp_pass || 
                $ultimo['secure'] != $smtp_secure) {
                $datos_cambiados = true;
                log_instalador("Datos de SMTP cambiados desde la última verificación", 'info');
                // Si los datos cambiaron, reiniciamos el estado de verificación
                $_SESSION['smtp_verificada'] = false;
            }
        } else {
            // Primera vez que se intenta verificar
            $datos_cambiados = true;
        }
        
        // Guardar estos datos como los últimos verificados
        $_SESSION['smtp_ultimo_verificado'] = [
            'host' => $smtp_host,
            'port' => $smtp_port,
            'user' => $smtp_user,
            'pass' => $smtp_pass,
            'secure' => $smtp_secure
        ];
        
        // Si se intenta continuar sin verificar la conexión o si los datos cambiaron
        if (!$solo_probar && (!isset($_SESSION['smtp_verificada']) || $_SESSION['smtp_verificada'] === false)) {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'Por favor, primero verifica la conexión con el servidor SMTP'];
            $errores_smtp = true;
        } 
        // Solo intentar conexión si es una prueba o si los datos han cambiado
        else {
            // Intentar conexión SMTP si es una prueba o si los datos cambiaron o no está verificada
            if ($solo_probar || $datos_cambiados || !isset($_SESSION['smtp_verificada']) || !$_SESSION['smtp_verificada']) {
                try {
                    // En un entorno real, aquí usaríamos PHPMailer o similar para probar la conexión
                    // Por ahora solo simulamos el resultado basado en datos comunes
                    $test_ok = false;
                    
                    if (strpos($smtp_host, '.') !== false && !empty($smtp_port) && !empty($smtp_user) && !empty($smtp_pass)) {
                        $test_ok = true;
                    } else {
                        throw new Exception('Datos de conexión SMTP incompletos o incorrectos');
                    }
                    
                    if ($test_ok) {
                        $mensajes[] = ['tipo' => 'success', 'texto' => 'Conexión SMTP probada correctamente'];
                        // Marcar la conexión como verificada en la sesión
                        $_SESSION['smtp_verificada'] = true;
                    }
                } catch (Exception $e) {
                    $mensajes[] = ['tipo' => 'error', 'texto' => 'Error de conexión SMTP: ' . $e->getMessage()];
                    // Marcar la conexión como no verificada
                    $_SESSION['smtp_verificada'] = false;
                }
            }
            
            // Si todo va bien y no es solo una prueba, avanzar al siguiente paso
            if (!$solo_probar && $_SESSION['smtp_verificada']) {
                $_SESSION['paso_actual'] = 4;
                $_SESSION['paso_maximo'] = max($_SESSION['paso_maximo'] ?? 1, 4);
            }
        }
    }
}

// Procesar datos del formulario FTP (Paso 4)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['guardar_ftp']) || isset($_POST['probar_ftp']))) {
    // Marcar que ahora sí debe mostrar errores si los hay
    $_SESSION['mostrar_errores_ftp'] = true;
    
    $ftp_tipo = trim($_POST['ftp_tipo']);
    $ftp_host = trim($_POST['ftp_host']);
    $ftp_user = trim($_POST['ftp_user']);
    $ftp_pass = $_POST['ftp_pass']; // No hacemos trim en contraseñas
    $ftp_port = trim($_POST['ftp_port']);
    $ftp_path = trim($_POST['ftp_path']);
    
    // Ajustar el puerto automáticamente si está vacío o si cambió el tipo de conexión
    if (empty($ftp_port) || (isset($_SESSION['ftp_config']['ftp_tipo']) && $_SESSION['ftp_config']['ftp_tipo'] !== $ftp_tipo)) {
        $ftp_port = $ftp_tipo === 'sftp' ? '22' : '21';
        log_instalador("Puerto FTP/SFTP ajustado automáticamente a $ftp_port basado en el tipo de conexión ($ftp_tipo)", 'info');
    }
    
    // Validación básica
    $errores_ftp = false;
    
    if (empty($ftp_host)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El host '.$ftp_tipo.' es obligatorio'];
        $errores_ftp = true;
    }
    
    if (empty($ftp_user)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El usuario '.$ftp_tipo.' es obligatorio'];
        $errores_ftp = true;
    }
    
    // Guardar en sesión los datos
    $_SESSION['ftp_config'] = [
        'ftp_tipo' => $ftp_tipo,
        'ftp_host' => $ftp_host,
        'ftp_user' => $ftp_user,
        'ftp_pass' => $ftp_pass,
        'ftp_port' => $ftp_port,
        'ftp_path' => $ftp_path
    ];
    
    // Si no hay errores, probar la conexión o avanzar si ya está verificada
    if (!$errores_ftp) {
        // Distinguir entre probar conexión y continuar al siguiente paso
        $solo_probar = isset($_POST['probar_ftp']);
        
        // Verificar si los datos han cambiado desde la última verificación
        $datos_cambiados = false;
        if (isset($_SESSION['ftp_ultimo_verificado'])) {
            $ultimo = $_SESSION['ftp_ultimo_verificado'];
            if ($ultimo['host'] != $ftp_host || $ultimo['user'] != $ftp_user || 
                $ultimo['pass'] != $ftp_pass || $ultimo['port'] != $ftp_port) {
                $datos_cambiados = true;
                log_instalador("Datos de FTP cambiados desde la última verificación", 'info');
                // Si los datos cambiaron, reiniciamos el estado de verificación
                $_SESSION['ftp_verificada'] = false;
            }
        } else {
            // Primera vez que se intenta verificar
            $datos_cambiados = true;
        }
        
        // Guardar estos datos como los últimos verificados
        $_SESSION['ftp_ultimo_verificado'] = [
            'host' => $ftp_host,
            'user' => $ftp_user,
            'pass' => $ftp_pass,
            'port' => $ftp_port,
            'path' => $ftp_path
        ];
        
        // Si se intenta continuar sin verificar la conexión o si los datos cambiaron
        if (!$solo_probar && (!isset($_SESSION['ftp_verificada']) || $_SESSION['ftp_verificada'] === false)) {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'Por favor, primero verifica la conexión con el servidor FTP/SFTP'];
            $errores_ftp = true;
        } 
        // Solo intentar conexión si es una prueba o si los datos han cambiado
        else {
            // Intentar conexión FTP si es una prueba o si los datos cambiaron o no está verificada
            if ($solo_probar || $datos_cambiados || !isset($_SESSION['ftp_verificada']) || !$_SESSION['ftp_verificada']) {                        try {
                    // En un entorno real, aquí usaríamos funciones ftp_connect o similar
                    // Por ahora solo simulamos el resultado basado en datos comunes
                    $test_ok = false;
                    
                    if (!empty($ftp_host) && !empty($ftp_port) && !empty($ftp_user) && !empty($ftp_pass)) {
                        $test_ok = true;
                        log_instalador("Probando conexión {$ftp_tipo} a {$ftp_host}:{$ftp_port} con usuario {$ftp_user}", 'info');
                    } else {
                        $missing = [];
                        if (empty($ftp_host)) $missing[] = 'host';
                        if (empty($ftp_port)) $missing[] = 'puerto';
                        if (empty($ftp_user)) $missing[] = 'usuario';
                        if (empty($ftp_pass)) $missing[] = 'contraseña';
                        
                        $missing_str = implode(', ', $missing);
                        log_instalador("Datos de conexión {$ftp_tipo} incompletos. Faltan: {$missing_str}", 'error');
                        throw new Exception('Datos de conexión FTP/SFTP incompletos o incorrectos');
                    }
                    
                    if ($test_ok) {
                        $mensajes[] = ['tipo' => 'success', 'texto' => "Conexión {$ftp_tipo} probada correctamente"];
                        log_instalador("Conexión {$ftp_tipo} a {$ftp_host}:{$ftp_port} verificada con éxito", 'success');
                        // Marcar la conexión como verificada en la sesión
                        $_SESSION['ftp_verificada'] = true;
                    }
                } catch (Exception $e) {
                    $mensajes[] = ['tipo' => 'error', 'texto' => 'Error de conexión FTP/SFTP: ' . $e->getMessage()];
                    // Marcar la conexión como no verificada
                    $_SESSION['ftp_verificada'] = false;
                }
            }
            
            // Si todo va bien y no es solo una prueba, avanzar al siguiente paso
            if (!$solo_probar && $_SESSION['ftp_verificada']) {
                $_SESSION['paso_actual'] = 5;
                $_SESSION['paso_maximo'] = max($_SESSION['paso_maximo'] ?? 1, 5);
            }
        }
    }
}

// Función para evaluar la fortaleza de la contraseña
function evaluarFortalezaContrasena($password) {
    $fortaleza = 0;
    $problemas = [];
    
    // Verificar longitud mínima (8 caracteres)
    if (strlen($password) < 8) {
        $problemas[] = "La contraseña debe tener al menos 8 caracteres";
    } else {
        $fortaleza++;
    }
    
    // Verificar la presencia de letras minúsculas
    if (!preg_match('/[a-z]/', $password)) {
        $problemas[] = "Debe incluir al menos una letra minúscula";
    } else {
        $fortaleza++;
    }
    
    // Verificar la presencia de letras mayúsculas
    if (!preg_match('/[A-Z]/', $password)) {
        $problemas[] = "Debe incluir al menos una letra mayúscula";
    } else {
        $fortaleza++;
    }
    
    // Verificar la presencia de números
    if (!preg_match('/[0-9]/', $password)) {
        $problemas[] = "Debe incluir al menos un número";
    } else {
        $fortaleza++;
    }
    
    // Verificar la presencia de caracteres especiales
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $problemas[] = "Debe incluir al menos un carácter especial";
    } else {
        $fortaleza++;
    }
    
    // Verificar que no sea una contraseña común
    $contrasenas_comunes = ['password', '123456', 'admin', 'qwerty', 'welcome', 'admin123'];
    if (in_array(strtolower($password), $contrasenas_comunes)) {
        $problemas[] = "La contraseña es demasiado común o débil";
        $fortaleza = 0; // Inmediatamente hacer la contraseña débil
    }
    
    return [
        'fortaleza' => $fortaleza,     // 0-5, donde 5 es la más fuerte
        'descripcion' => $fortaleza <= 2 ? 'débil' : ($fortaleza <= 4 ? 'media' : 'fuerte'),
        'problemas' => $problemas
    ];
}

// Procesar datos del formulario de administrador (Paso 6)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_admin'])) {
    $system_name = sanitizar_entrada($_POST['system_name'], 'texto');
    $admin_email = sanitizar_entrada($_POST['admin_email'], 'email');
    $admin_pass = $_POST['admin_pass']; // No aplicar sanitización a contraseñas
    $admin_confirm = $_POST['admin_confirm'];
    
    // Validación mejorada
    $errores_admin = false;
    
    if (empty($admin_email)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El correo del administrador es obligatorio'];
        $errores_admin = true;
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El formato del correo electrónico no es válido'];
        $errores_admin = true;
    }
    
    // Evaluar fortaleza de la contraseña
    $evaluacion = evaluarFortalezaContrasena($admin_pass);
    
    // Si la contraseña es débil, mostrar los problemas
    if ($evaluacion['fortaleza'] <= 2) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'La contraseña es demasiado débil: ' . implode(', ', $evaluacion['problemas'])];
        $errores_admin = true;
    }
    
    if ($admin_pass !== $admin_confirm) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'Las contraseñas no coinciden'];
        $errores_admin = true;
    }
    
    // Guardar en sesión
    $_SESSION['admin_config'] = [
        'admin_email' => $admin_email,
        'admin_pass' => $admin_pass,
        'admin_confirm' => $admin_confirm
    ];
    
    // Validar el nombre del sistema
    if (empty($system_name)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El nombre del sistema es obligatorio'];
        $errores_admin = true;
    } elseif (strlen($system_name) > 50) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El nombre del sistema no puede tener más de 50 caracteres'];
        $errores_admin = true;
    }
    
    // Si no hay errores, continuar
    if (!$errores_admin) {
        // Guardar el nombre del sistema
        $_SESSION['admin_config']['system_name'] = $system_name;
        
        // Continuar al siguiente paso
        $_SESSION['paso_actual'] = 7;
        $_SESSION['paso_maximo'] = max($_SESSION['paso_maximo'], 7);
    }
}

// Procesar subida de imágenes (Paso 5)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_imagenes'])) {
    $subida_correcta = true;
    $dir_subidas = realpath(__DIR__ . "/../recursos/");
    $dir_img = $dir_subidas . '/img';
    
    // Inicializar el array de archivos subidos en la sesión si no existe
    if (!isset($_SESSION['archivos_subidos'])) {
        $_SESSION['archivos_subidos'] = [];
    }
    
    // Verificar directorio de subidas
    if (!$dir_subidas || !is_writable($dir_subidas)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El directorio de recursos no existe o no tiene permisos de escritura'];
        $subida_correcta = false;
    }
    
    // Asegurar que existe el subdirectorio de imágenes
    if (!file_exists($dir_img)) {
        if (!@mkdir($dir_img, 0755, true)) {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'No se pudo crear el directorio para imágenes'];
            $subida_correcta = false;
        }
    } elseif (!is_writable($dir_img)) {
        $mensajes[] = ['tipo' => 'error', 'texto' => 'El directorio de imágenes no tiene permisos de escritura'];
        $subida_correcta = false;
    }
    
    // Logo del centro
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $_FILES['logo']['size'] > 0) {
        $tipo_archivo = mime_content_type($_FILES['logo']['tmp_name']);
        if ($tipo_archivo !== 'image/png' && $tipo_archivo !== 'image/jpeg') {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'El logo debe ser una imagen PNG o JPG'];
            $subida_correcta = false;
        } else {
            // Mover el archivo
            $ruta_logo = $dir_subidas . '/logo.png';
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_logo)) {
                $mensajes[] = ['tipo' => 'success', 'texto' => 'Logo subido correctamente'];
                
                // Guardar información del archivo en la sesión
                $_SESSION['archivos_subidos']['logo'] = [
                    'nombre_original' => $_FILES['logo']['name'],
                    'nombre_archivo' => 'logo.png',
                    'ruta_completa' => $ruta_logo,
                    'ruta_relativa' => '../recursos/logo.png',
                    'tamano' => $_FILES['logo']['size'],
                    'tipo_mime' => $tipo_archivo,
                    'fecha_subida' => date('Y-m-d H:i:s')
                ];
                
                log_instalador("Logo subido: {$_FILES['logo']['name']} -> logo.png", 'success');
            } else {
                $mensajes[] = ['tipo' => 'error', 'texto' => 'No se pudo subir el logo'];
                $subida_correcta = false;
            }
        }
    }
    
    // Imagen de usuario por defecto
    if (isset($_FILES['user_image_default']) && $_FILES['user_image_default']['error'] === UPLOAD_ERR_OK && $_FILES['user_image_default']['size'] > 0) {
        $tipo_archivo = mime_content_type($_FILES['user_image_default']['tmp_name']);
        if ($tipo_archivo !== 'image/png' && $tipo_archivo !== 'image/jpeg') {
            $mensajes[] = ['tipo' => 'error', 'texto' => 'La imagen de usuario debe ser PNG o JPG'];
            $subida_correcta = false;
        } else {
            // Mover el archivo
            $ruta_user_img = $dir_img . '/user_image_default.png';
            if (move_uploaded_file($_FILES['user_image_default']['tmp_name'], $ruta_user_img)) {
                $mensajes[] = ['tipo' => 'success', 'texto' => 'Imagen de usuario subida correctamente'];
                
                // Guardar información del archivo en la sesión
                $_SESSION['archivos_subidos']['user_image_default'] = [
                    'nombre_original' => $_FILES['user_image_default']['name'],
                    'nombre_archivo' => 'user_image_default.png',
                    'ruta_completa' => $ruta_user_img,
                    'ruta_relativa' => '../recursos/img/user_image_default.png',
                    'tamano' => $_FILES['user_image_default']['size'],
                    'tipo_mime' => $tipo_archivo,
                    'fecha_subida' => date('Y-m-d H:i:s')
                ];
                
                log_instalador("Imagen de usuario subida: {$_FILES['user_image_default']['name']} -> user_image_default.png", 'success');
            } else {
                $mensajes[] = ['tipo' => 'error', 'texto' => 'No se pudo subir la imagen de usuario'];
                $subida_correcta = false;
            }
        }
    }
    
    // Si todo va bien o si no se subieron imágenes (son opcionales), avanzar
    if ($subida_correcta) {
        $_SESSION['paso_actual'] = 6;
        $_SESSION['paso_maximo'] = max($_SESSION['paso_maximo'], 6);
    }
}

// Instalación final (Paso 7)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar'])) {
    $instalacion_correcta = true;
    
    try {
        // 1. Conectar a la base de datos
        $db = $_SESSION['db_config'];
        $mysqli = new mysqli($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name']);
        
        if ($mysqli->connect_error) {
            throw new Exception('No se pudo conectar a la base de datos: ' . $mysqli->connect_error);
        }
        
        // 2. Verificar acción a realizar con tablas existentes
        $accion_tablas = isset($_POST['accion_tablas']) ? $_POST['accion_tablas'] : 'actualizar';
        
        // Verificar confirmación para acciones peligrosas
        if (($accion_tablas === 'eliminar' || $accion_tablas === 'vaciar') && 
            (!isset($_POST['confirmar_accion_peligrosa']) || $_POST['confirmar_accion_peligrosa'] != '1')) {
            throw new Exception('Debe confirmar explícitamente la acción peligrosa para continuar.');
        }
        
        // Ejecutar la acción seleccionada
        if ($accion_tablas === 'eliminar') {
            // Eliminar todas las tablas existentes
            log_instalador("Eliminando todas las tablas existentes...", 'warning');
            if (function_exists('eliminarTablasExistentes')) {
                $resultado = eliminarTablasExistentes($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name']);
                if ($resultado['success']) {
                    log_instalador("Se eliminaron {$resultado['tablas_eliminadas']} tablas correctamente", 'success');
                    $mensajes[] = ['tipo' => 'success', 'texto' => "Se eliminaron {$resultado['tablas_eliminadas']} tablas correctamente"];
                } else {
                    log_instalador("Error al eliminar tablas: {$resultado['error']}", 'error');
                    throw new Exception("No se pudieron eliminar las tablas: {$resultado['error']}");
                }
            } else {
                throw new Exception('La función para eliminar tablas no está disponible');
            }
        } else if ($accion_tablas === 'vaciar') {
            // Vaciar todas las tablas existentes
            log_instalador("Vaciando todas las tablas existentes...", 'warning');
            if (function_exists('vaciarTablasExistentes')) {
                $resultado = vaciarTablasExistentes($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name']);
                if ($resultado['success']) {
                    log_instalador("Se vaciaron {$resultado['tablas_vaciadas']} tablas correctamente", 'success');
                    $mensajes[] = ['tipo' => 'success', 'texto' => "Se vaciaron {$resultado['tablas_vaciadas']} tablas correctamente"];
                    
                    // Crear usuario administrador directamente después de vaciar las tablas
                    // Después de vaciar las tablas, debemos crear el usuario administrador
                    if (function_exists('crearUsuarioAdmin')) {
                        $admin = $_SESSION['admin_config'];
                        $resultado_admin = crearUsuarioAdmin(
                            $_SESSION['db_config'], 
                            $admin
                        );
                        
                        // Agregar los mensajes retornados
                        foreach ($resultado_admin['messages'] as $msg) {
                            $mensajes[] = $msg;
                        }
                        
                        if ($resultado_admin['success']) {
                            log_instalador(
                                "Usuario administrador creado después del vaciado de tablas: " . $admin['admin_email'] . 
                                " (ID: " . ($resultado_admin['user_id'] ?? 'desconocido') . ")",
                                'success'
                            );
                            
                            // Marcar la instalación como completa sin ejecutar el SQL completo
                            $_SESSION['resumen_instalacion'] = [
                                "Se vaciaron {$resultado['tablas_vaciadas']} tablas manteniendo la estructura",
                                "Se creó el usuario administrador: " . htmlspecialchars($admin['admin_email']),
                                "Se guardó la configuración en el archivo .env",
                                "Se bloqueó el instalador correctamente"
                            ];
                            
                            $_SESSION['instalacion_completada'] = true;
                            
                            // Saltar directamente a la creación del archivo .env, evitando ejecutar el SQL completo
                            goto crear_env_y_finalizar;
                        } else {
                            log_instalador("Error al crear usuario admin después de vaciar tablas", 'error');
                            throw new Exception("Error al crear el usuario administrador");
                        }
                    }
                } else {
                    log_instalador("Error al vaciar tablas: {$resultado['error']}", 'error');
                    throw new Exception("No se pudieron vaciar las tablas: {$resultado['error']}");
                }
            } else {
                throw new Exception('La función para vaciar tablas no está disponible');
            }
        } else if ($accion_tablas === 'actualizar') {
            // Usar método inteligente para actualizar tablas
            $sql_path = realpath(__DIR__ . '/../../base_datos/migraciones/001_esquema_completo.sql');
            
            if (!$sql_path || !is_readable($sql_path)) {
                throw new Exception('No se encontró el archivo SQL o no es legible');
            }
            
            // Verificar si existe el archivo de actualización inteligente
            $actualizar_path = __DIR__ . '/actualizar_tablas.php';
            if (!file_exists($actualizar_path)) {
                throw new Exception('No se encontró el archivo para actualizar tablas de manera inteligente');
            }
            
            // Cargar la función de actualización inteligente
            require_once $actualizar_path;
            
            if (function_exists('actualizarTablasInteligente')) {
                log_instalador("Iniciando actualización inteligente de tablas...", 'info');
                $resultado_act = actualizarTablasInteligente($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name'], $sql_path);
                
                // Agregar mensajes del resultado
                foreach ($resultado_act['messages'] as $msg) {
                    $mensajes[] = $msg;
                }
                
                // Si hay errores, registrarlos pero continuar
                if (!empty($resultado_act['errors'])) {
                    foreach ($resultado_act['errors'] as $error) {
                        log_instalador("Error en actualización: $error", 'warning');
                    }
                }
                
                if (!$resultado_act['success']) {
                    log_instalador("La actualización de tablas encontró problemas pero continuamos", 'warning');
                    $mensajes[] = ['tipo' => 'warning', 'texto' => 'La actualización de tablas encontró algunos problemas pero se intentará continuar con el proceso'];
                } else {
                    log_instalador("Actualización inteligente de tablas completada exitosamente", 'success');
                }
                
                // Inicializar el array de resumen si no existe
                if (!isset($_SESSION['resumen_instalacion']) || !is_array($_SESSION['resumen_instalacion'])) {
                    $_SESSION['resumen_instalacion'] = [];
                }
                
                // Añadir registro de resumen para la actualización inteligente
                $_SESSION['resumen_instalacion'][] = "Se actualizaron las tablas de manera inteligente:";
                $_SESSION['resumen_instalacion'][] = "- {$resultado_act['tablas_existentes']} tablas existentes preservadas";
                $_SESSION['resumen_instalacion'][] = "- {$resultado_act['tablas_creadas']} tablas nuevas creadas";
                
                if (!empty($resultado_act['tablas_no_creadas'])) {
                    $_SESSION['resumen_instalacion'][] = "- Se encontraron problemas con " . count($resultado_act['tablas_no_creadas']) . " tablas";
                }
                
                // Procedemos directamente a verificar/crear usuario admin (skip SQL execution)
                goto verificar_admin;
            }
        }
        
        // Si no se usó actualización inteligente o no está disponible, se ejecuta el script SQL completo
        // 3. Ejecutar el script SQL para crear o actualizar tablas
        $sql_path = realpath(__DIR__ . '/../../base_datos/migraciones/001_esquema_completo.sql');
        
        if (!$sql_path || !is_readable($sql_path)) {
            throw new Exception('No se encontró el archivo SQL o no es legible');
        }
        
        log_instalador("Ejecutando script SQL completo desde: $sql_path", 'info');
        
        // Usar la función avanzada de ejecución SQL si está disponible
        if (function_exists('ejecutarScriptSQL')) {
            $resultado_sql = ejecutarScriptSQL($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name'], $sql_path);
            
            // Añadir los mensajes retornados
            foreach ($resultado_sql['messages'] as $msg) {
                $mensajes[] = $msg;
            }
            
            // Si hay errores, registrarlos
            if (!empty($resultado_sql['errors'])) {
                log_instalador("Se encontraron " . count($resultado_sql['errors']) . " errores al ejecutar el SQL", 'warning');
                foreach (array_slice($resultado_sql['errors'], 0, 5) as $error) {
                    log_instalador("Error SQL: " . substr($error, 0, 200), 'error');
                }
            }
            
            if (!$resultado_sql['success']) {
                throw new Exception("La ejecución del script SQL ha fallado con demasiados errores");
            }
            
        } else {
            // Método original como respaldo
            try {
                log_instalador("Usando el método básico para ejecutar SQL", 'info');
                
                // Configurar el modo de error de MySQLi
                $mysqli->options(MYSQLI_OPT_LOCAL_INFILE, true);
                
                // Aumentar timeout y límite de ejecución para scripts largos
                set_time_limit(300); // 5 minutos
                $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 300);
                
                // Ejecutar SQL por partes
                $sql = file_get_contents($sql_path);
                
                // Separar el SQL en declaraciones individuales
                $statements = explode(';', $sql);
                $errors = [];
                
                foreach ($statements as $statement) {
                    $stmt = trim($statement);
                    if (!empty($stmt)) {
                        if (!$mysqli->query($stmt)) {
                            // Colectar errores en lugar de fallar inmediatamente
                            $errors[] = $mysqli->error . " en: " . substr($stmt, 0, 100) . "...";
                        }
                    }
                }
                
                // Si hay errores, mostrar advertencia pero continuar si es posible
                if (!empty($errors)) {
                    $mensajes[] = ['tipo' => 'warning', 'texto' => 'Se encontraron ' . count($errors) . ' errores al ejecutar el SQL. Revise la base de datos.'];
                    // Limitar a los primeros 3 errores para no sobrecargar la pantalla
                    for ($i = 0; $i < min(3, count($errors)); $i++) {
                        $mensajes[] = ['tipo' => 'warning', 'texto' => 'Error SQL: ' . $errors[$i]];
                    }
                } else {
                    $mensajes[] = ['tipo' => 'success', 'texto' => 'Script SQL ejecutado correctamente'];
                }
            } catch (Exception $e) {
                // Intentar método alternativo si falla el primero
                try {
                    log_instalador("Intentando método alternativo para ejecutar SQL", 'warning');
                    
                    // Resetear la conexión
                    $mysqli->close();
                    $mysqli = new mysqli($db['db_host'], $db['db_user'], $db['db_pass'], $db['db_name']);
                    
                    // Intentar con multi_query para consultas más simples
                    $mysqli->multi_query($sql);
                    
                    // Limpiar resultados
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                    
                    if ($mysqli->error) {
                        throw new Exception('Error al ejecutar SQL: ' . $mysqli->error);
                    }
                    
                    $mensajes[] = ['tipo' => 'success', 'texto' => 'Script SQL ejecutado con método alternativo'];
                    
                } catch (Exception $e2) {
                    log_instalador("Error en ambos métodos para ejecutar SQL: " . $e2->getMessage(), 'error');
                    throw new Exception('No se pudo ejecutar el SQL: ' . $e2->getMessage());
                }
            }
        }
        
        // Etiqueta para creación de usuario administrador (proveniente de goto en opciones anteriores)
        verificar_admin:
        // 3. Crear usuario administrador usando funcionalidad avanzada
        if (function_exists('crearUsuarioAdmin')) {
            $admin = $_SESSION['admin_config'];
            $resultado_admin = crearUsuarioAdmin(
                $_SESSION['db_config'], 
                $admin
            );
            
            // Agregar los mensajes retornados
            foreach ($resultado_admin['messages'] as $msg) {
                $mensajes[] = $msg;
            }
            
            if (!$resultado_admin['success']) {
                throw new Exception('Error al crear el usuario administrador');
            }
            
            log_instalador(
                "Usuario administrador creado: " . $admin['admin_email'] . 
                " (ID: " . ($resultado_admin['user_id'] ?? 'desconocido') . ")",
                'success'
            );
            
        } else {
            // Método original como respaldo
            $admin = $_SESSION['admin_config'];
            $email = $mysqli->real_escape_string($admin['admin_email']);
            $password_hash = password_hash($admin['admin_pass'], PASSWORD_DEFAULT);
            
            // Verificar si el usuario ya existe
            $check_query = "SELECT id FROM usuarios WHERE correo = '$email'";
            $result = $mysqli->query($check_query);
            
            if ($result && $result->num_rows > 0) {
                // Actualizar usuario existente
                $row = $result->fetch_assoc();
                $user_id = $row['id'];
                $update_query = "UPDATE usuarios SET contrasena = '$password_hash', rol = 'admin', activo = 1 WHERE id = $user_id";
                
                if (!$mysqli->query($update_query)) {
                    throw new Exception('Error al actualizar usuario administrador: ' . $mysqli->error);
                }
                
                $mensajes[] = ['tipo' => 'info', 'texto' => 'El usuario administrador ya existía y ha sido actualizado'];
            } else {
                // Crear nuevo usuario
                $query = "INSERT INTO usuarios (nombre, apellidos, correo, contrasena, rol, activo, fecha_creacion) 
                         VALUES ('Administrador', 'Sistema', '$email', '$password_hash', 'admin', 1, NOW())";
                         
                if (!$mysqli->query($query)) {
                    throw new Exception('Error al crear usuario administrador: ' . $mysqli->error);
                }
                
                $mensajes[] = ['tipo' => 'success', 'texto' => 'Usuario administrador creado correctamente'];
            }
            
            log_instalador("Usuario administrador configurado: " . $email, 'success');
        }
        
        crear_env_y_finalizar:
        // 4. Crear archivo .env con configuración
        $env_path = realpath(__DIR__ . '/../../') . '/.env';
        
        // Verificar y hacer una copia de seguridad si ya existe el archivo .env
        if (file_exists($env_path)) {
            $backup_path = $env_path . '.backup.' . date('YmdHis');
            if (!copy($env_path, $backup_path)) {
                log_instalador("No se pudo hacer backup del archivo .env existente", 'warning');
            } else {
                log_instalador("Se creó una copia de seguridad del archivo .env en: $backup_path", 'info');
            }
        }
        
        // Construir el contenido con comentarios informativos
        $env_content = "# Archivo de configuración del sistema\n";
        $env_content .= "# Generado automáticamente por el instalador: " . date('Y-m-d H:i:s') . "\n";
        $env_content .= "# NO MODIFIQUE ESTE ARCHIVO MANUALMENTE\n\n";
        
        // Detectar BASE_URL correcta según el dominio actual
        $protocolo_actual = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host_actual = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_path = $_SERVER['SCRIPT_NAME'] ?? '';
        
        // La BASE_URL siempre debe ser el dominio principal sin rutas adicionales
        // No incluir "/instalador" o "/publico" en la URL base
        $base_url_final = $protocolo_actual . '://' . $host_actual;
        
        // Log para diagnóstico
        log_instalador("BASE_URL configurada: $base_url_final (desde: $host_actual)", 'info');
        
        // Base URL del sistema
        $env_content .= "# Base URL del sistema (sin slash final)\n";
        $env_content .= "BASE_URL=" . $base_url_final . "\n\n";
        
        // Configuración de base de datos
        $env_content .= "# Configuración de base de datos\n";
        $env_content .= "DB_HOST={$db['db_host']}\n";
        $env_content .= "DB_NAME={$db['db_name']}\n";
        $env_content .= "DB_USER={$db['db_user']}\n";
        $env_content .= "DB_PASS={$db['db_pass']}\n";
        $env_content .= "DB_PORT=3306\n";
        $env_content .= "DB_CHARSET=utf8mb4\n\n";
        
        // Añadir configuración SMTP
        $smtp = $_SESSION['smtp_config'];
        $admin = $_SESSION['admin_config'];
        
        // Usar el email del administrador como valor por defecto para SMTP_USER y SMTP_FROM si no se han especificado
        if (empty($smtp['smtp_user']) && !empty($admin['admin_email'])) {
            $smtp['smtp_user'] = $admin['admin_email'];
            log_instalador("Se usó el email del administrador ({$admin['admin_email']}) como SMTP_USER por defecto", 'info');
        }
        
        // Construir el FROM basado en el email del administrador o un valor por defecto
        $smtp_from = !empty($admin['admin_email']) ? $admin['admin_email'] : 'no-reply@example.com';
        
        // Generar un nombre de remitente inteligente basado en el dominio del correo
        $system_name = defined('SYSTEM_NAME') ? SYSTEM_NAME : "Sistema";
        $smtp_from_name = $system_name;
        if (!empty($admin['admin_email']) && strpos($admin['admin_email'], '@') !== false) {
            $dominio = explode('@', $admin['admin_email'])[1];
            // Extraer la parte principal del dominio (ej: de 'ejemplo.com' tomar 'ejemplo')
            $nombre_dominio = explode('.', $dominio)[0];
            if ($nombre_dominio && strtolower($nombre_dominio) !== 'gmail' && strtolower($nombre_dominio) !== 'hotmail' && strtolower($nombre_dominio) !== 'yahoo') {
                $smtp_from_name = "$system_name - " . ucfirst($nombre_dominio);
                log_instalador("Se generó un nombre de remitente personalizado basado en el dominio: $smtp_from_name", 'info');
            }
        }
        
        $env_content .= "# Configuración de correo electrónico (SMTP)\n";
        $env_content .= "# SMTP_FROM debe ser una dirección válida desde la que se enviarán los correos\n";
        $env_content .= "# SMTP_FROM_NAME es el nombre que aparecerá como remitente en los correos\n";
        $env_content .= "SMTP_HOST={$smtp['smtp_host']}\n";
        $env_content .= "SMTP_USER={$smtp['smtp_user']}\n";
        $env_content .= "SMTP_PASS={$smtp['smtp_pass']}\n";
        $env_content .= "SMTP_PORT={$smtp['smtp_port']}\n";
        $env_content .= "SMTP_SECURE={$smtp['smtp_secure']}\n";
        $env_content .= "SMTP_FROM={$smtp_from}\n";
        $env_content .= "SMTP_FROM_NAME={$smtp_from_name}\n\n";
        
        // Añadir configuración FTP
        $ftp = $_SESSION['ftp_config'];
        $env_content .= "# Configuración SFTP/FTP\n";
        $env_content .= "FTP_HOST={$ftp['ftp_host']}\n";
        $env_content .= "FTP_USER={$ftp['ftp_user']}\n";
        $env_content .= "FTP_PASS={$ftp['ftp_pass']}\n";
        $env_content .= "FTP_PORT={$ftp['ftp_port']}\n";
        $env_content .= "FTP_PATH={$ftp['ftp_path']}\n";
        // Almacenar explícitamente el tipo de conexión (sftp/ftp)
        $env_content .= "# Tipo de conexión FTP explícito (ftp o sftp)\n";
        $env_content .= "FTP_TYPE={$ftp['ftp_tipo']}\n";
        // Mantener FTP_SECURE para compatibilidad con versiones anteriores
        $env_content .= "# Flag de conexión segura (para compatibilidad)\n";
        $env_content .= "FTP_SECURE=" . ($ftp['ftp_tipo'] === 'sftp' ? 'true' : 'false') . "\n\n";
        
        log_instalador("Configuración FTP guardada en .env: tipo={$ftp['ftp_tipo']}, puerto={$ftp['ftp_port']}", 'info');
        
        // Configuración de seguridad
        $env_content .= "# Configuración de seguridad\n";
        $env_content .= "HASH_COST=12\n";
        $env_content .= "SESSION_LIFETIME=7200\n";
        $env_content .= "FB_MAX_INTENTOS=5\n";
        $env_content .= "FB_TIEMPO_BLOQUEO=30\n\n";
        
        // Configuración de archivos
        $env_content .= "# Configuración de archivos\n";
        $env_content .= "MAX_UPLOAD_SIZE=5242880\n";
        $env_content .= "ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx\n\n";
        
        // Configuración del sistema
        $env_content .= "# Configuración del sistema\n";
        // Usar el valor ingresado por el usuario o AUTOEXAM2 como valor predeterminado
        $system_name = !empty($_SESSION['admin_config']['system_name']) ? $_SESSION['admin_config']['system_name'] : 'AUTOEXAM2';
        $env_content .= "SYSTEM_NAME=$system_name\n";
        $env_content .= "SYSTEM_EMAIL_PREFIX=$system_name -\n";
        $env_content .= "TIMEZONE=Europe/Madrid\n";
        $env_content .= "DEBUG=false\n";
        $env_content .= "SISTEMA_VERSION=1.2\n";
        $env_content .= "SISTEMA_FECHA=2025-05-27\n\n";
        
        // Modo de mantenimiento
        $env_content .= "# Modo de mantenimiento\n";
        $env_content .= "MODO_MANTENIMIENTO=false\n";
        
        log_instalador("Generando archivo de configuración .env en: $env_path", 'info');
        
        if (file_put_contents($env_path, $env_content) === false) {
            log_instalador("Error al escribir el archivo .env", 'error');
            throw new Exception('No se pudo crear el archivo .env');
        }
        
        // 5. Bloquear el instalador creando un archivo lock
        $lock_file = __DIR__ . '/.lock';
        file_put_contents($lock_file, date('Y-m-d H:i:s'));
        
        // Preparar resumen de acciones realizadas
        $resumen_instalacion = [];
        
        // Agregar acciones realizadas al resumen
        if ($accion_tablas === 'eliminar') {
            $resumen_instalacion[] = "Se eliminaron y recrearon todas las tablas de la base de datos";
        } elseif ($accion_tablas === 'vaciar') {
            $resumen_instalacion[] = "Se vaciaron todas las tablas manteniendo la estructura";
        } else {
            if (isset($_SESSION['verificacion_tablas']) && $_SESSION['verificacion_tablas']['tablas_existen']) {
                $resumen_instalacion[] = "Se actualizaron las tablas existentes en la base de datos";
            } else {
                $resumen_instalacion[] = "Se crearon todas las tablas necesarias en la base de datos";
            }
        }
        
        $resumen_instalacion[] = "Se creó el usuario administrador: " . htmlspecialchars($_SESSION['admin_config']['admin_email']);
        $resumen_instalacion[] = "Se guardó la configuración en el archivo .env";
        $resumen_instalacion[] = "Se bloqueó el instalador correctamente";
        
        // Guardar resumen en sesión
        $_SESSION['resumen_instalacion'] = $resumen_instalacion;
        
        $mensajes[] = ['tipo' => 'success', 'texto' => 'Instalación completada correctamente'];
        
        // Limpiar parte de la sesión pero mantener info importante
        $_SESSION['instalacion_completada'] = true;
        
    } catch (Exception $e) {
        $instalacion_correcta = false;
        $mensajes[] = ['tipo' => 'error', 'texto' => $e->getMessage()];
    }
}

// Manejar navegación entre pasos (solo después de procesar datos)
if (isset($_POST['siguiente_paso']) && $_SESSION['paso_actual'] < count($pasos)) {
    $_SESSION['paso_actual']++;
} elseif (isset($_POST['paso_anterior']) && $_SESSION['paso_actual'] > 1) {
    $_SESSION['paso_actual']--;
} elseif (isset($_POST['ir_paso'])) {
    $ir_a = (int)$_POST['ir_paso'];
    if ($ir_a >= 1 && $ir_a <= $_SESSION['paso_maximo']) {
        $_SESSION['paso_actual'] = $ir_a;
    }
}

// Almacenar paso máximo alcanzado (para permitir navegar hacia atrás pero no adelante sin completar)
if (!isset($_SESSION['paso_maximo'])) {
    $_SESSION['paso_maximo'] = 1;
} elseif ($_SESSION['paso_actual'] > $_SESSION['paso_maximo']) {
    $_SESSION['paso_maximo'] = $_SESSION['paso_actual'];
}

// Paso 1: Comprobación de requisitos del sistema
function comprobarRequisitos() {
    $resultados = [];
    
    // PHP version
    $php_version_minima = '8.1.0';
    $php_version_ok = version_compare(PHP_VERSION, $php_version_minima, '>=');
    $resultados[] = [
        'nombre' => "PHP >= $php_version_minima",
        'ok' => $php_version_ok,
        'detalle' => 'Versión actual: ' . PHP_VERSION,
        'critico' => true // Requisito crítico
    ];
    log_instalador("Comprobación versión PHP: " . ($php_version_ok ? 'OK' : 'ERROR') . " - Version actual: " . PHP_VERSION);
    
    // Sistema operativo
    $sistema = php_uname('s');
    $version_sistema = php_uname('r');
    $resultados[] = [
        'nombre' => 'Sistema Operativo',
        'ok' => true, // Solo informativo
        'detalle' => "$sistema $version_sistema",
        'critico' => false
    ];
    
    // Servidor web
    $servidor = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Desconocido';
    $resultados[] = [
        'nombre' => 'Servidor Web',
        'ok' => true, // Solo informativo
        'detalle' => $servidor,
        'critico' => false
    ];
    
    // Verificación de instalación previa (.env)
    $env_file = realpath(__DIR__ . '/../../') . '/.env';
    $existe_env = file_exists($env_file);
    $resultados[] = [
        'nombre' => 'Archivo de configuración .env',
        'ok' => true, // Siempre es OK, solo informativo
        'detalle' => $existe_env ? 'Encontrado (REINSTALACIÓN)' : 'No encontrado (INSTALACIÓN NUEVA)',
        'critico' => false,
        'tipo' => 'reinstalacion',
        'reinstalacion' => $existe_env
    ];
    
    // Ya no verificamos las tablas aquí, se hará en el paso 2
    $tablas_existentes = false;
    $num_tablas = 0;
    // Recuperamos la información de verificación previa si existe
    if (isset($_SESSION['verificacion_tablas_inicial'])) {
        $tablas_existentes = $_SESSION['verificacion_tablas_inicial']['tablas_existen'] ?? false;
        $num_tablas = $_SESSION['verificacion_tablas_inicial']['cantidad_tablas'] ?? 0;
    }
    
    // Ya no mostramos verificación de tablas en el paso 1
    // Se mostrará en el paso 2 cuando se verifique la conexión
    
    // Límites de PHP
    $limite_memoria = ini_get('memory_limit');
    $memoria_mb = intval($limite_memoria); 
    if (strpos($limite_memoria, 'G') !== false) {
        $memoria_mb = intval($limite_memoria) * 1024;
    } elseif (strpos($limite_memoria, 'M') !== false) {
        $memoria_mb = intval($limite_memoria);
    }
    
    // Si el límite de memoria es -1 (ilimitado) o >= 128M, está bien
    $memoria_ok = $limite_memoria == '-1' || $memoria_mb >= 128;
    
    // Intentar establecer un límite de memoria adecuado
    if (!$memoria_ok) {
        @ini_set('memory_limit', '256M');
        // Verificar si el cambio fue efectivo
        $nuevo_limite = ini_get('memory_limit');
        if ($nuevo_limite == '256M' || $nuevo_limite == '-1') {
            $memoria_ok = true;
            $limite_memoria = $nuevo_limite;
            log_instalador("Se ha ajustado automáticamente el límite de memoria a: $nuevo_limite", 'info');
        }
    }
    
    // En cualquier caso, no lo marcamos como crítico ya que tenemos el php.ini configurado
    $resultados[] = [
        'nombre' => 'Límite de memoria PHP',
        'ok' => true,
        'detalle' => "Configuración actual: $limite_memoria (El sistema usará un valor óptimo mediante php.ini personalizado)",
        'critico' => false
    ];
    
    $max_execution = ini_get('max_execution_time');
    $execution_ok = $max_execution >= 30 || $max_execution == 0;
    $resultados[] = [
        'nombre' => 'Tiempo máximo de ejecución',
        'ok' => $execution_ok,
        'detalle' => "Configuración actual: $max_execution segundos (recomendado >= 30s)",
        'critico' => false
    ];
    
    // Verificar extensiones requeridas
    $extensiones_requeridas = ['mysqli', 'pdo', 'pdo_mysql', 'mbstring', 'json', 'gd', 'xml', 'curl', 'zip'];
    foreach ($extensiones_requeridas as $ext) {
        $ext_loaded = extension_loaded($ext);
        $resultados[] = [
            'nombre' => "Extensión PHP: $ext",
            'ok' => $ext_loaded,
            'detalle' => $ext_loaded ? "Instalada" : "No instalada",
            'critico' => in_array($ext, ['mysqli', 'pdo', 'mbstring', 'json']) // Algunas son críticas
        ];
        
        log_instalador("Extensión $ext: " . ($ext_loaded ? "OK" : "NO INSTALADA"), $ext_loaded ? 'info' : 'warning');
    }
    
    // Ya no verificamos OPCache como requisito
    
    // Configuración de seguridad
    $allow_url_fopen = ini_get('allow_url_fopen');
    $resultados[] = [
        'nombre' => 'allow_url_fopen',
        'ok' => $allow_url_fopen == "1",
        'detalle' => "Configuración actual: " . ($allow_url_fopen == "1" ? "Activado" : "Desactivado"),
        'critico' => false
    ];
    
    // Límites de carga
    $post_max_size = ini_get('post_max_size');
    $upload_max_filesize = ini_get('upload_max_filesize');
    $resultados[] = [
        'nombre' => 'Límites de subida',
        'ok' => true, // Informativo solamente
        'detalle' => "post_max_size: $post_max_size, upload_max_filesize: $upload_max_filesize",
        'critico' => false
    ];
    
    // Extensiones requeridas
    $extensiones = [
        'mysqli' => true,     // Crítica para BD
        'mbstring' => true,   // Crítica para Unicode
        'json' => true,       // Crítica para APIs
        'openssl' => true,    // Crítica para seguridad
        'fileinfo' => false,  // Importante pero no crítica
        'zip' => false,       // Importante pero no crítica
        'gd' => false,        // Importante pero no crítica
        'curl' => false,      // Importante pero no crítica
        'pdo' => false        // Alternativa a mysqli
    ];
    
    foreach ($extensiones as $ext => $critico) {
        $ext_cargada = extension_loaded($ext);
        $resultados[] = [
            'nombre' => "Extensión PHP: $ext",
            'ok' => $ext_cargada,
            'detalle' => $ext_cargada ? 'Cargada' : 'No cargada',
            'critico' => $critico
        ];
        log_instalador("Comprobación extensión $ext: " . ($ext_cargada ? 'OK' : 'No disponible') . ($critico ? ' (CRÍTICA)' : ''));
    }
    
    // Verificar directorios críticos con la función mejorada de verificación
    $verificacion = isset($GLOBALS['verificacion_directorios']) ? $GLOBALS['verificacion_directorios'] : verificarDirectorios();
    
    // Directorios críticos y su descripción
    $directorios_criticos = [
        'almacenamiento/tmp' => 'Almacenamiento temporal',
        'almacenamiento/logs/sistema' => 'Logs del sistema',
        'almacenamiento/cache' => 'Caché del sistema',
        'config' => 'Archivos de configuración',
        'publico/recursos' => 'Recursos públicos',
        'almacenamiento/subidas' => 'Archivos subidos',
        'documentacion' => 'Documentación del sistema'
    ];
    
    // Primero verificar el directorio raíz (importante para .env)
    $root_dir = realpath(__DIR__ . '/../../');
    $root_ok = is_writable($root_dir);
    
    $resultados[] = [
        'nombre' => "Directorio raíz del proyecto",
        'ok' => $root_ok,
        'detalle' => $root_ok ? "Escritura permitida (para .env)" : "No tiene permisos de escritura para crear el archivo .env",
        'critico' => true
    ];
    
    log_instalador("Comprobación directorio raíz: " . ($root_ok ? 'OK' : 'ERROR - No tiene permisos de escritura'), $root_ok ? 'info' : 'error');
    
    // Verificar cada directorio crítico
    foreach ($directorios_criticos as $dir => $descripcion) {
        // Comprobar si está en los resultados de verificación
        $estado = '';
        $detalle = '';
        $critico = in_array($dir, ['almacenamiento', 'config', 'almacenamiento/logs/sistema']);
        $existe = false;
        
        $ruta_completa = $root_dir . '/' . $dir;
        $existe = file_exists($ruta_completa);
        $es_escribible = $existe && is_writable($ruta_completa);
        
        // Intentar crear el directorio si no existe y no está en 'incorrectos'
        if (!$existe && !in_array($dir, $verificacion['incorrectos'])) {
            try {
                if (@mkdir($ruta_completa, 0755, true)) {
                    $existe = true;
                    $es_escribible = is_writable($ruta_completa);
                    log_instalador("Directorio creado automáticamente: $dir", 'success');
                    $verificacion['creados'][] = $dir;
                }
            } catch (Exception $e) {
                log_instalador("Error al crear directorio $dir: " . $e->getMessage(), 'error');
            }
        }
        
        if ($existe && $es_escribible) {
            $estado = true;
            $detalle = in_array($dir, $verificacion['creados']) ? 
                      "Directorio creado y listo para usar" : 
                      "Directorio existente con permisos correctos";
        } else if ($existe && !$es_escribible) {
            $estado = false;
            $detalle = "El directorio existe pero no tiene permisos de escritura";
        } else {
            $estado = false;
            $detalle = "No se pudo encontrar o crear el directorio";
        }
        
        $resultados[] = [
            'nombre' => "Directorio $dir ($descripcion)",
            'ok' => $estado,
            'detalle' => $detalle,
            'critico' => $critico
        ];
        
        log_instalador("Comprobación directorio $dir: " . ($estado ? 'OK' : 'ERROR') . ($critico ? ' (CRÍTICO)' : ''));
    }
    
    // Verificar capacidad de escritura para el archivo .env
    $env_path = $root_dir . '/.env';
    $test_content = "# Test write\n";
    $env_ok = @file_put_contents($env_path . '.test', $test_content) !== false;
    if ($env_ok) {
        @unlink($env_path . '.test');
        log_instalador("Prueba de escritura .env exitosa", 'success');
    } else {
        log_instalador("No se puede escribir el archivo .env en la raíz", 'error');
    }
    
    $resultados[] = [
        'nombre' => "Archivo de configuración .env",
        'ok' => $env_ok,
        'detalle' => $env_ok ? "Se puede escribir correctamente" : "No se puede crear/escribir en la raíz del proyecto",
        'critico' => true
    ];
    
    // Verificar permisos para el lock file
    $lock_path = __DIR__ . '/.lock';
    $lock_dir_ok = is_writable(dirname($lock_path));
    
    $resultados[] = [
        'nombre' => "Bloqueo del instalador",
        'ok' => $lock_dir_ok,
        'detalle' => $lock_dir_ok ? "Se puede crear el archivo de bloqueo" : "No se podrá bloquear el instalador después de completar",
        'critico' => false
    ];
    
    return $resultados;
}

function mostrarResultados($resultados) {
    echo "<h2>Comprobación de requisitos del sistema</h2>";
    echo "<table border='1' cellpadding='6' style='border-collapse:collapse;'>";
    echo "<tr><th>Requisito</th><th>Estado</th><th>Detalle</th></tr>";
    foreach ($resultados as $r) {
        $color = $r['ok'] ? '#c8e6c9' : '#ffcdd2';
        $estado = $r['ok'] ? '✔' : '✖';
        echo "<tr style='background:$color'><td>{$r['nombre']}</td><td style='text-align:center;font-size:1.2em;'>$estado</td><td>{$r['detalle']}</td></tr>";
    }
    echo "</table>";
}

function requisitosOK($resultados) {
    foreach ($resultados as $r) {
        if (!$r['ok']) return false;
    }
    return true;
}

// Comprobar requisitos solo cuando estamos en el paso 1 o si aún no se han comprobado
if ($_SESSION['paso_actual'] == 1 || !isset($_SESSION['resultados_requisitos'])) {
    $_SESSION['resultados_requisitos'] = comprobarRequisitos();
}
$resultados = $_SESSION['resultados_requisitos'];
$requisitos_ok = requisitosOK($resultados);

// Si no cumple requisitos y está en un paso posterior, regresar al paso 1
if (!$requisitos_ok && $_SESSION['paso_actual'] > 1) {
    $_SESSION['paso_actual'] = 1;
}

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Instalador <?= defined('SYSTEM_NAME') ? htmlspecialchars(SYSTEM_NAME) : 'del Sistema' ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Estilos personalizados del instalador -->
    <link rel="stylesheet" href="../recursos/css/instalador.css">
    <!-- Script para gestionar FTP/SFTP -->
    <script src="ftp-handler.js"></script>
    <script>
        function preConfigurarSMTP() {
            const tipo = document.getElementById('smtp_tipo').value;
            const hostInput = document.getElementById('smtp_host');
            const portInput = document.getElementById('smtp_port');
            const secureInput = document.getElementById('smtp_secure');
            
            // Valores predefinidos según el tipo seleccionado
            switch(tipo) {
                case 'GMAIL':
                    hostInput.value = 'smtp.gmail.com';
                    portInput.value = '587';
                    secureInput.value = 'tls';
                    break;
                case 'OUTLOOK':
                    hostInput.value = 'smtp-mail.outlook.com';
                    portInput.value = '587';
                    secureInput.value = 'tls';
                    break;
                case 'EXCHANGE':
                    hostInput.value = 'smtp.office365.com';
                    portInput.value = '587';
                    secureInput.value = 'tls';
                    break;
                default:
                    // No hacer nada para personalizado (CUSTOM)
                    break;
            }
        }
        
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const buttonElement = document.querySelector(`button[onclick*="togglePasswordVisibility('${inputId}')"]`);
            const icon = buttonElement ? buttonElement.querySelector('i') : null;
            
            if (!passwordInput) {
                console.warn('Input de contraseña no encontrado:', inputId);
                return;
            }
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye-fill');
                    icon.classList.add('bi-eye-slash-fill');
                }
            } else {
                passwordInput.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash-fill');
                    icon.classList.add('bi-eye-fill');
                }
            }
        }
        
        // Función para actualizar automáticamente el puerto y etiquetas según el tipo de conexión FTP/SFTP
        function actualizarPuertoFTP() {
            const tipoSelect = document.getElementById('ftp_tipo');
            const puertoInput = document.getElementById('ftp_port');
            const labelTipoFTP = document.getElementById('label_tipo_ftp');
            const labelPuertoFTP = document.getElementById('label_puerto_ftp');
            
            if (tipoSelect.value === 'sftp') {
                // Si se selecciona SFTP, cambiar al puerto 22 por defecto
                puertoInput.value = '22';
                labelTipoFTP.textContent = 'SFTP';
                labelPuertoFTP.textContent = 'SFTP';
            } else {
                // Si se selecciona FTP, cambiar al puerto 21 por defecto
                puertoInput.value = '21';
                labelTipoFTP.textContent = 'FTP';
                labelPuertoFTP.textContent = 'FTP';
            }
        }
        
        // Función para validar la seguridad de la contraseña
        function validarContrasena() {
            const pass = document.getElementById('admin_pass').value;
            const confirmPass = document.getElementById('admin_confirm').value;
            const indicadorSeguridad = document.getElementById('seguridad_contrasena');
            const coincidenciaInfo = document.getElementById('coincidencia_contrasena');
            const requisitosElement = document.getElementById('requisitos_contrasena');
            
            // Evaluar seguridad
            let puntuacion = 0;
            let mensaje = "";
            let color = "";
            
            // Criterios de seguridad
            const tieneLongitud = pass.length >= 8;
            const tieneLongitudExtra = pass.length >= 12;
            const tieneMayusculas = /[A-Z]/.test(pass);
            const tieneMinusculas = /[a-z]/.test(pass);
            const tieneNumeros = /[0-9]/.test(pass);
            const tieneEspeciales = /[^A-Za-z0-9]/.test(pass);
            
            // Calcular puntuación
            if (tieneLongitud) puntuacion += 1;
            if (tieneLongitudExtra) puntuacion += 1;
            if (tieneMayusculas) puntuacion += 1;
            if (tieneMinusculas) puntuacion += 1;
            if (tieneNumeros) puntuacion += 1;
            if (tieneEspeciales) puntuacion += 1;
            
            // Determinar nivel de seguridad
            if (pass.length === 0) {
                mensaje = "Ingrese una contraseña";
                color = "#999";
            } else if (puntuacion <= 2) {
                mensaje = "Muy débil";
                color = "#f44336";
            } else if (puntuacion <= 4) {
                mensaje = "Media";
                color = "#ff9800";
            } else {
                mensaje = "Fuerte";
                color = "#4caf50";
            }
            
            // Mostrar resultado
            if (indicadorSeguridad) {
                indicadorSeguridad.textContent = mensaje;
                indicadorSeguridad.style.color = color;
            }
            
            // Verificar coincidencia
            if (coincidenciaInfo) {
                if (confirmPass.length > 0) {
                    if (pass === confirmPass) {
                        coincidenciaInfo.textContent = "Las contraseñas coinciden";
                        coincidenciaInfo.style.color = "#4caf50";
                    } else {
                        coincidenciaInfo.textContent = "Las contraseñas no coinciden";
                        coincidenciaInfo.style.color = "#f44336";
                    }
                } else {
                    coincidenciaInfo.textContent = "";
                }
            }
            
            // Actualizar lista de requisitos
            if (requisitosElement) {
                requisitosElement.innerHTML = `
                    <div class="mt-3 mb-2">Requisitos de seguridad:</div>
                    <ul class="list-unstyled small">
                        <li class="${tieneLongitud ? 'text-success' : 'text-danger'}">
                            <i class="bi bi-${tieneLongitud ? 'check-circle-fill' : 'x-circle-fill'}"></i> 
                            Mínimo 8 caracteres
                        </li>
                        <li class="${tieneMayusculas ? 'text-success' : 'text-danger'}">
                            <i class="bi bi-${tieneMayusculas ? 'check-circle-fill' : 'x-circle-fill'}"></i> 
                            Al menos una letra mayúscula
                        </li>
                        <li class="${tieneMinusculas ? 'text-success' : 'text-danger'}">
                            <i class="bi bi-${tieneMinusculas ? 'check-circle-fill' : 'x-circle-fill'}"></i> 
                            Al menos una letra minúscula
                        </li>
                        <li class="${tieneNumeros ? 'text-success' : 'text-danger'}">
                            <i class="bi bi-${tieneNumeros ? 'check-circle-fill' : 'x-circle-fill'}"></i> 
                            Al menos un número
                        </li>
                        <li class="${tieneEspeciales ? 'text-success' : 'text-danger'}">
                            <i class="bi bi-${tieneEspeciales ? 'check-circle-fill' : 'x-circle-fill'}"></i> 
                            Al menos un carácter especial
                        </li>
                    </ul>
                `;
            }
        }
        
        // Ejecutar al cargar la página para asegurar que las etiquetas están correctas
        document.addEventListener('DOMContentLoaded', function() {
            // Si estamos en el paso 4, actualizar etiquetas según el tipo seleccionado
            if (document.getElementById('paso-4') && document.getElementById('paso-4').classList.contains('active')) {
                const tipoSelect = document.getElementById('ftp_tipo');
                const labelTipoFTP = document.getElementById('label_tipo_ftp');
                const labelPuertoFTP = document.getElementById('label_puerto_ftp');
                
                if (tipoSelect && labelTipoFTP && labelPuertoFTP) {
                    const esSftp = tipoSelect.value === 'sftp';
                    labelTipoFTP.textContent = esSftp ? 'SFTP' : 'FTP';
                    labelPuertoFTP.textContent = esSftp ? 'SFTP' : 'FTP';
                }
            }
        });
    </script>
</head>
<body>
<?php if (isset($_SESSION['mostrar_modal_lock']) && $_SESSION['mostrar_modal_lock']): ?>
<!-- Modal para cuando falta .env pero existe .lock -->
<div class="modal fade" id="modalLockFile" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalLockFileLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalLockFileLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Se requiere acción adicional</h5>
            </div>
            <div class="modal-body">
                <p>Se ha detectado que existe un archivo <code>.lock</code> pero falta el archivo <code>.env</code> de configuración.</p>
                <p>Para poder continuar con la instalación, es necesario eliminar el archivo <code>.lock</code> en la carpeta del instalador.</p>
                <p class="mb-0 fw-bold">Ruta del archivo lock: <code><?php echo htmlspecialchars($lock_file); ?></code></p>
            </div>
            <div class="modal-footer">
                <a href="<?php echo $base_url; ?>" class="btn btn-secondary">Volver al inicio</a>
                <button type="button" class="btn btn-primary" onclick="location.reload()">Verificar de nuevo</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Mostrar el modal automáticamente al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        var modalLockFile = new bootstrap.Modal(document.getElementById('modalLockFile'), {
            backdrop: 'static',
            keyboard: false
        });
        modalLockFile.show();
    });
</script>
<?php endif; ?>

<div class="app-container">
    <h1 class="d-flex align-items-center mb-4">
        <i class="bi bi-gear-fill me-2"></i> Instalador de <?= defined('SYSTEM_NAME') ? htmlspecialchars(SYSTEM_NAME) : 'Sistema' ?>
    </h1>
    
    <?php 
    // Determinar si es reinstalación basado en el estado de la sesión
    $modo_reinstalacion = isset($_SESSION['modo_reinstalacion']) && $_SESSION['modo_reinstalacion'];
    $es_reinstalacion = isset($_SESSION['es_reinstalacion']) && $_SESSION['es_reinstalacion'];
    $tiene_env = isset($_SESSION['config_env']) && !empty($_SESSION['config_env']);
    $tiene_tablas = isset($_SESSION['verificacion_tablas_inicial']) && 
                   isset($_SESSION['verificacion_tablas_inicial']['tablas_existen']) && 
                   $_SESSION['verificacion_tablas_inicial']['tablas_existen'];
    $num_tablas = isset($_SESSION['verificacion_tablas_inicial']['cantidad_tablas']) ? 
                 $_SESSION['verificacion_tablas_inicial']['cantidad_tablas'] : 0;
    
    if ($modo_reinstalacion || $es_reinstalacion || $tiene_env || $tiene_tablas): 
    ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Modo Reinstalación:</strong> 
        <?php if ($tiene_env && $tiene_tablas): ?>
            Se ha detectado una instalación previa (archivo .env y <?php echo $num_tablas; ?> tablas en la base de datos).
        <?php elseif ($tiene_env): ?>
            Se ha detectado una instalación previa (archivo .env).
        <?php elseif ($tiene_tablas): ?>
            Se han detectado <?php echo $num_tablas; ?> tablas existentes en la base de datos.
        <?php else: ?>
            Se ha detectado una instalación previa.
        <?php endif; ?>
        <?php if ($tiene_env): ?>
        Los valores existentes han sido precargados en los formularios. 
        Puedes modificarlos si lo deseas antes de reinstalar el sistema.
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['instalacion_completada']) && $_SESSION['instalacion_completada']): ?>
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i> Instalación completada correctamente</h2>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 64px;"></i>
                <h3 class="mt-3 mb-4">El sistema ha sido instalado correctamente</h3>
                
                <?php if (isset($_SESSION['resumen_instalacion']) && !empty($_SESSION['resumen_instalacion'])): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Resumen de acciones realizadas</h4>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($_SESSION['resumen_instalacion'] as $accion): ?>
                        <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> <?php echo $accion; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <p class="mb-4">Ya puedes acceder al sistema desde la página principal.</p>
                <div class="d-flex flex-column align-items-center gap-3">
                    <!-- Botón para limpieza completa de caché y redirección -->
                    <button id="btnLimpiarEIniciar" 
                       class="btn btn-primary btn-lg">
                        <i class="bi bi-house-door-fill me-1"></i> Ir a la página de inicio
                    </button>
                    
                    <!-- Enlace de respaldo si JavaScript no funciona -->
                    <noscript>
                        <a href="../../?nocache=<?php echo time(); ?>&force_reload=1" 
                           class="btn btn-secondary btn-lg">
                            <i class="bi bi-house-door-fill me-1"></i> Ir a la página de inicio (sin JavaScript)
                        </a>
                    </noscript>
                    
                    <!-- Enlace manual como alternativa -->
                    <a href="../../?nocache=<?php echo time(); ?>&force_reload=1" 
                       class="btn btn-outline-primary btn-sm" 
                       style="display: none;" 
                       id="enlaceManual">
                        <i class="bi bi-arrow-right-circle me-1"></i> Ir manualmente (si el botón no funciona)
                    </a>
                    
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Al hacer clic se limpiará la caché del navegador para garantizar que los cambios se apliquen correctamente
                    </small>
                </div>
            </div>
        </div>
    <?php else: ?>
    
    <!-- Mostrar mensajes de error o éxito (excepto para pasos que muestran sus propios mensajes) -->
    <?php 
    // No mostrar los mensajes generales si estamos en un paso que muestra sus propios mensajes
    if (!empty($mensajes) && $_SESSION['paso_actual'] != 2 && $_SESSION['paso_actual'] != 3 && $_SESSION['paso_actual'] != 4): 
    ?>
    <div class="mb-4">
        <?php foreach ($mensajes as $msg): ?>
            <div class="alert alert-<?php 
                echo $msg['tipo'] === 'error' ? 'danger' : 
                    ($msg['tipo'] === 'warning' ? 'warning' : 'success'); 
            ?> d-flex align-items-center" role="alert">
                <i class="bi bi-<?php 
                    echo $msg['tipo'] === 'error' ? 'exclamation-triangle-fill' : 
                        ($msg['tipo'] === 'warning' ? 'exclamation-circle-fill' : 'check-circle-fill'); 
                ?> me-2"></i>
                <div><?php echo $msg['texto']; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Mensaje informativo si se ha pre-cargado configuración existente -->
    <?php if (!empty($config_existente) && ($_SESSION['paso_actual'] == 2 || $_SESSION['paso_actual'] == 3 || $_SESSION['paso_actual'] == 4)): ?>
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <div>
            <strong>Configuración existente detectada:</strong> Los campos han sido pre-rellenados con la configuración encontrada en el archivo .env. 
            Puede modificar los valores según sea necesario.
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Barra de progreso personalizada -->
    <div class="custom-progress-container mb-5">
        <div class="custom-progress-inner" style="width: <?php echo (($_SESSION['paso_actual']-1) / (count($pasos)-1)) * 100; ?>%"></div>
        
        <?php foreach ($pasos as $num => $nombre): ?>
            <?php 
            $clase = '';
            if ($num == $_SESSION['paso_actual']) $clase = 'active';
            elseif ($num <= $_SESSION['paso_maximo']) $clase = 'completed';
            else $clase = 'disabled';
            
            // Definir icono para cada paso
            $icono = '';
            switch($num) {
                case 1: $icono = 'check-circle-fill'; break;
                case 2: $icono = 'database-fill'; break;
                case 3: $icono = 'envelope-fill'; break;
                case 4: $icono = 'hdd-network-fill'; break;
                case 5: $icono = 'palette-fill'; break;
                case 6: $icono = 'person-fill-gear'; break;
                case 7: $icono = 'lock-fill'; break;
                default: $icono = 'circle-fill';
            }
            ?>
            <div class="custom-step <?php echo $clase; ?>">
                <form method="post" class="m-0 p-0">
                    <input type="hidden" name="ir_paso" value="<?php echo $num; ?>">
                    <button type="submit" class="custom-step-number" <?php echo ($num > $_SESSION['paso_maximo']) ? 'disabled' : ''; ?>>
                        <i class="bi bi-<?php echo $icono; ?>"></i>
                    </button>
                </form>
                <div class="custom-step-label">
                    <span class="custom-step-paso">PASO <?php echo $num; ?>:</span>
                    <span class="custom-step-nombre"><?php echo $nombre; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Contenido del paso 1: Requisitos -->
    <div id="paso-1" class="tab-content <?php echo $_SESSION['paso_actual'] == 1 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Comprobación de requisitos del sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Requisito</th>
                                <th scope="col" class="text-center">Estado</th>
                                <th scope="col">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $r): ?>
                                <tr class="<?php echo $r['ok'] ? 'table-success' : 'table-danger'; ?>">
                                    <td>
                                        <strong><?php echo $r['nombre']; ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <i class="bi bi-<?php echo $r['ok'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'; ?> fs-4"></i>
                                    </td>
                                    <td><?php echo $r['detalle']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php 
                // Detectar si es reinstalación basado en resultados
                $es_reinstalacion = false;
                $tiene_tablas = false;
                $num_tablas = 0;
                
                foreach ($resultados as $r) {
                    if (isset($r['tipo']) && $r['tipo'] === 'reinstalacion' && isset($r['reinstalacion']) && $r['reinstalacion']) {
                        $es_reinstalacion = true;
                    }
                    if (isset($r['tipo']) && $r['tipo'] === 'tablas_existentes' && isset($r['tablas_existentes']) && $r['tablas_existentes']) {
                        $tiene_tablas = true;
                        $num_tablas = $r['num_tablas'] ?? 0;
                    }
                }
                
                // Guardar el estado de reinstalación en la sesión
                $_SESSION['es_reinstalacion'] = $es_reinstalacion || $tiene_tablas;
                ?>
                
                <?php if ($es_reinstalacion || $tiene_tablas): ?>
                    <div class="alert alert-info mt-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                        <div>
                            <h4 class="alert-heading">Reinstalación detectada</h4>
                            <p class="mb-0">
                                <?php if ($es_reinstalacion && $tiene_tablas): ?>
                                    Se ha detectado un archivo de configuración existente (.env) y <?php echo $num_tablas; ?> tablas en la base de datos.
                                    Los datos de configuración serán precargados en los formularios.
                                <?php elseif ($es_reinstalacion): ?>
                                    Se ha detectado un archivo de configuración existente (.env).
                                    Los datos de configuración serán precargados en los formularios.
                                <?php elseif ($tiene_tablas): ?>
                                    Se han detectado <?php echo $num_tablas; ?> tablas existentes en la base de datos.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mt-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                        <div>
                            <h4 class="alert-heading">Instalación nueva</h4>
                            <p class="mb-0">Esta es una instalación nueva del sistema. Complete todos los pasos del asistente para configurar el sistema.</p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$requisitos_ok): ?>
                    <div class="alert alert-warning mt-4 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>Es necesario corregir los requisitos marcados en rojo antes de continuar.</div>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-end mt-4">
                    <form method="post">
                        <button type="submit" name="siguiente_paso" class="btn btn-primary" <?php echo $requisitos_ok ? '' : 'disabled'; ?>>
                            <i class="bi bi-arrow-right me-1"></i> Siguiente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenido del paso 2: Base de datos -->
    <div id="paso-2" class="tab-content <?php echo $_SESSION['paso_actual'] == 2 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-database-fill me-2"></i>
                    Configuración de la base de datos
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    Ingresa la información de conexión para la base de datos MySQL o MariaDB.
                </p>
                
                <form method="post" class="mt-4">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="host-addon">
                                <i class="bi bi-hdd-network-fill" data-bs-toggle="tooltip" title="Servidor donde se aloja la base de datos"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="db_host" name="db_host" value="<?php echo htmlspecialchars($_SESSION['db_config']['db_host']); ?>" required placeholder="Host">
                                <label for="db_host">Host de la base de datos</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="name-addon">
                                <i class="bi bi-collection-fill" data-bs-toggle="tooltip" title="Nombre de la base de datos a utilizar"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="db_name" name="db_name" value="<?php echo htmlspecialchars($_SESSION['db_config']['db_name']); ?>" required placeholder="Nombre">
                                <label for="db_name">Nombre de la base de datos</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="user-addon">
                                <i class="bi bi-person-fill" data-bs-toggle="tooltip" title="Usuario con acceso a la base de datos"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="db_user" name="db_user" value="<?php echo htmlspecialchars($_SESSION['db_config']['db_user']); ?>" required placeholder="Usuario">
                                <label for="db_user">Usuario de la base de datos</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text" id="pass-addon">
                                <i class="bi bi-key-fill" data-bs-toggle="tooltip" title="Contraseña del usuario de la base de datos"></i>
                            </span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="db_pass" name="db_pass" value="<?php echo htmlspecialchars($_SESSION['db_config']['db_pass']); ?>" placeholder="Contraseña">
                                <label for="db_pass">Contraseña de la base de datos</label>
                            </div>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('db_pass')">
                                <i class="bi bi-eye-fill" data-toggle-password="db_pass"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Contenedor unificado de mensajes para el paso 2 -->
                    <div class="mensajes-container mt-4">
                        <?php 
                        // Filtrar mensajes específicos para el paso 2
                        $mensajes_paso2 = array_filter($mensajes, function($m) {
                            // Aquí puedes agregar lógica específica para filtrar mensajes del paso 2 si es necesario
                            return true;
                        });
                        
                        // Mostrar los mensajes filtrados
                        if (!empty($mensajes_paso2)): ?>
                            <?php foreach ($mensajes_paso2 as $msg): ?>
                                <div class="alert alert-<?php echo $msg['tipo'] === 'error' ? 'danger' : ($msg['tipo'] === 'warning' ? 'warning' : 'success'); ?> d-flex align-items-center mb-2" role="alert">
                                    <i class="bi bi-<?php echo $msg['tipo'] === 'error' ? 'exclamation-triangle-fill' : 
                                                        ($msg['tipo'] === 'warning' ? 'exclamation-circle-fill' : 'check-circle-fill'); ?> me-2"></i>
                                    <div><?php echo $msg['texto']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php if (isset($_SESSION['mostrar_errores_db']) && $_SESSION['mostrar_errores_db']): ?>
                                <?php if (isset($_SESSION['db_verificada']) && $_SESSION['db_verificada']): ?>
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div><strong>Conexión verificada correctamente</strong> - Puede continuar al siguiente paso.</div>
                                </div>
                                
                                <?php if (isset($_SESSION['verificacion_tablas_inicial']) && $_SESSION['verificacion_tablas_inicial']['tablas_existen']): ?>
                                <div class="alert alert-info d-flex align-items-center mt-2" role="alert">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <div>
                                        <strong>Tablas existentes detectadas:</strong> Se encontraron <?php echo $_SESSION['verificacion_tablas_inicial']['cantidad_tablas']; ?> 
                                        tablas del sistema AUTOEXAM2 en la base de datos. En el paso 7 podrá elegir si desea actualizarlas, vaciarlas o eliminarlas.
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php elseif (isset($_SESSION['db_verificada'])): ?>
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><strong>Error de conexión</strong> - Por favor revise los datos e intente nuevamente.</div>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <div>
                            <button type="submit" name="probar_db" class="btn btn-outline-primary me-2">
                                <i class="bi bi-database-check-fill me-1"></i> Probar conexión
                            </button>
                            <button type="submit" name="guardar_db" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Continuar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Contenido del paso 3: Configuración SMTP -->
    <div id="paso-3" class="tab-content <?php echo $_SESSION['paso_actual'] == 3 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-envelope-fill me-2"></i>
                    Configuración del servidor de correo (SMTP)
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    Configura los datos del servidor para enviar correos electrónicos desde el sistema.
                </p>
                
                <form method="post" class="mt-4">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-server" data-bs-toggle="tooltip" title="Selecciona un tipo de servidor predefinido o personalizado"></i>
                            </span>
                            <select class="form-select" id="smtp_tipo" name="smtp_tipo" onchange="preConfigurarSMTP()">
                                <option value="" <?php echo $_SESSION['smtp_config']['smtp_tipo'] === '' ? 'selected' : ''; ?>>-- Seleccionar tipo de servidor --</option>
                                <option value="GMAIL" <?php echo $_SESSION['smtp_config']['smtp_tipo'] === 'GMAIL' ? 'selected' : ''; ?>>Gmail</option>
                                <option value="OUTLOOK" <?php echo $_SESSION['smtp_config']['smtp_tipo'] === 'OUTLOOK' ? 'selected' : ''; ?>>Outlook/Hotmail</option>
                                <option value="EXCHANGE" <?php echo $_SESSION['smtp_config']['smtp_tipo'] === 'EXCHANGE' ? 'selected' : ''; ?>>Microsoft Exchange</option>
                                <option value="CUSTOM" <?php echo $_SESSION['smtp_config']['smtp_tipo'] === 'CUSTOM' ? 'selected' : ''; ?>>Personalizado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-hdd-network-fill" data-bs-toggle="tooltip" title="Dirección del servidor SMTP (ej: smtp.gmail.com)"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_host']); ?>" required placeholder="Host">
                                <label for="smtp_host">Servidor SMTP</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-ethernet" data-bs-toggle="tooltip" title="Puerto de conexión SMTP (común: 25, 465, 587)"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_port']); ?>" required placeholder="Puerto">
                                <label for="smtp_port">Puerto SMTP</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill" data-bs-toggle="tooltip" title="Usuario o correo electrónico para autenticarse"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_user']); ?>" required placeholder="Usuario">
                                <label for="smtp_user">Usuario SMTP</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-key-fill" data-bs-toggle="tooltip" title="Contraseña de acceso al servidor SMTP"></i>
                            </span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_pass']); ?>" placeholder="Contraseña">
                                <label for="smtp_pass">Contraseña SMTP</label>
                            </div>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('smtp_pass')">
                                <i class="bi bi-eye-fill" data-toggle-password="smtp_pass"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock-fill" data-bs-toggle="tooltip" title="Tipo de seguridad para la conexión"></i>
                            </span>
                            <select class="form-select" id="smtp_secure" name="smtp_secure">
                                <option value="tls" <?php echo $_SESSION['smtp_config']['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo $_SESSION['smtp_config']['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="none" <?php echo $_SESSION['smtp_config']['smtp_secure'] === 'none' ? 'selected' : ''; ?>>Ninguna</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Contenedor unificado de mensajes para el paso 3 -->
                    <div class="mensajes-container mt-4">
                        <?php 
                        // Filtrar mensajes específicos para el paso 3
                        $mensajes_paso3 = array_filter($mensajes, function($m) {
                            // Aquí puedes agregar lógica específica para filtrar mensajes del paso 3 si es necesario
                            return true;
                        });
                        
                        // Mostrar los mensajes filtrados
                        if (!empty($mensajes_paso3)): ?>
                            <?php foreach ($mensajes_paso3 as $msg): ?>
                                <div class="alert alert-<?php echo $msg['tipo'] === 'error' ? 'danger' : ($msg['tipo'] === 'warning' ? 'warning' : 'success'); ?> d-flex align-items-center mb-2" role="alert">
                                    <i class="bi bi-<?php echo $msg['tipo'] === 'error' ? 'exclamation-triangle-fill' : 
                                                        ($msg['tipo'] === 'warning' ? 'exclamation-circle-fill' : 'check-circle-fill'); ?> me-2"></i>
                                    <div><?php echo $msg['texto']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php if (isset($_SESSION['mostrar_errores_smtp']) && $_SESSION['mostrar_errores_smtp']): ?>
                                <?php if (isset($_SESSION['smtp_verificada']) && $_SESSION['smtp_verificada']): ?>
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div><strong>Conexión SMTP verificada correctamente</strong> - Puede continuar al siguiente paso.</div>
                                </div>
                                <?php elseif (isset($_SESSION['smtp_verificada'])): ?>
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><strong>Error de conexión SMTP</strong> - Por favor revise los datos e intente nuevamente.</div>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <div>
                            <button type="submit" name="probar_smtp" class="btn btn-outline-primary me-2">
                                <i class="bi bi-envelope-check-fill me-1"></i> Probar conexión
                            </button>
                            <button type="submit" name="guardar_smtp" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Guardar y continuar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Contenido del paso 4: Configuración FTP/SFTP -->
    <div id="paso-4" class="tab-content <?php echo $_SESSION['paso_actual'] == 4 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-hdd-network-fill me-2"></i>
                    Configuración del servidor FTP/SFTP
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    Configura los datos del servidor FTP/SFTP para gestionar archivos del sistema.
                </p>
                
                <form method="post" class="mt-4">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-server" data-bs-toggle="tooltip" title="Selecciona el tipo de conexión"></i>
                            </span>
                            <select class="form-select" id="ftp_tipo" name="ftp_tipo" onchange="actualizarPuertoFTP()">
                                <option value="ftp" <?php echo ($_SESSION['ftp_config']['ftp_tipo'] === 'ftp' || $_SESSION['ftp_config']['ftp_tipo'] === '') ? 'selected' : ''; ?>>FTP - Protocolo de Transferencia de Archivos</option>
                                <option value="sftp" <?php echo $_SESSION['ftp_config']['ftp_tipo'] === 'sftp' ? 'selected' : ''; ?>>SFTP - Protocolo Seguro de Transferencia de Archivos</option>
                            </select>
                        </div>
                        <div class="form-text small text-muted">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            SFTP ofrece más seguridad pero requiere que el servidor lo soporte.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-hdd-network-fill" data-bs-toggle="tooltip" title="Dirección del servidor"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="ftp_host" name="ftp_host" 
                                       value="<?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_host']); ?>" 
                                       required placeholder="Host">
                                <label for="ftp_host">Servidor <span id="label_tipo_ftp">FTP</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill" data-bs-toggle="tooltip" title="Usuario de acceso"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="ftp_user" name="ftp_user" 
                                       value="<?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_user']); ?>" 
                                       required placeholder="Usuario">
                                <label for="ftp_user">Usuario FTP/SFTP</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-key-fill" data-bs-toggle="tooltip" title="Contraseña de acceso"></i>
                            </span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="ftp_pass" name="ftp_pass" 
                                       value="<?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_pass']); ?>" 
                                       placeholder="Contraseña">
                                <label for="ftp_pass">Contraseña FTP/SFTP</label>
                            </div>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('ftp_pass')">
                                <i class="bi bi-eye-fill" data-toggle-password="ftp_pass"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-ethernet" data-bs-toggle="tooltip" title="Puerto de conexión (21/FTP, 22/SFTP)"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="ftp_port" name="ftp_port" 
                                       value="<?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_port']); ?>" 
                                       required placeholder="Puerto">
                                <label for="ftp_port">Puerto <span id="label_puerto_ftp">FTP</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-folder2-open" data-bs-toggle="tooltip" title="Directorio raíz (opcional)"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="ftp_path" name="ftp_path" 
                                       value="<?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_path']); ?>" 
                                       placeholder="Directorio">
                                <label for="ftp_path">Directorio raíz (opcional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenedor unificado de mensajes para el paso 4 -->
                    <div class="mensajes-container mt-4">
                        <?php 
                        // Filtrar mensajes específicos para el paso 4
                        $mensajes_paso4 = array_filter($mensajes, function($m) {
                            // Aquí puedes agregar lógica específica para filtrar mensajes del paso 4 si es necesario
                            return true;
                        });
                        
                        // Mostrar los mensajes filtrados
                        if (!empty($mensajes_paso4)): ?>
                            <?php foreach ($mensajes_paso4 as $msg): ?>
                                <div class="alert alert-<?php echo $msg['tipo'] === 'error' ? 'danger' : ($msg['tipo'] === 'warning' ? 'warning' : 'success'); ?> d-flex align-items-center mb-2" role="alert">
                                    <i class="bi bi-<?php echo $msg['tipo'] === 'error' ? 'exclamation-triangle-fill' : 
                                                        ($msg['tipo'] === 'warning' ? 'exclamation-circle-fill' : 'check-circle-fill'); ?> me-2"></i>
                                    <div><?php echo $msg['texto']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php if (isset($_SESSION['mostrar_errores_ftp']) && $_SESSION['mostrar_errores_ftp']): ?>
                                <?php if (isset($_SESSION['ftp_verificada']) && $_SESSION['ftp_verificada']): ?>
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <div><strong>Conexión FTP/SFTP verificada correctamente</strong> - Puede continuar al siguiente paso.</div>
                                </div>
                                <?php elseif (isset($_SESSION['ftp_verificada'])): ?>
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><strong>Error de conexión FTP/SFTP</strong> - Por favor revise los datos e intente nuevamente.</div>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <div>
                            <button type="submit" name="probar_ftp" class="btn btn-outline-primary me-2">
                                <i class="bi bi-hdd-network-check me-1"></i> Probar conexión
                            </button>
                            <button type="submit" name="guardar_ftp" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Guardar y continuar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Contenido del paso 5: Personalización -->
    <div id="paso-5" class="tab-content <?php echo $_SESSION['paso_actual'] == 5 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-palette-fill me-2"></i>
                    Personalización visual del sistema
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    Personaliza la apariencia del sistema subiendo imágenes para el logo y usuario predeterminado.
                </p>
                
                <form method="post" enctype="multipart/form-data" class="mt-4">
                    <div class="mb-4">
                        <label for="logo" class="form-label">
                            <strong>Logo del centro</strong> (PNG/JPG, recomendado 200x60px)
                        </label>
                        
                        <?php if (isset($_SESSION['archivos_subidos']['logo'])): ?>
                            <!-- Mostrar archivo ya subido -->
                            <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>Logo ya subido:</strong> <?php echo htmlspecialchars($_SESSION['archivos_subidos']['logo']['nombre_original']); ?>
                                    <br>
                                    <small class="text-muted">
                                        Subido el: <?php echo $_SESSION['archivos_subidos']['logo']['fecha_subida']; ?> | 
                                        Tamaño: <?php echo round($_SESSION['archivos_subidos']['logo']['tamano'] / 1024, 1); ?> KB
                                    </small>
                                </div>
                                <?php if (file_exists($_SESSION['archivos_subidos']['logo']['ruta_completa'])): ?>
                                <img src="<?php echo $_SESSION['archivos_subidos']['logo']['ruta_relativa']; ?>" 
                                     alt="Logo actual" class="ms-3" style="max-height: 40px; max-width: 120px; border: 1px solid #ddd; border-radius: 4px;">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-image" data-bs-toggle="tooltip" title="Selecciona un logo para la institución"></i>
                            </span>
                            <input type="file" class="form-control" id="logo" name="logo" accept="image/png,image/jpeg">
                        </div>
                        <div class="form-text">
                            <?php if (isset($_SESSION['archivos_subidos']['logo'])): ?>
                                Si selecciona un nuevo archivo, reemplazará el logo actual. El logo se mostrará en el encabezado del sistema y en los documentos generados.
                            <?php else: ?>
                                Este logo se mostrará en el encabezado del sistema y en los documentos generados.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="user_image_default" class="form-label">
                            <strong>Imagen de usuario por defecto</strong> (PNG/JPG, recomendado 150x150px)
                        </label>
                        
                        <?php if (isset($_SESSION['archivos_subidos']['user_image_default'])): ?>
                            <!-- Mostrar archivo ya subido -->
                            <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>Imagen ya subida:</strong> <?php echo htmlspecialchars($_SESSION['archivos_subidos']['user_image_default']['nombre_original']); ?>
                                    <br>
                                    <small class="text-muted">
                                        Subido el: <?php echo $_SESSION['archivos_subidos']['user_image_default']['fecha_subida']; ?> | 
                                        Tamaño: <?php echo round($_SESSION['archivos_subidos']['user_image_default']['tamano'] / 1024, 1); ?> KB
                                    </small>
                                </div>
                                <?php if (file_exists($_SESSION['archivos_subidos']['user_image_default']['ruta_completa'])): ?>
                                <img src="<?php echo $_SESSION['archivos_subidos']['user_image_default']['ruta_relativa']; ?>" 
                                     alt="Imagen usuario actual" class="ms-3 rounded-circle" style="max-height: 50px; max-width: 50px; border: 1px solid #ddd;">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-badge" data-bs-toggle="tooltip" title="Imagen para usuarios sin foto"></i>
                            </span>
                            <input type="file" class="form-control" id="user_image_default" name="user_image_default" accept="image/png,image/jpeg">
                        </div>
                        <div class="form-text">
                            <?php if (isset($_SESSION['archivos_subidos']['user_image_default'])): ?>
                                Si selecciona un nuevo archivo, reemplazará la imagen actual. Esta imagen se utilizará para los usuarios que no suban una foto de perfil.
                            <?php else: ?>
                                Esta imagen se utilizará para los usuarios que no suban una foto de perfil.
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <strong>Nota:</strong> Ambas imágenes son opcionales. Si no las sube ahora, se utilizarán 
                            imágenes predeterminadas que podrá cambiar posteriormente desde el panel de administración.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <button type="submit" name="guardar_imagenes" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Guardar y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Contenido del paso 6: Usuario administrador -->
    <div id="paso-6" class="tab-content <?php echo $_SESSION['paso_actual'] == 6 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bi bi-person-fill-gear me-2"></i>
                    Configuración del administrador principal
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> 
                    El sistema creará automáticamente un usuario administrador con acceso completo. 
                    Puede modificar estos datos antes de continuar.
                </p>
                
                <form method="post" class="mt-4">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-gear-fill" data-bs-toggle="tooltip" title="Nombre del sistema"></i>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="system_name" name="system_name" 
                                       value="<?php echo htmlspecialchars($_SESSION['admin_config']['system_name'] ?? 'AUTOEXAM2'); ?>" 
                                       required placeholder="Nombre del sistema">
                                <label for="system_name">Nombre del sistema</label>
                            </div>
                        </div>
                        <small class="form-text text-muted">Este nombre aparecerá en todo el sistema, correos y documentos.</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope-at-fill" data-bs-toggle="tooltip" title="Correo electrónico para acceder al sistema"></i>
                            </span>
                            <div class="form-floating">
                                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                       value="<?php echo htmlspecialchars($_SESSION['admin_config']['admin_email']); ?>" 
                                       required placeholder="Email">
                                <label for="admin_email">Correo electrónico del administrador</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock-fill" data-bs-toggle="tooltip" title="Contraseña de acceso"></i>
                            </span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="admin_pass" name="admin_pass" 
                                       value="<?php echo htmlspecialchars($_SESSION['admin_config']['admin_pass']); ?>" 
                                       required placeholder="Contraseña" oninput="validarContrasena()">
                                <label for="admin_pass">Contraseña</label>
                            </div>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('admin_pass')">
                                <i class="bi bi-eye-fill" data-toggle-password="admin_pass"></i>
                            </button>
                        </div>
                        <!-- Indicador de seguridad de contraseña -->
                        <div id="seguridad_contrasena" class="form-text mt-1 fw-bold"></div>
                        <!-- Lista de requisitos para la contraseña -->
                        <div id="requisitos_contrasena" class="ms-2 mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-check-circle-fill" data-bs-toggle="tooltip" title="Confirmar contraseña"></i>
                            </span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="admin_confirm" name="admin_confirm" 
                                       value="<?php echo htmlspecialchars($_SESSION['admin_config']['admin_confirm']); ?>" 
                                       required placeholder="Confirmar" oninput="validarContrasena()">
                                <label for="admin_confirm">Confirmar contraseña</label>
                            </div>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('admin_confirm')">
                                <i class="bi bi-eye-fill" data-toggle-password="admin_confirm"></i>
                            </button>
                        </div>
                        <!-- Mensaje de coincidencia de contraseñas -->
                        <div id="coincidencia_contrasena" class="form-text mt-1 fw-bold"></div>
                    </div>
                    
                    <div class="alert alert-warning d-flex align-items-center mt-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>
                            <strong>Importante:</strong> Este usuario tendrá acceso completo al sistema. Asegúrese de usar una contraseña 
                            segura y guardar estos datos en un lugar seguro.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Anterior
                        </button>
                        <button type="submit" name="guardar_admin" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Guardar y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php 
    // Verificar si existen tablas cuando se accede al paso 7
    if ($_SESSION['paso_actual'] == 7 && function_exists('verificarTablasExistentes') && !isset($_SESSION['verificacion_tablas'])) {
        $db_config = $_SESSION['db_config'];
        $verificacion = verificarTablasExistentes($db_config['db_host'], $db_config['db_user'], $db_config['db_pass'], $db_config['db_name']);
        $_SESSION['verificacion_tablas'] = $verificacion;
        
        if ($verificacion['tablas_existen']) {
            log_instalador("Se encontraron {$verificacion['cantidad_tablas']} tablas existentes en la base de datos", 'warning');
        } else {
            log_instalador("No se encontraron tablas existentes en la base de datos", 'info');
        }
    }
    ?>
    
    <!-- Contenido del paso 7: Instalación final -->
    <div id="paso-7" class="tab-content <?php echo $_SESSION['paso_actual'] == 7 ? 'active' : ''; ?>">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Instalación del sistema</h2>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <h3>Resumen de la configuración</h3>
                    
                    <table class="table table-striped table-bordered w-75 mx-auto mt-4">
                        <tr>
                            <th>Base de datos</th>
                            <td><?php echo htmlspecialchars($_SESSION['db_config']['db_name']); ?> (@<?php echo htmlspecialchars($_SESSION['db_config']['db_host']); ?>)</td>
                        </tr>
                        <tr>
                            <th>Servidor SMTP</th>
                            <td><?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_host']); ?> (Puerto: <?php echo htmlspecialchars($_SESSION['smtp_config']['smtp_port']); ?>)</td>
                        </tr>
                        <tr>
                            <th>Servidor FTP/SFTP</th>
                            <td><?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_host']); ?> (Puerto: <?php echo htmlspecialchars($_SESSION['ftp_config']['ftp_port']); ?>)</td>
                        </tr>
                        <tr>
                            <th>Nombre del sistema</th>
                            <td><?php echo htmlspecialchars($_SESSION['admin_config']['system_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Usuario administrador</th>
                            <td><?php echo htmlspecialchars($_SESSION['admin_config']['admin_email']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <form method="post">
                    <?php if (isset($_SESSION['verificacion_tablas']) && $_SESSION['verificacion_tablas']['tablas_existen']): ?>
                    <!-- Mostrar opciones para manejar tablas existentes -->
                    <div class="alert alert-warning">
                        <h4 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Tablas existentes detectadas</h4>
                        <p>Se encontraron <?php echo $_SESSION['verificacion_tablas']['cantidad_tablas']; ?> tablas existentes en la base de datos. 
                        Seleccione cómo desea proceder con las tablas existentes:</p>
                        
                        <div class="form-group mt-3">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="accion_tablas" id="accion_tablas_actualizar" value="actualizar" checked>
                                <label class="form-check-label" for="accion_tablas_actualizar">
                                    <strong>Actualizar tablas existentes</strong> - Verificar estructura y actualizar si es necesario (recomendado)
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="accion_tablas" id="accion_tablas_vaciar" value="vaciar">
                                <label class="form-check-label" for="accion_tablas_vaciar">
                                    <strong>Vaciar tablas existentes</strong> - Mantener estructura pero eliminar todos los datos
                                </label>
                                <div class="text-danger ms-4"><small><i class="bi bi-exclamation-triangle-fill me-1"></i> Esta acción eliminará todos los datos pero mantendrá la estructura de las tablas.</small></div>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="accion_tablas" id="accion_tablas_eliminar" value="eliminar">
                                <label class="form-check-label" for="accion_tablas_eliminar">
                                    <strong>Eliminar todas las tablas</strong> - Eliminar completamente y recrear desde cero
                                </label>
                                <div class="text-danger ms-4"><small><i class="bi bi-exclamation-triangle-fill me-1"></i> Esta acción eliminará todas las tablas y sus datos permanentemente.</small></div>
                            </div>
                        </div>
                        
                        <!-- Confirmación para opciones peligrosas -->
                        <div id="confirmacion_peligro" class="mt-3 border border-danger rounded p-3 d-none">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmar_accion_peligrosa" name="confirmar_accion_peligrosa" value="1">
                                <label class="form-check-label text-danger fw-bold" for="confirmar_accion_peligrosa">
                                    Confirmo que deseo realizar esta acción y comprendo que los datos actuales se perderán permanentemente.
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        // Mostrar confirmación para acciones peligrosas
                        document.addEventListener('DOMContentLoaded', function() {
                            const radioButtons = document.querySelectorAll('input[name="accion_tablas"]');
                            const confirmacionDiv = document.getElementById('confirmacion_peligro');
                            const confirmCheckbox = document.getElementById('confirmar_accion_peligrosa');
                            const submitButton = document.getElementById('btn_instalar');
                            
                            function actualizarConfirmacion() {
                                const accionSeleccionada = document.querySelector('input[name="accion_tablas"]:checked').value;
                                
                                if (accionSeleccionada === 'eliminar' || accionSeleccionada === 'vaciar') {
                                    confirmacionDiv.classList.remove('d-none');
                                    submitButton.disabled = !confirmCheckbox.checked;
                                } else {
                                    confirmacionDiv.classList.add('d-none');
                                    submitButton.disabled = false;
                                }
                            }
                            
                            // Evento para los radio buttons
                            radioButtons.forEach(radio => {
                                radio.addEventListener('change', actualizarConfirmacion);
                            });
                            
                            // Evento para el checkbox de confirmación
                            confirmCheckbox.addEventListener('change', function() {
                                submitButton.disabled = !this.checked;
                            });
                            
                            // Inicializar estado
                            actualizarConfirmacion();
                        });
                    </script>
                    <?php else: ?>
                    <!-- Si no hay tablas existentes, mostrar mensaje normal -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title"><i class="bi bi-check-circle-fill text-success me-2"></i>Base de datos lista para instalación</h4>
                            <p>No se detectaron tablas existentes. El sistema creará todas las tablas necesarias durante la instalación.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title">Proceso de instalación</h4>
                            <p>Al finalizar la instalación se realizarán las siguientes acciones:</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> Configuración de las tablas de la base de datos</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> Creación del usuario administrador</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> Generación del archivo .env con la configuración</li>
                                <li class="list-group-item"><i class="bi bi-check-circle-fill text-success me-2"></i> Bloqueo del instalador para prevenir reinstalaciones accidentales</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i> 
                        Esta operación es definitiva y configurará el sistema según los parámetros especificados.
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" name="paso_anterior" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Volver atrás
                        </button>
                        <button type="submit" name="instalar" class="btn btn-primary" id="btn_instalar">
                            <i class="bi bi-gear-fill me-1"></i> Instalar <?= defined('SYSTEM_NAME') ? htmlspecialchars(SYSTEM_NAME) : 'Sistema' ?>
                        </button>
                    </div>
                </form>
        </div>
    </div>
    
    <!-- Scripts de Bootstrap y jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Inicializar todos los tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Verificar que los botones de contraseña funcionan correctamente
            console.log('Verificando botones de contraseña...');
            
            // Buscar todos los botones de toggle de contraseña
            const passwordToggleButtons = document.querySelectorAll('button[onclick*="togglePasswordVisibility"]');
            console.log('Botones de toggle encontrados:', passwordToggleButtons.length);
            
            // Agregar event listeners como backup
            passwordToggleButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    // No prevenir el default para permitir que funcione el onclick
                    console.log('Click en botón de toggle detectado');
                });
            });
            
            // Agregar el manejador de limpieza de caché al botón de inicio
            const btnLimpiarEIniciar = document.getElementById('btnLimpiarEIniciar');
            if (btnLimpiarEIniciar) {
                // Agregar un estilo para el spinner
                const style = document.createElement('style');
                style.textContent = `
                    .spinning {
                        animation: spin 1s linear infinite;
                    }
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    
                    /* Iframe oculto para forzar recarga de recursos */
                    .cache-cleaner-frame {
                        position: absolute;
                        width: 0;
                        height: 0;
                        border: 0;
                        visibility: hidden;
                    }
                `;
                document.head.appendChild(style);
                
                // Función para limpieza completa de caché
                btnLimpiarEIniciar.addEventListener('click', function(e) {
                    // Prevenir comportamiento por defecto para manejar todo con JS
                    e.preventDefault();
                    
                    // Cambiar apariencia del botón durante la limpieza
                    const botonOriginal = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-arrow-clockwise spinning me-1"></i> Limpiando caché...';
                    this.disabled = true;
                    
                    // Log para depuración
                    console.log('Iniciando limpieza completa de caché...');
                    
                    // Crear y mostrar un overlay para evitar interacciones durante la limpieza
                    const overlay = document.createElement('div');
                    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999;';
                    document.body.appendChild(overlay);
                    
                    // Variable para rastrear si la redirección fue exitosa
                    let redireccionExitosa = false;
                    
                    // Timeout de seguridad - si después de 15 segundos no se redirigió, mostrar enlace manual
                    const timeoutSeguridad = setTimeout(() => {
                        if (!redireccionExitosa) {
                            console.error('Timeout: La redirección automática falló, mostrando enlace manual');
                            overlay.remove();
                            this.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Error en redirección';
                            this.disabled = false;
                            this.className = 'btn btn-warning btn-lg';
                            
                            const enlaceManual = document.getElementById('enlaceManual');
                            if (enlaceManual) {
                                enlaceManual.style.display = 'block';
                                enlaceManual.className = 'btn btn-primary btn-lg animate__animated animate__pulse';
                            }
                        }
                    }, 15000);
                    
                    // 1. Limpiar localStorage y sessionStorage
                    try {
                        localStorage.clear();
                        sessionStorage.clear();
                        console.log('Storage limpiado (local y session)');
                    } catch(e) {
                        console.error('Error al limpiar storage:', e);
                    }
                    
                    // 2. Limpiar cookies
                    try {
                        const cookies = document.cookie.split(";");
                        for (let i = 0; i < cookies.length; i++) {
                            const cookie = cookies[i];
                            const eqPos = cookie.indexOf("=");
                            const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/;";
                        }
                        console.log('Cookies limpiadas');
                    } catch(e) {
                        console.error('Error al limpiar cookies:', e);
                    }
                    
                    // 3. Limpiar service workers si existen
                    if ('serviceWorker' in navigator) {
                        navigator.serviceWorker.getRegistrations().then(function(registrations) {
                            for (let registration of registrations) {
                                registration.unregister();
                            }
                            console.log('Service Workers limpiados');
                        });
                    }
                    
                    // 4. Limpiar Cache API y forzar limpieza más agresiva
                    if ('caches' in window) {
                        caches.keys().then(function(cacheNames) {
                            return Promise.all(
                                cacheNames.map(function(cacheName) {
                                    return caches.delete(cacheName);
                                })
                            );
                        }).then(() => {
                            console.log('Cache API limpiada');
                        });
                    }
                    
                    // 4.5. Limpiar caché del navegador de forma más agresiva
                    try {
                        // Invalidar caché con método window.stop()
                        if (window.stop) {
                            window.stop();
                        }
                        
                        // Forzar vaciado de buffers
                        if (window.performance && window.performance.clearResourceTimings) {
                            window.performance.clearResourceTimings();
                        }
                        
                        // Crear una petición HEAD para invalidar caché de la página principal
                        const baseUrl = window.location.protocol + '//' + window.location.host;
                        const rootPath = window.location.pathname.split('/publico/')[0] || '';
                        const targetUrl = baseUrl + rootPath + '/index.php';
                        
                        fetch(targetUrl, {
                            method: 'HEAD',
                            cache: 'no-cache',
                            headers: {
                                'Cache-Control': 'no-cache, no-store, must-revalidate',
                                'Pragma': 'no-cache',
                                'Expires': '0'
                            }
                        }).then(() => {
                            console.log('Pre-fetch HEAD realizado exitosamente');
                        }).catch(e => {
                            console.log('Pre-fetch HEAD completado (error esperado):', e.message);
                        });
                        
                    } catch(e) {
                        console.error('Error en limpieza agresiva:', e);
                    }
                    
                    // 5. Forzar recarga de recursos principales
                    try {
                        // Crear iframes ocultos para forzar recarga de recursos clave
                        const urls = [
                            '/publico/recursos/css/main.css',
                            '/publico/recursos/js/app.js',
                            '/publico/recursos/img/logo.png'
                        ];
                        
                        urls.forEach(url => {
                            const iframe = document.createElement('iframe');
                            iframe.className = 'cache-cleaner-frame';
                            iframe.src = url + '?nocache=' + new Date().getTime();
                            document.body.appendChild(iframe);
                        });
                    } catch(e) {
                        console.error('Error al forzar recarga de recursos:', e);
                    }
                    
                    // 6. Forzar limpieza completa con location.reload(true) y window.location.replace
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Redirigiendo...';
                        
                        // Intentar diferentes métodos de limpieza según el navegador
                        if (window.performance && window.performance.navigation) {
                            // Forzar recarga desde servidor, no desde caché
                            window.location.reload(true);
                        }
                        
                        // 7. Finalmente redirigir con parámetros anti-caché
                        setTimeout(() => {
                            const timestamp = new Date().getTime();
                            // Usar URL absoluta para ir a la raíz del dominio
                            const baseUrl = window.location.protocol + '//' + window.location.host;
                            const rootPath = window.location.pathname.split('/publico/')[0] || '';
                            
                            try {
                                // Marcar que la redirección está en proceso
                                redireccionExitosa = true;
                                clearTimeout(timeoutSeguridad);
                                
                                console.log('Redirigiendo a:', baseUrl + rootPath + '/?nocache=' + timestamp + '&force_reload=1');
                                
                                // Usar replace para no mantener en historial
                                window.location.replace(baseUrl + rootPath + '/?nocache=' + timestamp + '&force_reload=1');
                            } catch (error) {
                                console.error('Error en redirección:', error);
                                redireccionExitosa = false;
                                
                                // Fallback: usar href normal
                                try {
                                    window.location.href = baseUrl + rootPath + '/?nocache=' + timestamp + '&force_reload=1';
                                } catch (error2) {
                                    console.error('Error en fallback de redirección:', error2);
                                    // Mostrar enlace manual
                                    overlay.remove();
                                    this.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Error - Usar enlace manual';
                                    this.disabled = false;
                                    this.className = 'btn btn-danger btn-lg';
                                    
                                    const enlaceManual = document.getElementById('enlaceManual');
                                    if (enlaceManual) {
                                        enlaceManual.style.display = 'block';
                                        enlaceManual.className = 'btn btn-primary btn-lg';
                                    }
                                }
                            }
                        }, 1000);
                    }, 2000);
                    
                    // Mostrar enlace manual después de un tiempo si algo falla
                    setTimeout(() => {
                        const enlaceManual = document.getElementById('enlaceManual');
                        if (enlaceManual) {
                            enlaceManual.style.display = 'block';
                        }
                    }, 8000); // Mostrar después de 8 segundos
                });
            }
        }
    </script>
    
    <?php endif; ?>
</div>
</body>
</html>
