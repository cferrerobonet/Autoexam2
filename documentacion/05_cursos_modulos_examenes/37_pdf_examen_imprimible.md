# 37 – Generación de examen imprimible (modo papel)

---

## 🎯 Objetivos clave del sistema

- Permitir exportar cualquier examen en formato físico con apariencia profesional  
- Ofrecer diferentes opciones de personalización antes de imprimir (respuestas, orden, hoja de desarrollo)  
- Facilitar la corrección manual en papel  
- Cumplir requisitos de archivado, inspección o entrega en formato tradicional  
- Garantizar seguridad y trazabilidad de la acción  

---

## 🧭 Objetivo

Permitir a profesores y administradores generar una versión PDF lista para imprimir de cualquier examen existente, con formato claro, profesional y opciones de visualización para exámenes físicos o archivado.

---

## 🔗 Dependencias

- `14_modulo_examenes.md`
- `31_gestion_multimedia.md`
- `33_exportacion_datos.md`
- `35_banco_preguntas.md`

---

## 📊 Funcionalidades

| Funcionalidad                    | Descripción                                                        |
|----------------------------------|---------------------------------------------------------------------|
| Generar versión PDF del examen   | Vista de impresión sin distracciones                              |
| Opciones de impresión            | Mostrar/ocultar respuestas, barajar orden, incluir hoja de desarrollo |
| Diseño limpio y formal           | Cabecera con logo, curso, módulo, fecha y duración del examen     |
| Pie de página                    | Paginación, pie opcional con firma o código del docente           |
| Modo de corrección               | Opción para imprimir también con respuestas correctas marcadas     |

---

## 🧪 UI/UX

- Botón “Imprimir examen” visible solo para admin y profesores  
- Modal con opciones de impresión: incluir respuestas, desordenar, formato A4/folio  
- Estilo PDF limpio, con numeración, encabezado e identidad del centro  

---

## 🧱 MVC y rutas implicadas

| Componente           | Ruta                                               |
|----------------------|----------------------------------------------------|
| Generador PDF        | `utilidades/generar_pdf_examen.php`               |
| Controlador          | `controladores/examen_pdf.php`                     |
| Vista de opciones    | `vistas/profesor/opciones_pdf_examen.php`         |
| Carpeta temporal     | `/tmp/pdf_examenes/`                               |

---

## 🗃️ Tablas implicadas

Utiliza datos ya existentes de:

- `examenes`
- `preguntas`
- `respuestas`
- `modulos`
- `cursos`

No requiere crear nuevas tablas.

---

## 🔐 Seguridad

- Solo accesible para admin o profesor asignado al examen  
- Protección por ID de examen y sesión activa  
- PDF generado no almacena datos personales del alumno  

---

## 🪵 Manejo de errores y logs

- Si hay error en la generación del PDF → se registra en `/almacenamiento/logs/pdf_examen_error.log`  
- Generación exitosa → se registra en `registro_actividad`  
- Si se pierde conexión con imágenes multimedia → log + aviso visible  

---

## ✅ Checklist Copilot

- [ ] Crear script `generar_pdf_examen.php` con TCPDF o equivalente  
- [ ] Incluir cabecera y pie de página configurables  
- [ ] Mostrar preguntas con o sin respuestas según opción  
- [ ] Añadir espacio para respuestas de desarrollo si aplica  
- [ ] Proteger el acceso a través de rol  
- [ ] Registrar la acción en `registro_actividad`  

---

📌 A continuación, Copilot debe leer e implementar: 40_duplicar_examen.md
