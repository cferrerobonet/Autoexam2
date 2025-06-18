#!/bin/bash

# Script para eliminar carpetas antiguas después de la migración
# Autor: Github Copilot
# Fecha: 13/06/2025

# Definir colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Imprimir mensaje de cabecera
echo -e "${GREEN}=============================================="
echo -e "ELIMINACIÓN DE CARPETAS ANTIGUAS - AUTOEXAM2"
echo -e "===============================================${NC}\n"

# Verificar que estamos en el directorio correcto
if [ ! -d "app" ] || [ ! -d "config" ] || [ ! -f "index.php" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde la raíz del proyecto AUTOEXAM2${NC}"
    exit 1
fi

# Lista de carpetas antiguas a eliminar
CARPETAS_ANTIGUAS=(
    "tmp/logs"
    "tmp/cache"
    "publico/logs"
    "publico/temp"
    "publico/uploads"
    "publico/subidas"
)

# Advertencia
echo -e "${RED}¡ADVERTENCIA! Este script eliminará las carpetas antiguas después de la migración.${NC}"
echo -e "${RED}Asegúrese de haber ejecutado previamente el script de migración y haber verificado que todo funciona correctamente.${NC}"
echo -e "${YELLOW}Las siguientes carpetas serán eliminadas:${NC}"

for carpeta in "${CARPETAS_ANTIGUAS[@]}"; do
    if [ -d "$carpeta" ]; then
        echo -e "  - ${YELLOW}$carpeta${NC}"
    fi
done

echo ""
read -p "¿Está seguro de que desea continuar? (s/n): " CONFIRM

if [[ $CONFIRM != "s" && $CONFIRM != "S" ]]; then
    echo -e "${YELLOW}Operación cancelada.${NC}"
    exit 0
fi

# Eliminar carpetas antiguas
echo -e "\n${YELLOW}Eliminando carpetas antiguas...${NC}"

for carpeta in "${CARPETAS_ANTIGUAS[@]}"; do
    if [ -d "$carpeta" ]; then
        echo -e "  Eliminando $carpeta"
        rm -rf "$carpeta"
        if [ ! -d "$carpeta" ]; then
            echo -e "  ${GREEN}✓ $carpeta eliminada correctamente${NC}"
        else
            echo -e "  ${RED}✗ Error al eliminar $carpeta${NC}"
        fi
    else
        echo -e "  ${YELLOW}$carpeta no existe, omitiendo...${NC}"
    fi
done

# Crear archivo README en la antigua estructura
echo -e "\n${YELLOW}Creando archivo README en carpetas principales para indicar la nueva ruta...${NC}"

if [ -d "tmp" ]; then
    echo "# Directorio obsoleto" > tmp/README.md
    echo "Esta estructura de directorios está obsoleta y se mantiene solo por compatibilidad." >> tmp/README.md
    echo "Por favor, utilice la nueva estructura en /almacenamiento/ según se documenta en:" >> tmp/README.md
    echo "/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md" >> tmp/README.md
    echo -e "  ${GREEN}✓ README creado en tmp/${NC}"
fi

if [ -d "publico" ]; then
    # Solo crear README en publico/logs y publico/temp si no existen ya
    mkdir -p publico/logs
    mkdir -p publico/temp
    
    echo "# Directorio obsoleto" > publico/logs/README.md
    echo "Esta estructura de directorios está obsoleta y se mantiene solo por compatibilidad." >> publico/logs/README.md
    echo "Por favor, utilice la nueva estructura en /almacenamiento/logs/ según se documenta en:" >> publico/logs/README.md
    echo "/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md" >> publico/logs/README.md
    echo -e "  ${GREEN}✓ README creado en publico/logs/${NC}"
    
    echo "# Directorio obsoleto" > publico/temp/README.md
    echo "Esta estructura de directorios está obsoleta y se mantiene solo por compatibilidad." >> publico/temp/README.md
    echo "Por favor, utilice la nueva estructura en /almacenamiento/tmp/ según se documenta en:" >> publico/temp/README.md
    echo "/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md" >> publico/temp/README.md
    echo -e "  ${GREEN}✓ README creado en publico/temp/${NC}"
fi

echo -e "\n${GREEN}=============================================="
echo -e "LIMPIEZA COMPLETADA"
echo -e "===============================================${NC}\n"

echo -e "Las carpetas antiguas han sido eliminadas y se han creado archivos README en las carpetas principales para indicar la nueva estructura."
echo -e "La nueva estructura de almacenamiento está lista para ser utilizada por el sistema."
