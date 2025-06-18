# AUTOEXAM2 – Manual de Implementación y Seguridad
URL: https://autoexam.epla.es
Última actualización: 14 de junio de 2025
---

## 🎯 Objetivos del sistema
- Gestión integral de exámenes online (test y desarrollo)
- Paneles diferenciados por rol (admin, profesor, alumno)
- Seguridad avanzada, logs, backups y trazabilidad
- Implementación reproducible y modular, lista para Copilot

---

## 📚 Documentación oficial

La documentación está organizada en módulos exhaustivos en `/documentacion/`. Para facilitar la navegación, ahora contamos con un índice completo:

- [Índice de Documentación](documentacion/indice_documentacion.md) 🆕 (Nuevo, 14/06/2025)
- [Estado actual de implementación](documentacion/00_estado_implementacion.md) ✅ (Actualizado 14/06/2025)

### Documentos Unificados (Nuevos)
- [Autenticación y Recuperación Unificado](documentacion/03_autenticacion_seguridad/autenticacion_y_recuperacion_unificado.md) 🆕 (Nuevo, 14/06/2025)
- [Estructura de Almacenamiento Unificado](documentacion/09_configuracion_mantenimiento/estructura_almacenamiento_unificado.md) 🆕 (Nuevo, 14/06/2025)

### Documentos Principales
- [Estructura y presentación](documentacion/01_estructura_presentacion/01_estructura_proyecto.md) ✅
- [Requisitos y dependencias](documentacion/01_estructura_presentacion/02_requisitos_sistema.md) ✅
- [Instalador seguro](documentacion/01_estructura_presentacion/03_instalador.md) ⚠️ (En progreso)
- [Autenticación y seguridad](documentacion/03_autenticacion_seguridad/05_autenticacion.md) ✅ (Implementado)
- [Recuperación de contraseñas](documentacion/03_autenticacion_seguridad/11_recuperacion_contrasena.md) ✅ (Implementado)
- [Refactorización recuperación](documentacion/03_autenticacion_seguridad/25_refactorizacion_recuperacion.md) ✅ (Nueva, 13/06/2025)
- [Usuarios y dashboard](documentacion/04_usuarios_dashboard/11_modulo_usuarios.md) ⚠️ (Parcialmente implementado)
- [Configuración y mantenimiento](documentacion/09_configuracion_mantenimiento/06_configuracion.md) ✅

Consulta el índice completo y estado de implementación en `/documentacion/00_estado_implementacion.md`.

---

## 🗂️ Estructura del proyecto

```
AUTOEXAM2/
├── app/                    # Lógica MVC
├── config/                 # Configuración y .env
│   ├── config.php          # Configuración principal
│   └── storage.php         # Gestión de almacenamiento
├── .env.example            # Plantilla de variables de entorno
├── .env                    # Variables de entorno (crear a partir de .env.example)
├── almacenamiento/         # Estructura centralizada de archivos
│   ├── logs/               # Logs (app, errores, acceso, sistema)
│   ├── cache/              # Caché de aplicación
│   ├── tmp/                # Archivos temporales
│   ├── subidas/            # Archivos subidos por usuarios
│   └── copias/             # Copias de seguridad
├── publico/                # Archivos accesibles web
├── herramientas/           # Scripts de administración
│   ├── gestor.sh           # Script maestro de herramientas
│   └── mantenimiento/      # Scripts de limpieza y mantenimiento
├── documentacion/          # Manuales exhaustivos por módulo
└── index.php, README.md, ...
```

---

## 👤 Roles y acceso

| Rol          | Panel         | Acceso principal                       |
|--------------|--------------|----------------------------------------|
| Administrador| `/admin/`    | Gestión global, configuración, backup  |
| Profesor     | `/admin/`    | Exámenes, calificaciones, alumnos      |
| Alumno       | `/publico/`  | Realiza exámenes, ve resultados        |

---

## 🔐 Seguridad y validaciones
- Control de sesión y rol en cada acceso
- CSRF y validación cruzada en formularios
- Hash seguro de contraseñas
- Logs por módulo, error, IP y rol
- Sesión única y bloqueo por mantenimiento

---

## 🛠️ Instalación y despliegue
1. Subir archivos al servidor
2. Acceder a `/instalador/` y seguir los pasos guiados
3. El sistema se bloquea tras la instalación

---

## 📦 Backup, restauración y exportación
- Backup completo en `.zip` (SQL + archivos)
- Restauración y verificación desde panel admin
- Exportación de datos y logs por módulo

---

## 🔁 Pruebas y QA
- Pruebas unitarias y de integración (módulo 64)
- Validación QA por módulo (módulo 57)
- Registro de errores y advertencias en logs

---

## 🤖 Instrucciones para Copilot
- Leer cada `.md` por orden de numeración
- Seguir checklist y fortalezas de cada manual
- Validar logs, seguridad y reproducibilidad
- Usar PHP moderno y buenas prácticas
- Documentar inline todo el código generado

---

## 📋 Estado de la documentación
- ✅ Manuales exhaustivos y numerados por módulo
- ✅ Enlaces internos y dependencias actualizados
- ✅ Listo para desarrollo reproducible y asistido por IA

---

## 🧹 Limpieza y mantenimiento
- Scripts automatizados de mantenimiento disponibles
- Estructura unificada de almacenamiento (13/06/2025)
- Limpieza periódica de logs y archivos temporales
- Documentación actualizada a 13/06/2025

Para ejecutar herramientas de mantenimiento:
```bash
./herramientas/gestor.sh
# Seleccionar opción 4 - Mantenimiento
```

---

## 🔧 Configuración del sistema

### Variables de Entorno

El sistema utiliza un archivo `.env` para gestionar la configuración y datos sensibles. Para facilitar la configuración:

1. Copie el archivo `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edite el archivo `.env` para configurar:
   - Credenciales de la base de datos
   - Configuración SMTP para correos
   - Credenciales del administrador
   - Nombre del sistema y URLs
   - Parámetros de seguridad

Todos los valores sensibles (contraseñas, URLs, nombres de sistema, etc.) se obtienen de este archivo, evitando datos hardcodeados en el código.

### Constantes de Configuración

Las constantes definidas en `config/config.php` leen los valores del archivo `.env` y proporcionan valores por defecto en caso de que la variable no esté definida:

```php
define('SYSTEM_NAME', Env::obtener('SYSTEM_NAME', 'Nombre por defecto'));
```

Para añadir nuevas configuraciones:
1. Agregue la variable en `.env.example` y `.env`
2. Defina la constante en `config/config.php`
3. Use la constante en lugar de cadenas literales

---

📌 Para comenzar la implementación, inicia por: [01_estructura_proyecto.md](documentacion/01_estructura_presentacion/01_estructura_proyecto.md)
