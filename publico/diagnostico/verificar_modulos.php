<?php
/**
 * Diagnóstico de tabla módulos
 * AUTOEXAM2
 */

// Cargar configuración
require_once '../../config/config.php';
require_once CONFIG_PATH . '/database.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Módulos - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Verificar Estado de Tabla Módulos</h3>
                        <a href="index.php" class="btn btn-secondary btn-sm">← Volver a diagnósticos</a>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        try {
                            // Verificar estructura de tabla módulos
                            echo "<h4>Estructura de tabla módulos:</h4>";
                            $result = $mysqli->query("DESCRIBE modulos");
                            if ($result) {
                                echo "<table class='table table-striped'>";
                                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                            
                            // Verificar si existe campo activo
                            echo "<h4>Verificar campo 'activo':</h4>";
                            $result = $mysqli->query("SHOW COLUMNS FROM modulos LIKE 'activo'");
                            if ($result && $result->num_rows > 0) {
                                echo "<div class='alert alert-success'>✓ Campo 'activo' existe</div>";
                            } else {
                                echo "<div class='alert alert-warning'>⚠ Campo 'activo' NO existe - necesita ser agregado</div>";
                            }
                            
                            // Verificar tabla modulo_curso
                            echo "<h4>Estructura de tabla modulo_curso:</h4>";
                            $result = $mysqli->query("DESCRIBE modulo_curso");
                            if ($result) {
                                echo "<table class='table table-striped'>";
                                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                            
                            // Contar módulos existentes
                            echo "<h4>Datos existentes:</h4>";
                            $result = $mysqli->query("SELECT COUNT(*) as total FROM modulos");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                echo "<p>Total de módulos: <strong>" . $row['total'] . "</strong></p>";
                            }
                            
                            // Verificar profesores disponibles
                            $result = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'profesor' AND activo = 1");
                            if ($result) {
                                $row = $result->fetch_assoc();
                                echo "<p>Profesores activos disponibles: <strong>" . $row['total'] . "</strong></p>";
                            }
                            
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                        ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
