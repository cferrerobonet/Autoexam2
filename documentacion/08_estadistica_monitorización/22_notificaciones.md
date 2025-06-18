# 22 – Sistema de notificaciones internas

---

## 🎯 Objetivos clave del sistema

- Informar a profesores y alumnos en tiempo real sobre eventos clave del sistema  
- Mejorar la interacción sin requerir sistema de mensajería completo  
- Permitir visualización rápida de novedades desde cualquier vista  
- Integrarse con entregas, calificaciones y publicación de exámenes  
- Marcar notificaciones como leídas y archivar si es necesario  

---

## 🧭 Objetivo

Sistema de alertas visuales no intrusivas que informa a los usuarios de eventos importantes (entrega, calificación, examen publicado, etc.) sin necesidad de abrir módulos específicos.

---

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `14_modulo_examenes.md`
- `16_modulo_calificaciones.md`
- `33_exportacion_datos.md`

---

## 🗃️ Tabla `notificaciones`

| Campo            | Tipo         | Descripción                         |
|------------------|--------------|-------------------------------------|
| id_notificacion  | INT PK AI    | ID                                  |
| id_usuario       | INT (FK)     | Destinatario                        |
| mensaje          | TEXT         | Contenido de la notificación        |
| fecha_envio      | DATETIME     | Fecha y hora del evento             |
| leido            | TINYINT(1)   | 1 si ha sido leída, 0 si no         |

---

## 💡 Ejemplos de notificaciones

- “Nuevo examen disponible”  
- “Tienes una entrega pendiente de corregir”  
- “Tu examen ha sido calificado”  
- “El profesor ha publicado tus resultados”

---

## 🧪 UI/UX

- Icono `fa-bell` en header fijo visible en todo el sistema  
- Badge rojo con número de no leídas  
- Modal lateral desplegable con la lista  
- Opción “Marcar todo como leído”  
- Opción “Ver más” para abrir listado completo (paginado)  

---

## 📋 Estándar de tabla interactiva

- Acciones fuera de la tabla (crear, borrar, desactivar…)  
- Selección múltiple por checkbox  
- Edición directa al hacer clic sobre fila  
- Fila tipo “pijama”  
- Separación clara entre filtros y botones de acción  
- Orden asc/desc en columnas clave  
- Paginación (5/10/15/20/Todos), por defecto 15  

---

## 🔐 Seguridad

- Cada usuario solo puede ver sus propias notificaciones  
- Validación de sesión activa y token CSRF  
- El administrador puede ver logs agregados (no mensajes privados)

---

## 🪵 Manejo de errores y logs

- Fallos al insertar o recuperar → `/almacenamiento/logs/notificaciones_error.log`  
- Acciones críticas registradas en `registro_actividad`  
- Lecturas, marcados como leído y borrados → logueados opcionalmente  

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


## ✅ Checklist Copilot

- [ ] Crear tabla `notificaciones`  
- [ ] Añadir icono `fa-bell` al layout general  
- [ ] Crear controlador `notificaciones.php`  
- [ ] Mostrar lista en modal lateral con scroll  
- [ ] Permitir marcar como leídas o eliminar  
- [ ] Insertar mensajes desde eventos: creación de examen, entrega subida, corrección publicada  
- [ ] Registrar toda acción en `registro_actividad`

---

📌 A continuación, Copilot debe leer e implementar: 06_configuracion.md
