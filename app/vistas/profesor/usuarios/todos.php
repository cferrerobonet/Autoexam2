<?php
/**
 * Vista de Listar Usuarios - AUTOEXAM2 (Rol Profesor)
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'profesor') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}

// Extraer variables de los datos
extract($datos);
?>

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_profesor.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users"></i> Gestión de Alumnos</h1>
            <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cursos
            </a>
        </div>

        <!-- Alertas -->
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

        <!-- Filtros y búsqueda -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-filter text-primary me-2"></i> Filtros
                </h5>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/usuarios" method="GET" class="row g-3">
                    <!-- Búsqueda -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                            <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, apellido, correo..." 
                                   value="<?= isset($filtros['buscar']) ? htmlspecialchars($filtros['buscar']) : '' ?>">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </div>
                    
                    <!-- Estado -->
                    <div class="col-md-3">
                        <select name="activo" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="1" <?= (isset($filtros['activo']) && $filtros['activo'] === 1) ? 'selected' : '' ?>>Activos</option>
                            <option value="0" <?= (isset($filtros['activo']) && $filtros['activo'] === 0) ? 'selected' : '' ?>>Inactivos</option>
                        </select>
                    </div>
                    
                    <!-- Ordenar por -->
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-sort"></i></span>
                            <select name="ordenar_por" class="form-select">
                                <option value="apellidos" <?= (isset($filtros['ordenar_por']) && $filtros['ordenar_por'] === 'apellidos') ? 'selected' : '' ?>>Apellidos</option>
                                <option value="nombre" <?= (isset($filtros['ordenar_por']) && $filtros['ordenar_por'] === 'nombre') ? 'selected' : '' ?>>Nombre</option>
                                <option value="correo" <?= (isset($filtros['ordenar_por']) && $filtros['ordenar_por'] === 'correo') ? 'selected' : '' ?>>Correo</option>
                                <option value="ultimo_acceso" <?= (isset($filtros['ordenar_por']) && $filtros['ordenar_por'] === 'ultimo_acceso') ? 'selected' : '' ?>>Último acceso</option>
                            </select>
                            <select name="orden" class="form-select">
                                <option value="ASC" <?= (!isset($filtros['orden']) || $filtros['orden'] === 'ASC') ? 'selected' : '' ?>>Ascendente</option>
                                <option value="DESC" <?= (isset($filtros['orden']) && $filtros['orden'] === 'DESC') ? 'selected' : '' ?>>Descendente</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="col-md-2 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Aplicar
                        </button>
                        <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de usuarios -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-primary me-2"></i> 
                        Alumnos asignados
                    </h5>
                    <div>
                        <a href="<?= BASE_URL ?>/usuarios/crear" class="btn btn-success me-2">
                            <i class="fas fa-user-plus"></i> Crear alumno
                        </a>
                        <span class="badge bg-primary text-white px-3 py-2">
                            Total: <?= $total_usuarios ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($usuarios) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Alumno</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Curso</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= $usuario['id_usuario'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($usuario['foto'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($usuario['foto']) ?>" 
                                                     class="rounded-circle me-2" width="40" height="40" alt="Avatar" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($usuario['apellidos'] . ', ' . $usuario['nombre']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                    <td>
                                        <?php if ($usuario['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            if (!empty($usuario['curso_asignado'])) {
                                                echo '<span class="badge bg-primary">' . $usuario['curso_asignado'] . '</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Sin asignar</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/usuarios/editar/<?= $usuario['id_usuario'] ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_paginas > 1): ?>
                    <!-- Paginación -->
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Anterior -->
                            <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($filtros, ['pagina' => $pagina_actual - 1])) ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <!-- Páginas -->
                            <?php 
                            $inicio = max(1, $pagina_actual - 2);
                            $fin = min($total_paginas, $pagina_actual + 2);
                            
                            if ($inicio > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . '/usuarios?' . http_build_query(array_merge($filtros, ['pagina' => 1])) . '">1</a></li>';
                                
                                if ($inicio > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $inicio; $i <= $fin; $i++): 
                            ?>
                                <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($filtros, ['pagina' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php 
                            endfor;
                            
                            if ($fin < $total_paginas) {
                                if ($fin < $total_paginas - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                
                                echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . '/usuarios?' . http_build_query(array_merge($filtros, ['pagina' => $total_paginas])) . '">' . $total_paginas . '</a></li>';
                            }
                            ?>
                            
                            <!-- Siguiente -->
                            <li class="page-item <?= ($pagina_actual >= $total_paginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/usuarios?<?= http_build_query(array_merge($filtros, ['pagina' => $pagina_actual + 1])) ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron alumnos con los criterios de búsqueda especificados.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php require_once APP_PATH . '/vistas/comunes/pie.php'; ?>
</body>
</html>
