<?php
/**
 * Vista para crear/editar pregunta del banco
 * 
 * @package AUTOEXAM2
 * @author Sistema AUTOEXAM2
 * @version 1.0
 * @since 21/06/2025
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

// Incluir head y navbar según el rol
if ($_SESSION['rol'] === 'admin') {
    require_once APP_PATH . '/vistas/parciales/head_admin.php';
} else {
    require_once APP_PATH . '/vistas/parciales/head_profesor.php';
}
?>
<!-- El doctype ya está incluido en los archivos head_admin.php o head_profesor.php -->
<body class="bg-light">
    <?php 
    // Incluir navbar según el rol
    if ($_SESSION['rol'] === 'admin') {
        require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
    } else {
        require_once APP_PATH . '/vistas/parciales/navbar_profesor.php';
    }
    ?>
    
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

<div class="container-fluid py-4">
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
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/banco-preguntas/<?= $es_edicion ? 'editar/' . $pregunta['id_pregunta'] : 'crear' ?>" enctype="multipart/form-data" id="formPregunta" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if ($es_edicion): ?>
            <input type="hidden" name="id_pregunta" value="<?= $pregunta['id_pregunta'] ?>">
            <input type="hidden" name="media_valor_actual" value="<?= htmlspecialchars($pregunta['media_valor'] ?? '') ?>">
        <?php endif; ?>

        <div class="row">
            <!-- Columna principal -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle"></i> Datos de la Pregunta
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Enunciado -->
                        <div class="mb-3">
                            <label for="enunciado" class="form-label form-label-sm">
                                <i class="fas fa-question-circle"></i> Enunciado de la Pregunta <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-question"></i></span>
                                <textarea class="form-control form-control-sm" id="enunciado" name="enunciado" rows="3" required 
                                          placeholder="Escribe aquí el enunciado de tu pregunta..."><?= $es_edicion ? htmlspecialchars($pregunta['enunciado']) : '' ?></textarea>
                            </div>
                            <small class="form-text text-muted">Describe claramente la pregunta</small>
                        </div>

                        <!-- Fila con Tipo y Dificultad -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">
                                        <i class="fas fa-list-alt"></i> Tipo de Pregunta <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                                        <select class="form-select form-select-sm" id="tipo" name="tipo" required onchange="cambiarTipoPregunta()">
                                            <option value="">Selecciona el tipo...</option>
                                            <option value="test" <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'selected' : '' ?>>
                                                Pregunta Test (Opción múltiple)
                                            </option>
                                            <option value="desarrollo" <?= $es_edicion && $pregunta['tipo'] == 'desarrollo' ? 'selected' : '' ?>>
                                                Pregunta de Desarrollo
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dificultad" class="form-label">
                                        <i class="fas fa-signal"></i> Dificultad <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-signal"></i></span>
                                        <select class="form-select form-select-sm" id="dificultad" name="dificultad" required>
                                            <option value="">Selecciona dificultad...</option>
                                            <option value="facil" <?= $es_edicion && $pregunta['dificultad'] == 'facil' ? 'selected' : '' ?>>Fácil</option>
                                            <option value="media" <?= !$es_edicion || $pregunta['dificultad'] == 'media' ? 'selected' : '' ?>>Media</option>
                                            <option value="dificil" <?= $es_edicion && $pregunta['dificultad'] == 'dificil' ? 'selected' : '' ?>>Difícil</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila con Categoría y Etiquetas -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoria" class="form-label">
                                        <i class="fas fa-folder"></i> Categoría <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-folder"></i></span>
                                        <select class="form-select form-select-sm" id="categoria" name="categoria" required>
                                            <option value="">Selecciona categoría...</option>
                                            <option value="matematicas" <?= $es_edicion && $pregunta['categoria'] == 'matematicas' ? 'selected' : '' ?>>Matemáticas</option>
                                            <option value="ciencias" <?= $es_edicion && $pregunta['categoria'] == 'ciencias' ? 'selected' : '' ?>>Ciencias</option>
                                            <option value="lenguaje" <?= $es_edicion && $pregunta['categoria'] == 'lenguaje' ? 'selected' : '' ?>>Lenguaje</option>
                                            <option value="historia" <?= $es_edicion && $pregunta['categoria'] == 'historia' ? 'selected' : '' ?>>Historia</option>
                                            <option value="geografia" <?= $es_edicion && $pregunta['categoria'] == 'geografia' ? 'selected' : '' ?>>Geografía</option>
                                            <option value="idiomas" <?= $es_edicion && $pregunta['categoria'] == 'idiomas' ? 'selected' : '' ?>>Idiomas</option>
                                            <option value="tecnologia" <?= $es_edicion && $pregunta['categoria'] == 'tecnologia' ? 'selected' : '' ?>>Tecnología</option>
                                            <option value="arte" <?= $es_edicion && $pregunta['categoria'] == 'arte' ? 'selected' : '' ?>>Arte</option>
                                            <option value="musica" <?= $es_edicion && $pregunta['categoria'] == 'musica' ? 'selected' : '' ?>>Música</option>
                                            <option value="educacion_fisica" <?= $es_edicion && $pregunta['categoria'] == 'educacion_fisica' ? 'selected' : '' ?>>Educación Física</option>
                                            <option value="otra" <?= !$es_edicion || $pregunta['categoria'] == 'otra' ? 'selected' : '' ?>>Otra</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="etiquetas" class="form-label">
                                        <i class="fas fa-hashtag"></i> Etiquetas
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                        <input type="text" class="form-control form-control-sm" id="etiquetas" name="etiquetas" 
                                               placeholder="algebra, ecuaciones, nivel-basico" 
                                               value="<?= $es_edicion ? htmlspecialchars($pregunta['etiquetas'] ?? '') : '' ?>">
                                    </div>
                                    <div class="form-text">Separa las etiquetas con comas</div>
                                </div>
                            </div>
                        </div>

                        <!-- Multimedia -->
                        <div class="card border-light mb-3 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="fas fa-paperclip text-primary me-2"></i> Contenido Multimedia (Opcional)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="media_tipo" class="form-label form-label-sm">
                                                <i class="fas fa-photo-video"></i> Tipo de Multimedia
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="fas fa-photo-video"></i></span>
                                                <select class="form-select form-select-sm" id="media_tipo" name="media_tipo" onchange="cambiarTipoMedia()">
                                                    <option value="ninguno" <?= !$es_edicion || ($pregunta['media_tipo'] ?? 'ninguno') == 'ninguno' ? 'selected' : '' ?>>
                                                        Sin multimedia
                                                    </option>
                                                    <option value="imagen" <?= $es_edicion && ($pregunta['media_tipo'] ?? '') == 'imagen' ? 'selected' : '' ?>>
                                                        Imagen
                                                    </option>
                                                    <option value="video" <?= $es_edicion && ($pregunta['media_tipo'] ?? '') == 'video' ? 'selected' : '' ?>>
                                                        Video (YouTube/Vimeo)
                                                    </option>
                                                    <option value="url" <?= $es_edicion && ($pregunta['media_tipo'] ?? '') == 'url' ? 'selected' : '' ?>>
                                                        Enlace Web
                                                    </option>
                                                    <option value="pdf" <?= $es_edicion && ($pregunta['media_tipo'] ?? '') == 'pdf' ? 'selected' : '' ?>>
                                                        Documento PDF
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Campos dinámicos según tipo multimedia -->
                                        <div id="media_campos">
                                            <?php if ($es_edicion && ($pregunta['media_tipo'] ?? 'ninguno') != 'ninguno'): ?>
                                                <div class="mb-3">
                                                    <label for="media_valor" class="form-label form-label-sm">
                                                        <?php
                                                        switch($pregunta['media_tipo'] ?? '') {
                                                            case 'imagen': echo 'Subir nueva imagen:'; break;
                                                            case 'video': echo 'URL del video:'; break;
                                                            case 'url': echo 'URL del enlace:'; break;
                                                            case 'pdf': echo 'Subir nuevo PDF:'; break;
                                                        }
                                                        ?>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <?php if (in_array($pregunta['media_tipo'] ?? '', ['imagen', 'pdf'])): ?>
                                                            <span class="input-group-text">
                                                                <i class="fas fa-<?= ($pregunta['media_tipo'] ?? '') == 'imagen' ? 'image' : 'file-pdf' ?>"></i>
                                                            </span>
                                                            <input type="file" class="form-control form-control-sm" id="media_valor" name="media_archivo" 
                                                                   accept="<?= ($pregunta['media_tipo'] ?? '') == 'imagen' ? 'image/*' : '.pdf' ?>">
                                                        <?php else: ?>
                                                            <span class="input-group-text">
                                                                <i class="fas fa-<?= ($pregunta['media_tipo'] ?? '') == 'video' ? 'video' : 'link' ?>"></i>
                                                            </span>
                                                            <input type="url" class="form-control form-control-sm" id="media_valor" name="media_valor" 
                                                                   value="<?= htmlspecialchars($pregunta['media_valor'] ?? '') ?>"
                                                                   placeholder="https://">
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (($pregunta['media_valor'] ?? '') && in_array($pregunta['media_tipo'] ?? '', ['imagen', 'pdf'])): ?>
                                                        <small class="text-muted d-block mt-1">
                                                            Archivo actual: <?= basename($pregunta['media_valor']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Opciones de respuesta (solo para test) -->
                        <div id="seccion_respuestas" style="display: <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'block' : 'none' ?>">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fas fa-list-ol text-primary me-2"></i> Opciones de Respuesta
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill" onclick="agregarRespuesta()">
                                            <i class="fas fa-plus"></i> Añadir Opción
                                        </button>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-3"><i class="fas fa-info-circle"></i> Marca las opciones correctas. Mínimo 2 opciones y 1 correcta.</small>
                                        
                                        <div id="contenedor_respuestas" class="rounded px-2 py-2 bg-light">
                                            <?php if ($es_edicion && isset($pregunta['respuestas']) && $pregunta['tipo'] == 'test'): ?>
                                                <?php foreach ($pregunta['respuestas'] as $index => $respuesta): ?>
                                                    <div class="opcion-respuesta mb-2">
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-text bg-light border-primary">
                                                                <input type="checkbox" class="form-check-input" 
                                                                       name="respuestas[<?= $index ?>][correcta]" 
                                                                       <?= $respuesta['correcta'] ? 'checked' : '' ?>>
                                                            </div>
                                                            <input type="text" class="form-control border-primary" 
                                                                   name="respuestas[<?= $index ?>][texto]" 
                                                                   placeholder="Opción de respuesta..." 
                                                                   value="<?= htmlspecialchars($respuesta['texto']) ?>" required>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    onclick="eliminarRespuesta(this)" title="Eliminar opción">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarRespuesta()">
                                                <i class="fas fa-plus"></i> Añadir Opción
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna lateral -->
            <div class="col-lg-4">
                <!-- Información y Acciones -->
                <?php if ($es_edicion): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2">
                                <strong>Creado:</strong><br>
                                <?= date('d/m/Y H:i', strtotime($pregunta['fecha_creacion'])) ?>
                            </li>
                            <li class="mb-2">
                                <strong>Autor:</strong><br>
                                <?= htmlspecialchars($pregunta['nombre_profesor'] . ' ' . $pregunta['apellidos_profesor']) ?>
                            </li>
                            <li>
                                <strong>Origen:</strong>
                                <span class="badge bg-secondary"><?= ucfirst($pregunta['origen']) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Visibilidad (solo admin puede cambiar) -->
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-eye"></i> Visibilidad
                        </h6>
                    </div>
                    <div class="card-body">
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
                </div>
                <?php endif; ?>

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
                        
                        <?php if ($es_edicion): ?>
                            <hr>
                            <button type="button" class="btn btn-outline-danger w-100" 
                                    onclick="eliminarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                <i class="fas fa-trash"></i> Eliminar Pregunta
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar contador de respuestas
    let contadorRespuestas = <?= $es_edicion && isset($pregunta['respuestas']) ? count($pregunta['respuestas']) : 0 ?>;

    // Cambiar tipo de pregunta (Test o Desarrollo)
    window.cambiarTipoPregunta = function() {
        const tipo = document.getElementById('tipo').value;
        const seccionRespuestas = document.getElementById('seccion_respuestas');
        
        if (tipo === 'test') {
            seccionRespuestas.style.display = 'block';
            const contenedor = document.getElementById('contenedor_respuestas');
            if (contenedor && contenedor.children.length === 0) {
                // Añadir mínimo 2 opciones de respuesta vacías
                agregarRespuesta();
                agregarRespuesta();
            }
        } else {
            seccionRespuestas.style.display = 'none';
        }
    };

    // Agregar nueva opción de respuesta
    window.agregarRespuesta = function() {
        const contenedor = document.getElementById('contenedor_respuestas');
        if (!contenedor) return;
        
        const nuevaRespuesta = document.createElement('div');
        nuevaRespuesta.className = 'opcion-respuesta mb-2';
        nuevaRespuesta.innerHTML = `
            <div class="input-group input-group-sm">
                <div class="input-group-text bg-light border-primary">
                    <input type="checkbox" class="form-check-input" name="respuestas[${contadorRespuestas}][correcta]">
                </div>
                <input type="text" class="form-control border-primary" name="respuestas[${contadorRespuestas}][texto]" 
                       placeholder="Opción de respuesta..." required>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRespuesta(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        contenedor.appendChild(nuevaRespuesta);
        contadorRespuestas++;
    };

    // Eliminar opción de respuesta
    window.eliminarRespuesta = function(boton) {
        const opcion = boton.closest('.opcion-respuesta');
        const totalOpciones = document.querySelectorAll('.opcion-respuesta').length;
        
        if (totalOpciones > 2) {
            opcion.remove();
        } else {
            alert('Debe mantener al menos 2 opciones de respuesta');
        }
    };

    // Cambiar tipo multimedia y mostrar los campos correspondientes
    window.cambiarTipoMedia = function() {
        const tipo = document.getElementById('media_tipo').value;
        const contenedor = document.getElementById('media_campos');
        
        if (!contenedor) return;
        contenedor.innerHTML = '';
        
        if (tipo !== 'ninguno') {
            let campo = '';
            let label = '';
            let icono = '';
            
            switch(tipo) {
                case 'imagen':
                    label = 'Subir imagen:';
                    icono = 'image';
                    campo = '<input type="file" class="form-control form-control-sm" name="media_archivo" accept="image/*">';
                    break;
                case 'video':
                    label = 'URL del video:';
                    icono = 'video';
                    campo = '<input type="url" class="form-control form-control-sm" name="media_valor" placeholder="https://www.youtube.com/watch?v=...">';
                    break;
                case 'url':
                    label = 'URL del enlace:';
                    icono = 'link';
                    campo = '<input type="url" class="form-control form-control-sm" name="media_valor" placeholder="https://ejemplo.com">';
                    break;
                case 'pdf':
                    label = 'Subir PDF:';
                    icono = 'file-pdf';
                    campo = '<input type="file" class="form-control form-control-sm" name="media_archivo" accept=".pdf">';
                    break;
            }
            
            contenedor.innerHTML = `
                <div class="mb-3">
                    <label class="form-label form-label-sm">${label}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-${icono}"></i></span>
                        ${campo}
                    </div>
                </div>
            `;
        }
    };

    <?php if ($es_edicion): ?>
    // Eliminar pregunta (solo en edición)
    window.eliminarPregunta = function(idPregunta) {
        if (!confirm('¿Estás seguro de que quieres eliminar esta pregunta? Esta acción no se puede deshacer.')) {
            return;
        }
        
        fetch(`<?= BASE_URL ?>/banco-preguntas/eliminar/${idPregunta}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '<?= BASE_URL ?>/banco-preguntas';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
        });
    };
    <?php endif; ?>

    // Validación del formulario antes de enviar
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const tipo = document.getElementById('tipo');
            
            if (tipo && tipo.value === 'test') {
                const respuestas = document.querySelectorAll('input[name*="[texto]"]');
                const correctas = document.querySelectorAll('input[name*="[correcta]"]:checked');
                
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
    }

    // Inicializar si es edición
    <?php if ($es_edicion): ?>
        cambiarTipoPregunta();
        <?php if (($pregunta['media_tipo'] ?? 'ninguno') != 'ninguno'): ?>
            cambiarTipoMedia();
        <?php endif; ?>
    <?php endif; ?>
});
</script>

    </main>
    
    <?php 
    // Incluir footer y scripts según el rol
    if ($_SESSION['rol'] === 'admin') {
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
    } else {
        require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
        require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
    }
    ?>
</body>
</html>
