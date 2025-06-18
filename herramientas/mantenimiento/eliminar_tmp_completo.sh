#!/bin/bash
# Script para eliminar completamente el directorio /tmp
# Este script forma parte de la unificación de la estructura de almacenamiento

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Cabecera
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}    ELIMINACIÓN COMPLETA DEL DIRECTORIO /TMP      ${NC}"
echo -e "${BLUE}==================================================${NC}"
echo ""

# Verificar que estamos en el directorio raíz del proyecto
if [ ! -d "almacenamiento" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raíz del proyecto${NC}"
    exit 1
fi

# Verificar que el php.ini está migrado correctamente
if [ ! -f "almacenamiento/config/php.ini" ]; then
    echo -e "${RED}Error: No se encontró el archivo php.ini en almacenamiento/config${NC}"
    echo -e "${RED}Por favor, ejecute primero el script migrar_php_ini.sh${NC}"
    exit 1
fi

echo -e "${YELLOW}Verificando referencias a /tmp en el código...${NC}"

# Buscar referencias a /tmp en archivos PHP
echo -e "  Buscando en archivos PHP..."
REFERENCIAS=$(grep -r "tmp/" --include="*.php" --exclude-dir=vendor . | wc -l | tr -d ' ')
if [ "$REFERENCIAS" -gt 0 ]; then
    echo -e "  ${RED}⚠️ Se encontraron $REFERENCIAS referencias a 'tmp/' en archivos PHP${NC}"
    echo -e "  ${RED}⚠️ Por favor, verifica estas referencias antes de eliminar el directorio${NC}"
    echo ""
    grep -r "tmp/" --include="*.php" --exclude-dir=vendor . | head -n 10
    
    if [ "$REFERENCIAS" -gt 10 ]; then
        echo -e "  ${YELLOW}... y $(($REFERENCIAS - 10)) más${NC}"
    fi
    
    echo ""
    echo -e "${YELLOW}¿Desea continuar con la eliminación a pesar de las referencias? (s/n)${NC}"
    read -r CONFIRMAR
    
    if [ "$CONFIRMAR" != "s" ] && [ "$CONFIRMAR" != "S" ]; then
        echo -e "${RED}Operación cancelada por el usuario${NC}"
        exit 1
    fi
else
    echo -e "  ${GREEN}✅ No se encontraron referencias a 'tmp/' en archivos PHP${NC}"
fi

# Buscar referencias a /tmp en scripts del sistema
echo -e "  Buscando en scripts del sistema..."
REFERENCIAS_SCRIPTS=$(grep -r "tmp/" --include="*.sh" . | wc -l | tr -d ' ')
if [ "$REFERENCIAS_SCRIPTS" -gt 0 ]; then
    echo -e "  ${RED}⚠️ Se encontraron $REFERENCIAS_SCRIPTS referencias a 'tmp/' en scripts${NC}"
    echo -e "  ${RED}⚠️ Por favor, verifica estas referencias antes de eliminar el directorio${NC}"
    echo ""
    grep -r "tmp/" --include="*.sh" . | head -n 10
    
    if [ "$REFERENCIAS_SCRIPTS" -gt 10 ]; then
        echo -e "  ${YELLOW}... y $(($REFERENCIAS_SCRIPTS - 10)) más${NC}"
    fi
    
    echo ""
    echo -e "${YELLOW}¿Desea continuar con la eliminación a pesar de las referencias? (s/n)${NC}"
    read -r CONFIRMAR
    
    if [ "$CONFIRMAR" != "s" ] && [ "$CONFIRMAR" != "S" ]; then
        echo -e "${RED}Operación cancelada por el usuario${NC}"
        exit 1
    fi
else
    echo -e "  ${GREEN}✅ No se encontraron referencias a 'tmp/' en scripts${NC}"
fi

echo ""
echo -e "${RED}⚠️ ADVERTENCIA: Esta operación eliminará completamente el directorio /tmp${NC}"
echo -e "${RED}⚠️ Esta acción es irreversible${NC}"
echo ""
echo -e "${YELLOW}¿Está seguro de que desea continuar? (s/n)${NC}"
read -r CONFIRMAR

if [ "$CONFIRMAR" != "s" ] && [ "$CONFIRMAR" != "S" ]; then
    echo -e "${RED}Operación cancelada por el usuario${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}Eliminando directorio /tmp...${NC}"

# Eliminar el directorio /tmp
if rm -rf tmp; then
    echo -e "${GREEN}✅ El directorio /tmp ha sido eliminado exitosamente${NC}"
else
    echo -e "${RED}❌ Error al eliminar el directorio /tmp${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}La eliminación se ha completado correctamente.${NC}"
echo -e "${BLUE}El sistema ahora utiliza exclusivamente la estructura centralizada en /almacenamiento/${NC}"
echo ""
