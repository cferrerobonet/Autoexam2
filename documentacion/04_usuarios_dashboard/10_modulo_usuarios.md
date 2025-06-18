# 10 – Módulo de usuarios

Este módulo gestiona la creación, edición, visualización y control general de los usuarios del sistema. Aplica a los tres roles: administrador, profesor y alumno.

---

## 🎯 Objetivos clave del sistema

- Gestionar la lista completa de usuarios con acceso al sistema  
- Permitir edición y creación rápida con validación visual  
- Controlar roles, estado y acceso por tipo de usuario  
- Registrar cada acción relevante en el sistema  
- Habilitar exportación, búsqueda y selección múltiple  

---

## 🗃️ Tabla `usuarios` (Implementada)

| Campo          | Tipo          | Descripción                                 | Estado        |
|----------------|---------------|---------------------------------------------|---------------|
| id_usuario     | INT PK AI     | Identificador único                         | ✅ Implementado |
| nombre         | VARCHAR(100)  | Nombre del usuario                          | ✅ Implementado |
| apellidos      | VARCHAR(150)  | Apellidos completos                         | ✅ Implementado |
| correo         | VARCHAR(150)  | Identificador único y validado              | ✅ Implementado |
| contrasena     | VARCHAR(255)  | Cifrada (hash seguro)                       | ✅ Implementado |
| foto           | VARCHAR(255)  | Ruta de imagen o `user_image_default.png`   | ✅ Implementado |
| rol            | ENUM          | admin, profesor, alumno                     | ✅ Implementado |
| activo         | TINYINT(1)    | 1 = habilitado, 0 = deshabilitado           | ✅ Implementado |
| curso_asignado | INT (nullable)| FK a curso si aplica (solo alumnos)         | ✅ Implementado |
| ultimo_acceso  | DATETIME      | Fecha y hora del último acceso              | ✅ Implementado |
| pin            | VARCHAR(6)    | PIN temporal (nullable)                     | ✅ Implementado |

---

## 📂 MVC y estado de implementación

| Componente                 | Ruta                                          | Estado        |
|----------------------------|-----------------------------------------------|---------------|
| Modelo                     | `modelos/usuario_modelo.php`                 | ✅ Implementado |
| Vista de login             | `vistas/autenticacion/login.php`             | ✅ Implementado |
| Vista de recuperación      | `vistas/autenticacion/recuperar.php`         | ⚠️ Parcial    |
| Controlador autenticación  | `controladores/autenticacion_controlador.php` | ✅ Implementado |
| Vista de usuarios          | `vistas/admin/usuarios/listar.php`           | ✅ Implementado |
| Controlador usuarios       | `controladores/usuarios_controlador.php`     | ✅ Implementado |
| Vistas adicionales         | `crear.php`, `editar.php`                    | ✅ Implementado |

---

## 🧪 Estado actual de implementación

### Implementado
- ✅ Modelo de usuario con funciones CRUD básicas
- ✅ Autenticación básica mediante correo y contraseña
- ✅ Sistema de hash seguro para contraseñas
- ✅ Vista de login responsiva con Bootstrap
- ✅ Validación de campos obligatorios
- ✅ Controlador de usuarios completo
- ✅ Vista de listado con paginación y filtros
- ✅ Vista de creación de usuarios
- ✅ Vista de edición de usuarios
- ✅ Sistema de desactivación de usuarios
- ✅ Validaciones de seguridad CSRF
- ✅ Control de permisos administrativos

### Parcial o en progreso
- ⚠️ Vista de recuperación de contraseña (estructura básica)
- ⚠️ Verificación de usuario activo

### Pendiente
- ❌ Vista de detalle de usuario
- ❌ Checkboxes para selección masiva
- ❌ Interfaz tipo "pijama" en filas (parcialmente implementado)
- ❌ Tooltips explicativos (parcialmente implementado)
- ❌ Iconos representativos por rol (implementados en badges)
- ❌ Filtros diferenciados visualmente (implementados básicos)
- ❌ Importación masiva de usuarios
- ❌ Exportación de datos de usuarios
- ❌ Gestión de avatares desde galería  

---

## 📊 Exportaciones disponibles (integrado con módulo 33)

| Contenido exportable        | Formato         | Acceso permitido |
|-----------------------------|------------------|------------------|
| Listado completo de usuarios| XLSX, CSV        | Admin            |
| Filtros aplicados en vista | XLSX, CSV        | Admin            |

- El botón “Exportar usuarios” aparece en la vista de listado general  
- La exportación respeta el filtro por rol, estado o búsqueda activa  
- El nombre del archivo incluye fecha (`usuarios_20250522.xlsx`)

---

## 🖼️ Selector de imagen desde galería

- Botón “Elegir desde galería” junto al input de imagen  
- Modal con vista galería (`tipo = avatar`) del propio usuario o públicas  
- Se previsualiza al seleccionarla  
- Ruta se guarda como campo `foto`  
- Acción registrada en `registro_actividad`

---

## 📋 Estándar de tabla interactiva

- Acciones fuera de la tabla (crear, borrar, desactivar…)  
- Selección múltiple por checkbox  
- Edición directa al hacer clic sobre fila  
- Fila tipo “pijama”  
- Separación clara entre filtros y botones de acción  
- Orden asc/desc en columnas clave  
- Paginación (5/10/15/20/Todos), por defecto 15  

---

## 🛡️ Seguridad

- Acceso restringido según rol (admin o profesor)  
- Validaciones de correo y duplicados  
- CSRF token en formularios  
- Registro en `registro_actividad`  

---

## 🪵 Manejo de errores y logs

- Validación de campos vacíos o duplicados con retroalimentación inmediata  
- Fallos al guardar → log en `/almacenamiento/logs/usuarios_error.log`  
- Errores críticos → se notifican al admin si está en modo debug  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación exhaustiva de entradas, permisos y sesiones
- Uso de token CSRF en formularios críticos
- Registro detallado de acciones en `registro_actividad`
- Logs técnicos separados por módulo en `/almacenamiento/logs/`
- Acceso restringido por rol y curso donde aplique
- Control de errores con feedback claro para el usuario
- Sanitización de entradas y protección contra manipulación
- Integración segura con otros módulos relacionados


## ✅ Checklist para Copilot

- [x] Mostrar listado general de usuarios  
- [x] Incluir acciones fuera de tabla  
- [x] Habilitar selección múltiple  
- [x] Aplicar estilo pijama y ordenación  
- [x] Separar filtros de acciones  
- [x] Paginación por defecto 15  
- [x] Validar correo único  
- [ ] Cargar imagen de usuario opcional  
- [x] Registrar eventos en log  
- [x] Agregar botón de exportación  
- [ ] Habilitar selector de imagen desde galería  
- [ ] Filtrar galería por tipo = avatar  
- [ ] Insertar ruta en campo `foto`  
- [ ] Validar imagen como válida y autorizada  
- [x] Generar XLSX o CSV con filtros activos  
- [x] Registrar exportación en `registro_actividad`

## ✅ Funcionalidades Avanzadas (Fase 3) - COMPLETADAS

### 🔄 Historial de Cambios
- ✅ Modelo `RegistroActividad` implementado
- ✅ Registro automático de todas las acciones (crear, editar, desactivar, importar)
- ✅ Vista de historial completo por usuario (/usuarios/historial/{id})
- ✅ Información detallada: fecha, acción, descripción, IP, user agent
- ✅ Navegación desde listado principal

### 📥 Importación Masiva
- ✅ Vista de importación (/usuarios/importar)
- ✅ Procesamiento de archivos CSV con validaciones
- ✅ Generación automática de contraseñas seguras
- ✅ Envío opcional de credenciales por email
- ✅ Plantilla CSV descargable con ejemplos
- ✅ Reporte detallado de éxitos y errores
- ✅ Validación de formatos y duplicados

### 📊 Estadísticas y Reportes
- ✅ Dashboard de estadísticas (/usuarios/estadisticas)
- ✅ Métricas generales (total, activos, inactivos)
- ✅ Distribución por roles con gráfico circular
- ✅ Actividad reciente de los últimos 30 días
- ✅ Integración con Chart.js para visualizaciones

### 📧 Notificaciones
- ✅ Sistema de email para usuarios importados
- ✅ Plantilla de bienvenida personalizable
- ✅ Integración con utilidad de correo existente
- ✅ Manejo de errores en envío de emails

### 🎯 Mejoras UX Avanzadas
- ✅ Botones de historial, importar y estadísticas en listado
- ✅ Iconos informativos y tooltips
- ✅ Navegación breadcrumb en todas las vistas
- ✅ Mensajes de estado mejorados (éxito, error, warning)
- ✅ Diseño responsive y moderno

---

📌 A continuación, Copilot debe leer e implementar: `12_modulo_cursos.md`
