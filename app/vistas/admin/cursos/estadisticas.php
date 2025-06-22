<?php
/**
 * Vista de Estadísticas de Cursos - AUTOEXAM2
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Las estadísticas vienen del controlador en la variable $datos['estadisticas']
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i> Estadísticas de Cursos</h2>
        <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $estadisticas['total_cursos'] ?></h4>
                            <small>Total Cursos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $estadisticas['cursos_activos'] ?></h4>
                            <small>Cursos Activos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-pause-circle fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $estadisticas['cursos_inactivos'] ?></h4>
                            <small>Cursos Inactivos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-percentage fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0">
                                <?= ($estadisticas['total_cursos'] > 0) ? 
                                    round(($estadisticas['cursos_activos'] / $estadisticas['total_cursos']) * 100, 1) : 0 ?>%
                            </h4>
                            <small>Tasa de Actividad</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de cursos por profesor -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-tie me-2"></i>Cursos por Profesor
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="grafico-profesores" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabla de estadísticas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Top Profesores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Profesor</th>
                                    <th>Cursos</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticas['por_profesor'] as $prof): ?>
                                <tr>
                                    <td><?= htmlspecialchars($prof['profesor']) ?></td>
                                    <td><?= $prof['cursos'] ?></td>
                                    <td><?= round(($prof['cursos'] / $estadisticas['total_cursos']) * 100, 1) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de evolución temporal -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Evolución Mensual
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="grafico-temporal" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de cursos por profesor
    const ctxProfesores = document.getElementById('grafico-profesores').getContext('2d');
    new Chart(ctxProfesores, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($estadisticas['por_profesor'], 'profesor')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($estadisticas['por_profesor'], 'cursos')) ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico temporal
    const ctxTemporal = document.getElementById('grafico-temporal').getContext('2d');
    new Chart(ctxTemporal, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($estadisticas['registros_por_mes'], 'mes')) ?>,
            datasets: [{
                label: 'Cursos Creados',
                data: <?= json_encode(array_column($estadisticas['registros_por_mes'], 'total')) ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
