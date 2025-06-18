# 65 – Control de versiones internas y rollback por módulo

---

## 🎯 Objetivos clave del sistema

- Permitir al administrador rastrear y revertir cambios críticos en configuración y estructura  
- Guardar versiones históricas de elementos clave (SMTP, backups, configuración general)  
- Facilitar un rollback automático o manual en caso de fallo o error humano  
- Asegurar trazabilidad completa de quién cambió qué y cuándo  
- Incrementar la resiliencia y auditabilidad del sistema

---

## 🧭 Objetivo

Agregar un sistema interno que guarde versiones previas de configuraciones sensibles y permita restaurarlas desde el panel administrativo.

---

## 🔗 Dependencias

- `06_configuracion.md`  
- `58_backup_restauracion.md`  
- `41_registro_actividad.md`

---

## 🧱 Tablas adicionales

### Tabla: `config_versiones`

| Campo         | Tipo         | Descripción                                  |
|---------------|--------------|----------------------------------------------|
| id_version    | INT PK AI    | ID de la versión guardada                    |
| tipo          | ENUM         | 'smtp', 'ftp', 'sistema', 'mantenimiento'    |
| json_config   | TEXT         | Configuración completa en JSON               |
| fecha_guardado| DATETIME     | Cuándo se guardó                             |
| guardado_por  | INT FK       | Usuario que realizó el cambio                |

---

## 🧪 UI/UX

- Tabla con historial en `admin/configuracion.php`  
- Botón “Restaurar esta versión”  
- Resumen de diferencias entre versión activa y antigua  
- Confirmación visual antes de aplicar rollback  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Serialización JSON completa de cada bloque de configuración  
- Registro seguro e inalterable de cambios por usuario y hora  
- Función de restauración con validación previa  
- Fallback automático si una restauración falla  
- Logs técnicos de cambios y errores en `/almacenamiento/logs/versionado.log`

---

## ✅ Checklist Copilot

- [ ] Crear tabla `config_versiones`  
- [ ] Serializar configuración en cada cambio (como copia)  
- [ ] Mostrar historial en interfaz admin  
- [ ] Permitir comparar y restaurar configuraciones  
- [ ] Registrar cada versión en `registro_actividad`  
- [ ] Log técnico separado por errores de rollback  

---

📌 A continuación, Copilot debe leer e implementar: 06_configuracion.md
