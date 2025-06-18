<?php
/**
 * Biblioteca para la carga y gestión de variables de entorno (.env)
 * 
 * Esta clase se encarga de cargar las variables desde el archivo .env
 * y proveer métodos para acceder a ellas de manera segura
 */

class Env {
    private static $variables = [];
    private static $loaded = false;
    
    /**
     * Carga las variables desde el archivo .env
     * 
     * @param string $path Ruta al archivo .env
     * @return bool Verdadero si se cargó correctamente, falso en caso contrario
     */
    public static function cargar($path) {
        if (self::$loaded) {
            return true;
        }
        
        if (!file_exists($path)) {
            error_log("Archivo .env no encontrado en: $path");
            return false;
        }
        
        $contenido = file_get_contents($path);
        $lineas = explode("\n", $contenido);
        
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            
            // Omitir comentarios (tanto # como //) y líneas vacías
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
            
            // Convertir "true" y "false" a booleanos
            if (strtolower($valor) === 'true') {
                $valor = true;
            } elseif (strtolower($valor) === 'false') {
                $valor = false;
            }
            
            // Almacenar en el array y en $_ENV para compatibilidad
            self::$variables[$clave] = $valor;
            $_ENV[$clave] = $valor;
            
            // También establecer como variable de entorno real
            putenv("$clave=$valor");
        }
        
        self::$loaded = true;
        return true;
    }
    
    /**
     * Obtiene el valor de una variable de entorno
     * 
     * @param string $clave Nombre de la variable
     * @param mixed $valorPorDefecto Valor por defecto si no existe
     * @return mixed Valor de la variable o el valor por defecto
     */
    public static function obtener($clave, $valorPorDefecto = null) {
        if (isset(self::$variables[$clave])) {
            return self::$variables[$clave];
        }
        
        $valor = getenv($clave);
        if ($valor !== false) {
            return $valor;
        }
        
        return $valorPorDefecto;
    }
    
    /**
     * Verifica si una variable de entorno existe
     * 
     * @param string $clave Nombre de la variable
     * @return bool Verdadero si existe, falso en caso contrario
     */
    public static function existe($clave) {
        return isset(self::$variables[$clave]) || getenv($clave) !== false;
    }
    
    /**
     * Establece una variable de entorno en tiempo de ejecución
     * 
     * @param string $clave Nombre de la variable
     * @param mixed $valor Valor a establecer
     */
    public static function establecer($clave, $valor) {
        self::$variables[$clave] = $valor;
        $_ENV[$clave] = $valor;
        putenv("$clave=$valor");
    }
}
