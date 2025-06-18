# 61 – Control de sesión única por usuario

---

## 🎯 Objetivos clave del sistema

- Evitar que un mismo usuario tenga sesiones activas simultáneas en diferentes dispositivos o ubicaciones  
- Forzar cierre de sesión anterior si se detecta login múltiple  
- Registrar todos los eventos de expulsión o reemplazo de sesión  
- Aumentar la seguridad en contextos académicos y evitar suplantaciones  
- Detectar usos anómalos (IPs distintas, navegadores simultáneos, etc.)

---

## 🧭 Objetivo

Implementar un sistema que controle que cada usuario tenga como máximo una sesión activa en AUTOEXAM2. Si intenta acceder desde otro dispositivo, se le notificará y se invalidará la sesión anterior.

---

## 🔗 Dependencias

- `05_autenticacion.md`  
- `23_sesiones_activas.md`  
- `41_registro_actividad.md`

---

## 📊 Tabla `sesiones_activas` (ya existente)

| Campo        | Tipo       | Descripción                         |
|--------------|------------|-------------------------------------|
| id_sesion    | INT PK AI  | Identificador                       |
| id_usuario   | INT        | Usuario activo                      |
| fecha_inicio | DATETIME   | Inicio sesión                       |
| ip           | VARCHAR(45)| IP del dispositivo                  |
| user_agent   | TEXT       | Navegador y sistema operativo       |
| activa       | TINYINT(1) | 1 = activa / 0 = cerrada            |

---

## 🧪 Comportamiento esperado

- Si un usuario se loguea y ya tiene otra sesión activa:
  - Opción 1: se cierra la sesión anterior y se mantiene la nueva  
  - Opción 2: se rechaza el nuevo login y se muestra aviso  
- Todo intento duplicado se registra en log y `registro_actividad`  
- Opcional: mostrar advertencia visual en panel del usuario

---

## 🧪 UI/UX

- Modal: “Ya estás conectado desde otro dispositivo”  
- Botón: “Forzar conexión aquí”  
- Feedback visual si se cierra sesión remota  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Verificación previa de sesión activa por `id_usuario` antes del login  
- Cierre automático de la sesión anterior si se fuerza  
- Registro de eventos con IP y navegador  
- Redirección clara y segura tras expulsión  
- Logs en `/almacenamiento/logs/sesion_duplicada.log`  
- Función de mantenimiento para limpiar sesiones viejas

---

## ✅ Checklist Copilot

- [ ] Añadir validación en login (`login.php`)  
- [ ] Consultar `sesiones_activas` por usuario  
- [ ] Invalidar sesión anterior si ya existe  
- [ ] Registrar evento en `registro_actividad`  
- [ ] Mostrar feedback visual en frontend  
- [ ] Mantener integridad de `$_SESSION` actual

---

📌 A continuación, Copilot debe leer e implementar: 23_sesiones_activas.md
