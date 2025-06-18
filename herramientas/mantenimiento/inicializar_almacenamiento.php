<?php
// Script para inicializar la estructura de almacenamiento

define('ROOT_PATH', dirname(__FILE__, 3)); // Subir tres niveles: mantenimiento -> herramientas -> raíz
require_once ROOT_PATH . '/config/storage.php';

echo "Inicializando estructura de directorios...\n\n";

if (initialize_storage_structure()) {
    echo "✅ Estructura de almacenamiento creada con éxito.\n";
    echo "\nDirectorios creados:\n";
    
    $directories = [
        "📁 LOGS_PATH: " . LOGS_PATH,
        "📁 APP_LOGS_PATH: " . APP_LOGS_PATH,
        "📁 ERROR_LOGS_PATH: " . ERROR_LOGS_PATH,
        "📁 ACCESS_LOGS_PATH: " . ACCESS_LOGS_PATH, 
        "📁 SYSTEM_LOGS_PATH: " . SYSTEM_LOGS_PATH,
        "📁 CACHE_PATH: " . CACHE_PATH,
        "📁 APP_CACHE_PATH: " . APP_CACHE_PATH,
        "📁 VIEW_CACHE_PATH: " . VIEW_CACHE_PATH,
        "📁 DATA_CACHE_PATH: " . DATA_CACHE_PATH,
        "📁 TMP_PATH: " . TMP_PATH,
        "📁 TEMP_UPLOADS_PATH: " . TEMP_UPLOADS_PATH,
        "📁 SESSION_PATH: " . SESSION_PATH,
        "📁 UPLOADS_PATH: " . UPLOADS_PATH,
        "📁 IMAGES_PATH: " . IMAGES_PATH,
        "📁 DOCUMENTS_PATH: " . DOCUMENTS_PATH,
        "📁 EXAMS_PATH: " . EXAMS_PATH,
        "📁 BACKUP_PATH: " . BACKUP_PATH,
        "📁 DB_BACKUP_PATH: " . DB_BACKUP_PATH,
        "📁 SYSTEM_BACKUP_PATH: " . SYSTEM_BACKUP_PATH
    ];
    
    foreach ($directories as $directory) {
        echo "  $directory\n";
    }
    
    echo "\n✅ Para más información, consulta la documentación en:\n";
    echo "   /documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md\n";
    
} else {
    echo "❌ Error: No se pudo inicializar la estructura de directorios.\n";
    echo "   Verifica los permisos de escritura en el directorio almacenamiento/\n";
}
