<?php
/**
 * Vista de Crear/Editar Curso - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Determinar si es edición o creación
$es_edicion = isset($curso) && !empty($curso);

// Recuperar datos del formulario en caso de error
$datos_form = isset($_SESSION['datos_form']) ? $_SESSION['datos_form'] : [];
$errores = isset($_SESSION['errores']) ? $_SESSION['errores'] : [];

// Limpiar datos de sesión
unset($_SESSION['datos_form']);
unset($_SESSION['errores']);

// Definir valores a mostrar en el formulario
$id_curso = $es_edicion ? $curso['id_curso'] : '';
$nombre_curso = $es_edicion ? $curso['nombre_curso'] : 
               (isset($datos_form['nombre_curso']) ? $datos_form['nombre_curso'] : '');
$descripcion = $es_edicion ? $curso['descripcion'] : 
              (isset($datos_form['descripcion']) ? $datos_form['descripcion'] : '');
$activo = $es_edicion ? $curso['activo'] : 
         (isset($datos_form['activo']) ? $datos_form['activo'] : '1');
?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fs-3 fw-bold text-dark mb-2">
                        <i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?> text-primary me-2"></i>
                        <?= $es_edicion ? 'Editar Curso' : 'Crear Curso' ?>
                    </h1>
                    <p class="text-muted mb-0">
                        <?= $es_edicion ? 'Modifica los datos del curso seleccionado' : 'Complete los campos para crear un nuevo curso' ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Volver a la lista
                    </a>
                </div>
            </div>

            <!-- Mensajes de estado -->
            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['exito']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-info-circle me-2"></i><?= $_SESSION['mensaje'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php 
                unset($_SESSION['mensaje']); 
                unset($_SESSION['tipo_mensaje']);
                endif; 
            ?>

            <!-- Formulario -->
            <div class="card shadow-sm form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-book text-primary me-2"></i>Datos del Curso
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/cursos/<?= $es_edicion ? 'actualizar' : 'crear' ?>" id="formCrearCurso" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <?php if($es_edicion): ?>
                        <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <!-- Nombre del curso -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre_curso" class="form-label">
                                    <i class="fas fa-book-open"></i> Nombre del Curso <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-book"></i></span>
                                    <input type="text" class="form-control <?= isset($errores['nombre_curso']) ? 'is-invalid' : '' ?>" 
                                           id="nombre_curso" name="nombre_curso" 
                                           required maxlength="100" 
                                           value="<?= htmlspecialchars($nombre_curso) ?>"
                                           placeholder="Ej. 1º Bachillerato A">
                                </div>
                                <?php if(isset($errores['nombre_curso'])): ?>
                                <div class="invalid-feedback"><?= $errores['nombre_curso'] ?></div>
                                <?php endif; ?>
                                <div class="form-text">Introduce un nombre descriptivo para el curso</div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="descripcion" class="form-label">
                                    <i class="fas fa-align-left"></i> Descripción
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                    <textarea class="form-control" id="descripcion" name="descripcion" 
                                              rows="4" maxlength="500"
                                              placeholder="Descripción del curso (opcional)"><?= htmlspecialchars($descripcion) ?></textarea>
                                </div>
                                <div class="form-text">Información adicional sobre el curso (máximo 500 caracteres)</div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">
                                    <i class="fas fa-toggle-on"></i> Estado del Curso
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                           value="1" <?= $activo == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="activo">
                                        <span class="fw-bold text-success" id="estadoTexto">
                                            <?= $activo == '1' ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </label>
                                </div>
                                <div class="form-text">Los cursos inactivos no serán visibles para los alumnos</div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <hr class="my-4">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                    <div class="d-flex gap-2">
                                        <?php if($es_edicion): ?>
                                            <a href="<?= BASE_URL ?>/cursos/asignarAlumnos?id=<?= $id_curso ?>" class="btn btn-info">
                                                <i class="fas fa-user-graduate me-1"></i>Gestionar alumnos
                                            </a>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fas fa-save me-2"></i><?= $es_edicion ? 'Actualizar Curso' : 'Crear Curso' ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer small text-muted">
                    <span class="text-danger">*</span> Campos obligatorios
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript específico -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle del estado
        const switchActivo = document.getElementById('activo');
        const estadoTexto = document.getElementById('estadoTexto');
        
        if (switchActivo && estadoTexto) {
            switchActivo.addEventListener('change', function() {
                if (this.checked) {
                    estadoTexto.textContent = 'Activo';
                    estadoTexto.className = 'fw-bold text-success';
                } else {
                    estadoTexto.textContent = 'Inactivo';
                    estadoTexto.className = 'fw-bold text-danger';
                }
            });
        }

        // Validación del formulario
        const form = document.getElementById('formCrearCurso');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        }
    });
</script>
