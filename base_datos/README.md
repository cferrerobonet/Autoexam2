# Base de Datos - AUTOEXAM2

Esta carpeta contiene todos los archivos SQL necesarios para la gestión de la base de datos del sistema AUTOEXAM2.

## 📁 Estructura de Carpetas

### 🔄 `/migraciones/`
Contiene los scripts para crear y actualizar la estructura de la base de datos:
- `001_esquema_completo.sql` - Esquema completo con todas las tablas del sistema

### 🛠️ `/mantenimiento/`
Scripts para tareas de mantenimiento de la base de datos:
- `vaciar_todas_tablas.sql` - Elimina todos los datos pero conserva la estructura
- `eliminar_todas_tablas.sql` - Elimina completamente todas las tablas

### 📊 `/datos_iniciales/`
Scripts con datos básicos para el funcionamiento del sistema:
- `admin_y_configuracion.sql` - Usuario administrador y configuración inicial (pendiente)

### 💾 `/respaldos/`
Carpeta para almacenar copias de seguridad de la base de datos.

## 🗄️ Tablas del Sistema

El sistema AUTOEXAM2 utiliza **19 tablas principales**:

### Tablas de Usuarios y Autenticación
- `usuarios` - Gestión de usuarios (admin, profesores, alumnos)
- `tokens_recuperacion` - Tokens para recuperación de contraseñas
- `intentos_login` - Control de intentos de acceso (protección fuerza bruta)
- `sesiones_activas` - Control de sesiones de usuario

### Tablas de Gestión Académica
- `instituciones` - Centros educativos registrados
- `cursos` - Definición de cursos
- `modulos` - Módulos dentro de cada curso
- `examenes` - Exámenes y evaluaciones
- `preguntas` - Banco de preguntas para exámenes
- `opciones_respuesta` - Opciones para preguntas de selección múltiple
- `respuestas_usuario` - Respuestas enviadas por estudiantes
- `resultados_examen` - Resultados finales de evaluaciones

### Tablas de Sistema y Configuración
- `configuracion_sistema` - Configuración global del sistema
- `registro_actividad` - Log detallado de actividades
- `permisos` - Definición de permisos del sistema
- `roles` - Roles disponibles en la plataforma
- `usuario_roles` - Asignación de roles a usuarios
- `config_versiones` - Control de versiones de configuración
- `backups` - Registro de copias de seguridad realizadas

## 🚀 Uso de los Scripts

### Instalación Inicial
```sql
-- 1. Crear estructura completa
SOURCE migraciones/001_esquema_completo.sql;

-- 2. Insertar datos iniciales
SOURCE datos_iniciales/admin_y_configuracion.sql;
```

### Mantenimiento
```sql
-- Vaciar datos pero conservar estructura
SOURCE mantenimiento/vaciar_todas_tablas.sql;

-- Eliminar todo para reinstalación
SOURCE mantenimiento/eliminar_todas_tablas.sql;
```

## ⚠️ Notas Importantes

1. **Backups**: Siempre realiza una copia de seguridad antes de ejecutar scripts de mantenimiento
2. **Entorno**: Los scripts de eliminación deben usarse solo en entornos de desarrollo/pruebas
3. **Orden**: Respeta el orden de ejecución debido a las claves foráneas
4. **Permisos**: Asegúrate de tener permisos suficientes para ejecutar los scripts

## 🔐 Usuario Administrador Predeterminado

- **Email**: admin@autoexam.local
- **Contraseña**: admin123
- **Rol**: admin

> ⚠️ **Importante**: Cambia estas credenciales después de la primera instalación

## 📝 Historial de Versiones

- **v1.3** (17/06/2025): Actualización a 19 tablas, documentación mejorada
- **v1.2** (14/06/2025): Estructura organizada en carpetas, tablas principales
- **v1.1** (11/06/2025): Mejoras en sistema de sesiones
- **v1.0**: Versión inicial

---
**Mantenido por**: Carlos Ferrero Bonet  
**Proyecto**: AUTOEXAM2 - Sistema de Exámenes Automáticos
