<?php
/**
 * Script para actualizar las rutas antiguas en los archivos del instalador
 * 
 * Este script busca y reemplaza las referencias a las rutas antiguas de
 * publico/subidas en los archivos del instalador para usar la nueva estructura.
 */

// Directorios a procesar
$directories = [
    __DIR__ . '/../../publico/instalador'
];

// Patrones de búsqueda y reemplazo
$replacements = [
    // Rutas absolutas en código PHP
    '/\/publico\/subidas\//' => '/almacenamiento/subidas/',
    
    // Referencias a directorios en configuración
    '/\'publico\/subidas\'/' => '\'almacenamiento/subidas\'',
    
    // Referencias con __DIR__ y concatenación
    '/\$.*\s*\.\s*\'\/\.\.\/\.\.\/publico\/subidas/' => '$${1} . \'/../../almacenamiento/subidas',
    
    // Referencias de ruta relativa en arrays
    '/\'publico\/subidas\' =>\s*\[/' => '\'almacenamiento/subidas\' => [',
    
    // Referencias de texto en arrays
    '/\'publico\/subidas\' =>\s*\'/' => '\'almacenamiento/subidas\' => \''
];

// Extensiones de archivos a procesar
$extensions = ['php', 'js', 'css', 'html'];

// Contador de archivos y reemplazos
$filesProcessed = 0;
$replacementsCount = 0;

// Función para procesar un archivo
function processFile($filePath, $replacements) {
    global $filesProcessed, $replacementsCount;
    
    // Leer el contenido
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Aplicar reemplazos
    foreach ($replacements as $search => $replace) {
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
function processDirectory($dir, $replacements, $extensions) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            processDirectory($path, $replacements, $extensions);
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, $extensions)) {
                processFile($path, $replacements);
            }
        }
    }
}

// Procesar directorios
echo "Iniciando actualización de rutas en archivos del instalador...\n";

foreach ($directories as $directory) {
    processDirectory($directory, $replacements, $extensions);
}

echo "Proceso completado.\n";
echo "Archivos procesados: $filesProcessed\n";
echo "Total de reemplazos: $replacementsCount\n";
