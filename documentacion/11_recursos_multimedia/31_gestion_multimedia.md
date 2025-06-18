# 31 – Gestión de multimedia y archivos en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Centralizar la carga, visualización, uso y eliminación de archivos multimedia  
- Garantizar seguridad, trazabilidad y reutilización de archivos  
- Permitir a cada usuario gestionar sus propios recursos y a los docentes compartirlos  
- Establecer filtros, vistas y rutas claras para galería y avatar institucional  
- Integrarse con preguntas, exámenes y configuración visual  

---

## 🧭 Objetivo

Permitir la administración eficiente, segura y reutilizable de todos los archivos que se cargan o vinculan en la aplicación.

---

## 📂 Tabla `archivos`

| Campo         | Tipo             | Descripción                                         |
|---------------|------------------|-----------------------------------------------------|
| id_archivo    | INT PK AI        | Identificador único                                 |
| tipo          | ENUM             | 'imagen', 'pdf', 'url', 'video', 'logo', 'avatar'   |
| ruta          | TEXT             | Ruta local o URL                                    |
| descripcion   | TEXT             | Descripción breve o título                          |
| fecha_subida  | DATETIME         | Fecha de alta                                       |
| subido_por    | INT (FK usuario) | Usuario que lo cargó                                |
| visible       | TINYINT(1)       | 1 = visible, 0 = oculto                             |

---

## 🛠️ Funcionalidades

- Subida con validación (formato, peso, dimensiones)  
- Drag & drop o selección manual  
- Visualización según tipo (preview, icono, thumbnail)  
- Eliminación solo si no está en uso  
- Asociación automática a preguntas, usuarios, exámenes  
- Filtro por tipo, usuario, módulo, curso  

---

## 👥 Accesible por

| Acción                    | Admin | Profesor | Alumno |
|---------------------------|-------|----------|--------|
| Subir imagen/avatar/logo  | ✔️     | ✔️        | ✔️      |
| Subir PDF (IA/examen)     | ✔️     | ✔️        | ❌      |
| Asociar URL externa       | ✔️     | ✔️        | ❌      |
| Ver galería multimedia    | ✔️     | ✔️        | ❌      |
| Eliminar archivo propio   | ✔️     | ✔️        | ❌      |

---

## 🧪 UI/UX

- Estilo galería (grid de imágenes/pdfs)  
- Iconos tipo: `fa-image`, `fa-file-pdf`, `fa-youtube`  
- Tooltips: tipo, peso, fecha  
- Búsqueda por usuario, módulo, examen, descripción  
- Vista previa (embed para video, thumbnail para imagen/pdf)  

---

## 📋 Estándar de tabla interactiva

- Acciones fuera de la tabla (subir, eliminar…)  
- Selección múltiple por checkbox  
- Edición directa no aplica (solo gestión por fila)  
- Fila tipo “pijama”  
- Separación clara entre filtros y acciones  
- Orden asc/desc por tipo, usuario, fecha  
- Paginación (5/10/15/20/Todos), por defecto 15  

---

## 📂 MVC

| Componente               | Ruta                                         |
|--------------------------|----------------------------------------------|
| Vista principal          | `vistas/recursos/galeria_archivos.php`       |
| Controlador              | `controladores/archivos.php`                 |
| Modelo                   | `modelos/archivo.php`                        |
| JS galería               | `publico/scripts/galeria_multimedia.js`      |
| Formulario de subida     | `componentes/formulario_subida.php`          |

---

## 🛡️ Seguridad

- Validación tipo MIME y extensión  
- Límite de tamaño y resolución  
- Acceso restringido por rol  
- Renombrado automático del archivo  
- Registro de cada subida/borrado en `registro_actividad`  

---

## 🪵 Manejo de errores y logs

- Errores en subida o eliminación → log en `/almacenamiento/logs/gestion_multimedia_error.log`  
- Ficheros no válidos → rechazo automático y feedback en formulario  
- Operaciones exitosas → registradas en `registro_actividad`  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación estricta del archivo (peso, extensión, resolución)
- Prevención de ejecución embebida en PDF o JS malicioso
- Renombrado automático y normalización de nombre
- Prohibición de sobrescritura o colisión entre usuarios
- Control de acceso según tipo y propiedad del archivo
- Logs de error y actividad separados por tipo de incidente
- Gestión robusta de rutas y visibilidad pública solo si es permitido

---

## ✅ Checklist Copilot

- [ ] Crear tabla `archivos`  
- [ ] Implementar subida validada  
- [ ] Vista galería con filtros  
- [ ] Asociar archivos a usuarios o exámenes  
- [ ] Permitir borrado si no está en uso  
- [ ] Mostrar thumbnails y previews  
- [ ] Registrar en `registro_actividad`  

---

## 📁 Carpeta de almacenamiento de avatares

Las fotos de perfil de los usuarios se almacenan en una ubicación pública y organizada para facilitar su acceso, edición y visualización en toda la plataforma.

### Ruta de almacenamiento:

```
/publico/subidas/avatars/
```

### Reglas y convenciones

| Parámetro              | Valor recomendado                                        |
|------------------------|----------------------------------------------------------|
| Formato permitido      | `.jpg`, `.jpeg`, `.png`, `.webp`                         |
| Tamaño máximo          | 256x256 px (redimensionado automáticamente si es mayor) |
| Nombre del archivo     | `usuario_{id}.webp` o hash único                         |
| Imagen por defecto     | `user_image_default.png`                                 |
| Acceso desde navegador | Sí, solo lectura (proteger contra scripts)              |

---

📌 A continuación, Copilot debe leer e implementar: 32_calendario_eventos.md
