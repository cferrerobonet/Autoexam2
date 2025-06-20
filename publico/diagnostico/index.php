<?php
/**
 * Índice de diagnósticos - AUTOEXAM2
 * Muestra todos los archivos de diagnóstico disponibles
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnósticos - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-stethoscope me-2"></i>Panel de Diagnósticos - AUTOEXAM2</h2>
                        <p class="mb-0 text-muted">Herramientas de diagnóstico y verificación del sistema</p>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        // Obtener todos los archivos PHP de diagnóstico (excepto index.php)
                        $archivos = glob("*.php");
                        $diagnosticos = array_filter($archivos, function($archivo) {
                            return $archivo !== 'index.php';
                        });
                        
                        // Ordenar por fecha de modificación (más recientes primero)
                        usort($diagnosticos, function($a, $b) {
                            return filemtime($b) - filemtime($a);
                        });
                        
                        if (empty($diagnosticos)) {
                            echo '<div class="alert alert-info">';
                            echo '<i class="fas fa-info-circle me-2"></i>';
                            echo 'No hay archivos de diagnóstico disponibles.';
                            echo '</div>';
                        } else {
                            echo '<div class="row">';
                            
                            foreach ($diagnosticos as $archivo) {
                                $nombre_sin_extension = pathinfo($archivo, PATHINFO_FILENAME);
                                $fecha_modificacion = date('d/m/Y H:i:s', filemtime($archivo));
                                $tamano = filesize($archivo);
                                $tamano_kb = round($tamano / 1024, 2);
                                
                                // Generar título más legible
                                $titulo = ucwords(str_replace(['_', '-'], ' ', $nombre_sin_extension));
                                
                                // Determinar icono según el tipo de diagnóstico
                                $icono = 'fas fa-file-code';
                                if (strpos($archivo, 'base') !== false || strpos($archivo, 'db') !== false) {
                                    $icono = 'fas fa-database';
                                } elseif (strpos($archivo, 'usuario') !== false) {
                                    $icono = 'fas fa-users';
                                } elseif (strpos($archivo, 'modulo') !== false) {
                                    $icono = 'fas fa-puzzle-piece';
                                } elseif (strpos($archivo, 'conexion') !== false) {
                                    $icono = 'fas fa-plug';
                                } elseif (strpos($archivo, 'config') !== false) {
                                    $icono = 'fas fa-cogs';
                                }
                                
                                echo '<div class="col-md-6 col-lg-4 mb-3">';
                                echo '<div class="card h-100 border-start border-primary border-3">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">';
                                echo '<i class="' . $icono . ' text-primary me-2"></i>';
                                echo htmlspecialchars($titulo);
                                echo '</h5>';
                                echo '<p class="card-text text-muted small">';
                                echo '<i class="fas fa-calendar me-1"></i>Modificado: ' . $fecha_modificacion . '<br>';
                                echo '<i class="fas fa-hdd me-1"></i>Tamaño: ' . $tamano_kb . ' KB';
                                echo '</p>';
                                echo '<a href="' . htmlspecialchars($archivo) . '" class="btn btn-primary btn-sm">';
                                echo '<i class="fas fa-play me-1"></i>Ejecutar';
                                echo '</a>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                        }
                        ?>
                        
                        <hr>
                        <div class="text-center">
                            <a href="../" class="btn btn-secondary">
                                <i class="fas fa-home me-1"></i>Volver al inicio
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
