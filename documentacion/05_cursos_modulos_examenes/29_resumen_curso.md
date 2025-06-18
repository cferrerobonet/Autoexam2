29 – Resumen por curso

Este modulo permite a profesores y administradores consultar un resumen completo y estructurado de un curso: alumnos, modulos, examenes y estadisticas generales.

⸻

🎯 Objetivos clave del sistema
	•	Mostrar en un solo lugar toda la información crítica de un curso: personas, asignaturas, exámenes, notas
	•	Permitir acciones rápidas desde cada bloque sin salir del resumen
	•	Ofrecer estadísticas visuales y comparativas para facilitar la gestión
	•	Permitir navegación por pestañas o secciones con diseño limpio y responsive

⸻

🎯 Objetivo

Ofrecer una vista consolidada de la informacion clave de un curso, mejorando la gestion y toma de decisiones.

⸻

👥 Quien puede acceder
	•	Administrador: todos los cursos
	•	Profesor: solo sus cursos

⸻

📋 Secciones del resumen
	1.	👥 Alumnos asignados
	•	Nombre, apellidos, correo
	•	Estado (activo/inactivo)
	•	Acceso directo a edicion de alumno
	2.	📚 Modulos asociados
	•	Titulo del modulo
	•	Profesor asignado
	•	Numero de examenes por modulo
	3.	📝 Examenes del curso
	•	Titulo
	•	Modulo
	•	Estado (borrador, publicado, corregido)
	•	Numero de alumnos que lo han realizado
	•	Promedio (si ya corregido)
	4.	📊 Estadisticas globales
	•	Total de alumnos
	•	Total de examenes
	•	Promedio general del curso
	•	Progreso medio por alumno

⸻

📂 MVC

Componente	Ruta
Vista resumen	vistas/cursos/resumen_curso.php
Controlador resumen	controladores/resumen_curso.php
Modelos implicados	curso.php, modulo.php, alumno.php, examen.php
JS dinamico (opcional)	publico/scripts/resumen_curso.js


⸻

🧪 UI/UX
	•	Mostrar en bloque lateral los próximos exámenes desde calendario (32_calendario_eventos.md).
	•	Diseno con bloques o pestañas
	•	Etiquetas visuales (badge) para estados
	•	Iconos representativos (fa-users, fa-book, fa-file-alt, fa-chart-bar)
	•	Tooltips explicativos
	•	Responsive y limpio (Bootstrap 5)

⸻

🛡️ Seguridad
	•	Validacion del rol y propiedad del curso
	•	Proteccion contra carga de curso no autorizado
	•	Registro en registro_actividad

⸻

🪵 Manejo de errores y logs
	•	Carga incompleta de alguna sección → log en /almacenamiento/logs/resumen_curso_error.log
	•	Acceso no permitido → redirigir y registrar intento en registro_actividad
	•	Estadísticas incorrectas → loguear y mostrar fallback visual

⸻

🛡️ Fortalezas que Copilot debe implementar
	•	Validación estricta del rol y del curso
	•	Separación clara de bloques por componente
	•	Estadísticas calculadas en tiempo real con fallback
	•	Logs diferenciados por error de acceso y cálculo
	•	Interfaz limpia y accesible
	•	Integración directa con calendario (32_calendario_eventos.md) y módulos relacionados

⸻

📋 Estandar de tabla interactiva
	•	Acciones fuera de la tabla (crear, borrar, desactivar…)
	•	Seleccion multiple por checkbox
	•	Edicion directa al hacer clic sobre fila
	•	Fila tipo “pijama”
	•	Separacion clara entre filtros y botones de accion
	•	Orden asc/desc en columnas clave
	•	Paginacion (5/10/15/20/Todos), por defecto 15

⸻

✅ Checklist Copilot
	•	Conectar con 32_calendario_eventos.md para mostrar eventos programados del curso
	•	Crear vista con resumen dividido en secciones
	•	Consultar alumnos, modulos y examenes asociados
	•	Mostrar estadisticas globales del curso
	•	Agregar acciones rapidas (editar alumno, ver examen)
	•	Proteger vista segun rol
	•	Aplicar diseño consistente con UI general
	•	Registrar accesos al resumen

⸻

📌 A continuación, Copilot debe leer e implementar: 35_banco_preguntas.md