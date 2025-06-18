<?php
/**
 * Script para actualizar las referencias a /tmp en archivos del instalador
 * 
 * Este script busca y reemplaza las referencias a la estructura antigua /tmp
 * en los archivos del instalador para usar la nueva estructura en /almacenamiento.
 */

// Directorios a procesar
$directories = [
    __DIR__ . '/../../publico/instalador'
];

// Patrones de búsqueda y reemplazo
$replacements = [
    // Referencias a tmp/logs
    '/\'tmp\/logs\'/' => '\'almacenamiento/logs/sistema\'',
    '/"\\/tmp\\/logs"/' => '"/almacenamiento/logs/sistema"',
    '/\'path\' => \$root_dir . \'\/tmp\/logs\'/' => '\'path\' => $root_dir . \'/almacenamiento/logs/sistema\'',
    '/\'tmp\/logs\' => \'/' => '\'almacenamiento/logs/sistema\' => \'',
    
    // Referencias a tmp/cache
    '/\'tmp\/cache\'/' => '\'almacenamiento/cache\'',
    '/"\\/tmp\\/cache"/' => '"/almacenamiento/cache"',
    '/\'path\' => \$root_dir . \'\/tmp\/cache\'/' => '\'path\' => $root_dir . \'/almacenamiento/cache\'',
    '/\'tmp\/cache\' => \'/' => '\'almacenamiento/cache\' => \'',
    
    // Referencias directas a tmp
    '/in_array\(\$dir, \[\'tmp\'/' => 'in_array($dir, [\'almacenamiento\'',
];

// Referencias directas con __DIR__
$directReplacements = [
    '/\$log_dir = __DIR__ . \'\/..\/..\/tmp\/logs\/\';/' => '$log_dir = __DIR__ . \'/../../almacenamiento/logs/sistema/\';',
    '/\$this->log_path = \$this->root_path . \'\/tmp\/logs\';/' => '$this->log_path = $this->root_path . \'/almacenamiento/logs/sistema\';',
];

// Extensiones de archivos a procesar
$extensions = ['php', 'js', 'css', 'html'];

// Contador de archivos y reemplazos
$filesProcessed = 0;
$replacementsCount = 0;

// Función para procesar un archivo
function processFile($filePath, $replacements, $directReplacements) {
    global $filesProcessed, $replacementsCount;
    
    // Leer el contenido
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Aplicar reemplazos con expresiones regulares
    foreach ($replacements as $search => $replace) {
        $content = preg_replace($search, $replace, $content, -1, $count);
        $replacementsCount += $count;
    }
    
    // Aplicar reemplazos directos
    foreach ($directReplacements as $search => $replace) {
        $content = preg_replace($search, $replace, $content, -1, $count);
        $replacementsCount += $count;
    }
    
    // Si ha habido cambios, guardar el archivo
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $filesProcessed++;
        echo "Actualizado: $filePath\n";
        echo "  Reemplazos en este archivo: $replacementsCount\n";
    }
}

// Función recursiva para procesar directorios
function processDirectory($dir, $replacements, $directReplacements, $extensions) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            processDirectory($path, $replacements, $directReplacements, $extensions);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, $extensions)) {
                processFile($path, $replacements, $directReplacements);
            }
        }
    }
}

// Procesar directorios
echo "Iniciando actualización de referencias a /tmp en archivos del instalador...\n";

foreach ($directories as $directory) {
    processDirectory($directory, $replacements, $directReplacements, $extensions);
}

echo "Proceso completado.\n";
echo "Archivos procesados: $filesProcessed\n";
echo "Total de reemplazos: $replacementsCount\n";
