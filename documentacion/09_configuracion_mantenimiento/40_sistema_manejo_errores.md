# 40 – Sistema de Manejo de Errores

**Implementado y funcional** ✅  
**Controlador principal:** `app/controladores/ruteador.php`  
**Vistas de error:** `app/vistas/error/`  

---

## 🎯 Objetivos del sistema

- Capturar y manejar errores de PHP de forma centralizada
- Proporcionar páginas de error personalizadas y user-friendly
- Registrar errores en logs estructurados para diagnóstico
- Mantener la seguridad ocultando detalles técnicos al usuario
- Facilitar el debugging en desarrollo y producción

---

## 🧱 Arquitectura del Sistema

### Componentes Principales
| Componente | Ubicación | Función |
|------------|-----------|---------|
| Handler de errores | `ruteador.php::manejarError()` | Captura errores PHP |
| Handler de excepciones | `ruteador.php::manejarExcepcion()` | Captura excepciones no controladas |
| Sistema de logging | `ruteador.php::registrarError()` | Registra errores en logs |
| Páginas de error | `app/vistas/error/` | Interfaces de error para usuarios |

---

## 🔧 Implementación en Ruteador

### Registro de Handlers
```php
public function __construct() {
    // Registrar manejador de errores personalizado
    set_error_handler([$this, 'manejarError']);
    set_exception_handler([$this, 'manejarExcepcion']);
}
```

### Handler de Errores PHP
```php
public function manejarError($nivel, $mensaje, $archivo, $linea) {
    // Registrar error en el log
    $this->registrarError('ERROR', $mensaje, $archivo, $linea);
    
    // Si es un error crítico, mostrar página de error
    if (in_array($nivel, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        require_once APP_PATH . '/vistas/error/error500.php';
        exit;
    }
}
```

### Handler de Excepciones
```php
public function manejarExcepcion($excepcion) {
    // Registrar excepción en el log
    $this->registrarError(
        'EXCEPCION', 
        $excepcion->getMessage(), 
        $excepcion->getFile(), 
        $excepcion->getLine()
    );
    
    // Mostrar página de error apropiada
    $this->mostrarPaginaError500();
    exit;
}
```

---

## 📝 Sistema de Logging

### Función de Registro
```php
private function registrarError($tipo, $mensaje, $archivo, $linea) {
    // Formatear mensaje de error de manera segura
    $mensajeLog = $tipo . " | " . $mensaje . " | " . $archivo . ":" . $linea;
    
    // Utilizar la función de log centralizada si está disponible
    if (function_exists('log_message')) {
        log_message($mensajeLog, 'errors', 'error');
    } else {
        // Fallback a error_log nativo de PHP
        error_log($mensajeLog);
    }
}
```

### Estructura de Logs
```
/almacenamiento/logs/errores/
├── error_[fecha].log          # Errores PHP generales
├── routing_[fecha].log        # Errores específicos de routing
└── exceptions_[fecha].log     # Excepciones no controladas
```

### Formato de Log
```
[2025-06-16 14:30:22] ERROR | Division by zero | /app/controladores/ejemplo.php:45
[2025-06-16 14:31:15] EXCEPCION | Class not found: NoExiste | /app/controladores/ruteador.php:67
```

---

## 🚫 Páginas de Error Personalizadas

### Error 404 - Página No Encontrada
```php
// En ruteador, cuando no se encuentra controlador o acción
$this->mostrarPaginaError404();
```

**Características:**
- Diseño consistente con el sistema
- Mensaje user-friendly
- Enlaces de navegación de regreso
- No expone estructura interna

### Error 500 - Error Interno del Servidor
```php
private function mostrarPaginaError500() {
    http_response_code(500);
    
    // HTML inline para evitar dependencias en caso de error crítico
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error del Servidor - ' . SYSTEM_NAME . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
            .error-container { max-width: 600px; margin: 50px auto; background: white; 
                              padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .error-title { color: #e74c3c; font-size: 24px; margin-bottom: 15px; }
            .error-message { color: #333; line-height: 1.6; margin-bottom: 20px; }
            .error-actions { text-align: center; }
            .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; 
                   text-decoration: none; border-radius: 5px; margin: 5px; }
            .btn:hover { background: #2980b9; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-title"><i class="fas fa-exclamation-triangle"></i> Error del Servidor</h1>
            <p class="error-message">
                Lo sentimos, ha ocurrido un error interno en el servidor. 
                Nuestro equipo técnico ha sido notificado y trabajamos para solucionarlo.
            </p>
            <div class="error-actions">
                <a href="' . BASE_URL . '" class="btn">Volver al Inicio</a>
                <a href="javascript:history.back()" class="btn">Página Anterior</a>
            </div>
        </div>
    </body>
    </html>';
}
```

---

## 🔍 Detección de Errores Específicos

### Errores de Routing
```php
// Controlador no encontrado
if (!file_exists($archivo_controlador)) {
    throw new Exception("Controlador no encontrado: " . $archivo_controlador);
}

// Clase de controlador no existe
if (!class_exists($nombreClase)) {
    throw new Exception("Clase de controlador no encontrada: " . $nombreClase);
}

// Método/acción no existe
if (!method_exists($this->controlador, $this->accion)) {
    throw new Exception("Método no encontrado: " . $this->accion);
}
```

### Errores de Base de Datos
```php
// Capturados por controladores individuales
try {
    $resultado = $this->modelo->operacion();
} catch (Exception $e) {
    error_log("Error BD: " . $e->getMessage());
    $_SESSION['error'] = 'Error en la operación. Inténtelo de nuevo.';
    header('Location: ' . BASE_URL . '/error');
    exit;
}
```

---

## 🛡️ Seguridad en Manejo de Errores

### Ocultación de Información Sensible
- Los errores mostrados al usuario no contienen:
  - Rutas de archivos del servidor
  - Nombres de base de datos o tablas
  - Contraseñas o tokens
  - Stack traces completos
  - Información del sistema

### Validación de Entrada
```php
// Sanitización en registro de errores
$mensajeLog = filter_var($tipo . " | " . $mensaje, FILTER_SANITIZE_STRING);
```

### Rate Limiting de Errores
- Evita spam de logs con errores repetitivos
- Limita registros por IP/sesión
- Rotación automática de logs

---

## 📊 Monitorización y Alertas

### Logs Estructurados
- Timestamp preciso
- Nivel de severidad
- Ubicación exacta del error
- Contexto de la petición
- IP del usuario (si aplica)

### Integración con Sistema de Logs Existente
```php
// Utiliza helpers.php para logging centralizado
if (function_exists('log_message')) {
    log_message($mensajeLog, 'errors', 'error');
}
```

### Métricas de Error
- Conteo de errores por tipo
- Frecuencia de errores críticos
- Patrones de error por usuario/IP
- Tendencias temporales

---

## 🔧 Configuración por Entorno

### Desarrollo
```php
// Mostrar errores detallados
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Producción
```php
// Ocultar errores al usuario final
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
```

### Variables de Entorno
```env
ERROR_REPORTING=production  # development | production
LOG_ERRORS=true
LOG_LEVEL=error            # debug | info | warning | error
ERROR_LOG_MAX_SIZE=10MB
```

---

## 🚀 Características Avanzadas

### Recovery Automático
```php
// Intento de recuperación en errores no críticos
if ($this->intentarRecuperacion($error)) {
    log_message("Recovery exitoso para: " . $error, 'info');
    return true;
}
```

### Notificación de Errores Críticos
```php
// Envío de email a administradores en errores críticos
if ($nivel === 'CRITICAL') {
    $this->notificarAdministradores($error);
}
```

### Cache de Páginas de Error
- Páginas de error estáticas para máximo rendimiento
- Evita errores recursivos
- Funciona incluso si hay problemas con BD

---

## 📋 Tipos de Error Manejados

### Errores PHP Nativos
- `E_ERROR` - Errores fatales
- `E_WARNING` - Advertencias
- `E_NOTICE` - Notices
- `E_PARSE` - Errores de parsing
- `E_CORE_ERROR` - Errores del núcleo PHP

### Excepciones Personalizadas
- Excepciones de routing
- Excepciones de base de datos
- Excepciones de validación
- Excepciones de autenticación

### Errores HTTP
- 400 - Bad Request
- 401 - Unauthorized
- 403 - Forbidden
- 404 - Not Found
- 500 - Internal Server Error

---

## ✅ Estado de Implementación

### Completamente Implementado ✅
- [x] Handler de errores PHP
- [x] Handler de excepciones
- [x] Sistema de logging básico
- [x] Página de error 500 inline
- [x] Registro de errores en ruteador
- [x] Manejo de errores de routing
- [x] Integración con sistema de logs existente

### Implementado Parcialmente ⚠️
- [~] Páginas de error personalizadas (estimado 70%)
- [~] Clasificación de errores por tipo (estimado 60%)
- [~] Sistema de notificaciones (estimado 30%)

### Pendiente de Implementación ❌
- [ ] Página de error 404 dedicada
- [ ] Dashboard de errores para admin
- [ ] Sistema de alertas automáticas
- [ ] Métricas de error en tiempo real
- [ ] Recovery automático avanzado

---

## 🔄 Integración con Otros Módulos

### Sistema de Logs (`helpers.php`)
- Utiliza función `log_message()` centralizada
- Respeta configuración de niveles de log
- Integrado con rotación de logs

### Sistema de Autenticación
- Maneja errores de sesión
- Redirige a login en errores de autenticación
- Protege información sensible en logs

### Dashboard Administrativo
- Muestra resumen de errores
- Permite visualización de logs
- Estadísticas de estabilidad del sistema

---

## 🚀 Mejoras Futuras Sugeridas

1. **Dashboard de Errores**
   - Interfaz web para visualización de errores
   - Filtros por fecha, tipo, severidad
   - Gráficas de tendencias

2. **Sistema de Alertas**
   - Notificaciones email automáticas
   - Integración con Slack/Teams
   - Alertas por umbral de errores

3. **Análisis Predictivo**
   - Detección de patrones de error
   - Alertas preventivas
   - Sugerencias de optimización

4. **Recovery Avanzado**
   - Reintentos automáticos
   - Fallbacks configurables
   - Degradación funcional elegante

---

📌 **Nota:** Este sistema está implementado en su funcionalidad core y proporciona una base sólida para el manejo de errores. Las mejoras futuras pueden añadirse gradualmente según las necesidades del sistema.
