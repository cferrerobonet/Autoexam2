# 32 – Modulo de calendario y programacion de eventos

---

## 🎯 Objetivos clave del sistema

- Mostrar de forma visual todos los exámenes programados para cada usuario según su rol  
- Facilitar la planificación académica con una vista filtrable por fechas, cursos, módulos y estados  
- Permitir acceder directamente a la edición o intento del examen desde el evento  
- Integrarse con el dashboard para alertas y recordatorios  
- Asegurar que solo los usuarios autorizados visualicen o editen los eventos relacionados  

---

## 🧭 Objetivo

Proveer una vista global, visual y filtrable de los examenes programados por hora y fecha para cada usuario (admin, profesor, alumno), permitiendo gestionar mejor el tiempo, la carga y los accesos.

---

## 🔗 Dependencias

- `14_modulo_examenes.md` (campos `fecha_hora_inicio`, `fecha_hora_fin`)
- `18_dashboard_por_rol.md` (puede mostrar resumen diario/semanal)
- `10_modulo_usuarios.md` (para saber a qué examenes accede cada rol)

---

## 📊 Tablas implicadas

No se crea tabla nueva. Se usa la existente:

### Tabla `examenes` (fragmento relevante)

| Campo             | Tipo       | Descripcion                         |
|------------------|------------|-------------------------------------|
| fecha_hora_inicio| DATETIME   | Fecha y hora exacta de inicio       |
| fecha_hora_fin   | DATETIME   | Fecha y hora exacta de cierre       |

---

## 👥 Acceso por rol

| Acción                        | Admin | Profesor | Alumno |
|-------------------------------|:-----:|:--------:|:------:|
| Ver calendario completo       |  ✔️   |   ✔️     |   ❌   |
| Ver examenes personales       |  ❌   |   ❌     |   ✔️   |
| Editar desde calendario       |  ✔️   |   ✔️     |   ❌   |
| Acceder desde evento calendar|  ✔️   |   ✔️     |   ✔️   |

---

## 🗓️ Comportamiento

- Cada examen aparece como evento con:
  - Título (nombre del examen)
  - Hora de inicio y fin
  - Curso y módulo
  - Color por estado (`publicado`, `activo`, `cerrado`, `borrador`)
- Los eventos se renderizan por hora (no solo por día)
- Al hacer clic: se abre la edición o el intento, según el rol

---

## 🎨 UI/UX

- Uso de librería `FullCalendar` o similar con vista semanal/mensual
- Eventos con colores e iconos según estado
- Tooltips al pasar sobre un evento: duración, módulo, profesor
- Filtros:
  - Curso
  - Módulo
  - Estado (`activo`, `finalizado`, `borrador`)
  - Profesor (solo visible para admin)

---

## 🧱 MVC y rutas

| Componente             | Ruta                                           |
|------------------------|------------------------------------------------|
| Vista principal        | `vistas/comunes/calendario_examenes.php`       |
| Controlador API        | `controladores/api/calendario_eventos.php`     |
| JS de eventos          | `publico/scripts/calendario.js`                |
| Fuente de datos (JSON) | `examenes` filtrados con acceso por rol        |

---

## 🪵 Manejo de errores y logs

- Fallos al cargar eventos → `/almacenamiento/logs/calendario_error.log`
- Acceso no permitido a edición o intento → registrado en `registro_actividad`
- Error de formato en fechas → feedback visual y log

---

## 🛡️ Fortalezas que Copilot debe implementar

- Carga dinámica de eventos según rol y curso asignado  
- Protección de eventos con token y verificación de sesión  
- Validación de acceso a edición/intento desde evento clicado  
- Filtrado avanzado con seguridad y protección contra manipulación  
- Logs en caso de fallos de carga o intentos no autorizados  
- Integración visual clara y coherente con dashboard por rol  

---

## ✅ Checklist Copilot

- [ ] Crear vista con FullCalendar
- [ ] Cargar examenes según `fecha_hora_inicio` y `fecha_hora_fin`
- [ ] Mostrar eventos diferenciados por color y duración
- [ ] Hacer eventos clicables según permisos
- [ ] Agregar filtros por curso, modulo y estado
- [ ] Mostrar resumen de calendario en dashboard
- [ ] Validar acceso temporal a examen por hora exacta

---

📌 A continuación, Copilot debe leer e implementar: 18_dashboard_por_rol.md
