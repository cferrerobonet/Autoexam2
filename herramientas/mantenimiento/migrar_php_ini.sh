#!/bin/bash
# Script para migrar el archivo php.ini de /tmp a /almacenamiento/config
# Este script forma parte de la unificación de la estructura de almacenamiento

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Cabecera
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}    MIGRACIÓN DE PHP.INI A LA NUEVA ESTRUCTURA    ${NC}"
echo -e "${BLUE}==================================================${NC}"
echo ""

# Verificar que estamos en el directorio raíz del proyecto
if [ ! -d "almacenamiento" ] || [ ! -d "tmp" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raíz del proyecto${NC}"
    exit 1
fi

# Verificar que existe el archivo php.ini original
if [ ! -f "tmp/php.ini" ]; then
    echo -e "${RED}Error: No se encontró el archivo php.ini en /tmp${NC}"
    exit 1
fi

# Crear el directorio de destino si no existe
mkdir -p almacenamiento/config

echo -e "${YELLOW}Paso 1: Migrar php.ini a la nueva estructura${NC}"
echo -e "  Copiando archivo de tmp/php.ini a almacenamiento/config/php.ini"
cp tmp/php.ini almacenamiento/config/php.ini

# Verificar que la copia fue exitosa
if [ $? -eq 0 ]; then
    echo -e "  ${GREEN}✅ Archivo copiado correctamente${NC}"
else
    echo -e "  ${RED}❌ Error al copiar el archivo${NC}"
    exit 1
fi

echo -e "${YELLOW}Paso 2: Actualizar la ruta del error_log en el nuevo archivo${NC}"
# Actualizar la ruta de error_log en el nuevo archivo
sed -i'.bak' 's|error_log = "../almacenamiento/registros/php_errors.log"|error_log = "../almacenamiento/logs/errores/php_errors.log"|g' almacenamiento/config/php.ini
rm -f almacenamiento/config/php.ini.bak

echo -e "  ${GREEN}✅ Ruta de error_log actualizada${NC}"

echo -e "${YELLOW}Paso 3: Actualizar la referencia en el código${NC}"
echo -e "  Actualizando referencia en index.php..."

# Buscar y reemplazar la ruta en index.php
if grep -q "TMP_PATH . '/php.ini'" index.php; then
    sed -i'.bak' "s|TMP_PATH . '/php.ini'|CONFIG_PATH . '/php.ini'|g" index.php
    rm -f index.php.bak
    echo -e "  ${GREEN}✅ Referencia actualizada en index.php${NC}"
else
    echo -e "  ${YELLOW}⚠️ No se encontró la referencia esperada en index.php${NC}"
    echo -e "  Por favor, revisa manualmente el archivo index.php"
fi

echo ""
echo -e "${GREEN}Migración completada exitosamente.${NC}"
echo -e "${YELLOW}Nota: El archivo tmp/php.ini se ha mantenido por compatibilidad.${NC}"
echo -e "${YELLOW}Puede eliminarlo manualmente cuando esté seguro de que todo funciona correctamente.${NC}"
echo ""
