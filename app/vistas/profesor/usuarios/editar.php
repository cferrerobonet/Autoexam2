<?php
/**
 * Vista de Editar Usuario - AUTOEXAM2 (Rol Profesor)
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

<?php require_once APP_PATH . '/vistas/parciales/head_admin.php'; ?>

<body class="bg-light">
    <?php require_once APP_PATH . '/vistas/parciales/navbar_profesor.php'; ?>

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
                    <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a cursos
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
                                    <span class="badge bg-info text-dark me-2 px-3 py-2">
                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($datos['usuario']['correo']) ?>
                                    </span>
                                    
                                    <?php
                                    $rolText = '';
                                    $rolClass = '';
                                    
                                    switch ($datos['usuario']['rol']) {
                                        case 'admin':
                                            $rolText = 'Administrador';
                                            $rolClass = 'bg-danger text-white';
                                            break;
                                        case 'profesor':
                                            $rolText = 'Profesor';
                                            $rolClass = 'bg-purple text-white';
                                            break;
                                        case 'alumno':
                                        default:
                                            $rolText = 'Alumno';
                                            $rolClass = 'bg-success text-white';
                                            break;
                                    }
                                    ?>
                                    
                                    <span class="badge <?= $rolClass ?> px-3 py-2">
                                        <i class="fas fa-user-tag"></i> <?= $rolText ?>
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
                            <i class="fas fa-edit text-primary me-2"></i> Editar Usuario
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formEditarUsuario" method="post" action="<?= BASE_URL ?>/usuarios/actualizar/<?= $datos['usuario']['id_usuario'] ?>" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($datos['csrf_token']) ?>">
                            
                            <div class="row">
                                <!-- Nombre y apellidos -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user"></i> Nombre *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?= htmlspecialchars($datos['usuario']['nombre']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">
                                        <i class="fas fa-user-friends"></i> Apellidos *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card-alt"></i></span>
                                        <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                               value="<?= htmlspecialchars($datos['usuario']['apellidos']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Correo y rol -->
                                <div class="col-md-6 mb-3">
                                    <label for="correo" class="form-label">
                                        <i class="fas fa-envelope"></i> Correo Electrónico *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" 
                                               value="<?= htmlspecialchars($datos['usuario']['correo']) ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">
                                        <i class="fas fa-user-tag"></i> Rol *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-users-cog"></i></span>
                                        <select class="form-select" id="rol" name="rol" required>
                                            <option value="alumno" <?= ($datos['usuario']['rol'] === 'alumno') ? 'selected' : '' ?>>Alumno</option>
                                        </select>
                                    </div>
                                    <div class="form-text text-info">
                                        <i class="fas fa-info-circle"></i> 
                                        Los profesores solo pueden gestionar alumnos
                                    </div>
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
                                <!-- Curso asignado (para alumnos) -->
                                <div class="col-md-6 mb-3" id="cursoContainer">
                                    <label for="curso_asignado" class="form-label">
                                        <i class="fas fa-graduation-cap"></i> Curso Asignado
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <select class="form-select" id="curso_asignado" name="curso_asignado">
                                            <option value="">Sin asignar</option>
                                            <?php
                                            // Se cargarán dinámicamente los cursos que imparte el profesor
                                            require_once APP_PATH . '/modelos/curso_modelo.php';
                                            $cursoModelo = new Curso();
                                            $cursos = $cursoModelo->obtenerCursosPorProfesor($_SESSION['id_usuario']);
                                            
                                            // Obtener el curso actual del alumno desde la tabla curso_alumno
                                            $cursoActual = $cursoModelo->obtenerCursoDeAlumno($datos['usuario']['id_usuario']);
                                            
                                            foreach ($cursos as $curso) {
                                                // Usar $cursoActual para la comparación en lugar de curso_asignado
                                                $selected = ($cursoActual == $curso['id_curso']) ? 'selected' : '';
                                                echo '<option value="' . $curso['id_curso'] . '" ' . $selected . '>' . 
                                                     htmlspecialchars($curso['nombre_curso']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado y Foto -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">
                                        <i class="fas fa-toggle-on"></i> Estado del Usuario
                                    </label>
                                    <?php 
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
                                               value="1" <?= $datos['usuario']['activo'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="activo">
                                            <i class="fas fa-toggle-on"></i> Cambiar estado
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="foto" class="form-label">
                                        <i class="fas fa-image"></i> Foto de perfil <span class="text-muted">(opcional)</span>
                                    </label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><i class="fas fa-camera"></i></span>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/gif" aria-describedby="fotoHelp">
                                    </div>
                                    <div id="fotoHelp" class="form-text">
                                        <span class="badge bg-secondary me-1">Formatos: JPG, PNG, GIF</span> 
                                        <span class="badge bg-secondary">Máx: 2MB</span>
                                    </div>
                                    
                                    <!-- Previsualización -->
                                    <div id="fotoPreview" class="mt-2 text-center" style="display: none;">
                                        <img src="#" alt="Previsualización" class="img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="<?= BASE_URL ?>/cursos" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/comunes/pie.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Estado (activo/inactivo)
            const estadoCheckbox = document.getElementById('activo');
            const estadoIndicador = document.querySelector('.card.text-success, .card.text-danger');
            const estadoIcono = estadoIndicador.querySelector('i');
            
            if (estadoCheckbox && estadoIndicador) {
                estadoCheckbox.addEventListener('change', function() {
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

            // Validación del formulario
            const form = document.getElementById('formEditarUsuario');
            form.addEventListener('submit', function(e) {
                const nombre = document.getElementById('nombre').value.trim();
                const apellidos = document.getElementById('apellidos').value.trim();
                const correo = document.getElementById('correo').value.trim();
                const contrasena = document.getElementById('contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;

                if (!nombre || !apellidos || !correo) {
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
