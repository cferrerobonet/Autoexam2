<?php
/**
 * Servicio de Recuperación de Contraseña - AUTOEXAM2
 * 
 * Gestiona las operaciones relacionadas con la recuperación de contraseñas
 * 
 * @author Github Copilot
 * @version 1.0
 * @date 13/06/2025
 */

class RecuperacionServicio {
    private $tokenModelo;
    private $usuarioModelo;
    private $correoUtil;
    
    /**
     * Constructor
     * 
     * @param TokenRecuperacion $tokenModelo Modelo de tokens de recuperación
     * @param Usuario $usuarioModelo Modelo de usuarios
     * @param Correo $correoUtil Utilidad de correo
     */
    public function __construct($tokenModelo, $usuarioModelo, $correoUtil) {
        $this->tokenModelo = $tokenModelo;
        $this->usuarioModelo = $usuarioModelo;
        $this->correoUtil = $correoUtil;
    }
    
    /**
     * Procesa una solicitud de recuperación de contraseña
     * 
     * @param string $correo Correo electrónico del usuario
     * @return array Resultado de la operación con mensaje y error
     */
    public function procesarSolicitudRecuperacion($correo) {
        $resultado = [
            'mensaje' => null,
            'error' => null,
            'exito' => false
        ];
        
        try {
            if (!$this->usuarioModelo->verificarConexion()) {
                $resultado['error'] = 'El sistema de recuperación de contraseña no está disponible en este momento. Por favor, contacte al administrador.';
                error_log("Intento de recuperación de contraseña con base de datos no disponible");
                return $resultado;
            }
            
            $usuario = $this->usuarioModelo->buscarPorCorreo($correo);
            
            // Siempre dar la misma respuesta por seguridad, independientemente de si existe o no
            $mensajeGenerico = 'Si el correo existe en nuestro sistema, recibirá instrucciones para restablecer su contraseña.';
            
            if (!$usuario || $usuario['activo'] != 1) {
                if (!$usuario) {
                    error_log("Intento de recuperación para correo inexistente: $correo");
                } else {
                    error_log("Intento de recuperación para usuario inactivo: $correo");
                }
                
                $resultado['mensaje'] = $mensajeGenerico;
                return $resultado;
            }
            
            // Generar y enviar token
            $resultado = $this->generarYEnviarToken($usuario);
            
            // Si no hay mensaje específico, usar el genérico
            if (!$resultado['mensaje']) {
                $resultado['mensaje'] = $mensajeGenerico;
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en recuperación de contraseña: " . $e->getMessage());
            $resultado['error'] = 'Ha ocurrido un error al procesar su solicitud. Por favor, inténtelo más tarde.';
            return $resultado;
        }
    }
    
    /**
     * Genera un token de recuperación y envía el correo al usuario
     * 
     * @param array $usuario Datos del usuario
     * @return array Resultado de la operación
     */
    private function generarYEnviarToken($usuario) {
        $resultado = [
            'mensaje' => null,
            'error' => null,
            'exito' => false
        ];
        
        $token = $this->tokenModelo->crearToken($usuario['id_usuario']);
        
        if (!$token) {
            error_log("Error al generar token para el usuario ID: {$usuario['id_usuario']}");
            return $resultado;
        }
        
        $urlRecuperacion = BASE_URL . '/autenticacion/restablecer/' . $token;
        
        // Datos para la plantilla de correo
        $datosCorreo = [
            'nombre' => $usuario['nombre'],
            'url' => $urlRecuperacion
        ];
        
        // Generar plantilla y enviar correo
        $cuerpoCorreo = $this->correoUtil->generarPlantillaRecuperacion($datosCorreo);
        
        // Añadir timestamp para evitar caché de correos
        $asuntoCorreo = 'Recuperación de contraseña en AUTOEXAM2 - ' . date('H:i:s');
        
        error_log("Iniciando proceso de envío de recuperación de contraseña para: {$usuario['correo']}");
        
        // Primer intento con método especializado
        $enviado = $this->correoUtil->enviarRecuperacionContrasena(
            $usuario['correo'],
            $asuntoCorreo,
            $cuerpoCorreo
        );
        
        if ($enviado) {
            $resultado['mensaje'] = 'Se ha enviado un correo con instrucciones para restablecer su contraseña.';
            $resultado['exito'] = true;
            error_log("✅ Correo de recuperación enviado exitosamente a: {$usuario['correo']}");
        } else {
            // Segundo intento con método estándar
            error_log("⚠️ Primer intento fallido. Intentando con método estándar...");
            
            // Forzar modo debug para la recuperación de contraseña en el método estándar
            $this->correoUtil->debug = true;
            
            $enviado = $this->correoUtil->enviar(
                $usuario['correo'],
                $asuntoCorreo . ' (2do intento)',
                $cuerpoCorreo
            );
            
            if ($enviado) {
                $resultado['mensaje'] = 'Se ha enviado un correo con instrucciones para restablecer su contraseña.';
                $resultado['exito'] = true;
                error_log("✅ Correo de recuperación enviado en segundo intento a: {$usuario['correo']}");
            } else {
                // Ambos métodos fallaron
                $resultado['mensaje'] = 'Se produjo un error al enviar el correo de recuperación.';
                $resultado['error'] = 'Error técnico: No se pudo enviar el correo de recuperación. Por favor, contacte con el administrador del sistema o intente más tarde.';
                error_log("❌ ERROR: Ambos métodos de envío fallaron para: {$usuario['correo']}");
                error_log("Detalles de envío - Host: {$this->correoUtil->host}, Puerto: {$this->correoUtil->puerto}, Usuario: {$this->correoUtil->usuario}");
            }
        }
        
        return $resultado;
    }
    
    /**
     * Valida un token de recuperación
     * 
     * @param string $token Token de recuperación
     * @return array|false Datos del token si es válido, false en caso contrario
     */
    public function validarToken($token) {
        if (!$token) {
            error_log("Intento de validación con token vacío");
            return false;
        }
        
        error_log("Verificando validez del token: " . substr($token, 0, 10) . '...');
        $verificacion = $this->tokenModelo->validarToken($token);
        
        if (!$verificacion) {
            error_log("Token inválido o expirado: " . substr($token, 0, 10) . '...');
            return false;
        }
        
        error_log("Token válido para el usuario ID: " . $verificacion['id_usuario']);
        return $verificacion;
    }
    
    /**
     * Procesa el cambio de contraseña
     * 
     * @param int $idUsuario ID del usuario
     * @param int $idToken ID del token
     * @param string $nuevaContrasena Nueva contraseña
     * @return bool True si se actualizó correctamente
     */
    public function actualizarContrasena($idUsuario, $idToken, $nuevaContrasena) {
        try {
            $actualizado = $this->usuarioModelo->actualizar($idUsuario, [
                'contrasena' => $nuevaContrasena
            ]);
            
            if ($actualizado) {
                // Marcar el token como usado
                $this->tokenModelo->marcarComoUsado($idToken);
                error_log("Contraseña actualizada con éxito para el usuario ID: $idUsuario");
                return true;
            }
            
            error_log("No se pudo actualizar la contraseña para el usuario ID: $idUsuario");
            return false;
        } catch (Exception $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Valida la complejidad de una contraseña
     * 
     * @param string $contrasena Contraseña a validar
     * @return array Resultado de la validación con éxito y mensaje de error
     */
    public function validarComplejidadContrasena($contrasena) {
        $resultado = [
            'valida' => true,
            'error' => null
        ];
        
        if (strlen($contrasena) < 8) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe tener al menos 8 caracteres.';
            return $resultado;
        }
        
        if (!preg_match('/[A-Z]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos una letra mayúscula.';
            return $resultado;
        }
        
        if (!preg_match('/[a-z]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos una letra minúscula.';
            return $resultado;
        }
        
        if (!preg_match('/[0-9]/', $contrasena)) {
            $resultado['valida'] = false;
            $resultado['error'] = 'La contraseña debe contener al menos un número.';
            return $resultado;
        }
        
        return $resultado;
    }
    
    /**
     * Limpia los tokens expirados
     * 
     * @return int Número de tokens limpiados
     */
    public function limpiarTokensExpirados() {
        return $this->tokenModelo->limpiarTokensExpirados();
    }
}
?>
