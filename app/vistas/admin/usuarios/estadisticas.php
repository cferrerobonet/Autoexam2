<?php
/**
 * Vista: Estadísticas de Usuarios - AUTOEXAM2
 */
?>

<!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inicio">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/usuarios">Usuarios</a></li>
                    <li class="breadcrumb-item active">Estadísticas</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">Estadísticas de Usuarios</h2>
                    <p class="text-muted mb-0">Análisis y métricas del sistema de usuarios</p>
                </div>
                <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
                </a>
            </div>

            <!-- Estadísticas Generales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Total Usuarios</h5>
                                    <h2 class="mb-0"><?= $estadisticas['total_usuarios'] ?? 0 ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Activos</h5>
                                    <h2 class="mb-0"><?= $estadisticas['usuarios_activos'] ?? 0 ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Inactivos</h5>
                                    <h2 class="mb-0"><?= $estadisticas['usuarios_inactivos'] ?? 0 ?></h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-times fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title">Roles</h5>
                                    <h2 class="mb-0">3</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-tag fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Distribución por Roles -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>Distribución por Roles
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($estadisticas['por_rol'])): ?>
                                <canvas id="rolesChart" width="400" height="200"></canvas>
                                
                                <div class="mt-3">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="text-danger">
                                                <i class="fas fa-user-shield fa-2x"></i>
                                                <div class="mt-2">
                                                    <strong><?= $estadisticas['por_rol']['admin'] ?? 0 ?></strong>
                                                    <div>Admins</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-warning">
                                                <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                                <div class="mt-2">
                                                    <strong><?= $estadisticas['por_rol']['profesor'] ?? 0 ?></strong>
                                                    <div>Profesores</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-info">
                                                <i class="fas fa-user-graduate fa-2x"></i>
                                                <div class="mt-2">
                                                    <strong><?= $estadisticas['por_rol']['alumno'] ?? 0 ?></strong>
                                                    <div>Alumnos</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-line me-2"></i>Actividad Reciente (30 días)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($actividad_reciente)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Acción</th>
                                                <th>Fecha</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($actividad_reciente as $actividad): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?= htmlspecialchars($actividad['accion']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($actividad['fecha_dia'])) ?></td>
                                                    <td class="text-end">
                                                        <strong><?= $actividad['total'] ?></strong>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <h6>No hay actividad reciente</h6>
                                    <p class="text-muted">No se registraron actividades en los últimos 30 días.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de distribución por roles
<?php if (isset($estadisticas['por_rol'])): ?>
const ctx = document.getElementById('rolesChart').getContext('2d');
const rolesChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Administradores', 'Profesores', 'Alumnos'],
        datasets: [{
            data: [
                <?= $estadisticas['por_rol']['admin'] ?? 0 ?>,
                <?= $estadisticas['por_rol']['profesor'] ?? 0 ?>,
                <?= $estadisticas['por_rol']['alumno'] ?? 0 ?>
            ],
            backgroundColor: [
                '#dc3545',
                '#ffc107',
                '#17a2b8'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>
