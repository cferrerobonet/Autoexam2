# 47 - Sistema de Protección Contra Fuerza Bruta

**Implementado y funcional** ✅  
**Ubicación:** `app/utilidades/fuerza_bruta.php`  
**Base de datos:** Tabla `intentos_login`

---

## 🎯 Objetivos del sistema

- Proteger la aplicación contra ataques de fuerza bruta en el inicio de sesión
- Limitar el número de intentos fallidos por usuario y dirección IP
- Implementar un sistema de bloqueo temporal progresivo
- Registrar y notificar intentos sospechosos de acceso
- Proporcionar una capa adicional de seguridad sin afectar la experiencia de usuario legítimo

---

## 🧱 Arquitectura del Sistema

### Componentes principales

```
app/utilidades/fuerza_bruta.php      # Clase principal con la lógica de protección
app/controladores/autenticacion_controlador.php  # Implementación en el login
```

### Estructura de base de datos

```sql
CREATE TABLE IF NOT EXISTS `intentos_login` (
  `id_intento` INT AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(45) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `intentos` INT NOT NULL DEFAULT 1,
  `bloqueado_hasta` DATETIME,
  `ultimo_intento` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ip_correo` (`ip`, `correo`)
) ENGINE=InnoDB;
```

---

## 🔄 Funcionamiento del Sistema

### 1. Registro de intentos

Cada vez que se produce un intento fallido de inicio de sesión:
- Se registra la combinación IP + correo en la tabla `intentos_login`
- Se incrementa el contador de intentos o se crea un nuevo registro

### 2. Verificación de bloqueo

Antes de procesar el intento de inicio de sesión:
- Se verifica si la combinación IP + correo está bloqueada
- Se comprueba si ha expirado el tiempo de bloqueo

### 3. Sistema de bloqueo progresivo

El tiempo de bloqueo aumenta con cada intento fallido sucesivo:
- **3-5 intentos**: Bloqueo de 15 minutos
- **6-10 intentos**: Bloqueo de 30 minutos
- **11-15 intentos**: Bloqueo de 1 hora
- **16-20 intentos**: Bloqueo de 2 horas
- **>20 intentos**: Bloqueo de 24 horas

### 4. Reset de intentos

El contador de intentos fallidos se reinicia:
- Cuando el usuario inicia sesión correctamente
- Después de un período largo sin intentos fallidos (24 horas)

---

## 📊 Implementación en el Controlador de Autenticación

```php
// En el método de inicio de sesión
public function iniciarSesion() {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    // Verificar si está bloqueado
    $fuerza_bruta = new FuerzaBruta();
    $ip = obtenerIP();
    
    if ($fuerza_bruta->estaBloqueado($ip, $correo)) {
        $tiempo_restante = $fuerza_bruta->tiempoRestanteBloqueo($ip, $correo);
        $this->mostrarError("Demasiados intentos fallidos. Inténtelo de nuevo en $tiempo_restante minutos.");
        return;
    }
    
    // Continuar con la autenticación...
    if ($autenticado) {
        // Éxito: reiniciar intentos
        $fuerza_bruta->reiniciarIntentos($ip, $correo);
    } else {
        // Fallo: registrar intento
        $fuerza_bruta->registrarIntentoFallido($ip, $correo);
    }
}
```

---

## ⚙️ Configuración del Sistema

El sistema de protección contra fuerza bruta puede configurarse con estos parámetros:

```php
// Umbrales de intentos y tiempos de bloqueo (en minutos)
private $umbrales = [
    3 => 15,    // 3-5 intentos: 15 minutos
    6 => 30,    // 6-10 intentos: 30 minutos
    11 => 60,   // 11-15 intentos: 1 hora
    16 => 120,  // 16-20 intentos: 2 horas
    21 => 1440  // >20 intentos: 24 horas
];

// Tiempo para eliminar registros antiguos (en horas)
private $tiempoLimpiezaRegistros = 72;
```

---

## 🔄 Mantenimiento del Sistema

El sistema incluye funciones de mantenimiento:

1. **Limpieza automática**: Elimina registros antiguos (por defecto, más de 72 horas)
2. **Reinicio de intentos**: Después de un inicio de sesión exitoso
3. **Informes de seguridad**: Para administradores, mostrando patrones de intentos

---

## 💻 Uso para Desarrolladores

### Verificar si una IP o usuario está bloqueado

```php
$fuerza_bruta = new FuerzaBruta();
$bloqueado = $fuerza_bruta->estaBloqueado($ip, $correo);

if ($bloqueado) {
    $tiempo_restante = $fuerza_bruta->tiempoRestanteBloqueo($ip, $correo);
    // Mostrar mensaje al usuario
}
```

### Registrar un intento fallido

```php
$fuerza_bruta = new FuerzaBruta();
$fuerza_bruta->registrarIntentoFallido($ip, $correo);
```

---

## 🔒 Mejoras de Seguridad

El sistema implementa varias características avanzadas:

1. **Análisis de patrones**: Detección de intentos sistemáticos
2. **Notificaciones**: Alertas al administrador sobre intentos múltiples
3. **Captcha adaptativo**: Integración opcional con captcha después de 2-3 intentos
4. **Registro detallado**: Los intentos fallidos se registran con todos los detalles para análisis
5. **Protección distribuida**: Considera tanto la IP como el correo para prevenir ataques distribuidos

---

## ⚠️ Consideraciones Importantes

1. **Equilibrio**: El sistema está diseñado para no penalizar en exceso a usuarios legítimos que olvidan su contraseña
2. **Proxies y NAT**: Las direcciones IP compartidas pueden afectar a múltiples usuarios
3. **Notificación al usuario**: Se informa claramente al usuario sobre el bloqueo temporal
4. **Alternativas**: Se ofrece una ruta de recuperación de contraseña cuando se detectan múltiples intentos
