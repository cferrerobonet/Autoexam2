#!/bin/bash

# Script para limpiar archivos de logs antiguos
# Autor: Github Copilot
# Fecha: 13/06/2025

# Definir colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Imprimir mensaje de cabecera
echo -e "${GREEN}=============================================="
echo -e "LIMPIEZA DE LOGS - AUTOEXAM2"
echo -e "===============================================${NC}\n"

# Verificar que estamos en el directorio correcto
if [ ! -d "app" ] || [ ! -d "config" ] || [ ! -f "index.php" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde la raíz del proyecto AUTOEXAM2${NC}"
    exit 1
fi

# Definir el directorio de logs y días para mantener
LOGS_DIR="almacenamiento/logs"
KEEP_DAYS=30

# Preguntar al usuario si desea continuar
echo -e "${YELLOW}Este script eliminará archivos de logs con más de $KEEP_DAYS días de antigüedad.${NC}"
read -p "¿Desea continuar? (s/n): " CONFIRM

if [[ $CONFIRM != "s" && $CONFIRM != "S" ]]; then
    echo -e "${YELLOW}Operación cancelada.${NC}"
    exit 0
fi

# Verificar que el directorio existe
if [ ! -d "$LOGS_DIR" ]; then
    echo -e "${RED}Error: El directorio de logs no existe: $LOGS_DIR${NC}"
    exit 1
fi

echo -e "${YELLOW}Buscando archivos de logs antiguos...${NC}"

# Contar archivos antes de la limpieza
TOTAL_FILES=$(find $LOGS_DIR -type f -name "*.log" | wc -l)
echo -e "Total de archivos de log encontrados: $TOTAL_FILES"

# Buscar y eliminar archivos antiguos
OLD_FILES=$(find $LOGS_DIR -type f -name "*.log" -mtime +$KEEP_DAYS)
COUNT=0

if [ -z "$OLD_FILES" ]; then
    echo -e "${GREEN}No se encontraron archivos antiguos para eliminar.${NC}"
else
    for file in $OLD_FILES; do
        echo "Eliminando: $file"
        rm -f "$file"
        COUNT=$((COUNT+1))
    done
    
    echo -e "${GREEN}Se han eliminado $COUNT archivos de log antiguos.${NC}"
fi

# Vaciar (pero no eliminar) archivos de log muy grandes
echo -e "\n${YELLOW}Comprobando archivos de log demasiado grandes...${NC}"
LARGE_FILES=$(find $LOGS_DIR -type f -name "*.log" -size +10M)

if [ -z "$LARGE_FILES" ]; then
    echo -e "${GREEN}No se encontraron archivos de log demasiado grandes.${NC}"
else
    for file in $LARGE_FILES; do
        echo "Truncando archivo grande: $file"
        # Guardar las últimas 1000 líneas y truncar el archivo
        tail -n 1000 "$file" > "$file.tmp"
        mv "$file.tmp" "$file"
    done
    
    echo -e "${GREEN}Se han truncado los archivos de log demasiado grandes.${NC}"
fi

# Comprimir logs antiguos pero no tanto como para eliminar
echo -e "\n${YELLOW}Comprimiendo logs antiguos...${NC}"
COMPRESS_FILES=$(find $LOGS_DIR -type f -name "*.log" -mtime +7 -mtime -$KEEP_DAYS -not -name "*.gz")

if [ -z "$COMPRESS_FILES" ]; then
    echo -e "${GREEN}No se encontraron archivos para comprimir.${NC}"
else
    for file in $COMPRESS_FILES; do
        echo "Comprimiendo: $file"
        gzip -9 "$file"
    done
    
    echo -e "${GREEN}Se han comprimido los archivos de log.${NC}"
fi

echo -e "\n${GREEN}=============================================="
echo -e "LIMPIEZA COMPLETADA"
echo -e "===============================================${NC}\n"
