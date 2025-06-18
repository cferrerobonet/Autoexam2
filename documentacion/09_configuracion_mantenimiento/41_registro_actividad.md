# 41 – Registro de actividad del sistema

## 🎯 Objetivos clave del sistema

Registrar automáticamente cada acción relevante realizada por los usuarios en la plataforma AUTOEXAM2 para:

- Auditar el uso del sistema  
- Rastrear accesos, ediciones, creaciones y eliminaciones  
- Generar informes de trazabilidad  
- Detectar errores o mal uso del sistema  

---

## 🔗 Dependencias

- Todos los módulos funcionales  
- `10_modulo_usuarios.md` (para identificar actor)  
- `33_exportacion_datos.md` (para exportar registros)  
- `06_configuracion.md` (si se activa/desactiva el log)  

---

## 📊 Funcionalidades

| Funcionalidad                   | Descripción                                                       |
|--------------------------------|-------------------------------------------------------------------|
| Registrar evento automático    | Cada vez que se realiza una acción relevante                      |
| Campos registrados             | Fecha, hora, IP, usuario, acción, módulo, ID afectado             |
| Filtro por rol, módulo o usuario | Visualización por criterios seleccionables                        |
| Exportar registro              | CSV o XLSX vía módulo 33                                          |

---

## 🧑‍💻 UI/UX

- Vista tipo tabla con filtros: por fecha, usuario, acción  
- Orden por fecha descendente  
- Colores por tipo de acción: crear, editar, borrar, acceder  
- Accesible desde panel de administrador  

---

## 🧱 MVC y rutas implicadas

| Componente              | Ruta                                         |
|-------------------------|----------------------------------------------|
| Controlador             | `controladores/registro_actividad.php`       |
| Vista admin             | `vistas/admin/registro_actividad.php`        |
| Utilidad de inserción   | `utilidades/logger.php`                      |
| Exportación (mod. 33)   | `utilidades/exportar_logs.php`              |

---

## 🗃️ Tabla `registro_actividad`

| Campo        | Tipo         | Requerido | Descripción                                |
|--------------|--------------|-----------|--------------------------------------------|
| id_registro  | INT PK AI    | ✔️        | Identificador único del registro           |
| id_usuario   | INT (FK)     | ✔️        | Usuario que ejecutó la acción              |
| rol          | ENUM         | ✔️        | Rol del usuario                            |
| modulo       | VARCHAR(100) | ✔️        | Módulo donde ocurrió la acción             |
| accion       | VARCHAR(100) | ✔️        | Tipo de acción: login, crear, editar, etc. |
| id_objetivo  | INT          | ✖️        | ID del elemento afectado (si aplica)       |
| fecha_hora   | DATETIME     | ✔️        | Timestamp del evento                       |
| ip           | VARCHAR(45)  | ✖️        | Dirección IP del usuario                   |
| descripcion  | TEXT         | ✖️        | Descripción adicional si aplica            |

---

## 🔐 Seguridad

- Solo el administrador puede acceder al registro completo  
- Validación para evitar registros maliciosos  
- Acciones sensibles como login, logout, eliminar y exportar siempre se registran  

---

## 🪵 Manejo de errores y logs

- Si falla el log → se guarda error en `/almacenamiento/logs/registro_actividad_error.log`  
- Fallos en inserciones se notificarán al administrador (modo debug)  
- Integrado con fallback de logs plano si la tabla no está disponible  

---

## ✅ Checklist para Copilot

- [ ] Crear tabla `registro_actividad`  
- [ ] Añadir función global `log_evento()` en `logger.php`  
- [ ] Llamar a `log_evento()` desde todos los módulos clave  
- [ ] Crear vista para consulta por el administrador  
- [ ] Añadir opción de exportar a CSV/XLSX  
- [ ] Validar datos antes de insertarlos en log  

---

📌 A continuación, Copilot debe leer e implementar: `33_exportacion_datos.md`
