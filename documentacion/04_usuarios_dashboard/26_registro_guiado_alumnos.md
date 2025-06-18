26 – Registro guiado de alumnos

⸻

🎯 Objetivos clave del sistema
	•	Facilitar el registro de alumnos nuevos por parte del administrador o profesor sin errores
	•	Asegurar que todos los datos requeridos estén completos, validados y almacenados correctamente
	•	Ofrecer una experiencia paso a paso clara y usable
	•	Reforzar la seguridad desde el registro: contraseña, imagen, rol, validación cruzada
	•	Automatizar tareas opcionales como notificación por email

⸻

🧭 Objetivo

Facilitar el alta de nuevos alumnos por parte del administrador o el profesor a través de un asistente paso a paso con validaciones, campos obligatorios, detección de duplicados y ayuda visual.

⸻

🔗 Dependencias
	•	10_modulo_usuarios.md
	•	06_configuracion.md (SMTP si se envía mail de confirmación)
	•	33_exportacion_datos.md (si se importa desde CSV o Excel)

⸻

🔀 Flujo del registro guiado
	1.	Paso 1: Datos personales
	•	Nombre, apellidos, correo electrónico
	•	Validación de email único y formato correcto
	2.	Paso 2: Rol
	•	Selección del rol (solo “alumno” en este módulo)
	•	Vista específica adaptada
	3.	Paso 3: Contraseña
	•	Introducción doble
	•	Requisitos de seguridad (mínimo, complejidad, feedback visual)
	4.	Paso 4: Foto de perfil (opcional)
	•	Drop o selector tradicional
	•	Si no se sube, se asigna user_image_default.png
	5.	Paso 5: Confirmación
	•	Resumen de datos
	•	Botón “Finalizar registro” y envío opcional de bienvenida

⸻

🧪 UI/UX
	•	Navegación paso a paso con validación por pantalla
	•	Feedback en tiempo real
	•	Tooltips con iconos explicativos
	•	Vista responsive optimizada para panel de control
	•	Iconos: fa-user-plus, fa-lock, fa-envelope, fa-user-circle

⸻

🧱 MVC y rutas implicadas

Componente	Ruta
Controlador	controladores/registro_guiado.php
Vistas paso a paso	vistas/admin/registro_guiado/
Validaciones JS/PHP	utilidades/validacion_usuario.php
Imagen temporal subida	publico/subidas/avatar_tmp/


⸻

🔐 Seguridad
	•	Validación backend + frontend
	•	Verificación de duplicado por email
	•	Contraseña cifrada (hash seguro)
	•	Imagen validada por extensión, tamaño y tipo

⸻

🗃️ Tablas y campos implicados

Reutiliza la tabla usuarios con los siguientes campos:

Campo	Tipo	Requerido	Descripción
nombre	VARCHAR(100)	✔️	Nombre del alumno
apellidos	VARCHAR(150)	✔️	Apellidos
correo	VARCHAR(150)	✔️ UNIQUE	Email como identificador
contrasena	VARCHAR(255)	✔️	Cifrada con hash
rol	ENUM	✔️	Siempre “alumno”
foto	TEXT	✖️	Ruta de la imagen
curso_asignado	INT (FK)	✖️	Se asignará más adelante


⸻

🪵 Manejo de errores y logs
	•	Correos duplicados o inválidos → rechazo con mensaje visual + log en /almacenamiento/logs/registro_guiado_error.log
	•	Subida de imagen no válida → log y fallback a imagen por defecto
	•	Registro exitoso → registrado en registro_actividad

⸻

🛡️ Fortalezas que Copilot debe implementar
	•	Validación visual y backend de todos los pasos
	•	Prevención de duplicados por correo electrónico
	•	Cifrado robusto de contraseña (password_hash)
	•	Manejo seguro y temporal de imágenes subidas
	•	Feedback visual y tooltips en campos complejos
	•	Envío opcional de correo (si SMTP está activo)
	•	Registro completo en registro_actividad

⸻

✅ Checklist Copilot
	•	Crear controlador registro_guiado.php
	•	Implementar vista paso a paso (multi-step)
	•	Validar campos en cada paso con JS y PHP
	•	Guardar alumno en tabla usuarios
	•	Enviar email de bienvenida (opcional)
	•	Usar imagen por defecto si no se sube ninguna

⸻

📌 A continuación, Copilot debe leer e implementar: 27_edicion_alumnos.md