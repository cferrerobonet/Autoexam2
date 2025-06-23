<?php
/**
 * Sanitizador - AUTOEXAM2
 * 
 * Clase para sanitizar y validar entradas de datos
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 23/06/2025
 */
class Sanitizador {

    /**
     * Sanitiza una cadena de texto para uso seguro en HTML
     * 
     * @param string $texto Texto a sanitizar
     * @return string Texto sanitizado
     */
    public static function texto($texto) {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitiza un correo electrónico
     * 
     * @param string $email Correo a sanitizar
     * @return string Correo sanitizado
     */
    public static function email($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Valida si un correo electrónico tiene formato válido
     * 
     * @param string $email Correo a validar
     * @return bool True si es válido, false si no
     */
    public static function esEmailValido($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitiza un número entero
     * 
     * @param mixed $numero Número a sanitizar
     * @return int Número sanitizado
     */
    public static function entero($numero) {
        return (int)filter_var($numero, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Sanitiza un número decimal
     * 
     * @param mixed $numero Número a sanitizar
     * @return float Número sanitizado
     */
    public static function decimal($numero) {
        // Reemplazar comas por puntos para formato español
        if (is_string($numero)) {
            $numero = str_replace(',', '.', $numero);
        }
        return (float)filter_var($numero, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    /**
     * Sanitiza un valor para uso seguro en SQL (evita inyecciones)
     * Para uso con consultas no preparadas (aunque se recomienda siempre usar consultas preparadas)
     * 
     * @param string $valor Valor a sanitizar
     * @param object $conexion Conexión de base de datos
     * @return string Valor sanitizado
     */
    public static function sql($valor, $conexion) {
        if (is_null($conexion)) {
            return addslashes($valor);
        }
        return mysqli_real_escape_string($conexion, $valor);
    }
    
    /**
     * Sanitiza una URL
     * 
     * @param string $url URL a sanitizar
     * @return string URL sanitizada
     */
    public static function url($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }
    
    /**
     * Valida si una URL es válida
     * 
     * @param string $url URL a validar
     * @return bool True si es válida, false si no
     */
    public static function esUrlValida($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Sanitiza un array de datos según el tipo especificado
     * 
     * @param array $datos Array con datos a sanitizar
     * @param array $tipos Array asociativo con los tipos de cada campo
     * @param object $conexion Conexión a base de datos (opcional, para sanitización SQL)
     * @return array Array sanitizado
     */
    public static function array($datos, $tipos, $conexion = null) {
        $sanitizado = [];
        
        foreach ($datos as $clave => $valor) {
            if (isset($tipos[$clave])) {
                switch ($tipos[$clave]) {
                    case 'texto':
                        $sanitizado[$clave] = self::texto($valor);
                        break;
                    case 'email':
                        $sanitizado[$clave] = self::email($valor);
                        break;
                    case 'entero':
                        $sanitizado[$clave] = self::entero($valor);
                        break;
                    case 'decimal':
                        $sanitizado[$clave] = self::decimal($valor);
                        break;
                    case 'sql':
                        $sanitizado[$clave] = self::sql($valor, $conexion);
                        break;
                    case 'url':
                        $sanitizado[$clave] = self::url($valor);
                        break;
                    default:
                        $sanitizado[$clave] = self::texto($valor);
                }
            } else {
                // Por defecto sanitizar como texto
                $sanitizado[$clave] = is_string($valor) ? self::texto($valor) : $valor;
            }
        }
        
        return $sanitizado;
    }
    
    /**
     * Sanitiza datos GET
     * 
     * @param array $campos Campos a extraer y sanitizar de $_GET
     * @param array $tipos Array asociativo con los tipos de cada campo
     * @return array Array con datos sanitizados
     */
    public static function get($campos, $tipos = []) {
        $datos = [];
        
        foreach ($campos as $campo) {
            if (isset($_GET[$campo])) {
                $tipo = $tipos[$campo] ?? 'texto';
                
                switch ($tipo) {
                    case 'email':
                        $datos[$campo] = self::email($_GET[$campo]);
                        break;
                    case 'entero':
                        $datos[$campo] = self::entero($_GET[$campo]);
                        break;
                    case 'decimal':
                        $datos[$campo] = self::decimal($_GET[$campo]);
                        break;
                    case 'url':
                        $datos[$campo] = self::url($_GET[$campo]);
                        break;
                    default:
                        $datos[$campo] = self::texto($_GET[$campo]);
                }
            } else {
                $datos[$campo] = null;
            }
        }
        
        return $datos;
    }
    
    /**
     * Sanitiza datos POST
     * 
     * @param array $campos Campos a extraer y sanitizar de $_POST
     * @param array $tipos Array asociativo con los tipos de cada campo
     * @return array Array con datos sanitizados
     */
    public static function post($campos, $tipos = []) {
        $datos = [];
        
        foreach ($campos as $campo) {
            if (isset($_POST[$campo])) {
                $tipo = $tipos[$campo] ?? 'texto';
                
                switch ($tipo) {
                    case 'email':
                        $datos[$campo] = self::email($_POST[$campo]);
                        break;
                    case 'entero':
                        $datos[$campo] = self::entero($_POST[$campo]);
                        break;
                    case 'decimal':
                        $datos[$campo] = self::decimal($_POST[$campo]);
                        break;
                    case 'url':
                        $datos[$campo] = self::url($_POST[$campo]);
                        break;
                    default:
                        $datos[$campo] = self::texto($_POST[$campo]);
                }
            } else {
                $datos[$campo] = null;
            }
        }
        
        return $datos;
    }
}
