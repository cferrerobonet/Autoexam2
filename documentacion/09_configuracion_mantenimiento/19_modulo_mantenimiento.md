# 19 – Modulo de Mantenimiento en AUTOEXAM2

---

## 🎯 Objetivos clave del sistema

- Ofrecer herramientas de limpieza y depuración controlada para el administrador  
- Permitir eliminar datos masivamente sin afectar la configuración base del sistema  
- Garantizar trazabilidad y confirmación previa de cada operación  
- Asegurar que solo usuarios con rol administrador puedan acceder y ejecutar acciones críticas  
- Proteger la integridad de la base de datos ante operaciones destructivas  

---

## 🧭 Objetivo

Proveer herramientas internas para limpiar datos de prueba, corregir estructuras huérfanas y resetear partes del sistema sin afectar la configuración base ni las cuentas principales.

---

## 🔐 Acceso restringido

- URL protegida: `/admin/mantenimiento`  
- Solo accesible por rol `administrador`  
- Se requiere confirmación con contraseña en cada acción  

---

## 🧱 Acciones disponibles

| Acción                                 | Confirmación doble | Resultado                                               |
|----------------------------------------|---------------------|----------------------------------------------------------|
| Vaciar `usuarios` (solo alumnos)       | ✅                  | Elimina todos los registros de alumnos                   |
| Vaciar `examenes`                      | ✅                  | Elimina todos los exámenes creados                      |
| Vaciar `respuestas_alumno`             | ✅                  | Elimina todas las entregas y respuestas                 |
| Vaciar `registro_actividad`            | ✅                  | Borra el historial de acciones                          |
| Reasignar cursos sin profesor          | ✅                  | Marca cursos como “sin asignar”                         |
| Eliminar cursos inactivos sin relaciones| ✅                 | Eliminación física de cursos sin módulos ni exámenes     |

---

## 🧠 Reglas de seguridad

- Cada operación muestra:
  - Descripción detallada
  - Advertencia de datos que se perderán
  - Formulario de confirmación con contraseña del administrador
- Todas las acciones son registradas en la tabla `registro_actividad`

---

## 🧑‍💻 UI/UX

- Diseño con tarjetas colapsables por tipo de acción  
- Botones `btn-danger` con texto claro  
- Tooltips en cada botón con resumen del efecto  
- Iconos de alerta (`fa-exclamation-triangle`)  
- Modal de confirmación con doble verificación  

---

## 📂 Archivos y estructura

| Componente                   | Ruta                                         |
|------------------------------|----------------------------------------------|
| Controlador mantenimiento    | `app/controladores/mantenimiento.php`        |
| Vista panel mantenimiento    | `app/vistas/administrador/mantenimiento.php` |
| Utilidades limpieza          | `app/utilidades/mantenimiento_acciones.php`  |

---

## 🗃️ Tablas implicadas

- `usuarios`  
- `examenes`  
- `respuestas_alumno`  
- `registro_actividad`  
- `cursos`  
- `modulos`

---

## 🪵 Manejo de errores y logs

- Errores durante operaciones → log en `/almacenamiento/logs/mantenimiento_error.log`  
- Si la contraseña es incorrecta → mensaje de error visible + log  
- Acciones ejecutadas → registradas en `registro_actividad` con detalle del tipo de acción  

---

## 🛡️ Fortalezas que Copilot debe implementar

- Confirmación con contraseña antes de cualquier acción destructiva  
- Protección contra ejecución por URL sin formulario válido  
- Validación cruzada antes de eliminar para evitar incoherencias relacionales  
- Registro detallado en `registro_actividad` con ID de usuario, IP y tipo de acción  
- Logs técnicos en `/almacenamiento/logs/mantenimiento_error.log`  
- Acceso exclusivo para administradores con sesión activa  
- Interfaz clara que obligue a leer y aceptar advertencias antes de ejecutar  

---

## ✅ Checklist para Copilot

- [ ] Crear vista segura de mantenimiento solo para admins  
- [ ] Mostrar resumen claro de cada acción antes de ejecutar  
- [ ] Confirmar con contraseña del administrador  
- [ ] Ejecutar acciones con seguridad relacional  
- [ ] Registrar todas las operaciones en el sistema de auditoría  

---

📌 A continuación, Copilot debe leer e implementar: 33_exportacion_datos.md
