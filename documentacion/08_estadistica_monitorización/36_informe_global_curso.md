# 36 – Informe global por curso

---

## 🎯 Objetivos clave del sistema

- Visualizar el rendimiento académico completo de un curso de forma centralizada  
- Ofrecer estadísticas por alumno, módulo y curso en conjunto  
- Permitir exportar el informe completo en PDF, CSV o XLSX  
- Garantizar que cada rol vea solo la información que le corresponde  
- Servir como documento de referencia para evaluación, tutorías y archivado institucional  

---

## 🧭 Objetivo

Permitir al administrador y a los profesores generar un informe resumen completo de un curso que incluya el rendimiento de todos los alumnos: módulos cursados, exámenes realizados, calificaciones y medias por alumno y por módulo.

---

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `12_modulo_cursos.md`
- `16_modulo_calificaciones.md`
- `17_modulo_estadisticas.md`
- `33_exportacion_datos.md`
- `34_resumen_academico_alumno.md`

---

## 📊 Funcionalidades principales

| Funcionalidad                     | Acceso         | Descripción                                                   |
|----------------------------------|----------------|---------------------------------------------------------------|
| Ver informe general del curso    | Admin, profesor| Muestra todos los alumnos con sus notas por módulo           |
| Ver media por módulo             | Admin, profesor| Calculada automáticamente                                     |
| Ver media por alumno             | Admin, profesor| Muestra promedio individual del curso                         |
| Ver media global del curso       | Admin, profesor| Cálculo de promedio general                                   |
| Exportar el informe              | Admin, profesor| XLSX, CSV, PDF                                                |
| Visualización por alumno         | Alumno         | Solo lectura, sólo de su curso                                |

---

## 🧪 UI/UX

- Tabla resumen curso → alumnos × módulos  
- Cabecera fija, scroll horizontal si necesario  
- Badges visuales de aprobado/suspenso  
- Botones flotantes: “Exportar como...”  
- Filtros: módulo, fechas, alumno  

---

## 🧱 MVC y rutas implicadas

| Componente           | Ruta                                              |
|----------------------|---------------------------------------------------|
| Vista resumen curso  | `vistas/profesor/resumen_curso.php`              |
| Controlador          | `controladores/resumen_curso.php`                |
| Exportador           | `utilidades/exportar_resumen_curso.php`          |

---

## 🗃️ Tablas y campos implicados

Consulta cruzada entre:

- `usuarios` (rol = alumno)  
- `cursos`, `alumno_curso`  
- `modulos`, `modulo_curso`  
- `examenes`  
- `calificaciones`  

No requiere crear nuevas tablas.

---

## 🔐 Seguridad

- El profesor solo accede a cursos donde es responsable  
- El alumno solo puede ver su parte en modo lectura  
- El admin tiene acceso completo  
- Validación cruzada por curso y rol activo  

---

## 🪵 Manejo de errores y logs

- Fallos en cálculo de medias o consultas → log en `/almacenamiento/logs/informe_curso_error.log`  
- Exportaciones realizadas → registradas en `registro_actividad`  
- Intento de acceso a otro curso no autorizado → redirigir + registrar evento  

---

## ✅ Checklist Copilot

- [ ] Crear controlador y vista `resumen_curso.php`  
- [ ] Renderizar tabla alumno × módulo con notas  
- [ ] Calcular medias por alumno, por módulo y global  
- [ ] Añadir filtros por módulo, alumno, fechas  
- [ ] Añadir botón de exportación a PDF/XLSX  
- [ ] Limitar acceso según rol y curso asignado  

---

📌 A continuación, Copilot debe leer e implementar: 22_notificaciones.md
