<?php
/**
 * Footer para vistas del profesor - AUTOEXAM2
 * 
 * Pie de página para las vistas del profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
?>
<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <span class="text-muted"><?= SYSTEM_NAME ?> &copy; <?= date('Y') ?> - Panel de Profesores</span>
            </div>
            <div class="col-md-6 text-end">
                <a href="<?= BASE_URL ?>/ayuda" class="text-decoration-none text-muted me-3">
                    <i class="fas fa-question-circle"></i> Ayuda
                </a>
                <a href="<?= BASE_URL ?>/contacto" class="text-decoration-none text-muted me-3">
                    <i class="fas fa-envelope"></i> Contacto
                </a>
            </div>
        </div>
    </div>
</footer>
