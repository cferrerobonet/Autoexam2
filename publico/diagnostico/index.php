<?php
/**
 * Índice dinámico de archivos de diagnóstico y test
 * AUTOEXAM2 - Sistema de gestión de archivos de prueba
 */

// Habilitar visualización de errores para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Evitar cache del navegador
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configuración
$directorioBase = __DIR__;
$urlBase = './'; // Ruta relativa desde el directorio actual

/**
 * Función recursiva para obtener todos los archivos PHP
 */
function obtenerArchivosPhp($directorio, $directorioBase) {
    $archivos = [];
    
    if (!is_dir($directorio)) {
        return $archivos;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $archivo) {
        if ($archivo->isFile() && file_exists($archivo->getRealPath())) {
            $extension = strtolower($archivo->getExtension());
            
            // Solo archivos PHP, HTML y otros archivos de diagnóstico
            if (in_array($extension, ['php', 'html', 'htm', 'txt', 'log'])) {
                $rutaCompleta = $archivo->getRealPath();
                $rutaRelativa = str_replace($directorioBase . DIRECTORY_SEPARATOR, '', $rutaCompleta);
                $rutaRelativa = str_replace(DIRECTORY_SEPARATOR, '/', $rutaRelativa);
                
                // Excluir este mismo archivo index.php y verificar que el archivo realmente existe
                if (basename($rutaCompleta) !== 'index.php' && is_readable($rutaCompleta)) {
                    $archivos[] = [
                        'nombre' => basename($rutaCompleta),
                        'ruta_relativa' => $rutaRelativa,
                        'ruta_completa' => $rutaCompleta,
                        'directorio' => dirname($rutaRelativa),
                        'extension' => $extension,
                        'tamaño' => $archivo->getSize(),
                        'fecha_modificacion' => $archivo->getMTime(),
                        'fecha_creacion' => $archivo->getCTime(),
                        'es_ejecutable' => $archivo->isExecutable()
                    ];
                }
            }
        }
    }
    
    // Ordenar por fecha de creación descendente
    usort($archivos, function($a, $b) {
        return $b['fecha_creacion'] - $a['fecha_creacion'];
    });
    
    return $archivos;
}

/**
 * Formatear el tamaño de archivo
 */
function formatearTamaño($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($unidades) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $unidades[$pow];
}

/**
 * Obtener icono según la extensión
 */
function obtenerIcono($extension) {
    switch ($extension) {
        case 'php':
            return 'fab fa-php text-primary';
        case 'html':
        case 'htm':
            return 'fab fa-html5 text-warning';
        case 'txt':
            return 'fas fa-file-alt text-secondary';
        case 'log':
            return 'fas fa-file-medical text-danger';
        default:
            return 'fas fa-file text-muted';
    }
}

// Obtener todos los archivos
$archivos = obtenerArchivosPhp($directorioBase, $directorioBase);
$totalArchivos = count($archivos);

// Agrupar por directorio
$archivosPorDirectorio = [];
foreach ($archivos as $archivo) {
    $dir = $archivo['directorio'] === '.' ? 'Raíz' : $archivo['directorio'];
    $archivosPorDirectorio[$dir][] = $archivo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnósticos y Tests - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .archivo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .directorio-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .archivo-item {
            border-left: 4px solid #007bff;
        }
        .archivo-item:hover {
            border-left-color: #28a745;
            background-color: #f8f9fa;
        }
        .actualizar-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Encabezado -->
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-tools me-2"></i>
                Diagnósticos y Tests - AUTOEXAM2
            </span>
            <div class="d-flex">
                <button class="btn btn-outline-light btn-sm me-2" onclick="limpiarCacheYRecargar()">
                    <i class="fas fa-sync-alt me-1"></i>
                    Actualizar
                </button>
                <span class="badge bg-primary rounded-pill">
                    <?= $totalArchivos ?> archivo<?= $totalArchivos != 1 ? 's' : '' ?>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($totalArchivos === 0): ?>
            <!-- Sin archivos -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body py-5">
                            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                            <h3 class="text-muted">No hay archivos de diagnóstico</h3>
                            <p class="text-muted">
                                No se encontraron archivos PHP o HTML en el directorio de diagnósticos.
                            </p>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Los archivos aparecerán automáticamente al crearlos
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-code fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-0"><?= $totalArchivos ?></h4>
                                    <small>Total archivos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-folder fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-0"><?= count($archivosPorDirectorio) ?></h4>
                                    <small>Directorios</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fab fa-php fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-0"><?= count(array_filter($archivos, function($a) { return $a['extension'] === 'php'; })) ?></h4>
                                    <small>Archivos PHP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fab fa-html5 fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-0"><?= count(array_filter($archivos, function($a) { return in_array($a['extension'], ['html', 'htm']); })) ?></h4>
                                    <small>Archivos HTML</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-alt fa-2x me-3"></i>
                                <div>
                                    <h4 class="mb-0"><?= count(array_filter($archivos, function($a) { return in_array($a['extension'], ['txt', 'log']); })) ?></h4>
                                    <small>Otros archivos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de archivos por directorio -->
            <?php foreach ($archivosPorDirectorio as $directorio => $archivosDir): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header directorio-header">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open me-2"></i>
                            <?= htmlspecialchars($directorio) ?>
                            <span class="badge bg-light text-dark ms-2">
                                <?= count($archivosDir) ?> archivo<?= count($archivosDir) != 1 ? 's' : '' ?>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($archivosDir as $archivo): ?>
                                <a href="<?= htmlspecialchars($archivo['ruta_relativa']) ?>" 
                                   class="list-group-item list-group-item-action archivo-item"
                                   <?= $archivo['extension'] === 'php' ? 'target="_blank"' : '' ?>>
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="<?= obtenerIcono($archivo['extension']) ?> fa-lg me-3"></i>
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($archivo['nombre']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Creado: <?= date('d/m/Y H:i:s', $archivo['fecha_creacion']) ?>
                                                    <span class="mx-2">|</span>
                                                    <i class="fas fa-edit me-1"></i>
                                                    Modificado: <?= date('d/m/Y H:i:s', $archivo['fecha_modificacion']) ?>
                                                    <br>
                                                    <i class="fas fa-folder me-1"></i>
                                                    Ruta: <?= htmlspecialchars($archivo['ruta_relativa']) ?>
                                                    <?php if (!$archivo['es_ejecutable'] && $archivo['extension'] === 'php'): ?>
                                                        <span class="badge bg-warning ms-2">No ejecutable</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-secondary">
                                                <?= formatearTamaño($archivo['tamaño']) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted text-uppercase">
                                                <?= $archivo['extension'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Información adicional -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información del sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Directorio base:</strong> 
                            <code><?= htmlspecialchars($directorioBase) ?></code>
                        </p>
                        <p class="mb-2">
                            <strong>URL base:</strong> 
                            <code><?= htmlspecialchars($urlBase) ?></code>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Última actualización:</strong> 
                            <?= date('d/m/Y H:i:s') ?>
                        </p>
                        <p class="mb-2">
                            <strong>Extensiones monitoreadas:</strong> 
                            <span class="badge bg-primary me-1">PHP</span>
                            <span class="badge bg-warning me-1">HTML</span>
                            <span class="badge bg-secondary me-1">TXT</span>
                            <span class="badge bg-danger">LOG</span>
                        </p>
                        <p class="mb-0">
                            <strong>Errores PHP:</strong> 
                            <span class="badge bg-<?= error_reporting() ? 'success' : 'danger' ?>">
                                <?= error_reporting() ? 'Habilitados' : 'Deshabilitados' ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Información de depuración -->
                <div class="row mt-3">
                    <div class="col-12">
                        <details class="mb-3">
                            <summary class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-bug me-1"></i>Información de depuración
                            </summary>
                            <div class="mt-2 p-3 bg-light rounded">
                                <h6>Variables de servidor relevantes:</h6>
                                <ul class="list-unstyled small">
                                    <li><strong>DOCUMENT_ROOT:</strong> <code><?= $_SERVER['DOCUMENT_ROOT'] ?? 'No definido' ?></code></li>
                                    <li><strong>SCRIPT_FILENAME:</strong> <code><?= $_SERVER['SCRIPT_FILENAME'] ?? 'No definido' ?></code></li>
                                    <li><strong>REQUEST_URI:</strong> <code><?= $_SERVER['REQUEST_URI'] ?? 'No definido' ?></code></li>
                                    <li><strong>PHP_SELF:</strong> <code><?= $_SERVER['PHP_SELF'] ?? 'No definido' ?></code></li>
                                </ul>
                                <h6 class="mt-3">Configuración PHP:</h6>
                                <ul class="list-unstyled small">
                                    <li><strong>Version PHP:</strong> <code><?= phpversion() ?></code></li>
                                    <li><strong>Error reporting:</strong> <code><?= error_reporting() ?></code></li>
                                    <li><strong>Display errors:</strong> <code><?= ini_get('display_errors') ?></code></li>
                                    <li><strong>Max execution time:</strong> <code><?= ini_get('max_execution_time') ?>s</code></li>
                                </ul>
                            </div>
                        </details>
                    </div>
                </div>
                
                <hr>
                <div class="text-center">
                    <a href="../" class="btn btn-secondary">
                        <i class="fas fa-home me-1"></i>Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante de actualización -->
    <button class="btn btn-primary btn-lg actualizar-btn shadow" onclick="limpiarCacheYRecargar()" title="Actualizar lista">
        <i class="fas fa-sync-alt"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para limpiar cache y recargar
        function limpiarCacheYRecargar() {
            // Limpiar localStorage y sessionStorage
            if (typeof(Storage) !== "undefined") {
                localStorage.clear();
                sessionStorage.clear();
            }
            
            // Recargar con cache busting
            const timestamp = new Date().getTime();
            window.location.href = window.location.pathname + '?t=' + timestamp;
        }
        
        // Auto-actualización cada 30 segundos con limpieza de cache
        setInterval(function() {
            limpiarCacheYRecargar();
        }, 30000);

        // Efecto de hover en las tarjetas
        document.querySelectorAll('.archivo-item').forEach(function(item) {
            item.addEventListener('mouseenter', function() {
                this.style.borderLeftColor = '#28a745';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.borderLeftColor = '#007bff';
            });
        });

        // Mostrar notificación cuando se actualiza
        document.querySelector('.actualizar-btn').addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            
            setTimeout(function() {
                limpiarCacheYRecargar();
            }, 500);
        });
    </script>
</body>
</html>
