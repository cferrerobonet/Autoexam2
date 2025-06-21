<?php
require_once __DIR__ . '/../../utilidades/sesion.php';

// Verificar que el usuario esté autenticado y sea alumno
if (!Sesion::esta_iniciada() || Sesion::obtener_rol() !== 'alumno') {
    header('Location: /autenticacion/iniciar-sesion');
    exit;
}

$usuario_id = Sesion::obtener_usuario_id();
$nombre_usuario = Sesion::obtener_nombre_completo();

// Obtener el historial completo desde el controlador
$historial = $historial ?? [];
$filtros = $filtros ?? [];
$paginacion = $paginacion ?? ['pagina' => 1, 'total_paginas' => 1, 'total_registros' => 0];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Exámenes - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/publico/recursos/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../parciales/header_alumno.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../parciales/sidebar_alumno.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-clock-history"></i>
                        Historial de Exámenes
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?= BASE_URL ?>/alumno/examenes" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i>
                                Volver a Exámenes
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel"></i>
                            Filtros de Búsqueda
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="<?= BASE_URL ?>/examenes/historial-examenes" class="row g-3">
                            <div class="col-md-3">
                                <label for="curso_id" class="form-label">Curso</label>
                                <select name="curso_id" id="curso_id" class="form-select">
                                    <option value="">Todos los cursos</option>
                                    <?php if (isset($cursos) && is_array($cursos)): ?>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= htmlspecialchars($curso['id']) ?>" 
                                                    <?= ($filtros['curso_id'] ?? '') == $curso['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($curso['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select name="estado" id="estado" class="form-select">
                                    <option value="">Todos los estados</option>
                                    <option value="completado" <?= ($filtros['estado'] ?? '') == 'completado' ? 'selected' : '' ?>>
                                        Completado
                                    </option>
                                    <option value="en_progreso" <?= ($filtros['estado'] ?? '') == 'en_progreso' ? 'selected' : '' ?>>
                                        En Progreso
                                    </option>
                                    <option value="no_iniciado" <?= ($filtros['estado'] ?? '') == 'no_iniciado' ? 'selected' : '' ?>>
                                        No Iniciado
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label for="fecha_desde" class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" 
                                       value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="fecha_hasta" class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" 
                                       value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i>
                                        Filtrar
                                    </button>
                                    <a href="<?= BASE_URL ?>/examenes/historial-examenes" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <?php if (!empty($estadisticas)): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-check-circle text-success fs-1"></i>
                                <h5 class="card-title">Completados</h5>
                                <p class="card-text fs-4"><?= $estadisticas['completados'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-clock text-warning fs-1"></i>
                                <h5 class="card-title">En Progreso</h5>
                                <p class="card-text fs-4"><?= $estadisticas['en_progreso'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-star text-primary fs-1"></i>
                                <h5 class="card-title">Promedio</h5>
                                <p class="card-text fs-4"><?= number_format($estadisticas['promedio'] ?? 0, 1) ?>%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-graph-up text-info fs-1"></i>
                                <h5 class="card-title">Total</h5>
                                <p class="card-text fs-4"><?= $estadisticas['total'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Listado de exámenes -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Historial Completo
                            <?php if ($paginacion['total_registros'] > 0): ?>
                                <span class="badge bg-secondary"><?= $paginacion['total_registros'] ?> registros</span>
                            <?php endif; ?>
                        </h5>
                        
                        <?php if (!empty($historial)): ?>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportarHistorial()">
                                <i class="bi bi-download"></i>
                                Exportar
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($historial)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <h4 class="text-muted">No hay exámenes en el historial</h4>
                                <p class="text-muted">
                                    <?php if (!empty($filtros) && array_filter($filtros)): ?>
                                        No se encontraron exámenes con los filtros aplicados.
                                        <br>
                                        <a href="<?= BASE_URL ?>/examenes/historial-examenes" class="btn btn-outline-primary mt-2">
                                            Ver todos los exámenes
                                        </a>
                                    <?php else: ?>
                                        Aún no has realizado ningún examen.
                                        <br>
                                        <a href="<?= BASE_URL ?>/alumno/examenes" class="btn btn-primary mt-2">
                                            Ver exámenes disponibles
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Examen</th>
                                            <th>Curso</th>
                                            <th>Fecha Realización</th>
                                            <th>Estado</th>
                                            <th>Calificación</th>
                                            <th>Tiempo</th>
                                            <th>Intentos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial as $intento): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($intento['titulo_examen']) ?></div>
                                                <?php if (!empty($intento['descripcion_examen'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($intento['descripcion_examen']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= htmlspecialchars($intento['nombre_curso']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($intento['fecha_inicio']): ?>
                                                    <div><?= date('d/m/Y', strtotime($intento['fecha_inicio'])) ?></div>
                                                    <small class="text-muted"><?= date('H:i', strtotime($intento['fecha_inicio'])) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $estado_class = '';
                                                $estado_texto = '';
                                                switch ($intento['estado']) {
                                                    case 'completado':
                                                        $estado_class = 'bg-success';
                                                        $estado_texto = 'Completado';
                                                        break;
                                                    case 'en_progreso':
                                                        $estado_class = 'bg-warning';
                                                        $estado_texto = 'En Progreso';
                                                        break;
                                                    case 'no_iniciado':
                                                        $estado_class = 'bg-secondary';
                                                        $estado_texto = 'No Iniciado';
                                                        break;
                                                    default:
                                                        $estado_class = 'bg-light text-dark';
                                                        $estado_texto = ucfirst($intento['estado']);
                                                }
                                                ?>
                                                <span class="badge <?= $estado_class ?>"><?= $estado_texto ?></span>
                                            </td>
                                            <td>
                                                <?php if ($intento['calificacion'] !== null): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold me-2"><?= number_format($intento['calificacion'], 1) ?>%</span>
                                                        <?php
                                                        $calificacion = floatval($intento['calificacion']);
                                                        if ($calificacion >= 70):
                                                        ?>
                                                            <i class="bi bi-check-circle text-success"></i>
                                                        <?php elseif ($calificacion >= 50): ?>
                                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-x-circle text-danger"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($intento['tiempo_utilizado']): ?>
                                                    <?php
                                                    $tiempo = $intento['tiempo_utilizado'];
                                                    $horas = floor($tiempo / 3600);
                                                    $minutos = floor(($tiempo % 3600) / 60);
                                                    $segundos = $tiempo % 60;
                                                    
                                                    if ($horas > 0) {
                                                        echo sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
                                                    } else {
                                                        echo sprintf('%02d:%02d', $minutos, $segundos);
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?= $intento['numero_intento'] ?? 1 ?>/<?= $intento['max_intentos'] ?? '∞' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($intento['estado'] === 'completado'): ?>
                                                        <a href="<?= BASE_URL ?>/examenes/resultado-examen/<?= $intento['id'] ?>" 
                                                           class="btn btn-outline-primary btn-sm" 
                                                           title="Ver resultado">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    <?php elseif ($intento['estado'] === 'en_progreso'): ?>
                                                        <a href="<?= BASE_URL ?>/examenes/realizar-examen/<?= $intento['examen_id'] ?>" 
                                                           class="btn btn-outline-warning btn-sm" 
                                                           title="Continuar examen">
                                                            <i class="bi bi-play-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($intento['estado'] === 'no_iniciado' && 
                                                              ($intento['numero_intento'] < $intento['max_intentos'] || 
                                                               $intento['max_intentos'] == 0)): ?>
                                                        <a href="<?= BASE_URL ?>/examenes/realizar-examen/<?= $intento['examen_id'] ?>" 
                                                           class="btn btn-outline-success btn-sm" 
                                                           title="Realizar examen">
                                                            <i class="bi bi-play-fill"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($paginacion['total_paginas'] > 1): ?>
                            <nav aria-label="Navegación del historial">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $paginacion['pagina'] <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['pagina' => $paginacion['pagina'] - 1])) ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                    
                                    <?php
                                    $inicio = max(1, $paginacion['pagina'] - 2);
                                    $fin = min($paginacion['total_paginas'], $paginacion['pagina'] + 2);
                                    ?>
                                    
                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item <?= $i == $paginacion['pagina'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['pagina' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?= $paginacion['pagina'] >= $paginacion['total_paginas'] ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?<?= http_build_query(array_merge($filtros, ['pagina' => $paginacion['pagina'] + 1])) ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportarHistorial() {
            const params = new URLSearchParams(window.location.search);
            params.set('exportar', 'csv');
            window.location.href = '<?= BASE_URL ?>/examenes/historial-examenes?' + params.toString();
        }
        
        // Auto-submit del formulario cuando cambian los filtros de select
        document.getElementById('curso_id').addEventListener('change', function() {
            this.form.submit();
        });
        
        document.getElementById('estado').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
