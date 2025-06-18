<?php
/**
 * Funciones auxiliares generales para AUTOEXAM2
 * Este archivo contiene funciones de ayuda reutilizables en todo el sistema.
 */

/**
 * Obtiene la URL correcta para un recurso, teniendo en cuenta si estamos en producción o desarrollo
 * En producción, la carpeta 'publico' es la raíz del sitio web, por lo que no debe incluirse en la URL
 * En desarrollo, la URL debe incluir 'publico'
 *
 * @param string $path Ruta relativa del recurso desde la carpeta publico (sin barra inicial)
 * @return string URL completa al recurso
 */
function get_resource_url($path) {
    // Eliminamos barras iniciales para evitar problemas de dobles barras
    $path = ltrim($path, '/');
    
    // Determinamos si estamos en producción o desarrollo
    // Verificamos si la función existe antes de intentar llamarla
    $isProduction = !function_exists('is_development_environment') || !is_development_environment();
    
    // Debug: Registrar información para diagnóstico
    error_log("get_resource_url - Ruta: $path, es producción: " . ($isProduction ? 'sí' : 'no') . ", BASE_URL: " . BASE_URL);
    
    // En producción, 'publico' es la raíz del sitio, no debe incluirse en la URL
    if ($isProduction) {
        // En producción, no incluimos 'publico' en la URL
        return BASE_URL . '/' . $path;
    } else {
        // En desarrollo, añadimos 'publico' a la ruta
        return BASE_URL . '/publico/' . $path;
    }
}

/**
 * Obtiene la URL de la imagen del logo
 *
 * @return string URL completa a la imagen del logo
 */
function get_logo_url() {
    $logoUrl = get_resource_url('recursos/logo.png');
    error_log("get_logo_url - URL generada para el logo: " . $logoUrl);
    return $logoUrl;
}

/**
 * Genera un imagen fallback para cuando el logo no puede cargarse
 *
 * @return string Data URI de una imagen SVG básica
 */
function get_logo_fallback_svg() {
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iIzAwN2JmZiIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFVVE9FWEFNPC90ZXh0Pjwvc3ZnPg==';
}

/**
 * Verifica si el archivo del logo existe físicamente en el servidor
 *
 * @return bool True si el archivo existe, False en caso contrario
 */
function verify_logo_file_exists() {
    $logoPath = ROOT_PATH . '/publico/recursos/logo.png';
    $exists = file_exists($logoPath);
    $readable = is_readable($logoPath);
    $fileSize = $exists ? filesize($logoPath) : 0;
    
    error_log("verify_logo_file - Ruta física: $logoPath, Existe: " . ($exists ? 'Sí' : 'No') . 
             ", Legible: " . ($readable ? 'Sí' : 'No') . ", Tamaño: $fileSize bytes");
    
    return $exists && $readable && $fileSize > 0;
}
