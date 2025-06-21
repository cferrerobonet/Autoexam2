<?php
/**
 * Barra de navegación para el panel de administración - AUTOEXAM2
 */
// Asegurar que existe la sesión
if (!isset($_SESSION)) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fs-4 fw-bold d-flex align-items-center" href="<?= BASE_URL ?>">
            <div class="logo-container me-3">
                <img src="<?= BASE_URL ?>/recursos/logo.png" alt="Logo AUTOEXAM2" class="logo-img"> 
            </div>
            <?= defined('SYSTEM_NAME') ? SYSTEM_NAME : 'AUTOEXAM2' ?>
        </a>
        
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="perfilDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> 
                        <span class="user-name">
                        <?php
                        // Mostrar nombre completo del usuario si está disponible
                        $nombreUsuario = isset($datos['usuario']['nombre']) ? $datos['usuario']['nombre'] : ($_SESSION['nombre'] ?? 'Admin');
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
                            <i class="fas fa-id-card text-primary"></i> Mi Perfil
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

<div class="container-fluid">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3 sidebar-sticky">
                <!-- Información del usuario -->
                <div class="text-center mb-3">
                    <div class="user-info d-flex align-items-center justify-content-center mb-3">
                        <div class="me-3">
                            <?php if (!empty($_SESSION['foto'])): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['foto']) ?>" 
                                     class="rounded-circle me-2" width="80" height="80" 
                                     alt="Avatar" style="object-fit: cover; border: 3px solid var(--color-admin-primary);">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; min-width: 80px; border: 3px solid var(--color-admin-sidebar-border);">
                                    <i class="fas fa-user text-white" style="font-size: 32px;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-start">
                            <h5 class="mb-0">
                                <?= isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Administrador' ?>
                            </h5>
                            <div class="badge bg-primary">Administrador</div>
                        </div>
                    </div>
                </div>
                
                <!-- Menú principal con iconos en primer plano -->
                <h6 class="sidebar-heading px-3 mt-3 mb-2 text-muted text-uppercase">
                    <i class="fas fa-th-large me-2"></i> Principal
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'inicio' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/inicio">
                            <i class="fas fa-tachometer-alt text-primary me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'usuarios' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/usuarios">
                            <i class="fas fa-users text-primary me-2"></i>
                            Usuarios
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'sesiones_activas' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/sesiones_activas">
                            <i class="fas fa-user-clock text-primary me-2"></i>
                            Sesiones Activas
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'configuracion' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/configuracion">
                            <i class="fas fa-cogs text-primary me-2"></i>
                            Configuración
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'mantenimiento' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/mantenimiento">
                            <i class="fas fa-tools text-primary me-2"></i>
                            Mantenimiento
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Menú académico -->
                <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'profesor')): ?>
                <h6 class="sidebar-heading px-3 mt-4 mb-2 text-muted text-uppercase">
                    <i class="fas fa-book me-2"></i> Académico
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'cursos' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/cursos">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            Cursos
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'modulos' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/modulos">
                            <i class="fas fa-puzzle-piece text-primary me-2"></i>
                            Módulos
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'examenes' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/examenes">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Exámenes
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
                
                <!-- Menú de mantenimiento -->
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <h6 class="sidebar-heading px-3 mt-4 mb-2 text-muted text-uppercase">
                    <i class="fas fa-tools me-2"></i> Mantenimiento
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'registros' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/registros">
                            <i class="fas fa-clipboard-list text-primary me-2"></i>
                            Registros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($GLOBALS['controlador'] ?? '') === 'copias' ? 'active' : '' ?>" 
                           href="<?= BASE_URL ?>/copias">
                            <i class="fas fa-database text-primary me-2"></i>
                            Copias de seguridad
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </nav>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- El contenido se insertará aquí -->
