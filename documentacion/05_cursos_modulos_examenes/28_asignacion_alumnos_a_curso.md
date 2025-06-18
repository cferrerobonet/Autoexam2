# 28 – Asignacion de alumnos a cursos

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `12_modulo_cursos.md`

Este modulo permite asignar alumnos existentes a uno o varios cursos desde la vista de edicion del curso, mediante una interfaz visual e interactiva.

---

## 🎯 Objetivos clave del sistema

- Facilitar la asignación dinámica y controlada de alumnos a cursos ya creados
- Evitar errores de duplicación, sobreescritura o asignación cruzada
- Permitir una interfaz visual clara, filtrable y responsive para el administrador o profesor responsable
- Reforzar la trazabilidad de cambios en asignaciones de grupo

---

## 👤 Quien puede asignar alumnos

- Administrador: puede modificar cualquier curso
- Profesor: solo puede modificar cursos que tenga asignados

---

## 📊 Tabla intermedia: `curso_alumno`

| Campo       | Tipo        | Descripcion                         |
|-------------|-------------|-------------------------------------|
| id          | INT PK AI   | ID                                  |
| id_curso    | INT FK      | Curso                               |
| id_alumno   | INT FK      | Alumno                              |

---

## 📂 MVC

| Componente                        | Ruta                                              |
|-----------------------------------|---------------------------------------------------|
| Vista edicion curso               | `vistas/cursos/editar_curso.php`                  |
| Controlador guardar alumnos       | `controladores/guardar_alumnos_curso.php`         |
| Modelo relacion                   | `modelos/curso_alumno.php`                        |
| JS asignacion AJAX                | `publico/scripts/asignar_alumnos.js`              |

---

## 🧪 UI/UX

- Selector multiple con buscador (`select2`)
- Alumnos ya asignados precargados
- Boton “Guardar cambios” con feedback de exito/error
- Posibilidad de quitar alumnos del curso
- Validaciones:
  - Si el curso es de otro profesor → acceso denegado
  - Si no hay alumnos seleccionados → advertencia

---

## 🛡️ Seguridad

- Validacion de permisos segun rol y propiedad del curso
- CSRF token en formulario
- Validacion del lado servidor en controlador
- Registro en `registro_actividad`

---

## 🪵 Manejo de errores y logs

- Acceso denegado a curso no autorizado → log en `/almacenamiento/logs/asignacion_error.log`
- Alumnos inexistentes o inválidos → feedback y log de rechazo
- Acciones exitosas de asignación/desasignación → registro completo

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación cruzada de rol, curso y relación
- Control de duplicados al guardar
- CSRF activo en formulario
- Asignación por AJAX con feedback visual claro
- Registro detallado en `registro_actividad`
- Logs técnicos separados por tipo de error

---

## 📋 Estandar de tabla interactiva

- Acciones fuera de la tabla (crear, borrar, desactivar…)
- Seleccion multiple por checkbox
- Edicion directa al hacer clic sobre fila
- Fila tipo “pijama”
- Separacion clara entre filtros y botones de accion
- Orden asc/desc en columnas clave
- Paginacion (5/10/15/20/Todos), por defecto 15

---

## ✅ Checklist Copilot

- [ ] Crear tabla `curso_alumno`
- [ ] Implementar select2 con alumnos disponibles
- [ ] Cargar alumnos asignados al curso
- [ ] Guardar cambios via AJAX
- [ ] Validar permisos del usuario
- [ ] Registrar accion en log

---

📌 A continuación, Copilot debe leer e implementar: 29_resumen_curso.md