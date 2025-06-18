# 16 – Módulo de calificaciones

---

## 🎯 Objetivos clave del sistema

- Permitir calificar respuestas de forma automática o manual  
- Ofrecer herramientas visuales y eficientes para revisar respuestas  
- Garantizar integridad, trazabilidad y precisión de las notas  
- Habilitar observaciones asociadas a cada calificación  
- Ofrecer al alumno retroalimentación visual, clara y estructurada

---

## 🧭 Objetivo

Calificar respuestas de los alumnos (automáticamente o manualmente), registrar las notas y permitir al alumno visualizar sus resultados por examen.

---

## 🔗 Dependencias

- `14_modulo_examenes.md`  
- `15_modulo_ia.md`  
- `34_resumen_academico_alumno.md`  
- `39_resultado_pdf_alumno.md`  

---

## 📊 Tabla `calificaciones`

| Campo           | Tipo          | Descripción                                |
|------------------|---------------|--------------------------------------------|
| id_calificacion  | INT PK AI     | Identificador                              |
| id_examen        | INT FK        | Examen correspondiente                     |
| id_alumno        | INT FK        | Alumno evaluado                            |
| nota_final       | DECIMAL(5,2)  | Nota total calculada o ingresada           |
| modo_correccion  | ENUM          | 'manual', 'auto', 'mixto'                  |
| observaciones    | TEXT          | Comentario visible para el alumno          |
| fecha_correccion | DATETIME      | Cuándo se calificó                         |
| corregido_por    | INT FK        | Usuario que corrigió (admin o profesor)    |

---

## 🧪 UI/UX

- Panel de revisión de exámenes con navegación pregunta a pregunta  
- Visualización diferenciada entre test (auto) y desarrollo (manual)  
- Campo visual para nota y observaciones  
- Barra de progreso de corrección  
- Confirmación al guardar cada calificación  
- Posibilidad de mostrar u ocultar resultado al alumno (según fecha)  

---

## 📂 MVC y rutas implicadas

| Componente             | Ruta                                           |
|------------------------|------------------------------------------------|
| Controlador corrección | `controladores/corregir_examen.php`           |
| Vista profesor         | `vistas/profesor/corregir_examen.php`         |
| Modelo calificación    | `modelos/calificacion.php`                    |
| Resultado alumno       | `vistas/alumno/ver_resultado.php`             |
| Exportación PDF        | `utilidades/exportar_resultado_alumno.php`    |

---

## 🪵 Manejo de errores y logs

- Fallos en guardado de nota → log en `/almacenamiento/logs/calificaciones_error.log`  
- Intento de corrección no autorizado → registrado en `registro_actividad`  
- Nota fuera de rango permitido → rechazo con feedback visual  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación de acceso a calificación según rol y propiedad del examen  
- Control de duplicado de calificación (evitar doble guardado)  
- Hash y logs de toda corrección manual  
- Trazabilidad: qué usuario corrigió, cuándo y cómo  
- Feedback claro para el alumno en su panel  
- Posibilidad de reactivar corrección en caso de error  
- Separación de lógica de corrección test/desarrollo  

---

## ✅ Checklist Copilot

- [ ] Crear tabla `calificaciones`  
- [ ] Registrar nota, observación, corrector y fecha  
- [ ] Mostrar en `ver_resultado.php` para el alumno  
- [ ] Separar corrección test (auto) vs desarrollo (manual)  
- [ ] Habilitar edición segura por parte del profesor  
- [ ] Aplicar seguridad por curso, profesor y sesión  
- [ ] Registrar acciones en `registro_actividad`  

---

📌 A continuación, Copilot debe leer e implementar: 17_modulo_estadisticas.md
