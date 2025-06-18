# 59 – Modo mantenimiento y bloqueo temporal

---

## 🎯 Objetivos clave del sistema

- Permitir al administrador activar un estado de “mantenimiento” temporal del sistema  
- Bloquear automáticamente el acceso de profesores y alumnos mientras esté activado  
- Mostrar una vista personalizada o mensaje informativo a los usuarios bloqueados  
- Proteger integridad del sistema durante actualizaciones, backups o restauraciones  
- Registrar en el log cada activación o desactivación del modo

---

## 🧭 Objetivo

Activar una funcionalidad que bloquee el acceso a usuarios que no sean administradores cuando se requiera realizar tareas de mantenimiento en AUTOEXAM2.

---

## 🔗 Dependencias

- `06_configuracion.md`  
- `19_modulo_mantenimiento.md`  
- `58_backup_restauracion.md`

---

## 🧱 Tabla implicada

Se usa una fila extra en `config_sistema` con clave `modo_mantenimiento`.

---

## ⚙️ Funcionalidades

| Acción                  | Responsable | Resultado                                         |
|-------------------------|-------------|--------------------------------------------------|
| Activar mantenimiento   | Admin       | Profesores y alumnos no podrán iniciar sesión    |
| Acceder al panel admin  | Admin       | Permitido con banner de aviso                    |
| Ver pantalla de bloqueo | Usuarios    | Pantalla informativa + contacto de soporte       |
| Desactivar modo         | Admin       | Sistema vuelve a funcionamiento normal           |

---

## 🧪 UI/UX

- Checkbox en `configuracion.php` o botón separado  
- Banner fijo visible para el admin mientras está activo  
- Vista de bloqueo: `mantenimiento.php` con mensaje editable  
- Registrar quién activó y desactivó la función

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación en `verificarSesion()` para redirigir usuarios no admin  
- Control visual global del estado (activo/inactivo)  
- Registro en `registro_actividad`  
- Seguridad: evitar que usuarios burlen la pantalla vía URL  
- Fallback si config no existe o da error

---

## ✅ Checklist Copilot

- [ ] Añadir campo `modo_mantenimiento` en tabla `config_sistema`  
- [ ] Modificar login y `verificarSesion()` para bloquear por rol  
- [ ] Crear vista `mantenimiento.php` con mensaje personalizable  
- [ ] Mostrar banner visible al admin mientras esté activo  
- [ ] Registrar cada cambio en `registro_actividad`

---

📌 A continuación, Copilot debe leer e implementar: 06_configuracion.md
