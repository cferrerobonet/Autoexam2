<?php
/**
 * Vista de Editar Usuario - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_admin.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
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
                </style>
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
                    <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a la lista
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

                <!-- Información del usuario -->
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
                                         class="rounded-circle border border-2 shadow-sm" width="100" height="100" alt="Avatar" style="object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-sm" 
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Indicador de estado -->
                                <div class="mt-3">
                                    <?php if ($datos['usuario']['activo']): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3">
                                            <i class="fas fa-check"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3">
                                            <i class="fas fa-times"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </div>
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
                                        'alumno' => 'bg-purple text-white'
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
                            <i class="fas fa-edit text-primary me-2"></i> Editar Datos
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>/usuarios/actualizar/<?= $datos['usuario']['id_usuario'] ?>" id="formEditarUsuario" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            
                            <div class="row">
                                <!-- Nombre -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user-tag"></i> Nombre <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               required maxlength="100" 
                                               value="<?= htmlspecialchars($datos['usuario']['nombre']) ?>">
                                    </div>
                                </div>

                                <!-- Apellidos -->
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">
                                        <i class="fas fa-id-card"></i> Apellidos <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                               required maxlength="150"
                                               value="<?= htmlspecialchars($datos['usuario']['apellidos']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Correo -->
                                <div class="col-md-6 mb-3">
                                    <label for="correo" class="form-label">
                                        <i class="fas fa-at"></i> Correo Electrónico <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" 
                                               required maxlength="150"
                                               value="<?= htmlspecialchars($datos['usuario']['correo']) ?>">
                                    </div>
                                    <div class="form-text">Cambiar el correo puede afectar el acceso del usuario</div>
                                </div>

                                <!-- Rol -->
                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">
                                        <i class="fas fa-user-shield"></i> Rol <span class="text-danger">*</span>
                                    </label>
                                    <?php 
                                    $esAdminPrincipal = ($datos['usuario']['id_usuario'] == 1 || 
                                                      ($datos['usuario']['rol'] == 'admin' && 
                                                       $datos['usuario']['correo'] == 'no_contestar@autoexam.epla.es'));
                                    $esUsuarioActual = ($datos['usuario']['id_usuario'] == $_SESSION['id_usuario']);
                                    $rolBloqueado = $esAdminPrincipal || $esUsuarioActual;
                                    ?>
                                    
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                                        <select class="form-select" id="rol" name="rol" required <?= $rolBloqueado ? 'disabled' : '' ?>>
                                            <option value="admin" <?= $datos['usuario']['rol'] === 'admin' ? 'selected' : '' ?>>
                                                Administrador
                                            </option>
                                            <option value="profesor" <?= $datos['usuario']['rol'] === 'profesor' ? 'selected' : '' ?>>
                                                Profesor
                                            </option>
                                            <option value="alumno" <?= $datos['usuario']['rol'] === 'alumno' ? 'selected' : '' ?>>
                                                Alumno
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <?php if ($rolBloqueado): ?>
                                        <!-- Campo oculto para que se envíe el rol actual -->
                                        <input type="hidden" name="rol" value="<?= htmlspecialchars($datos['usuario']['rol']) ?>">
                                    <?php endif; ?>
                                    
                                    <?php if ($esAdminPrincipal): ?>
                                        <div class="form-text text-danger">
                                            <i class="fas fa-shield-alt"></i> 
                                            El rol del administrador principal no puede ser modificado
                                        </div>
                                    <?php elseif ($esUsuarioActual): ?>
                                        <div class="form-text text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            No puede cambiar su propio rol
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Nueva contraseña (opcional) -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contrasena" class="form-label">
                                        <i class="fas fa-lock"></i> Nueva Contraseña <span class="text-muted">(opcional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                               minlength="6" placeholder="Dejar vacío para mantener actual">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Solo llenar si desea cambiar la contraseña</div>
                                </div>

                                <!-- Confirmar nueva contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="confirmar_contrasena" class="form-label">
                                        <i class="fas fa-lock-alt"></i> Confirmar Nueva Contraseña <span class="text-muted">(opcional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                        <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" 
                                               minlength="6" placeholder="Confirmar nueva contraseña">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text" id="passwordMatch">Debe coincidir si cambia la contraseña</div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Curso asignado (solo para alumnos) -->
                                <div class="col-md-6 mb-3" id="cursoContainer" style="display: none;">
                                    <label for="curso_asignado" class="form-label">
                                        <i class="fas fa-graduation-cap"></i> Curso Asignado
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <select class="form-select" id="curso_asignado" name="curso_asignado">
                                            <option value="">Sin asignar</option>
                                            <?php
                                            // Se cargarán todos los cursos disponibles para el administrador
                                            require_once APP_PATH . '/modelos/curso_modelo.php';
                                            $cursoModelo = new Curso();
                                            
                                            // Obtener el curso actual del alumno desde la tabla curso_alumno
                                            $cursoActual = null;
                                            if ($datos['usuario']['rol'] === 'alumno') {
                                                $cursoActual = $cursoModelo->obtenerCursoDeAlumno($datos['usuario']['id_usuario']);
                                            }
                                            
                                            // Obtener todos los cursos activos
                                            $cursos = $cursoModelo->obtenerTodos(100, 1, ['activo' => 1])['cursos'];
                                            
                                            foreach ($cursos as $curso) {
                                                // Aseguramos que la comparación sea siempre entre enteros
                                                $selected = ((int)$cursoActual === (int)$curso['id_curso']) ? 'selected' : '';
                                                echo '<option value="' . $curso['id_curso'] . '" ' . $selected . '>' . 
                                                     htmlspecialchars($curso['nombre_curso']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-text">Solo aplicable para alumnos</div>
                                </div>
                            </div>

                            <!-- Estado y Foto -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">
                                        <i class="fas fa-toggle-on"></i> Estado del Usuario
                                    </label>
                                    <?php 
                                    $esAdminPrincipal = ($datos['usuario']['id_usuario'] == 1 || 
                                                        ($datos['usuario']['rol'] == 'admin' && 
                                                         $datos['usuario']['correo'] == 'no_contestar@autoexam.epla.es'));
                                    $esUsuarioActual = ($datos['usuario']['id_usuario'] == $_SESSION['id_usuario']);
                                    $debeEstarDeshabilitado = ($esAdminPrincipal || $esUsuarioActual);
                                    $estadoClase = $datos['usuario']['activo'] ? 'text-success bg-success-subtle border-success' : 'text-danger bg-danger-subtle border-danger';
                                    $estadoIcono = $datos['usuario']['activo'] ? 'fas fa-check-circle' : 'fas fa-times-circle';
                                    $estadoTexto = $datos['usuario']['activo'] ? 'Usuario activo' : 'Usuario inactivo';
                                    ?>
                                    
                                    <!-- Indicador visual del estado -->
                                    <div class="card mb-2 <?= $estadoClase ?>" style="--bs-border-opacity: .3;">
                                        <div class="card-body p-2 d-flex align-items-center">
                                            <i class="<?= $estadoIcono ?> me-2"></i>
                                            <strong><?= $estadoTexto ?></strong>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                               value="1" <?= $datos['usuario']['activo'] ? 'checked' : '' ?>
                                               <?= $debeEstarDeshabilitado ? 'disabled' : '' ?>>
                                        
                                        <?php if ($esAdminPrincipal || $esUsuarioActual): ?>
                                            <!-- Campo oculto para asegurar que el valor se envíe aunque el checkbox esté deshabilitado -->
                                            <input type="hidden" name="activo" value="1">
                                        <?php endif; ?>
                                        
                                        <label class="form-check-label" for="activo">
                                            <i class="fas fa-toggle-on"></i> Cambiar estado
                                        </label>
                                        
                                        <?php if ($esAdminPrincipal): ?>
                                            <div class="form-text text-danger">
                                                <i class="fas fa-shield-alt"></i> 
                                                El administrador principal no puede ser desactivado
                                            </div>
                                        <?php elseif ($esUsuarioActual): ?>
                                            <div class="form-text text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> 
                                                No puede desactivar su propia cuenta
                                            </div>
                                        <?php else: ?>
                                            <div class="form-text">
                                                Si desmarca, el usuario no podrá acceder al sistema
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Foto de perfil -->
                                <div class="col-md-6 mb-3">
                                    <label for="foto" class="form-label">
                                        <i class="fas fa-camera"></i> Foto de Perfil <span class="text-muted">(opcional)</span>
                                    </label>
                                    
                                    <!-- Foto actual -->
                                    <?php if (!empty($datos['usuario']['foto'])): ?>
                                        <div class="mb-2">
                                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($datos['usuario']['foto']) ?>" 
                                                 alt="Foto actual" class="rounded border" width="120" height="120" style="object-fit: cover;">
                                            <small class="text-muted d-block mt-1">Foto actual</small>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-2">
                                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 120px; height: 120px;">
                                                <i class="fas fa-user fa-3x text-muted"></i>
                                            </div>
                                            <small class="text-muted d-block mt-1">Sin foto</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                                        <input type="file" class="form-control" id="foto" name="foto" 
                                               accept="image/*">
                                    </div>
                                    <div id="fotoPreview" class="mt-2" style="display:none;">
                                        <img src="" class="rounded border" width="120" height="120" style="object-fit: cover;" alt="Nueva foto">
                                        <small class="text-muted d-block mt-1">Vista previa</small>
                                    </div>
                                    <div class="form-text">
                                        Formatos permitidos: JPG, PNG, GIF. Máximo 2MB. 
                                        <?= !empty($datos['usuario']['foto']) ? 'Deje vacío para mantener la foto actual.' : '' ?>
                                    </div>
                                </div>
                            </div>                                <!-- Botones -->
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-times me-2"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información de seguridad -->
                <?php if ($datos['usuario']['id_usuario'] == $_SESSION['id_usuario']): ?>
                    <div class="alert alert-info mt-4 shadow-sm" role="alert">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Editando cuenta propia</h5>
                                <p class="mb-0">Algunas opciones están deshabilitadas por seguridad cuando edita su propia cuenta.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Estado del usuario - Actualizador visual
            const checkboxActivo = document.getElementById('activo');
            if (checkboxActivo) {
                checkboxActivo.addEventListener('change', function() {
                    const estadoIndicador = document.querySelector('.card.text-success, .card.text-danger');
                    const estadoIcono = estadoIndicador.querySelector('i');
                    const estadoTexto = estadoIndicador.querySelector('strong');
                    
                    if (this.checked) {
                        // Usuario activo
                        estadoIndicador.classList.remove('text-danger', 'bg-danger-subtle', 'border-danger');
                        estadoIndicador.classList.add('text-success', 'bg-success-subtle', 'border-success');
                        estadoIcono.classList.remove('fa-times-circle');
                        estadoIcono.classList.add('fa-check-circle');
                        estadoTexto.textContent = 'Usuario activo';
                    } else {
                        // Usuario inactivo
                        estadoIndicador.classList.remove('text-success', 'bg-success-subtle', 'border-success');
                        estadoIndicador.classList.add('text-danger', 'bg-danger-subtle', 'border-danger');
                        estadoIcono.classList.remove('fa-check-circle');
                        estadoIcono.classList.add('fa-times-circle');
                        estadoTexto.textContent = 'Usuario inactivo';
                    }
                });
            }
            
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('contrasena');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordField = document.getElementById('confirmar_contrasena');
            
            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            if (toggleConfirmPassword && confirmPasswordField) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPasswordField.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Validación de contraseñas coincidentes
            function validatePasswordMatch() {
                const password = passwordField ? passwordField.value : '';
                const confirmPassword = confirmPasswordField ? confirmPasswordField.value : '';
                const matchDiv = document.getElementById('passwordMatch');
                
                // Solo validar si se está intentando cambiar la contraseña
                if (password || confirmPassword) {
                    if (password !== confirmPassword) {
                        confirmPasswordField.classList.add('is-invalid');
                        confirmPasswordField.classList.remove('is-valid');
                        matchDiv.classList.add('text-danger');
                        matchDiv.classList.remove('text-success');
                        matchDiv.textContent = 'Las contraseñas no coinciden';
                        return false;
                    } else if (password && confirmPassword) {
                        confirmPasswordField.classList.add('is-valid');
                        confirmPasswordField.classList.remove('is-invalid');
                        matchDiv.classList.add('text-success');
                        matchDiv.classList.remove('text-danger');
                        matchDiv.textContent = 'Las contraseñas coinciden';
                        return true;
                    }
                }
                
                confirmPasswordField.classList.remove('is-invalid', 'is-valid');
                matchDiv.classList.remove('text-danger', 'text-success');
                matchDiv.textContent = 'Debe coincidir si cambia la contraseña';
                return true;
            }

            // Event listeners para validación en tiempo real
            if (passwordField) passwordField.addEventListener('input', validatePasswordMatch);
            if (confirmPasswordField) confirmPasswordField.addEventListener('input', validatePasswordMatch);

            // Mostrar/ocultar campo de curso según el rol
            const rolSelect = document.getElementById('rol');
            const cursoContainer = document.getElementById('cursoContainer');
            
            function toggleCursoField() {
                if (rolSelect.value === 'alumno') {
                    cursoContainer.style.display = 'block';
                } else {
                    cursoContainer.style.display = 'none';
                    document.getElementById('curso_asignado').value = '';
                }
            }
            
            if (rolSelect && cursoContainer) {
                rolSelect.addEventListener('change', toggleCursoField);
                toggleCursoField(); // Ejecutar al cargar
            }

            // Deshabilitar cambio de rol propio
            <?php if ($datos['usuario']['id_usuario'] == $_SESSION['id_usuario']): ?>
                const rolField = document.getElementById('rol');
                if (rolField) {
                    rolField.disabled = true;
                    rolField.title = 'No puede cambiar su propio rol';
                }
            <?php endif; ?>

            // Validación del formulario
            const form = document.getElementById('formEditarUsuario');
            form.addEventListener('submit', function(e) {
                const nombre = document.getElementById('nombre').value.trim();
                const apellidos = document.getElementById('apellidos').value.trim();
                const correo = document.getElementById('correo').value.trim();
                const rol = document.getElementById('rol').value;
                const contrasena = document.getElementById('contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;

                if (!nombre || !apellidos || !correo || !rol) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return;
                }

                // Si se ingresó nueva contraseña, validar
                if (contrasena || confirmarContrasena) {
                    if (contrasena.length < 6) {
                        e.preventDefault();
                        alert('La nueva contraseña debe tener al menos 6 caracteres.');
                        return;
                    }
                    
                    if (contrasena !== confirmarContrasena) {
                        e.preventDefault();
                        alert('Las contraseñas no coinciden.');
                        return;
                    }
                }

                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(correo)) {
                    e.preventDefault();
                    alert('Por favor, ingrese un correo electrónico válido.');
                    return;
                }

                // Confirmación si se está cambiando información crítica
                const confirm = window.confirm('¿Está seguro de que desea guardar los cambios?');
                if (!confirm) {
                    e.preventDefault();
                }
            });
            
            // Previsualización de la foto
            const fotoInput = document.getElementById('foto');
            const fotoPreview = document.getElementById('fotoPreview');
            const previewImg = fotoPreview ? fotoPreview.querySelector('img') : null;
            
            if (fotoInput) {
                fotoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validar tamaño (2MB)
                        if (file.size > 2 * 1024 * 1024) {
                            alert('El archivo es demasiado grande. Máximo 2MB.');
                            e.target.value = '';
                            return;
                        }
                        
                        // Validar tipo
                        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!allowedTypes.includes(file.type)) {
                            alert('Formato no permitido. Solo JPG, PNG y GIF.');
                            e.target.value = '';
                            return;
                        }
                        
                        // Mostrar previsualización
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (fotoPreview && previewImg) {
                                previewImg.src = e.target.result;
                                fotoPreview.style.display = 'block';
                            }
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Ocultar previsualización si se cancela la selección
                        if (fotoPreview) {
                            fotoPreview.style.display = 'none';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
