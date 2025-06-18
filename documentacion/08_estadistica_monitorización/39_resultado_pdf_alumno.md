# 39 – Resultado individual PDF por alumno

---

## 🎯 Objetivos clave del sistema

- Generar una hoja PDF personalizada con los resultados académicos de un alumno  
- Ofrecer un resumen claro, exportable y validado de su progreso  
- Facilitar la entrega física, archivo institucional o informe a familias  
- Asegurar que solo roles con permisos puedan generar este documento  
- Garantizar trazabilidad de la exportación  

---

## 🧭 Objetivo

Permitir a profesores y administradores generar una hoja resumen individual en PDF con los resultados académicos de un alumno: notas por módulo, medias, información general y observaciones opcionales.

---

## 🔗 Dependencias

- `10_modulo_usuarios.md`
- `12_modulo_cursos.md`
- `16_modulo_calificaciones.md`
- `36_informe_global_curso.md`
- `33_exportacion_datos.md`
- `31_gestion_multimedia.md`

---

## 📊 Funcionalidades

| Funcionalidad              | Descripción                                                    |
|----------------------------|----------------------------------------------------------------|
| Seleccionar alumno         | Desde vista de alumnos del curso                              |
| Generar informe PDF        | Exportar hoja limpia con identidad del alumno y resultados     |
| Incluir medias             | Por módulo y general                                           |
| Añadir observación final   | Comentario opcional del profesor                               |
| Descargar PDF              | Archivo generado temporalmente y descargable                   |

---

## 🧪 UI/UX

- Opción “Ver resumen alumno” junto a cada línea en listado de alumnos  
- Formulario modal para añadir observación final  
- PDF limpio: logo institucional, nombre del centro, pie de página  
- Botón “Exportar como PDF” accesible solo a roles con permiso  

---

## 🧱 MVC y rutas implicadas

| Componente             | Ruta                                           |
|------------------------|------------------------------------------------|
| Generador PDF          | `utilidades/exportar_resultado_alumno.php`    |
| Controlador            | `controladores/resultados_pdf.php`            |
| Vista alumno en curso  | `vistas/profesor/ver_resultado_alumno.php`    |
| Carpeta temporal PDF   | `/tmp/pdf_resultados/`                         |

---

## 🗃️ Tablas y campos implicados

No requiere nuevas tablas. Usa:

- `usuarios` → nombre, apellidos, rol, curso  
- `modulos`, `cursos`, `calificaciones`, `examenes`  

Consulta JOIN combinada y filtrada por `id_alumno`.

---

## 🔐 Seguridad

- Solo profesores del curso o administradores pueden generar el PDF  
- Protección de ID alumno + curso activo  
- Registro en `registro_actividad` al exportar  

---

## 🪵 Manejo de errores y logs

- Fallos en generación del PDF → log en `/almacenamiento/logs/resultados_pdf_error.log`  
- Exportación exitosa → registrada en `registro_actividad`  
- Intento no autorizado → bloqueado y registrado  

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

- [ ] Crear generador PDF `exportar_resultado_alumno.php`  
- [ ] Cargar alumno, curso, módulos y notas  
- [ ] Añadir campo de observación opcional  
- [ ] Crear plantilla visual con logo y datos  
- [ ] Permitir exportar desde vista de curso  
- [ ] Validar permisos y registrar exportación  

---

📌 A continuación, Copilot debe leer e implementar: 36_informe_global_curso.md
