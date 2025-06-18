# 05 - Sistema de Configuración y Variables de Entorno

**Implementado y funcional** ✅  
**Ubicación:** `config/config.php`, `app/utilidades/env.php`  
**Tipo:** Infraestructura Base

---

## 🎯 Objetivos del sistema

- Proporcionar una gestión centralizada de la configuración del sistema
- Separar los valores sensibles o específicos del entorno del código fuente
- Permitir diferentes configuraciones para desarrollo y producción
- Facilitar la instalación y despliegue en diferentes entornos
- Asegurar datos sensibles como contraseñas y claves de API

---

## 🧱 Arquitectura del Sistema

### Componentes principales
```
config/config.php         # Configuración global centralizada
config/storage.php        # Configuración de almacenamiento
app/utilidades/env.php    # Gestor de variables de entorno
.env                      # Archivo con variables de entorno (no versionado)
```

### Flujo de inicialización

1. `index.php` define constantes básicas de rutas y carga las utilidades
2. Se carga `config/storage.php` para definir las rutas de almacenamiento
3. Se carga `config/config.php` que a su vez:
   - Carga la clase `Env` de `app/utilidades/env.php`
   - Busca y carga el archivo `.env`
   - Establece las constantes y configuraciones según el entorno

---

## 📝 Clase `Env` - Gestor de Variables de Entorno

La clase `Env` proporciona una interfaz para cargar y acceder a variables de entorno desde un archivo `.env`:

### Métodos principales

- `Env::cargar($ruta_archivo)` - Carga variables desde un archivo `.env`
- `Env::obtener($clave, $defecto = null)` - Obtiene el valor de una variable
- `Env::establecer($clave, $valor)` - Establece una variable manualmente
- `Env::requerido($clave)` - Obtiene variable y falla si no existe

### Formato del archivo `.env`

```
# Comentario
CLAVE=valor
OTRA_CLAVE="valor con espacios"
```

---

## ⚙️ Variables de entorno utilizadas

El sistema utiliza las siguientes variables de entorno:

### Configuración básica
- `APP_NAME` - Nombre de la aplicación
- `APP_ENV` - Entorno (development/production)
- `APP_DEBUG` - Modo debug (true/false)
- `BASE_URL` - URL base del sistema

### Base de datos
- `DB_HOST` - Host de la base de datos
- `DB_PORT` - Puerto de la base de datos (normalmente 3306)
- `DB_DATABASE` - Nombre de la base de datos
- `DB_USERNAME` - Usuario de la base de datos
- `DB_PASSWORD` - Contraseña de la base de datos

### Correo electrónico
- `MAIL_HOST` - Servidor SMTP
- `MAIL_PORT` - Puerto SMTP
- `MAIL_USERNAME` - Usuario SMTP
- `MAIL_PASSWORD` - Contraseña SMTP
- `MAIL_ENCRYPTION` - Tipo de cifrado (ssl/tls)
- `MAIL_FROM_ADDRESS` - Dirección de envío
- `MAIL_FROM_NAME` - Nombre del remitente

---

## 🔄 Detección automática del entorno

El sistema puede detectar automáticamente si se está ejecutando en un entorno de desarrollo local:

```php
function is_development_environment() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    return (
        strpos($server_name, 'localhost') !== false || 
        strpos($server_name, '127.0.0.1') !== false ||
        strpos($server_name, '.local') !== false ||
        strpos($server_name, '.test') !== false
    );
}
```

---

## 💻 Uso para Desarrolladores

Para añadir una nueva configuración:

1. Agregar la variable al archivo `.env` con un valor predeterminado seguro
2. Actualizar la documentación o plantilla de `.env.example` (si existe)
3. Acceder en el código usando `Env::obtener('NUEVA_VARIABLE')` o a través de constantes definidas en `config.php`

Para acceder a la configuración:

```php
// Usando la clase Env directamente
$valor = Env::obtener('VARIABLE_ENV', 'valor_predeterminado');

// O usando las constantes definidas
echo BASE_URL;
```

---

## 🔒 Seguridad

- El archivo `.env` no debe ser versionado en git
- El instalador debe crear este archivo o proporcionar una interfaz para configurarlo
- Las variables sensibles nunca deben ser hardcodeadas en el código
- Los valores de configuración deben validarse antes de usarse
