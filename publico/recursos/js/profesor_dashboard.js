/**
 * JavaScript para el dashboard de profesores - AUTOEXAM2
 * 
 * Funciones específicas para el panel de profesores
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar FullCalendar
    initCalendario();
    
    // Cargar notificaciones
    cargarNotificaciones();
    
    // Cargar tabla de cursos
    cargarTablaCursos();
    
    // Cargar tabla de exámenes
    cargarTablaExamenes();
    
    // Inicializar gráficos estadísticos
    initEstadisticas();
    
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
    const calendarioEl = document.getElementById('calendario-profesor');
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
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        height: 350,
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
 * Carga las notificaciones del profesor
 */
function cargarNotificaciones() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const contenedor = document.getElementById('lista-notificaciones-profesor');
        if (!contenedor) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const notificaciones = [
            {
                tipo: 'urgente',
                titulo: 'Exámenes por corregir',
                descripcion: 'Tienes 5 exámenes pendientes de corrección',
                tiempo: 'Hace 2 días'
            },
            {
                tipo: 'info',
                titulo: 'Sugerencias de IA disponibles',
                descripcion: '3 sugerencias para mejorar tus exámenes',
                tiempo: 'Hace 1 día'
            },
            {
                tipo: 'recordatorio',
                titulo: 'Próximo examen programado',
                descripcion: 'Matemáticas - 20 de junio de 2025',
                tiempo: 'Hace 5 horas'
            }
        ];
        
        // Generar HTML
        let html = '';
        
        notificaciones.forEach(notificacion => {
            html += `
                <div class="list-group-item list-group-item-action notificacion-item ${notificacion.tipo}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${notificacion.titulo}</h6>
                        <small class="text-${notificacion.tipo === 'urgente' ? 'danger' : 
                              notificacion.tipo === 'info' ? 'info' : 
                              'warning'}">${notificacion.tipo === 'urgente' ? 'Urgente' : 
                              notificacion.tipo === 'info' ? 'Nuevo' : 
                              'Recordatorio'}</small>
                    </div>
                    <p class="mb-1 small">${notificacion.descripcion}</p>
                    <small class="text-muted">${notificacion.tiempo}</small>
                </div>
            `;
        });
        
        contenedor.innerHTML = html;
    }, 800);
}

/**
 * Carga la tabla de cursos del profesor
 */
function cargarTablaCursos() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const tablaCursos = document.getElementById('tabla-cursos-profesor');
        if (!tablaCursos) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const cursos = [
            {
                id: 1,
                nombre: 'Matemáticas 3º ESO',
                curso: '2024-2025',
                modulo: 'Álgebra',
                alumnos: 24,
                examenes_activos: 3,
                icono: 'book',
                color: 'primary'
            },
            {
                id: 2,
                nombre: 'Biología 4º ESO',
                curso: '2024-2025',
                modulo: 'Genética',
                alumnos: 18,
                examenes_activos: 1,
                icono: 'flask',
                color: 'success'
            }
        ];
        
        // Generar HTML
        let html = '';
        
        cursos.forEach(curso => {
            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="fa-stack fa-1x me-2">
                                <i class="fas fa-circle fa-stack-2x text-${curso.color}"></i>
                                <i class="fas fa-${curso.icono} fa-stack-1x fa-inverse"></i>
                            </span>
                            <div>
                                <h6 class="mb-0">${curso.nombre}</h6>
                                <small class="text-muted">Curso ${curso.curso}</small>
                            </div>
                        </div>
                    </td>
                    <td>${curso.modulo}</td>
                    <td><span class="badge bg-info">${curso.alumnos} alumnos</span></td>
                    <td><span class="badge ${curso.examenes_activos > 2 ? 'bg-success' : 'bg-warning text-dark'}">${curso.examenes_activos} ${curso.examenes_activos === 1 ? 'activo' : 'activos'}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="#" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver curso">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Crear examen">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="#" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Gestionar alumnos">
                                <i class="fas fa-users"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tablaCursos.innerHTML = html;
        
        // Reinicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }, 800);
}

/**
 * Carga la tabla de exámenes recientes
 */
function cargarTablaExamenes() {
    // En un sistema real, aquí habría una llamada AJAX
    // Por ahora simulamos con setTimeout
    setTimeout(() => {
        const tablaExamenes = document.getElementById('tabla-examenes-recientes');
        if (!tablaExamenes) return;
        
        // Datos de ejemplo (en producción vendrían del servidor)
        const examenes = [
            {
                id: 1,
                titulo: 'Ecuaciones de segundo grado',
                curso: 'Matemáticas 3º ESO',
                modulo: 'Álgebra',
                fecha: '15/06/2025',
                estado: 'activo'
            },
            {
                id: 2,
                titulo: 'Teoría de la evolución',
                curso: 'Biología 4º ESO',
                modulo: 'Genética',
                fecha: '10/06/2025',
                estado: 'por_corregir'
            },
            {
                id: 3,
                titulo: 'Problemas con fracciones',
                curso: 'Matemáticas 3º ESO',
                modulo: 'Álgebra',
                fecha: '01/06/2025',
                estado: 'finalizado'
            }
        ];
        
        // Mapeo de estados a estilos visuales
        const estadosUI = {
            'activo': { clase: 'bg-success', texto: 'Activo', acciones: ['ver', 'editar', 'cerrar'] },
            'por_corregir': { clase: 'bg-warning text-dark', texto: 'Por corregir', acciones: ['ver', 'corregir'] },
            'finalizado': { clase: 'bg-secondary', texto: 'Finalizado', acciones: ['ver', 'duplicar'] }
        };
        
        // Generar HTML
        let html = '';
        
        examenes.forEach(examen => {
            // Obtener configuración de UI para este estado
            const estadoUI = estadosUI[examen.estado] || { clase: 'bg-secondary', texto: 'Desconocido', acciones: ['ver'] };
            
            // Generar botones de acciones
            let botonesAccion = '';
            
            if (estadoUI.acciones.includes('ver')) {
                botonesAccion += `
                    <a href="#" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Ver examen">
                        <i class="fas fa-eye"></i>
                    </a>
                `;
            }
            
            if (estadoUI.acciones.includes('editar')) {
                botonesAccion += `
                    <a href="#" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                `;
            }
            
            if (estadoUI.acciones.includes('corregir')) {
                botonesAccion += `
                    <a href="#" class="btn btn-outline-info" data-bs-toggle="tooltip" title="Corregir">
                        <i class="fas fa-check"></i>
                    </a>
                `;
            }
            
            if (estadoUI.acciones.includes('cerrar')) {
                botonesAccion += `
                    <a href="#" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="Cerrar">
                        <i class="fas fa-lock"></i>
                    </a>
                `;
            }
            
            if (estadoUI.acciones.includes('duplicar')) {
                botonesAccion += `
                    <a href="#" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Duplicar">
                        <i class="fas fa-copy"></i>
                    </a>
                `;
            }
            
            html += `
                <tr>
                    <td>
                        <div>
                            <h6 class="mb-0">${examen.titulo}</h6>
                            <small class="text-muted">${examen.curso}</small>
                        </div>
                    </td>
                    <td>${examen.modulo}</td>
                    <td>${examen.fecha}</td>
                    <td><span class="badge ${estadoUI.clase}">${estadoUI.texto}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${botonesAccion}
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tablaExamenes.innerHTML = html;
        
        // Reinicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Actualizar contadores
        document.getElementById('total-examenes-creados').innerText = '12';
        document.getElementById('total-examenes-pendientes').innerText = '5';
        document.getElementById('promedio-notas').innerText = '7.3';
    }, 800);
}

/**
 * Inicializa los gráficos estadísticos
 */
function initEstadisticas() {
    const ctx = document.getElementById('grafico-estadisticas-profesor');
    if (!ctx) return;
    
    // Crear gráfico con Chart.js
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Corregidos', 'Activos', 'Por corregir'],
            datasets: [{
                data: [7, 3, 5],
                backgroundColor: [
                    'rgba(52, 168, 83, 0.7)',  // Verde
                    'rgba(66, 133, 244, 0.7)', // Azul
                    'rgba(251, 188, 5, 0.7)'   // Amarillo
                ],
                borderColor: [
                    'rgba(52, 168, 83, 1)',
                    'rgba(66, 133, 244, 1)',
                    'rgba(251, 188, 5, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            },
            cutout: '60%'
        }
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
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `;
    
    // Simular recarga (en producción sería una llamada AJAX)
    setTimeout(() => {
        // Invocar función apropiada según el componente
        switch(componenteId) {
            case 'calendario-profesor':
                initCalendario();
                break;
            case 'lista-notificaciones-profesor':
                cargarNotificaciones();
                break;
            case 'tabla-cursos-profesor':
                cargarTablaCursos();
                break;
            case 'tabla-examenes-recientes':
                cargarTablaExamenes();
                break;
            case 'grafico-estadisticas-profesor':
                initEstadisticas();
                break;
            default:
                // Intentar recargar la página si no hay un manejador específico
                location.reload();
        }
    }, 1000);
}
