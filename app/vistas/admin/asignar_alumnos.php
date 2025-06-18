<?php
/**
 * Vista para asignar alumnos a un curso (Administrador)
 * AUTOEXAM2 - 16/06/2025
 */

// Variables para la vista
$id_curso = $curso['id_curso'];
$nombre_curso = $curso['nombre_curso'];
?>

<!-- Título de la página -->
<div class="container-fluid px-4 py-4">
    <h1 class="mt-2 mb-4">
        <i class="fas fa-user-graduate me-2"></i> Asignar alumnos a curso
    </h1>

    <!-- Alertas y mensajes -->
    <?php if(isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
        <?= $_SESSION['mensaje'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['mensaje']); 
    unset($_SESSION['tipo_mensaje']);
    endif;
    
    // Errores específicos
    if(isset($_SESSION['errores']) && is_array($_SESSION['errores'])): 
    ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading">Se encontraron errores:</h5>
        <ul class="mb-0">
            <?php foreach($_SESSION['errores'] as $error): ?>
            <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['errores']);
    endif; 
    ?>

    <!-- Información del curso -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i> Información del curso
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>ID del curso:</strong> <?= $id_curso ?></p>
                    <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($nombre_curso) ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Profesor:</strong> <?= htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellidos_profesor']) ?></p>
                    <p class="mb-1"><strong>Estado:</strong> 
                        <?php if($curso['activo'] == 1): ?>
                        <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                        <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Alumnos asignados -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-users me-1"></i> Alumnos asignados
                    </div>
                    <span class="badge bg-primary"><?= count($alumnos_asignados) ?> alumnos</span>
                </div>
                <div class="card-body">
                    <?php if(count($alumnos_asignados) > 0): ?>
                    <form action="<?= BASE_URL ?>/cursos/procesarAsignacion" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllDesasignar">
                                            </div>
                                        </th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Correo</th>
                                        <th scope="col">Fecha asignación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($alumnos_asignados as $alumno): ?>
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input checkbox-desasignar" type="checkbox" 
                                                       name="desasignar[]" value="<?= $alumno['id_usuario'] ?>">
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombre']) ?></td>
                                        <td><?= htmlspecialchars($alumno['correo']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($alumno['fecha_asignacion'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-danger btn-sm" id="btnDesasignarAlumnos" disabled>
                                <i class="fas fa-user-minus me-1"></i> Desasignar seleccionados
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-1"></i> No hay alumnos asignados a este curso.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alumnos disponibles -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-user-plus me-1"></i> Asignar nuevos alumnos
                    </div>
                    <span class="badge bg-secondary"><?= count($alumnos_disponibles) ?> disponibles</span>
                </div>
                <div class="card-body">
                    <?php if(count($alumnos_disponibles) > 0): ?>
                    <form action="<?= BASE_URL ?>/cursos/procesarAsignacion" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                        
                        <!-- Búsqueda de alumnos -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control" id="buscarAlumno" 
                                       placeholder="Buscar por nombre o correo">
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllAsignar">
                                            </div>
                                        </th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Correo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($alumnos_disponibles as $alumno): ?>
                                    <tr class="fila-alumno">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input checkbox-asignar" type="checkbox" 
                                                       name="alumnos[]" value="<?= $alumno['id_usuario'] ?>">
                                            </div>
                                        </td>
                                        <td class="alumno-nombre">
                                            <?= htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombre']) ?>
                                        </td>
                                        <td class="alumno-correo"><?= htmlspecialchars($alumno['correo']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success btn-sm" id="btnAsignarAlumnos" disabled>
                                <i class="fas fa-user-plus me-1"></i> Asignar seleccionados
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i> No hay alumnos disponibles para asignar.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="d-flex gap-2 mb-4">
        <a href="<?= BASE_URL ?>/cursos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a cursos
        </a>
        <a href="<?= BASE_URL ?>/cursos/editar?id=<?= $id_curso ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Editar curso
        </a>
        <a href="<?= BASE_URL ?>/cursos/ver?id=<?= $id_curso ?>" class="btn btn-info">
            <i class="fas fa-eye me-1"></i> Ver curso
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Seleccionar todos los checkboxes de desasignación
        const selectAllDesasignarCheckbox = document.getElementById('selectAllDesasignar');
        const desasignarCheckboxes = document.querySelectorAll('.checkbox-desasignar');
        const btnDesasignarAlumnos = document.getElementById('btnDesasignarAlumnos');
        
        if (selectAllDesasignarCheckbox) {
            selectAllDesasignarCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                
                desasignarCheckboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
                
                btnDesasignarAlumnos.disabled = !isChecked;
            });
        }
        
        // Verificar estado de checkboxes individuales de desasignación
        if (desasignarCheckboxes.length > 0) {
            desasignarCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const haySeleccionados = Array.from(desasignarCheckboxes).some(cb => cb.checked);
                    btnDesasignarAlumnos.disabled = !haySeleccionados;
                    
                    // Actualizar estado del "seleccionar todos" si es necesario
                    if (selectAllDesasignarCheckbox) {
                        const todosSeleccionados = Array.from(desasignarCheckboxes).every(cb => cb.checked);
                        selectAllDesasignarCheckbox.checked = todosSeleccionados;
                        selectAllDesasignarCheckbox.indeterminate = haySeleccionados && !todosSeleccionados;
                    }
                });
            });
        }
        
        // Seleccionar todos los checkboxes de asignación
        const selectAllAsignarCheckbox = document.getElementById('selectAllAsignar');
        const asignarCheckboxes = document.querySelectorAll('.checkbox-asignar');
        const btnAsignarAlumnos = document.getElementById('btnAsignarAlumnos');
        
        if (selectAllAsignarCheckbox) {
            selectAllAsignarCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                const filasVisibles = document.querySelectorAll('.fila-alumno:not([style*="display: none"])');
                
                filasVisibles.forEach(fila => {
                    const checkbox = fila.querySelector('.checkbox-asignar');
                    if (checkbox) checkbox.checked = isChecked;
                });
                
                const haySeleccionados = Array.from(asignarCheckboxes).some(cb => cb.checked);
                btnAsignarAlumnos.disabled = !haySeleccionados;
            });
        }
        
        // Verificar estado de checkboxes individuales de asignación
        if (asignarCheckboxes.length > 0) {
            asignarCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const haySeleccionados = Array.from(asignarCheckboxes).some(cb => cb.checked);
                    btnAsignarAlumnos.disabled = !haySeleccionados;
                    
                    // Actualizar estado del "seleccionar todos"
                    if (selectAllAsignarCheckbox) {
                        const filasVisibles = document.querySelectorAll('.fila-alumno:not([style*="display: none"])');
                        const checkboxesVisibles = Array.from(filasVisibles).map(fila => fila.querySelector('.checkbox-asignar'));
                        
                        const todosSeleccionados = checkboxesVisibles.every(cb => cb.checked);
                        const algunoSeleccionado = checkboxesVisibles.some(cb => cb.checked);
                        
                        selectAllAsignarCheckbox.checked = todosSeleccionados;
                        selectAllAsignarCheckbox.indeterminate = algunoSeleccionado && !todosSeleccionados;
                    }
                });
            });
        }
        
        // Filtrado de alumnos disponibles
        const buscarAlumnoInput = document.getElementById('buscarAlumno');
        const filasAlumnos = document.querySelectorAll('.fila-alumno');
        
        if (buscarAlumnoInput) {
            buscarAlumnoInput.addEventListener('input', function() {
                const valor = this.value.toLowerCase();
                let hayResultados = false;
                
                filasAlumnos.forEach(fila => {
                    const nombre = fila.querySelector('.alumno-nombre').textContent.toLowerCase();
                    const correo = fila.querySelector('.alumno-correo').textContent.toLowerCase();
                    
                    if (nombre.includes(valor) || correo.includes(valor)) {
                        fila.style.display = '';
                        hayResultados = true;
                    } else {
                        fila.style.display = 'none';
                    }
                });
                
                // Actualizar estado del "seleccionar todos"
                if (selectAllAsignarCheckbox) {
                    const filasVisibles = document.querySelectorAll('.fila-alumno:not([style*="display: none"])');
                    const checkboxesVisibles = Array.from(filasVisibles).map(fila => fila.querySelector('.checkbox-asignar'));
                    
                    const todosSeleccionados = checkboxesVisibles.length > 0 && checkboxesVisibles.every(cb => cb.checked);
                    
                    selectAllAsignarCheckbox.checked = todosSeleccionados;
                    selectAllAsignarCheckbox.indeterminate = !todosSeleccionados && checkboxesVisibles.some(cb => cb.checked);
                }
            });
        }
    });
</script>
