# 14 – Módulo de Exámenes en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Permitir a profesores crear y gestionar exámenes para cursos y módulos específicos  
- Incluir soporte para preguntas tipo test y desarrollo  
- Permitir corrección manual o automática mediante IA  
- Garantizar la seguridad del entorno de examen  
- Ofrecer al alumno una experiencia clara, funcional y protegida  

---

## 🧭 Objetivo

Gestionar la creación, configuración, asignación, realización y corrección de exámenes. Un examen pertenece a un módulo y se asigna a un curso. Puede contener preguntas tipo test o desarrollo.

---

## 🔗 Dependencias

- Requiere `12_modulo_cursos.md` y `13_modulo_modulos.md`  
- Relacionado con `15_modulo_ia.md` para generación/corrección automática  
- Sincroniza con `32_calendario_eventos.md`  

---

## 📊 Tablas implicadas

### Tabla `examenes`

| Campo           | Tipo           | Descripción                                 |
|------------------|----------------|---------------------------------------------|
| id_examen        | INT PK AI      | Identificador único                         |
| titulo           | VARCHAR(150)   | Nombre del examen                           |
| id_modulo        | INT (FK)       | Módulo al que pertenece                     |
| id_curso         | INT (FK)       | Curso asignado                              |
| tiempo_limite    | INT            | Duración en minutos                         |
| aleatorio_preg   | TINYINT(1)     | Mostrar preguntas en orden aleatorio        |
| aleatorio_resp   | TINYINT(1)     | Orden aleatorio de respuestas               |
| fecha_inicio     | DATETIME       | Fecha de inicio                             |
| fecha_fin        | DATETIME       | Fecha de cierre                             |
| visible          | TINYINT(1)     | 1 visible / 0 oculto                         |
| activo           | TINYINT(1)     | Activo o desactivado                        |

### Tabla `preguntas`

| Campo         | Tipo        | Descripción                                    |
|----------------|-------------|-------------------------------------------------|
| id_pregunta    | INT PK AI   | Identificador                                  |
| id_examen      | INT (FK)    | Relación al examen                             |
| tipo           | ENUM        | 'test' o 'desarrollo'                          |
| enunciado      | TEXT        | Pregunta                                       |
| media_tipo     | ENUM        | 'imagen', 'video', 'url', 'pdf', 'ninguno'     |
| media_valor    | TEXT        | Ruta o enlace                                  |
| habilitada     | TINYINT(1)  | Visible o no                                   |

### Tabla `respuestas` (solo para tipo test)

| Campo         | Tipo        | Descripción                                    |
|----------------|-------------|-------------------------------------------------|
| id_respuesta   | INT PK AI   | Identificador                                  |
| id_pregunta    | INT (FK)    | Relación a pregunta                             |
| texto          | TEXT        | Texto de la respuesta                           |
| correcta       | TINYINT(1)  | 1 si es correcta                                |
| media_tipo     | ENUM        | Multimedia asociada                             |
| media_valor    | TEXT        | Ruta o URL                                      |

---

## 👥 Acceso por rol

| Acción                     | Admin | Profesor | Alumno |
|----------------------------|:-----:|:--------:|:------:|
| Crear examen               |  ✔️   |    ✔️     |   ❌   |
| Editar examen              |  ✔️   |    ✔️     |   ❌   |
| Realizar examen            |  ❌   |    ❌     |   ✔️   |
| Corregir (manual o IA)     |  ✔️   |    ✔️     |   ❌   |

---

## 📋 Funcionalidad especial IA

- Subida de PDF → IA sugiere preguntas test/desarrollo  
- Corrección automática de desarrollo mediante rúbrica  
- Comentarios generados por IA visibles al alumno  

---

## 🔐 Seguridad examen

- Fechas y horas visibles desde el calendario (`32_calendario_eventos.md`)  
- Bloqueo de copia, selección de texto, y captura (donde se pueda)  
- Temporizador y autoguardado AJAX  
- Reanudación si hay reconexión  
- Reintento posible si el profesor lo permite  

---

## 🎨 UI/UX

- Editor enriquecido de preguntas  
- Selector multimedia con vista previa  
- Reordenar preguntas drag & drop  
- Temporizador y modo sin distracciones  

---

## 🧱 MVC y rutas

| Componente               | Ruta                                            |
|--------------------------|-------------------------------------------------|
| Controlador              | `app/controladores/examenes.php`               |
| Modelo examen            | `app/modelos/examen.php`                       |
| Modelo pregunta          | `app/modelos/pregunta.php`                     |
| Modelo respuesta         | `app/modelos/respuesta.php`                    |
| Vista: listado profesor  | `app/vistas/profesor/examenes.php`            |
| Vista: formulario        | `app/vistas/profesor/formulario_examen.php`   |
| Vista: alumno            | `app/vistas/alumno/realizar_examen.php`       |
| JS examen                | `publico/scripts/examen.js`                    |

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

- Fallo al guardar examen o preguntas → `/almacenamiento/logs/examenes_error.log`  
- Intentos fuera de tiempo → log en `registro_actividad`  
- Acciones como creación, edición, asignación → log activado  

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

- [ ] Crear tabla `examenes`, `preguntas`, `respuestas`  
- [ ] Añadir multimedia y orden aleatorio de preguntas  
- [ ] Permitir corrección IA y comentarios automáticos  
- [ ] Autoguardado con AJAX  
- [ ] Temporizador + bloqueo de teclado/ratón donde se permita  
- [ ] Registrar todo en `registro_actividad`  
- [ ] Exportar resultados por alumno/profesor (XLSX, CSV, PDF)  
- [ ] Integrar con calendario de eventos  

---

📌 A continuación, Copilot debe leer e implementar: `25_versionado_examenes.md`
