<?php
/**
 * Vista del banco de preguntas
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

$titulo_pagina = 'Banco de Preguntas';
require_once __DIR__ . '/../comunes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">🏦 Banco de Preguntas</h1>
            <p class="text-muted">Gestiona y reutiliza preguntas para tus exámenes</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Pregunta
            </a>
            <div class="btn-group ms-2">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/banco-preguntas/exportar?formato=excel">
                        <i class="fas fa-file-excel"></i> Excel (XLSX)
                    </a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/banco-preguntas/exportar?formato=pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['mensaje_error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Pregunta</label>
                    <select class="form-select" id="tipo" name="tipo">
                        <option value="">Todos los tipos</option>
                        <option value="test" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'test' ? 'selected' : '' ?>>Tipo Test</option>
                        <option value="desarrollo" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'desarrollo' ? 'selected' : '' ?>>Desarrollo</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="origen" class="form-label">Origen</label>
                    <select class="form-select" id="origen" name="origen">
                        <option value="">Todos los orígenes</option>
                        <option value="manual" <?= isset($_GET['origen']) && $_GET['origen'] == 'manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="ia" <?= isset($_GET['origen']) && $_GET['origen'] == 'ia' ? 'selected' : '' ?>>Generado por IA</option>
                        <option value="pdf" <?= isset($_GET['origen']) && $_GET['origen'] == 'pdf' ? 'selected' : '' ?>>Extraído de PDF</option>
                    </select>
                </div>
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                <div class="col-md-2">
                    <label for="publica" class="form-label">Visibilidad</label>
                    <select class="form-select" id="publica" name="publica">
                        <option value="">Todas</option>
                        <option value="si" <?= isset($_GET['publica']) && $_GET['publica'] == 'si' ? 'selected' : '' ?>>Públicas</option>
                        <option value="no" <?= isset($_GET['publica']) && $_GET['publica'] == 'no' ? 'selected' : '' ?>>Privadas</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-<?= $_SESSION['rol'] == 'admin' ? '3' : '4' ?>">
                    <label for="busqueda" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" name="busqueda" 
                           placeholder="Buscar en enunciados..." 
                           value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de preguntas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-question-circle"></i> 
                Preguntas del Banco (<?= count($preguntas) ?>)
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="seleccionarTodas()">
                    <i class="fas fa-check-square"></i> Seleccionar todas
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="limpiarSeleccion()">
                    <i class="fas fa-square"></i> Limpiar selección
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($preguntas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay preguntas en el banco</h5>
                    <p class="text-muted">Comienza creando tu primera pregunta reutilizable</p>
                    <a href="<?= BASE_URL ?>/banco-preguntas/crear" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Primera Pregunta
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Pregunta</th>
                                <th width="100">Tipo</th>
                                <th width="120">Origen</th>
                                <th width="150">Autor</th>
                                <th width="120">Estado</th>
                                <th width="100">Respuestas</th>
                                <th width="150">Fecha</th>
                                <th width="120">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($preguntas as $pregunta): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="pregunta-checkbox" 
                                               value="<?= $pregunta['id_pregunta'] ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">
                                                    <?= htmlspecialchars(substr($pregunta['enunciado'], 0, 100)) ?>
                                                    <?= strlen($pregunta['enunciado']) > 100 ? '...' : '' ?>
                                                </div>
                                                <?php if ($pregunta['media_tipo'] != 'ninguno'): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-paperclip"></i> 
                                                        Multimedia: <?= ucfirst($pregunta['media_tipo']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $pregunta['tipo'] == 'test' ? 'primary' : 'info' ?>">
                                            <?= ucfirst($pregunta['tipo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $pregunta['origen'] == 'manual' ? 'secondary' : 
                                            ($pregunta['origen'] == 'ia' ? 'warning' : 'success') 
                                        ?>">
                                            <i class="fas fa-<?= 
                                                $pregunta['origen'] == 'manual' ? 'hand-paper' : 
                                                ($pregunta['origen'] == 'ia' ? 'robot' : 'file-pdf') 
                                            ?>"></i>
                                            <?= ucfirst($pregunta['origen']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?= htmlspecialchars($pregunta['nombre_profesor'] . ' ' . $pregunta['apellidos_profesor']) ?>
                                            <?php if ($pregunta['id_profesor'] == $_SESSION['id_usuario']): ?>
                                                <span class="badge bg-success ms-1">Tuya</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['rol'] == 'admin'): ?>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="publica_<?= $pregunta['id_pregunta'] ?>"
                                                       <?= $pregunta['publica'] ? 'checked' : '' ?>
                                                       onchange="cambiarVisibilidad(<?= $pregunta['id_pregunta'] ?>, this.checked)">
                                                <label class="form-check-label" for="publica_<?= $pregunta['id_pregunta'] ?>">
                                                    <small><?= $pregunta['publica'] ? 'Pública' : 'Privada' ?></small>
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-<?= $pregunta['publica'] ? 'success' : 'secondary' ?>">
                                                <?= $pregunta['publica'] ? 'Pública' : 'Privada' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pregunta['tipo'] == 'test'): ?>
                                            <span class="badge bg-light text-dark">
                                                <?= $pregunta['total_respuestas'] ?? 0 ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($pregunta['fecha_creacion'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php if ($_SESSION['rol'] == 'admin' || $pregunta['id_profesor'] == $_SESSION['id_usuario']): ?>
                                                    <li>
                                                        <a class="dropdown-item" 
                                                           href="<?= BASE_URL ?>/banco-preguntas/editar/<?= $pregunta['id_pregunta'] ?>">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="verDetalle(<?= $pregunta['id_pregunta'] ?>)">
                                                        <i class="fas fa-eye"></i> Ver detalle
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       onclick="duplicarAExamen(<?= $pregunta['id_pregunta'] ?>)">
                                                        <i class="fas fa-copy"></i> Usar en examen
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if ($_SESSION['rol'] == 'admin' || $pregunta['id_profesor'] == $_SESSION['id_usuario']): ?>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" 
                                                           onclick="eliminarPregunta(<?= $pregunta['id_pregunta'] ?>)">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para ver detalle de pregunta -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de la Pregunta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoDetalle">
                <div class="text-center">
                    <div class="spinner-border" role="status"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleccionar examen -->
<div class="modal fade" id="modalSeleccionarExamen" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Examen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="selectExamen" class="form-label">Elige el examen donde usar la pregunta:</label>
                    <select class="form-select" id="selectExamen">
                        <option value="">Cargando exámenes...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarDuplicacion()">
                    <i class="fas fa-copy"></i> Usar Pregunta
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let preguntaSeleccionada = null;

// Seleccionar/deseleccionar todas las preguntas
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.pregunta-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function seleccionarTodas() {
    document.getElementById('selectAll').checked = true;
    toggleSelectAll();
}

function limpiarSeleccion() {
    document.getElementById('selectAll').checked = false;
    toggleSelectAll();
}

// Cambiar visibilidad de pregunta (solo admin)
function cambiarVisibilidad(idPregunta, publica) {
    fetch(`<?= BASE_URL ?>/banco-preguntas/cambiar-visibilidad/${idPregunta}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ publica: publica })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            // Revertir el switch
            document.getElementById(`publica_${idPregunta}`).checked = !publica;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
        // Revertir el switch
        document.getElementById(`publica_${idPregunta}`).checked = !publica;
    });
}

// Ver detalle de pregunta
function verDetalle(idPregunta) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
    modal.show();
    
    fetch(`<?= BASE_URL ?>/banco-preguntas/obtener/${idPregunta}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('contenidoDetalle').innerHTML = formatearDetallePregunta(data.pregunta);
        } else {
            document.getElementById('contenidoDetalle').innerHTML = 
                '<div class="alert alert-danger">Error al cargar la pregunta</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('contenidoDetalle').innerHTML = 
            '<div class="alert alert-danger">Error de conexión</div>';
    });
}

// Formatear detalle de pregunta para el modal
function formatearDetallePregunta(pregunta) {
    let html = `
        <div class="mb-3">
            <h6>Tipo de Pregunta</h6>
            <span class="badge bg-${pregunta.tipo == 'test' ? 'primary' : 'info'}">${pregunta.tipo.toUpperCase()}</span>
        </div>
        
        <div class="mb-3">
            <h6>Enunciado</h6>
            <div class="border p-3 rounded">${pregunta.enunciado}</div>
        </div>
    `;
    
    if (pregunta.respuestas && pregunta.respuestas.length > 0) {
        html += `
            <div class="mb-3">
                <h6>Respuestas</h6>
                <div class="list-group">
        `;
        
        pregunta.respuestas.forEach((respuesta, index) => {
            html += `
                <div class="list-group-item ${respuesta.correcta == 1 ? 'list-group-item-success' : ''}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${respuesta.texto}</span>
                        ${respuesta.correcta == 1 ? '<i class="fas fa-check text-success"></i>' : ''}
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    html += `
        <div class="row">
            <div class="col-md-6">
                <h6>Origen</h6>
                <span class="badge bg-secondary">${pregunta.origen.toUpperCase()}</span>
            </div>
            <div class="col-md-6">
                <h6>Visibilidad</h6>
                <span class="badge bg-${pregunta.publica == 1 ? 'success' : 'secondary'}">
                    ${pregunta.publica == 1 ? 'Pública' : 'Privada'}
                </span>
            </div>
        </div>
    `;
    
    return html;
}

// Duplicar pregunta a examen
function duplicarAExamen(idPregunta) {
    preguntaSeleccionada = idPregunta;
    cargarExamenes();
    const modal = new bootstrap.Modal(document.getElementById('modalSeleccionarExamen'));
    modal.show();
}

// Cargar lista de exámenes
function cargarExamenes() {
    fetch(`<?= BASE_URL ?>/examenes/obtener-lista`)
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('selectExamen');
        select.innerHTML = '<option value="">Selecciona un examen...</option>';
        
        if (data.success && data.examenes) {
            data.examenes.forEach(examen => {
                select.innerHTML += `<option value="${examen.id_examen}">${examen.titulo} - ${examen.nombre_curso}</option>`;
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('selectExamen').innerHTML = '<option value="">Error al cargar exámenes</option>';
    });
}

// Confirmar duplicación de pregunta
function confirmarDuplicacion() {
    const idExamen = document.getElementById('selectExamen').value;
    
    if (!idExamen) {
        alert('Por favor selecciona un examen');
        return;
    }
    
    fetch(`<?= BASE_URL ?>/preguntas/importar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            preguntas: [preguntaSeleccionada],
            id_examen: parseInt(idExamen)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pregunta agregada al examen correctamente');
            bootstrap.Modal.getInstance(document.getElementById('modalSeleccionarExamen')).hide();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// Eliminar pregunta
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
</script>

<?php require_once __DIR__ . '/../comunes/footer.php'; ?>
