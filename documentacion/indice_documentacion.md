# Índice de Documentación - AUTOEXAM2

**Última actualización:** 21 de junio de 2025

Este documento proporciona un índice organizado de toda la documentación disponible para AUTOEXAM2, facilitando el acceso a la información específica necesaria para el desarrollo y mantenimiento.

> **NOTA:** La documentación ha sido reorganizada en dos secciones principales: `/actual/` (contiene la documentación actualizada y unificada) y `/historial/` (contiene versiones anteriores para referencia histórica).
>
> **ESTADO DE MIGRACIÓN:** Los directorios numerados (01_estructura_presentación, etc.) contienen documentación original que está en proceso de migración a la estructura unificada `/actual/`. Se mantendrán hasta completar la migración de todos sus documentos.

---

## 🚀 DOCUMENTACIÓN ACTUALIZADA (Junio 2025)

### 📋 Resumen de Funcionalidades Implementadas
- [**Módulos Funcionales Implementados**](actual/sistema/modulos_funcionales_implementados.md) - **✨ NUEVO** - Estado completo del sistema implementado

### 🎓 Sistema de Examenes
- [**Sistema de Gestión de Exámenes**](actual/examenes/sistema_gestion_examenes.md) - **✨ NUEVO** - Sistema completo de exámenes online
- [**Sistema de Banco de Preguntas**](actual/examenes/sistema_banco_preguntas.md) - **✨ NUEVO** - Gestión centralizada de preguntas reutilizables

### 📚 Gestión Académica
- [**Sistema de Gestión de Módulos**](actual/modulos/sistema_gestion_modulos.md) - **✨ NUEVO** - CRUD completo de módulos v3.0

### 👥 Usuarios y Dashboards
- [**Dashboards por Rol**](actual/usuarios/dashboards_por_rol.md) - **✨ NUEVO** - Interfaces personalizadas por rol

### 🔒 Seguridad y Auditoría
- [**Sistema de Actividad y Auditoría**](actual/sistema/actividad_auditoria.md) - **✨ NUEVO** - Registro completo de actividad
- [**Sistema de Gestión de Sesiones Activas**](actual/seguridad/sesiones_activas.md) - **✨ NUEVO** - Control de sesiones en tiempo real

---

## 1. Estructura y Presentación General

### 1.1 Estructura del Proyecto
- [00 - Estructura del Proyecto](01_estructura_presentacion/01_estructura_proyecto.md) - Arquitectura base y convenciones
- [01 - Presentación General](01_estructura_presentacion/01_presentacion.md) - Objetivos, alcance y módulos
- [04 - Arquitectura MVC y Ruteado](01_estructura_presentacion/04_mvc_routing.md) - Sistema de controladores y rutas
- [05 - Sistema de Configuración](01_estructura_presentacion/05_sistema_configuracion.md) - Variables de entorno
- [Sistema de Almacenamiento](actual/sistema/almacenamiento.md) - Gestión de archivos (Documento Unificado)
- [15 - Sistema de Vistas Parciales](01_estructura_presentacion/15_sistema_vistas_parciales.md) - Componentes UI reutilizables
- [16 - Sistema de Estilos Unificado](01_estructura_presentacion/16_sistema_estilos_unificado.md) - Estilos CSS por rol
- [17 - Sistema de JavaScript Unificado](01_estructura_presentacion/17_sistema_javascript_unificado.md) - Scripts comunes

### 1.2 Requisitos y Configuración
- [02 - Requisitos del Sistema](01_estructura_presentacion/02_requisitos_sistema.md) - Entorno necesario
- [03 - Instalador](01_estructura_presentacion/03_instalador.md) - Proceso de instalación
- [09 - PWA](01_estructura_presentacion/09_pwa.md) - Aplicación web progresiva

### 1.3 Estado del Proyecto
- [Estado de Implementación](actual/estado_implementacion.md) - Progreso actual del proyecto (Documento Unificado)
- [Auditoría Implementación vs Documentación](historial/versiones/00_auditoria_implementacion_vs_documentacion.md) - Revisión completa (Archivo histórico)

---

## 2. Autenticación y Seguridad

### 2.1 Documentación Unificada
- [Autenticación y Seguridad](actual/autenticacion/autenticacion_seguridad.md) - Visión completa del sistema de autenticación (Documento Unificado)

### 2.2 Módulos Específicos
- [11 - Recuperación de Contraseña](03_autenticacion_seguridad/11_recuperacion_contrasena.md) - Sistema de recuperación
- [25 - Refactorización de Recuperación](03_autenticacion_seguridad/25_refactorizacion_recuperacion.md) - Mejoras recientes

### 2.3 Implementaciones en Progreso
- [23 - Sesiones Activas](03_autenticacion_seguridad/23_sesiones_activas.md) - Control de sesiones
- [24 - Sistema de Gestión de Sesiones](03_autenticacion_seguridad/24_sistema_sesiones.md) - Gestión completa ✨ NUEVO
- [24 - Control Horario de Login](03_autenticacion_seguridad/24_control_horario_login.md) - Restricción de acceso
- [46 - Protección Fuerza Bruta](03_autenticacion_seguridad/46_proteccion_fuerza_bruta.md) - Limitación de intentos
- [47 - Protección Fuerza Bruta Avanzada](03_autenticacion_seguridad/47_proteccion_fuerza_bruta_avanzada.md) - Sistema completo ✨ NUEVO
- [60 - Verificación Post Login](03_autenticacion_seguridad/60_verificacion_post_login.md) - Seguridad avanzada

### 2.4 Otros Recursos
- [Solución de Problemas de Correo](03_autenticacion_seguridad/solucion_problemas_correo.md) - Diagnóstico de correo

---

## 3. Usuarios y Roles

### 3.1 Gestión de Usuarios
- [10 - Módulo de Usuarios](04_usuarios_dashboard/10_modulo_usuarios.md) - CRUD de usuarios
- [35 - Gestión de Perfil de Usuario](04_usuarios_dashboard/35_gestion_perfil_usuario.md) - Perfil y sesiones propias
- [Gestión de Avatares de Usuario](actual/usuarios/gestion_avatares.md) - Sistema de avatares (Documento Unificado)
- [Refactorización del Módulo de Usuarios](actual/usuarios/refactorizacion/refactorizacion_modulo_usuarios.md) - Mejoras implementadas
- [61 - Sesión Única por Usuario](04_usuarios_dashboard/61_sesion_unica_usuario.md) - Control de simultaneidad

### 3.2 Roles y Permisos
- [03 - Roles y Entidades](02_roles_entidades_permisos/03_roles_entidades.md) - Definición de roles
- [04 - Flujos Funcionales](02_roles_entidades_permisos/04_flujos_funcionales.md) - Procesos por rol
- [21 - Gestión de Permisos](02_roles_entidades_permisos/21_gestion_permisos.md) - Control de acceso

### 3.3 Dashboard
- [18 - Dashboard por Rol](04_usuarios_dashboard/18_dashboard_por_rol.md) - Visualizaciones personalizadas

---

## 4. Configuración y Mantenimiento

### 4.1 Configuración
- [04 - Configuración](09_configuracion_mantenimiento/04_configuracion.md) - Configuración global
- [06 - Configuración Avanzada](09_configuracion_mantenimiento/06_configuracion.md) - Parámetros detallados
- [40 - Sistema de Manejo de Errores](09_configuracion_mantenimiento/40_sistema_manejo_errores.md) - Control de errores centralizado ✨ NUEVO
- [Clase Env](09_configuracion_mantenimiento/clase_env.md) - Sistema de variables de entorno
- [Variables de Entorno](09_configuracion_mantenimiento/variables_entorno.md) - Configuración .env
- [Clase Correo](09_configuracion_mantenimiento/clase_correo.md) - Sistema de correo electrónico

### 4.2 Almacenamiento y Base de Datos
- [Sistema de Almacenamiento](actual/sistema/almacenamiento.md) - Sistema centralizado (Documento Unificado)
- [Sistema de Base de Datos](actual/sistema/base_datos.md) - Estructura completa de BD (Documento Unificado)
- [Registro de Actualizaciones](09_configuracion_mantenimiento/registro_actualizaciones.md) - Historial de cambios

### 4.3 Herramientas
- [19 - Módulo de Mantenimiento](09_configuracion_mantenimiento/19_modulo_mantenimiento.md) - Mantenimiento
- [41 - Registro de Actividad](09_configuracion_mantenimiento/41_registro_actividad.md) - Auditoría
- [45 - Verificación Integridad](09_configuracion_mantenimiento/45_verificacion_integridad_sistema.md) - Validación
- [57 - Pruebas y Validación](09_configuracion_mantenimiento/57_pruebas_validacion_qa.md) - QA
- [58 - Backup y Restauración](09_configuracion_mantenimiento/58_backup_restauracion.md) - Respaldos
- [59 - Modo Mantenimiento](09_configuracion_mantenimiento/59_modo_mantenimiento.md) - Mantenimiento
- [65 - Control de Versionado](09_configuracion_mantenimiento/65_control_versionado.md) - Versiones
- [66 - Sistema de Diagnóstico](09_configuracion_mantenimiento/66_sistema_diagnostico.md) - Panel web de diagnóstico
- [68 - Actualizaciones del Instalador](09_configuracion_mantenimiento/68_actualizaciones_instalador.md) - Mejoras instalador
- [Herramientas Administrativas](09_configuracion_mantenimiento/herramientas_administrativas.md) - Gestión

### 4.4 Historial de Cambios Recientes
- [Registro de Cambios](registro_cambios_documentacion.md) - Cambios en documentación
- [Estado de Implementación](actual/estado_implementacion.md) - Progreso actual del proyecto (Documento Unificado)

### 4.5 Recursos y Optimización
- [70 - Optimización de Recursos Estáticos](09_configuracion_mantenimiento/70_optimizacion_recursos_estaticos.md) - Consolidación y gestión
- [71 - Minificación de Recursos](09_configuracion_mantenimiento/71_minificacion_recursos.md) - Optimización CSS/JS ✨ NUEVO
- [20 - Variables CSS y Personalización](01_estructura_presentacion/20_variables_css_personalizacion.md) - Sistema de temas ✨ NUEVO

---

## 5. Funcionalidad Principal

### 5.1 Gestión Académica
- [Módulos de Cursos y Exámenes](05_cursos_modulos_examenes/) - Sistema académico principal
- [Módulo de Calificaciones](07_calificaciones_entrega/16_modulo_calificaciones.md) - Evaluación

### 5.2 Características Adicionales
- [Módulo IA](06_ia/15_modulo_ia.md) - Inteligencia Artificial
- [Gestión Multimedia](11_recursos_multimedia/31_gestion_multimedia.md) - Recursos
- [Exportación de Datos](09_configuracion_mantenimiento/33_exportacion_datos.md) - Informes

---

## 6. Bases de Datos y Scripts SQL

- [Scripts SQL](00_sql/) - Estructura de base de datos

---

## 7. Pruebas y Validación

- [Pruebas Unitarias](13_test/64_pruebas_unitarias.md) - Testing

---

## 8. Nueva Estructura de Documentación

### 8.1 Documentos Actualizados (Carpeta `/actual/`)
La documentación actualizada y unificada se encuentra en la carpeta `/actual/`, organizada por módulos:

- [Estado de Implementación](actual/estado_implementacion.md) - Visión general del proyecto actualizada
- **Sistema**:
  - [Sistema de Almacenamiento](actual/sistema/almacenamiento.md) - Gestión unificada de archivos
  - [Sistema de Base de Datos](actual/sistema/base_datos.md) - Estructura y gestión de BD ✨ NUEVO
  - [Mapa Visual de Documentación](actual/mapa_documentacion.md) - Navegación visual por la documentación
  - [Sistema de Revisiones de Documentación](actual/sistema_revisiones_documentacion.md) - Proceso de mantenimiento
- **Autenticación**:
  - [Autenticación y Seguridad](actual/autenticacion/autenticacion_seguridad.md) - Sistema completo de autenticación
- **Usuarios**:
  - [Gestión de Avatares](actual/usuarios/gestion_avatares.md) - Sistema de fotos de perfil y avatares
- **Cursos y Exámenes**:
  - [Gestión de Cursos y Exámenes](actual/cursos/gestion_cursos_examenes.md) - Sistema académico unificado ✨ NUEVO

### 8.2 Documentación Histórica (Carpeta `/historial/`)
Los documentos históricos y versiones anteriores se han conservado en la carpeta `/historial/versiones/` para referencia:

- Documentos de estado de implementación antiguos
- Versiones previas de la documentación de autenticación
- Versiones previas de la documentación de almacenamiento
- Versiones previas de la documentación de usuarios y avatares

> **Nota**: La documentación se está unificando progresivamente para eliminar duplicidades y ofrecer una visión coherente del sistema. Los documentos en `/actual/` deben ser considerados como la referencia actualizada.

---

Este índice organiza la documentación para facilitar el acceso a información específica. Se actualiza periódicamente para reflejar los cambios en el proyecto.

Para comenzar a trabajar con AUTOEXAM2, se recomienda revisar primero la [Estructura del Proyecto](01_estructura_presentacion/01_estructura_proyecto.md) y luego el [Estado de Implementación](actual/estado_implementacion.md) para entender la situación actual.
