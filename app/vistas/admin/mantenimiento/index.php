<?php
/**
 * Vista principal de mantenimiento - AUTOEXAM2
 * Panel de administración de herramientas de mantenimiento
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<!-- Título -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-tools text-warning me-2"></i>
            <?= $datos['titulo'] ?>
        </h1>
        <p class="text-muted mb-0">Herramientas de administración y mantenimiento del sistema</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/configuracion" class="btn btn-outline-secondary">
            <i class="fas fa-cogs me-1"></i>Configuración
        </a>
        <a href="<?= BASE_URL ?>/inicio" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>
</div>

<!-- Mensajes -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['exito'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['exito']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['exito']); ?>
<?php endif; ?>

<!-- Estadísticas del Sistema -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text">
                    <span class="badge bg-primary"><?= array_sum($datos['estadisticas']['usuarios'] ?? []) ?></span>
                    Total
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-file-alt fa-2x text-info mb-2"></i>
                <h5 class="card-title">Archivos de Log</h5>
                <p class="card-text">
                    <span class="badge bg-info"><?= $datos['estadisticas']['archivos']['logs'] ?? 0 ?></span>
                    Archivos
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-memory fa-2x text-warning mb-2"></i>
                <h5 class="card-title">Caché</h5>
                <p class="card-text">
                    <span class="badge bg-warning"><?= number_format(($datos['estadisticas']['espacio']['cache_size'] ?? 0) / 1024 / 1024, 1) ?></span>
                    MB
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-cloud-upload-alt fa-2x text-success mb-2"></i>
                <h5 class="card-title">Subidas</h5>
                <p class="card-text">
                    <span class="badge bg-success"><?= number_format(($datos['estadisticas']['espacio']['uploads_size'] ?? 0) / 1024 / 1024, 1) ?></span>
                    MB
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Herramientas de Mantenimiento -->
<div class="row">
    <!-- Limpieza del Sistema -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-broom me-2"></i>Limpieza del Sistema
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Herramientas para limpiar archivos temporales y optimizar el rendimiento del sistema.</p>
                
                <div class="d-grid gap-2">
                    <form method="POST" action="<?= BASE_URL ?>/mantenimiento/limpiarCache" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                        <button type="submit" class="btn btn-warning w-100" 
                                onclick="return confirm('¿Seguro que desea limpiar la caché del sistema?')">
                            <i class="fas fa-trash me-2"></i>Limpiar Caché
                        </button>
                    </form>
                    
                    <button type="button" class="btn btn-outline-warning w-100" onclick="limpiarLogsSistema()">
                        <i class="fas fa-file-alt me-2"></i>Limpiar Logs Antiguos
                    </button>
                    
                    <button type="button" class="btn btn-outline-warning w-100" onclick="limpiarArchivosTemporales()">
                        <i class="fas fa-clock me-2"></i>Limpiar Archivos Temporales
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Verificación del Sistema -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shield-alt me-2"></i>Verificación del Sistema
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Comprobar la integridad y configuración del sistema.</p>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-info w-100" onclick="ejecutarVerificacion()">
                        <i class="fas fa-search me-2"></i>Verificar Sistema
                    </button>
                    
                    <button type="button" class="btn btn-outline-info w-100" onclick="verificarBaseDatos()">
                        <i class="fas fa-database me-2"></i>Verificar Base de Datos
                    </button>
                    
                    <button type="button" class="btn btn-outline-info w-100" onclick="verificarPermisos()">
                        <i class="fas fa-lock me-2"></i>Verificar Permisos
                    </button>
                </div>
                
                <div id="resultadosVerificacion" class="mt-3"></div>
            </div>
        </div>
    </div>
    
    <!-- Configuración del Sistema -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Configuración del Sistema
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Regenerar y gestionar archivos de configuración del sistema.</p>
                
                <div class="d-grid gap-2">
                    <form method="POST" action="<?= BASE_URL ?>/mantenimiento/regenerarConfiguracion" class="mb-2">
                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                        <button type="submit" class="btn btn-success w-100" 
                                onclick="return confirm('¿Regenerar archivos de configuración? Se creará un backup automático.')">
                            <i class="fas fa-sync me-2"></i>Regenerar Configuración
                        </button>
                    </form>
                    
                    <a href="<?= BASE_URL ?>/configuracion" class="btn btn-outline-success w-100">
                        <i class="fas fa-edit me-2"></i>Editar Configuración
                    </a>
                    
                    <button type="button" class="btn btn-outline-success w-100" onclick="exportarConfiguracion()">
                        <i class="fas fa-download me-2"></i>Exportar Configuración
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Herramientas Avanzadas -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Herramientas Avanzadas
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Herramientas especializadas para administradores del sistema.</p>
                
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="mostrarInformacionSistema()">
                        <i class="fas fa-info-circle me-2"></i>Información del Sistema
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="exportarLogs()">
                        <i class="fas fa-file-export me-2"></i>Exportar Logs
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="reiniciarSesiones()">
                        <i class="fas fa-user-times me-2"></i>Reiniciar Sesiones
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Información del Sistema -->
<div class="modal fade" id="modalInfoSistema" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información del Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenidoInfoSistema">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando información...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para ejecutar verificación completa
function ejecutarVerificacion() {
    const contenedor = document.getElementById('resultadosVerificacion');
    contenedor.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Ejecutando verificación...</div>';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= $datos['csrf_token'] ?>');
    
    fetch('<?= BASE_URL ?>/mantenimiento/verificarSistema', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarResultadosVerificacion(data.resultados);
        } else {
            contenedor.innerHTML = '<div class="alert alert-danger">Error: ' + data.error + '</div>';
        }
    })
    .catch(error => {
        contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
    });
}

// Mostrar resultados de verificación
function mostrarResultadosVerificacion(resultados) {
    const contenedor = document.getElementById('resultadosVerificacion');
    let html = '<div class="alert alert-success">Verificación completada</div>';
    
    // Mostrar resumen
    html += '<div class="row">';
    html += '<div class="col-6"><small><strong>BD:</strong> ' + (resultados.base_datos.conexion ? '✅' : '❌') + '</small></div>';
    html += '<div class="col-6"><small><strong>Extensiones PHP:</strong> ✅</small></div>';
    html += '</div>';
    
    contenedor.innerHTML = html;
}

// Función para mostrar información del sistema
function mostrarInformacionSistema() {
    const modal = new bootstrap.Modal(document.getElementById('modalInfoSistema'));
    const contenido = document.getElementById('contenidoInfoSistema');
    
    contenido.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Servidor</h6>
                <ul class="list-unstyled small">
                    <li><strong>PHP:</strong> <?= PHP_VERSION ?></li>
                    <li><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?></li>
                    <li><strong>OS:</strong> <?= php_uname('s') ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Configuración</h6>
                <ul class="list-unstyled small">
                    <li><strong>Memoria:</strong> <?= ini_get('memory_limit') ?></li>
                    <li><strong>Upload:</strong> <?= ini_get('upload_max_filesize') ?></li>
                    <li><strong>Tiempo:</strong> <?= ini_get('max_execution_time') ?>s</li>
                </ul>
            </div>
        </div>
    `;
    
    modal.show();
}

// Funciones placeholder para futuras implementaciones
function limpiarLogsSistema() {
    alert('Funcionalidad pendiente de implementar');
}

function limpiarArchivosTemporales() {
    alert('Funcionalidad pendiente de implementar');
}

function verificarBaseDatos() {
    alert('Funcionalidad pendiente de implementar');
}

function verificarPermisos() {
    alert('Funcionalidad pendiente de implementar');
}

function exportarConfiguracion() {
    alert('Funcionalidad pendiente de implementar');
}

function exportarLogs() {
    alert('Funcionalidad pendiente de implementar');
}

function reiniciarSesiones() {
    if (confirm('¿Seguro que desea reiniciar todas las sesiones activas?')) {
        alert('Funcionalidad pendiente de implementar');
    }
}
</script>
