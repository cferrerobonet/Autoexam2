<?php
/**
 * Pie común para todas las vistas
 */
?>
</main>

<!-- Pie de página -->
<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <span class="text-muted">AUTOEXAM2 © 2025 - Sistema de gestión de exámenes</span>
    </div>
</footer>

<!-- Scripts de Bootstrap -->
<script src="<?= BASE_URL ?>/recursos/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/recursos/js/jquery.min.js"></script>

<!-- Scripts adicionales según el rol -->
<?php
$rol = $_SESSION['rol'] ?? 'admin';
if ($rol === 'admin') {
    echo '<script src="' . BASE_URL . '/recursos/js/admin.js"></script>';
} elseif ($rol === 'profesor') {
    echo '<script src="' . BASE_URL . '/recursos/js/profesor.js"></script>';
} else {
    echo '<script src="' . BASE_URL . '/recursos/js/alumno.js"></script>';
}
?>

</body>
</html>
