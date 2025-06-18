# 17 - Sistema de JavaScript Unificado

**Implementado y funcional** ✅  
**Ubicación:** `publico/recursos/js/`  
**Tipo:** Interfaz de Usuario

---

## 🎯 Objetivos del sistema

- Proporcionar una capa de JavaScript consistente en toda la aplicación
- Unificar la apariencia y comportamiento de elementos comunes de la UI
- Facilitar la transformación dinámica de elementos generados por el servidor
- Automatizar la aplicación de estilos a elementos como botones, badges y tablas
- Minimizar la duplicación de código JavaScript entre diferentes secciones

---

## 🧱 Arquitectura del Sistema

### Estructura de archivos
```
publico/recursos/js/
├── autoexam-ui.js          # Script principal de unificación de UI
├── admin_dashboard.js      # Scripts específicos del dashboard de admin
├── profesor_dashboard.js   # Scripts específicos del dashboard de profesor
└── alumno_dashboard.js     # Scripts específicos del dashboard de alumno
```

### Modelo de funcionamiento

El sistema JavaScript implementa un enfoque de "mejora progresiva" donde:

1. La interfaz base es generada por PHP con todas las funcionalidades esenciales
2. Los scripts JavaScript mejoran la experiencia añadiendo:
   - Transformaciones visuales (botones redondeados, badges con iconos, etc.)
   - Interactividad (confirmaciones, filtros dinámicos, etc.)
   - Actualización de elementos sin recargar la página

---

## 🎨 Funcionalidades de `autoexam-ui.js`

Este archivo proporciona funcionalidades generales para toda la aplicación:

### 1. Unificación de botones de acción

```javascript
// Aplicar estilo a botones de acción en tablas
document.querySelectorAll('.table tbody a.btn:not(.rounded-pill), .table tbody button.btn:not(.rounded-pill)').forEach(btn => {
    btn.classList.add('btn-light', 'rounded-pill', 'border', 'px-2', 'shadow-sm');
    btn.classList.remove('btn-primary', 'btn-success', 'btn-danger', 'btn-warning', 'btn-info');
    
    // Colorear iconos dentro de los botones según su función
    const icon = btn.querySelector('i.fas, i.far, i.fab');
    if (icon) {
        // Detectar el tipo de acción basado en clases o texto del botón
        if (btn.innerHTML.includes('Editar') || icon.classList.contains('fa-edit')) {
            icon.classList.add('text-primary');
        } else if (btn.innerHTML.includes('Ver') || icon.classList.contains('fa-eye')) {
            icon.classList.add('text-info');
        } // otros tipos...
    }
});
```

### 2. Transformación de badges

```javascript
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
        // formato para profesores...
    } else if (rol === 'alumno') {
        // formato para alumnos...
    }
});
```

### 3. Observación del DOM para cambios dinámicos

```javascript
// Volver a aplicar estilos después de cambios dinámicos en el DOM
const observer = new MutationObserver(function(mutations) {
    unificarEstilosUI();
});

observer.observe(document.body, { 
    childList: true, 
    subtree: true 
});
```

---

## 💻 Scripts específicos por rol

Cada rol tiene scripts específicos para sus necesidades:

### `admin_dashboard.js`

- Gestión de tablas de datos de usuarios
- Filtros avanzados en listados
- Visualización de estadísticas
- Inicialización del calendario de administración

### `profesor_dashboard.js`

- Manejo de listas de exámenes y calificaciones
- Herramientas para el banco de preguntas
- Inicialización del calendario de profesor
- Gestión de notificaciones específicas

### `alumno_dashboard.js`

- Visualización de exámenes pendientes
- Interfaz de realización de exámenes
- Inicialización del calendario de alumno
- Visualización de calificaciones

---

## 🔄 Integración con el sistema de estilos

Los scripts JavaScript trabajan coordinadamente con el sistema de estilos CSS:

1. Los scripts detectan clases y atributos de datos para determinar el comportamiento adecuado
2. Se utilizan atributos `data-*` para proporcionar metadatos desde el servidor:
   - `data-rol` - Indica el rol del usuario para formateo de badges
   - `data-estado` - Estado para aplicar los colores correctos
   - `data-accion` - Tipo de acción para formatear botones

---

## ⚙️ Optimización del Rendimiento

El sistema implementa varias técnicas para optimizar el rendimiento:

1. **Ejecución diferida**: Los scripts se cargan con el atributo `defer`
2. **Delegación de eventos**: Los eventos se delegan al nivel más alto posible
3. **Observación selectiva**: MutationObserver se configura para minimizar el impacto
4. **Carga condicional**: Los scripts específicos solo se cargan cuando son necesarios

---

## 💻 Uso para Desarrolladores

Para utilizar el sistema de JavaScript en nuevos componentes:

1. **Elementos estandarizados**:
   - Para badges de rol: `<span class="badge" data-rol="admin">Administrador</span>`
   - Para badges de estado: `<span class="badge" data-estado="activo">Activo</span>`

2. **Botones de acción**:
   - Utilizar clases de Bootstrap: `<a href="#" class="btn"><i class="fas fa-edit"></i> Editar</a>`
   - Los estilos serán aplicados automáticamente por `autoexam-ui.js`

3. **Componentes dinámicos**:
   - Después de insertar nuevo contenido mediante AJAX, no es necesario llamar a ninguna función
   - El MutationObserver aplicará los estilos automáticamente
