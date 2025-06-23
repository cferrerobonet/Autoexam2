# Plan de Refactorización y Sanitización Escalonada - AUTOEXAM2

## Fecha: 23 de junio de 2025

Este documento detalla un plan escalonado para refactorizar y sanitizar el código del proyecto AUTOEXAM2, permitiendo implementaciones progresivas y comprobaciones en cada fase.

## Fase 1: Autenticación y Seguridad Básica

### Prioridad: ALTA
### Archivos:
- `/app/controladores/autenticacion_controlador.php`
- `/app/utilidades/sesion.php`

### Cambios a realizar:
1. Mejorar sanitización de inputs en el login y registro
2. Implementar validación consistente de tokens CSRF
3. Sanitizar datos de sesión
4. Reforzar protección contra fuerza bruta

### Implementación de ejemplo para la fase 1:
```php
// Sanitización mejorada del correo
$correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Formato de correo electrónico no válido');
}

// Validación más estricta de campos
$campos_requeridos = ['correo', 'contrasena'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        throw new Exception('Todos los campos son obligatorios');
    }
}
```

## Fase 2: Controladores de Gestión (CRUD)

### Prioridad: MEDIA-ALTA
### Archivos:
- `/app/controladores/usuarios_controlador.php`
- `/app/controladores/preguntas_controlador.php`
- `/app/controladores/examenes_controlador.php`

### Cambios a realizar:
1. Implementar función centralizada de sanitización de parámetros
2. Asegurar validación de permisos por rol
3. Sanitizar datos antes de enviarlos a modelos
4. Validar parámetros GET/POST de forma consistente

## Fase 3: Modelos y Acceso a Datos

### Prioridad: MEDIA
### Archivos:
- `/app/modelos/usuario_modelo.php`
- `/app/modelos/pregunta_modelo.php`
- Resto de modelos

### Cambios a realizar:
1. Parametrizar todas las consultas SQL
2. Implementar escape de datos consistente
3. Validar tipos de datos antes de operaciones CRUD
4. Centralizar la validación de entradas

## Fase 4: Vistas y Presentación

### Prioridad: MEDIA-BAJA
### Archivos:
- Todos los archivos en `/app/vistas/`

### Cambios a realizar:
1. Asegurar escape de salida en todas las vistas
2. Implementar escape automático de variables
3. Sanitizar datos que provienen de la URL o POST

## Fase 5: Funcionalidades Avanzadas

### Prioridad: BAJA
### Archivos:
- `/app/controladores/api/`
- `/publico/api/`

### Cambios a realizar:
1. Implementar validación de parámetros API
2. Sanitizar entradas y salidas JSON
3. Mejorar manejo de errores y excepciones
4. Validar formatos de datos específicos (fechas, IDs)

## Consideraciones para la implementación

1. **Pruebas progresivas:** Después de cada cambio, realizar pruebas en un entorno controlado
2. **Respaldos:** Crear copias de seguridad antes de cada modificación
3. **Comentarios claros:** Documentar los cambios realizados y el motivo
4. **Registro de errores:** Implementar un sistema para detectar problemas post-implementación

## Herramientas y funciones recomendadas

- `filter_var()` y `filter_input()` para sanitización de entradas
- Funciones PDO preparadas para consultas SQL
- `htmlspecialchars()` para el escape de salidas en HTML
- Validación de tipos con `is_*()` y expresiones regulares donde sea necesario
