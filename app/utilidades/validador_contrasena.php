<?php
/**
 * Validador de Contraseñas - AUTOEXAM2
 * 
 * Valida la complejidad y seguridad de las contraseñas
 * 
 * @author Github Copilot
 * @version 1.0
 * @date 13/06/2025
 */

class ValidadorContrasena {
    private $config;
    
    /**
     * Constructor
     * 
     * @param array $config Configuración opcional de validación
     */
    public function __construct($config = null) {
        // Configuración por defecto
        $this->config = $config ?? [
            'longitud_minima' => 8,
            'requiere_mayusculas' => true,
            'requiere_minusculas' => true,
            'requiere_numeros' => true,
            'requiere_simbolos' => false,
            'simbolos_validos' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
            'max_intentos' => 3
        ];
    }
    
    /**
     * Valida la complejidad de una contraseña
     * 
     * @param string $contrasena Contraseña a validar
     * @return array Resultado de la validación [valida => bool, error => string]
     */
    public function validarComplejidad($contrasena) {
        $resultado = [
            'valida' => true,
            'error' => null
        ];
        
        // Validar longitud
        if (strlen($contrasena) < $this->config['longitud_minima']) {
            $resultado['valida'] = false;
            $resultado['error'] = "La contraseña debe tener al menos {$this->config['longitud_minima']} caracteres.";
            return $resultado;
        }
        
        // Validar mayúsculas
        if ($this->config['requiere_mayusculas'] && !preg_match('/[A-Z]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos una letra mayúscula.';
            return $resultado;
        }
        
        // Validar minúsculas
        if ($this->config['requiere_minusculas'] && !preg_match('/[a-z]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos una letra minúscula.';
            return $resultado;
        }
        
        // Validar números
        if ($this->config['requiere_numeros'] && !preg_match('/[0-9]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos un número.';
            return $resultado;
        }
        
        // Validar símbolos si es requerido
        if ($this->config['requiere_simbolos']) {
            $pattern = '/[' . preg_quote($this->config['simbolos_validos'], '/') . ']/';
            if (!preg_match($pattern, $contrasena)) {
                $resultado['valida'] = false;
                $resultado['error'] = 'La contraseña debe contener al menos un símbolo especial.';
                return $resultado;
            }
        }
        
        return $resultado;
    }
    
    /**
     * Verifica que dos contraseñas coincidan
     * 
     * @param string $contrasena Contraseña
     * @param string $confirmacion Confirmación de contraseña
     * @return array Resultado de la validación [coinciden => bool, error => string]
     */
    public function validarCoincidencia($contrasena, $confirmacion) {
        $resultado = [
            'coinciden' => true,
            'error' => null
        ];
        
        if ($contrasena !== $confirmacion) {
            $resultado['coinciden'] = false;
            $resultado['error'] = 'Las contraseñas no coinciden.';
        }
        
        return $resultado;
    }
    
    /**
     * Calcula la fortaleza de una contraseña
     * 
     * @param string $contrasena Contraseña a evaluar
     * @return array Información sobre la fortaleza [puntuacion => int, nivel => string, recomendaciones => array]
     */
    public function calcularFortaleza($contrasena) {
        $puntuacion = 0;
        $recomendaciones = [];
        
        // Longitud
        $puntuacion += min(strlen($contrasena) * 4, 40);
        
        // Mayúsculas
        $mayusculas = preg_match_all('/[A-Z]/', $contrasena);
        if ($mayusculas > 0) {
            $puntuacion += ($mayusculas * 2);
        } else {
            $recomendaciones[] = 'Incluye letras mayúsculas para mejorar la seguridad.';
        }
        
        // Minúsculas
        $minusculas = preg_match_all('/[a-z]/', $contrasena);
        if ($minusculas > 0) {
            $puntuacion += ($minusculas * 2);
        } else {
            $recomendaciones[] = 'Incluye letras minúsculas para mejorar la seguridad.';
        }
        
        // Números
        $numeros = preg_match_all('/[0-9]/', $contrasena);
        if ($numeros > 0) {
            $puntuacion += ($numeros * 4);
        } else {
            $recomendaciones[] = 'Incluye números para mejorar la seguridad.';
        }
        
        // Símbolos
        $simbolos = preg_match_all('/[^a-zA-Z0-9]/', $contrasena);
        if ($simbolos > 0) {
            $puntuacion += ($simbolos * 6);
        } else {
            $recomendaciones[] = 'Incluye símbolos especiales para mejorar la seguridad.';
        }
        
        // Determinar nivel
        $nivel = 'débil';
        if ($puntuacion >= 80) {
            $nivel = 'muy fuerte';
        } else if ($puntuacion >= 60) {
            $nivel = 'fuerte';
        } else if ($puntuacion >= 40) {
            $nivel = 'media';
        } else if ($puntuacion >= 20) {
            $nivel = 'baja';
        }
        
        return [
            'puntuacion' => $puntuacion,
            'nivel' => $nivel,
            'recomendaciones' => $recomendaciones
        ];
    }
    
    /**
     * Obtiene los requisitos de contraseña actuales
     * 
     * @return array Requisitos configurados para las contraseñas
     */
    public function obtenerRequisitos() {
        return [
            'longitud_minima' => $this->config['longitud_minima'],
            'requiere_mayusculas' => $this->config['requiere_mayusculas'],
            'requiere_minusculas' => $this->config['requiere_minusculas'],
            'requiere_numeros' => $this->config['requiere_numeros'],
            'requiere_simbolos' => $this->config['requiere_simbolos']
        ];
    }
}
?>
