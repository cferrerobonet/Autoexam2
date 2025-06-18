# Solución de problemas de envío de correos en AUTOEXAM2

Este documento proporciona una guía completa para diagnosticar y solucionar problemas relacionados con el envío de correos electrónicos en el sistema AUTOEXAM2, especialmente para la funcionalidad de recuperación de contraseñas.

## 🔍 Herramientas de diagnóstico disponibles

### Herramientas basadas en navegador

Para diagnosticar problemas de envío de correos, puede utilizar las siguientes herramientas integradas en la aplicación:

1. **Test SMTP básico**:
   - Ruta: `/publico/diagnostico/test_smtp_debug.php`
   - Permite verificar la configuración básica SMTP y enviar correos de prueba.

2. **Test de caracteres especiales**:
   - Ruta: `/publico/diagnostico/test_caracteres_especiales.php`
   - Prueba específicamente el manejo de caracteres UTF-8 (acentos, eñes, etc.)

3. **Test SMTP avanzado**:
   - Ruta: `/publico/diagnostico/smtp_avanzado.php`
   - Permite probar diferentes configuraciones SMTP desde la interfaz web

4. **Test de recuperación**:
   - Ruta: `/publico/diagnostico/test_recuperacion.php`
   - Simula el proceso completo de recuperación de contraseña

5. **Test de enlaces de recuperación**:
   - Ruta: `/publico/diagnostico/test_enlaces_recuperacion.php`
   - Verifica que los enlaces de recuperación funcionen correctamente

6. **Test de configuraciones SMTP**:
   - Ruta: `/publico/diagnostico/test_smtp_configs.php`
   - Prueba múltiples configuraciones SMTP para determinar la óptima

### Herramientas de línea de comandos

También puede ejecutar pruebas desde la línea de comandos:

```bash
# Test básico de envío de correo
php herramientas/diagnostico/test_correo.php correo@ejemplo.com

# Test completo con opciones avanzadas
php herramientas/diagnostico/test_correo.php --verbose --from=noreply@dominio.com --subject="Prueba SMTP" correo@ejemplo.com

# Test con diferentes configuraciones
php herramientas/diagnostico/test_smtp_config.php --port=587 --security=tls correo@ejemplo.com
```

## 🚨 Problemas comunes y soluciones

### 1. PHPMailer no está instalado

**Síntomas**: No se envían correos o aparecen errores relacionados con clases no encontradas como "Class 'PHPMailer\PHPMailer\PHPMailer' not found".

**Solución**: Instalar PHPMailer usando Composer:

```bash
composer require phpmailer/phpmailer
```

O descargarlo manualmente desde https://github.com/PHPMailer/PHPMailer y colocarlo en:
`librerias/PHPMailer/`

**Verificación**: Puede comprobar si PHPMailer está instalado correctamente usando la herramienta de diagnóstico SMTP avanzado en `/publico/diagnostico/smtp_avanzado.php`.

### 2. Datos de configuración SMTP incorrectos

**Síntomas**: Errores de conexión o autenticación SMTP. Mensajes como "Connection could not be established" o "SMTP Error: Could not authenticate".

**Solución**: Verificar y corregir las credenciales SMTP en el archivo `.env`:

```
SMTP_HOST=smtp.servidor.com
SMTP_USER=usuario@dominio.com
SMTP_PASS=contraseña_segura
SMTP_PORT=587             # Puertos comunes: 587 (TLS), 465 (SSL), 25 (sin cifrado)
SMTP_SECURE=tls           # Opciones: tls, ssl, o vacío
SMTP_FROM=no-reply@dominio.com  # Dirección del remitente
SMTP_FROM_NAME=AUTOEXAM2        # Nombre visible del remitente
```

> **IMPORTANTE**: Asegúrate de que `SMTP_FROM` y `SMTP_FROM_NAME` estén correctamente configurados. A partir de la versión más reciente del sistema:
> 
> - Si `SMTP_FROM` no está definido, el sistema intentará usar `SMTP_USER` como dirección de remitente.
> - Si `SMTP_FROM_NAME` no está definido, el sistema usará un nombre genérico derivado del dominio de correo.
> - Para garantizar la correcta visualización de caracteres especiales, ambos campos se envían con codificación base64.
> 
> Es recomendable definir ambas variables explícitamente para asegurar un funcionamiento correcto y personalizado.

### 3. Modo DEBUG activado

**Síntomas**: Los correos se simulan pero no se envían realmente. El sistema indica éxito pero los correos nunca llegan.

**Solución**: En el archivo `.env`, asegúrese de que:

```
DEBUG=false
```

**Verificación**: Revise los logs en `/almacenamiento/registros/php_errors.log` - si ve mensajes como "CORREO SIMULADO (modo DEBUG)" significa que está en modo de depuración.

### 4. Problemas de codificación de caracteres

**Síntomas**: Los correos llegan pero los acentos, eñes y otros caracteres especiales aparecen mal codificados (como "RecuperaciÃ³n" en lugar de "Recuperación").

**Soluciones**:
- Verificar que se está usando la implementación más reciente de la clase `Correo`
- Comprobar que el envío se realiza con `CharSet = 'UTF-8'` y `Encoding = 'base64'`
- Utilizar la herramienta `/publico/diagnostico/test_caracteres_especiales.php` para diagnosticar y probar
- Asegurarse de que las plantillas HTML incluyan:
  ```html
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  ```
- Verificar que los headers del correo (From, Subject) usen codificación base64 para caracteres especiales

### 5. Problemas con el servidor de correos

**Síntomas**: El script se ejecuta sin errores pero los correos no llegan.

**Soluciones**:
- Verificar si el servidor SMTP está bloqueando conexiones (contacte a su proveedor)
- Comprobar si el correo está llegando a carpetas de spam
- Verificar límites de envío diarios del servidor SMTP
- Intentar usar otro puerto (587, 465 o 25)
- Verificar la configuración de SSL/TLS del servidor

### 6. Problemas de red o firewall

**Síntomas**: Tiempos de espera o errores de conexión.

**Solución**: Verificar que el servidor tiene acceso al puerto SMTP necesario (generalmente 587, 465 o 25).

```bash
# Probar conectividad SMTP desde la línea de comandos:
telnet smtp.servidor.com 587
```

### 7. Enlaces de recuperación que redirigen al login

**Síntomas**: Al hacer clic en los enlaces de recuperación en el correo, se redirige a la pantalla de login.

**Solución**: 
- Verificar que 'restablecer' está en las acciones públicas del ruteador en `app/controladores/ruteador.php`:
```php
$accionesPublicas = ['login', 'recuperar', 'restablecer', 'verificar', 'error'];
```
- Usar la herramienta `/publico/diagnostico/test_enlaces_recuperacion.php` para verificar el comportamiento
- Comprobar que el token de recuperación es válido y no ha expirado

## 🧪 Solución temporal: Modo DEBUG

Si necesita probar la funcionalidad de recuperación de contraseña sin enviar correos reales, puede activar el modo DEBUG en `.env`:

```
DEBUG=true
```

En este modo, los correos no se enviarán realmente, pero la aplicación simulará que se han enviado con éxito. Los datos del correo se registrarán en los archivos de log.

**Características**:
- No requiere configuración SMTP real
- Los mensajes se registran completos en los logs
- Muestra en pantalla (si está habilitado) los destinatarios y asuntos
- Simula correctamente toda la cadena de eventos del sistema

## 📋 Registros (Logs)

Los errores relacionados con el envío de correos se registran en:

```
almacenamiento/registros/php_errors.log
tmp/logs/errors.log
```

La información detallada sobre intentos de envío, configuración SMTP y errores específicos se encuentra en estos archivos. Use `tail -f` para monitorear los logs en tiempo real mientras realiza pruebas:

```bash
tail -f almacenamiento/registros/php_errors.log
```

### Ejemplo de entradas de log

```
[13-Jun-2025 10:45:22 UTC] Inicializando clase Correo para envío de correos
[13-Jun-2025 10:45:22 UTC] Configuración de correo - De: no-reply@autoexam2.com, Nombre: AUTOEXAM2, Host: smtp.example.com, Puerto: 587, Usuario: user@example.com, Seguridad: tls, Debug: No
[13-Jun-2025 10:45:22 UTC] === INICIO ENVÍO DE CORREO ===
[13-Jun-2025 10:45:23 UTC] Usando PHPMailer para enviar correo
[13-Jun-2025 10:45:24 UTC] === FIN ENVÍO DE CORREO (EXITOSO) ===
```

## 🚀 Cambios recientes implementados

1. **Mejora de codificación UTF-8**: Se ha implementado soporte completo para caracteres especiales en correos.
2. **Diagnóstico mejorado**: Nuevas herramientas en `/publico/diagnostico/` para facilitar la solución de problemas.
3. **Corrección de redirección**: Se ha arreglado la redirección incorrecta al login en enlaces de recuperación.
4. **Plantillas mejoradas**: Las plantillas HTML ahora tienen etiquetas meta UTF-8 explícitas.
5. **Soporte multi-cliente**: Las mejoras garantizan compatibilidad con diversos clientes de correo electrónico.
6. **Múltiples intentos**: Sistema implementa reintentos automáticos con diferentes configuraciones en caso de fallo.
7. **Logging detallado**: Sistema de logging mejorado para facilitar diagnóstico.
8. **Manejo de errores específicos**: Respuestas personalizadas según el tipo de error detectado.

## 🌐 Herramientas de Diagnóstico Web

Para facilitar el diagnóstico en producción (especialmente en hostings como IONOS que no permiten acceso SSH), las herramientas de diagnóstico están disponibles vía web en:

```
https://tudominio.com/diagnostico/
```

### Herramientas Disponibles

1. **Test de Correo**: `diagnostico/test_correo.php`
   - Verificación completa de configuración SMTP
   - Envío de correos de prueba
   
2. **Test Simple**: `diagnostico/test_simple_correo.php`
   - Prueba básica de envío

3. **Test de Recuperación**: `diagnostico/test_recuperacion_completa.php`
   - Prueba completa del flujo de recuperación

4. **Test de Base de Datos**: `diagnostico/test_bd.php`
   - Verificación de conectividad

**Ejemplo de uso**:
```
https://tudominio.com/diagnostico/test_correo.php?email=tu-email@ejemplo.com
```

## 📚 Documentación relacionada

- [Recuperación de Contraseña](11_recuperacion_contrasena.md) - Documentación completa del proceso de recuperación
- [Clase Correo](../09_configuracion_mantenimiento/clase_correo.md) - Documentación de la clase de envío de correos
- [Variables de Entorno](../09_configuracion_mantenimiento/variables_entorno.md) - Configuración mediante variables de entorno

---

Última actualización: 13 de junio de 2025
