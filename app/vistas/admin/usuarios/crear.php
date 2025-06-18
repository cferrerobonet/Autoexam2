<?php
/**
 * Vista de Crear Usuario - AUTOEXAM2
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
                    <h1><i class="fas fa-user-plus"></i> Crear Usuario</h1>
                    <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i> Volver a la lista
                    </a>
                </div>

                <!-- Mensajes de estado -->
                <?php if (isset($_SESSION['exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['exito']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Formulario -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-user-plus text-primary me-2"></i> Datos del Usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>/usuarios/guardar" id="formCrearUsuario" enctype="multipart/form-data">
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
                                               value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                                    </div>
                                    <div class="form-text">Ingrese el nombre del usuario</div>
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
                                               value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : '' ?>">
                                    </div>
                                    <div class="form-text">Ingrese los apellidos del usuario</div>
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
                                               value="<?= isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>">
                                    </div>
                                    <div class="form-text">Será usado para iniciar sesión</div>
                                </div>

                                <!-- Rol -->
                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">
                                        <i class="fas fa-user-shield"></i> Rol <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="">Seleccione un rol</option>
                                            <option value="admin" <?= (isset($_POST['rol']) && $_POST['rol'] === 'admin') ? 'selected' : '' ?>>
                                                Administrador
                                            </option>
                                            <option value="profesor" <?= (isset($_POST['rol']) && $_POST['rol'] === 'profesor') ? 'selected' : '' ?>>
                                                Profesor
                                            </option>
                                            <option value="alumno" <?= (isset($_POST['rol']) && $_POST['rol'] === 'alumno') ? 'selected' : '' ?>>
                                                Alumno
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-text">Define los permisos del usuario</div>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contrasena" class="form-label">
                                        <i class="fas fa-lock"></i> Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="contrasena" name="contrasena" 
                                               required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mínimo 6 caracteres</div>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="confirmar_contrasena" class="form-label">
                                        <i class="fas fa-lock-alt"></i> Confirmar Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                        <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" 
                                               required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text" id="passwordMatch">Debe coincidir con la contraseña</div>
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
                                            <?php if (isset($datos['cursos']) && is_array($datos['cursos'])): ?>
                                                <?php foreach ($datos['cursos'] as $curso): ?>
                                                    <option value="<?= $curso['id_curso'] ?>">
                                                        <?= htmlspecialchars($curso['nombre_curso']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-text">Solo requerido para alumnos</div>
                                </div>
                            </div>                                <!-- Estado -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">
                                        <i class="fas fa-toggle-on"></i> Estado del Usuario
                                    </label>
                                    
                                    <!-- Indicador visual del estado -->
                                    <div class="card mb-2 text-success bg-success-subtle border-success" style="--bs-border-opacity: .3;" id="estadoIndicador">
                                        <div class="card-body p-2 d-flex align-items-center">
                                            <i class="fas fa-check-circle me-2" id="estadoIcono"></i>
                                            <strong id="estadoTexto">Usuario activo</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                               value="1" <?= (!isset($_POST['activo']) || $_POST['activo']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="activo">
                                            <i class="fas fa-toggle-on"></i> Cambiar estado
                                        </label>
                                        <div class="form-text">Si está desmarcado, el usuario no podrá acceder al sistema</div>
                                    </div>
                                </div>

                                <!-- Foto de perfil -->
                                <div class="col-md-6 mb-3">
                                    <label for="foto" class="form-label">
                                        <i class="fas fa-camera"></i> Foto de Perfil <span class="text-muted">(opcional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-image"></i></span>
                                        <input type="file" class="form-control" id="foto" name="foto" 
                                               accept="image/*">
                                    </div>
                                    <div class="form-text">
                                        Formatos permitidos: JPG, PNG, GIF. Máximo 2MB
                                    </div>
                                    <div id="fotoPreview" class="mt-2" style="display:none;">
                                        <img src="" class="rounded border shadow-sm" width="120" height="120" style="object-fit: cover;" alt="Vista previa">
                                        <small class="text-muted d-block mt-1">Vista previa</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary rounded-pill px-4">
                                            <i class="fas fa-times me-2"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                            <i class="fas fa-save me-2"></i> Crear Usuario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 d-flex align-items-center">
                            <i class="fas fa-info-circle text-primary me-2"></i> Información
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 mb-3 bg-info-subtle">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Antes de crear un usuario</h5>
                                    <p class="mb-0">Asegúrese de verificar la información y configurar correctamente los permisos según el rol.</p>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i> Permisos por rol</h6>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-danger border-opacity-25 bg-danger-subtle bg-opacity-10">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center text-danger">
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle me-2">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                            Administrador
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Acceso completo al sistema</li>
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Gestión de usuarios</li>
                                            <li><i class="fas fa-check-circle text-success me-1"></i> Configuración general</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-primary border-opacity-25 bg-primary-subtle bg-opacity-10">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center text-primary">
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle me-2">
                                                <i class="fas fa-chalkboard-teacher"></i>
                                            </span>
                                            Profesor
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Crear y editar exámenes</li>
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Gestión de calificaciones</li>
                                            <li><i class="fas fa-check-circle text-success me-1"></i> Reportes de alumnos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-purple border-opacity-25 bg-purple-subtle">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center text-purple">
                                            <span class="badge rounded-pill bg-purple text-white me-2">
                                                <i class="fas fa-user-graduate"></i>
                                            </span>
                                            Alumno
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Realizar exámenes</li>
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> Ver sus calificaciones</li>
                                            <li><i class="fas fa-check-circle text-success me-1"></i> Historial de evaluaciones</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('contrasena');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordField = document.getElementById('confirmar_contrasena');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordField.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Validación de contraseñas coincidentes
            function validatePasswordMatch() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                const matchDiv = document.getElementById('passwordMatch');
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordField.classList.add('is-invalid');
                    confirmPasswordField.classList.remove('is-valid');
                    matchDiv.classList.add('text-danger');
                    matchDiv.classList.remove('text-success');
                    matchDiv.textContent = 'Las contraseñas no coinciden';
                    return false;
                } else if (confirmPassword && password === confirmPassword) {
                    confirmPasswordField.classList.add('is-valid');
                    confirmPasswordField.classList.remove('is-invalid');
                    matchDiv.classList.add('text-success');
                    matchDiv.classList.remove('text-danger');
                    matchDiv.textContent = 'Las contraseñas coinciden';
                    return true;
                } else {
                    confirmPasswordField.classList.remove('is-invalid', 'is-valid');
                    matchDiv.classList.remove('text-danger', 'text-success');
                    matchDiv.textContent = 'Debe coincidir con la contraseña';
                    return true;
                }
            }

            // Event listeners para validación en tiempo real
            passwordField.addEventListener('input', validatePasswordMatch);
            confirmPasswordField.addEventListener('input', validatePasswordMatch);

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
            
            rolSelect.addEventListener('change', toggleCursoField);
            toggleCursoField(); // Ejecutar al cargar

            // Validación del formulario
            const form = document.getElementById('formCrearUsuario');
            form.addEventListener('submit', function(e) {
                const nombre = document.getElementById('nombre').value.trim();
                const apellidos = document.getElementById('apellidos').value.trim();
                const correo = document.getElementById('correo').value.trim();
                const contrasena = document.getElementById('contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;
                const rol = document.getElementById('rol').value;

                if (!nombre || !apellidos || !correo || !contrasena || !confirmarContrasena || !rol) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return;
                }

                if (contrasena.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres.');
                    return;
                }

                if (contrasena !== confirmarContrasena) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden.');
                    return;
                }

                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(correo)) {
                    e.preventDefault();
                    alert('Por favor, ingrese un correo electrónico válido.');
                    return;
                }
            });
            
            // Previsualización de la foto
            const fotoInput = document.getElementById('foto');
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
                        let preview = document.getElementById('fotoPreview');
                        if (!preview) {
                            preview = document.createElement('div');
                            preview.id = 'fotoPreview';
                            preview.className = 'mt-2';
                            fotoInput.parentNode.appendChild(preview);
                        }
                        preview.innerHTML = `
                            <img src="${e.target.result}" class="user-avatar-preview rounded" alt="Previsualización">
                            <small class="text-muted d-block mt-1">Previsualización</small>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Eliminar previsualización si no hay archivo
                    const preview = document.getElementById('fotoPreview');
                    if (preview) {
                        preview.remove();
                    }
                }
            });
            
            // Estado del usuario - Actualizador visual
            const checkboxActivo = document.getElementById('activo');
            const estadoIndicador = document.getElementById('estadoIndicador');
            const estadoIcono = document.getElementById('estadoIcono');
            const estadoTexto = document.getElementById('estadoTexto');
            
            if (checkboxActivo) {
                checkboxActivo.addEventListener('change', function() {
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
        });
    </script>
</body>
</html>
