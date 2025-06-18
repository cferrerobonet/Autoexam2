#!/bin/bash

# Script para migrar archivos a la nueva estructura de almacenamiento
# Autor: Github Copilot
# Fecha: 13/06/2025

# Definir colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Imprimir mensaje de cabecera
echo -e "${GREEN}=============================================="
echo -e "MIGRACIÓN DE ARCHIVOS DE ALMACENAMIENTO - AUTOEXAM2"
echo -e "===============================================${NC}\n"

# Verificar que estamos en el directorio correcto
if [ ! -d "app" ] || [ ! -d "config" ] || [ ! -f "index.php" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde la raíz del proyecto AUTOEXAM2${NC}"
    exit 1
fi

# Crear la estructura de directorios si no existe
echo -e "${YELLOW}[1/6] Verificando la estructura de directorios...${NC}"
php -r "define('ROOT_PATH', realpath('.')); require_once 'config/storage.php'; echo initialize_storage_structure() ? 'OK' : 'ERROR';"

if [ $? -ne 0 ]; then
    echo -e "${RED}Error al inicializar la estructura de directorios${NC}"
    exit 1
fi

echo -e "${GREEN}Estructura de directorios verificada${NC}\n"

# Migrar archivos de logs
echo -e "${YELLOW}[2/6] Migrando archivos de logs...${NC}"

# Migrar logs de tmp/logs
if [ -d "tmp/logs" ]; then
    echo -e "  Migrando archivos de tmp/logs a almacenamiento/logs/sistema"
    cp -v tmp/logs/* almacenamiento/logs/sistema/ 2>/dev/null
fi

# Migrar logs de publico/logs
if [ -d "publico/logs" ]; then
    echo -e "  Migrando archivos de publico/logs a almacenamiento/logs/acceso"
    cp -v publico/logs/* almacenamiento/logs/acceso/ 2>/dev/null
fi

# Migrar archivo php_errors.log
if [ -f "almacenamiento/registros/php_errors.log" ]; then
    echo -e "  Migrando php_errors.log a almacenamiento/logs/errores"
    cp -v almacenamiento/registros/php_errors.log almacenamiento/logs/errores/ 2>/dev/null
fi

echo -e "${GREEN}Archivos de logs migrados${NC}\n"

# Migrar archivos temporales
echo -e "${YELLOW}[3/6] Migrando archivos temporales...${NC}"

# Migrar tmp/cache
if [ -d "tmp/cache" ]; then
    echo -e "  Migrando archivos de tmp/cache a almacenamiento/cache/app"
    cp -rv tmp/cache/* almacenamiento/cache/app/ 2>/dev/null
fi

# Migrar publico/temp
if [ -d "publico/temp" ]; then
    echo -e "  Migrando archivos de publico/temp a almacenamiento/tmp"
    cp -rv publico/temp/* almacenamiento/tmp/ 2>/dev/null
fi

echo -e "${GREEN}Archivos temporales migrados${NC}\n"

# Migrar archivos subidos
echo -e "${YELLOW}[4/6] Migrando archivos subidos...${NC}"

# Migrar publico/uploads
if [ -d "publico/uploads" ]; then
    echo -e "  Migrando archivos de publico/uploads a almacenamiento/subidas"
    cp -rv publico/uploads/* almacenamiento/subidas/ 2>/dev/null
fi

# Migrar publico/subidas
if [ -d "publico/subidas" ]; then
    echo -e "  Migrando archivos de publico/subidas a almacenamiento/subidas"
    cp -rv publico/subidas/* almacenamiento/subidas/ 2>/dev/null
fi

# Migrar almacenamiento/subidas
if [ -d "almacenamiento/subidas" ] && [ -d "almacenamiento/subidas" ] && [ "$(readlink -f almacenamiento/subidas)" != "$(readlink -f almacenamiento/subidas)" ]; then
    echo -e "  Migrando archivos de almacenamiento/subidas (antigua) a almacenamiento/subidas"
    cp -rv almacenamiento/subidas/* almacenamiento/subidas/ 2>/dev/null
fi

echo -e "${GREEN}Archivos subidos migrados${NC}\n"

# Actualizar .gitignore si existe
echo -e "${YELLOW}[5/6] Actualizando .gitignore...${NC}"

if [ -f ".gitignore" ]; then
    # Verificar si ya tenemos entradas para la nueva estructura
    if ! grep -q "almacenamiento/logs/" .gitignore; then
        echo -e "  Agregando nuevas rutas a .gitignore"
        cat <<EOT >> .gitignore

# Nueva estructura de almacenamiento
almacenamiento/logs/*
!almacenamiento/logs/**/.gitkeep
almacenamiento/cache/*
!almacenamiento/cache/**/.gitkeep
almacenamiento/tmp/*
!almacenamiento/tmp/**/.gitkeep
almacenamiento/subidas/*
!almacenamiento/subidas/**/.gitkeep
EOT
    else
        echo -e "  La estructura ya está en .gitignore"
    fi
else
    echo -e "  Creando archivo .gitignore"
    cat <<EOT > .gitignore
# Archivos de configuración
.env
config/local.*

# Nueva estructura de almacenamiento
almacenamiento/logs/*
!almacenamiento/logs/**/.gitkeep
almacenamiento/cache/*
!almacenamiento/cache/**/.gitkeep
almacenamiento/tmp/*
!almacenamiento/tmp/**/.gitkeep
almacenamiento/subidas/*
!almacenamiento/subidas/**/.gitkeep

# Directorios obsoletos (se mantendrán por compatibilidad)
tmp/*
publico/logs/*
publico/temp/*
publico/uploads/*
publico/subidas/*
EOT
fi

echo -e "${GREEN}Archivo .gitignore actualizado${NC}\n"

# Crear archivo README para explicar la nueva estructura
echo -e "${YELLOW}[6/6] Creando documentación...${NC}"

mkdir -p documentacion/09_configuracion_mantenimiento

cat <<EOT > documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md
# Estructura de Almacenamiento en AUTOEXAM2

Este documento describe la estructura unificada de almacenamiento implementada el 13/06/2025.

## Estructura General

\`\`\`
/almacenamiento/
  ├── logs/               # Todos los archivos de registro
  │   ├── app/            # Logs de la aplicación
  │   ├── errores/        # Logs de errores PHP
  │   ├── acceso/         # Logs de acceso/auditoría
  │   └── sistema/        # Logs del sistema y diagnósticos
  ├── cache/              # Cache del sistema
  │   ├── app/            # Cache de la aplicación
  │   ├── vistas/         # Cache de vistas
  │   └── datos/          # Cache de datos
  ├── tmp/                # Archivos temporales
  │   ├── uploads/        # Subidas temporales
  │   └── sesiones/       # Datos de sesión (si se usan archivos)
  ├── subidas/            # Subidas permanentes de usuarios
  │   ├── imagenes/       # Imágenes subidas
  │   ├── documentos/     # Documentos subidos
  │   └── examenes/       # Exámenes subidos
  └── copias/             # Copias de seguridad
      ├── db/             # Copias de la base de datos
      └── sistema/        # Copias de configuración
\`\`\`

## Objetivos de la Unificación

1. **Centralización**: Todos los archivos generados por el sistema están en un solo directorio raíz.
2. **Organización**: Clara separación por tipo y propósito de los archivos.
3. **Mantenimiento**: Facilita las copias de seguridad y la limpieza periódica.
4. **Seguridad**: Mejor control de permisos y acceso a los archivos.

## Uso en el Código

Para utilizar esta estructura en el código:

\`\`\`php
// Las constantes están disponibles después de cargar config/storage.php
require_once CONFIG_PATH . '/storage.php';

// Ejemplos de uso
$mi_log = get_log_path('mi_modulo', 'app');
file_put_contents($mi_log, "Mi mensaje", FILE_APPEND);

// O usando la función helper
log_message("Mi mensaje de error", "mi_modulo", "error");
\`\`\`

## Compatibilidad

Las rutas antiguas siguen funcionando por compatibilidad, pero se recomienda migrar progresivamente a la nueva estructura.

## Mantenimiento

Para limpiar archivos temporales y logs antiguos, puede usar los scripts de mantenimiento:

\`\`\`bash
./herramientas/gestor.sh limpiar-logs
./herramientas/gestor.sh limpiar-cache
\`\`\`
EOT

echo -e "${GREEN}Documentación creada${NC}\n"

echo -e "${GREEN}=============================================="
echo -e "MIGRACIÓN COMPLETADA"
echo -e "===============================================${NC}\n"

echo -e "NOTA: Las carpetas antiguas se han mantenido por compatibilidad."
echo -e "      Puede ejecutar las siguientes acciones para verificar:"
echo -e "      1. Revisar la nueva estructura en almacenamiento/"
echo -e "      2. Leer la documentación en documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md"
echo -e "      3. Probar el sistema para verificar que todo funcione correctamente"
echo -e "\nGracias por actualizar AUTOEXAM2\n"
