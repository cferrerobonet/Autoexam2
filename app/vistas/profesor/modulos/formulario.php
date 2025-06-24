<?php
/**
 * Vista de formulario de módulos para profesor
 * AUTOEXAM2 - Siguiendo el patrón de usuarios y cursos
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fs-3 fw-bold text-dark mb-2">
                <i class="fas fa-puzzle-piece text-primary me-2"></i>
                <?= $datos['titulo'] ?>
            </h1>
            <p class="text-muted mb-0">Complete los campos para crear un nuevo módulo</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver a módulos
            </a>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Formulario -->    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Información del módulo
                    </h5>
                </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/modulos/crear" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                    
                    <div class="mb-4">
                        <label for="titulo" class="form-label">
                            <i class="fas fa-tag me-1"></i>Título del módulo <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="titulo" 
                               name="titulo" 
                               required 
                               maxlength="150"
                               placeholder="Ej: Matemáticas, Física, Literatura..."
                               value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Nombre identificativo del módulo (máximo 150 caracteres)
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="descripcion" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Descripción
                        </label>
                        <textarea class="form-control" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="4" 
                                  placeholder="Describe brevemente el contenido y objetivos del módulo..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Información adicional sobre el módulo (opcional)
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-chalkboard-teacher me-1"></i>Profesor asignado
                        </label>
                        <div class="card bg-light">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 48px; height: 48px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($_SESSION['apellidos'] . ', ' . $_SESSION['nombre']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($_SESSION['correo']) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Como profesor, serás asignado automáticamente a este módulo
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-book-open me-1"></i>Cursos <span class="text-danger">*</span>
                        </label>
                        <div class="border rounded p-3 bg-light">
                            <div class="form-text mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Seleccione sus cursos donde se impartirá este módulo
                            </div>
                            <?php if (!empty($datos['cursos'])): ?>
                                <div class="row">
                                    <?php foreach ($datos['cursos'] as $curso): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="cursos[]" 
                                                       value="<?= $curso['id_curso'] ?>" 
                                                       id="curso_<?= $curso['id_curso'] ?>"
                                                       <?= (isset($_POST['cursos']) && in_array($curso['id_curso'], $_POST['cursos'])) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="curso_<?= $curso['id_curso'] ?>">
                                                    <strong><?= htmlspecialchars($curso['nombre_curso']) ?></strong>
                                                    <?php if (!empty($curso['descripcion'])): ?>
                                                        <small class="text-muted d-block"><?= htmlspecialchars(mb_substr($curso['descripcion'], 0, 40)) ?><?= mb_strlen($curso['descripcion']) > 40 ? '...' : '' ?></small>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    No tienes cursos asignados. <a href="<?= BASE_URL ?>/cursos" class="alert-link">Contacta con el administrador</a> para que te asigne cursos.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Crear módulo
                        </button>
                        <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Información adicional -->
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>Información importante
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>El título debe ser único e identificativo</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Podrás crear exámenes dentro de este módulo</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Organiza tus materias por módulos</small>
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Podrás editar esta información después</small>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Estadísticas -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="fs-4 fw-bold text-primary">0</div>
                            <small class="text-muted">Módulos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-bold text-success">0</div>
                        <small class="text-muted">Exámenes</small>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1"></i>
                        Los módulos te ayudan a organizar tus exámenes por materias
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación en tiempo real del formulario
    const form = document.querySelector('form');
    const titulo = document.getElementById('titulo');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    function validarFormulario() {
        const tituloValido = titulo.value.trim().length > 0;
        
        if (tituloValido) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        }
        
        // Validar título
        if (titulo.value.trim().length > 0) {
            titulo.classList.remove('is-invalid');
            titulo.classList.add('is-valid');
        } else if (titulo.value.length > 0) {
            titulo.classList.remove('is-valid');
            titulo.classList.add('is-invalid');
        }
    }
    
    titulo.addEventListener('input', validarFormulario);
    
    // Validación inicial
    validarFormulario();
    
    // Prevenir envío si hay errores
    form.addEventListener('submit', function(e) {
        if (titulo.value.trim().length === 0) {
            e.preventDefault();
            titulo.focus();
            titulo.classList.add('is-invalid');
        }
    });
    
    // Habilitar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
