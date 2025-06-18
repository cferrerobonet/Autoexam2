# Resolución del problema de configuración FTP/SFTP en AUTOEXAM2

## Descripción del problema

Se detectó que el instalador de AUTOEXAM2 no guardaba correctamente el tipo de conexión FTP (FTP vs SFTP) en el archivo `.env`. Esto causaba que durante una reinstalación, el sistema no pudiera determinar correctamente qué tipo de conexión se había configurado originalmente.

## Solución implementada

### 1. Agregar explícitamente `FTP_TYPE` al archivo .env

Se ha añadido una nueva variable `FTP_TYPE` al archivo `.env` para almacenar explícitamente el tipo de conexión (valores posibles: "ftp" o "sftp").

```
# Tipo de conexión FTP explícito (ftp o sftp)
FTP_TYPE=sftp
# Flag de conexión segura (para compatibilidad)
FTP_SECURE=true
```

### 2. Mejorar la detección del tipo de conexión

Se mejoró el algoritmo de detección del tipo de conexión para seguir este orden de prioridad:

1. Usar `FTP_TYPE` si está definido en el archivo .env
2. Si `FTP_TYPE` no existe, inferir el tipo desde `FTP_SECURE` (true = sftp, false = ftp)

### 3. Ajuste automático del puerto

Se mejoró la lógica para ajustar automáticamente el puerto según el tipo de conexión:
- Para FTP: Puerto 21
- Para SFTP: Puerto 22

### 4. Logs de diagnóstico

Se añadieron logs detallados para facilitar la resolución de problemas:
- Registro del tipo de conexión detectado
- Registro del origen de la información (FTP_TYPE directo o inferido desde FTP_SECURE)
- Validación de coherencia entre el puerto y el tipo de conexión

### 5. Herramienta de diagnóstico

Se creó una herramienta específica para verificar la configuración FTP:
- `herramientas/diagnostico/test_ftp_config.php`: Permite validar la correcta detección del tipo de conexión y la coherencia con el puerto configurado.

## Compatibilidad

La solución mantiene compatibilidad con archivos `.env` antiguos que no tienen la variable `FTP_TYPE`. En estos casos, el sistema infiere el tipo de conexión desde `FTP_SECURE`, asegurando que las instalaciones existentes sigan funcionando correctamente.

## Cambios en el código

1. Se añadió `FTP_TYPE` a la lista de variables cargadas desde `.env`
2. Se mejoró la inicialización de la configuración FTP con mejor detección del tipo
3. Se agregaron logs detallados para facilitar el diagnóstico
4. Se mejoró la gestión del puerto automático según el tipo de conexión
5. Se agregó información de diagnóstico durante la verificación de la conexión

## Pruebas realizadas

- Comprobación con `.env` que tiene `FTP_TYPE=sftp` → Detecta correctamente "sftp"
- Comprobación con `.env` que tiene `FTP_TYPE=ftp` → Detecta correctamente "ftp"
- Comprobación con `.env` que no tiene `FTP_TYPE` pero tiene `FTP_SECURE=true` → Infiere correctamente "sftp"
- Comprobación con `.env` que no tiene `FTP_TYPE` pero tiene `FTP_SECURE=false` → Infiere correctamente "ftp"
- Verificación de la coherencia entre el tipo detectado y el puerto configurado
