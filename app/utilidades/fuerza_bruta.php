<?php
// filepath: /Users/cferrerobonet/Documents/04 DESARROLLADOR/Web/EPLA/AUTOEXAM2/app/utilidades/fuerza_bruta.php

/**
 * Clase FuerzaBruta - Protección contra ataques de fuerza bruta en AUTOEXAM2
 * 
 * Esta clase se encarga de monitorizar y bloquear intentos repetidos de login fallidos
 * para proteger el sistema contra ataques de fuerza bruta.
 * 
 * @author Carlos Ferrero Bonet
 * @version 1.1
 * @date 13/06/2025
 */
class FuerzaBruta {
    /**
     * @var PDO $db Conexión a la base de datos
     */
    private $db;
    
    /**
     * @var int $maxIntentos Número máximo de intentos permitidos antes del bloqueo
     */
    private $maxIntentos;
    
    /**
     * @var int $tiempoBloqueo Tiempo de bloqueo en minutos
     */
    private $tiempoBloqueo;
    
    /**
     * @var string $tabla Nombre de la tabla para almacenar los intentos
     */
    private $tabla = 'intentos_login';
    
    /**
     * Constructor
     * 
     * @param PDO $conexionDB Conexión de base de datos
     * @param int $maxIntentos Número máximo de intentos permitidos antes del bloqueo (por defecto 5)
     * @param int $tiempoBloqueo Tiempo de bloqueo en minutos (por defecto 30)
     */
    public function __construct($conexionDB, $maxIntentos = 5, $tiempoBloqueo = 30) {
        $this->db = $conexionDB;
        $this->maxIntentos = $maxIntentos;
        $this->tiempoBloqueo = $tiempoBloqueo;
        
        // Limpiar registros antiguos al inicializar
        $this->limpiarRegistrosAntiguos();
    }
    
    /**
     * Verifica si una IP y correo están bloqueados
     * 
     * @param string $ip Dirección IP del cliente
     * @param string $correo Correo electrónico utilizado en el intento
     * @return bool|array false si no está bloqueado, array con datos de bloqueo si lo está
     */
    public function estaBloqueado($ip, $correo) {
        try {
            // Sanitizar entradas
            $ip = $this->sanitizarIP($ip);
            $correo = $this->sanitizarCorreo($correo);
            
            // Si no son válidos, no procesar
            if (!$ip || !$correo) {
                return false;
            }
            
            // Consultar bloques activos
            $stmt = $this->db->prepare("
                SELECT intentos, bloqueado_hasta, TIMESTAMPDIFF(SECOND, NOW(), bloqueado_hasta) as tiempo_restante 
                FROM {$this->tabla} 
                WHERE ip = :ip AND correo = :correo AND bloqueado_hasta > NOW()
            ");
            
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $bloqueo = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'bloqueado' => true,
                    'intentos' => (int)$bloqueo['intentos'],
                    'bloqueado_hasta' => $bloqueo['bloqueado_hasta'],
                    'tiempo_restante' => (int)$bloqueo['tiempo_restante']
                ];
            }
            
            return false;
        } catch (PDOException $e) {
            $this->registrarError('Error al verificar bloqueo: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra un intento fallido de login
     * 
     * @param string $ip Dirección IP del cliente
     * @param string $correo Correo electrónico utilizado en el intento
     * @return bool|array false en caso de error, array con información de intentos/bloqueo en caso exitoso
     */
    public function registrarIntentoFallido($ip, $correo) {
        try {
            // Sanitizar entradas
            $ip = $this->sanitizarIP($ip);
            $correo = $this->sanitizarCorreo($correo);
            
            // Si no son válidos, no procesar
            if (!$ip || !$correo) {
                $this->registrarError('Intento de registro con IP o correo inválidos');
                return false;
            }
            
            // Transacción para garantizar la integridad
            $this->db->beginTransaction();
            
            try {
                // Verificar si ya existe un registro para esta IP y correo
                $stmt = $this->db->prepare("
                    SELECT id_intento, intentos, bloqueado_hasta 
                    FROM {$this->tabla} 
                    WHERE ip = :ip AND correo = :correo 
                    FOR UPDATE
                ");
                $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
                $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                $stmt->execute();
                $registro = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($registro) {
                    // Actualizar contador de intentos
                    $intentos = (int)$registro['intentos'] + 1;
                    
                    // Determinar si se debe bloquear
                    $bloquear = $intentos >= $this->maxIntentos;
                    $bloqueadoHasta = $bloquear ? date('Y-m-d H:i:s', strtotime("+{$this->tiempoBloqueo} minutes")) : null;
                    
                    $stmt = $this->db->prepare("
                        UPDATE {$this->tabla} 
                        SET intentos = :intentos, 
                            bloqueado_hasta = :bloqueado, 
                            ultimo_intento = NOW() 
                        WHERE id_intento = :id
                    ");
                    $stmt->bindParam(':intentos', $intentos, PDO::PARAM_INT);
                    $stmt->bindParam(':bloqueado', $bloqueadoHasta, $bloqueadoHasta === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmt->bindParam(':id', $registro['id_intento'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Registrar en log de actividad si se bloquea
                    if ($bloquear) {
                        $this->registrarActividadBloqueo($ip, $correo, $intentos);
                    }
                    
                    // Commit de la transacción
                    $this->db->commit();
                    
                    return [
                        'intentos' => $intentos,
                        'bloqueado' => $bloquear,
                        'bloqueado_hasta' => $bloqueadoHasta,
                        'maxIntentos' => $this->maxIntentos
                    ];
                    
                } else {
                    // Crear nuevo registro
                    $stmt = $this->db->prepare("
                        INSERT INTO {$this->tabla} (ip, correo, intentos, ultimo_intento) 
                        VALUES (:ip, :correo, 1, NOW())
                    ");
                    $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
                    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
                    $stmt->execute();
                    
                    // Commit de la transacción
                    $this->db->commit();
                    
                    return [
                        'intentos' => 1,
                        'bloqueado' => false,
                        'bloqueado_hasta' => null,
                        'maxIntentos' => $this->maxIntentos
                    ];
                }
            } catch (PDOException $innerEx) {
                // Rollback en caso de error
                $this->db->rollBack();
                throw $innerEx;
            }
            
        } catch (PDOException $e) {
            $this->registrarError('Error al registrar intento fallido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reinicia el contador de intentos fallidos cuando se produce un login exitoso
     * 
     * @param string $ip Dirección IP del cliente
     * @param string $correo Correo electrónico utilizado en el intento
     * @return bool true si se reinició correctamente, false en caso de error
     */
    public function reiniciarIntentos($ip, $correo) {
        try {
            // Sanitizar entradas
            $ip = $this->sanitizarIP($ip);
            $correo = $this->sanitizarCorreo($correo);
            
            // Si no son válidos, no procesar
            if (!$ip || !$correo) {
                return false;
            }
            
            $stmt = $this->db->prepare("
                DELETE FROM {$this->tabla} 
                WHERE ip = :ip AND correo = :correo
            ");
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->registrarError('Error al reiniciar intentos: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpia registros antiguos de intentos fallidos (más de 24 horas)
     * 
     * @param int $horasLimpieza Horas de antigüedad para considerar un registro como antiguo
     * @return bool true si se limpiaron correctamente, false en caso de error
     */
    public function limpiarRegistrosAntiguos($horasLimpieza = 24) {
        try {
            // Validar que las horas sean un número positivo
            $horasLimpieza = max(1, (int)$horasLimpieza);
            
            // Eliminar registros no bloqueados con más de N horas
            $stmt = $this->db->prepare("
                DELETE FROM {$this->tabla} 
                WHERE (bloqueado_hasta IS NULL OR bloqueado_hasta < NOW()) 
                AND ultimo_intento < DATE_SUB(NOW(), INTERVAL :horas HOUR)
            ");
            $stmt->bindParam(':horas', $horasLimpieza, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->registrarError('Error al limpiar registros antiguos: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra actividad de bloqueo en la tabla de registro_actividad
     * 
     * @param string $ip IP del cliente
     * @param string $correo Correo electrónico bloqueado
     * @param int $intentos Número de intentos realizados
     */
    private function registrarActividadBloqueo($ip, $correo, $intentos) {
        try {
            // Sanitizar datos antes de insertar
            $ip = $this->sanitizarIP($ip);
            $correo = $this->sanitizarCorreo($correo);
            $intentos = (int)$intentos;
            
            // Preparar user agent de manera segura
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? 
                         substr(filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING), 0, 255) : 
                         'Desconocido';
            
            // Descripción con detalles parciales del correo para privacidad
            $correoOculto = $this->ofuscarCorreo($correo);
            $descripcion = "IP bloqueada tras {$intentos} intentos fallidos para {$correoOculto}";
            
            $stmt = $this->db->prepare("
                INSERT INTO registro_actividad (
                    id_usuario, accion, descripcion, fecha, ip, user_agent, modulo
                ) VALUES (
                    NULL, 'login_bloqueado_ip', :descripcion, NOW(), :ip, :user_agent, 'autenticacion'
                )
            ");
            
            $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            $this->registrarError('Error al registrar actividad de bloqueo: ' . $e->getMessage());
        }
    }
    
    /**
     * Ofusca un correo electrónico para proteger la privacidad en los logs
     * 
     * @param string $correo El correo electrónico a ofuscar
     * @return string El correo ofuscado (ej: j***@e***.com)
     */
    private function ofuscarCorreo($correo) {
        if (!$correo || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return 'correo-inválido';
        }
        
        $partes = explode('@', $correo);
        if (count($partes) !== 2) {
            return 'correo-inválido';
        }
        
        $usuario = $partes[0];
        $dominio = $partes[1];
        
        // Preservar el primer caracter del usuario y dominio
        $usuarioOfuscado = substr($usuario, 0, 1) . str_repeat('*', strlen($usuario) - 1);
        
        $parteDominio = explode('.', $dominio);
        $dominioBase = $parteDominio[0];
        $extension = end($parteDominio);
        
        $dominioOfuscado = substr($dominioBase, 0, 1) . str_repeat('*', strlen($dominioBase) - 1);
        
        return $usuarioOfuscado . '@' . $dominioOfuscado . '.' . $extension;
    }
    
    /**
     * Registra errores en el archivo de log
     * 
     * @param string $mensaje Mensaje de error para registrar
     */
    private function registrarError($mensaje) {
        try {
            $logDir = STORAGE_PATH . '/logs/errores';
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $logFile = $logDir . '/fuerza_bruta.log';
            $fecha = date('Y-m-d H:i:s');
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
            $mensaje = "[{$fecha}] [{$ip}] {$mensaje}" . PHP_EOL;
            
            file_put_contents($logFile, $mensaje, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Si falla el logging, al menos intentamos registrar en error_log
            error_log('Error en FuerzaBruta: ' . $mensaje . ' - ' . $e->getMessage());
        }
    }
    
    /**
     * Sanitiza y valida una dirección IP
     * 
     * @param string $ip La dirección IP a sanitizar
     * @return string|false La IP sanitizada o false si es inválida
     */
    private function sanitizarIP($ip) {
        // Eliminar espacios y filtrar la IP
        $ip = trim($ip);
        
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
        
        $this->registrarError("IP inválida detectada: " . substr($ip, 0, 30));
        return false;
    }
    
    /**
     * Sanitiza y valida un correo electrónico
     * 
     * @param string $correo El correo electrónico a sanitizar
     * @return string|false El correo sanitizado o false si es inválido
     */
    private function sanitizarCorreo($correo) {
        // Eliminar espacios y convertir a minúsculas
        $correo = trim(strtolower($correo));
        
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return $correo;
        }
        
        $this->registrarError("Correo inválido detectado: " . substr($correo, 0, 30));
        return false;
    }
}
