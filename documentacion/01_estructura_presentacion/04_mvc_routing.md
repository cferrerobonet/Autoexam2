# 04 - Arquitectura MVC y Sistema de Ruteado

**Implementado y funcional** ✅  
**Ubicación:** `app/controladores/ruteador.php`, `index.php`, `publico/.htaccess`  
**Tipo:** Arquitectura del Sistema

---

## 🎯 Objetivos del sistema

- Proporcionar un sistema organizado y modular mediante el patrón MVC (Modelo-Vista-Controlador)
- Implementar un sistema de ruteo simple pero robusto para manejar las URLs amigables
- Facilitar la separación de responsabilidades en el desarrollo
- Centralizar la gestión y manejo de errores
- Permitir un desarrollo ordenado y escalable de nuevas funcionalidades

---

## 🧱 Arquitectura del Sistema MVC

### Componentes principales
```
index.php               # Punto de entrada principal del sistema
app/controladores/      # Lógica de control de la aplicación
app/modelos/            # Modelos de datos y lógica de negocio
app/vistas/             # Vistas para la presentación al usuario
app/servicios/          # Servicios reutilizables del sistema
app/utilidades/         # Funciones auxiliares y herramientas
publico/                # Directorio raíz del servidor web
```

### Flujo de ejecución

1. Todas las peticiones web son recibidas por el servidor web en el directorio `publico/`
2. El archivo `.htaccess` redirige todas las peticiones no-estáticas a `index.php` con el parámetro `url`
3. `index.php` inicializa el sistema y pasa el control al `Ruteador`
4. El `Ruteador` analiza la URL y determina:
   - Controlador a ejecutar
   - Método (acción) del controlador
   - Parámetros adicionales
5. El controlador correspondiente procesa la petición y carga la vista apropiada

---

## 🛣️ Sistema de Ruteo

### Formato de URL

Las URLs siguen el formato:
```
https://dominio.com/controlador/accion/parametro1/parametro2/...
```

Donde:
- **controlador**: Corresponde a una clase en `app/controladores/` (si se omite, se usa `inicio_controlador.php`)
- **acción**: Método del controlador a ejecutar (si se omite, se usa `index()`)
- **parámetros**: Argumentos opcionales pasados al método como `array`

### Ejemplos de rutas
- `/` → `inicio_controlador->index()`
- `/autenticacion` → `autenticacion_controlador->index()`
- `/autenticacion/iniciar` → `autenticacion_controlador->iniciar()`
- `/usuarios/editar/123` → `usuarios_controlador->editar('123')`

### Implementación en `.htaccess`
El sistema usa reglas de reescritura Apache para procesar las URLs amigables:

```apache
RewriteEngine On
RewriteBase /

# Excluir directorios especiales
RewriteCond %{REQUEST_URI} !^/diagnostico/

# Redirigir a index.php si no es un archivo o directorio real
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## 🔄 Manejo de Errores

El sistema implementa un manejo centralizado de errores y excepciones:

- `Ruteador->manejarError()` - Para errores PHP generales
- `Ruteador->manejarExcepcion()` - Para excepciones no capturadas
- `Ruteador->mostrarErrorPersonalizado()` - Para mostrar páginas de error personalizadas

Los errores son:
1. Registrados en el archivo de log correspondiente
2. Mostrados con información detallada en desarrollo
3. Mostrados con mensajes genéricos amigables en producción

---

## 💻 Uso para Desarrolladores

Para crear una nueva funcionalidad:

1. Crear un nuevo controlador en `app/controladores/nuevo_controlador.php`
2. Implementar los métodos necesarios (al menos el método `index()`)
3. Crear los modelos correspondientes en `app/modelos/` si es necesario
4. Crear las vistas en `app/vistas/nueva_funcionalidad/`

La nueva funcionalidad estará automáticamente disponible en `/nuevo`

---

## ⚙️ Configuración

El sistema de rutas puede configurarse en `app/controladores/ruteador.php`:

- `$controladorPredeterminado` - Controlador si no se especifica (predeterminado: 'inicio')
- `$accionPredeterminada` - Método si no se especifica (predeterminado: 'index')
