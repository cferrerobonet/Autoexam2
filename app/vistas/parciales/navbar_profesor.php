<?php
/**
 * Navbar para vistas del profesor - AUTOEXAM2
 * 
 * Menú de navegación para el perfil de profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fs-4 fw-bold d-flex align-items-center" href="<?= BASE_URL ?>/inicio">
            <div class="logo-container me-3">
                <img src="<?= BASE_URL ?>/recursos/logo.png" alt="<?= SYSTEM_NAME ?>" class="logo-img">
            </div>
            <?= SYSTEM_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarProfesor" 
                aria-controls="navbarProfesor" aria-expanded="false" aria-label="Alternar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarProfesor">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/cursos">
                        <i class="fas fa-book-open"></i> Cursos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/modulos">
                        <i class="fas fa-puzzle-piece"></i> Módulos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/usuarios">
                        <i class="fas fa-users"></i> Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/examenes">
                        <i class="fas fa-file-alt"></i> Exámenes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/banco-preguntas">
                        <i class="fas fa-question-circle"></i> Banco de Preguntas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/calendario">
                        <i class="fas fa-calendar-alt"></i> Calendario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/estadisticas">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificacionesDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger notification-badge">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificacionesDropdown">
                        <li><h6 class="dropdown-header">Notificaciones</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small" href="#">No hay notificaciones nuevas</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="perfilDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> 
                        <span class="user-name">
                        <?php
                        // Mostrar nombre completo del usuario si está disponible
                        $nombreUsuario = isset($datos['usuario']['nombre']) ? $datos['usuario']['nombre'] : ($_SESSION['nombre'] ?? 'Usuario');
                        $apellidosUsuario = isset($datos['usuario']['apellidos']) ? $datos['usuario']['apellidos'] : ($_SESSION['apellidos'] ?? '');
                        
                        $nombreCompleto = htmlspecialchars($nombreUsuario);
                        if (!empty($apellidosUsuario)) {
                            $nombreCompleto .= ' ' . htmlspecialchars($apellidosUsuario);
                        }
                        
                        // Si el nombre es muy largo, mostrar solo el nombre de pila
                        if (strlen($nombreCompleto) > 20) {
                            echo htmlspecialchars($nombreUsuario);
                        } else {
                            echo $nombreCompleto;
                        }
                        ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="perfilDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">
                            <i class="fas fa-id-card text-primary"></i> Perfil
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil/cambiar-contrasena">
                            <i class="fas fa-key text-warning"></i> Cambiar Contraseña
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/autenticacion/logout"
                               onclick="return confirm('¿Seguro que desea cerrar sesión?')">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
