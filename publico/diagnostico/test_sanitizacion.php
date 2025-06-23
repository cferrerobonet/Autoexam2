<?php
/**
 * Test de Sanitización - AUTOEXAM2
 * 
 * Este archivo comprueba el funcionamiento de la clase Sanitizador
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 23/06/2025
 */

// Configuración inicial
define('APP_PATH', dirname(dirname(__DIR__)) . '/app');
define('BASE_URL', '/');

// Incluir la clase Sanitizador
require_once APP_PATH . '/utilidades/sanitizador.php';

// Habilitar errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');

// Función para testear el sanitizador
function testSanitizador() {
    $resultados = [];
    
    // Test 1: Sanitizar texto
    $texto = "Texto con <script>alert('XSS')</script> y caracteres especiales & ' \"";
    $resultados[] = [
        'prueba' => 'Sanitizar texto con HTML y caracteres especiales',
        'entrada' => $texto,
        'salida' => Sanitizador::texto($texto),
        'esperado' => 'Texto con &lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt; y caracteres especiales &amp; &#039; &quot;'
    ];
    
    // Test 2: Sanitizar email
    $email = "usuario+prueba@dominio.com";
    $resultados[] = [
        'prueba' => 'Sanitizar email válido',
        'entrada' => $email,
        'salida' => Sanitizador::email($email),
        'esperado' => 'usuario+prueba@dominio.com'
    ];
    
    // Test 3: Validar email correcto
    $resultados[] = [
        'prueba' => 'Validar email correcto',
        'entrada' => 'usuario@dominio.com',
        'salida' => Sanitizador::esEmailValido('usuario@dominio.com') ? 'Válido' : 'Inválido',
        'esperado' => 'Válido'
    ];
    
    // Test 4: Validar email incorrecto
    $resultados[] = [
        'prueba' => 'Validar email incorrecto',
        'entrada' => 'usuario@dominio',
        'salida' => Sanitizador::esEmailValido('usuario@dominio') ? 'Válido' : 'Inválido',
        'esperado' => 'Inválido'
    ];
    
    // Test 5: Sanitizar entero
    $resultados[] = [
        'prueba' => 'Sanitizar entero',
        'entrada' => '123abc',
        'salida' => Sanitizador::entero('123abc'),
        'esperado' => 123
    ];
    
    // Test 6: Sanitizar decimal
    $resultados[] = [
        'prueba' => 'Sanitizar decimal con coma (formato español)',
        'entrada' => '123,45',
        'salida' => Sanitizador::decimal('123,45'),
        'esperado' => 123.45
    ];
    
    // Test 7: Sanitizar array GET
    $_GET = [
        'id' => '123abc',
        'email' => 'test@test.com',
        'nombre' => '<script>alert("XSS")</script>'
    ];
    
    $datos_get = Sanitizador::get(['id', 'email', 'nombre'], [
        'id' => 'entero',
        'email' => 'email'
    ]);
    
    $resultados[] = [
        'prueba' => 'Sanitizar datos GET',
        'entrada' => json_encode($_GET),
        'salida' => json_encode($datos_get),
        'esperado' => json_encode([
            'id' => 123,
            'email' => 'test@test.com',
            'nombre' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;'
        ])
    ];
    
    return $resultados;
}

// Ejecutar pruebas
$resultados = testSanitizador();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Sanitización - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pass { background-color: #d4edda; }
        .fail { background-color: #f8d7da; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Test de Sanitización - AUTOEXAM2</h2>
                    </div>
                    <div class="card-body">
                        <p class="lead">Este archivo verifica el correcto funcionamiento de la clase <code>Sanitizador</code>.</p>
                        <p>Fecha de ejecución: <?= date('d/m/Y H:i:s') ?></p>
                        <hr>

                        <h3 class="h5 mb-3">Resultados:</h3>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Prueba</th>
                                        <th width="25%">Entrada</th>
                                        <th width="20%">Salida</th>
                                        <th width="20%">Esperado</th>
                                        <th width="5%">Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $i => $test): ?>
                                        <?php 
                                        $pass = $test['salida'] == $test['esperado'] ||
                                               (is_string($test['salida']) && is_string($test['esperado']) && 
                                                strcmp($test['salida'], $test['esperado']) === 0);
                                        ?>
                                        <tr class="<?= $pass ? 'pass' : 'fail' ?>">
                                            <td><?= $i + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($test['prueba']) ?></strong></td>
                                            <td><code><?= htmlspecialchars(print_r($test['entrada'], true)) ?></code></td>
                                            <td><code><?= htmlspecialchars(print_r($test['salida'], true)) ?></code></td>
                                            <td><code><?= htmlspecialchars(print_r($test['esperado'], true)) ?></code></td>
                                            <td class="text-center">
                                                <?php if ($pass): ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">ERROR</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <a href="./index.php" class="btn btn-primary">Volver al índice de diagnóstico</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
