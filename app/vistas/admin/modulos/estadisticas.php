<?php
/**
 * Vista de Estadísticas de Módulos - AUTOEXAM2
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Obtener estadísticas (desde la variable pasada desde el controlador)
$estadisticas = $datos['estadisticas'] ?? [];
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i> Estadísticas de Módulos</h2>
        <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-puzzle-piece fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $estadisticas['total_modulos'] ?? 0 ?></h4>
                            <small>Total Módulos</small>
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
                            <h4 class="mb-0"><?= $estadisticas['modulos_activos'] ?? 0 ?></h4>
                            <small>Módulos Activos</small>
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
                            <h4 class="mb-0"><?= $estadisticas['modulos_inactivos'] ?? 0 ?></h4>
                            <small>Módulos Inactivos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book fa-2x me-3"></i>
                        <div>
                            <h4 class="mb-0"><?= $estadisticas['cursos_con_modulos'] ?? 0 ?></h4>
                            <small>Cursos con Módulos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de módulos por curso -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Módulos por Curso
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['por_curso'])): ?>
                        <canvas id="grafico-cursos" width="400" height="200"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>No hay datos disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabla de estadísticas -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Top Cursos con más Módulos
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['por_curso'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Módulos</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = $estadisticas['total_modulos'] ?? 1;
                                    foreach (array_slice($estadisticas['por_curso'], 0, 5) as $curso): 
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($curso['curso'] ?? 'Sin nombre') ?></td>
                                        <td><?= $curso['modulos'] ?? 0 ?></td>
                                        <td><?= round((($curso['modulos'] ?? 0) / $total) * 100, 1) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <p>No hay datos disponibles</p>
                        </div>
                    <?php endif; ?>
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
                        <i class="fas fa-chart-line me-2"></i>Evolución Mensual de Creación
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($estadisticas['registros_por_mes'])): ?>
                        <canvas id="grafico-temporal" width="800" height="300"></canvas>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>No hay datos de evolución temporal disponibles</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Información Adicional
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">Promedio de módulos por curso:</dt>
                        <dd class="col-sm-6">
                            <?php 
                            $promedio = ($estadisticas['cursos_con_modulos'] ?? 0) > 0 ? 
                                round(($estadisticas['total_modulos'] ?? 0) / ($estadisticas['cursos_con_modulos'] ?? 1), 1) : 
                                0;
                            echo $promedio;
                            ?>
                        </dd>
                        
                        <dt class="col-sm-6">Curso con más módulos:</dt>
                        <dd class="col-sm-6">
                            <?php
                            if (!empty($estadisticas['por_curso'])) {
                                $topCurso = $estadisticas['por_curso'][0];
                                echo htmlspecialchars($topCurso['curso']) . ' (' . $topCurso['modulos'] . ')';
                            } else {
                                echo 'No disponible';
                            }
                            ?>
                        </dd>
                        
                        <dt class="col-sm-6">Tasa de actividad:</dt>
                        <dd class="col-sm-6">
                            <?php 
                            $total = $estadisticas['total_modulos'] ?? 0;
                            $activos = $estadisticas['modulos_activos'] ?? 0;
                            $tasa = $total > 0 ? round(($activos / $total) * 100, 1) : 0;
                            echo $tasa . '%';
                            ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/modulos/exportar" class="btn btn-outline-success">
                            <i class="fas fa-download me-2"></i>Exportar Todos los Módulos
                        </a>
                        <a href="<?= BASE_URL ?>/modulos/importar" class="btn btn-outline-primary">
                            <i class="fas fa-upload me-2"></i>Importar Módulos
                        </a>
                        <a href="<?= BASE_URL ?>/modulos/nuevo" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-2"></i>Crear Nuevo Módulo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($estadisticas['por_curso'])): ?>
    // Gráfico de módulos por curso
    const ctxCursos = document.getElementById('grafico-cursos').getContext('2d');
    new Chart(ctxCursos, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($estadisticas['por_curso'], 'curso')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($estadisticas['por_curso'], 'modulos')) ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
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
    <?php endif; ?>

    <?php if (!empty($estadisticas['registros_por_mes'])): ?>
    // Gráfico temporal
    const ctxTemporal = document.getElementById('grafico-temporal').getContext('2d');
    new Chart(ctxTemporal, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($estadisticas['registros_por_mes'], 'mes')) ?>,
            datasets: [{
                label: 'Módulos Creados',
                data: <?= json_encode(array_column($estadisticas['registros_por_mes'], 'total')) ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
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
    <?php endif; ?>
});
</script>
