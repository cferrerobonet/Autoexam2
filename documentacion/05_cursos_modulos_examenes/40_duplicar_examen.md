# 40 – Duplicar examen entre cursos o módulos

---

## 🎯 Objetivos clave del sistema

- Permitir la reutilización eficiente de exámenes existentes  
- Facilitar la adaptación de exámenes a distintos cursos o módulos sin recreación manual  
- Asegurar trazabilidad entre original y duplicado  
- Mantener la integridad de las preguntas y respuestas al copiar  
- Registrar cada duplicación en el historial del sistema  

---

## 🧭 Objetivo

Permitir a profesores y administradores duplicar exámenes existentes para reutilizarlos en otros cursos o módulos sin necesidad de recrearlos manualmente, con opción de editar propiedades clave en el proceso.

---

## 🔗 Dependencias

- `14_modulo_examenes.md`
- `12_modulo_cursos.md`
- `13_modulo_modulos.md`
- `25_versionado_examenes.md`

---

## 📊 Funcionalidades

| Funcionalidad            | Descripción                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| Opción “Duplicar examen” | Disponible desde listado o edición de examen                               |
| Selección de destino     | Elegir curso y módulo donde se quiere duplicar                             |
| Modificar propiedades    | Título, fecha, duración, visibilidad, aleatorización                       |
| Copia de preguntas       | Las preguntas del examen original se duplican como nuevas asociadas al nuevo examen |
| Historial opcional       | Se puede registrar el `id_examen_origen` como referencia cruzada           |

---

## 🧪 UI/UX

- Botón “Duplicar” visible en tarjeta o tabla de examen  
- Modal emergente con formulario: curso destino, módulo, título nuevo, fechas  
- Checkbox para copiar también la configuración del examen original  
- Feedback al completar: “Examen duplicado correctamente en curso XYZ”  

---

## 🧱 MVC y rutas implicadas

| Componente               | Ruta                                              |
|--------------------------|---------------------------------------------------|
| Controlador duplicación  | `controladores/duplicar_examen.php`              |
| Vista formulario modal   | `vistas/profesor/modal_duplicar_examen.php`      |
| Utilidades               | `utilidades/clonar_examen.php`                   |

---

## 🗃️ Tablas y campos implicados

Se insertan nuevos registros en:

- `examenes` (nuevo ID, mismo contenido que el original)  
- `preguntas` y `respuestas` asociadas al nuevo `id_examen`  
- Campo opcional: `id_examen_origen` (INT FK)

No se modifica el examen original.

---

## 🔐 Seguridad

- Solo profesores responsables del curso/módulo original o administradores pueden duplicar  
- Validación de integridad al clonar (no clonar preguntas no activas)  
- Registro completo de duplicaciones en `registro_actividad`  

---

## 🪵 Manejo de errores y logs

- Errores en la clonación → registrados en `/almacenamiento/logs/duplicar_examen_error.log`  
- Si faltan datos clave (curso/módulo destino) → feedback en formulario  
- Toda duplicación exitosa → registrada en `registro_actividad` con ID de origen y destino  

---

## ✅ Checklist Copilot

- [ ] Crear controlador `duplicar_examen.php`  
- [ ] Mostrar modal con curso/módulo de destino y nuevo título  
- [ ] Copiar examen, preguntas y respuestas con nuevos IDs  
- [ ] Permitir modificar propiedades antes de guardar  
- [ ] Añadir campo `id_examen_origen` para trazabilidad (opcional)  
- [ ] Registrar duplicación como evento en `registro_actividad`  

---

📌 A continuación, Copilot debe leer e implementar: `34_resumen_academico_alumno.md`
