<?php
/**
 * Vista principal de configuración - AUTOEXAM2
 * Panel de administración de configuración del sistema
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
            <i class="fas fa-cogs text-primary me-2"></i>
            <?= $datos['titulo'] ?>
        </h1>
        <p class="text-muted mb-0">Administrar configuración del sistema</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/inicio" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver al Dashboard
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

<!-- Tabs de configuración -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <ul class="nav nav-tabs card-header-tabs" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sistema-tab" data-bs-toggle="tab" data-bs-target="#sistema" type="button" role="tab">
                            <i class="fas fa-desktop me-1"></i>Sistema
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="basedatos-tab" data-bs-toggle="tab" data-bs-target="#basedatos" type="button" role="tab">
                            <i class="fas fa-database me-1"></i>Base de Datos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="correo-tab" data-bs-toggle="tab" data-bs-target="#correo" type="button" role="tab">
                            <i class="fas fa-envelope me-1"></i>Correo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sftp-tab" data-bs-toggle="tab" data-bs-target="#sftp" type="button" role="tab">
                            <i class="fas fa-cloud me-1"></i>SFTP/FTP
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="archivos-tab" data-bs-toggle="tab" data-bs-target="#archivos" type="button" role="tab">
                            <i class="fas fa-file me-1"></i>Archivos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mantenimiento-tab" data-bs-toggle="tab" data-bs-target="#mantenimiento" type="button" role="tab">
                            <i class="fas fa-tools me-1"></i>Mantenimiento
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="configTabsContent">
                    
                    <!-- Tab Sistema -->
                    <div class="tab-pane fade show active" id="sistema" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-desktop me-2"></i>Configuración del Sistema</h5>
                        
                        <form method="POST" action="<?= BASE_URL ?>/configuracion/actualizar">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <input type="hidden" name="seccion" value="sistema">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre_app" class="form-label">Nombre de la Aplicación</label>
                                        <input type="text" class="form-control" id="nombre_app" name="nombre_app" 
                                               value="<?= htmlspecialchars($datos['configuracion']['sistema']['nombre_app']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="base_url" class="form-label">URL Base</label>
                                        <input type="url" class="form-control" id="base_url" name="base_url" 
                                               value="<?= htmlspecialchars($datos['configuracion']['sistema']['base_url']) ?>" readonly>
                                        <div class="form-text">Esta configuración se establece en el archivo .env</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="modo_debug" name="modo_debug" 
                                                   <?= $datos['configuracion']['sistema']['modo_debug'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="modo_debug">
                                                Modo Debug
                                            </label>
                                            <div class="form-text">Mostrar errores detallados en desarrollo</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="modo_mantenimiento" name="modo_mantenimiento" 
                                                   <?= $datos['configuracion']['sistema']['modo_mantenimiento'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="modo_mantenimiento">
                                                Modo Mantenimiento
                                            </label>
                                            <div class="form-text">Activar para mantenimiento del sistema</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del sistema -->
                            <div class="mb-4">
                                <h6 class="mb-3">Información del Servidor</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="small text-muted">Versión PHP</div>
                                        <div class="fw-bold"><?= $datos['info_sistema']['php_version'] ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Servidor</div>
                                        <div class="fw-bold"><?= htmlspecialchars($datos['info_sistema']['servidor']) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Memoria Límite</div>
                                        <div class="fw-bold"><?= $datos['info_sistema']['memoria_limite'] ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="small text-muted">Tiempo Ejecución</div>
                                        <div class="fw-bold"><?= $datos['info_sistema']['tiempo_ejecucion'] ?>s</div>
                                    </div>
                                </div>
                                
                                <!-- Extensiones PHP -->
                                <div class="mt-3">
                                    <h6 class="mb-2">Extensiones PHP Requeridas</h6>
                                    <div class="row">
                                        <?php foreach ($datos['info_sistema']['extensiones_requeridas'] as $ext => $instalada): ?>
                                        <div class="col-md-3">
                                            <span class="badge <?= $instalada ? 'bg-success' : 'bg-danger' ?>">
                                                <i class="fas <?= $instalada ? 'fa-check' : 'fa-times' ?> me-1"></i>
                                                <?= $ext ?>
                                            </span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Configuración
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Base de Datos -->
                    <div class="tab-pane fade" id="basedatos" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-database me-2"></i>Configuración Base de Datos</h5>
                        
                        <form method="POST" action="<?= BASE_URL ?>/configuracion/actualizar" id="formBaseDatos">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <input type="hidden" name="seccion" value="basedatos">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_host" class="form-label">Host</label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" 
                                               value="<?= htmlspecialchars($datos['configuracion']['base_datos']['host']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_puerto" class="form-label">Puerto</label>
                                        <input type="number" class="form-control" id="db_puerto" name="db_puerto" 
                                               value="<?= htmlspecialchars($datos['configuracion']['base_datos']['puerto']) ?>" 
                                               min="1" max="65535" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_nombre" class="form-label">Nombre Base de Datos</label>
                                        <input type="text" class="form-control" id="db_nombre" name="db_nombre" 
                                               value="<?= htmlspecialchars($datos['configuracion']['base_datos']['nombre']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_usuario" class="form-label">Usuario</label>
                                        <input type="text" class="form-control" id="db_usuario" name="db_usuario" 
                                               value="<?= htmlspecialchars($datos['configuracion']['base_datos']['usuario']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_contrasena" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="db_contrasena" name="db_contrasena" 
                                               placeholder="Dejar vacío para no cambiar">
                                        <div class="form-text">Solo se actualiza si se proporciona una nueva contraseña</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estado Actual</label>
                                        <div class="input-group">
                                            <span class="form-control" id="estadoConexionBD">
                                                <?php if ($datos['configuracion']['base_datos']['estado_conexion'] === 'conectado'): ?>
                                                    <i class="fas fa-check-circle text-success me-1"></i>Conectado
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle text-danger me-1"></i>Error de conexión
                                                <?php endif; ?>
                                            </span>
                                            <button type="button" class="btn btn-outline-secondary" id="probarConexionBD">
                                                <i class="fas fa-sync me-1"></i>Probar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Advertencia:</strong> Cambiar la configuración de base de datos puede interrumpir el funcionamiento del sistema. 
                                Se recomienda hacer respaldo antes de aplicar cambios.
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Configuración
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Correo -->
                    <div class="tab-pane fade" id="correo" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-envelope me-2"></i>Configuración de Correo</h5>
                        
                        <form method="POST" action="<?= BASE_URL ?>/configuracion/actualizar" id="formCorreo">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <input type="hidden" name="seccion" value="correo">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_host" class="form-label">Host SMTP</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="<?= htmlspecialchars($datos['configuracion']['correo']['host']) ?>" 
                                               placeholder="smtp.gmail.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_puerto" class="form-label">Puerto</label>
                                        <input type="number" class="form-control" id="smtp_puerto" name="smtp_puerto" 
                                               value="<?= htmlspecialchars($datos['configuracion']['correo']['puerto']) ?>" 
                                               min="1" max="65535" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_usuario" class="form-label">Usuario SMTP</label>
                                        <input type="email" class="form-control" id="smtp_usuario" name="smtp_usuario" 
                                               value="<?= htmlspecialchars($datos['configuracion']['correo']['usuario']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="smtp_contrasena" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="smtp_contrasena" name="smtp_contrasena" 
                                               placeholder="Dejar vacío para mantener actual">
                                        <div class="form-text">Solo se actualiza si se proporciona una nueva contraseña</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="smtp_seguridad" class="form-label">Seguridad</label>
                                        <select class="form-select" id="smtp_seguridad" name="smtp_seguridad">
                                            <option value="tls" <?= $datos['configuracion']['correo']['seguridad'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                                            <option value="ssl" <?= $datos['configuracion']['correo']['seguridad'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="smtp_from_email" class="form-label">Email FROM</label>
                                        <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" 
                                               value="<?= htmlspecialchars($datos['configuracion']['correo']['from_email'] ?? '') ?>" 
                                               placeholder="noreply@example.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="smtp_from_name" class="form-label">Nombre FROM</label>
                                        <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" 
                                               value="<?= htmlspecialchars($datos['configuracion']['correo']['from_name'] ?? '') ?>" 
                                               placeholder="AUTOEXAM2">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estado</label>
                                        <div class="form-control">
                                            <?php if ($datos['configuracion']['correo']['estado_conexion'] === 'configurado'): ?>
                                                <i class="fas fa-check-circle text-success me-1"></i>Configurado
                                            <?php else: ?>
                                                <i class="fas fa-exclamation-circle text-warning me-1"></i>No configurado
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Guardar Configuración
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="mostrarModalPruebaSMTP()">
                                    <i class="fas fa-paper-plane me-1"></i>Probar Conexión
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab SFTP/FTP -->
                    <div class="tab-pane fade" id="sftp" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-cloud me-2"></i>Configuración SFTP/FTP</h5>
                        
                        <form method="POST" action="<?= BASE_URL ?>/configuracion/actualizar" id="formSFTP">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <input type="hidden" name="seccion" value="sftp">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ftp_host" class="form-label">Host SFTP/FTP</label>
                                        <input type="text" class="form-control" id="ftp_host" name="ftp_host" 
                                               value="<?= htmlspecialchars($datos['configuracion']['sftp']['host'] ?? '') ?>" 
                                               placeholder="ftp.example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ftp_puerto" class="form-label">Puerto</label>
                                        <input type="number" class="form-control" id="ftp_puerto" name="ftp_puerto" 
                                               value="<?= htmlspecialchars($datos['configuracion']['sftp']['puerto'] ?? '21') ?>" 
                                               min="1" max="65535" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ftp_usuario" class="form-label">Usuario FTP</label>
                                        <input type="text" class="form-control" id="ftp_usuario" name="ftp_usuario" 
                                               value="<?= htmlspecialchars($datos['configuracion']['sftp']['usuario'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="ftp_contrasena" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="ftp_contrasena" name="ftp_contrasena" 
                                               placeholder="Dejar vacío para mantener actual">
                                        <div class="form-text">Solo se actualiza si se proporciona una nueva contraseña</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="ftp_seguro" name="ftp_seguro" 
                                                   <?= ($datos['configuracion']['sftp']['seguro'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ftp_seguro">
                                                Conexión Segura (SFTP)
                                            </label>
                                            <div class="form-text">Usar SFTP en lugar de FTP estándar</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Estado</label>
                                        <div class="input-group">
                                            <span class="form-control" id="estadoConexionFTP">
                                                <?php if (($datos['configuracion']['sftp']['estado_conexion'] ?? '') === 'configurado'): ?>
                                                    <i class="fas fa-check-circle text-success me-1"></i>Configurado
                                                <?php else: ?>
                                                    <i class="fas fa-exclamation-circle text-warning me-1"></i>No configurado
                                                <?php endif; ?>
                                            </span>
                                            <button type="button" class="btn btn-outline-secondary" id="probarConexionFTP">
                                                <i class="fas fa-sync me-1"></i>Probar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                La configuración FTP se utiliza para backups automáticos y transferencia de archivos.
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Configuración
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Archivos -->
                    <div class="tab-pane fade" id="archivos" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-file me-2"></i>Configuración de Archivos</h5>
                        
                        <form method="POST" action="<?= BASE_URL ?>/configuracion/actualizar">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <input type="hidden" name="seccion" value="archivos">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="upload_max_size" class="form-label">Tamaño Máximo de Archivo</label>
                                        <input type="text" class="form-control" id="upload_max_size" name="upload_max_size" 
                                               value="<?= htmlspecialchars($datos['configuracion']['archivos']['tamaño_maximo']) ?>" 
                                               placeholder="2MB" required>
                                        <div class="form-text">Formato: 2MB, 10MB, etc.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="allowed_extensions" class="form-label">Extensiones Permitidas</label>
                                        <input type="text" class="form-control" id="allowed_extensions" name="allowed_extensions" 
                                               value="<?= htmlspecialchars($datos['configuracion']['archivos']['tipos_permitidos']) ?>" 
                                               placeholder="jpg,png,gif,pdf" required>
                                        <div class="form-text">Separadas por comas, sin espacios</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Directorio de Subidas</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($datos['configuracion']['archivos']['directorio_subidas']) ?>" readonly>
                                        <div class="form-text">Configurado en el sistema</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Límite PHP</label>
                                        <input type="text" class="form-control" 
                                               value="<?= ini_get('upload_max_filesize') ?>" readonly>
                                        <div class="form-text">Límite del servidor PHP</div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Guardar Configuración
                            </button>
                        </form>
                        
                        <!-- Gestión de Logs -->
                        <hr class="my-4">
                        <h6 class="mb-3"><i class="fas fa-file-alt me-2"></i>Gestión de Logs</h6>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="tipoLog" class="form-label">Ver Logs</label>
                                    <select class="form-select" id="tipoLog" onchange="cargarLogs()">
                                        <option value="app">Aplicación</option>
                                        <option value="errores">Errores</option>
                                        <option value="acceso">Acceso</option>
                                        <option value="sistema">Sistema</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="border rounded p-3" style="height: 300px; overflow-y: auto; background-color: #f8f9fa;">
                                        <div id="contenidoLogs">
                                            <div class="text-muted text-center">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Cargando logs...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <form method="POST" action="<?= BASE_URL ?>/configuracion/limpiarLogs" onsubmit="return confirm('¿Seguro que desea eliminar los logs antiguos?')">
                                    <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                    
                                    <div class="mb-3">
                                        <label for="dias" class="form-label">Limpiar logs más antiguos de:</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="dias" name="dias" value="30" min="1" max="365" required>
                                            <span class="input-group-text">días</span>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-warning btn-sm w-100">
                                        <i class="fas fa-trash me-1"></i>Limpiar Logs
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Mantenimiento -->
                    <div class="tab-pane fade" id="mantenimiento" role="tabpanel">
                        <h5 class="mb-3"><i class="fas fa-tools me-2"></i>Herramientas de Mantenimiento</h5>
                        
                        <!-- Backup y Restauración -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-download me-2"></i>Crear Backup</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Crear copia de seguridad de la configuración actual</p>
                                        <form method="POST" action="<?= BASE_URL ?>/configuracion/crearBackup">
                                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('¿Crear backup de la configuración actual?')">
                                                <i class="fas fa-download me-1"></i>Crear Backup
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Restaurar Backup</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted">Restaurar configuración desde backup</p>
                                        <form method="POST" action="<?= BASE_URL ?>/configuracion/restaurarBackup">
                                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                                            <div class="mb-3">
                                                <select class="form-select" name="archivo_backup" required>
                                                    <option value="">Seleccionar backup...</option>
                                                    <?php foreach ($datos['configuracion']['backups'] as $backup): ?>
                                                    <option value="<?= htmlspecialchars($backup['nombre']) ?>">
                                                        <?= htmlspecialchars($backup['nombre']) ?> 
                                                        (<?= $backup['fecha_formateada'] ?>) - 
                                                        <?= number_format($backup['tamaño'] / 1024, 1) ?> KB
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-warning" 
                                                    onclick="return confirm('ADVERTENCIA: Esto sobrescribirá la configuración actual. ¿Continuar?')">
                                                <i class="fas fa-upload me-1"></i>Restaurar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lista de Backups Disponibles -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-archive me-2"></i>Backups Disponibles</h6>
                            </div>
                            <div class="card-body">
                                <?php if (empty($datos['configuracion']['backups'])): ?>
                                    <div class="text-muted text-center py-3">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay backups disponibles</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Archivo</th>
                                                    <th>Fecha</th>
                                                    <th>Tamaño</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($datos['configuracion']['backups'] as $backup): ?>
                                                <tr>
                                                    <td>
                                                        <i class="fas fa-file-archive text-primary me-1"></i>
                                                        <?= htmlspecialchars($backup['nombre']) ?>
                                                    </td>
                                                    <td><?= $backup['fecha_formateada'] ?></td>
                                                    <td><?= number_format($backup['tamaño'] / 1024, 1) ?> KB</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="restaurarBackup('<?= htmlspecialchars($backup['nombre']) ?>')">
                                                            <i class="fas fa-undo me-1"></i>Restaurar
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Herramientas de Sistema -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Herramientas del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <i class="fas fa-broom fa-2x text-warning mb-2"></i>
                                            <h6>Limpiar Caché</h6>
                                            <p class="text-muted small">Eliminar archivos de caché temporales</p>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="limpiarCache()">
                                                <i class="fas fa-broom me-1"></i>Limpiar
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <i class="fas fa-sync fa-2x text-info mb-2"></i>
                                            <h6>Regenerar Configuración</h6>
                                            <p class="text-muted small">Recrear archivos de configuración</p>
                                            <button type="button" class="btn btn-sm btn-info" onclick="regenerarConfig()">
                                                <i class="fas fa-sync me-1"></i>Regenerar
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 text-center">
                                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                            <h6>Verificar Sistema</h6>
                                            <p class="text-muted small">Comprobar integridad del sistema</p>
                                            <button type="button" class="btn btn-sm btn-success" onclick="verificarSistema()">
                                                <i class="fas fa-shield-alt me-1"></i>Verificar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para probar SMTP -->
<div class="modal fade" id="modalProbarSMTP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Probar Configuración SMTP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="email_prueba" class="form-label">Email de prueba</label>
                    <input type="email" class="form-control" id="email_prueba" required>
                    <div class="form-text">Se enviará un email de prueba a esta dirección</div>
                </div>
                <div id="resultado_prueba"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarPruebaSMTP()">
                    <i class="fas fa-paper-plane me-1"></i>Enviar Prueba
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar logs al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarLogs();
});

// Función para cargar logs
function cargarLogs() {
    const tipo = document.getElementById('tipoLog').value;
    const contenedor = document.getElementById('contenidoLogs');
    
    contenedor.innerHTML = '<div class="text-muted text-center"><i class="fas fa-spinner fa-spin me-2"></i>Cargando logs...</div>';
    
    fetch(`<?= BASE_URL ?>/configuracion/logs?tipo=${tipo}&limite=50`)
        .then(response => response.json())
        .then(data => {
            if (data.logs && data.logs.length > 0) {
                let html = '';
                data.logs.forEach(log => {
                    const clase = log.nivel === 'error' ? 'text-danger' : 
                                 log.nivel === 'warning' ? 'text-warning' : 'text-muted';
                    html += `<div class="mb-1 ${clase}" style="font-size: 0.85em; font-family: monospace;">
                                <small class="text-muted">[${log.timestamp}]</small> ${log.mensaje}
                             </div>`;
                });
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = '<div class="text-muted text-center">No hay logs disponibles</div>';
            }
        })
        .catch(error => {
            contenedor.innerHTML = '<div class="text-danger text-center">Error cargando logs</div>';
        });
}

// Función para mostrar modal de prueba SMTP
function mostrarModalPruebaSMTP() {
    document.getElementById('email_prueba').value = '';
    document.getElementById('resultado_prueba').innerHTML = '';
    new bootstrap.Modal(document.getElementById('modalProbarSMTP')).show();
}

// Función para enviar prueba SMTP
function enviarPruebaSMTP() {
    const email = document.getElementById('email_prueba').value;
    const resultado = document.getElementById('resultado_prueba');
    
    if (!email) {
        resultado.innerHTML = '<div class="alert alert-danger">Por favor ingrese un email válido</div>';
        return;
    }
    
    resultado.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Enviando email de prueba...</div>';
    
    const formData = new FormData();
    formData.append('email_prueba', email);
    formData.append('csrf_token', '<?= $datos['csrf_token'] ?>');
    
    fetch('<?= BASE_URL ?>/configuracion/probarSMTP', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            resultado.innerHTML = `<div class="alert alert-success"><i class="fas fa-check me-2"></i>${data.exito}</div>`;
        } else {
            resultado.innerHTML = `<div class="alert alert-danger"><i class="fas fa-times me-2"></i>${data.error}</div>`;
        }
    })
    .catch(error => {
        resultado.innerHTML = '<div class="alert alert-danger">Error de conexión</div>';
    });
}

// Función para probar conexión de BD
document.getElementById('probarConexionBD').addEventListener('click', function() {
    const boton = this;
    const estado = document.getElementById('estadoConexionBD');
    
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Probando...';
    
    const formData = new FormData();
    formData.append('host', document.getElementById('db_host').value);
    formData.append('nombre', document.getElementById('db_nombre').value);
    formData.append('usuario', document.getElementById('db_usuario').value);
    formData.append('contrasena', document.getElementById('db_contrasena').value);
    formData.append('puerto', document.getElementById('db_puerto').value);
    formData.append('csrf_token', '<?= $datos['csrf_token'] ?>');
    
    fetch('<?= BASE_URL ?>/configuracion/probarBaseDatos', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            estado.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Conectado - ' + data.exito;
        } else {
            estado.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Error - ' + data.error;
        }
    })
    .catch(error => {
        estado.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Error de conexión';
    })
    .finally(() => {
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-sync me-1"></i>Probar';
    });
});

// Función para probar conexión de FTP
document.getElementById('probarConexionFTP').addEventListener('click', function() {
    const boton = this;
    const estado = document.getElementById('estadoConexionFTP');
    
    boton.disabled = true;
    boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Probando...';
    
    const formData = new FormData();
    formData.append('host', document.getElementById('ftp_host').value);
    formData.append('usuario', document.getElementById('ftp_usuario').value);
    formData.append('contrasena', document.getElementById('ftp_contrasena').value);
    formData.append('puerto', document.getElementById('ftp_puerto').value);
    formData.append('csrf_token', '<?= $datos['csrf_token'] ?>');
    
    fetch('<?= BASE_URL ?>/configuracion/probarFTP', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            estado.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>Conectado - ' + data.exito;
        } else {
            estado.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Error - ' + data.error;
        }
    })
    .catch(error => {
        estado.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i>Error de conexión';
    })
    .finally(() => {
        boton.disabled = false;
        boton.innerHTML = '<i class="fas fa-sync me-1"></i>Probar';
    });
});

// Función para restaurar backup desde tabla
function restaurarBackup(nombreArchivo) {
    if (!confirm('ADVERTENCIA: Esto sobrescribirá la configuración actual. ¿Continuar?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= BASE_URL ?>/configuracion/restaurarBackup';
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $datos['csrf_token'] ?>';
    
    const archivoInput = document.createElement('input');
    archivoInput.type = 'hidden';
    archivoInput.name = 'archivo_backup';
    archivoInput.value = nombreArchivo;
    
    form.appendChild(csrfInput);
    form.appendChild(archivoInput);
    document.body.appendChild(form);
    form.submit();
}

// Funciones de herramientas del sistema
function limpiarCache() {
    if (!confirm('¿Seguro que desea limpiar la caché del sistema?')) {
        return;
    }
    
    // TODO: Implementar limpieza de caché
    alert('Funcionalidad de limpieza de caché pendiente de implementar');
}

function regenerarConfig() {
    if (!confirm('¿Seguro que desea regenerar los archivos de configuración?')) {
        return;
    }
    
    // TODO: Implementar regeneración de configuración
    alert('Funcionalidad de regeneración de configuración pendiente de implementar');
}

function verificarSistema() {
    // TODO: Implementar verificación del sistema
    alert('Funcionalidad de verificación del sistema pendiente de implementar');
}
</script>
