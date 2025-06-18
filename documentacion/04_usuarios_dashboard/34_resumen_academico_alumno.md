# 34 – Resumen académico del alumno

---

## 🎯 Objetivos clave del sistema

- Mostrar al alumno un resumen claro y estructurado de su progreso académico  
- Ofrecer estadísticas visuales y medias generales por curso y módulo  
- Facilitar la descarga de un informe personalizado en PDF  
- Asegurar que el acceso se limite al alumno autenticado correspondiente  
- Integrarse con otros módulos sin duplicar la información existente  

---

## 🧭 Objetivo

Permitir a cada alumno visualizar desde su panel un resumen estructurado de su progreso académico: cursos, módulos, exámenes realizados, notas obtenidas y medias generales.

---

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `12_modulo_cursos.md`
- `14_modulo_examenes.md`
- `16_modulo_calificaciones.md`
- `33_exportacion_datos.md`

---

## 📊 Contenido del resumen

| Elemento                          | Detalles                                                       |
|----------------------------------|----------------------------------------------------------------|
| Cursos                           | Listado de cursos actuales o pasados del alumno                |
| Módulos                          | Módulos vinculados a los cursos                                |
| Exámenes realizados              | Nombre, módulo, fecha, calificación (sin mostrar contenido)     |
| Estado por módulo                | Icono y badge: "En curso", "Aprobado", "No iniciado"           |
| Media por curso y por módulo     | Promedios automáticos según las notas disponibles               |

---

## 🧪 UI/UX

- Disponible desde el dashboard del alumno (`dashboard_alumno.php`)  
- Tabla expandible por curso → módulos → exámenes  
- Botón para exportar resumen académico como PDF  
- Colores según estado: verde (aprobado), azul (en curso), gris (no iniciado)  
- Etiquetas con iconos: `fa-book`, `fa-graduation-cap`, `fa-check`  

---

## 🧱 MVC y rutas implicadas

| Componente             | Ruta                                              |
|------------------------|---------------------------------------------------|
| Vista resumen alumno   | `vistas/alumno/resumen_academico.php`            |
| Controlador            | `controladores/resumen_alumno.php`               |
| Generador PDF opcional | `utilidades/pdf_resumen_alumno.php`              |

---

## 🔒 Seguridad

- Solo accesible por alumnos autenticados  
- Solo pueden ver su propia información  
- Acceso controlado mediante token de sesión  

---

## 🧩 Exportaciones disponibles (via módulo 33)

| Contenido exportable     | Formato         | Acceso |
|--------------------------|------------------|--------|
| Resumen completo         | PDF              | Alumno |

- Exportación solo desde su propio panel  
- Incluye cursos, módulos, medias, sin detalles sensibles  

---

## 🗃️ Tablas y campos implicados

Este módulo no requiere una tabla nueva. La información mostrada se compone de datos ya existentes en las siguientes tablas:

- `usuarios` (datos del alumno)  
- `cursos` y `alumno_curso` (matriculación)  
- `modulos`, `modulo_curso` (asociaciones)  
- `examenes` (exámenes asignados al alumno)  
- `calificaciones` (notas obtenidas)  

Se accede mediante JOINs controlados desde el backend con validación de sesión activa.

---

## 🪵 Manejo de errores y logs

- Fallos en generación de resumen → log en `/almacenamiento/logs/resumen_alumno_error.log`  
- Si se genera el PDF correctamente → registrar acción en `registro_actividad`  
- Intentos de acceso a otro usuario → redirigir + registrar intento no autorizado  

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


## ✅ Checklist Copilot

- [ ] Crear vista resumen solo para rol alumno  
- [ ] Agrupar contenido por curso y módulo  
- [ ] Mostrar estado visual (etiquetas)  
- [ ] Calcular media por curso y por módulo  
- [ ] Exportar resumen como PDF personal  
- [ ] Registrar exportación e intentos inválidos en log  

---

📌 A continuación, Copilot debe leer e implementar: 39_resultado_pdf_alumno.md
