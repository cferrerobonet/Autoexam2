# 02 – Manual de Requisitos del Sistema AUTOEXAM2

Este manual detalla todos los requisitos técnicos, de entorno y validaciones necesarias para implementar y ejecutar AUTOEXAM2 desde cero, asegurando compatibilidad, seguridad y rendimiento óptimo. Incluye checklist, dependencias, validaciones, manejo de errores y fortalezas que Copilot debe garantizar.

---

## 1. Objetivos y Alcance
- Garantizar la ejecución correcta en cualquier entorno Linux estándar compatible con PHP 8.1+
- Validar extensiones, permisos y configuraciones antes de instalar
- Definir requisitos mínimos de cliente y servidor
- Prevenir errores de despliegue y asegurar seguridad desde el inicio

---

## 2. Requisitos Técnicos Mínimos para el Servidor
- **Servidor web:** Apache 2.4 o Nginx 1.18+ (con soporte .htaccess y mod_rewrite)
- **PHP:** 8.1 o superior, con extensiones:
  - mysqli, mbstring, json, openssl, fileinfo, zip, gd
- **Base de datos:** MySQL 8.0+ o MariaDB 10.5+, usuario con permisos completos, charset utf8mb4_unicode_ci
- **Permisos de carpetas:**
  - `/tmp/` → escritura
  - `/publico/subidas/` → escritura
  - `/config/` → lectura/escritura
  - `/documentacion/` → lectura total (para Copilot local)
- **Restricción importante:** en IONOS solo conexiones locales (localhost/127.0.0.1)

---

## 3. Requisitos del Cliente
- Navegador moderno (Chrome, Firefox, Edge, Safari)
- Resolución mínima: 1024x600
- JavaScript habilitado

---

## 4. Herramientas de Desarrollo Recomendadas
- Editor: VS Code + GitHub Copilot Pro
- MySQL GUI: phpMyAdmin, DBeaver, TablePlus
- Navegador con herramientas de desarrollo (F12)

---

## 5. Dependencias Funcionales
- [01_estructura_proyecto.md](01_estructura_proyecto.md)
- [01_presentacion.md](01_presentacion.md)
- [03_instalador.md](03_instalador.md)
- [04_configuracion.md](../09_configuracion_mantenimiento/04_configuracion.md)

---

## 6. Seguridad y Control
- Validar que el servidor no expone rutas fuera de `/publico/`
- El instalador debe crear `.env` con variables críticas y protegidas
- Deshabilitar navegación de directorios en Apache/Nginx
- Usar `.htaccess` en `/config/`, `/tmp/`, `/subidas/` para bloquear acceso web directo

---

## 7. Validación de Datos
- Comprobar versiones de PHP, MySQL y extensiones desde el instalador
- Validar permisos de carpetas con `is_writable()`
- Verificar conexión a base de datos por localhost antes de continuar

---

## 8. Manejo de Errores y Logs
- Mostrar advertencias amigables si faltan extensiones
- Registrar fallos de conexión, permisos o errores fatales en `/almacenamiento/logs/`
- Incluir informe técnico de validación en `registro_actividad` (tipo diagnóstico)

---

## 9. Casos Límite y Comportamiento Esperado
- PHP < 8.0 → advertencia y bloqueo del instalador
- Extensiones faltantes → impedir avanzar al paso 2
- Base de datos existente pero vacía → iniciar instalación correctamente
- Permisos incorrectos → advertencia en el paso 1
- Navegador desactualizado o sin JS → aviso visual

---

## 10. Checklist de Implementación - Estado Actual
- [x] Configuración básica del servidor implementada
- [x] Validación de PHP y sus extensiones mediante archivos de configuración
- [x] Sistema de validación de permisos de carpetas implementado
- [x] Conexión a MySQL con charset utf8mb4 implementada
- [x] Acceso a base de datos por localhost implementado
- [x] Verificación de archivos críticos antes de iniciar el sistema
- [ ] Instalador web completo pendiente de completar
- [ ] Comprobación completa de requisitos en instalador pendiente

---

## 11. Fortalezas que Copilot debe Implementar
- Validación exhaustiva de entorno antes de instalar
- Feedback visual claro ante cualquier error de requisitos
- Registro de todos los fallos técnicos en logs y en `registro_actividad`
- Seguridad en la creación y protección de archivos críticos
- Automatización de comprobaciones en el instalador

---

## 12. Siguiente Paso
📌 A continuación, Copilot debe leer e implementar: [03_instalador.md](03_instalador.md)