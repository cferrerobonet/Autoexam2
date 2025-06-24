<?php
/**
 * Diagnóstico - Vista de Alumnos Unificada
 * Verificar si la vista está correctamente actualizada
 */

// Configuración básica
define('BASE_URL', 'http://autoexam2.cferrerobonet.com');
define('APP_PATH', dirname(__DIR__, 1) . '/app');

// Función para verificar archivos
function verificarArchivo($ruta, $descripcion) {
    $rutaCompleta = dirname(__DIR__, 1) . $ruta;
    $existe = file_exists($rutaCompleta);
    $tamaño = $existe ? filesize($rutaCompleta) : 0;
    $modificado = $existe ? date('Y-m-d H:i:s', filemtime($rutaCompleta)) : 'N/A';
    
    echo "<tr class='" . ($existe ? 'table-success' : 'table-danger') . "'>";
    echo "<td>$descripcion</td>";
    echo "<td>" . ($existe ? 'SÍ' : 'NO') . "</td>";
    echo "<td>" . number_format($tamaño) . " bytes</td>";
    echo "<td>$modificado</td>";
    echo "</tr>";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Vista Alumnos Unificada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-search"></i> Diagnóstico - Vista de Alumnos Unificada</h3>
                        <small class="text-muted">Verificación de archivos actualizados - <?= date('Y-m-d H:i:s') ?></small>
                    </div>
                    <div class="card-body">
                        
                        <h5>Estado de Archivos Críticos</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Archivo</th>
                                    <th>Existe</th>
                                    <th>Tamaño</th>
                                    <th>Última Modificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                verificarArchivo('/app/vistas/profesor/usuarios/listar.php', 'Vista Alumnos');
                                verificarArchivo('/app/controladores/cursos_controlador.php', 'Controlador Cursos');
                                verificarArchivo('/app/modelos/curso_modelo.php', 'Modelo Curso');
                                verificarArchivo('/publico/recursos/css/profesor-views.css', 'CSS Unificado');
                                verificarArchivo('/app/vistas/parciales/head_profesor.php', 'Head Profesor');
                                ?>
                            </tbody>
                        </table>

                        <h5 class="mt-4">Verificación de Métodos del Modelo</h5>
                        <?php
                        require_once dirname(__DIR__, 1) . '/config/config.php';
                        require_once dirname(__DIR__, 1) . '/app/modelos/curso_modelo.php';
                        
                        $curso = new Curso();
                        $metodos = get_class_methods($curso);
                        
                        echo "<div class='row'>";
                        echo "<div class='col-md-6'>";
                        echo "<h6>Métodos Disponibles:</h6>";
                        echo "<ul>";
                        foreach ($metodos as $metodo) {
                            if (strpos($metodo, 'obtener') !== false || strpos($metodo, 'contar') !== false) {
                                echo "<li>$metodo</li>";
                            }
                        }
                        echo "</ul>";
                        echo "</div>";
                        
                        echo "<div class='col-md-6'>";
                        echo "<h6>Métodos Críticos:</h6>";
                        echo "<ul>";
                        $metodosRequeridos = [
                            'obtenerAlumnosPorCursoConFiltros',
                            'contarAlumnosPorCurso',
                            'obtenerAlumnosPorCurso'
                        ];
                        
                        foreach ($metodosRequeridos as $metodo) {
                            $existe = method_exists($curso, $metodo);
                            echo "<li class='" . ($existe ? 'text-success' : 'text-danger') . "'>";
                            echo $metodo . " " . ($existe ? '✓' : '✗');
                            echo "</li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                        echo "</div>";
                        ?>

                        <h5 class="mt-4">Contenido de la Vista (Primeras líneas)</h5>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><?php
                        $vistaContent = file_get_contents(dirname(__DIR__, 1) . '/app/vistas/profesor/usuarios/listar.php');
                        echo htmlspecialchars(substr($vistaContent, 0, 1500)) . '...';
                        ?></pre>

                        <div class="mt-4">
                            <a href="<?= BASE_URL ?>/cursos" class="btn btn-primary">Ir a Cursos</a>
                            <a href="index.php" class="btn btn-secondary">Volver al Diagnóstico</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
