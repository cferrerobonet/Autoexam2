<?php
/**
 * Barra de navegación para el panel de administración - AUTOEXAM2
 */
// Asegurar que existe la sesión
if (!isset($_SESSION)) {
    session_start();
}
?>
<header class="navbar navbar-dark sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="<?= BASE_URL ?>">
        <img src="<?= BASE_URL ?>/recursos/logo.png" alt="Logo AUTOEXAM2" height="32" class="d-inline-block align-middle me-2"> 
        <?= defined('SYSTEM_NAME') ? SYSTEM_NAME : 'AUTOEXAM2' ?>
    </a>
    
    <button class="navbar-toggler position-absolute d-md-none collapsed border-0" type="button" 
            data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" 
            aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="w-100 d-none d-md-block"></div>
    
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="<?= BASE_URL ?>/autenticacion/logout" 
               onclick="return confirm('¿Seguro que desea cerrar sesión?')">
                <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
            </a>
        </div>
    </div>
</header>

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
                                     class="rounded-circle me-2" width="60" height="60" 
                                     alt="Avatar" style="object-fit: cover; border: 2px solid var(--color-admin-primary);">
                            <?php else: ?>
                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px; min-width: 60px; border: 2px solid var(--color-admin-sidebar-border);">
                                    <i class="fas fa-user text-white" style="font-size: 24px;"></i>
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
