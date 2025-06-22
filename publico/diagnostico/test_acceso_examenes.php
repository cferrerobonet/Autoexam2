<?php
/**
 * Test de acceso a gestión de exámenes
 */
session_start();

// Configurar el entorno
define('APP_PATH', __DIR__ . '/../../app');
define('BASE_URL', 'http://localhost/AUTOEXAM2');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Acceso Exámenes - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { padding: 10px; margin: 5px 0; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background-color: #cce7ff; color: #004085; border: 1px solid #b3d7ff; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔍 Diagnóstico de Acceso a Exámenes</h1>
        <p><a href="index.php" class="btn btn-secondary">← Volver</a></p>
        
        <div class="row">
            <div class="col-12">
                <h3>Estado de la Sesión</h3>
                <div class="test-result <?= isset($_SESSION['id_usuario']) ? 'success' : 'error' ?>">
                    <strong>Sesión iniciada:</strong> <?= isset($_SESSION['id_usuario']) ? 'SÍ' : 'NO' ?>
                </div>
                
                <?php if (isset($_SESSION['id_usuario'])): ?>
                <div class="test-result info">
                    <strong>ID Usuario:</strong> <?= $_SESSION['id_usuario'] ?? 'No definido' ?><br>
                    <strong>Rol:</strong> <?= $_SESSION['rol'] ?? 'No definido' ?><br>
                    <strong>Nombre:</strong> <?= $_SESSION['nombre'] ?? 'No definido' ?><br>
                    <strong>Email:</strong> <?= $_SESSION['email'] ?? 'No definido' ?>
                </div>
                <?php endif; ?>
                
                <h3 class="mt-4">Verificaciones de Archivos</h3>
                
                <?php
                $archivos_verificar = [
                    'Controlador de Exámenes' => APP_PATH . '/controladores/examenes_controlador.php',
                    'Modelo de Examen' => APP_PATH . '/modelos/examen_modelo.php',
                    'Vista Admin Navbar' => APP_PATH . '/vistas/parciales/navbar_admin.php',
                    'Vista Profesor Navbar' => APP_PATH . '/vistas/parciales/navbar_profesor.php',
                    'Vista Profesor Exámenes' => APP_PATH . '/vistas/profesor/examenes.php',
                    'Vista Alumno Exámenes' => APP_PATH . '/vistas/alumno/examenes.php',
                    'Ruteador' => APP_PATH . '/controladores/ruteador.php'
                ];
                
                foreach ($archivos_verificar as $nombre => $ruta):
                ?>
                <div class="test-result <?= file_exists($ruta) ? 'success' : 'error' ?>">
                    <strong><?= $nombre ?>:</strong> <?= file_exists($ruta) ? 'EXISTE' : 'NO EXISTE' ?>
                    <br><small><?= $ruta ?></small>
                </div>
                <?php endforeach; ?>
                
                <h3 class="mt-4">Test de Conexión de Base de Datos</h3>
                
                <?php
                try {
                    $conexion = Database::getInstance()->getConnection();
                    echo '<div class="test-result success"><strong>Conexión BD:</strong> EXITOSA</div>';
                    
                    // Verificar tablas relacionadas con exámenes
                    $tablas = ['examen', 'pregunta', 'respuesta', 'usuario', 'curso', 'modulo'];
                    foreach ($tablas as $tabla) {
                        $resultado = $conexion->query("SHOW TABLES LIKE '$tabla'");
                        $existe = $resultado->num_rows > 0;
                        echo '<div class="test-result ' . ($existe ? 'success' : 'error') . '">';
                        echo '<strong>Tabla ' . $tabla . ':</strong> ' . ($existe ? 'EXISTE' : 'NO EXISTE');
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="test-result error"><strong>Error BD:</strong> ' . $e->getMessage() . '</div>';
                }
                ?>
                
                <h3 class="mt-4">Test de Controlador</h3>
                
                <?php
                if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor')):
                    try {
                        require_once APP_PATH . '/controladores/examenes_controlador.php';
                        echo '<div class="test-result success"><strong>Controlador cargado:</strong> EXITOSO</div>';
                        
                        $controlador = new ExamenesControlador();
                        echo '<div class="test-result success"><strong>Instancia creada:</strong> EXITOSO</div>';
                        
                        // Verificar si los métodos principales existen
                        $metodos = ['index', 'crear', 'editar', 'eliminar'];
                        foreach ($metodos as $metodo) {
                            $existe = method_exists($controlador, $metodo);
                            echo '<div class="test-result ' . ($existe ? 'success' : 'error') . '">';
                            echo '<strong>Método ' . $metodo . ':</strong> ' . ($existe ? 'EXISTE' : 'NO EXISTE');
                            echo '</div>';
                        }
                        
                    } catch (Exception $e) {
                        echo '<div class="test-result error"><strong>Error Controlador:</strong> ' . $e->getMessage() . '</div>';
                    }
                else:
                    echo '<div class="test-result warning"><strong>Test de Controlador:</strong> Requiere sesión de admin o profesor</div>';
                endif;
                ?>

                <h3 class="mt-4">Test de Simulación de Ruta</h3>
                
                <?php
                if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor')):
                    try {
                        // Simular la lógica del ruteador para examenes
                        $controlador_archivo = APP_PATH . '/controladores/examenes_controlador.php';
                        
                        if (file_exists($controlador_archivo)) {
                            echo '<div class="test-result success"><strong>Archivo del controlador:</strong> ENCONTRADO</div>';
                            
                            require_once $controlador_archivo;
                            
                            if (class_exists('ExamenesControlador')) {
                                echo '<div class="test-result success"><strong>Clase ExamenesControlador:</strong> EXISTE</div>';
                                
                                $instancia = new ExamenesControlador();
                                
                                if (method_exists($instancia, 'index')) {
                                    echo '<div class="test-result success"><strong>Método index:</strong> DISPONIBLE</div>';
                                    
                                    // Intentar capturar la salida del método index
                                    ob_start();
                                    try {
                                        // Simulamos sin ejecutar realmente
                                        echo '<div class="test-result success"><strong>Test de ejecución:</strong> PREPARADO (no ejecutado por seguridad)</div>';
                                    } catch (Exception $e) {
                                        echo '<div class="test-result error"><strong>Error en test:</strong> ' . $e->getMessage() . '</div>';
                                    }
                                    ob_end_clean();
                                    
                                } else {
                                    echo '<div class="test-result error"><strong>Método index:</strong> NO ENCONTRADO</div>';
                                }
                            } else {
                                echo '<div class="test-result error"><strong>Clase ExamenesControlador:</strong> NO EXISTE</div>';
                            }
                        } else {
                            echo '<div class="test-result error"><strong>Archivo del controlador:</strong> NO ENCONTRADO</div>';
                        }
                        
                    } catch (Exception $e) {
                        echo '<div class="test-result error"><strong>Error en simulación:</strong> ' . $e->getMessage() . '</div>';
                    }
                else:
                    echo '<div class="test-result warning"><strong>Test de simulación:</strong> Requiere sesión de admin o profesor</div>';
                endif;
                ?>
                
                <h3 class="mt-4">Acciones de Prueba</h3>
                
                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor')): ?>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/examenes" class="btn btn-primary" target="_blank">
                        🔗 Acceder a Gestión de Exámenes
                    </a>
                    <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-success" target="_blank">
                        ➕ Crear Nuevo Examen
                    </a>
                </div>
                <?php else: ?>
                <div class="test-result warning">
                    <strong>Acciones de Prueba:</strong> Solo disponibles para admin y profesor
                </div>
                <?php endif; ?>
                
                <h3 class="mt-4">Enlaces Directos para Debug</h3>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/publico/index.php?url=examenes" class="btn btn-outline-primary btn-sm">
                        Direct: /examenes
                    </a>
                    <a href="<?= BASE_URL ?>/publico/index.php?url=examenes/index" class="btn btn-outline-primary btn-sm">
                        Direct: /examenes/index
                    </a>
                    <a href="<?= BASE_URL ?>/publico/index.php?url=examenes/crear" class="btn btn-outline-success btn-sm">
                        Direct: /examenes/crear
                    </a>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>
