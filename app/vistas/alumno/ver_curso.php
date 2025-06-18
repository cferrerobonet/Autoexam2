<?php
/**
 * Vista para ver detalles de un curso (Alumno)
 * AUTOEXAM2 - 16/06/2025
 */

// Variables para la vista
$id_curso = $curso['id_curso'];
$nombre_curso = $curso['nombre_curso'];
$descripcion = $curso['descripcion'];
$profesor_nombre = $curso['nombre_profesor'] . ' ' . $curso['apellidos_profesor'];
$total_alumnos = count($alumnos);
?>

<!-- Título de la página -->
<div class="container-fluid px-4 py-4">
    <h1 class="mt-2 mb-4">
        <i class="fas fa-book me-2"></i> <?= htmlspecialchars($nombre_curso) ?>
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

    <!-- Información general del curso -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i> Información del curso
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <?php if(!empty($descripcion)): ?>
                    <div class="mb-3">
                        <h5>Descripción:</h5>
                        <p><?= nl2br(htmlspecialchars($descripcion)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <h5>Profesor:</h5>
                        <p><?= htmlspecialchars($profesor_nombre) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Compañeros de clase:</h5>
                        <p><?= $total_alumnos - 1 ?> alumnos</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Datos del curso</h5>
                            <ul class="list-unstyled">
                                <li><strong>ID:</strong> <?= $id_curso ?></li>
                                <li><strong>Nombre:</strong> <?= htmlspecialchars($nombre_curso) ?></li>
                                <li><strong>Estado:</strong> 
                                    <?php if($curso['activo'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón de volver -->
            <div class="mt-3">
                <a href="<?= BASE_URL ?>/cursos/misCursos" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver a mis cursos
                </a>
            </div>
        </div>
    </div>
    
    <!-- Módulos del curso (placeholder para futura implementación) -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group me-1"></i> Contenidos del curso
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-1"></i> Los contenidos del curso estarán disponibles próximamente.
            </div>
        </div>
    </div>
    
    <!-- Exámenes próximos (placeholder para futura implementación) -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-clipboard-list me-1"></i> Exámenes
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-1"></i> No hay exámenes disponibles actualmente.
            </div>
        </div>
    </div>
</div>
