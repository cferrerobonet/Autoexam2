<?php
/**
 * Vista de Importación de Módulos - AUTOEXAM2
 */

// Verificar permisos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . BASE_URL . '/error/acceso');
    exit;
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-upload me-2"></i> Importar Módulos</h2>
                <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Instrucciones -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Instrucciones
                    </h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>El archivo debe estar en formato CSV con separador punto y coma (;)</li>
                        <li>La primera fila debe contener los encabezados</li>
                        <li>Las columnas requeridas son: <strong>Título, Descripción, ID Curso, Orden</strong></li>
                        <li>El ID del curso debe existir en el sistema</li>
                        <li>El orden debe ser un número entero positivo</li>
                        <li>Los módulos se crearán como activos por defecto</li>
                    </ol>
                    
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/modulos/plantilla-csv" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i> Descargar Plantilla CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Formulario de importación -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-csv me-2"></i>Seleccionar Archivo
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/modulos/procesar-importacion" method="POST" enctype="multipart/form-data" id="formImportar">
                        <input type="hidden" name="csrf_token" value="<?= $datos['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label for="archivo" class="form-label">Archivo CSV</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" 
                                   accept=".csv" required>
                            <div class="form-text">
                                Máximo 2MB. Solo archivos CSV.
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirmarImportacion" required>
                            <label class="form-check-label" for="confirmarImportacion">
                                Confirmo que el archivo CSV tiene el formato correcto y entiendo que esta acción creará nuevos módulos en el sistema.
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/modulos" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success" id="btnImportar" disabled>
                                <i class="fas fa-upload me-1"></i> Importar Módulos
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Ejemplo de formato -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Ejemplo de formato CSV
                    </h6>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>Título;Descripción;ID Curso;Orden
Introducción;Módulo introductorio al curso;1;1
Conceptos Básicos;Fundamentos teóricos principales;1;2
Práctica Inicial;Ejercicios prácticos introductorios;1;3</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmarCheckbox = document.getElementById('confirmarImportacion');
    const btnImportar = document.getElementById('btnImportar');
    const formImportar = document.getElementById('formImportar');

    // Habilitar/deshabilitar botón según checkbox
    confirmarCheckbox.addEventListener('change', function() {
        btnImportar.disabled = !this.checked;
    });

    // Mostrar loading al enviar
    formImportar.addEventListener('submit', function() {
        btnImportar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Importando...';
        btnImportar.disabled = true;
    });

    // Validar archivo
    document.getElementById('archivo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('El archivo es demasiado grande. Máximo 2MB.');
                this.value = '';
                return;
            }
            
            if (!file.name.toLowerCase().endsWith('.csv')) {
                alert('Solo se permiten archivos CSV.');
                this.value = '';
                return;
            }
        }
    });
});
</script>
