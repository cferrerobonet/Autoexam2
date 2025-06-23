<?php
/**
 * Vista de Gestión del Banco de Preguntas - Admin - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="py-4">
            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">🏦 Banco de Preguntas</h1>
                    <p class="text-muted">Gestiona y reutiliza preguntas para exámenes</p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Pregunta
                    </a>
                    <div class="btn-group ms-2">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/banco-preguntas/exportar?formato=excel">
                                <i class="fas fa-file-excel"></i> Excel (XLSX)
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/banco-preguntas/exportar?formato=pdf">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i> Filtros
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas las categorías</option>
                                <option value="matematicas" <?= isset($_GET['categoria']) && $_GET['categoria'] == 'matematicas' ? 'selected' : '' ?>>Matemáticas</option>
                                <option value="ciencias" <?= isset($_GET['categoria']) && $_GET['categoria'] == 'ciencias' ? 'selected' : '' ?>>Ciencias</option>
                                <option value="lenguaje" <?= isset($_GET['categoria']) && $_GET['categoria'] == 'lenguaje' ? 'selected' : '' ?>>Lenguaje</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="test" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'test' ? 'selected' : '' ?>>Test</option>
                                <option value="abierta" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'abierta' ? 'selected' : '' ?>>Abierta</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="dificultad" class="form-label">Dificultad</label>
                            <select class="form-select" id="dificultad" name="dificultad">
                                <option value="">Todas</option>
                                <option value="facil" <?= isset($_GET['dificultad']) && $_GET['dificultad'] == 'facil' ? 'selected' : '' ?>>Fácil</option>
                                <option value="media" <?= isset($_GET['dificultad']) && $_GET['dificultad'] == 'media' ? 'selected' : '' ?>>Media</option>
                                <option value="dificil" <?= isset($_GET['dificultad']) && $_GET['dificultad'] == 'dificil' ? 'selected' : '' ?>>Difícil</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="publica" class="form-label">Visibilidad</label>
                            <select class="form-select" id="publica" name="publica">
                                <option value="">Todas</option>
                                <option value="si" <?= isset($_GET['publica']) && $_GET['publica'] == 'si' ? 'selected' : '' ?>>Públicas</option>
                                <option value="no" <?= isset($_GET['publica']) && $_GET['publica'] == 'no' ? 'selected' : '' ?>>Privadas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="busqueda" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="busqueda" name="busqueda" 
                                   placeholder="Buscar en enunciados..." 
                                   value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de preguntas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Preguntas del Banco
                        <?php if (isset($preguntas) && count($preguntas) > 0): ?>
                            <span class="badge bg-primary ms-2"><?= count($preguntas) ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (isset($preguntas) && count($preguntas) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Enunciado</th>
                                        <th>Tipo</th>
                                        <th>Categoría</th>
                                        <th>Dificultad</th>
                                        <th>Visibilidad</th>
                                        <th>Autor</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preguntas as $pregunta): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?= htmlspecialchars(substr($pregunta['enunciado'], 0, 100)) ?>...
                                                </div>
                                                <?php if (!empty($pregunta['etiquetas'])): ?>
                                                    <div class="mt-1">
                                                        <?php 
                                                        $etiquetas = explode(',', $pregunta['etiquetas']);
                                                        foreach ($etiquetas as $etiqueta): 
                                                        ?>
                                                            <span class="badge bg-light text-dark me-1"><?= htmlspecialchars(trim($etiqueta)) ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $pregunta['tipo'] == 'test' ? 'info' : 'warning' ?>">
                                                    <?= ucfirst($pregunta['tipo']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($pregunta['categoria'] ?? 'Sin categoría') ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    ($pregunta['dificultad'] ?? 'media') == 'facil' ? 'success' : 
                                                    (($pregunta['dificultad'] ?? 'media') == 'media' ? 'warning' : 'danger') 
                                                ?>">
                                                    <?= ucfirst($pregunta['dificultad'] ?? 'Media') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $pregunta['publica'] ? 'success' : 'secondary' ?>">
                                                    <?= $pregunta['publica'] ? 'Pública' : 'Privada' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($pregunta['nombre_profesor'] ?? 'Sistema') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($pregunta['fecha_creacion'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" 
                                                               href="<?= BASE_URL ?>/banco-preguntas/editar/<?= $pregunta['id_pregunta'] ?>">
                                                                <i class="fas fa-edit"></i> Editar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="verDetalle(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-eye"></i> Ver detalle
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" 
                                                               onclick="duplicarAExamen(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-copy"></i> Usar en examen
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" 
                                                               onclick="eliminarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                                <i class="fas fa-trash"></i> Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay preguntas en el banco</h5>
                            <p class="text-muted">Crea tu primera pregunta para comenzar a usar el banco de preguntas.</p>
                            <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Primera Pregunta
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modales y scripts -->
        <script>
        function verDetalle(idPregunta) {
            // TODO: Implementar modal de detalle
            alert('Ver detalle: ' + idPregunta);
        }

        function duplicarAExamen(idPregunta) {
            // TODO: Implementar funcionalidad
            alert('Duplicar a examen: ' + idPregunta);
        }

        function eliminarPregunta(idPregunta) {
            if (confirm('¿Está seguro de que desea eliminar esta pregunta?')) {
                // TODO: Implementar eliminación
                alert('Eliminar: ' + idPregunta);
            }
        }
        </script>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>
</body>
</html>
