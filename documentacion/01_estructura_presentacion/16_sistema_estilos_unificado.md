# 16 - Sistema de Estilos Unificado por Rol

**Implementado y funcional** ✅  
**Ubicación:** `publico/recursos/css/`  
**Arquitectura:** Estilos específicos por rol con componentes compartidos

---

## 🎯 Objetivos del sistema

- Proporcionar una apariencia visual consistente en toda la aplicación
- Separar estilos por rol (admin, profesor, alumno) para experiencias personalizadas
- Optimizar rendimiento mediante la consolidación de recursos CSS
- Facilitar el mantenimiento centralizado de componentes visuales comunes
- Implementar estilos específicos para elementos funcionales como calendarios

---

## 🧱 Arquitectura de Estilos

### Estructura de Archivos
```
publico/recursos/css/
├── admin.css         # Estilos completos para administradores
├── profesor.css      # Estilos completos para profesores
├── alumno.css        # Estilos completos para alumnos
├── estilos.css       # Estilos generales compartidos
├── instalador.css    # Estilos específicos para el instalador
└── obsoletos/        # Archivos CSS obsoletos (para referencia)
```

### Organización Interna
Cada archivo CSS de rol contiene secciones bien definidas:

1. **Estilos específicos de rol** - Variables, colores y componentes propios del rol
2. **Estilos comunes integrados** - Componentes compartidos (badges, botones, etc.)
3. **Estilos de calendario integrados** - Visualización de calendarios específica por rol

---

## 🎨 Sistema de Colores por Rol

### Admin
- **Color principal:** `#4285F4` (azul Google)
- **Color secundario:** `#34A853` (verde)
- **Color peligro:** `#EA4335` (rojo)
- **Color advertencia:** `#FBBC05` (amarillo)

### Profesor
- **Color principal:** `#4285F4` (azul Google)
- **Color secundario:** `#5F6368` (gris oscuro)
- **Color éxito:** `#34A853` (verde)
- **Color peligro:** `#EA4335` (rojo)

### Alumno
- **Color principal:** `#8a5cd1` (morado)
- **Color secundario:** `#5F6368` (gris oscuro)
- **Color peligro:** `#D9534F` (rojo)
- **Color advertencia:** `#F0AD4E` (amarillo)

---

## 🧩 Componentes Visuales Unificados

### Badges con Significado Visual

```html
<span class="badge" data-rol="admin">Administrador</span>
<span class="badge" data-rol="profesor">Profesor</span>
<span class="badge" data-rol="alumno">Alumno</span>

<span class="badge" data-estado="activo">Activo</span>
<span class="badge" data-estado="inactivo">Inactivo</span>
<span class="badge" data-estado="pendiente">Pendiente</span>
```

### Botones de Acción con Iconos

```html
<button class="btn btn-accion"><i class="fas fa-edit"></i></button>
<button class="btn btn-accion"><i class="fas fa-eye"></i></button>
<button class="btn btn-accion"><i class="fas fa-trash"></i></button>
```

### Clases de Utilidad Específicas

```css
.bg-purple { background-color: #8a5cd1 !important; }
.text-purple { color: #8a5cd1 !important; }
.border-purple { border-color: #8a5cd1 !important; }
```

---

## 📱 Diseño Responsivo

El sistema de estilos implementa un enfoque responsivo completo:

- Basado en el sistema de rejilla de Bootstrap 5
- Optimizado para dispositivos móviles, tabletas y escritorio
- Breakpoints estándar de Bootstrap (xs, sm, md, lg, xl)
- Componentes que se adaptan automáticamente al tamaño de pantalla

---

## 🔄 Integración en Vistas

### Inclusion en Head

```php
<!-- Admin -->
<link rel="stylesheet" href="<?= BASE_URL ?>/recursos/css/admin.css?v=<?= time() ?>">

<!-- Profesor -->
<link href="<?= BASE_URL ?>/recursos/css/estilos.css?v=<?= time() ?>" rel="stylesheet">
<link href="<?= BASE_URL ?>/recursos/css/profesor.css?v=<?= time() ?>" rel="stylesheet">

<!-- Alumno -->
<link href="<?= BASE_URL ?>/recursos/css/estilos.css?v=<?= time() ?>" rel="stylesheet">
<link href="<?= BASE_URL ?>/recursos/css/alumno.css?v=<?= time() ?>" rel="stylesheet">
```

### Prevención de Caché

- Parámetro dinámico `?v=<?= time() ?>` para forzar recarga durante desarrollo
- En producción, se recomienda usar número de versión estático

---

## ✅ Estado de Implementación

### Completamente Implementado ✅
- [x] Estilos para administradores (admin.css)
- [x] Estilos para profesores (profesor.css)
- [x] Estilos para alumnos (alumno.css)
- [x] Sistema de badges por rol y estado
- [x] Sistema de botones de acción con iconos
- [x] Integración de estilos de calendario
- [x] Optimización de recursos (unificación)

### Pendiente de Mejora ⚠️
- [ ] Minificación de archivos CSS para producción
- [ ] Implementación de variables CSS para facilitar personalización
- [ ] Documentación interna de componentes
- [ ] Sistema de versiones para control de caché

---

## 🔄 Recursos Optimizados

Los archivos CSS integran varios recursos que anteriormente estaban separados:

1. **autoexam-common.css** → Integrado en cada archivo CSS por rol
2. **calendario-personalizado.css** → Integrado en cada archivo CSS por rol

Esta consolidación reduce el número de solicitudes HTTP y mejora el rendimiento de carga.

---

## 📝 Recomendaciones para Desarrollo

1. Mantener la consistencia en la paleta de colores por rol
2. Usar las clases de utilidad existentes antes de crear nuevas
3. Seguir el patrón data-* para badges y estados
4. Respetar la estructura de secciones en los archivos CSS
5. Documentar componentes nuevos o modificaciones importantes

---

📌 **Nota:** Este sistema está completamente implementado y funcional. La documentación refleja el estado actual del código y proporciona una guía para el desarrollo futuro.
