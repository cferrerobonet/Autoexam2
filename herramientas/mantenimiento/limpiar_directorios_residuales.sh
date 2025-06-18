#!/bin/bash
# Script para finalizar la limpieza de directorios residuales en /publico
# Este script elimina los directorios vacíos o con solo README.md en /publico
# que ya han sido migrados a la nueva estructura en /almacenamiento

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Cabecera
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}    LIMPIEZA FINAL DE DIRECTORIOS EN /PUBLICO     ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Función para verificar si un directorio solo contiene el README.md
function solo_contiene_readme() {
    local dir="$1"
    local count=$(find "$dir" -type f | wc -l | tr -d ' ')
    local has_readme=$(find "$dir" -name "README.md" | wc -l | tr -d ' ')
    
    # Si solo hay un archivo y es README.md
    if [ "$count" -eq 1 ] && [ "$has_readme" -eq 1 ]; then
        return 0 # true en bash
    else
        return 1 # false en bash
    fi
}

# Directorio raíz del proyecto
ROOT_DIR="$(pwd)"

# Verificar que estamos en el directorio correcto
if [ ! -d "publico" ] || [ ! -d "almacenamiento" ]; then
    echo -e "${RED}Error: Este script debe ejecutarse desde el directorio raíz del proyecto${NC}"
    echo -e "${RED}No se encontraron los directorios 'publico' o 'almacenamiento'${NC}"
    exit 1
fi

echo -e "${YELLOW}Verificando directorios residuales en /publico...${NC}"
echo ""

# Array de directorios a verificar
DIRS_TO_CHECK=(
    "publico/logs"
    "publico/temp"
    "publico/tmp"
    "publico/uploads"
    "publico/subidas"
)

# Verificar cada directorio
for dir in "${DIRS_TO_CHECK[@]}"; do
    if [ -d "$dir" ]; then
        echo -e "Verificando $dir..."
        
        # Verificar si está vacío o solo tiene README.md
        if [ -z "$(ls -A "$dir")" ]; then
            echo -e "  ${YELLOW}El directorio está vacío${NC}"
            echo -e "  ${GREEN}→ Eliminando directorio vacío${NC}"
            rmdir "$dir"
        elif solo_contiene_readme "$dir"; then
            echo -e "  ${YELLOW}El directorio solo contiene README.md${NC}"
            
            # Comprobar que ya existe la carpeta de destino
            case "$dir" in
                "publico/logs")
                    if [ -d "almacenamiento/logs" ]; then
                        echo -e "  ${GREEN}→ Eliminando directorio con README${NC}"
                        rm -rf "$dir"
                    else
                        echo -e "  ${RED}No se puede eliminar: el directorio de destino no existe${NC}"
                    fi
                    ;;
                "publico/temp"|"publico/tmp")
                    if [ -d "almacenamiento/tmp" ]; then
                        echo -e "  ${GREEN}→ Eliminando directorio con README${NC}"
                        rm -rf "$dir"
                    else
                        echo -e "  ${RED}No se puede eliminar: el directorio de destino no existe${NC}"
                    fi
                    ;;
                "publico/uploads"|"publico/subidas")
                    if [ -d "almacenamiento/subidas" ]; then
                        echo -e "  ${GREEN}→ Eliminando directorio con README${NC}"
                        rm -rf "$dir"
                    else
                        echo -e "  ${RED}No se puede eliminar: el directorio de destino no existe${NC}"
                    fi
                    ;;
            esac
        else
            echo -e "  ${YELLOW}El directorio contiene archivos. No se eliminará.${NC}"
            echo -e "  ${BLUE}Contenido:${NC}"
            ls -la "$dir"
        fi
    else
        echo -e "El directorio $dir no existe."
    fi
    echo ""
done

echo -e "${GREEN}Limpieza finalizada.${NC}"
echo -e "${YELLOW}Nota: Si hay directorios que no se pudieron eliminar porque contienen archivos,${NC}"
echo -e "${YELLOW}revisar manualmente si esos archivos son necesarios y migrarlos a /almacenamiento${NC}"
echo -e "${YELLOW}antes de eliminar el directorio.${NC}"
