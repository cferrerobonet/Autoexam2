<?php
// Verificar que estamos en el contexto correcto
if (!isset($cursos) || !isset($modulos)) {
    header("Location: " . BASE_URL);
    exit;
}

// Determinar si es edición o creación
$es_edicion = isset($examen) && isset($examen['id_examen']);
$titulo_pagina = $es_edicion ? 'Editar Examen' : 'Crear Examen';

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?> - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <style>
        .pregunta-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .pregunta-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .pregunta-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
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
        .drag-handle {
            cursor: move;
            color: #6c757d;
        }
        .configuracion-seccion {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-pregunta {
            margin: 5px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../comunes/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../comunes/sidebar.php'; ?>
            
            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Encabezado -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?> me-2"></i>
                        <?= $titulo_pagina ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver a la lista
                        </a>
                        <?php if ($es_edicion): ?>
                            <button type="button" class="btn btn-outline-info me-2" onclick="previsualizarExamen()">
                                <i class="fas fa-eye me-1"></i>
                                Previsualizar
                            </button>
                            <button type="button" class="btn btn-outline-success me-2" onclick="guardarVersion()">
                                <i class="fas fa-save me-1"></i>
                                Guardar Versión
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mensajes -->
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['mensaje_exito']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['mensaje_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['mensaje_error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_error']); ?>
                <?php endif; ?>

                <form method="POST" id="formularioExamen">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <!-- Configuración básica del examen -->
                    <div class="configuracion-seccion">
                        <h4 class="mb-3">
                            <i class="fas fa-cog me-2"></i>
                            Configuración del Examen
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Título del examen *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?= htmlspecialchars($examen['titulo'] ?? '') ?>" 
                                           placeholder="Introduce el título del examen..." required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tiempo_limite" class="form-label">Tiempo límite (minutos)</label>
                                    <input type="number" class="form-control" id="tiempo_limite" name="tiempo_limite" 
                                           value="<?= $examen['tiempo_limite'] ?? '' ?>" 
                                           placeholder="Ej: 60" min="1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_curso" class="form-label">Curso *</label>
                                    <select class="form-select" id="id_curso" name="id_curso" required>
                                        <option value="">Seleccionar curso...</option>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id_curso'] ?>" 
                                                    <?= (isset($examen) && $examen['id_curso'] == $curso['id_curso']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($curso['nombre_curso']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_modulo" class="form-label">Módulo *</label>
                                    <select class="form-select" id="id_modulo" name="id_modulo" required>
                                        <option value="">Seleccionar módulo...</option>
                                        <?php foreach ($modulos as $modulo): ?>
                                            <option value="<?= $modulo['id_modulo'] ?>" 
                                                    <?= (isset($examen) && $examen['id_modulo'] == $modulo['id_modulo']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($modulo['titulo']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha y hora de inicio</label>
                                    <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?= isset($examen['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_inicio'])) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha y hora de fin</label>
                                    <input type="datetime-local" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?= isset($examen['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($examen['fecha_fin'])) : '' ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aleatorio_preg" name="aleatorio_preg" 
                                           <?= (isset($examen) && $examen['aleatorio_preg']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aleatorio_preg">
                                        Preguntas aleatorias
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aleatorio_resp" name="aleatorio_resp" 
                                           <?= (isset($examen) && $examen['aleatorio_resp']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="aleatorio_resp">
                                        Respuestas aleatorias
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="visible" name="visible" 
                                           <?= (!isset($examen) || $examen['visible']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="visible">
                                        Visible para alumnos
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                           <?= (!isset($examen) || $examen['activo']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="activo">
                                        Examen activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción principal -->
                    <div class="d-flex justify-content-end mb-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            <?= $es_edicion ? 'Actualizar Examen' : 'Crear Examen' ?>
                        </button>
                    </div>
                </form>

                <?php if ($es_edicion): ?>
                    <!-- Gestión de preguntas -->
                    <div class="configuracion-seccion">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>
                                <i class="fas fa-question-circle me-2"></i>
                                Preguntas del Examen
                                <span class="badge bg-primary ms-2" id="contador-preguntas">
                                    <?= count($preguntas ?? []) ?>
                                </span>
                            </h4>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-pregunta" onclick="nuevaPreguntaTest()">
                                    <i class="fas fa-plus me-1"></i>
                                    Pregunta Tipo Test
                                </button>
                                <button type="button" class="btn btn-outline-info btn-pregunta" onclick="nuevaPreguntaDesarrollo()">
                                    <i class="fas fa-plus me-1"></i>
                                    Pregunta Desarrollo
                                </button>
                                <button type="button" class="btn btn-outline-success btn-pregunta" onclick="importarDelBanco()">
                                    <i class="fas fa-database me-1"></i>
                                    Importar del Banco
                                </button>
                            </div>
                        </div>

                        <!-- Lista de preguntas -->
                        <div id="lista-preguntas">
                            <?php if (isset($preguntas) && !empty($preguntas)): ?>
                                <?php foreach ($preguntas as $index => $pregunta): ?>
                                    <div class="pregunta-item" data-pregunta-id="<?= $pregunta['id_pregunta'] ?>">
                                        <div class="pregunta-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-grip-vertical drag-handle me-2"></i>
                                                    <h6 class="mb-0">
                                                        Pregunta <?= $index + 1 ?> 
                                                        <span class="badge bg-<?= $pregunta['tipo'] == 'test' ? 'primary' : 'info' ?>">
                                                            <?= ucfirst($pregunta['tipo']) ?>
                                                        </span>
                                                        <?php if (!$pregunta['habilitada']): ?>
                                                            <span class="badge bg-secondary">Deshabilitada</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="editarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-<?= $pregunta['habilitada'] ? 'warning' : 'success' ?>" 
                                                            onclick="togglePregunta(<?= $pregunta['id_pregunta'] ?>, <?= $pregunta['habilitada'] ? 'false' : 'true' ?>)">
                                                        <i class="fas fa-<?= $pregunta['habilitada'] ? 'eye-slash' : 'eye' ?>"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="eliminarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-3">
                                            <div class="pregunta-contenido">
                                                <?= nl2br(htmlspecialchars($pregunta['enunciado'])) ?>
                                            </div>
                                            
                                            <?php if ($pregunta['tipo'] == 'test' && isset($pregunta['respuestas'])): ?>
                                                <div class="mt-3">
                                                    <h6>Respuestas:</h6>
                                                    <?php foreach ($pregunta['respuestas'] as $respuesta): ?>
                                                        <div class="respuesta-item <?= $respuesta['correcta'] ? 'correcta' : '' ?>">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-<?= $respuesta['correcta'] ? 'check-circle text-success' : 'circle text-muted' ?> me-2"></i>
                                                                <?= htmlspecialchars($respuesta['texto']) ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5" id="sin-preguntas">
                                    <div class="mb-4">
                                        <i class="fas fa-question-circle fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">No hay preguntas en este examen</h5>
                                    <p class="text-muted">Comienza agregando preguntas a tu examen</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para nueva pregunta -->
    <div class="modal fade" id="modalPregunta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalPregunta">Nueva Pregunta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formPregunta">
                        <input type="hidden" id="pregunta_id" name="id_pregunta">
                        <input type="hidden" id="pregunta_tipo" name="tipo">
                        
                        <div class="mb-3">
                            <label for="enunciado_pregunta" class="form-label">Enunciado de la pregunta *</label>
                            <textarea class="form-control" id="enunciado_pregunta" name="enunciado" rows="4" required></textarea>
                        </div>
                        
                        <div id="respuestas-container" style="display: none;">
                            <label class="form-label">Respuestas</label>
                            <div id="lista-respuestas"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarRespuesta()">
                                <i class="fas fa-plus me-1"></i>
                                Agregar Respuesta
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarPregunta()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para importar del banco -->
    <div class="modal fade" id="modalBanco" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Importar Preguntas del Banco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="filtro_tipo_banco">
                                <option value="">Todos los tipos</option>
                                <option value="test">Tipo Test</option>
                                <option value="desarrollo">Desarrollo</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="buscar_banco" placeholder="Buscar en el banco...">
                        </div>
                    </div>
                    <div id="preguntas-banco-lista">
                        <!-- Se carga dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="importarSeleccionadas()">
                        Importar Seleccionadas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../comunes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Inicializar editor de texto enriquecido
        $('#enunciado_pregunta').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });

        // Hacer las preguntas ordenables
        <?php if ($es_edicion): ?>
        new Sortable(document.getElementById('lista-preguntas'), {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                actualizarOrdenPreguntas();
            }
        });
        <?php endif; ?>

        // Funciones para gestión de preguntas
        function nuevaPreguntaTest() {
            document.getElementById('tituloModalPregunta').textContent = 'Nueva Pregunta Tipo Test';
            document.getElementById('pregunta_id').value = '';
            document.getElementById('pregunta_tipo').value = 'test';
            document.getElementById('enunciado_pregunta').value = '';
            $('#enunciado_pregunta').summernote('code', '');
            
            mostrarRespuestas();
            limpiarRespuestas();
            agregarRespuesta();
            agregarRespuesta();
            
            new bootstrap.Modal(document.getElementById('modalPregunta')).show();
        }

        function nuevaPreguntaDesarrollo() {
            document.getElementById('tituloModalPregunta').textContent = 'Nueva Pregunta de Desarrollo';
            document.getElementById('pregunta_id').value = '';
            document.getElementById('pregunta_tipo').value = 'desarrollo';
            document.getElementById('enunciado_pregunta').value = '';
            $('#enunciado_pregunta').summernote('code', '');
            
            ocultarRespuestas();
            
            new bootstrap.Modal(document.getElementById('modalPregunta')).show();
        }

        function mostrarRespuestas() {
            document.getElementById('respuestas-container').style.display = 'block';
        }

        function ocultarRespuestas() {
            document.getElementById('respuestas-container').style.display = 'none';
        }

        function limpiarRespuestas() {
            document.getElementById('lista-respuestas').innerHTML = '';
        }

        function agregarRespuesta() {
            const container = document.getElementById('lista-respuestas');
            const index = container.children.length;
            
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <div class="input-group-text">
                    <input class="form-check-input" type="checkbox" name="respuestas[${index}][correcta]">
                </div>
                <input type="text" class="form-control" name="respuestas[${index}][texto]" placeholder="Texto de la respuesta..." required>
                <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(div);
        }

        function guardarPregunta() {
            const form = document.getElementById('formPregunta');
            const formData = new FormData(form);
            
            // Agregar contenido del editor
            formData.set('enunciado', $('#enunciado_pregunta').summernote('code'));
            formData.append('id_examen', <?= $examen['id_examen'] ?? 'null' ?>);
            
            fetch('<?= BASE_URL ?>/preguntas/guardar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalPregunta')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }

        function importarDelBanco() {
            // Cargar preguntas del banco
            fetch('<?= BASE_URL ?>/preguntas/banco')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarPreguntasBanco(data.preguntas);
                    new bootstrap.Modal(document.getElementById('modalBanco')).show();
                }
            });
        }

        function mostrarPreguntasBanco(preguntas) {
            const container = document.getElementById('preguntas-banco-lista');
            container.innerHTML = '';
            
            preguntas.forEach(pregunta => {
                const div = document.createElement('div');
                div.className = 'card mb-2';
                div.innerHTML = `
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${pregunta.id_pregunta}" id="banco_${pregunta.id_pregunta}">
                            <label class="form-check-label" for="banco_${pregunta.id_pregunta}">
                                <strong>${pregunta.tipo.toUpperCase()}</strong> - ${pregunta.enunciado.substring(0, 100)}...
                                <small class="text-muted d-block">Por: ${pregunta.nombre_profesor}</small>
                            </label>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function importarSeleccionadas() {
            const seleccionadas = [];
            document.querySelectorAll('#preguntas-banco-lista input:checked').forEach(checkbox => {
                seleccionadas.push(checkbox.value);
            });
            
            if (seleccionadas.length === 0) {
                alert('Selecciona al menos una pregunta');
                return;
            }
            
            fetch('<?= BASE_URL ?>/preguntas/importar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    preguntas: seleccionadas,
                    id_examen: <?= $examen['id_examen'] ?? 'null' ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalBanco')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }

        // Funciones adicionales
        function eliminarPregunta(id) {
            if (confirm('¿Estás seguro de eliminar esta pregunta?')) {
                fetch(`<?= BASE_URL ?>/preguntas/eliminar/${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        }

        function togglePregunta(id, habilitar) {
            fetch(`<?= BASE_URL ?>/preguntas/toggle/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({habilitada: habilitar})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function actualizarOrdenPreguntas() {
            const orden = [];
            document.querySelectorAll('.pregunta-item').forEach((item, index) => {
                orden.push({
                    id: item.dataset.preguntaId,
                    orden: index
                });
            });
            
            fetch('<?= BASE_URL ?>/preguntas/ordenar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({orden: orden})
            });
        }

        // Validaciones del formulario
        document.getElementById('formularioExamen').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);
            
            if (fechaInicio && fechaFin && fechaInicio >= fechaFin) {
                e.preventDefault();
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
        });
    </script>
</body>
</html>
