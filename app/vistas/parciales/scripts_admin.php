<!-- JavaScript esencial para el panel de administración -->
<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (necesario para DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Script de unificación de UI -->
<script src="<?= BASE_URL ?>/recursos/js/autoexam-ui.js"></script>

<script>
    // Configuración básica de DataTables
    $(document).ready(function() {
        if ($('.data-table').length > 0) {
            $('.data-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
                },
                responsive: true
            });
        }
    });
</script>

<!-- JavaScript para acciones masivas -->
<script>
// Variables globales para acciones masivas
let elementosSeleccionados = [];

// Función para manejar acciones masivas
function accionMasiva(tipo) {
    const checkboxes = document.querySelectorAll('input[name="seleccionar[]"]:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        alert('Debes seleccionar al menos un elemento para realizar esta acción.');
        return;
    }

    let mensaje = '';
    let url = '';
    const modulo = obtenerModuloActual();

    switch (tipo) {
        case 'desactivar':
            mensaje = `¿Estás seguro de que quieres desactivar ${ids.length} elemento(s) seleccionado(s)?`;
            url = `${BASE_URL}/${modulo}/desactivar-masivo`;
            break;
        case 'exportar':
            mensaje = `¿Quieres exportar ${ids.length} elemento(s) seleccionado(s)?`;
            url = `${BASE_URL}/${modulo}/exportar-seleccionados`;
            break;
        default:
            alert('Acción no válida');
            return;
    }

    if (confirm(mensaje)) {
        // Crear formulario para enviar los IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        // Agregar token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="csrf_token"]')?.value;
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }

        // Agregar IDs seleccionados
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        // Agregar tipo de acción
        const accionInput = document.createElement('input');
        accionInput.type = 'hidden';
        accionInput.name = 'accion';
        accionInput.value = tipo;
        form.appendChild(accionInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Función para obtener el módulo actual basado en la URL
function obtenerModuloActual() {
    const path = window.location.pathname;
    if (path.includes('/usuarios')) return 'usuarios';
    if (path.includes('/cursos')) return 'cursos';
    if (path.includes('/modulos')) return 'modulos';
    return 'usuarios'; // Por defecto
}

// Función para seleccionar/deseleccionar todos los elementos
function toggleSeleccionarTodos(checkbox) {
    const checkboxes = document.querySelectorAll('input[name="seleccionar[]"]');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    actualizarBotonesAcciones();
}

// Función para actualizar el estado de los botones de acciones masivas
function actualizarBotonesAcciones() {
    const checkboxes = document.querySelectorAll('input[name="seleccionar[]"]:checked');
    const botonAcciones = document.getElementById('accionesMasivas');
    
    if (botonAcciones) {
        botonAcciones.disabled = checkboxes.length === 0;
    }
}

// Event listeners para checkboxes
document.addEventListener('DOMContentLoaded', function() {
    // Listener para checkboxes individuales
    const checkboxes = document.querySelectorAll('input[name="seleccionar[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', actualizarBotonesAcciones);
    });

    // Listener para checkbox de seleccionar todos
    const seleccionarTodos = document.getElementById('seleccionar_todos');
    if (seleccionarTodos) {
        seleccionarTodos.addEventListener('change', function() {
            toggleSeleccionarTodos(this);
        });
    }

    // Inicializar estado de botones
    actualizarBotonesAcciones();
});

// Función para confirmación de eliminación individual
function confirmarEliminacion(elemento, nombre) {
    const mensaje = `¿Estás seguro de que quieres eliminar "${nombre}"? Esta acción no se puede deshacer.`;
    return confirm(mensaje);
}

// Función para mostrar loading en botones
function mostrarLoading(boton, textoOriginal) {
    if (!boton.dataset.textoOriginal) {
        boton.dataset.textoOriginal = textoOriginal || boton.innerHTML;
    }
    boton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando...';
    boton.disabled = true;
}

// Función para restaurar botón después de loading
function restaurarBoton(boton) {
    if (boton.dataset.textoOriginal) {
        boton.innerHTML = boton.dataset.textoOriginal;
        boton.disabled = false;
    }
}
</script>
