/**
 * JavaScript para el dashboard de administradores - AUTOEXAM2
 * 
 * Funciones específicas para el panel de administradores
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar contadores
    initContadores();
    
    // Cargar actividad reciente 
    cargarActividadReciente();
    
    // Inicializar gráficos estadísticos
    initEstadisticas();
    
    // Comprobar estado del sistema
    comprobarEstadoSistema();
});

/**
 * Inicializa los contadores con animación
 */
function initContadores() {
    // Lista de elementos a animar
    const contadores = [
        { elemento: 'contador-admin', valorFinal: 3 },
        { elemento: 'contador-profesores', valorFinal: 12 },
        { elemento: 'contador-alumnos', valorFinal: 145 },
        { elemento: 'contador-cursos', valorFinal: 8 }
    ];
    
    // Animación para cada contador
    contadores.forEach(contador => {
        const elemento = document.getElementById(contador.elemento);
        if (!elemento) return;
        
        // Iniciar en 0
        let valorActual = 0;
        
        // Calcular incremento según valor final (más rápido para valores grandes)
        const incremento = Math.max(1, Math.floor(contador.valorFinal / 50));
        
        // Duración aproximada de 1 segundo para la animación
        const intervalo = setInterval(() => {
            valorActual += incremento;
            
            // No sobrepasar el valor final
            if (valorActual >= contador.valorFinal) {
                valorActual = contador.valorFinal;
                clearInterval(intervalo);
            }
            
            // Actualizar elemento DOM
            elemento.textContent = valorActual.toLocaleString();
        }, 50);
    });
}

/**
 * Carga la actividad reciente del sistema
 */
function cargarActividadReciente() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const contenedor = document.getElementById('acciones-recientes');
        if (!contenedor) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const actividades = [
            {
                tipo: 'usuario_creado',
                titulo: 'Nuevo usuario creado',
                descripcion: 'María López (alumno) ha sido registrado',
                tiempo: 'Hace 30 min',
                autor: 'Admin'
            },
            {
                tipo: 'curso_modificado',
                titulo: 'Curso modificado',
                descripcion: 'Matemáticas 3º ESO - Añadido nuevo módulo',
                tiempo: 'Hace 2 horas',
                autor: 'Admin'
            },
            {
                tipo: 'backup_sistema',
                titulo: 'Backup realizado',
                descripcion: 'Backup automático completo: BD y archivos',
                tiempo: 'Hace 3 días',
                autor: 'Sistema'
            }
        ];
        
        // Generar HTML
        let html = '';
        
        actividades.forEach(actividad => {
            html += `
                <div class="list-group-item actividad-item ${actividad.tipo}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${actividad.titulo}</h6>
                        <small class="text-muted">${actividad.tiempo}</small>
                    </div>
                    <p class="mb-1 small">${actividad.descripcion}</p>
                    <small>Por: ${actividad.autor}</small>
                </div>
            `;
        });
        
        contenedor.innerHTML = html;
    }, 800);
}

/**
 * Inicializa los gráficos estadísticos
 */
function initEstadisticas() {
    const ctx = document.getElementById('graficoEstadisticas');
    if (!ctx) return;
    
    // Crear gráfico con Chart.js
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Exámenes creados',
                data: [12, 19, 3, 5, 2, 3],
                backgroundColor: 'rgba(66, 133, 244, 0.2)',
                borderColor: 'rgba(66, 133, 244, 1)',
                borderWidth: 2,
                tension: 0.1
            }, {
                label: 'Exámenes realizados',
                data: [7, 11, 5, 8, 3, 7],
                backgroundColor: 'rgba(234, 67, 53, 0.2)',
                borderColor: 'rgba(234, 67, 53, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

/**
 * Comprueba el estado del sistema
 */
function comprobarEstadoSistema() {
    // En un sistema real, aquí habría varias llamadas AJAX para verificar cada componente
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        // Solo para demostración, en producción esto vendría del servidor
        document.getElementById('estado-smtp').className = 'badge bg-success';
        document.getElementById('estado-bd').className = 'badge bg-success';
        document.getElementById('estado-almacenamiento').className = 'badge bg-info';
        document.getElementById('estado-backup').className = 'badge bg-warning text-dark';
        document.getElementById('estado-ia').className = 'badge bg-success';
        document.getElementById('estado-seguridad').className = 'badge bg-success';
    }, 1000);
}

/**
 * Maneja errores en componentes específicos del dashboard
 * 
 * @param {string} componenteId ID del elemento HTML del componente
 * @param {string} mensaje Mensaje de error
 */
function manejarError(componenteId, mensaje = "Error al cargar datos") {
    const elemento = document.getElementById(componenteId);
    if (!elemento) return;
    
    elemento.innerHTML = `
        <div class="alert alert-warning" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>${mensaje}</div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" 
                        onclick="recargarComponente('${componenteId}')">
                    <i class="fas fa-sync"></i> Reintentar
                </button>
            </div>
        </div>
    `;
}

/**
 * Recarga un componente específico
 * 
 * @param {string} componenteId ID del elemento HTML del componente
 */
function recargarComponente(componenteId) {
    // Implementación básica, en producción tendría lógica específica para cada componente
    const elemento = document.getElementById(componenteId);
    if (!elemento) return;
    
    // Mostrar indicador de carga
    elemento.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    // Simular recarga (en producción sería una llamada AJAX)
    setTimeout(() => {
        // Invocar función apropiada según el componente
        switch(componenteId) {
            case 'acciones-recientes':
                cargarActividadReciente();
                break;
            case 'graficoEstadisticas':
                initEstadisticas();
                break;
            case 'estado-sistema':
                comprobarEstadoSistema();
                break;
            default:
                // Intentar recargar la página si no hay un manejador específico
                location.reload();
        }
    }, 1000);
}
