27 – Edición y actualización de datos de alumno

🎯 Objetivos clave del sistema
	•	Permitir modificar de forma controlada los datos de un alumno ya existente
	•	Asegurar validaciones visuales e internas antes de guardar cualquier cambio
	•	Facilitar la actualización de imagen, contraseña o datos básicos según el rol
	•	Proteger integridad de los datos ya vinculados (exámenes, calificaciones)
	•	Reforzar la seguridad en el acceso y edición de registros personales

⸻

🔗 Dependencias
	•	10_modulo_usuarios.md
	•	06_configuracion.md

Este módulo permite editar los datos personales y de acceso de alumnos ya creados, con validaciones visuales y seguridad.

⸻

👥 Quién puede editar alumnos
	•	Administrador (todos los alumnos)
	•	Profesor (solo alumnos de sus cursos)

⸻

✏️ Campos editables

Campo	Editable	Reglas
nombre	✅	Solo letras, obligatorio
apellidos	✅	Solo letras, obligatorio
correo	✅*	Solo si no tiene actividad (no entregas realizadas)
contraseña	✅	Opcional. Si se cambia, debe verificarse con doble campo
foto	✅	Puede actualizarse o restaurarse a la imagen por defecto


⸻

⚠️ Restricciones
	•	El correo no se puede cambiar si el alumno ya ha entregado algún examen.
	•	El profesor no puede cambiar la contraseña (solo el admin).
	•	Toda modificación queda registrada en registro_actividad.

⸻

📧 Reenvío de contraseña por email

Desde la vista de edición de alumno o desde el listado de alumnos, un profesor o administrador puede pulsar un botón para enviar al alumno un enlace temporal de recuperación de contraseña.

Flujo
	1.	Se genera un token único y temporal
	2.	Se envía un email al alumno con enlace de cambio
	3.	El alumno accede al formulario y establece nueva contraseña
	4.	El token se invalida tras uso o pasados 60 minutos

⸻

🗂️ Tabla tokens_recuperacion

Campo	Tipo	Descripción
id_token	INT PK AI	ID
id_usuario	INT (FK)	Alumno asociado
token	VARCHAR(64)	Token único
fecha_creacion	DATETIME	Cuándo se generó
usado	TINYINT(1)	0 = activo, 1 = consumido/caducado


⸻

🧱 MVC

Componente	Ruta
Botón de reenviar	vistas/usuarios/editar_alumno.php / listado.php
Controlador generador de token	controladores/reenviar_contrasena.php
Vista email HTML	plantillas/emails/recuperar_contrasena.html
Formulario de nueva contraseña	vistas/autenticacion/recuperar_contrasena.php


⸻

🧪 Validaciones UI/UX
	•	Formulario precargado con los datos actuales
	•	Validación inmediata en cambios (correo, contraseña)
	•	Confirmación visual antes de guardar
	•	Tooltips de ayuda por campo
	•	Foto con subida por drop o selector
	•	Iconos representativos (fa-user-edit, fa-envelope, fa-lock, etc.)
	•	Icono para “📧 Reenviar contraseña”

⸻

🛡️ Seguridad
	•	Verificación de rol y propiedad del alumno
	•	CSRF token
	•	Escape de entradas
	•	Control de campos bloqueados si hay actividad
	•	El token solo puede usarse una vez y caduca en 60 minutos
	•	Registro en registro_actividad

⸻

🪵 Manejo de errores y logs
	•	Fallos al actualizar → /almacenamiento/logs/edicion_alumno_error.log
	•	Intento de edición no permitida → registrar en registro_actividad
	•	Reenvío de email con errores → log + mensaje al usuario actual

⸻

🛡️ Fortalezas que Copilot debe implementar
	•	Control visual y backend del formulario completo
	•	Validación estricta de campos modificados
	•	Verificación de actividad antes de permitir edición de email
	•	Hash seguro de contraseña si se cambia (password_hash)
	•	Control de acceso por rol y pertenencia al curso (si profesor)
	•	Logs por error, éxito y reenvíos desde edición
	•	Protección de los tokens y expiración automática de enlaces

⸻

📋 Estándar de tabla interactiva
	•	Acciones fuera de la tabla (crear, borrar, desactivar…)
	•	Selección múltiple por checkbox
	•	Edición directa al hacer clic sobre fila
	•	Fila tipo “pijama”
	•	Separación clara entre filtros y botones de acción
	•	Orden asc/desc en columnas clave
	•	Paginación (5/10/15/20/Todos), por defecto 15

⸻

✅ Checklist Copilot
	•	Mostrar formulario de edición con datos precargados
	•	Validar campos modificados
	•	Bloquear cambio de correo si hay actividad
	•	Registrar cambios en registro_actividad
	•	Controlar acceso según rol
	•	Crear tabla tokens_recuperacion
	•	Agregar botón de envío de enlace de recuperación
	•	Crear controlador y email de envío
	•	Implementar formulario de nueva contraseña
	•	Habilitar selector de imagen desde galería
	•	Mostrar galería filtrada por tipo ‘avatar’ y visibilidad
	•	Insertar ruta en campo foto del usuario
	•	Validar que la imagen seleccionada pertenece al usuario o es global

⸻

📌 A continuación, Copilot debe leer e implementar: 30_gestion_profesores.md