#!/bin/bash

# Script para limpiar archivos temporales y caché
# Autor: Github Copilot
# Fecha: 13/06/2025

# Definir colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Imprimir mensaje de cabecera
echo -e "${GREEN}=============================================="
echo -e "LIMPIEZA DE ARCHIVOS TEMPORALES Y CACHÉ - AUTOEXAM2"
echo -e "===============================================${NC}\n"

# Verificar que estamos en el directorio correcto
if [ ! -d "app" ] || [ ! -d "config" ] || [ ! -f "index.php" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde la raíz del proyecto AUTOEXAM2${NC}"
    exit 1
fi

# Definir directorios a limpiar
TEMP_DIR="almacenamiento/tmp"
CACHE_DIR="almacenamiento/cache"

# Preguntar al usuario si desea continuar
echo -e "${YELLOW}Este script eliminará archivos temporales y de caché.${NC}"
read -p "¿Desea continuar? (s/n): " CONFIRM

if [[ $CONFIRM != "s" && $CONFIRM != "S" ]]; then
    echo -e "${YELLOW}Operación cancelada.${NC}"
    exit 0
fi

# Verificar si los directorios existen
if [ ! -d "$TEMP_DIR" ]; then
    echo -e "${RED}Error: El directorio temporal no existe: $TEMP_DIR${NC}"
    exit 1
fi

if [ ! -d "$CACHE_DIR" ]; then
    echo -e "${RED}Error: El directorio de caché no existe: $CACHE_DIR${NC}"
    exit 1
fi

# Limpiar directorio temporal
echo -e "${YELLOW}Limpiando archivos temporales...${NC}"
TEMP_FILES=$(find $TEMP_DIR -type f -not -name ".gitkeep")
TEMP_COUNT=0

if [ -z "$TEMP_FILES" ]; then
    echo -e "${GREEN}No se encontraron archivos temporales para eliminar.${NC}"
else
    for file in $TEMP_FILES; do
        echo "Eliminando: $file"
        rm -f "$file"
        TEMP_COUNT=$((TEMP_COUNT+1))
    done
    
    echo -e "${GREEN}Se han eliminado $TEMP_COUNT archivos temporales.${NC}"
fi

# Limpiar directorios vacíos en /tmp (excepto los directorios base)
echo -e "\n${YELLOW}Limpiando directorios temporales vacíos...${NC}"
find $TEMP_DIR -mindepth 1 -type d -empty -not -path "$TEMP_DIR/uploads" -not -path "$TEMP_DIR/sesiones" -exec rmdir {} \; 2>/dev/null
echo -e "${GREEN}Directorios vacíos eliminados.${NC}"

# Limpiar directorio de caché
echo -e "\n${YELLOW}Limpiando archivos de caché...${NC}"
CACHE_FILES=$(find $CACHE_DIR -type f -not -name ".gitkeep")
CACHE_COUNT=0

if [ -z "$CACHE_FILES" ]; then
    echo -e "${GREEN}No se encontraron archivos de caché para eliminar.${NC}"
else
    for file in $CACHE_FILES; do
        echo "Eliminando: $file"
        rm -f "$file"
        CACHE_COUNT=$((CACHE_COUNT+1))
    done
    
    echo -e "${GREEN}Se han eliminado $CACHE_COUNT archivos de caché.${NC}"
fi

# Limpiar directorios vacíos en /cache (excepto los directorios base)
echo -e "\n${YELLOW}Limpiando directorios de caché vacíos...${NC}"
find $CACHE_DIR -mindepth 1 -type d -empty -not -path "$CACHE_DIR/app" -not -path "$CACHE_DIR/vistas" -not -path "$CACHE_DIR/datos" -exec rmdir {} \; 2>/dev/null
echo -e "${GREEN}Directorios vacíos eliminados.${NC}"

# Recrear estructura básica
echo -e "\n${YELLOW}Recreando estructura básica...${NC}"
mkdir -p $TEMP_DIR/uploads $TEMP_DIR/sesiones
mkdir -p $CACHE_DIR/app $CACHE_DIR/vistas $CACHE_DIR/datos

# Verificar si tenemos permisos para crear archivos en los directorios
touch $TEMP_DIR/uploads/.gitkeep $TEMP_DIR/sesiones/.gitkeep
touch $CACHE_DIR/app/.gitkeep $CACHE_DIR/vistas/.gitkeep $CACHE_DIR/datos/.gitkeep

echo -e "${GREEN}Estructura básica recreada.${NC}"

echo -e "\n${GREEN}=============================================="
echo -e "LIMPIEZA COMPLETADA"
echo -e "===============================================${NC}\n"
