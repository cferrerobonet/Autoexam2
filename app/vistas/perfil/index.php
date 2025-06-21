<?php
/**
 * Vista de Perfil de Usuario - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar que los datos estén disponibles
if (!isset($datos) || !isset($datos['usuario'])) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
                <a href="<?= BASE_URL ?>/perfil/sesiones" class="btn btn-outline-info">
                    <i class="fas fa-desktop"></i> Gestionar Sesiones
                </a>
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

            <!-- Información actual del usuario -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-2"></i> Información Actual
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <?php if (!empty($datos['usuario']['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($datos['usuario']['foto']) ?>" 
                                     alt="Foto de perfil" class="img-fluid rounded-circle mb-3" 
                                     style="max-width: 120px; max-height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 120px; height: 120px;">
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h4 class="mb-3"><?= htmlspecialchars($datos['usuario']['nombre'] . ' ' . $datos['usuario']['apellidos']) ?></h4>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2"><i class="fas fa-id-card-alt fa-fw"></i> ID:</span> 
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($datos['usuario']['id_usuario']) ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-muted me-2"><i class="fas fa-envelope fa-fw"></i> Correo:</span> 
                                <span><?= htmlspecialchars($datos['usuario']['correo']) ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2"><i class="fas fa-user-tag fa-fw"></i> Rol:</span> 
                                <?php
                                $rolClases = [
                                    'admin' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                    'profesor' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                    'alumno' => 'bg-success-subtle text-success border border-success-subtle'
                                ];
                                $rolIconos = [
                                    'admin' => 'fa-crown',
                                    'profesor' => 'fa-chalkboard-teacher',
                                    'alumno' => 'fa-user-graduate'
                                ];
                                ?>
                                <span class="badge rounded-pill <?= $rolClases[$datos['usuario']['rol']] ?> px-3">
                                    <i class="fas <?= $rolIconos[$datos['usuario']['rol']] ?>"></i>
                                    <?= ucfirst($datos['usuario']['rol']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de edición -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-edit text-success me-2"></i> Editar Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/perfil/actualizar" enctype="multipart/form-data" id="formEditarPerfil">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                        
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="fas fa-user"></i> Nombre <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           required maxlength="50" pattern="[A-Za-záéíóúñÑ\s]+"
                                           value="<?= htmlspecialchars($datos['usuario']['nombre']) ?>">
                                </div>
                                <div class="form-text">Solo letras y espacios</div>
                            </div>

                            <!-- Apellidos -->
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">
                                    <i class="fas fa-user-tag"></i> Apellidos <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-signature"></i></span>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           required maxlength="100" pattern="[A-Za-záéíóúñÑ\s]+"
                                           value="<?= htmlspecialchars($datos['usuario']['apellidos']) ?>">
                                </div>
                                <div class="form-text">Solo letras y espacios</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo electrónico -->
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label">
                                    <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="email" class="form-control" id="correo" name="correo" 
                                           required maxlength="150"
                                           value="<?= htmlspecialchars($datos['usuario']['correo']) ?>">
                                </div>
                                <div class="form-text">Su correo electrónico para acceso y notificaciones</div>
                            </div>

                            <!-- Foto de perfil -->
                            <div class="col-md-6 mb-3">
                                <label for="foto" class="form-label">
                                    <i class="fas fa-camera"></i> Foto de Perfil
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    <input type="file" class="form-control" id="foto" name="foto" 
                                           accept="image/jpeg,image/png,image/gif">
                                </div>
                                <div class="form-text">JPG, PNG o GIF. Máximo 2MB</div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="<?= BASE_URL ?>/perfil/cambiar-contrasena" class="btn btn-warning rounded-pill px-4">
                                        <i class="fas fa-key me-2"></i> Cambiar Contraseña
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="location.reload()">
                                            <i class="fas fa-undo me-2"></i> Restablecer
                                        </button>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="alert alert-info mt-4 shadow-sm" role="alert">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Información importante</h5>
                        <ul class="mb-0">
                            <li>Los cambios en su información personal se aplicarán inmediatamente.</li>
                            <li>Si cambia su correo electrónico, deberá usar el nuevo para iniciar sesión.</li>
                            <li>Para cambiar su contraseña, utilice el botón "Cambiar Contraseña".</li>
                            <li>Puede gestionar sus sesiones activas desde "Gestionar Sesiones".</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const form = document.getElementById('formEditarPerfil');
    form.addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const apellidos = document.getElementById('apellidos').value.trim();
        const correo = document.getElementById('correo').value.trim();

        if (!nombre || !apellidos || !correo) {
            e.preventDefault();
            alert('Por favor, complete todos los campos obligatorios.');
            return;
        }

        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(correo)) {
            e.preventDefault();
            alert('Por favor, ingrese un correo electrónico válido.');
            return;
        }

        // Confirmación
        const confirm = window.confirm('¿Está seguro de que desea guardar los cambios en su perfil?');
        if (!confirm) {
            e.preventDefault();
        }
    });

    // Previsualización de imagen
    const fotoInput = document.getElementById('foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño
                if (file.size > 2 * 1024 * 1024) {
                    alert('El archivo debe ser menor a 2MB');
                    this.value = '';
                    return;
                }
                
                // Validar tipo
                if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                    alert('Solo se permiten archivos JPG, PNG o GIF');
                    this.value = '';
                    return;
                }
            }
        });
    }
});
</script>

<?php
// Cargar footer y scripts según el rol
switch ($_SESSION['rol']) {
    case 'admin':
        require_once APP_PATH . '/vistas/parciales/footer_admin.php';
        require_once APP_PATH . '/vistas/parciales/scripts_admin.php';
        break;
    case 'profesor':
        require_once APP_PATH . '/vistas/parciales/footer_profesor.php';
        require_once APP_PATH . '/vistas/parciales/scripts_profesor.php';
        break;
    case 'alumno':
        require_once APP_PATH . '/vistas/parciales/footer_alumno.php';
        require_once APP_PATH . '/vistas/parciales/scripts_alumno.php';
        break;
}
?>
</body>
</html>
