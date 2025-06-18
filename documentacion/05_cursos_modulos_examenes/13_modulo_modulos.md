# 13 – Módulo de Módulos (Asignaturas) en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Gestionar las asignaturas asociadas a cursos  
- Asociar cada módulo a un profesor y uno o varios cursos  
- Habilitar y desactivar módulos sin perder información  
- Servir como contenedor obligatorio para crear exámenes  
- Registrar todas las acciones en logs y validar con seguridad  

---

## 🧭 Objetivo

Gestionar las asignaturas asociadas a cursos. Cada módulo representa una materia impartida por un profesor y puede estar vinculada a varios cursos. Los exámenes se crean dentro de cada módulo.

---

## 🔗 Dependencias

- Requiere `10_modulo_usuarios.md` (profesores)  
- Relacionado con `12_modulo_cursos.md` y `14_modulo_examenes.md`  

---

## 🗃️ Tablas implicadas

### Tabla `modulos`

| Campo         | Tipo          | Requerido | Descripción                          |
|---------------|---------------|-----------|--------------------------------------|
| id_modulo     | INT PK AI     | ✔️        | Identificador único                  |
| nombre_modulo | VARCHAR(100)  | ✔️        | Nombre del módulo (ej: Matemáticas) |
| id_profesor   | INT (FK)      | ✔️        | Usuario con rol profesor             |
| activo        | TINYINT(1)    | ✔️        | 1 activo / 0 inactivo                |

### Relación `modulo_curso`

| Campo         | Tipo          | Notas                             |
|---------------|---------------|------------------------------------|
| id_relacion   | INT PK AI     | Clave primaria                     |
| id_modulo     | INT (FK)      | Módulo vinculado                   |
| id_curso      | INT (FK)      | Curso al que se asocia             |

---

## 👥 Acceso por rol

| Acción                  | Admin | Profesor | Alumno |
|-------------------------|:-----:|:--------:|:------:|
| Crear módulos           |  ✔️   |    ✔️     |   ❌   |
| Editar módulos propios  |  ✔️   |    ✔️     |   ❌   |
| Asignar a cursos        |  ✔️   |    ✔️     |   ❌   |
| Ver listado             |  ✔️   |    ✔️     |   ❌   |

---

## 📋 Reglas funcionales

- Un módulo puede pertenecer a varios cursos  
- Solo los profesores pueden ser asignados a módulos  
- Exámenes solo pueden crearse dentro de un módulo activo  

---

## 🗑️ Eliminación y desactivación

**Desactivación lógica:**
- `activo = 0`
- Oculta el módulo de la interfaz
- Oculta los exámenes asociados

**Eliminación física:**
- Solo posible desde modo mantenimiento
- Requiere que no haya exámenes asociados activos

---

## 🎨 UI/UX

- Formulario con validación en tiempo real  
- Selector de cursos y profesor  
- Filtros en listado por curso, estado y docente  
- Tooltips y feedback visual con Bootstrap 5  

---

## 🧱 MVC y rutas

| Componente          | Ruta                                              |
|---------------------|---------------------------------------------------|
| Controlador         | `app/controladores/modulos.php`                   |
| Modelo              | `app/modelos/modulo.php`                          |
| Vista: listado      | `app/vistas/administrador/modulos.php`           |
| Vista: formulario   | `app/vistas/administrador/formulario_modulo.php` |
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

- Campos obligatorios vacíos o duplicados → feedback visual y log en `/almacenamiento/logs/modulos_error.log`  
- Acción cancelada → log en `registro_actividad`  
- Eliminación → requiere confirmación y se registra  

---

## ✅ Checklist Copilot

- [ ] Crear tabla `modulos` y relación `modulo_curso`  
- [ ] Crear formulario con validación y selección de cursos/profesor  
- [ ] Aplicar desactivación lógica con cascada a exámenes  
- [ ] Mostrar filtros por estado, curso y docente  
- [ ] Proteger eliminación física con confirmación  
- [ ] Registrar actividad en log  

---

📌 A continuación, Copilot debe leer e implementar: `14_modulo_examenes.md`
