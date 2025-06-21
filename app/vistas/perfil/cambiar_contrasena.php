<?php
/**
 * Vista de Cambiar Contraseña - AUTOEXAM2
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Verificar que los datos estén disponibles
if (!isset($datos)) {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>
                <a href="<?= BASE_URL ?>/perfil" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Perfil
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

            <!-- Formulario de cambio de contraseña -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-shield-alt text-warning me-2"></i> Cambiar Contraseña
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/perfil/cambiar-contrasena/procesar" id="formCambiarContrasena">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
                        
                        <!-- Contraseña actual -->
                        <div class="mb-3">
                            <label for="contrasena_actual" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña Actual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" 
                                       required placeholder="Ingrese su contraseña actual">
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Necesaria para verificar su identidad</div>
                        </div>

                        <!-- Nueva contraseña -->
                        <div class="mb-3">
                            <label for="nueva_contrasena" class="form-label">
                                <i class="fas fa-lock-open"></i> Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" 
                                       required minlength="6" placeholder="Ingrese su nueva contraseña">
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-1">
                                <div class="password-strength-bar" id="passwordStrength"></div>
                            </div>
                            <ul class="password-info text-muted ps-3 small mt-1">
                                <li id="req-length">Al menos 6 caracteres</li>
                                <li id="req-upper">Al menos una letra mayúscula</li>
                                <li id="req-lower">Al menos una letra minúscula</li>
                                <li id="req-number">Al menos un número</li>
                                <li id="req-special">Al menos un carácter especial</li>
                            </ul>
                        </div>

                        <!-- Confirmar nueva contraseña -->
                        <div class="mb-4">
                            <label for="confirmar_contrasena" class="form-label">
                                <i class="fas fa-check-double"></i> Confirmar Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" 
                                       required minlength="6" placeholder="Confirme su nueva contraseña">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text" id="passwordMatch"></div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= BASE_URL ?>/perfil" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="fas fa-times me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm" id="submitButton" disabled>
                                <i class="fas fa-key me-2"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de seguridad -->
            <div class="alert alert-warning mt-4 shadow-sm" role="alert">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Recomendaciones de seguridad</h5>
                        <ul class="mb-0">
                            <li>Use una contraseña única que no haya usado en otros sitios</li>
                            <li>Combine letras mayúsculas, minúsculas, números y símbolos</li>
                            <li>Evite información personal como fechas de nacimiento o nombres</li>
                            <li>Después del cambio, todas sus sesiones activas se cerrarán excepto la actual</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.password-strength {
    height: 8px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 4px;
}

.password-info li {
    transition: all 0.3s ease;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos
    const form = document.getElementById('formCambiarContrasena');
    const currentPassword = document.getElementById('contrasena_actual');
    const newPassword = document.getElementById('nueva_contrasena');
    const confirmPassword = document.getElementById('confirmar_contrasena');
    const submitButton = document.getElementById('submitButton');
    const passwordMatch = document.getElementById('passwordMatch');
    const strengthBar = document.getElementById('passwordStrength');

    // Toggle visibilidad de contraseñas
    function setupPasswordToggle(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        
        button.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    setupPasswordToggle('contrasena_actual', 'toggleCurrentPassword');
    setupPasswordToggle('nueva_contrasena', 'toggleNewPassword');
    setupPasswordToggle('confirmar_contrasena', 'toggleConfirmPassword');

    // Validación de fuerza de contraseña
    function checkPasswordStrength(password) {
        let strength = 0;
        let color = '#dc3545'; // Rojo por defecto

        // Verificar criterios
        const requirements = {
            length: password.length >= 6,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        // Actualizar iconos de requisitos
        updateRequirementStatus('req-length', requirements.length);
        updateRequirementStatus('req-upper', requirements.upper);
        updateRequirementStatus('req-lower', requirements.lower);
        updateRequirementStatus('req-number', requirements.number);
        updateRequirementStatus('req-special', requirements.special);

        // Calcular fuerza
        Object.values(requirements).forEach(met => {
            if (met) strength += 20;
        });

        // Determinar color
        if (strength >= 100) color = '#198754'; // Verde
        else if (strength >= 80) color = '#20c997'; // Verde claro
        else if (strength >= 60) color = '#ffc107'; // Amarillo
        else if (strength >= 40) color = '#fd7e14'; // Naranja

        return { percentage: strength, color: color };
    }

    function updateRequirementStatus(elementId, isValid) {
        const element = document.getElementById(elementId);
        if (isValid) {
            element.innerHTML = element.textContent.replace(/^.*/, '<i class="fas fa-check text-success"></i> ' + element.textContent);
            element.classList.add('text-success');
            element.classList.remove('text-muted');
        } else {
            element.innerHTML = element.textContent.replace(/^.*/, '<i class="fas fa-times text-danger"></i> ' + element.textContent);
            element.classList.add('text-muted');
            element.classList.remove('text-success');
        }
    }

    // Verificar coincidencia de contraseñas
    function checkPasswordMatch() {
        const newPass = newPassword.value;
        const confirmPass = confirmPassword.value;
        
        if (confirmPass.length === 0) {
            passwordMatch.innerHTML = '';
            return false;
        }
        
        if (newPass === confirmPass) {
            passwordMatch.innerHTML = '<i class="fas fa-check-circle text-success"></i> Las contraseñas coinciden';
            passwordMatch.className = 'form-text text-success';
            return true;
        } else {
            passwordMatch.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Las contraseñas no coinciden';
            passwordMatch.className = 'form-text text-danger';
            return false;
        }
    }

    // Validar formulario completo
    function validateForm() {
        const currentPass = currentPassword.value.trim();
        const newPass = newPassword.value;
        const confirmPass = confirmPassword.value;
        
        const strength = checkPasswordStrength(newPass);
        const passwordsMatch = checkPasswordMatch();
        
        const isValid = currentPass.length > 0 && 
                       strength.percentage >= 60 && 
                       passwordsMatch;
        
        submitButton.disabled = !isValid;
        return isValid;
    }

    // Event listeners
    newPassword.addEventListener('input', function() {
        const strength = checkPasswordStrength(this.value);
        
        // Actualizar barra de fuerza
        strengthBar.style.width = strength.percentage + '%';
        strengthBar.style.backgroundColor = strength.color;
        
        validateForm();
    });

    confirmPassword.addEventListener('input', validateForm);
    currentPassword.addEventListener('input', validateForm);

    // Validación del formulario al enviar
    form.addEventListener('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
            alert('Por favor, complete todos los campos con una contraseña segura.');
            return;
        }

        const confirm = window.confirm('¿Está seguro de que desea cambiar su contraseña? Se cerrarán todas sus otras sesiones activas.');
        if (!confirm) {
            e.preventDefault();
        }
    });

    // Inicializar validación
    validateForm();
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
