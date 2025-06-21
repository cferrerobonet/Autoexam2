<?php
/**
 * Diagnóstico simple de acceso a exámenes
 * Archivo de prueba para verificar acceso
 */

// Configuración básica
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/utilidades/sesion.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Exámenes - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1>Diagnóstico de Acceso a Exámenes</h1>
                <a href="index.php" class="btn btn-secondary mb-3">← Volver al índice de diagnósticos</a>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Estado de la Sesión</h3>
                    </div>
                    <div class="card-body">
                        <?php if (Sesion::esta_iniciada()): ?>
                            <div class="alert alert-success">
                                <strong>✓ Sesión iniciada correctamente</strong><br>
                                <strong>Usuario ID:</strong> <?= Sesion::obtener_usuario_id() ?><br>
                                <strong>Rol:</strong> <?= Sesion::obtener_rol() ?><br>
                                <strong>Nombre:</strong> <?= Sesion::obtener_nombre_completo() ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>✗ No hay sesión iniciada</strong><br>
                                Debes <a href="<?= BASE_URL ?>/autenticacion/login">iniciar sesión</a> primero.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (Sesion::esta_iniciada()): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Verificación de Permisos</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $rol = Sesion::obtener_rol();
                        if ($rol === 'admin' || $rol === 'profesor'): 
                        ?>
                            <div class="alert alert-success">
                                <strong>✓ Tienes permisos para acceder a exámenes</strong><br>
                                Rol detectado: <code><?= $rol ?></code>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <strong>⚠ Permisos limitados</strong><br>
                                Tu rol (<code><?= $rol ?></code>) tiene acceso limitado a exámenes.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Verificación de Archivos</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $archivos_verificar = [
                            'Controlador de exámenes' => __DIR__ . '/../../app/controladores/examenes_controlador.php',
                            'Modelo de examen' => __DIR__ . '/../../app/modelos/examen_modelo.php',
                            'Vista de exámenes (profesor)' => __DIR__ . '/../../app/vistas/profesor/examenes.php',
                        ];
                        
                        foreach ($archivos_verificar as $nombre => $ruta):
                        ?>
                            <div class="row mb-2">
                                <div class="col-6"><strong><?= $nombre ?>:</strong></div>
                                <div class="col-6">
                                    <?php if (file_exists($ruta)): ?>
                                        <span class="badge bg-success">✓ Existe</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">✗ No existe</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Enlaces de Prueba</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <a href="<?= BASE_URL ?>/examenes" class="btn btn-primary" target="_blank">
                                    Ir a Exámenes (misma ventana)
                                </a>
                            </div>
                            <div class="col-12 mb-2">
                                <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-primary" target="_blank">
                                    Ir a Exámenes (nueva ventana)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($rol === 'admin' || $rol === 'profesor'): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Conexión a Base de Datos</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $db = $GLOBALS['db'];
                            if ($db && $db->ping()) {
                                echo '<div class="alert alert-success">✓ Conexión a BD: OK</div>';
                                
                                // Verificar tablas importantes
                                $tablas = ['examenes', 'modulos', 'cursos', 'usuarios'];
                                foreach ($tablas as $tabla) {
                                    $resultado = $db->query("SHOW TABLES LIKE '$tabla'");
                                    if ($resultado && $resultado->num_rows > 0) {
                                        echo "<div class='badge bg-success me-2'>$tabla ✓</div>";
                                    } else {
                                        echo "<div class='badge bg-danger me-2'>$tabla ✗</div>";
                                    }
                                }
                            } else {
                                echo '<div class="alert alert-danger">✗ No hay conexión a BD</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">Error BD: ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3>Información del Sistema</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6"><strong>BASE_URL:</strong></div>
                            <div class="col-6"><code><?= BASE_URL ?></code></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Fecha/Hora:</strong></div>
                            <div class="col-6"><?= date('d/m/Y H:i:s') ?></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><strong>Archivo actual:</strong></div>
                            <div class="col-6"><code><?= __FILE__ ?></code></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
