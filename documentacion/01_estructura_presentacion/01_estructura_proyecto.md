# 00 – Estructura del Proyecto AUTOEXAM2

Este documento define la arquitectura base y las convenciones de AUTOEXAM2. Es la referencia principal para reproducir la estructura, la seguridad y la lógica del sistema. Todo desarrollo debe respetar y actualizar este documento.

---

## 🧭 Objetivo

Definir de forma detallada la arquitectura base de AUTOEXAM2 para que cualquier desarrollador o sistema de ayuda (incluido GitHub Copilot) pueda comprender, construir y validar todo el ecosistema del proyecto sin ambigüedades. Esta estructura define convenciones, carpetas, seguridad global, dependencias, comportamiento del servidor y requisitos mínimos de funcionamiento.

---

## 🔗 Dependencias funcionales clave

- `02_requisitos_sistema.md`: requisitos mínimos del servidor (Implementado)
- `06_configuracion.md`: configuración avanzada del sistema (Implementado)
- `03_instalador.md`: responsable de generar estructura inicial (Pendiente de implementación completa)
- `33_exportacion_datos.md`: debe respetar estructura MVC y acceso a carpetas (Pendiente)
- `41_registro_actividad.md`: activo desde el inicio para auditar acciones (Implementado parcialmente mediante logs)

---

## 🧱 Tecnologías utilizadas

| Área         | Tecnología                        |
|--------------|-----------------------------------|
| Backend      | PHP 8.1+                          |
| Base de datos| MySQL 8.0                         |
| Frontend     | HTML5, CSS3, JavaScript ES6+      |
| CSS Framework| Bootstrap 5                       |
| Iconografía  | FontAwesome 6                     |
| Exportación  | PhpSpreadsheet, TCPDF             |
| Seguridad    | Sesiones seguras, CSRF, logs      |
| Testing      | QA manual + pruebas documentadas  |

---

## 📁 Estructura de carpetas general

```
AUTOEXAM2/
├── app/
│   ├── controladores/          # Lógica de negocio por módulo
│   ├── modelos/                # Acceso a datos y entidades
│   ├── vistas/                 # Vistas separadas por rol o módulo
│   └── utilidades/             # Scripts comunes, logs, helpers
├── config/                     # Configuración BD, correo, variables del sistema
├── documentacion/              # Archivos .md de diseño y especificación
├── publico/                    # Punto de entrada web (raíz del dominio)
│   ├── instalador/             # Instalador guiado del sistema (accesible vía https://autoexam.epla.es/instalador)
│   ├── diagnostico/            # Herramientas de diagnóstico web (accesible vía https://autoexam.epla.es/diagnostico)
│   │   ├── index.html          # Página principal de herramientas de diagnóstico
│   │   ├── test_correo.php     # Test completo de configuración SMTP y envío de correos
│   │   ├── test_simple_correo.php  # Test básico de envío de correos
│   │   ├── test_recuperacion_completa.php  # Test del flujo completo de recuperación de contraseña
│   │   ├── test_bd.php         # Test de conectividad y estado de la base de datos
│   │   ├── base_datos/         # Herramientas específicas de base de datos
│   │   ├── rendimiento/        # Herramientas de análisis de rendimiento
│   │   └── sistema/            # Herramientas de diagnóstico del sistema
│   ├── estilos/                # CSS personalizados
│   ├── iconos/                 # Imágenes públicas (logos, avatars)
│   ├── scripts/                # JS frontend
│   └── subidas/                # Archivos cargados por usuarios
├── tmp/                        # Archivos temporales, backups, PDFs
├── herramientas/               # Herramientas administrativas organizadas por categorías
│   ├── gestor.sh               # Script maestro con menú interactivo
│   ├── README.md               # Documentación de las herramientas
│   ├── seguridad/              # Herramientas de seguridad (configuración, monitoreo, validación)
│   │   ├── configuracion/      # Scripts de configuración automática de cron
│   │   ├── migracion/          # Scripts de migración de configuración
│   │   ├── monitoreo/          # Monitores de seguridad 24/7
│   │   ├── testing/            # Suite de tests de integración
│   │   └── validacion/         # Validadores de producción
│   ├── administracion/         # Herramientas de administración (usuarios, permisos)
│   └── mantenimiento/          # Herramientas de mantenimiento (backup, limpieza)
└── index.php                   # Entrada principal del sistema (MVC activado)
```

El dominio https://autoexam.epla.es apunta directamente a la carpeta `/publico/`. Todo lo fuera de `/publico/` queda protegido (acceso solo interno).

### 🩺 Nota sobre Herramientas de Diagnóstico

Las herramientas de diagnóstico están ubicadas en `/publico/diagnostico/` (accesibles vía web) en lugar de `/herramientas/diagnostico/` por las siguientes razones:

1. **Hostings restrictivos**: Proveedores como IONOS no permiten acceso SSH, haciendo necesario el acceso web para diagnóstico en producción
2. **Interfaz mejorada**: Versión web con formularios HTML para facilitar las pruebas
3. **Diagnóstico en tiempo real**: Permite verificar configuración SMTP, base de datos y recuperación de contraseña directamente en el entorno de producción

**Seguridad**: Estas herramientas deben usarse solo para diagnóstico temporal y pueden restringirse por IP o protegerse con contraseña según las necesidades de seguridad.

---

## 🧩 Arquitectura lógica y convenciones

- Patrón de diseño: MVC modular desacoplado
- Convenciones de nomenclatura:
  - Archivos: snake_case
  - Variables y funciones: camelCase
  - Clases: PascalCase
  - Constantes: MAYUS_CON_GUIONES
  - Idioma: Español neutro, sin tildes ni “ñ” en nombres de archivos, funciones, variables o carpetas
  - Comentarios en el código siempre en español técnico claro

---

## 🛡️ Seguridad y control de sesiones

- ✅ Rutas protegidas con validación de sesión activa mediante el ruteador
- ✅ Vistas protegidas con `session_start()` y control de acceso
- ✅ Tokens CSRF implementados en formularios de autenticación
- ✅ Contraseñas cifradas (`password_hash()` + `password_verify()`)
- ⚠️ Logs de actividad implementados parcialmente (archivos log en `/almacenamiento/logs/`)
- ⚠️ Control de sesión única por usuario parcialmente implementado
- ❌ Expulsión de sesiones paralelas pendiente

---

## ✅ Validación de datos

- Validación doble (frontend con JS y backend con PHP)
- Escapado de campos para prevenir XSS
- Filtros de tipo, longitud, unicidad y consistencia relacional

---

## 🪵 Manejo de errores y logs

- Registro de todos los errores funcionales y de sistema en `registro_actividad`
- Archivos de log en `/almacenamiento/logs/` para diagnóstico extendido
- Mostrar solo mensajes genéricos al usuario para evitar filtración de información

---

## 🧪 Pruebas y casos límite

- Comprobación de ruta de cada carpeta crítica
- Intentos de acceso a `/app/` directamente → denegados
- Manipulación de GET/POST → redirigir o bloquear con log
- Cambios manuales de URLs → comprobar rol y permisos

---

## 🧾 Archivos críticos iniciales para generar

- `/index.php`
- `/app/controladores/ruteador.php`
- `/app/utilidades/sesion.php`
- `/config/config.php` + `.env.example`
- `/publico/iconos/logo.png`, `user_image_default.png`
- `/publico/estilos/formulario.css`

---

## 🔐 Fortalezas obligatorias que debe implementar Copilot

- Tokens CSRF en todos los formularios que gestionen datos
- Validación doble de datos (cliente y servidor)
- Control de sesión única por usuario
- Registro obligatorio en `registro_actividad` de cada acción relevante
- Protección de rutas, vistas y controladores por rol y sesión
- Cifrado seguro de contraseñas
- Trazabilidad completa desde el primer acceso
- Validación y sanitización de entradas para evitar XSS, SQLi y ataques comunes
- Protección de archivos en `/subidas/`, `/config/`, `/tmp/` con rutas absolutas no accesibles por el navegador
- Reforzar integridad en MVC (cada acción debe validarse antes de ejecutarse)

---

## ✅ Checklist para Copilot

- Crear estructura MVC completa como se indica
- Aplicar todas las convenciones y roles definidos
- Validar acceso a `/publico/` y restringir lo demás
- Añadir seguridad, validación, logs y trazabilidad
- Mantener todo el sistema documentado en español técnico neutro
- Actualizar este archivo si se modifica la arquitectura
- Usar este documento como fuente de verdad para el proyecto completo

---

## 🔧 Herramientas Administrativas

### Gestor Maestro

AUTOEXAM2 incluye un sistema completo de herramientas administrativas organizadas por categorías y accesibles a través de un gestor maestro interactivo:

```bash
# Ejecutar el gestor maestro
./herramientas/gestor.sh
```

**Características del gestor:**
- Menú interactivo colorizado
- Navegación por categorías (Seguridad, Administración, Diagnóstico, Mantenimiento)
- Ejecución segura de herramientas individuales
- Suite completa de seguridad automatizada
- Estado del sistema en tiempo real

#### Herramientas de Seguridad Implementadas

1. **configurar_cron.sh**: Configuración automática de tareas de monitorización
2. **migrar_configuracion.php**: Migración automatizada con backup y dry-run
3. **monitor_instalador.php**: Monitor de seguridad con alertas por email
4. **tests_integracion.php**: Suite de 63 tests de validación
5. **test_env.php**: Validación de biblioteca de variables de entorno
6. **test_deteccion_instalacion.php**: Test de detección de instalación previa
7. **test_autocompletado.php**: Test de autocompletado del instalador
8. **validacion_produccion.php**: Validador completo del entorno de producción

---

## 🌱 Sistema de Variables de Entorno

AUTOEXAM2 implementa un sistema completo de gestión de variables de entorno para separar la configuración sensible del código fuente:

- **Biblioteca Env** (`app/utilidades/env.php`): Gestión completa de variables de entorno
- **Archivo .env**: Configuración centralizada y segura
- **Plantilla .env.example**: Referencia para configuración
- **Control de instalación previa**: Verificación automática de archivos críticos
- **Autocompletado**: El instalador pre-rellena campos automáticamente desde configuración existente

---

## 📋 Estado de la Documentación

### Integración Completada ✅

La documentación de AUTOEXAM2 ha sido completamente integrada y consolidada en la estructura oficial. Los siguientes archivos redundantes han sido eliminados exitosamente:

**Archivos MD eliminados:**
- `documentacion/02_administracion/guia_configuracion_segura.md`
- `documentacion/03_tecnica/implementacion_seguridad.md`
- `INTEGRACION_DOCUMENTACION_COMPLETADA.md`

**Directorios eliminados:**
- `documentacion/03_tecnica/` (vacío tras eliminación de archivos)
- `documentacion/02_administracion/` (vacío tras eliminación de archivos)

### Contenido Integrado ✅

Todo el contenido de los archivos eliminados ha sido verificado e integrado en la documentación oficial:

- **Variables de entorno (.env):** Documentado en `06_configuracion.md`
- **Autenticación:** Documentada en `05_autenticacion.md`
- **Implementación de seguridad:** Documentada en `07_instalador.md` y en este archivo
- **Herramientas administrativas:** Documentadas en `herramientas_administrativas.md`

### Estructura Final Limpia 🎯

La documentación del proyecto está ahora organizada exclusivamente en la carpeta `/documentacion/` con su estructura oficial por módulos. No existen archivos MD redundantes o duplicados en el directorio raíz del proyecto.

**Estado del proyecto:** Documentación consolidada y lista para desarrollo  
**Fecha de consolidación:** Diciembre 2024  
**Archivos de documentación activos:** 52 archivos MD en estructura modular

---

📌 Este archivo es el primero que debe procesar Copilot para entender todo el proyecto AUTOEXAM2. Nada debe desarrollarse fuera de este marco sin estar aquí documentado.