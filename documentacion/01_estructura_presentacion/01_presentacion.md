# 01 – Manual de Presentación e Implementación General de AUTOEXAM2

Este documento es la guía exhaustiva para comprender, planificar y comenzar la implementación de AUTOEXAM2. Aquí se definen los objetivos, arquitectura, roles, módulos principales, dependencias y las fortalezas globales que deben estar presentes en toda la plataforma. Sirve como punto de partida y referencia para cualquier desarrollador o sistema de ayuda (incluido Copilot).

---

## 1. Objetivos y Alcance
- Gestionar exámenes en línea de forma segura y trazable.
- Diferenciar el acceso y permisos según el rol (admin, profesor, alumno).
- Evaluar automáticamente y registrar calificaciones personalizadas.
- Extraer estadísticas por curso, módulo o alumno.
- Ofrecer un diseño moderno, responsive y adaptable como PWA.
- Asegurar accesibilidad, seguridad y mantenimiento completo de la plataforma.

---

## 2. Propósito y Filosofía
AUTOEXAM2 es una aplicación web modular en PHP puro, arquitectura MVC desacoplada, orientada a la gestión integral de exámenes online en centros educativos. Permite evaluaciones, gestión de usuarios por rol, calificación automática/manual, informes y compatibilidad total con dispositivos móviles (PWA).

---

## 3. Dependencias y Estructura de Documentación
- [00_estructura_proyecto.md](01_estructura_proyecto.md): arquitectura y estructura base.
- [02_requisitos_sistema.md](02_requisitos_sistema.md): entorno mínimo requerido.
- [05_autenticacion.md](../03_autenticacion_seguridad/05_autenticacion.md): control de acceso inicial.
- [04_configuracion.md](../09_configuracion_mantenimiento/04_configuracion.md): parámetros globales.
- [11_modulo_usuarios.md](../04_usuarios_dashboard/11_modulo_usuarios.md): alta de usuarios desde el inicio.
- [33_exportacion_datos.md](../05_cursos_modulos_examenes/33_exportacion_datos.md): extracción de información desde cualquier módulo.

---

## 4. Roles y Alcance Funcional
| Rol         | Funcionalidades habilitadas                                                                 |
|-------------|---------------------------------------------------------------------------------------------|
| Administrador| Gestión completa de usuarios, configuración, mantenimiento, estadísticas, exportación      |
| Profesor    | Gestión de cursos, módulos, exámenes, corrección, acceso a multimedia y banco de preguntas |
| Alumno      | Realización de exámenes, consulta de resultados y comentarios, estadísticas personales     |

---

## 5. Módulos Principales Incluidos
1. Instalador guiado
2. Autenticación y control de acceso
3. Gestión de usuarios y roles
4. Dashboard según tipo de usuario
5. Cursos, módulos y banco de preguntas
6. Exámenes y calificaciones (manual o automática)
7. Exportación de informes y datos
8. Estadísticas y seguimiento académico
9. Soporte para IA (generación y corrección)
10. Calendario interactivo y planificador
11. Gestión multimedia centralizada
12. Configuración avanzada del sistema
13. Módulos de mantenimiento, backup y QA

---

## 6. Fortalezas Globales que Copilot debe Implementar
- Control de acceso según sesión activa y rol.
- Validación de entradas en cliente y servidor.
- Hash seguro de contraseñas, PIN o tokens.
- Restricción de navegación por rutas según permisos.
- Control único de sesión (un usuario, una sesión activa).
- Registro obligatorio en `registro_actividad` de cada acción relevante.
- Trazabilidad de todos los eventos relevantes (exámenes, correcciones, cambios).
- Exportación segura limitada por permisos.
- Compatibilidad completa con dispositivos móviles (responsive + PWA).

---

## 7. Validaciones y Control de Errores Esperados
- Errores visibles solo con mensajes genéricos (“credenciales incorrectas”, “sin permiso”, etc.).
- Validaciones visibles: campos obligatorios, formato, duplicados.
- Validación de relaciones cruzadas entre entidades (usuarios y cursos, módulos y exámenes).
- Validación contextual según el rol del usuario logueado.

---

## 8. Registro de Actividad y Errores
- Todas las acciones críticas deben registrar: usuario, IP, hora, módulo, acción.
- Almacenamiento principal en tabla `registro_actividad`.
- Posibilidad de exportación para auditoría (ver módulo 33).
- Captura de errores lógicos o técnicos en logs internos (`/almacenamiento/logs/`).

---

## 9. Casos Límite y Comportamiento Esperado
- Alumno sin exámenes → mostrar mensaje.
- Profesor sin cursos asignados → acceso parcial limitado.
- Usuario con sesión caducada → redirección automática a login.
- Reintento de acción sin permisos → log + redirección con mensaje.

---

## 10. Checklist de Implementación para Copilot
- [ ] Comprender objetivos generales del sistema.
- [ ] Tener clara la separación y restricciones por rol.
- [ ] Controlar que cada módulo tenga validación, seguridad y trazabilidad.
- [ ] Aplicar todas las fortalezas globales y cumplir dependencias.
- [ ] Actualizar todos los `.md` con el avance real (checklist).
- [ ] Usar este documento como visión general del sistema.

---

## 11. Estado del Proyecto
- Documentación consolidada y lista para desarrollo asistido por IA.
- Arquitectura modular y autocontenida.
- Fuente única de información técnica para el desarrollo del sistema.

---

## 12. Siguiente Paso
📌 A continuación, Copilot debe leer e implementar: [02_requisitos_sistema.md](02_requisitos_sistema.md)