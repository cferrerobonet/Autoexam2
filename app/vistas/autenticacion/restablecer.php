<?php
/**
 * Vista de formulario para restablecer contraseña - AUTOEXAM2
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $datos['titulo'] ?? 'Restablecer Contraseña' ?> - <?= SYSTEM_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/estilos.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .recovery-container {
            max-width: 450px;
            padding: 2rem;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .logo {
            max-height: 80px;
            max-width: 100%;
            height: auto;
            width: auto;
            object-fit: contain;
            margin-bottom: 1.5rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .password-info {
            font-size: 0.85rem;
            margin-top: 8px;
        }
        .password-info li {
            margin-bottom: 3px;
        }
        .password-strength {
            height: 5px;
            margin-top: 8px;
            background-color: #e9ecef;
            border-radius: 3px;
        }
        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="text-center mb-4">
            <?php 
                // Verificar si existe el archivo del logo
                $mainLogoPath = ROOT_PATH . '/publico/recursos/logo.png';
                
                // Detectar si estamos en producción (no localhost)
                $isProduction = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') === false && 
                                strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
                
                // En producción usamos /recursos/logo.png, en desarrollo /publico/recursos/logo.png
                $logoPath = $isProduction 
                    ? BASE_URL . '/recursos/logo.png' 
                    : BASE_URL . '/publico/recursos/logo.png';
                    
                // Debug: Registrar información sobre la carga del logo
                error_log("Restablecer.php - Intentando cargar logo desde: " . $logoPath . " (Producción: " . ($isProduction ? "Sí" : "No") . ")");
            ?>
                <img src="<?= $logoPath ?>" alt="<?= SYSTEM_NAME ?> Logo" class="logo" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iIzAwN2JmZiIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjIwIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkFVVE9FWEFNPC90ZXh0Pjwvc3ZnPg=='; this.classList.add('default-logo'); console.log('Error al cargar el logo desde: ' + this.src);"
                >
            
            <h2>Restablecer Contraseña</h2>
            <p class="text-muted">Cree una nueva contraseña segura</p>
        </div>

        <?php if (isset($datos['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $datos['error'] ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/autenticacion/restablecer/<?= $datos['token'] ?>">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?>">
            
            <div class="mb-3">
                <label for="nueva_contrasena" class="form-label">
                    <i class="fas fa-lock me-2"></i>Nueva contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required
                           placeholder="Ingrese su nueva contraseña" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength mt-1">
                    <div class="password-strength-bar" id="passwordStrength"></div>
                </div>
                <ul class="password-info text-muted ps-3">
                    <li>Al menos <?= $datos['requisitos']['longitud_minima'] ?? 8 ?> caracteres</li>
                    <?php if (isset($datos['requisitos']['requiere_mayusculas']) && $datos['requisitos']['requiere_mayusculas']): ?>
                        <li>Al menos una letra mayúscula</li>
                    <?php endif; ?>
                    <?php if (isset($datos['requisitos']['requiere_minusculas']) && $datos['requisitos']['requiere_minusculas']): ?>
                        <li>Al menos una letra minúscula</li>
                    <?php endif; ?>
                    <?php if (isset($datos['requisitos']['requiere_numeros']) && $datos['requisitos']['requiere_numeros']): ?>
                        <li>Al menos un número</li>
                    <?php endif; ?>
                    <?php if (isset($datos['requisitos']['requiere_simbolos']) && $datos['requisitos']['requiere_simbolos']): ?>
                        <li>Al menos un símbolo especial</li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="mb-4">
                <label for="confirmar_contrasena" class="form-label">
                    <i class="fas fa-lock me-2"></i>Confirmar contraseña
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required
                           placeholder="Confirme su nueva contraseña" autocomplete="new-password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="form-text text-muted" id="passwordMatch"></div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary" id="submitButton">
                    <i class="fas fa-check-circle me-2"></i>Restablecer Contraseña
                </button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables para requisitos de contraseña
        const minLength = <?= $datos['requisitos']['longitud_minima'] ?? 8 ?>;
        const requireUppercase = <?= isset($datos['requisitos']['requiere_mayusculas']) && $datos['requisitos']['requiere_mayusculas'] ? 'true' : 'false' ?>;
        const requireLowercase = <?= isset($datos['requisitos']['requiere_minusculas']) && $datos['requisitos']['requiere_minusculas'] ? 'true' : 'false' ?>;
        const requireNumbers = <?= isset($datos['requisitos']['requiere_numeros']) && $datos['requisitos']['requiere_numeros'] ? 'true' : 'false' ?>;
        const requireSymbols = <?= isset($datos['requisitos']['requiere_simbolos']) && $datos['requisitos']['requiere_simbolos'] ? 'true' : 'false' ?>;
        
        // Mostrar/ocultar contraseña
        document.getElementById('togglePassword1').addEventListener('click', function() {
            togglePasswordVisibility('nueva_contrasena', this);
        });
        
        document.getElementById('togglePassword2').addEventListener('click', function() {
            togglePasswordVisibility('confirmar_contrasena', this);
        });
        
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Verificar fuerza de la contraseña
        document.getElementById('nueva_contrasena').addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            const strengthBar = document.getElementById('passwordStrength');
            
            // Actualizar barra de fuerza
            strengthBar.style.width = strength.percentage + '%';
            strengthBar.style.backgroundColor = strength.color;
            
            // Actualizar estado de requisitos visualmente
            updateRequirementStatuses(password);
            
            // Verificar coincidencia con confirmación
            checkPasswordMatch();
        });
        
        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            checkPasswordMatch();
        });
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let maxPoints = 0;
            
            // Asignar puntos basados en los requisitos configurados
            if (minLength > 0) {
                maxPoints += 25;
                if (password.length >= minLength) strength += 25;
            }
            
            if (requireUppercase) {
                maxPoints += 25;
                if (/[A-Z]/.test(password)) strength += 25;
            }
            
            if (requireLowercase) {
                maxPoints += 25;
                if (/[a-z]/.test(password)) strength += 25;
            }
            
            if (requireNumbers) {
                maxPoints += 25;
                if (/[0-9]/.test(password)) strength += 25;
            }
            
            if (requireSymbols) {
                maxPoints += 25;
                if (/[^a-zA-Z0-9]/.test(password)) strength += 25;
            }
            
            // Si no hay requisitos (caso extremo), establecer 100% por defecto
            const percentage = maxPoints > 0 ? (strength / maxPoints * 100) : 100;
            
            // Determinar color según fuerza
            let color = '#dc3545'; // Rojo (débil)
            if (percentage >= 100) color = '#198754'; // Verde (fuerte)
            else if (percentage >= 75) color = '#20c997'; // Verde claro (muy bueno)
            else if (percentage >= 50) color = '#ffc107'; // Amarillo (media)
            else if (percentage >= 25) color = '#fd7e14'; // Naranja (baja)
            
            return {
                percentage: percentage,
                color: color
            };
        }
        
        function updateRequirementStatuses(password) {
            const requirementItems = document.querySelectorAll('.password-info li');
            
            let currentItem = 0;
            
            // Verificar longitud mínima
            if (minLength > 0 && currentItem < requirementItems.length) {
                updateRequirementStatus(requirementItems[currentItem], password.length >= minLength);
                currentItem++;
            }
            
            // Verificar mayúsculas
            if (requireUppercase && currentItem < requirementItems.length) {
                updateRequirementStatus(requirementItems[currentItem], /[A-Z]/.test(password));
                currentItem++;
            }
            
            // Verificar minúsculas
            if (requireLowercase && currentItem < requirementItems.length) {
                updateRequirementStatus(requirementItems[currentItem], /[a-z]/.test(password));
                currentItem++;
            }
            
            // Verificar números
            if (requireNumbers && currentItem < requirementItems.length) {
                updateRequirementStatus(requirementItems[currentItem], /[0-9]/.test(password));
                currentItem++;
            }
            
            // Verificar símbolos
            if (requireSymbols && currentItem < requirementItems.length) {
                updateRequirementStatus(requirementItems[currentItem], /[^a-zA-Z0-9]/.test(password));
            }
        }
        
        function updateRequirementStatus(element, isValid) {
            // Extraer el texto original sin íconos
            const originalText = element.textContent.replace(/^[\u00A0-\u9999<>&]+ /, '').trim();
            
            if (isValid) {
                element.innerHTML = '<i class="fas fa-check text-success"></i> ' + originalText;
                element.classList.add('text-success');
                element.classList.remove('text-muted');
            } else {
                element.innerHTML = '<i class="fas fa-times text-danger"></i> ' + originalText;
                element.classList.add('text-muted');
                element.classList.remove('text-success');
            }
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('nueva_contrasena').value;
            const confirm = document.getElementById('confirmar_contrasena').value;
            const matchText = document.getElementById('passwordMatch');
            const submitButton = document.getElementById('submitButton');
            
            if (confirm.length === 0) {
                matchText.innerHTML = '';
                return;
            }
            
            if (password === confirm) {
                matchText.innerHTML = '<i class="fas fa-check-circle text-success"></i> Las contraseñas coinciden';
                matchText.className = 'form-text text-success';
                // Solo habilitar el botón si la contraseña cumple con los requisitos
                const strength = checkPasswordStrength(password);
                submitButton.disabled = strength.percentage < 100;
            } else {
                matchText.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Las contraseñas no coinciden';
                matchText.className = 'form-text text-danger';
                submitButton.disabled = true;
            }
        }
        
        // Inicializar los iconos en los requisitos
        document.addEventListener('DOMContentLoaded', function() {
            const requirementItems = document.querySelectorAll('.password-info li');
            requirementItems.forEach(item => {
                // Guardar el texto original sin formato
                const originalText = item.textContent.trim();
                // Añadir el icono
                item.innerHTML = '<i class="fas fa-times text-danger"></i> ' + originalText;
            });
        });
    </script>
</body>
</html>
