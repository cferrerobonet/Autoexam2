<?php
/**
 * Vista para listar los cursos del alumno
 * AUTOEXAM2 - 16/06/2025
 */
?>

<!-- Título de la página -->
<div class="container-fluid px-4 py-4">
    <h1 class="mt-2 mb-4">
        <i class="fas fa-book me-2"></i> Mis Cursos
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

    <?php if(count($cursos) > 0): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach($cursos as $curso): ?>
        <div class="col">
            <div class="card h-100 <?= $curso['activo'] == 0 ? 'border-danger' : '' ?>">
                <div class="card-header <?= $curso['activo'] == 0 ? 'bg-danger text-white' : 'bg-primary text-white' ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i> <?= htmlspecialchars($curso['nombre_curso']) ?>
                        </h5>
                        <?php if($curso['activo'] == 0): ?>
                        <span class="badge bg-light text-danger">Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(!empty($curso['descripcion'])): ?>
                    <p class="card-text"><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
                    <?php else: ?>
                    <p class="card-text text-muted fst-italic">Sin descripción</p>
                    <?php endif; ?>
                    
                    <div class="mt-3">
                        <p class="mb-1"><strong>Profesor:</strong> <?= htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellidos_profesor']) ?></p>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $curso['id_curso'] ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye me-1"></i> Ver detalles
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> No estás matriculado en ningún curso actualmente.
    </div>
    <?php endif; ?>
</div>
