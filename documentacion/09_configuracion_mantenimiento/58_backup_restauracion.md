# 58 – Backup y restauración del sistema

---

## 🎯 Objetivos clave del sistema

- Proteger los datos críticos del sistema AUTOEXAM2 frente a errores o pérdidas  
- Permitir realizar copias de seguridad completas o parciales bajo demanda o programadas  
- Facilitar la restauración guiada de una copia previamente realizada  
- Asegurar que las copias se generan con integridad y se almacenan en un lugar seguro  
- Registrar todas las acciones de backup y restauración en logs y en el historial de actividad

---

## 🧭 Objetivo

Permitir que el administrador realice copias de seguridad completas de la base de datos y carpetas esenciales (configuración, subidas, logs) y pueda restaurarlas posteriormente de forma manual o automática.

---

## 🔗 Dependencias

- `06_configuracion.md`  
- `07_instalador.md`  
- `45_verificacion_integridad_sistema.md`

---

## 📂 Elementos incluidos en el backup

| Elemento                    | Detalles                                             |
|-----------------------------|------------------------------------------------------|
| Base de datos               | Dump en SQL completo (`autoexam2.sql`)              |
| Archivos de configuración   | `.env`, SMTP/SFTP desde carpeta `/config/`          |
| Subidas multimedia          | `/subidas/avatars/`, `/subidas/pdfs/`, `/imagenes/` |
| Logs del sistema            | Carpeta `/almacenamiento/logs/`                                 |

---

## 📦 Estructura del backup generado

```plaintext
backup_YYYYMMDD_HHMMSS.zip
├── autoexam2.sql
├── config/
├── subidas/
├── tmp/logs/
```

---

## 🔄 Restauración

- Vista segura de restauración desde rol administrador  
- Permite subir archivo `.zip` con backup válido  
- Extrae y repone archivos y base de datos  
- Verificación final del estado del sistema  

---

## 🧪 UI/UX

- Botón “Crear backup ahora” con confirmación visual  
- Botón “Subir y restaurar backup” con validación  
- Historial de backups recientes realizados  
- Feedback visual: barra de progreso, alertas de error o éxito  

---

## 🛡️ Seguridad

- Accesible solo para administrador  
- Verificación de integridad del `.zip` antes de restaurar  
- Protección contra sobrescritura de archivos críticos  
- Logs de acceso, errores y operaciones exitosas  

---

## 🪵 Manejo de errores y logs

- Fallos en backup → log en `/almacenamiento/logs/backup_error.log`  
- Fallos en restauración → `/almacenamiento/logs/restauracion_error.log`  
- Operaciones exitosas → registradas en `registro_actividad`  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación del contenido antes de permitir restauración  
- Generación del archivo `.zip` con nombre y contenido estructurado  
- Logs diferenciados por tipo de operación  
- Confirmación de permisos de escritura antes de exportar  
- Prevención contra ejecución de scripts desde `.zip` cargado  
- Protección de credenciales y archivos `.env` durante extracción  
- Registro detallado de hora, usuario y resultado de cada operación

---

## ✅ Checklist Copilot

- [ ] Crear generador de backup en `utilidades/crear_backup.php`  
- [ ] Comprimir estructura estándar en `.zip`  
- [ ] Vista para seleccionar y subir backup  
- [ ] Validar contenido antes de restaurar  
- [ ] Registrar operaciones en `registro_actividad` y logs  
- [ ] Proteger con rol administrador y CSRF  

---

📌 Con esto finaliza el módulo 58. Copilot puede considerar completada la implementación del sistema.
