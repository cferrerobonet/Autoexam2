<?php
/**
 * Vista: Importar Usuarios - AUTOEXAM2
 */

require_once APP_PATH . '/vistas/parciales/header_admin.php';
require_once APP_PATH . '/vistas/parciales/navbar_admin.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/inicio">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/usuarios">Usuarios</a></li>
                    <li class="breadcrumb-item active">Importar</li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0">Importar Usuarios</h2>
                    <p class="text-muted mb-0">Carga masiva de usuarios desde archivo CSV</p>
                </div>
                <a href="<?= BASE_URL ?>/usuarios" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
                </a>
            </div>

            <div class="row">
                <!-- Formulario de Importación -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-upload me-2"></i>Cargar Archivo
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="<?= BASE_URL ?>/usuarios/procesarImportacion" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                                
                                <div class="mb-4">
                                    <label for="archivo" class="form-label">Archivo CSV</label>
                                    <input type="file" class="form-control" id="archivo" name="archivo" 
                                           accept=".csv,.txt" required>
                                    <div class="form-text">Solo se permiten archivos CSV (máximo 5MB)</div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Importante:</strong> Los usuarios importados recibirán un email con sus credenciales de acceso (si el correo está configurado).
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Importar Usuarios
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-question-circle me-2"></i>Formato del Archivo
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Estructura requerida:</h6>
                            <div class="bg-light p-2 rounded mb-3">
                                <code>
                                    nombre,apellidos,correo,rol,curso_asignado<br>
                                    Juan,Pérez García,juan.perez@email.com,alumno,1<br>
                                    María,García López,maria.garcia@email.com,profesor,<br>
                                    Pedro,Martín Ruiz,pedro.martin@email.com,admin,
                                </code>
                            </div>

                            <h6>Campos obligatorios:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>nombre</li>
                                <li><i class="fas fa-check text-success me-2"></i>apellidos</li>
                                <li><i class="fas fa-check text-success me-2"></i>correo</li>
                                <li><i class="fas fa-check text-success me-2"></i>rol (admin, profesor, alumno)</li>
                            </ul>

                            <h6>Campos opcionales:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-minus text-muted me-2"></i>curso_asignado (ID del curso)</li>
                            </ul>

                            <div class="alert alert-warning p-2">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Las contraseñas se generan automáticamente y se envían por email.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Plantilla -->
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-download me-2"></i>Plantilla
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Descarga una plantilla CSV para facilitar la importación:</p>
                            <a href="<?= BASE_URL ?>/usuarios/descargarPlantilla" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download me-2"></i>Descargar Plantilla
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/vistas/parciales/footer_admin.php'; ?>
