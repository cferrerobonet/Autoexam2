# Gestión de Cursos y Exámenes

**Última actualización:** 17 de junio de 2025

Este documento unifica la documentación relacionada con la gestión de cursos, módulos y exámenes en el sistema AUTOEXAM2.

## 1. Visión General del Módulo

La gestión de cursos y exámenes es uno de los módulos centrales de AUTOEXAM2, permitiendo la creación y administración de contenido educativo organizado jerárquicamente:

```
Curso > Módulo > Examen
```

Este sistema permite a los profesores crear contenido educativo estructurado y a los alumnos acceder a los materiales y evaluaciones correspondientes.

## 2. Estructura de Datos

### 2.1. Tablas Principales
- `cursos`: Almacena información básica sobre los cursos
- `modulos`: Agrupa contenido dentro de un curso
- `examenes`: Evaluaciones dentro de un módulo

### 2.2. Tablas de Relación
- `curso_profesor`: Relación entre cursos y profesores que los imparten
- `curso_alumno`: Asignación de alumnos a cursos
- `examen_alumno`: Registro de exámenes completados por alumnos

## 3. Funcionalidades Implementadas

### 3.1. Gestión de Cursos
- Creación, edición y eliminación de cursos
- Asignación de profesores a cursos
- Matriculación de alumnos en cursos
- Visualización de estadísticas de curso

### 3.2. Gestión de Módulos
- Organización de contenido dentro de un curso
- Control de requisitos y progresión

### 3.3. Gestión de Exámenes
- Creación de exámenes con diferentes tipos de preguntas
- Configuración de tiempo límite y número de intentos
- Corrección automática y manual
- Estadísticas de rendimiento

## 4. Flujos de Trabajo

### 4.1. Profesor
1. Crear un curso
2. Estructurar módulos dentro del curso
3. Diseñar exámenes para cada módulo
4. Revisar y calificar exámenes (cuando sea necesario)
5. Consultar estadísticas de rendimiento

### 4.2. Alumno
1. Matricularse en un curso
2. Acceder a los módulos disponibles
3. Completar exámenes
4. Revisar calificaciones y retroalimentación

## 5. Implementación Técnica

### 5.1. Controladores
- `cursos_controlador.php`: Gestión general de cursos
- `modulos_controlador.php`: Gestión de módulos dentro de cursos
- `examenes_controlador.php`: Gestión de exámenes y evaluaciones

### 5.2. Modelos
- `curso_modelo.php`: Operaciones CRUD para cursos
- `modulo_modelo.php`: Operaciones CRUD para módulos
- `examen_modelo.php`: Operaciones CRUD para exámenes

### 5.3. Vistas
Las vistas están organizadas por rol de usuario y funcionalidad específica:
- `/vistas/profesor/cursos/`
- `/vistas/profesor/modulos/`
- `/vistas/profesor/examenes/`
- `/vistas/alumno/cursos/`
- `/vistas/alumno/examenes/`

## 6. Estado Actual de Implementación

| Funcionalidad                | Estado        | Observaciones                               |
|-----------------------------|---------------|---------------------------------------------|
| Creación de cursos           | Completado    | Incluye validación y gestión de permisos    |
| Matriculación de alumnos     | Completado    | Sistema de códigos de invitación implementado |
| Creación de exámenes         | Completado    | Soporta múltiples tipos de preguntas        |
| Corrección automática        | En desarrollo | Funcionando para preguntas tipo test        |
| Exportación de calificaciones| Planificado   | Pendiente de implementar                    |

## 7. Próximas Mejoras

- Implementación de sistema de plantillas para exámenes
- Mejora del sistema de análisis estadístico de rendimiento
- Integración con el módulo de IA para adaptación de contenidos
- Sistema de revisión colaborativa entre profesores

---

**Referencias**:
- Documentación anterior: [Módulos de Cursos y Exámenes](../historial/versiones/05_cursos_modulos_examenes.md)
- Base de datos: Ver esquema en `/base_datos/001_esquema_completo.sql`
