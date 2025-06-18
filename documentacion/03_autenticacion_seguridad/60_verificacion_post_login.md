# 60 – Verificación automática post-login

---

## 🎯 Objetivos clave del sistema

- Detectar en el login si el usuario tiene configuraciones clave incompletas  
- Alertar de forma visual si faltan curso, módulo, correo o rol mal asignado  
- Impedir el acceso si hay incoherencias graves (rol mal definido, sin curso, etc.)  
- Reforzar la seguridad y la consistencia del sistema desde el primer acceso  
- Registrar todos los accesos incompletos para control administrativo

---

## 🧭 Objetivo

Validar automáticamente al hacer login que los usuarios tengan todos los elementos necesarios para operar correctamente dentro del sistema según su rol.

---

## 🔗 Dependencias

- `05_autenticacion.md`  
- `23_sesiones_activas.md`  
- `10_modulo_usuarios.md`  
- `12_modulo_cursos.md`  

---

## ⚙️ Comprobaciones obligatorias

| Verificación                          | Aplicable a | Acción                           |
|--------------------------------------|-------------|----------------------------------|
| ¿Tiene curso asignado?               | Alumno      | Redirigir a mensaje de error     |
| ¿Su rol es válido ('admin'...)       | Todos       | Redirigir al logout              |
| ¿Tiene correo válido y único?        | Todos       | Mostrar alerta                   |
| ¿Tiene módulo asignado (profesor)?   | Profesor    | Mostrar advertencia visual       |

---

## 🧪 UI/UX

- Alerta visual roja en dashboard si falta curso/módulo  
- Redirección con mensaje si el acceso es crítico  
- Registro de advertencias con icono de ⚠️  
- Modal con sugerencia de contactar con administración  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación inmediata tras login  
- Acceso bloqueado si `$_SESSION[rol]` inválido  
- Evaluación de los campos clave del usuario y su curso/módulo  
- Registro en `registro_actividad` si hay incoherencias  
- Módulo reutilizable desde `verificarSesion()`  
- Logs separados por tipo de advertencia: `/almacenamiento/logs/login_alertas.log`

---

## ✅ Checklist Copilot

- [ ] Añadir verificación extendida en `verificarSesion()`  
- [ ] Redirigir o bloquear acceso según gravedad  
- [ ] Mostrar alertas visuales según lo que falte  
- [ ] Registrar el incidente en `registro_actividad` y log  
- [ ] Marcar usuario como “incompleto” si le falta algo

---

📌 A continuación, Copilot debe leer e implementar: 10_modulo_usuarios.md
