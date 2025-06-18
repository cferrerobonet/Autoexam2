<?php
/**
 * Clase Correo - AUTOEXAM2
 * 
 * Maneja el envío de correos electrónicos en el sistema
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.0
 */

class Correo {
    // Atributos públicos para facilitar diagnóstico
    public $de;
    public $nombre;
    public $host;
    public $puerto;
    public $usuario;
    private $contrasena; // La contraseña sigue siendo privada por seguridad
    public $seguridad;
    public $debug;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Registrar mensaje inicial de inicialización
        error_log("Inicializando clase Correo para envío de correos");
        
        // Cargar configuración de correo desde variables de entorno/configuración
        $this->host = defined('SMTP_HOST') ? SMTP_HOST : '';
        $this->puerto = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->usuario = defined('SMTP_USER') ? SMTP_USER : '';
        $this->contrasena = defined('SMTP_PASS') ? SMTP_PASS : '';
        $this->seguridad = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $this->debug = defined('DEBUG') ? DEBUG : false;
        
        // Si SMTP_FROM no está definido, usar SMTP_USER como valor predeterminado
        if (!defined('SMTP_FROM') || empty(SMTP_FROM)) {
            error_log("ADVERTENCIA: Constante SMTP_FROM no definida. Usando SMTP_USER como dirección de remitente.");
            $this->de = $this->usuario; // Usar el usuario SMTP como dirección de remitente
        } else {
            $this->de = SMTP_FROM;
        }
        
        // Nombre de remitente - si no está definido, usar un valor más personalizado
        $nombreSistema = defined('SYSTEM_NAME') ? SYSTEM_NAME : 'Sistema';
        if (!defined('SMTP_FROM_NAME') || empty(SMTP_FROM_NAME)) {
            // Intentar extraer un nombre de dominio del correo para personalizar
            $dominio = '';
            if (!empty($this->de) && strpos($this->de, '@') !== false) {
                $partes = explode('@', $this->de);
                if (isset($partes[1])) {
                    $dominio = explode('.', $partes[1])[0];
                    $dominio = ucfirst($dominio); // Primera letra en mayúscula
                }
            }
            
            $nombreDefecto = $dominio ? "$nombreSistema - $dominio" : $nombreSistema;
            error_log("ADVERTENCIA: Constante SMTP_FROM_NAME no definida. Usando '$nombreDefecto' como nombre de remitente.");
            $this->nombre = $nombreDefecto;
        } else {
            $this->nombre = SMTP_FROM_NAME;
        }
        
        // Verificaciones adicionales para la dirección de correo
        if (empty($this->de)) {
            error_log("ERROR: No se ha configurado una dirección de correo remitente (SMTP_FROM). Los correos pueden no entregarse correctamente.");
            // Establecer un valor predeterminado genérico
            $this->de = defined('SMTP_FROM') ? SMTP_FROM : 'no-reply@example.com';
        }
        
        // Registrar configuración (sin contraseña)
        error_log("Configuración de correo - De: {$this->de}, Nombre: {$this->nombre}, Host: {$this->host}, Puerto: {$this->puerto}, Usuario: {$this->usuario}, Seguridad: {$this->seguridad}, Debug: " . ($this->debug ? 'Sí' : 'No'));
    }
    
    /**
     * Envía un correo electrónico
     * 
     * @param string|array $para Dirección(es) de correo del destinatario
     * @param string $asunto Asunto del correo
     * @param string $cuerpo Cuerpo del mensaje (HTML)
     * @param array $adjuntos Archivos adjuntos
     * @return bool Éxito del envío
     */
    public function enviar($para, $asunto, $cuerpo, $adjuntos = []) {
        // Marcar inicio del intento de envío para mejor trazabilidad
        error_log("=== INICIO ENVÍO DE CORREO ===");
        error_log("Para: " . (is_array($para) ? implode(', ', $para) : $para));
        error_log("Asunto: $asunto");
        
        // Verificar configuración básica
        if (empty($this->host) || empty($this->usuario) || empty($this->contrasena)) {
            error_log("ERROR: Configuración SMTP incompleta. Host: {$this->host}, Usuario: {$this->usuario}");
            return false;
        }
        
        // Si estamos en desarrollo y no se ha forzado el debug, simular el envío
        if ((defined('DEBUG') && DEBUG) && !$this->debug) {
            error_log("CORREO SIMULADO (modo DEBUG): Para: $para, Asunto: $asunto");
            error_log("=== FIN ENVÍO DE CORREO (SIMULADO) ===");
            return true;
        }
        
        // Cuerpo reducido para logging
        $cuerpo_reducido = strlen($cuerpo) > 150 ? 
                            substr($cuerpo, 0, 150) . "..." : 
                            $cuerpo;
        error_log("Cuerpo (extracto): " . preg_replace('/\s+/', ' ', strip_tags($cuerpo_reducido)));
        
        try {
            // Verificar si PHPMailer está disponible
            $phpmailerExistsComposer = file_exists(ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
            $phpmailerExistsManual = file_exists(ROOT_PATH . '/librerias/PHPMailer/src/PHPMailer.php');
            $phpmailerAutoload = file_exists(ROOT_PATH . '/vendor/autoload.php');
            
            error_log("Verificación PHPMailer - Composer: " . ($phpmailerExistsComposer ? "Sí" : "No") . 
                     ", Manual: " . ($phpmailerExistsManual ? "Sí" : "No") . 
                     ", Autoload: " . ($phpmailerAutoload ? "Sí" : "No"));
            
            if (($phpmailerExistsComposer || $phpmailerExistsManual || $phpmailerAutoload) && 
                 class_exists('PHPMailer\\PHPMailer\\PHPMailer', true)) {
                // Usar PHPMailer 
                error_log("Usando PHPMailer para enviar correo");
                $resultado = $this->enviarConPHPMailer($para, $asunto, $cuerpo, $adjuntos);
                error_log("Resultado envío PHPMailer: " . ($resultado ? "Exitoso" : "Fallido"));
                error_log("=== FIN ENVÍO DE CORREO (" . ($resultado ? "EXITOSO" : "FALLIDO") . ") ===");
                return $resultado;
            } else {
                // Fallback a mail() de PHP
                error_log("PHPMailer no disponible. Intentando con mail() nativo");
                $resultado = $this->enviarConMailNativo($para, $asunto, $cuerpo);
                error_log("Resultado envío mail() nativo: " . ($resultado ? "Exitoso" : "Fallido"));
                error_log("=== FIN ENVÍO DE CORREO (" . ($resultado ? "EXITOSO" : "FALLIDO") . ") ===");
                return $resultado;
            }
        } catch (Exception $e) {
            error_log("EXCEPCIÓN al enviar correo: " . $e->getMessage());
            error_log("Traza: " . $e->getTraceAsString());
            error_log("=== FIN ENVÍO DE CORREO (EXCEPCIÓN) ===");
            return false;
        }
    }
    
    /**
     * Envía un correo utilizando la librería PHPMailer
     */
    private function enviarConPHPMailer($para, $asunto, $cuerpo, $adjuntos = []) {
        // Cargar PHPMailer desde Composer o desde la ubicación manual
        if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
            // Carga desde Composer
            require_once ROOT_PATH . '/vendor/autoload.php';
            error_log("Cargando PHPMailer desde Composer");
        } else if (file_exists(ROOT_PATH . '/librerias/PHPMailer/src/PHPMailer.php')) {
            // Carga desde instalación manual
            require_once ROOT_PATH . '/librerias/PHPMailer/src/Exception.php';
            require_once ROOT_PATH . '/librerias/PHPMailer/src/PHPMailer.php';
            require_once ROOT_PATH . '/librerias/PHPMailer/src/SMTP.php';
            error_log("Cargando PHPMailer desde librería manual");
        } else {
            error_log("ERROR: No se pudo encontrar PHPMailer en ninguna ubicación");
            throw new Exception("PHPMailer no está disponible");
        }
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Capturar la salida de debug para logging en caso de error
            $debug_output = '';
            $mail->Debugoutput = function($str, $level) use (&$debug_output) {
                $debug_output .= $str . "\n";
            };
            
            // Iniciar captura de cualquier salida directa
            ob_start();
            
            // Configuración del servidor con nivel de debug mejorado
            $mail->SMTPDebug = $this->debug ? 4 : 0; // Usar nivel 4 para debug completo
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->usuario;
            $mail->Password = $this->contrasena;
            $mail->SMTPSecure = $this->seguridad;
            $mail->Port = $this->puerto;
            // Configuración mejorada para caracteres especiales
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64'; // base64 asegura compatibilidad universal para caracteres especiales
            
            // Añadir timeout para evitar bloqueos
            $mail->Timeout = 60; // 60 segundos de timeout
            
            // Manejo de certificados SSL para servidores con certificados autofirmados
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Configuración SMTP adicional
            $mail->SMTPKeepAlive = true; // Mantener la conexión viva
            
            // Remitente - sin codificación adicional, PHPMailer lo hará correctamente
            $mail->setFrom($this->de, $this->nombre);
            $mail->addReplyTo($this->de, $this->nombre);
            // Añadir X-Mailer para consistencia
            $mail->XMailer = SYSTEM_NAME . ' PHPMailer UTF-8';
            
            // Destinatarios
            if (is_array($para)) {
                foreach ($para as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($para);
            }
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            
            // Verificar que el cuerpo HTML tenga las etiquetas meta correctas de UTF-8
            if (strpos($cuerpo, '<meta charset="UTF-8">') === false) {
                // Si no tiene la meta de charset, insertarla en el <head>
                $cuerpo = preg_replace('/<head>/i', '<head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', $cuerpo);
            }
            
            // Verificación de caracteres UTF-8 válidos
            if (!preg_match('//u', $cuerpo)) {
                error_log("⚠️ ADVERTENCIA: El cuerpo del correo contiene caracteres no UTF-8. Intentando corregir...");
                $cuerpo = mb_convert_encoding($cuerpo, 'UTF-8', mb_detect_encoding($cuerpo));
            }
            
            $mail->Body = $cuerpo;
            
            // Generar texto plano como alternativa con mejor manejo de etiquetas HTML
            $texto_plano = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>', '<div>', '</div>', '<table>', '</table>', '<tr>', '</tr>', '<td>', '</td>'], "\n", $cuerpo));
            $texto_plano = preg_replace('/\s+/', ' ', $texto_plano);
            $mail->AltBody = $texto_plano;
            
            // Adjuntos
            if (is_array($adjuntos) && !empty($adjuntos)) {
                foreach ($adjuntos as $adjunto) {
                    if (file_exists($adjunto)) {
                        $mail->addAttachment($adjunto);
                    }
                }
            }
            
            // Intentar enviar el correo
            error_log("Intentando enviar correo via SMTP a: " . (is_array($para) ? implode(', ', $para) : $para));
            $resultado = $mail->send();
            $salida_directa = ob_get_clean(); // Capturar cualquier salida directa
            
            if (!$resultado) {
                error_log("ERROR al enviar correo: " . $mail->ErrorInfo);
                error_log("Debug SMTP: " . $debug_output);
                if (!empty($salida_directa)) {
                    error_log("Salida directa: " . $salida_directa);
                }
                return false;
            }
            
            error_log("✅ Correo enviado exitosamente a: " . (is_array($para) ? implode(', ', $para) : $para));
            return true;
        } catch (Exception $e) {
            $salida_directa = ob_get_clean(); // Asegurar que se capture la salida en caso de excepción
            error_log("❌ EXCEPCIÓN al enviar correo con PHPMailer: " . $e->getMessage());
            error_log("Archivo: " . $e->getFile() . " | Línea: " . $e->getLine());
            error_log("Traza: " . $e->getTraceAsString());
            if (isset($debug_output) && !empty($debug_output)) {
                error_log("Debug SMTP de la excepción: " . $debug_output);
            }
            if (!empty($salida_directa)) {
                error_log("Salida directa: " . $salida_directa);
            }
            return false;
        }
    }
    
    /**
     * Envía un correo utilizando la función mail() nativa
     */
    private function enviarConMailNativo($para, $asunto, $cuerpo) {
        try {
            // Si tenemos configuración SMTP pero no PHPMailer, advertir
            if (!empty($this->host) && !empty($this->usuario) && !empty($this->contrasena)) {
                error_log("ADVERTENCIA: Configuración SMTP disponible pero PHPMailer no está instalado. El correo puede no enviarse correctamente.");
            }
            
            // Verificar que el cuerpo sea UTF-8 válido
            if (!preg_match('//u', $cuerpo)) {
                error_log("Convirtiendo cuerpo del correo a UTF-8 válido");
                $cuerpo = mb_convert_encoding($cuerpo, 'UTF-8', mb_detect_encoding($cuerpo));
            }
            
            // Asegurar que el cuerpo HTML tiene etiquetas meta charset
            if (strpos($cuerpo, '<meta charset="UTF-8">') === false) {
                $cuerpo = preg_replace('/<head>/i', '<head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', $cuerpo);
            }
            
            // Cabeceras del correo con codificación mejorada
            $cabeceras = "MIME-Version: 1.0\r\n";
            $cabeceras .= "Content-Type: text/html; charset=UTF-8\r\n";
            $cabeceras .= "Content-Transfer-Encoding: base64\r\n";
            
            // Codificar nombre remitente para caracteres especiales
            $nombreCodificado = "=?UTF-8?B?".base64_encode($this->nombre)."?=";
            $cabeceras .= "From: {$nombreCodificado} <{$this->de}>\r\n";
            $cabeceras .= "Reply-To: {$this->de}\r\n";
            $cabeceras .= "X-Mailer: " . SYSTEM_NAME . "/" . (defined('SISTEMA_VERSION') ? SISTEMA_VERSION : '1.0') . " PHP/" . phpversion();
            
            // Sanitizar destinatario
            if (is_array($para)) {
                $para = implode(', ', $para);
            }
            
            // Sanitizar asunto y codificarlo para soportar caracteres especiales
            $asunto = str_replace(["\r", "\n"], '', $asunto);
            // Verificar que el asunto sea UTF-8 válido
            if (!preg_match('//u', $asunto)) {
                error_log("Convirtiendo asunto del correo a UTF-8 válido");
                $asunto = mb_convert_encoding($asunto, 'UTF-8', mb_detect_encoding($asunto));
            }
            $asuntoCodificado = "=?UTF-8?B?".base64_encode($asunto)."?=";
            
            // Codificar el cuerpo en base64 para mejor compatibilidad
            $cuerpoCodificado = chunk_split(base64_encode($cuerpo));
            
            // Intentar enviar correo con asunto codificado
            $resultado = mail($para, $asuntoCodificado, $cuerpoCodificado, $cabeceras);
            
            if ($resultado) {
                error_log("Correo enviado correctamente con mail() nativo a: $para");
            } else {
                error_log("Error al enviar correo con mail() nativo a: $para");
                error_log("Posible error de codificación. Detalles: " . error_get_last()['message'] ?? 'No hay detalles adicionales');
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en enviarConMailNativo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Genera una plantilla para el correo de recuperación de contraseña
     * 
     * @param array $datos Datos para la plantilla
     * @return string HTML del correo
     */
    public function generarPlantillaRecuperacion($datos) {
        $nombre = $datos['nombre'] ?? 'Usuario';
        $url = $datos['url'] ?? BASE_URL;
        
        // Registrar la URL que se está utilizando para depuración
        error_log("Generando plantilla de recuperación con URL: " . $url);
        
        // Garantizar que la URL no tenga espacios ni caracteres extraños
        $url = trim($url);
        
        // No usar htmlspecialchars en URLs que se utilizarán en enlaces HTML
        // ya que convertiría caracteres como & en &amp; lo que puede causar problemas
        
        // Usar UTF-8 para todos los caracteres especiales explícitamente
        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Recuperación de Contraseña</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .container {
                    background-color: #f9f9f9;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 20px;
                    margin-top: 20px;
                }
                .header {
                    background-color: #3498db;
                    color: white;
                    padding: 10px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    text-align: center;
                    color: #666;
                }
                .button {
                    display: inline-block;
                    background-color: #3498db;
                    color: white !important;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .note {
                    background-color: #fffde7;
                    padding: 10px;
                    border-left: 4px solid #ffd600;
                    margin: 20px 0;
                }
                .url-display {
                    background-color: #f1f1f1;
                    padding: 10px;
                    border: 1px dashed #ccc;
                    word-break: break-all;
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Recuperación de Contraseña</h2>
                </div>
                <p>Hola <strong>' . $nombre . '</strong>,</p>
                <p>Hemos recibido una solicitud para restablecer la contraseña asociada a esta dirección de correo electrónico en ' . SYSTEM_NAME . '.</p>
                <p>Para continuar con el proceso, por favor haga clic en el siguiente enlace:</p>
                
                <div style="text-align: center;">
                    <a href="' . $url . '" class="button" style="display: inline-block; background-color: #3498db; color: white !important; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin: 20px 0;">Restablecer Contraseña</a>
                </div>
                
                <p>O copie y pegue la siguiente URL en su navegador:</p>
                <div style="background-color: #f1f1f1; padding: 10px; border: 1px dashed #ccc; word-break: break-all; margin: 15px 0;">
                    <a href="' . $url . '" style="color: #3498db; text-decoration: underline; word-break: break-all;">' . $url . '</a>
                </div>
                
                <div class="note">
                    <p><strong>Nota:</strong> Este enlace es válido por 24 horas. Después de ese tiempo, deberá solicitar un nuevo enlace de restablecimiento.</p>
                    <p>Si no ha solicitado restablecer su contraseña, puede ignorar este mensaje. Su cuenta sigue segura.</p>
                </div>
                
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . SYSTEM_NAME . ' - Todos los derechos reservados</p>
                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Envía un correo de recuperación de contraseña utilizando la configuración 
     * exacta que funciona en test_smtp_debug.php, con manejo especial para caracteres UTF-8
     * 
     * @param string $para Email del destinatario
     * @param string $asunto Asunto del correo
     * @param string $cuerpo Contenido HTML del correo
     * @return bool Éxito o fracaso del envío
     */
    public function enviarRecuperacionContrasena($para, $asunto, $cuerpo) {
        error_log("=== INICIO ENVÍO DE CORREO DE RECUPERACIÓN ===");
        error_log("Destinatario: $para");
        error_log("Asunto: $asunto");
        
        try {
            // Verificar si PHPMailer está disponible
            if (!file_exists(ROOT_PATH . '/vendor/autoload.php')) {
                error_log("ERROR: PHPMailer no está disponible. Compruebe la instalación.");
                return false;
            }
            
            // Cargar PHPMailer
            require_once ROOT_PATH . '/vendor/autoload.php';
            
            // Crear instancia exactamente como en test_smtp_debug.php
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Capturar salida de debug
            $debug_output = "";
            $mail->Debugoutput = function($str, $level) use (&$debug_output) {
                $debug_output .= htmlspecialchars($str) . "\n";
            };
            
            // Iniciar captura de salida para capturar cualquier salida directa
            ob_start();
            
            // Usar la misma configuración exacta que funciona en test_smtp_debug.php
            $mail->SMTPDebug = 4; // Debug completo como en test_smtp_debug.php
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // IMPORTANTE: Configuración reforzada para caracteres especiales
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64'; // base64 funciona mejor para contenido internacional
            
            // Opciones adicionales que han demostrado funcionar con test_smtp_debug.php
            $mail->Timeout = 60;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Usar nombre de remitente sin codificarlo directamente aquí, PHPMailer lo hará correctamente
            // gracias a la configuración de CharSet = 'UTF-8'
            $nombreRemitente = SYSTEM_EMAIL_PREFIX . ' Recuperación de Contraseña';
            $mail->setFrom(SMTP_FROM, $nombreRemitente);
            $mail->addReplyTo(SMTP_FROM, $nombreRemitente);
            
            // Asegurarse de que el header From esté correctamente codificado
            $mail->XMailer = SYSTEM_NAME . ' PHPMailer';
            
            // Destinatario
            $mail->addAddress($para);
            
            // Contenido - Sin codificación adicional, PHPMailer lo hará correctamente
            $mail->isHTML(true);
            $mail->Subject = $asunto; // PHPMailer codificará el asunto según sea necesario
            
            // Asegurarse de que el cuerpo HTML tenga las etiquetas meta correctas de UTF-8
            if (strpos($cuerpo, '<meta charset="UTF-8">') === false) {
                // Si no tiene la meta de charset, insertarla en el <head>
                $cuerpo = preg_replace('/<head>/i', '<head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', $cuerpo);
            }
            
            $mail->Body = $cuerpo;
            // Mejorar la versión de texto plano
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>', '<div>', '</div>', '<table>', '</table>', '<tr>', '</tr>', '<td>', '</td>'], "\n", $cuerpo));
            
            error_log("Intentando enviar correo de recuperación usando configuración UTF-8 mejorada...");
            
            // Verificación final antes de enviar
            if (!preg_match('//u', $mail->Subject)) {
                error_log("⚠️ ADVERTENCIA: El asunto contiene caracteres no UTF-8. Intentando corregir...");
                $mail->Subject = mb_convert_encoding($mail->Subject, 'UTF-8', mb_detect_encoding($mail->Subject));
            }
            
            if (!preg_match('//u', $mail->Body)) {
                error_log("⚠️ ADVERTENCIA: El cuerpo contiene caracteres no UTF-8. Intentando corregir...");
                $mail->Body = mb_convert_encoding($mail->Body, 'UTF-8', mb_detect_encoding($mail->Body));
            }
            
            // Force priority to normal
            $mail->Priority = 3;
            
            // Enviar correo
            $resultado = $mail->send();
            
            // Capturar la salida directa
            $direct_output = ob_get_clean();
            
            if ($resultado) {
                error_log("✅ ÉXITO: Correo de recuperación enviado correctamente a: $para");
                error_log("=== FIN ENVÍO DE CORREO DE RECUPERACIÓN (EXITOSO) ===");
                return true;
            } else {
                error_log("❌ ERROR: No se pudo enviar el correo de recuperación");
                error_log("Mensaje de error: " . $mail->ErrorInfo);
                
                if (!empty($debug_output)) {
                    error_log("Debug SMTP: " . $debug_output);
                }
                
                if (!empty($direct_output)) {
                    error_log("Salida directa: " . $direct_output);
                }
                
                error_log("=== FIN ENVÍO DE CORREO DE RECUPERACIÓN (FALLIDO) ===");
                return false;
            }
        } catch (Exception $e) {
            // Asegurarnos de limpiar el buffer de salida en caso de excepción
            $direct_output = ob_get_clean();
            
            error_log("❌ EXCEPCIÓN al enviar correo de recuperación: " . $e->getMessage());
            error_log("Archivo: " . $e->getFile() . " | Línea: " . $e->getLine());
            error_log("Traza: " . $e->getTraceAsString());
            
            if (!empty($direct_output)) {
                error_log("Salida directa: " . $direct_output);
            }
            
            error_log("=== FIN ENVÍO DE CORREO DE RECUPERACIÓN (EXCEPCIÓN) ===");
            return false;
        }
    }
}
?>
