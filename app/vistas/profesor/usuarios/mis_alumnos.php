<?php
/**
 * Vista de Mis Alumnos - AUTOEXAM2 (Rol Profesor)
 * 
 * Muestra todos los alumnos asignados a los cursos del profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

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
    .curso-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users text-primary"></i> Mis Alumnos</h1>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cursos
            </a>
            <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nuevo Alumno
            </a>
        </div>
    </div>

    <!-- Mensajes de estado -->
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $datos['total_alumnos'] ?></h4>
                            <small>Total Alumnos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $datos['alumnos_asignados'] ?></h4>
                            <small>Alumnos Asignados</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $datos['cursos_con_alumnos'] ?></h4>
                            <small>Cursos con Alumnos</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4><?= $datos['alumnos_sin_asignar'] ?></h4>
                            <small>Sin Asignar</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de alumnos -->
    <div class="card shadow-sm">
        <div class="card-header bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate text-primary me-2"></i> 
                    Lista de Alumnos
                </h5>
                <span class="badge bg-primary text-white">
                    <?= $datos['total_alumnos'] ?> alumnos
                </span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($datos['alumnos'])): ?>
                <div class="alert alert-info mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h5 class="alert-heading">No hay alumnos asignados</h5>
                            <p class="mb-0">Aún no tienes alumnos asignados a tus cursos. Puedes crear nuevos alumnos o contactar con el administrador para asignar alumnos existentes.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">
                                    <i class="fas fa-user"></i> Alumno
                                </th>
                                <th scope="col">
                                    <i class="fas fa-envelope"></i> Correo
                                </th>
                                <th scope="col">
                                    <i class="fas fa-book"></i> Curso
                                </th>
                                <th scope="col">
                                    <i class="fas fa-calendar"></i> Fecha Asignación
                                </th>
                                <th scope="col">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </th>
                                <th scope="col" class="text-center">
                                    <i class="fas fa-cogs"></i> Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($datos['alumnos'] as $alumno): ?>
                            <tr class="<?= $alumno['activo'] == 0 ? 'table-secondary opacity-75' : '' ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if (!empty($alumno['foto'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($alumno['foto']) ?>" 
                                                     class="rounded-circle" width="56" height="56" 
                                                     alt="Avatar" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 56px; height: 56px; min-width: 56px;">
                                                    <i class="fas fa-user text-white" style="font-size: 24px;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombre']) ?></h6>
                                            <small class="text-muted">ID: <?= $alumno['id_usuario'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted"><?= htmlspecialchars($alumno['correo']) ?></span>
                                </td>
                                <td>
                                    <?php if ($alumno['id_curso'] === null): ?>
                                        <span class="badge rounded-pill bg-light text-muted border border-secondary-subtle small">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Sin asignar
                                        </span>
                                    <?php else: ?>
                                        <?php 
                                        $coloresCurso = generarColorCurso($alumno['nombre_curso']);
                                        ?>
                                        <span class="badge rounded-pill <?= $coloresCurso[0] ?> <?= $coloresCurso[1] ?> border <?= $coloresCurso[2] ?> small">
                                            <i class="fas fa-book me-1"></i><?= htmlspecialchars($alumno['nombre_curso']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?= $alumno['fecha_asignacion'] ? date('d/m/Y', strtotime($alumno['fecha_asignacion'])) : 'N/A' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($alumno['activo']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/usuarios/editar/<?= $alumno['id_usuario'] ?>" 
                                           class="btn btn-outline-primary" title="Editar alumno">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($alumno['id_curso'] !== null): ?>
                                            <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $alumno['id_curso'] ?>" 
                                               class="btn btn-outline-info" title="Ver curso">
                                                <i class="fas fa-book"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>/cursos/asignarAlumnos" 
                                               class="btn btn-outline-warning" title="Asignar a curso">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Eliminar alumno"
                                                onclick="confirmarEliminacion(<?= $alumno['id_usuario'] ?>, '<?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal de confirmación para eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 48px; height: 48px;">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">¿Estás seguro de que quieres eliminar este alumno?</h6>
                        <p class="mb-0 text-muted">Esta acción no se puede deshacer.</p>
                    </div>
                </div>
                <div class="alert alert-warning d-flex align-items-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>
                        <strong>Alumno a eliminar:</strong> <span id="nombreAlumno"></span>
                    </div>
                </div>
                <p class="text-muted small mb-0">
                    Se eliminarán todos los datos asociados al alumno, incluyendo inscripciones, actividades y registros.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmarEliminar">
                    <i class="fas fa-trash me-1"></i>Eliminar alumno
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="formEliminar" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
</form>

<?php 
// Función para generar colores consistentes por curso
function generarColorCurso($nombreCurso) {
    if (empty($nombreCurso)) {
        return ['bg-light', 'text-muted', 'border-secondary'];
    }
    
    $colores = [
        ['bg-primary-subtle', 'text-primary', 'border-primary-subtle'],
        ['bg-info-subtle', 'text-info', 'border-info-subtle'],
        ['bg-success-subtle', 'text-success', 'border-success-subtle'],
        ['bg-warning-subtle', 'text-warning', 'border-warning-subtle'],
        ['bg-danger-subtle', 'text-danger', 'border-danger-subtle'],
        ['bg-purple-subtle', 'text-purple', 'border-purple'],
        ['bg-secondary-subtle', 'text-secondary', 'border-secondary-subtle']
    ];
    
    // Usar hash del nombre para consistencia
    $indice = crc32($nombreCurso) % count($colores);
    return $colores[abs($indice)];
}
?>

<script>
// Variables globales
let idUsuarioEliminar = null;

// Función para confirmar eliminación
function confirmarEliminacion(id, nombre) {
    idUsuarioEliminar = id;
    document.getElementById('nombreAlumno').textContent = nombre;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
}

// Manejar confirmación de eliminación
document.getElementById('confirmarEliminar').addEventListener('click', function() {
    if (idUsuarioEliminar) {
        // Configurar el formulario
        const form = document.getElementById('formEliminar');
        form.action = '<?= BASE_URL ?>/usuarios/eliminar/' + idUsuarioEliminar;
        
        // Enviar formulario
        form.submit();
    }
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
