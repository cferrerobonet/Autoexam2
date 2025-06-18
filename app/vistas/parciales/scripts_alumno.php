<?php
/**
 * Scripts para vistas del alumno - AUTOEXAM2
 * 
 * JavaScript y recursos para las vistas del alumno
 * 
 * @author GitHub Copilot
 * @version 1.0
 */
?>
<!-- Scripts generales -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

<!-- Script de unificación de UI -->
<script src="<?= BASE_URL ?>/recursos/js/autoexam-ui.js"></script>

<!-- Scripts personalizados -->
<script src="<?= BASE_URL ?>/publico/recursos/js/alumno.js"></script>

<!-- Script para inicializar tooltips -->
<script>
    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    // Función para manejar la cuenta regresiva en exámenes activos
    function iniciarCuentaRegresiva(elementosId, fechaFin) {
        const elementos = document.querySelectorAll(elementosId);
        if (!elementos.length) return;
        
        const intervalo = setInterval(function() {
            const ahora = new Date().getTime();
            const tiempoRestante = new Date(fechaFin).getTime() - ahora;
            
            if (tiempoRestante < 0) {
                clearInterval(intervalo);
                elementos.forEach(elem => {
                    elem.innerHTML = '<span class="text-danger">Expirado</span>';
                });
                return;
            }
            
            // Cálculo de tiempo
            const horas = Math.floor(tiempoRestante / (1000 * 60 * 60));
            const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
            
            elementos.forEach(elem => {
                elem.innerHTML = `${horas}h ${minutos}m ${segundos}s`;
            });
        }, 1000);
    }

    // Función para manejar errores en componentes del dashboard
    function manejarErrorComponente(elementoId, mensaje = "Error al cargar contenido") {
        const elemento = document.getElementById(elementoId);
        if (elemento) {
            elemento.innerHTML = `
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn btn-sm btn-outline-secondary float-end" 
                            onclick="recargarComponente('${elementoId}')">
                        <i class="fas fa-sync"></i> Reintentar
                    </button>
                </div>
            `;
        }
    }

    // Función para recargar un componente individual
    function recargarComponente(elementoId) {
        // Implementación basada en AJAX
        console.log(`Recargando componente: ${elementoId}`);
        // Aquí iría la lógica de recarga específica para cada componente
    }
</script>
