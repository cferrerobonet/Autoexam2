<?php
/**
 * Vista de Listar Alumnos - AUTOEXAM2 (Rol Profesor)
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_profesor.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-users"></i> Alumnos del curso: <?= htmlspecialchars($datos['curso']['nombre']) ?></h1>
                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a cursos
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

                <!-- Lista de alumnos -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate text-primary me-2"></i> 
                                Alumnos asignados
                            </h5>
                            <span class="badge bg-primary text-white">
                                <?= count($datos['alumnos']) ?> alumnos
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($datos['alumnos'])): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> No hay alumnos asignados a este curso.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Apellidos</th>
                                            <th scope="col">Correo</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($datos['alumnos'] as $index => $alumno): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($alumno['nombre']) ?></td>
                                            <td><?= htmlspecialchars($alumno['apellidos']) ?></td>
                                            <td><?= htmlspecialchars($alumno['correo']) ?></td>
                                            <td>
                                                <?php if ($alumno['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= BASE_URL ?>/usuarios/editar/<?= $alumno['id_usuario'] ?>" 
                                                       class="btn btn-outline-primary" title="Editar usuario">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
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
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/comunes/pie.php'; ?>
</body>
</html>
