# 18 – Dashboard por rol en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Mostrar un resumen claro, útil y adaptado al rol del usuario  
- Facilitar accesos directos a las funcionalidades más frecuentes  
- Ofrecer estadísticas básicas y recordatorios activos (notificaciones)  
- Integrar visualmente calendario, módulos y acciones recientes  
- Adaptarse a cualquier dispositivo con diseño responsive  

---

Este documento define la pantalla principal (home) que ve cada usuario inmediatamente tras iniciar sesión, adaptada según su rol: administrador, profesor o alumno.

---

## 👑 Administrador – Vista inicial

### Panel general del sistema

**Contenido mostrado:**
- 👥 Conteo de usuarios:
  - Total de administradores, profesores, alumnos
- 🏫 Cursos activos y módulos asignados
- 🔄 Últimos accesos y acciones recientes (`registro_actividad`)
- ⚙️ Accesos directos a:
  - Crear nuevo usuario
  - Crear nuevo curso o módulo
  - Configuración general (SMTP, BD, FTP)
  - Modo mantenimiento y limpieza
- 📊 Estadísticas globales (resumen numérico o gráfico)
- 🔔 Estado del sistema (conexión SMTP, último backup, etc.)

---

## 👨‍🏫 Profesor – Vista inicial

- 🗓️ Acceso al calendario de exámenes programados vía `32_calendario_eventos.md`.

### Mis cursos, módulos y exámenes

**Contenido mostrado:**
- 📚 Lista de cursos donde es responsable
  - Nº de alumnos por curso
  - Botón "Gestionar alumnos"
- 🧩 Módulos asignados al profesor
  - Botón "Crear nuevo examen"
- 📝 Exámenes recientes:
  - Estado (activo, cerrado, pendiente)
  - Acceso directo a corregir, editar o reactivar
- 🤖 Notificaciones:
  - Sugerencias IA disponibles
  - Exámenes sin corregir
- 📈 Estadísticas personales (notas promedio, participación por curso)

---

## 👨‍🎓 Alumno – Vista inicial

### Mis exámenes y calificaciones

**Contenido mostrado:**
- ⏱️ Lista de exámenes activos disponibles
  - Fecha de apertura y cierre
  - Tiempo restante
  - Botón "Comenzar" si disponible
- 📈 Resultados anteriores:
  - Nota final (solo si permitido)
  - Estado del examen (pendiente, corregido, repetible)
  - Comentarios del profesor o IA (si visibles)
- 🔔 Notificaciones:
  - Examen próximo a vencer
  - Permiso para repetir un examen

---

## 🪵 Manejo de errores y logs

- Fallos en carga de estadísticas → log en `/almacenamiento/logs/dashboard_error.log`  
- Acceso por rol no válido → redirección + registro en `registro_actividad`  
- Elementos ocultos por error → fallback visual (mensaje + contacto)

---

## 🛡️ Fortalezas que Copilot debe implementar

- Verificación inmediata del rol y redirección en caso de error  
- Control de acceso por sesión activa y token CSRF  
- Separación visual de bloques con UI/UX responsiva  
- Fallback por cada bloque de datos si no hay conexión o respuesta  
- Registro de accesos y errores de carga en `registro_actividad`  
- Compatible con `FullCalendar` y vistas adaptadas por tipo de usuario

---

## ✅ Checklist para Copilot

- [ ] Mostrar dashboard específico según rol  
- [ ] Incluir accesos rápidos y resumen de estado  
- [ ] Visualizar exámenes y actividades recientes  
- [ ] Generar notificaciones por usuario  
- [ ] Incluir estadísticas compactas (gráficas opcionales)

---

📌 A continuación, Copilot debe leer e implementar: 26_registro_guiado_alumnos.md
