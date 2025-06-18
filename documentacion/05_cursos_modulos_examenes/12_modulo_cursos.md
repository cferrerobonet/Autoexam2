# 12 – Módulo de Cursos en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Gestionar cursos como contenedores de módulos, alumnos y exámenes  
- Asociar fácilmente profesores y alumnos a cada curso  
- Permitir activar/desactivar cursos sin pérdida de historial  
- Mantener la integridad entre curso, módulos y relaciones  
- Facilitar la edición visual con validación inmediata  

---

## 🧭 Objetivo

Gestionar la creación, edición, desactivación lógica y visualización de cursos. Cada curso puede tener alumnos, módulos y exámenes asociados.

---

## 🔗 Dependencias

- Requiere `10_modulo_usuarios.md` (gestión de usuarios con rol profesor y alumno)  
- Relacionado con `13_modulo_modulos.md` y `14_modulo_examenes.md`  

---

## 🗃️ Tablas implicadas

### Tabla `cursos`

| Campo         | Tipo         | Requerido | Descripción                           |
|---------------|--------------|-----------|----------------------------------------|
| id_curso      | INT PK AI    | ✔️        | Identificador único del curso          |
| nombre_curso  | VARCHAR(100) | ✔️        | Nombre descriptivo (ej: 1BachA)        |
| id_profesor   | INT (FK)     | ✔️        | Usuario con rol profesor               |
| activo        | TINYINT(1)   | ✔️        | 1 activo / 0 inactivo                  |

### Tabla `alumno_curso`

| Campo            | Tipo       | Notas                                 |
|------------------|------------|----------------------------------------|
| id_relacion      | INT PK AI  | Clave primaria                         |
| id_alumno        | INT (FK)   | Solo usuarios con rol alumno           |
| id_curso         | INT (FK)   | Curso al que pertenece el alumno       |
| fecha_asignacion | DATETIME   | Fecha de asignación del alumno         |

---

## 👥 Acceso por rol

| Acción                  | Admin | Profesor | Alumno |
|-------------------------|:-----:|:--------:|:------:|
| Crear curso             |  ✔️   |   ✔️     |   ❌   |
| Editar curso            |  ✔️   |   ✔️     |   ❌   |
| Eliminar o desactivar   |  ✔️   |   ✔️     |   ❌   |
| Asignar alumnos         |  ✔️   |   ✔️     |   ❌   |
| Ver cursos asignados    |  ✔️   |   ✔️     |   ✔️   |

---

## 📋 Reglas funcionales

- Un curso puede contener muchos alumnos y módulos  
- Un profesor puede tener varios cursos  
- Los cursos son permanentes (sin año académico)  
- Al finalizar un curso, se desasignan alumnos; los módulos y exámenes no se eliminan  

---

## 🗑️ Eliminación y desactivación

**Desactivación lógica:**
- Se marca con `activo = 0`
- Se oculta de la interfaz
- Desactiva módulos y exámenes asociados
- Desasigna alumnos y desvincula profesor

**Eliminación física:**
- Solo disponible en modo mantenimiento
- Requiere:
  - No tener módulos ni exámenes activos
  - Desasignar previamente a todos los alumnos
  - Confirmación doble por parte del administrador

---

## 🧑‍🤝‍🧑 Asociación de alumnos

- Solo desde el módulo `cursos`  
- Mediante tabla con checkboxes (selector múltiple)  
- Relación gestionada vía `tabla alumno_curso`  

---

## 🎨 UI/UX

- Formulario con validaciones en tiempo real  
- Iconos: `fa-book`, `fa-user`, `fa-link`  
- Tabla con alumnos asignados y botón "Agregar alumnos"  
- Tooltips y feedback visual claro  

---

## 🧱 MVC y rutas implicadas

| Componente          | Ruta                                              |
|---------------------|---------------------------------------------------|
| Controlador         | `app/controladores/cursos.php`                    |
| Modelo              | `app/modelos/curso.php`                           |
| Vista: listado      | `app/vistas/administrador/cursos.php`            |
| Vista: formulario   | `app/vistas/administrador/formulario_curso.php`  |
| Vista: asignar      | `app/vistas/administrador/asignar_alumnos.php`   |
| Validaciones        | `app/utilidades/validacion.php`                  |

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

## 🪵 Manejo de errores y logs

- Inserciones inválidas → feedback visual y log en `/almacenamiento/logs/cursos_error.log`  
- Errores críticos → notificación visible al administrador (si está en modo debug)  
- Acciones clave → registradas en `registro_actividad` (crear, editar, desactivar, asignar alumnos)

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

- [ ] Crear tabla `cursos` y `alumno_curso`  
- [ ] Crear formulario y controlador de creación/edición  
- [ ] Asignar alumnos desde interfaz de edición  
- [ ] Desactivar curso y aplicar cascada lógica  
- [ ] Implementar eliminación física en modo mantenimiento  
- [ ] Separar filtros de acciones en tabla  
- [ ] Registrar acciones en `registro_actividad`

---

📌 A continuación, Copilot debe leer e implementar: `13_modulo_modulos.md`
