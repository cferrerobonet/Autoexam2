<?php
/**
 * Navbar para vistas del alumno - AUTOEXAM2
 * 
 * Menú de navegación para el perfil de alumno
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/inicio">
            <img src="<?= BASE_URL ?>/recursos/logo.png" alt="<?= SYSTEM_NAME ?>" height="32">
            <?= SYSTEM_NAME ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAlumno" 
                aria-controls="navbarAlumno" aria-expanded="false" aria-label="Alternar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAlumno">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/cursos/misCursos">
                        <i class="fas fa-book-open"></i> Mis Cursos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/examenes/disponibles">
                        <i class="fas fa-file-alt"></i> Exámenes Disponibles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/examenes/historial-examenes">
                        <i class="fas fa-history"></i> Historial
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/calendario">
                        <i class="fas fa-calendar-alt"></i> Calendario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/calificaciones">
                        <i class="fas fa-star"></i> Calificaciones
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
                        <?= isset($datos['usuario']['nombre']) ? $datos['usuario']['nombre'] : 'Usuario' ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="perfilDropdown">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil">
                            <i class="fas fa-id-card"></i> Mi Perfil
                        </a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/perfil/cambiar-contrasena">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/autenticacion/logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
