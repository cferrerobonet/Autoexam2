<?php
/**
 * Vista de Crear Alumno - AUTOEXAM2 (Rol Profesor)
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

<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="fs-3 fw-bold text-dark mb-2">
                        <i class="fas fa-user-plus text-primary me-2"></i>Crear Alumno Nuevo
                    </h1>
                    <p class="text-muted mb-0">Complete los campos para registrar un nuevo alumno</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/usuarios/todos" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Volver a Alumnos
                    </a>
                </div>
            </div>

            <!-- Mensajes de estado -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['exito'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['exito']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>

            <!-- Formulario de creación -->
            <div class="card shadow-sm form-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-graduate text-primary me-2"></i>Datos del nuevo alumno
                    </h5>
                </div>
                    <div class="card-body">
                        <form id="formCrearAlumno" action="<?= BASE_URL ?>/usuarios/guardar" method="POST" enctype="multipart/form-data">
                            <!-- Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                            <!-- Rol oculto: siempre alumno para el profesor -->
                            <input type="hidden" name="rol" value="alumno">

                            <!-- Datos básicos -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user"></i> Nombre *
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required
                                           placeholder="Nombre del alumno">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">
                                        <i class="fas fa-user-tag"></i> Apellidos *
                                    </label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" required
                                           placeholder="Apellidos del alumno">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="correo" class="form-label">
                                        <i class="fas fa-envelope"></i> Correo Electrónico *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" required
                                               placeholder="correo@ejemplo.com">
                                    </div>
                                    <div class="form-text">Debe ser único en el sistema</div>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="contrasena" class="form-label">
                                        <i class="fas fa-lock"></i> Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="contrasena" name="contrasena" required
                                               minlength="6" placeholder="Mínimo 6 caracteres">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirmar_contrasena" class="form-label">
                                        <i class="fas fa-lock"></i> Confirmar Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required
                                               minlength="6" placeholder="Repita la contraseña">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text" id="passwordMatch">Las contraseñas deben coincidir</div>
                                </div>
                            </div>

                            <!-- Foto de perfil -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="foto" class="form-label">
                                        <i class="fas fa-image"></i> Foto de Perfil <span class="text-muted">(opcional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                        <input type="file" class="form-control" id="foto" name="foto" 
                                               accept="image/jpeg, image/png, image/gif">
                                    </div>
                                    <div class="form-text">Formatos: JPG, PNG o GIF. Máx. 2MB</div>
                                </div>
                            </div>

                            <!-- Curso asignado (cursos del profesor) -->
                            <div class="row">
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
                                            
                                            foreach ($cursos as $curso) {
                                                echo '<option value="' . $curso['id_curso'] . '">' . 
                                                     htmlspecialchars($curso['nombre_curso']) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>                                

                            <div class="row">
                                <!-- Estado -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">
                                        <i class="fas fa-toggle-on"></i> Estado del Alumno
                                    </label>
                                    
                                    <!-- Indicador visual del estado -->
                                    <div class="card mb-2 text-success bg-success-subtle border-success" style="--bs-border-opacity: .3;" id="estadoIndicador">
                                        <div class="card-body py-2 px-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                                <label class="form-check-label" for="activo" id="estadoLabel">Activo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 mb-3 bg-info-subtle">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">Información importante</h5>
                                        <p class="mb-0">El alumno se creará con el rol de 'Alumno' y solo podrá ser asignado a cursos que usted imparte como profesor.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar Alumno
                                    </button>
                                    <a href="<?= BASE_URL ?>/usuarios/todos" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-times me-1"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
    <?php require_once APP_PATH . '/vistas/parciales/scripts_admin.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validación del formulario
            const form = document.getElementById('formCrearAlumno');
            form.addEventListener('submit', function(e) {
                const nombre = document.getElementById('nombre').value.trim();
                const apellidos = document.getElementById('apellidos').value.trim();
                const correo = document.getElementById('correo').value.trim();
                const contrasena = document.getElementById('contrasena').value;
                const confirmarContrasena = document.getElementById('confirmar_contrasena').value;

                if (!nombre || !apellidos || !correo || !contrasena || !confirmarContrasena) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return;
                }

                if (contrasena !== confirmarContrasena) {
                    e.preventDefault();
                    document.getElementById('passwordMatch').classList.add('text-danger');
                    document.getElementById('passwordMatch').textContent = 'Las contraseñas no coinciden';
                    return;
                }
            });

            // Validación de contraseñas en tiempo real
            const contrasena = document.getElementById('contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            const passwordMatch = document.getElementById('passwordMatch');
            
            function validarCoincidencia() {
                if (contrasena.value && confirmarContrasena.value) {
                    if (contrasena.value === confirmarContrasena.value) {
                        passwordMatch.classList.remove('text-danger');
                        passwordMatch.classList.add('text-success');
                        passwordMatch.textContent = 'Las contraseñas coinciden';
                        return true;
                    } else {
                        passwordMatch.classList.remove('text-success');
                        passwordMatch.classList.add('text-danger');
                        passwordMatch.textContent = 'Las contraseñas no coinciden';
                        return false;
                    }
                } else {
                    passwordMatch.classList.remove('text-success');
                    passwordMatch.classList.remove('text-danger');
                    passwordMatch.textContent = 'Las contraseñas deben coincidir';
                    return false;
                }
            }
            
            contrasena.addEventListener('input', validarCoincidencia);
            confirmarContrasena.addEventListener('input', validarCoincidencia);
            
            // Mostrar/ocultar contraseña
            document.getElementById('togglePassword').addEventListener('click', function() {
                if (contrasena.type === 'password') {
                    contrasena.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    contrasena.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
            
            document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
                if (confirmarContrasena.type === 'password') {
                    confirmarContrasena.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    confirmarContrasena.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
            
            // Cambio de estado (activo/inactivo)
            const checkActivo = document.getElementById('activo');
            const estadoIndicador = document.getElementById('estadoIndicador');
            const estadoLabel = document.getElementById('estadoLabel');
            
            checkActivo.addEventListener('change', function() {
                if (this.checked) {
                    estadoIndicador.classList.remove('text-danger', 'bg-danger-subtle', 'border-danger');
                    estadoIndicador.classList.add('text-success', 'bg-success-subtle', 'border-success');
                    estadoLabel.textContent = 'Activo';
                } else {
                    estadoIndicador.classList.remove('text-success', 'bg-success-subtle', 'border-success');
                    estadoIndicador.classList.add('text-danger', 'bg-danger-subtle', 'border-danger');
                    estadoLabel.textContent = 'Inactivo';
                }
            });
        });
    </script>
        </div>
    </div>
</div>
