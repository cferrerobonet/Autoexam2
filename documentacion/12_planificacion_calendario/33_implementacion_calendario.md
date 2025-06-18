# 33 - Implementación de Calendarios con FullCalendar

**Implementado y funcional** ✅  
**Ubicación:** `app/vistas/comunes/calendario.php`, vistas dashboard  
**Biblioteca:** FullCalendar 5.11.3

---

## 🎯 Objetivos del sistema

- Visualizar eventos académicos (exámenes, entregas) en un formato de calendario intuitivo
- Proporcionar interfaces diferenciadas por rol con colores distintivos
- Garantizar excelente legibilidad y experiencia de usuario consistente
- Optimizar la visualización de eventos para todos los dispositivos
- Permitir navegación rápida entre vistas (mes, semana, lista)

---

## 🧱 Arquitectura del Sistema

### Estructura Principal
```
app/vistas/comunes/calendario.php     # Vista completa del calendario
app/controladores/calendario_controlador.php  # Controlador específico
publico/recursos/css/[rol].css        # Estilos integrados por rol
```

### Integración en Dashboard
- Dashboard Profesor: Vista mensual con altura fija (350px)
- Dashboard Alumno: Vista de lista semanal con altura fija (300px)
- Calendario Completo: Vista mensual con altura flexible (700px)

---

## 📅 Funcionalidades Implementadas

### Navegación Temporal
- Botones Anterior/Siguiente para navegar por periodos
- Botón "Hoy" para regresar a la fecha actual
- Título dinámico mostrando mes/año actual

### Vistas Disponibles
- **Vista Mensual**: Calendario completo con días del mes
- **Vista Semanal**: Horario detallado por horas (profesor/admin)
- **Vista Lista**: Lista compacta de eventos (alumno)

### Eventos Personalizados
- Título descriptivo del evento
- Colores específicos según tipo y rol
- Metadatos adicionales (curso, módulo, descripción)
- Enlaces para acceder directamente al evento

---

## 🎨 Esquema de Colores por Rol

### Admin
- **Eventos principales**: `#2c60d3` (azul oscuro)
- **Borde eventos**: `#1e4eb6`
- **Botón "Hoy"**: `#4285F4`

### Profesor
- **Eventos principales**: `#4285F4` (azul Google)
- **Borde eventos**: `#3266c2`
- **Botón "Hoy"**: `#4285F4`

### Alumno
- **Eventos principales**: `#7d50c4` (morado)
- **Borde eventos**: `#6e46a8`
- **Botón "Hoy"**: `#8a5cd1`

### Eventos Adicionales
- **Tipo verde**: `#34A853` (entrega, aprobado)
- **Tipo rojo**: `#EA4335` (urgente, calificación)

---

## 🖥️ Integración en Interfaz

### Clases CSS para Contenedores
```html
<!-- Para profesor -->
<div id="calendario-profesor" class="profesor-calendar"></div>

<!-- Para alumno -->
<div id="calendario-alumno" class="alumno-calendar"></div>

<!-- Calendario completo -->
<div id="calendario-completo" class="<?= $rolUsuario ?>-calendar"></div>
```

### Inicialización JavaScript
```js
const calendario = new FullCalendar.Calendar(calendarioEl, {
    initialView: 'dayGridMonth',
    locale: 'es',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
    },
    height: 350,
    events: [
        {
            title: 'Examen Matemáticas',
            start: '2025-06-20',
            backgroundColor: '#4285F4',
            borderColor: '#3266c2',
            textColor: '#ffffff'
        }
        // Más eventos...
    ]
});
```

---

## 📱 Responsive Design

### Adaptaciones Móviles
- Toolbar colapsable en pantallas pequeñas
- Vista de lista como predeterminada en móviles
- Tamaño de texto optimizado para pantallas táctiles
- Eventos con altura mínima para facilitar interacción

### Media Queries
```css
@media (max-width: 768px) {
  .fc .fc-toolbar {
    flex-direction: column;
    gap: 10px;
  }
  
  .fc .fc-toolbar-title {
    font-size: 1.2rem;
  }
}
```

---

## 🖌️ Mejoras Visuales Implementadas

### Eventos
- Fondos de color sólido para mejor contraste
- Texto en color blanco con sombra sutil
- Bordes de color más oscuro que el fondo
- Efectos hover suaves (brillo, elevación)

### Navegación
- Botones con estilo coherente con el rol
- Estados activos claramente diferenciados
- Día actual resaltado visualmente
- Tooltip con información adicional al pasar el cursor

---

## ✅ Estado de Implementación

### Completamente Implementado ✅
- [x] Inicialización de FullCalendar con opciones adecuadas
- [x] Implementación responsive
- [x] Estilos personalizados por rol
- [x] Vista de calendario completo
- [x] Integración en dashboards
- [x] Optimización visual de eventos
- [x] Navegación entre diferentes vistas

### Pendiente de Integración ⚠️
- [ ] Carga dinámica de eventos desde base de datos
- [ ] Filtros de eventos por tipo/estado
- [ ] Interacción completa con sistema de exámenes
- [ ] Notificaciones y recordatorios

---

## 📝 Recomendaciones para Desarrollo

1. Mantener coherencia visual con la paleta de colores establecida
2. Priorizar alto contraste para garantizar legibilidad
3. Integrar datos dinámicos con estructura de eventos ya definida
4. Preservar estructura responsive y adaptabilidad
5. Documentar cualquier nueva personalización de eventos

---

📌 **Nota:** El sistema de calendarios está implementado visualmente y requiere principalmente la integración con datos dinámicos de la base de datos para ser completamente funcional.
