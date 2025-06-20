<?php
/**
 * Diagnóstico - Insertar datos de ejemplo en registro_actividad
 * Solo para testing del dashboard de actividad reciente
 */

require_once '../../config/config.php';

try {
    $conexion = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Verificar si ya hay datos
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM registro_actividad");
    $stmt->execute();
    $resultado = $stmt->fetch();

    if ($resultado['total'] == 0) {
        // Obtener primer usuario admin
        $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE rol = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            $adminId = $admin['id_usuario'];
            
            // Insertar datos de ejemplo
            $actividades = [
                [
                    'id_usuario' => $adminId,
                    'accion' => 'usuario_creado',
                    'descripcion' => 'Nuevo usuario creado: María López (alumno)',
                    'modulo' => 'usuarios',
                    'fecha' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
                ],
                [
                    'id_usuario' => $adminId,
                    'accion' => 'curso_modificado',
                    'descripcion' => 'Curso Matemáticas 3º ESO actualizado - Añadido nuevo módulo',
                    'modulo' => 'cursos',
                    'fecha' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                ],
                [
                    'id_usuario' => null,
                    'accion' => 'backup_sistema',
                    'descripcion' => 'Backup automático completo: BD y archivos del sistema',
                    'modulo' => 'sistema',
                    'fecha' => date('Y-m-d H:i:s', strtotime('-1 day'))
                ],
                [
                    'id_usuario' => $adminId,
                    'accion' => 'configuracion_actualizada',
                    'descripcion' => 'Configuración de correo SMTP actualizada',
                    'modulo' => 'configuracion',
                    'fecha' => date('Y-m-d H:i:s', strtotime('-3 days'))
                ]
            ];

            $sql = "INSERT INTO registro_actividad (id_usuario, accion, descripcion, modulo, fecha, ip, user_agent) 
                    VALUES (:id_usuario, :accion, :descripcion, :modulo, :fecha, '127.0.0.1', 'AUTOEXAM2-System')";
            
            $stmt = $conexion->prepare($sql);
            
            foreach ($actividades as $actividad) {
                $stmt->execute($actividad);
            }
            
            echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> Se insertaron " . count($actividades) . " actividades de ejemplo.</div>";
        } else {
            echo "<div class='alert alert-warning'><i class='fas fa-exclamation-triangle'></i> No se encontró usuario admin.</div>";
        }
    } else {
        echo "<div class='alert alert-info'><i class='fas fa-info-circle'></i> Ya existen {$resultado['total']} registros de actividad.</div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Datos de Actividad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Diagnóstico - Registro de Actividad</h5>
                    </div>
                    <div class="card-body">
                        <p>Este script verifica e inserta datos de ejemplo en la tabla registro_actividad para probar el dashboard.</p>
                        
                        <!-- El resultado se muestra arriba -->
                        
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="/diagnostico" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al índice de diagnósticos
                            </a>
                            <a href="/dashboard" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt me-2"></i>Ver Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
