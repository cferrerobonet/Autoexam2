<?php
/**
 * Vista de sesiones activas del usuario - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?> - AUTOEXAM2</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/estilos.css">
</head>
<body>
    <?php include_once APP_PATH . '/vistas/parciales/navbar.php'; ?>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1><i class="fas fa-user-clock me-2"></i><?= $datos['titulo'] ?></h1>
                <p class="text-muted">Gestione sus sesiones activas en diferentes dispositivos</p>
            </div>
            <div class="col-md-4 text-end">
                <!-- Botón para cerrar todas las otras sesiones -->
                <form method="post" action="<?= BASE_URL ?>/perfil/cerrarOtrasSesiones" class="d-inline" 
                      onsubmit="return confirm('¿Está seguro de cerrar todas sus otras sesiones activas?')">
                    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sign-out-alt"></i> Cerrar otras sesiones
                    </button>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['mensaje'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($datos['sesiones'])): ?>
                    <div class="alert alert-info">No tiene otras sesiones activas en este momento</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Última actividad</th>
                                    <th>IP</th>
                                    <th>Dispositivo</th>
                                    <th>Estado</th>
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
                                    <tr class="<?= $class ?>">
                                        <td><?= date('d/m/Y H:i', strtotime($sesion['fecha_inicio'])) ?></td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($sesion['ultima_actividad'])) ?>
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
                                        <td><?= htmlspecialchars($sesion['ip']) ?></td>
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
                                        <td>
                                            <?php if ($es_sesion_actual): ?>
                                                <span class="badge bg-primary">Sesión actual</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!$es_sesion_actual): ?>
                                                <form method="post" action="<?= BASE_URL ?>/perfil/cerrarSesion" 
                                                      onsubmit="return confirm('¿Seguro que desea cerrar esta sesión?')">
                                                    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                                    <input type="hidden" name="token_sesion" value="<?= $sesion['token'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-sign-out-alt"></i> Cerrar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <i class="fas fa-check-circle text-primary"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Por cuestiones de seguridad, las sesiones inactivas durante más de 24 horas se cierran automáticamente.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
