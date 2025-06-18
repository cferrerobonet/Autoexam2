#!/bin/bash

# Script para verificar referencias a rutas de almacenamiento obsoletas
# Autor: Github Copilot
# Fecha: 13/06/2025

# Definir colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;36m'
NC='\033[0m' # No Color

# Imprimir mensaje de cabecera
echo -e "${GREEN}=============================================="
echo -e "VERIFICACIÓN DE RUTAS OBSOLETAS - AUTOEXAM2"
echo -e "===============================================${NC}\n"

# Verificar que estamos en el directorio correcto
if [ ! -d "app" ] || [ ! -d "config" ] || [ ! -f "index.php" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde la raíz del proyecto AUTOEXAM2${NC}"
    exit 1
fi

# Definir patrones de búsqueda
PATRONES=(
    "tmp/logs"
    "tmp/cache"
    "publico/logs"
    "publico/temp"
    "publico/uploads"
    "publico/subidas"
    "almacenamiento/registros/php_errors.log"
)

# Definir tipos de archivos a verificar
TIPOS_ARCHIVOS=(
    "php"
    "sh"
    "js"
    "md"
)

# Función para verificar rutas
verificar_rutas() {
    local patron=$1
    local extension=$2
    
    echo -e "${BLUE}Buscando '$patron' en archivos .$extension...${NC}"
    
    # Excluir directorios vendor, node_modules y herramientas/mantenimiento
    ARCHIVOS=$(find . -name "*.$extension" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./herramientas/mantenimiento/*")
    
    local encontrado=0
    
    for archivo in $ARCHIVOS; do
        if grep -q "$patron" "$archivo"; then
            echo -e "  ${YELLOW}Encontrado en: $archivo${NC}"
            grep --color=always -n "$patron" "$archivo" | sed 's/^/    /'
            encontrado=1
        fi
    done
    
    if [ $encontrado -eq 0 ]; then
        echo -e "  ${GREEN}No se encontraron referencias${NC}"
    fi
    
    return $encontrado
}

echo -e "${YELLOW}Este script buscará referencias a rutas de almacenamiento obsoletas.${NC}"

TOTAL_ENCONTRADOS=0

# Verificar cada patrón en cada tipo de archivo
for patron in "${PATRONES[@]}"; do
    echo -e "\n${YELLOW}Verificando patrón: ${RED}$patron${NC}"
    
    for extension in "${TIPOS_ARCHIVOS[@]}"; do
        verificar_rutas "$patron" "$extension"
        ENCONTRADOS=$?
        TOTAL_ENCONTRADOS=$((TOTAL_ENCONTRADOS+ENCONTRADOS))
    done
done

# Mostrar resumen
echo -e "\n${YELLOW}Resumen:${NC}"
if [ $TOTAL_ENCONTRADOS -eq 0 ]; then
    echo -e "${GREEN}No se encontraron referencias a rutas obsoletas.${NC}"
else
    echo -e "${RED}Se encontraron referencias a rutas obsoletas. Por favor actualícelas según la documentación.${NC}"
    echo -e "${BLUE}Consulte: documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md${NC}"
fi

# Sugerir uso de storage.php
echo -e "\n${YELLOW}Recomendación:${NC}"
echo -e "Para actualizar las referencias, utilice las constantes definidas en config/storage.php:"
echo -e "  - ${BLUE}LOGS_PATH${NC} para logs"
echo -e "  - ${BLUE}CACHE_PATH${NC} para caché"
echo -e "  - ${BLUE}TMP_PATH${NC} para archivos temporales"
echo -e "  - ${BLUE}UPLOADS_PATH${NC} para subidas"
echo -e "\nEjemplo:"
echo -e "  Cambiar: ${RED}require_once 'tmp/logs/error.log';${NC}"
echo -e "  Por:     ${GREEN}require_once LOGS_PATH . '/error.log';${NC}"

echo -e "\n${GREEN}=============================================="
echo -e "VERIFICACIÓN COMPLETADA"
echo -e "===============================================${NC}\n"
