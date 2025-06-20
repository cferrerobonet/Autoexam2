<?php
/**
 * Actualizar tabla módulos - Agregar campo activo
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
    <title>Actualizar Módulos - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Actualizar Tabla Módulos</h3>
                        <a href="index.php" class="btn btn-secondary btn-sm">← Volver a diagnósticos</a>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        try {
                            // Verificar si ya existe el campo activo
                            $result = $mysqli->query("SHOW COLUMNS FROM modulos LIKE 'activo'");
                            
                            if ($result && $result->num_rows > 0) {
                                echo "<div class='alert alert-info'>✓ El campo 'activo' ya existe en la tabla módulos</div>";
                            } else {
                                // Agregar el campo activo
                                $sql = "ALTER TABLE modulos ADD COLUMN activo TINYINT(1) DEFAULT 1";
                                
                                if ($mysqli->query($sql)) {
                                    echo "<div class='alert alert-success'>✓ Campo 'activo' agregado exitosamente a la tabla módulos</div>";
                                    
                                    // Actualizar todos los módulos existentes para que estén activos
                                    $update_sql = "UPDATE modulos SET activo = 1 WHERE activo IS NULL";
                                    if ($mysqli->query($update_sql)) {
                                        echo "<div class='alert alert-success'>✓ Módulos existentes marcados como activos</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-danger'>Error al agregar campo 'activo': " . $mysqli->error . "</div>";
                                }
                            }
                            
                            // Mostrar estructura actualizada
                            echo "<h4>Estructura actualizada:</h4>";
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
