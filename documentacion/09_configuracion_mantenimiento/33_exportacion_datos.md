# 33 – Exportación y respaldo de datos

## 🎯 Objetivos clave del sistema

Permitir al administrador (y en parte al profesor) exportar datos clave del sistema en formatos abiertos (XLSX, CSV, PDF) para copia, auditoría o análisis externo.

---

## 🔗 Dependencias funcionales

- `10_modulo_usuarios.md`
- `14_modulo_examenes.md`
- `16_modulo_calificaciones.md`
- `17_modulo_estadisticas.md`
- `41_registro_actividad.md`
- `06_configuracion.md`

---

## 🗃️ Tablas utilizadas o requeridas

Este módulo **no requiere nuevas tablas**, pero accede a:

- `usuarios`
- `cursos`, `modulos`, `examenes`
- `calificaciones`
- `registro_actividad`
- `config_sistema`, `config_versiones` (modo lectura)

---

## 📊 Tipos de datos exportables

| Tipo de datos                        | Formato            | Acceso     |
|--------------------------------------|---------------------|------------|
| Listado de usuarios                  | XLSX, CSV           | Admin      |
| Alumnos por curso                    | XLSX, PDF           | Admin, prof|
| Resultados de un examen              | XLSX, CSV, PDF      | Admin, prof|
| Estadísticas por curso/módulo        | XLSX, PNG (gráfico) | Admin      |
| Registro de actividad (auditoría)    | CSV                 | Admin      |
| Configuración SMTP/SFTP actual       | TXT, JSON           | Admin      |

---

## 🛠️ Detalles técnicos

- Carpeta temporal de exportación: `/tmp/descargas/`
- Generación en servidor y descarga directa
- Eliminación automática tras 1 hora o al cerrar sesión
- Generación de nombres de archivo con timestamp: `usuarios_20250522.csv`

---

## 🧪 UI/UX

- Botones `Exportar como...` junto a listados
- Modal de selección de columnas si aplica
- Iconos: `fa-download`, `fa-file-excel`, `fa-file-pdf`
- Feedback tras exportación: ruta, éxito o error

---

## 🧱 MVC y rutas implicadas

| Componente              | Ruta                                          |
|-------------------------|-----------------------------------------------|
| Controlador principal   | `controladores/exportar.php`                  |
| Vistas exportables      | `vistas/admin/usuarios.php`, `examenes.php`   |
| Utilidades              | `utilidades/exportador_excel.php`, `pdf.php` |

---

## 🧩 Seguridad

- Solo roles autorizados (admin, y profesor en su ámbito)
- Validación de filtros activos
- Registro en `registro_actividad`
- Token de sesión para acceso al archivo generado
- Archivo temporal se borra tras descarga o sesión cerrada

---

## 📋 Estándar de tabla interactiva (origen)

- Botón de exportar fuera de la tabla
- Filtros aplicados antes de exportar
- Orden y columnas respetados
- Compatibilidad con DataTables o tabla personalizada

---


---

## 🛡️ Fortalezas que Copilot debe implementar

- Validación del rol y filtros activos antes de exportar
- Protección contra inyección o exportación de columnas no permitidas
- Registro detallado de cada exportación en `registro_actividad`
- Logs técnicos en `/almacenamiento/logs/exportaciones_error.log` si falla alguna exportación
- Acceso exclusivo con sesión activa y token de validación
- Eliminación programada y segura de archivos temporales


## ✅ Checklist para Copilot

- [ ] Crear `exportar.php` con rutas por tipo
- [ ] Usar `PhpSpreadsheet`, `fputcsv` y `TCPDF` según formato
- [ ] Incluir filtros y contexto en cada exportación
- [ ] Validar permisos y rol de usuario
- [ ] Generar archivo temporal y forzar descarga
- [ ] Registrar cada exportación en `registro_actividad`

---

📌 A continuación, Copilot debe leer e implementar: `08_ui_ux.md`
