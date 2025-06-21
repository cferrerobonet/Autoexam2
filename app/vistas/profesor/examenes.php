<?php
// Verificar que estamos en el contexto correcto
if (!isset($examenes)) {
    header("Location: " . BASE_URL);
    exit;
}

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
    <title>Gestión de Exámenes - AUTOEXAM2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .estado-badge {
            font-size: 0.8em;
        }
        .examen-card {
            transition: transform 0.2s;
            border-left: 4px solid #dee2e6;
        }
        .examen-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .examen-card.activo {
            border-left-color: #28a745;
        }
        .examen-card.borrador {
            border-left-color: #ffc107;
        }
        .examen-card.finalizado {
            border-left-color: #dc3545;
        }
        .btn-action {
            margin: 2px;
        }
        .filtros-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
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
                        <i class="fas fa-file-alt me-2"></i>
                        Gestión de Exámenes
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i>
                            Crear Examen
                        </a>
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i>
                            Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportarExamenes('excel')">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportarExamenes('pdf')">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a></li>
                        </ul>
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

                <!-- Filtros -->
                <div class="filtros-container">
                    <form method="GET" action="<?= BASE_URL ?>/examenes" class="row g-3">
                        <div class="col-md-3">
                            <label for="filtro_titulo" class="form-label">Buscar por título</label>
                            <input type="text" class="form-control" id="filtro_titulo" name="titulo" 
                                   value="<?= htmlspecialchars($_GET['titulo'] ?? '') ?>" 
                                   placeholder="Título del examen...">
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_curso" class="form-label">Curso</label>
                            <select class="form-select" id="filtro_curso" name="curso">
                                <option value="">Todos los cursos</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>" 
                                            <?= ($_GET['curso'] ?? '') == $curso['id_curso'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($curso['nombre_curso']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_modulo" class="form-label">Módulo</label>
                            <select class="form-select" id="filtro_modulo" name="modulo">
                                <option value="">Todos los módulos</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?= $modulo['id_modulo'] ?>" 
                                            <?= ($_GET['modulo'] ?? '') == $modulo['id_modulo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modulo['titulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro_estado" class="form-label">Estado</label>
                            <select class="form-select" id="filtro_estado" name="estado">
                                <option value="">Todos los estados</option>
                                <option value="borrador" <?= ($_GET['estado'] ?? '') == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                <option value="activo" <?= ($_GET['estado'] ?? '') == 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="finalizado" <?= ($_GET['estado'] ?? '') == 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filtrar
                            </button>
                            <a href="<?= BASE_URL ?>/examenes" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Lista de exámenes -->
                <?php if (empty($examenes)): ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-file-alt fa-4x text-muted"></i>
                        </div>
                        <h4 class="text-muted">No hay exámenes disponibles</h4>
                        <p class="text-muted">Comienza creando tu primer examen</p>
                        <a href="<?= BASE_URL ?>/examenes/crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Crear Primer Examen
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Controles de selección múltiple -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="seleccionar_todos">
                            <label class="form-check-label" for="seleccionar_todos">
                                Seleccionar todos
                            </label>
                        </div>
                        <div class="btn-group" id="acciones_multiples" style="display: none;">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarSeleccionados()">
                                <i class="fas fa-trash me-1"></i>
                                Eliminar seleccionados
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="cambiarEstadoSeleccionados('borrador')">
                                <i class="fas fa-edit me-1"></i>
                                Marcar como borrador
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="cambiarEstadoSeleccionados('activo')">
                                <i class="fas fa-play me-1"></i>
                                Activar seleccionados
                            </button>
                        </div>
                    </div>

                    <!-- Tarjetas de exámenes -->
                    <div class="row">
                        <?php foreach ($examenes as $examen): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card examen-card <?= $examen['estado'] ?>" data-examen-id="<?= $examen['id_examen'] ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input examen-checkbox" type="checkbox" 
                                                   value="<?= $examen['id_examen'] ?>" id="check_<?= $examen['id_examen'] ?>">
                                        </div>
                                        <div class="badge-container">
                                            <?php
                                            $badge_class = [
                                                'borrador' => 'bg-warning',
                                                'activo' => 'bg-success',
                                                'finalizado' => 'bg-danger'
                                            ];
                                            ?>
                                            <span class="badge <?= $badge_class[$examen['estado']] ?> estado-badge">
                                                <?= ucfirst($examen['estado']) ?>
                                            </span>
                                            <?php if (!$examen['visible']): ?>
                                                <span class="badge bg-secondary estado-badge ms-1">Oculto</span>
                                            <?php endif; ?>
                                            <?php if (!$examen['activo']): ?>
                                                <span class="badge bg-dark estado-badge ms-1">Inactivo</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="<?= BASE_URL ?>/examenes/editar/<?= $examen['id_examen'] ?>" 
                                               class="text-decoration-none">
                                                <?= htmlspecialchars($examen['titulo']) ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="card-text">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-book me-1"></i>
                                                <?= htmlspecialchars($examen['nombre_curso'] ?? 'Sin curso') ?>
                                            </small>
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-layer-group me-1"></i>
                                                <?= htmlspecialchars($examen['nombre_modulo'] ?? 'Sin módulo') ?>
                                            </small>
                                            <small class="text-muted d-block mb-2">
                                                <i class="fas fa-question-circle me-1"></i>
                                                <?= $examen['total_preguntas'] ?? 0 ?> preguntas
                                            </small>
                                            
                                            <?php if ($examen['fecha_inicio'] || $examen['fecha_fin']): ?>
                                                <div class="mb-2">
                                                    <?php if ($examen['fecha_inicio']): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-play me-1"></i>
                                                            Inicio: <?= date('d/m/Y H:i', strtotime($examen['fecha_inicio'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if ($examen['fecha_fin']): ?>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-stop me-1"></i>
                                                            Fin: <?= date('d/m/Y H:i', strtotime($examen['fecha_fin'])) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($examen['tiempo_limite']): ?>
                                                <small class="text-muted d-block mb-2">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Tiempo límite: <?= $examen['tiempo_limite'] ?> minutos
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <a href="<?= BASE_URL ?>/examenes/editar/<?= $examen['id_examen'] ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-info btn-sm" 
                                                    onclick="duplicarExamen(<?= $examen['id_examen'] ?>)">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="exportarExamenPDF(<?= $examen['id_examen'] ?>)">
                                                <i class="fas fa-file-pdf"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="eliminarExamen(<?= $examen['id_examen'] ?>, '<?= htmlspecialchars($examen['titulo']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Modal para duplicar examen -->
    <div class="modal fade" id="modalDuplicar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplicar Examen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formDuplicar">
                        <div class="mb-3">
                            <label for="titulo_duplicado" class="form-label">Nuevo título</label>
                            <input type="text" class="form-control" id="titulo_duplicado" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="curso_duplicado" class="form-label">Curso destino</label>
                            <select class="form-select" id="curso_duplicado" name="id_curso" required>
                                <option value="">Seleccionar curso...</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>">
                                        <?= htmlspecialchars($curso['nombre_curso']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modulo_duplicado" class="form-label">Módulo destino</label>
                            <select class="form-select" id="modulo_duplicado" name="id_modulo" required>
                                <option value="">Seleccionar módulo...</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?= $modulo['id_modulo'] ?>">
                                        <?= htmlspecialchars($modulo['titulo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarDuplicacion()">Duplicar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../comunes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let examenADuplicar = null;

        // Selección múltiple
        document.getElementById('seleccionar_todos').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.examen-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            toggleAccionesMultiples();
        });

        document.querySelectorAll('.examen-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', toggleAccionesMultiples);
        });

        function toggleAccionesMultiples() {
            const seleccionados = document.querySelectorAll('.examen-checkbox:checked');
            const acciones = document.getElementById('acciones_multiples');
            acciones.style.display = seleccionados.length > 0 ? 'block' : 'none';
        }

        // Eliminar examen individual
        function eliminarExamen(id, titulo) {
            if (confirm(`¿Estás seguro de que quieres eliminar el examen "${titulo}"?`)) {
                fetch(`<?= BASE_URL ?>/examenes/eliminar/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                    }
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
        }

        // Duplicar examen
        function duplicarExamen(id) {
            examenADuplicar = id;
            document.getElementById('titulo_duplicado').value = '';
            new bootstrap.Modal(document.getElementById('modalDuplicar')).show();
        }

        function confirmarDuplicacion() {
            const form = document.getElementById('formDuplicar');
            const formData = new FormData(form);
            
            fetch(`<?= BASE_URL ?>/examenes/duplicar/${examenADuplicar}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalDuplicar')).hide();
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

        // Exportar funciones
        function exportarExamenes(formato) {
            window.open(`<?= BASE_URL ?>/examenes/exportar?formato=${formato}`, '_blank');
        }

        function exportarExamenPDF(id) {
            window.open(`<?= BASE_URL ?>/examenes/pdf/${id}`, '_blank');
        }
    </script>
</body>
</html>
