# 06 – Configuración avanzada en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Centralizar todos los parámetros técnicos críticos del sistema  
- Facilitar la administración de SMTP, SFTP, imágenes, errores y copias de seguridad  
- Garantizar trazabilidad y seguridad en cada cambio aplicado  
- Automatizar tareas de mantenimiento y exportación de registros  
- Ofrecer pruebas de conexión en tiempo real con feedback inmediato  

---

## 🧭 Objetivo

Gestionar las configuraciones avanzadas del sistema, incluyendo SMTP, SFTP, ajustes generales, manejo de errores, registros de logs y gestión de backups.

---

## 🔗 Dependencias

- `03_instalador.md` (Parcialmente implementado)
- `33_exportacion_datos.md` (Pendiente)
- `41_registro_actividad.md` (Implementado parcialmente mediante logs)

---

## 📊 Funcionalidades y estado de implementación

| Funcionalidad              | Descripción                                                  | Estado |
|---------------------------|--------------------------------------------------------------|--------|
| Configuración SMTP        | Selección y validación de proveedores, pruebas de conexión  | ⚠️ Parcial (definido en .env) |
| Configuración SFTP/FTP    | Datos de acceso, prueba y modificación desde panel admin     | ⚠️ Parcial (definido en .env) |
| Ajustes generales         | Parámetros globales de la aplicación                         | ✅ Implementado (config.php y .env) |
| Manejo de errores y logs  | Registro de errores y eventos importantes                    | ✅ Implementado (archivos log) |
| Backup y restauración     | Procedimientos y programación de copias de seguridad        | ❌ Pendiente |

---

## 🧪 Estado actual de implementación

### Implementado
- ✅ Carga de configuraciones desde archivo .env mediante la clase Env
- ✅ Configuración centralizada en config.php
- ✅ Sistema de logs para errores y eventos
- ✅ Detección automática del entorno (desarrollo/producción)
- ✅ Registro básico de actividad en archivos

### Parcial o en progreso
- ⚠️ Configuración SMTP definida en variables pero sin interfaz de administración
- ⚠️ Configuración FTP/SFTP definida en variables pero sin interfaz de administración
- ⚠️ Manejo de archivos pendiente de mejorar

### Pendiente
- ❌ Interfaz de administración para configuración
- ❌ Formularios con validaciones en tiempo real
- ❌ Feedback visual para éxito o error en conexión
- ❌ Botones de prueba y verificación de configuraciones
- ❌ Backup y restauración

---

## 🧱 MVC y rutas implicadas

| Componente          | Ruta                                    |
|---------------------|-----------------------------------------|
| Controlador         | `controladores/configuracion.php`        |
| Vista               | `vistas/admin/configuracion.php`         |
| Utilidades          | `utilidades/logger.php`, `utilidades/backup.php` |

---

## 🗃️ Tablas implicadas

- `config_sistema`
- `config_versiones`
- `registro_actividad`

---

## 🔐 Seguridad y control de sesiones

- Acceso restringido a administradores  
- Validación estricta de datos antes de guardar  
- Registro de cambios en configuración en logs  
- Protección de credenciales almacenadas (encriptación recomendada)  

---

## 🧪 Validación de datos

- Validación de campos obligatorios, formatos de email, puertos, etc.  
- Sanitización de entradas para evitar inyección  
- Mensajes claros para errores de configuración  

---

## ⚠️ Manejo de errores y logs

- Registro de fallos en conexión SMTP/SFTP  
- Notificación visual en panel si configuraciones fallan  
- Backup automático de configuraciones antes de cambios  

---

## 🧪 Pruebas y casos límite

- Comprobar conexiones SMTP/SFTP con datos correctos e incorrectos  
- Probar subida de imágenes en distintos formatos y tamaños  
- Verificar que cambios se registran en logs correctamente  
- Validar recuperación correcta ante errores de conexión  

---

## 🎨 Multimedia y subida de archivos

- Límites de tamaño máximo para logo y avatares (ejemplo: 5MB)  
- Formatos permitidos: jpg, png, gif, pdf para documentos  
- Sanitización y verificación de tipo de archivo  
- Prevención contra ejecución de código malicioso  

---

## 💾 Exportación y backup

- Backup programado y manual de configuraciones  
- Exportación de logs de error y actividad al formato CSV o XLSX  

---

## 🔒 Sistema de Variables de Entorno

### Configuración Segura
AUTOEXAM2 implementa un sistema completo de gestión de configuración mediante variables de entorno:

#### Archivo .env
```bash
# Base URL del sistema
BASE_URL=https://autoexam.tudominio.com

# Configuración de base de datos
DB_HOST=localhost
DB_NAME=autoexam2
DB_USER=usuario_bd
DB_PASS=contraseña_segura

# Configuración SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@tudominio.com
SMTP_PASS=contraseña_correo
SMTP_SECURE=tls

# Configuración FTP/SFTP
FTP_HOST=ftp.tudominio.com
FTP_PORT=21
FTP_USER=usuario_ftp
FTP_PASS=contraseña_ftp
FTP_SECURE=false

# Configuración de seguridad
HASH_COST=12
SESSION_LIFETIME=7200
DEBUG=false
```

#### Biblioteca Env
La clase `Env` en `app/utilidades/env.php` proporciona:
- Carga segura de variables desde archivo `.env`
- Conversión automática de tipos (booleanos, números)
- Valores por defecto para variables faltantes
- Compatibilidad con `$_ENV` y `putenv()`

Para una documentación completa de la implementación de la clase Env, consulte:
[Clase Env - Documentación completa](clase_env.md)

### Beneficios del Sistema
- ✅ **Seguridad**: Datos sensibles separados del código fuente
- ✅ **Flexibilidad**: Configuración diferente por entorno
- ✅ **Centralización**: Todas las configuraciones en un lugar
- ✅ **Autocompletado**: El instalador pre-rellena campos automáticamente

---

## 🛠️ Herramientas Administrativas

### Gestor Maestro
Sistema completo de herramientas administrativas accesible mediante:
```bash
./herramientas/gestor.sh
```

### Herramientas de Configuración Disponibles
- **configurar_cron.sh**: Configuración automática de tareas de monitorización
- **migrar_configuracion.php**: Migración de configuración legacy a sistema .env
- **monitor_instalador.php**: Monitorización continua del sistema
- **validacion_produccion.php**: Validación completa del entorno

### Suite de Testing
- **test_env.php**: Validación de variables de entorno
- **test_deteccion_instalacion.php**: Test de instalación previa
- **test_autocompletado.php**: Test de autocompletado del instalador
- **tests_integracion.php**: Suite completa de 63 tests

Ver documentación completa en: `documentacion/09_configuracion_mantenimiento/herramientas_administrativas.md`

---

## 🛡️ Estado de implementación de características clave

### Implementado
- ✅ Sistema de variables de entorno con `Env`
- ✅ Registro básico de logs en `/almacenamiento/logs/`
- ✅ Detección automática de entorno (desarrollo/producción)
- ✅ Configuración centralizada en archivo `.env`
- ✅ Valors por defecto para parámetros no definidos
- ✅ Restricción de acceso basada en sesiones

### Pendiente
- ❌ Interfaz para editar configuración
- ❌ Validación estricta de formularios de conexión y rutas
- ❌ Protección CSRF avanzada en subida de archivos
- ❌ Encriptación de contraseñas SMTP/SFTP almacenadas
- ❌ Logs detallados por error en `/almacenamiento/logs/configuracion_error.log`
- ❌ Registro completo de cambios en base de datos `registro_actividad`
- ❌ Prevención de inyección y manipulación directa de la vista

---

## ✅ Checklist Copilot

- [ ] Implementar formularios de configuración SMTP y SFTP con validación  
- [ ] Añadir funciones para probar conexiones en tiempo real  
- [ ] Gestionar subida y cambio de imágenes con seguridad  
- [ ] Registrar todos los cambios y errores en registro_actividad  
- [ ] Crear sistema de backup y restauración de configuraciones  
- [ ] Validar exhaustivamente todos los datos de entrada  

---

📌 A continuación, Copilot debe leer e implementar: 19_modulo_mantenimiento.md
