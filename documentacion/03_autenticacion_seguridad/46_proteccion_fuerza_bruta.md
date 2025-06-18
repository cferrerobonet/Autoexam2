# 46 – Protección contra ataques de fuerza bruta

Módulo encargado de evitar accesos indebidos mediante repetición masiva de intentos fallidos.

---

## 🎯 Objetivos clave del sistema

- Prevenir ataques de fuerza bruta contra el sistema de login  
- Registrar todos los intentos fallidos de acceso por IP y usuario  
- Bloquear temporalmente accesos tras exceder el número permitido de intentos fallidos  
- Evitar divulgación de información sensible sobre credenciales  

---

## 🔗 Dependencias funcionales

- `05_autenticacion.md`  
- `11_modulo_autenticacion.md`  
- `41_registro_actividad.md`  
- `06_configuracion.md` (configurar número de intentos y tiempo de bloqueo)

---

## 🗃️ Tablas utilizadas

### Tabla `registro_actividad` (ya existente)

Se registran entradas de tipo:

- `login_fallido`
- `login_bloqueado_ip`
- `login_exitoso` (reinicia contador si existía bloqueo previo)

---

## 📊 Funcionalidades incluidas

| Funcionalidad               | Descripción                                                                 |
|----------------------------|------------------------------------------------------------------------------|
| Limitación de intentos     | Bloqueo temporal tras número configurable de fallos                          |
| Control de IP              | Seguimiento de intentos por dirección IP + usuario                          |
| Feedback al usuario        | Mensaje genérico para evitar filtrado de información                        |
| Integración con recuperación | Permitir recuperación de acceso sin esperar a que finalice el bloqueo       |

---

## 🔐 Seguridad y control

- Se reinicia el contador de intentos al hacer login correcto  
- Se bloquea temporalmente el acceso desde esa IP y usuario  
- Tiempo de bloqueo configurable desde `configuracion` o `.env`  
- Proteger contra manipulación del contador vía backend seguro  

---

## 🧑‍💻 UI/UX

- Mensaje como: “Demasiados intentos fallidos. Intenta de nuevo en X minutos”  
- Opción visible para recuperar contraseña durante el bloqueo  
- Ocultar si el usuario o la contraseña eran correctos o incorrectos  

---

## ✅ Validación y datos

- Validar que IP y usuario sean válidos y existentes  
- Mantener consistencia en conteo y fecha del último intento  
- Reset automático del contador tras `login_exitoso`  

---

## 🪵 Manejo de errores y logs

- Si se excede el límite → bloqueo y registro en `registro_actividad`  
- Intento de login durante el bloqueo → no procesa credenciales, solo mensaje  
- Logs técnicos → `/almacenamiento/logs/fuerza_bruta.log`  

---

## 🧪 Casos límite esperados

- 5 intentos fallidos → usuario bloqueado por 30 minutos  
- Usuario cambia contraseña → puede volver a iniciar sesión antes del tiempo  
- IP compartida (como aula) → bloqueo solo por combinación IP + usuario  
- El mismo usuario desde dos IPs → cada una tiene su propio control  

---

## 📂 MVC y archivos implicados

| Componente              | Ruta                                |
|-------------------------|-------------------------------------|
| Controlador login       | `controladores/login.php`           |
| Utilidad de seguridad   | `utilidades/fuerza_bruta.php`       |
| Vista login             | `vistas/publico/login.php`          |
| Log de sistema          | `/almacenamiento/logs/fuerza_bruta.log`        |

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación exhaustiva de entradas, permisos y sesiones
- Uso de token CSRF en formularios críticos
- Registro detallado de acciones en `registro_actividad`
- Logs técnicos separados por módulo en `/almacenamiento/logs/`
- Acceso restringido por rol y curso donde aplique
- Control de errores con feedback claro para el usuario
- Sanitización de entradas y protección contra manipulación
- Integración segura con otros módulos relacionados


## ✅ Checklist para Copilot

- [ ] Implementar conteo de intentos fallidos por IP y usuario  
- [ ] Registrar intentos en `registro_actividad`  
- [ ] Aplicar bloqueo temporal tras N fallos consecutivos  
- [ ] Reiniciar contador si el login es exitoso  
- [ ] Mostrar mensajes genéricos sin revelar causa  
- [ ] Integrar con el sistema de recuperación de contraseña  
- [ ] Respetar tiempo de bloqueo configurado desde `.env`  

---

📌 A continuación, Copilot debe leer e implementar: `41_registro_actividad.md`
