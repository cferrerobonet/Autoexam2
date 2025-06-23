<?php
/**
 * Diagnóstico - Debug Banco de Preguntas
 * Verifica la configuración de rutas y permisos del banco de preguntas
 */

// Cargar configuración
require_once '../../config/config.php';
require_once '../../app/utilidades/sesion.php';

// Iniciar sesión
iniciarSesion();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "<h2>❌ No hay sesión activa</h2>";
    echo "<p>Para probar esta funcionalidad necesitas estar logueado como admin.</p>";
    echo "<a href='" . BASE_URL . "/autenticacion/iniciar-sesion'>Ir a login</a><br>";
    echo "<a href='" . BASE_URL . "/diagnostico'>← Volver al diagnóstico</a>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Banco de Preguntas - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2><i class="fas fa-bug"></i> Debug: Banco de Preguntas</h2>
                    </div>
                    <div class="card-body">
                        
                        <h3>🔐 Información de Sesión</h3>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <tr><td><strong>ID Usuario:</strong></td><td><?= $_SESSION['id_usuario'] ?? 'No definido' ?></td></tr>
                                <tr><td><strong>Email:</strong></td><td><?= $_SESSION['email'] ?? 'No definido' ?></td></tr>
                                <tr><td><strong>Rol:</strong></td><td><?= $_SESSION['rol'] ?? 'No definido' ?></td></tr>
                                <tr><td><strong>Nombre:</strong></td><td><?= $_SESSION['nombre'] ?? 'No definido' ?></td></tr>
                            </table>
                        </div>

                        <h3>📁 Verificación de Archivos</h3>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <?php
                                $archivos_verificar = [
                                    'Controlador Banco Preguntas' => APP_PATH . '/controladores/banco_preguntas_controlador.php',
                                    'Vista Admin Nueva Pregunta' => APP_PATH . '/vistas/admin/nueva_pregunta_banco.php',
                                    'Vista Profesor Nueva Pregunta' => APP_PATH . '/vistas/profesor/nueva_pregunta_banco.php',
                                    'Modelo Pregunta Banco' => APP_PATH . '/modelos/pregunta_banco_modelo.php',
                                    'Ruteador' => APP_PATH . '/controladores/ruteador.php'
                                ];

                                foreach ($archivos_verificar as $nombre => $ruta) {
                                    $existe = file_exists($ruta);
                                    echo "<tr>";
                                    echo "<td><strong>$nombre:</strong></td>";
                                    echo "<td>";
                                    if ($existe) {
                                        echo "<span class='badge bg-success'>✓ Existe</span> <code>$ruta</code>";
                                    } else {
                                        echo "<span class='badge bg-danger'>✗ No existe</span> <code>$ruta</code>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </div>

                        <h3>🚀 Pruebas de URLs</h3>
                        <div class="mb-4">
                            <p>Haz clic en los enlaces para probar cada ruta:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="<?= BASE_URL ?>/banco-preguntas" class="btn btn-outline-primary btn-sm" target="_blank">
                                        📋 Listar Banco de Preguntas
                                    </a>
                                    <code class="ms-2"><?= BASE_URL ?>/banco-preguntas</code>
                                </li>
                                <li class="mb-2">
                                    <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-outline-success btn-sm" target="_blank">
                                        ➕ Crear Nueva Pregunta
                                    </a>
                                    <code class="ms-2"><?= BASE_URL ?>/banco-preguntas/crear</code>
                                </li>
                            </ul>
                        </div>

                        <h3>🔍 Información de PHP</h3>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <tr><td><strong>BASE_URL:</strong></td><td><code><?= BASE_URL ?></code></td></tr>
                                <tr><td><strong>APP_PATH:</strong></td><td><code><?= APP_PATH ?></code></td></tr>
                                <tr><td><strong>URL actual:</strong></td><td><code><?= $_SERVER['REQUEST_URI'] ?></code></td></tr>
                                <tr><td><strong>Method:</strong></td><td><code><?= $_SERVER['REQUEST_METHOD'] ?></code></td></tr>
                            </table>
                        </div>

                        <?php
                        // Verificar si el método crear existe en el controlador
                        require_once APP_PATH . '/controladores/banco_preguntas_controlador.php';
                        $controlador = new BancoPreguntasControlador();
                        ?>

                        <h3>🧪 Verificación de Métodos</h3>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <tr>
                                    <td><strong>Método 'crear' existe:</strong></td>
                                    <td>
                                        <?php if (method_exists($controlador, 'crear')): ?>
                                            <span class="badge bg-success">✓ Sí</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">✗ No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Método 'index' existe:</strong></td>
                                    <td>
                                        <?php if (method_exists($controlador, 'index')): ?>
                                            <span class="badge bg-success">✓ Sí</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">✗ No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="alert alert-info">
                            <h5>🔧 Pasos para solucionar:</h5>
                            <ol>
                                <li>Verifica que todos los archivos existan</li>
                                <li>Comprueba que tu sesión tenga rol 'admin' o 'profesor'</li>
                                <li>Asegúrate de que no haya errores en los logs del servidor</li>
                                <li>Prueba las URLs desde aquí para identificar el punto de falla</li>
                            </ol>
                        </div>

                        <div class="mt-4">
                            <a href="<?= BASE_URL ?>/diagnostico" class="btn btn-secondary">
                                ← Volver al diagnóstico
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
