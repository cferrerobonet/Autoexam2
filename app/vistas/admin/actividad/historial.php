<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-history"></i> Historial de Actividad</h1>
                    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>

                <!-- Mensajes de estado -->
                <?php if (isset($_SESSION['exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">
                                            <i class="fas fa-chart-line text-primary me-2"></i>
                                            Total de Actividades Registradas
                                        </h5>
                                        <p class="card-text text-muted mb-0">
                                            Se muestran las <?= count($datos['actividades']) ?> actividades más recientes de un total de <?= $datos['total_actividades'] ?>
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <?= number_format($datos['total_actividades']) ?> registros
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de actividad -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>
                            Registro Detallado de Actividades
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($datos['actividades'])): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Usuario</th>
                                            <th>Acción</th>
                                            <th>Descripción</th>
                                            <th>Módulo</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($datos['actividades'] as $actividad): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($actividad['fecha'])) ?><br>
                                                    <?= date('H:i:s', strtotime($actividad['fecha'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (!empty($actividad['nombre'])): ?>
                                                    <strong><?= htmlspecialchars($actividad['nombre'] . ' ' . $actividad['apellidos']) ?></strong>
                                                <?php else: ?>
                                                    <span class="text-muted">Sistema</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= ucfirst(str_replace('_', ' ', $actividad['accion'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($actividad['descripcion']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= ucfirst($actividad['modulo']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted font-monospace">
                                                    <?= htmlspecialchars($actividad['ip']) ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($datos['total_paginas'] > 1): ?>
                            <div class="card-footer bg-white">
                                <nav aria-label="Paginación del historial">
                                    <ul class="pagination justify-content-center mb-0">
                                        <!-- Anterior -->
                                        <?php if ($datos['pagina_actual'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/actividad?pagina=<?= $datos['pagina_actual'] - 1 ?>">
                                                <i class="fas fa-chevron-left"></i> Anterior
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <!-- Páginas -->
                                        <?php
                                        $inicio = max(1, $datos['pagina_actual'] - 2);
                                        $fin = min($datos['total_paginas'], $datos['pagina_actual'] + 2);
                                        ?>
                                        
                                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item <?= $i == $datos['pagina_actual'] ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= BASE_URL ?>/actividad?pagina=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                        <?php endfor; ?>

                                        <!-- Siguiente -->
                                        <?php if ($datos['pagina_actual'] < $datos['total_paginas']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/actividad?pagina=<?= $datos['pagina_actual'] + 1 ?>">
                                                Siguiente <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Página <?= $datos['pagina_actual'] ?> de <?= $datos['total_paginas'] ?> 
                                        (<?= number_format($datos['total_actividades']) ?> registros en total)
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay actividad registrada</h5>
                                <p class="text-muted">Aún no se han registrado actividades en el sistema.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>
</body>
</html>
