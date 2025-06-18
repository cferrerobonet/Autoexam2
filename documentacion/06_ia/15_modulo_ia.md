# 15 – Módulo de inteligencia artificial en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Automatizar la generación de preguntas a partir de documentos PDF u órdenes simples  
- Sugerir preguntas adicionales mediante IA entrenada con contenido previo del módulo  
- Permitir la corrección automática de respuestas de desarrollo con IA según rúbricas base  
- Aumentar la eficiencia del profesorado y la calidad del examen  
- Ofrecer explicaciones claras, trazables y visualizables al alumno sobre su calificación

---

## 🧭 Objetivo

Incorporar funcionalidades de inteligencia artificial para apoyar al profesorado en la creación, validación y corrección de exámenes de forma automática o semiautomática.

---

## 🔗 Dependencias

- `14_modulo_examenes.md` (exámenes creados manual o automáticamente)  
- `35_banco_preguntas.md` (almacén de preguntas generadas)  
- `31_gestion_multimedia.md` (PDF, enlaces o contenido fuente)

---

## 🧠 Funcionalidades IA

| Funcionalidad                       | Activador                          | Resultado                                                        |
|-------------------------------------|------------------------------------|------------------------------------------------------------------|
| Generar preguntas desde PDF         | Subida de PDF                      | Preguntas test o desarrollo con opciones                         |
| Generar pregunta desde texto libre  | Formulario con prompt              | Pregunta con 3-5 respuestas posibles                             |
| Corregir respuesta de desarrollo    | Al guardar calificación            | Puntaje estimado + justificación textual                         |
| Sugerir comentario personalizado    | Tras corrección                    | Comentario IA opcional visible al alumno                         |

---

## 🧪 UI/UX

- Editor enriquecido con botón “IA” al lado de cada área de redacción  
- Modal con progreso de generación/corrección  
- Modo revisión: botón “Ver explicación IA”  
- Badge visual “Corregido por IA”  
- Feedback visual (éxito, error, confianza estimada, etc.)

---

## 📂 MVC y rutas

| Componente                  | Ruta                                                  |
|-----------------------------|--------------------------------------------------------|
| Backend API IA              | `controladores/ia_controller.php`                     |
| Vista botones               | `vistas/profesor/editar_examen.php`, `corregir.php`   |
| Módulo base de redacción    | `publico/scripts/ia_modulo.js`                         |
| Generador IA PDF            | `utilidades/ia_pdf_parser.php`                         |

---

## 🛡️ Seguridad

- Solo profesores o administradores pueden activar herramientas IA  
- Revisión posterior manual posible para cualquier corrección automatizada  
- Trazabilidad completa de generación/corrección en `registro_actividad`  
- Se guarda si fue o no aceptada por el profesor la sugerencia IA  

---

## 🪵 Manejo de errores y logs

- Fallos de conexión con el servicio IA → log en `/almacenamiento/logs/ia_error.log`  
- Preguntas no válidas generadas → rechazo y mensaje  
- Cualquier corrección incorrecta → se marca para revisión posterior  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación de entrada antes de enviar a IA (tipo, tamaño, formato)  
- Registro en `registro_actividad` de cada acción con IA  
- Modal visual con progreso y estado  
- Botón para aceptar o rechazar sugerencia de IA  
- Separación entre correcciones IA y humanas (badge y logs diferenciados)  
- Protección de recursos externos (PDF, URLs) usados como fuente  

---

## ✅ Checklist Copilot

- [ ] Habilitar subida de PDF y extracción de texto  
- [ ] Implementar llamada a servicio de generación de preguntas  
- [ ] Añadir botón de corrección IA en exámenes de desarrollo  
- [ ] Mostrar sugerencia de nota + comentario generado  
- [ ] Registrar acciones IA en log y actividad  
- [ ] Mostrar badges IA en vista alumno  

---

📌 A continuación, Copilot debe leer e implementar: 16_modulo_calificaciones.md
