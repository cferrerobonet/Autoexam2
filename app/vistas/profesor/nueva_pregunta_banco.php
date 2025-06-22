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
if (!isset($_SESSION['usuario_logueado']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'profesor')) {
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
<!DOCTYPE html>
<html lang="es">
<head>
    <title><?= $titulo_pagina ?> - <?= SYSTEM_NAME ?></title>
</head>
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

    <form method="POST" enctype="multipart/form-data" id="formPregunta">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <?php if ($es_edicion): ?>
            <input type="hidden" name="id_pregunta" value="<?= $pregunta['id_pregunta'] ?>">
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
                        <!-- Tipo de pregunta -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Pregunta *</label>
                            <select class="form-select" id="tipo" name="tipo" required onchange="cambiarTipoPregunta()">
                                <option value="">Selecciona el tipo</option>
                                <option value="test" <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'selected' : '' ?>>
                                    Tipo Test (opciones múltiples)
                                </option>
                                <option value="desarrollo" <?= $es_edicion && $pregunta['tipo'] == 'desarrollo' ? 'selected' : '' ?>>
                                    Desarrollo (respuesta abierta)
                                </option>
                            </select>
                        </div>

                        <!-- Enunciado -->
                        <div class="mb-3">
                            <label for="enunciado" class="form-label">Enunciado de la Pregunta *</label>
                            <textarea class="form-control" id="enunciado" name="enunciado" rows="6" required 
                                      placeholder="Escribe aquí el enunciado de tu pregunta..."><?= $es_edicion ? htmlspecialchars($pregunta['enunciado']) : '' ?></textarea>
                            <small class="form-text text-muted">
                                Puedes usar HTML básico para formato (negrita, cursiva, listas, etc.)
                            </small>
                        </div>

                        <!-- Multimedia -->
                        <div class="card border-light mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-paperclip"></i> Contenido Multimedia (Opcional)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="media_tipo" class="form-label">Tipo de Multimedia</label>
                                    <select class="form-select" id="media_tipo" name="media_tipo" onchange="cambiarTipoMedia()">
                                        <option value="ninguno" <?= !$es_edicion || $pregunta['media_tipo'] == 'ninguno' ? 'selected' : '' ?>>
                                            Sin multimedia
                                        </option>
                                        <option value="imagen" <?= $es_edicion && $pregunta['media_tipo'] == 'imagen' ? 'selected' : '' ?>>
                                            Imagen
                                        </option>
                                        <option value="video" <?= $es_edicion && $pregunta['media_tipo'] == 'video' ? 'selected' : '' ?>>
                                            Video (YouTube/Vimeo)
                                        </option>
                                        <option value="url" <?= $es_edicion && $pregunta['media_tipo'] == 'url' ? 'selected' : '' ?>>
                                            Enlace Web
                                        </option>
                                        <option value="pdf" <?= $es_edicion && $pregunta['media_tipo'] == 'pdf' ? 'selected' : '' ?>>
                                            Documento PDF
                                        </option>
                                    </select>
                                </div>

                                <!-- Campos dinámicos según tipo multimedia -->
                                <div id="media_campos">
                                    <?php if ($es_edicion && $pregunta['media_tipo'] != 'ninguno'): ?>
                                        <div class="mb-3">
                                            <label for="media_valor" class="form-label">
                                                <?php
                                                switch($pregunta['media_tipo']) {
                                                    case 'imagen': echo 'Subir nueva imagen o mantener actual:'; break;
                                                    case 'video': echo 'URL del video:'; break;
                                                    case 'url': echo 'URL del enlace:'; break;
                                                    case 'pdf': echo 'Subir nuevo PDF o mantener actual:'; break;
                                                }
                                                ?>
                                            </label>
                                            <?php if (in_array($pregunta['media_tipo'], ['imagen', 'pdf'])): ?>
                                                <input type="file" class="form-control" id="media_valor" name="media_archivo" 
                                                       accept="<?= $pregunta['media_tipo'] == 'imagen' ? 'image/*' : '.pdf' ?>">
                                                <?php if ($pregunta['media_valor']): ?>
                                                    <small class="text-muted">
                                                        Archivo actual: <?= basename($pregunta['media_valor']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <input type="url" class="form-control" id="media_valor" name="media_valor" 
                                                       value="<?= htmlspecialchars($pregunta['media_valor'] ?? '') ?>"
                                                       placeholder="https://">
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Respuestas (solo para tipo test) -->
                        <div id="seccion_respuestas" style="display: <?= $es_edicion && $pregunta['tipo'] == 'test' ? 'block' : 'none' ?>">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list"></i> Opciones de Respuesta
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        Añade al menos 2 opciones y marca las correctas. Puedes tener múltiples respuestas correctas.
                                    </p>
                                    
                                    <div id="contenedor_respuestas">
                                        <?php if ($es_edicion && $pregunta['tipo'] == 'test' && isset($pregunta['respuestas'])): ?>
                                            <?php foreach ($pregunta['respuestas'] as $index => $respuesta): ?>
                                                <div class="respuesta-item mb-3 p-3 border rounded" data-index="<?= $index ?>">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-1">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       name="respuestas[<?= $index ?>][correcta]" 
                                                                       <?= $respuesta['correcta'] ? 'checked' : '' ?>>
                                                                <label class="form-check-label">
                                                                    <small>Correcta</small>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-10">
                                                            <input type="text" class="form-control" 
                                                                   name="respuestas[<?= $index ?>][texto]" 
                                                                   placeholder="Escribe la opción de respuesta..."
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
                        <!-- Visibilidad (solo admin puede cambiar) -->
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

                        <!-- Información adicional -->
                        <?php if ($es_edicion): ?>
                            <div class="border-top pt-3">
                                <h6>Información</h6>
                                <ul class="list-unstyled small">
                                    <li>
                                        <strong>Creado:</strong> 
                                        <?= date('d/m/Y H:i', strtotime($pregunta['fecha_creacion'])) ?>
                                    </li>
                                    <li>
                                        <strong>Autor:</strong> 
                                        <?= htmlspecialchars($pregunta['nombre_profesor'] . ' ' . $pregunta['apellidos_profesor']) ?>
                                    </li>
                                    <li>
                                        <strong>Origen:</strong> 
                                        <span class="badge bg-secondary"><?= ucfirst($pregunta['origen']) ?></span>
                                    </li>
                                </ul>
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
let contadorRespuestas = <?= $es_edicion && isset($pregunta['respuestas']) ? count($pregunta['respuestas']) : 0 ?>;

// Cambiar tipo de pregunta
function cambiarTipoPregunta() {
    const tipo = document.getElementById('tipo').value;
    const seccionRespuestas = document.getElementById('seccion_respuestas');
    
    if (tipo === 'test') {
        seccionRespuestas.style.display = 'block';
        // Si no hay respuestas, agregar 2 por defecto
        if (contadorRespuestas === 0) {
            agregarRespuesta();
            agregarRespuesta();
        }
    } else {
        seccionRespuestas.style.display = 'none';
    }
}

// Cambiar tipo de multimedia
function cambiarTipoMedia() {
    const tipo = document.getElementById('media_tipo').value;
    const contenedor = document.getElementById('media_campos');
    
    if (tipo === 'ninguno') {
        contenedor.innerHTML = '';
        return;
    }
    
    let html = '<div class="mb-3"><label for="media_valor" class="form-label">';
    
    switch(tipo) {
        case 'imagen':
            html += 'Subir Imagen:</label>';
            html += '<input type="file" class="form-control" id="media_valor" name="media_archivo" accept="image/*">';
            html += '<small class="form-text text-muted">Formatos admitidos: JPG, PNG, GIF (máx. 5MB)</small>';
            break;
        case 'video':
            html += 'URL del Video:</label>';
            html += '<input type="url" class="form-control" id="media_valor" name="media_valor" placeholder="https://youtube.com/watch?v=... o https://vimeo.com/...">';
            html += '<small class="form-text text-muted">URLs de YouTube o Vimeo</small>';
            break;
        case 'url':
            html += 'URL del Enlace:</label>';
            html += '<input type="url" class="form-control" id="media_valor" name="media_valor" placeholder="https://ejemplo.com">';
            html += '<small class="form-text text-muted">Enlace a página web o recurso online</small>';
            break;
        case 'pdf':
            html += 'Subir PDF:</label>';
            html += '<input type="file" class="form-control" id="media_valor" name="media_archivo" accept=".pdf">';
            html += '<small class="form-text text-muted">Documento PDF (máx. 10MB)</small>';
            break;
    }
    
    html += '</div>';
    contenedor.innerHTML = html;
}

// Agregar nueva respuesta
function agregarRespuesta() {
    const contenedor = document.getElementById('contenedor_respuestas');
    const div = document.createElement('div');
    div.className = 'respuesta-item mb-3 p-3 border rounded';
    div.setAttribute('data-index', contadorRespuestas);
    
    div.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="respuestas[${contadorRespuestas}][correcta]">
                    <label class="form-check-label">
                        <small>Correcta</small>
                    </label>
                </div>
            </div>
            <div class="col-md-10">
                <input type="text" class="form-control" name="respuestas[${contadorRespuestas}][texto]" 
                       placeholder="Escribe la opción de respuesta..." required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarRespuesta(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    contenedor.appendChild(div);
    contadorRespuestas++;
}

// Eliminar respuesta
function eliminarRespuesta(boton) {
    const respuestaItem = boton.closest('.respuesta-item');
    const contenedor = document.getElementById('contenedor_respuestas');
    
    // No permitir eliminar si solo quedan 2 respuestas
    if (contenedor.children.length <= 2) {
        alert('Debe haber al menos 2 opciones de respuesta');
        return;
    }
    
    respuestaItem.remove();
}

// Eliminar pregunta (solo en edición)
<?php if ($es_edicion): ?>
function eliminarPregunta(idPregunta) {
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
}
<?php endif; ?>

// Validación del formulario
document.getElementById('formPregunta').addEventListener('submit', function(e) {
    const tipo = document.getElementById('tipo').value;
    
    if (tipo === 'test') {
        const respuestas = document.querySelectorAll('input[name*="[texto]"]');
        const correctas = document.querySelectorAll('input[name*="[correcta]"]:checked');
        
        let respuestasValidas = 0;
        respuestas.forEach(input => {
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
            alert('Debe marcar al menos una respuesta como correcta');
            return;
        }
    }
});

// Inicializar campos multimedia si es edición
<?php if ($es_edicion && $pregunta['media_tipo'] != 'ninguno'): ?>
document.addEventListener('DOMContentLoaded', function() {
    cambiarTipoMedia();
});
<?php endif; ?>
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
