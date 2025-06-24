/**
 * API JavaScript para Dashboard de Profesor - AUTOEXAM2
 * 
 * Gestiona la obtención de datos reales para el dashboard del profesor
 * 
 * @author GitHub Copilot
 * @version 1.0
 */

class ProfesorDashboardAPI {
    constructor() {
        this.baseUrl = window.location.origin;
        this.init();
    }

    /**
     * Inicializar dashboard con datos reales
     */
    async init() {
        try {
            // Verificar si los datos ya están cargados desde PHP
            const tablaCursos = document.getElementById('tabla-cursos-profesor');
            const tablaExamenes = document.getElementById('tabla-examenes-recientes');
            
            // Solo cargar via API si no hay datos iniciales (solo spinners)
            const cursosNecesitaAPI = tablaCursos && tablaCursos.querySelector('.spinner-border');
            const examenesNecesitaAPI = tablaExamenes && tablaExamenes.querySelector('.spinner-border');
            
            const promesas = [];
            
            if (cursosNecesitaAPI) {
                promesas.push(this.cargarCursosReales());
            }
            
            if (examenesNecesitaAPI) {
                promesas.push(this.cargarExamenesReales());
            }
            
            // Estas siempre se pueden actualizar dinámicamente
            promesas.push(this.cargarCalendarioReales());
            
            // Solo cargar notificaciones si hay spinner
            const notificaciones = document.getElementById('lista-notificaciones-profesor');
            if (notificaciones && notificaciones.querySelector('.spinner-border')) {
                promesas.push(this.cargarNotificacionesReales());
            }
            
            await Promise.all(promesas);
        } catch (error) {
            console.error('Error al inicializar dashboard:', error);
            // No mostrar error general si los datos básicos ya están cargados
        }
    }

    /**
     * Cargar cursos reales del profesor
     */
    async cargarCursosReales() {
        try {
            const response = await fetch(`${this.baseUrl}/publico/api/profesor/index.php?ruta=cursos`);
            if (!response.ok) {
                throw new Error('Error al obtener cursos');
            }
            
            const cursos = await response.json();
            this.mostrarCursosEnTabla(cursos);
        } catch (error) {
            console.error('Error al cargar cursos:', error);
            this.mostrarCursosVacios();
        }
    }

    /**
     * Cargar exámenes reales del profesor
     */
    async cargarExamenesReales() {
        try {
            const response = await fetch(`${this.baseUrl}/publico/api/profesor/index.php?ruta=examenes`);
            if (!response.ok) {
                throw new Error('Error al obtener exámenes');
            }
            
            const examenes = await response.json();
            this.mostrarExamenesEnTabla(examenes);
        } catch (error) {
            console.error('Error al cargar exámenes:', error);
            this.mostrarExamenesVacios();
        }
    }

    /**
     * Cargar estadísticas reales del profesor
     */
    async cargarEstadisticasReales() {
        try {
            const response = await fetch(`${this.baseUrl}/publico/api/profesor/index.php?ruta=estadisticas`);
            if (!response.ok) {
                throw new Error('Error al obtener estadísticas');
            }
            
            const estadisticas = await response.json();
            this.actualizarContadores(estadisticas);
            this.actualizarGrafico(estadisticas);
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
            this.mostrarEstadisticasVacias();
        }
    }

    /**
     * Cargar notificaciones reales del profesor
     */
    async cargarNotificacionesReales() {
        try {
            const response = await fetch(`${this.baseUrl}/publico/api/profesor/index.php?ruta=notificaciones`);
            if (!response.ok) {
                throw new Error('Error al obtener notificaciones');
            }
            
            const notificaciones = await response.json();
            this.mostrarNotificaciones(notificaciones);
        } catch (error) {
            console.error('Error al cargar notificaciones:', error);
            this.mostrarNotificacionesVacias();
        }
    }

    /**
     * Cargar eventos reales del calendario
     */
    async cargarCalendarioReales() {
        try {
            const response = await fetch(`${this.baseUrl}/publico/api/profesor/index.php?ruta=calendario`);
            if (!response.ok) {
                throw new Error('Error al obtener eventos del calendario');
            }
            
            const eventos = await response.json();
            this.actualizarCalendario(eventos);
        } catch (error) {
            console.error('Error al cargar calendario:', error);
            this.mostrarCalendarioVacio();
        }
    }

    /**
     * Mostrar cursos en la tabla
     */
    mostrarCursosEnTabla(cursos) {
        const tablaCursos = document.getElementById('tabla-cursos-profesor');
        if (!tablaCursos) return;

        if (cursos.length === 0) {
            tablaCursos.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-3 text-muted">
                        <i class="fas fa-book-open fa-2x mb-2"></i>
                        <p class="mb-0">No tienes cursos asignados actualmente</p>
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        cursos.forEach(curso => {
            html += `
                <tr>
                    <td><strong>${this.escapeHtml(curso.nombre_curso)}</strong></td>
                    <td>${this.escapeHtml(curso.nombre_modulo || 'Sin módulo')}</td>
                    <td><span class="badge" data-estado="activo">${curso.num_alumnos || 0} alumnos</span></td>
                    <td><span class="badge" data-estado="pendiente">${curso.examenes_activos || 0} activos</span></td>
                    <td>
                        <a href="${this.baseUrl}/cursos/ver/${curso.id_curso}" class="btn btn-sm">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="${this.baseUrl}/cursos/editar/${curso.id_curso}" class="btn btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="${this.baseUrl}/examenes/crear?curso=${curso.id_curso}" class="btn btn-sm">
                            <i class="fas fa-plus"></i> Examen
                        </a>
                    </td>
                </tr>`;
        });
        
        tablaCursos.innerHTML = html;
        this.aplicarEstilosUnificados();
    }

    /**
     * Mostrar exámenes en la tabla
     */
    mostrarExamenesEnTabla(examenes) {
        const tablaExamenes = document.getElementById('tabla-examenes-recientes');
        if (!tablaExamenes) return;

        if (examenes.length === 0) {
            tablaExamenes.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-3 text-muted">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <p class="mb-0">No tienes exámenes creados aún</p>
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        examenes.slice(0, 5).forEach(examen => { // Mostrar solo los 5 más recientes
            const fechaFormateada = this.formatearFecha(examen.fecha_inicio);
            const estado = this.determinarEstadoExamen(examen);
            
            html += `
                <tr>
                    <td><strong>${this.escapeHtml(examen.titulo)}</strong></td>
                    <td>${this.escapeHtml(examen.nombre_modulo || 'Sin módulo')}</td>
                    <td>${fechaFormateada}</td>
                    <td><span class="badge" data-estado="${estado.clase}">${estado.texto}</span></td>
                    <td>
                        <a href="${this.baseUrl}/examenes/ver/${examen.id_examen}" class="btn btn-sm">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <a href="${this.baseUrl}/examenes/editar/${examen.id_examen}" class="btn btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="${this.baseUrl}/examenes/exportar/${examen.id_examen}" class="btn btn-sm">
                            <i class="fas fa-download"></i> Exportar
                        </a>
                    </td>
                </tr>`;
        });
        
        tablaExamenes.innerHTML = html;
        this.aplicarEstilosUnificados();
    }

    /**
     * Actualizar contadores de estadísticas
     */
    actualizarContadores(estadisticas) {
        const elementos = {
            'total-examenes-creados': estadisticas.total_examenes || 0,
            'total-examenes-pendientes': estadisticas.examenes_pendientes || 0,
            'promedio-notas': (estadisticas.promedio_notas || 0).toFixed(1)
        };

        Object.entries(elementos).forEach(([id, valor]) => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = valor;
            }
        });
    }

    /**
     * Actualizar gráfico de estadísticas
     */
    actualizarGrafico(estadisticas) {
        const ctx = document.getElementById('grafico-estadisticas-profesor');
        if (!ctx) return;

        const datos = [
            estadisticas.examenes_completados || 0,
            estadisticas.examenes_activos || 0,
            estadisticas.examenes_pendientes || 0
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completados', 'Activos', 'Pendientes'],
                datasets: [{
                    data: datos,
                    backgroundColor: ['#34A853', '#4285F4', '#FBBC05'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Mostrar notificaciones
     */
    mostrarNotificaciones(notificaciones) {
        const lista = document.getElementById('lista-notificaciones-profesor');
        if (!lista) return;

        if (notificaciones.length === 0) {
            lista.innerHTML = `
                <div class="list-group-item text-center p-3 text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-2"></i>
                    <p class="mb-0">No tienes notificaciones nuevas</p>
                </div>`;
            return;
        }

        let html = '';
        notificaciones.slice(0, 3).forEach(notif => {
            const fecha = this.formatearFechaRelativa(notif.fecha);
            const tipo = this.determinarTipoNotificacion(notif.tipo);
            
            html += `
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${this.escapeHtml(notif.titulo)}</h6>
                        <small class="text-muted">${fecha}</small>
                    </div>
                    <p class="mb-1 small">${this.escapeHtml(notif.mensaje)}</p>
                    <small><span class="badge rounded-pill ${tipo.clase}">
                        <i class="${tipo.icono}"></i> ${tipo.texto}
                    </span></small>
                </a>`;
        });
        
        lista.innerHTML = html;
        this.aplicarEstilosUnificados();
    }

    /**
     * Funciones auxiliares
     */
    escapeHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    formatearFecha(fecha) {
        return new Date(fecha).toLocaleDateString('es-ES');
    }

    formatearFechaRelativa(fecha) {
        const ahora = new Date();
        const fechaObj = new Date(fecha);
        const diff = ahora - fechaObj;
        const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (dias === 0) return 'Hoy';
        if (dias === 1) return 'Ayer';
        return `Hace ${dias} días`;
    }

    determinarEstadoExamen(examen) {
        const ahora = new Date();
        const inicio = new Date(examen.fecha_inicio);
        const fin = new Date(examen.fecha_fin);
        
        if (examen.activo == 1 && inicio <= ahora && fin >= ahora) {
            return { clase: 'activo', texto: 'Activo' };
        } else if (examen.activo == 1 && inicio > ahora) {
            return { clase: 'pendiente', texto: 'Pendiente' };
        } else {
            return { clase: 'completado', texto: 'Completado' };
        }
    }

    determinarTipoNotificacion(tipo) {
        switch (tipo) {
            case 'urgente':
                return { 
                    clase: 'bg-danger-subtle text-danger border border-danger-subtle',
                    icono: 'fas fa-exclamation-circle',
                    texto: 'Prioritario'
                };
            case 'recordatorio':
                return { 
                    clase: 'bg-warning-subtle text-warning border border-warning-subtle',
                    icono: 'fas fa-clock',
                    texto: 'Recordatorio'
                };
            default:
                return { 
                    clase: 'bg-info-subtle text-info border border-info-subtle',
                    icono: 'fas fa-info-circle',
                    texto: 'Información'
                };
        }
    }

    aplicarEstilosUnificados() {
        // Implementar estilos unificados como en el código original
        // Esta función se puede expandir según sea necesario
    }

    /**
     * Funciones de respaldo para casos de error
     */
    mostrarCursosVacios() {
        const tablaCursos = document.getElementById('tabla-cursos-profesor');
        if (tablaCursos) {
            tablaCursos.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-3 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p class="mb-0">Error al cargar cursos</p>
                    </td>
                </tr>`;
        }
    }

    mostrarExamenesVacios() {
        const tablaExamenes = document.getElementById('tabla-examenes-recientes');
        if (tablaExamenes) {
            tablaExamenes.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center p-3 text-muted">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p class="mb-0">Error al cargar exámenes</p>
                    </td>
                </tr>`;
        }
    }

    mostrarEstadisticasVacias() {
        ['total-examenes-creados', 'total-examenes-pendientes', 'promedio-notas'].forEach(id => {
            const elemento = document.getElementById(id);
            if (elemento) elemento.textContent = '--';
        });
    }

    mostrarNotificacionesVacias() {
        const lista = document.getElementById('lista-notificaciones-profesor');
        if (lista) {
            lista.innerHTML = `
                <div class="list-group-item text-center p-3 text-muted">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p class="mb-0">Error al cargar notificaciones</p>
                </div>`;
        }
    }

    mostrarCalendarioVacio() {
        // Implementar cuando se tenga el calendario funcional
    }

    actualizarCalendario(eventos) {
        // Implementar cuando se tenga el calendario funcional
    }

    mostrarErrorGeneral() {
        console.error('Error general en el dashboard del profesor');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    new ProfesorDashboardAPI();
});
