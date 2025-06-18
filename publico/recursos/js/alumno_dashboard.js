/**
 * JavaScript para el dashboard de alumnos - AUTOEXAM2
 * 
 * Funciones específicas para el panel de alumnos
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar FullCalendar
    initCalendario();
    
    // Cargar exámenes activos
    cargarExamenesActivos();
    
    // Cargar tabla de calificaciones
    cargarTablaCalificaciones();
    
    // Inicializar gráficos estadísticos
    initEstadisticas();
    
    // Inicializar cuenta regresiva para exámenes
    initCuentaRegresiva();
    
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

/**
 * Inicializa el calendario de FullCalendar
 */
function initCalendario() {
    const calendarioEl = document.getElementById('calendario-alumno');
    if (!calendarioEl) return;
    
    // En un sistema real, obtendríamos eventos desde el servidor
    // Por ahora usamos datos de ejemplo
    const eventos = [
        {
            title: 'Examen Matemáticas',
            start: '2025-06-20',
            color: '#4285F4',
            id: 'exam-1'
        },
        {
            title: 'Examen Literatura',
            start: '2025-06-22',
            color: '#34A853',
            id: 'exam-2'
        },
        {
            title: 'Examen Historia',
            start: '2025-06-25',
            color: '#EA4335',
            id: 'exam-3'
        }
    ];
    
    const calendario = new FullCalendar.Calendar(calendarioEl, {
        initialView: 'listWeek',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listWeek'
        },
        height: 300,
        events: eventos,
        eventClick: function(info) {
            // En producción, redirigir a la página del examen
            alert(`Has seleccionado: ${info.event.title}`);
            // window.location.href = `/examenes/ver/${info.event.id}`;
        }
    });
    
    calendario.render();
}

/**
 * Carga los exámenes activos del alumno
 */
function cargarExamenesActivos() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const contenedor = document.getElementById('lista-examenes-activos');
        if (!contenedor) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const examenes = [
            {
                id: 1,
                titulo: 'Ecuaciones de segundo grado',
                curso: 'Matemáticas 3º ESO',
                modulo: 'Álgebra',
                fecha_limite: '20/06/2025',
                tiempo_restante: '2d 5h 32m',
                tipo: 'primary'
            },
            {
                id: 2,
                titulo: 'Teoría de la evolución',
                curso: 'Biología 4º ESO',
                modulo: 'Genética',
                fecha_limite: '25/06/2025',
                tiempo_restante: '10d 2h 15m',
                tipo: 'success'
            }
        ];
        
        // Generar HTML
        let html = '';
        
        if (examenes.length === 0) {
            html = `
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i> No tienes exámenes activos en este momento.
                    </div>
                </div>
            `;
        } else {
            examenes.forEach(examen => {
                const esCritico = examen.tiempo_restante.includes('d') && 
                                 parseInt(examen.tiempo_restante) <= 2;
                                 
                html += `
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 examen-card border-${examen.tipo}">
                            <div class="card-header bg-${examen.tipo} text-white">
                                <h6 class="mb-0">${examen.titulo}</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><strong>Módulo:</strong> ${examen.modulo}</p>
                                <p class="card-text"><strong>Curso:</strong> ${examen.curso}</p>
                                <p class="card-text"><strong>Fecha límite:</strong> ${examen.fecha_limite}</p>
                                <p class="card-text">
                                    <strong>Tiempo restante:</strong> 
                                    <span class="text-${esCritico ? 'danger' : examen.tipo} countdown-timer" 
                                          data-target-date="${examen.fecha_limite}">
                                        ${examen.tiempo_restante}
                                    </span>
                                </p>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="#" class="btn btn-${examen.tipo} w-100">
                                    <i class="fas fa-play me-2"></i> Comenzar examen
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        contenedor.innerHTML = html;
    }, 800);
}

/**
 * Carga la tabla de calificaciones del alumno
 */
function cargarTablaCalificaciones() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const tablaCalificaciones = document.getElementById('tabla-calificaciones');
        if (!tablaCalificaciones) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const calificaciones = [
            {
                id: 1,
                examen: 'Problemas con fracciones',
                curso: 'Matemáticas 3º ESO',
                modulo: 'Álgebra',
                fecha: '01/06/2025',
                nota: 8.5,
                estado: 'aprobado'
            },
            {
                id: 2,
                examen: 'Principios de genética',
                curso: 'Biología 4º ESO',
                modulo: 'Genética',
                fecha: '15/05/2025',
                nota: 4.2,
                estado: 'no_aprobado'
            },
            {
                id: 3,
                examen: 'Preposiciones inglesas',
                curso: 'Inglés 3º ESO',
                modulo: 'Gramática',
                fecha: '05/05/2025',
                nota: 7.8,
                estado: 'aprobado'
            }
        ];
        
        // Mapeo de estados a estilos visuales
        const estadosUI = {
            'aprobado': { clase: 'bg-success', texto: 'Aprobado' },
            'no_aprobado': { clase: 'bg-danger', texto: 'No aprobado' },
            'pendiente': { clase: 'bg-warning text-dark', texto: 'Pendiente' },
            'corrigiendo': { clase: 'bg-info', texto: 'Corrigiendo' }
        };
        
        // Generar HTML
        let html = '';
        
        if (calificaciones.length === 0) {
            html = `
                <tr>
                    <td colspan="6" class="text-center py-3">
                        No hay calificaciones disponibles.
                    </td>
                </tr>
            `;
        } else {
            calificaciones.forEach(calificacion => {
                // Obtener configuración de UI para este estado
                const estadoUI = estadosUI[calificacion.estado] || { clase: 'bg-secondary', texto: 'Desconocido' };
                
                // Determinar clase CSS para la nota
                let notaClase = 'bg-success';
                if (calificacion.nota < 5) {
                    notaClase = 'bg-danger';
                } else if (calificacion.nota < 7) {
                    notaClase = 'bg-warning';
                }
                
                html += `
                    <tr>
                        <td>
                            <div>
                                <h6 class="mb-0">${calificacion.examen}</h6>
                                <small class="text-muted">${calificacion.curso}</small>
                            </div>
                        </td>
                        <td>${calificacion.modulo}</td>
                        <td>${calificacion.fecha}</td>
                        <td>
                            <span class="badge rounded-pill ${notaClase}">${calificacion.nota.toFixed(1)}</span>
                        </td>
                        <td><span class="badge ${estadoUI.clase}">${estadoUI.texto}</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                `;
            });
        }
        
        tablaCalificaciones.innerHTML = html;
        
        // Actualizar contadores
        document.getElementById('total-examenes-realizados').innerText = '7';
        document.getElementById('nota-media-alumno').innerText = '7.2';
    }, 800);
}

/**
 * Inicializa los gráficos estadísticos
 */
function initEstadisticas() {
    const ctx = document.getElementById('grafico-calificaciones-alumno');
    if (!ctx) return;
    
    // Crear gráfico con Chart.js
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Matemáticas', 'Biología', 'Inglés', 'Historia'],
            datasets: [{
                label: 'Calificación Media',
                data: [8.5, 6.2, 7.8, 6.5],
                backgroundColor: [
                    'rgba(92, 184, 92, 0.7)',
                    'rgba(66, 133, 244, 0.7)',
                    'rgba(240, 173, 78, 0.7)',
                    'rgba(217, 83, 79, 0.7)'
                ],
                borderColor: [
                    'rgba(92, 184, 92, 1)',
                    'rgba(66, 133, 244, 1)',
                    'rgba(240, 173, 78, 1)',
                    'rgba(217, 83, 79, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

/**
 * Inicializa la cuenta regresiva para exámenes activos
 */
function initCuentaRegresiva() {
    // Identificar todos los elementos con clase countdown-timer
    const timerElements = document.querySelectorAll('.countdown-timer');
    
    // Si no hay elementos, salir
    if (timerElements.length === 0) return;
    
    // Para cada elemento, iniciar cuenta regresiva
    timerElements.forEach(element => {
        // En producción se usaría el atributo data-target-date
        // Por ahora usamos una fecha fija de ejemplo
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 2); // Fecha objetivo: hoy + 2 días
        
        // Actualizar cada segundo
        const interval = setInterval(() => {
            const now = new Date().getTime();
            const distance = targetDate - now;
            
            // Si la cuenta regresiva ha terminado
            if (distance < 0) {
                clearInterval(interval);
                element.innerHTML = '<span class="text-danger">Expirado</span>';
                return;
            }
            
            // Cálculos de tiempo
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Mostrar tiempo restante
            element.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            
            // Cambiar color según urgencia
            if (days <= 1) {
                element.classList.add('text-danger');
                element.classList.remove('text-success', 'text-primary');
            }
        }, 1000);
    });
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
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    // Simular recarga (en producción sería una llamada AJAX)
    setTimeout(() => {
        // Invocar función apropiada según el componente
        switch(componenteId) {
            case 'calendario-alumno':
                initCalendario();
                break;
            case 'lista-examenes-activos':
                cargarExamenesActivos();
                break;
            case 'tabla-calificaciones':
                cargarTablaCalificaciones();
                break;
            case 'grafico-calificaciones-alumno':
                initEstadisticas();
                break;
            default:
                // Intentar recargar la página si no hay un manejador específico
                location.reload();
        }
    }, 1000);
}
