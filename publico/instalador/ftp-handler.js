/**
 * Script para gestionar la selección de FTP/SFTP en el instalador
 * 
 * Este archivo contiene funciones para actualizar el puerto y las etiquetas
 * cuando el usuario cambia entre FTP y SFTP.
 */

// Función para actualizar automáticamente el puerto y etiquetas según el tipo de conexión FTP/SFTP
function actualizarPuertoFTP() {
    console.log("Ejecutando actualizarPuertoFTP");
    
    // Obtener elementos del DOM
    const tipoSelect = document.getElementById('ftp_tipo');
    if (!tipoSelect) {
        console.error("No se encontró el elemento ftp_tipo");
        return;
    }
    
    // Determinar si es SFTP según la selección
    const esSFTP = tipoSelect.value === 'sftp';
    console.log("Tipo seleccionado:", tipoSelect.value, "Es SFTP:", esSFTP);
    
    // Actualizar el puerto
    const puertoInput = document.getElementById('ftp_port');
    if (puertoInput) {
        puertoInput.value = esSFTP ? '22' : '21';
        console.log("Puerto actualizado a:", puertoInput.value);
    } else {
        console.error("No se encontró el elemento ftp_port");
    }
    
    // Actualizar etiquetas
    const labelTipoFTP = document.getElementById('label_tipo_ftp');
    const labelPuertoFTP = document.getElementById('label_puerto_ftp');
    
    if (labelTipoFTP) {
        labelTipoFTP.textContent = esSFTP ? 'SFTP' : 'FTP';
    } else {
        console.error("No se encontró el elemento label_tipo_ftp");
    }
    
    if (labelPuertoFTP) {
        labelPuertoFTP.textContent = esSFTP ? 'SFTP' : 'FTP';
    } else {
        console.error("No se encontró el elemento label_puerto_ftp");
    }
}

// Inicializar cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM cargado completamente");
    
    // Inicializar etiquetas y puerto FTP/SFTP al cargar la página
    const paso4 = document.getElementById('paso-4');
    if (paso4 && paso4.classList.contains('active')) {
        console.log("Paso 4 activo, inicializando puerto FTP/SFTP");
        
        // Obtener el selector FTP/SFTP
        const tipoSelect = document.getElementById('ftp_tipo');
        if (tipoSelect) {
            // Ejecutar una vez ahora para establecer valores iniciales
            actualizarPuertoFTP();
            
            // Y asegurarnos que el evento change esté correctamente asociado
            tipoSelect.addEventListener('change', function() {
                console.log("Cambio detectado en selector FTP/SFTP");
                actualizarPuertoFTP();
            });
        }
    }
});
