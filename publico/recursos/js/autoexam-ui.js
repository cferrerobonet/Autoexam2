/**
 * Script global para unificación de estilos de UI en AUTOEXAM2
 * 
 * Este script se encarga de aplicar automáticamente los estilos de badges y botones
 * en toda la aplicación para mantener la coherencia visual.
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Función para unificar la apariencia de badges y botones en toda la aplicación
     */
    function unificarEstilosUI() {
        // Aplicar estilo a botones de acción en tablas
        document.querySelectorAll('.table tbody a.btn:not(.rounded-pill), .table tbody button.btn:not(.rounded-pill)').forEach(btn => {
            btn.classList.add('btn-light', 'rounded-pill', 'border', 'px-2', 'shadow-sm');
            btn.classList.remove('btn-primary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info');
            
            // Colorear iconos dentro de los botones
            const icon = btn.querySelector('i.fas, i.far, i.fab');
            if (icon) {
                // Detectar el tipo de acción basado en clases o texto del botón
                if (btn.innerHTML.includes('Editar') || icon.classList.contains('fa-edit') || icon.classList.contains('fa-pencil-alt')) {
                    icon.classList.add('text-primary');
                } else if (btn.innerHTML.includes('Ver') || btn.innerHTML.includes('Mostrar') || icon.classList.contains('fa-eye')) {
                    icon.classList.add('text-info');
                } else if (btn.innerHTML.includes('Eliminar') || icon.classList.contains('fa-trash')) {
                    icon.classList.add('text-danger');
                } else if (btn.innerHTML.includes('Descargar') || icon.classList.contains('fa-download')) {
                    icon.classList.add('text-success');
                } else if (btn.innerHTML.includes('Historial') || icon.classList.contains('fa-history')) {
                    icon.classList.add('text-info');
                } else if (btn.innerHTML.includes('Añadir') || icon.classList.contains('fa-plus')) {
                    icon.classList.add('text-success');
                } else if (btn.innerHTML.includes('Bloquear') || icon.classList.contains('fa-ban')) {
                    icon.classList.add('text-danger');
                }
            }
        });
        
        // Transformar badges regulares a pill badges con bordes
        document.querySelectorAll('.badge:not(.rounded-pill)').forEach(badge => {
            badge.classList.add('rounded-pill');
        });
        
        // Aplicar estilos específicos a badges de rol
        document.querySelectorAll('[data-rol]').forEach(badge => {
            const rol = badge.getAttribute('data-rol');
            badge.classList.add('rounded-pill');
            
            if (rol === 'admin') {
                badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-crown"></i> ' + badge.innerHTML;
                }
            } else if (rol === 'profesor') {
                badge.className = 'badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-chalkboard-teacher"></i> ' + badge.innerHTML;
                }
            } else if (rol === 'alumno') {
                badge.className = 'badge rounded-pill bg-purple text-white';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-user-graduate"></i> ' + badge.innerHTML;
                }
            }
        });
        
        // Aplicar estilos específicos a badges de estado
        document.querySelectorAll('[data-estado]').forEach(badge => {
            const estado = badge.getAttribute('data-estado');
            badge.classList.add('rounded-pill');
            
            if (estado === 'activo' || estado === '1') {
                badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-check"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'inactivo' || estado === '0') {
                badge.className = 'badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-times"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'pendiente') {
                badge.className = 'badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-clock"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'completado') {
                badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-check-double"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'revisado') {
                badge.className = 'badge rounded-pill bg-info-subtle text-info border border-info-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-eye"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'suspendido') {
                badge.className = 'badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-times-circle"></i> ' + badge.innerHTML;
                }
            } else if (estado === 'aprobado') {
                badge.className = 'badge rounded-pill bg-success-subtle text-success border border-success-subtle';
                if (!badge.querySelector('i')) {
                    badge.innerHTML = '<i class="fas fa-check-circle"></i> ' + badge.innerHTML;
                }
            }
        });
    }
    
    // Aplicar estilos iniciales
    unificarEstilosUI();
    
    // Volver a aplicar estilos después de cambios dinámicos en el DOM
    // como tablas que se cargan mediante AJAX
    const observer = new MutationObserver(function(mutations) {
        unificarEstilosUI();
    });
    
    // Observar cambios en el cuerpo del documento
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // También actualizar cuando se carguen datos dinámicamente (por ejemplo, con AJAX)
    document.addEventListener('DOMContentLoaded', unificarEstilosUI);
    window.addEventListener('load', unificarEstilosUI);
});
