<?php
/**
 * Vista para ver detalles de un curso (Administrador)
 * AUTOEXAM2 - 16/06/2025
 */

// Variables para la vista
$id_curso = $curso['id_curso'];
$nombre_curso = $curso['nombre_curso'];
$descripcion = $curso['descripcion'];
$activo = $curso['activo'] == 1;
$profesor_nombre = $curso['nombre_profesor'] . ' ' . $curso['apellidos_profesor'];
$total_alumnos = count($alumnos);
?>

<!-- Título de la página -->
<div class="container-fluid px-4 py-4">
    <h1 class="mt-2 mb-4">
        <i class="fas fa-book me-2"></i> Información del Curso
    </h1>

    <!-- Alertas y mensajes -->
    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['mensaje'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['tipo_mensaje']);
    endif; 
    ?>

    <div class="row">
        <!-- Información general -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-info-circle me-1"></i> Datos del curso
                    </div>
                    <span class="badge <?= $activo ? 'bg-success' : 'bg-danger' ?>">
                        <?= $activo ? 'Activo' : 'Inactivo' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h4><?= htmlspecialchars($nombre_curso) ?></h4>
                        <?php if(!empty($descripcion)): ?>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($descripcion)) ?></p>
                        <?php else: ?>
                        <p class="text-muted fst-italic">Sin descripción</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 fw-bold">Identificador del curso</p>
                            <p><?= $id_curso ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="mb-1 fw-bold">Profesor asignado</p>
                            <p><?= htmlspecialchars($profesor_nombre) ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col mb-3">
                            <p class="mb-1 fw-bold">Alumnos matriculados</p>
                            <p><?= $total_alumnos ?> alumnos</p>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="d-flex gap-2 mt-4">
                        <a href="<?= BASE_URL ?>/cursos" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Volver a cursos
                        </a>
                        <a href="<?= BASE_URL ?>/cursos/editar?id=<?= $id_curso ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Editar curso
                        </a>
                        <a href="<?= BASE_URL ?>/cursos/asignarAlumnos?id=<?= $id_curso ?>" class="btn btn-info">
                            <i class="fas fa-user-graduate me-1"></i> Gestionar alumnos
                        </a>
                        <?php if($_SESSION['rol'] === 'profesor'): ?>
                        <a href="<?= BASE_URL ?>/cursos/alumnos/<?= $id_curso ?>" class="btn btn-success">
                            <i class="fas fa-users me-1"></i> Editar alumnos
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen y estadísticas -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i> Resumen
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Total de Alumnos</h6>
                                        </div>
                                        <div>
                                            <h3 class="mb-0"><?= $total_alumnos ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="card bg-info text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Módulos</h6>
                                        </div>
                                        <div>
                                            <h3 class="mb-0">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Exámenes</h6>
                                        </div>
                                        <div>
                                            <h3 class="mb-0">0</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de alumnos -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users me-1"></i> Alumnos matriculados
            </div>
            <div>
                <a href="<?= BASE_URL ?>/cursos/asignarAlumnos?id=<?= $id_curso ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i> Gestionar alumnos
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if($total_alumnos > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Fecha asignación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($alumnos as $alumno): ?>
                        <tr>
                            <td><?= $alumno['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombre']) ?></td>
                            <td><?= htmlspecialchars($alumno['correo']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($alumno['fecha_asignacion'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-1"></i> No hay alumnos asignados a este curso.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
