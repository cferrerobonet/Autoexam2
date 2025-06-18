# Recursos de AUTOEXAM2

Esta carpeta contiene recursos estáticos utilizados por el sistema AUTOEXAM2.

## Estructura de carpetas

- `/css`: Hojas de estilo CSS para diferentes partes del sistema
  - `instalador.css`: Estilos específicos para el asistente de instalación
  
- `/img`: Imágenes utilizadas en la interfaz
  - `/avatares`: Imágenes predeterminadas para perfiles de usuario
  
- `logo.png`: Logo principal del sistema

## Uso de recursos

Los recursos deben ser referenciados desde los archivos HTML/PHP usando rutas relativas:

```html
<!-- Desde archivos en la raíz del proyecto -->
<link rel="stylesheet" href="/publico/recursos/css/instalador.css">
<img src="/publico/recursos/img/avatares/avatar1.png" alt="Avatar">

<!-- Desde archivos dentro de /publico -->
<link rel="stylesheet" href="/recursos/css/instalador.css">
<img src="/recursos/img/avatares/avatar1.png" alt="Avatar">
```

## Mantenimiento

Al añadir nuevos recursos, mantenga la organización por tipo (css, js, img, etc.) y documente su propósito en el README correspondiente de cada carpeta.
