# Estructura de Base de Datos - AUTOEXAM2

**Archivo:** `67_estructura_base_datos.md`  
**Ubicación:** `/documentacion/09_configuracion_mantenimiento/`  
**Fecha:** 15 de junio de 2025  
**Versión:** 1.0

---

## Descripción General

AUTOEXAM2 implementa una **estructura organizacional mejorada** para la gestión de scripts de base de datos, migraciones y datos iniciales, siguiendo convenciones en español y mejores prácticas de desarrollo.

## Nueva Estructura `/base_datos/`

### Organización Principal
```
📂 base_datos/
├── 📁 migraciones/              # Scripts de esquema y actualizaciones
│   └── 001_esquema_completo.sql
├── 📁 mantenimiento/            # Scripts de limpieza y mantenimiento
│   ├── vaciar_todas_tablas.sql
│   └── eliminar_todas_tablas.sql
├── 📁 datos_iniciales/          # Datos básicos del sistema
│   └── admin_y_configuracion.sql
├── 📁 respaldos/                # Carpeta para copias de seguridad
└── README.md                    # Documentación de la estructura
```

## Descripción de Directorios

### 📁 **migraciones/**
**Propósito:** Scripts de creación y actualización del esquema de base de datos.

**Contenido:**
- `001_esquema_completo.sql` - Esquema completo con las 17 tablas del sistema

**Funcionalidad:**
- Creación inicial de la base de datos
- Actualizaciones incrementales del esquema
- Control de versiones de estructura

### 📁 **mantenimiento/**
**Propósito:** Scripts para mantenimiento y limpieza de la base de datos.

**Contenido:**
- `vaciar_todas_tablas.sql` - Limpia contenido manteniendo estructura
- `eliminar_todas_tablas.sql` - Elimina completamente todas las tablas

**Funcionalidad:**
- Limpieza de datos de desarrollo
- Reinicio completo del sistema
- Mantenimiento preventivo

### 📁 **datos_iniciales/**
**Propósito:** Scripts con datos básicos necesarios para el funcionamiento del sistema.

**Contenido:**
- `admin_y_configuracion.sql` - Usuario administrador y configuración inicial

**Funcionalidad:**
- Configuración inicial post-instalación
- Datos de ejemplo para desarrollo
- Configuraciones por defecto

### 📁 **respaldos/**
**Propósito:** Almacenamiento de copias de seguridad de la base de datos.

**Funcionalidad:**
- Backups automáticos
- Puntos de restauración
- Archivos históricos

## Tablas del Sistema (17 total)

### Autenticación y Usuarios
1. `usuarios` - Datos principales de usuarios
2. `tokens_recuperacion` - Tokens para recuperación de contraseña
3. `intentos_login` - Registro de intentos de acceso
4. `sesiones_activas` - Control de sesiones abiertas

### Gestión Académica
5. `instituciones` - Centros educativos
6. `cursos` - Cursos disponibles
7. `modulos` - Módulos de cursos
8. `examenes` - Exámenes del sistema
9. `preguntas` - Preguntas de exámenes
10. `opciones_respuesta` - Opciones de preguntas múltiples
11. `respuestas_usuario` - Respuestas de estudiantes
12. `resultados_examen` - Resultados finales

### Sistema y Configuración
13. `configuracion_sistema` - Configuración global
14. `registro_actividad` - Log de actividades
15. `permisos` - Permisos del sistema
16. `roles` - Roles de usuario
17. `usuario_roles` - Asignación de roles

## Migración desde Estructura Anterior

### Ubicación Anterior
```
📂 documentacion/00_sql/
├── autoexam2.sql
├── eliminar_base_autoexam2.sql
└── vaciar_tablas_autoexam2.sql
```

### Correspondencia de Archivos
- `autoexam2.sql` → `migraciones/001_esquema_completo.sql`
- `vaciar_tablas_autoexam2.sql` → `mantenimiento/vaciar_todas_tablas.sql`
- `eliminar_base_autoexam2.sql` → `mantenimiento/eliminar_todas_tablas.sql`

### Estado de Archivos Originales
- ✅ **Conservados** en `/documentacion/00_sql/` como respaldo
- ✅ **Copiados** a la nueva estructura `/base_datos/`
- ✅ **Actualizados** con las nuevas tablas del sistema

## Integración con Instalador

### Scripts del Instalador Actualizados
- `publico/instalador/funciones_tablas.php` - Lista completa de 17 tablas
- `publico/instalador/actualizar_tablas.php` - Gestión de migraciones
- `publico/instalador/instalacion_completa.php` - Referencias a nueva estructura

### Funcionalidades del Instalador
- ✅ Creación completa de esquema
- ✅ Actualización incremental
- ✅ Vaciado de datos de desarrollo
- ✅ Eliminación completa para reinstalación

## Ventajas de la Nueva Estructura

### 🎯 **Organización Mejorada**
- Separación clara por tipo de función
- Nombres descriptivos en español
- Estructura escalable para futuras necesidades

### 🔧 **Mantenimiento Simplificado**
- Scripts específicos para cada tarea
- Documentación integrada
- Mejor control de versiones

### 🚀 **Desarrollo Eficiente**
- Migraciones organizadas cronológicamente
- Datos iniciales separados del esquema
- Herramientas de limpieza específicas

### 🛡️ **Seguridad y Respaldos**
- Separación de respaldos
- Scripts de mantenimiento seguros
- Preservación de datos críticos

## Uso de la Estructura

### Instalación Nueva
```bash
# 1. Crear esquema completo
mysql < base_datos/migraciones/001_esquema_completo.sql

# 2. Insertar datos iniciales
mysql < base_datos/datos_iniciales/admin_y_configuracion.sql
```

### Desarrollo y Testing
```bash
# Limpiar datos manteniendo estructura
mysql < base_datos/mantenimiento/vaciar_todas_tablas.sql

# Eliminar todo para reinstalación
mysql < base_datos/mantenimiento/eliminar_todas_tablas.sql
```

### Respaldos
```bash
# Crear respaldo
mysqldump database > base_datos/respaldos/backup_$(date +%Y%m%d_%H%M%S).sql
```

## Mantenimiento y Actualizaciones

### Añadir Nuevas Migraciones
1. Crear archivo numerado: `002_nueva_funcionalidad.sql`
2. Documentar cambios en el archivo
3. Actualizar lista en instalador
4. Probar en entorno de desarrollo

### Actualizar Datos Iniciales
1. Modificar archivos en `datos_iniciales/`
2. Verificar compatibilidad con esquema actual
3. Probar instalación limpia

### Gestión de Respaldos
1. Programar respaldos automáticos
2. Rotar archivos antiguos
3. Verificar integridad periódicamente

## Documentación Relacionada

### Referencias del Sistema
- [Sistema de Diagnóstico](66_sistema_diagnostico.md) - Scripts de verificación
- [Herramientas Administrativas](herramientas_administrativas.md) - Gestión
- [Variables de Entorno](variables_entorno.md) - Configuración DB

### Scripts de Instalación
- [Instalador del Sistema](../01_estructura_presentacion/03_instalador.md)
- Documentación en `/publico/instalador/README.md`

## Historial de Cambios

### Versión 1.0 (15 de junio de 2025)
- ✅ Creación de la estructura `/base_datos/`
- ✅ Migración de archivos desde `/documentacion/00_sql/`
- ✅ Actualización completa del instalador
- ✅ Documentación de 17 tablas del sistema
- ✅ Implementación de nomenclatura en español

---

## Notas Técnicas

### Convenciones de Nomenclatura
- Directorios en plural (migraciones, respaldos)
- Archivos descriptivos con guiones bajos
- Numeración secuencial para migraciones

### Consideraciones de Rendimiento
- Scripts optimizados para ejecución rápida
- Índices definidos en migraciones
- Constraints de integridad incluidos

### Compatibilidad
- MySQL 5.7+
- MariaDB 10.2+
- Codificación UTF-8 completa

---

**Archivo README.md de referencia:** Ver `/base_datos/README.md` para información específica sobre el uso de cada directorio y archivo.
