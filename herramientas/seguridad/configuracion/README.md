# Configuración de Seguridad

Scripts para configurar aspectos de seguridad del sistema.

## Scripts Disponibles

### configurar_cron.sh
**Propósito**: Configuración automática de tareas cron para monitorización de seguridad 24/7.

**Funcionalidades**:
- Configuración automática de 4 tareas cron
- Monitorización cada 15 minutos
- Verificación diaria completa
- Limpieza semanal de logs
- Corrección automática de permisos
- Backup automático del crontab existente

**Uso**:
```bash
./configurar_cron.sh
```

**Requisitos**: 
- Permisos de administrador
- Servicio cron activo
