<?php
/**
 * Scripts para vistas del profesor - AUTOEXAM2
 * 
 * JavaScript y recursos para las vistas del profesor
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
<script src="<?= BASE_URL ?>/publico/recursos/js/profesor.js"></script>

<!-- Script para inicializar tooltips y menús desplegables -->
<script>
    // Inicializar componentes de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Inicializar dropdowns (menús desplegables) con configuración explícita
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
        const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl, {
                autoClose: true,
                boundary: 'clippingParents'
            })
        });
        
        // Forzar la inicialización del menú de perfil específicamente
        const perfilDropdown = document.getElementById('perfilDropdown');
        if (perfilDropdown) {
            new bootstrap.Dropdown(perfilDropdown, {
                autoClose: true,
                boundary: 'clippingParents'
            });
        }
        
        // Asegurar que los menús de navegación funcionen correctamente en móviles
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                const navbarCollapse = document.querySelector(this.getAttribute('data-bs-target'));
                if (navbarCollapse) {
                    // Alternar manualmente si hay problemas con el toggle automático
                    if (navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.remove('show');
                    } else {
                        navbarCollapse.classList.add('show');
                    }
                }
            });
        }
    });

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
