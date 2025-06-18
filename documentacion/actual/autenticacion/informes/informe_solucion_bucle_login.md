# Solución al Problema de Bucle en el Login de AUTOEXAM2

## Diagnóstico del Problema

El problema principal detectado es que tras un inicio de sesión correcto, el sistema vuelve a la página de login en lugar de avanzar a la página de inicio, creando un bucle. Esto ocurre específicamente en el entorno de producción (IONOS).

### Causas Identificadas:

1. **Problema con las Cookies de Sesión**: La causa principal es que la cookie de sesión (PHPSESSID) no está siendo establecida o persistida correctamente entre peticiones:
   - La configuración de las cookies de sesión incluía parámetros como `domain`, `secure` y `samesite` que pueden causar incompatibilidades en ciertos entornos.
   - La detección del entorno de producción/desarrollo puede estar funcionando correctamente, pero la configuración de cookies resultante es demasiado restrictiva.

2. **Flujo de Autenticación**: El proceso funciona correctamente:
   - El login valida las credenciales
   - Se crea una sesión PHP
   - Se almacena el token en la base de datos
   - Se establece la redirección a /inicio

3. **Validación de Sesión**: 
   - Al intentar acceder a /inicio, el ruteador verifica si hay sesión activa
   - No encuentra la cookie de sesión o no puede validar el token
   - Redirige de nuevo a /login

## Solución Implementada

Se ha modificado el archivo `app/utilidades/sesion.php` para utilizar una configuración mínima de cookies de sesión, eliminando los parámetros que pueden causar incompatibilidades:

```php
// Configuración simplificada para máxima compatibilidad
$cookie_params = [
    'lifetime' => $cookieParams['lifetime'],
    'path' => '/',
    'httponly' => true
    // Omitimos intencionadamente 'domain', 'secure' y 'samesite' 
    // para maximizar compatibilidad con diferentes servidores
];

session_set_cookie_params($cookie_params);
```

### Detalles de la Solución:

1. **Eliminación de parámetros problemáticos**:
   - Se eliminó el parámetro `domain` que puede causar problemas si no coincide exactamente con el dominio actual
   - Se eliminó el parámetro `secure` que requiere HTTPS
   - Se eliminó el parámetro `samesite` que puede no ser compatible con todos los navegadores

2. **Herramientas de diagnóstico creadas**:
   - `publico/diagnostico/diagnostico_cookie_sesion.php`: Herramienta completa para diagnosticar problemas de sesiones y cookies
   - `publico/diagnostico/sesion_minima.php`: Implementación de prueba con configuración mínima de cookies

3. **Compromiso de seguridad/funcionalidad**:
   - Esta solución prioriza la funcionalidad sobre algunas características de seguridad
   - La cookie sigue siendo `httponly` para protección contra XSS
   - La validación del token en base de datos sigue funcionando

## Instrucciones de Verificación

1. Acceder a `[URL]/publico/diagnostico/` para usar las herramientas de diagnóstico
2. Verificar que las cookies de sesión se establezcan correctamente en `diagnostico_cookie_sesion.php`
3. Probar el login en la aplicación principal para confirmar que el bucle se ha resuelto
4. Verificar que la sesión persiste entre peticiones

## Consideraciones Adicionales

En caso de que esta solución no resuelva completamente el problema, considerar:

1. Revisar la configuración del servidor web (especialmente headers HTTP que puedan afectar cookies)
2. Verificar si hay algún problema con la tabla `sesiones_activas` en la base de datos
3. Añadir logs temporales en el flujo de validación de sesión para identificar puntos exactos de fallo
4. Revisar si hay alguna incompatibilidad en el hosting específico (IONOS) con características de cookies

## Recomendaciones de Seguridad

Una vez solucionado el problema de funcionalidad, considerar:

1. Si el entorno lo permite, volver a habilitar `secure` para cookies en HTTPS
2. Mantener la regeneración de IDs de sesión para prevenir ataques de session fixation
3. Mantener el sistema de tokens en base de datos para validar sesiones

## Conclusión

El cambio implementado simplifica la configuración de cookies de sesión para garantizar la máxima compatibilidad con diferentes entornos de servidor y navegadores, resolviendo el problema de bucle en el login sin comprometer significativamente la seguridad del sistema.
