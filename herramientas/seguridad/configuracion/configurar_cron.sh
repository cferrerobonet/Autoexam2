#!/bin/bash
# AUTOEXAM2 - Script de configuración de tareas cron para monitorización
# 
# Este script configura automáticamente las tareas cron necesarias para
# el monitoreo de seguridad del sistema AUTOEXAM2
#
# Uso: ./configurar_cron.sh [email_alertas]

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar mensajes con color
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Obtener directorio del script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_PATH="$SCRIPT_DIR"

# Email de alertas (argumento o solicitar al usuario)
ALERT_EMAIL="$1"
if [ -z "$ALERT_EMAIL" ]; then
    echo -n "Ingrese el email para alertas de seguridad: "
    read ALERT_EMAIL
fi

# Validar formato de email básico
if [[ ! "$ALERT_EMAIL" =~ ^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$ ]]; then
    log_error "Formato de email inválido: $ALERT_EMAIL"
    exit 1
fi

log_info "Configurando tareas cron para AUTOEXAM2..."
log_info "Proyecto: $PROJECT_PATH"
log_info "Email de alertas: $ALERT_EMAIL"

# Verificar que los scripts necesarios existen
if [ ! -f "$PROJECT_PATH/monitor_instalador.php" ]; then
    log_error "No se encontró el script monitor_instalador.php"
    exit 1
fi

if [ ! -x "$PROJECT_PATH/monitor_instalador.php" ]; then
    log_warn "El script monitor_instalador.php no es ejecutable, corrigiendo..."
    chmod +x "$PROJECT_PATH/monitor_instalador.php"
fi

# Crear backup del crontab actual
BACKUP_FILE="/tmp/crontab_backup_$(date +%Y%m%d_%H%M%S).txt"
crontab -l > "$BACKUP_FILE" 2>/dev/null || true
log_info "Backup del crontab actual guardado en: $BACKUP_FILE"

# Crear archivo temporal con las nuevas tareas cron
TEMP_CRON="/tmp/autoexam2_cron_$(date +%Y%m%d_%H%M%S).txt"

# Copiar crontab existente (si existe)
crontab -l > "$TEMP_CRON" 2>/dev/null || echo "# Crontab inicial para AUTOEXAM2" > "$TEMP_CRON"

# Verificar si ya existen tareas de AUTOEXAM2
if grep -q "AUTOEXAM2" "$TEMP_CRON"; then
    log_warn "Se detectaron tareas cron existentes para AUTOEXAM2"
    echo -n "¿Desea reemplazarlas? (y/N): "
    read REPLACE
    
    if [[ "$REPLACE" =~ ^[Yy]$ ]]; then
        # Eliminar líneas existentes de AUTOEXAM2
        grep -v "AUTOEXAM2" "$TEMP_CRON" > "${TEMP_CRON}.tmp"
        mv "${TEMP_CRON}.tmp" "$TEMP_CRON"
        log_info "Tareas cron existentes eliminadas"
    else
        log_info "Manteniendo configuración existente"
        exit 0
    fi
fi

# Agregar nuevas tareas cron
cat >> "$TEMP_CRON" << EOF

# AUTOEXAM2 - Tareas de monitorización de seguridad
# Configurado automáticamente el $(date)

# Verificación de seguridad cada 15 minutos
*/15 * * * * cd "$PROJECT_PATH" && php monitor_instalador.php --check-once --alert-email="$ALERT_EMAIL" >/dev/null 2>&1

# Verificación de integridad diaria a las 02:00
0 2 * * * cd "$PROJECT_PATH" && php monitor_instalador.php --check-once --alert-email="$ALERT_EMAIL" --full-check >/dev/null 2>&1

# Limpieza de logs antiguos semanal (domingos a las 03:00)
0 3 * * 0 find "$PROJECT_PATH/tmp/logs" -name "*.log" -mtime +30 -delete 2>/dev/null

# Verificación de permisos de archivos críticos diaria a las 01:30
30 1 * * * cd "$PROJECT_PATH" && [ -f .env ] && chmod 600 .env; [ -f config/config.php ] && chmod 644 config/config.php

EOF

# Instalar el nuevo crontab
if crontab "$TEMP_CRON"; then
    log_info "Tareas cron configuradas correctamente"
    
    # Mostrar las tareas instaladas
    echo ""
    log_info "Tareas cron activas para AUTOEXAM2:"
    echo "----------------------------------------"
    crontab -l | grep -A 10 "AUTOEXAM2"
    echo "----------------------------------------"
    
    # Información sobre las tareas
    echo ""
    log_info "Descripción de las tareas configuradas:"
    echo "• Cada 15 minutos: Verificación rápida de seguridad"
    echo "• Diariamente 02:00: Verificación completa de integridad"
    echo "• Domingos 03:00: Limpieza de logs antiguos (>30 días)"
    echo "• Diariamente 01:30: Verificación de permisos de archivos"
    
    echo ""
    log_info "Para ver los logs de monitorización:"
    echo "tail -f $PROJECT_PATH/tmp/logs/monitor_*.log"
    
    echo ""
    log_info "Para desactivar las tareas cron, ejecute:"
    echo "crontab -e"
    echo "Y elimine las líneas que contienen 'AUTOEXAM2'"
    
else
    log_error "Error al instalar las tareas cron"
    log_info "Restaurando backup desde: $BACKUP_FILE"
    crontab "$BACKUP_FILE" 2>/dev/null || log_warn "No se pudo restaurar el backup"
    exit 1
fi

# Limpiar archivos temporales
rm -f "$TEMP_CRON"

# Verificar que el servicio cron está ejecutándose
if systemctl is-active --quiet cron 2>/dev/null || systemctl is-active --quiet crond 2>/dev/null; then
    log_info "El servicio cron está ejecutándose correctamente"
elif pgrep -x "cron" > /dev/null || pgrep -x "crond" > /dev/null; then
    log_info "El servicio cron está ejecutándose"
else
    log_warn "El servicio cron podría no estar ejecutándose"
    log_info "Para iniciar el servicio cron:"
    log_info "  Ubuntu/Debian: sudo systemctl start cron"
    log_info "  CentOS/RHEL:   sudo systemctl start crond"
fi

# Prueba inmediata del monitor
echo ""
log_info "Ejecutando prueba del monitor de seguridad..."
cd "$PROJECT_PATH"
if php monitor_instalador.php --check-once --alert-email="$ALERT_EMAIL"; then
    log_info "✓ Monitor de seguridad funcionando correctamente"
else
    log_warn "⚠ Se detectaron problemas en el monitor de seguridad"
fi

echo ""
log_info "Configuración de cron completada exitosamente"
log_info "El sistema comenzará a monitorear automáticamente la seguridad"

exit 0
