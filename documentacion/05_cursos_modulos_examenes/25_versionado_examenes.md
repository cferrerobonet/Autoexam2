# 25 – Versionado de exámenes

---

## 🎯 Objetivos clave del sistema

- Conservar un historial completo y rastreable de cambios en los exámenes  
- Permitir a los profesores comparar versiones antiguas y actuales  
- Restaurar versiones anteriores sin pérdida de datos  
- Facilitar el control de calidad, revisión pedagógica y trazabilidad histórica  
- Mejorar la exportación y archivo de versiones con contexto  

---

## 🧭 Objetivo

Permitir almacenar y gestionar distintas versiones de un mismo examen para conservar historial de cambios, comparar versiones anteriores, restaurar versiones antiguas y mantener trazabilidad de la evolución del contenido.

---

## 🔗 Dependencias

- `14_modulo_examenes.md`
- `35_banco_preguntas.md`
- `36_informe_global_curso.md`
- `33_exportacion_datos.md`

---

## 📊 Funcionalidades clave

| Funcionalidad               | Descripción                                                                |
|-----------------------------|-----------------------------------------------------------------------------|
| Guardar nueva versión       | Cada vez que se edita un examen, se puede guardar como nueva versión       |
| Historial de versiones      | Muestra lista de versiones con fecha, autor y comentario opcional          |
| Comparar versiones          | Ver diferencias entre dos versiones del mismo examen                       |
| Restaurar versión           | Permite recuperar una versión anterior y hacerla activa                    |
| Exportar versión concreta   | Exportar versión seleccionada a PDF o Excel                                |

---

## 🧪 UI/UX

- Botón “Guardar como nueva versión” en vista de edición  
- Tabla de versiones con campos: versión, fecha, autor, comentario  
- Comparador visual (por nombre, preguntas, número de ítems)  
- Botón “Restaurar” visible solo si el usuario es admin o autor original  

---

## 🧱 MVC y rutas implicadas

| Componente                     | Ruta                                            |
|--------------------------------|-------------------------------------------------|
| Tabla de versiones             | `examenes_versiones` (nueva)                   |
| Controlador                    | `controladores/versionado_examenes.php`        |
| Vista historial                | `vistas/profesor/versiones_examen.php`         |
| Utilidades de exportación      | `utilidades/exportar_version_examen.php`       |

---

## 🗃️ Tablas y campos implicados

### Tabla `examenes_versiones`

| Campo              | Tipo         | Requerido | Descripción                                 |
|--------------------|--------------|-----------|---------------------------------------------|
| id_version         | INT PK AI    | ✔️        | Identificador único                         |
| id_examen_original | INT (FK)     | ✔️        | ID del examen base                          |
| titulo             | VARCHAR(150) | ✔️        | Título de la versión                        |
| preguntas_json     | TEXT         | ✔️        | Preguntas codificadas (JSON)                |
| activo             | TINYINT(1)   | ✔️        | 1 si es la versión activa actual            |
| autor              | INT (FK)     | ✔️        | ID del usuario que creó la versión          |
| fecha_creacion     | DATETIME     | ✔️        | Fecha y hora de creación                    |
| comentario         | TEXT         | ✖️        | Nota opcional del creador                   |

---

## 🔐 Seguridad

- Solo el autor original o el administrador puede editar o restaurar versiones  
- Todas las versiones son visibles, pero solo una puede estar activa a la vez  
- Registro en `registro_actividad` de restauraciones y versiones nuevas  

---

## 🪵 Manejo de errores y logs

- Fallo al guardar JSON → se registra en `/almacenamiento/logs/versionado_examen_error.log`  
- Restauraciones forzadas → log + email al administrador (modo debug)  
- Acciones de creación/restauración → logueadas en `registro_actividad`  

---

## ✅ Checklist Copilot

- [ ] Crear tabla `examenes_versiones`  
- [ ] Añadir botón de guardado como versión en editor de examen  
- [ ] Implementar vista de historial y comparación  
- [ ] Activar restauración de versión previa  
- [ ] Registrar todos los cambios y restauraciones  
- [ ] Habilitar exportación individual por versión  

---

📌 A continuación, Copilot debe leer e implementar: `35_banco_preguntas.md`
