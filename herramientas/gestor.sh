#!/bin/bash

# ============================================================================
# Script Maestro de Herramientas Administrativas - AUTOEXAM2
# ============================================================================
# Gestiona todas las herramientas administrativas de forma centralizada
# Autor: Github Copilot
# Fecha: 14/06/2025
# ============================================================================

# Configuración
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TOOLS_DIR="$PROJECT_ROOT/herramientas"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Función para mostrar banner
mostrar_banner() {
    echo -e "${CYAN}"
    echo "============================================================================"
    echo "           HERRAMIENTAS ADMINISTRATIVAS - AUTOEXAM2"
    echo "============================================================================"
    echo -e "${NC}"
}

# Función para mostrar menú principal
mostrar_menu() {
    echo -e "${YELLOW}Seleccione una categoría de herramientas:${NC}"
    echo ""
    echo -e "${BLUE}🔒 1)${NC} Seguridad"
    echo -e "${BLUE}👥 2)${NC} Administración"
    echo -e "${BLUE}🩺 3)${NC} Diagnóstico"
    echo -e "${BLUE}🔧 4)${NC} Mantenimiento"
    echo -e "${BLUE}📊 5)${NC} Ver estado del sistema"
    echo -e "${BLUE}❓ 6)${NC} Ayuda"
    echo -e "${RED}0)${NC} Salir"
    echo ""
    echo -n "Opción: "
}

# Función para mostrar menú de seguridad
menu_seguridad() {
    echo -e "${YELLOW}🔒 HERRAMIENTAS DE SEGURIDAD${NC}"
    echo ""
    echo -e "${GREEN}1)${NC} Configurar monitorización automática (cron)"
    echo -e "${GREEN}2)${NC} Migrar configuración de entorno"
    echo -e "${GREEN}3)${NC} Ejecutar monitor de seguridad"
    echo -e "${GREEN}4)${NC} Ejecutar tests de integración"
    echo -e "${GREEN}5)${NC} Validar configuración de producción"
    echo ""
    echo -e "${CYAN}═══ TESTS INDIVIDUALES ═══${NC}"
    echo -e "${GREEN}6)${NC} Test biblioteca Env"
    echo -e "${GREEN}7)${NC} Test detección de instalación"
    echo -e "${GREEN}8)${NC} Test autocompletado del instalador"
    echo ""
    echo -e "${CYAN}═══ SUITE COMPLETA ═══${NC}"
    echo -e "${GREEN}9)${NC} Ejecutar suite completa de seguridad"
    echo -e "${BLUE}0)${NC} Volver al menú principal"
    echo ""
    echo -n "Opción: "
}

# Función para mostrar menú de mantenimiento
menu_mantenimiento() {
    echo -e "${YELLOW}🔧 HERRAMIENTAS DE MANTENIMIENTO${NC}"
    echo ""
    echo -e "${BLUE}1)${NC} Migrar a estructura unificada de almacenamiento"
    echo -e "${BLUE}2)${NC} Limpiar archivos de logs"
    echo -e "${BLUE}3)${NC} Limpiar archivos temporales y caché"
    echo -e "${BLUE}4)${NC} Ver estructura de almacenamiento"
    echo -e "${BLUE}5)${NC} Ver estadísticas de archivos"
    echo -e "${BLUE}6)${NC} Verificar rutas obsoletas"
    echo -e "${BLUE}7)${NC} Inicializar estructura de almacenamiento"
    echo -e "${RED}8)${NC} Eliminar directorios antiguos"
    echo -e "${BLUE}9)${NC} Limpiar directorios residuales en /publico"
    echo -e "${BLUE}10)${NC} Actualizar rutas en archivos del instalador"
    echo -e "${BLUE}11)${NC} Migrar php.ini a nueva estructura"
    echo -e "${BLUE}12)${NC} Limpiar directorio /tmp residual"
    echo -e "${RED}13)${NC} Eliminar completamente directorio /tmp"
    echo -e "${BLUE}14)${NC} Actualizar referencias a /tmp en instalador"
    echo -e "${RED}0)${NC} Volver al menú principal"
    echo ""
    echo -n "Opción: "
}

# Función para ejecutar herramientas de mantenimiento
ejecutar_mantenimiento() {
    case $1 in
        1)
            echo -e "${CYAN}Migrando a estructura unificada de almacenamiento...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/migrar_almacenamiento.sh"
            "$TOOLS_DIR/mantenimiento/migrar_almacenamiento.sh"
            ;;
        2)
            echo -e "${CYAN}Limpiando archivos de logs...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/limpiar_logs.sh"
            "$TOOLS_DIR/mantenimiento/limpiar_logs.sh"
            ;;
        3)
            echo -e "${CYAN}Limpiando archivos temporales y caché...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/limpiar_cache.sh"
            "$TOOLS_DIR/mantenimiento/limpiar_cache.sh"
            ;;
        4)
            echo -e "${CYAN}Estructura de almacenamiento:${NC}"
            echo ""
            echo -e "${YELLOW}== Estructura principal ==${NC}"
            ls -la "$PROJECT_ROOT/almacenamiento/"
            echo ""
            echo -e "${YELLOW}== Logs ==${NC}"
            ls -la "$PROJECT_ROOT/almacenamiento/logs/"
            echo ""
            echo -e "${YELLOW}== Cache ==${NC}"
            ls -la "$PROJECT_ROOT/almacenamiento/cache/"
            echo ""
            echo -e "${YELLOW}== Temporales ==${NC}"
            ls -la "$PROJECT_ROOT/almacenamiento/tmp/"
            echo ""
            echo -e "${YELLOW}== Subidas ==${NC}"
            ls -la "$PROJECT_ROOT/almacenamiento/subidas/"
            ;;
        5)
            echo -e "${CYAN}Estadísticas de archivos:${NC}"
            echo ""
            echo -e "${YELLOW}== Tamaño total de almacenamiento ==${NC}"
            du -sh "$PROJECT_ROOT/almacenamiento/"
            echo ""
            echo -e "${YELLOW}== Desglose por directorio ==${NC}"
            du -sh "$PROJECT_ROOT/almacenamiento/"*
            echo ""
            echo -e "${YELLOW}== Archivos más grandes ==${NC}"
            find "$PROJECT_ROOT/almacenamiento/" -type f -not -name ".gitkeep" -exec du -sh {} \; | sort -rh | head -n 10
            ;;
        6)
            echo -e "${CYAN}Verificando rutas obsoletas...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/verificar_rutas_obsoletas.sh"
            "$TOOLS_DIR/mantenimiento/verificar_rutas_obsoletas.sh"
            ;;
        7)
            echo -e "${CYAN}Inicializando estructura de almacenamiento...${NC}"
            php "$TOOLS_DIR/mantenimiento/inicializar_almacenamiento.php"
            ;;
        8)
            echo -e "${CYAN}Eliminando directorios antiguos...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/eliminar_directorios_antiguos.sh"
            "$TOOLS_DIR/mantenimiento/eliminar_directorios_antiguos.sh"
            ;;
        9)
            echo -e "${CYAN}Limpiando directorios residuales en /publico...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/limpiar_directorios_residuales.sh"
            "$TOOLS_DIR/mantenimiento/limpiar_directorios_residuales.sh"
            ;;
        10)
            echo -e "${CYAN}Actualizando rutas en archivos del instalador...${NC}"
            php "$TOOLS_DIR/mantenimiento/actualizar_rutas_instalador.php"
            ;;
        11)
            echo -e "${CYAN}Migrando php.ini a la nueva estructura...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/migrar_php_ini.sh"
            "$TOOLS_DIR/mantenimiento/migrar_php_ini.sh"
            ;;
        12)
            echo -e "${CYAN}Limpiando directorio /tmp residual...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/limpiar_tmp_residual.sh"
            "$TOOLS_DIR/mantenimiento/limpiar_tmp_residual.sh"
            ;;
        13)
            echo -e "${CYAN}Eliminando completamente directorio /tmp...${NC}"
            chmod +x "$TOOLS_DIR/mantenimiento/eliminar_tmp_completo.sh"
            "$TOOLS_DIR/mantenimiento/eliminar_tmp_completo.sh"
            ;;
        14)
            echo -e "${CYAN}Actualizando referencias a /tmp en instalador...${NC}"
            php "$TOOLS_DIR/mantenimiento/actualizar_referencias_tmp.php"
            ;;
        *)
            echo -e "${RED}Opción inválida${NC}"
            ;;
    esac
}

# Función para mostrar estado del sistema
mostrar_estado() {
    echo -e "${YELLOW}📊 ESTADO DEL SISTEMA${NC}"
    echo ""
    
    # Verificar archivos clave
    echo -e "${BLUE}Verificando archivos clave:${NC}"
    
    if [ -f "$PROJECT_ROOT/.env" ]; then
        echo -e "${GREEN}✅ .env configurado${NC}"
    else
        echo -e "${RED}❌ .env no encontrado${NC}"
    fi
    
    if [ -f "$PROJECT_ROOT/config/config.php" ]; then
        echo -e "${GREEN}✅ config.php presente${NC}"
    else
        echo -e "${RED}❌ config.php no encontrado${NC}"
    fi
    
    # Verificar permisos
    echo -e "${BLUE}Verificando permisos:${NC}"
    if [ -w "$PROJECT_ROOT/almacenamiento" ]; then
        echo -e "${GREEN}✅ Directorio almacenamiento escribible${NC}"
    else
        echo -e "${RED}❌ Directorio almacenamiento no escribible${NC}"
    fi
    
    # Verificar herramientas
    echo -e "${BLUE}Herramientas disponibles:${NC}"
    TOOLS_COUNT=$(find "$TOOLS_DIR" -name "*.php" -o -name "*.sh" | wc -l)
    echo -e "${GREEN}📦 $TOOLS_COUNT herramientas disponibles${NC}"
    
    # Verificar estructura de almacenamiento
    echo -e "${BLUE}Estructura de almacenamiento:${NC}"
    if [ -d "$PROJECT_ROOT/almacenamiento/logs" ] && [ -d "$PROJECT_ROOT/almacenamiento/cache" ] && [ -d "$PROJECT_ROOT/almacenamiento/tmp" ]; then
        echo -e "${GREEN}✅ Estructura de almacenamiento correcta${NC}"
    else
        echo -e "${RED}❌ Estructura de almacenamiento incompleta${NC}"
    fi
    
    # Verificar estado de migración
    echo -e "${BLUE}Estado de migración:${NC}"
    if [ -d "$PROJECT_ROOT/tmp/logs" ] || [ -d "$PROJECT_ROOT/publico/temp" ] || [ -d "$PROJECT_ROOT/publico/uploads" ] || [ -d "$PROJECT_ROOT/publico/logs" ] || [ -d "$PROJECT_ROOT/publico/subidas" ] || [ -f "$PROJECT_ROOT/tmp/php.ini" ]; then
        echo -e "${YELLOW}⚠️ Migración incompleta - ejecute 'Eliminar directorios antiguos', 'Limpiar directorios residuales' y 'Migrar php.ini'${NC}"
    else
        echo -e "${GREEN}✅ Migración completa (14/06/2025)${NC}"
    fi
}

# Función para mostrar ayuda
mostrar_ayuda() {
    echo -e "${YELLOW}❓ AYUDA${NC}"
    echo ""
    echo -e "Este script proporciona acceso a todas las herramientas administrativas del sistema AUTOEXAM2."
    echo -e "Seleccione una categoría y luego la herramienta específica que desea utilizar."
    echo ""
    echo -e "${BLUE}Categorías disponibles:${NC}"
    echo -e "  🔒 ${YELLOW}Seguridad${NC} - Herramientas relacionadas con la seguridad del sistema"
    echo -e "  👥 ${YELLOW}Administración${NC} - Herramientas para administrar usuarios, roles, etc."
    echo -e "  🩺 ${YELLOW}Diagnóstico${NC} - Herramientas para diagnosticar problemas"
    echo -e "  🔧 ${YELLOW}Mantenimiento${NC} - Herramientas para mantener el sistema"
    echo -e "  📊 ${YELLOW}Estado del sistema${NC} - Muestra el estado actual del sistema"
    echo ""
    echo -e "${BLUE}Uso:${NC}"
    echo -e "  ./gestor.sh [categoría] [herramienta]"
    echo ""
    echo -e "${BLUE}Ejemplos:${NC}"
    echo -e "  ./gestor.sh                  # Muestra el menú principal"
    echo -e "  ./gestor.sh mantenimiento    # Accede directamente al menú de mantenimiento"
    echo -e "  ./gestor.sh mantenimiento 3  # Ejecuta la herramienta 3 de mantenimiento"
}

# Función para mostrar menú de administración (a implementar en el futuro)
menu_administracion() {
    echo -e "${YELLOW}👥 HERRAMIENTAS DE ADMINISTRACIÓN${NC}"
    echo ""
    echo -e "${GREEN}1)${NC} Gestionar usuarios"
    echo -e "${GREEN}2)${NC} Gestionar roles y permisos"
    echo -e "${GREEN}3)${NC} Configuración del sistema"
    echo -e "${BLUE}0)${NC} Volver al menú principal"
    echo ""
    echo -n "Opción: "
}

# Función para mostrar menú de diagnóstico (a implementar en el futuro)
menu_diagnostico() {
    echo -e "${YELLOW}🩺 HERRAMIENTAS DE DIAGNÓSTICO${NC}"
    echo ""
    echo -e "${GREEN}1)${NC} Verificar estado de la base de datos"
    echo -e "${GREEN}2)${NC} Verificar conectividad"
    echo -e "${GREEN}3)${NC} Verificar logs de errores"
    echo -e "${BLUE}0)${NC} Volver al menú principal"
    echo ""
    echo -n "Opción: "
}

# Función principal
main() {
    # Manejar argumentos de línea de comandos
    if [ "$1" = "mantenimiento" ]; then
        mostrar_banner
        if [ -n "$2" ]; then
            ejecutar_mantenimiento "$2"
            echo ""
            echo -e "${CYAN}Presione Enter para continuar...${NC}"
            read -r
            exit 0
        fi
        while true; do
            echo ""
            menu_mantenimiento
            read -r sub_opcion
            
            if [ "$sub_opcion" = "0" ]; then
                break
            else
                echo ""
                ejecutar_mantenimiento "$sub_opcion"
                echo ""
                echo -e "${CYAN}Presione Enter para continuar...${NC}"
                read -r
            fi
        done
        main
        return
    fi
    
    # Menú principal
    while true; do
        clear
        mostrar_banner
        mostrar_menu
        read -r opcion
        
        case $opcion in
            1)
                clear
                mostrar_banner
                while true; do
                    echo ""
                    menu_seguridad
                    read -r sub_opcion
                    
                    if [ "$sub_opcion" = "0" ]; then
                        break
                    else
                        echo ""
                        echo -e "${CYAN}Esta función estará disponible próximamente${NC}"
                        echo ""
                        echo -e "${CYAN}Presione Enter para continuar...${NC}"
                        read -r
                    fi
                done
                ;;
            2)
                clear
                mostrar_banner
                while true; do
                    echo ""
                    menu_administracion
                    read -r sub_opcion
                    
                    if [ "$sub_opcion" = "0" ]; then
                        break
                    else
                        echo ""
                        echo -e "${CYAN}Esta función estará disponible próximamente${NC}"
                        echo ""
                        echo -e "${CYAN}Presione Enter para continuar...${NC}"
                        read -r
                    fi
                done
                ;;
            3)
                clear
                mostrar_banner
                while true; do
                    echo ""
                    menu_diagnostico
                    read -r sub_opcion
                    
                    if [ "$sub_opcion" = "0" ]; then
                        break
                    else
                        echo ""
                        echo -e "${CYAN}Esta función estará disponible próximamente${NC}"
                        echo ""
                        echo -e "${CYAN}Presione Enter para continuar...${NC}"
                        read -r
                    fi
                done
                ;;
            4)
                clear
                mostrar_banner
                while true; do
                    echo ""
                    menu_mantenimiento
                    read -r sub_opcion
                    
                    if [ "$sub_opcion" = "0" ]; then
                        break
                    else
                        echo ""
                        ejecutar_mantenimiento "$sub_opcion"
                        echo ""
                        echo -e "${CYAN}Presione Enter para continuar...${NC}"
                        read -r
                    fi
                done
                ;;
            5)
                clear
                mostrar_banner
                mostrar_estado
                echo ""
                echo -e "${CYAN}Presione Enter para continuar...${NC}"
                read -r
                ;;
            6)
                clear
                mostrar_banner
                mostrar_ayuda
                echo ""
                echo -e "${CYAN}Presione Enter para continuar...${NC}"
                read -r
                ;;
            0)
                echo ""
                echo -e "${GREEN}¡Gracias por usar las herramientas administrativas de AUTOEXAM2!${NC}"
                echo ""
                exit 0
                ;;
            *)
                echo ""
                echo -e "${RED}Opción inválida${NC}"
                echo ""
                echo -e "${CYAN}Presione Enter para continuar...${NC}"
                read -r
                ;;
        esac
    done
}

# Ejecutar la función principal
main "$@"
