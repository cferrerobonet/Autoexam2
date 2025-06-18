# 20 - Variables CSS y Personalización Visual

**Implementación pendiente** ⚠️  
**Ubicación objetivo:** `publico/recursos/css/`  
**Tipo:** Interfaz de Usuario

---

## 🎯 Objetivos del sistema

- Implementar un sistema centralizado de variables CSS para toda la aplicación
- Facilitar la personalización visual de la interfaz sin modificar múltiples archivos
- Permitir la creación de temas o variaciones visuales coherentes
- Mejorar la mantenibilidad del código CSS
- Proporcionar una paleta de colores consistente por rol de usuario

---

## 🧱 Arquitectura propuesta

### Estructura de archivos
```
publico/recursos/css/
├── variables/
│   ├── colores.css         # Variables de colores generales y por rol
│   ├── tipografia.css      # Variables de fuentes y tamaños
│   ├── espaciado.css       # Variables de márgenes y padding
│   └── componentes.css     # Variables específicas para componentes
├── admin.css               # Importa variables + estilos específicos
├── profesor.css            # Importa variables + estilos específicos
└── alumno.css              # Importa variables + estilos específicos
```

### Implementación técnica

El sistema utilizará variables CSS nativas para garantizar el soporte en navegadores modernos:

```css
/* En colores.css */
:root {
  /* Colores primarios */
  --color-primario: #6200ee;
  --color-primario-light: #9d46ff;
  --color-primario-dark: #0a00b6;
  --color-primario-text: #ffffff;
  
  /* Colores de rol */
  --color-admin: #dc3545;
  --color-admin-light: #f8d7da;
  --color-admin-dark: #842029;
  
  --color-profesor: #0d6efd;
  --color-profesor-light: #cfe2ff;
  --color-profesor-dark: #084298;
  
  --color-alumno: #6a0dad;
  --color-alumno-light: #e2d4ef;
  --color-alumno-dark: #3b0764;
  
  /* Grises y neutrales */
  --color-gris-100: #f8f9fa;
  --color-gris-200: #e9ecef;
  --color-gris-300: #dee2e6;
  --color-gris-400: #ced4da;
  --color-gris-500: #adb5bd;
  --color-gris-600: #6c757d;
  --color-gris-700: #495057;
  --color-gris-800: #343a40;
  --color-gris-900: #212529;
}
```

---

## 🎨 Sistema de colores por rol

### Colores principales por rol

| Rol | Color principal | Color claro | Color oscuro |
|-----|----------------|-------------|-------------|
| Admin | #dc3545 (rojo) | #f8d7da | #842029 |
| Profesor | #0d6efd (azul) | #cfe2ff | #084298 |
| Alumno | #6a0dad (morado) | #e2d4ef | #3b0764 |

### Variables de estado y acción

```css
:root {
  /* Estados de acción */
  --color-exito: #198754;
  --color-exito-light: #d1e7dd;
  --color-exito-dark: #0f5132;
  
  --color-aviso: #ffc107;
  --color-aviso-light: #fff3cd;
  --color-aviso-dark: #664d03;
  
  --color-error: #dc3545;
  --color-error-light: #f8d7da;
  --color-error-dark: #842029;
  
  --color-info: #0dcaf0;
  --color-info-light: #cff4fc;
  --color-info-dark: #055160;
}
```

---

## 📝 Tipografía y espaciado

### Variables de tipografía

```css
:root {
  /* Tipografía */
  --fuente-principal: 'Roboto', 'Segoe UI', system-ui, -apple-system;
  --fuente-secundaria: 'Poppins', 'Segoe UI', system-ui, -apple-system;
  --fuente-monoespaciada: 'Roboto Mono', 'Consolas', monospace;
  
  /* Tamaños base */
  --texto-xs: 0.75rem;
  --texto-sm: 0.875rem;
  --texto-base: 1rem;
  --texto-lg: 1.125rem;
  --texto-xl: 1.25rem;
  --texto-2xl: 1.5rem;
  --texto-3xl: 1.875rem;
  --texto-4xl: 2.25rem;
}
```

### Variables de espaciado

```css
:root {
  /* Espaciado */
  --espaciado-0: 0;
  --espaciado-1: 0.25rem;
  --espaciado-2: 0.5rem;
  --espaciado-3: 0.75rem;
  --espaciado-4: 1rem;
  --espaciado-5: 1.25rem;
  --espaciado-6: 1.5rem;
  --espaciado-8: 2rem;
  --espaciado-10: 2.5rem;
  --espaciado-12: 3rem;
  --espaciado-16: 4rem;
}
```

---

## 🧩 Uso de variables en componentes

### Ejemplo para botones

```css
/* Variables para componentes */
:root {
  /* Botones */
  --boton-border-radius: 50px;
  --boton-padding-x: var(--espaciado-3);
  --boton-padding-y: var(--espaciado-2);
  --boton-font-size: var(--texto-base);
  --boton-icon-gap: var(--espaciado-2);
}

/* Aplicación en componentes */
.btn-rounded {
  border-radius: var(--boton-border-radius);
  padding: var(--boton-padding-y) var(--boton-padding-x);
  font-size: var(--boton-font-size);
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  gap: var(--boton-icon-gap);
}
```

---

## 💻 Uso para Desarrolladores

### Importación en archivos CSS

```css
/* En admin.css */
@import 'variables/colores.css';
@import 'variables/tipografia.css';
@import 'variables/espaciado.css';
@import 'variables/componentes.css';

/* Resto del CSS usando variables */
.badge-admin {
  background-color: var(--color-admin-light);
  color: var(--color-admin-dark);
  border: 1px solid var(--color-admin);
  padding: var(--espaciado-1) var(--espaciado-3);
  font-size: var(--texto-sm);
  border-radius: var(--boton-border-radius);
}
```

### Uso en JavaScript

```javascript
// Acceder a variables CSS desde JavaScript
const colorPrimario = getComputedStyle(document.documentElement)
  .getPropertyValue('--color-primario').trim();
  
// Modificar variables CSS dinámicamente
document.documentElement.style.setProperty('--color-primario', '#7400ee');
```

---

## 🔍 Personalización para instituciones

El sistema de variables facilita la personalización rápida para diferentes instituciones educativas:

1. **Crear archivo de sobrescritura**:
   ```css
   /* institucion-personalizada.css */
   :root {
     --color-primario: #004D99; /* Azul institucional */
     --color-profesor: #005580; /* Color personalizado para profesores */
     /* Otras variables personalizadas */
   }
   ```

2. **Importar después de las variables base**:
   ```php
   <!-- En el archivo head.php -->
   <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/css/variables/colores.css">
   <!-- Otras importaciones base -->
   <link rel="stylesheet" href="<?= BASE_URL ?>/recursos/css/personalizado/institucion-personalizada.css">
   ```

---

## 📋 Plan de implementación

1. **Fase 1: Extracción de variables**
   - Identificar valores repetidos en archivos CSS actuales
   - Crear archivos de variables separados por categoría
   - Establecer la paleta de colores principal

2. **Fase 2: Refactorización progresiva**
   - Adaptar componentes principales (botones, badges, tablas)
   - Actualizar archivos CSS específicos por rol
   - Documentar todas las variables disponibles

3. **Fase 3: Personalización avanzada**
   - Crear sistema de temas intercambiables
   - Implementar opciones de personalización en el panel de admin
   - Permitir guardar preferencias por usuario
