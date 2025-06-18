# Herramientas de Mantenimiento

Esta carpeta contiene herramientas para el mantenimiento y gestión del sistema AUTOEXAM2.

## Herramientas disponibles

### 📂 Gestión de almacenamiento

- **migrar_almacenamiento.sh**: Migra archivos de las ubicaciones antiguas a la nueva estructura centralizada.
- **verificar_rutas_obsoletas.sh**: Busca referencias a rutas obsoletas en el código.

## Cómo usar las herramientas

Todas las herramientas se pueden ejecutar directamente desde la línea de comandos, o a través del script gestor principal:

```bash
# Desde la raíz del proyecto
./herramientas/gestor.sh
```

El gestor proporciona un menú interactivo para seleccionar la herramienta deseada (opción 4 - Mantenimiento).

## Mantenimiento periódico recomendado

Se recomienda realizar las siguientes tareas de mantenimiento de forma periódica:

1. **Diariamente**: Limpiar archivos temporales y caché.
   ```bash
   ./herramientas/mantenimiento/limpiar_cache.sh
   ```

2. **Semanalmente**: Limpiar logs antiguos.
   ```bash
   ./herramientas/mantenimiento/limpiar_logs.sh
   ```

3. **Mensualmente**: Verificar rutas obsoletas y actualizar referencias en el código.
   ```bash
   ./herramientas/mantenimiento/verificar_rutas_obsoletas.sh
   ```

## Configuración de tareas programadas (cron)

Para automatizar las tareas de mantenimiento, puede configurar los siguientes cron jobs:

```bash
# Limpiar logs antiguos cada domingo a las 3:00 AM
0 3 * * 0 /ruta/al/proyecto/herramientas/mantenimiento/limpiar_logs.sh

# Limpiar archivos temporales y caché todos los días a las 4:00 AM
0 4 * * * /ruta/al/proyecto/herramientas/mantenimiento/limpiar_cache.sh
```

## Estructura de almacenamiento

Para más información sobre la estructura de almacenamiento del sistema, consulte la documentación en:

```
/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md
```

## Planificado para el futuro

### 💾 backup/
Herramientas de respaldo adicionales:
- Backup completo del sistema
- Restauración de backups

### ⚡ optimizacion/
Herramientas de optimización:
- Optimización de base de datos
- Compresión de archivos
- Optimización de rendimiento

## Solución de problemas

Si encuentra problemas con las herramientas de mantenimiento:

1. Asegúrese de estar ejecutando los scripts desde la raíz del proyecto.
2. Verifique que los scripts tienen permisos de ejecución:
   ```bash
   chmod +x herramientas/mantenimiento/*.sh
   ```
3. Consulte los logs del sistema en `/almacenamiento/logs/sistema/`.
