#!/bin/bash
# Script para eliminar el directorio /tmp después de la migración del php.ini
# Este script está diseñado para ejecutarse después de migrar php.ini a la nueva estructura

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Cabecera
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}    ELIMINACIÓN DEL DIRECTORIO /TMP RESIDUAL      ${NC}"
echo -e "${BLUE}==================================================${NC}"
echo ""

# Verificar que estamos en el directorio raíz del proyecto
if [ ! -d "almacenamiento" ] || [ ! -d "tmp" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raíz del proyecto${NC}"
    exit 1
fi

# Verificar que el archivo php.ini ha sido migrado
if [ ! -f "almacenamiento/config/php.ini" ]; then
    echo -e "${RED}Error: No se encontró el archivo php.ini en almacenamiento/config${NC}"
    echo -e "${RED}Por favor, ejecute primero el script migrar_php_ini.sh${NC}"
    exit 1
fi

echo -e "${YELLOW}Verificando directorio /tmp...${NC}"

# Si existe un README.md en el directorio, lo conservamos
if [ -f "tmp/README.md" ]; then
    echo -e "  ${BLUE}README.md encontrado, será conservado${NC}"
    # Crear un directorio temporal para almacenar el README
    mkdir -p tmp_backup
    cp tmp/README.md tmp_backup/
fi

echo -e "${YELLOW}Eliminando directorio /tmp...${NC}"
# Eliminar todo el directorio
rm -rf tmp

# Recrear el directorio con solo el README
mkdir -p tmp

# Si habíamos guardado el README, restaurarlo
if [ -f "tmp_backup/README.md" ]; then
    cp tmp_backup/README.md tmp/
    rm -rf tmp_backup
else
    # Si no existía un README, crear uno nuevo
    echo "# Directorio obsoleto" > tmp/README.md
    echo "Esta estructura de directorios está obsoleta y se mantiene solo por compatibilidad." >> tmp/README.md
    echo "Por favor, utilice la nueva estructura en /almacenamiento/ según se documenta en:" >> tmp/README.md
    echo "/documentacion/09_configuracion_mantenimiento/estructura_almacenamiento.md" >> tmp/README.md
fi

echo -e "${GREEN}✅ Directorio /tmp limpiado exitosamente${NC}"
echo -e "${GREEN}✅ Se ha conservado un README.md como referencia${NC}"
echo ""
