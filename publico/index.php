<?php
/**
 * AUTOEXAM2 - Punto de entrada público
 * 
 * Este es el archivo principal que se ejecuta cuando se accede al dominio.
 * Redirige al sistema principal que está en el directorio padre.
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.2
 */

// Cambiar el directorio de trabajo al directorio padre antes de incluir el sistema principal
// Esto asegura que todas las rutas relativas funcionen correctamente
chdir(dirname(__DIR__));

// Incluir el archivo principal del sistema
require_once dirname(__DIR__) . '/index.php';
