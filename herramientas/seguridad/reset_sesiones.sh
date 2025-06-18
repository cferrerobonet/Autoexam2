#!/bin/bash
# Archivo: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/herramientas/seguridad/reset_sesiones.sh

# Encabezado
echo "==============================================="
echo "AUTOEXAM2 - Herramienta de reinicio de sesiones"
echo "==============================================="
echo

# Verificar si estamos en el directorio correcto
if [[ ! -f "../../.env" ]]; then
    echo "ERROR: Este script debe ser ejecutado desde el directorio herramientas/seguridad"
    echo "No se puede encontrar el archivo .env"
    echo "Directorio actual: $(pwd)"
    exit 1
fi

# Obtener credenciales de la base de datos desde el archivo .env
echo "Leyendo configuración de la base de datos desde .env..."

# Leer variables del archivo .env
if [ -f "../../.env" ]; then
    # Cargar variables del archivo .env
    export $(grep -v '^#' ../../.env | xargs)
    
    # Verificar que las variables necesarias estén definidas
    if [ -z "$DB_HOST" ] || [ -z "$DB_USER" ] || [ -z "$DB_NAME" ]; then
        echo "ERROR: Faltan variables de configuración en el archivo .env"
        exit 1
    fi
else
    echo "ERROR: Archivo .env no encontrado"
    exit 1
fi

if [[ -z "$DB_HOST" || -z "$DB_USER" || -z "$DB_NAME" ]]; then
    echo "ERROR: No se pudieron obtener las credenciales de la base de datos"
    exit 1
fi

echo "Base de datos: $DB_NAME en $DB_HOST"
echo

# Mostrar menú
echo "Opciones disponibles:"
echo "1) Reparar tabla sesiones_activas (si no existe o está corrupta)"
echo "2) Cerrar todas las sesiones activas"
echo "3) Ver sesiones activas actuales"
echo "4) Salir"
echo

read -p "Seleccione una opción (1-4): " opcion

case $opcion in
    1)
        echo "Reparando tabla de sesiones..."
        
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
DROP TABLE IF EXISTS sesiones_activas;

CREATE TABLE IF NOT EXISTS sesiones_activas (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    php_session_id VARCHAR(64),
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad DATETIME,
    fecha_fin DATETIME,
    ip VARCHAR(45),
    user_agent TEXT,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE INDEX IF NOT EXISTS idx_sesiones_token ON sesiones_activas (token);
CREATE INDEX IF NOT EXISTS idx_sesiones_php_id ON sesiones_activas (php_session_id);
EOF

        if [ $? -eq 0 ]; then
            echo "✅ Tabla reparada correctamente."
        else
            echo "❌ Error al reparar la tabla."
        fi
        ;;
        
    2)
        echo "Cerrando todas las sesiones activas..."
        
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
UPDATE sesiones_activas 
SET activa = 0, fecha_fin = NOW() 
WHERE activa = 1;
EOF

        if [ $? -eq 0 ]; then
            echo "✅ Todas las sesiones han sido cerradas."
            
            # Eliminar archivos de sesión si están en el directorio por defecto
            echo "Eliminando archivos de sesión..."
            rm -f /tmp/sess_* 2>/dev/null
            rm -f ../../almacenamiento/tmp/sesiones/* 2>/dev/null
            
            echo "✅ Archivos de sesión eliminados."
        else
            echo "❌ Error al cerrar las sesiones."
        fi
        ;;
        
    3)
        echo "Sesiones activas actualmente:"
        echo "----------------------------"
        
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
SELECT sa.id_sesion, u.nombre, u.apellidos, u.rol, 
       sa.fecha_inicio, sa.ultima_actividad, sa.ip 
FROM sesiones_activas sa
JOIN usuarios u ON sa.id_usuario = u.id_usuario
WHERE sa.activa = 1
ORDER BY sa.ultima_actividad DESC;
EOF
        ;;
        
    4)
        echo "Saliendo..."
        exit 0
        ;;
        
    *)
        echo "Opción no válida"
        exit 1
        ;;
esac

echo
echo "Operación completada. Acceda a http://localhost/autoexam2/publico/diagnostico/sesion.php para verificar el estado de las sesiones."
