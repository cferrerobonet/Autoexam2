<?php
/**
 * Vista para crear/editar pregunta del banco - Admin - AUTOEXAM2
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 22/06/2025
 */

// Verificar sesión y permisos
if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
    header("Location: " . BASE_URL . "/autenticacion/login");
    exit;
}

$es_edicion = isset($pregunta) && !empty($pregunta);
$titulo_pagina = $es_edicion ? 'Editar Pregunta del Banco' : 'Nueva Pregunta del Banco';

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
                    <h1 class="h3 mb-1">
                        <i class="fas fa-<?= $es_edicion ? 'edit' : 'plus' ?>"></i> 
                        <?= $es_edicion ? 'Editar' : 'Nueva' ?> Pregunta del Banco
                    </h1>
                    <p class="text-muted">
                        <?= $es_edicion ? 'Modifica los datos de la pregunta' : 'Crea una nueva pregunta reutilizable' ?>
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/banco-preguntas" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Banco
                </a>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="<?= BASE_URL ?>/banco-preguntas/<?= $es_edicion ? 'editar/' . $pregunta['id_pregunta'] : 'crear' ?>" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <?php if ($es_edicion): ?>
                    <input type="hidden" name="id_pregunta" value="<?= $pregunta['id_pregunta'] ?>">
                <?php endif; ?>

                <div class="row">
                    <!-- Columna principal -->
                    <div class="col-lg-8">
                        <!-- Datos básicos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-edit"></i> Datos de la Pregunta
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Enunciado -->
                                <div class="mb-3">
                                    <label for="enunciado" class="form-label">Enunciado de la pregunta <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="enunciado" name="enunciado" rows="4" required 
                                              placeholder="Escribe aquí el enunciado de la pregunta..."><?= $es_edicion ? htmlspecialchars($pregunta['enunciado']) : '' ?></textarea>
                                </div>

                                <!-- Tipo de pregunta -->
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de pregunta <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo" name="tipo" required onchange="cambiarTipoPregunta()">
                                        <option value="">Selecciona el tipo...</option>
                                        <option value="test" <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'selected' : '' ?>>Pregunta Test (Opción múltiple)</option>
                                        <option value="abierta" <?= $es_edicion && $pregunta['tipo'] == 'abierta' ? 'selected' : '' ?>>Pregunta Abierta</option>
                                    </select>
                                </div>

                                <!-- Opciones de respuesta (solo para test) -->
                                <div id="opciones-test" style="display: <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'block' : 'none' ?>;">
                                    <div class="mb-3">
                                        <label class="form-label">Opciones de respuesta <span class="text-danger">*</span></label>
                                        <small class="text-muted d-block mb-2">Marca la(s) opción(es) correcta(s)</small>
                                        
                                        <div id="contenedor-respuestas">
                                            <?php if ($es_edicion && isset($respuestas) && $pregunta['tipo'] == 'test'): ?>
                                                <?php foreach ($respuestas as $index => $respuesta): ?>
                                                    <div class="opcion-respuesta mb-2">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-1">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" 
                                                                           name="correcta[]" value="<?= $index ?>" 
                                                                           <?= $respuesta['es_correcta'] ? 'checked' : '' ?>>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-10">
                                                                <input type="text" class="form-control" name="respuestas[]" 
                                                                       placeholder="Opción de respuesta..." 
                                                                       value="<?= htmlspecialchars($respuesta['texto']) ?>" required>
                                                            </div>
                                                            <div class="col-md-1">
                                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                        onclick="eliminarRespuesta(this)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <button type="button" class="btn btn-outline-primary" onclick="agregarRespuesta()">
                                            <i class="fas fa-plus"></i> Añadir Opción
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna lateral -->
                    <div class="col-lg-4">
                        <!-- Configuración -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-cog"></i> Configuración
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Categoría -->
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <select class="form-select" id="categoria" name="categoria">
                                        <option value="">Sin categoría</option>
                                        <option value="matematicas" <?= $es_edicion && $pregunta['categoria'] == 'matematicas' ? 'selected' : '' ?>>Matemáticas</option>
                                        <option value="ciencias" <?= $es_edicion && $pregunta['categoria'] == 'ciencias' ? 'selected' : '' ?>>Ciencias</option>
                                        <option value="lenguaje" <?= $es_edicion && $pregunta['categoria'] == 'lenguaje' ? 'selected' : '' ?>>Lenguaje</option>
                                    </select>
                                </div>

                                <!-- Dificultad -->
                                <div class="mb-3">
                                    <label for="dificultad" class="form-label">Dificultad</label>
                                    <select class="form-select" id="dificultad" name="dificultad">
                                        <option value="facil" <?= $es_edicion && $pregunta['dificultad'] == 'facil' ? 'selected' : '' ?>>Fácil</option>
                                        <option value="media" <?= (!$es_edicion || $pregunta['dificultad'] == 'media') ? 'selected' : '' ?>>Media</option>
                                        <option value="dificil" <?= $es_edicion && $pregunta['dificultad'] == 'dificil' ? 'selected' : '' ?>>Difícil</option>
                                    </select>
                                </div>

                                <!-- Etiquetas -->
                                <div class="mb-3">
                                    <label for="etiquetas" class="form-label">Etiquetas</label>
                                    <input type="text" class="form-control" id="etiquetas" name="etiquetas" 
                                           placeholder="Ej: álgebra, ecuaciones" 
                                           value="<?= $es_edicion ? htmlspecialchars($pregunta['etiquetas'] ?? '') : '' ?>">
                                    <small class="text-muted">Separa las etiquetas con comas</small>
                                </div>

                                <!-- Visibilidad (solo admin) -->
                                <?php if ($_SESSION['rol'] == 'admin'): ?>
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="publica" name="publica" 
                                                   <?= $es_edicion && $pregunta['publica'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="publica">
                                                <strong>Pregunta Pública</strong>
                                                <small class="d-block text-muted">
                                                    Visible para todos los profesores
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-save"></i> 
                                    <?= $es_edicion ? 'Actualizar' : 'Crear' ?> Pregunta
                                </button>
                                
                                <a href="<?= BASE_URL ?>/banco-preguntas" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Scripts -->
        <script>
        function cambiarTipoPregunta() {
            const tipo = document.getElementById('tipo').value;
            const opcionesTest = document.getElementById('opciones-test');
            
            if (tipo === 'test') {
                opcionesTest.style.display = 'block';
                // Agregar 2 opciones por defecto si no hay ninguna
                if (document.querySelectorAll('#contenedor-respuestas .opcion-respuesta').length === 0) {
                    agregarRespuesta();
                    agregarRespuesta();
                }
            } else {
                opcionesTest.style.display = 'none';
            }
        }

        function agregarRespuesta() {
            const contenedor = document.getElementById('contenedor-respuestas');
            const index = contenedor.children.length;
            
            const div = document.createElement('div');
            div.className = 'opcion-respuesta mb-2';
            div.innerHTML = `
                <div class="row align-items-center">
                    <div class="col-md-1">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="correcta[]" value="${index}">
                        </div>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" name="respuestas[]" 
                               placeholder="Opción de respuesta..." required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="eliminarRespuesta(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            contenedor.appendChild(div);
        }

        function eliminarRespuesta(boton) {
            const opcion = boton.closest('.opcion-respuesta');
            if (document.querySelectorAll('.opcion-respuesta').length > 2) {
                opcion.remove();
            } else {
                alert('Debe tener al menos 2 opciones de respuesta');
            }
        }

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const tipo = document.getElementById('tipo').value;
            
            if (tipo === 'test') {
                const respuestas = document.querySelectorAll('input[name="respuestas[]"]');
                const correctas = document.querySelectorAll('input[name="correcta[]"]:checked');
                
                let respuestasValidas = 0;
                respuestas.forEach(function(input) {
                    if (input.value.trim() !== '') {
                        respuestasValidas++;
                    }
                });
                
                if (respuestasValidas < 2) {
                    e.preventDefault();
                    alert('Debe tener al menos 2 opciones de respuesta válidas');
                    return;
                }
                
                if (correctas.length === 0) {
                    e.preventDefault();
                    alert('Debe marcar al menos una opción como correcta');
                    return;
                }
            }
        });

        // Inicializar si es edición
        <?php if ($es_edicion): ?>
        document.addEventListener('DOMContentLoaded', function() {
            cambiarTipoPregunta();
        });
        <?php endif; ?>
        </script>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>
</body>
</html>
