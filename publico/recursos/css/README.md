# Estilos CSS para AUTOEXAM2

Este directorio contiene los archivos CSS utilizados en el sistema AUTOEXAM2.

## Archivos disponibles

- `admin.css`: Estilos para el panel de administración (incluye estilos comunes y de calendario)
- `profesor.css`: Estilos para el panel de profesores (incluye estilos comunes y de calendario)
- `alumno.css`: Estilos para el panel de alumnos (incluye estilos comunes y de calendario)
- `estilos.css`: Estilos generales compartidos
- `instalador.css`: Estilos específicos para el asistente de instalación del sistema

## Organización

Los estilos están organizados por roles, donde cada archivo CSS contiene todos los estilos necesarios para su respectiva interfaz. Los estilos anteriormente separados en archivos como `autoexam-common.css` y `calendario-personalizado.css` han sido integrados para:

1. Reducir el número de peticiones HTTP
2. Mejorar el rendimiento de carga 
3. Facilitar el mantenimiento al tener todos los estilos relacionados en un solo lugar
4. Permitir personalización específica por rol

## Colores por rol

- **Admin**: Colores azul primario `#4285F4` y verde secundario `#34A853`
- **Profesor**: Color azul principal `#4285F4`
- **Alumno**: Color morado `#8a5cd1` / `#7d50c4`

## Uso

Para usar estos archivos en cualquier parte del proyecto:

```html
<link rel="stylesheet" href="/recursos/css/[nombre-archivo].css">
```

## Estructura y organización

Los estilos están organizados por componentes y funcionalidades:

- **Layout general**: Configuración básica de la página y contenedores
- **Componentes personalizados**: Estilos específicos para elementos UI personalizados
- **Formularios**: Mejoras visuales para campos de formulario y validación
- **Utilidades**: Espaciado, colores y otros helpers
- **Responsive**: Ajustes para diferentes tamaños de pantalla

## Dependencias

La mayoría de los estilos se construyen sobre [Bootstrap 5](https://getbootstrap.com/) y están diseñados para trabajar en conjunto con [Bootstrap Icons](https://icons.getbootstrap.com/).
