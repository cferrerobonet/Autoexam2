<?php
// Verificar que $datos exista y tenga los valores necesarios
if (!isset($datos) || !isset($datos['sesiones'])) {
    echo "<div class='alert alert-danger'>Error: No se pudo cargar la información de las sesiones</div>";
    exit;
}
?>

<!-- Vista de listado de sesiones activas -->
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>
    <title>AUTOEXAM2 - <?php echo $datos['titulo']; ?></title>
</head>
<body>
    <?php include_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><?php echo $datos['titulo']; ?></h1>
                <p class="text-muted">Panel de control de sesiones activas en el sistema</p>
            </div>
            <div class="col-md-4 text-end">
                <!-- Formulario para limpiar sesiones inactivas -->
                <form method="post" action="<?php echo BASE_URL; ?>/sesiones_activas/limpiar" class="mb-3">
                    <input type="hidden" name="csrf_token" value="<?php echo $datos['csrf_token']; ?>">
                    <div class="input-group">
                        <select name="horas_inactividad" class="form-select">
                            <option value="1">1 hora</option>
                            <option value="3">3 horas</option>
                            <option value="6">6 horas</option>
                            <option value="12">12 horas</option>
                            <option value="24" selected>24 horas</option>
                            <option value="48">2 días</option>
                            <option value="168">7 días</option>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-broom"></i> Limpiar inactivas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($datos['sesiones'])): ?>
                    <div class="alert alert-info">No hay sesiones activas registradas en este momento</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Inicio</th>
                                    <th>Última actividad</th>
                                    <th>IP</th>
                                    <th>Dispositivo</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($datos['sesiones'] as $sesion): ?>
                                    <?php 
                                    // Calcular tiempo de inactividad
                                    $ultima = new DateTime($sesion['ultima_actividad']);
                                    $ahora = new DateTime();
                                    $diff = $ultima->diff($ahora);
                                    $minutos_inactivo = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                                    
                                    // Color según tiempo de inactividad
                                    $class = '';
                                    if ($minutos_inactivo > 60) {
                                        $class = 'table-warning';
                                    } else if ($minutos_inactivo > 15) {
                                        $class = 'table-light';
                                    }
                                    
                                    // No permitir cerrar la propia sesión desde aquí
                                    $es_sesion_actual = ($sesion['token'] === $_SESSION['token_sesion']);
                                    ?>
                                    <tr class="<?php echo $class; ?>">
                                        <td>
                                            <?php echo htmlspecialchars($sesion['nombre'] . ' ' . $sesion['apellidos']); ?>
                                            <?php if ($es_sesion_actual): ?>
                                                <span class="badge bg-primary">Actual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                    switch($sesion['rol']) {
                                                        case 'admin': echo 'bg-danger'; break;
                                                        case 'profesor': echo 'bg-success'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                <?php echo htmlspecialchars($sesion['rol']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($sesion['fecha_inicio'])); ?></td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($sesion['ultima_actividad'])); ?>
                                            <?php if ($minutos_inactivo > 0): ?>
                                                <br><small class="text-muted">
                                                    <?php 
                                                    if ($diff->days > 0) echo $diff->days . 'd ';
                                                    if ($diff->h > 0) echo $diff->h . 'h ';
                                                    echo $diff->i . 'm';
                                                    ?> inactivo
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($sesion['ip']); ?></td>
                                        <td>
                                            <?php
                                            // Intentar identificar el dispositivo/navegador
                                            $ua = $sesion['user_agent'];
                                            if (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) {
                                                echo '<i class="fas fa-mobile-alt"></i> ';
                                                echo strpos($ua, 'iPhone') !== false ? 'iPhone' : 'iPad';
                                            } elseif (strpos($ua, 'Android') !== false) {
                                                echo '<i class="fas fa-mobile-alt"></i> Android';
                                            } elseif (strpos($ua, 'Windows') !== false) {
                                                echo '<i class="fas fa-laptop"></i> Windows';
                                            } elseif (strpos($ua, 'Macintosh') !== false) {
                                                echo '<i class="fas fa-laptop"></i> Mac OS';
                                            } elseif (strpos($ua, 'Linux') !== false) {
                                                echo '<i class="fas fa-laptop"></i> Linux';
                                            } else {
                                                echo '<i class="fas fa-question-circle"></i> Otro';
                                            }
                                            
                                            // Intentar identificar el navegador
                                            echo ' - ';
                                            if (strpos($ua, 'Chrome') !== false && strpos($ua, 'Edg') === false && strpos($ua, 'OPR') === false) {
                                                echo 'Chrome';
                                            } elseif (strpos($ua, 'Firefox') !== false) {
                                                echo 'Firefox';
                                            } elseif (strpos($ua, 'Safari') !== false && strpos($ua, 'Chrome') === false) {
                                                echo 'Safari';
                                            } elseif (strpos($ua, 'Edg') !== false) {
                                                echo 'Edge';
                                            } elseif (strpos($ua, 'OPR') !== false) {
                                                echo 'Opera';
                                            } else {
                                                echo 'Otro';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!$es_sesion_actual): ?>
                                                <form method="post" action="<?php echo BASE_URL; ?>/sesiones_activas/cerrar" onsubmit="return confirm('¿Seguro que desea cerrar esta sesión?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $datos['csrf_token']; ?>">
                                                    <input type="hidden" name="token_sesion" value="<?php echo $sesion['token']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-sign-out-alt"></i> Cerrar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php include_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>
</body>
</html>
