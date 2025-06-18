30 – Gestion de profesores

Este modulo permite al administrador crear, editar, desactivar y consultar informacion completa de los profesores del sistema.

⸻

🎯 Objetivos clave del sistema
	•	Dotar al administrador de control total sobre el personal docente
	•	Registrar cursos, módulos y exámenes asociados a cada profesor
	•	Permitir cambios controlados en sus datos personales y credenciales
	•	Evitar eliminación si existen relaciones activas
	•	Facilitar visualización, filtros, búsquedas y acciones masivas

⸻

🎯 Objetivo

Dotar al administrador de un panel completo para gestionar el personal docente, incluyendo sus datos personales, cursos asignados, modulos y examenes vinculados.

⸻

👥 Quien puede usar este modulo
	•	Administrador unicamente

⸻

📋 Acciones disponibles

Accion	Disponible para	Descripcion
Crear profesor	Admin	Alta manual con validacion y foto opcional
Editar profesor	Admin	Si es rol profesor
Ver detalle	Admin	Cursos, modulos y examenes asociados
Desactivar	Admin	Suspende acceso sin eliminar datos
Eliminar	❌ No	No se permite si hay dependencias (solo desde mantenimiento)


⸻

🧩 Tablas implicadas
	•	usuarios (rol = profesor)
	•	curso (id_profesor)
	•	modulo (id_profesor)
	•	examen (id_profesor)
	•	registro_actividad

⸻

📂 MVC

Componente	Ruta
Listado profesores	vistas/usuarios/listado_profesores.php
Formulario nuevo	vistas/usuarios/nuevo_profesor.php
Formulario edicion	vistas/usuarios/editar_profesor.php
Vista detalle	vistas/usuarios/detalle_profesor.php
Controlador alta	controladores/nuevo_profesor.php
Controlador actualizacion	controladores/actualizar_profesor.php
Controlador desactivacion	controladores/desactivar_profesor.php
Modelo	modelos/usuario.php


⸻

🧪 UI/UX

🖼️ Extension UI: Seleccionar imagen desde galería
	•	Añadir botón “Elegir desde galería” al lado del input de imagen
	•	Abre modal con vista galería (tipo = avatar) del propio usuario o públicas
	•	Se previsualiza al seleccionarla
	•	Ruta se guarda como campo foto
	•	Registro de cambio en registro_actividad
	•	Formulario de alta y edicion con validaciones visuales
	•	Carga de imagen por drop o explorador
	•	Listado de profesores con filtros
	•	Acciones fuera de la tabla
	•	Vista detalle con modulos/cursos/examenes vinculados
	•	Iconos e informacion clara

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

🛡️ Seguridad
	•	Solo accesible para administrador
	•	Validacion del rol antes de ejecutar acciones
	•	Registro completo en registro_actividad

⸻

🪵 Manejo de errores y logs
	•	Errores en creación o edición → /almacenamiento/logs/profesores_error.log
	•	Acción no permitida → redirigir y registrar
	•	Eliminación bloqueada si hay relaciones → feedback al admin + log

⸻

🛡️ Fortalezas que Copilot debe implementar
	•	Validación exhaustiva en creación y edición de profesores
	•	Protección contra edición si el rol no es profesor
	•	Selector de imagen seguro y vinculado a usuario autorizado
	•	Control de relaciones activas antes de desactivar o eliminar
	•	Registro de acciones en registro_actividad
	•	Interfaz con feedback visual para cada acción

⸻

✅ Checklist Copilot
	•	Habilitar selector de imagen desde galería
	•	Mostrar galería filtrada por tipo ‘avatar’ y visibilidad
	•	Insertar ruta en campo foto del usuario
	•	Validar que la imagen seleccionada pertenece al usuario o es global
	•	Registrar acción en log de actividad
	•	Crear vista de listado de profesores
	•	Habilitar filtros y ordenamiento
	•	Crear formulario nuevo con validacion
	•	Implementar formulario de edicion
	•	Vista detalle con cursos/modulos/examenes asociados
	•	Permitir desactivar sin eliminar
	•	Mostrar botones de accion fuera de la tabla
	•	Aplicar diseño pijama, seleccion multiple, paginacion
	•	Registrar todas las acciones

⸻

📌 A continuación, Copilot debe leer e implementar: 34_resumen_academico_alumno.md