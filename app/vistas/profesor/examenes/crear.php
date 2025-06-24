<?php
/**
 * Vista de Crear/Editar Examen - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Determinar si es edición o creación
$es_edicion = isset($datos['examen']) && !empty($datos['examen']['id_examen']);
$titulo_pagina = $es_edicion ? 'Editar Examen' : 'Crear Examen';

// Extraer datos para compatibilidad con el resto del archivo
$examen = $datos['examen'] ?? [];
$cursos = $datos['cursos'] ?? [];
$modulos = $datos['modulos'] ?? [];
$csrf_token = $datos['csrf_token'] ?? $_SESSION['csrf_token'];
?>

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fs-3 fw-bold text-dark mb-2">
                        <i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?> text-primary me-2"></i>
                        <?= $titulo_pagina ?>
                    </h1>
                    <p class="text-muted mb-0">
                        <?= $es_edicion ? 'Modifica los datos del examen seleccionado' : 'Complete los campos para crear un nuevo examen' ?>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($es_edicion): ?>
                        <a href="<?= BASE_URL ?>/examenes/ver/<?= $examen['id_examen'] ?>" class="btn btn-outline-info">
                            <i class="fas fa-eye me-2"></i>Ver examen
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a exámenes
                    </a>
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

            <!-- Formulario -->
            <form method="POST" action="<?= BASE_URL ?>/examenes/<?= $es_edicion ? 'actualizar' : 'guardar' ?>" id="formExamen" enctype="multipart/form-data">
                <?php if ($es_edicion): ?>
                    <input type="hidden" name="id_examen" value="<?= $examen['id_examen'] ?>">
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <!-- Información Básica -->
                <div class="card shadow-sm form-card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle text-primary me-2"></i>Información Básica
                        </h5>
                    </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Título -->
                                <div class="col-md-8 mb-3">
                                    <label for="titulo" class="form-label">
                                        <i class="fas fa-heading"></i> Título del Examen <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                        <input type="text" class="form-control" id="titulo" name="titulo" 
                                               required maxlength="255"
                                               value="<?= htmlspecialchars($examen['titulo'] ?? $_POST['titulo'] ?? '') ?>"
                                               placeholder="Ingrese el título del examen">
                                    </div>
                                    <div class="form-text">Máximo 255 caracteres</div>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-4 mb-3">
                                    <label for="estado" class="form-label">
                                        <i class="fas fa-toggle-on"></i> Estado
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info"></i></span>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="borrador" <?= ($examen['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                            <option value="activo" <?= ($examen['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                            <option value="finalizado" <?= ($examen['estado'] ?? '') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">
                                    <i class="fas fa-align-left"></i> Descripción
                                </label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                          rows="3" maxlength="500"><?= htmlspecialchars($examen['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>
                                <div class="form-text">Información adicional sobre el examen (máximo 500 caracteres)</div>
                            </div>
                        </div>
                    </div>

                    <!-- Asignación -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-sitemap text-primary me-2"></i> Asignación
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Curso -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_curso" class="form-label">
                                        <i class="fas fa-book"></i> Curso <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <select class="form-select" id="id_curso" name="id_curso" required onchange="cargarModulos()">
                                            <option value="">Seleccionar curso</option>
                                            <?php if (!empty($cursos)): ?>
                                                <?php foreach ($cursos as $curso): ?>
                                                <option value="<?= $curso['id_curso'] ?>"
                                                        <?= ($examen['id_curso'] ?? '') == $curso['id_curso'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($curso['nombre_curso']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Módulo -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_modulo" class="form-label">
                                        <i class="fas fa-puzzle-piece"></i> Módulo <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-puzzle-piece"></i></span>
                                        <select class="form-select" id="id_modulo" name="id_modulo" required>
                                            <option value="">Seleccionar módulo</option>
                                            <?php if (!empty($modulos)): ?>
                                                <?php foreach ($modulos as $modulo): ?>
                                                <option value="<?= $modulo['id_modulo'] ?>" 
                                                        data-curso="<?= $modulo['id_curso'] ?>"
                                                        <?= ($examen['id_modulo'] ?? '') == $modulo['id_modulo'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($modulo['titulo']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-cogs text-primary me-2"></i> Configuración
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Tipo -->
                                <div class="col-md-4 mb-3">
                                    <label for="tipo" class="form-label">
                                        <i class="fas fa-list"></i> Tipo de Examen
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <select class="form-select" id="tipo" name="tipo">
                                            <option value="cuestionario" <?= ($examen['tipo'] ?? '') === 'cuestionario' ? 'selected' : '' ?>>Cuestionario</option>
                                            <option value="test" <?= ($examen['tipo'] ?? '') === 'test' ? 'selected' : '' ?>>Test</option>
                                            <option value="evaluacion" <?= ($examen['tipo'] ?? '') === 'evaluacion' ? 'selected' : '' ?>>Evaluación</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Duración -->
                                <div class="col-md-4 mb-3">
                                    <label for="duracion_minutos" class="form-label">
                                        <i class="fas fa-clock"></i> Duración (minutos)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-stopwatch"></i></span>
                                        <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" 
                                               min="1" max="600"
                                               value="<?= htmlspecialchars($examen['duracion_minutos'] ?? $_POST['duracion_minutos'] ?? '') ?>">
                                    </div>
                                    <div class="form-text">Dejar vacío para sin límite de tiempo</div>
                                </div>

                                <!-- Intentos máximos -->
                                <div class="col-md-4 mb-3">
                                    <label for="intentos_maximos" class="form-label">
                                        <i class="fas fa-redo"></i> Intentos máximos
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        <input type="number" class="form-control" id="intentos_maximos" name="intentos_maximos" 
                                               min="1" max="10"
                                               value="<?= htmlspecialchars($examen['intentos_maximos'] ?? $_POST['intentos_maximos'] ?? '1') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-calendar text-primary me-2"></i> Programación
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Fecha inicio -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar-plus"></i> Fecha y hora de inicio
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                               value="<?= isset($examen['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_inicio'])) : '' ?>">
                                    </div>
                                </div>

                                <!-- Fecha fin -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar-times"></i> Fecha y hora de fin
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin"
                                               value="<?= isset($examen['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_fin'])) : '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calificación -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-star text-primary me-2"></i> Calificación
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Puntuación máxima -->
                                <div class="col-md-6 mb-3">
                                    <label for="puntuacion_maxima" class="form-label">
                                        <i class="fas fa-trophy"></i> Puntuación máxima
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-star"></i></span>
                                        <input type="number" class="form-control" id="puntuacion_maxima" name="puntuacion_maxima" 
                                               step="0.1" min="0"
                                               value="<?= htmlspecialchars($examen['puntuacion_maxima'] ?? $_POST['puntuacion_maxima'] ?? '10') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i> Cancelar
                                </a>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" name="accion" value="guardar" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> <?= $es_edicion ? 'Actualizar' : 'Crear' ?> Examen
                                    </button>
                                    
                                    <?php if ($es_edicion): ?>
                                        <a href="<?= BASE_URL ?>/preguntas/examenes/<?= $examen['id_examen'] ?>" class="btn btn-info">
                                            <i class="fas fa-question-circle me-2"></i> Gestionar Preguntas
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script>
        // Cargar módulos según el curso seleccionado
        function cargarModulos() {
            const cursoSelect = document.getElementById('id_curso');
            const moduloSelect = document.getElementById('id_modulo');
            const cursoId = cursoSelect.value;
            
            // Mostrar/ocultar módulos según el curso
            const opciones = moduloSelect.querySelectorAll('option[data-curso]');
            opciones.forEach(opcion => {
                if (!cursoId || opcion.getAttribute('data-curso') === cursoId) {
                    opcion.style.display = '';
                } else {
                    opcion.style.display = 'none';
                }
            });
            
            // Resetear selección si no es válida
            if (!cursoId) {
                moduloSelect.value = '';
            }
        }

        // Validación del formulario
        document.getElementById('formExamen').addEventListener('submit', function(e) {
            const titulo = document.getElementById('titulo').value.trim();
            const curso = document.getElementById('id_curso').value;
            const modulo = document.getElementById('id_modulo').value;
            
            if (!titulo) {
                e.preventDefault();
                alert('El título del examen es obligatorio');
                document.getElementById('titulo').focus();
                return false;
            }
            
            if (!curso) {
                e.preventDefault();
                alert('Debe seleccionar un curso');
                document.getElementById('id_curso').focus();
                return false;
            }
            
            if (!modulo) {
                e.preventDefault();
                alert('Debe seleccionar un módulo');
                document.getElementById('id_modulo').focus();
                return false;
            }
            
            // Validar fechas
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            if (fechaInicio && fechaFin && new Date(fechaInicio) >= new Date(fechaFin)) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                e.preventDefault();
                return;
            }
            
            return true;
        });

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar módulos si hay curso seleccionado
            cargarModulos();
            
            // Tooltip para campos con ayuda
            document.querySelectorAll('[title]').forEach(el => {
                new bootstrap.Tooltip(el);
            });
        });
    </script>
        </div>
    </div>
</div>
