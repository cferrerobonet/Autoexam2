<?php
/**
 * Diagnóstico de Token CSRF - AUTOEXAM2
 * Verifica el estado del token CSRF en la sesión
 */

session_start();

// Incluir configuración
require_once '../../../config/config.php';
require_once APP_PATH . '/utilidades/sesion.php';

$sesion = new Sesion();

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n";
echo "<title>Diagnóstico CSRF - AUTOEXAM2</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; }\n";
echo ".info { background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo ".warning { background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo ".error { background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }\n";
echo "</style>\n";
echo "</head>\n<body>\n";

echo "<h1>Diagnóstico de Token CSRF</h1>\n";
echo "<p><a href='index.php'>← Volver al diagnóstico</a></p>\n";

echo "<h2>Estado de la Sesión</h2>\n";
echo "<div class='info'>\n";
echo "<strong>ID de Sesión:</strong> " . session_id() . "<br>\n";
echo "<strong>Estado de Sesión:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVA' : 'INACTIVA') . "<br>\n";
echo "</div>\n";

echo "<h2>Token CSRF en Sesión</h2>\n";
if (isset($_SESSION['csrf_token'])) {
    echo "<div class='info'>\n";
    echo "<strong>Token existe:</strong> SÍ<br>\n";
    echo "<strong>Token:</strong> " . substr($_SESSION['csrf_token'], 0, 16) . "...<br>\n";
    echo "<strong>Tiempo:</strong> " . (isset($_SESSION['csrf_token_time']) ? date('Y-m-d H:i:s', $_SESSION['csrf_token_time']) : 'No definido') . "<br>\n";
    
    if (isset($_SESSION['csrf_token_time'])) {
        $tiempoTranscurrido = time() - $_SESSION['csrf_token_time'];
        $tiempoValidez = defined('TOKEN_VALIDITY_TIME') ? TOKEN_VALIDITY_TIME : 3600;
        echo "<strong>Tiempo transcurrido:</strong> " . $tiempoTranscurrido . " segundos<br>\n";
        echo "<strong>Tiempo de validez:</strong> " . $tiempoValidez . " segundos<br>\n";
        echo "<strong>¿Expirado?:</strong> " . ($tiempoTranscurrido > $tiempoValidez ? 'SÍ' : 'NO') . "<br>\n";
    }
    echo "</div>\n";
} else {
    echo "<div class='warning'>\n";
    echo "<strong>Token existe:</strong> NO<br>\n";
    echo "</div>\n";
}

echo "<h2>Generar Nuevo Token</h2>\n";
$nuevoToken = $sesion->generarTokenCSRF();
echo "<div class='info'>\n";
echo "<strong>Nuevo token generado:</strong> " . substr($nuevoToken, 0, 16) . "...<br>\n";
echo "</div>\n";

echo "<h2>Prueba de Validación</h2>\n";
if (isset($_POST['test_token'])) {
    $resultado = $sesion->validarTokenCSRF($_POST['test_token']);
    if ($resultado) {
        echo "<div class='info'><strong>Resultado:</strong> Token VÁLIDO</div>\n";
    } else {
        echo "<div class='error'><strong>Resultado:</strong> Token INVÁLIDO</div>\n";
    }
}

echo "<form method='POST'>\n";
echo "<input type='hidden' name='test_token' value='" . $nuevoToken . "'>\n";
echo "<button type='submit'>Probar Validación de Token</button>\n";
echo "</form>\n";

echo "<h2>Variables de Sesión</h2>\n";
echo "<div class='info'>\n";
echo "<pre>" . print_r($_SESSION, true) . "</pre>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
?>
