# 71 - Recomendaciones para Minificación de Recursos

**Implementación pendiente** ⚠️  
**Ubicación objetivo:** `publico/recursos/minimizados/`  
**Tipo:** Optimización y Rendimiento

---

## 🎯 Objetivos del sistema

- Reducir significativamente el tamaño de los recursos estáticos para mejorar la velocidad de carga
- Eliminar código innecesario (comentarios, espacios en blanco) de archivos CSS y JS
- Implementar un sistema automatizado para generar versiones minificadas
- Mantener la legibilidad de los archivos originales para desarrollo
- Reducir el número de peticiones HTTP mediante la consolidación de archivos

---

## 🧱 Arquitectura propuesta

### Estructura de directorios
```
publico/recursos/
├── css/                     # Archivos CSS originales (desarrollo)
├── js/                      # Archivos JS originales (desarrollo)
└── minimizados/             # Versiones minificadas (producción)
    ├── css/
    │   ├── admin.min.css    # Versión minificada del CSS de admin
    │   ├── profesor.min.css # Versión minificada del CSS de profesor
    │   └── alumno.min.css   # Versión minificada del CSS de alumno
    └── js/
        ├── admin.min.js     # Versión minificada consolidada para admin
        ├── profesor.min.js  # Versión minificada consolidada para profesor
        └── alumno.min.js    # Versión minificada consolidada para alumno
```

---

## 🔧 Herramientas recomendadas

Para implementar el proceso de minificación, se recomiendan estas herramientas:

1. **UglifyJS** - Para minificación de JavaScript
   - Elimina espacios y comentarios
   - Renombra variables locales
   - Optimiza expresiones

2. **Clean-CSS** - Para minificación de CSS
   - Comprime los estilos
   - Combina selectores redundantes
   - Simplifica valores y unidades

3. **Gulp/Grunt** - Para automatización
   - Gestiona el proceso de build
   - Permite watch mode durante desarrollo
   - Integra múltiples herramientas

---

## 📋 Proceso de implementación recomendado

### 1. Instalación de herramientas

```bash
# Si no está instalado Node.js, instalarlo primero

# Instalar Gulp globalmente
npm install --global gulp-cli

# Inicializar proyecto Node en la raíz
cd /raiz/proyecto
npm init

# Instalar dependencias para minificación
npm install --save-dev gulp gulp-uglify gulp-clean-css gulp-rename gulp-concat
```

### 2. Configuración de Gulp

Crear archivo `gulpfile.js` en la raíz del proyecto:

```javascript
const gulp = require('gulp');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const concat = require('gulp-concat');

// Minificar CSS por rol
gulp.task('css-admin', () => {
  return gulp.src('publico/recursos/css/admin.css')
    .pipe(cleanCSS())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('publico/recursos/minimizados/css/'));
});

// Crear tareas similares para profesor.css y alumno.css

// Consolidar y minificar JS específico para admin
gulp.task('js-admin', () => {
  return gulp.src([
    'publico/recursos/js/autoexam-ui.js',
    'publico/recursos/js/admin_dashboard.js'
  ])
  .pipe(concat('admin.min.js'))
  .pipe(uglify())
  .pipe(gulp.dest('publico/recursos/minimizados/js/'));
});

// Crear tareas similares para profesor y alumno

// Tarea por defecto que ejecuta todas las minificaciones
gulp.task('default', gulp.parallel(
  'css-admin', 'css-profesor', 'css-alumno',
  'js-admin', 'js-profesor', 'js-alumno'
));
```

### 3. Modificación de las vistas para usar archivos minificados

```php
<?php
// En los archivos de cabecera (head_*.php)

// Determinar si estamos en producción
$en_produccion = !is_development_environment();

// CSS: usar versión minificada en producción o normal en desarrollo
if ($en_produccion) {
    echo '<link rel="stylesheet" href="' . BASE_URL . '/recursos/minimizados/css/admin.min.css?v=' . VERSION . '">';
} else {
    echo '<link rel="stylesheet" href="' . BASE_URL . '/recursos/css/admin.css?v=' . VERSION . '">';
}

// Similar para JS en los archivos de scripts
?>
```

---

## 💻 Uso para Desarrolladores

### Generar recursos minificados

```bash
# Generar todas las versiones minificadas
gulp

# Generar solo CSS minificado
gulp css-admin css-profesor css-alumno

# Generar solo JS minificado
gulp js-admin js-profesor js-alumno
```

### Modo desarrollo con actualización automática

```bash
# Iniciar modo watch para actualización automática
gulp watch
```

---

## 🔄 Estrategia de control de versiones

Para gestionar el control de caché en los navegadores, se recomiendan dos enfoques:

1. **Parámetro de versión dinámico**
   - Definir constante `VERSION` basada en fecha o hash
   - Agregar como parámetro de consulta: `?v=1.2.3`

2. **Nombres de archivos con hash**
   - Generar nombre basado en el contenido: `admin.a1b2c3.min.css`
   - Requiere generar un archivo de mapeo para referenciarlos

---

## ⚠️ Consideraciones importantes

1. **Debugging**: Los archivos minificados son difíciles de depurar, usar originales en desarrollo
2. **Source Maps**: Considerar generar source maps para facilitar debugging en producción
3. **Automatización**: Integrar el proceso de minificación en el despliegue
4. **Testing**: Verificar que todo funciona correctamente con las versiones minificadas
5. **Mantenimiento**: Actualizar recursos minificados después de cada cambio en los originales
