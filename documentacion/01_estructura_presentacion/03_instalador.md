# 03 – Manual de Implementación del Instalador de AUTOEXAM2

Este manual describe, paso a paso, la implementación y validación del instalador automático de AUTOEXAM2, tanto en su versión web como por línea de comandos. Incluye estructura, dependencias, lógica, checklist y fortalezas que Copilot debe garantizar para reproducibilidad y seguridad total.

---

## 1. Objetivo
- Permitir la instalación guiada y segura de AUTOEXAM2 en cualquier entorno compatible.
- Generar archivos de configuración y usuario administrador inicial.
- Validar requisitos, permisos y estructura antes de finalizar.

---

## 2. Dependencias Previas
- [01_estructura_proyecto.md](01_estructura_proyecto.md)
- [02_requisitos_sistema.md](02_requisitos_sistema.md)
- Script SQL: `00_sql/autoexam2.sql`
- [04_configuracion.md](../09_configuracion_mantenimiento/04_configuracion.md)

---

## 3. Estructura de Archivos del Instalador
```
publico/instalador/
├── index.php                # Interfaz web principal
├── db_verify.php            # Verificación de BD
├── admin_verify.php         # Verificación de admin
├── funciones_tablas.php     # Gestión de tablas
├── instalacion_completa.php # Instalación end-to-end
├── actualizar_tablas.php    # Actualización de tablas
├── ftp-handler.js           # UI para FTP/SFTP
```

---

## 4. Tablas y Archivos Clave
- Tabla `usuarios` (ver [11_modulo_usuarios.md](../04_usuarios_dashboard/11_modulo_usuarios.md))
- Archivo `.env` generado automáticamente con parámetros sensibles:
  - DB_HOST, DB_NAME, DB_USER, DB_PASS, SMTP_HOST, SMTP_USER, SMTP_PASS

---

## 5. Proceso de Instalación
1. Validar requisitos y permisos ([02_requisitos_sistema.md](02_requisitos_sistema.md)).
2. Descargar y descomprimir AUTOEXAM2.
3. Renombrar y editar `config/config.php`.
4. Importar `autoexam2.sql` en la base de datos.
5. Asignar permisos de escritura a carpetas críticas.
6. (Opcional) Ejecutar `composer install` si hay dependencias PHP.
7. Acceder a `/instalador/` y completar el proceso guiado.
8. Crear usuario administrador inicial.

---

## 6. Validaciones y Seguridad
- Validar conexión a BD y permisos antes de continuar.
- Comprobar existencia y escritura de archivos críticos.
- Generar `.env` y `config.php` con permisos seguros.
- Registrar todos los eventos en `registro_actividad`.
- Eliminar o renombrar `/instalador/` tras la instalación.

---

## 7. Checklist de Implementación - Estado Actual
- [x] Verificación inicial de requisitos y archivos críticos
- [x] Estructura base para archivos de configuración y `.env` implementada
- [x] Clase Env para manejo de variables de entorno implementada
- [x] Detección automática de entorno (desarrollo/producción)
- [x] Registro básico de eventos mediante logs
- [ ] Instalador web completo pendiente de finalizar
- [ ] Creación de usuario administrador inicial pendiente
- [ ] Sistema para generar/importar estructura de BD pendiente
- [ ] Función para eliminar/renombrar `/instalador/` pendiente

---

## 8. Fortalezas que Copilot debe Implementar
- Validación exhaustiva de entorno y permisos.
- Generación segura de archivos críticos.
- Feedback visual claro ante errores o advertencias.
- Registro de todos los pasos y errores en logs y `registro_actividad`.
- Protección contra instalaciones incompletas o repetidas.

---

## 9. Siguiente Paso
📌 A continuación, Copilot debe leer e implementar: [04_configuracion.md](../09_configuracion_mantenimiento/04_configuracion.md)
