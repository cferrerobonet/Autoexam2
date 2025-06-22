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

// Incluir archivos según el rol
$es_admin = $_SESSION['rol'] === 'admin';
$head_file = $es_admin ? 'head_admin.php' : 'head_profesor.php';
$navbar_file = $es_admin ? 'navbar_admin.php' : 'navbar_profesor.php';
$footer_file = $es_admin ? 'footer_admin.php' : 'footer_profesor.php';
?>

<?php require_once APP_PATH . '/vistas/parciales/' . $head_file; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/' . $navbar_file; ?>

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Estilos personalizados -->
                <style>
                    .bg-purple {
                        background-color: #8a5cd1 !important;
                    }
                    .text-purple {
                        color: #8a5cd1 !important;
                    }
                    .border-purple {
                        border-color: #8a5cd1 !important;
                    }
                    .bg-purple-subtle {
                        background-color: rgba(138, 92, 209, 0.1) !important;
                    }
                    .pregunta-item {
                        border: 1px solid #dee2e6;
                        border-radius: 8px;
                        margin-bottom: 15px;
                        transition: all 0.3s;
                    }
                    .pregunta-item:hover {
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                    .respuesta-item {
                        border-left: 3px solid #dee2e6;
                        padding: 10px;
                        margin: 5px 0;
                        border-radius: 0 5px 5px 0;
                    }
                    .respuesta-item.correcta {
                        border-left-color: #28a745;
                        background-color: #f8fff9;
                    }
                </style>
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?>"></i> <?= $titulo_pagina ?></h1>
                    <div class="d-flex gap-2">
                        <?php if ($es_edicion): ?>
                            <a href="<?= BASE_URL ?>/examenes/ver/<?= $datos['examen']['id_examen'] ?>" class="btn btn-outline-info rounded-pill px-4">
                                <i class="fas fa-eye me-2"></i> Previsualizar
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-2"></i> Volver a la lista
                        </a>
                    </div>
                </div>

                <!-- Mensajes de estado -->
                <?php if (isset($_SESSION['exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Formulario -->
                <form method="POST" action="<?= BASE_URL ?>/examenes/<?= $es_edicion ? 'actualizar' : 'guardar' ?>" id="formExamen" enctype="multipart/form-data">
                    <?php if ($es_edicion): ?>
                        <input type="hidden" name="id_examen" value="<?= $datos['examen']['id_examen'] ?>">
                    <?php endif; ?>
                    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                    
                    <!-- Información básica del examen -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-info-circle text-primary me-2"></i> Información Básica
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
                                               required maxlength="200" 
                                               value="<?= htmlspecialchars($datos['examen']['titulo'] ?? $_POST['titulo'] ?? '') ?>">
                                    </div>
                                    <div class="form-text">Nombre descriptivo para identificar el examen</div>
                                </div>

                                <!-- Estado -->
                                <div class="col-md-4 mb-3">
                                    <label for="estado" class="form-label">
                                        <i class="fas fa-toggle-on"></i> Estado <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                        <select class="form-select" id="estado" name="estado" required>
                                            <option value="borrador" <?= ($datos['examen']['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                            <option value="activo" <?= ($datos['examen']['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                            <option value="finalizado" <?= ($datos['examen']['estado'] ?? '') === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Estado actual del examen</div>
                                </div>

                                <!-- Descripción -->
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">
                                        <i class="fas fa-align-left"></i> Descripción
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-comment"></i></span>
                                        <textarea class="form-control" id="descripcion" name="descripcion" 
                                                  rows="3" maxlength="500"><?= htmlspecialchars($datos['examen']['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-text">Descripción opcional del contenido del examen</div>
                                </div>

                                <!-- Curso -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_curso" class="form-label">
                                        <i class="fas fa-book"></i> Curso <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                        <select class="form-select" id="id_curso" name="id_curso" required>
                                            <option value="">Selecciona un curso</option>
                                            <?php if (isset($datos['cursos'])): ?>
                                                <?php foreach ($datos['cursos'] as $curso): ?>
                                                    <option value="<?= $curso['id_curso'] ?>" 
                                                            <?= ($datos['examen']['id_curso'] ?? '') == $curso['id_curso'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($curso['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-text">Curso al que pertenece el examen</div>
                                </div>

                                <!-- Módulo -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_modulo" class="form-label">
                                        <i class="fas fa-cube"></i> Módulo
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                        <select class="form-select" id="id_modulo" name="id_modulo">
                                            <option value="">Selecciona un módulo</option>
                                            <?php if (isset($datos['modulos'])): ?>
                                                <?php foreach ($datos['modulos'] as $modulo): ?>
                                                    <option value="<?= $modulo['id_modulo'] ?>" 
                                                            data-curso="<?= $modulo['id_curso'] ?>"
                                                            <?= ($datos['examen']['id_modulo'] ?? '') == $modulo['id_modulo'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($modulo['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-text">Módulo específico (opcional)</div>
                                </div>

                                <!-- Tipo -->
                                <div class="col-md-4 mb-3">
                                    <label for="tipo" class="form-label">
                                        <i class="fas fa-tags"></i> Tipo
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-clipboard-list"></i></span>
                                        <select class="form-select" id="tipo" name="tipo">
                                            <option value="cuestionario" <?= ($datos['examen']['tipo'] ?? '') === 'cuestionario' ? 'selected' : '' ?>>Cuestionario</option>
                                            <option value="test" <?= ($datos['examen']['tipo'] ?? '') === 'test' ? 'selected' : '' ?>>Test</option>
                                            <option value="evaluacion" <?= ($datos['examen']['tipo'] ?? '') === 'evaluacion' ? 'selected' : '' ?>>Evaluación</option>
                                        </select>
                                    </div>
                                    <div class="form-text">Tipo de examen</div>
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
                                               value="<?= htmlspecialchars($datos['examen']['duracion_minutos'] ?? $_POST['duracion_minutos'] ?? '') ?>">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <div class="form-text">Tiempo límite (vacío = sin límite)</div>
                                </div>

                                <!-- Intentos máximos -->
                                <div class="col-md-4 mb-3">
                                    <label for="intentos_maximos" class="form-label">
                                        <i class="fas fa-redo"></i> Intentos Máximos
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-repeat"></i></span>
                                        <input type="number" class="form-control" id="intentos_maximos" name="intentos_maximos" 
                                               min="1" max="10" 
                                               value="<?= htmlspecialchars($datos['examen']['intentos_maximos'] ?? $_POST['intentos_maximos'] ?? '1') ?>">
                                    </div>
                                    <div class="form-text">Número de intentos permitidos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración avanzada -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-cogs text-warning me-2"></i> Configuración Avanzada
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Fechas -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-play"></i> Fecha de Inicio
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-plus"></i></span>
                                        <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                               value="<?= isset($datos['examen']['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($datos['examen']['fecha_inicio'])) : '' ?>">
                                    </div>
                                    <div class="form-text">Cuándo estará disponible el examen</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-stop"></i> Fecha de Finalización
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-times"></i></span>
                                        <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin"
                                               value="<?= isset($datos['examen']['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($datos['examen']['fecha_fin'])) : '' ?>">
                                    </div>
                                    <div class="form-text">Hasta cuándo estará disponible</div>
                                </div>

                                <!-- Puntuación -->
                                <div class="col-md-4 mb-3">
                                    <label for="puntuacion_maxima" class="form-label">
                                        <i class="fas fa-star"></i> Puntuación Máxima
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-trophy"></i></span>
                                        <input type="number" class="form-control" id="puntuacion_maxima" name="puntuacion_maxima" 
                                               min="1" step="0.1" 
                                               value="<?= htmlspecialchars($datos['examen']['puntuacion_maxima'] ?? $_POST['puntuacion_maxima'] ?? '10') ?>">
                                        <span class="input-group-text">pts</span>
                                    </div>
                                    <div class="form-text">Puntuación total del examen</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="puntuacion_aprobado" class="form-label">
                                        <i class="fas fa-check"></i> Puntuación para Aprobar
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                        <input type="number" class="form-control" id="puntuacion_aprobado" name="puntuacion_aprobado" 
                                               min="0" step="0.1" 
                                               value="<?= htmlspecialchars($datos['examen']['puntuacion_aprobado'] ?? $_POST['puntuacion_aprobado'] ?? '5') ?>">
                                        <span class="input-group-text">pts</span>
                                    </div>
                                    <div class="form-text">Puntuación mínima para aprobar</div>
                                </div>

                                <!-- Configuraciones especiales -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-random"></i> Configuraciones Especiales
                                    </label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="orden_aleatorio" name="orden_aleatorio" value="1"
                                               <?= ($datos['examen']['orden_aleatorio'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="orden_aleatorio">
                                            Orden aleatorio de preguntas
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="mostrar_resultados" name="mostrar_resultados" value="1"
                                               <?= ($datos['examen']['mostrar_resultados'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="mostrar_resultados">
                                            Mostrar resultados al finalizar
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="permitir_revision" name="permitir_revision" value="1"
                                               <?= ($datos['examen']['permitir_revision'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="permitir_revision">
                                            Permitir revisión posterior
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0 d-flex align-items-center">
                                <i class="fas fa-list-ol text-info me-2"></i> Instrucciones para el Estudiante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="instrucciones" class="form-label">
                                    <i class="fas fa-file-text"></i> Instrucciones
                                </label>
                                <textarea class="form-control" id="instrucciones" name="instrucciones" 
                                          rows="5"><?= htmlspecialchars($datos['examen']['instrucciones'] ?? $_POST['instrucciones'] ?? '') ?></textarea>
                                <div class="form-text">Instrucciones que verá el estudiante antes de comenzar el examen</div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                </div>
                                <div>
                                    <button type="submit" name="accion" value="borrador" class="btn btn-outline-warning me-2">
                                        <i class="fas fa-save me-2"></i>Guardar como Borrador
                                    </button>
                                    <button type="submit" name="accion" value="<?= $es_edicion ? 'actualizar' : 'publicar' ?>" class="btn btn-primary">
                                        <i class="fas fa-<?= $es_edicion ? 'save' : 'paper-plane' ?> me-2"></i>
                                        <?= $es_edicion ? 'Actualizar Examen' : 'Crear y Publicar' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrar módulos por curso seleccionado
        document.getElementById('id_curso').addEventListener('change', function() {
            const cursoSeleccionado = this.value;
            const selectModulo = document.getElementById('id_modulo');
            const opciones = selectModulo.querySelectorAll('option[data-curso]');
            
            // Resetear selección de módulo
            selectModulo.value = '';
            
            // Mostrar/ocultar opciones según el curso
            opciones.forEach(opcion => {
                if (!cursoSeleccionado || opcion.getAttribute('data-curso') === cursoSeleccionado) {
                    opcion.style.display = '';
                } else {
                    opcion.style.display = 'none';
                }
            });
        });

        // Ejecutar al cargar para filtrar módulos si hay curso pre-seleccionado
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('id_curso').dispatchEvent(new Event('change'));
        });

        // Validar fechas
        document.getElementById('fecha_inicio').addEventListener('change', function() {
            const fechaInicio = new Date(this.value);
            const fechaFinInput = document.getElementById('fecha_fin');
            
            if (fechaFinInput.value) {
                const fechaFin = new Date(fechaFinInput.value);
                if (fechaFin <= fechaInicio) {
                    alert('La fecha de finalización debe ser posterior a la fecha de inicio');
                    fechaFinInput.value = '';
                }
            }
        });

        document.getElementById('fecha_fin').addEventListener('change', function() {
            const fechaFin = new Date(this.value);
            const fechaInicioInput = document.getElementById('fecha_inicio');
            
            if (fechaInicioInput.value) {
                const fechaInicio = new Date(fechaInicioInput.value);
                if (fechaFin <= fechaInicio) {
                    alert('La fecha de finalización debe ser posterior a la fecha de inicio');
                    this.value = '';
                }
            }
        });

        // Validar puntuaciones
        document.getElementById('puntuacion_aprobado').addEventListener('input', function() {
            const puntuacionMaxima = parseFloat(document.getElementById('puntuacion_maxima').value) || 10;
            const puntuacionAprobado = parseFloat(this.value);
            
            if (puntuacionAprobado > puntuacionMaxima) {
                alert('La puntuación para aprobar no puede ser mayor que la puntuación máxima');
                this.value = puntuacionMaxima;
            }
        });

        document.getElementById('puntuacion_maxima').addEventListener('input', function() {
            const puntuacionAprobado = parseFloat(document.getElementById('puntuacion_aprobado').value) || 0;
            const puntuacionMaxima = parseFloat(this.value);
            
            if (puntuacionAprobado > puntuacionMaxima) {
                document.getElementById('puntuacion_aprobado').value = puntuacionMaxima;
            }
        });

        // Validación del formulario
        document.getElementById('formExamen').addEventListener('submit', function(e) {
            const titulo = document.getElementById('titulo').value.trim();
            const curso = document.getElementById('id_curso').value;
            
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
            
            return true;
        });

        // Tooltip para campos con ayuda
        document.querySelectorAll('[title]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    </script>

    <?php require_once APP_PATH . '/vistas/parciales/' . $footer_file; ?>
</body>
</html>
        margin-bottom: 20px;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?>"></i> <?= $titulo_pagina ?></h1>
                <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Volver a la lista
                </a>
            </div>

            <!-- Mensajes de estado -->
            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="<?= BASE_URL ?>/examenes/<?= $es_edicion ? 'actualizar/' . $examen['id_examen'] : 'guardar' ?>" id="formExamen">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <!-- Información Básica -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary me-2"></i> Información Básica
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
                                           value="<?= $es_edicion ? htmlspecialchars($examen['titulo']) : '' ?>"
                                           placeholder="Ingrese el título del examen">
                                </div>
                                <div class="form-text">Máximo 255 caracteres</div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-4 mb-3">
                                <label for="activo" class="form-label">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-power-off"></i></span>
                                    <select class="form-select" id="activo" name="activo">
                                        <option value="1" <?= (!$es_edicion || $examen['activo']) ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= ($es_edicion && !$examen['activo']) ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-align-left"></i> Descripción
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                      placeholder="Descripción opcional del examen"><?= $es_edicion ? htmlspecialchars($examen['descripcion']) : '' ?></textarea>
                            <div class="form-text">Información adicional sobre el examen</div>
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
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id_curso'] ?>" 
                                                <?= ($es_edicion && $examen['id_curso'] == $curso['id_curso']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($curso['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                        <?php foreach ($modulos as $modulo): ?>
                                            <option value="<?= $modulo['id_modulo'] ?>" 
                                                data-curso="<?= $modulo['id_curso'] ?>"
                                                <?= ($es_edicion && $examen['id_modulo'] == $modulo['id_modulo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($modulo['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Tiempo y Fechas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-2"></i> Configuración de Tiempo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Tiempo límite -->
                            <div class="col-md-4 mb-3">
                                <label for="tiempo_limite" class="form-label">
                                    <i class="fas fa-stopwatch"></i> Tiempo Límite (minutos)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="number" class="form-control" id="tiempo_limite" name="tiempo_limite" 
                                           min="1" max="300"
                                           value="<?= $es_edicion ? $examen['tiempo_limite'] : '' ?>"
                                           placeholder="60">
                                </div>
                                <div class="form-text">Dejar vacío para examen sin límite de tiempo</div>
                            </div>

                            <!-- Fecha inicio -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_inicio" class="form-label">
                                    <i class="fas fa-calendar-plus"></i> Fecha de Inicio
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio"
                                           value="<?= $es_edicion && $examen['fecha_inicio'] ? date('Y-m-d\TH:i', strtotime($examen['fecha_inicio'])) : '' ?>">
                                </div>
                            </div>

                            <!-- Fecha fin -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_fin" class="form-label">
                                    <i class="fas fa-calendar-times"></i> Fecha de Fin
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                    <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin"
                                           value="<?= $es_edicion && $examen['fecha_fin'] ? date('Y-m-d\TH:i', strtotime($examen['fecha_fin'])) : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opciones Avanzadas -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-cogs text-primary me-2"></i> Opciones Avanzadas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aleatorio_preg" name="aleatorio_preg"
                                           <?= ($es_edicion && $examen['aleatorio_preg']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aleatorio_preg">
                                        <i class="fas fa-random"></i> Preguntas aleatorias
                                    </label>
                                </div>
                                <small class="form-text text-muted">Las preguntas aparecerán en orden aleatorio</small>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aleatorio_resp" name="aleatorio_resp"
                                           <?= ($es_edicion && $examen['aleatorio_resp']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aleatorio_resp">
                                        <i class="fas fa-shuffle"></i> Respuestas aleatorias
                                    </label>
                                </div>
                                <small class="form-text text-muted">Las opciones de respuesta se mezclarán</small>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="visible" name="visible"
                                           <?= (!$es_edicion || $examen['visible']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="visible">
                                        <i class="fas fa-eye"></i> Visible para estudiantes
                                    </label>
                                </div>
                                <small class="form-text text-muted">Los estudiantes podrán ver este examen</small>
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
    
    // Limpiar módulos
    moduloSelect.innerHTML = '<option value="">Seleccionar módulo</option>';
    
    if (cursoId) {
        // Mostrar solo módulos del curso seleccionado
        const todasLasOpciones = moduloSelect.querySelectorAll('option[data-curso]');
        
        <?php foreach ($modulos as $modulo): ?>
            if ('<?= $modulo['id_curso'] ?>' === cursoId) {
                const option = document.createElement('option');
                option.value = '<?= $modulo['id_modulo'] ?>';
                option.textContent = '<?= htmlspecialchars($modulo['nombre']) ?>';
                moduloSelect.appendChild(option);
            }
        <?php endforeach; ?>
    }
}

// Validación del formulario
document.getElementById('formExamen').addEventListener('submit', function(e) {
    const titulo = document.getElementById('titulo').value.trim();
    const curso = document.getElementById('id_curso').value;
    const modulo = document.getElementById('id_modulo').value;
    
    if (!titulo) {
        alert('El título del examen es obligatorio');
        e.preventDefault();
        return;
    }
    
    if (!curso) {
        alert('Debe seleccionar un curso');
        e.preventDefault();
        return;
    }
    
    if (!modulo) {
        alert('Debe seleccionar un módulo');
        e.preventDefault();
        return;
    }
    
    // Validar fechas
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    
    if (fechaInicio && fechaFin && new Date(fechaInicio) >= new Date(fechaFin)) {
        alert('La fecha de fin debe ser posterior a la fecha de inicio');
        e.preventDefault();
        return;
    }
});

// Inicializar la carga de módulos si hay un curso preseleccionado
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($es_edicion && !empty($examen['id_curso'])): ?>
        cargarModulos();
        // Seleccionar el módulo después de cargar
        setTimeout(function() {
            document.getElementById('id_modulo').value = '<?= $examen['id_modulo'] ?>';
        }, 100);
    <?php endif; ?>
});
</script>

<?php require_once APP_PATH . '/vistas/parciales/footer.php'; ?>
