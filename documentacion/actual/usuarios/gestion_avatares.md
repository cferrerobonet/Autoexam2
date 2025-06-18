# Gestión de Avatares y Fotos de Usuario - AUTOEXAM2

**Última actualización:** 17 de junio de 2025

Este documento describe el sistema unificado de gestión de avatares y fotos de perfil para usuarios en AUTOEXAM2, incluyendo el almacenamiento, procesamiento y visualización de imágenes de perfil.

---

## 1. Visión General

El sistema de avatares permite que los usuarios de AUTOEXAM2 (administradores, profesores y alumnos) tengan una foto de perfil personalizada que se muestra en diversas partes de la interfaz, mejorando la experiencia de usuario y haciendo más reconocible cada cuenta.

### 1.1 Características Actuales

- Subida de imágenes de perfil personalizadas
- Procesamiento y validación de imágenes
- Almacenamiento centralizado en directorio público dedicado
- Avatar por defecto para usuarios sin foto personalizada
- Visualización coherente en listados, menús y perfiles

### 1.2 Evolución del Sistema

| Fecha | Cambio |
|-------|--------|
| Junio 2024 | Implementación inicial - `/almacenamiento/subidas/avatars/` |
| Mayo 2025 | Mejora de validación y procesamiento |
| Junio 2025 | Unificación - `/publico/recursos/subidas/avatares/` |

---

## 2. Estructura de Almacenamiento

### 2.1 Ubicación de Avatares (ACTUAL)

Los avatares se almacenan actualmente en:

```
/publico/recursos/subidas/avatares/
```

Esta ruta es accesible directamente desde la web para permitir la visualización eficiente de avatares sin necesidad de pasar por un controlador.

### 2.2 Nombrado de Archivos

El formato de nombre para los avatares sigue el siguiente patrón:

- **Avatar personalizado:** `perfil_[hash_único].png` (ej: `perfil_68511b51b8803.png`)
- **Avatar por defecto:** `avatar_usuario_defecto.png`

El hash único se genera durante la subida para evitar colisiones de nombres y prevenir accesos no autorizados.

### 2.3 Referencia en Base de Datos

En la tabla `usuarios`, la columna `foto` almacena la ruta relativa al avatar:

```
recursos/subidas/avatares/perfil_68511b51b8803.png
```

Esta ruta relativa se combina con `BASE_URL` para generar la URL completa en las vistas.

---

## 3. Implementación Técnica

### 3.1 Procesamiento de Avatares

El método `procesarFotoPerfil()` en `usuarios_controlador.php` gestiona la subida y procesamiento:

```php
/**
 * Procesa la foto de perfil subida
 * 
 * @param array $archivo Datos del archivo $_FILES['foto']
 * @return string|false Ruta relativa donde se guardó la foto o false si falló
 */
private function procesarFotoPerfil($archivo) {
    // Validación y procesamiento
    // ...
    
    // Construir ruta de destino y nombre único
    $nombre_archivo = 'perfil_' . uniqid() . '.' . $extension;
    $ruta_destino = AVATARS_STORAGE_DIR . '/' . $nombre_archivo;
    
    // Mover y devolver ruta relativa
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        return AVATARS_PUBLIC_SUBPATH . '/' . $nombre_archivo;
    }
    
    return false;
}
```

### 3.2 Validaciones Implementadas

- **Tipos permitidos:** JPG, PNG, GIF
- **Tamaño máximo:** 2MB
- **Dimensiones:** Se mantienen las originales (sin redimensionamiento automático)

### 3.3 Formularios de Usuario

- Campo de foto añadido en formulario de **creación** (`/usuarios/crear`)
- Campo de foto añadido en formulario de **edición** (`/usuarios/editar`) 
- Soporte para `enctype="multipart/form-data"` en ambos formularios
- Validación frontend: tipo de archivo y tamaño máximo
- Previsualización de imagen antes de enviar el formulario

---

## 4. Uso en Vistas

### 4.1 Mostrar Avatar en Menú Lateral

```php
<?php if (!empty($_SESSION['foto'])): ?>
    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($_SESSION['foto']) ?>" 
         class="rounded-circle" width="40" height="40" alt="Avatar">
<?php else: ?>
    <img src="<?= BASE_URL ?>/recursos/img/user_image_default.png" 
         class="rounded-circle" width="40" height="40" alt="Avatar">
<?php endif; ?>
```

### 4.2 Mostrar Avatar en Listado de Usuarios

```php
<?php if (!empty($usuario['foto'])): ?>
    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($usuario['foto']) ?>" 
         class="rounded-circle" width="30" height="30" alt="Avatar">
<?php else: ?>
    <div class="user-icon-placeholder">
        <i class="fas fa-user"></i>
    </div>
<?php endif; ?>
```

---

## 5. Migración de Avatares

En junio de 2025 se implementó una migración para unificar la ubicación de avatares. Este proceso:

1. Movió avatares antiguos desde `/almacenamiento/subidas/avatars/` a `/publico/recursos/subidas/avatares/`
2. Actualizó las referencias en la base de datos
3. Estableció el nuevo estándar de nomenclatura

Este proceso aseguró consistencia en todo el sistema para la gestión de avatares.

---

## 6. Consideraciones de Seguridad

- Las imágenes se validan por tipo MIME y extensión
- Se generan nombres de archivo aleatorios para evitar colisiones y adivinaciones
- No se permite la ejecución de scripts en el directorio de avatares
- Se aplica sanitización a todas las rutas antes de mostrarlas en HTML

---

## 7. Referencia de Implementación

- **Controlador:** `/app/controladores/usuarios_controlador.php` (método `procesarFotoPerfil()`)
- **Configuración:** `/config/storage.php` (constantes `AVATARS_PUBLIC_SUBPATH` y `AVATARS_STORAGE_DIR`)
- **Directorio físico:** `/publico/recursos/subidas/avatares/`
- **Avatar por defecto:** `/publico/recursos/img/user_image_default.png`

---

## 8. Documentación Histórica

Este documento unifica la información anteriormente contenida en:
- `/04_usuarios_dashboard/12_gestion_fotos_usuarios.md` (Implementación inicial)
- `/04_usuarios_dashboard/36_gestion_avatares_usuario.md` (Implementación actual)

Para acceder a las versiones históricas, consultar el directorio `/documentacion/historial/versiones/`.
