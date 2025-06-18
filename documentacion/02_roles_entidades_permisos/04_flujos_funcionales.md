# 04 – Flujos funcionales entre entidades y acciones

---

## 🎯 Objetivos clave del sistema

- Representar visual y conceptualmente los flujos de trabajo del sistema entre entidades principales  
- Servir como referencia estructural para la lógica de navegación, acciones encadenadas y permisos  
- Definir qué entidad genera a cuál, cómo se relacionan y qué rol lo puede iniciar o gestionar  
- Clarificar dependencias funcionales y relaciones activas para evitar errores de diseño o duplicidad  
- Guía para navegación lógica y jerárquica en la app

---

## 🔗 Dependencias

- `03_roles_entidades.md`
- `10_modulo_usuarios.md`
- `12_modulo_cursos.md`
- `13_modulo_modulos.md`
- `14_modulo_examenes.md`

---

## 🧭 Flujo general

```plaintext
ADMIN O PROFESOR
  └─> crea CURSO
          └─> asigna ALUMNOS
          └─> crea MODULOS
                    └─> crea EXAMENES
                                └─> alumnos realizan
                                          └─> se califican
                                                  └─> resumen y estadística
```

---

## 🔄 Flujo desde curso

| Acción                            | Quién puede realizarla  | Resultado esperado                      |
|----------------------------------|--------------------------|------------------------------------------|
| Crear curso                      | Admin / Profesor         | Curso activo con o sin alumnos           |
| Asignar alumnos                  | Admin / Profesor         | Se relacionan en tabla `curso_alumno`    |
| Añadir módulos                   | Admin / Profesor         | Se crea relación `modulo_curso`          |
| Asignar profesor                 | Admin                    | Se establece vínculo curso-profesor      |

---

## 🔁 Flujo desde módulo

| Acción                            | Quién puede realizarla  | Resultado esperado                          |
|----------------------------------|--------------------------|----------------------------------------------|
| Crear módulo                     | Admin / Profesor         | Módulo disponible para cursos                |
| Asociar a curso                  | Admin / Profesor         | Relación en tabla intermedia                 |
| Crear examen                     | Admin / Profesor         | Asociado al módulo y visible en resumen curso|
| Ver estadísticas                 | Admin / Profesor         | Mostrar promedios y participaciones          |

---

## 🧪 Flujo de realización y corrección

1. Profesor crea examen dentro de módulo de un curso
2. Establece fechas, visibilidad, preguntas
3. Alumno lo visualiza y lo realiza
4. Examen se autocorrige o corrige manualmente
5. Calificación queda registrada
6. Disponible en panel alumno y para informe de curso

---

## 📑 Apoyo gráfico

En desarrollo opcional para incluir como imagen o gráfico interactivo.

---

## 🪵 Manejo de errores y logs

- Si se intenta crear una entidad sin su padre (módulo sin curso, examen sin módulo) → log y error visual  
- Fallos en enlaces intermedios (`curso_alumno`, `modulo_curso`) → `/almacenamiento/logs/flujo_error.log`  
- Intentos de flujo no autorizado por rol → registrado en `registro_actividad`  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación cruzada entre entidades antes de crear relaciones  
- Control de jerarquía: no crear exámenes sin módulos, ni módulos sin curso  
- Verificación del rol en cada acción de flujo  
- Logs diferenciados por tipo de ruptura del flujo  
- Refuerzo visual de las relaciones para evitar confusiones  
- Navegación contextual guiada según paso anterior  

---

## ✅ Checklist Copilot

- [ ] Crear lógica de navegación fluida curso → módulo → examen  
- [ ] Verificar que todas las relaciones se validan antes de insertarse  
- [ ] Registrar cada paso importante en log de actividad  
- [ ] Bloquear acciones si el flujo lógico no se cumple  
- [ ] Añadir ayuda visual (tooltips, iconos) para mostrar el flujo  
- [ ] Implementar rutas encadenadas donde sea posible  

---

📌 A continuación, Copilot debe leer e implementar: 21_gestion_permisos.md
