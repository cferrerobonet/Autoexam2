# 62 – Historial de accesos por IP y control administrativo

---

## 🎯 Objetivos clave del sistema

- Permitir al administrador consultar desde qué IPs y dispositivos acceden los usuarios  
- Facilitar la detección de comportamientos anómalos o accesos sospechosos  
- Reforzar la trazabilidad y la seguridad sin invadir la privacidad  
- Posibilitar el bloqueo de IPs específicas si fuera necesario  
- Generar un informe de accesos completo por usuario

---

## 🧭 Objetivo

Incorporar una sección en el panel de administrador donde se pueda consultar el historial de accesos de cualquier usuario, con su IP, dispositivo y fecha/hora de conexión.

---

## 🔗 Dependencias

- `23_sesiones_activas.md`  
- `05_autenticacion.md`  
- `41_registro_actividad.md`  

---

## 📊 Tabla `sesiones_activas`

Se utiliza la tabla ya existente, donde se almacena IP y user_agent.

---

## 📑 Información visualizada

| Campo         | Ejemplo                                             |
|---------------|------------------------------------------------------|
| Usuario       | Juan Pérez                                           |
| Fecha/Hora    | 2025-05-26 09:34:12                                  |
| IP            | 83.44.228.71                                         |
| Navegador     | Chrome 120 / macOS                                   |
| Ubicación (*) | Geolocalización aproximada (opcional)               |

---

## 🧪 UI/UX

- Sección adicional en `admin/detalle_usuario.php`  
- Tabla con paginación y filtros por fecha, IP o navegador  
- Iconos por navegador y sistema (FontAwesome, flag-icon, etc.)  
- Posibilidad de exportar CSV con accesos de un usuario  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación de acceso solo desde cuenta administrador  
- Filtro y visualización eficiente para sesiones activas  
- Enlace directo desde logins recientes al historial completo  
- Protección contra edición de registros  
- Logs en `/almacenamiento/logs/consulta_ips.log`  

---

## ✅ Checklist Copilot

- [ ] Reutilizar `sesiones_activas` para generar historial  
- [ ] Añadir sección en `detalle_usuario.php`  
- [ ] Mostrar tabla con IP, user agent y timestamp  
- [ ] Permitir filtro por fecha/IP/navegador  
- [ ] Exportación CSV del historial  
- [ ] Log de consultas en archivo técnico

---

📌 A continuación, Copilot debe leer e implementar: 23_sesiones_activas.md
